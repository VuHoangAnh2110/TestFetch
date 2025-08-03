<?php
header('Content-Type: text/html; charset=UTF-8');

    if (isset($_POST['refresh'])) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

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
        
        echo "<h2>Đang lấy dữ liệu từ: " . htmlspecialchars($builderUrl) . "</h2>";
        
        // Lấy HTML từ trang builder
        $htmlContent = fetchHTMLContent($builderUrl);
        
        if ($htmlContent) {
            echo "<div style='color: green;'>Lấy dữ liệu thành công!</div>";
            
            // Trích xuất nội dung chính
            $mainContent = extractMainContent($htmlContent);
            
            // Lưu vào file cache
            $savedFile = saveContentToFile($mainContent);
            echo "<div style='color: blue;'>Đã lưu vào file: " . htmlspecialchars($savedFile) . "</div>";
            
            // Hiển thị preview
            echo "<hr>";
            echo "<h3>Preview nội dung đã lấy:</h3>";
            echo "<div style='border: 1px solid #ccc; padding: 10px; max-height: 400px; overflow-y: auto;'>";
            echo "<pre>" . htmlspecialchars(substr($mainContent, 0, 3000)) . "...</pre>";
            echo "</div>";
            
            // Hiển thị nội dung thực tế
            echo "<hr>";
            echo "<h3>Nội dung được render:</h3>";
            echo "<div style='border: 2px solid #007cba; padding: 15px;'>";
            echo $mainContent;
            echo "</div>";
            
        } else {
            echo "<div style='color: red;'>Không thể lấy dữ liệu</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>Lỗi: " . htmlspecialchars($e->getMessage()) . "</div>";
    }

    echo "<hr>";
    echo "<div style='display: flex; justify-content: center; margin: 20px 0;'>";
    echo "<form method='post'>";
    echo "<button type='submit' name='refresh' style='padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer;'>Làm mới Dữ Liệu</button>";
    echo "</form>";
    echo "</div>";


?>