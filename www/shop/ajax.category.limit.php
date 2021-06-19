<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

if(!$_POST['od_id'] || !$_POST['penId'])
  json_response(400, '유효하지않은 요청입니다.');

$limit = [];
$result = sql_query("
  SELECT ca_id, ca_name, ca_limit_month, ca_limit_num
  FROM {$g5['g5_shop_category_table']}
  WHERE ca_use_limit = 1
");
while($row = sql_fetch_array($result)) {
  $limit[$row['ca_id']] = array(
    'ca_name' => $row['ca_name'],
    'month' => $row['ca_limit_month'],
    'num' => $row['ca_limit_num']
  );
}

if(!$limit)
  json_response(200, 'OK', []);

$result = sql_query("
  select count(*) as cnt, x.ca_id, y.ca_name
  from (
    select ca_id
    from {$g5['g5_shop_cart_table']} a
    left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
    where a.od_id = '{$_POST['od_id']}'
    and a.ct_select = '1'
    group by a.it_id
  ) x
  left join {$g5['g5_shop_category_table']} y on x.ca_id = y.ca_id
  group by x.ca_id
");

$res = [];
while($row = sql_fetch_array($result)) {
  $lm = $limit[$row['ca_id']];

  if($lm) {
    $cur_cnt = sql_fetch("
    SELECT COUNT(*) as cnt
    FROM `eform_document` d
    LEFT JOIN `eform_document_item` i ON d.dc_id = i.dc_id
    LEFT JOIN `{$g5['g5_shop_item_table']}` x ON i.it_code = x.ProdPayCode
    LEFT JOIN `{$g5['g5_shop_category_table']}` y ON x.ca_id = y.ca_id
    WHERE penId = '{$_POST['penId']}'
    AND (d.dc_datetime BETWEEN DATE_SUB(NOW(), INTERVAL {$lm['month']} MONTH) AND NOW())
    AND y.ca_id = '{$row['ca_id']}'
    ")['cnt'];

    if($cur_cnt + $row['cnt'] > $lm['num']) { // 구매 가능한 수량 넘으면
      $res[$row['ca_name']] = array(
        'ca_id' => $row['ca_id'],
        'limit' => $lm['num'],
        'current' => $cur_cnt,
        'cnt' => $row['cnt']
      );
    }
  }
}

json_response(200, 'OK', $res);
?>
