<?php
include_once('./_common.php');

if(!$member['mb_id']) {
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "거래처원장";
include_once("./_head.php");

# 기간
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
if(!$fr_date)
  $fr_date = date('Y-m-01');
if(!$to_date)
  $to_date = date('Y-m-d');
$where_time = " and (od_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
$where_ledger_time = " and (lc_created_at between '$fr_date 00:00:00' and '$to_date 23:59:59') ";

$sql_search = " and o.mb_id = '{$member['mb_id']}' ";
$sql_ledger_search = " and l.mb_id = '{$member['mb_id']}' ";

# 매출
$sql_order = "
  SELECT
    o.od_time,
    o.od_id,
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
      CASE
        WHEN i.it_taxInfo = '영세'
        THEN
          (
            (c.ct_qty - c.ct_stock_qty) *
            CASE
              WHEN c.io_type = 0
              THEN c.ct_price + c.io_price
              ELSE c.io_price
            END - c.ct_discount
          )
        ELSE
          ROUND(
            (
              (c.ct_qty - c.ct_stock_qty) *
              CASE
                WHEN c.io_type = 0
                THEN c.ct_price + c.io_price
                ELSE c.io_price
              END - c.ct_discount
            ) / 1.1
          )
      END
    ) as price_d_p,
    (
      CASE
        WHEN i.it_taxInfo = '영세'
        THEN 0
        ELSE
          ROUND (
            (
              (
                (c.ct_qty - c.ct_stock_qty) *
                CASE
                  WHEN c.io_type = 0
                  THEN c.ct_price + c.io_price
                  ELSE c.io_price
                END - c.ct_discount
              )
            ) / 1.1 / 10
          )
      END
    ) as price_d_s,
    0 as deposit,
    o.od_b_name,
    1 as custom_order,
    c.ct_id as custom_sub_order
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  LEFT JOIN
    g5_shop_item i ON i.it_id = c.it_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0
";

# 배송비
$sql_send_cost = "
  SELECT
    o.od_time,
    o.od_id,
    '^배송비' as it_name,
    '' as ct_option,
    1 as ct_qty,
    (o.od_send_cost + o.od_send_cost2) as price_d,
    ROUND( (o.od_send_cost + o.od_send_cost2) / 1.1) as price_d_p,
    ROUND( (o.od_send_cost + o.od_send_cost2) / 1.1 / 10) as price_d_s,
    0 as deposit,
    o.od_b_name,
    2 as custom_order,
    0 as custom_sub_order
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0 and
    o.od_send_cost > 0
";

# 매출할인
$sql_sales_discount = "
  SELECT
    o.od_time,
    o.od_id,
    '^매출할인' as it_name,
    '' as ct_option,
    1 as ct_qty,
    (-o.od_sales_discount) as price_d,
    ROUND(-o.od_sales_discount / 1.1) as price_d_p,
    ROUND(-o.od_sales_discount / 1.1 / 10) as price_d_s,
    0 as deposit,
    o.od_b_name,
    3 as custom_order,
    0 as custom_sub_order
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0 and
    o.od_sales_discount > 0
";

# 쿠폰할인
$coupon_price = "(o.od_cart_coupon + o.od_coupon + o.od_send_coupon)";
$sql_sales_coupon = "
  SELECT
    o.od_time,
    o.od_id,
    '^쿠폰할인' as it_name,
    '' as ct_option,
    1 as ct_qty,
    (-$coupon_price) as price_d,
    ROUND(-$coupon_price / 1.1) as price_d_p,
    ROUND(-$coupon_price / 1.1 / 10) as price_d_s,
    0 as deposit,
    o.od_b_name,
    4 as custom_order,
    0 as custom_sub_order
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0 and
    $coupon_price > 0
";

# 포인트결제
$sql_sales_point = "
  SELECT
    o.od_time,
    o.od_id,
    '^포인트결제' as it_name,
    '' as ct_option,
    1 as ct_qty,
    (-o.od_receipt_point) as price_d,
    ROUND(-o.od_receipt_point / 1.1) as price_d_p,
    ROUND(-o.od_receipt_point / 1.1 / 10) as price_d_s,
    0 as deposit,
    o.od_b_name,
    5 as custom_order,
    0 as custom_sub_order
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  WHERE
    c.ct_status = '완료' and
    c.ct_qty - c.ct_stock_qty > 0 and
    o.od_receipt_point > 0
