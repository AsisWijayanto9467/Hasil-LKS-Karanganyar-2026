<?php
session_start();

header("Content-Type: image/png");

$width = 150;
$height = 50;

$image = imagecreatetruecolor($width, $height);

$bg_color = imagecolorallocate($image, rand(200,255), rand(200,255), rand(200,255));
imagefill($image, 0, 0, $bg_color);

/* ===================== */
/* Generate 4 characters */
/* ===================== */

$characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
$captcha_code = "";

for ($i = 0; $i < 4; $i++) {
    $captcha_code .= $characters[rand(0, strlen($characters) - 1)];
}

$_SESSION["captcha_code"] = $captcha_code;

/* ===================== */
/* Draw random lines */
/* ===================== */

for ($i = 0; $i < 3; $i++) {
    $line_color = imagecolorallocate($image, rand(0,150), rand(0,150), rand(0,150));
    imageline($image, rand(0,$width), rand(0,$height), rand(0,$width), rand(0,$height), $line_color);
}

/* ===================== */
/* Draw noise dots */
/* ===================== */

for ($i = 0; $i < 50; $i++) {
    $dot_color = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
    imagesetpixel($image, rand(0,$width), rand(0,$height), $dot_color);
}

/* ===================== */
/* Draw rotated text */
/* ===================== */

$font_size = 20;
$font_file = __DIR__ . "/arial.ttf"; // OPTIONAL if you have TTF font

for ($i = 0; $i < 4; $i++) {

    $text_color = imagecolorallocate($image, rand(0,100), rand(0,100), rand(0,100));

    $angle = rand(-25, 25); // Slight rotation

    $x = 15 + ($i * 30);
    $y = rand(30, 45); // Not same row

    if (file_exists($font_file)) {
        imagettftext($image, $font_size, $angle, $x, $y, $text_color, $font_file, $captcha_code[$i]);
    } else {
        imagestring($image, 5, $x, $y - 20, $captcha_code[$i], $text_color);
    }
}

/* ===================== */
/* Output image */
/* ===================== */

imagepng($image);
imagedestroy($image);
?>
