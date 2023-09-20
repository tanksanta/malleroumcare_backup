<?php

include_once("./_common.php");

if(!$member["mb_id"] || !$member["mb_entId"])
  json_response(400, '먼저 로그인하세요.');


if($member["cert_reg_sts"] == "Y"){//공인인증서 등록이 완료 되었을 경우
	if ((time() - $_SESSION['CREATED']) > 64800) {//18시간 : 64800, 3분 : 180;
		// 세션생성 18시간 경과
		$_SESSION['CREATED'] = 0;  // update creation time
		$_SESSION['Pwd'] = "";
	}
if($_SESSION['PriKey'] == "" && $_SESSION['PubKey'] == ""){//공인인증서 등록 완료
	$cert_data_ref =  explode("|",$member["cert_data_ref"]);
	if(strtotime(base64_decode($cert_data_ref[2])." 23:59:59") < time()){//인증서 만료
		json_response(400, '등록된 인증서가 사용 기간이 만료 되었습니다. 공인인증서를 재등록 해 주세요.', array(
		  'err_code' => "3",
		));
		exit;
	}
	if($member["mb_level"]<9){
		if($member["mb_ent_num"] == "" || strlen(preg_replace("/[^0-9]*/s","",$member["mb_ent_num"])) != 11){
			json_response(400, '장기요양기관번호를 입력해 주세요.', array(
			  'err_code' => "5",
			));
			exit;
		}
	}
	if($_SESSION['Pwd'] == ""){
		json_response(400, "[ ".base64_decode($cert_data_ref[1]).' ]로 등록된 공인인증서가 있습니다. 공인인증서 비밀번호를 입력해 주세요.', array(
		  'err_code' => "2",
		  'cert_name' => base64_decode($cert_data_ref[1]),
		));
		exit;
	}

	$upload_dir = $_SERVER['DOCUMENT_ROOT']."/data/file/member/tilko/";
	
	$file_name = base64_encode($cert_data_ref[0]);
	if(file_exists($upload_dir.$file_name.".enc") || file_exists($upload_dir.$file_name.".txt")){
		if($_SESSION['Pwd'] != ""){//비밀번호를 받았을 때
			if(file_exists($upload_dir.$file_name.".enc")){
				@system('echo -n '.base64_decode($_SESSION['Pwd']).' | openssl aes-256-cbc -d -in '.$upload_dir.$file_name.'.enc -out '.$upload_dir.$file_name.'.txt -pass stdin'); //입력 받은 비밀번호로 파일 복호화 저장
			}
			//@system('echo -n '."thkc!@#".' | openssl aes-256-cbc -d -in '.$upload_dir.$file_name.'.enc -out '.$upload_dir.$file_name.'.txt -pass stdin'); //고정값으로 파일 복호화 저장
			$fp = fopen($upload_dir.$file_name.".txt", 'r');    // list.txt 파일을 읽기 전용으로 열고 반환된 파일 포인터를 $fp에 저장함. 
			$i = 0;
			while(!feof($fp)){ // feof() 함수는 전달받은 파일 포인터가 파일의 끝에 도달하면, true를 반환
				$cert[$i] = fgets($fp); // 한 줄씩 $member 변수에 저장하고 
				//echo $cert[]."<br>";  // 출력함.
				$i++;
			}
			$_SESSION['PriKey'] = $cert[0];
			$_SESSION['PubKey'] = $cert[1];
			if(file_exists($upload_dir.$file_name.".enc")){
				unlink($upload_dir.$file_name.".txt");//암호화 안된 파일 삭제
			}
		}else{//비밀번호를 받지 않았을 때
			json_response(400, "[ ".base64_decode($cert_data_ref[1]).' ]로 등록된 공인인증서가 있습니다. 공인인증서 비밀번호를 입력해 주세요.', array(
			  'err_code' => "2",
			  'cert_name' => base64_decode($cert_data_ref[1]),
			));
			exit;
		}
	}else{//등록 파일 삭제 된 경우
		json_response(400, '먼저 공인인증서를 등록 해 주세요.',array(
			'err_code' => "1",
		));
		exit;
		
	}
}else{
	if($_SESSION['Pwd'] == ""){
		json_response(400, "[ ".base64_decode($cert_data_ref[1]).' ]로 등록된 공인인증서가 있습니다. 공인인증서 비밀번호를 입력해 주세요.', array(
		  'err_code' => "2",
		  'cert_name' => base64_decode($cert_data_ref[1]),
		));
		exit;
	}
}
}

