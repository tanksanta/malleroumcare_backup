<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default') 
  json_response(400, '사업소 회원만 이용하실 수 있습니다.');

// 승인여부
$is_approved = false;
$res = api_post_call(EROUMCARE_API_ENT_ACCOUNT, array(
  'usrId' => $member['mb_id']
));
if($res['data']['entConfirmCd'] == '01' || $member['mb_level'] >= 5 ) {
  $is_approved = true;
}

if(!$is_approved)
  json_response(400, '승인된 회원만 이용하실 수 있습니다.');

$dc_id = get_search_string($_POST['dc_id']);

if(!$dc_id)
    json_response(400, '유효하지 않은 요청입니다.');

$sql = " select * from eform_document where dc_id = unhex('$dc_id') and entId = '{$member['mb_entId']}' and dc_status in ('2', '3') ";
$dc = sql_fetch($sql);

if(!$dc)
    json_response(400, '계약서가 존재하지 않습니다.');

// 메일 발송
if($dc['penMail']) {
    $file = [];

    if($dc['dc_status'] == 3 && $dc['od_id']) {
        // 마이그레이션해온 이전 계약서
        $pdfdir = G5_DATA_PATH.'/eform/legacy';
        $pdffile .= '/ALL.pdf';
        $pdfdir .= '/'.$pdffile;
    
        $file[] = array('path' => $pdfdir, 'name' => "{$dc['dc_subject']}.pdf");
    } else {
        $pdfdir = G5_DATA_PATH.'/eform/pdf';
        $pdffile = $eform['dc_pdf_file'];
        $pdfdir .= '/'.$pdffile;
        $certdir = G5_DATA_PATH.'/eform/cert';
        $certfile = $eform['dc_cert_pdf_file'];
        $certdir .= '/'.$certfile;
    
        $file[] = array('path' => $pdfdir, 'name' => "{$dc['dc_subject']}.pdf");
        $file[] = array('path' => $certdir, 'name' => "감사추적인증서_{$dc['dc_subject']}.pdf");
    }

    ob_start();
    include_once ('./mail.eform.sign.php');
    $content = ob_get_contents();
    ob_end_clean();
    mailer('이로움', 'no-reply@eroumcare.com', $dc['penMail'], "[이로움] {$dc['penNm']}님 {$dc['entNm']}사업소와 전자계약이 체결되었습니다.", $content, 1, $file);
}

// 알림톡 발송
$dc_id_b64 = base64_encode($dc['dc_id']);
$dc_id_b64 = str_replace(['+', '/', '='], ['-', '_', ''], $dc_id_b64);
send_alim_talk('PEN_EF_'.$dc_id_b64, $dc['penConNum'], 'pen_eform_result', "[이로움]\n\n{$dc['penNm']}님,\n{$dc['entNm']} 사업소와 전자계약이 체결되었습니다.", array(
  'button' => [
    array(
      'name' => '문서확인',
      'type' => 'WL',
      'url_mobile' => 'https://eroumcare.com/shop/eform/eformInquiry.php?id='.$dc_id
    )
  ]
));

json_response(200, 'OK');
