<?php
require_once 'config.php';

// Set header type to JSON
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['imageUrl']) || empty($input['imageUrl'])) {
    echo json_encode(['error' => 'URL ảnh không được để trống.']);
    exit;
}

$imageUrl = filter_var($input['imageUrl'], FILTER_VALIDATE_URL);
if (!$imageUrl) {
    echo json_encode(['error' => 'URL ảnh không hợp lệ.']);
    exit;
}

// Initialize curl
$ch = curl_init($imageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

// Get headers only
$response = curl_exec($ch);
$contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

// Check file size
if ($contentLength > MAX_FILE_SIZE) {
    echo json_encode(['error' => 'Kích thước file vượt quá giới hạn 10MB.']);
    curl_close($ch);
    exit;
}

// Validate content type
if (!in_array($contentType, ['image/jpeg', 'image/png'])) {
    echo json_encode(['error' => 'Định dạng file không được hỗ trợ.']);
    curl_close($ch);
    exit;
}

// Reset curl to get the actual image
curl_setopt($ch, CURLOPT_NOBODY, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$imageData = curl_exec($ch);

if ($imageData === false) {
    echo json_encode(['error' => 'Không thể tải ảnh từ URL.']);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Get file extension from content type
$extension = match($contentType) {
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    default => ''
};

// Generate unique filename
$uniqueName = uniqid() . '_' . time() . '.' . $extension;
$destination = UPLOAD_DIR . $uniqueName;

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        echo json_encode(['error' => 'Không thể tạo thư mục lưu trữ.']);
        exit;
    }
}

// Save the image
if (file_put_contents($destination, $imageData) === false) {
    echo json_encode(['error' => 'Không thể lưu file được tải lên.']);
    exit;
}

// Generate URLs for the uploaded file
$fileUrl = BASE_URL . '/uploads/' . $uniqueName;

$result = [
    'viewLink' => $fileUrl,
    'directLink' => $fileUrl,
    'htmlCode' => '<img src="' . $fileUrl . '" alt="Uploaded Image">',
    'markdownCode' => '![](' . $fileUrl . ')'
];

echo json_encode($result);
exit;