if($member["mb_level"]<9){
		if($member["mb_ent_num"] == "" || strlen(preg_replace("/[^0-9]*/s","",$member["mb_ent_num"])) != 11){
			json_response(400, '장기요양기관번호를 입력해 주세요.', array(
			  'err_code' => "5",
			));
			exit;
		}
	}

//json_response(400, $_SESSION['PriKey'],array(
//		'err_code' => "1",
//	));
//exit;
if($_SESSION['PriKey'] == "" || $_SESSION['PubKey'] == ""){
	json_response(400, '먼저 공인인증서를 등록 해 주세요.',array(
		'err_code' => "1",
	));
	
}

set_include_path(get_include_path() . PATH_SEPARATOR . G5_SHOP_PATH.'/tilko/phpseclib1.0.19');
require_once('Crypt/RSA.php');
class contractToolSetList { 
	// initialize value with -1.
	// these value means : -1 < 0: init, 대여불가
	//						0 == 0; 대여 가능
	//						 > 0; 이미 구매한 수량 

	// 판매 품목
	public $movingToilet =-1; // 이동변기 : 1개
	public $bathingChair =-1; // 목욕의자 : 1개
	public $safetyHandGrip =-1; // 안전손잡이 : 10개
	public $safetyPreventSlivery =-1; // 미끄럼방지용품(매트,방지액): 5개
	public $sliveryPreventSocks =-1;	 //미끄럼방지양말 : 6켤레	
	public $simpleToilet =-1;			 //간이변기 : 2개
	public $cane =-1;					//지팡이 : 1개
	public $bedsorePreventMatriss =-1; 	//욕창방지 매트리스 : 1개
	public $cushionPreventMatriss =-1; 	//욕창방지 방석 : 1개 
	public $postureChangeTool =-1; 		//자세변환 용구 : 5개
	public $adultWalker =-1; 		//성인용 보행기 : 2개
	public $incontinencePanty =-1; 		//요실금 팬티: 4개
	public $runway =-1; 		//경사로(실내용) : 6개

	// 대여 품목
	public $mWheelChair = -1; // 수동 휠체어
	public $eBed = -1; // 전동침대 
	public $mBed = -1; // 수동침대 
	public $lendBedsorePreventionMatriss = -1; // 욕창예방 매트리스 
	public $portableBath = -1; // 이동 욕조 
	public $bathLift = -1; // 목욕 리프트 
	public $loiteringDetection = -1; // 배회 감지기 
	public $lendRunway = -1; // 경사로(실외용) 
}

// 23.07.18 : 서원 - 틸코 API 관련 외부 호출 부분 함수 처리(-시작-)
// 						해당 함수는 NPIA201M01 / NPIA201P01 / NPIA208P01 3가지 항목에 대해서만 사용!!
function Longtermcare_API($host, $uri, $headers, $data) {

	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_URL, $host.$uri);
	curl_setopt($oCurl, CURLOPT_POST, true);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 2); // curl이 첫 응답 시간에 대한 timeout
	curl_setopt($oCurl, CURLOPT_TIMEOUT, 3); // curl 전체 실행 시간에 대한 timeout
	
	$response = curl_exec($oCurl);   
	curl_close($oCurl);
    
	return $response;
}
// 23.07.18 : 서원 - 틸코 API 관련 외부 호출 부분 함수 처리(-종료-)


// collect Get Data
$id = $_POST['id'];
$rn = $_POST['rn'];
//$id = $_GET['id'];
//$rn = $_POST['rn'];
//$id = '권숙자';
//$rn = '2007175271';
$str = ".$sid .$rn : 입력값이 잘못 되었습니다 ";
//return json_response(400, $str);

$BusinessNumber = ($member["mb_level"]>8 || $member["mb_ent_num"] == "")?"32623000271":str_replace("-","",$member["mb_ent_num"]);//$data['BN'];
$RecipientName= $rn; //'이간난'//$data['rn']
$RecipientId= $id; //'1612104758';//$data['id'];

//$apiHost   = "https://api.tilko.net";
$apiHost   = "http://211.110.140.26";
$apiKey    = "a55aaf2f84a0477da82bb4572f97babf";


