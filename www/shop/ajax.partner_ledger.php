<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  json_response(400, '파트너 회원만 접근가능합니다.');

$mb_id = $member['mb_id'];
$pl_type = intval($_POST['pl_type']);
$pl_amount = intval(str_replace(',', '', $_POST['pl_amount']));
$pl_memo = sql_real_escape_string($_POST['pl_memo']);

// 값 검증
if(!$mb_id)
  json_response(400, '유효하지않은 요청입니다.');

if(!in_array($pl_type, [1, 2])) // 1: 입금, 2: 출금
  json_response(400, '입금 또는 환수 여부를 선택해주세요.');

if($pl_amount <= 0)
  json_response(400, '금액을 입력해주세요.');

$result = sql_query("
  INSERT INTO
    partner_ledger
  SET
    mb_id = '{$mb_id}',
    pl_type = '{$pl_type}',
    pl_amount = '{$pl_amount}',
    pl_memo = '{$pl_memo}',
    pl_created_at = NOW(),
    pl_created_by = '{$mb_id}'
");

if(!$result)
  json_response(500, '서버 오류로 실패했습니다.');

json_response(200, 'OK');
?>
