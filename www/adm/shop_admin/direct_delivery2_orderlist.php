<?php
$sub_menu = '400406';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");
add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);

$g5['title'] = '설치배송 주문관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

////////////////////////////////////////////////////////////////////////////////////////////////////
if($auth_check = auth_check($auth[$sub_menu], "r"))
// 초기 3개월 범위 적용
$fr_date = $_REQUEST["fr_date"];
$to_date = $_REQUEST["to_date"];
if ($fr_date == "" && $to_date == "" && $all_date !="ok") {
    $fr_date = date("Y-m-d", strtotime("-3 month"));
    $to_date = date("Y-m-d");
}
$qstr .= '&amp;page_rows='.$page_rows;
$click_status = ($click_status == "")?"준비": $click_status;

$sql = "SELECT COUNT(CASE WHEN ct_status='준비' THEN 1 END) AS count1, 
COUNT(CASE WHEN ct_status='출고준비' THEN 1 END) AS count2,
COUNT(CASE WHEN ct_status='배송' THEN 1  END) AS count3 
FROM g5_shop_cart c
LEFT JOIN g5_shop_order o ON c.od_id = o.od_id
WHERE ct_is_direct_delivery = '2'
AND od_del_yn = 'N'";
$row = sql_fetch($sql,true);

$count1 = $row["count1"];//상품준비count
$count2 = $row["count2"];//출고준비count
$count3 = $row["count3"];//출고완료(배송완료포함)count



$where = array();
$where[] = "ct_is_direct_delivery = '2'";//직배항목만

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

if ($search_mb_name != "") {//사업소명 검색
  $search_mb_name = trim($search_mb_name);
  $where[] = " m.mb_name like '%$search_mb_name%' ";
  $qstr .="&amp;search_mb_name=".$_REQUEST["search_mb_name"];
}

if ($search_memo != "") {//요청사항 검색
  $search_memo = trim($search_memo);
  $where[] = " (o.od_memo like '%$search_memo%' or c.prodMemo like '%$search_memo%') ";
  $qstr .="&amp;search_memo=".$_REQUEST["search_memo"];
}



// 품목구분
if (gettype($ca_id) == 'string' && $ca_id !== '') {
    $where[] = " ( substring(ca_id,1,4) = '$ca_id') ";
	$qstr .= "&amp;ca_id=".$ca_id;
}

// 설치파트너
if (gettype($partner_id) == 'string' && $partner_id !== '') {
    $where[] = " ( m2.mb_id = '$partner_id' ) ";
	$qstr .= "&amp;partner_id=".$partner_id;
}

// 설치결과보고
if (gettype($pip) == 'string' && $pip !== '') {
  if ($pip == '등록')
    $where[] = " ( pip.img_cnt1 > 0 and pip.img_cnt2 > 0 and pip.img_cnt3 > 0 ) ";
  else
    $where[] = " ( pip.img_cnt1 < 1 OR pip.img_cnt1 IS NULL OR pip.img_cnt2 < 1 OR pip.img_cnt3 IS NULL OR pip.img_cnt3 < 1 OR pip.img_cnt3 IS NULL ) ";
  $qstr .= "&amp;pip=".$pip;
}

// 설치이슈여부
if (gettype($pir_issue) == 'string' && $pir_issue !== '') {
	$where[] = " ( $pir_issue = '1' ) ";
	$qstr .= "&amp;pir_issue=".$pir_issue;
}

//////////////////
$search_date = ($search_date == "")?"od_time":$search_date;//검색기간 구분

if ($fr_date && $to_date) {
  $where[] = " ($search_date between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
  $qstr .= "&amp;search_date=".$search_date."&amp;fr_date=".$fr_date."&amp;to_date=".$to_date;
}

