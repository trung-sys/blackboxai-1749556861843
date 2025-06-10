<?php
// Create a 32x32 image
$im = imagecreatetruecolor(32, 32);

// Set background color (purple - #7c3aed)
$purple = imagecolorallocate($im, 124, 58, 237);
imagefill($im, 0, 0, $purple);

// Set the content type header to image/x-icon
header('Content-Type: image/x-icon');

// Create ICO data
imagepng($im);

// Free up memory
imagedestroy($im);
