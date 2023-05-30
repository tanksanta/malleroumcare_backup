<?php
$sub_menu = '400620';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);

$doc = strip_tags($doc);
$sort1 = in_array($sort1, array('it_id', 'it_name', 'it_stock_qty', 'it_use', 'it_soldout', 'it_stock_sms', 'sum_ws_qty')) ? $sort1 : '';
$sort2 = in_array($sort2, array('desc', 'asc')) ? $sort2 : 'desc';
$sel_ca_id = get_search_string($sel_ca_id);
$sel_field = get_search_string($sel_field);
$search = get_search_string($search);
$wh_name = get_search_string($wh_name);
$use_warehouse_where_sql = get_use_warehouse_where_sql();

$g5['title'] = '상품재고관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql_search = " where 1 ";
if ($search != "") {
  if ($sel_field != "") {
    $sql_search .= " and $sel_field like '%$search%' ";
  }
}

if ($sel_ca_id != "") {
  $sql_search .= " and ca_id like '$sel_ca_id%' ";
}

if ($wh_name != '') {
  $sql_search .= " and ( select (sum(ws_qty) - sum(ws_scheduled_qty)) from warehouse_stock s where T.it_id = s.it_id and T.it_warehousing_warehouse = '$wh_name' and ws_del_yn = 'N' {$use_warehouse_where_sql} ) <> 0 ";
}

// 안전재고 상품
if ($stock_type == 'safe_min') {
  $sql_search .= " and (sum_ws_qty <= safe_min_stock_qty) and sum_ws_qty != 0 and safe_min_stock_qty != 0 ";
}

// 최대재고 상품
if ($stock_type == 'safe_max') {
  $sql_search .= " and (sum_ws_qty > safe_min_stock_qty and sum_ws_qty <= safe_max_stock_qty) and sum_ws_qty != 0 and safe_min_stock_qty != 0 ";
}

// 악성재고 상품
if ($stock_type == 'malignity') {
  $sql_search .= " and (sum_ws_qty > safe_max_stock_qty) and sum_ws_qty != 0 and safe_min_stock_qty != 0 ";
}

// 재고와 바코드 상이한 제품
if ($stock_type == 'notMatchBarcodeQty') {
  $sql_search .= " and (sum_ws_qty != sum_barcode_qty) and sum_ws_qty != 0";
}

if ($sel_field == "")  $sel_field = "it_name";
if ($sort1 == "") $sort1 = "it_stock_qty";
if ($sort2 == "") $sort2 = "asc";