$where[] = " od_del_yn = 'N' ";

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
	LEFT JOIN ( SELECT od_id, COUNT(CASE WHEN img_type = '설치사진' THEN 1 END) AS img_cnt1
,COUNT(CASE WHEN img_type = '실물바코드사진' THEN 1 END) AS img_cnt2
,COUNT(CASE WHEN img_type = '설치ㆍ회수ㆍ소독확인서' THEN 1 END) AS img_cnt3 
FROM partner_install_photo WHERE 1=1 GROUP BY od_id) AS pip ON c.od_id = pip.od_id
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
$page_rows = (int)$page_rows ? (int)$page_rows : "50";
$rows = $page_rows;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


if($click_status == "준비"){
	$title_bg = "#32841c";
}elseif($click_status == "출고준비"){
	$title_bg = "#36a6de";
}elseif($click_status == "배송"){
	$title_bg = "#28759c";
}

$sql_order = " ORDER BY o.od_id DESC ";//기본 정렬

$sql_common .= $sql_order;

$sql  = "
  select *, o.od_id as od_id, c.ct_id as ct_id, c.mb_id as mb_id,m.mb_name as mb_name2,m2.mb_name AS partner_name,m2.mb_id AS partner_id
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
		background: rgba(0, 0, 0, 0.6);		
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

<form name="frmsamhwaorderlist" id="frmsamhwaorderlist" style="margin-top:-15px;" method="get" action="direct_delivery2_orderlist.php">
<input type="hidden" name="page_rows" id="page_rows" value="<?=$page_rows?>">
<input type="hidden" name="click_status" value="<?=$click_status?>">
<input type="hidden" name="all_date" id="all_date" value="<?=$all_date?>">
  <div class="new_form">
    <table class="new_form_table" id="search_detail_table">
	  <tr>
        <th>검색조건</th>
        <td >
			품목구분&nbsp;&nbsp;
            <select name="ca_id" id="ca_id">
            <option value="" >전체</option>
            <option value="1090" <?php echo get_selected($ca_id, '1090'); ?>>안전손잡이</option>
            <option value="2060" <?php echo get_selected($ca_id, '2060'); ?>>수동침대</option>
			<option value="2070" <?php echo get_selected($ca_id, '2070'); ?>>전동침대</option>
			<option value="2080" <?php echo get_selected($ca_id, '2080'); ?>>수동휠체어</option>
			</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		  
			설치파트너&nbsp;&nbsp;
            <select name="partner_id" id="partner_id">
            <option value="" >전체</option>
<?php $sql_p = "SELECT mb_id,mb_name FROM g5_member WHERE mb_partner_type = '설치';";
$result_p = sql_query($sql_p);
while($row_p = sql_fetch_array($result_p)){?>
            <option value="<?=$row_p["mb_id"]?>" <?php echo get_selected($partner_id, $row_p["mb_id"]); ?>><?=$row_p["mb_name"]?></option>
<?php }?>
			</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			설치결과보고&nbsp;&nbsp;
            <select name="pip" id="pip">
            <option value="">전체</option>
            <option value="등록" <?php echo get_selected($pip, '등록'); ?>>등록</option>
            <option value="미등록" <?php echo get_selected($pip, '미등록'); ?>>미등록</option>
			</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
			설치이슈여부&nbsp;&nbsp;
            <select name="pir_issue" id="pir_issue">
            <option value="" >전체</option>
            <option value="ir_is_issue_1" <?php echo get_selected($pir_issue, 'ir_is_issue_1'); ?>>상품변경</option>
            <option value="ir_is_issue_2" <?php echo get_selected($pir_issue, 'ir_is_issue_2'); ?>>상품추가</option>
			<option value="ir_is_issue_3" <?php echo get_selected($pir_issue, 'ir_is_issue_3'); ?>>미설치</option>
			</select>
        </td>
      </tr>
	  <tr>
        <th>검색기간</th>
        <td>
          <div class="sel_field">
			<select name="search_date" id="search_date">
				<option value="od_time" <?php echo get_selected($search_date, 'od_time'); ?>>주문일</option>
				<option value="ct_move_date" <?php echo get_selected($search_date, 'ct_move_date'); ?>>변경일</option>
				<option value="ct_direct_delivery_date" <?php echo get_selected($search_date, 'ct_direct_delivery_date'); ?>>설치예정일</option>
			</select>
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
        <th>검색어입력</th>
        <td>			
			상품명&nbsp;&nbsp;
			<input type="text" name="search_it_name" value="<?php echo $search_it_name; ?>" id="search_it_name" class="frm_input" autocomplete="off" style="width:150px;">&nbsp;&nbsp;&nbsp;&nbsp;
			사업소&nbsp;&nbsp;
			<input type="text" name="search_mb_name" value="<?php echo $search_mb_name; ?>" id="search_mb_name" class="frm_input" autocomplete="off" style="width:150px;">&nbsp;&nbsp;&nbsp;&nbsp;
			수령인&nbsp;&nbsp;
			<input type="text" name="search_b_name" value="<?php echo $search_b_name; ?>" id="search_b_name" class="frm_input" autocomplete="off" style="width:150px;">&nbsp;&nbsp;&nbsp;&nbsp;
			요청사항&nbsp;&nbsp;
			<input type="text" name="search_memo" value="<?php echo $search_memo; ?>" id="search_memo" class="frm_input" autocomplete="off" style="width:150px;">&nbsp;&nbsp;
			
			<input type="submit" value="검색" class="newbutton" style="background-color:#000000;color:#ffffff;width:70px;">
        </td>
      </tr>
	  
    </table>
  </div>
