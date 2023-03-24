<?php

/********************
    상수 선언
********************/

define('G5_VERSION', '그누보드5');
define('G5_GNUBOARD_VER', '5.3.3.3');
define('G5_YOUNGCART_VER', '5.3.3.3.1');

// 이 상수가 정의되지 않으면 각각의 개별 페이지는 별도로 실행될 수 없음
define('_GNUBOARD_', true);

if (PHP_VERSION >= '5.1.0') {
    //if (function_exists("date_default_timezone_set")) date_default_timezone_set("Asia/Seoul");
    date_default_timezone_set("Asia/Seoul");
}

/********************
    경로 상수
********************/

/*
보안서버 도메인
회원가입, 글쓰기에 사용되는 https 로 시작되는 주소를 말합니다.
포트가 있다면 도메인 뒤에 :443 과 같이 입력하세요.
보안서버주소가 없다면 공란으로 두시면 되며 보안서버주소 뒤에 / 는 붙이지 않습니다.
입력예) https://www.domain.com:443/gnuboard5
*/
define('G5_DOMAIN', '');
define('G5_HTTPS_DOMAIN', '');

/*
www.sir.kr 과 sir.kr 도메인은 서로 다른 도메인으로 인식합니다. 쿠키를 공유하려면 .sir.kr 과 같이 입력하세요.
이곳에 입력이 없다면 www 붙은 도메인과 그렇지 않은 도메인은 쿠키를 공유하지 않으므로 로그인이 풀릴 수 있습니다.
*/
define('G5_COOKIE_DOMAIN',  '');

define('G5_ADMIN_DIR',      'adm');
define('G5_BBS_DIR',        'bbs');
define('G5_CSS_DIR',        'css');
define('G5_DATA_DIR',       'data');
define('G5_EXTEND_DIR',     'extend');
define('G5_IMG_DIR',        'img');
define('G5_JS_DIR',         'js');
define('G5_LIB_DIR',        'lib');
define('G5_PLUGIN_DIR',     'plugin');
define('G5_SKIN_DIR',       'skin');
define('G5_EDITOR_DIR',     'editor');
define('G5_MOBILE_DIR',     'mobile');
define('G5_OKNAME_DIR',     'okname');

define('G5_KCPCERT_DIR',    'kcpcert');
define('G5_LGXPAY_DIR',     'lgxpay');

define('G5_SNS_DIR',        'sns');
define('G5_SYNDI_DIR',      'syndi');
define('G5_PHPMAILER_DIR',  'PHPMailer');
define('G5_SESSION_DIR',    'session');
define('G5_THEME_DIR',      'theme');

// 구글 파이어베이스 API
define('GOOGLE_API_KEY', 'AAAAx6iZn-E:APA91bF0R-XCX6x5f9t7jBsuzDi7QqQ20L9ky1r-occvv8FdI47D8xD1oeVOt_AQmK1Axfp3H_-yTD4DzmxX5fFVlizXr1TT6fqERpAiEzwXHBnkMvVd6pX3XYHWYriKQGdWN5vbpOB4');

// 이로움 API HOST
define('EROUMCARE_API_HOST',                                     'https://system.eroumcare.com');
$subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];
if ($_SERVER["HTTP_HOST"] == 'test.eroumcare.com' || $subdomain == 'test') {
    define('EROUMCARE_API_HOST',                                   'https://test.eroumcare.com');
} else {
    define('EROUMCARE_API_HOST',                                     'https://system.eroumcare.com');
}

