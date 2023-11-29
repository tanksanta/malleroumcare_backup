<?php
$sub_menu = '500810';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");
add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);

$g5['title'] = '대여계약 급여제공기록 관리';
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
$sel_field = isset($_GET['sel_field']) && in_array($_GET['sel_field'], array('penNm', 'penLtmNum','entNm','mb_id','all')) ? $_GET['sel_field'] : '';
$page_rows = isset($_GET['page_rows']) ? get_search_string($_GET['page_rows']) : 10;//


$qstr = '';
if($sel_stat !="" && $sel_stat != "all"){
	if($sel_stat == 3){
		$where[] = " (E.rh_status='$sel_stat' || E.rh_status='2') ";
	}else{
		$where[] = " E.rh_status='$sel_stat'";
	}
	$qstr .= 'sel_stat='.$sel_stat;
}else{
	// 작성 완료된 계약서 & 마이그레이션 된 계약서만 + 간편 계약서로 생성된 계약서
	$where[] = " (E.rh_status = '2' OR E.rh_status = '3' OR E.rh_status = '11' OR E.rh_status = '4' OR E.rh_status = '5') ";
	$qstr .= 'sel_stat=';
}


$qstr .= '&amp;od_release='.$od_release;

if($fr_date != "" || $to_date != ""){//날짜 검색 조건이 있을 경우
	if($od_release == 1){//서명요청일
		$where_od = "dc_sign_send_datetime";
	}elseif($od_release == 2){//서명완료일
		$where_od = "dc_sign_datetime";
	}elseif($od_release == "" ||  $od_release == 0){//생성일
		$where_od = "reg_date";	
	}
		
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
$sql_order .= 'E.reg_date ' . $index_order;

//

//$select[] = ' m.mb_id ';

$sql_group = " GROUP BY E.rh_id";
$where2 = "";
if ($search != '' && $sel_field != '') {
  $qstr .= '&amp;search='.urlencode($search);
  $qstr .= '&amp;sel_field='.urlencode($sel_field);
  
	if($sel_field == "all"){
		$where[] = " (ef.penNm like '%{$search}%' or ef.penLtmNum like '%{$search}%' or ef.entNm like '%{$search}%') ";
		$sql_join .=" left outer join (
						SELECT mb_entId FROM g5_member WHERE mb_id = '{$search}'
					) M ON 1 = 1 ";
	}elseif($sel_field == "mb_id"){
	  $sql_join .=" INNER JOIN (
						SELECT mb_entId FROM g5_member WHERE mb_id = '{$search}'
					) M ON M.mb_entId = E.entId ";
	}else{
	  $where[] = " ef.$sel_field like '%{$search}%' ";
	}
}
// select 배열 처리
$select[] = "E.*";
$select[] = "ef.penNm";
$select[] = "ef.penRecGraNm";
$select[] = "ef.penTypeNm";
$select[] = "ef.penLtmNum";
$select[] = "(SELECT mb_id FROM `g5_member` WHERE mb_entId=E.entId) AS mb_id";
$sql_select = implode(', ', $select);