</form>

<div style="margin:35px 0px 0px 20px; float:left">
	<input type="button" value="상품준비(<?=number_format($count1)?>)" class="newbutton2" onClick="$('#click_status').val('준비');move_staus();" id="click_status1" style="background: #32841c !important;color:#fff;"/>&nbsp;&nbsp;
		<input type="button" value="출고준비(<?=number_format($count2)?>)" class="newbutton2" onClick="$('#click_status').val('출고준비');move_staus();"id="click_status2" style="background: #36a6de !important;color:#fff;"/>&nbsp;&nbsp;
        <input type="button" value="출고완료(<?=number_format($count3)?>)" class="newbutton2" onClick="$('#click_status').val('배송');move_staus();"id="click_status3" style="background: #28759c !important;color:#fff;"/>
		<input type="hidden" name="click_status" id="click_status" value="<?=$click_status?>">
</div>
<div style="margin:-15px 20px 0px 0px; float:right;right:0px; text-align:right;">
	
<?php if($click_status == "준비"){
		echo '<input type="button" value="선택 출고준비로 변경" id="" name="" class="newbutton2" onClick="return change_step_go(\'출고준비\')" style="background: #36a6de;color:#fff;"/>';
	}elseif($click_status == "출고준비"){
		echo '<input type="button" value="선택 상품준비로 변경" id="" name="" class="newbutton2" onClick="return change_step_go(\'준비\')" style="background: #32841c;color:#fff;"/>';
	}
		echo '&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="엑셀다운로드" id="" name="" class="newbutton2" style="background:#6e9254;color:#fff;" onclick="downloadExcel();"/>';

