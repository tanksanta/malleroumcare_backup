<?php
include_once('./common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

if(!$POST['od_id'] || !$POST['penId'])
  json_response(400, '유효하지않은 요청입니다.');

$limit = [];
$result = sql_query("
  SELECT i.ProdPayCode, c.ca_limit_month, c.ca_limit_num
  FROM {$g5['g5_shop_category_table']} c
  LEFT JOIN {$g5['g5_shop_item_table']} i ON c.ca_id = i.ca_id
  WHERE c.ca_use_limit = 1
");
while($row = sql_fetch_array($result)) {
  $limit[$row['ProdPayCode']] = array(
    'month' => $row['ca_limit_month'],
    'num' => $row['ca_limit_num']
  );
}

if(!$limit)
  json_response(200, 'OK', []);


$result = sql_query("
  select count(*) as cnt, b.it_name, b.ProdPayCode
  from {$g5['g5_shop_cart_table']} a
  left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
  where a.od_id = '{$POST['od_id']}'
  and a.ct_select = '1'
  group by a.it_id
");
$result = sql_query($sql);

$res = [];
while($row = sql_fetch_array($result)) {
  $lm = $limit[$row['ProdPayCode']];
  if($lm) {
    $cur_cnt = sql_fetch("
      SELECT COUNT(*) as cnt
      FROM `eform_document` d
      LEFT JOIN `eform_document_item` i ON d.dc_id = i.dc_id
      WHERE penId = '{$POST['penId']}'
      AND (d.dc_datetime BETWEEN DATE_SUB(NOW(), INTERVAL {$limit['month']} MONTH) AND NOW())
      AND i.it_code = {$row['ProdPayCode']}
    ")['cnt'];

    if($cur_cnt + $row['cnt'] > $lm['num']) { // 구매 가능한 수량 넘으면
      $res[$row['it_name']] = array(
        'limit' => $lm['num'],
        'current' => $cur_cnt,
        'cnt' => $row['cnt']
      );
    }
  }
}

json_response(200, 'OK', $res);
?>