// AES 암호화 함수
function aesEncrypt($aesKey, $aesIv, $plainText) {
    $ret = openssl_encrypt($plainText, 'AES-128-CBC', $aesKey, OPENSSL_RAW_DATA, $aesIv);   //default padding은 PKCS7 padding
    return base64_encode($ret);
}
function array_flatten($array) {

   $return = array();
   foreach ($array as $key => $value) {
       if (is_array($value)){ $return = array_merge($return, array_flatten($value));}
       else {$return[$key] = $value;}
   }
   return $return;

}

// RSA 공개키(Public Key) 조회 함수
function getPublicKey($apiKey) {
    global $apiHost;

    $url        = $apiHost . "api/Auth/GetPublicKey?APIkey=" . $apiKey;

    $curl       = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL             => $url,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_CUSTOMREQUEST   => "GET",
        CURLOPT_SSL_VERIFYHOST  => 0,
        CURLOPT_SSL_VERIFYPEER  => 0,
		CURLOPT_TIMEOUT => 5
    )); 

    $response   = curl_exec($curl);

    curl_close($curl);

    return json_decode($response, true)["PublicKey"];
}
function random_key(){	
	$random_key = random_bytes(16);
	if($random_key == ""){
		random_key();
	}else{
		return $random_key;
	}
}

$log_txt = "\r\n";
$log_txt .= '(' . date("Y-m-d H:i:s") . ')'."\r\n";
$log_txt .= "--  사업소: ".$member["mb_name"]."({$member['mb_id']})\r\n";
$log_txt .= "--  수급자: ".$id."/".$rn."\r\n";

// RSA Public Key 조회
//$rsaPublicKey   = getPublicKey($apiKey);
$rsaPublicKey   = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoX90PipEec8UkGBkaaoP1zMZG3FlvlpYjVuZpga5smxQXeN4efJjw8cejv19C4Dg082H+Oe4y+dmkstV+q9o8CrWYFBHs8DMSMAVFTgR+JwIugDU8XTQv7FwUf3B8iBEJ9K+hM42e93SoOed6TBakd0SDdMOTlk+gTwz7JDFSyIItQ8teQoygNL9M1jfT1aL6A5p2jTuLHsl7ul2G+H4ZoKswG3vb9LmYMscoaSlGCL24Gk6hnb6md6e+D/dSN/Lsv+ylkyGJJtYDaQbjO2w2bkeMPmQ3+Xxto5Q9DLm2mR3wfZMipGBMAZWo7ZsNs0b2oskbiyfqKl6/82hE8B9ZwIDAQAB";
//print("rsaPublicKey:" . $rsaPublicKey);


// AES Secret Key 및 IV 생성
$aesKey     = random_key();
$aesIv      = str_repeat(chr(0), 16);
$log_txt .= "--  aesKey: ".$aesKey."\r\n";

// AES Key를 RSA Public Key로 암호화
$rsa            = new Crypt_RSA();
$rsa->loadKey($rsaPublicKey);
$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);

$aesCipheredKey = $rsa->encrypt($aesKey);
$log_txt .= "--  aesCipheredKey: ".$aesCipheredKey."\r\n";

// API URL 설정
// HELP: https://tilko.dev/Help/Api/POST-api-apiVersion-Longtermcare-NPIA201M01
//$url_recipientContractDetail  = $apiHost."/api/v1.0/Longtermcare/NPIA201M01";
//$url_recipientToolList 		  = $apiHost."/api/v1.0/Longtermcare/NPIA201P01";
//$url_recipientContractHistory	  = $apiHost."/api/v1.0/Longtermcare/NPIA208P01";


// 23.07.18 - 서원 : 기존 코드 주석으로 유지하며 신규 변수 생성.
$url_recipientContractDetail  = "/api/v1.0/Longtermcare/NPIA201M01";
$url_recipientToolList 		  = "/api/v1.0/Longtermcare/NPIA201P01";
$url_recipientContractHistory	  = "/api/v1.0/Longtermcare/NPIA208P01";


// 인증서 경로 설정
//$certPath   = "C:/Users/username/AppData/LocalLow/NPKI/yessign/USER/user01/";
//$certPath   = G5_SHOP_PATH.'/tilko/'; //"C:/Users/username/AppData/LocalLow/NPKI/yessign/USER/user01/";
//$certFile   = $certPath . "signCert.der";
//$keyFile    = $certPath . "signPri.key";
$certPw = base64_decode($_SESSION['Pwd']);//"thkc##1301493";
$PubKey = base64_decode($_SESSION['PubKey']);
$PriKey = base64_decode($_SESSION['PriKey']);

