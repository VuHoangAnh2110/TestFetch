<?php
header('Content-Type: text/html; charset=UTF-8');

    /**
     * Lấy nội dung HTML từ URL bằng cURL
     * @param mixed $url
     * @throws \Exception
     * @return bool|string
     */
    function fetchHTMLContent($url) {
        // Khởi tạo cURL
        $ch = curl_init();
        
        // Cấu hình cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  //Trả về kết quả thay vì in ra màn hình
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  //Tự động theo dõi các redirect 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //Tắt kiểm tra SSL certificate (cho HTTPS)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Tắt kiểm tra hostname (cho HTTPS)
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Thời gian timeout
        // Thiết lập User-Agent để tránh bị chặn bởi một số server
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        
        // Thực hiện request
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: " . $error);
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: " . $httpCode);
        }
        
        return $html;
    }

    /**
     * Trích xuất nội dung chính từ HTML
     * @param string $html
     * @return string
     */
    function extractMainContent($html) {
        // Sử dụng DOMDocument để parse HTML
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);              // Tắt warning cho HTML không chuẩn
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html); // Thêm encoding XML để tránh lỗi ký tự đặc biệt
        libxml_clear_errors();

        // Lấy toàn bộ html content
        $body = $dom->getElementsByTagName('html')->item(0);
        
        if ($body) {
            //Chuyển DOM object về HTML string
            return $dom->saveHTML($body);
        }
        
        return $html;
    }

    /**
     * Chỉnh sửa HTML: thay đổi title và loại bỏ các thẻ không cần thiết
     * @param string $html
     * @return string
     */
    function modifyHTMLContent($html) {
        $html = preg_replace('/<title[^>]*>.*?<\/title>/i', '<title>Trường Đại học Mở Hà Nội</title>', $html);
        $html = preg_replace('/<link[^>]*rel=["\']dns-prefetch["\'][^>]*>/i', '', $html);
        $html = preg_replace('/<link[^>]*rel=["\']alternate["\'][^>]*type=["\']application\/rss\+xml["\'][^>]*>/i', '', $html);        
        $html = preg_replace('/<link[^>]*rel=["\']https:\/\/api\.w\.org\/["\'][^>]*>/i', '', $html);        
        $html = preg_replace('/<link[^>]*rel=["\']EditURI["\'][^>]*>/i', '', $html);        
        $html = preg_replace('/<link[^>]*rel=["\']wlwmanifest["\'][^>]*>/i', '', $html);        
        $html = preg_replace('/<meta[^>]*name=["\']generator["\'][^>]*content=["\']WordPress[^"\']*["\'][^>]*>/i', '', $html);        
        $html = preg_replace('/<link[^>]*rel=["\']canonical["\'][^>]*>/i', '', $html);        
        $html = preg_replace('/<link[^>]*rel=["\']shortlink["\'][^>]*>/i', '', $html);        
        $html = preg_replace('/<link[^>]*rel=["\']alternate["\'][^>]*type=["\']application\/json\+oembed["\'][^>]*>/i', '', $html);
        $html = preg_replace('/<link[^>]*rel=["\']alternate["\'][^>]*type=["\']text\/xml\+oembed["\'][^>]*>/i', '', $html);        
        $html = preg_replace('/<meta[^>]*name=["\']generator["\'][^>]*content=["\']Powered by Visual Composer[^"\']*["\'][^>]*>/i', '', $html);
        $html = preg_replace('/^\s*\n/m', '', $html);
        $html = preg_replace('/\n\s*\n/', "\n", $html);
                
        return $html;
    }

    function overrideSplideCss($html){
        $splideOverrideCSS = '
            <style type="text/css">
            /* Override external splide-skyblue.min.css */
            .splide__list {
                backface-visibility: hidden !important;
                display: -ms-flexbox !important;
                display: flex !important;
                height: auto !important; 
                margin: 0 !important;
                padding: 0 !important;
            }
            </style>';
        $insertAfter = '</head>';
        $html = str_replace($insertAfter, $splideOverrideCSS . $insertAfter, $html);
    
        return $html;
    }

    /**
     * Lưu nội dung vào file cache
     * @param string $content
     * @param string $filename
     * @return string
     */
    function saveContentToFile($content, $filename = 'cached_content.html') {
        $filepath = __DIR__ . '/' . $filename;
        file_put_contents($filepath, $content);
        return $filepath;
    }

    try {
        // URL trang builder
        $builderUrl = 'https://conf.hou.edu.vn/builder/conf_hou_edu_vn';
                
        // Lấy HTML từ trang builder
        $htmlContent = fetchHTMLContent($builderUrl);
        
        if ($htmlContent) {            
            // Trích xuất nội dung chính
            $mainContent = extractMainContent($htmlContent);
            
            $modifiedContent = modifyHTMLContent($mainContent);
            $modifiedContent = overrideSplideCss($modifiedContent);
            
            // Lưu vào file cache
            $savedFile = saveContentToFile($modifiedContent);
            
            // Hiển thị nội dung thực tế
            echo $modifiedContent;
            
        } else {
            echo "<div style='color: red;'>Không thể lấy dữ liệu</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>Lỗi: " . htmlspecialchars($e->getMessage()) . "</div>";
    }


?>