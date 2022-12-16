<?php
include_once('./_common.php');

if(!$is_samhwa_partner && !$is_admin)
  json_response(400, '파트너 회원만 접근가능합니다.');

if (!$is_admin) {
  $check_member = "and mb_id = '{$member['mb_id']}'";
}

$od_id = get_search_string($_POST['od_id']);
if(!$od_id)
  json_response(400, '유효하지 않은 요청입니다.');

$od = sql_fetch(" SELECT * FROM g5_shop_order WHERE od_id = '$od_id' ");

$report = sql_fetch("
  SELECT * FROM partner_install_report
  WHERE od_id = '{$od_id}' {$check_member}
");
if(!$report || !$report['od_id'])
  json_response(400, '설치보고서가 존재하지 않습니다.');

$ir_issue = sql_real_escape_string($_POST['ir_issue']);
$ir_is_issue_1 = (int)$ir_is_issue_1;
$ir_is_issue_2 = (int)$ir_is_issue_2;
$ir_is_issue_3 = (int)$ir_is_issue_3;

$result = sql_query("
  UPDATE partner_install_report
  SET
    ir_issue = '{$ir_issue}',
    ir_is_issue_1 = '{$ir_is_issue_1}',
    ir_is_issue_2 = '{$ir_is_issue_2}',
    ir_is_issue_3 = '{$ir_is_issue_3}'
  WHERE od_id = {$od_id} {$check_member}
");
if(!$result)
  json_response(500, 'DB 서버 오류 발생');

set_order_admin_log($od_id, "설치결과보고서 작성");

json_response(200, 'OK');
?>