// API 요청 파라미터 설정
$headers    = array(
    "Content-Type:"             . "application/json",
    "API-Key:"                  . $apiKey,
    "ENC-Key:"                  . base64_encode($aesCipheredKey),
);

//ENC-Key 누락 점검 로그기록======================
$log_dir = $_SERVER["DOCUMENT_ROOT"].'/data/log/';
if(!is_dir($log_dir)){//인증서 파일 생성할 폴더 확인 
	@umask(0);
	@mkdir($log_dir,0777);
	//@chmod($upload_dir, 0777);
}

$log_txt .= "--  ENC-Key: ".base64_encode($aesCipheredKey)."\r\n";

$log_file = fopen($log_dir . 'log'.date("Ymd").'.txt', 'a');
fwrite($log_file, $log_txt . "\r\n\r\n");
fclose($log_file);
//=============================================

$bodies_recipientContractDetail     = array(
    "CertFile" => aesEncrypt($aesKey, $aesIv, $PubKey),
    "KeyFile" => aesEncrypt($aesKey, $aesIv, $PriKey),
    "CertPassword" => aesEncrypt($aesKey, $aesIv, $certPw),
    "BusinessNumber" => aesEncrypt($aesKey, $aesIv, $BusinessNumber),
    "Name" => aesEncrypt($aesKey, $aesIv, $RecipientName),
    "IdentityNumber" => aesEncrypt($aesKey, $aesIv, $RecipientId),
);
$bodies_recipientToolList = array(
    "CertFile" => aesEncrypt($aesKey, $aesIv, $PubKey),
    "KeyFile" => aesEncrypt($aesKey, $aesIv, $PriKey),
    "CertPassword" => aesEncrypt($aesKey, $aesIv, $certPw),
    "BusinessNumber" => aesEncrypt($aesKey, $aesIv, $BusinessNumber),
    "IdentityNumber" => aesEncrypt($aesKey, $aesIv, $RecipientId),
    "Col" => "__VALUE__", // = response_recipientContractDetail['Result']['ds_welToolTgHistList']['LTC_MGMT_NO_SEQ']
);
$bodies_recipientContractHistory = array(
    "CertFile" => aesEncrypt($aesKey, $aesIv, $PubKey),
    "KeyFile" => aesEncrypt($aesKey, $aesIv, $PriKey),
    "CertPassword" => aesEncrypt($aesKey, $aesIv, $certPw),
    "BusinessNumber" => aesEncrypt($aesKey, $aesIv, $BusinessNumber),
    "IdentityNumber" => aesEncrypt($aesKey, $aesIv, $RecipientId),
    "StartDate" => "__VALUE__", // = response_recipientContractDetail['Result']['ds_toolPayLmtList'][0]['APDT_FR_DT']
    "EndDate" => "__VALUE__", // = response_recipientContractDetail['Result']['ds_toolPayLmtList'][0]['APDT_TO_DT']

);

$obj_purchaseHistory = new contractToolSetList();

//print_r($bodies_recipientToolList);

/*
	// API 호출
	$curl   = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL             => $url_recipientContractDetail,
		CURLOPT_RETURNTRANSFER  => true,
		CURLOPT_CUSTOMREQUEST   => "POST",
		CURLOPT_POSTFIELDS      => json_encode($bodies_recipientContractDetail),
		CURLOPT_HTTPHEADER      => $headers,
		CURLOPT_VERBOSE         => false,
		CURLOPT_SSL_VERIFYHOST  => 0,
		CURLOPT_SSL_VERIFYPEER  => 0,
		CURLOPT_TIMEOUT => 5

	));

	$response   = curl_exec($curl);
	curl_close($curl);
*/

//echo "step1";



