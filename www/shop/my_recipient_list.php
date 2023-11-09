<?php
include_once("./_common.php");

$query = "SHOW COLUMNS FROM g5_member WHERE `Field` = 'cert_reg_sts';";//인증서 항목 없을 시 추가
$wzres = sql_fetch( $query );
if(!$wzres['Field']) {
    sql_query("ALTER TABLE `g5_member`
	ADD `cert_reg_sts` varchar(20) DEFAULT NULL COMMENT '사업소의 공인 인증서 등록 상태' AFTER mb_account,
	ADD `cert_reg_date` date DEFAULT NULL COMMENT '공인인증서 최초 등록일' AFTER cert_reg_sts,
	ADD `cert_data_ref` text NOT NULL COMMENT '공인인증서 key ref file' AFTER cert_reg_date", true);
}

if($member["mb_id"] != $_COOKIE['membre_id']){//이전 로그인 사용자 쿠키가 남아있을 경우 초기화
	setcookie("total_rental_price", "", time() + (12*3600));
	setcookie("total_amt", "", time() + (12*3600));
	setcookie("expire_30", "", time() + (12*3600));
	setcookie("rental_30", "", time() + (12*3600));
	setcookie("membre_id", "", time() + (12*3600));
	setcookie("shearch_time","", time() + (12*3600));
}

define('_RECIPIENT_', true);

include_once("./_head.php");

if(!$is_member){
  alert("접근 권한이 없습니다.");
  exit;
}

// 연결기간(3일) 지난 수급자 연결해제
recipient_link_clean();

// 수급자 활동 알림
// category_limit_noti();

$write_pages = 5;
$page_rows = $_COOKIE["recipient_page_rows"] ? $_COOKIE["recipient_page_rows"] : 10;
$page = $_GET["page"] ?? 1;
$page_option = $_GET['option'] ?? 'none'; // none : expire : rental

$send_data = [];
$send_data["usrId"] = $member["mb_id"];
$send_data["entId"] = $member["mb_entId"];
if ( $page_option === 'expire' || $page_option === 'rental'){// 30일 이내 수급자 클릭 시만 활성화
	$res_tot = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);
}
$send_data["pageNum"] = $page;
$send_data["pageSize"] = $page_rows;
if ($sel_field === 'penNm') {
  $send_data['penNm'] = $search;
}
if ($sel_field === 'penLtmNum') {
  $send_data['penLtmNum'] = $search;
}
if ($sel_field === 'penProNm') {
  $send_data['penProNm'] = $search;
}
$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);

if ( $page_option === 'expire' || $page_option === 'rental'){// 30일 이내 수급자 클릭 시만 활성화	
	$res['data']=null;
}

echo "<script>console.log('page :  ".$page."');</script>"; // 정상

$list = [];
// count on meeting with expire date within one month
$list_reipient_on_dealine = [];
$timetoday = date('y-m-d');
$today = new DateTime($timetoday);

$count_on_deadline = 0;
$total_amt_pention_in_list = 0;
$penDefaultAmt = 1600000; 
$remainAmt = 0;
$exp_idx = 0;
$remainAmtList = [];
$day_diff = 0;
$arr_item_pl = array(); // 수급자별 우리샵 판매/계약/대역 내역 구하기
$arr_rental = array();
$arr_expire = array();

//print_r($jjjres);
//echo "<br/>===================================================<br/>";
//print_r($res['data']);

// 수급자 수가 페이지 최대 수보다 많을때(페이징 필요할 때)
/*
if ($res["total"] > $page_rows) 	{
  $send_data["pageNum"] = $page;
  $send_data["pageSize"] = $res['total'];
}   
$res_tot = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);
*/

// echo "<script>console.log('res_tot :  ".json_encode($res_tot)."');</script>"; // 정상
// echo "<script>console.log('res_tot count :  ".count($res_tot['data'])."');</script>"; // 정상
// echo "<script>console.log('res :  ".json_encode($res)."');</script>"; // 정상
// echo "<script>console.log('res count :  ".count($res['data'])."');</script>"; // 정상

// 남은 적용기간이 30일 이내인 수급자 찾기(전체목록에서는 카운트만 사용)
if ( $page_option === 'expire' || $page_option === 'rental'){// 30일 이내 수급자 클릭 시만 활성화
for ($idx = 0 ; $idx < $res_tot['total'] ; $idx++){
  $thatday = new DateTime(substr($res_tot['data'][$idx]['penAppEdDtm'],0,8)); // 각 수급자의 적용기간 끝나는 시점
  if (strtotime($timetoday) < strtotime(substr($res_tot['data'][$idx]['penAppEdDtm'],0,8))) { // 오늘보다 적용기간 끝나는 날짜가 뒤여야 한다
    $day_diff = fmod(date_diff($thatday,$today)->days,365); // 날짜 차이 계산(365로 나눈 나머지를 구하는 이유는 모름)

    if ($day_diff< 30) { // 30일 아래로 차이나면
				$count_on_deadline++; // 상단에 올라갈 카운트 계산
        $arr_expire[] = $res_tot['data'][$idx];
			}

    if (date_diff($thatday,$today)->days > 0) { // 적용구간 남아있는 수급자 
      $sql = "SELECT * FROM PEN_PURCHASE_HIST WHERE pen_ltm_num='".$res_tot['data'][$idx]['penLtmNum']."' AND (CURDATE() between PEN_EXPI_ST_DTM and PEN_EXPI_ED_DTM) AND ENT_ID='".$member["mb_entId"]."';";
	  $tmp_row = sql_fetch($sql); //get_totalamt_pention($res_tot['data'][$idx]['penLtmNum']); // PEN_PURCHASE_HIST 기록 가져오기
      $remainAmt = $tmp_row['PEN_BUDGET']; 
      $tmpLtmNum =$res_tot['data'][$idx]['penLtmNum']; 
      $remainAmtList[$tmpLtmNum]["remainAmt"] = $remainAmt> 0?$remainAmt:$penDefaultAmt;
      //$remainAmtList[$tmpLtmNum]["updatedDate"] = $tmp_row['MODIFY_DTM'];
      $remainAmtList[$tmpLtmNum]["updatedDate"] =
        substr($res_tot['data'][$idx]['modifyDtm'],0,4).'-'.substr($res_tot['data'][$idx]['modifyDtm'],4,2).'-'.substr($res_tot['data'][$idx]['modifyDtm'],6,2);

      if ($remainAmt  > 0) {
        $total_amt_pention_in_list += $remainAmt;
      } else {
        $total_amt_pention_in_list += $penDefaultAmt;
      }
    } 

  }

  $thatday = null;
} 
echo "<script>console.log('remainAmtList :  ".json_encode($remainAmtList)."');</script>"; // 정상
}
// 적용기간 종료 명수를 클릭해서 들어온 경우
if ( $page_option === 'expire' ){
  // $res['data'] = $arr_expire;
  $page_start = $page - 1;
  $res['data'] = array_slice($arr_expire, $page_start*$page_rows, $page_rows, true);
  $res['total'] = count($arr_expire);
}

// 대여기간 종료 명수를 클릭해서 들어온 경우
if ( $page_option === 'rental' ){
$rental_list_within_period = []; 
$rental_list_within_period = get_rentalitem_deadline2($member["mb_entId"]);
$total_rental_price = 0;
for ($idx = 0 ; $idx < count($rental_list_within_period); $idx++){
  for($idx_i = 0; $idx_i < $res_tot['total']; $idx_i++) {
    if (strcmp($res_tot['data'][$idx_i]['penLtmNum'], $rental_list_within_period[$idx]['penLtmNum']) == 0) {
      $arr_rental[] = $res_tot['data'][$idx_i];
      $total_rental_price += $rental_list_within_period[$idx]['total_price'];
    }
  }
}


  // $res['data'] = $arr_rental;
  $page_start = $page - 1;
  $res['data'] = array_slice($arr_rental, $page_start*$page_rows, $page_rows, true);
  $res['total'] = count($arr_rental);


echo "<script>console.log('arr_rental :  ".json_encode($arr_rental)."');</script>"; // 정상
echo "<script>console.log('arr_rental :  ".count($arr_rental)."');</script>"; // 정상
}

