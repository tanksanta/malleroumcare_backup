<?php
include_once('./_common.php');

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
// 22.11.07 : 서원
// 발주서 다운로드 파일생성 페이지 (PDF기본 추후 필요시 해당페이지에 엑셀파일 기능 추가.)
// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 

$type = clean_xss_tags( trim($_GET['type']) );
$od_id = clean_xss_tags( trim($_GET['od_id']) );

$sql = " select od_id from purchase_order where od_id = '$od_id' ";
$od = sql_fetch($sql);

if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

if( $type === "pdf" ) {
	header("Content-type: application/pdf");

	$url = G5_URL."/shop/pop.purchase_estimate.php?od_id=".$od_id;

	// PDF 파일 생성
	$pdfdir = G5_DATA_PATH."/PurchaseOdrer/".date("Y")."/".date("m");
	if(!is_dir($pdfdir)) {
		@mkdir($pdfdir, G5_DIR_PERMISSION, true);
		@chmod($pdfdir, G5_DIR_PERMISSION);
	}

	$mb_id = $member['mb_id'];
	$manager_mb_id = get_session('ss_manager_mb_id');
	if($manager_mb_id) { $mb_id = $manager_mb_id; }
	
	$pdffile = "PurchaseOrder_".$od_id."_down_".date("ymdHis")."_".$mb_id.".pdf";
	$pdfdir .= "/".$pdffile;

    // 서버 내 wkhtmltopdf 파일 경로 :  /usr/local/bin
    // 저장
    // @exec('C:/_THKC/_Dev/wkhtmltox/bin/wkhtmltopdf "'.$url.'" "'.$pdfdir.'" 2>&1');
    @exec('/usr/local/bin/wkhtmltopdf "'.$url.'" "'.$pdfdir.'" 2>&1');
    @exec('wkhtmltopdf "'.$url.'" "'.$pdfdir.'" 2>&1');

	header("Content-Disposition: attachment; filename=\"{$pdffile}\"");
	@readfile( $pdfdir );

	set_purchase_order_admin_log($od_id, "발주서 - 다운로드({$pdffile})");
}
else if( $type === "excel" ) {
	alert("현재 Excel파일 다운로드는 지원하지 않습니다.");
}
else {
	alert("파일 다운로드가 불가능 합니다.");
}

?>