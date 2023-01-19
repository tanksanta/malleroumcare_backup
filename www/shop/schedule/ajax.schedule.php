<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

if(!$_POST['od_id'] && !$_POST['ct_id'])
  json_response(400, '유효하지않은 요청입니다.');

$check_sum_duplicate = duplicate_partner_install_schedule($_POST['partner_manager_mb_id'], $_POST['delivery_date'], $_POST['delivery_datetime']);
//if (!$check_sum_duplicate) json_response(400, '설치파트너 매니저 일정을 확인해주세요.'); //설치일정 중복 허용 요청 2023.01.19 정한진
$check_sum_duplicate = duplicate_partner_deny_schedule($_POST['partner_manager_mb_id'], $_POST['delivery_date']);
if (!$check_sum_duplicate) json_response(400, '해당 날짜는 담당자의 설치불가일입니다.
다른 날짜를 지정하거나 담당자를 변경해주세요.');
$check_sum = exit_partner_install_schedule($_POST['od_id']);
$code = 201;

if ($check_sum) {
    // 이미 생성 된 일정이 있는 경우

    if($_POST['partner_manager_mb_id']) {
        // 담당자 지정인 경우
        $res = update_partner_install_schedule_partner_by_ct_id($_POST['ct_id'], $_POST['partner_manager_mb_id']);
        if ($res) $code = 200;
    }

    if ($_POST['delivery_date'] && $_POST['delivery_datetime']) {
        // 출고예정일 변경인 경우
        $res = update_partner_install_schedule_delivery_date_and_delivery_datetime_by_ct_id($_POST['ct_id'], $_POST['delivery_date'], $_POST['delivery_datetime']);
        if ($res) $code = 200;
    }
} else {
    // 생성 된 일정이 없는 경우
    
    $res = create_partner_install_schedule($_POST['od_id']);
    if (!$res) json_response(400, '에러');
    
    if($_POST['partner_manager_mb_id']) {
        // 담당자 지정인 경우
        $res = update_partner_install_schedule_partner_by_ct_id($_POST['ct_id'], $_POST['partner_manager_mb_id']);
        if ($res) $code = 200;
    }

    if ($_POST['delivery_date'] && $_POST['delivery_datetime']) {
        // 출고예정일 변경인 경우
        $res = update_partner_install_schedule_delivery_date_and_delivery_datetime_by_ct_id($_POST['ct_id'], $_POST['delivery_date'], $_POST['delivery_datetime']);
        if ($res) $code = 200;
    }
}
if ($res) json_response($code, 'OK', $res);
else json_response(400, '유효하지않은 요청입니다.');
?>