if($res["data"]) {
 
  foreach($res['data'] as $data) {
    $thatday = new DateTime(substr($data['penAppEdDtm'],0,8)); // 각 수급자의 적용기간 끝나는 시점
	 // if (strtotime($timetoday) < strtotime(substr($data['penAppEdDtm'],0,8))) { // 오늘보다 적용기간 끝나는 날짜가 뒤여야 한다
		$day_diff = fmod(date_diff($thatday,$today)->days,365); // 날짜 차이 계산(365로 나눈 나머지를 구하는 이유는 모름)

		if (date_diff($thatday,$today)->days > 0) { // 적용구간 남아있는 수급자 
		  $sql = "SELECT * FROM PEN_PURCHASE_HIST WHERE pen_ltm_num='".$data['penLtmNum']."' AND (CURDATE() between PEN_EXPI_ST_DTM and PEN_EXPI_ED_DTM) AND ENT_ID='".$member["mb_entId"]."';";
		  $tmp_row = sql_fetch($sql); //get_totalamt_pention($data['penLtmNum']); // PEN_PURCHASE_HIST 기록 가져오기
		  $remainAmt = $tmp_row['PEN_BUDGET']; 
		  $tmpLtmNum =$data['penLtmNum']; 
		  $remainAmtList[$tmpLtmNum]["remainAmt"] = $remainAmt> 0?$remainAmt:$penDefaultAmt;
		  //$remainAmtList[$tmpLtmNum]["updatedDate"] = $tmp_row['MODIFY_DTM'];
		  $remainAmtList[$tmpLtmNum]["updatedDate"] =
			substr($data['modifyDtm'],0,4).'-'.substr($data['modifyDtm'],4,2).'-'.substr($data['modifyDtm'],6,2);


		} 

	  //}

	 // $thatday = null;
	
	// 수급자 필수정보 입력 체크
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
    $data['incomplete'] = $is_incomplete;

    // 욕구사정기록지 작성 체크
    $data['recYn'] = 'N';
    $rec_count = sql_fetch("
      SELECT count(*) as cnt
      FROM recipient_rec_simple
      WHERE penId = '{$data['penId']}' and mb_id = '{$member['mb_id']}'
    ");
    if($rec_count['cnt'] > 0)
      $data['recYn'] = 'Y';
    
    // 수급자 설명 텍스트 (00년생/남|여)
    $pen_desc_txt = [];
    if(substr($data['penBirth'], 2, 2)) $pen_desc_txt[] = substr($data['penBirth'], 2, 2).'년생';
    if($data['penGender']) $pen_desc_txt[] = $data['penGender'];
    if($pen_desc_txt) $pen_desc_txt = ' (' . implode('/', $pen_desc_txt) . ')';
    else $pen_desc_txt = '';
    $data['desc_text'] = $pen_desc_txt;

    // 수급자 1년 계약 건수
    $data['per_year'] = get_recipient_grade_per_year($data['penId'], $data['penExpiStDtm']);

    // 장바구니 개수
    $data['carts'] = get_carts_by_recipient($data['penId']);

    $list[] = $data;
  }
}
// 수급자별 우리샵 구매내역
//$res_arr = get_contract_info($member["mb_entId"], $arr_item_pl);

# 페이징
$total_count = $res["total"];
$total_page = ceil( $total_count / $page_rows ); # 총 페이지


// 예비 수급자
$rows_spare = 5;
$page_spare = $_GET["page_spare"] ?? 1;
$send_data = [];
$send_data["usrId"] = $member["mb_id"];
$send_data["entId"] = $member["mb_entId"];
$send_data["pageNum"] = $page_spare;
$send_data["pageSize"] = $rows_spare;
if ($sel_field === 'penNm') {
  $send_data['penNm'] = $search;
}
if ($sel_field === 'penProNm') {
  $send_data['penProNm'] = $search;
}
$res = get_eroumcare(EROUMCARE_API_SPARE_RECIPIENT_SELECTLIST, $send_data);

$list_spare = [];
if($res["data"]) {
  foreach($res['data'] as $data) {
    // 수급자 설명 텍스트 (00년생/남|여)
    $pen_desc_txt = [];
    if(substr($data['penBirth'], 2, 2)) $pen_desc_txt[] = substr($data['penBirth'], 2, 2).'년생';
    if($data['penGender']) $pen_desc_txt[] = $data['penGender'];
    if($pen_desc_txt) $pen_desc_txt = ' (' . implode('/', $pen_desc_txt) . ')';
    else $pen_desc_txt = '';
    $data['desc_text'] = $pen_desc_txt;
    
    $list_spare[] = $data;
  }
}

$total_count_spare = $res["total"];
$total_page_spare = ceil( $total_count_spare / $rows_spare ); # 총 페이지

/*
$ctr_cnt_sql = "select ENT_ID, PEN_NM, PEN_LTM_NUM, ORD_STATUS,count(*) as cnt from pen_purchase_hist where ent_id = '{$member["mb_entId"]}' and (curdate() between PEN_EXPI_ST_DTM and PEN_EXPI_ED_DTM) group by pen_ltm_num, ord_status;";
$ctr_cnt_res = sql_query($ctr_cnt_sql);
$arr_crt_cnt = [];
while ($res_item = sql_fetch_array($ctr_cnt_res)) { 
  $arr_crt_cnt[$res_item['PEN_LTM_NUM']][$res_item['ORD_STATUS']] = $res_item['cnt'];
}
*/
// 수급자 연결
$links = get_recipient_links($member['mb_id']);


//인증서 업로드 추가 영역
$mobile_agent = "/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS)/";

if(preg_match($mobile_agent, $_SERVER['HTTP_USER_AGENT'])){
	$mobile_yn = "Mobile";
}else{
	$mobile_yn = "Pc";
}
$is_file = false;
if($member["cert_data_ref"] != ""){
	$cert_data_ref =  explode("|",$member["cert_data_ref"]);
	$cert_info = "사용자명:".base64_decode($cert_data_ref[1])." | 만료일자:".base64_decode($cert_data_ref[2]);
	$upload_dir = $_SERVER['DOCUMENT_ROOT']."/data/file/member/tilko/";
	$file_name = base64_encode($cert_data_ref[0]);
	if(file_exists($upload_dir.$file_name.".enc") || file_exists($upload_dir.$file_name.".txt")){
		$is_file = true;
	}
}
//인증서 업로드 추가 영역 끝
?>
<script src="<?php echo G5_JS_URL; ?>/cookie.js"></script>
<script src="<?php echo G5_JS_URL; ?>/client.min.js"></script>
<script src="<?php echo G5_JS_URL; ?>/recipient_device_security.js"></script>
<script src="https://code.iconify.design/iconify-icon/1.0.0/iconify-icon.min.js"></script>
<script>
function get_fingerprint() {
  var client = new ClientJS(); // Create A New Client Object
  // var fingerprint = client.getFingerprint(); // Get Client's Fingerprint

  var ua = client.getBrowserData().ua;
	var canvasPrint = client.getCanvasPrint();
  var fingerprint = client.getCustomFingerprint(ua, canvasPrint);

   console.log( fingerprint );
  return fingerprint;
}

function excelform(url){
  var opt = "width=600,height=450,left=10,top=10";
  window.open(url, "win_excel", opt);
  return false;
}

$(function() {
    $(".BottomButton").click(function() {
        $('html').animate({scrollTop : ($('.footer_area').offset().top)}, 600);
    });
});

$(document.body).on('change','#page_rows',function(){ // 10/15/20/.../개씩 보기 변경 시
  var recipient_page_rows = $("#page_rows option:selected").val();
  // console.log(recipient_page_rows);
  $.cookie('recipient_page_rows', recipient_page_rows, { expires: 365 })
  loading_onoff2('on');
  window.location.reload();
})

function check_all_list(f)
{
  var chk = document.getElementsByName("chk[]");

  for (i=0; i<chk.length; i++)
      chk[i].checked = f.chkall.checked;
}

function check_all_list_spare(f)
{
  var chk = document.getElementsByName("spare_chk[]");

  for (i=0; i<chk.length; i++)
      chk[i].checked = f.chkall_spare.checked;
}

function form_check(act) {
  var requests = [];
  if (act == "seldelete")
  {
    var delete_count = $("input[name^=chk]:checked").size();
    if(delete_count < 1) {
      alert("삭제하실 항목을 하나이상 선택해 주십시오.");
      return false;
    }

    $("input[name^=chk]:checked").each(function(i) {
      var chk_value = this.value.split("|");
      var penId = chk_value[0];
      var sell_count = chk_value[1];
      var data_penNum = $("input[name^=chk]:checked").parent().parent().eq(i).children().find("span[class='data_penNum']").text();

      if (sell_count > 0) {
        alert("선택한 수급자 중 주문이 있는 수급자는 일괄삭제가 불가능합니다. 삭제를 원하시면 상세화면에서 삭제해주시기 바랍니다.");
        requests = [];
        return false;  
      }
      requests.push(
        $.ajax({
          type: 'POST',
          url: './ajax.my.recipient.list.update.php',
          data: {penId : penId, delYn : 'Y', penLtmNum : data_penNum}
        })
      );
    });
  }
  else if (act == "selupdate") {
    var update_count = $("input[name^=chk]:checked").size();
    if(update_count < 1) {
      alert("수정하실 항목을 하나이상 선택해 주십시오.");
      return false;
    }

    if (check_device_security_val() == false) {
      return;
    }

    $("input[name^=chk]:checked").each(function() {
      var chk_value = this.value.split("|");
      var penId = chk_value[0];

      var penRecGraCd = $("#sel_grade option:selected").val();
      var penTypeCd = $("#sel_type_cd option:selected").val();
      requests.push(
        $.ajax({
          type: 'POST',
          url: './ajax.my.recipient.list.update.php',
          data: {penId : penId, penRecGraCd : penRecGraCd, penTypeCd : penTypeCd}
        })
      );
    });
  }
  else if (act == "spare_seldelete")
  {
    var delete_count = $("input[name^=spare_chk]:checked").size();
    if(delete_count < 1) {
      alert("삭제하실 항목을 하나이상 선택해 주십시오.");
      return false;
    }

    $("input[name^=spare_chk]:checked").each(function() {
      requests.push(
        $.ajax({
          type: 'POST',
          url: './ajax.my.recipient.list.update.php',
          data: {penId : this.value, delYn : 'Y', isSpare : 'Y'}
        })
      );
    });
  }
  else if (act == "show_list_all")
  {	loading_onoff2('on');
	location.href = './my_recipient_list.php';
  }

  if (requests.length > 0) {
    $.when.apply($, requests).then(function() {
      alert('완료되었습니다');
	  loading_onoff2('on');
      window.location.reload();
    }, function(error) {
      alert(error.message)
    });
    return true;
  }
  return false;
}

</script>

<style>
.no_content { width:100%; padding: 50px 0; text-align:center; }
#myRecipientListWrap > .titleWrap > .link_notice_wrap {
  position: absolute; top:-20px; right:0; font-weight: normal !important; font-size: 16px; line-height: 20px; height: 60px; padding: 20px 40px; text-align: center;
  color: #fff; background-color: #ee8102; border-radius: 8px;cursor: pointer;
}
@media (max-width: 960px) {
  #myRecipientListWrap > .titleWrap > .link_notice_wrap {
    position: static; margin-bottom: 20px;height:auto;
  }
}
.ajax-loader {
  display: none;
  background-color: rgba(255,255,255,0.7);
  position: absolute;
  z-index: 90;
  width: 100%;
  height:100%;
  top: 0;
  left: 0;
  text-align: center;
  padding-top: 25%;
}

