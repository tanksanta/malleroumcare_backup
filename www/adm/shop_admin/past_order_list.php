<?php
error_reporting(E_ALL);
ini_set("display_errors", 1); 
$sub_menu = '400420';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$table_nm = "fm";

if($type=="partner"){
  $title_name = "-파트너";
  $table_nm = "pm";
}

$g5['title'] = '과거주문내역'.$title_name;
include_once (G5_ADMIN_PATH.'/admin.head.php');

$where = " and ";
$sql_search = "";

if($sfl == "goods_name" && $stx != ""){
	$sql_search .= " $where order_seq IN (select order_seq from ".$table_nm."_order_item where goods_name like '%$stx%') ";
	$where = " and ";
}else{
	if ($stx != "") {
		if ($sfl == 'all') {
			$sql_search .= " $where ( `order_seq` like '%$stx%' OR `recipient_user_name` like '%$stx%' OR `order_user_name` like '%$stx%' OR order_seq IN (select order_seq from ".$table_nm."_order_item where goods_name like '%$stx%') ) ";
			$where = " and ";
		} else if ($sfl != "") {
			$sql_search .= " $where $sfl like '%$stx%' ";
			$where = " and ";
		}
	}
}

if($s_price && $e_price){
	$sql_search .= " $where settleprice between $s_price and $e_price ";
	$where = " and ";
}

if ($sca != "") {
    $sql_search .= " $where ";
}

if ($sfl == "")  $sfl = "all";

$sql_common = " from ".$table_nm."_order where 1=1 ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
	$sst = "order_seq";
    $sod = "desc";
}

$sql_order = "order by $pth $sst $sod $ptt";

$sql  = " select *
           $sql_common
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;
$qstr  = $qstr.'&amp;type='.$type.'page='.$page.'&amp;s_price='.$s_price.'&amp;e_price='.$e_price;
?>


					<div id="text_size">
						<!-- font_resize('엘리먼트id', '제거할 class', '추가할 class'); -->
						<button onclick="font_resize('container', 'ts_up ts_up2', '');"><img src="https://signstand.co.kr/adm/img/ts01.gif" alt="기본"></button>
						<button onclick="font_resize('container', 'ts_up ts_up2', 'ts_up');"><img src="https://signstand.co.kr/adm/img/ts02.gif" alt="크게"></button>
						<button onclick="font_resize('container', 'ts_up ts_up2', 'ts_up2');"><img src="https://signstand.co.kr/adm/img/ts03.gif" alt="더크게"></button>
					</div>
					<div class="local_ov01 local_ov">
					    <span class="btn_ov01"><span class="ov_txt"> 등록된 주문 </span><span class="ov_num"> <?=number_format($total_count)?>개</span></span>

						<a href="past_order_list.php">[과거주문내역]</a>
						<a href="past_csorder_list.php">[과거cs주문내역]</a>
						<a href="past_order_list.php?type=partner">[파트너과거주문내역]</a>
						<a href="past_csorder_list.php?type=partner">[파트너과거cs주문내역]</a>
					
					    <form name="fsearch" class="local_sch01 local_sch">
						<input type="hidden" name="type" value="<?php echo $type ?>">
					    <label for="bn_position" class="sound_only">검색</label>
					    <select name="sfl" id="bn_position">
							<option value="all" <?php echo ($sfl=="all")?"selected":""?>>전체</option>
					        <option value="order_seq" <?php echo ($sfl=="order_seq")?"selected":""?>>주문번호</option>
					        <option value="recipient_user_name" <?php echo ($sfl=="recipient_user_name")?"selected":""?>>받는분</option>
					        <option value="order_user_name" <?php echo ($sfl=="order_user_name")?"selected":""?>>주문자</option>
					        <option value="goods_name" <?php echo ($sfl=="goods_name")?"selected":""?>>상품명</option>
					    </select> <input type="text" name="stx" value="<?php echo $stx ?>" />
					    / 결제금액 : <input type="text" name="s_price" value="<?php echo $s_price ?>"/>원 ~ <input type="text" name="e_price" value="<?php echo $e_price ?>"/>원
					
					    <input type="submit" value="검색" class="btn_submit">
					
					    </form>
					
					</div>
		
					<div class="tbl_head01 tbl_wrap">
					    <table id="sodr_list">
					    <caption>배너관리 목록</caption>
						    <thead>
							    <tr>
							        <th scope="col" >No</th>
							        <th scope="col" >주문번호</th>
							        <th scope="col" >주문상품</th>
							        <th scope="col" >주문일시 </th>
							        <th scope="col" >받는분/주문자</th>
							        <th scope="col" >결제수단</th>
							        <th scope="col" >결제금액</th>
							        <th scope="col" >매니저</th>
							        <th scope="col" >출고방법</th>
							        <th scope="col" >보기</th>
							    </tr>
						    </thead>
						    <tbody>
<?php
$num = $total_count - ($rows*($page-1));
for ($i=0; $row=sql_fetch_array($result); $i++){

    $item_result = sql_query("select goods_name from ".$table_nm."_order_item where order_seq='".$row['order_seq']."'");
	$item_cnt = 0;
	$item_name = "";
    for ($i=0; $item_row    = sql_fetch_array($item_result); $i++){
		if($i==0) $item_name = $item_row['goods_name'];
		else{
			if($i==1){ 
				$item_name .=  " 외 ";
			}
			$item_cnt++;
		}
	}
	if($item_cnt){
	  $item_name .= $item_cnt."종";
	}

	switch($row['payment']){
		case "card":
			$payment_nm = "카드";
		    break;
	    case "point";
			$payment_nm = "포인트";
		    break;
	    case "account";
			$payment_nm = "무통장";
		    break;
	    case "vitual";
			$payment_nm = "가상계좌";
		    break;
	    case "cellphone";
			$payment_nm = "휴대폰";
		    break;
	}

	switch($row['shipping_method']){
		case "delivery":
			$delivery_nm = "택배(선불)";
		    break;
		case "quick":
			$delivery_nm = "오토바이";
		    break;
		case "direct":
			$delivery_nm = "직접수령";
		    break;
	}

	$manager_row = sql_fetch("select mname from ".$table_nm."_cs_manager where mseq='".$row['mseq']."'");
?>
							    <tr class="bg0">
							        <td><?php echo $num; ?></td>
							        <td><?php echo $row['order_seq']; ?></td>
							        <td><?php echo $item_name ?></td>
							        <td><?php echo substr($row['regist_date'],0,16)?></td>
							        <td><?php echo $row['recipient_user_name'] ?><br>
							        	<?php echo $row['order_user_name'] ?></td>
							        <td><?php echo $payment_nm ?></td>
							        <td><?php echo number_format($row['settleprice']) ?>원</td>
							        <td><?php echo $manager_row['mname'] ?></td>
							        <td><?php echo $delivery_nm ?><br>
							        	<?php echo ($row['deposit_yn'])?"전송":"미전송" ?></td>
							        <td class="td_mng td_mng_s"><a href="past_order_view.php?type<?php echo $type ?>&seq=<?php echo $row['order_seq'];?>" class="mng_mod btn btn_02">보기</a></td>
							    </tr>
<?php 
	$num--;
}
?>
						    </tbody>
					    </table>
					
					</div>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr"); ?>
		
		
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>