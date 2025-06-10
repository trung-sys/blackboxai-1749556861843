<?php
require_once 'config.php';

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

    // Get file info from URL
    $headers = get_headers($url, 1);
    if ($headers === false) {
        throw new Exception('Không thể truy cập URL.');
    }

    // Get content type and size
    $contentType = $headers['Content-Type'] ?? '';
    if (is_array($contentType)) {
        $contentType = end($contentType);
    }
    
    $contentLength = $headers['Content-Length'] ?? '';
    if (is_array($contentLength)) {
        $contentLength = end($contentLength);
    }
    $contentLength = intval($contentLength);

    // Check if it's an image or video
    $isImage = in_array($contentType, ALLOWED_IMAGE_TYPES);
    $isVideo = in_array($contentType, ALLOWED_VIDEO_TYPES);

    if (!$isImage && !$isVideo) {
        throw new Exception('URL phải trỏ đến file ảnh hoặc video được hỗ trợ.');
    }

    // Check file size
    if ($isImage && $contentLength > MAX_IMAGE_SIZE) {
        throw new Exception('Kích thước ảnh không được vượt quá 5MB.');
    }
    if ($isVideo && $contentLength > MAX_VIDEO_SIZE) {
        throw new Exception('Kích thước video không được vượt quá 15MB.');
    }

    // Generate filename from URL
    $originalName = basename(parse_url($url, PHP_URL_PATH));
    $filename = generateUniqueFilename($originalName);
    $uploadPath = UPLOAD_PATH . $filename;

    // Download and save file
    $content = file_get_contents($url);
    if ($content === false) {
        throw new Exception('Không thể tải file từ URL.');
    }

    if (!file_put_contents($uploadPath, $content)) {
        throw new Exception('Không thể lưu file.');
    }

    // Generate public URL
    $fileUrl = getFileUrl($filename);

    // Verify downloaded file
    $downloadedFile = [
        'tmp_name' => $uploadPath,
        'type' => $contentType,
        'size' => filesize($uploadPath)
    ];

    if (!isValidFileType($downloadedFile)) {
        unlink($uploadPath);
        throw new Exception('File tải về không phải là ảnh hoặc video hợp lệ.');
    }

    // Send Telegram notification
    $message = "<b>File Mới Được Tải Lên (URL)</b>\n";
    $message .= "URL gốc: " . $url . "\n";
    $message .= "Kích thước: " . round($contentLength / 1024 / 1024, 2) . "MB\n";
    $message .= "Loại: " . ($isImage ? 'Ảnh' : 'Video') . "\n";
    $message .= "URL mới: " . $fileUrl;

    sendTelegramNotification($message);

    // Return success response
    echo json_encode([
        'success' => true,
        'url' => $fileUrl,
        'name' => $originalName,
        'size' => $contentLength,
        'type' => $isImage ? 'image' : 'video'
    ]);

} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
