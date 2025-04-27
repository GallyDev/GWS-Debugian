<?php
session_start();
header('Content-Type: image/png');

$gws_captcha_colors = [
	'text' => [0, 0, 0],
	'background' => [255, 255, 255],
];

$scale = 2;

$width = 200 * $scale;
$height = 35 * $scale;

if(file_exists(__DIR__.'/../settings.php')){
	include_once(__DIR__.'/../settings.php');
}

$image = imagecreatetruecolor($width, $height);

// Colors (R, G, B)
$textColor = imagecolorallocate($image, $gws_captcha_colors['text'][0], $gws_captcha_colors['text'][1], $gws_captcha_colors['text'][2]);
$backgroundColor = imagecolorallocate($image, $gws_captcha_colors['background'][0], $gws_captcha_colors['background'][1], $gws_captcha_colors['background'][2]);

imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColor);

$fontPath = __DIR__ . '/font.ttf';
if(!file_exists($fontPath)) {
	$fontPath = __DIR__ . '/default.ttf';
}
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