<?php
include_once('./_common.php');

$w = $_POST['w'];
$cm_code = clean_xss_tags($_POST['cm_code']);
$year = preg_replace('/[^0-9]/', '', $_POST['year']);
$month = preg_replace('/[^0-9]/', '', $_POST['month']);

$cm = sql_fetch("
    SELECT * FROM center_member
    WHERE mb_id = '{$member['mb_id']}' and cm_code = '$cm_code'
");

if(!$cm['cm_id'])
    alert('해당 직원이 존재하지 않습니다.');

if(!$year || $month < 1 || $month > 12)
    alert('유효하지 않은 요청입니다.');

$cp_total = intval(preg_replace('/[^0-9]/', '', $_POST['cp_total']));
$cp_deduction = intval(preg_replace('/[^0-9]/', '', $_POST['cp_deduction']));
$cp_tax = intval(preg_replace('/[^0-9]/', '', $_POST['cp_tax']));
$cp_insurance = intval(preg_replace('/[^0-9]/', '', $_POST['cp_insurance']));
$cp_pay = intval(preg_replace('/[^0-9]/', '', $_POST['cp_pay']));

if($w == 'u') {
    // 수정
    $sql = "
        UPDATE
            center_member_pay
        SET
            cp_total = '$cp_total',
            cp_deduction = '$cp_deduction',
            cp_tax = '$cp_tax',
            cp_insurance = '$cp_insurance',
            cp_pay = '$cp_pay',
            updated_at = NOW()
        WHERE
            mb_id = '{$member['mb_id']}' and
            cm_code = '$cm_code' and
            cp_year = '$year' and
            cp_month = '$month'
    ";

    $result = sql_query($sql);
    if(!$result)
        alert('DB 오류가 발생하여 완료하지 못했습니다.');

} else {
    // 작성
    $sql = "
        INSERT INTO
            center_member_pay
        SET
            mb_id = '{$member['mb_id']}',
            cm_code = '$cm_code',
            cp_year = '$year',
            cp_month = '$month',
            cp_total = '$cp_total',
            cp_deduction = '$cp_deduction',
            cp_tax = '$cp_tax',
            cp_insurance = '$cp_insurance',
            cp_pay = '$cp_pay',
            updated_at = NOW()
    ";

    $result = sql_query($sql);
    if(!$result)
        alert('DB 오류가 발생하여 완료하지 못했습니다.');
}

goto_url('center_member_view.php?cm_code=' . $cm_code);