$common_ct_status = "('주문', '입금', '준비', '출고준비', '배송', '완료')";
$sql_common = "
from 
	(SELECT 
		b.io_id,
		b.io_type,
		b.io_stock_qty,
		b.io_noti_qty,
		a.*,
    CASE
      WHEN io_stock_manage_min_qty IS NOT NULL AND io_stock_manage_min_qty > 0
        THEN io_stock_manage_min_qty 
      WHEN it_stock_manage_min_qty IS NOT NULL AND it_stock_manage_min_qty > 0
        THEN it_stock_manage_min_qty
      ELSE
        IFNULL(ROUND((SELECT sum(ct_qty) FROM g5_shop_cart
        WHERE (ct_time >= DATE_FORMAT(CONCAT(SUBSTR(NOW() - INTERVAL 3 MONTH, 1 ,8), '01'), '%Y-%m-%d 00:00:00') AND
          ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 MONTH), '%Y-%m-%d 23:59:59'))
        AND ct_status IN {$common_ct_status}
        AND it_id = a.it_id AND io_id = IFNULL(b.io_id, '')) / 3 * 0.5), 0)
    END AS safe_min_stock_qty,    
    CASE
      WHEN io_stock_manage_max_qty IS NOT NULL AND io_stock_manage_max_qty > 0
        THEN io_stock_manage_max_qty 
      WHEN it_stock_manage_max_qty IS NOT NULL AND it_stock_manage_max_qty > 0
        THEN it_stock_manage_max_qty
      ELSE
        IFNULL(ROUND((SELECT sum(ct_qty) FROM g5_shop_cart
        WHERE (ct_time >= DATE_FORMAT(CONCAT(SUBSTR(NOW() - INTERVAL 3 MONTH, 1 ,8), '01'), '%Y-%m-%d 00:00:00') AND
          ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 MONTH), '%Y-%m-%d 23:59:59'))
        AND ct_status IN {$common_ct_status}
        AND it_id = a.it_id AND io_id = IFNULL(b.io_id, '')) / 3 * 1.5), 0)
    END AS safe_max_stock_qty, 
    (SELECT IFNULL(sum(ws_qty) - sum(ws_scheduled_qty), 0) FROM warehouse_stock WHERE it_id = a.it_id AND io_id = IFNULL(b.io_id, '') AND ws_del_yn = 'N' {$use_warehouse_where_sql}) AS sum_ws_qty,
    (SELECT count(*) FROM g5_cart_barcode WHERE it_id = a.it_id AND io_id = IFNULL(b.io_id, '') AND bc_del_yn = 'N') AS sum_barcode_qty,
    ROUND((SELECT sum(ct_qty) FROM g5_shop_cart
        WHERE (ct_time >= DATE_FORMAT(CONCAT(SUBSTR(NOW() - INTERVAL 3 MONTH, 1 ,8), '01'), '%Y-%m-%d 00:00:00') AND
          ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 MONTH), '%Y-%m-%d 23:59:59'))
        AND ct_status IN {$common_ct_status}
        AND it_id = a.it_id AND io_id = IFNULL(b.io_id, '')) / 3) AS sum_ct_qty_3month,
    (SELECT sum(ct_qty) FROM g5_shop_cart 
        WHERE (ct_time >= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 31 DAY), '%Y-%m-%d 00:00:00') AND
              ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 DAY), '%Y-%m-%d 23:59:59'))
            AND ct_status IN {$common_ct_status}
            AND it_id = a.it_id AND io_id = IFNULL(b.io_id, '')) AS sum_ct_qty_1month,
    (SELECT sum(ct_qty) FROM g5_shop_cart 
        WHERE (ct_time >= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 2 DAY), '%Y-%m-%d 00:00:00') AND
              ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 DAY), '%Y-%m-%d 23:59:59'))
            AND ct_status IN {$common_ct_status}
            AND it_id = a.it_id AND io_id = IFNULL(b.io_id, '')) AS sum_ct_qty_1day,
    (SELECT max(ct_time) FROM g5_shop_cart 
        WHERE ct_status IN {$common_ct_status}
        AND it_id = a.it_id AND io_id = IFNULL(b.io_id, '')) AS last_ct_time
	FROM 
	  (select
	      it_id,
	      it_name,
	      it_use,
	      it_stock_qty,
	      it_stock_sms,
	      it_noti_qty,
	      it_soldout,
	      ca_id,
	      pt_it,
	      pt_id,
	      it_expected_warehousing_date,
	      it_option_subject,
	      it_stock_manage_min_qty,
	      it_stock_manage_max_qty,
	      it_warehousing_warehouse
    	from g5_shop_item i) AS a
		LEFT JOIN (SELECT * from g5_shop_item_option WHERE io_type = '0' AND io_use = '1') AS b ON (a.it_id = b.it_id)
	) AS t
";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common . $sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// APMS - 2014.07.20
$sql  = "
  select *
  {$sql_common}
  {$sql_search}
  order by $sort1 $sort2
  limit $from_record, $rows
";
$result = sql_query($sql);

$colspan = 11;

