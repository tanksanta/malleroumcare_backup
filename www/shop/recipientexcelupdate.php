<?php
$sub_menu = '400300';
include_once('../common.php');

// 상품이 많을 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

if(!$member["mb_entId"] || !$member["mb_id"]) {
    alert('사업소 회원만 이용할 수 있습니다.');
}

if($_FILES['excelfile']['tmp_name']) {
    $file = $_FILES['excelfile']['tmp_name'];

    include_once('../lib/Excel/reader.php');

    $data = new Spreadsheet_Excel_Reader();

    // Set output Encoding.
    $data->setOutputEncoding('UTF-8');
    /***
    * if you want you can change 'iconv' to mb_convert_encoding:
    * $data->setUTFEncoder('mb');
    *
    **/

    /***
    * By default rows & cols indeces start with 1
    * For change initial index use:
    * $data->setRowColOffset(0);
    *
    **/



    /***
    *  Some function for formatting output.
    * $data->setDefaultFormat('%.2f');
    * setDefaultFormat - set format for columns with unknown formatting
    *
    * $data->setColumnFormat(4, '%.3f');
    * setColumnFormat - set format for column (apply only to number fields)
    *
    **/

    $data->read($file);

    $total_count = 0;
    $inputs = [];
    for($i = 6; $i <= $data->sheets[0]['numRows']; $i++) {
        $total_count++;
        $j = 1;
        $sendData = [];
        $number = addslashes($data->sheets[0]['cells'][$i][$j++]); //수급자명
        $sendData['penNm'] = addslashes($data->sheets[0]['cells'][$i][$j++]); //수급자명
        $sendData['penJumin'] = addslashes($data->sheets[0]['cells'][$i][$j++]); //주민등록번호
        $sendData['penBirth'] = addslashes($data->sheets[0]['cells'][$i][$j++]); //생년월일
        $sendData['penLtmNum'] = addslashes($data->sheets[0]['cells'][$i][$j++]); //장기요양번호
        $sendData['penRecGraCd'] = addslashes($data->sheets[0]['cells'][$i][$j++]); //인정등급
        $sendData['penExpiStDtm'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//유효기간 시작일
        $sendData['penExpiEdDtm'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//유효기간 종료일
        $sendData['penTypeCd'] = addslashes($data->sheets[0]['cells'][$i][$j++]); //본인부담율 일반 15% = 00/ 감경 9% =01 / 감경 6%=02 / 의료 6% =03 / 기초 0% =04
        $sendData['penGender'] = addslashes($data->sheets[0]['cells'][$i][$j++]); //성별
        $sendData['penConNum'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//휴대번호
        $sendData['penConPnum'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//일반번호
        $sendData['penZip'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//우편번호
        $sendData['penAddr'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//주소
        $sendData['penAddrDtl'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//상세주소
        $sendData['penProNm'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//보호자명
        $sendData['penProBirth'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//생년월일
        $sendData['penProRel'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//관계
        $sendData['penProRelEtc'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//기타관계
        $sendData['penProEmail'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//이메일
        $sendData['penProConNum'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//휴대전화
        $sendData['penProConPnum'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//일반전화
        $sendData['penProZip'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//우편번호
        $sendData['penProAddr'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//주소
        $sendData['penProAddrDtl'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//상세주소
        $y_and_n = addslashes($data->sheets[0]['cells'][$i][$j++]);//수급자동일

        if($y_and_n=="Y") {
            $sendData['penProNm'] = $sendData['penNm'];
            $sendData['penProBirth'] = $sendData['penBirth'];//생년월일
            $sendData['penProRel'] = "";
            $sendData['penProRelEtc'] = "본인";//기타관계
            $sendData['penProConNum'] = $sendData['penConNum'];//휴대전화
            $sendData['penProConPnum'] = $sendData['penConPnum'];//일반전화
            $sendData['penProZip'] = $sendData['penZip'];//우편번호
            $sendData['penProAddr'] = $sendData['penProAddr'];//주소
            $sendData['penProAddrDtl'] = $sendData['penAddrDtl'];//주소
        }
        $sendData['entUsrId'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//담당직원정보

        $sendData['penCnmTypeCd'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//확인자
        $sendData['penRecTypeCd'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//수령방법
        $sendData['penRecTypeTxt'] = addslashes($data->sheets[0]['cells'][$i][$j++]);//수령방법 기타
        $sendData['penRemark'] =  addslashes($data->sheets[0]['cells'][$i][$j++]);//특이사항

        $sendData['entId'] = $member["mb_entId"];
        $sendData['appCd'] = "01";
        $sendData['usrId'] = $member["mb_id"];
        $sendData['delYn'] = "N";

        if($valid = valid_recipient_input($sendData)) {
            // 입력값 오류 발생
            alert(($i-5)."번째 순번을 확인해주세요. ({$sendData['penNm']} 수급자)\\n오류 : ".$valid);
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
    }

    alert('완료되었습니다.');
} else {
    alert('파일을 읽을 수 없습니다.');
}
?>
