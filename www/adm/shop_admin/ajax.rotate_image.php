<?php
$sub_menu = '400400';
include_once('./_common.php');

$auth_check = auth_check($auth[$sub_menu], 'w', true);
if($auth_check)
    json_response(400, $auth_check);

$path = clean_xss_tags($_POST['path']);

$source_file = G5_DATA_PATH . '/' . $path;
if(!is_file($source_file))
    json_response(400, '이미지를 찾을 수 없습니다.');

$size = @getimagesize($source_file);
if($size[2] < 1 || $size[2] > 3) // gif, jpg, png 에 대해서만 적용
    return;

if ($size[2] == 1) {
    $src = @imagecreatefromgif($source_file);
    $src_transparency = @imagecolortransparent($src);
} else if ($size[2] == 2) {
    $src = @imagecreatefromjpeg($source_file);
} else if ($size[2] == 3) {
    $src = @imagecreatefrompng($source_file);
    @imagealphablending($src, true);
}

$src = @imagerotate($src, 90, 0);

imagejpeg($src, $source_file, 100);

json_response(200, 'OK');
