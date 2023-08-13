<?php
$sub_menu = '300100';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '게시판 조회';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

////////////////////////////////////////////////////////////////////////////////////////////////////
if($auth_check = auth_check($auth[$sub_menu], "r"))
// 초기 3개월 범위 적용
$fr_date = $_REQUEST["fr_date"];
$to_date = $_REQUEST["to_date"];
$qstr .= '&amp;page_rows='.$page_rows;



$where = array();
//$where[] = "";

$search_tag = get_search_string($search_tag);//검색어
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';


if ($search_tag != "") {//검색태그 검색
  $search_tag = trim($search_tag);
  if($search_select == ""){//전체 조회 시
	$where[] = " (a.wr_subject like '%$search_tag%' or a.mb_id like '%$search_tag%' or a.wr_id like '%$search_tag%' or a.mb_id in (select mb_id from g5_member where mb_name like '%$search_tag%')) ";
  }elseif($search_select == "mb_name"){//닉네임 조회 시
	$where[] = " a.mb_id in (select mb_id from g5_member where mb_name like '%$search_tag%') ";
  }else{
	$where[] = " a.".$search_select." like '%$search_tag%' ";
  }
  $qstr .="&amp;search_tag=".$_REQUEST["search_tag"]."&amp;search_select=".$search_select;
}


if ($fr_date && $to_date) {
  $where[] = " (a.wr_datetime between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
  $qstr .= "&amp;fr_date=".$fr_date."&amp;to_date=".$to_date;
}

$where_count = $where;

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

$sql_b2 = $sql_b = "select bo_table from {$g5['board_table']} where 1";
if($_REQUEST["bo_table"] != ""){
	$sql_b .= " and bo_table='".$_REQUEST["bo_table"]."'";
	$qstr .= "&amp;bo_table=".$bo_table;
}
$result_b = sql_query($sql_b);//게시판 테이블 구하기
$i = 0;
$union = "";
while($row_b = sql_fetch_array($result_b)){
	$union = ($i == 0)?"":" UNION ";
	$query = "SHOW COLUMNS FROM g5_write_".$row_b["bo_table"]." WHERE `Field` = 'wr_update';";//수정날짜 확인
	$wzres = sql_fetch( $query );
	if(!$wzres['Field']) {
		sql_query(" ALTER TABLE g5_write_".$row_b["bo_table"]." ADD `wr_update` datetime NOT NULL default '0000-00-00 00:00:00' AFTER `wr_datetime`", false);
		sql_query(" ALTER TABLE g5_write_".$row_b["bo_table"]." ADD `wr_update_mb_id` varchar(20) NULL default '' AFTER `wr_update`", false);
	}
	$wr_update = ",a.wr_update";

	$sql_common .= $union."(SELECT '".$row_b["bo_table"]."' as bo_table,a.wr_id,a.wr_subject,a.mb_id".$wr_update."
,(SELECT mb_name FROM g5_member WHERE mb_id=a.mb_id) AS mb_name
,a.as_update,a.wr_datetime,a.wr_hit
,(SELECT COUNT(DISTINCT mb_id) FROM (SELECT mb_id,wr_id FROM g5_board_log WHERE bo_table='".$row_b["bo_table"]."') AS m WHERE m.wr_id=a.wr_id) AS log_cnt
FROM g5_write_".$row_b["bo_table"]." AS a ".$sql_search."
)";
	$sql_common2 .= $union."(SELECT count(*) cnt2
FROM g5_write_".$row_b["bo_table"]." AS a ".$sql_search."
)";
	$i = 1;
}

// 페이지네이트
$sql = " select sum(cnt2) as cnt from (" .$sql_common2.") as c";
$row = sql_fetch($sql, true);
$total_count = $row['cnt'];
$page_rows = (int)$page_rows ? (int)$page_rows : $config['cf_page_rows'];
$rows = $page_rows;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql_order = " ORDER BY wr_datetime DESC ";//기본 정렬

//$sql_common .= $sql_order;

$sql  = "
  select * from (
  $sql_common
  ) as c
  $sql_order limit $from_record, $rows
";
//echo $sql;
$result = sql_query($sql);