";

# 입금/출금
$sql_ledger = "
  SELECT
    lc_created_at as od_time,
    '' as od_id,
    (
      CASE
        WHEN lc_type = 1
        THEN '입금'
        WHEN lc_type = 2
        THEN '출금'
      END
    ) as it_name,
    lc_memo as ct_option,
    1 as ct_qty,
    (
      CASE
        WHEN lc_type = 2
        THEN lc_amount
        ELSE 0
      END
    ) as price_d,
    (
      CASE
        WHEN lc_type = 2
        THEN lc_amount
        ELSE 0
      END
    ) as price_d_p,
    0 as price_d_s,
    (
      CASE
        WHEN lc_type = 1
        THEN lc_amount
        ELSE 0
      END
    ) as deposit,
    '' as od_b_name,
    0 as custom_order,
    0 as custom_sub_order
  FROM
    ledger_content l
  WHERE
    1 = 1
";

$sql_common = "
FROM
  (
    ({$sql_order} {$sql_search} {$where_time})
    UNION ALL
    ({$sql_send_cost} {$sql_search} {$where_time} GROUP BY o.od_id)
    UNION ALL
    ({$sql_sales_discount} {$sql_search} {$where_time} GROUP BY o.od_id)
    UNION ALL
    ({$sql_sales_coupon} {$sql_search} {$where_time} GROUP BY o.od_id)
    UNION ALL
    ({$sql_sales_point} {$sql_search} {$where_time} GROUP BY o.od_id)
    UNION ALL
    ({$sql_ledger} {$sql_ledger_search} {$where_ledger_time})
  ) u
";

# 구매액 합계 계산
$total_result = sql_fetch("SELECT sum(price_d * ct_qty) as total_price, sum(price_d_p) as total_price_p, sum(price_d_s) as total_price_s, count(*) as cnt {$sql_common}");
$total_price = $total_result['total_price'];
$total_price_p = $total_result['total_price_p'];
$total_price_s = $total_result['total_price_s'];
$total_count = $total_result['cnt'];