.ajax-loader p {
  margin: 20px;
  color: #666;
}

.rep_update {
  position: relative;
  width: 100%;
  height: 225px;
  padding-top: 10px;
}

.rep_update .btn_update {
  float: right;
  position: relative;
  display: inline-block;
  color: #333;
  font-weight: normal;
  font-size: 14px;
  line-height: 20px;
  height: 30px;
  padding: 5px 20px;
  border-radius: 3px;
  vertical-align: middle;
  background-color: #ee8102;
  color: #fff;
  border: none;
  margin-bottom: 5px;
  display: inline-block;
}
.rep_update .final_update {
  font-weight: normal;
  font-size: 15px;
  line-height: 1;
  width: fit-content;
  height:fit-content;
  float: left;
  position: relative;
  bottom: 0;
  margin:10px 0px 0px 0px;
  vertical-align: bottom
}
.rep_update table {
  border-collapse: collapse;
  border: #D4D4D4 1px solid;
  width: 100%;
  border-radius: 5px;
  border-style: hidden;
  box-shadow: 0 0 0 1px #D4D4D4;
  margin-top: 5px;
}
.rep_update table th{
  border: #D4D4D4 1px solid;
  text-align: center;
  padding: 30px 0px 20px 0px;
  width: 33.3%;
}

.chk_mobile input[type="checkbox"] {
    width: 3em;
    height: 3em;
    background: url('./img/none_check.png') no-repeat center center / contain;
    -webkit-appearance: none;
    appearance: none;
    border-radius: 50%;
    vertical-align: middle;
    outline: none;
    cursor: pointer;
}

.chk_mobile input[type="checkbox"]:checked {
  background: url('./img/check.png') no-repeat center center / contain;
}
.rep_update .con_top{ font-weight: normal; font-size: 20px; line-height: 1; }
.rep_update .con_mid{ font-weight: 800; font-size: 30px; line-height: 2; }
.rep_update .con_bot{ font-weight: normal; font-size: 15px; line-height: 1; }
@media (max-width: 960px) {
  .rep_update table th{ padding: 15px 0px 10px 0px; }
  .rep_update .con_top{ font-size: 16px; }
  .rep_update .con_mid{ font-size: 24px; }
  .rep_update .con_bot{ font-size: 12px; }
  .rep_update { height: 180px; }
  #mobile_rep_list .chk_mobile { width: 10%; }
  #mobile_rep_list .info { width: 75%; }
  #mobile_rep_list .li_box_right_btn { width: 15rem; }
}
@media (max-width: 720px) {
  .rep_update table th{ padding: 15px 0px 10px 0px; }
  .rep_update .con_top{ font-size: 12px; }
  .rep_update .con_mid{ font-size: 18px; }
  .rep_update .con_bot{ font-size: 9px; }
  .chk_mobile input[type="checkbox"] { margin-right: 15rem; }
  .rep_update { height: 170px; }
}
@media (max-width: 480px) {
  .rep_update table th{ padding: 15px 0px 10px 0px; }
  .rep_update .con_top{ font-size: 8px; }
  .rep_update .con_mid{ font-size: 12px; }
  .rep_update .con_bot{ font-size: 5px; }
  .rep_update { height: 160px; }
  .rep_update .final_update { font-size: 12px; }
  .chk_mobile input[type="checkbox"] { width: 2em; height: 2em; margin-right: 10rem; }
  #mobile_rep_list .li_box_right_btn { width: 10rem; }
}
@media (max-width: 430px) {
  .chk_mobile input[type="checkbox"] { width: 1.5em; height: 1.5em; }
}
@media (max-width: 380px) {
  .rep_update .con_mid{ font-size: 10px; }
  .rep_update .con_bot{ font-size: 4px; }
  .rep_update .btn_update { font-size: 12px; padding: 3px 15px; }
}
@media (max-width: 300px) {
  .rep_update .con_mid{ font-size: 10px; }
  .rep_update .con_bot{ font-size: 4px; }
  .rep_update .btn_update { font-size: 12px; padding: 3px 15px; }
  #mobile_rep_list .info { width: 65%; }
  #mobile_rep_list .li_box_right_btn { width: 8rem; }
}
/* 인증서 비번 팝업 - 인증서 업로드 추가 */
#cert_popup_box {
  display: none;
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index:9999;
  background: rgba(0, 0, 0, 0.5);
}
#cert_popup_box iframe {
  width:322px;
  height:307px;
  max-height: 80%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
}

#cert_guide_popup_box {
  display: none;
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index:9999;
  background: rgba(0, 0, 0, 0.5);
}
#cert_guide_popup_box iframe {
  width:850px;
  height:750px;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
}
</style>