// where 배열 처리
$sql_where = " WHERE 1 ";//" WHERE E.entId = '{$entId}' ";
if($where) {
  $sql_where .= ' AND '.implode(' AND ', $where);
}
$sql_join .=" LEFT OUTER JOIN `eform_document` AS ef ON E.penId = ef.penId AND ef.dc_id =(
	SELECT dc_id FROM`eform_document` WHERE penId = E.penId ORDER BY dc_datetime DESC LIMIT 1
) ";
$sql_from = " FROM `eform_rent_hist` E";
$total_count = sql_fetch("SELECT COUNT(R.rh_id) AS cnt FROM (SELECT E.rh_id" . $sql_from . $sql_join . $sql_where . $sql_group . ') R')['cnt'];

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
		  진행상태&nbsp;&nbsp;&nbsp;
            <select name="sel_stat" id="sel_stat">
            <option value="all" <?php echo get_selected($sel_stat, 'all'); ?>>전체</option>
            <option value="11" <?php echo get_selected($sel_stat, '11'); ?>>기록지생성</option>
            <option value="4" <?php echo get_selected($sel_stat, '4'); ?>>서명요청</option>
            <option value="3" <?php echo get_selected($sel_stat, '3'); ?>>서명완료</option>
            <option value="5" <?php echo get_selected($sel_stat, '5'); ?>>서명거절</option>
          </select>
          </div>
        </td>
      </tr>
	  <tr>
        <th>기간구분</th>
        <td>
          <div class="sel_field">
            <input type="radio" id="od_release_all" name="od_release" value="0" <?php echo option_array_checked('0', $od_release); ?>> <label for="od_release_all"> 생성일자</label>&nbsp;
            <input type="radio" id="od_release_0" name="od_release" value="1" <?php echo option_array_checked('1', $od_release); ?>> <label for="od_release_0"> 서명요청일</label>&nbsp;
            <input type="radio" id="od_release_1" name="od_release" value="2" <?php echo option_array_checked('2', $od_release); ?>> <label for="od_release_1"> 서명완료일</label>&nbsp;
          </div>
        </td>
      </tr>
	  <tr>
        <th>검색기간</th>
        <td>
          <div class="sel_field">
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
        <th>검색어</th>
        <td>
          <select name="sel_field" id="sel_field" style="width:137px;">
            <option value="all" <?php echo get_selected($sel_field, 'all'); ?>>전체</option>
            <option value="entNm" <?php echo get_selected($sel_field, 'entNm'); ?>>사업소명</option>
            <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>사업소ID</option>
            <option value="penNm" <?php echo get_selected($sel_field, 'penNm'); ?>>수급자명</option>
            <option value="penLtmNum" <?php echo get_selected($sel_field, 'penLtmNum'); ?>>장기요양인정번호</option>
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
        <th scope="col">No.</th>
        <th scope="col">사업소명</th>
		<th scope="col">사업소ID</th>
        <th scope="col">수급자명</th>
		<th scope="col">인정번호</th>
        <th scope="col">인정등급</th>
		<th scope="col">본인부담금율</th>
		<th scope="col">상품수량</th>	
		<th scope="col">기록지 생성일자</th>
		<th scope="col">서명요청일자</th>
		<th scope="col">서명완료일자</th>
		<th scope="col">상태</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $num = $total_count -(($page-1)*$page_rows)- $i ;

        $bg = 'bg'.($i%2);
		switch($row["rh_status"]){
			case "11":
			$rh_status = "계약서생성";
			break;
			case "2": case "3":
			$rh_status = "서명완료";
			break;
			case "4":
			$rh_status = "서명요청";
			break;
			case "5":
			$rh_status = "서명거절";
			break;
		}

    ?>
    <tr class="<?php echo $bg; ?>">
        <td align="center" onClick="go_detail('<?=$row["rh_id"]?>')"><?=$num; ?></td>
        <td align="center"><a href="javascript:get_mb_id('<?=$row["entId"]?>');"><?=$row["entNm"]; ?></a></td>
		<td align="center"><a href="javascript:get_mb_id('<?=$row["entId"]?>');"><?=$row["mb_id"]; ?></a></td>
		<td align="center" onClick="go_detail('<?=$row["rh_id"]?>')"><?=(mb_substr($row["penNm"],0,1)."*".mb_substr($row["penNm"],-1)); ?></td>
		<td align="center" onClick="go_detail('<?=$row["rh_id"]?>')"><?=(substr($row["penLtmNum"],0,4)."****".substr($row["penLtmNum"],7,4)); ?></td>
   		<td align="center" onClick="go_detail('<?=$row["rh_id"]?>')"><?=$row["penRecGraNm"]; ?></td>
		<td align="center" onClick="go_detail('<?=$row["rh_id"]?>')"><?=$row["penTypeNm"]; ?></td>
		<td align="center" onClick="go_detail('<?=$row["rh_id"]?>')"><?=number_format(count(explode(",",$row["it_ids"]))); ?></td>
		<td align="center" onClick="go_detail('<?=$row["rh_id"]?>')"><?=substr($row["reg_date"],0,16); ?></td>
		<td align="center" onClick="go_detail('<?=$row["rh_id"]?>')"><?=($row["dc_sign_send_datetime"] == "0000-00-00 00:00:00" || $row["dc_sign_send_datetime"] == "")?"-":substr($row["dc_sign_send_datetime"],0,16); ?></td>
		<td align="center" onClick="go_detail('<?=$row["rh_id"]?>')"><?=($row["dc_sign_datetime"] == "0000-00-00 00:00:00")?"-":substr($row["dc_sign_datetime"],0,16); ?></td>
		<td align="center" onClick="go_detail('<?=$row["rh_id"]?>')"><?=$rh_status; ?></td>

    </tr>
    <?php
    }

    if ($i == 0) {
        echo '<tr><td colspan="12" class="empty_table">자료가 없습니다.</td></tr>';
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
});

function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

function downloadExcel() {
    var href = './eform_rent.excel.download.php';

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

function go_detail(dc_id){
	location.href="./eform_rent_detail.php?dc_id="+dc_id+"&<?=str_replace('&amp;','&',$qstr1)?>&page=<?=$page?>";
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