// 이로움 API PORT
define('EROUMCARE_API_PORT',                                     9901);
// 주문 추가
define('EROUMCARE_API_ORDER_INSERT',                            EROUMCARE_API_HOST . '/api/order/insert');
// 주문 수정
define('EROUMCARE_API_ORDER_UPDATE',                            EROUMCARE_API_HOST . '/api/order/update');
// 주문 수정
define('EROUMCARE_API_ORDER_EDIT',                              EROUMCARE_API_HOST . '/api/order/editOrder');
// 주문 삭제
define('EROUMCARE_API_ORDER_DELETE',                            EROUMCARE_API_HOST . '/api/order/delete');
// 재고 상세 목록
define('EROUMCARE_API_STOCK_SELECT_DETAIL_LIST',                EROUMCARE_API_HOST . '/api/stock/selectDetailList');
// 재고 등록
define('EROUMCARE_API_STOCK_INSERT',                            EROUMCARE_API_HOST . '/api/stock/insert');
// 재고 수정
define('EROUMCARE_API_STOCK_UPDATE',                            EROUMCARE_API_HOST . '/api/stock/update');
// 재고 벌크 삭제
define('EROUMCARE_API_STOCK_DELETE_MULTI',                      EROUMCARE_API_HOST . '/api/stock/deleteMulti');
// 장바구니 정보
define('EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP',           EROUMCARE_API_HOST . '/api/prod/selectPro2000ProdInfoAjaxByShop.do');
// 상품 등록
define('EROUMCARE_API_PROD_INSERT',                             EROUMCARE_API_HOST . '/api/prod/insert');
// 상품 업데이트
define('EROUMCARE_API_PROD_UPDATE',                             EROUMCARE_API_HOST . '/api/prod/update');
// 예비수급자 등록
define('EROUMCARE_API_SPARE_RECIPIENT_INSERT',                  EROUMCARE_API_HOST . '/api/recipient/insertSpare');
// 예비수급자 업데이트
define('EROUMCARE_API_SPARE_RECIPIENT_UPDATE',                  EROUMCARE_API_HOST . '/api/recipient/updateSpare');
// 예비수급자 조회
define('EROUMCARE_API_SPARE_RECIPIENT_SELECTLIST',              EROUMCARE_API_HOST . '/api/recipient/selectSpareList');
// 수급자 등록
define('EROUMCARE_API_RECIPIENT_INSERT',                        EROUMCARE_API_HOST . '/api/recipient/insert');
// 수급자 업데이트
define('EROUMCARE_API_RECIPIENT_UPDATE',                        EROUMCARE_API_HOST . '/api/recipient/update');
// 수급자 조회
define('EROUMCARE_API_RECIPIENT_SELECTLIST',                    EROUMCARE_API_HOST . '/api/recipient/selectList');
// 수급자별 품목 조회
define('EROUMCARE_API_RECIPIENT_SELECT_ITEM_LIST',              EROUMCARE_API_HOST . '/api/recipient/selectItemList');
// 수급자별 욕구사정기록지 조회
define('EROUMCARE_API_RECIPIENT_SELECT_REC_LIST',               EROUMCARE_API_HOST . '/api/recipient/selectRecList');
// 수급자별 욕구사정기록지 작성
define('EROUMCARE_API_RECIPIENT_INSERT_REC',                    EROUMCARE_API_HOST . '/api/recipient/insertRec');
// 수급자별 욕구사정기록지 업데이트
define('EROUMCARE_API_RECIPIENT_UPDATE_REC',                    EROUMCARE_API_HOST . '/api/recipient/updateRec');
// 수급자별 욕구사정기록지 삭제
define('EROUMCARE_API_RECIPIENT_DELETE_REC',                    EROUMCARE_API_HOST . '/api/recipient/deleteRec');
// 계약서 초기값 가져오기 (품목 별 본인부담률 계산한 값들 포함)
define('EROUMCARE_API_EFORM_SELECT_INITIAL_STATE_LIST',         EROUMCARE_API_HOST . '/api/eform/selectEform001');
// 수급자 취급상품 등록
define('EROUMCARE_API_RECIPIENT_ITEM_INSERT',                   EROUMCARE_API_HOST . '/api/recipient/setItem');

define('EROUMCARE_API_ENT_ACCOUNT',                             EROUMCARE_API_HOST . '/api/ent/account');
define('EROUMCARE_API_ENT_INSERT',                              EROUMCARE_API_HOST . '/api/ent/insert');
define('EROUMCARE_API_ENT_UPDATE',                              EROUMCARE_API_HOST . '/api/ent/update');
define('EROUMCARE_API_ENT_UPDATE_USRID',                        EROUMCARE_API_HOST . '/api/ent/updateUsrId');
// 회원조회2
define('EROUMCARE_API_ACCOUNT_ENT_LOGIN',                       EROUMCARE_API_HOST . '/api/account/entLogin');
define('EROUMCARE_API_ACCOUNT_ENT_UPDATE',                      EROUMCARE_API_HOST . '/api/account/entUpdate');

// 재고
define('EROUMCARE_API_STOCK_LIST',                              EROUMCARE_API_HOST . '/api/stock/selectNotEmptyListForEnt');