?>
	<br><br>
	<select name="page_rows" id="page_rows2" onChange="javascript:$('#page_rows').val(this.value);$('#frmsamhwaorderlist').submit();" style="width:130px;height:33px;">
		<option value="50"  <?=($page_rows =='50')?"selected":"";?>>50개씩보기</option>
        <option value="100" <?=($page_rows=='100')?"selected":"";?>>100개씩보기</option>
		<option value="200" <?=($page_rows=='200')?"selected":"";?>>200개씩보기</option>
    </select>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <thead>
    <tr>
        <th scope="col" width="10px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;"><input type="checkbox" name="all_chk" id="all_chk" class="frm_input"></th>
		<th scope="col" width="107px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">주문일시<br>(주문번호)</th>
		<th scope="col" width="75px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">변경일시</th>
		<th scope="col" width="170px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">상품명(옵션)</th>
		<th scope="col" width="75px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">품목구분</th>
		<th scope="col" width="60px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">단가</th>
		<th scope="col" width="60px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">바코드<br>/수량</th>
		<th scope="col" width="70px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">합계금액</th>
		<th scope="col" width="170px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">사업소명</th>
		<th scope="col" width="120px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">수령인</th>
		<th scope="col" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">배송주소</th>
		<th scope="col" width="110px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">설치파트너</th>
		<th scope="col" width="5px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">요청사항</th>
		<th scope="col" width="75px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">설치예정일</th>
        <th scope="col" width="150px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">설치이슈</th>
		<th scope="col" width="70px;" style="background: <?=$title_bg?>;border-color: <?=$title_bg?>;">설치결과보고</th>
    </tr>
    </thead>
    <tbody>
    <?php
	$i = 0;
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

		$pip = ($order['img_cnt1'] > 0 && $order['img_cnt2'] > 0 && $order['img_cnt3'] > 0)?"등록":"<font color='red'>미등록</font>";//설치결과보고
		$a_link = ($click_status != "준비")? "<a href='javascript:partner_installreport(".$order["od_id"].")'>":"";
		$a_link2 = ($click_status != "준비")? "</a>":"";
		$pip2 = $a_link.$pip.$a_link2;
		$order['od_memo'];
		$order['prodMemo'];
		$memo = ($order['od_memo'] !="" || $order['prodMemo'] != "")?"<a href=\"javascript:;\" onClick=\"go_view('".$order["od_id"]."','".$order["it_name"]."','".$order['od_memo']."','".$order['prodMemo']."')\">보기</a>":"-";//요청사항보기
		if ($cancel_order_table[$order['od_id']]) {
			$is_order_cancel_requested = "cancel_requested";
		  }

		switch(substr($order['ca_id'],0,4)){
			case "1090": $ca_nm = "안전손잡이"; break;
			case "2060": $ca_nm = "수동침대"; break;
			case "2070": $ca_nm = "전동침대"; break;
			case "2080": $ca_nm = "수동휠체어"; break;
			default: $ca_nm = "-"; break;
		}
		$ir_is_issue1 = ($order["ir_is_issue_1"]=="1")?"상품변경" : "";
		$ir_is_issue2 = ($order["ir_is_issue_2"]=="1")?"상품추가" : "";		
		$ir_is_issue3 = ($order["ir_is_issue_3"]=="1")?"미설치" : "";
		$sl1  = ($ir_is_issue1 != "" && $ir_is_issue2 != "")? "/":"";
		if($ir_is_issue3 != ""){			
			if($sl1 != ""){
				$sl2 = "/";
			}elseif($sl1 == "" && ($ir_is_issue1 != "" || $ir_is_issue2 !="")){
				$sl2 = "/";
			}else{
				$sl2 = "";
			}
		}
    ?>
    <tr class="<?php echo $bg; ?>">
        <td align="center"><input type="checkbox" name="od_id[]" id="<?=$order["ct_id"];?>" value="<?=$order["ct_id"];?>" data-value="<?=substr($order["ca_id"],0,2)?>" data-barcode='<?=($order['ct_barcode_insert']!=$order['ct_qty'])?0:1;?>' class="frm_input checkSelect chkbox"></td>
		<td align="center"><a href="samhwa_orderform.php?od_id=<?=$order["od_id"];?>&sub_menu=400405" target="_blank"><?=substr($order['od_time'],0,16)?><br><?=$order["od_id"];//주문일/주문번호 ?></a></td>
		<td align="center"><?=substr($order['ct_move_date'],0,16);//변경일 ?></td>
		<td align="center"><?=$order["it_name"].(($order["ct_option"] != $order["it_name"])?" (".$order["ct_option"].")":"");//상품명(옵션) ?></td>
		<td align="center"><?=$ca_nm;//품목명 ?></td>
		<td align="right"><?=number_format($order["opt_price"]);//단가 ?></td>
		<td align="center" <?=($order['ct_barcode_insert'] >= $order['ct_qty'] || substr($order["ca_id"],0,2) == "70")?"":"style='color:red;'"; ?>><span class="barcode_pop" style='cursor:pointer;' data-option="<?=$order["ct_option"]?>" data-it="<?=$order["it_id"]?>" data-stock="1" data-od="<?=$order["od_id"]?>"><?=(substr($order["ca_id"],0,2) != "70")?$order['ct_barcode_insert']."/".$order['ct_qty']:$order['ct_qty'];//바코드/수량 ?></span></td>
		<td align="right"><?=number_format($order["ct_price_stotal"]);//합계금액 ?></td>
		<td align="center"><?=$order["mb_name2"];//사업소명 ?></a></td>
		<td align="center"><?=$order["od_b_name"];//수령인 ?></a></td>
		<td align="center"><?=$order["od_b_addr1"].(($order["od_b_addr2"]!="")?" ".$order["od_b_addr2"]:"").(($order["od_b_addr3"]!="")?" ".$order["od_b_addr3"]:"");//배송주소 ?></td>
		<td align="center"><?=$ct_direct_delivery_partner_name;//설치파트너?></td>
		<td align="center"><?=$memo;//요청사항 ?></td>
        <td align="center"><?=substr($order["ct_direct_delivery_date"],0,16);//설치예정일?></td>
		<td align="center"><?=$ir_is_issue1.$sl1.$ir_is_issue2.$sl2.$ir_is_issue3;//설치이슈?></td>
		<td align="center"><?=$pip2?></td>
    </tr>
    <?php
    $i++;
	}

    if ($i == 0) {
        echo '<tr><td colspan="16" class="empty_table">자료가 없습니다.</td></tr>';
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
<div id="popup_box" class="popup_box2">    
	<div id="" class="popup_box_con" style="height:645px;margin-top:-300px;margin-left:-25%;width:50%;left:50%;top:50%;padding:0px;">
		<div id="partner_insert_report" style="float:left;width:100%;">
		</div>		
	</div>	
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

    


<script>
$(function() {

	$("#dragg_popup").draggable();
	
	//시프트(shift) 멀티 체크박스 선택 =======================================
	var $chkboxes = $('.chkbox');
    var lastChecked = null;

    $chkboxes.click(function(e) {
        if(!lastChecked) {
            lastChecked = this;
            return;
        }

        if(e.shiftKey) {
            var start = $chkboxes.index(this);
            var end = $chkboxes.index(lastChecked);

            $chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastChecked.checked);

        }

        lastChecked = this;
    });
	//시프트(shift) 멀티 체크박스 선택 =======================================

	var EXCEL_DOWNLOADER = null;//엑셀 다운로더

	$("#fr_date, #to_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        maxDate: "+0d"
    });

	$("#to_date").on("propertychange change keyup paste input", function(){
		$('#all_date').val("");
	});
	$("#fr_date").on("propertychange change keyup paste input", function(){
		$('#all_date').val("");
	});

	// 기간 - 전체 버튼
  $('#select_date_all').click(function() {
    $('#to_date').val("");
    $('#fr_date').val("");
	$('#all_date').val("ok");
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
			//$("#all_chk2").val("전체해제");
		}else{
			$(".checkSelect").prop("checked", false);
			//$("#all_chk2").val("전체선택");
		}
		checkbox_count();
	});
