<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  json_response(400, '파트너 회원만 접근가능합니다.');

$od_id = get_search_string($_POST['od_id']);
if(!$od_id)
  json_response(400, '유효하지 않은 요청입니다.');

$report = sql_fetch("
  SELECT * FROM partner_install_report
  WHERE od_id = '{$od_id}' and mb_id = '{$member['mb_id']}'
");
if(!$report || !$report['od_id'])
  json_response(400, '설치보고서가 존재하지 않습니다.');

if(!$report['ir_cert_url'])
  json_response(400, '설치 확인서 파일을 등록해주세요.');

$ir_issue = sql_real_escape_string($_POST['ir_issue']);

$result = sql_query("
  UPDATE partner_install_report
  SET ir_issue = '{$ir_issue}'
  WHERE od_id = {$od_id} and mb_id = '{$member['mb_id']}'
");
if(!$result)
  json_response(500, 'DB 서버 오류 발생');

json_response(200, 'OK');
?>
