<?php
include_once('./_common.php');

if(!$member['mb_entId'])
  json_response(400, '사업소 회원만 이용할 수 있습니다.');

$stoId = $_POST['stoId'];
$prodBarNum = $_POST['prodBarNum'];

if(!$stoId || !$prodBarNum)
  json_response(400, '바코드 정보가 유효하지 않습니다.');

$prodColor = $_POST['prodColor'];
$prodSize = $_POST['prodSize'];
$prodOption = $_POST['prodOption'];

$result = api_post_call(EROUMCARE_API_STOCK_UPDATE, array(
  'entId' => $member['mb_entId'],
  'usrId' => $member['mb_id'],
  'prods' => [
    array(
      'stoId' => $stoId,
      'prodBarNum' => $prodBarNum,
      'prodColor' => $prodColor,
      'prodSize' => $prodSize,
      'prodOption' => $prodOption
    )
  ]
));

if($result['errorYN'] != 'N')
  json_response(500, $result['message']);

json_response(200, 'OK');
?>