/*
	$("#all_chk2").click(function() {
		$("#all_chk").trigger("click");
	});
*/
	$(".checkSelect").click(function() {
		var total = $(".checkSelect").length;
		var checked = $(".checkSelect:checked").length;
		if(total != checked) $("#all_chk").prop("checked", false);
		else $("#all_chk").prop("checked", true); 
		checkbox_count();
	});
});

function checkbox_count(){
	var checked = $(".checkSelect:checked").length;
	$("#all_chk2").val(checked+"건 선택");
	
}


function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  $('#all_date').val("");
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
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
		var od_id2 = [];
		var item = $("input[name='od_id[]']:checked");
		for(var i = 0; i < item.length; i++) {
			od_id.push($(item[i]).val());
			if($(item[i]).data('value') == "70" && $(item[i]).data('barcode') == "0"){//비급여 건 처리
				od_id2.push($(item[i]).val());				
			}
		}
		change_step2(od_id, step,'',od_id2);
	}
}
function change_step2(od_id, step, api,od_id2) {
    console.log(od_id);
    console.log(step);
    console.log(api);
	var response = true; 
    if(od_id2.length > 0){//비급여 바코드 확인 패스 처리
		response = false;
		$.ajax({
			method: "POST",
			url: "./ajax.pass_ct.php",
			async:false,
			data: {
				'ct_id': od_id2
			},
		}).done(function (data) {
			response = true;
		});
	}
	if(response == true){
		$.ajax({
			method: "POST",
			url: "./ajax.order.step.php",
			async:false,
			data: {
				'step': step,
				'od_id[]': od_id,
				'api': api
			},
		}).done(function (data) {
			console.log(data);
			if (data == 'success') {
				alert('상태가 변경되었습니다.');  
				if(step == "배송"){
					$("#click_status3").trigger("click");
				}else if(step == "출고준비"){
					$("#click_status2").trigger("click");
				}else{
					$("#click_status1").trigger("click");
				}
			} else {
				alert(data);			
			}
		});
	}
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
    var href = './direct_delivery2_orderlist.excel.download.php';

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

var openDialog2 = function(od_id, closeCallback) {
        var win = $("#partner_insert_report").html('<iframe src="/shop/popup.partner_installreport.php?od_id=' + od_id +
      '" scrolling="yes" frameborder="0" allowTransparency="false" style="width:100%;height:645px;"></iframe>');
		$('body').addClass('modal-open');
		$('#popup_box').show();
        var interval = window.setInterval(function() {
            try {
                if ($('#popup_box').is(':visible') == false) {
                    window.clearInterval(interval);
                    closeCallback(win);
                }
            }
            catch (e) {
            }
        }, 500);
        return win ;
    };

function partner_installreport(od_id) {
	openDialog2(od_id, function(win) {
       location.reload();
    });
}

function info_close(){
	$('#popup_box3').hide();
	$('body').removeClass('modal-open');
	$('#view_od_id').text("");
	$('#view_it_name').text("");
	$('#view_od_memo').val("");
	$('#view_prodMemo').val("");
}

var openDialog = function(uri, name, options, closeCallback) {
        var win = window.open(uri, name, options);
        var interval = window.setInterval(function() {
            try {
                if (win == null || win.closed) {
                    window.clearInterval(interval);
                    closeCallback(win);
                }
            }
            catch (e) {
            }
        }, 500);
        return win ;
    };

$(document).on("click", ".barcode_pop", function(e) {
    e.preventDefault();
    var popupWidth = 800;
    var popupHeight = 700;
    var popupX = (window.screen.width / 2) - (popupWidth / 2);
    var popupY= (window.screen.height / 2) - (popupHeight / 2);
    // var id = $(this).attr("data-id");
    // window.open("./popup.prodBarNum.form.php?od_id=" + id, "바코드 저장", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
    var od = $(this).attr("data-od");
    var it = $(this).attr("data-it");
    var stock = $(this).attr("data-stock");
    var option = encodeURIComponent($(this).attr("data-option"));
    //popup.prodBarNum.form_3.php 으로하면 cart 기준으로 바뀜 (상품하나씩)
    openDialog("./popup.prodBarNum.form.php?no_refresh=1&orderlist=1&prodId=" + it + "&od_id=" + od + "&stock_insert=" + stock + "&option=" + option, "바코드 저장", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX, function(win) {
       location.reload();
    });
	
	//const loginPopup = window.open("./popup.prodBarNum.form.php?no_refresh=1&orderlist=1&prodId=" + it + "&od_id=" + od + "&stock_insert=" + stock + "&option=" + option, "바코드 저장", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
});


function move_staus(){	
	location.href="direct_delivery2_orderlist.php?page_rows=<?=$page_rows?>&click_status="+$("#click_status").val();
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
