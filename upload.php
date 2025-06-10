<?php
require_once 'config.php';

// Ensure no PHP errors are sent in the JSON response
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

try {
    if (empty($_FILES['files'])) {
        throw new Exception('Không có file nào được tải lên.');
    }

    $uploadResults = [];

    foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
        $file = [
            'name' => $_FILES['files']['name'][$key],
            'type' => $_FILES['files']['type'][$key],
            'tmp_name' => $tmpName,
            'error' => $_FILES['files']['error'][$key],
            'size' => $_FILES['files']['size'][$key]
        ];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Có lỗi xảy ra khi tải file lên.');
        }

        // Validate file type
        if (!isValidFileType($file)) {
            throw new Exception('Chỉ chấp nhận file ảnh (jpg, png, gif, webp) hoặc video (mp4, mov, avi, wmv).');
        }

        // Check if file is image or video
        $isImage = isImage($file);

        // Check file size
        if ($isImage && $file['size'] > MAX_IMAGE_SIZE) {
            throw new Exception('Kích thước ảnh không được vượt quá 5MB.');
        }
        if (!$isImage && $file['size'] > MAX_VIDEO_SIZE) {
            throw new Exception('Kích thước video không được vượt quá 15MB.');
        }

        // Generate unique filename
        $filename = generateUniqueFilename($file['name']);
        $uploadPath = UPLOAD_PATH . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Không thể lưu file.');
        }

        // Generate public URL
        $fileUrl = getFileUrl($filename);

        try {
            // Send Telegram notification
            $message = "<b>File Mới Được Tải Lên</b>\n";
            $message .= "Tên file: " . $file['name'] . "\n";
            $message .= "Kích thước: " . round($file['size'] / 1024 / 1024, 2) . "MB\n";
            $message .= "Loại: " . ($isImage ? 'Ảnh' : 'Video') . "\n";
            $message .= "URL: " . $fileUrl;

            sendTelegramNotification($message);
        } catch (Exception $e) {
            // Log Telegram error but don't stop the upload process
            error_log('Telegram notification error: ' . $e->getMessage());
        }

        // Add to results
        $uploadResults[] = [
            'success' => true,
            'url' => $fileUrl,
            'name' => $file['name'],
            'size' => $file['size'],
            'type' => $isImage ? 'image' : 'video'
        ];
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'files' => $uploadResults
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