// 23.07.18 : 서원 - 요양정보조회 함수 호출 부분에 대한 에러 처리 부분(-시작-).
//						서버 이상으로 인한 접속불가와 관련된 에러 발생시 다른 순서에 따른 apiHost값을 변경하여 다른 서버에 재 시도.
//						해당 재시도는 1차-당사서버 / 2차-틸코메인서버 / 3차-틸코서브서버
try {
    // 1단계 try-catch 블록
	$response = Longtermcare_API( $apiHost , $url_recipientContractDetail , $headers , $bodies_recipientContractDetail );
	if ($response === false) { throw new Exception(''); }
	
	// 23.07.18 : 서원 - 리턴 값이 있을 경우 API조회 데이터 확인.
	$recipientContractDetail = json_decode(substr($response,strpos($response,'{')),TRUE);
	if ( strcmp($recipientContractDetail['Status'],'OK') != 0) { throw new Exception(''); }

} catch (Exception $e) {
    // 1단계 try-catch 블록에서 발생한 예외 처리
	$apiHost = "https://api.tilko.net";

	try {
		// 2단계 try-catch 블록
		$response = Longtermcare_API( $apiHost , $url_recipientContractDetail , $headers , $bodies_recipientContractDetail );
		if ($response === false) { throw new Exception(''); }

		// 23.07.18 : 서원 - 리턴 값이 있을 경우 API조회 데이터 확인.
		$recipientContractDetail = json_decode(substr($response,strpos($response,'{')),TRUE);
		if ( strcmp($recipientContractDetail['Status'],'OK') != 0) { throw new Exception(''); }

    } catch (Exception $e) {
        // 2단계 try-catch 블록에서 발생한 예외 처리
		$apiHost   = "https://api2.tilko.net";

		try {
			// 3단계 try-catch 블록
			$response = Longtermcare_API( $apiHost , $url_recipientContractDetail , $headers , $bodies_recipientContractDetail );
		} catch (Exception $e) {
			// 3단계 try-catch 블록에서 발생한 예외 처리
	
		}
    }
}
// 23.07.18 : 서원 - 요양정보조회 함수 호출 부분에 대한 에러 처리 부분(-종료-).


// 23.07.18 : 서원 - 틸코 서버 host변경 관련 로그기록(-시작-)
$log_dir = $_SERVER["DOCUMENT_ROOT"].'/data/log/';
if(!is_dir($log_dir)){//인증서 파일 생성할 폴더 확인 
	@umask(0);
	@mkdir($log_dir,0777);
	//@chmod($upload_dir, 0777);
}

$log_txt .= "apiHost: = = = = = = = = = = \r\n";
$log_txt .= "apiHost: ".$apiHost."\r\n";
$log_txt .= "apiHost: = = = = = = = = = = \r\n";

$log_file = fopen($log_dir . 'log'.date("Ymd").'.txt', 'a');
fwrite($log_file, $log_txt . "\r\n\r\n");
fclose($log_file);
// 23.07.18 : 서원 - 틸코 서버 host변경 관련 로그기록(-종료-)


$log_txt .= "url : ".$url_recipientContractDetail."\r\n";
$log_txt .= "response : ".$response."\r\n";
//echo "step1";
$log_file = fopen($log_dir . 'log'.date("Ymd").'.txt', 'a');
fwrite($log_file, $log_txt . "\r\n\r\n");
fclose($log_file);


// 복지용구계약대상자조회
$recipientContractDetail = json_decode(substr($response,strpos($response,'{')),TRUE);
if ( strcmp($recipientContractDetail['Status'],'OK') != 0)
{
	if($recipientContractDetail['Message'] == ""){
		return json_response(406, "조회오류 : 서버 응답시간이 초과 되었습니다. 잠시 후 다시 조회 해주세요.",array(
		  'err_code' => "4",
		));
	}
	return json_response(406, "조회오류 : ".$recipientContractDetail['Message'],array(
		  'err_code' => "4",
		));
}
//print_r($recipientContractDetail);
//print_r($recipientContractDetail['Result']);

//$recipientContractDetail = json_decode($json_data,TRUE);
//print_r($recipientContractDetail['Result']['ds_welToolTgtHistList'][0]['LTC_MGMT_NO_SEQ']);
$count = count($recipientContractDetail['Result']['ds_welToolTgtHistList']);// find most recently updated list

//$bodies_recipientToolList['Col'] = $recipientContractDetail['Result']['ds_welToolTgtHistList'][$count-1]['LTC_MGMT_NO_SEQ'];
for($i = 0; $i<$count; $i++){//인정구간 리스트 조회	
	if(date("Ymd") == $recipientContractDetail['Result']['ds_welToolTgtHistList'][$i]["RCGT_EDA_FR_DT"] || date("Ymd") == $recipientContractDetail['Result']['ds_welToolTgtHistList'][$i]["RCGT_EDA_TO_DT"] || (date("Ymd") > $recipientContractDetail['Result']['ds_welToolTgtHistList'][$i]["RCGT_EDA_FR_DT"] && date("Ymd") < $recipientContractDetail['Result']['ds_welToolTgtHistList'][$i]["RCGT_EDA_TO_DT"])){//현재 날짜가 인정구간 안에 포함 될 때만 추출
		$bodies_recipientToolList['Col'] = $recipientContractDetail['Result']['ds_welToolTgtHistList'][$i]['LTC_MGMT_NO_SEQ'];
	}
}

