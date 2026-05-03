<?php
session_start();
error_reporting(0);
$permitted_chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

function generate_string($input, $strength = 10) {
    $input_length = strlen($input);
    $random_string = '';
    for($i = 0; $i < $strength; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
    return $random_string;
}

$image = imagecreatetruecolor(200, 50);
if (!$image) {
    die('Error creating image');
}

$colors = [];
$red = rand(125, 175);
$green = rand(125, 175);
$blue = rand(125, 175);
for($i = 0; $i < 5; $i++) {
  $colors[] = imagecolorallocate($image, $red - 20*$i, $green - 20*$i, $blue - 20*$i);
}

imagefill($image, 0, 0, $colors[0]);

for($i = 0; $i < 10; $i++) {
  imagesetthickness($image, rand(1, 3));
  $line_color = $colors[rand(1, 4)];
  imageline($image, rand(0, 200), rand(0, 50), rand(0, 200), rand(0, 50), $line_color);
}

$black = imagecolorallocate($image, 0, 0, 0);
$white = imagecolorallocate($image, 255, 255, 255);
$textcolors = [$black, $white];

$string_length = 6;
$captcha_string = generate_string($permitted_chars, $string_length);
$_SESSION['captcha_text'] = $captcha_string;

$font_size = 5;
$x = 10;
for($i = 0; $i < $string_length; $i++) {
  $y = rand(15, 30);
  imagestring($image, $font_size, $x, $y, $captcha_string[$i], $textcolors[rand(0, 1)]);
  $x += 30;
}

header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
?>