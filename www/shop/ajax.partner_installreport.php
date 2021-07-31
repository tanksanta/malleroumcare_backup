<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  json_response(400, '파트너 회원만 접근가능합니다.');

$m = $_POST['m'];

$od_id = get_search_string($_POST['od_id']);
$check_result = sql_fetch("
  SELECT od_id FROM {$g5['g5_shop_cart_table']}
  WHERE od_id = '{$od_id}' and ct_direct_delivery_partner = '{$member['mb_id']}'
  LIMIT 1
");
if(!$check_result['od_id'])
  json_response(400, '존재하지 않는 주문입니다.');

if($m == 'w') {
  // 보고서 새로 작성

  # 설치 확인서
  $file_cert = $_FILES['file_cert']['tmp_name'];
  if(!$file_cert)
    json_response(400, '설치 확인서 파일을 등록해주세요.');
}

else if($m == 'u') {
  // 기존 보고서 수정
}

json_response('200', 'OK');
?>