$response = '';

/*
$curl   = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL             => $url_recipientToolList,
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_CUSTOMREQUEST   => "POST",
    CURLOPT_POSTFIELDS      => json_encode($bodies_recipientToolList),
    CURLOPT_HTTPHEADER      => $headers,
    CURLOPT_VERBOSE         => false,
    CURLOPT_SSL_VERIFYHOST  => 0,
    CURLOPT_SSL_VERIFYPEER  => 0,
	CURLOPT_TIMEOUT => 5
));

$response   = curl_exec($curl);
curl_close($curl);
*/


// 23.07.18 - 서원 : 틸코 API 외부 호출 부분 함수로 변경.
$response = Longtermcare_API( $apiHost , $url_recipientToolList , $headers , $bodies_recipientToolList );
$log_txt = "url : ".$url_recipientToolList."\r\n";
$log_txt .= "response : ".$response."\r\n";
$log_file = fopen($log_dir . 'log'.date("Ymd").'.txt', 'a');
fwrite($log_file, $log_txt . "\r\n\r\n");
fclose($log_file);

// 복지용구계약대상자조회
//$recipientToolList = json_decode(substr($response,strpos($response,'{')),TRUE);
$recipientToolList = $response;
$arr_recipientToolList = json_decode($recipientToolList,TRUE);
if ( strcmp($arr_recipientToolList['Status'],'OK') != 0)
{
	if($arr_recipientToolList['Message'] == ""){
		return json_response(406, "조회오류 : 서버 응답시간이 초과 되었습니다. 잠시 후 다시 조회 해주세요.",array(
		  'err_code' => "4",
		));
	}
	return json_response(406, "조회오류 : ".$arr_recipientToolList['Message'],array(
		  'err_code' => "4",
		));
}

// 대여가능/불가능,구매가능/불가능 항목 체크
if ($arr_recipientToolList['Result']['ds_payPsblLnd1'] != null)
{
	$count = count($arr_recipientToolList['Result']['ds_payPsblLnd1']);
	for ( $i = 0 ; $i < $count ;$i++ )
	{
		if ( strcmp( $arr_recipientToolList['Result']['ds_payPsblLnd1'][$i]['WIM_ITM_CD'], '수동휠체어') == 0)
		{
			$obj_purchaseHistory->mWheelChair = 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsblLnd1'][$i]['WIM_ITM_CD'], '전동침대') == 0)
		{
			$obj_purchaseHistory->eBed= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsblLnd1'][$i]['WIM_ITM_CD'], '수동침대') == 0)
		{
			$obj_purchaseHistory->mBed= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsblLnd1'][$i]['WIM_ITM_CD'], '욕창예방 매트리스') == 0)
		{
			$obj_purchaseHistory->lendBedsorePreventionMatriss= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsblLnd1'][$i]['WIM_ITM_CD'], '이동욕조') == 0)
		{
			$obj_purchaseHistory->portableBath= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsblLnd1'][$i]['WIM_ITM_CD'], '목욕리프트') == 0)
		{
			$obj_purchaseHistory->bathLift= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsblLnd1'][$i]['WIM_ITM_CD'], '배회감지기') == 0)
		{
			$obj_purchaseHistory->loiteringDetection= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsblLnd1'][$i]['WIM_ITM_CD'], '경사로(실외용)') == 0)
		{
			$obj_purchaseHistory->lendRunway= 0;
		}
		else
		{
			return json_response(400, '알수 없는 품목이 수신되었습니다.',array(
		  'err_code' => "4",
		));
		}
	
	}
}

if ($arr_recipientToolList['Result']['ds_payPsbl1'] != null)
{
	$count = count($arr_recipientToolList['Result']['ds_payPsbl1']);
	for ( $i = 0 ; $i < $count ;$i++ )
	{
		if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '이동변기') == 0)
		{
			$obj_purchaseHistory->movingToilet = 0; // 이동변기 : 1개
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '목욕의자') == 0)
		{
			$obj_purchaseHistory->bathingChair = 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '안전손잡이') == 0)
		{
			$obj_purchaseHistory->safetyHandGrip= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '미끄럼 방지용품') == 0)
		{
			$obj_purchaseHistory->safetyPreventSlivery = 0;
			$obj_purchaseHistory->sliveryPreventSocks= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '간이변기') == 0)
		{
			$obj_purchaseHistory->simpleToilet = 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '지팡이') == 0)
		{
			$obj_purchaseHistory->cane = 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '욕창예방 매트리스') == 0)
		{
			$obj_purchaseHistory->bedsorePreventMatriss= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '욕창예방방석') == 0)
		{
			$obj_purchaseHistory->cushionPreventMatriss= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '자세변환용구') == 0)
		{
			$obj_purchaseHistory->postureChangeTool= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '성인용보행기') == 0)
		{
			$obj_purchaseHistory->adultWalker= 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '요실금팬티') == 0)
		{
			$obj_purchaseHistory->incontinencePanty = 0;
		}
		else if ( strcmp( $arr_recipientToolList['Result']['ds_payPsbl1'][$i]['WIM_ITM_CD'], '경사로(실내용)') == 0)
		{
			$obj_purchaseHistory->runway = 0;
		}
		else
		{
			return json_response(400, '알수 없는 품목이 수신되었습니다.',array(
		  'err_code' => "4",
		));
		}
	
	}
}


