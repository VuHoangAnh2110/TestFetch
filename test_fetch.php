<?php
header('Content-Type: text/html; charset=UTF-8');

function fetchHTMLContent($url) {
    // Khởi tạo cURL
    $ch = curl_init();
    
    // Cấu hình cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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

function extractMainContent($html) {
    // Sử dụng DOMDocument để parse HTML
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Tắt warning cho HTML không chuẩn
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    
    // Lấy toàn bộ body content (hoặc có thể chỉ định phần cụ thể)
    $body = $dom->getElementsByTagName('body')->item(0);
    
    if ($body) {
        return $dom->saveHTML($body);
    }
    
    return $html;
}

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
        echo "<div style='color: green;'>✓ Lấy dữ liệu thành công!</div>";
        
        // Trích xuất nội dung chính
        $mainContent = extractMainContent($htmlContent);
        
        // Lưu vào file cache (tùy chọn)
        $savedFile = saveContentToFile($mainContent);
        echo "<div style='color: blue;'>✓ Đã lưu vào file: " . htmlspecialchars($savedFile) . "</div>";
        
        // Hiển thị preview
        echo "<hr>";
        echo "<h3>Preview nội dung đã lấy:</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; max-height: 400px; overflow-y: auto;'>";
        echo "<pre>" . htmlspecialchars(substr($mainContent, 0, 2000)) . "...</pre>";
        echo "</div>";
        
        // Hiển thị nội dung thực tế
        echo "<hr>";
        echo "<h3>Nội dung được render:</h3>";
        echo "<div style='border: 2px solid #007cba; padding: 15px;'>";
        echo $mainContent;
        echo "</div>";
        
    } else {
        echo "<div style='color: red;'>✗ Không thể lấy dữ liệu</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Lỗi: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Thêm form để refresh thủ công
echo "<hr>";
echo "<form method='post'>";
echo "<button type='submit' name='refresh' style='padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer;'>Refresh Dữ Liệu</button>";
echo "</form>";

if (isset($_POST['refresh'])) {
    echo "<script>window.location.reload();</script>";
}
?>