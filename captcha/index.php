<?php
session_start();
header('Content-Type: image/png');

$scale = 2;

$width = 200 * $scale;
$height = 35 * $scale;
$image = imagecreatetruecolor($width, $height);

// Colors (R, G, B)
$backgroundColor = imagecolorallocate($image, 255,195,0); 
$textColor = imagecolorallocate($image, 0, 0, 0); 

imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColor);

$fontPath = __DIR__ . '/font.ttf';
$text = $_SESSION['gws_captcha'];
$fontSize = 20 * $scale;;

$bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
$textWidth = $bbox[2] - $bbox[0];

$x = $width - $textWidth - 5 * $scale; // Right-align the text
$x = 0;
$y = 25 * $scale;
imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $text);

imagepng($image); // Output the image

imagedestroy($image);
session_write_close();
?>