//print_r($arr_recipientToolList);

$response = '';

// find recipient contract history of the dedicated period
if ($recipientContractDetail['Result']['ds_toolPayLmtList'] != null)
{
	$count = count($recipientContractDetail['Result']['ds_toolPayLmtList']);
	$dateToday = date("Ymd");
 
	for ($i=0; $i < $count ; $i++)
	{
	    if ((strtotime($recipientContractDetail['Result']['ds_toolPayLmtList'][$i]['APDT_FR_DT']) < strtotime($dateToday) &&
	    strtotime($recipientContractDetail['Result']['ds_toolPayLmtList'][$i]['APDT_TO_DT']) > strtotime($dateToday)) || $recipientContractDetail['Result']['ds_toolPayLmtList'][$i]['APDT_FR_DT'] == $dateToday || $recipientContractDetail['Result']['ds_toolPayLmtList'][$i]['APDT_TO_DT'] == $dateToday)
	    {
	        $target_period = $i;
			break;
	    }
	}
}
/////////////////////////////////////////////////////////

$bodies_recipientContractHistory['StartDate'] = $recipientContractDetail['Result']['ds_toolPayLmtList'][$target_period]['APDT_FR_DT'];
$bodies_recipientContractHistory['EndDate'] = $recipientContractDetail['Result']['ds_toolPayLmtList'][$target_period]['APDT_TO_DT'];


/*
$curl   = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL             => $url_recipientContractHistory,
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_CUSTOMREQUEST   => "POST",
    CURLOPT_POSTFIELDS      => json_encode($bodies_recipientContractHistory),
    CURLOPT_HTTPHEADER      => $headers,
    CURLOPT_VERBOSE         => false,
    CURLOPT_SSL_VERIFYHOST  => 0,
    CURLOPT_SSL_VERIFYPEER  => 0,
		CURLOPT_TIMEOUT => 5
));
$response   = curl_exec($curl);
curl_close($curl);
*/


// 23.07.18 - 서원 : 틸코 API 외부 호출 부분 함수로 변경.
$response = Longtermcare_API( $apiHost , $url_recipientContractHistory , $headers , $bodies_recipientContractHistory );
$log_txt = "url : ".$url_recipientContractHistory."\r\n";
$log_txt .= "response : ".$response."\r\n";
$log_file = fopen($log_dir . 'log'.date("Ymd").'.txt', 'a');
fwrite($log_file, $log_txt . "\r\n\r\n");
fclose($log_file);

