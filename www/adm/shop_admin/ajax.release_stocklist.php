<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "r");
$where = array();
$where_sql = '';

$sel_field = get_search_string($sel_field);
$sel_field_arr = array('it_name', 'io_id', 'ProdPayCode');
if (!in_array($sel_field, $sel_field_arr) && $sel_field != 'all') { // 검색할 필드 대상이 아니면 값을 제거
  $sel_field = '';
}
$search_text = get_search_string($search_text);

if ($sel_field && $search_text) {

  if ($sel_field == 'all') {
    foreach ($sel_field_arr as $key => $value) {
      $where[] = " {$value} like '%{$search_text}%' ";
    }
    $where_sql .= ' AND (' . implode(' OR ', $where ) . ') ';

  } else {
    $where_sql .= " AND {$sel_field} like '%{$search_text}%' ";
  }
}

if ($only_diff_qty == 'true') {
  $where_sql .= " AND sum_ws_qty != sum_checked_barcode_qty ";
}


$sql = "
  SELECT
    count(*) AS cnt
  FROM
    (SELECT
        it_id,
        it_name,
        it_use
      FROM g5_shop_item i) AS a
  LEFT JOIN (SELECT * FROM g5_shop_item_option WHERE io_type = '0' AND io_use = '1') AS b ON (a.it_id = b.it_id)
";

$total_count = sql_fetch($sql)['cnt'];
//$rows = $config['cf_page_rows'];
$rows = 50;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

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
  WHERE 1 = 1 {$where_sql}
  LIMIT {$from_record}, {$rows}
";

$result = sql_query($sql);

while ($row = sql_fetch_array($result)) {
  $option = '';
  $option_br = '';
  if ($row['io_type']) {
    $opt = explode(chr(30), $row['io_id']);
    if ($opt[0] && $opt[1])
      $option .= $opt[0] . ' : ' . $opt[1];
  } else {
    $subj = explode(',', $row['it_option_subject']);
    $opt = explode(chr(30), $row['io_id']);
    for ($k = 0; $k < count($subj); $k++) {
      if ($subj[$k] && $opt[$k]) {
        $option .= $option_br . $subj[$k] . ' : ' . $opt[$k];
        $option_br = ' / ';
      }
    }
  }

  $warning_icon = '';

  if ($row['sum_ws_qty'] != $row['sum_checked_barcode_qty']) {
    $warning_icon = '<span class="warning_icon">!</span>';
  }
?>
  <li class="flex-row align-center" data-it_id="<?php echo $row['it_id'] ?>" data-io_id="<?php echo $row['io_id'] ?>">
    <div class="name"><?php echo $row['it_name'] ?> <?php echo $option ? "({$option})" : '' ?> <?php echo $warning_icon ?></div>
    <div class="stockQty"><?php echo $row['sum_ws_qty'] ?></div>
    <div class="barcodeQty"><?php echo $row['sum_checked_barcode_qty'] ?></div>
  </li>
<?php
}


