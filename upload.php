<?php
require_once 'config.php';

// Set header type to JSON
header('Content-Type: application/json');

// Check if files were uploaded
if (empty($_FILES['files'])) {
    echo json_encode(['error' => 'Không có file nào được tải lên.']);
    exit;
}

$uploadResults = [];

// Loop through each uploaded file
foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
    $error = $_FILES['files']['error'][$key];
    
    // Check for upload errors
    if ($error !== UPLOAD_ERR_OK) {
        $errorMessage = match($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File quá lớn.',
            UPLOAD_ERR_PARTIAL => 'File chỉ được tải lên một phần.',
            UPLOAD_ERR_NO_FILE => 'Không có file nào được tải lên.',
            default => 'Có lỗi xảy ra khi tải file lên.'
        };
        echo json_encode(['error' => $errorMessage]);
        exit;
    }
    
    // Check file size
    $fileSize = $_FILES['files']['size'][$key];
    if ($fileSize > MAX_FILE_SIZE) {
        echo json_encode(['error' => 'Kích thước file vượt quá giới hạn 10MB.']);
        exit;
    }
    
    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpName);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
        echo json_encode(['error' => 'Định dạng file không được hỗ trợ.']);
        exit;
    }
    
    // Get file extension from mime type
    $extension = match($mimeType) {
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
    
    // Move uploaded file
    if (!move_uploaded_file($tmpName, $destination)) {
        echo json_encode(['error' => 'Không thể lưu file được tải lên.']);
        exit;
    }
    
    // Generate URLs for the uploaded file
    $fileUrl = BASE_URL . '/uploads/' . $uniqueName;
    
    $uploadResults[] = [
        'viewLink' => $fileUrl,
        'directLink' => $fileUrl,
        'htmlCode' => '<img src="' . $fileUrl . '" alt="Uploaded Image">',
        'markdownCode' => '![](' . $fileUrl . ')'
    ];
}

// Return the first result (or you could modify the frontend to handle multiple results)
echo json_encode($uploadResults[0]);
exit;