$warehouse_total_qty = 0;
$warehouse_list = get_warehouses();
foreach($warehouse_list as &$warehouse) {
  $sql = " select (sum(ws_qty) - sum(ws_scheduled_qty))  as total from warehouse_stock where wh_name = '$warehouse' and ws_del_yn = 'N' {$use_warehouse_where_sql} ";
  $result_total = sql_fetch($sql);

  $warehouse = [
    'name' => $warehouse,
    'total' => $result_total['total'] ?: 0
  ];

  $warehouse_total_qty += $result_total['total'] ?: 0;
  $colspan++;
}
unset($warehouse);

$qstr1 = 'sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search.'&amp;wh_name='.$wh_name.'&amp;stock_type='.$stock_type;
$qstr = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$count_warn1 = get_manage_stock_count(1);
$count_warn2 = get_manage_stock_count(2);
$count_warn3 = get_manage_stock_count(3);

$sql = "
  SELECT
      COUNT(*) AS cnt
    FROM
    (SELECT
      (SELECT 
        IFNULL(sum(ws_qty) - sum(ws_scheduled_qty), 0) 
      FROM warehouse_stock 
      WHERE it_id = a.it_id AND io_id = IFNULL(b.io_id, '') AND ws_del_yn = 'N' {$use_warehouse_where_sql}) AS sum_ws_qty,
      (SELECT count(*)
        FROM g5_cart_barcode
        WHERE it_id = a.it_id AND io_id = IFNULL(b.io_id, '') AND bc_del_yn = 'N') AS sum_barcode_qty
    FROM
      g5_shop_item AS a
      LEFT JOIN (SELECT * from g5_shop_item_option WHERE io_type = '0' AND io_use = '1') AS b ON (a.it_id = b.it_id)) AS T
  WHERE sum_ws_qty != sum_barcode_qty 
";

$count_warn4 = sql_fetch($sql)['cnt'];

?>

<script src="<?php echo G5_ADMIN_URL;?>/apms_admin/apms.admin.js"></script>

<style>
  #loading_excel {
    display: none;
    width: 100%;
    height: 100%;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.3);
  }
  #loading_excel .loading_modal {
    position: absolute;
    width: 400px;
    padding: 30px 20px;
    background: #fff;
    text-align: center;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
  #loading_excel .loading_modal p {
    padding: 0;
    font-size: 16px;
  }
  #loading_excel .loading_modal img {
    display: block;
    margin: 20px auto;
  }
  #loading_excel .loading_modal button {
    padding: 10px 30px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
  }

  .quick_link_area a.active {
    border: 1px solid #f00;
  }

  .smart_btn {
    float: right;
    background: #ff5c01;
    color: #fff;
    padding: 8px 18px;
    font-size: 13px;
    top: -2px;
    position: relative;
  }

  tr.warn {
    background: #ffe6ea;
  }
</style>

<div class="local_ov01 local_ov">
  <?php echo $listall; ?>
  <span class="btn_ov01"><span class="ov_txt">전체 상품</span><span class="ov_num">  <?php echo $total_count; ?>개</span></span>
  <button class="smart_btn" onclick="openPopSmartPurchase()">스마트 발주 (<?php echo $count_warn1 ?>개 대기)</button>
</div>

<form id="smartForm" action="/adm/shop_admin/purchase_orderlist.php" method="POST">
  <input type="hidden" name="smart_purchase_data" value="">
</form>

<div style="padding: 5px 20px">
  <ul>
    <li>안전재고 : 월 평균 판매 수량의 50%를 보유해야 안전합니다.</li>
    <li>최대재고 : 월 평균 판매 수량의 3배 이상 보유한 경우 과재고입니다.</li>
  </ul>
</div>

