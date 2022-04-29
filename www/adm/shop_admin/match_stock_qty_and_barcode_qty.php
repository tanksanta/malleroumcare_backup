<?php
include_once('./_common.php');
exit;

if ($is_admin == 'super') {
  $use_warehouse_where_sql = get_use_warehouse_where_sql();
  $sql = "
    SELECT
     T.*
    FROM
    (SELECT
      (SELECT 
        IFNULL(sum(ws_qty) - sum(ws_scheduled_qty), 0) 
      FROM warehouse_stock 
      WHERE it_id = a.it_id AND io_id = IFNULL(b.io_id, '') AND ws_del_yn = 'N' {$use_warehouse_where_sql}) AS sum_ws_qty,
      (SELECT count(*)
        FROM g5_cart_barcode
        WHERE it_id = a.it_id AND io_id = IFNULL(b.io_id, '') AND bc_del_yn = 'N' AND ct_id = '0' AND checked_at IS NOT NULL) AS sum_checked_barcode_qty,
      a.*,
      b.io_type,
      b.io_id
    FROM
      (SELECT
        it_id,
        it_name,
        it_use,
        it_option_subject,
        ProdPayCode
      FROM g5_shop_item i) AS a
    LEFT JOIN (SELECT * from g5_shop_item_option WHERE io_type = '0' AND io_use = '1') AS b ON (a.it_id = b.it_id)) AS T
  ";

  $result = sql_query($sql);

  while ($row = sql_fetch_array($result)) {
    $io_value = '';
    $it_option_subjects = explode(',', $row['it_option_subject']);
    if ($row['io_id']) {
      $io_ids = explode(chr(30), $row['io_id']);
      for($g = 0; $g < count($io_ids); $g++) {
        if ($g > 0) {
          $io_value .= ' / ';
        }
        $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
      }
    } else {
      $io_value = $row['it_name'];
    }

    if ($row['sum_ws_qty'] < $row['sum_checked_barcode_qty']) {
      $add_qty = $row['sum_checked_barcode_qty'] - $row['sum_ws_qty'];
      $sql = "
        INSERT INTO warehouse_stock
        SET 
          it_id = '{$row['it_id']}',
          io_id = '{$row['io_id']}',
          io_type = '0',
          it_name = '{$row['it_name']}',
          ws_option = '{$io_value}',
          ws_qty = '{$add_qty}',
          ws_scheduled_qty = '0',
          mb_id = '{$member['mb_id']}',
          ws_memo = '보유재고 바코드 수량 매칭',
          wh_name = '검단창고',
          od_id = '0',
          ct_id = '0',
          inserted_from = 'match_qty',
          ws_created_at = NOW(),
          ws_updated_at = NOW()
      ";
      sql_query($sql);
    }
  }
  echo '완료';

} else {
  echo '권한없음';
}