<?php
$sub_menu = '400300';
include_once('../common.php');

// 상품이 많을 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$file = $_FILES['excelfile']['tmp_name'];
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$sheetData = $spreadsheet->getSheet(0)->toArray(null, true, true, true);

function parse_birth($ymd) {
  $date = DateTime::createFromFormat('Ymd', $ymd);

  if(!$date) return '';

  return $date->format('Y-m-d');
}

if($sheetData) {
    $inputs = [];
    $num_rows = $spreadsheet->getSheet(0)->getHighestDataRow('A');
    for ($i = 3; $i <= $num_rows; $i++) {
      $sendData = [];
      $sendData['penNm'] = addslashes($sheetData[$i]['A']); //수급자명
      $sendData['penLtmNum'] = addslashes($sheetData[$i]['B']); //장기요양번호
      $sendData['penBirth'] = addslashes(parse_birth($sheetData[$i]['C'])); //생년월일
      $penGender = addslashes($sheetData[$i]['D']);
      $sendData['penGender'] = $penGender == '1' ? '남' : ($penGender == '2' ? '여' : ''); //남 여
      $sendData['penConNum'] = addslashes($sheetData[$i]['E']); // 휴대번호
      if(substr($sendData['penConNum'],0,2) != "01"){
		alert("({$i}행) {$sendData['penNm']} 수급자\\n오류 : 휴대폰번호를 확인해주세요.");
	  }
	  $sendData['penConPnum'] = addslashes($sheetData[$i]['F']); // 일반번호
      $sendData['penZip'] = addslashes($sheetData[$i]['G']); // 우편번호
      $sendData['penAddr'] = addslashes($sheetData[$i]['H']);//주소
      $sendData['penAddrDtl'] = addslashes($sheetData[$i]['I']);//상세주소

      $sendData['penProRel'] = addslashes($sheetData[$i]['J']); // 보호자 관계
	  switch($sendData['penProRel']){
		case "처": $sendData['penProRel'] = "00"; break; 
		case "남편": $sendData['penProRel'] = "01"; break;
		case "자": $sendData['penProRel'] = "02"; break;
		case "자부": $sendData['penProRel'] = "03"; break;
		case "사위": $sendData['penProRel'] = "04"; break;
		case "형제": $sendData['penProRel'] = "05"; break;
		case "자매": $sendData['penProRel'] = "06"; break;
		case "손": $sendData['penProRel'] = "07"; break;
		case "배우자 형제자매": $sendData['penProRel'] = "08"; break;
		case "외손": $sendData['penProRel'] = "09"; break;
		case "부모": $sendData['penProRel'] = "10"; break;
		default : $sendData['penProRel'] = "00"; break;
	  }
      $sendData['penProNm'] = addslashes($sheetData[$i]['K']);//보호자명
      $sendData['penProBirth'] = addslashes(parse_birth($sheetData[$i]['L']));//생년월일
      $sendData['penProEmail'] = addslashes($sheetData[$i]['M']);//이메일
      $sendData['penProConNum'] = addslashes($sheetData[$i]['N']);//휴대전화
      $sendData['penProConPnum'] = addslashes($sheetData[$i]['O']);//일반전화
      $sendData['penProZip'] = addslashes($sheetData[$i]['P']);//우편번호
      $sendData['penProAddr'] = addslashes($sheetData[$i]['Q']);//주소
      $sendData['penProAddrDtl'] = addslashes($sheetData[$i]['R']);//상세주소

      $penCnmTypeCd = addslashes($sheetData[$i]['S']);
      $sendData['penCnmTypeCd'] = $penCnmTypeCd == '1' ? '00' : ($penCnmTypeCd == '2' ? '01' : ''); // 장기요양급여 제공기록지 00: 수급자, 01: 보호자
      $penRecTypeCd = addslashes($sheetData[$i]['T']);
      $sendData['penCnmTypeCd'] = $penRecTypeCd == '1' ? '00' : ($penRecTypeCd == '2' ? '01' : ''); // 장기요양급여 제공기록지 수령방법 00: 방문, 01: 유선
      $sendData['penRemark'] = addslashes($sheetData[$i]['U']); // 특이사항

      
      $sendData['entId'] = $member["mb_entId"];
      $sendData['appCd'] = "01";
      $sendData['usrId'] = $member["mb_id"];
      $sendData['delYn'] = "N";

      if($valid = valid_recipient_input($sendData, false, true)) {
          // 입력값 오류 발생
          alert("({$i}행) {$sendData['penNm']} 수급자\\n오류 : ".$valid);
          // echo "{$sendData['penNm']} 수급자\\n오류 : ".$valid;
      }
      $inputs[] = normalize_recipient_input($sendData);
    }
    
    $exist_recipient = [];
    foreach($inputs as $input) {

        // 시작 -->
        // 서원 : 22.09.02 - [공통] 사업소 : 수급자관리→수급자일괄등록 불가 오류
        // 설명 : 변수 오기입
        $ent_pen = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
            'usrId' => $input['usrId'],
            'entId' => $input['entId'],
            'penLtmNum' => $input['penLtmNum']
        ));
        /*
        $ent_pen = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
            'usrId' => $sendData['usrId'],
            'entId' => $sendData['entId'],
            'penLtmNum' => $penLtmNum,
        ));
        */

        //
        // 종료 -->

        $ent_pen = $ent_pen['data'][0];
        if ($ent_pen) {
            array_push($exist_recipient, $input['penNm']);
        }
        else {
            $res = get_eroumcare(EROUMCARE_API_RECIPIENT_INSERT, $input);
            if($res['errorYN'] != 'N') {
                echo "{$input['penNm']} 수급자를 업로드 하는 도중 오류가 발생했습니다.<br>";
                echo "{$input['penNm']} 수급자부터 다시 등록해주세요.<br><br>";
                echo "오류 내용 : ";
                var_dump($res);
                exit;
            }
    
            if ($input['penRecGraCd'] == '00') {
                $penRecGraNm = '등급외';
            } else {
                $penRecGraNm = (int)$input['penRecGraCd'] . '등급';
            }
    
            $penTypeNm = '';
            switch($input['penTypeCd']) {
                case '00' :
                    $penTypeNm = '일반 15%';
                    break;
                case '01' :
                    $penTypeNm = '감경 9%';
                    break;
                case '02' :
                    $penTypeNm = '감경 6%';
                    break;
                case '03' :
                    $penTypeNm = '의료 6%';
                    break;
                case '04' :
                    $penTypeNm = '기초 0%';
                    break;
            }
    
            // 적용기간 기준일
            $penGraApplyMonth = explode('-', $input['penGraApplyDate'])[0];
            $penGraApplyDay = explode('-', $input['penGraApplyDate'])[1];
    
            // 등급적용 시점
            $pen_gra_edit_dtm = date('Y-m-d');
            if($sendData['penExpiStDtm']) {
              $pen_gra_edit_dtm = $sendData['penExpiStDtm'];
            }
    
            $sql = "INSERT INTO
                recipient_grade_log
            SET
                pen_id = '{$res['data']['penId']}',
                pen_rec_gra_cd = '{$input['penRecGraCd']}',
                pen_rec_gra_nm = '{$penRecGraNm}',
                pen_type_cd = '{$input['penTypeCd']}',
                pen_type_nm = '{$penTypeNm}',
                pen_gra_edit_dtm = '{$pen_gra_edit_dtm}',
                pen_gra_apply_month = '{$penGraApplyMonth}',
                pen_gra_apply_day = '{$penGraApplyDay}',
                created_by = '{$member['mb_id']}' ";
            $row = sql_query($sql);
    
            // 취급상품 모두 등록
            $setItemData = [];
            $setItemData['penId'] = $res['data']['penId'];
            $setItemData['itemList'] = [];
            foreach(array_keys($sale_product_table) as $item) {
                $setItemData['itemList'][] = $item;
            }
            foreach(array_keys($rental_product_table) as $item) {
                $setItemData['itemList'][] = $item;
            }
    
            get_eroumcare(EROUMCARE_API_RECIPIENT_ITEM_INSERT, $setItemData);
        }
    }

    if (count($exist_recipient) > 0) {
        $exist_count = count($exist_recipient);
        $total_count = count($inputs) - $exist_count;
        // echo "중복미등록 : {$exist_count}건<br>";
        // echo "오류 내용 : ";
        // var_dump($exist_count);
        alert_close("{$total_count}명의 수급자가 등록되었습니다.\\n중복미등록 : {$exist_count}건", false, true);
    }
    else {
        $total_count = count($inputs);
        alert_close("{$total_count}명의 수급자가 등록되었습니다.", false, true);
    }
} else {
    alert_close('파일을 읽을 수 없습니다.');
}
?>
