<?php
$sub_menu = '400300';
include_once('./_common.php');

// 데이터가 많을 경우 대비 설정변경
set_time_limit (0);
ini_set('memory_limit', '100M');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$file = $_FILES['excelfile']['tmp_name'];
if (!$file) {
    alert('파일을 업로드해주세요.');
}

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$sheetData = $spreadsheet->getSheet(0)->toArray(null, true, true, true);

function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if($sheetData) {
    $inputs = [];
    $num_rows = $spreadsheet->getSheet(0)->getHighestDataRow('A');
    for ($i = 2; $i <= $num_rows; $i++) {

        // 아이디
        $mb_id = 'CS' . substr(date("Ymd"), 2, 6) . generate_random_string(10);

        $thezone_code = str_replace('-', '', addslashes($sheetData[$i]['A'])); // 거래처 코드
        // 사업자번호
        $mb_giup_bnum = preg_replace(
            "/([0-9]{3})-?([0-9]{2})-?([0-9]{5})/", 
            "$1-$2-$3", 
            str_replace('-', '', addslashes($sheetData[$i]['B']))
        );

        $mb_giup_bname = addslashes($sheetData[$i]['C']); // 기업명
        $mb_tel = addslashes($sheetData[$i]['D']); // 전화번호
        $tel = explode('-', $mb_tel);
        $mb_tel1 = $tel[1];
        $mb_tel2 = $tel[2];
        $mb_tel3 = $tel[3];
        $mb_giup_zip = addslashes($sheetData[$i]['E']); // 우편번호
        $mb_giup_zip1        = isset($mb_giup_zip)           ? substr(trim($mb_giup_zip), 0, 3) : "";
        $mb_giup_zip2        = isset($mb_giup_zip)           ? substr(trim($mb_giup_zip), 3)    : "";
        $mb_giup_addr1 = addslashes($sheetData[$i]['F']); // 기본주소
        $mb_giup_addr2 = addslashes($sheetData[$i]['G']); // 상세주소
                
        $sql = " select count(*) as cnt from `{$g5['member_table']}` where mb_giup_bnum = '{$mb_giup_bnum}' and mb_temp = 0 and mb_id != '{$mb_id}'";
        $row = sql_fetch($sql);
        if ($row['cnt']) {
            alert( $mb_giup_bname . '(' . $mb_giup_bnum . ')은 이미 존재하는 사업자 번호 입니다.');
        }
                
        $inputs[] = array(
            'usrId' => $mb_id,
            'usrPw' => generate_random_string(16),
            'entNm' => $mb_giup_bname,
            'usrPnum' => $mb_tel,
            'entPnum' => $mb_tel,
            'mbType' => 'default',
            'entZip' => $mb_giup_zip1 . $mb_giup_zip2,
            'entAddr' => $mb_giup_addr1,
            'entAddrDetail' => $mb_giup_addr2 . $mb_giup_addr3,
            'usrZip' => $mb_giup_zip1 . $mb_giup_zip2,
            'usrAddr' => $mb_giup_addr1,
            'usrAddrDetail' => $mb_giup_addr2 . $mb_giup_addr3,

            'thezone_code' => $thezone_code,
            'entConAcco1' => '본 계약은 국민건강보험 노인장기요양보험 급여상품의 공급계약을 체결함에 목적이 있다.',
            'entConAcco2' => '본 계약서에 명시되지 아니한 사항이나 의견이 상이할 때에는 상호 협의하에 해결하는 것을 원칙으로 한다.',
            'mb_giup_addr1' => $mb_giup_addr1,
            'mb_giup_addr2' => $mb_giup_addr2,
            'mb_giup_addr3' => $mb_giup_addr3,
            'mb_giup_zip1' => $mb_giup_zip1,
            'mb_giup_zip2' => $mb_giup_zip2,
            'mb_giup_bnum' => $mb_giup_bnum,
        );
    }

    $total_count = 0;

    foreach ($inputs as $input) {
        $result = post_formdata(EROUMCARE_API_ENT_INSERT, $input);
        if($result['errorYN'] !== 'N')
            alert($result['message']);
    
        $mb_entId = $result['data']['entId'];
        if(!$mb_entId) {
            continue;
        }

        
        $sql = "INSERT INTO {$g5['member_table']} SET
            mb_id = '{$input['usrId']}',
            mb_password = '".get_encrypt_string($input['usrPw'])."',
            mb_datetime = '".G5_TIME_YMDHIS."',
            mb_ip = '{$_SERVER['REMOTE_ADDR']}',
            mb_email_certify = '".G5_TIME_YMDHIS."',
            mb_name = '{$input['entNm']}',
            mb_nick = '{$input['entNm']}',
            mb_giup_bname = '{$input['entNm']}',
            mb_tel = '{$input['usrPnum']}',
            mb_hp = '{$input['usrPnum']}',
            mb_giup_btel = '{$input['usrPnum']}',
            mb_zip1 = '{$input['mb_giup_zip1']}',
            mb_zip2 = '{$input['mb_giup_zip2']}',
            mb_addr1 = '{$input['mb_giup_addr1']}',
            mb_addr2 = '{$input['mb_giup_addr2']}',
            mb_addr3 = '{$input['mb_giup_addr3']}',
            mb_giup_zip1 = '{$input['mb_giup_zip1']}',
            mb_giup_zip2 = '{$input['mb_giup_zip2']}',
            mb_giup_addr1 = '{$input['mb_giup_addr1']}',
            mb_giup_addr2 = '{$input['mb_giup_addr2']}',
            mb_giup_addr3 = '{$input['mb_giup_addr3']}',
            mb_entId = '{$mb_entId}',
            mb_entConAcc01 = '{$input['entConAcco1']}',
            mb_entConAcc02 = '{$input['entConAcco2']}',
            mb_temp = TRUE,
            mb_giup_bnum = '{$input['mb_giup_bnum']}',
            mb_thezone = '{$input['thezone_code']}',
            mb_level = 3
        ";
        $sql_result = sql_query($sql);

        if (!$sql_result) {
            continue;
        }
        $total_count++;
    }

    alert_close("{$total_count}명의 임시회원이 등록되었습니다.", false, true);
} else {
    alert_close('파일을 읽을 수 없습니다.');
}
?>
