<?php
include_once('./_common.php');

if(!$member['mb_id'])
    json_response(400, '먼저 로그인 하세요.');

if(!$_FILES['sealFile']['tmp_name'])
    json_response(400, '직인 이미지 파일을 선택해주세요.');

//서버 최대 용량 10Mb
$max_file_size = 1024*1024*3;

// 변수 정리
$uploads_dir = G5_DATA_PATH.'/file/member/stamp';
$error = $_FILES['sealFile']['error'];
$name = $_FILES['sealFile']['name'];
$disallowed_ext = ['exe'];
$ext = array_pop(explode('.', $name));
$temp = explode(".", $_FILES["sealFile"]["name"]);
$sealFile_name = $member['mb_id'].'_'.round(microtime(true)) . '.' . end($temp);
$sealFile = "$uploads_dir/$sealFile_name";
// 오류 확인
if( $error != UPLOAD_ERR_OK ) {
    switch( $error ) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            json_response(500, '파일이 너무 큽니다.');
            break;
        exit;
        default:
            json_response(500, '파일이 제대로 업로드되지 않았습니다.');
            exit;
    }
    exit;
}
if($_FILES['sealFile']['size'] >= $max_file_size)
    json_response(500, '3Mb 까지만 업로드 가능합니다.');
// 확장자 확인
if( in_array($ext, $disallowed_ext) )
    json_response(500, '허용되지 않는 확장자입니다');

$sql = " UPDATE g5_member SET sealFile = '$sealFile_name' WHERE mb_id = '{$member['mb_id']}' ";
sql_query($sql, true);

move_uploaded_file( $_FILES['sealFile']['tmp_name'], $sealFile);
$response["sealFile"] = $sealFile_name;
//json_response(200, 'OK');
echo json_encode($response);
