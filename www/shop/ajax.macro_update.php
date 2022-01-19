<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

include_once("./_common.php");

$ct_id = $_POST['ct_id'];
$birth = $_POST['birth'];
$grade = $_POST['grade'];
$type = $_POST['type'];
$percent = $_POST['percent'];

# 이미지 파일 경로
$img_dir = G5_DATA_PATH.'/person/img';
if(!is_dir($img_dir)) {
    @mkdir($img_dir, G5_DIR_PERMISSION, true);
    @chmod($img_dir, G5_DIR_PERMISSION);
}

function img_file_name() {
    global $ct_id;

    $file_name = [];
    $file_name[] = $ct_id;
    $file_name[] = round(microtime(true) * 1000);
    $file_name[] = bin2hex(random_bytes(5));

    return implode('_', $file_name);
}

function re_array_files($arr) {
    foreach( $arr as $key => $all ){
        foreach( $all as $i => $val ){
            $new[$i][$key] = $val;   
        }   
    }
    return $new;
}

$result = sql_query("
    UPDATE macro_request
    SET status = 'D', birth = '{$birth}', grade = '{$grade}', type = '{$type}', percent = '{$percent}', updated_at = NOW()
    WHERE id = '{$ct_id}'
");

if(!$result)
    json_response(500, 'DB 서버 오류 발생');

$photos = $_FILES['file_photo'] ? re_array_files($_FILES['file_photo']) : [];
foreach($photos as $photo) {
    if(!$photo['name']) 
        continue;

    $src_name = get_search_string($photo['name']);
    $dest_name = img_file_name();
    if(!$src_name) 
        $src_name = $dest_name;
        
    upload_file($photo['tmp_name'], $dest_name, $img_dir);

    $result = sql_query("
        INSERT INTO
            macro_request_image
        SET
            id = '{$ct_id}',
            image_name = '{$src_name}',
            image_url = '{$dest_name}',
            regdt = NOW()
    ");
    if(!$result) {
        @unlink($img_dir.'/'.$dest_name);
        json_response(500, 'DB 서버 오류 발생');
    }
}       

json_response(200, 'OK');
?>
