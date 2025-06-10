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
            error_log('Upload error code: ' . $file['error']);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tải file lên.']);
            exit;
        }

        // Validate file type
        if (!isValidFileType($file)) {
            error_log('Invalid file type: ' . $file['type']);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (jpg, png, gif, webp) hoặc video (mp4, mov, avi, wmv).']);
            exit;
        }

        // Check if file is image or video
        $isImage = isImage($file);

        // Check file size
        if ($isImage && $file['size'] > MAX_IMAGE_SIZE) {
            error_log('Image size too large: ' . $file['size']);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Kích thước ảnh không được vượt quá 5MB.']);
            exit;
        }
        if (!$isImage && $file['size'] > MAX_VIDEO_SIZE) {
            error_log('Video size too large: ' . $file['size']);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Kích thước video không được vượt quá 15MB.']);
            exit;
        }

        // Generate unique filename
        $filename = generateUniqueFilename($file['name']);
        $uploadPath = UPLOAD_PATH . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            error_log('Failed to move uploaded file.');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể lưu file.']);
            exit;
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
    error_log('Exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi máy chủ nội bộ. Vui lòng thử lại sau.'
    ], JSON_UNESCAPED_UNICODE);
}
