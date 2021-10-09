<?php
include_once('./_common.php');

if (!$mb_id) {
  json_response(400, '회원 아이디를 입력하세요.');
}

$rows = array();

// 기본적으로 회원정보에 있는 거 추가
$mb = get_member($mb_id);
$rows[] = array(
  'ad_subject' => '회원정보',
  'ad_name' => $mb['mb_name'],
  'ad_tel' => $mb['mb_tel'],
  'ad_hp' => $mb['mb_hp'],
  'ad_zip1' => $mb['mb_zip1'],
  'ad_zip2' => $mb['mb_zip2'],
  'ad_addr1' => $mb['mb_addr1'],
  'ad_addr2' => $mb['mb_addr2'],
  'ad_addr3' => $mb['mb_addr3'],
  'ad_addr_jibeon' => $mb['mb_addr_jibeon'],
);

$sql = " select *
  from {$g5['g5_shop_order_address_table']} where mb_id = '{$mb_id}'
  order by ad_default desc, ad_id desc
";
$result = sql_query($sql);

while ( $row = sql_fetch_array($result) ) {
    $rows[] = $row;
}


json_response(200, 'OK', $rows);