define('EROUMCARE_API_ENT_INFO',                                EROUMCARE_API_HOST . '/api/account/entInfo');
define('EROUMCARE_API_ORDER_SELECT_LIST',                       EROUMCARE_API_HOST . '/api/order/selectList');
define('EROUMCARE_API_STOCK_SELECT_LIST',                       EROUMCARE_API_HOST . '/api/stock/selectList');
define('EROUMCARE_API_INSERT_PPC',                              EROUMCARE_API_HOST . '/api/prod/insertPpc');
define('EROUMCARE_API_DELETE_PPC',                              EROUMCARE_API_HOST . '/api/prod/deletePpc');
define('EROUMCARE_API_SELECT_PPC_LIST',                         EROUMCARE_API_HOST . '/api/prod/selectPpcList');
define('EROUMCARE_API_STOCK_SELECT_BARNUM_LIST',                EROUMCARE_API_HOST . '/api/stock/selectBarNumList');

// 비즈톡 API 연동
define('BIZTALK_API_HOST', 'https://www.biztalk-api.com');
define('BIZTALK_API_BS_ID', 'thkc1300');
define('BIZTALK_API_BS_PWD', 'd267d7b9d328031338f3bdffc9c1a7345b182ef8');
define('BIZTALK_API_SENDER_KEY', '4034a64c0543fbef8c1eb5647972105343cdd69d'); // 채널 키값 - (아이디) @eroumcare
//define('BIZTALK_API_SENDER_KEY', '34fbabc21279a4883a334bbe8509cc90f0c373a3'); // 채널 키값 - (아이디) @thkc1300

// 카카오 디벨로퍼스 REST API 연동
define('KAKAO_DEVELOPERS_REST_API_KEY', '7a991b6e94ba43c5d266d9aa4a2edca1');

// 로젠택배 EDI 연동 
// dbconfig 파일에 작성하도록 수정
// 실주소 : https://ediweb.ilogen.com/iLOGEN.EDI.WebService/W_PHPServer.asmx?WSDL
// 테스트서버 : http://1.255.199.16/iLOGEN.EDI.WebService/W_PHPServer.asmx?WSDL
// define('G5_EDI_URL',        'http://1.255.199.16/iLOGEN.EDI.WebService/W_PHPServer.asmx?WSDL');
define('G5_EDI_URL',        'https://ediweb.ilogen.com/iLOGEN.EDI.WebService/W_PHPServer.asmx?WSDL');
define('G5_EDI_USERID',     '32551369');
define('G5_EDI_PASSWORD',   '!121200a');

// 인스타그램 API 연동
define('G5_INSTAGRAM_TOKEN', 'IGQVJXQU4xdFVsand1d1g1UGo4OUU3MWw3Q1NGdUh1ZA29LSlNETDF3Xy04RmFfZAzVLQnNoRmFPY3J3ZAHVYa1JUdXd1S1NLdU1FbVVOYTFrYWZA4UDdvTGJrMnlzUzNsTHRWRDg5SFBMcWI0M3ZARNnBPaAZDZD');

//모두싸인 API 연동
define('G5_MDS_KEY',   'NzI2YjU2MzktYzBjZS00OGVlLWIyMzktZTRhMjIwNzg1YWVj');
define('G5_MDS_ID',   'platform@thkc.co.kr');

//이로움 1.5 API 연동 key
define('eroumAPI_Key',   'f9793511dea35edee3181513b640a928644025a66e5bccdac8836cfadb875856');

//이로움 1.5 API 연동 url
if(strpos($_SERVER['HTTP_HOST'],".eroumcare")){
	define('eroumAPI_url',   'https://eroum.icubesystems.co.kr/eroumcareApi/bplcRecv/callback.json');//dev,test,local 일때
}else{
	define('eroumAPI_url',   'https://eroum.co.kr/eroumcareApi/bplcRecv/callback.json');//상용서버일때
}

//이로움 1.5 sso 연동 url
if(strpos($_SERVER['HTTP_HOST'],".eroumcare")){
	define('eroumon_login_url',   'http://192.168.0.229/partners/login');//dev,test,local 일때
}else{
	define('eroumon_login_url',   '');//상용서버일때
}
/*
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {
    $g5_path['url'] = str_replace('http://', 'https://', $g5_path['url']);
}
*/
$httpsonoff = '';
if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){
    $httpsonoff = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://';
}
else{
    $httpsonoff = !empty($_SERVER['HTTPS']) ? "https://" : "http://";
}
$g5_path['url'] = str_replace('http://', $httpsonoff, $g5_path['url']);


// URL 은 브라우저상에서의 경로 (도메인으로 부터의)
if (G5_DOMAIN) {
    define('G5_URL', G5_DOMAIN);
} else {
    if (isset($g5_path['url']))
        define('G5_URL', $g5_path['url']);
    else
        define('G5_URL', '');
}

if (isset($g5_path['path'])) {
    define('G5_PATH', $g5_path['path']);
} else {
    define('G5_PATH', '');
}