$recipientContractHistory = json_decode($response,TRUE);
if ( strcmp($recipientContractHistory['Status'],'OK') != 0)
{
	if($recipientContractHistory['Message'] == ""){
		return json_response(406, "조회오류 : 서버 응답시간이 초과 되었습니다. 잠시 후 다시 조회 해주세요.",array(
		  'err_code' => "4",
		));
	}
	return json_response(406, "조회오류 : ".$recipientContractHistory['Message'],array(
		  'err_code' => "4",
		));
}
//print_r($recipientContractHistory);
////////////count for the remaining right to purchase
if ($recipientContractHistory['Result']['ds_result'] != null){

	
	$count = count($recipientContractHistory['Result']['ds_result']);
	for ( $i = 0; $i < $count ; $i++ )
	{
		if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '이동변기') == 0)
		{
			//$obj_purchaseHistory->movingToilet++; // 이동변기 : 1개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '목욕의자') == 0)
		{
			//$obj_purchaseHistory->bathingChair++; // 목욕의자 : 1개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '안전손잡이') == 0)
		{
			//$obj_purchaseHistory->safetyHandGrip++; // 안전손잡이 : 10개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '미끄럼 방지용품') == 0)
		{
			if (strpos($recipientContractHistory['Result']['ds_result'][$i]['MGDS_NM'], '양말') != 0)
			{
				//$obj_purchaseHistory->safetyPreventSlivery++; // 미끄럼방지용품(매트,방지액): 5개
			}
			else 
			{
				//$obj_purchaseHistory->sliveryPreventSocks++;	 //미끄럼방지양말 : 6켤레	
			}
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '간이변기') == 0)
		{
			//$obj_purchaseHistory->simpleToilet++;			 //간이변기 : 2개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '지팡이') == 0)
		{
			//$obj_purchaseHistory->cane++;					//지팡이 : 1개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '욕창예방 매트리스') == 0)
		{
			//$obj_purchaseHistory->bedsorePreventMatriss++; 	//욕창방지 매트리스 : 1개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '욕창예방방석') == 0)
		{
			//$obj_purchaseHistory->cushionPreventMatriss++; 	//욕창방지 방석 : 1개 
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '자세변환용구') == 0)
		{
			//$obj_purchaseHistory->postureChangeTool++; 		//자세변환 용구 : 5개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '성인용보행기') == 0)
		{
			//$obj_purchaseHistory->adultWalker++; 		//성인용 보행기 : 2개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '요실금팬티') == 0)
		{
			//$obj_purchaseHistory->incontinencePanty++; 		//요실금 팬티: 4개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '경사로(실내용)') == 0)
		{
			//$obj_purchaseHistory->runway++; 		//경사로(실내용) : 6개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '수동휠체어') == 0)
		{
			//$obj_purchaseHistory->mWheelChair++; 		//경사로(실내용) : 6개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '전동침대') == 0)
		{
			//$obj_purchaseHistory->eBed++; 		//경사로(실내용) : 6개
		}else if ( strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '수동침대') == 0)
		{
			//$obj_purchaseHistory->mBed++;
		}
		else if ( strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '욕창예방 매트리스') == 0)
		{
			//$obj_purchaseHistory->lendBedsorePreventionMatriss++;
		}
		else if ( strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '이동욕조') == 0)
		{
			//$obj_purchaseHistory->portableBath++;
		}
		else if ( strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '목욕리프트') == 0)
		{
			//$obj_purchaseHistory->bathLift++;
		}
		else if ( strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '배회감지기') == 0)
		{
			//$obj_purchaseHistory->loiteringDetection++;
		}
		else if ( strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '경사로(실외용)') == 0)
		{
			//$obj_purchaseHistory->lendRunway++;
		}
		else
		{
			return json_response(400, '알수 없는 품목이 수신되었습니다.',array(
		  'err_code' => "4",
		));
		}
	}
}

$arr_ph = (array) $obj_purchaseHistory;
//print_r($arr_ph);
//print_r(json_encode($arr_ph));

//echo ("<br>");
//print_r($response_arr['Result']['Result']);
//print_r($response_arr['Result']['ds_Result']);
//print_r($response_arr['Result']['ds_welToolTgtList']);
//foreach($response_arr['Result']['ds_Result'] as $key ==> $value)
//{
//	echo $key;
//	echo $value";
//echo ("<br>");
//}
// 복지용구급여가능불가능품목조회

return json_response(200, '조회가 완료되었습니다.', array(
  'penId' => $penId,
  'recipientContractDetail' => $recipientContractDetail,
  'recipientToolList' => $recipientToolList,
  'recipientContractHistory' => $recipientContractHistory,
  'recipientPurchaseRecord' => json_encode($arr_ph) 
));
?>
