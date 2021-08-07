<?php
include_once('./_common.php');

if(!$member['mb_entId'])
  json_response(400, '사업소 회원만 이용할 수 있습니다.');

$prodId = $_POST['prodId'];
$stoId = $_POST['stoId'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

if(!$stoId || !$start_date || !$end_date)
  json_response(400, '유효하지않은 요청입니다.');

$start_time = strtotime($start_date);
$end_time = strtotime($end_date);
if(!$start_time || $end_time)
  json_response('선택한 날짜가 유효하지 않습니다.');
$start_date = date('Y-m-d', $start_time);
$end_date = date('Y-m-d', $end_time);

# 대여중인 재고 조회
$stock_result = api_post_call(EROUMCARE_API_STOCK_SELECT_DETAIL_LIST, array(
  'entId' => $member['mb_entId'],
  'usrId' => $member['mb_id'],
  'prodId' => $prodId,
  'stoId' => $stoId,
  'stateCd' => ['02']
));

if(!$stock_result['errorYN'] != 'N')
  json_response(500, '시스템 서버 오류 발생 : '.$stock_result['message']);

$stock = $stock_result['data'] ? $stock_result['data'][0] : null;
if(!$stock || $stock['stoId'] != $stoId)
  json_response(400, '대여기간변경이 가능한 재고가 아닙니다.');

$penOrdId = $stock['penOrdId'];

if($penOrdId) {
  # 시스템 주문 변경
  $result = api_post_call(EROUMCARE_API_ORDER_UPDATE, array(
    'usrId' => $member['mb_id'],
    'penOrdId' => $penOrdId,
    'ordLendStrDtm' => $start_date,
    'ordLendEndDtm' => $end_date
  ));

  if($result['errorYN'] != 'N')
    json_response(500, '시스템 서버 오류 발생 : '.$stock_result['message']);
}

# 대여 로그 변경


?>