$db_config_file = 'dbconfig.' . ltrim($_SERVER['HTTP_HOST'], 'www.') . '.php';

if (file_exists(G5_PATH . '/data/' . $db_config_file)) {
    define('G5_DBCONFIG_FILE',  $db_config_file);
}else{
    define('G5_DBCONFIG_FILE',  'dbconfig.php');
}

//echo G5_DBCONFIG_FILE;
//exit;

define('G5_ADMIN_URL',      G5_URL.'/'.G5_ADMIN_DIR);
define('G5_BBS_URL',        G5_URL.'/'.G5_BBS_DIR);
define('G5_CSS_URL',        G5_URL.'/'.G5_CSS_DIR);
define('G5_DATA_URL',       G5_URL.'/'.G5_DATA_DIR);
define('G5_IMG_URL',        G5_URL.'/'.G5_IMG_DIR);
define('G5_JS_URL',         G5_URL.'/'.G5_JS_DIR);
define('G5_SKIN_URL',       G5_URL.'/'.G5_SKIN_DIR);
define('G5_PLUGIN_URL',     G5_URL.'/'.G5_PLUGIN_DIR);
define('G5_EDITOR_URL',     G5_PLUGIN_URL.'/'.G5_EDITOR_DIR);
define('G5_OKNAME_URL',     G5_PLUGIN_URL.'/'.G5_OKNAME_DIR);
define('G5_KCPCERT_URL',    G5_PLUGIN_URL.'/'.G5_KCPCERT_DIR);
define('G5_LGXPAY_URL',     G5_PLUGIN_URL.'/'.G5_LGXPAY_DIR);
define('G5_SNS_URL',        G5_PLUGIN_URL.'/'.G5_SNS_DIR);
define('G5_SYNDI_URL',      G5_PLUGIN_URL.'/'.G5_SYNDI_DIR);
define('G5_MOBILE_URL',     G5_URL.'/'.G5_MOBILE_DIR);

// PATH 는 서버상에서의 절대경로
define('G5_ADMIN_PATH',     G5_PATH.'/'.G5_ADMIN_DIR);
define('G5_BBS_PATH',       G5_PATH.'/'.G5_BBS_DIR);
define('G5_DATA_PATH',      G5_PATH.'/'.G5_DATA_DIR);
define('G5_EXTEND_PATH',    G5_PATH.'/'.G5_EXTEND_DIR);
define('G5_LIB_PATH',       G5_PATH.'/'.G5_LIB_DIR);
define('G5_PLUGIN_PATH',    G5_PATH.'/'.G5_PLUGIN_DIR);
define('G5_SKIN_PATH',      G5_PATH.'/'.G5_SKIN_DIR);
define('G5_MOBILE_PATH',    G5_PATH.'/'.G5_MOBILE_DIR);
define('G5_SESSION_PATH',   G5_DATA_PATH.'/'.G5_SESSION_DIR);
define('G5_EDITOR_PATH',    G5_PLUGIN_PATH.'/'.G5_EDITOR_DIR);
define('G5_OKNAME_PATH',    G5_PLUGIN_PATH.'/'.G5_OKNAME_DIR);

define('G5_KCPCERT_PATH',   G5_PLUGIN_PATH.'/'.G5_KCPCERT_DIR);
define('G5_LGXPAY_PATH',    G5_PLUGIN_PATH.'/'.G5_LGXPAY_DIR);

define('G5_SNS_PATH',       G5_PLUGIN_PATH.'/'.G5_SNS_DIR);
define('G5_SYNDI_PATH',     G5_PLUGIN_PATH.'/'.G5_SYNDI_DIR);
define('G5_PHPMAILER_PATH', G5_PLUGIN_PATH.'/'.G5_PHPMAILER_DIR);
//==============================================================================


//==============================================================================
// 사용기기 설정
// pc 설정 시 모바일 기기에서도 PC화면 보여짐
// mobile 설정 시 PC에서도 모바일화면 보여짐
// both 설정 시 접속 기기에 따른 화면 보여짐
//------------------------------------------------------------------------------
define('G5_SET_DEVICE', 'both');

define('G5_USE_MOBILE', false); // 모바일 홈페이지를 사용하지 않을 경우 false 로 설정
define('G5_USE_CACHE',  true); // 최신글등에 cache 기능 사용 여부


