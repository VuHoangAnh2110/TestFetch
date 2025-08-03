<?php
// File này có thể chạy định kỳ bằng cron job hoặc scheduled task

    function autoSyncContent() {
        $builderUrl = 'https://conf.hou.edu.vn/builder/conf_hou_edu_vn';
        $outputFile = 'synced_content.html';
        
        try {
            // Include functions từ file chính
            include_once 'test_fetch.php';
            
            $htmlContent = fetchHTMLContent($builderUrl);
            $mainContent = extractMainContent($htmlContent);
            
            // Lưu với timestamp
            $contentWithTimestamp = "<!-- Cập nhật lúc: " . date('Y-m-d H:i:s') . " -->\n" . $mainContent;
            file_put_contents($outputFile, $contentWithTimestamp);
            
            echo "Đồng bộ thành công lúc: " . date('Y-m-d H:i:s') . "\n";
            
        } catch (Exception $e) {
            echo "Lỗi đồng bộ: " . $e->getMessage() . "\n";
        }
    }

    // Chạy nếu được gọi trực tiếp
    if (php_sapi_name() === 'cli' || basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
        autoSyncContent();
    }
    
?>