<?php
$sub_menu = '400430';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$table_nm = "fm";

if($type=="partner"){
  $title_name = "-파트너";
  $table_nm = "pm";
}

$g5['title'] = '과거CS주문내역'.$title_name;

include_once (G5_ADMIN_PATH.'/admin.head.php');

$where = " and ";
$sql_search = "";
if($sfl == "order_seq" && $stx != ""){
	$sql_search .= " $where a.seq IN (select cseq from ".$table_nm."_order where order_seq like '%$stx%') ";
	$where = " and ";
}else if($sfl == "csmanager" && $stx != ""){
	$sql_search .= " $where a.mseq IN (select mseq from ".$table_nm."_cs_manager where mname like '%$stx%') ";
	$where = " and ";
}else{
	if ($stx != "") {
		if ($sfl == 'all') {
			$sql_search .= " $where ( 
				a.seq IN (select cseq from ".$table_nm."_order where order_seq like '%$stx%') OR 
				a.mseq IN (select mseq from ".$table_nm."_cs_manager where mname like '%$stx%') OR
				`cs_seq` like '%$stx%' OR
				`depositor` like '%$stx%' OR
				`dname`  like '%$stx%'
			 ) ";
			$where = " and ";
		} else if ($sfl != "") {
			$sql_search .= " $where $sfl like '%$stx%' ";
			$where = " and ";
		}
	}
}

if ($sca != "") {
    $sql_search .= " $where ";
}

if($s_price && $e_price){
	$sql_search .= " $where a.seq IN (select cseq from ".$table_nm."_order where settleprice between $s_price and $e_price) ";
	$where = " and ";
}

if ($sfl == "")  $sfl = "all";

$sql_common = " from ".$table_nm."_cs_order a,".$table_nm."_cs_order_deli b where b.pseq=a.seq ";
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
	$sst = "cs_seq";
    $sod = "desc";
}

$sql_order = "order by $pth $sst $sod $ptt";

$sql  = " select *
           $sql_common
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;
$qstr  = $qstr.'&amp;type='.$type.'&amp;page='.$page.'&amp;s_price='.$s_price.'&amp;e_price='.$e_price;
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
					
					    <form name="flist" class="local_sch01 local_sch">
					    <input type="hidden" name="page" value="1">
						<input type="hidden" name="type" value="<?php echo $type ?>">					
					    <label for="bn_position" class="sound_only">검색</label>
					    <select name="sfl" id="bn_position">
							<option value="all" <?php echo ($sfl=="all")?"selected":""?>>전체</option>
					        <option value="order_seq" <?php echo ($sfl=="order_seq")?"selected":""?>>주문번호</option>
					        <option value="cs_seq" <?php echo ($sfl=="cs_seq")?"selected":""?>>주문서번호</option>
				            <option value="depositor" <?php echo ($sfl=="depositor")?"selected":""?>>업체명</option>
					        <option value="dname" <?php echo ($sfl=="dname")?"selected":""?>>담당자</option>
					        <option value="csmanager" <?php echo ($sfl=="csmanager")?"selected":""?>>CS매니저</option>
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
							        <th scope="col" >주문서작성일</th>
							        <th scope="col" >주문서번호</th>
							        <th scope="col" >주문번호</th>
							        <th scope="col" >업체명</th>
							        <th scope="col" >담당자 </th>
							        <th scope="col" >CS매니저</th>
							        <th scope="col" >주문상품명</th>
							        <th scope="col" >주문금액</th>
							        <th scope="col" >상태</th>
							        <th scope="col" >보기</th>
							    </tr>
						    </thead>
						    <tbody>
<?php
$num = $total_count - ($rows*($page-1));

for ($i=0; $row=sql_fetch_array($result); $i++){

	$order_row = sql_fetch("select order_seq,settleprice from ".$table_nm."_order where cseq='".$row['seq']."'");

	$item_name = "";
    if($order_row['order_seq']){
		$item_result = sql_query("select goods_name from ".$table_nm."_order_item where order_seq='".$order_row['order_seq']."'");
		$item_cnt = 0;
		for ($i=0; $item_row = sql_fetch_array($item_result); $i++){
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
	}

	$manager_row = sql_fetch("select mname from ".$table_nm."_cs_manager where mseq='".$row['mseq']."'");
?>
							    <tr class="bg0">
							        <td><?php echo $num; ?></td>
							        <td><?php echo $row['rdate']; ?></td>
							        <td><?php echo $row['cs_seq']; ?></td>
							        <td><?php echo $order_row['order_seq']; ?></td>
							        <td><?php echo $row['depositor']; ?></td>
							        <td><?php echo $row['dname']; ?></td>
							        <td><?php echo $manager_row['mname']?></td>
							        <td><?php echo $item_name ?></td>
							        <td><?php echo number_format($order_row['settleprice']) ?></td>
							        <td><?php echo ($order_row['order_seq'])?"<b style='color:purple'>주문생성</b>":"저장시도" ?></td>
							        <td class="td_mng td_mng_s"><a href="past_csorder_view.php?type=<?php echo $type ?>&seq=<?php echo $row['cs_seq'];?>" class="mng_mod btn btn_02">보기</a></td>
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