<?php
if (!defined('_GNUBOARD_')) exit;

include_once 'Popbill/PopbillFax.php';


function sendFax($send_fax_arr) {
  try {
    // 링크아이디
    $LinkID = 'THKC';

    // 비밀키
    $SecretKey = 'SK6O74B5rFqXWhm3Fa73ESVTXwBL2vfQiWvrHE4tzlc=';

    // 통신방식 기본은 CURL , curl 사용에 문제가 있을경우 STREAM 사용가능.
    // STREAM 사용시에는 php.ini의 allow_url_fopen = on 으로 설정해야함.
    define('LINKHUB_COMM_MODE','CURL');

    $FaxService = new FaxService($LinkID, $SecretKey);

    // 연동환경 설정값, 개발용(true), 상업용(false)
    $FaxService->IsTest(false);

    // 인증토큰에 대한 IP제한기능 사용여부, 권장(true)
    $FaxService->IPRestrictOnOff(false);

    // 팝빌 API 서비스 고정 IP 사용여부(GA), 기본값(false)
    $FaxService->UseStaticIP(false);

    // 로컬시스템 시간 사용 여부 true(기본값) - 사용, false(미사용)
    $FaxService->UseLocalTimeYN(true);

    // 팝빌 회원 사업자번호
    $testCorpNum = '6178614330';
    // $balance = $FaxService -> GetBalance($testCorpNum);
    // if ($balance < 100) {
    //   return "팩스 잔액({$balance})이 부족합니다.";
    // }

    // 팝빌 회원 아이디
    $testUserID = 'thkc1300';

    // 팩스전송 발신번호
    $Sender = '028301308';

    // 팩스전송 발신자명
    $SenderName = '(주)티에이치케이컴퍼니';

    // 팩스전송파일, 해당파일에 읽기 권한이 설정되어 있어야 함. 최대 20개.
    // $Files = array('/Users/taewoongkong/Desktop/eroum/malleroumcare/www/lib/test.pdf');

    // $FileDatas[] = array(
    //     //파일명
    //     'fileName' => 'ledger.xls',
    //     //fileData - BLOB 데이터 입력
    //     'fileData' => $excelData //file_get_contenst-바이너리데이터 추출
    // );

    // 예약전송일시(yyyyMMddHHmmss) ex) 20151212230000, null인경우 즉시전송
    $reserveDT = null;

    // 광고팩스 전송여부
    $adsYN = false;

        // 팩스제목
     $title = '티에이치케이컴퍼니';

     // $Receivers[] = array(
     //     // 팩스 수신번호
     //     'rcv' => '05075306114',
     //     // 팩스 수신자명
     //     'rcvnm' => '공태웅'
     // );

     // 전송요청번호
     // 파트너가 전송 건에 대해 관리번호를 구성하여 관리하는 경우 사용.
     // 1~36자리로 구성. 영문, 숫자, 하이픈(-), 언더바(_)를 조합하여 팝빌 회>    원별로 중복되지 않도록 할당.
     $requestNum = '';

     // echo 'console.log("' . var_dump($send_fax_arr) . '")';
     foreach($send_fax_arr as $data) {
       $FileData = [];
       $Receiver = [];
       // $FileData[] = array('fileName' => $data['filename'], 'fileData' => $data['excel']);
       $FileData[] = array('fileName' => $data['filename'], 'fileData' => file_get_contents($data['filename']));
       $Receiver[] = array('rcv' => $data['rcv'], 'rcvnm' => $data['rcvnm']);
       $receiptNum = $FaxService->SendFAXBinary($testCorpNum, $Sender, $Receiver, $FileData, $reserveDT, $testUserID, $SenderName, $adsYN, $title, $requestNum);

       // $result = $FaxService->GetFaxDetail($testCorpNum, $receiptNum);
       // return $result;
     }

     // $receiptNum = $FaxService->SendFAXBinary($testCorpNum, $Sender, $Rece    iver, $FileData, $reserveDT, $testUserID, $SenderName, $adsYN, $title, $requ    estNum);

     // $url = $FaxService->GetUnitCost($testCorpNum);
     // return $receiptNum;
   }
   catch (PopbillException $pe) {
       $code = $pe->getCode();
       $message = $pe->getMessage();
       return "[" . $code . "]" . $message;
   }
 }
/*

    // 팩스제목
    $title = '티에이치케이컴퍼니';

    // $Receivers[] = array(
    //     // 팩스 수신번호
    //     'rcv' => '05075306114',
    //     // 팩스 수신자명
    //     'rcvnm' => '공태웅'
    // );

    // 전송요청번호
    // 파트너가 전송 건에 대해 관리번호를 구성하여 관리하는 경우 사용.
    // 1~36자리로 구성. 영문, 숫자, 하이픈(-), 언더바(_)를 조합하여 팝빌 회원별로 중복되지 않도록 할당.
    $requestNum = '';

    //echo 'console.log("' . var_dump($send_fax_arr) . '")';
    foreach($send_fax_arr as $data) {
      $Receiver = [];
      $Receiver[] = array('rcv' => $data['rcv'], 'rcvnm' => $data['rcvnm']);

      if( isset($data['type']) && $data['type']==="SendFAX" ) {
        $FileData = "";
        $FileData = array( $data['filename'] );
        $receiptNum = $FaxService->SendFAX($testCorpNum, $Sender, $Receiver, $FileData, $reserveDT, $testUserID , $SenderName, $adsYN, $title, $requestNum);
      } else {
        $FileData = [];
        $FileData[] = array('fileName' => $data['filename'], 'fileData' => $data['excel']);
        $receiptNum = $FaxService->SendFAXBinary($testCorpNum, $Sender, $Receiver, $FileData, $reserveDT, $testUserID, $SenderName, $adsYN, $title, $requestNum);
      }

      // $result = $FaxService->GetFaxDetail($testCorpNum, $receiptNum);
      // return $result;
    }

    // $receiptNum = $FaxService->SendFAXBinary($testCorpNum, $Sender, $Receiver, $FileData, $reserveDT, $testUserID, $SenderName, $adsYN, $title, $requestNum);

    // $url = $FaxService->GetUnitCost($testCorpNum);
    // return $receiptNum;
  }
  catch (PopbillException $pe) {
      $code = $pe->getCode();
      $message = $pe->getMessage();
      return "[" . $code . "]" . $message;
  }
}

*/
?>