<form name="flist" class="local_sch01 local_sch">
  <input type="hidden" name="doc" value="<?php echo $doc; ?>">
  <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
  <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
  <input type="hidden" name="page" value="<?php echo $page; ?>">

  <div class="quick_link_area" style="padding-bottom: 20px">
    <a class="<?php echo $wh_name == '' ? 'active' : '' ?>" href="<?php echo $_SERVER['SCRIPT_NAME']."?wh_name=" ?>">전체 상품 </a>
    <?php foreach($warehouse_list as $warehouse) { ?>
      <a class="<?php echo $wh_name == $warehouse['name'] ? 'active' : '' ?>" href="<?php echo $_SERVER['SCRIPT_NAME'].'?wh_name='.$warehouse['name']; ?>"><?php echo $warehouse['name']; ?><?php // echo $warehouse['total']; ?></a>
    <?php } ?>
  </div>

  <div class="quick_link_area" style="padding-bottom: 20px">
    <?php if ($count_warn1 > 0) { ?>
      <a class="<?php echo $stock_type == 'safe_min' ? 'active' : '' ?>" href="<?php echo $stock_type == 'safe_min' ? $_SERVER['SCRIPT_NAME'] . '?stock_type=' :  $_SERVER['SCRIPT_NAME'].'?stock_type=safe_min' ?>"><img src="/img/warn1.png" style="margin-right: 8px">안전재고 이하 상품 (<?php echo $count_warn1 ?>개)</a>
    <?php } ?>

    <?php if ($count_warn2 > 0) { ?>
      <a class="<?php echo $stock_type == 'safe_max' ? 'active' : '' ?>" href="<?php echo $stock_type == 'safe_max' ? $_SERVER['SCRIPT_NAME'] . '?stock_type=' :  $_SERVER['SCRIPT_NAME'].'?stock_type=safe_max' ?>"><img src="/img/warn2.png" style="margin-right: 8px">최대재고 이상 상품 (<?php echo $count_warn2 ?>개)</a>
    <?php } ?>

    <?php if ($count_warn3 > 0) { ?>
      <a class="<?php echo $stock_type == 'malignity' ? 'active' : '' ?>" href="<?php echo $stock_type == 'malignity' ? $_SERVER['SCRIPT_NAME'] . '?stock_type=' : $_SERVER['SCRIPT_NAME'].'?stock_type=malignity' ?>"><img src="/img/warn3.png" style="margin-right: 8px">악성재고 상품 (<?php echo $count_warn3 ?>개)</a>
    <?php } ?>

    <?php if ($count_warn4 > 0) { ?>
      <a class="<?php echo $stock_type == 'notMatchBarcodeQty' ? 'active' : '' ?>" href="<?php echo $stock_type == 'notMatchBarcodeQty' ?$_SERVER['SCRIPT_NAME'] .  '?stock_type=' : $_SERVER['SCRIPT_NAME'].'?stock_type=notMatchBarcodeQty' ?>"><img src="/img/warn5.png" style="margin-right: 8px">재고와 바코드 상이한 상품 (<?php echo $count_warn4 ?>개)</a>
    <?php } ?>
  </div>

  <label for="sel_ca_id" class="sound_only">분류선택</label>
  <select name="sel_ca_id" id="sel_ca_id">
    <option value=''>전체분류</option>
    <?php
    $sql1 = " select ca_id, ca_name, as_line from {$g5['g5_shop_category_table']} order by ca_id,ca_order  ";
    $result1 = sql_query($sql1);
    for ($i=0; $row1=sql_fetch_array($result1); $i++) {
      $len = strlen($row1['ca_id']) / 2 - 1;
      $nbsp = "";
      for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
      if($row1['as_line']) {
        echo "<option value=\"\">".$nbsp."------------</option>\n";
      }
      echo '<option value="'.$row1['ca_id'].'" '.get_selected($sel_ca_id, $row1['ca_id']).'>'.$nbsp.$row1['ca_name'].'</option>'.PHP_EOL;
    }
    ?>
  </select>

  <label for="sel_field" class="sound_only">검색대상</label>
  <select name="sel_field" id="sel_field">
    <option value="it_name" <?php echo get_selected($sel_field, 'it_name'); ?>>상품명</option>
    <option value="it_id" <?php echo get_selected($sel_field, 'it_id'); ?>>상품코드</option>
    <!-- APMS - 2014.07.20 -->
    <option value="pt_id" <?php echo get_selected($sel_field, 'pt_id'); ?>>파트너 아이디</option>
    <!-- // -->
  </select>

  <label for="search" class="sound_only">검색어</label>
  <input type="text" name="search" id="search" value="<?php echo $search; ?>" class="frm_input">
  <input type="submit" value="검색" class="btn_submit">

