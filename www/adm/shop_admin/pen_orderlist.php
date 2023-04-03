<?php
$sub_menu = '400490';
include_once('./_common.php');
$query = "SHOW tables LIKE 'g5_shop_order_api'";//api 오더 테이블 유무 확인
$wzres = sql_num_rows( sql_query($query) );
//$query = "SHOW tables LIKE 'g5_shop_cart_api'";//api 카트 테이블 유무 확인
//$wzres = sql_fetch( $query );

if($wzres < 1) {
    sql_query("CREATE TABLE `g5_shop_order_api` (
  `od_id` varchar(50) NOT NULL DEFAULT '0',
  `order_send_id` varchar(50) NOT NULL DEFAULT '0' COMMENT '1.5주문ID',
  `mb_id` varchar(255) NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 사업소 아이디',
  `od_name` varchar(20) NOT NULL DEFAULT '',
  `relation_code` varchar(20) DEFAULT NULL,
  `od_penId` varchar(20) DEFAULT NULL,
  `od_penNm` varchar(20) NOT NULL,
  `od_penRecGraNm` varchar(255) DEFAULT NULL,
  `od_penTypeNm` varchar(20) DEFAULT NULL,
  `od_penExpiDtm` varchar(255) DEFAULT NULL,
  `od_penAppEdDtm` varchar(255) DEFAULT NULL,
  `od_penGender` varchar(10) DEFAULT '' COMMENT '성별 구별 남자:남, 여자:여',
  `od_penConPnum` varchar(20) DEFAULT NULL,
  `od_penConNum` varchar(20) DEFAULT NULL,
  `od_penZip1` char(3) DEFAULT NULL COMMENT '수급자 우편번호1',
  `od_penZip2` char(3) DEFAULT NULL COMMENT '수급자 우편번호2',
  `od_penAddr` varchar(100) DEFAULT NULL COMMENT '수급자 주소',
  `od_penAddr2` varchar(100) DEFAULT NULL COMMENT '수급자 주소 상세',
  `od_penLtmNum` varchar(100) NOT NULL,
  `od_zip1` char(3) DEFAULT NULL COMMENT '신청자 우편번호',
  `od_zip2` char(3) DEFAULT NULL COMMENT '신청자 우편번호2',
  `od_addr` varchar(100) DEFAULT NULL COMMENT '신청자 주소',
  `od_addr2` varchar(100) DEFAULT NULL COMMENT '신청자 주소 상세',
  `od_birth` varchar(20) DEFAULT NULL COMMENT '신청자 생년월일',
  `od_b_id` varchar(50) NOT NULL DEFAULT '',
  `od_b_name` varchar(20) NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 구매자 이름',
  `od_b_name2` varchar(20) NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 수령인 이름',
  `od_b_tel` varchar(20) NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 수령인 연락처',
  `od_b_hp` varchar(20) NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 구매자 연락처',
  `od_b_zip1` char(3) NOT NULL DEFAULT '',
  `od_b_zip2` char(3) NOT NULL DEFAULT '',
  `od_b_addr1` varchar(100) NOT NULL DEFAULT '',
  `od_b_addr2` varchar(100) NOT NULL DEFAULT '',
  `od_b_addr3` varchar(255) NOT NULL DEFAULT '',
  `od_memo` text NOT NULL,
  `od_cart_count` int(11) NOT NULL DEFAULT 0,
  `od_status` varchar(255) NOT NULL DEFAULT '',
  `od_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `od_cancel_reason` varchar(30) DEFAULT NULL COMMENT '취소신청사유',
  `od_cancel_time` datetime DEFAULT NULL COMMENT '취소신청시간',
  `od_sync_odid` varchar(50) DEFAULT NULL COMMENT '/* 23.03.08 : 서원 - 추가 */ g5_shop_order 테이블의 연결 od_id 값',
  KEY `index2` (`mb_id`),
  KEY `order_send_id` (`order_send_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", true);
}

//$query = "SHOW tables LIKE 'g5_shop_cart_api'";

//$result = mysql_fetch_row(mysql_query($query));

$query = "SHOW tables LIKE 'g5_shop_cart_api'";//api 카트 테이블 유무 확인
$wzres = sql_num_rows( sql_query($query) );
if($wzres < 1) {
	sql_query("
CREATE TABLE `g5_shop_cart_api` (
  `ct_id` int(11) NOT NULL AUTO_INCREMENT,
  `od_id` varchar(50) NOT NULL DEFAULT '0',
  `order_send_id` varchar(50) NOT NULL DEFAULT '0' COMMENT '1.5주문ID',
  `order_send_id2` varchar(50) NOT NULL DEFAULT '0' COMMENT '1.5주문상세ID',
  `mb_id` varchar(255) NOT NULL DEFAULT '',
  `it_id` varchar(20) NOT NULL DEFAULT '',
  `ProdPayCode` varchar(20) NOT NULL DEFAULT '',
  `it_name` varchar(255) NOT NULL DEFAULT '',
  `ct_status` enum('','승인','반려') DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 변경 */ 1.0과 1.5사이에 발생한 상품 이벤트 처리결과 ( '''', ''승인'',''반려'' ) 3가지 항목만 입력가능',
  `ct_qty` int(11) NOT NULL DEFAULT 0,
  `ct_stock_qty` int(11) DEFAULT 0,
  `ct_barcode` text NOT NULL DEFAULT '',
  `ct_notax` tinyint(4) NOT NULL DEFAULT 0,
  `io_id` varchar(255) NOT NULL DEFAULT '',
  `io_type` tinyint(4) NOT NULL DEFAULT 0,
  `ct_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ct_memo` text NOT NULL DEFAULT '' COMMENT '/* 23.03.08 : 서원 - 코멘트추가 */ 상품에 대한 반려 사유 저장 항목',
  `ordLendStrDtm` datetime DEFAULT NULL COMMENT '대여시작일',
  `ordLendEndDtm` datetime DEFAULT NULL COMMENT '대여종료일',
  `ct_delivery_yn` varchar(1) NOT NULL DEFAULT 'N',
  `ct_delivery_company` longtext DEFAULT NULL,
  `ct_delivery_num` longtext DEFAULT NULL,
  `ct_delivery_cnt` int(11) NOT NULL DEFAULT 1,
  `ct_sync_ctid` int(11) DEFAULT NULL COMMENT '/* 23.03.08 : 서원 - 추가 */ g5_shop_cart테이블의 ct_id 연결 값',
  PRIMARY KEY (`ct_id`),
  KEY `it_id` (`it_id`,`order_send_id`,`order_send_id2`,`ct_status`,`ct_sync_ctid`,`mb_id`)
) ENGINE=InnoDB AUTO_INCREMENT=461178 DEFAULT CHARSET=utf8
");
}

$query = "SHOW tables LIKE 'g5_shop_api_log'";//api 로그 테이블 유무 확인
$wzres = sql_num_rows( sql_query($query) );
if($wzres < 1) {
	sql_query("CREATE TABLE `g5_shop_api_log` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_send_id` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT '1.5주문ID',
  `mb_id` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT '사업소ID',
  `log_type` TINYINT(5) NOT NULL DEFAULT 0 COMMENT '로그구분 1:수신,2:송신,3:로그',
  `log_cont` VARCHAR(255) NULL DEFAULT ''  COMMENT '로그 내용',
  `log_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '로그 기록 시간' ,
  PRIMARY KEY (`log_id`),
  KEY `order_send_id` (`order_send_id`),
  KEY `mb_id` (`mb_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");
}

auth_check($auth[$sub_menu], "r");
add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);

$g5['title'] = '수급자 주문관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

if (!$fr_date) $fr_date = "";
if (!$to_date) $to_date = "";

//if( preg_match("/[^0-9]/", $fr_date) ) $fr_date = '';
//if( preg_match("/[^0-9]/", $to_date) ) $to_date = '';


// 검색처리
$select = array();
$where = array();



$sel_stat = isset($_GET['sel_stat']) ? get_search_string($_GET['sel_stat']) : '';
$od_release = isset($_GET['od_release']) ? get_search_string($_GET['od_release']) : '0';
$fr_date = isset($_GET['fr_date']) ? get_search_string($_GET['fr_date']) : '';
$to_date = isset($_GET['to_date']) ? get_search_string($_GET['to_date']) : '';

$penId = isset($_GET['penId']) ? get_search_string($_GET['penId']) : '';
$search = isset($_GET['search']) ? get_search_string($_GET['search']) : '';
$sel_field = isset($_GET['sel_field']) && in_array($_GET['sel_field'], array('od_penNm', 'od_penLtmNum','mb_id','od_name','od_b_name','od_b_name2','od_b_id','od_d_addr','od_b_tel','od_b_hp','all','order_send_id')) ? $_GET['sel_field'] : '';
$page_rows = isset($_GET['page_rows']) ? get_search_string($_GET['page_rows']) : 10;//


$qstr = '';
if($sel_stat !="" && $sel_stat != "all"){
	$where[] = " od_status = '$sel_stat' ";
	$qstr .= 'sel_stat=$sel_stat';
}else{
	// 작성 완료된 계약서 & 마이그레이션 된 계약서만 + 간편 계약서로 생성된 계약서
	//$where[] = " (E.dc_status = '2' OR E.dc_status = '3' OR E.dc_status = '11' OR E.dc_status = '4' OR E.dc_status = '5') ";
	$qstr .= 'sel_stat=';
}


$qstr .= '&amp;od_release='.$od_release;

if($fr_date != "" || $to_date != ""){//날짜 검색 조건이 있을 경우
	$where_od = "od_time";		
		
	if($to_date == ""){//시작 날짜만 있을 경우 >=
		$where[] = " $where_od >= '$fr_date 00:00:00' ";
	}elseif($fr_date == ""){//종료 날짜만 있을 경우 <=
		$where[] = " $where_od <= '$to_date 23:59:59' ";
	}else{// 둘다 있을 경우 between
		$where[] = " $where_od between '$fr_date 00:00:00' and '$to_date 23:59:59' "; 
	}
	$qstr .= '&amp;fr_date='.$fr_date.'&amp;to_date='.$to_date;
}


// 정렬 순서
$sql_order = ' ORDER BY ';
$index_order = '';
$index_order = 'DESC';
$sql_order .= 'O.od_time ' . $index_order;

//

//$select[] = ' m.mb_id ';
$select[] = ' O.* ';
$select[] = ' M.mb_entId as entId';
$sql_join = ' LEFT outer JOIN g5_member as M on M.mb_id = O.mb_id ';
//$sql_group = " GROUP BY E.dc_id";
$where2 = "";
if ($search != '' && $sel_field != '') {
  $qstr .= '&amp;search='.urlencode($search);
  $qstr .= '&amp;sel_field='.urlencode($sel_field);
	if($sel_field == "all"){
		$where[] = " (O.order_send_id like '%{$search}%' or O.mb_id like '{$search}' or O.od_name like '%{$search}%' or O.od_b_id like '%{$search}%' or O.od_b_name like '%{$search}%' or O.od_b_tel like '%{$search}%' or O.od_b_hp like '%{$search}%' or O.od_b_addr1 like '%{$search}%' or O.od_b_addr2 like '%{$search}%' or O.od_b_addr3 like '%{$search}%' or O.od_b_name2 like '%{$search}%' or O.od_penNm like '%{$search}%'  or O.od_penLtmNum like '%{$search}%') ";

	}elseif($sel_field == "od_b_addr"){
		$where[] = " (O.od_b_addr1 like '%{$search}%' or O.od_b_addr2 like '%{$search}%') or O.od_b_addr3 like '%{$search}%') ";
	}else{
	  $where[] = " $sel_field like '%{$search}%' ";
	}
}
// select 배열 처리
$sql_select = implode(', ', $select);

// where 배열 처리
$sql_where = " WHERE 1 ";//" WHERE E.entId = '{$entId}' ";
if($where) {
  $sql_where .= ' AND '.implode(' AND ', $where);
}

$sql_from = " FROM `g5_shop_order_api` O";
$total_count = sql_fetch("SELECT COUNT(R.order_send_id) AS cnt FROM (SELECT O.order_send_id" . $sql_from . $sql_join . $sql_where . $sql_group . ') R')['cnt'];

$total_page = ceil($total_count / $page_rows); // 전체 페이지 계산
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " LIMIT {$from_record}, {$page_rows} ";

$result = sql_query("SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . $sql_group . $sql_order . $sql_limit);
//echo "SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . $sql_group . $sql_order . $sql_limit;
$qstr1 = $qstr.'&amp;page_rows='.$page_rows;

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
</style>
<form name="frmsamhwaorderlist" id="frmsamhwaorderlist" style="margin-top:-15px;">
<input type="hidden" name="page_rows" id="page_rows" value="<?=$page_rows?>">
  <div class="new_form">
    <table class="new_form_table" id="search_detail_table">
      <tr>
        <th>검색조건</th>
        <td>
          <div class="sel_field">
		  상태&nbsp;&nbsp;&nbsp;
            <select name="sel_stat" id="sel_stat" style="width:120px;">
            <option value="all" <?php echo get_selected($sel_stat, 'all'); ?>>전체</option>
            <option value="승인대기" <?php echo get_selected($sel_stat, '승인대기'); ?>>승인대기</option>
            <option value="주문처리" <?php echo get_selected($sel_stat, '주문처리'); ?>>주문처리</option>
            <!--option value="결제진행" <?php echo get_selected($sel_stat, '결제진행'); ?>>결제진행</option-->
            <option value="결제완료" <?php echo get_selected($sel_stat, '결제완료'); ?>>결제완료</option>
			<option value="주문완료" <?php echo get_selected($sel_stat, '주문완료'); ?>>주문완료</option>
			<option value="출고완료" <?php echo get_selected($sel_stat, '출고완료'); ?>>출고완료</option>
			<option value="작성완료" <?php echo get_selected($sel_stat, '작성완료'); ?>>계약서서명요청</option>
			<option value="서명완료" <?php echo get_selected($sel_stat, '서명완료'); ?>>계약서서명완료</option>
			<option value="주문취소" <?php echo get_selected($sel_stat, '주문취소'); ?>>주문취소</option>
          </select>
          </div>
        </td>
      </tr>
	  <tr>
        <th>기간조건</th>
        <td>
          <div class="sel_field">
            <input type="radio" id="od_release_all" name="od_release" value="0" <?php echo option_array_checked('0', $od_release); ?>> <label for="od_release_all"> 구매요청일</label>&nbsp;
			<input type="button" value="전체" id="select_date_all" name="select_date" class="select_date newbutton"/>
			<input type="button" value="오늘" id="select_date_today" name="select_date" class="select_date newbutton"/>
            <input type="button" value="어제" id="select_date_yesterday" name="select_date" class="select_date newbutton"/>
            <input type="button" value="일주일" id="select_date_sevendays" name="select_date" class="select_date newbutton"/>
            <input type="button" value="이번달" id="select_date_thismonth" name="select_date" class="select_date newbutton"/>
            <input type="button" value="지난달" id="select_date_lastmonth" name="select_date" class="select_date newbutton"/>            
            <input type="text" id="fr_date" class="date" name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete='off'> ~
            <input type="text" id="to_date" class="date" name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete='off'>
          </div>
        </td>
      </tr>
      <tr>
        <th>키워드검색</th>
        <td>
          <select name="sel_field" id="sel_field">
            <option value="all" <?php echo get_selected($sel_field, 'all'); ?>>전체</option>
			<option value="order_send_id" <?php echo get_selected($sel_field, 'order_send_id'); ?>>주문번호</option>
			<option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>사업소ID</option>
            <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>사업소명</option>
            <option value="od_b_id" <?php echo get_selected($sel_field, 'od_b_id'); ?>>구매자ID</option>
			<option value="od_b_name" <?php echo get_selected($sel_field, 'od_b_name'); ?>>구매자명</option>
			<option value="od_b_tel" <?php echo get_selected($sel_field, 'od_b_tel'); ?>>구매자연락처</option>
            <option value="od_penNm" <?php echo get_selected($sel_field, 'od_penNm'); ?>>수급자명</option>
            <option value="od_penLtmNum" <?php echo get_selected($sel_field, 'od_penLtmNum'); ?>>수급자번호</option>
			<option value="od_b_addr" <?php echo get_selected($sel_field, 'od_b_addr'); ?>>배송지</option>
			<option value="od_b_name2" <?php echo get_selected($sel_field, 'od_b_name2'); ?>>수령인이름</option>
			<option value="od_b_hp" <?php echo get_selected($sel_field, 'od_b_hp'); ?>>수령인연락처</option>
          </select>
          <input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">          
          <input type="submit" value="검색" class="newbutton">
        </td>
      </tr>
    </table>
  </div>
</form>

<div style="margin:-5px 0px 0px 20px; width:300px;float:left">
	검색 개수 : <?php echo $total_count; ?> 건
</div>
<div style="margin:-13px 20px 10px 20px; float:right;right:0px;">
	<select name="page_rows" id="page_rows2" onChange="javascript:$('#page_rows').val(this.value);$('#frmsamhwaorderlist').submit();">
        <option value="10" <?php echo get_selected($page_rows, '10'); ?>>10개씩보기</option>
		<option value="15" <?php echo get_selected($page_rows, '15'); ?>>15개씩보기</option>
        <option value="20" <?php echo get_selected($page_rows, '20'); ?>>20개씩보기</option>
        <option value="50" <?php echo get_selected($page_rows, '50'); ?>>50개씩보기</option>
        <option value="100" <?php echo get_selected($page_rows, '100'); ?>>100개씩보기</option>
        <option value="200" <?php echo get_selected($page_rows, '200'); ?>>200개씩보기</option>
    </select>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <thead>
    <tr>
        <th scope="col">주문번호</th>
		<th scope="col">사업소ID</th>
        <th scope="col">사업소명</th>
        <th scope="col">수급자명</th>
		<th scope="col">수급자번호</th>
		<th scope="col">본인부담금율</th>
		<th scope="col">구매자ID</th>
		<th scope="col">구매자명</th>
		<th scope="col">구매자연락처</th>
		<th scope="col">수급자와관계</th>
		<th scope="col">배송지</th>
		<th scope="col">수령인이름</th>
		<th scope="col">수령인연락처</th>
		<th scope="col">주문일자</th>
		<th scope="col">상태</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $num = $total_count -(($page-1)*$page_rows)- $i ;

        $bg = 'bg'.($i%2);
		switch($row["relation_code"]){
			case "0":
			$relation_code = "본인";
			break;
			case "1": 
			$relation_code = "가족";
			break;
			case "2":
			$relation_code= "친족";
			break;
			case "3":
			$relation_code = "기타";
			break;
			default : $relation_code = "본인";
			break;
		}
			if($row["od_penTypeNm"] == ""){			
				$data = array();
				$send_data = [];
				$send_data["penNm"] = $row["od_penNm"];
				$send_data["penLtmNum"] = "L".$row["od_penLtmNum"];//수급자번호에 L 제거 저장
				$send_data["usrId"] = $row["mb_id"];
				$send_data["entId"] = $row["entId"];
				$send_data["pageNum"] = $page;
				$send_data["pageSize"] = 1;
				$send_data["appCd"] = "01";

				$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);
				$list = [];
				foreach($res['data'] as $data) {
				  $checklist = ['penRecGraCd', 'penTypeCd', 'penExpiDtm', 'penBirth'];
				  $is_incomplete = false;
				  foreach($checklist as $check) {
					if(!$data[$check])
					  $is_incomplete = true;
				  }
				  if(!in_array($data['penGender'], ['남', '여']))
					$is_incomplete = true;
				  if($data['penTypeCd'] == '04' && !$data['penJumin'])
					$is_incomplete = true;
				  if($data['penExpiDtm']) {
					// 유효기간 만료일 지난 수급자는 유효기간 입력 후 주문하게 함
					$expired_dtm = substr($data['penExpiDtm'], -10);
					if (strtotime(date("Y-m-d")) > strtotime($expired_dtm)) {
					  $data['penExpiDtm'] = '';
					  $is_incomplete = true;
					}
				  }
				  $data['incomplete'] = $is_incomplete;
				}
			}
	

    ?>
    <tr class="<?php echo $bg; ?>" <?php if($row["od_status"] == "주문취소"){?>style="color:red;"<?php }?>>
        <td align="center"><a href="javascript:go_detail('<?=$row["order_send_id"]?>','<?=$row["mb_id"]?>');"><?=$row["order_send_id"]; ?></a></td>
		<td align="center"><a href="javascript:get_mb_id('<?=$row["entId"]?>');"><?=$row["mb_id"]; ?></a></td>
        <td align="center"><a href="javascript:get_mb_id('<?=$row["entId"]?>');"><?=$row["od_name"]; ?></a></td>
		<td align="center"><?=mb_substr($row["od_penNm"],0,1)."*".mb_substr($row["od_penNm"],-1); ?></td>
		<td align="center"><?="L".(substr(str_replace("L","",$row["od_penLtmNum"]),0,2)."******".substr(str_replace("L","",$row["od_penLtmNum"]),8,2)); ?></td>
   		<td align="center"><?=($row["od_penTypeNm"] != "")?$row["od_penTypeNm"]:$data["penTypeNm"]; ?></td>
		<td align="center"><?=$row["od_b_id"]; ?></td>
		<td align="center"><?=mb_substr($row["od_b_name"],0,1)."*".mb_substr($row["od_b_name"],-1); ?></td>
		<td align="center"><?=substr($row["od_b_tel"],0,6)."****"; ?></td>
		<td align="center"><?=$relation_code; ?></td>
		<td align="center"><?=($row["od_b_addr1"] != "")?mb_substr($row["od_b_addr1"],0,6)."*************":"";//.$row["penAddrDtl"]; ?></td>
		<td align="center"><?=($row["od_b_name2"]!="")?(mb_substr($row["od_b_name2"],0,1)."*".mb_substr($row["od_b_name2"],-1)):""; ?></td>
		<td align="center"><?=substr($row["od_b_hp"],0,6)."****"; ?></td>
		<td align="center"><?=($row["od_time"]); ?></td>
		<td align="center"><?=$row["od_status"]; ?></td>

    </tr>
    <?php
    }

    if ($i == 0) {
        echo '<tr><td colspan="15" class="empty_table">자료가 없습니다.</td></tr>';
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

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr1&amp;page="); ?>
<div class="btn_fixed_top" style="text-align:right;padding-right:15px !important;">
    <a href="javascript:downloadExcel();" class="btn " style="background:#339900;color:#fff;border-radius: 3px;">엑셀다운로드</a>
</div>

<script>
$(function() {
	var EXCEL_DOWNLOADER = null;
    $("#fr_date, #to_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yymmdd",
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
});

function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

function downloadExcel() {
    var href = './pen_order.excel.download.php';

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

function get_mb_id(entid){
	$.ajax({
      url: "./ajax.eform.php",
      type: "POST",
      data: {
        "mb_entId": entid
      },
      dataType: "json",
      async: false,
      cache: false,
      success: function(data, textStatus) {
        window.open('/adm/member_form.php?sst=&sod=&sfl=&stx=&page=&w=u&mb_id='+data);
		}
    });	
}

function go_detail(order_send_id,mb_id){
	location.href="./pen_order_detail.php?order_send_id="+order_send_id+"&mb_id="+mb_id+"&<?=str_replace('&amp;','&',$qstr1)?>&page=<?=$page?>";
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
