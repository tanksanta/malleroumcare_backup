<?php

include_once("./_common.php");

if(!$member["mb_id"] || !$member["mb_entId"])
  json_response(400, '먼저 로그인하세요.');

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
// collect Get Data
$id = $_POST['id'];
$rn = $_POST['rn'];
//$id = $_GET['id'];
//$rn = $_POST['rn'];
//$id = '권숙자';
//$rn = '2007175271';
$str = ".$sid .$rn : 입력값이 잘못 되었습니다 ";
//return json_response(400, $str);

$BusinessNumber = '32623000271';//$data['BN'];
$RecipientName= $rn; //'이간난'//$data['rn']
$RecipientId= $id; //'1612104758';//$data['id'];

$apiHost   = "https://api.tilko.net/";
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
        CURLOPT_SSL_VERIFYPEER  => 0
    )); 

    $response   = curl_exec($curl);

    curl_close($curl);

    return json_decode($response, true)["PublicKey"];
}


// RSA Public Key 조회
$rsaPublicKey   = getPublicKey($apiKey);
//print("rsaPublicKey:" . $rsaPublicKey);


// AES Secret Key 및 IV 생성
$aesKey     = random_bytes(16);
$aesIv      = str_repeat(chr(0), 16);


// AES Key를 RSA Public Key로 암호화
$rsa            = new Crypt_RSA();
$rsa->loadKey($rsaPublicKey);
$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);

$aesCipheredKey = $rsa->encrypt($aesKey);


// API URL 설정
// HELP: https://tilko.dev/Help/Api/POST-api-apiVersion-Longtermcare-NPIA201M01
$url_recipientContractDetail  = $apiHost . "api/v1.0/Longtermcare/NPIA201M01";
$url_recipientToolList 		  = $apiHost . "api/v1.0/Longtermcare/NPIA201P01";
$url_recipientContractHistory	  = $apiHost . "api/v1.0/Longtermcare/NPIA208P01";


// 인증서 경로 설정
//$certPath   = "C:/Users/username/AppData/LocalLow/NPKI/yessign/USER/user01/";
$certPath   = G5_SHOP_PATH.'/tilko/'; //"C:/Users/username/AppData/LocalLow/NPKI/yessign/USER/user01/";
$certFile   = $certPath . "signCert.der";
$keyFile    = $certPath . "signPri.key";
$certPw		= "thkc##1301493";

// API 요청 파라미터 설정
$headers    = array(
    "Content-Type:"             . "application/json",
    "API-Key:"                  . $apiKey,
    "ENC-Key:"                  . base64_encode($aesCipheredKey),
);

$bodies_recipientContractDetail     = array(
    "CertFile" => aesEncrypt($aesKey, $aesIv, file_get_contents($certFile)),
    "KeyFile" => aesEncrypt($aesKey, $aesIv, file_get_contents($keyFile)),
    "CertPassword" => aesEncrypt($aesKey, $aesIv, $certPw),
    "BusinessNumber" => aesEncrypt($aesKey, $aesIv, $BusinessNumber),
    "Name" => aesEncrypt($aesKey, $aesIv, $RecipientName),
    "IdentityNumber" => aesEncrypt($aesKey, $aesIv, $RecipientId),
);
$bodies_recipientToolList = array(
    "CertFile" => aesEncrypt($aesKey, $aesIv, file_get_contents($certFile)),
    "KeyFile" => aesEncrypt($aesKey, $aesIv, file_get_contents($keyFile)),
    "CertPassword" => aesEncrypt($aesKey, $aesIv, $certPw),
    "BusinessNumber" => aesEncrypt($aesKey, $aesIv, $BusinessNumber),
    "IdentityNumber" => aesEncrypt($aesKey, $aesIv, $RecipientId),
    "Col" => "__VALUE__", // = response_recipientContractDetail['Result']['ds_welToolTgHistList']['LTC_MGMT_NO_SEQ']
);
$bodies_recipientContractHistory = array(
    "CertFile" => aesEncrypt($aesKey, $aesIv, file_get_contents($certFile)),
    "KeyFile" => aesEncrypt($aesKey, $aesIv, file_get_contents($keyFile)),
    "CertPassword" => aesEncrypt($aesKey, $aesIv, $certPw),
    "BusinessNumber" => aesEncrypt($aesKey, $aesIv, $BusinessNumber),
    "IdentityNumber" => aesEncrypt($aesKey, $aesIv, $RecipientId),
    "StartDate" => "__VALUE__", // = response_recipientContractDetail['Result']['ds_toolPayLmtList'][0]['APDT_FR_DT']
    "EndDate" => "__VALUE__", // = response_recipientContractDetail['Result']['ds_toolPayLmtList'][0]['APDT_TO_DT']

);

$obj_purchaseHistory = new contractToolSetList();

//print_r($bodies_recipientToolList);

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
    CURLOPT_SSL_VERIFYPEER  => 0
));

$response   = curl_exec($curl);
curl_close($curl);

//echo "step1";

// 복지용구계약대상자조회
$recipientContractDetail = json_decode(substr($response,strpos($response,'{')),TRUE);
if ( strcmp($recipientContractDetail['Status'],'OK') != 0)
{
	return json_response(406, "조회오류 : ".$recipientContractDetail['Message']);
}
//print_r($recipientContractDetail);
//print_r($recipientContractDetail['Result']);

//$recipientContractDetail = json_decode($json_data,TRUE);
//print_r($recipientContractDetail['Result']['ds_welToolTgtHistList'][0]['LTC_MGMT_NO_SEQ']);
$count = count($recipientContractDetail['Result']['ds_welToolTgtHistList']);// find most recently updated list