</form>

<div class="local_desc01 local_desc flex-row justify-space-between align-center">
  <p style="padding: 0">재고수정의 수치를 수정하시면 창고재고의 수치가 변경됩니다.</p>
  <div class="flex-row justify-space-between align-center">
    <button style="border: 0px;background: #383838;color: #fff;padding: 5px 10px;margin-right: 10px;" onclick="downloadExcel();">엑셀 다운로드</button>
    <button style="border: 0px;background: #383838;color: #fff;padding: 5px 10px;" onclick="excelUploadPopUp();">재고 엑셀 일괄 업로드</button>
  </div>
</div>

<form name="fitemstocklist" action="./itemstocklistupdate.php" method="post">
  <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
  <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
  <input type="hidden" name="sel_ca_id" value="<?php echo $sel_ca_id; ?>">
  <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
  <input type="hidden" name="search" value="<?php echo $search; ?>">
  <input type="hidden" name="page" value="<?php echo $page; ?>">

  <div class="tbl_head01 tbl_wrap">
    <table>
      <caption><?php echo $g5['title']; ?> 목록</caption>
      <thead>
      <tr>
        <th scope="col"><a href="<?php echo title_sort("it_id") . "&amp;$qstr1"; ?>">상품코드</a></th>
        <th scope="col"><a href="<?php echo title_sort("it_name") . "&amp;$qstr1"; ?>">상품명</a></th>
        <th scope="col">옵션항목</th>
        <th scope="col">재고경고</th>
        <th scope="col"><a href="<?php echo title_sort("sum_ws_qty") . "&amp;$qstr1"; ?>">창고재고</a></th>
        <th scope="col">바코드</th>
        <th scope="col">평균출고</th>
        <th scope="col">안전재고</th>
        <?php foreach($warehouse_list as $warehouse) {
             if ((strcmp($wh_name,'') == 0) || (strcmp($warehouse['name'],$wh_name) == 0)) { ?>
            <th scope="col" style="color:yellow"><?=$warehouse['name']?></th>
        <?php }
        } ?>
        <th scope="col">주문대기</th>
        <th scope="col">가재고</th>
        <th scope="col">입고예정일알림</th>
        <!-- <th scope="col">재고수정</th>
        <th scope="col">통보수량</th> -->
        <th scope="col"><a href="<?php echo title_sort("it_use") . "&amp;$qstr1"; ?>">판매</a></th>
        <th scope="col"><a href="<?php echo title_sort("it_soldout") . "&amp;$qstr1"; ?>">품절</a></th>
        <th scope="col"><a href="<?php echo title_sort("it_stock_sms") . "&amp;$qstr1"; ?>">재입고알림</a></th>
        <th scope="col">입/출고관리</th>
        <th scope="col">상품관리</th>
        <th scope="col">실시간 평균 판매</th>
      </tr>
      </thead>
      <tbody>
      <?php
      for ($i=0; $row=sql_fetch_array($result); $i++)
      {
        $href = G5_SHOP_URL."/item.php?it_id={$row['it_id']}";

        // 선택옵션이 있을 경우 주문대기 수량 계산하지 않음
        $sql2 = " select count(*) as cnt from {$g5['g5_shop_item_option_table']} where it_id = '{$row['it_id']}' and io_type = '0' and io_use = '1' ";
        $row2 = sql_fetch($sql2);

        if(!$row2['cnt']) {
          $sql1 = " select SUM(ct_qty) as sum_qty
                        from {$g5['g5_shop_cart_table']}
                       where it_id = '{$row['it_id']}'
                         and ct_stock_use = '0'
                         and ct_status in ('쇼핑', '주문', '입금', '준비') ";
          $row1 = sql_fetch($sql1);
          $wait_qty = $row1['sum_qty'];
        }

        // 가재고 (미래재고)
        $temporary_qty = $row['it_stock_qty'] - $wait_qty;

        // 통보수량보다 재고수량이 작을 때
        $it_stock_qty = number_format($row['it_stock_qty']);
        $it_stock_qty_st = ''; // 스타일 정의
        if($row['it_stock_qty'] <= $row['it_noti_qty']) {
          $it_stock_qty_st = ' sit_stock_qty_alert';
          $it_stock_qty = ''.$it_stock_qty.' !<span class="sound_only"> 재고부족 </span>';
        }

        $bg = 'bg'.($i%2);
        $bg_warn = '';
        if ($row['sum_ws_qty'] != $row['sum_barcode_qty']) {
          $bg_warn = 'warn';
        }
        if ( (strcmp($wh_name,'') == 0) ||
                (strcmp($row['it_warehousing_warehouse'],$wh_name) == 0)) {

        ?>
        <tr class="<?php echo $bg; ?> <?php echo $bg_warn; ?>">
          <!-- APMS - 2014.07.20 -->
          <td class="td_code" style="white-space:nowrap">
            <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
            <div style="font-size:11px; letter-spacing:-1px;"><?php echo apms_pt_it($row['pt_it'],1);?></div>
            <b><?php echo $row['it_id']; ?></b>
            <?php if($row['pt_id']) { ?>
              <div style="font-size:11px; letter-spacing:-1px;"><?php echo $row['pt_id'];?></div>
            <?php } ?>
          </td>
          <!-- // -->
          <td class="td_left"><a href="<?php echo $href; ?>"><?php echo get_it_image($row['it_id'], 50, 50); ?> <?php echo cut_str(stripslashes($row['it_name']), 60, "&#133"); ?></a></td>
          <!--        <td class="td_num--><?php //echo $it_stock_qty_st; ?><!--">--><?php //echo $it_stock_qty; ?><!--</td>-->
          <td class="td_left">
            <?php
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
                  $option_br = '<br>';
                }
              }
            }

            echo $option
            ?>
          </td>
          <?php
          $img_src = '';
          $alt_txt = '';
          $current_ws_qty = $row['sum_ws_qty'] ?: 0;
          $safe_min_qty = $row['safe_min_stock_qty'] ?: 0;
          $safe_max_qty = $row['safe_max_stock_qty'] ?: 0;

          if ($current_ws_qty <= $safe_min_qty) {
            $img_src = '/img/warn1.png';
            $alt_txt = "안전재고 ({$safe_min_qty}개)";

            $purchase_list = get_purchase_order_by_it_id($row['it_id'], '발주완료');
            if (count($purchase_list) > 0) {
              $total_purchase_qty = 0;
              for ($i = 0; $i < count($purchase_list); $i++) {
                $alt_txt .= '&#10;';
                $alt_txt .= "발주완료 ({$purchase_list[$i]['ct_qty']}개) {$purchase_list[$i]['ct_time']}";;
                $total_purchase_qty += $purchase_list[$i]['ct_qty'];
              }
              if ($safe_min_qty < ($current_ws_qty + $total_purchase_qty)) {
                $img_src = '/img/warn4.png';
              }
            }
          }
          if (($current_ws_qty > $safe_min_qty) && ($current_ws_qty <= $safe_max_qty)) {
            $img_src = '/img/warn2.png';
            $alt_txt = "최대재고 ({$safe_max_qty}개)";
          }

          if ($current_ws_qty > $safe_max_qty) {
            $img_src = '/img/warn3.png';
            $alt_txt = "최대재고 ({$safe_max_qty}개)";
          }

          if ($current_ws_qty == 0 && $safe_min_qty == 0 && $safe_max_qty == 0) {
            $img_src = '';
            $alt_txt = '';
          }

          ?>
          <td class="td_num"><?php echo $img_src ? '<img src="' . $img_src . '" title="' . $alt_txt . '">' : '' ?></td>
          <td class="td_num"><?php echo number_format($row['sum_ws_qty']) ?></td>
          <td class="td_num">
            <a href="./itemstockbarcodelist.php?it_id=<?=$row['it_id']?>&io_id=<?=$row['io_id']?>&type=hold" style="text-decoration: underline !important;">
              <?php echo number_format($row['sum_barcode_qty']) ?>
            </a>
          </td>
          <td class="td_num"><?php echo number_format($row['sum_ct_qty_3month']) ?></td>
          <td class="td_num"><?php echo number_format($row['safe_min_stock_qty']) ?></td>
          <?php
          foreach($warehouse_list as $warehouse) {
             if ((strcmp($wh_name,'') == 0) || (strcmp($warehouse['name'],$wh_name) == 0)) {
            $sql = " select (sum(ws_qty) - sum(ws_scheduled_qty)) as stock from warehouse_stock where it_id = '{$row['it_id']}' and io_id = '{$row['io_id']}' and wh_name = '{$warehouse['name']}' and ws_del_yn = 'N' {$use_warehouse_where_sql} ";
            $stock = sql_fetch($sql)['stock'] ?: 0;
            echo '<td class="td_num" style="color:red">'.number_format($stock).'</td>';
            }
          }
          ?>
          <td class="td_num"><?php echo number_format($wait_qty); ?></td>
          <td class="td_num"><?php echo number_format($temporary_qty); ?></td>
          <td class="td_num"><input type="text" name="it_expected_warehousing_date[<?php echo $i; ?>]" value="<?php echo get_text(cut_str($row['it_expected_warehousing_date'], 250, "")); ?>" class="frm_input" /></td>
          <!-- <td class="td_num">
            <label for="stock_qty_<?php echo $i; ?>" class="sound_only">재고수정</label>
            <input type="text" name="it_stock_qty[<?php echo $i; ?>]" value="<?php echo $row['it_stock_qty']; ?>" id="stock_qty_<?php echo $i; ?>" class="frm_input" size="10" autocomplete="off">
        </td> 
        <td class="td_num">
            <label for="noti_qty_<?php echo $i; ?>" class="sound_only">통보수량</label>
            <input type="text" name="it_noti_qty[<?php echo $i; ?>]" value="<?php echo $row['it_noti_qty']; ?>" id="noti_qty_<?php echo $i; ?>" class="frm_input" size="10" autocomplete="off">
        </td>-->
          <td class="td_chk2">
            <label for="use_<?php echo $i; ?>" class="sound_only">판매</label>
            <input type="checkbox" name="it_use[<?php echo $i; ?>]" value="1" id="use_<?php echo $i; ?>" <?php echo ($row['it_use'] ? "checked" : ""); ?>>
          </td>
          <td class="td_chk2">
            <label for="soldout_<?php echo $i; ?>" class="sound_only">품절</label>
            <input type="checkbox" name="it_soldout[<?php echo $i; ?>]" value="1" id="soldout_<?php echo $i; ?>" <?php echo ($row['it_soldout'] ? "checked" : ""); ?>>
          </td>
          <td class="td_chk2">
            <label for="stock_sms_<?php echo $i; ?>" class="sound_only">재입고 알림</label>
            <input type="checkbox" name="it_stock_sms[<?php echo $i; ?>]" value="1" id="stock_sms_<?php echo $i; ?>" <?php echo ($row['it_stock_sms'] ? "checked" : ""); ?>>
          </td>
          <td class="td_mng td_mng_s"><a href="./itemstockview.php?it_id=<?php echo $row['it_id']; ?>" class="btn btn_03">상세관리</a></td>
          <td class="td_mng td_mng_s"><a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>&amp;<?php echo $qstr; ?>" class="btn btn_03">수정</a></td>
          <?php
          $sum_ct_qty_1month = $row['sum_ct_qty_1month'] ?: 0;
          $sum_ct_qty_1day =  $row['sum_ct_qty_1day'] ?: 0;
          ?>
          <td class="td_mng"><?php echo "월 {$sum_ct_qty_1month}개 / 일 {$sum_ct_qty_1day}개" ?></td>
        </tr>
        <?php
        } // if warehouse selected
      }
      if (!$i)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table"><span>자료가 없습니다.</span></td></tr>';
      ?>
      </tbody>
    </table>
  </div>

  <div class="btn_fixed_top">
    <!-- <a href="./optionstocklist.php" class="btn btn_02">상품옵션재고</a> -->
    <a href="./itemsellrank.php"  class="btn btn_02">상품판매순위</a>
    <input type="submit" value="일괄수정" class="btn_submit btn">
    <a href="#" id="btn_stock_add" class="btn btn_submit">재고등록</a>
    <a href="#"  class="btn btn_02">재고일괄등록</a>
  </div>
