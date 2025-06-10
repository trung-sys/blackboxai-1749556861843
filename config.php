<?php
// config.php

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('BASE_URL', 'http://localhost:8000'); // For development, will need to change in production

// Maximum file size in bytes (10MB = 10 * 1024 * 1024)
define('MAX_FILE_SIZE', 10485760); 

// Allowed file extensions
$ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg'];