<!-- 210204 수급자목록 -->
<div id="myRecipientListWrap">
  <div class="titleWrap" style="margin-bottom:10px;">
    <?php if($links) { ?>
    <div class="link_notice_wrap BottomButton">
      <i class="fa fa-bell-o" aria-hidden="true"></i>
      신규 수급자(<?=get_text($links[0]['rl_pen_name'])?>) 추천되었습니다.
    </div>
    <?php } ?>
    <?php /* 23.09.13 : 서원 - 스마트 서비스 매뉴얼 추가 */?>
    수급자 관리 <a href="javascript:void(0);" onclick="window.open('<?=G5_DATA_URL;?>/file/이로움_스마트_서비스_01_수급자_관리_조회.pdf');" class="thkc_btnManual">사용방법 확인하기</a>
    <div class="page_rows">
        <!-- <select name="orderby" id="orderby">
            <option value="it_id" <?php echo $orderby == 'it_id' || !$orderby ? 'selected' : ''; ?>>최근등록순 정렬</option>
            <option value="it_name" <?php echo $orderby == 'it_name' ? 'selected' : ''; ?>>가나다순 정렬</option>
        </select> -->
        <select name="page_rows" id="page_rows" style="font-weight: normal;" onChange="loading_onoff2('on')">
            <option value="10" <?php echo $page_rows == '10' ? 'selected' : ''; ?>>10개씩보기</option>
            <option value="15" <?php echo $page_rows == '15' ? 'selected' : ''; ?>>15개씩보기</option>
            <option value="20" <?php echo $page_rows == '20' ? 'selected' : ''; ?>>20개씩보기</option>
            <option value="50" <?php echo $page_rows == '50' ? 'selected' : ''; ?>>50개씩보기</option>
            <option value="100" <?php echo $page_rows == '100' ? 'selected' : ''; ?>>100개씩보기</option>
            <option value="200" <?php echo $page_rows == '200' ? 'selected' : ''; ?>>200개씩보기</option>
        </select>
    </div>
  </div>

  <div class="rep_update">
        <!--- <a class = "btn_update" href="./my_recipient_update.php?id=<?=$pen['penId']?>" >변경내역 업데이트</a> -->
        <!-- <p class="final_update">최종 업데이트 YYYY-MM-DD</p> -->
      <table >
          <th>
            <div>
              <p class="con_top">계약 가능 금액</p>
              <p class="con_mid" style="color: #ee0000;" id="total_amt"><?php echo number_format($total_amt_pention_in_list); ?></p>
              <p class="con_bot">* 등록된 전체 수급자의 잔여금액 합계</p>
              <p class="con_bot">(유효 적용기간 기준)</p>
            </div>
          </th>
          <th>
              <p class="con_top">적용구간 종료 30일 이내</p>
      		  <p class="con_mid"><a href="my_recipient_list.php?option=expire" onclick="loading_onoff2('on')" id="expire_30"><?php echo $count_on_deadline ?>명</a></p>
              <p class="con_bot">* 조회일이 포함된 적용구간 기준</p>
          </th>
          <th>
              <p class="con_top">대여기간 종료 30일 이내</p>
              <!-- <p class="con_mid"><a href="my_recipient_list.php?option=rental"><?php echo count($rental_list_within_period)?>명</p> -->
              <p class="con_mid"><a href="my_recipient_list.php?option=rental" onclick="loading_onoff2('on')" id="rental_30"><?php echo count($arr_rental)?>명</a></p>
              <p class="con_bot">* 재계약 가능 금액 : <span id="total_rental_price"><?php echo number_format($total_rental_price)?></span>원</p>
          </th>
      </table>
  </div>
  <div id="loading_bg" class="rep_update" style="background-color:#000000;margin-top:-210px;opacity:0.6;border-radius:5px;padding-top:0px;height:200px;">
  </div>
  <div id="loading_con" class="rep_update" style="padding-top:0px;height:170px;margin-top:-160px;text-align:center;color:#ffffff;font-size:20px;font-weight:bold;">
	<img src="/img/loading_apple.gif" width="70px"><br><br>
	데이터 로딩중...
  </div>
<!--
  <div style="position: relative; width: 100%; height: 250px; padding-top: 30px; margin-bottom: 30px;">
      <div>
          <p style="font-weight: normal; font-size: 20px">최종 업데이트 YYYY-MM-DD</p>
      </div>
      <div style="position: relative;">
          <div style="width: 33.3%; height: 80%; float: left; text-align: center;
          border: #0c0c0c 1px solid; padding: 35px 5px 20px 5px; border-radius: 10px 0px 0px 10px;">
              <p style="font-weight: normal; font-size: 20px; line-height: 1;">계약 가능 금액</p>
              <p style="font-weight: 800; font-size: 30px; line-height: 2;">0,000,000,000원</p>
              <p style="font-weight: normal; font-size: 15px; line-height: 1;">* 기초등급 1,600,000원 기준</p>
          </div>
          <div style="width: 33.3%; height: 80%; float: left;  text-align: center;
          border: #0c0c0c 1px solid; border-left-style:none; border-right-style: none; padding: 35px 5px 20px 5px;">
              <p style="font-weight: normal; font-size: 20px; line-height: 1;">적용구간 내 미계약</p>
              <p style="font-weight: 800; font-size: 30px; line-height: 2;">00명</p>
              <p style="font-weight: normal; font-size: 15px; line-height: 1;">* 조회일이 포함된 적용구간 기준</p>
          </div>
          <div style="width: 33.3%; height: 80%; float: left;  text-align: center;
          border: #0c0c0c 1px solid; padding: 35px 5px 20px 5px; border-radius: 0px 10px 10px 0px;">
              <p style="font-weight: normal; font-size: 20px; line-height: 1;">대여종료일 N개월 미만</p>
              <p style="font-weight: 800; font-size: 30px; line-height: 2;">00명</p>
              <p style="font-weight: normal; font-size: 15px; line-height: 1;">* 재계약 가능 금액 : 000,000원</p>
          </div>
      </div>
  </div>