$board_list = array();
while( $row = sql_fetch_array($result) ) {
  $board_list[] = $row;
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
		border: 1px solid #0033ff;
		color: #fff;
		background-color:#0033ff;
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

<form name="frmsamhwatag_list" id="frmsamhwatag_list" style="margin-top:-15px;" method="get">
<input type="hidden" name="page_rows" id="page_rows" value="<?=$page_rows?>">
<input type="hidden" name="all_date" id="all_date" value="<?=$all_date?>">
<input type="hidden" name="order" id="order" value="<?=$order?>">
<input type="hidden" name="st_id" id="st_id" value="">
  <div class="new_form">
    <table class="new_form_table" id="search_detail_table">
	  <tr>
        <th>검색 조건</th>
        <td >
			Table&nbsp;&nbsp;
            <select name="bo_table" id="bo_table" style="width:150px;">
            <option value="" >전체</option>
   <?php $result_b2 = sql_query($sql_b2);//게시판 테이블 구하기
   while($row_b2 = sql_fetch_array($result_b2)){
   ?>
			<option value="<?=$row_b2["bo_table"]?>" <?php echo get_selected($bo_table, $row_b2["bo_table"]); ?>><?=$row_b2["bo_table"]?></option>
	<?php }?>
			</select>
        </td>
      </tr>
	  <tr>
        <th>기간 조건</th>
        <td>
          <div class="sel_field">
			등록일자&nbsp;&nbsp;
			<input type="button" value="전체" id="select_date_all" name="select_date" class="select_date newbutton4"/>
			<input type="button" value="오늘" id="select_date_today" name="select_date" class="select_date newbutton"/>
            <input type="button" value="어제" id="select_date_yesterday" name="select_date" class="select_date newbutton"/>
            <input type="button" value="일주일" id="select_date_sevendays" name="select_date" class="select_date newbutton"/>
            <input type="button" value="이번달" id="select_date_thismonth" name="select_date" class="select_date newbutton"/>
            <input type="button" value="지난달" id="select_date_lastmonth" name="select_date" class="select_date newbutton"/>    
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="text" id="fr_date" class="date" name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete='off' readonly> ~
            <input type="text" id="to_date" class="date" name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete='off' readonly>
          </div>
        </td>
      </tr>
      <tr>
        <th>키워드 검색</th>
        <td>			
			<select name="search_select" id="search_select" style="width:120px;">
            <option value="" >전체</option>
            <option value="wr_subject" <?php echo get_selected($search_select, 'wr_subject'); ?>>제목</option>
            <option value="mb_name" <?php echo get_selected($search_select, 'mb_name'); ?>>글쓴이(닉네임)</option>
			<option value="mb_id" <?php echo get_selected($search_select, 'mb_id'); ?>>회원아이디</option>
			<option value="wr_id" <?php echo get_selected($search_select, 'wr_id'); ?>>wr_id</option>
			</select>
			<input type="text" name="search_tag" value="<?php echo $search_tag; ?>" id="search_tag" class="frm_input" autocomplete="off" style="width:350px;" maxlength="15">&nbsp;&nbsp;
			<input type="submit" value="검색" class="newbutton" style="background-color:#000000;color:#ffffff;width:70px;">
        </td>
      </tr>	  
    </table>	
  </div>  
</form>

<div style="margin:0px 0px 5px 20px; float:left">
	검색개수 : <?=number_format($total_count)?> 건
</div>

<div style="margin:-10px 20px 5px 0px; float:right;right:0px;">
	<select name="page_rows" id="page_rows2" onChange="javascript:$('#page_rows').val(this.value);$('#frmsamhwatag_list').submit();" style="width:130px;height:33px;">
		<option value="" <?=($list_num=="")?"selected":""?>>쇼핑몰 설정으로 보기 </option>
		<option value="50"  <?=($page_rows =='50')?"selected":"";?>>50개씩보기</option>
        <option value="100" <?=($page_rows=='100')?"selected":"";?>>100개씩보기</option>
        <option value="500" <?=($page_rows=='500')?"selected":"";?>>500개씩보기</option>
    </select>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <thead>
    <tr>
		<th scope="col" width="120px;">Table</th>
		<th scope="col" width="50px;">wr_id</th>
		<th scope="col">제목</th>
		<th scope="col" width="170px;">글쓴이(닉네임)</th>
		<th scope="col" width="170px;">회원아이디</th>
		<th scope="col" width="150px;">수정일</th>
		<th scope="col" width="150px;">생성일</th>
		<th scope="col" width="80px;">조회수</th>
		<th scope="col" width="80px;">조회사용자</th>
    </tr>
    </thead>
    <tbody>
    <?php
	$i = 0;
    foreach($board_list as $row) {
        $bg = ($i%2 == "1")?'bg1':"bg0";
		
		
    ?>
    <tr class="<?php echo $bg; ?>">
		<td align="center"><?=$row["bo_table"];//테이블명 ?></td>
		<td align="center"><?=$row['wr_id'];//wr_id ?></td>
		<td align="center"><a href="/bbs/board.php?bo_table=<?=$row["bo_table"];//테이블명 ?>&wr_id=<?=$row['wr_id'];//wr_id ?>" target="_blank"><?=$row["wr_subject"];//제목 ?></a></td>
		<td align="center"><?=$row["mb_name"];//글쓴이(닉네임) ?></td>
		<td align="center"><?=$row["mb_id"];//회원아이디 ?></td>
		<td align="center"><?=($row['wr_update'] == "0000-00-00 00:00:00")?"":$row['wr_update'];//수정일 ?></td>
		<td align="center"><?=$row["wr_datetime"];//생성일 ?></td>
		<td align="center"><?=number_format($row["wr_hit"]);//조회수 ?></a></td>
		<td align="center"><a href="board_search_log.php?bo_table=<?=$bo_table?>&search_select=wr_id&search_tag=<?=$row['wr_id']?>" target="_blank"><?=number_format($row["log_cnt"]);//조회사용자?></a></td>
	</tr>
    <?php
    $i++;
	}

    if ($i == 0) {
        echo '<tr><td colspan="9" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
   


<script>
$(function() {
	$("#fr_date, #to_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        maxDate: "+2y"
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

});

function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  $('#all_date').val("");
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}


</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
