<?php
require_once 'config.php';

// Ensure no PHP errors are sent in the JSON response
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

try {
    // Get URL from POST data
    $url = $_POST['url'] ?? null;
    if (!$url) {
        throw new Exception('URL không được để trống.');
    }

    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('URL không hợp lệ.');
    }

    // Initialize curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Get headers
    $response = curl_exec($ch);
    if ($response === false) {
        throw new Exception('Không thể truy cập URL.');
    }

    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    
    curl_close($ch);

    // Check if it's an image or video
    $isImage = false;
    $isVideo = false;
    
    foreach (ALLOWED_IMAGE_TYPES as $type) {
        if (strpos($contentType, $type) !== false) {
            $isImage = true;
            break;
        }
    }
    
    if (!$isImage) {
        foreach (ALLOWED_VIDEO_TYPES as $type) {
            if (strpos($contentType, $type) !== false) {
                $isVideo = true;
                break;
            }
        }
    }

    if (!$isImage && !$isVideo) {
        throw new Exception('URL phải trỏ đến file ảnh hoặc video được hỗ trợ.');
    }

    // Check file size
    if ($contentLength <= 0) {
        throw new Exception('Không thể xác định kích thước file.');
    }
    
    if ($isImage && $contentLength > MAX_IMAGE_SIZE) {
        throw new Exception('Kích thước ảnh không được vượt quá 5MB.');
    }
    if ($isVideo && $contentLength > MAX_VIDEO_SIZE) {
        throw new Exception('Kích thước video không được vượt quá 15MB.');
    }

    // Generate filename from URL
    $originalName = basename(parse_url($url, PHP_URL_PATH));
    if (empty($originalName)) {
        $originalName = 'downloaded_file';
    }
    
    $filename = generateUniqueFilename($originalName);
    $uploadPath = UPLOAD_PATH . $filename;

    // Download file
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $content = curl_exec($ch);
    
    if ($content === false) {
        throw new Exception('Không thể tải file từ URL.');
    }
    
    curl_close($ch);

    // Save file
    if (file_put_contents($uploadPath, $content) === false) {
        throw new Exception('Không thể lưu file.');
    }

    // Generate public URL
    $fileUrl = getFileUrl($filename);

    try {
        // Send Telegram notification
        $message = "<b>File Mới Được Tải Lên (URL)</b>\n";
        $message .= "URL gốc: " . $url . "\n";
        $message .= "Kích thước: " . round($contentLength / 1024 / 1024, 2) . "MB\n";
        $message .= "Loại: " . ($isImage ? 'Ảnh' : 'Video') . "\n";
        $message .= "URL mới: " . $fileUrl;

        sendTelegramNotification($message);
    } catch (Exception $e) {
        // Log Telegram error but don't stop the upload process
        error_log('Telegram notification error: ' . $e->getMessage());
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'url' => $fileUrl,
        'name' => $originalName,
        'size' => $contentLength,
        'type' => $isImage ? 'image' : 'video'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