-->
  <div class="recipient_security">
    <input type="hidden" value="N" class="device_security">
    <div>
      <img src="<?php echo G5_SHOP_URL; ?>/img/icon_security.png" />
    </div>
    <div class="recipient_security_content">
      <h4>이로움 정보 보안관리 시스템</h4>
      <p>
        수급자 정보(이름, 요양인정번호, 연락처)는 암호화되어 저장됩니다.<br/>
        수급자계약서 체결 및 엑셀다운로드 시 본인 인증 확인(동일IP확인 및 비밀번호 확인) 후 진행됩니다.
      </p>
      <a href="/bbs/content.php?co_id=privacy">[온라인 개인정보 처리방침 자세히보기]</a>
    </div>
    <div class="recipient_security_check">
      <img src="<?php echo G5_SHOP_URL; ?>/img/icon_security_check.png" />
      <span class="recipient_security_authorized"><?php echo $member['mb_name']; ?> 확인됨</span>
      <span class="recipient_security_not_authorized" style="color:white;opacity:80%"><?php echo $member['mb_name']; ?> 미확인(해당기기 본인 확인 필요)</span>
    </div>
  </div>

  <form id="form_search" method="get">
    <div class="search_box">
      <select name="sel_field" id="sel_field">
        <option value="penNm"<?php if($sel_field == 'penNm' || $sel_field == 'all') echo ' selected'; ?>>수급자명</option>
        <option value="penProNm"<?php if($sel_field == 'penProNm') echo ' selected'; ?>>보호자명</option>
        <option value="penLtmNum"<?php if($sel_field == 'penLtmNum') echo ' selected'; ?>>장기요양번호</option>
      </select>
      <div class="input_search">
          <input name="search" id="search" value="<?=$search?>" type="text">
          <button id="btn_search" type="submit" onclick="loading_onoff2('on')"></button>
      </div>
    </div>
    <?php if($noti_count = get_recipient_noti_count() > 0) { ?>
    <div class="recipient_noti">
      신규 확인이 필요한 알림 <?=$noti_count?>건이 있습니다.
      <a href="./my_recipient_noti.php">바로확인</a>
    </div>
    <?php } ?>
    <div class="r_btn_area pc">
      <a href="javascript::" class="btn eroumcare_btn2" id="recipient_excel_download" title="수급자 엑셀 다운로드">수급자 엑셀 다운로드</a>
      <a href="./my_recipient_write.php" class="btn eroumcare_btn2" title="수급자 등록">수급자 등록</a>
      <a href="./recipientexcel.php" onclick="return excelform(this.href);" target="_blank" class="btn eroumcare_btn2" title="수급자일괄등록">수급자일괄등록</a>
      <?php if($mobile_yn == 'Pc'){?><a href="javascript:;" class="btn eroumcare_btn2" title="인증서 재 등록하기" onClick="alert('공인인증서 등록 시 발급 용도 [용도제한용]으로 재등록 부탁드립니다.');tilko_call('1');">인증서 재 등록하기</a><?php }?>
      <?php /*<div class="tooltip_btn">
        <a href="./recipientexcel_b.php" onclick="return excelform(this.href);" target="_blank" class="btn eroumcare_btn2" title="B사 엑셀 일괄등록">
          B사 엑셀 일괄등록
          <span class="question">?</span>
        </a>
        <div class="btn_tooltip">
          B사 수급자 목록 일괄등록 방법<br>
          <br>
          1. B사 복지용구프로그램 로그인<br>
          2. (고객관리 > 고객등록 > 조회) 메뉴 선택 <br>
          3. 엑셀(F8) 클릭 후 엑셀 다운로드<br>
          4. 다운로드된 엑셀파일을 이로움에 업로드<br>
          <br>

        </div>
      </div>
	  */ ?>

    </div>
    <div class="r_btn_area mobile">
      <a href="./my_recipient_write.php" class="btn eroumcare_btn2" title="수급자 등록">수급자 등록</a>
    </div>
  </form>
  
  <?php
  if (!get_tutorial('recipient_list_tooltip')) { 
    set_tutorial('recipient_list_tooltip', 1);
  ?>
  <script>
    $(document).ready(function(){
      $('.tooltip_btn .btn_tooltip').fadeIn(1000);
      setTimeout(function() {
        $('.tooltip_btn .btn_tooltip').fadeOut(1000, function() {
          $('.tooltip_btn .btn_tooltip').css('display', '');
        });
      }, 4000);	  
    });
  </script>
  <?php } ?>

  <div class="list_box pc">
    <form name="fmemberlist" id="fmemberlist" action="#" onsubmit="return fmemberlist_submit(this);" method="post">
    <div class="table_box" style="display:none;">  
      <table class = "rep_list">
        <tr>
          <th id="mb_list_chk">
            <label for="chkall" class="sound_only">수급자 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all_list(this.form)">
          </th>
          <th>No.</th>
          <th>수급자 정보</th>
          <th>장기요양정보</th>
          <!--<th>관리내역</th> -->
          <th>급여내역</th>
          <th>장바구니</th>
          <th>욕구사정기록지</th>
        </tr>
        <?php $i = -1; ?>

        <script>
        var list__ = <?=json_encode($list)?>;
        console.log("list__ : ", list__);
        </script>
        
        <?php foreach($list as $data) { ?>
        <?php $i++; ?>
        <tr>
          <td headers="mb_list_chk" id="mb_list_chk">
            <?php
            $contract_sell = get_recipient_contract_sell($data['penId']);
            ?>
            <input type="hidden" name="chk[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>" id="chk_<?php echo $i ?>" class="chk_input">
            <label for="spare_chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $data['penId'] . '|' . $contract_sell['sell_count'] ?>" id="chk_<?php echo $i ?>">
          </td>
          <td>
            <?php echo $total_count - (($page - 1) * $page_rows) - $i; ?>
          </td>
          <td>
            <!-- <a href='<?php echo G5_URL; ?>/shop/my_recipient_view.php?id=<?php echo $data['penId'];?>'> -->
            <a onclick='return check_device_security_val()' href='<?php echo G5_URL; ?>/shop/my_recipient_view.php?id=<?php echo $data['penId'];?>'>
              <span class="data_name"><?=$data['penNm']?></span><?=$data['desc_text']; ?>
              <?php if($data['incomplete']) echo '<img src="'.THEMA_URL.'/assets/img/icon_notice_recipient.png" style="vertical-align:bottom;">'; ?>
              <br/>
              <?php if ($data['penProNm']) { ?>
                보호자(<span class="data_name"><?php echo $data['penProNm']; ?></span><span class="data_phone"><?php echo $data['penProConNum'] ? '/' . $data['penProConNum'] : ''; ?></span>)
              <?php } else { ?>
                보호자(<span>미등록</span>)
              <?php } ?>
              <?php
              $pros = get_pros_by_recipient($data['penId']);
              foreach($pros as $pro) {
                $pro_data = [];
                if($pro['pro_name']) $pro_data[] = $pro['pro_name'];
                if($pro['pro_hp']) $pro_data[] = $pro['pro_hp'];
                echo '<br>보호자(' . implode('/', $pro_data) . ')';
              }
              ?>
            </a>
          </td>
          <td class="recipient_info">
            <?php if ($data["penLtmNum"]) { ?>
              <span class="data_penNum"><?php echo $data["penLtmNum"]; ?></span>
              (<?php if($data["penRecGraNm"]==''){echo str_replace('0','',$data["penRecGraCd"])."등급";} else {echo $data["penRecGraNm"];} ?><?php echo $pen_type_cd[$data['penTypeCd']] ? '/' . $pen_type_cd[$data['penTypeCd']] : ''; ?>)
              <br/>
			  적용구간 : 
              <?php 
                $apped = substr($data['penAppEdDtm'], 0, 4).'-'.substr($data['penAppEdDtm'], 4, 2).'-'.substr($data['penAppEdDtm'], 6, 2); 
                $appst = date('Y-m-d', strtotime("-1 years +1 days",strtotime($apped))); 
                while($appst > date('Y-m-d')){
                  $apped = date('Y-m-d', strtotime("-1 years",strtotime($apped))); 
                  $appst = date('Y-m-d', strtotime("-1 years",strtotime($appst))); 
                }
                echo $appst.' ~ '.$apped;?>
              <br/>
              최근 조회일 : <?php echo substr($remainAmtList[$data["penLtmNum"]]["updatedDate"],0,10); ?>
            <?php }else{ ?>
              예비수급자
            <?php } ?>
          </td>
          <td style="text-align:center;">
            <span class="Pention_Amount"> 잔액 :  <?php echo number_format($remainAmtList[$data["penLtmNum"]]["remainAmt"]); ?>원 </span>
			</br>
            <span class="Pention_Amount"> 사용 : <?php echo number_format(1600000-$remainAmtList[$data["penLtmNum"]]["remainAmt"]); ?>원 </span>

            <!--<span class="<?php echo $data['per_year']['sum_price'] > 1400000 ? 'red' : ''; ?>"><?php echo number_format($res_arr[$data['penLtmNum']]['transaction_amt']); ?>원</span>
            <br/>
            계약 <?php echo $res_arr[$data['penLtmNum']]['contract']; ?>건, 판매 <?php echo $res_arr[$data['penLtmNum']]['purchase']?>건, 대여 <?php echo $res_arr[$data['penLtmNum']]['rental'] ?>건
			-->
          </td>
          <td style="text-align:center;">
			<style>
				a:link { color : black; }
				a:visited { color : black; }
				a:hover { color : black; }
				a:active { color : green }
			</style>
			<a href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$data['penId'].'&redirect='.urlencode('/shop/cart.php')?>"><?php echo $data['carts'] . '개'; ?></a>

            <br/>
            <?php if ($data["penLtmNum"]) { ?>

			<a href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$data['penId'].'&redirect='.urlencode('/shop/list.php?ca_id=10')?>" class="btn eroumcare_btn2 small" title="추가하기">추가하기</a> 
            <?php } ?>
          </td>
          <td style="text-align:center;">
            <?php if ($data['recYn'] === 'N') { ?>
              미작성<br/>
              <a href="<?php echo G5_SHOP_URL; ?>/my_recipient_rec_form.php?id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2 small" title="작성하기">작성하기</a>
            <?php } else { ?>
              작성완료<br/>
              <a href="<?php echo G5_SHOP_URL; ?>/my_recipient_rec_form.php?id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2 small" title="작성하기">추가작성</a>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
      </table>
    </div>
    </form>
  </div>


  <?php if(!$list){ ?>
  <div class="no_content">
    내용이 없습니다
  </div>
  <?php } ?>
  <?php if($list) { ?>
  <div class="list_box mobile">
    <ul class="li_box" id="mobile_rep_list">
      <?php $temp_ind = 0; foreach ($list as $data) { 
        $contract_sell = get_recipient_contract_sell($data['penId']);?>
      <li>
        <div class="chk_mobile">
          <label for="chk_<?php echo $temp_ind; ?>">
            <input type="checkbox" name="chk[]" value="<?php echo $data['penId'] . '|' . $contract_sell['sell_count'] ?>" id="chk_<?php echo $temp_ind; $temp_ind++; ?>" class="mobile_chk">
            <i class="circle"></i>
          </label>
        </div>
        <div class="info">
        <a onclick='return check_device_security_val()' href='<?php echo G5_URL; ?>/shop/my_recipient_view.php?id=<?php echo $data['penId'];?>'>
            <b>
              <?php echo $data['penNm'].$data['desc_text']; ?>
              <?php if($data['incomplete']) echo '<img src="'.THEMA_URL.'/assets/img/icon_notice_recipient.png" style="vertical-align:bottom;">'; ?>
            </b>
            <?php if ($data['penProNm']) { ?>
            <span class="li_box_protector">
              * 보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProTypeCd'] == '00' ? '/없음' : ''; ?><?php echo $data['penProTypeCd'] == '01' ? '/일반보호자' : ''; ?><?php echo $data['penProTypeCd'] == '02' ? '/요양보호사' : ''; ?>)
            </span>
            <?php } else { ?>
                <span class="li_box_protector">
                  * 보호자(미등록)
                </span>
            <?php } ?>
            <p>
              <?php if ($data["penLtmNum"]) { ?>
              <b>
                <?php echo $data["penLtmNum"]; ?>
                (<?php echo $data["penRecGraNm"]; ?><?php echo $pen_type_cd[$data['penTypeCd']] ? '/' . $pen_type_cd[$data['penTypeCd']] : ''; ?>)
              </b>
              <?php } else { ?>
              예비수급자
              <?php } ?>
              <?php echo "<br/>급여 잔액 : ".number_format($remainAmtList[$data["penLtmNum"]]["remainAmt"])."원"; ?>
              <?php echo "<br/>사용 금액 : ".number_format(1600000-$remainAmtList[$data["penLtmNum"]]["remainAmt"])."원"; ?>
            </p>
          </a>
          <?php if ($data['recYn'] === 'N') { ?>
            <a href="<?php echo G5_SHOP_URL; ?>/my_recipient_rec_form.php?id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2" style="margin-top:10px;" title="작성하기">욕구사정기록지 신규작성</a>
          <?php } else { ?>
            <a href="<?php echo G5_SHOP_URL; ?>/my_recipient_rec_form.php?id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2" style="margin-top:10px;" title="작성하기">욕구사정기록지 추가작성</a>
          <?php } ?>
        </div>
        <?php if ($data["penLtmNum"]) { ?>
        <a href="<?php echo G5_SHOP_URL; ?>/connect_recipient.php?pen_id=<?php echo $data['penId']; ?>&redirect=<?=urlencode('/shop/cart.php')?>" class="li_box_right_btn" title="추가하기">
          장바구니
          <br/>
          <b><?php echo $data['carts'] . '개'; ?></b>
        </a>
        <?php } ?>
      </li>
      <?php } ?>
    </ul>
  </div>
  <?php } ?>
  <!-- <button type="button" class="btn eroumcare_btn2" onclick="return form_check('seldelete');">선택삭제</button> -->
  <button type="button" class="btn eroumcare_btn2" style ="float : right;" onclick="return form_check('show_list_all');">전체목록</button>

  <div class="list-paging"; >
    <ul class="pagination pagination-sm en">
      <?php 
      if($page_option == 'none'){
        echo apms_paging($write_pages, $page, $total_page, "?sel_field={$sel_field}&search={$search}&page_spare={$page_spare}&page=");
      } else {
        echo apms_paging($write_pages, $page, $total_page, "?option={$page_option}&sel_field={$sel_field}&search={$search}&page_spare={$page_spare}&page=");
      }?>
    </ul>
  </div>


  <div class="l_btn_area pc" style="margin-bottom:10px; display:none">
    <button type="button" class="btn eroumcare_btn2" onclick="return form_check('seldelete');">선택삭제</button>
    &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; 일괄수정 : &nbsp;&nbsp;&nbsp;
    <select name="sel_grade" id="sel_grade">
        <option value="">등급</option>
        <option value="00">등급외</option>
        <option value="01">1등급</option>
        <option value="02">2등급</option>
        <option value="03">3등급</option>
        <option value="04">4등급</option>
        <option value="05">5등급</option>
        <option value="06">6등급</option>
    </select>
    <select name="sel_type_cd" id="sel_type_cd">
        <option value="">본인부담금</option>
        <option value="00">일반 15%</option>
        <option value="01">감경 9%</option>
        <option value="02">감경 6%</option>
        <option value="03">의료 6%</option>
        <option value="04">기초 0%</option>
    </select>
    <button type="button" class="btn eroumcare_btn2" onclick="return form_check('selupdate');">선택수정</button>
    <!-- <a href="./my_recipient_write.php" class="btn eroumcare_btn2" title="수급자 등록">본인부담금</a> -->
  </div>
  <br/><br/><br/>

  <!-- 예비수급자 관리 숨기기-->
  <div class="titleWrap" style="margin-bottom:10px; display:none;">
    예비수급자관리
  </div>

  <div class="list_box pc" style="display:none;">
    <form name="fsparememberlist" id="fsparememberlist" action="#" method="post">
    <div class="table_box">  
      <table>
        <tr>
          <th id="mb_list_chk">
            <label for="chkall" class="sound_only">예비수급자 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall_spare" onclick="check_all_list_spare(this.form)">
          </th>
          <th class="number_area">No.</th>
          <th>수급자 정보</th>
          <th>장기요양정보</th>
          <th>비고</th>
        </tr>
        <?php $i = -1; ?>
        <?php foreach($list_spare as $data) { ?>
        <?php $i++; ?>
        <tr>
          <td headers="mb_list_spare_chk" id="mb_list_spare_chk">
            <input type="hidden" name="spare_chk[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>" id="spare_chk_<?php echo $i ?>" class="spare_chk_input">
            <label for="spare_chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
            <input type="checkbox" name="spare_chk[]" value="<?php echo $data['penId'] ?>" id="spare_chk_<?php echo $i ?>">
          </td>
          <td>
            <?php echo $total_count_spare - (($page_spare - 1) * $rows_spare) - $i; ?>
          </td>
          <td>
            <a href="<?=G5_SHOP_URL?>/my_recipient_update.php?penSpare=1&id=<?=$data['penId']?>">
              <?php echo $data['penNm'].$data['desc_text']; ?>
              <br/>
              <?php if ($data['penProNm']) { ?>
                보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProConNum'] ? '/' . $data['penProConNum'] : ''; ?>)
              <?php } ?>
            </a>
          </td>
          <td>
            예비수급자
          </td>
          <td style="text-align:center;">
          </td>
        </tr>
        <?php } ?>
      </table>
    </div>
  </form>
  </div>

  <?php if(!$list_spare) { ?>
  <div class="no_content"  style="display:none;">
    내용이 없습니다
  </div>
  <?php } ?>

  <?php if($list_spare) { ?>
  <div class="list_box mobile"  style="display: none !important; ">
    <ul class="li_box">
      <?php foreach ($list_spare as $data) { ?>
      <li>
        <div class="info">
          <a href="<?=G5_SHOP_URL?>/my_recipient_update.php?penSpare=1&id=<?=$data['penId']?>">
            <b>
              <?php echo $data['penNm'].$data['desc_text']; ?>
            </b>
            <?php if ($data['penProNm']) { ?>
            <span class="li_box_protector">
              * 보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProTypeCd'] == '00' ? '/없음' : ''; ?><?php echo $data['penProTypeCd'] == '01' ? '/일반보호자' : ''; ?><?php echo $data['penProTypeCd'] == '02' ? '/요양보호사' : ''; ?>)
            </span>
            <?php } ?>
            <p>
              예비수급자
            </p>
          </a>
        </div>
      </li>
      <?php } ?>
    </ul>
  </div>
  <?php } ?>
  <div class="list-paging"  style="display:none;">
    <ul class="pagination pagination-sm en">
      <?php echo apms_paging($write_pages, $page_spare, $total_page_spare, "?sel_field={$sel_field}&search={$search}&page={$page}&page_spare="); ?>
    </ul>
  </div>
  <div class="l_btn_area pc" style="margin-bottom: 30px;display:none;"  >
    <button type="button" class="btn eroumcare_btn2" onclick="return form_check('spare_seldelete');"  style="display:none;">선택삭제</button>
  </div>

  <?php
  if($links) {
  ?>
  <div class="titleWrap" style="margin-bottom:10px;">
    대기중인 수급자 관리
  </div>
  <div class="list_box pc">
    <div class="table_box">  
      <table id="tb_links">
        <thead>
          <tr>
            <th scope="col">No.</th>
            <th scope="col">수급자명</th>
            <th scope="col">인정정보</th>
            <th scope="col">주소</th>
            <th scope="col">연락처</th>
            <th scope="col">보호자정보</th>
            <th scope="col">연결일시(3일 후 자동취소)</th>
          </tr>
        </thead>
        <tbody>
          <?php
          for($i = 0; $i < count($links); $i++) {
          $rl = $links[$i];
          ?>
          <tr data-id="<?=$rl['rl_id']?>">
            <td><?=count($links) - $i?></td>
            <td style="text-align:center;"><?=get_text($rl['rl_pen_name'])?></td>
            <td style="text-align:center;"><?=$rl['rl_pen_ltm_num'] ? get_text('L'.$rl['rl_pen_ltm_num']) : '예비'?></td>
            <td style="max-width:300px;width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <?=get_text($rl['rl_pen_addr1'])?>
              <?=get_text($rl['rl_pen_addr2'])?>
              <?=get_text($rl['rl_pen_addr3'])?>
            </td>
            <td style="text-align:center;"><?=get_text($rl['rl_pen_hp'])?></td>
            <td style="text-align:center;">
              <?=get_text($rl['rl_pen_pro_name'])?>
              (<?=get_text($rl['rl_pen_pro_hp'])?>)
            </td>
            <td style="text-align:center;">
              <?php
              if($rl['status'] == 'request') {
                echo '미연결';
              } else {
                echo date('Y-m-d', strtotime($rl['updated_at']));
              }
              ?>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="list_box mobile">
    <ul id="ul_links" class="li_box">
      <?php
      for($i = 0; $i < count($links); $i++) {
        $rl = $links[$i];
      ?>
      <li data-id="<?=$rl['rl_id']?>">
        <div class="info">
          <b>
            <?=get_text($rl['rl_pen_name'])?>
          </b>
          <?php if ($rl['rl_pen_pro_name']) { ?>
          <span class="li_box_protector">
            * 보호자(<?=get_text($rl['rl_pen_pro_name'])?> / <?=get_text($rl['rl_pen_pro_hp'])?>)
          </span>
          <?php } ?>
          <p>
            <?=$rl['rl_pen_ltm_num'] ? get_text('L'.$rl['rl_pen_ltm_num']) : '예비'?>
          </p>
          <p>
            <b>
              <?=get_text($rl['rl_pen_addr1'])?>
              <?=get_text($rl['rl_pen_addr2'])?>
              <?=get_text($rl['rl_pen_addr3'])?>
            </b>
          </p>
          <p>
            <b>연결일시: </b>
            <span style="font-size:0.9em;">
              <?php
              if($rl['status'] == 'request') {
                echo '미연결';
              } else {
                echo date('Y-m-d', strtotime($rl['updated_at']));
              }
              ?>
            </span>
          </p>
        </div>
      </li>
      <?php } ?>
    </ul>
  </div>
  <div id="popup_recipient_link">
    <div></div>
  </div>
  <style>
  #tb_links td, #ul_links li { cursor: pointer }
  #tb_links tr:hover, #tb_links tr:active, #ul_links li:hover, #ul_links li:active { background-color: #f5f5f5; }
  #popup_recipient_link { position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
  #popup_recipient_link > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
  #popup_recipient_link iframe { position: relative; width: 1024px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -512px; }
  #popup_recipient_link iframe.mini { width: 600px; margin-left: -300px; }
  @media (max-width : 1240px){
    #popup_recipient_link iframe, #popup_recipient_link iframe.mini { width: 100%; height: 100%; left: 0; margin-left: 0; }
  }
  </style>
  <script>
  $(function() {
    $("#popup_recipient_link").hide();
    $("#popup_recipient_link").css("opacity", 1);

    $('#tb_links td').click(function(e) {
      var rl_id = $(this).closest('tr').data('id');
      $("#popup_recipient_link > div").html("<iframe src='my_recipient_link.php?rl_id="+rl_id+"'>");
      $("#popup_recipient_link iframe").removeClass('mini');
      $("#popup_recipient_link iframe").load(function() {
        $("body").addClass('modal-open');
        $("#popup_recipient_link").show();
      });
    });

    $('#ul_links li').click(function(e) {
      var rl_id = $(this).data('id');
      $("#popup_recipient_link > div").html("<iframe src='my_recipient_link.php?rl_id="+rl_id+"'>");
      $("#popup_recipient_link iframe").removeClass('mini');
      $("#popup_recipient_link iframe").load(function() {
        $("body").addClass('modal-open');
        $("#popup_recipient_link").show();
      });
    });
  });
  </script>
  <?php } ?>
</div>
<div class="ajax-loader">
  <div class="loader-wr">
    <img src="<?php echo G5_URL; ?>/shop/img/loading.gif">
    <p>수급자 일괄 등록 중입니다...</p>
  </div>
</div>

<script>
// 엑셀 일괄등록, 로딩
function excelPost(action, data) {
  $('.ajax-loader').show();
  $.ajax({
    url: action,
    type: 'POST',
    data: data,
    processData: false,
    contentType: false,
    dataType: 'json'
  })
  .done(function(result) {
    alert(result.message);
	loading_onoff2('on');
    window.location.reload();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  })
  .always(function() {
    $('.ajax-loader').hide();
  });
}
</script>
<div id="popup_recipient">
  <div></div>
</div>
<style>
#popup_recipient { position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
#popup_recipient > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
#popup_recipient iframe { position: relative; width: 1024px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -512px; }
#popup_recipient iframe.mini { width: 600px; margin-left: -300px; }
#popup_recipient iframe.security {
  width: 600px;
  margin-left: -300px;
  max-height: 500px;
}
@media (max-width : 1240px){
  #popup_recipient iframe,
  #popup_recipient iframe.mini,
  #popup_recipient iframe.security {
    width: 100%;
    height: 100%;
    left: 0;
    margin-left: 0;
  }
}
</style>
<script>
  let maskingFunc = {
    checkNull : function (str){
      if(typeof str == "undefined" || str == null || str == ""){
        return true;
      }
      else{
        return false;
      }
    },
    /*
    ※ 이메일 마스킹
    ex1) 원본 데이터 : abcdefg12345@naver.com
      변경 데이터 : ab**********@naver.com
    ex2) 원본 데이터 : abcdefg12345@naver.com
        변경 데이터 : ab**********@nav******
    */
    email : function(str){
      let originStr = str;
      let emailStr = originStr.match(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/gi);
      let strLength;
      
      if(this.checkNull(originStr) == true || this.checkNull(emailStr) == true){
        return originStr;
      }else{
        strLength = emailStr.toString().split('@')[0].length - 3;
        
        // ex1) abcdefg12345@naver.com => ab**********@naver.com
        // return originStr.toString().replace(new RegExp('.(?=.{0,' + strLength + '}@)', 'g'), '*');

        // ex2) abcdefg12345@naver.com => ab**********@nav******
        return originStr.toString().replace(new RegExp('.(?=.{0,' + strLength + '}@)', 'g'), '*').replace(/.{6}$/, "******");
      }
    },
    /* 
    ※ 휴대폰 번호 마스킹
    ex1) 원본 데이터 : 01012345678, 변경 데이터 : 010****5678
    ex2) 원본 데이터 : 010-1234-5678, 변경 데이터 : 010-****-5678
    ex3) 원본 데이터 : 0111234567, 변경 데이터 : 011***4567
    ex4) 원본 데이터 : 011-123-4567, 변경 데이터 : 011-***-4567
    */
    phone : function(str){
      let originStr = str;
      let phoneStr;
      let maskingStr;
      
      if(this.checkNull(originStr) == true){
        return originStr;
      }
      
      if (originStr.toString().split('-').length != 3)
      { // 1) -가 없는 경우
        phoneStr = originStr.length < 11 ? originStr.match(/\d{10}/gi) : originStr.match(/\d{11}/gi);
        if(this.checkNull(phoneStr) == true){
          return originStr;
        }
        
        if(originStr.length < 11)
        { // 1.1) 0110000000
          maskingStr = originStr.toString().replace(phoneStr, phoneStr.toString().replace(/(\d{3})(\d{3})(\d{4})/gi,'$1$2*****$3'));
        }
        else
        { // 1.2) 01000000000
          maskingStr = originStr.toString().replace(phoneStr, phoneStr.toString().replace(/(\d{3})(\d{4})(\d{4})/gi,'$1$2******$3'));
        }
      }else
      { // 2) -가 있는 경우
        phoneStr = originStr.match(/\d{2,3}-\d{3,4}-\d{4}/gi);
        if(this.checkNull(phoneStr) == true){
          return originStr;
        }
        
        if(/-[0-9]{3}-/.test(phoneStr))
        { // 2.1) 00-000-0000
          maskingStr = originStr.toString().replace(phoneStr, phoneStr.toString().replace(/(\d{3})-(\d{1})\d{2}-\d{3}(\d{1})/gi, "$1-$2**-***$3"));
        } else if(/-[0-9]{4}-/.test(phoneStr))
        { // 2.2) 00-0000-0000
          // maskingStr = originStr.toString().replace(phoneStr, phoneStr.toString().replace(/-[0-9]{4}-/g, "-****-"));
          maskingStr = originStr.toString().replace(phoneStr, phoneStr.toString().replace(/(\d{3})-(\d{1})\d{3}-\d{3}(\d{1})/gi, "$1-$2***-***$3"));
        }
      }
      
      return maskingStr;
    },
    /*
    ※ 이름 마스킹
    ex1) 원본 데이터 : 갓댐희, 변경 데이터 : 갓댐*
    ex2) 원본 데이터 : 하늘에수, 변경 데이터 : 하늘**
    ex3) 원본 데이터 : 갓댐, 변경 데이터 : 갓*
    */
    name : function(str){
      let originStr = str;
      let maskingStr;
      let strLength;
      
      if(this.checkNull(originStr) == true){
        return originStr;
      }
      
      strLength = originStr.length;
      
      if(strLength < 2) {
        maskingStr = originStr.substring(0, 1) + '*';
      } else {
        maskingStr = originStr.substring(0, 2);
        for(var i = 0; i < strLength - 2; i++) {
          maskingStr += '*';
        }
      }
      
      return maskingStr;
    },
    /*
    ※ 요양번호 마스킹
    ex1) 원본 데이터 : L1234567890, 변경 데이터 : L12345*****
    */
    penNum : function(str){
      let originStr = str;
      let maskingStr;
      let strLength;
      
      if(this.checkNull(originStr) == true){
        return originStr;
      }
      
      strLength = originStr.length;

      maskingStr = originStr.substring(0, 6) + '*****';

      return maskingStr;
    }
}

  $(function() {
	$('.pagination li').click(function(e) {
		if($(this).hasClass("disabled") === true || $(this).hasClass("active") === true){
			loading_onoff2('off');
		}else{
			loading_onoff2('on');
		}
	});
	
    $("#popup_recipient").hide();
    $("#popup_recipient").css("opacity", 1);

    <?php
    $tttttt = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
      'usrId' => $member['mb_id'],
      'entId' => $member['mb_entId']
    ));
    ?>
    var ttt = <?=json_encode($tttttt)?>;
    console.log("ttt : ", ttt);


    $('#recipient_excel_download').click(function(e) {
      if (check_device_security_val() == true) {
        var link ='my_recipient_excel.php';
        var write_data = [];
        var cnt = 0;
        $('.rep_list tr').each(function() {
          if(cnt == 0){
            cnt++;
          } else {
            var key = $(this).find("td").eq(2).find("span").eq(0).text();
            var value = $(this).find("td").eq(4).find("span").eq(0).text().split(" : ")[1].replace("원", "").replaceAll(',','');
            write_data[key] = value;
          }
        });
        
        $.redirectPost(link, write_data);
      }
    });

    $(document).ready(function(){
      var fingerprint = get_fingerprint();
      $.ajax({
        type: 'POST',
        url: './ajax.check_fingerprint.php',
        data: {fingerprint : fingerprint, type : 'check'}
      })
      .done(function(result) {
        // console.log("done");
        $('.recipient_security_check img').show();
        $('.recipient_security_authorized').show();
        $('.device_security').val("A");
        $(".table_box").show();
      })
      .fail(function(result) {
        // console.log("fail");
        // console.log(result.responseJSON);
        $('.recipient_security_not_authorized').show();
        $('.device_security').val(result.responseJSON.data);
        $(".data_name").each(function() {
          // $(this).text("***");
          var name = $(this).text();
          $(this).text(maskingFunc.name(name));
        });
        $(".data_phone").each(function() {
          // $(this).text("***");
          var phone = $(this).text();
          $(this).text(maskingFunc.phone(phone));
        });
        $(".data_penNum").each(function() {
          // $(this).text("***");
          var penNum = $(this).text();
          $(this).text(maskingFunc.penNum(penNum));
        });
        $(".table_box").show();
      });
    });
	loading_onoff2('off');
	//계약가능 금액,적용구간종료30일
	setTimeout(function() { loading_data() }, 100);
  });

  $.extend({
      redirectPost: function (location, args) {
          var form = $('<form></form>');
          form.attr("method", "post");
          form.attr("action", location);
          
          var key_list = Object.keys(args);
          var value_list = Object.values(args);

          
          for(var i = 0; i < key_list.length; i++){
              var field = $('<input></input>');
              field.attr("type", "hidden");
              field.attr("name", key_list[i]);
              field.attr("value", value_list[i].replaceAll(' ', ''));

              form.append(field);
          }

          console.log("form : ", form);

          // 위에서 생성된 폼을 제출 한다
          $(form).appendTo('body').submit();
      }
  });
