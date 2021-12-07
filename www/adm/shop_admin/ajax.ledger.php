<?php
$sub_menu = '400460';
include_once('./_common.php');

$auth_check = auth_check($auth[$sub_menu], 'w', true);
if($auth_check)
  json_response(400, $auth_check);

$mb_id = sql_real_escape_string($_POST['mb_id']);
$lc_type = intval($_POST['lc_type']);
$lc_amount = intval(str_replace(',', '', $_POST['lc_amount']));
$lc_memo = sql_real_escape_string($_POST['lc_memo']);
$lc_base_date = sql_real_escape_string($_POST['lc_base_date']);

// 값 검증
if(!$mb_id)
  json_response(400, '유효하지않은 요청입니다.');

$ent = get_member($mb_id);
if(!$ent['mb_id'])
  json_response(400, '존재하지않는 사업소입니다.');

$type = $ent['mb_type'];

if(!in_array($lc_type, [1, 2])) // 1: 입금, 2: 출금
  json_response(400, '입금 또는 출금 여부를 선택해주세요.');

if($lc_amount <= 0)
  json_response(400, '금액을 입력해주세요.');

if($type == 'default') {
  $result = sql_query("
    INSERT INTO
      ledger_content
    SET
      mb_id = '{$mb_id}',
      lc_type = '{$lc_type}',
      lc_amount = '{$lc_amount}',
      lc_memo = '{$lc_memo}',
      lc_created_at = NOW(),
      lc_created_by = '{$member['mb_id']}',
      lc_base_date = '{$lc_base_date}'
  ");
} else if($type == 'partner') {
  $result = sql_query("
    INSERT INTO
      partner_ledger
    SET
      mb_id = '{$mb_id}',
      pl_type = '{$lc_type}',
      pl_amount = '{$lc_amount}',
      pl_memo = '{$lc_memo}',
      pl_created_at = NOW(),
      pl_created_by = '{$member['mb_id']}',
      pl_base_date = '{$lc_base_date}'
  ");
}

if(!$result)
  json_response(500, '서버 오류로 실패했습니다.');

json_response(200, 'OK');
?>