$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$result = sql_query("
  SELECT
    *
  {$sql_common}
  ORDER BY
    od_time ASC,
    od_id ASC,
    custom_order ASC,
    custom_sub_order ASC
", true);

$ledgers = [];
$carried_balance = get_outstanding_balance($member['mb_id'], $fr_date);
$balance = $carried_balance;
while($row = sql_fetch_array($result)) {
  $balance += ($row['price_d'] * $row['ct_qty']);
  $balance -= ($row['deposit']);
  $row['balance'] = $balance;
  $ledgers[] = $row;
}

# 검색어
$sel_field = in_array($sel_field, ['it_name', 'od_id']) ? $sel_field : '';
$search = get_search_string($search);
if($sel_field && $search) {
  // 검색결과 필터링
  $ledgers = array_values(array_filter($ledgers, function($v) {
    global $sel_field, $search;
    $pattern = '/.*'.preg_quote($search).'.*/i';
    return preg_match($pattern, $v[$sel_field]);
  }));

  // 페이지 다시 계산
  $total_count = count($ledgers);
  $total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
}

$qstr = "fr_date={$fr_date}&to_date={$to_date}&sel_field={$sel_field}&search={$search}";

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>

<section class="wrap">
  <div class="sub_section_tit">거래처원장</div>
  <form method="get">
    <div class="search_box">
      <div class="search_date">
        <input type="text" name="fr_date" value="<?=$fr_date?>" id="fr_date" class="datepicker"/> ~ <input type="text" name="to_date" value="<?=$to_date?>" id="to_date" class="datepicker"/>
        <a href="#" id="select_date_thismonth">이번달</a>
        <a href="#" id="select_date_lastmonth">저번달</a>
      </div>
      <select name="sel_field" id="sel_field">
        <option value="it_name" <?=get_selected($sel_field, 'it_name')?>>품목명</option>
        <option value="od_id" <?=get_selected($sel_field, 'od_id')?>>주문번호</option>
      </select>
      <div class="input_search">
        <input name="search" value="<?=$search?>" type="text">
        <button type="submit"></button>
      </div>
    </div>
  </form>
  <div class="inner">
    <div class="list_box">
      <div class="subtit">
        검색 기간 내 구매액 : <?=number_format($total_price)?>원 <span>(공급가:<?=number_format($total_price_p)?>원, VAT:<?=number_format($total_price_s)?>원)</span>
        <div class="r_area">
          <a href="my_ledger_excel.php?fr_date=<?=$fr_date?>&amp;to_date=<?=$to_date?>" class="btn_green_box">엑셀다운로드</a>
        </div>
      </div>
      <div class="table_box">
        <table>
          <thead>
            <tr>
              <th>주문일</th>
              <th>주문번호</th>
              <th>품목명</th>
              <th>수량</th>
              <th>단가(VAT포함)</th>
              <th>공금가액</th>
              <th>부가세</th>
              <th>구매</th>
              <th>수금</th>
              <th>잔액</th>
              <th>수령인</th>
            </tr>
          </thead>
          <tbody>
            <?php if($page == 1 && $carried_balance && !($sel_field && $search)) { ?>
            <tr>
              <td colspan="9">이월잔액</td>
              <td class="text_r"><?=number_format($carried_balance)?></td>
              <td></td>
            </tr>
            <?php } ?>
            <?php
            for($i = $from_record; $i < ($from_record + $page_rows); $i++) {
              if(!isset($ledgers[$i])) break;
              $row = $ledgers[$i];
            ?>
            <tr>
              <td class="text_c"><?=date('y-m-d', strtotime($row['od_time']))?></td>
              <td class="text_c">
                <?php if($row['od_id']) { ?>
                <a href="<?=G5_SHOP_URL?>/orderinquiryview.php?od_id=<?=$row['od_id']?>"><?=$row['od_id']?></a>
                <?php } ?>
              </td>
              <td><?=$row['it_name']?><?=$row['ct_option'] && $row['ct_option'] != $row['it_name'] ? "({$row['ct_option']})" : ''?></td>
              <td class="text_c"><?=$row['ct_qty']?></td>
              <td class="text_r"><?=number_format($row['price_d'])?></td>
              <td class="text_r"><?=number_format($row['price_d_p'])?></td>
              <td class="text_r"><?=number_format($row['price_d_s'])?></td>
              <td class="text_r"><?=number_format($row['price_d'] * $row['ct_qty'])?></td>
              <td class="text_r"><?=number_format($row['deposit'])?></td>
              <td class="text_r"><?=number_format($row['balance'])?></td>
              <td><?=$row['od_b_name']?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <div class="list-paging">
        <ul class="pagination pagination-sm en">
          <?php echo apms_paging(5, $page, $total_page, '?'.$qstr.'&amp;page='); ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<script>
function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

$(function() {
  // 기간 - datepicker
  $('.datepicker').datepicker({
    changeMonth: true,
    changeYear: true,
    dateFormat: "yy-mm-dd",
    showButtonPanel: true,
    yearRange: "c-99:c+99",
    maxDate: "+0d"
  });

  // 기간 - 이번달 버튼
  $('#select_date_thismonth').click(function(e) {
    e.preventDefault();

    var today = new Date(); // 오늘
    $('#to_date').val(formatDate(today));
    today.setDate(1); // 이번달 1일
    $('#fr_date').val(formatDate(today));
  });
  // 기간 - 저번달 버튼
  $('#select_date_lastmonth').click(function(e) {
    e.preventDefault();

    var today = new Date();
    today.setDate(0); // 지난달 마지막일
    $('#to_date').val(formatDate(today));
    today.setDate(1); // 지난달 1일
    $('#fr_date').val(formatDate(today));
  });
});
</script>

<?php
include_once('./_tail.php');
?>