$bodies_recipientToolList['Col'] = $recipientContractDetail['Result']['ds_welToolTgtHistList'][$count-1]['LTC_MGMT_NO_SEQ'];

$response = '';

$curl   = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL             => $url_recipientToolList,
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_CUSTOMREQUEST   => "POST",
    CURLOPT_POSTFIELDS      => json_encode($bodies_recipientToolList),
    CURLOPT_HTTPHEADER      => $headers,
    CURLOPT_VERBOSE         => false,
    CURLOPT_SSL_VERIFYHOST  => 0,
    CURLOPT_SSL_VERIFYPEER  => 0
));

$response   = curl_exec($curl);
curl_close($curl);

// 복지용구계약대상자조회
//$recipientToolList = json_decode(substr($response,strpos($response,'{')),TRUE);
$recipientToolList = $response;
$arr_recipientToolList = json_decode($recipientToolList,TRUE);
if ( strcmp($arr_recipientToolList['Status'],'OK') != 0)
{
	return json_response(406, "조회오류 : ".$arr_recipientToolList['Message']);
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
			return json_response(400, '알수 없는 품목이 수신되었습니다.');
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
			return json_response(400, '알수 없는 품목이 수신되었습니다.');
		}
	
	}
}


//print_r($arr_recipientToolList);

$response = '';

// find recipient contract history of the dedicated period
if ($recipientContractDetail['Result']['ds_toolPayLmtList'] != null)
{
	$count = count($recipientContractDetail['Result']['ds_toolPayLmtList']);
	$dateToday = date("ymd");
 
	for ($i=0; $i < $count ; $i++)
	{
	    if ((strtotime($recipientContractDetail['Result']['ds_toolPayLmtList'][$i]['APDT_FR_DT']) < strtotime($dateToday)) &&
	    (strtotime($recipientContractDetail['Result']['ds_toolPayLmtList'][$i]['APDT_TO_DT']) > strtotime($dateToday)))
	    {
	        $target_period = $i;
			break;
	    }
	}
}
/////////////////////////////////////////////////////////

$bodies_recipientContractHistory['StartDate'] = $recipientContractDetail['Result']['ds_toolPayLmtList'][$target_period]['APDT_FR_DT'];
$bodies_recipientContractHistory['EndDate'] = $recipientContractDetail['Result']['ds_toolPayLmtList'][$target_period]['APDT_TO_DT'];
$curl   = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL             => $url_recipientContractHistory,
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_CUSTOMREQUEST   => "POST",
    CURLOPT_POSTFIELDS      => json_encode($bodies_recipientContractHistory),
    CURLOPT_HTTPHEADER      => $headers,
    CURLOPT_VERBOSE         => false,
    CURLOPT_SSL_VERIFYHOST  => 0,
    CURLOPT_SSL_VERIFYPEER  => 0
));

$response   = curl_exec($curl);
curl_close($curl);

$recipientContractHistory = json_decode($response,TRUE);
if ( strcmp($recipientContractHistory['Status'],'OK') != 0)
{
	return json_response(406, "조회오류 : ".$recipientContractHistory['Message']);
}
//print_r($recipientContractHistory);
////////////count for the remaining right to purchase
if ($recipientContractHistory['Result']['ds_result'] != null){

	
	$count = count($recipientContractHistory['Result']['ds_result']);
	for ( $i = 0; $i < $count ; $i++ )
	{
		if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '이동변기') == 0)
		{
			$obj_purchaseHistory->movingToilet++; // 이동변기 : 1개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '목욕의자') == 0)
		{
			$obj_purchaseHistory->bathingChair++; // 목욕의자 : 1개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '안전손잡이') == 0)
		{
			$obj_purchaseHistory->safetyHandGrip++; // 안전손잡이 : 10개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '미끄럼 방지용품') == 0)
		{
			if (strpos($recipientContractHistory['Result']['ds_result'][$i]['MGDS_NM'], '양말') != 0)
			{
				$obj_purchaseHistory->safetyPreventSlivery++; // 미끄럼방지용품(매트,방지액): 5개
			}
			else 
			{
				$obj_purchaseHistory->sliveryPreventSocks++;	 //미끄럼방지양말 : 6켤레	
			}
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '간이변기') == 0)
		{
			$obj_purchaseHistory->simpleToilet++;			 //간이변기 : 2개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '지팡이') == 0)
		{
			$obj_purchaseHistory->cane++;					//지팡이 : 1개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '욕창예방 매트리스') == 0)
		{
			$obj_purchaseHistory->bedsorePreventMatriss++; 	//욕창방지 매트리스 : 1개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '욕창예방방석') == 0)
		{
			$obj_purchaseHistory->cushionPreventMatriss++; 	//욕창방지 방석 : 1개 
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '자세변환용구') == 0)
		{
			$obj_purchaseHistory->postureChangeTool++; 		//자세변환 용구 : 5개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '성인용보행기') == 0)
		{
			$obj_purchaseHistory->adultWalker++; 		//성인용 보행기 : 2개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '요실금팬티') == 0)
		{
			$obj_purchaseHistory->incontinencePanty++; 		//요실금 팬티: 4개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '경사로(실내용)') == 0)
		{
			$obj_purchaseHistory->runway++; 		//경사로(실내용) : 6개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '수동휠체어') == 0)
		{
			$obj_purchaseHistory->mWheelChair++; 		//경사로(실내용) : 6개
		}
		else if (strcmp($recipientContractHistory['Result']['ds_result'][$i]['PROD_NM'], '전동침대') == 0)
		{
			$obj_purchaseHistory->eBed++; 		//경사로(실내용) : 6개
		}
		else
		{
			return json_response(400, '알수 없는 품목이 수신되었습니다.');
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
