<?php
$sub_menu = '400405';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");
add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);

$g5['title'] = '직배송 주문관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

////////////////////////////////////////////////////////////////////////////////////////////////////
if($auth_check = auth_check($auth[$sub_menu], "r"))
// 초기 3개월 범위 적용
$fr_date = $_REQUEST["fr_date"];
$to_date = $_REQUEST["to_date"];
if ($fr_date == "" && $to_date == "") {
    $fr_date = date("Y-m-d", strtotime("-60 day"));
    $to_date = date("Y-m-d");
}
$qstr .= '&amp;page_rows='.$page_rows;
$click_status = ($click_status == "")?"준비": $click_status;

$sql = "SELECT COUNT(CASE WHEN ct_status='준비' THEN 1 END) AS count1, 
COUNT(CASE WHEN ct_status='출고준비' THEN 1 END) AS count2,
COUNT(CASE WHEN ct_status='배송' THEN 1  END) AS count3 
FROM g5_shop_cart c
LEFT JOIN g5_shop_order o ON c.od_id = o.od_id
WHERE ct_is_direct_delivery = '1'
AND od_del_yn = 'N'";
$row = sql_fetch($sql,true);

$count1 = $row["count1"];//상품준비count
$count2 = $row["count2"];//출고준비count
$count3 = $row["count3"];//출고완료(배송완료포함)count



$where = array();
$where[] = "ct_is_direct_delivery = '1'";//직배항목만

$replace_table = array(
    'od_id' => 'c.od_id',
    'it_name' => 'c.it_name',
    'mb_id' => 'c.mb_id'
);

$search_it_name = get_search_string($search_it_name);//상품명 검색
$search_b_name = get_search_string($search_b_name);//수령인명 검색
$search_b_addr = get_search_string($search_b_addr);//배송주소 검색
$search_partner = get_search_string($search_partner);//파트너 ID 검색
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';


if ($search_it_name != "") {//상품명 검색
  $search_it_name = trim($search_it_name);
  $where[] = " i.it_name like '%$search_it_name%' ";
  $qstr .="&amp;search_it_name=".$_REQUEST["search_it_name"];
}

if ($search_b_name != "") {//수령인명 검색
  $search_b_name = trim($search_b_name);
  $where[] = " od_b_name like '%$search_b_name%' ";
  $qstr .="&amp;search_b_name=".$_REQUEST["search_b_name"];
}

if ($search_b_addr != "") {//배송주소 검색
  $search_b_addr = trim($search_b_addr);
  $where[] = " (od_b_addr1 like '%$search_b_addr%' or od_b_addr2 like '%$search_b_addr%' or od_b_addr3 like '%$search_b_addr%') ";
  $qstr .="&amp;search_b_addr=".$_REQUEST["search_b_addr"];
}

if ($search_partner != "") {//파트너 ID 검색
  $search_partner = trim($search_partner);
  if($search_partner == "미등록"){
	$where[] = " ct_direct_delivery_partner = '' ";	
  }else{
	$where[] = " ct_direct_delivery_partner like '$search_partner' ";	
  }
  $qstr .="&amp;search_partner=".$_REQUEST["search_partner"];  
}

if($_REQUEST["it_deadline"] != ""){//마감시간
	switch($_REQUEST["it_deadline"]){
		case 1: $where[] .= " i.it_deadline between '09:00:00' and '09:59:59' "; break;
		case 2: $where[] .= " i.it_deadline between '10:00:00' and '10:59:59' "; break;
		case 3: $where[] .= " i.it_deadline between '11:00:00' and '11:59:59' "; break;
		case 4: $where[] .= " i.it_deadline between '12:00:00' and '12:59:59' "; break;
		case 5: $where[] .= " i.it_deadline between '13:00:00' and '13:59:59' "; break;
		case 6: $where[] .= " i.it_deadline between '14:00:00' and '14:59:59' "; break;
		case 7: $where[] .= " i.it_deadline between '15:00:00' and '15:59:59' "; break;
		case 8: $where[] .= " i.it_deadline between '16:00:00' and '16:59:59' "; break;
		case 9: $where[] .= " i.it_deadline between '17:00:00' and '17:59:59' "; break;
		case 10: $where[] .= " (i.it_deadline between '18:00:00' and '23:59:59' or i.it_deadline between '00:00:00' and '08:59:59') "; break;
		default: $where[] .= " i.it_deadline between '09:00:00' and '09:59:59' "; break;
	}
	$qstr .="&amp;it_deadline=".$_REQUEST["it_deadline"];
}




// 바코드 입력완료, 미입력
if (gettype($ct_barcode_saved) == 'string' && $ct_barcode_saved !== '') {
  if ($ct_barcode_saved == 'saved')
    $where[] = " ( ct_barcode_insert = ct_qty or ct_barcode_insert > ct_qty or substring(ca_id,1,2) = '70') ";
  else if ($ct_barcode_saved == 'none')
    $where[] = " ( ct_barcode_insert = 0 OR ct_barcode_insert ='') and substring(ca_id,1,2) != '70' ";
  $qstr .= "&amp;ct_barcode_saved=".$ct_barcode_saved;
}

