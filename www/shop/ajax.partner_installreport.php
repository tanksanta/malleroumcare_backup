<?php
include_once('./_common.php');

if(!$is_samhwa_partner && !$is_admin)
  json_response(400, '파트너 회원만 접근가능합니다.');

if (!$is_admin) {
  $check_member = "and mb_id = '{$member['mb_id']}'";
}

$ct_id = get_search_string($_POST['ct_id']);
if(!$ct_id)
  json_response(400, '유효하지 않은 요청입니다.');

$ct = sql_fetch(" SELECT * FROM g5_shop_cart WHERE ct_id = '$ct_id' ");

$report = sql_fetch("
  SELECT * FROM partner_install_report
  WHERE ct_id = '{$ct_id}' {$check_member}
");
if(!$report || !$report['ct_id'])
  json_response(400, '설치보고서가 존재하지 않습니다.');

// if(!$report['ir_cert_url'])
//   json_response(400, '설치 확인서 파일을 등록해주세요.');

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
  WHERE ct_id = {$ct_id} {$check_member}
");
if(!$result)
  json_response(500, 'DB 서버 오류 발생');

set_order_admin_log($ct['od_id'], "설치결과보고서 작성 : {$ct['it_name']}({$ct['ct_option']})");

json_response(200, 'OK');
?>
