<?php
include_once('./_common.php');

$cm_name = clean_xss_tags($_POST['cm_name']);
$cm_code = clean_xss_tags($_POST['cm_code']);
$cm_sex = in_array($_POST['cm_sex'], ['1', '2']) ? $_POST['cm_sex'] : '0';
$cm_birth = clean_xss_tags($_POST['cm_birth']);
$cm_cont = in_array($_POST['cm_cont'], ['1', '2']) ? $_POST['cm_cont'] : '0';
$cm_type = in_array($_POST['cm_type'], ['1', '2']) ? $_POST['cm_type'] : '0';
$cm_paytype = in_array($_POST['cm_paytype'], ['1', '2']) ? $_POST['cm_paytype'] : '0';
$cm_pay = preg_replace('/[^\d]/', '', $_POST['cm_pay']);
$cm_joindate = clean_xss_tags($_POST['cm_joindate']);
$cm_retired = in_array($_POST['cm_retired'], ['0', '1']) ? $_POST['cm_retired'] : '0';
$cm_retiredate = clean_xss_tags($_POST['cm_retiredate']);
$cm_hp = clean_xss_tags($_POST['cm_hp']);
$cm_addr = clean_xss_tags($_POST['cm_addr']);
$cm_zip = clean_xss_tags($_POST['cm_zip']) ?: '';
$w = $_POST['w'];

function upload_cm_img($file, $cm_code) {
    global $member;

    // 직원 대표이미지 저장할 경로
    $dir = G5_DATA_PATH.'/center/member';
    if(!is_dir($dir)) {
        @mkdir($dir, G5_DIR_PERMISSION, true);
        @chmod($dir, G5_DIR_PERMISSION);
    }

    //서버 최대 용량 10Mb
    $max_file_size = 1024*1024*10;

    $ext = array_pop(explode('.', $file['name']));
    $allowed_ext = ['jpg', 'png', 'jpeg', 'gif', 'bmp'];
    if(!in_array($ext, $allowed_ext)) {
        alert('사진 파일이 허용되지 않는 확장자입니다.');
    }

    if($file['size'] >= $max_file_size) {
        alert('사진 파일은 10MB 까지만 업로드가 가능합니다.');
    }

    switch($file['error']) {
        case UPLOAD_ERR_OK:
            // do nothing
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            alert('사진 파일 사이즈가 너무 큽니다.');
            break;
        default:
            alert('사진 파일이 제대로 업로드되지 않았습니다.');
            break;
    }

    $filename = hash('sha256', $member['mb_id'] . round(microtime(true)) . $cm_code) . '.' . $ext;

    move_uploaded_file($file['tmp_name'], $dir . '/' . $filename);

    $sql = "
        UPDATE center_member SET cm_img = '$filename' WHERE mb_id = '{$member['mb_id']}' and cm_code = '$cm_code'
    ";
    $result = sql_query($sql);

    return $result;
}

if(!$cm_name)
    alert('이름을 입력해주세요.');

if(!$cm_code)
    alert('접속코드를 입력해주세요.');

if($cm_paytype == '0') {
    alert('급여를 입력해주세요.');
} else if($cm_paytype == '1') {
    $cm_pay = intval($cm_pay);
    if($cm_pay <= 0)
        alert('급여를 입력해주세요.');
}

if($w == '') {
    $sql = " select count(*) as cnt from center_member where mb_id = '{$member['mb_id']}' and cm_code = '$cm_code' ";
    $result = sql_fetch($sql);

    if($result['cnt'] > 0)
        alert('중복된 접속코드 입니다.');

    $sql = "
        INSERT INTO
            center_member
        SET
            mb_id = '{$member['mb_id']}',
            cm_code = '$cm_code',
            cm_name = '$cm_name',
            cm_sex = '$cm_sex',
            cm_birth = '$cm_birth',
            cm_cont = '$cm_cont',
            cm_type = '$cm_type',
            cm_paytype = '$cm_paytype',
            cm_pay = '$cm_pay',
            cm_joindate = '$cm_joindate',
            cm_retired = '$cm_retired',
            cm_retiredate = '$cm_retiredate',
            cm_hp = '$cm_hp',
            cm_addr = '$cm_addr',
            cm_zip = '$cm_zip',
            created_at = NOW(),
            updated_at = NOW()
    ";

    $result = sql_query($sql);
    if(!$result)
        alert('DB 오류가 발생하여 직원등록에 실패했습니다.');

    if($_FILES['cm_img']['tmp_name']) {
        $result = upload_cm_img($_FILES['cm_img'], $cm_code);
    }

} else if($w == 'u') {

    $sql = "
        SELECT * FROM center_member
        WHERE mb_id = '{$member['mb_id']}' and cm_code = '$cm_code'
    ";
    $cm = sql_fetch($sql);
    if(!$cm['cm_id'])
        alert('해당 직원이 존재하지 않습니다.');

    $sql = "
        UPDATE
            center_member
        SET
            cm_name = '$cm_name',
            cm_sex = '$cm_sex',
            cm_birth = '$cm_birth',
            cm_cont = '$cm_cont',
            cm_type = '$cm_type',
            cm_paytype = '$cm_paytype',
            cm_pay = '$cm_pay',
            cm_joindate = '$cm_joindate',
            cm_retired = '$cm_retired',
            cm_retiredate = '$cm_retiredate',
            cm_hp = '$cm_hp',
            cm_addr = '$cm_addr',
            cm_zip = '$cm_zip',
            updated_at = NOW()
        WHERE
            mb_id = '{$member['mb_id']}' and
            cm_code = '$cm_code'
    ";

    $result = sql_query($sql);
    if(!$result)
        alert('DB 오류가 발생하여 정보수정에 실패했습니다.');

    if($_FILES['cm_img']['tmp_name']) {
        $dir = G5_DATA_PATH.'/center/member';
        // 파일 삭제
        @unlink($dir . '/' . $cm['cm_img']);
        $result = upload_cm_img($_FILES['cm_img'], $cm_code);
    }
}

goto_url('center_member_view.php?cm_code=' . $cm_code);