// 배송정보 입력완료, 미입력
if (gettype($ct_delivery_saved) == 'string' && $ct_delivery_saved !== '') {
  if ($ct_delivery_saved == 'saved')
    $where[] = " ( CHAR_LENGTH(ct_delivery_num) > 6 ) ";
  else if ($ct_delivery_saved == 'none')
    $where[] = " ( ct_delivery_num IS NULL OR ct_delivery_num = '' ) ";
  $qstr .= "&amp;ct_delivery_saved=".$ct_delivery_saved;
}

// 급여, 비급여
if (gettype($gubun) == 'string' && $gubun !== '') {
  if ($gubun == '10')
    $where[] = " ( substring(ca_id,1,2) = '10' or substring(ca_id,1,2) = '20' ) ";
  else if ($gubun == '70')
    $where[] = " ( substring(ca_id,1,2) = '70' ) ";
  $qstr .= "&amp;gubun=".$gubun;
}

// 위탁엑셀 다운, 미다운
if (gettype($ct_is_delivery_excel_downloaded) == 'string' && $ct_is_delivery_excel_downloaded !== '') {
  if ($ct_is_delivery_excel_downloaded == 'saved')
    $where[] = " ( ct_is_delivery_excel_downloaded = '1' ) ";
  else if ($ct_is_delivery_excel_downloaded == 'none')
    $where[] = " ( ct_is_delivery_excel_downloaded = '0' ) ";
  $qstr .= "&amp;ct_is_delivery_excel_downloaded=".$ct_is_delivery_excel_downloaded;
}

//////////////////

if ($fr_date && $to_date) {
  $where[] = " (ct_time between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
  $qstr .= "&amp;fr_date=".$fr_date."&amp;to_date=".$to_date;
}

$where[] = " od_del_yn = 'N' ";

// 최고관리자가 아닐때
//if ( $ct_status == '작성' && $is_admin != 'super' ) {
  //$where[] = " od_writer = '{$member['mb_id']}' ";
//}

$where_count = $where;

if ($click_status) {//상품상태
  $where[] = " ct_status = '{$click_status}'";  
  $qstr .= "&amp;click_status=".$click_status;
} 

$where[] = " (m.mb_intercept_date = '' OR m.mb_intercept_date IS NULL) ";

$sql_search = '';
if ($where) {
  $sql_search = ' where '.implode(' and ', $where);
}

$sql_count_search = '';
if ($where_count) {
  $sql_count_search = ' where '.implode(' and ', $where_count);
}

// shop_cart 조인으로 수정
// member 테이블 조인
$sql_common = "
  FROM
    {$g5['g5_shop_cart_table']} c
  LEFT JOIN
    {$g5['g5_shop_item_table']} i ON c.it_id = i.it_id
  LEFT JOIN
    {$g5['g5_shop_order_table']} o ON c.od_id = o.od_id
  LEFT JOIN
    {$g5['member_table']} m ON c.mb_id = m.mb_id
  LEFT JOIN 
    {$g5['member_table']} m2 ON c.ct_direct_delivery_partner = m2.mb_id
  LEFT JOIN
    partner_install_report pir ON c.od_id = pir.od_id
  LEFT JOIN
    g5_shop_order_cancel_request ocr ON c.od_id = ocr.od_id
";

$sql_counts = "
  SELECT
    count(*) as cnt,
    ct_status,
    sum(
      case
        when io_type = 0
        then ct_price + io_price
        else ct_price
      end * ct_qty
    ) as ct_price,
    sum(ct_sendcost) as ct_sendcost,
    sum(ct_discount) as ct_discount
  {$sql_common}
  {$sql_count_search}
  GROUP BY
    ct_status
";
$result_counts = sql_query($sql_counts);
$cate_counts = [];
$total_info = [];
while($count = sql_fetch_array($result_counts)) {
  $cate_counts[$count['ct_status']] = $count['cnt'];
  $total_info[$count['ct_status']] = $count;
}

$sql_common .= $sql_search;

// 페이지네이트
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql, true);
$total_count = $row['cnt'];
$page_rows = (int)$page_rows ? (int)$page_rows : "100";
$rows = $page_rows;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if($_REQUEST["orb"] == ""){
	if($click_status == "준비"){
		$_REQUEST["orb"] = "deadline_it";//마감-상품명
	}elseif($click_status == "출고준비"){
		$_REQUEST["orb"] = "od_id";//주문번호
	}elseif($click_status == "배송"){
		$_REQUEST["orb"] = "out_time_partner";//출고일-파트너명
	}
}

