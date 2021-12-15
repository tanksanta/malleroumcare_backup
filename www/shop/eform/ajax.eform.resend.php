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



//급여비용 명세서 엑셀 
function getExcelFile($member, $dc_id) {
  include_once(G5_LIB_PATH.'/PHPExcel.php');

  $reader = PHPExcel_IOFactory::createReader('Excel2007');
  $excel = $reader->load('receipt_form.xlsx');
  
  # 사업소 정보
  $excel->getActiveSheet()->setCellValue('C5', $member['mb_ent_num']);
  $excel->getActiveSheet()->setCellValue('C6', sprintf(
    "%s-%s\n%s %s %s",
    $member['mb_giup_zip1'],
    $member['mb_giup_zip2'],
    $member['mb_giup_addr1'],
    $member['mb_giup_addr2'],
    $member['mb_giup_addr3']
  ));
  $excel->getActiveSheet()->setCellValue('M5', $member['mb_entNm']);
  $excel->getActiveSheet()->setCellValue('M6', $member['mb_giup_bnum']);
  if($member['mb_account']) {
    $excel->getActiveSheet()->setCellValue('A28', "입금계좌 : {$member['mb_account']}");
  }
  
  # 직인
  $excel->getActiveSheet()->setCellValue('L28', date('Y년 m월 d일'));
  $excel->getActiveSheet()->setCellValue('L29', "대표자명 : {$member['mb_giup_boss_name']}");
  $excel->getActiveSheet()->setCellValue('A29', "장기요양기관명 : {$member['mb_entNm']}");
  if($member['sealFile']) {
    try {
      $seal_path = G5_DATA_PATH."/file/member/stamp/{$member['sealFile']}";
      $seal = new PHPExcel_Worksheet_Drawing();
      $seal->setName('직인');
      $seal->setDescription('직인 이미지');
      $seal->setPath($seal_path);
      $seal->setCoordinates('S29');
      $seal->setOffsetX(20);
      $seal->setOffsetY(5);
      $seal->setHeight(100);
      $seal->setResizeProportional(true);
      $seal->setWorksheet($excel->getActiveSheet());
    } catch(Exception $e) {
      // do nothing
    }
  }
  
  if($dc_id) {
    # 수급자 정보
    $pen = sql_fetch("
      SELECT
        penNm,
        penLtmNum
      FROM
        eform_document
      WHERE
        dc_id = unhex('{$dc_id}')
    ");
  
    if(!$pen['penNm'])
      alert('해당 주문이 존재하지 않습니다.');
    
    $excel->getActiveSheet()->setCellValue('A8', $pen['penNm']);
    $excel->getActiveSheet()->setCellValue('C8', $pen['penLtmNum']);
  
    # 계약서 품목
    $sql_document = "
      SELECT
        gubun,
        ca_name,
        it_name,
        it_price,
        it_price_pen,
        it_price_ent,
        it_date
      FROM
        eform_document d
      LEFT JOIN
        eform_document_item i ON d.dc_id = i.dc_id
      WHERE
        d.dc_id = unhex('{$dc_id}')
    ";
  
    $sql_result = sql_query("
      {$sql_document}
      ORDER BY
        gubun asc,
        it_date asc,
        ca_name asc,
        it_name asc
    ");
  
    $rent_txt = "";
    $rent_count = 0;
    $rent_date = '';
    $total_price_rent = 0;
    $buy_txt = "";
    $buy_count = 0;
    $buy_date = '';
    $total_price_buy = 0;
    $total_price_pen = 0;
    $total_price_ent = 0;
    $total_price = 0;
    while($row = sql_fetch_array($sql_result)) {
      if($row['gubun'] == '01') {
        $gubun = '대여';
        $rent_count++;
        $total_price_rent += $row['it_price'];
        if(!$rent_txt) {
          $rent_txt = "대여품목 : {$row['ca_name']} {$row['it_name']}";
        }
        $rent_str_date = substr($rent_date, 0, 10);
        $rent_end_date = substr($rent_date, 11, 10);
        $this_str_date = substr($row['it_date'], 0, 10);
        $this_end_date = substr($row['it_date'], 11, 10);
        if(!$rent_date || strtotime($rent_end_date) - strtotime($rent_str_date) < strtotime($this_end_date) - strtotime($this_str_date)) {
          $rent_date = $row['it_date'];
        }
      } else {
        $gubun = '구입';
        $buy_count++;
        $total_price_buy += $row['it_price'];
        if(!$buy_txt) {
          $buy_txt = "판매품목 : {$row['ca_name']} {$row['it_name']}";
        }
        if(!$buy_date || strtotime($row['it_date']) < strtotime($buy_date)) {
          $buy_date = $row['it_date'];
        }
      }
  
      $total_price_pen += $row['it_price_pen'];
      $total_price_ent += $row['it_price_ent'];
      $total_price += $row['it_price'];
    }
  
    if($rent_count > 1) {
      $rent_txt .= ' 외 ' . ($rent_count - 1) . '건';
    }
    if($rent_txt) {
      $rent_txt .= "\n대여금액 : " . number_format($total_price_rent) . '원';
    }
    if($buy_count > 1) {
      $buy_txt .= ' 외 ' . ($buy_count - 1) . '건';
    }
    if($buy_txt) {
      $buy_txt .= "\n판매금액 : " . number_format($total_price_buy) . '원';
    }
  
    if($rent_date) {
      $rent_str_date = substr($rent_date, 0, 10);
      $rent_end_date = substr($rent_date, 11, 10);
      $excel->getActiveSheet()->setCellValue('G8', $rent_str_date . ' ~ ' .$rent_end_date);
    } else {
      $excel->getActiveSheet()->setCellValue('G8', $buy_date);
    }
  
    $excel->getActiveSheet()->setCellValue('G10', $total_price_pen);
    $excel->getActiveSheet()->setCellValue('G11', $total_price_ent);
    $excel->getActiveSheet()->setCellValue('G13', $total_price);
    $excel->getActiveSheet()->setCellValue('M10', $total_price);
    $excel->getActiveSheet()->setCellValue('M12', $total_price_pen);
    $excel->getActiveSheet()->setCellValue('M14', 0);
    $excel->getActiveSheet()->setCellValue('J24', ($rent_txt ? ($rent_txt . "\n") : '') . $buy_txt);
  }
  
  $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
  ob_start();
  $writer->save('php://output');
  $excelOutput = ob_get_clean();
  return $excelOutput;
}

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
        $file[] = array('path' => $receipt_excel, 'name' => "급여제공명세서_{$eform['dc_subject']}.xlsx", 'filetype'=>"base64");
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
$alimtalk_result = send_alim_talk('PEN_EF_'.$dc_id_b64, $dc['penConNum'], 'pen_eform_result', "[이로움]\n\n{$dc['penNm']}님,\n{$dc['entNm']} 사업소와 전자계약이 체결되었습니다.", array(
  'button' => [
    array(
      'name' => '문서확인',
      'type' => 'WL',
      'url_mobile' => 'https://eroumcare.com/shop/eform/eformInquiry.php?id='.$dc_id
    )
  ]
));

//수급자 알림톡 전송 결과
if ($alimtalk_result['responseCode'] == "1000") {
  //재전송 성공
  sql_query("UPDATE `eform_document` SET
  `dc_send_kakao` = '2'
  WHERE `dc_id` = UNHEX('$dc_id')
  ");
}


json_response(200, 'OK');
