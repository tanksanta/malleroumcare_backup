<?php
$sub_menu = '400620';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$doc = strip_tags($doc);
$sort1 = in_array($sort1, array('it_id', 'it_name', 'it_stock_qty', 'it_use', 'it_soldout', 'it_stock_sms')) ? $sort1 : '';
$sort2 = in_array($sort2, array('desc', 'asc')) ? $sort2 : 'desc';
$sel_ca_id = get_search_string($sel_ca_id);
$sel_field = get_search_string($sel_field);
$search = get_search_string($search);
$wh_name = get_search_string($wh_name);

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
  $sql_search .= " and ( select sum(ws_qty) from warehouse_stock where wh_name = '$wh_name' ) > 0 ";
}

if ($sel_field == "")  $sel_field = "it_name";
if ($sort1 == "") $sort1 = "it_stock_qty";
if ($sort2 == "") $sort2 = "asc";

$sql_common = "  from {$g5['g5_shop_item_table']} ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// APMS - 2014.07.20
$sql  = "
  select
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
    it_expected_warehousing_date
  $sql_common
  order by $sort1 $sort2
  limit $from_record, $rows
";
$result = sql_query($sql);

$colspan = 11;

$warehouse_list = get_warehouses();
foreach($warehouse_list as &$warehouse) {
  $sql = " select sum(ws_qty) as total from warehouse_stock where wh_name = '$warehouse' ";
  $result_total = sql_fetch($sql);

  $warehouse = [
    'name' => $warehouse,
    'total' => $result_total['total'] ?: 0
  ];

  $colspan++;
}
unset($warehouse);

$qstr1 = 'sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search.'&amp;wh_name='.$wh_name;
$qstr = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

?>

<script src="<?php echo G5_ADMIN_URL;?>/apms_admin/apms.admin.js"></script>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">전체 상품</span><span class="ov_num">  <?php echo $total_count; ?>개</span></span>
</div>

<form name="flist" class="local_sch01 local_sch">
<input type="hidden" name="doc" value="<?php echo $doc; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<div class="quick_link_area">
  <?php foreach($warehouse_list as $warehouse) { ?>
    <a href="<?php echo $_SERVER['SCRIPT_NAME'].'?wh_name='.$warehouse['name']; ?>"><?php echo $warehouse['name']; ?>(<?php echo $warehouse['total']; ?>개)</a>
  <?php } ?>
</div>


<label for="sel_ca_id" class="sound_only">분류선택</label>
<select name="sel_ca_id" id="sel_ca_id">
    <option value=''>전체분류</option>
    <?php
    $sql1 = " select ca_id, ca_name, as_line from {$g5['g5_shop_category_table']} order by ca_order, ca_id ";
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

<div class="local_desc01 local_desc">
    <p>재고수정의 수치를 수정하시면 창고재고의 수치가 변경됩니다.</p>
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
        <th scope="col"><a href="<?php echo title_sort("it_stock_qty") . "&amp;$qstr1"; ?>">창고재고</a></th>
        <?php foreach($warehouse_list as $warehouse) { ?>
        <th scope="col"><?=$warehouse['name']?></th>
        <?php } ?>
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

    ?>
    <tr class="<?php echo $bg; ?>">
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
        <td class="td_num<?php echo $it_stock_qty_st; ?>"><?php echo $it_stock_qty; ?></td>
        <?php
        foreach($warehouse_list as $warehouse) {
          $sql = " select sum(ws_qty) as stock from warehouse_stock where wh_name = '{$warehouse['name']}' and it_id = '{$row['it_id']}' ";
          $stock = sql_fetch($sql)['stock'] ?: 0;
          echo '<td class="td_num">'.number_format($stock).'</td>';
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
        <td class="td_mng td_mng_s"><a href="#" class="btn btn_03">상세관리</a></td>
        <td class="td_mng td_mng_s"><a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>&amp;<?php echo $qstr; ?>" class="btn btn_03">수정</a></td>
    </tr>
    <?php
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

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<style>
#popup_order_add {
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index: 999;
  background-color: rgba(0, 0, 0, 0.6);
  display:none;
}
#popup_order_add > div {
  width: 1000px;
  max-width: 80%;
  height: 80%;
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
}
#popup_order_add > div iframe {
  width:100%;
  height:100%;
  border: 0;
  background-color: #FFF;
}
</style>
<div id="popup_order_add">
  <div></div>
</div>

<script>
$(function() {
    $('#btn_stock_add').click(function(e) {
        e.preventDefault();

        $("#popup_order_add > div").html("<iframe src='./pop.stock.add.php'></iframe>");
        $("#popup_order_add iframe").load(function(){
            $("#popup_order_add").show();
            $('#hd').css('z-index', 3);
        });

    });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
