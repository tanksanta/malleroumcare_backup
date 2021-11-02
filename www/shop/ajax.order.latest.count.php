<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

$sql = "
  SELECT
    count(*) as cnt,
    ct_status
  FROM
    g5_shop_cart c
  LEFT JOIN
    g5_shop_order o ON c.od_id = o.od_id
  WHERE
    c.mb_id = '{$member['mb_id']}' and
    o.od_del_yn = 'N' and
    o.od_time >= DATE(NOW() - INTERVAL 3 MONTH)
  GROUP BY
    ct_status
";

$count_result = sql_query($sql);

$data = array(
  '준비' => 0,
  '출고준비' => 0,
  '배송' => 0,
  '완료' => 0
);
while($row = sql_fetch_array($count_result)) {
  $data[$row['ct_status']] = $row['cnt'];
}

json_response(200, 'OK', $data);
?>
