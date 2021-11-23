<?php
include_once('./_common.php');

$w = $_POST['w'];
$cm_code = clean_xss_tags($_POST['cm_code']);
$cm = sql_fetch("
    SELECT * FROM center_member
    WHERE mb_id = '{$member['mb_id']}' and cm_code = '$cm_code'
");

if(!$cm['cm_id'])
    alert('해당 직원이 존재하지 않습니다.');

$cl_title = clean_xss_tags($_POST['cl_title']);
$cl_title = substr(trim($cl_title),0,255);

$cl_content = clean_xss_tags($_POST['cl_content']);
$cl_content = substr(trim($cl_content),0,65536);

if($w == 'u') {
    // 수정
    $cl_id = get_search_string($_POST['cl_id']);
    $sql = "
        UPDATE center_member_log
        SET
            cl_title = '$cl_title',
            cl_content = '$cl_content',
            updated_at = NOW()
        WHERE
            cl_id = '$cl_id' and
            mb_id = '{$member['mb_id']}' and
            cm_code = '$cm_code'
    ";
    $result = sql_query($sql);

    if(!$result)
        alert('DB 오류가 발생하여 수정에 실패했습니다.');
} else {
    $sql = "
        INSERT INTO center_member_log
        SET
            mb_id = '{$member['mb_id']}',
            cm_code = '$cm_code',
            cl_title = '$cl_title',
            cl_content = '$cl_content',
            created_at = NOW(),
            updated_at = NOW()
    ";

    $result = sql_query($sql);

    if(!$result)
        alert('DB 오류가 발생하여 작성에 실패했습니다.');
}

goto_url('center_member_view.php?cm_code=' . $cm_code);
