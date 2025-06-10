<?php
// Basic configuration
define('SITE_URL', 'http://localhost:8000');  // Change this to your actual domain in production
define('UPLOAD_PATH', 'uploads/');
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv']);

// File size limits
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);  // 5MB in bytes
define('MAX_VIDEO_SIZE', 15 * 1024 * 1024); // 15MB in bytes

// Telegram configuration
define('TELEGRAM_BOT_TOKEN', '7884222753:AAHYkSyHFhRrNT3E4oYzymhRO76qKuf7ezY');
define('TELEGRAM_CHAT_ID', '-1002417998960');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Increase PHP limits for file uploads
ini_set('upload_max_filesize', '15M');
ini_set('post_max_size', '15M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Function to send Telegram notification
function sendTelegramNotification($message) {
    try {
        $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
        $data = [
            'chat_id' => TELEGRAM_CHAT_ID,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            error_log('Failed to send Telegram notification');
        }
    } catch (Exception $e) {
        error_log('Error sending Telegram notification: ' . $e->getMessage());
    }
}

// Function to validate file type
function isValidFileType($file) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    return in_array($mimeType, array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_VIDEO_TYPES));
}

// Function to check if file is an image
function isImage($file) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    return in_array($mimeType, ALLOWED_IMAGE_TYPES);
}

// Function to generate a unique filename
function generateUniqueFilename($originalName) {
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    return uniqid() . '_' . time() . '.' . $extension;
}

// Function to get file URL
function getFileUrl($filename) {
    return rtrim(SITE_URL, '/') . '/' . UPLOAD_PATH . $filename;
}
