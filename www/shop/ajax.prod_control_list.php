<?php
include_once('./_common.php');

if (!$is_member || !$member['mb_id'])
  json_response(400, '먼저 로그인 하세요.');

if($member['mb_type'] !== 'default')
  json_response(400, '사업소 회원만 이용할 수 있습니다.');

$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];
$gubun = $_POST['gubun']?:"";
$sendData["gubun"] = $gubun!="all"?$gubun:"";
$to_date = $_POST['to_date']?new DateTime($_POST['to_date']):"";
$fr_date = $_POST['fr_date']?new DateTime($_POST['fr_date']):"";

$res = api_post_call(EROUMCARE_API_STOCK_LIST, $sendData);
$prod_detail = [];

if($res["data"]){
  $sendData["stateCd"] =['01','02','08','09']; // 01: 재고(대여가능) 02: 재고소진(대여중) 03: AS신청 04: 반품 05: 기타 06: 재고대기 07: 주문대기 08: 소독중 09: 대여종료

  foreach($res["data"] as $item) {
    if ($item['gubun'] == '02') continue; // 비급여 상품은 포함하지 않는다
    $sendData["prodId"] = $item['prodId'];
    $stockCntList = api_post_call(EROUMCARE_API_STOCK_SELECT_DETAIL_LIST, $sendData);

    $prod_log = [];
    foreach($stockCntList["data"] as $item_detail) {
      $item_detail_info = [];
      $rental_log = [];
      if ($item_detail['gubun'] == '00') { // 판매 상품의 경우
        // 재고가 있는 경우(미판매 상품) 제품관리대장에 기입하지 않음(대여는 이전 기록이 남아있을 수 있기때문에 미포함)
        if($item_detail['stateCd'] == '01') continue;
        // 검색 기간 내에 판매되지 않은 제품은 제품관리대장에 기입하지 않음
        $end_date = new DateTime(substr($item_detail['modifyDtm'], 0,4).'-'.substr($item_detail['modifyDtm'], 4,2).'-'.substr($item_detail['modifyDtm'], 6,2));
        if(!($end_date > $fr_date && $end_date < $to_date)) continue;

        $item_detail_info[0] = $item_detail;
        $item_detail_info[0]['stoId'] = $item_detail['stoId'];
        $item_detail_info[0]['gubun'] = $item_detail['gubun'];
        $item_detail_info[0]['itemNm'] = $item_detail['itemNm'];
        $item_detail_info[0]['modifyDtm'] = substr($item_detail['modifyDtm'], 0,4).'-'.substr($item_detail['modifyDtm'], 4,2).'-'.substr($item_detail['modifyDtm'], 6,2);
        $item_detail_info[0]['prodBarNum'] = $item_detail['prodBarNum'];
        $item_detail_info[0]['prodId'] = $item_detail['prodId'];
        $item_detail_info[0]['prodPayCode'] = $item_detail['prodPayCode'];
        $item_detail_info[0]['prodNm'] = $item_detail['prodNm'];
        $item_detail_info[0]['stateCd'] = $item_detail['stateCd'];
        $item_detail_info[0]['stateNm'] = $item_detail['stateNm'];
        $item_detail_info[0]['deli_stat'] = '판매';
        $item_detail_info[0]['penNm'] = "";

        if($item_detail['penId']){
          $sendData["penId"] = $item_detail['penId']?:"";
          $pen_result = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, $sendData);
          if($pen_result['data']) $item_detail_info[0]['penNm'] = $pen_result['data'][0]['penNm'];
        }

        $prod_log[] = $item_detail_info;
      }
      else {  // 대여 상품의 경우

        $sql_retal_log = "select stoId, strdate, enddate, dis_total_date, ren_person, ren_eformUrl from g5_rental_log where stoId = '{$item_detail['stoId']}' and rental_log_division = '2'"; // 로그 검색 - 소독관려 로그 제외
        $result_rl = sql_query($sql_retal_log);
        $add_index = 0;
        $num_rows = sql_num_rows($result_rl)-1;
        for ($i=0;$row=sql_fetch_array($result_rl);$i++){
          $ind = $i + $add_index;
          $rental_log[$ind]['stoId'] = $item_detail['stoId'];
          $rental_log[$ind]['gubun'] = $item_detail['gubun'];
          $rental_log[$ind]['itemNm'] = $item_detail['itemNm'];
          $rental_log[$ind]['modifyDtm'] = $item_detail['modifyDtm'];
          $rental_log[$ind]['prodBarNum'] = $item_detail['prodBarNum'];
          $rental_log[$ind]['prodId'] = $item_detail['prodId'];
          $rental_log[$ind]['prodPayCode'] = $item_detail['prodPayCode'];
          $rental_log[$ind]['prodNm'] = $item_detail['prodNm'];
          $rental_log[$ind]['stateCd'] = $item_detail['stateCd'];
          $rental_log[$ind]['stateNm'] = $item_detail['stateNm'];

          if(str_contains($row['ren_person'], '종료')){
            if($i==0) json_response(401, "디비 정보를 불러올 수 없습니다.");
            $rental_log[$ind]['strdate'] = $row['strdate'];
            $rental_log[$ind]['enddate'] = explode(")", explode("(", $row['ren_person'])[1])[0]; // 회수 로그에 대한 종료일 변경
            $rental_log[$ind-1]['enddate'] = explode(")", explode("(", $row['ren_person'])[1])[0]; // 대여 로그에 대한 종료일 변경
            $rental_log[$ind]['ren_person'] = $row['ren_person'];
            $rental_log[$ind]['penNm'] = $rental_log[$ind-1]['ren_person'];
            $rental_log[$ind]['deli_stat'] = '회수';
            $rental_log[$ind]['modifyDtm'] = explode(")", explode("(", $row['ren_person'])[1])[0];
          } else {
            // 정상 대여 종료 상품의 경우는 대여가 종료되었다는 로그가 남지 않아 강제로 생성
            if($i!=0&&$rental_log[$ind-1]['deli_stat'] == '대여'){
              $rental_log[$ind]['strdate'] = $rental_log[$ind-1]['strdate'];
              $rental_log[$ind]['enddate'] = $rental_log[$ind-1]['enddate'];
              $rental_log[$ind]['ren_person'] = '정상종료';
              $rental_log[$ind]['penNm'] = $rental_log[$ind-1]['ren_person'];
              $rental_log[$ind]['deli_stat'] = '회수';
              $rental_log[$ind]['modifyDtm'] = $rental_log[$ind-1]['enddate'];
              $add_index++;
              $ind = $i + $add_index;
              $rental_log[$ind]['stoId'] = $item_detail['stoId'];
              $rental_log[$ind]['gubun'] = $item_detail['gubun'];
              $rental_log[$ind]['itemNm'] = $item_detail['itemNm'];
              $rental_log[$ind]['modifyDtm'] = $item_detail['modifyDtm'];
              $rental_log[$ind]['prodBarNum'] = $item_detail['prodBarNum'];
              $rental_log[$ind]['prodId'] = $item_detail['prodId'];
              $rental_log[$ind]['prodPayCode'] = $item_detail['prodPayCode'];
              $rental_log[$ind]['prodNm'] = $item_detail['prodNm'];
              $rental_log[$ind]['stateCd'] = $item_detail['stateCd'];
              $rental_log[$ind]['stateNm'] = $item_detail['stateNm'];
            }
            $rental_log[$ind]['strdate'] = $row['strdate'];
            $rental_log[$ind]['enddate'] = $row['enddate'];
            $rental_log[$ind]['ren_person'] = $row['ren_person'];
            $rental_log[$ind]['penNm'] = $row['ren_person'];
            $rental_log[$ind]['deli_stat'] = '대여';
            $rental_log[$ind]['modifyDtm'] = $row['strdate'];

            // 맨 마지막 로그가 정상적으로 대여 종료가 된 경우, 회수 로그가 따로 남지 않아 강제로 생성
            if($num_rows == $i && $rental_log[$ind]['deli_stat']=='대여'){
              $time_now = date("Y-m-d");
              $time_target = date($rental_log[$ind]['enddate']);
              if($time_now > $time_target) {              
                $rental_log[$ind+1]['stoId'] = $item_detail['stoId'];
                $rental_log[$ind+1]['gubun'] = $item_detail['gubun'];
                $rental_log[$ind+1]['itemNm'] = $item_detail['itemNm'];
                $rental_log[$ind+1]['modifyDtm'] = $item_detail['modifyDtm'];
                $rental_log[$ind+1]['prodBarNum'] = $item_detail['prodBarNum'];
                $rental_log[$ind+1]['prodId'] = $item_detail['prodId'];
                $rental_log[$ind+1]['prodPayCode'] = $item_detail['prodPayCode'];
                $rental_log[$ind+1]['prodNm'] = $item_detail['prodNm'];
                $rental_log[$ind+1]['stateCd'] = $item_detail['stateCd'];
                $rental_log[$ind+1]['stateNm'] = $item_detail['stateNm'];
                $rental_log[$ind+1]['strdate'] = $row['strdate'];
                $rental_log[$ind+1]['enddate'] = $row['enddate'];
                $rental_log[$ind+1]['ren_person'] = '정상종료';
                $rental_log[$ind+1]['penNm'] = $row['ren_person'];
                $rental_log[$ind+1]['deli_stat'] = '회수';
                $rental_log[$ind+1]['modifyDtm'] = $row['enddate'];
              }
            }
          }
        }

        $sch_rental_log = [];
        if($rental_log==[]) continue; // 렌탈 이력이 없으면 제품관리대장에 기입하지 않는다.
        else { // 검색기간과 대여기간이 겹치지 않는 제품은 제품관리대장에 기입하지 않음
          foreach($rental_log as $row){
            $str_date = new DateTime($row['strdate']);
            $end_date = new DateTime($row['enddate']);

            if($fr_date > $end_date||$to_date < $str_date) continue;
            $sch_rental_log[] = $row;
          }
        }

        if($sch_rental_log==[]) continue; // 검색 조건에 맞는 렌탈 이력이 없으면 제품관리대장에 기입하지 않는다.

        $prod_log[] = $sch_rental_log;
      } // if($item['gubun']) 끝
    } // foreach($stockCntList["data"]) 끝
    if(count($prod_log)==0) continue; // 판매, 대여 기록이 없는 경우 제품관리대장에 기입하지 않음
    $prod_detail[] = $prod_log;
  }
}
json_response(200, json_encode($prod_detail));
?>