/********************
    시간 상수
********************/
// 서버의 시간과 실제 사용하는 시간이 틀린 경우 수정하세요.
// 하루는 86400 초입니다. 1시간은 3600초
// 6시간이 빠른 경우 time() + (3600 * 6);
// 6시간이 느린 경우 time() - (3600 * 6);
define('G5_SERVER_TIME',    time());
define('G5_TIME_YMDHIS',    date('Y-m-d H:i:s', G5_SERVER_TIME));
define('G5_TIME_YMD',       substr(G5_TIME_YMDHIS, 0, 10));
define('G5_TIME_HIS',       substr(G5_TIME_YMDHIS, 11, 8));

// 입력값 검사 상수 (숫자를 변경하시면 안됩니다.)
define('G5_ALPHAUPPER',      1); // 영대문자
define('G5_ALPHALOWER',      2); // 영소문자
define('G5_ALPHABETIC',      4); // 영대,소문자
define('G5_NUMERIC',         8); // 숫자
define('G5_HANGUL',         16); // 한글
define('G5_SPACE',          32); // 공백
define('G5_SPECIAL',        64); // 특수문자

// 퍼미션
define('G5_DIR_PERMISSION',  0755); // 디렉토리 생성시 퍼미션
define('G5_FILE_PERMISSION', 0644); // 파일 생성시 퍼미션

// 모바일 인지 결정 $_SERVER['HTTP_USER_AGENT']
define('G5_MOBILE_AGENT',   'phone|samsung|lgtel|mobile|[^A]skt|nokia|blackberry|BB10|android|sony');

// SMTP
// lib/mailer.lib.php 에서 사용
//define('G5_SMTP',      'smtp.daum.net');
define('G5_SMTP',      'smtp.naver.com');
define('G5_SMTP_PORT', '465');

//define('G5_SMTP_USERNAME', 'thkc1301'); // daum mail	
//define('G5_SMTP_PASSWORD', 'thdeckc01@!'); // daum pw
define('G5_SMTP_USERNAME', 'thkc1300');
define('G5_SMTP_PASSWORD', '6thgkfossmkc!');

define('G5_SMTP_SSL', true);


/********************
    기타 상수
********************/

// 암호화 함수 지정
// 사이트 운영 중 설정을 변경하면 로그인이 안되는 등의 문제가 발생합니다.
define('G5_STRING_ENCRYPT_FUNCTION', 'sql_password');

// SQL 에러를 표시할 것인지 지정
// 에러를 표시하려면 TRUE 로 변경
define('G5_DISPLAY_SQL_ERROR', FALSE);

// escape string 처리 함수 지정
// addslashes 로 변경 가능
define('G5_ESCAPE_FUNCTION', 'sql_escape_string');

// sql_escape_string 함수에서 사용될 패턴
//define('G5_ESCAPE_PATTERN',  '/(and|or).*(union|select|insert|update|delete|from|where|limit|create|drop).*/i');
//define('G5_ESCAPE_REPLACE',  '');

// 게시판에서 링크의 기본개수를 말합니다.
// 필드를 추가하면 이 숫자를 필드수에 맞게 늘려주십시오.
define('G5_LINK_COUNT', 2);

// 썸네일 jpg Quality 설정
define('G5_THUMB_JPG_QUALITY', 90);

// 썸네일 png Compress 설정
define('G5_THUMB_PNG_COMPRESS', 5);

// 모바일 기기에서 DHTML 에디터 사용여부를 설정합니다.
define('G5_IS_MOBILE_DHTML_USE', false);

// MySQLi 사용여부를 설정합니다.
define('G5_MYSQLI_USE', true);

// Browscap 사용여부를 설정합니다.
define('G5_BROWSCAP_USE', true);

// 접속자 기록 때 Browscap 사용여부를 설정합니다.
define('G5_VISIT_BROWSCAP_USE', false);

// ip 숨김방법 설정
/* 123.456.789.012 ip의 숨김 방법을 변경하는 방법은
\\1 은 123, \\2는 456, \\3은 789, \\4는 012에 각각 대응되므로
표시되는 부분은 \\1 과 같이 사용하시면 되고 숨길 부분은 ♡등의
다른 문자를 적어주시면 됩니다.
*/
define('G5_IP_DISPLAY', '\\1.♡.\\3.\\4');

if((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') OR $httpsonoff == 'https://' ) {   //https 통신일때 daum 주소 js
    //define('G5_POSTCODE_JS', '<script src="https://spi.maps.daum.net/imap/map_js_init/postcode.v2.js"></script>');	
	define('G5_POSTCODE_JS', '<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>');
} else {  //http 통신일때 daum 주소 js
    define('G5_POSTCODE_JS', '<script src="http://dmaps.daum.net/map_js_init/postcode.v2.js"></script>');
}
?>