</form>

<div id="loading_excel">
  <div class="loading_modal">
    <p>엑셀파일 다운로드 중입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
    <button onclick="cancelExcelDownload();" class="btn_cancel_excel">취소</button>
  </div>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<style>
  .popup_iframe {
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    z-index: 999;
    background-color: rgba(0, 0, 0, 0.6);
    display:none;
  }
  .popup_iframe > div {
    width: 1000px;
    max-width: 80%;
    height: 80%;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
  }
  .popup_iframe > div iframe {
    width:100%;
    height:100%;
    border: 0;
    background-color: #FFF;
  }
</style>
<div id="popup_order_add" class="popup_iframe">
  <div></div>
</div>

<div id="popup_excel_upload" class="popup_iframe">
  <div style="width: 500px;height: 50%;"></div>
</div>

<script>
  var EXCEL_DOWNLOADER = null;

  $(function () {
    $('#btn_stock_add').click(function (e) {
      e.preventDefault();

      $("#popup_order_add > div").html("<iframe src='./pop.stock.add.php'></iframe>");
      $("#popup_order_add iframe").load(function () {
        $("#popup_order_add").show();
        $('#hd').css('z-index', 3);
      });

    });

    $(document).on("click", "#order_add", function (e) {
      e.preventDefault();

      $("#popup_order_add > div").html("<iframe src='./pop.purchase.order.add.php'></iframe>");
      $("#popup_order_add iframe").load(function(){
        $("#popup_order_add").show();
        $('#hd').css('z-index', 3);
        $('#popup_order_add iframe').contents().find('.mb_id_flexdatalist').focus();
      });

    });
  });

  function downloadExcel() {
    var href = './itemstock.excel.download.php';

    $('#loading_excel').show();
    EXCEL_DOWNLOADER = $.fileDownload(href, {
      httpMethod: "POST",
      data: {wh_name:"<?=$_REQUEST['wh_name']?>"}
    })
      .always(function() {
        $('#loading_excel').hide();
      });
  }

  function excelUploadPopUp() {
    $("#popup_excel_upload > div").html("<iframe src='./pop.itemstock.excel.upload.php'></iframe>");
    $("#popup_excel_upload iframe").load(function(){
      $("#popup_excel_upload").show();
      $('#hd').css('z-index', 3);
    });
  }


  function cancelExcelDownload() {
    if (EXCEL_DOWNLOADER != null) {
      EXCEL_DOWNLOADER.abort();
    }
    $('#loading_excel').hide();
  }

  function openPopSmartPurchase() {
    var popupWidth = 1100;
    var popupHeight = 700;

    var popupX = (window.screen.width / 2) - (popupWidth / 2);
    var popupY = (window.screen.height / 2) - (popupHeight / 2);;

    release_purchaseorderview_pop = window.open("./popup.smart_purchaseorder.php", "스마트 발주", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX);
  }

</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
