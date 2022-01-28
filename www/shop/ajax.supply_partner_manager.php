<?php
include_once('./_common.php');

if($member['mb_type'] !== 'partner')
    json_response('파트너 회원만 접근 가능합니다.');

$manager_mb_id = get_session('ss_manager_mb_id');
if($manager_mb_id)
    json_response('담당자회원은 담당자를 변경할 수 없습니다.');

$od_id = clean_xss_tags($_POST['od_id']);
$manager = clean_xss_tags($_POST['manager']);

if(!$od_id)
    json_response(400, '유효하지 않은 요청입니다.');

if($manager) {
    $mb = get_member($manager);
}

$sql = "
    UPDATE
        purchase_order o
    LEFT JOIN
        purchase_cart c ON c.od_id = o.od_id
    SET
        o.od_partner_manager = '$manager'
    WHERE
        o.od_id = '$od_id' and
        ct_supply_partner = '{$member['mb_id']}'
";

$result = sql_query($sql);
if(!$result)
    json_response(400, 'DB 오류가 발생하여 담당자를 지정하지 못했습니다.');

if($mb)
    set_purchase_order_admin_log($od_id, "발주 담당자 지정 : [직원] {$mb['mb_name']}");
else
    set_purchase_order_admin_log($od_id, "발주 담당자 해제");

json_response(200, 'OK');
?>