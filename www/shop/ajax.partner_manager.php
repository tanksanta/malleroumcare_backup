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

$sql = "
    UPDATE
        g5_shop_order o
    LEFT JOIN
        g5_shop_cart c ON c.od_id = o.od_id
    SET
        o.od_partner_manager = '$manager'
    WHERE
        o.od_id = '$od_id' and
        ct_is_direct_delivery IN(1, 2) and
        ct_direct_delivery_partner = '{$member['mb_id']}'
";

$result = sql_query($sql);
if(!$result)
    json_response(400, 'DB 오류가 발생하여 담당자를 지정하지 못했습니다.');

json_response(200, 'OK');
?>
