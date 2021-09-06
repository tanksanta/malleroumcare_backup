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
  $date = DateTime::createFromFormat('ymd', $ymd);

  if(!$date) return '';

  return $date->format('Y-m-d');
}

if($sheetData) {
    $inputs = [];
    $num_rows = $spreadsheet->getSheet(0)->getHighestDataRow('A');
    for ($i = 2; $i <= $num_rows; $i++) {
        if (!$sheetData[$i]['C']) continue;
        $sendData = [];
        $sendData['penNm'] = addslashes($sheetData[$i]['C']); //수급자명
        $sendData['penGender'] = addslashes($sheetData[$i]['D']); // 성별
        $sendData['penTypeBiz'] = addslashes($sheetData[$i]['E']); // 대상자 구분
        
        $sendData['penTypeNm'] = '';
        $sendData['penTypeCd'] = '';
        switch($sendData['penTypeBiz']) {
            case '일반수급자' :
                $sendData['penTypeCd'] = '00';
                $sendData['penTypeNm'] = '일반 15%';
                break;
            case '감경(9%)' :
                $sendData['penTypeCd'] = '01';
                $sendData['penTypeNm'] = '감경 9%';
                break;
            case '감경(6%)' :
            case '감경자' :
                $sendData['penTypeCd'] = '02';
                $sendData['penTypeNm'] = '감경 6%';
                break;
            case '의료(6%)' :
            case '의료급여자' :
                $sendData['penTypeCd'] = '03';
                $sendData['penTypeNm'] = '의료 6%';
                break;
            case '기초생활수급자' :
                $sendData['penTypeCd'] = '04';
                $sendData['penTypeNm'] = '기초 0%';
                break;
        }

        //생년월일
        $sendData['penBirth'] = addslashes(parse_birth($sheetData[$i]['F']));
        $sendData['penJumin'] = addslashes($sheetData[$i]['F']);
        $sendData['penLtmNum'] = addslashes($sheetData[$i]['G']); //장기요양번호
        $sendData['penExpiStDtm'] = addslashes($sheetData[$i]['H']); // 유효기간 시작일
        $sendData['penExpiEdDtm'] = addslashes($sheetData[$i]['I']); // 유효기간 종료일
        $sendData['penRecGraCd'] = addslashes(str_pad($sheetData[$i]['K'][0], 2, '0', STR_PAD_LEFT)); //인정등급
        $sendData['penConNum'] = hyphen_tel_number($sheetData[$i]['L']); // 휴대번호
        $sendData['penConPnum'] = hyphen_tel_number($sheetData[$i]['M']); // 일반번호
        $sendData['penProRel'] = '11'; // 보호자 관계
        $sendData['penProRelEtc'] = addslashes($sheetData[$i]['N']); // 보호자 관계
        $sendData['penProNm'] = addslashes($sheetData[$i]['O']);//보호자명
        // 보호자 생년월일
        $sendData['penProBirth'] = addslashes(parse_birth($sheetData[$i]['P']));
        $sendData['penProConNum'] = hyphen_tel_number($sheetData[$i]['Q']); // 보호자 휴대전화
        
        $sendData['penZip'] = addslashes(str_replace('-', '', $sheetData[$i]['R'])); // 우편번호
        $sendData['penAddr'] = addslashes($sheetData[$i]['S']); //주소
        $sendData['penAddrDtl'] = addslashes($sheetData[$i]['T']); //상세주소
        
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
    
    foreach($inputs as $input) {
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

        // 등급기준일
        $penGraApplyDtm = $input['penExpiStDtm'];
        if($penGraApplyDtm) {
          $penGraApplyMonth = explode('-', $input['penExpiStDtm'])[1];
          $penGraApplyDay = explode('-', $input['penExpiStDtm'])[2];

          $sql = "INSERT INTO
              recipient_grade_log
          SET
              pen_id = '{$res['data']['penId']}',
              pen_rec_gra_cd = '{$input['penRecGraCd']}',
              pen_rec_gra_nm = '{$penRecGraNm}',
              pen_type_cd = '{$input['penTypeCd']}',
              pen_type_nm = '{$input['penTypeNm']}',
              pen_gra_edit_dtm = '{$penGraApplyDtm}',
              pen_gra_apply_month = '{$penGraApplyMonth}',
              pen_gra_apply_day = '{$penGraApplyDay}',
              created_by = '{$member['mb_id']}' ";
          $row = sql_query($sql);
        }
        
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

    $total_count = count($inputs);
    alert_close("{$total_count}명의 수급자가 등록되었습니다.", false, true);
} else {
    alert_close('파일을 읽을 수 없습니다.');
}
?>