if($_REQUEST["orb"] == "od_id"){//주문번호
	$sql_order = " ORDER BY o.od_id DESC ";
}elseif($_REQUEST["orb"] == "deadline_partner"){//마감-파트너명
	$sql_order = " ORDER BY IF(time_dead>0, 1, 2) ASC, time_dead ASC,
	CASE
    WHEN partner_name IS NULL THEN '2'
    WHEN partner_name = '' THEN '1'
    ELSE '0'	
	END,partner_name ASC ";
}elseif($_REQUEST["orb"] == "deadline_it"){//마감-상품명
	$sql_order = " ORDER BY IF(time_dead>0, 1, 2) ASC, time_dead ASC,
	i.it_name ASC ";
}elseif($_REQUEST["orb"] == "partner_it"){//파트너명-상품명
	$sql_order = " ORDER BY CASE
    WHEN partner_name IS NULL THEN '2'
    WHEN partner_name = '' THEN '1'
    ELSE '0'	
END,partner_name ASC, i.it_name ASC ";
}elseif($_REQUEST["orb"] == "out_time_partner"){//출고일-파트너명
	$sql_order = " ORDER BY ct_ex_date DESC,CASE
    WHEN partner_name IS NULL THEN '2'
    WHEN partner_name = '' THEN '1'
    ELSE '0'	
END,partner_name ASC ";
}
$qstr .= "&amp;orb=".$_REQUEST["orb"];
$sql_common .= $sql_order;

$sql  = "
  select *, o.od_id as od_id, c.ct_id as ct_id, c.mb_id as mb_id,m2.mb_name AS partner_name, (od_cart_coupon + od_coupon + od_send_coupon) as couponprice,
  TIMEDIFF(it_deadline,DATE_FORMAT(NOW(), '%H:%i:%s')) AS time_dead 
  $sql_common
  limit $from_record, $rows
";
if ($click_status || $od_status) {
  if ($show_all == 'Y' && ($click_status == "준비" || $click_status == "출고준비" || $od_status == '준비' || $od_status == '출고준비')) {
    $sql = preg_replace('/limit (.*)/i', '', $sql);
  }
}
$result = sql_query($sql);
//echo $sql;
$orderlist = array();
while( $row = sql_fetch_array($result) ) {
  $orderlist[] = $row;
}

