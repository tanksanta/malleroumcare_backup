<?php include("orderBy.php"); ?>
<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

if(!$_POST['partner_mb_id'])
  json_response(400, '유효하지않은 요청입니다.');

validate_schedule($_POST['partner_mb_id'], $member);
if ($member['mb_type'] === 'default' && $member['mb_level'] < 9) {
  $res = get_partner_schedule_by_mb_id($_POST['partner_mb_id']);
} else {
  $res = get_partner_schedule_by_partner_mb_id($_POST['partner_mb_id'], $member);
}

if (count($res) > 0)
  $res = order_by($res, ['type', 'delivery_datetime'], ['asc', 'asc'], 'delivery_date');
else
  $res =(object) array();

json_response(200, 'OK', $res);
?>