</script>
<!-- 인증서 업로드 추가 영역 -->
<div id="cert_popup_box">
  <iframe name="cert_iframe" src="" scrolling="no" frameborder="0" allowTransparency="false"></iframe>
</div>

<div id="cert_guide_popup_box">
  <iframe name="cert_guide_iframe" src="" scrolling="no" frameborder="0" allowTransparency="false"></iframe>
</div>

<iframe name="tilko" id="tilko" src="" scrolling="no" frameborder="0" allowTransparency="false" height="0" width="0"></iframe>
<script type="text/javascript">
	$( document ).ready(function() {
		<?php if($member["cert_reg_sts"] != "Y"){//등록 안되어 있음
			if($mobile_yn == 'Pc'){?>
		//공인인증서 등록 안내 및 등록 버튼 팝업 알림으로 교체 될 영역	
			cert_guide();
		<?php }else{?>
		alert("컴퓨터에서 공인인증서를 등록 후 이용이 가능한 서비스 입니다.");
		<?php }
		}else{//등록 되어 있음
			if(!$is_file){
	?>		tilko_call('1');
	<?php	}
		}?>
		
		$('#cert_popup_box').click(function() {
		  $('body').removeClass('modal-open');
		  $('#cert_popup_box').hide();
		});
		$('#cert_guide_popup_box').click(function() {
		  $('body').removeClass('modal-open');
		  $('#cert_guide_popup_box').hide();
		});
		
	});
	function loading_data(){
		var shearch_time = 0;
		if(getCookie("total_amt") != "" && getCookie("total_amt") != null){
			$("#total_rental_price").text(parseInt(getCookie("total_rental_price")).toLocaleString('ko-KR'));
			$("#total_amt").text(parseInt(getCookie("total_amt")).toLocaleString('ko-KR'));
			$("#expire_30").text(parseInt(getCookie("expire_30")).toLocaleString('ko-KR')+"명");
			$("#rental_30").text(parseInt(getCookie("rental_30")).toLocaleString('ko-KR')+"명");
			shearch_time = getCookie("shearch_time");
			
		}
		// ajax 통신
        if(parseInt(shearch_time)<<?=strtotime('Now')?>){
		$.ajax({
            type : "POST",            // HTTP method type(GET, POST) 형식이다.
            url : "./ajax.my_recipient_total.php",      // 컨트롤러에서 대기중인 URL 주소이다.
            data : "1",            // Json 형식의 데이터이다.
			dataType: "json",
            success : function(res){ // 비동기통신의 성공일경우 success콜백으로 들어옵니다. 'res'는 응답받은 데이터이다.
                // 응답코드 > 0000
				$("#total_rental_price").text(res["total_rental_price"].toLocaleString('ko-KR'));
                $("#total_amt").text(res["total_amt"].toLocaleString('ko-KR'));
				$("#expire_30").text(res["expire_30"].toLocaleString('ko-KR')+"명");
				$("#rental_30").text(res["rental_30"].toLocaleString('ko-KR')+"명");
				$("#loading_bg").css("display","none");
				$("#loading_con").css("display","none");
            },
            error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
                alert("통신 실패.");
            }
        });
		}else{
			$("#loading_bg").css("display","none");
			$("#loading_con").css("display","none");
		}
	}	
	
	function tilko_call(a=1){
		$("#tilko").attr("src","/tilko_test.php?option="+a);
	}
	
	function tilko_download(){
		//alert("공인인증서 전송 프로그램 설치가 필요합니다. 설치 파일을 다운로드 합니다.");
		$("#tilko").attr("src","/Resources/setup.exe");
	}
	function cert_guide(){// 공인인증서 등록 절차 가이드 창 오픈
		var url = "/shop/pop.cert_guide.php";
		$('#cert_guide_popup_box iframe').attr('src', url);
		$('body').addClass('modal-open');
		$('#cert_guide_popup_box').show();
	}
		
	function pwd_insert(){// 공인인증서 비밀번호 입력 창 오픈
		var url = "/shop/pop.certmobilelogin.php";
		$('#cert_popup_box iframe').attr('src', url);
		$('body').addClass('modal-open');
		$('#cert_popup_box').show();
	}
	function cert_pwd(pwd){
		var params = {
				  mode      : 'pwd'
				, Pwd       : pwd
			}
			$.ajax({
				type : "POST",            // HTTP method type(GET, POST) 형식이다.
				url : "/ajax.tilko.php",      // 컨트롤러에서 대기중인 URL 주소이다.
				data : params, 
				dataType: 'json',// Json 형식의 데이터이다.
				success : function(res){ // 비동기통신의 성공일경우 success콜백으로 들어옵니다. 'res'는 응답받은 데이터이다.
					$("#btn_submit").trigger("click");
				  },
				error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
					alert(XMLHttpRequest['responseJSON']['message']);
					pwd_insert();
				}
			});
	}
</script>
<!-- 인증서 업로드 추가 영역 끝-->

<?php include_once("./_tail.php"); ?>
