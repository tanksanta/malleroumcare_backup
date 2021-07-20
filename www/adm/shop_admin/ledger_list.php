<?php
$sub_menu = '400460';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '거래처원장';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$qstr = "";
$where = [];
$where_ledger = [];

# 기간
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
if(!$fr_date)
  $fr_date = date('Y-m-01');
if(!$to_date)
  $to_date = date('Y-m-d');
$where_time = " and (od_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
$where_ledger_time = " and (lc_created_at between '$fr_date 00:00:00' and '$to_date 23:59:59') ";

# 영업담당자
if(!$mb_manager)
  $mb_manager = [];
$where_manager = [];
if(!$mb_manager_all && $mb_manager) {
  foreach($mb_manager as $man) {
    $qstr .= "mb_manager%5B%5D={$man}&amp;";
    $where_manager[] = " m.mb_manager = '$man' ";
  }
  $where[] = ' ( ' . implode(' or ', $where_manager) . ' ) ';
  $where_ledger[] = ' ( ' . implode(' or ', $where_manager) . ' ) ';
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

# 금액
$sel_price_field = in_array($sel_price_field, ['price_d', 'price_d_p', 'price_d_s', 'price_d*ct_qty']) ? $sel_price_field : '';
$where_price = '';
if($price && $sel_price_field && $price_s <= $price_e) {
  $price_s = intval($price_s);
  $price_e = intval($price_e);
  $where_price = " where ({$sel_price_field} between {$price_s} and {$price_e}) ";
}

# 검색어
$sel_field = in_array($sel_field, ['mb_entNm', 'o.od_id', 'c.it_name', 'o.od_b_name']) ? $sel_field : '';
$search = get_search_string($search);
if($search) {
  $where[] = " {$sel_field} LIKE '%{$search}%' ";
  if($sel_field == 'mb_entNm')
    $where_ledger[] = " {$sel_field} LIKE '%{$search}%' ";
}

$sql_search = '';
if($where) {
  $sql_search = ' and '.implode(' and ', $where);
}
if($where_ledger) {
  $sql_ledger_search = ' and '.implode(' and ', $where_ledger);
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
    o.od_b_name
  FROM
    g5_shop_order o
  LEFT JOIN
    g5_shop_cart c ON o.od_id = c.od_id
  LEFT JOIN
    g5_member m ON o.mb_id = m.mb_id
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
    m.mb_entNm,
    (
      SELECT mb_name from g5_member WHERE mb_id = m.mb_manager
    ) as mb_manager,
    '^배송비' as it_name,
    '' as ct_option,
    1 as ct_qty,
    o.od_send_cost as price_d,
    ROUND(o.od_send_cost / 1.1) as price_d_p,
    ROUND(o.od_send_cost / 1.1 / 10) as price_d_s,
    0 as deposit,
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
    '^매출할인' as it_name,
    '' as ct_option,
    1 as ct_qty,
    (-o.od_sales_discount) as price_d,
    ROUND(-o.od_sales_discount / 1.1) as price_d_p,
    ROUND(-o.od_sales_discount / 1.1 / 10) as price_d_s,
    0 as deposit,
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
";

# 입금/출금
$sql_ledger = "
  SELECT
    lc_created_at as od_time,
    '' as od_id,
    m.mb_entNm,
    (
      SELECT mb_name from g5_member WHERE mb_id = m.mb_manager
    ) as mb_manager,
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
    '' as od_b_name
  FROM
    ledger_content l
  LEFT JOIN
    g5_member m ON l.mb_id = m.mb_id
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
    ({$sql_ledger} {$sql_ledger_search} {$where_ledger_time})
  ) u
";

# 구매액 합계 계산
$total_result = sql_fetch("SELECT sum(price_d * ct_qty) as total_price, sum(price_d_p) as total_price_p, sum(price_d_s) as total_price_s, count(*) as cnt {$sql_common} {$where_price}");
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
  {$where_price}
  ORDER BY
    od_time asc,
    od_id asc
");

$ledgers = [];
$balance = 0;
while($row = sql_fetch_array($result)) {
  $balance += ($row['price_d'] * $row['ct_qty']);
  $balance -= ($row['deposit']);
  $row['balance'] = $balance;
  $ledgers[] = $row;
}

/*# 이월잔액
$balance_carried = sql_fetch("
  SELECT
    sum(price_d * ct_qty) as balance
  FROM
    (
      ({$sql_order} {$sql_search} and od_time < '$fr_date 00:00:00')
      UNION ALL
      ({$sql_send_cost} {$sql_search} and od_time < '$fr_date 00:00:00' GROUP BY o.od_id)
      UNION ALL
      ({$sql_sales_discount} {$sql_search} and od_time < '$fr_date 00:00:00' GROUP BY o.od_id)
    ) u
  {$where_price}
")['balance'];

# 잔액
$balance = $balance_carried;*/

$qstr .= "fr_date={$fr_date}&amp;to_date={$to_date}&amp;sel_price_field={$sel_price_field}&amp;price_s={$price_s}&amp;price_e={$price_e}&amp;sel_field={$sel_field}&amp;search={$search}";
?>

<style>
.td_price { width: 100px; }
</style>

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
            <input type="checkbox" name="price" value="1" id="search_won" <?=$price ? 'checked' : ''?>><label for="search_won">&nbsp;</label>
            <select name="sel_price_field" id="sel_price_field">
              <option value="price_d" <?=get_selected($sel_price_field, 'price_d')?>>단가</option>
              <option value="price_d_p" <?=get_selected($sel_price_field, 'price_d_p')?>>공급가액</option>
              <option value="price_d_s" <?=get_selected($sel_price_field, 'price_d_s')?>>부가세</option>
              <option value="price_d*ct_qty" <?=get_selected($sel_price_field, 'price_d*ct_qty')?>>판매</option>
            </select>
            <input type="text" name="price_s" value="<?=$price_s?>" class="line" maxlength="10" style="width:80px">
            원 ~
            <input type="text" name="price_e" value="<?=$price_e?>" class="line" maxlength="10" style="width:80px">
            원
          </td>
        </tr>
        <tr>
          <th>검색어</th>
          <td>
            <select name="sel_field" id="sel_field">
              <option value="mb_entNm" <?=get_selected($sel_field, 'mb_entNm')?>>사업소명</option>
              <option value="o.od_id" <?=get_selected($sel_field, 'o.od_id')?>>주문번호</option>
              <option value="c.it_name" <?=get_selected($sel_field, 'c.it_name')?>>품목명</option>
              <option value="o.od_b_name" <?=get_selected($sel_field, 'o.od_b_name')?>>수령인</option>
            </select>
            <input type="text" name="search" value="<?=$search?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
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
      구매액 합계: <?=number_format($total_price)?>원 (공급가:<?=number_format($total_price_p)?>원, VAT:<?=number_format($total_price_s)?>원)
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
        <th>수령인</th>
      </tr>
    </thead>
    <tbody>
      <!--<tr>
        <td class="td_date"><?=date('y-m-d', strtotime($fr_date))?></td>
        <td class="td_odrnum2"></td>
        <td class="td_id"></td>
        <td class="td_payby"></td>
        <td>이월잔액</td>
        <td class="td_numsmall"></td>
        <td class="td_price"></td>
        <td class="td_price"></td>
        <td class="td_price"></td>
        <td class="td_price"></td>
        <td class="td_price"></td>
        <td class="td_price"><?=number_format($balance)?></td>
        <td class="td_id"></td>
      </tr>-->
      <?php
      for($i = $from_record; $i < ($from_record + $page_rows); $i++) {
        if(!isset($ledgers[$i])) break;
        $row = $ledgers[$i];
      ?>
      <tr>
        <td class="td_date"><?=date('y-m-d', strtotime($row['od_time']))?></td>
        <td class="td_odrnum2">
          <?php if($row['od_id']) { ?>
          <a href="<?=G5_ADMIN_URL?>/shop_admin/samhwa_orderform.php?od_id=<?=$row['od_id']?>"><?=$row['od_id']?></a>
          <?php } ?>
        </td>
        <td class="td_id"><?=$row['mb_entNm']?></td>
        <td class="td_payby"><?=$row['mb_manager']?></td>
        <td><?=$row['it_name']?><?=$row['ct_option'] ? "({$row['ct_option']})" : ''?></td>
        <td class="td_numsmall"><?=$row['ct_qty']?></td>
        <td class="td_price"><?=number_format($row['price_d'])?></td>
        <td class="td_price"><?=number_format($row['price_d_p'])?></td>
        <td class="td_price"><?=number_format($row['price_d_s'])?></td>
        <td class="td_price"><?=number_format($row['price_d'] * $row['ct_qty'])?></td>
        <td class="td_price"><?=number_format($row['deposit'])?></td>
        <td class="td_price"><?=number_format($row['balance'])?></td>
        <td class="td_id"><?=$row['od_b_name']?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
</div>

<script>
function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

$(function() {
  // 수금관리 버튼
  $('#btn_ledger_manage').click(function() {
    location.href = "<?=G5_ADMIN_URL?>/shop_admin/ledger_search.php";
  });

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
