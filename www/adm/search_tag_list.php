<?php
$sub_menu = '300410';
include_once('./_common.php');

$query = "SHOW tables LIKE 'g5_search_tag'";//api 오더 테이블 유무 확인
$wzres = sql_num_rows( sql_query($query) );
//$query = "SHOW tables LIKE 'g5_shop_cart_api'";//api 카트 테이블 유무 확인
//$wzres = sql_fetch( $query );

if($wzres < 1) {
    sql_query("CREATE TABLE `g5_search_tag` (
  `st_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `st_text` varchar(255) DEFAULT NULL COMMENT '검색태그',
  `fr_date` date DEFAULT NULL COMMENT '시작일자',
  `to_date` date DEFAULT NULL COMMENT '종료일자',
  `c_count` int(11) DEFAULT 0 COMMENT '클릭수',
  `useYN` varchar(5) DEFAULT NULL COMMENT '사용유무',
  `type` tinyint(2) DEFAULT NULL COMMENT '유형 1:검색,2:링크이동',
  `link` varchar(255) DEFAULT NULL COMMENT '링크주소',
  `memo` varchar(255) DEFAULT NULL COMMENT '관리자메모',
  `order_num` varchar(11) DEFAULT NULL COMMENT '우선순위 최대99까지',
  `reg_date` datetime DEFAULT NULL COMMENT '등록일',
  PRIMARY KEY (`st_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8", true);
}
auth_check($auth[$sub_menu], "r");

$g5['title'] = '검색태그 관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

////////////////////////////////////////////////////////////////////////////////////////////////////
if($auth_check = auth_check($auth[$sub_menu], "r"))
// 초기 3개월 범위 적용
$fr_date = $_REQUEST["fr_date"];
$to_date = $_REQUEST["to_date"];
$qstr .= '&amp;page_rows='.$page_rows;

$sql_u = "update g5_search_tag set useYN='N' where !('".date("Y-m-d")."' between fr_date and to_date)";

sql_query($sql_u);

$where = array();
//$where[] = "";

$search_tag = get_search_string($search_tag);//검색어
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';


if ($search_tag != "") {//검색태그 검색
  $search_tag = trim($search_tag);
  $where[] = " st_text like '%$search_tag%' ";
  $qstr .="&amp;search_tag=".$_REQUEST["search_tag"];
}

// 사용여부
if (gettype($useYN) == 'string' && $useYN !== '') {
    $where[] = " ( useYN = '$useYN') ";
	$qstr .= "&amp;useYN=".$useYN;
}


if ($fr_date && $to_date) {
  $where[] = " (reg_date between '$fr_date 00:00:00' and '$to_date 23:59:59') ";
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
$sql_common = "
  FROM
    g5_search_tag
";

$sql_common .= $sql_search;

// 페이지네이트
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql, true);
$total_count = $row['cnt'];
$page_rows = (int)$page_rows ? (int)$page_rows : $config['cf_page_rows'];
$rows = $page_rows;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$order_by = "";
if($order != ""){//우선순위 정렬 작은 수가 위로
	$order_by = "`order_num` IS NULL ASC, `order_num`='' ASC, order_num asc,order_num ASC, ";
}
$sql_order = " ORDER BY ".$order_by."reg_date DESC ";//기본 정렬

$sql_common .= $sql_order;

$sql  = "
  select *
  $sql_common
  limit $from_record, $rows
";

$result = sql_query($sql);
//echo $sql;
$tag_list = array();
while( $row = sql_fetch_array($result) ) {
  $tag_list[] = $row;
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

<form name="frmsamhwatag_list" id="frmsamhwatag_list" style="margin-top:-15px;" method="get" action="search_tag_list.php">
<input type="hidden" name="page_rows" id="page_rows" value="<?=$page_rows?>">
<input type="hidden" name="all_date" id="all_date" value="<?=$all_date?>">
<input type="hidden" name="order" id="order" value="<?=$order?>">
<input type="hidden" name="st_id" id="st_id" value="">
  <div class="new_form">
    <table class="new_form_table" id="search_detail_table">
	  <tr>
        <th>검색조건</th>
        <td >
			사용여부&nbsp;&nbsp;
            <select name="useYN" id="useYN">
            <option value="" >전체</option>
            <option value="Y" <?php echo get_selected($useYN, 'Y'); ?>>사용</option>
            <option value="N" <?php echo get_selected($useYN, 'N'); ?>>미사용</option>
			</select>
        </td>
      </tr>
	  <tr>
        <th>검색기간</th>
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
        <th>검색어</th>
        <td>			
			검색태그&nbsp;&nbsp;
			<input type="text" name="search_tag" value="<?php echo $search_tag; ?>" id="search_tag" class="frm_input" autocomplete="off" style="width:350px;" maxlength="15">&nbsp;&nbsp;
			<input type="submit" value="검색" class="newbutton" style="background-color:#000000;color:#ffffff;width:70px;">
        </td>
      </tr>	  
    </table>	
  </div>  
</form>
<div style="margin:-20px 0px 15px 20px;">
	※ 상시 5개 전시를 권장합니다. ※ 디바이스에 따라 검색태그 노출 개수가 다릅니다.(PC 5개, 모바일 3개) 
</div>
<div style="margin:0px 0px 5px 20px; float:left">
	<input type="button" value="선택 사용" id="" name="" class="newbutton2" onclick="change_useYN('Y')"/>
	<input type="button" value="선택 미사용" id="" name="" class="newbutton2" onclick="change_useYN('N')"/>
</div>

<div style="margin:0px 20px 0px 0px; float:right;right:0px;">
	<input type="button" value="우선순위 정렬보기" id="" name="" class="newbutton2" onclick="order_by();"/>
	<select name="page_rows" id="page_rows2" onChange="javascript:$('#page_rows').val(this.value);$('#frmsamhwatag_list').submit();" style="width:130px;height:33px;">
		<option value="" <?=($list_num=="")?"selected":""?>> 시스템 기본 보기 </option>
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
        <th scope="col" width="30px;"><input type="checkbox" name="all_chk" id="all_chk" class="frm_input"></th>
		<th scope="col" width="70px;">ID</th>
		<th scope="col">검색태그</th>
		<th scope="col" width="150px;">시작일자</th>
		<th scope="col" width="150px;">종료일자</th>
		<th scope="col" width="100px;">클릭 수</th>
		<th scope="col" width="100px;">사용여부</th>
		<th scope="col" width="150px;">등록일시</th>
		<th scope="col" width="100px;">우선순위</th>
		<th scope="col" width="200px;">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
	$i = 0;
    foreach($tag_list as $targ) {
        $bg = ($targ["useYN"] == "Y")?'bg1':"bg0";
		
		
    ?>
    <tr class="<?php echo $bg; ?>">
        <td align="center"><input type="checkbox" name="st_id[]" id="<?=$targ["st_id"];?>" value="<?=$targ["st_id"];?>" class="frm_input checkSelect chkbox"></td>
		<td align="center"><?=$targ["st_id"];//ID ?></td>
		<td align="left"><?=$targ['st_text'];//검색태그 ?></td>
		<td align="center"><?=$targ["fr_date"];//시작일 ?></td>
		<td align="center"><?=$targ["to_date"];//종료일자 ?></td>
		<td align="center"><?=number_format($targ["c_count"]);//클릭수 ?></td>
		<td align="center"><?=($targ['useYN'] == "Y")?"사용":"미사용"; ?></td>
		<td align="center"><?=$targ["reg_date"];//등록일시 ?></td>
		<td align="center"><?=$targ["order_num"];//우선순위 ?></a></td>
		<td align="center"><input type="button" value="수정" id="select_date_today" name="select_date" class="newbutton3" style="width:40%;" onClick="insert_update('<?=$targ["st_id"];?>')"/> <input type="button" value="삭제" id="select_date_all" name="select_date" class="newbutton4" style="width:40%;" onClick="search_tag_del('<?=$targ["st_id"]?>','<?=$targ['st_text']?>')"/></td>
	</tr>
    <?php
    $i++;
	}

    if ($i == 0) {
        echo '<tr><td colspan="10" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
<div class="btn_fixed_top">
    <a href="javascript:insert_update();" class="btn " style="background:#ff3399;color:#fff;width:100px;">등록</a>
</div>
    


<script>
$(function() {

	
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

	$("#all_chk").click(function() {
		if($("#all_chk").is(":checked")){
			$(".checkSelect").prop("checked", true);
			//$("#all_chk2").val("전체해제");
		}else{
			$(".checkSelect").prop("checked", false);
			//$("#all_chk2").val("전체선택");
		}
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
	});
});

function order_by(){
	$("#order").val("order");
	$('#frmsamhwatag_list').submit();
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
		alert("항목을 선택해주십시오.");
		return false;
	}
	return true;
}

function change_useYN(useYN){
	if(select_check() == true){
		var st_id = [];
		var s_tag = $("input[name='st_id[]']:checked");
		for(var i = 0; i < s_tag.length; i++) {
			st_id.push($(s_tag[i]).val());
		}
		change_useYN2(st_id, useYN);
	}
}
function change_useYN2(st_id, useYN) {
	$.ajax({
		method: "POST",
		url: "./ajax.search_tag_useYN.php",
		async:false,
		data: {
			'st_id': st_id,
			'useYN': useYN
		},
	}).done(function (data) {
		console.log(data);
		if (data.message == 'OK') {
			alert('상태가 변경되었습니다.');  
			location.reload();
		} else {
			alert(data.message);			
		}
	});
}

function search_tag_del(st_id,tag) {
	if(confirm("정말 삭제하시겠습니까?")){
		$.ajax({
			method: "POST",
			url: "./ajax.search_tag_del.php",
			async:false,
			data: {
				'st_id': st_id
			},
		}).done(function (data) {
			console.log(data);
			if (data.message == 'OK') {
				alert(tag+'이(가) 삭제 되었습니다.');  
				location.reload();
			} else {
				alert(data.message);			
			}
		});
	}
}

function insert_update(st_id = ''){
	$("#st_id").val(st_id);
	document.frmsamhwatag_list.action = "search_tag_edit.php";
	document.frmsamhwatag_list.submit();
}

</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
