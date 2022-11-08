<?php
include_once('./_common.php');

if($member['mb_type'] !== 'partner')
    json_response('파트너 회원만 접근 가능합니다.');

$ct_id = $_POST['ct_id1'];

$status = get_search_string($_POST['status']);
$od_id = get_search_string($_POST['od_id']);
$manager = get_search_string($_POST['mb_id']);

$scheduled_date = get_search_string($_POST["ct_direct_delivery_date_{$ct_id}"]);;
$scheduled_time = get_search_string($_POST["ct_direct_delivery_time_{$ct_id}"]);;



$result = create_partner_install_schedule($status, $scheduled_date, $scheduled_time, $manager, $od_id);
	
if(!$result)
    json_response(400, 'DB 오류가 발생하여 일정을 저장하지 못했습니다.');

	/*
$manager_mb_id = get_session('ss_manager_mb_id');
if($manager_mb_id)
    json_response('담당자회원은 담당자를 변경할 수 없습니다.');
$status = clean_xss_tags($_POST['status']);
$od_id = clean_xss_tags($_POST['od_id']);
$manager = clean_xss_tags($_POST['manager']);
$scheduled_date = clean_xss_tags($_POST['delivery_date']);
$scheduled_time = clean_xss_tags($_POST['delivery_datetime']);

if(!$od_id)
    json_response(400, '유효하지 않은 요청입니다.');

if($manager) {
    $mb = get_member($manager);
}

$result = create_partner_install_schedule();

if(!$result)
    json_response(400, 'DB 오류가 발생하여 담당자를 지정하지 못했습니다.');

if($mb)
    set_order_admin_log($od_id, "담당자 지정 : [직원] {$mb['mb_name']}");
else
    set_order_admin_log($od_id, "담당자 해제");
*/
json_response(200, 'OK');
?>
