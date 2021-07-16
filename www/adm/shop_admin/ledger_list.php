<?php
$sub_menu = '400460';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

if(!$is_development) alert('개발 중입니다.');

$g5['title'] = '거래처원장';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$where = [];

# 기간
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
if(!$fr_date)
  $fr_date = date('Y-m-01');
if(!$to_date)
  $to_date = date('Y-m-d');
$where[] = " (od_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";

# 영업담당자
if(!$mb_manager)
  $mb_manager = [];
$where_manager = [];
if(!$mb_manager_all && $mb_manager) {
  foreach($mb_manager as $man) {
    $where_manager[] = " m.mb_manager = '$man' ";
  }
  $where[] = ' ( ' . implode(' or ', $where_manager) . ' ) ';
}
$manager_result = sql_query("
  SELECT
    a.mb_id,
    m.mb_name
  FROM
    g5_auth a
  LEFT JOIN
    g5_member m ON a.mb_id = m.mb_id
  WHERE
    au_menu = '400400' and
    au_auth LIKE '%w%'
");
$managers = [];
while($manager = sql_fetch_array($manager_result)) {
  $managers[$manager['mb_id']] = $manager['mb_name'];
}

$sql_search = '';
if($where) {
  $sql_search = ' and '.implode(' and ', $where);
}

# 매출
$sql_order = "
  SELECT
    o.od_time,
    o.od_id,
    m.mb_entNm,
    (
      SELECT mb_name from g5_member WHERE mb_id = m.mb_manager
    ) as mb_manager,
    c.it_name,
    c.ct_option,
    (c.ct_qty - c.ct_stock_qty) as ct_qty,
    (
      (
        (c.ct_qty - c.ct_stock_qty) *
        CASE
          WHEN c.io_type = 0
          THEN c.ct_price + c.io_price
          ELSE c.io_price
        END - c.ct_discount
      ) / (c.ct_qty - c.ct_stock_qty)
    ) as price_d,
    (
      SELECT it_taxInfo from g5_shop_item WHERE it_id = c.it_id
    ) as tax_info,
    o.od_b_name
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  LEFT JOIN
    g5_member m ON o.mb_id = m.mb_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0
    {$sql_search}
";

# 배송비
$sql_send_cost = "
  SELECT
    o.od_time,
    o.od_id,
    m.mb_entNm,
    (
      SELECT mb_name from g5_member WHERE mb_id = m.mb_manager
    ) as mb_manager,
    '배송비' as it_name,
    '' as ct_option,
    1 as ct_qty,
    o.od_send_cost as price_d,
    '과세' as tax_info,
    o.od_b_name
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  LEFT JOIN
    g5_member m ON o.mb_id = m.mb_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0 and
    o.od_send_cost > 0
    {$sql_search}
  GROUP BY
    o.od_id
";

# 매출할인
$sql_sales_discount = "
  SELECT
    o.od_time,
    o.od_id,
    m.mb_entNm,
    (
      SELECT mb_name from g5_member WHERE mb_id = m.mb_manager
    ) as mb_manager,
    '매출할인' as it_name,
    '' as ct_option,
    1 as ct_qty,
    (-o.od_sales_discount) as price_d,
    '과세' as tax_info,
    o.od_b_name
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  LEFT JOIN
    g5_member m ON o.mb_id = m.mb_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0 and
    o.od_sales_discount > 0
    {$sql_search}
  GROUP BY
    o.od_id
";

# Todos: 이월잔액

$sql_common = "
FROM
  (
    ({$sql_order})
    UNION ALL
    ({$sql_send_cost})
    UNION ALL
    ({$sql_sales_discount})
  ) u
";

# 구매액 합계 계산
$total_price = sql_fetch("SELECT sum(price_d * ct_qty) as total_price {$sql_common}")['total_price'];

$result = sql_query("
  SELECT
    *
  {$sql_common}
  ORDER BY
    od_time asc,
    od_id asc
");

$ledgers = [];
while($row = sql_fetch_array($result)) {
  if($row['tax_info'] == '영세') {
    $row['price_d_p'] = $row['price_d'] * $row['ct_qty'];
    $row['price_d_s'] = 0;
  } else {
    $row['price_d_p'] = @round(($row['price_d'] ? $row['price_d'] : 0) / 1.1) * $row['ct_qty']; // 공급가액
    $row['price_d_s'] = @round(($row['price_d'] ? $row['price_d'] : 0) / 1.1 / 10) * $row['ct_qty']; // 부가세
  }

  $ledgers[] = $row;
}

# 잔액
$balance = 0;
?>
<div class="new_form">
  <form method="get">
    <table class="new_form_table">
      <tbody>
        <tr>
          <th>기간</th>
          <td>
            <input type="text" id="fr_date" class="date hasDatepicker" name="fr_date" value="<?=$fr_date?>" size="10" maxlength="10"> ~
            <input type="text" id="to_date" class="date hasDatepicker" name="to_date" value="<?=$to_date?>" size="10" maxlength="10">
            <input type="button" value="이번달" id="select_date_thismonth" name="select_date" class="select_date newbutton">
            <input type="button" value="저번달" id="select_date_lastmonth" name="select_date" class="select_date newbutton">
          </td>
        </tr>
        <tr>
          <th>영업담당자</th>
          <td>
            <input type="checkbox" name="mb_manager_all" value="1" id="chk_mb_manager_all" <?php if(!array_diff(array_keys($managers), $mb_manager)) echo 'checked'; ?>>
            <label for="chk_mb_manager_all">전체</label>
            <?php foreach($managers as $mb_id => $mb_name) { ?>
            <input type="checkbox" name="mb_manager[]" value="<?=$mb_id?>" id="manager_<?=$mb_id?>" class="chk_mb_manager" <?php if(in_array($mb_id, $mb_manager)) echo 'checked'; ?>>
            <label for="manager_<?=$mb_id?>"><?=$mb_name?></label>
            <?php } ?>
          </td>
        </tr>
        <tr>
          <th>금액</th>
          <td>
            <select name="sel_price_field" id="sel_price_field">
              <option value="it_price" selected="selected">단가</option>
            </select>
            <input type="text" name="price_s" value="" class="line" maxlength="10" style="width:80px">
            원 ~
            <input type="text" name="price_e" value="" class="line" maxlength="10" style="width:80px">
            원
          </td>
        </tr>
        <tr>
          <th>검색어</th>
          <td>
            <select name="sel_field" id="sel_field">
              <option value="mb_entNm" selected="selected">사업소명</option>
            </select>
            <input type="text" name="search" value="" id="search" class="frm_input" autocomplete="off" style="width:200px;">
          </td>
        </tr>
      </tbody>
    </table>
    <div class="submit">
      <button type="submit" id="search-btn"><span>검색</span></button>
    </div>
  </form>
</div>
<div class="tbl_head01 tbl_wrap">
  <div class="local_ov01" style="border:1px solid #e3e3e3;">
    <h1 style="border:0;padding:5px 0;margin:0;letter-spacing:0;">
      구매액 합계: <?=number_format($total_price)?>원 (공급가:<?=number_format((int)($total_price / 1.1))?>원, VAT:<?=number_format($total_price - (int)($total_price / 1.1))?>원)
    </h1>
    <div class="right">
      <button id="btn_ledger_excel"><img src="<?=G5_ADMIN_URL?>/shop_admin/img/btn_img_ex.gif">엑셀다운로드</button>
      <button id="btn_ledger_manage">수금관리</button>
    </div>
  </div>
  <table>
    <thead>
      <tr>
        <th>주문일</th>
        <th>주문번호</th>
        <th>사업소명</th>
        <th>영업담당자</th>
        <th>품목명</th>
        <th>수량</th>
        <th>단가(VAT포함)</th>
        <th>공급가액</th>
        <th>부가세</th>
        <th>판매</th>
        <th>수금</th>
        <th>잔액</th>
        <th>출고처</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($ledgers as $row) { ?>
      <tr>
        <td class="td_date"><?=date('y-m-d', strtotime($row['od_time']))?></td>
        <td class="td_odrnum2"><?=$row['od_id']?></td>
        <td class="td_id"><?=$row['mb_entNm']?></td>
        <td class="td_payby"><?=$row['mb_manager']?></td>
        <td><?=$row['it_name']?><?=$row['ct_option'] ? "({$row['ct_option']})" : ''?></td>
        <td class="td_numsmall"><?=$row['ct_qty']?></td>
        <td class="td_price"><?=number_format($row['price_d'])?></td>
        <td class="td_price"><?=number_format($row['price_d_p'])?></td>
        <td class="td_price"><?=number_format($row['price_d_s'])?></td>
        <td class="td_price"><?=number_format($row['price_d'] * $row['ct_qty'])?></td>
        <td class="td_price">0</td>
        <td class="td_price"><?=number_format($balance += ($row['price_d'] * $row['ct_qty']))?></td>
        <td class="td_id"><?=$row['od_b_name']?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<style>
.td_price { width: 100px; }
</style>

<script>
function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

$(function() {
  // 기간 - 이번달 버튼
  $('#select_date_thismonth').click(function() {
    var today = new Date(); // 오늘
    $('#to_date').val(formatDate(today));
    today.setDate(1); // 이번달 1일
    $('#fr_date').val(formatDate(today));
  });
  // 기간 - 저번달 버튼
  $('#select_date_lastmonth').click(function() {
    var today = new Date();
    today.setDate(0); // 지난달 마지막일
    $('#to_date').val(formatDate(today));
    today.setDate(1); // 지난달 1일
    $('#fr_date').val(formatDate(today));
  });

  // 영업담당자 - 전체 버튼
  $('#chk_mb_manager_all').change(function() {
    var checked = $(this).is(":checked");
    $(".chk_mb_manager").prop('checked', checked);
  });
  // 영업담당자 - 영업담당자 버튼
  $('.chk_mb_manager').change(function() {
    var total = $('.chk_mb_manager').length;
    var checkedTotal = $('.chk_mb_manager:checked').length;
    $("#chk_mb_manager_all").prop('checked', total <= checkedTotal); 
  });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