?>
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
  .popup_box2 {
		display: none;
		position: fixed;
		width: 100%;
		height: 100%;
		left: 0;
		top: 0;
		z-index: 9999;
		background: rgba(0, 0, 0, 0.8);		
	}

	.popup_box_con {
		padding:20px;
		position: relative;
		background: #ffffff;
		z-index: 99999;
		margin-left:-206px;
	}
	.newbutton2{
		font-size: 12px;
		height: 33px;
		padding: 0 10px;
		cursor: pointer;
		outline: none;
		box-sizing: border-box;
		border: 1px solid #ddd;
	}
	.newbutton3{
		font-size: 12px;
		height: 33px;
		padding: 0 10px;
		cursor: pointer;
		outline: none;
		box-sizing: border-box;
		border: 1px solid #000;
		color: #fff;
		background-color:#000;
	}
	.newbutton4{
		font-size: 12px;
		height: 33px;
		padding: 0 10px;
		cursor: pointer;
		outline: none;
		box-sizing: border-box;
		border: 1px solid #0033ff;
		color: #0033ff;
		background-color:#fff;
	}
	.bg0 {background:#fff}
	.bg1 {background:#f2f5f9}
	.bg1 td {border-color:#e9e9e9}
</style>
<form name="frmsamhwaorderlist" id="frmsamhwaorderlist" style="margin-top:-15px;">
<input type="hidden" name="page_rows" id="page_rows" value="<?=$page_rows?>">
  <div class="tbl_wrap" style="margin-bottom:-25px;margin-top:30px;">
		<input type="button" value="상품준비(<?=number_format($count1)?>)" class="<?=($click_status == "준비")?"newbutton3":"newbutton2";?>" onClick="$('#click_status').val('준비');move_staus();" id="click_status1"/>
		<input type="button" value="출고준비(<?=number_format($count2)?>)" class="<?=($click_status == "출고준비")?"newbutton3":"newbutton2";?>" onClick="$('#click_status').val('출고준비');move_staus();"id="click_status2"/>
        <input type="button" value="출고완료(<?=number_format($count3)?>)" class="<?=($click_status == "배송")?"newbutton3":"newbutton2";?>" onClick="$('#click_status').val('배송');move_staus();"id="click_status3"/>
		<input type="hidden" name="click_status" id="click_status" value="<?=$click_status?>">
  </div>
  <div class="new_form">
    <table class="new_form_table" id="search_detail_table">
      <tr>
        <th>정렬 기준</th>
        <td>
          <input type="radio" name="orb" id="od_id1" value="od_id" <?=($_REQUEST["orb"] == "od_id")?"checked":"";?>> <label for='od_id1'>주문번호</label>		  
		  <input type="radio" name="orb" id="deadline_partner" value="deadline_partner" <?=($_REQUEST["orb"] == "deadline_partner")?"checked":"";?>> <label for='deadline_partner'>마감시간-파트너명</label>
		  <input type="radio" name="orb" id="deadline_it" value="deadline_it" <?=($_REQUEST["orb"] == "deadline_it" || $_REQUEST["orb"] == "")?"checked":"";?>> <label for='deadline_it'>마감시간-상품명</label> 
		  <input type="radio" name="orb" id="partner_it" value="partner_it" <?=($_REQUEST["orb"] == "partner_it")?"checked":"";?>> <label for='partner_it'>파트너명-상품명</label>
		  <input type="radio" name="orb" id="out_time_partner" value="out_time_partner" <?=($_REQUEST["orb"] == "out_time_partner")?"checked":"";?>> <label for='out_time_partner'>출고일-파트너명</label>
		  
        </td>
      </tr>
	  <tr>
        <th>검색조건</th>
        <td >
			바코드 입력여부&nbsp;&nbsp;
            <select name="ct_barcode_saved" id="ct_barcode_saved">
            <option value="" >전체</option>
            <option value="none" <?php echo get_selected($ct_barcode_saved, 'none'); ?>>미입력</option>
            <option value="saved" <?php echo get_selected($ct_barcode_saved, 'saved'); ?>>입력완료</option>
			</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		  
			배송정보입력&nbsp;&nbsp;
            <select name="ct_delivery_saved" id="ct_delivery_saved">
            <option value="" >전체</option>
            <option value="none" <?php echo get_selected($ct_delivery_saved, 'none'); ?>>미입력</option>
            <option value="saved" <?php echo get_selected($ct_delivery_saved, 'saved'); ?>>입력완료</option>
			</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			급여구분&nbsp;&nbsp;
            <select name="gubun" id="gubun">
            <option value="">전체</option>
            <option value="10" <?php echo get_selected($gubun, '10'); ?>>급여</option>
            <option value="70" <?php echo get_selected($gubun, '70'); ?>>비급여</option>
			</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
			위탁엑셀다운로드&nbsp;&nbsp;
            <select name="ct_is_delivery_excel_downloaded" id="ct_is_delivery_excel_downloaded">
            <option value="" >전체</option>
            <option value="saved" <?php echo get_selected($ct_is_delivery_excel_downloaded, 'saved'); ?>>다운완료</option>
            <option value="none" <?php echo get_selected($ct_is_delivery_excel_downloaded, 'none'); ?>>미다운</option>
			</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			마감시간&nbsp;&nbsp;
            <select name="it_deadline" id="it_deadline" style="width:125px;">
            <option value="" >전체</option>
            <option value="1" <?php echo get_selected($it_deadline, '1'); ?>>09:00~10:00</option>
            <option value="2" <?php echo get_selected($it_deadline, '2'); ?>>10:00~11:00</option>
            <option value="3" <?php echo get_selected($it_deadline, '3'); ?>>11:00~12:00</option>
            <option value="4" <?php echo get_selected($it_deadline, '4'); ?>>12:00~13:00</option>
			<option value="5" <?php echo get_selected($it_deadline, '5'); ?>>13:00~14:00</option>
			<option value="6" <?php echo get_selected($it_deadline, '6'); ?>>14:00~15:00</option>
			<option value="7" <?php echo get_selected($it_deadline, '7'); ?>>15:00~16:00</option>
			<option value="8" <?php echo get_selected($it_deadline, '8'); ?>>16:00~17:00</option>
			<option value="9" <?php echo get_selected($it_deadline, '9'); ?>>17:00~18:00</option>
			<option value="10" <?php echo get_selected($it_deadline, '10'); ?>>기타/시간미등록</option>
			</select>
        </td>
      </tr>
	  <tr>
        <th>검색기간</th>
        <td>
          <div class="sel_field">
			<input type="button" value="오늘" id="select_date_today" name="select_date" class="select_date newbutton"/>
            <input type="button" value="어제" id="select_date_yesterday" name="select_date" class="select_date newbutton"/>
            <input type="button" value="일주일" id="select_date_sevendays" name="select_date" class="select_date newbutton"/>
            <input type="button" value="이번달" id="select_date_thismonth" name="select_date" class="select_date newbutton"/>
            <input type="button" value="지난달" id="select_date_lastmonth" name="select_date" class="select_date newbutton"/>    
			<input type="button" value="전체" id="select_date_all" name="select_date" class="select_date newbutton4"/>
            <input type="text" id="fr_date" class="date" name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete='off' readonly> ~
            <input type="text" id="to_date" class="date" name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete='off' readonly>
          </div>
        </td>
      </tr>
      <tr>
        <th>키워드 검색</th>
        <td>
			파트너ID&nbsp;&nbsp;
			<input type="text" name="search_partner" value="<?php echo $search_partner; ?>" id="search_partner" class="frm_input" autocomplete="off" style="width:200px;" placeholder="파트너ID로 검색"> <input type="button" value="파트너검색" class="newbutton" style="background-color:#000000;color:#ffffff;" onClick="partner_search()">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			상품명&nbsp;&nbsp;
			<input type="text" name="search_it_name" value="<?php echo $search_it_name; ?>" id="search_it_name" class="frm_input" autocomplete="off" style="width:150px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			수령인명&nbsp;&nbsp;
			<input type="text" name="search_b_name" value="<?php echo $search_b_name; ?>" id="search_b_name" class="frm_input" autocomplete="off" style="width:150px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			배송주소&nbsp;&nbsp;
			<input type="text" name="search_b_addr" value="<?php echo $search_b_addr; ?>" id="search_b_addr" class="frm_input" autocomplete="off" style="width:300px;">&nbsp;&nbsp;
			<input type="submit" value="검색" class="newbutton" style="background-color:#000000;color:#ffffff;width:70px;">
        </td>
      </tr>
	  
    </table>
  </div>
</form>

<div style="margin:0px 0px 5px 20px; float:left">
	<!--검색 개수 : <?php echo $total_count; ?> 건 -->
	<input type="button" value="전체선택" id="all_chk2" name="all_chk2" class="newbutton2"/>
<?php if($click_status == "준비"){
		echo '<input type="button" value="선택 출고준비로 변경 ▶" id="" name="" class="newbutton2" onClick="return change_step_go(\'출고준비\')"/>';
	}elseif($click_status == "출고준비"){
		echo '<input type="button" value="선택 출고완료로 변경 ▶" id="" name="" class="newbutton2" onClick="return change_step_go(\'배송\')"/>
		<input type="button" value="◀ 선택 상품준비로 되돌리기" id="" name="" class="newbutton2" onClick="return change_step_go(\'준비\')"/>
		<input type="button" value="선택 바코드 정보 입력" id="" name="" class="newbutton2" style="background-color:#000;color:#fff;" onClick="barcode_insert(\'\');"/>';
	}elseif($click_status == "배송"){//출고완료
		echo '<input type="button" value="◀ 선택 출고준비로 되돌리기" id="" name="" class="newbutton2" onClick="return change_step_go(\'출고준비\')"/>';
	}
	if($click_status == "준비" || $click_status == "출고준비"){
		echo ' <input type="button" value="선택 위탁 엑셀다운로드" id="" name="" class="newbutton2" style="background:#339900;color:#fff;" onclick="direct_delivery_excel();"/>';
	}
?>
</div>
<div style="margin:0px 20px 0px 0px; float:right;right:0px;">
	<select name="page_rows" id="page_rows2" onChange="javascript:$('#page_rows').val(this.value);$('#frmsamhwaorderlist').submit();" style="width:130px;height:33px;">
		<option value="50"  <?=($page_rows =='50')?"selected":"";?>>50개씩보기</option>
        <option value="100" <?=($page_rows=='100')?"selected":"";?>>100개씩보기</option>
		<option value="300" <?=($page_rows=='300')?"selected":"";?>>300개씩보기</option>
        <option value="500" <?=($page_rows=='500')?"selected":"";?>>500개씩보기</option>
    </select>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <thead>
    <tr>
        <th scope="col" width="10px;"><input type="checkbox" name="all_chk" id="all_chk" class="frm_input"></th>
		<th scope="col" width="107px;">주문번호</th>
        <th scope="col" width="110px;">직배송 파트너</th>
		<th scope="col" width="120px;">수령인</th>
        <th scope="col" width="100px;">수령인 연락처</th>
		<th scope="col">배송주소</th>
        <th scope="col" width="50px;">급여<br>구분</th>
		<th scope="col" width="170px;">상품명</th>
		<th scope="col" width="60px;">바코드<br>/수량</th>
		<th scope="col" width="60px;">배송정보</th>
		<th scope="col" width="60px;">단가</th>
		<th scope="col" width="70px;">공급가격</th>
		<th scope="col" width="60px;">부가세</th>
		<th scope="col" width="70px;">총액</th>
		<th scope="col" width="75px;">마감시간</th>
		<th scope="col" width="5px;">요청사항</th>
		<th scope="col" width="75px;">주문일</th>
		<th scope="col" width="75px;">출고일</th>
		<th scope="col" width="70px;">위탁엑셀다운</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach($orderlist as $order) {
        $num = $total_count -(($page-1)*$page_rows)- $i ;

        $bg = 'bg'.($i%2);
		
		//$mb = get_member($order['ct_direct_delivery_partner']);
		$ct_direct_delivery_partner_name = ($order['partner_name'] == "")?"미등록": $order['partner_name'];//파트너
		if(!$order['ct_barcode_insert']) {//등록 바코드 수량
			$order['ct_barcode_insert'] = 0;
		}
		$opt_price = 0;

		if($order['io_type'])
		  $opt_price = $order['io_price'];
		else
		  $opt_price = $order['ct_price'] + $order['io_price'];

		$order["opt_price"] = $opt_price;

		// 소계
		$order['ct_price_stotal'] = $opt_price * $order['ct_qty'] - $order['ct_discount'];
		if($order["prodSupYn"] == "Y") {
		  $order["ct_price_stotal"] -= ($order["ct_stock_qty"] * $opt_price);
		}
		// 단가 역산
		$order["opt_price"] = $order['ct_price_stotal'] ? @round($order['ct_price_stotal'] / ($order["ct_qty"] - $order["ct_stock_qty"])) : 0;

		// 공급가액
		$order["basic_price"] = $order['ct_price_stotal'];
		// 부가세
		$order["tax_price"] = 0;
		if($order['it_taxInfo'] != "영세" ) {
		  // 공급가액
		  $order["basic_price"] = round($order['ct_price_stotal'] / 1.1);
		  // 부가세
		  $order["tax_price"] = round($order['ct_price_stotal'] / 11);
		}
		$direct_delivery_text = ($order['ct_is_delivery_excel_downloaded'] == 1)?"다운완료":"-";//위탁엑셀다운로드완료
		$order['od_memo'];
		$order['prodMemo'];
		$memo = ($order['od_memo'] !="" || $order['prodMemo'] != "")?"<a href=\"javascript:;\" onClick=\"go_view('".$order["od_id"]."','".$order["it_name"]."','".$order['od_memo']."','".$order['prodMemo']."')\">보기</a>":"-";//요청사항보기
		if ($cancel_order_table[$order['od_id']]) {
			$is_order_cancel_requested = "cancel_requested";
		  }
    ?>
    <tr class="<?php echo $bg; ?>">
        <td align="center"><input type="checkbox" name="od_id[]" value="<?=$order["ct_id"];?>" class="frm_input checkSelect"></td>
		<td align="center"><a href="samhwa_orderform.php?od_id=<?=$order["od_id"];?>&sub_menu=400405" target="_blank"><?=$order["od_id"];//주문번호 ?></a></td>
        <td align="center"><?=$ct_direct_delivery_partner_name;//직배송파트너?></td>
		<td align="center"><?=$order["od_b_name"];//수령인 ?></a></td>
		<td align="center"><?=$order["od_b_tel"];//연락처 ?></td>
		<td align="center"><?=$order["od_b_addr1"].(($order["od_b_addr2"]!="")?" ".$order["od_b_addr2"]:"").(($order["od_b_addr3"]!="")?" ".$order["od_b_addr3"]:"");//배송주소 ?></td>
   		<td align="center"><?=(substr($order["ca_id"],0,2) == "70")?"비급여":"급여";//급여구분 ?></td>
		<td align="center"><?=$order["it_name"].(($order["ct_option"] != $order["it_name"])?" [".$order["ct_option"]."]":"");//상품명 ?></td>
		<td align="center" <?=($order['ct_barcode_insert'] >= $order['ct_qty'] || substr($order["ca_id"],0,2) == "70")?"":"style='color:red;'"; ?>><span  style='cursor:pointer;' onClick="barcode_insert('<?=$order["ct_id"]?>')"><?=(substr($order["ca_id"],0,2) != "70")?$order['ct_barcode_insert']."/".$order['ct_qty']:$order['ct_qty'];//바코드/수량 ?></span></td>
		<td align="center" <?=($order['ct_combine_ct_id']||$order['ct_delivery_num'])?"":"style='color:red;'";?>><?=($order['ct_combine_ct_id']||$order['ct_delivery_num'])?"입력완료":"미입력";//배송정보 ?></td>
		<td align="right"><?=number_format($order["opt_price"]);//단가 ?></td>
		<td align="right"><?=number_format($order["basic_price"]);//공급가격?></td>
		<td align="right"><?=number_format($order["tax_price"]);//부가세 ?></td>
		<td align="right"><?=number_format($order["ct_price_stotal"]);//총액 ?></td>
		<td align="center"><?=($order["it_deadline"] == "00:00:00" || $order["it_type11"] == "0")?"-":$order["it_deadline"];//마감시간 ?></td>
		<td align="center"><?=$memo;//요청사항 ?></td>
		<td align="center"><?=substr($order['od_time'],0,10);//주문일 ?></td>
		<td align="center"><?=($order["ct_ex_date"]=="" || $order["ct_ex_date"]=="0000-00-00")?"-":$order["ct_ex_date"];//출고일 ?></td>
		<td align="center"><?=$direct_delivery_text?></td>
    </tr>
    <?php
    $i++;
	}

    if ($i == 0) {
        echo '<tr><td colspan="19" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div id="loading_excel">
  <div class="loading_modal">
    <p>엑셀파일 다운로드 중입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
    <button onclick="cancelExcelDownload();" class="btn_cancel_excel">취소</button>
  </div>
</div>
<?php //요청사항 확인 모달팝업 ?>
<div id="popup_box3" class="popup_box2">
    <div id="" class="popup_box_con" style="height:360px;margin-top:-180px;margin-left:-225px;width:450px;left:50%;top:50%;">
		<div style="top:0px;width:100%;">
		<span style="float:right;cursor:pointer;margin-top:-15px;margin-right:-15px;" onClick="info_close()" title="돌아가기" >Ⅹ</span>
		</div>
		<div class="form-group" style="background-color:#eeeeee;border-radius:5px;padding:10px;">
            <ul>
				<li>
					<span style="line-height:18px;">주문번호 </span>
					<span id="view_od_id" style="width:335px;float:right;line-height:18px;"></span>
				</li>
				<li>
					<span style="line-height:18px;">상품명 </span>
					<span id="view_it_name" style="width:335px;float:right;line-height:18px;"></span>
				</li>
            </ul>			
        </div>
		<div class="form-group" style="margin-top:20px;">
            <ul>
				<li><b>배송요청 사항</b></li>
				<li><textarea id="view_od_memo" rows="" cols="" readonly></textarea><br><br></li>
				<li><b>상품요청 사항</b></li>
				<li><textarea id="view_prodMemo" rows="" cols="" readonly></textarea></li>
            </ul>			
        </div>		

		<div style="text-align:right;bottom:0px;width:100%;margin-top:5px;">
			<button type="button" class="btn btn-black btn-sm btn_close" onClick="info_close()">돌아가기</button>
		</div>
	</div>
	
</div>
<?php //파터너 선택 모달팝업 ?>
<div id="popup_box4" class="popup_box2">    
	<div id="" class="popup_box_con" style="height:600px;margin-top:-300px;margin-left:-25%;width:50%;left:50%;top:50%;padding:0px;">
		<div style="text-align:left;top:0px;width:100%;background-color:#000000;cursor:pointer;padding:10px;float:left;">
		<span style="color:#ffffff;font-size:17px;float:left;">직배송 파트너 선택</span>
		<span style="color:#ffffff;font-size:17px;float:right;cursor:pointer;" onClick="info_close2()" >Ⅹ</span>
		</div>
		<div id="" style="float:left;width:100%;">
		<iframe id="partner_frame" src="/adm/shop_admin/popup.direct_delivery_partner.php" scrolling="yes" frameborder="0" allowTransparency="false" style="width:100%;height:645px;"></iframe>		
		</div>		
	</div>	
</div>
<?php //바코드 입력 모달팝업 ?>
<div id="popup_box5" class="popup_box2">    
	<div id="" class="popup_box_con" style="height:500px;margin-top:-240px;margin-left:-30%;width:60%;left:50%;top:50%;padding:0px;">
		<div style="text-align:left;top:0px;width:100%;background-color:#000000;padding:10px;float:left;">
		<span style="color:#ffffff;font-size:17px;float:left;">바코드 입력</span>
		<span style="color:#ffffff;font-size:17px;float:right;cursor:pointer;" onClick="info_close3()" >Ⅹ</span>
		</div>
		<div id="" style="float:left;width:100%;">
		<form method="post" action="/adm/shop_admin/popup.barcode_insert.php" name="barcode_form" target='barcode_frame'>
			<input type="hidden" name="barcode_ct_id" id="barcode_ct_id" value="">
		</form>
		<iframe id="barcode_frame" name="barcode_frame" src="" scrolling="yes" frameborder="0" allowTransparency="false" style="width:100%;height:500px;"></iframe>		
		</div>
		<div style="text-align:center;width:100%;background-color:#ffffff;padding:10px;">
		<a href="javascript:info_close3();" class="btn " style="background:#dddddd;border-radius:3px;width:70px;">닫기</a>
		</div>
	</div>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
<div class="btn_fixed_top">
    <a href="javascript:downloadExcel();" class="btn " style="background:#339900;color:#fff;border-radius: 3px;">엑셀다운로드</a>
</div>

<script>
$(function() {
	var EXCEL_DOWNLOADER = null;
    $("#fr_date, #to_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        maxDate: "+0d"
    });

	// 기간 - 전체 버튼
  $('#select_date_all').click(function() {
    $('#to_date').val("");
    $('#fr_date').val("");
  });	
	// 기간 - 오늘 버튼
  $('#select_date_today').click(function() {
    var today = new Date(); // 오늘
    $('#to_date').val(formatDate(today));
    $('#fr_date').val(formatDate(today));
  });
  // 기간 - 어제 버튼
  $('#select_date_yesterday').click(function() {
    var today = new Date(); // 오늘
	var yesterday = new Date(today.setDate(today.getDate()-1)); // 어제
    $('#to_date').val(formatDate(yesterday));
    $('#fr_date').val(formatDate(yesterday));
  });
  // 기간 - 일주일 버튼
  $('#select_date_sevendays').click(function() {
    var today = new Date(); // 오늘	
    $('#to_date').val(formatDate(today));
	var sevendays = new Date(today.setDate(today.getDate()-7)); // 일주일
    $('#fr_date').val(formatDate(sevendays));
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

	$("#all_chk").click(function() {
		if($("#all_chk").is(":checked")){
			$(".checkSelect").prop("checked", true);
			$("#all_chk2").val("전체해제");
		}else{
			$(".checkSelect").prop("checked", false);
			$("#all_chk2").val("전체선택");
		}
	});

	$("#all_chk2").click(function() {
		$("#all_chk").trigger("click");
	});

	$(".checkSelect").click(function() {
		var total = $(".checkSelect").length;
		var checked = $(".checkSelect:checked").length;

		if(total != checked) $("#all_chk").prop("checked", false);
		else $("#all_chk").prop("checked", true); 
	});
});

function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

function partner_id(id){
	$("#search_partner").val(id);
	info_close2();
}

function select_check() {
	if($(".checkSelect:checked").length == 0){
		alert("선택된 주문이 없습니다.\n주문을 선택해 주시기 바랍니다.");
		return false;
	}
	return true;
}

function change_step_go(step){
	if(select_check() == true){
		var od_id = [];
		var item = $("input[name='od_id[]']:checked");
		for(var i = 0; i < item.length; i++) {
			od_id.push($(item[i]).val());
		}
		
		change_step2(od_id, step);
		if(step == "배송"){
			$("#click_status3").trigger("click");
		}else if(step == "출고준비"){
			$("#click_status2").trigger("click");
		}else{
			$("#click_status1").trigger("click");
		}
	}
}
function change_step2(od_id, step, api) {
    console.log(od_id);
    console.log(step);
    console.log(api);
    $.ajax({
        method: "POST",
        url: "./ajax.order.step.php",
        data: {
            'step': step,
            'od_id[]': od_id,
            'api': api
        },
    }).done(function (data) {
        console.log(data);
        if (data == 'success') {
            alert('상태가 변경되었습니다.');            
        } else {
            alert(data);
        }
    });
}

function direct_delivery_excel(){
	if(select_check() == true){
		$('#loading_excel').show();
		href = './order.partner.excel.php';
		var od_id = [];
		var item = $("input[name='od_id[]']:checked");
		for(var i = 0; i < item.length; i++) {
			od_id.push($(item[i]).val());
		}
		//var queryString = $.param({"od_id":od_id});
		excel_downloader = $.fileDownload(href, {
			httpMethod: "POST",
			data: {"od_id":od_id}
		})
		.always(function() {
			$('#loading_excel').hide();
			location.reload();
		});
	}
}

function downloadExcel() {
    var href = './direct_delivery_orderlist.excel.download.php';

    $('#loading_excel').show();
    EXCEL_DOWNLOADER = $.fileDownload(href, {
      httpMethod: "POST",
      data: $("#frmsamhwaorderlist").serialize()
    })
      .always(function() {
        $('#loading_excel').hide();
      });
}

function cancelExcelDownload() {
    if (EXCEL_DOWNLOADER != null) {
      EXCEL_DOWNLOADER.abort();
    }
    $('#loading_excel').hide();
}


function go_view(a,b,c,d){//a:상품관리코드,b:유통,c:급여,d:상품명,e:위탁,f:파트너,g:마감시간,h:창고,i:메모
	$('#view_od_id').text(": "+a);
	$('#view_it_name').text(": "+b);
	$('#view_od_memo').val(c);
	$('#view_prodMemo').val(d);	
	
	$('body').addClass('modal-open');
	$('#popup_box3').show();
}

function partner_search() {
	$("#partner_frame").attr('src',"/adm/shop_admin/popup.direct_delivery_partner.php");
	$('body').addClass('modal-open');
	$('#popup_box4').show();
}

function info_close(){
	$('#popup_box3').hide();
	$('body').removeClass('modal-open');
	$('#view_od_id').text("");
	$('#view_it_name').text("");
	$('#view_od_memo').val("");
	$('#view_prodMemo').val("");
}
function info_close2(){
	$('#popup_box4').hide();
	$('body').removeClass('modal-open');
}
function info_close3(){
	location.reload();
	$('#popup_box5').hide();
	$('body').removeClass('modal-open');
}

function item_edit(){
	if(confirm("정말 수정하시겠습니까?")){
        var direct_delivery = 0;
		if($('#edit_it_is_direct_delivery').is(':checked')){
			direct_delivery = 1;
		}

		var params = {
            it_id : $('#edit_it_id').val()
		    , it_is_direct_delivery : direct_delivery
            , it_direct_delivery_partner : $("#edit_it_direct_delivery_partner").val()
            , it_deadline : $("#edit_it_deadline").val()
			, it_default_warehouse : $("#edit_it_default_warehouse").val()
			, it_admin_memo : $("#edit_it_admin_memo").val()
        }                
        // ajax 통신
        $.ajax({
            type : "POST",            // HTTP method type(GET, POST) 형식이다.
            url : "./ajax.item_edit.php",      // 컨트롤러에서 대기중인 URL 주소이다.
            data : params,            // Json 형식의 데이터이다.
			dataType: "json",
            success : function(res){ // 비동기통신의 성공일경우 success콜백으로 들어옵니다. 'res'는 응답받은 데이터이다.
                // 응답코드 > 0000
                if(res == true){
					location.reload();
				}else{
					alert("유통정보 수정에 실패 했습니다.\n다시 시도해 주세요.");
				}
            },
            error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
                alert("통신 실패.");
            }
        });
	}else{
		return false;
	}
}

function barcode_insert(a){//선택 바코드 정보 입력
	var od_id = [];
	if(a != ""){
		od_id.push(a);
		$("#barcode_ct_id").val(od_id);
		document.barcode_form.submit();
			
			//$("#barcode_frame").attr('src',"/adm/shop_admin/popup.barcode_insert.php");
		$('body').addClass('modal-open');
		$('#popup_box5').show();
	}else{
		if(select_check() == true){
			var item = $("input[name='od_id[]']:checked");
			for(var i = 0; i < item.length; i++) {
				od_id.push($(item[i]).val());
			}
			$("#barcode_ct_id").val(od_id);
			document.barcode_form.submit();
			
			//$("#barcode_frame").attr('src',"/adm/shop_admin/popup.barcode_insert.php");
			$('body').addClass('modal-open');
			$('#popup_box5').show();			
		}
	}
}

function move_staus(){	
	location.href="direct_delivery_orderlist.php?page_rows=<?=$page_rows?>&click_status="+$("#click_status").val();
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
