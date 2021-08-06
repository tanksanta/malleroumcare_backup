<?php
include_once('./_common.php');

// 판매보유재고 -> 판매완료 처리

if(!$member['mb_entId'])
  json_response(400, '사업소 회원만 이용 가능합니다.');

$prod_id = $_POST['prodId'];
$sto_id = $_POST['stoId'];
$prod_bar_num = $_POST['prodBarNum'];

if(!$prod_id || !$sto_id)
  json_response(400, '유효하지 않은 요청입니다.');

// 재고 조회
$api_result = api_post_call(EROUMCARE_API_STOCK_SELECT_DETAIL_LIST, array(
  'entId' => $member['mb_entId'],
  'usrId' => $member['mb_id'],
  'prodId' => $prod_id,
  'stoId' => $sto_id,
  'stateCd' => ['01']
));

if($api_result['errorYN'] != 'N')
  json_response(500, $api_result['message']);

if(!in_array($sto_id, array_column($api_result['data'], 'stoId')))
  json_response(400, '판매완료처리가 가능한 재고가 아닙니다.');

$prods = array(
  'stoId' => $sto_id,
  'prodBarNum' => $prod_bar_num,
  'prodId' => $prod_id,
  'stateCd' => '02'
);

$api_result = api_post_call(EROUMCARE_API_STOCK_UPDATE, array(
  'entId' => $member['mb_entId'],
  'usrId' => $member['mb_id'],
  'prods' => [$prods]
));

if($api_result['errorYN'] != 'N')
  json_response(500, $api_result['message']);

json_response(200, 'OK');
?>
