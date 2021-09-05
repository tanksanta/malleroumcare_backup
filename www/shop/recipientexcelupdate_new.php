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

if($sheetData) {
    $total_count = 0;
    $inputs = [];
    for ($i = 3; $i <= count($sheetData); $i++) {
        $total_count++;
        $j = 1;
        $sendData = [];
        $sendData['penNm'] = addslashes($sheetData[$i]['A']); //수급자명
        $sendData['penJumin'] = addslashes($sheetData[$i]['B']); //주민등록번호 앞자리
        $sendData['penBirth'] = addslashes(substr($sheetData[$i]['C'],0,4) . '-' . substr($sheetData[$i]['C'],4,2) . '-' . substr($sheetData[$i]['C'],6,2)); //생년월일
        $penGender = addslashes($sheetData[$i]['D']);
        $sendData['penGender'] = $penGender == '1' ? '남' : '여'; //남 여
        $sendData['penConNum'] = addslashes($sheetData[$i]['E']); // 휴대번호
        $sendData['penConPnum'] = addslashes($sheetData[$i]['F']); // 일반번호
        $sendData['penZip'] = addslashes($sheetData[$i]['G']); // 우편번호
        $sendData['penAddr'] = addslashes($sheetData[$i]['H']);//주소
        $sendData['penAddrDtl'] = addslashes($sheetData[$i]['I']);//상세주소
        $sendData['penLtmNum'] = addslashes($sheetData[$i]['J']); //장기요양번호
        $sendData['penRecGraCd'] = addslashes(str_pad($sheetData[$i]['K'], 2, '0', STR_PAD_LEFT)); //인정등급
        $sendData['penTypeCd'] = addslashes(str_pad($sheetData[$i]['L'] - 1, 2, '0', STR_PAD_LEFT)); //본인부담율 일반 15% = 00/ 감경 9% =01 / 감경 6%=02 / 의료 6% =03 / 기초 0% =04
        // 유효기간 시작일
        $sendData['penExpiStDtm'] = addslashes(
            explode('/', $sheetData[$i]['M'])[2]
            . "-"
            .str_pad(explode('/', $sheetData[$i]['M'])[0], 2, '0', STR_PAD_LEFT)
            . "-"
            .str_pad(explode('/', $sheetData[$i]['M'])[1], 2, '0', STR_PAD_LEFT)
        );
        // 유효기간 종료일
        $sendData['penExpiEdDtm'] = addslashes(
            explode('/', $sheetData[$i]['N'])[2]
            . "-"
            .str_pad(explode('/', $sheetData[$i]['N'])[0], 2, '0', STR_PAD_LEFT)
            . "-"
            .str_pad(explode('/', $sheetData[$i]['N'])[1], 2, '0', STR_PAD_LEFT)
        );
        $sendData['penGraApplyDate'] = addslashes($sheetData[$i]['O']);//적용기간 기준일
        $sendData['penProRel'] = addslashes($sheetData[$i]['P']); // 보호자 관계
        $sendData['penProNm'] = addslashes($sheetData[$i]['Q']);//보호자명
        $sendData['penProBirth'] = addslashes($sheetData[$i]['R']);//생년월일
        $sendData['penProEmail'] = addslashes($sheetData[$i]['S']);//이메일
        $sendData['penProConNum'] = addslashes($sheetData[$i]['T']);//휴대전화
        $sendData['penProConPnum'] = addslashes($sheetData[$i]['U']);//일반전화
        $sendData['penProZip'] = addslashes($sheetData[$i]['V']);//우편번호
        $sendData['penProAddr'] = addslashes($sheetData[$i]['W']);//주소
        $sendData['penProAddrDtl'] = addslashes($sheetData[$i]['X']);//상세주소
        $penCnmTypeCd = addslashes($sheetData[$i]['Y']);
        $sendData['penCnmTypeCd'] = $penCnmTypeCd == '1' ? '00' : '01'; // 장기요양급여 제공기록지 00: 수급자, 01: 보호자
        $penRecTypeCd = addslashes($sheetData[$i]['Z']);
        $sendData['penCnmTypeCd'] = $penCnmTypeCd == '1' ? '00' : '01'; // 장기요양급여 제공기록지 수령방법 00: 방문, 01: 유선
        $sendData['penRemark'] = addslashes($sheetData[$i]['AA']); // 특이사항

        
        $sendData['entId'] = $member["mb_entId"];
        $sendData['appCd'] = "01";
        $sendData['usrId'] = $member["mb_id"];
        $sendData['delYn'] = "N";

        if($valid = valid_recipient_input($sendData, false, true)) {
            // 입력값 오류 발생
            alert("{$sendData['penNm']} 수급자\\n오류 : ".$valid);
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

        // 등급기준일
        $penGraApplyDtm = date("Y") . '-' . $input['penGraApplyDate'];
        $penGraApplyMonth = explode('-', $input['penGraApplyDate'])[0];
        $penGraApplyDay = explode('-', $input['penGraApplyDate'])[1];

        $sql = "INSERT INTO
            recipient_grade_log
        SET
            pen_id = '{$res['data']['penId']}',
            pen_rec_gra_cd = '{$input['penRecGraCd']}',
            pen_rec_gra_nm = '{$penRecGraNm}',
            pen_type_cd = '{$input['penTypeCd']}',
            pen_type_nm = '{$penTypeNm}',
            pen_gra_edit_dtm = '{$penGraApplyDtm}',
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
    
    alert_close("{$total_count}명의 수급자가 등록되었습니다.", false, true);
} else {
    alert_close('파일을 읽을 수 없습니다.');
}
?>
