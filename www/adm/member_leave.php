<?php
$sub_menu = '200110';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$query = "SHOW tables LIKE 'g5_member_leave'";//탈퇴신청 관리 테이블 확인
$wzres = sql_num_rows( sql_query($query) );
if($wzres < 1) {
	sql_query("CREATE TABLE `g5_member_leave` (
  `ml_no` int(11) NOT NULL COMMENT '탈퇴 신청번호',
  `mb_id` varchar(30) NOT NULL COMMENT '탈퇴 신청인',
  `mb_leave_date2` varchar(20) NOT NULL COMMENT '탈퇴 신청일',
  `mb_leave_resn` text DEFAULT NULL COMMENT '탈퇴 사유',
  `mb_leave_date3` varchar(20) DEFAULT NULL COMMENT '탈퇴 거부일',
  `mb_leave_reject_resn` text DEFAULT NULL COMMENT '탈퇴 거부 사유',
  `mb_leave_confirm` varchar(50) DEFAULT NULL COMMENT '탈퇴 승인자',
  KEY `mb_id` (`mb_id`,`mb_leave_date2`,`mb_leave_date3`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
}

$g5['title'] = '회원탈퇴 관리';
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
$fr_date = isset($_GET['fr_date']) ? get_search_string($_GET['fr_date']) : '';
$to_date = isset($_GET['to_date']) ? get_search_string($_GET['to_date']) : '';


$search = isset($_GET['search']) ? get_search_string($_GET['search']) : '';
$sel_field = isset($_GET['sel_field']) && in_array($_GET['sel_field'], array('mb_id', 'mb_name','mb_nick','mb_email','mb_hp','mb_leave_confirm')) ? $_GET['sel_field'] : '';
$page_rows = isset($_GET['page_rows']) ? get_search_string($_GET['page_rows']) : 10;//


$qstr = '';
if($sel_stat !="" && $sel_stat != "all"){
	if($sel_stat == 'mb_leave_date'){//승인완료
		$where[] = " m3.mb_leave_date!='' ";

	}elseif($sel_stat == 'mb_leave_date2'){//대기
		$where[] = "m3.mb_leave_date='' and m.mb_leave_date2!='' and m.mb_leave_date3='' ";
	}else{//거부
		$where[] = "m3.mb_leave_date='' and m.mb_leave_date3!='' ";
	}
	$qstr .= 'sel_stat='.$sel_stat;
}else{//전체
	// 작성 완료된 계약서 & 마이그레이션 된 계약서만 + 간편 계약서로 생성된 계약서
	$where[] = " (m.mb_leave_date2!='') ";
	$qstr .= 'sel_stat=';
}


if($fr_date != "" || $to_date != ""){//날짜 검색 조건이 있을 경우
	if($sel_stat != 'all'){//승인대기,승인완료,거부
		$where_st = ($sel_stat == 'mb_leave_date')?"m3.".$sel_stat :"m.".$sel_stat ;
		if($to_date == ""){//시작 날짜만 있을 경우 >=
			$where[] = " $where_st >= '".str_replace("-",'',$fr_date)."' ";
		}elseif($fr_date == ""){//종료 날짜만 있을 경우 <=
			$where[] = " $where_st <= '".str_replace("-",'',$to_date)."' ";
		}else{// 둘다 있을 경우 between
			$where[] = " $where_st between '".str_replace("-",'',$fr_date)."' and '".str_replace("-",'',$to_date)."' "; 
		}
	}else{
		if($to_date == ""){//시작 날짜만 있을 경우 >=
			$where[] = " (m3.mb_leave_date >= '".str_replace("-",'',$fr_date)."' or m.mb_leave_date2 >= '".str_replace("-",'',$fr_date)."'  or m.mb_leave_date3 >= '".str_replace("-",'',$fr_date)."') ";
		}elseif($fr_date == ""){//종료 날짜만 있을 경우 <=
			$where[] = " (m3.mb_leave_date <= '".str_replace("-",'',$to_date)."' or m.mb_leave_date2 <= '".str_replace("-",'',$to_date)."' or m.mb_leave_date3 <= '".str_replace("-",'',$to_date)."') ";
		}else{// 둘다 있을 경우 between
			$where[] = " (m3.mb_leave_date between '".str_replace("-",'',$fr_date)."' and '".str_replace("-",'',$to_date)."' or m.mb_leave_date2  between '".str_replace("-",'',$fr_date)."' and '".str_replace("-",'',$to_date)."' or m.mb_leave_date3  between '".str_replace("-",'',$fr_date)."' and '".str_replace("-",'',$to_date)."') "; 
		}
	}
		
	
	$qstr .= '&amp;fr_date='.$fr_date.'&amp;to_date='.$to_date;
}


// 정렬 순서
$sql_order = ' ORDER BY ';
$index_order = '';
$index_order = 'DESC';
$sql_order .= 'm.mb_leave_date2 ' . $index_order;
$sql_order .= ',m3.mb_leave_date ' . $index_order;

//

//$select[] = ' m.mb_id ';

$sql_group = "";
$where2 = "";
if ($search != '' && $sel_field != '') {
  $qstr .= '&amp;search='.urlencode($search);
  $qstr .= '&amp;sel_field='.urlencode($sel_field);
  
	if($sel_field == "mb_leave_confirm"){
		$sql_join .=" INNER JOIN (
						SELECT mb_id FROM g5_member WHERE mb_name = '{$search}'
					) m2 ON m.mb_leave_confirm = m2.mb_id ";
	}elseif($sel_field == "mb_id"){
		$where[] = " m3.$sel_field like '{$search}' ";	
	}else{
		$where[] = " m3.$sel_field like '%{$search}%' ";
	}
}

// select 배열 처리
$select[] = "CASE WHEN m.mb_id = m3.mb_id THEN m.mb_leave_date2 ELSE '' END AS mb_leave_date2";
$select[] = "CASE WHEN m.mb_id = m3.mb_id THEN m.mb_leave_date3 ELSE '' END AS mb_leave_date3";
$select[] = "CASE WHEN m.mb_id = m3.mb_id THEN m.mb_leave_resn ELSE '' END AS mb_leave_resn";
$select[] = "CASE WHEN m.mb_id = m3.mb_id THEN m.mb_leave_reject_resn ELSE '' END AS mb_leave_reject_resn";
$select[] = "CASE WHEN m.mb_id = m3.mb_id THEN m.mb_leave_confirm ELSE '' END AS mb_leave_confirm";
$select[] = "CASE WHEN m.mb_id = m3.mb_id THEN (SELECT mb_name FROM `g5_member` WHERE mb_id=m.mb_leave_confirm) ELSE '' END AS mb_leave_confirm_name";
$select[] = "CASE WHEN m.mb_id = m3.mb_id THEN m.ml_no ELSE '' END AS ml_no";
$select[] = "m3.*";
$sql_select = implode(', ', $select);

// where 배열 처리
$sql_where = " WHERE 1 ";//" WHERE E.entId = '{$entId}' ";
if($where) {
  $sql_where .= ' AND '.implode(' AND ', $where);
}
$sql_join .=" left join g5_member m3 on m3.mb_id = m.mb_id";
$sql_from = " FROM `g5_member_leave` m";
$total_count = sql_fetch("SELECT COUNT(R.mb_id) AS cnt FROM (SELECT m.mb_id" . $sql_from . $sql_join . $sql_where . $sql_group . ') R')['cnt'];

$total_page = ceil($total_count / $page_rows); // 전체 페이지 계산
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " LIMIT {$from_record}, {$page_rows} ";

$result = sql_query("SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . $sql_group . $sql_order . $sql_limit);
//echo "SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . $sql_group . $sql_order . $sql_limit;
$qstr1 = $qstr.'&amp;page_rows='.$page_rows;


function cutStr($string, $num = 35) {
	$tail="...&nbsp;&nbsp;&nbsp;<a href='javascript:resn_view(\"".$string."\",\"탈퇴 사유\");' style='color:#0099ff;text-decoration:underline !important;'>더보기</a>";
	if(strlen($string) < $num) return $string;  //자를길이보다 문자열이 작으면 그냥 리턴
	$i = 0; $returnstr = '';
	$whnum = 0; // 글자수....
	while($whnum < $num) {
	  if(ord($string[$i]) > 127) {
		$returnstr .= substr($string, $i, 3);
		$i += 3;    //한글일 경우 3byte 옮김
	  } else {
		$returnstr .= substr($string, $i, 1);
		$i++;
	  }
	  $whnum++;
	}
	  return $returnstr.$tail;
}

?>
<style>
	.popup_box2 { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index: 9999; background: rgba(0, 0, 0, 0.8); 	}
	.popup_box_con { padding:20px; position: relative; background: #ffffff; z-index: 99999; margin-left:-206px;	}
</style>

<div style="margin:5px 0px 0px 20px; color:red; float:left;font-weight:bold;">
	주의) 사업소의 미수금 등 탈퇴가 불가한 사항들을 모두 체크 후 이상 없을 시 승인해 주세요.
</div>
<form name="frmsamhwaorderlist" id="frmsamhwaorderlist">
<input type="hidden" name="page_rows" id="page_rows" value="<?=$page_rows?>">
  <div class="new_form">  
    <table class="new_form_table" id="search_detail_table">
      <tr>
        <th>검색조건</th>
        <td>
          <div class="sel_field">
		    <select name="sel_stat" id="sel_stat">
            <option value="all" <?php echo get_selected($sel_stat, 'all'); ?>>전체</option>
            <option value="mb_leave_date2" <?php echo get_selected($sel_stat, 'mb_leave_date2'); ?>>대기</option>
            <option value="mb_leave_date" <?php echo get_selected($sel_stat, 'mb_leave_date'); ?>>승인 완료</option>
			<option value="mb_leave_date3" <?php echo get_selected($sel_stat, 'mb_leave_date3'); ?>>거부 완료</option>
          </select>
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
            <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원아이디</option>
            <option value="mb_name" <?php echo get_selected($sel_field, 'mb_name'); ?>>이름</option>
            <option value="mb_nick" <?php echo get_selected($sel_field, 'mb_nick'); ?>>닉네임</option>
            <option value="mb_email" <?php echo get_selected($sel_field, 'mb_email'); ?>>이메일</option>
			<option value="mb_hp" <?php echo get_selected($sel_field, 'mb_hp'); ?>>휴대폰번호</option>
			<option value="mb_leave_confirm" <?php echo get_selected($sel_field, 'mb_leave_confirm'); ?>>승인자</option>
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
        <th scope="col" width="30px;"></th>
        <th scope="col" width="60px;">상태</th>
		<th scope="col" width="130px;">회원아이디</th>
        <th scope="col" width="130px;">이름</th>
		<th scope="col" width="130px;">닉네임</th>
        <th scope="col" width="100px;">사업자번호</th>
		<th scope="col" width="180px;">이메일</th>
		<th scope="col" width="100px;">휴대폰번호</th>
		<th scope="col">탈퇴 사유</th>	
		<th scope="col" width="75px;">탈퇴 요청일</th>
		<th scope="col" width="75px;">탈퇴 승인일 / 거부일</th>
		<th scope="col" width="110px;">승인자 / 거부자</th>
		<th scope="col" width="70px;">탈퇴 거부</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $num = $total_count -(($page-1)*$page_rows)- $i ;

        $bg = 'bg'.($i%2);


    ?>
    <tr class="<?php echo $bg; ?>">
        <td align="center"><input type="checkbox" name="mb_id[]" value="<?=$row["mb_id"]."|".$row["ml_no"]?>" <?=($row["mb_leave_date"] == "" && $row["mb_leave_date3"] == "")?"":" disabled";?>></td>
        <td align="center"><?=($row["mb_leave_date"] != "")?"승인완료":(($row["mb_leave_date3"] != "")?"거부완료":"대기");?></td>
		<td align="center"><a href="/adm/member_form.php?w=u&mb_id=<?=$row["mb_id"]?>" target="_blank"><?=$row["mb_id"]?></a></td>
		<td align="center"><?=$row["mb_name"]?></td>
		<td align="center"><?=$row["mb_nick"]?></td>
		<td align="center"><?=$row["mb_giup_bnum"]?></td>
		<td align="center"><?=$row["mb_email"]?></td>
   		<td align="center"><?=$row["mb_hp"]?></td>
		<td align="left"><?=($row["mb_leave_resn"] == "")?"":cutStr($row["mb_leave_resn"]);?></td>
		<td align="center"><?=($row["mb_leave_date2"])?substr($row["mb_leave_date2"],0,4)."-".substr($row["mb_leave_date2"],4,2)."-".substr($row["mb_leave_date2"],6,2):"-";?></td>
		<td align="center"><?=($row["mb_leave_date"] != "")?substr($row["mb_leave_date"],0,4)."-".substr($row["mb_leave_date"],4,2)."-".substr($row["mb_leave_date"],6,2) : (($row["mb_leave_date3"] != "")?substr($row["mb_leave_date3"],0,4)."-".substr($row["mb_leave_date3"],4,2)."-".substr($row["mb_leave_date3"],6,2):"-");?></td>
		<td align="center"><?=($row["mb_leave_confirm_name"] == "")?"-":$row["mb_leave_confirm_name"];?><?=($row["mb_leave_date3"] != "" && $row["mb_leave_date"] == "")?"&nbsp;&nbsp;&nbsp;<a href=\"javascript:resn_view('".$row["mb_leave_reject_resn"]."','거부 사유');\" style='color:#0099ff;text-decoration:underline !important;'>거부사유</a>":"";?></td>
		<td align="center"><?=($row["mb_leave_date"]!="" || $row["mb_leave_date3"]!="")?"":"<a href=\"javascript:go_view('".$row['mb_id']."',".$row['ml_no']."');\" class=\"btn btn_01\" style=\"border-radius: 3px;\">탈퇴거부</a>";?></td>

    </tr>
    <?php
    }

    if ($i == 0) {
        echo '<tr><td colspan="13" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<?php //요청사항 확인 모달팝업 ?>
<div id="popup_box3" class="popup_box2">
    <div id="" class="popup_box_con" style="height:410px;margin-top:-205px;margin-left:-200px;width:400px;left:50%;top:50%;">
		<div style="top:0px;width:100%;font-size:20px;font-weight:bold;">
		탈퇴 거부
		</div>
		
		<div class="form-group" style="margin-top:20px;">
            <ul>
				<li style="font-weight:bold;">탈퇴 거부 사유를 적어주세요.</li>
				<li style="color:red;font-weight:bold;margin-bottom:20px;">* 알림톡으로 안내될 내용이니 신중히 작성해 주세요.</li>
				<li><textarea id="leave_reject_resn" name="leave_reject_resn" rows="10" cols="" style="padding:10px;resize: none;" maxlength="500" placeholder="탈퇴 거부 사유를 적어주세요."></textarea>
				<input type="hidden" name="mb_id" id="mb_id"><input type="hidden" name="ml_no" id="ml_no"></li>
				<li><p class="f_s14 d-flex justify-content-end" id="counter" style="text-align:right;">0/500</p></li>
            </ul>			
        </div>		

		<div style="text-align:center;bottom:0px;width:100%;margin-top:5px;">
			<button type="button" class="btn btn_close" onClick="info_close()" style="border-radius: 5px;border:1px solid #333; width:49%; height:50px; font-size:16px;">취소</button>
			<button type="button" class="btn btn-black btn_close" onClick="leave_reject()" style="border-radius: 5px; width:49%; height:50px; font-size:16px;">탈퇴 거부하기</button>
		</div>
	</div>
	
</div>

<?php //탈퇴사유 확인 모달팝업 ?>
<div id="popup_box4" class="popup_box2">
    <div id="" class="popup_box_con" style="height:336px;margin-top:-168px;margin-left:-200px;width:400px;left:50%;top:50%;">
		<div style="top:0px;width:100%;font-size:20px;font-weight:bold;">
		<span id="resn_title">탈퇴 사유</span>
		<span style="float:right;cursor:pointer;" onClick="info_close2()">x</span>
		</div>
		
		<div class="form-group" style="margin-top:20px;">
            <ul>
				<li><textarea id="leave_resn" name="leave_resn" rows="14" cols="" readonly style="padding:10px;resize: none;"></textarea></li>
            </ul>			
        </div>		

	</div>
	
</div>


<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr1&amp;page="); ?>
<div class="btn_fixed_top">
    <a href="javascript:leave_confirm();" class="btn btn_03" style="border-radius: 3px;">탈퇴 승인</a>
</div>

<script>
$(function() {
	$('#leave_reject_resn').keyup(function (e){
				var content = $(this).val();
				$('#counter').html(content.length+"/500");    //글자수 실시간 카운팅    
				if (content.length > 500){        
					alert("최대 500자까지 입력 가능합니다.");        
					$(this).val(content.substring(0, 501));
					$('#counter').html("500/500");    
				}
			});
    
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

function go_view(a,b){
	$('#mb_id').val(a);
	$('#ml_no').val(b);
	$('body').addClass('modal-open');
	$('#popup_box3').show();
}

function resn_view(a,b){
	$("#resn_title").text(b);
	$("#leave_resn").val(a);
	$('body').addClass('modal-open');
	$('#popup_box4').show();
}

function info_close(){
	$('#popup_box3').hide();
	$('body').removeClass('modal-open');
	$('#mb_id').val("");
	$("#ml_no").val("");
	$('#leave_reject_resn').val("");
}

function info_close2(){
	$('#popup_box4').hide();
	$('body').removeClass('modal-open');
	$("#leave_resn").val("");
}

function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

function leave_reject(){//탈퇴 거부
	if($('#leave_reject_resn').val() == ""){
		alert("탈퇴 거부 사유를 적어 주세요.");
		$('#leave_reject_resn').focus();
		return false;
	}else{
		$.ajax({
		  url: "./ajax.mb_leave.php",
		  type: "POST",
		  data: {
			"mb_id": $("#mb_id").val(),
			"ml_no": $("#ml_no").val(),
			"leave_reject_resn": $('#leave_reject_resn').val(),
			"mode" : "reject"
		  },
		  dataType: "json",
		  async: false,
		  cache: false,
		  success: function(data, textStatus) {
			alert("탈퇴가 거부 처리 되었습니다.");
			location.reload();
			}
		})
		.fail(function($xhr) {
            var data = $xhr.responseJSON;
            alert(data && data.message);
        });
	}
}

function leave_confirm(){//탈퇴 처리
		var mb_ids = [];
        var mb_id = $("input[name='mb_id[]']:checked");
        for (var i = 0; i < mb_id.length; i++) {
            mb_ids.push($(mb_id[i]).val());
        }

        if (!mb_ids.length) {
            alert('탈퇴 승인할 회원을 선택해주세요.');
            return false;			
        }

	if(confirm("탈퇴 승인 즉시 탈퇴 처리가 완료됩니다.\n정말 승인하시겠습니까?")){
		$.ajax({
		  url: "./ajax.mb_leave.php",
		  type: "POST",
		  data: {
			"mb_id": mb_ids,
			"mode": "confirm"
		  },
		  dataType: "json",
		  async: false,
		  cache: false,
		  success: function(data, textStatus) {
			alert(mb_id.length+"명의 탈퇴 승인이 처리 되었습니다.");
			location.reload();
			}
		})
		.fail(function($xhr) {
            var data = $xhr.responseJSON;
			alert(data && data.message);
        });	
	}
}

</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>