<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/* 외부이미지 저장 */
function FileSave($FileLink, $dir){

    if(!is_dir($dir)) {
        @mkdir($dir, G5_DIR_PERMISSION);
        @chmod($dir, G5_DIR_PERMISSION);
    }

    $PhotoInfo = pathinfo($FileLink);
    $PhotoName[] = md5($PhotoInfo['filename'])."_".time();
    $PhotoName[] = $PhotoInfo['extension'];
    $PhotoName = implode(".", $PhotoName);
    $Curl = curl_init();
    curl_setopt($Curl, CURLOPT_URL, $FileLink);
    curl_setopt($Curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($Curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($Curl, CURLOPT_SSL_VERIFYHOST, 0);
    $Result = curl_exec($Curl);
    $FileSave = fopen($dir.'/'.$PhotoName, 'a');
    fwrite($FileSave, $Result);
    fclose($FileSave);

	$file = str_replace(G5_DATA_PATH.'/item/', '', $dir.'/'.$PhotoName);
	return $file;
}

/* 필수값정의 */
$item_required_fild = Array(
	'prodId'=>'제품ID',
	'gubun'=>'구분 ("00")',
	'prodNm'=>'제품명',
	'itemId'=>'품목ID',
	'prodSupPrice'=>'공급가격',
	'prodOflPrice'=>'판매금액',
	'ProdPayCode'=>'급여코드',
	'prodDetail'=>'상세정보',
	'regDtm'=>'최초등록일시(YMDHIS)',
	'regUsrIp'=>'최초등록자 IP (IPV6 포함 총 39자리)'
);
/*
$item_required_fild = Array(
	'prodId'=>'제품ID',
	'gubun'=>'구분 ("00")',
	'prodNm'=>'제품명',
	'itemId'=>'품목ID',
	'subItem'=>'하위품목',
	'prodSupPrice'=>'공급가격',
	'prodOflPrice'=>'판매금액',
	'ProdPayCode'=>'급여코드',
	'supId'=>'공급업체 아이디',
	'prodColor'=>'색상 (ex “빨강|파랑|노랑” )',
	'prodSym'=>'재질',
	'prodWeig'=>'중량',
	'prodSize'=>'사이즈',
	'prodQty'=>'주문가능수량',
	'prodDetail'=>'상세정보',
	'regDtm'=>'최초등록일시(YMDHIS)',
	'regUsrId'=>'최초등록자 ID',
	'regUsrIp'=>'최초등록자 IP (IPV6 포함 총 39자리)',
	'supNm'=>'공급업체 이름',
	'prodImgAttr'=>'[이미지 첨부파일 이름들]'
);
*/
?>