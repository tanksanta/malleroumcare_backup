<?php
include_once('./_common.php');

header('Content-type: application/json');
if(!$member["mb_id"]){
  json_response(400, '먼저 로그인하세요.');
  exit;
}

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

$send_data = [];
$send_data["usrId"] = $member["mb_id"];
$send_data["entId"] = $member["mb_entId"];

$res_tot = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);
$res['data']=null;

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
      //$remainAmtList[$tmpLtmNum]["updatedDate"] =
      //  substr($res_tot['data'][$idx]['modifyDtm'],0,4).'-'.substr($res_tot['data'][$idx]['modifyDtm'],4,2).'-'.substr($res_tot['data'][$idx]['modifyDtm'],6,2);

      if ($remainAmt  > 0) {
        $total_amt_pention_in_list += $remainAmt;
      } else {
        $total_amt_pention_in_list += $penDefaultAmt;
      }
    } 

  }

  $thatday = null;
} 

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

$total_amt = $total_amt_pention_in_list;
$total_amt = $total_amt_pention_in_list;
$expire_30 = $count_on_deadline;
$rental_30 = count($arr_rental);
$shearch_time = strtotime("+1 minutes");//1분 이내에 재 조회 시 조회 없이 쿠키값으로 대체
setcookie("total_rental_price", $total_rental_price, time() + (12*3600));
setcookie("total_amt", $total_amt, time() + (12*3600));
setcookie("expire_30", $expire_30, time() + (12*3600));
setcookie("rental_30", $rental_30, time() + (12*3600));
setcookie("membre_id", $member["mb_id"], time() + (12*3600));
setcookie("shearch_time",$shearch_time, time() + (12*3600));
$data["total_rental_price"] =  $total_rental_price;
$data["total_amt"] =  $total_amt;
$data["expire_30"] =  $expire_30;
$data["rental_30"] =  $rental_30;
$data["shearch_time"] =  $shearch_time;

if($shearch_time != ""){//계약가능금액
	echo json_encode($data);
	exit;
}else{
	json_response(400, '계약가능금액');
	exit;
}

?>