<?php
include_once("./_common.php");
include_once(G5_LIB_PATH.'/icode.lms.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

if(!$is_member) {
  json_response(400, '먼저 로그인하세요.');
}

$uuid = $_POST['uuid'];
$state = json_decode(stripslashes($_POST['state']), true);
if(!$uuid || !$state) {
  json_response(400, '잘못된 요청입니다.');
}

$eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$uuid')");
if(!$eform['dc_id']) {
  json_response(500, '서명할 계약서를 찾을 수 없습니다.');
}

if($eform['dc_status'] == '11') {
  $is_simple_efrom = true;
} else {
  $is_simple_efrom = false;

  $sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE `od_id` = '{$eform['od_id']}'";
  if($is_member && !$is_admin)
      $sql .= " AND mb_id = '{$member['mb_id']}' ";
  $od = sql_fetch($sql);
  if(!$od['mb_id']) {
    json_response(400, '계약서에 서명할 권한이 없습니다.');
  }

  if($eform['dc_status'] == '2') {
    json_response(400, '이미 서명이 완료된 계약서입니다.');
  }

  if($eform['dc_status'] != '1') {
    json_response(400, '계약서가 서명할 수 없는 상태입니다.');
  }
}

// 서명 파일 사본 저장할 경로
$signdir = G5_DATA_PATH.'/eform/sign';
if(!is_dir($signdir)) {
  @mkdir($signdir, G5_DIR_PERMISSION, true);
  @chmod($signdir, G5_DIR_PERMISSION);
}

foreach($state as $id => $val) {
  $key = explode('_', $id);

  // 서명일 경우 서명 이미지 저장
  if($key[0] === 'sign') {
    $encoded_image = explode(",", $val)[1];
    $decoded_image = base64_decode($encoded_image);

    $filename = $uuid."_".$eform['penId']."_".$id."_".date("YmdHisw").".png";
    file_put_contents("$signdir/$filename", $decoded_image);

    $val = "/data/eform/sign/{$filename}";
  }

  sql_query("INSERT INTO `eform_document_content` SET
  `dc_id` = UNHEX('$uuid'),
  `ct_id` = '$id',
  `ct_content` = '$val'
  ");
}

$ip = $_SERVER['REMOTE_ADDR'];
$browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$timestamp = time();
$datetime = date('Y-m-d H:i:s', $timestamp);

// 계약서 로그 작성
$log = '전자계약서에 서명했습니다.';

sql_query("INSERT INTO `eform_document_log` SET
`dc_id` = UNHEX('$uuid'),
`dl_log` = '$log',
`dl_ip` = '$ip',
`dl_browser` = '$browser',
`dl_datetime` = '$datetime'
");

// PDF 파일 생성
$pdfdir = G5_DATA_PATH.'/eform/pdf';
if(!is_dir($pdfdir)) {
  @mkdir($pdfdir, G5_DIR_PERMISSION, true);
  @chmod($pdfdir, G5_DIR_PERMISSION);
}
$pdffile = $uuid.'_'.$eform['penId'].'_'.$eform['entId'].'_'.date("YmdHisw").'.pdf';
$pdfdir .= '/'.$pdffile;
include_once('./lib/renderpdf.lib.php');

// 감사 추적 인증서 PDF 파일 생성
$certdir = G5_DATA_PATH.'/eform/cert';
if(!is_dir($certdir)) {
  @mkdir($certdir, G5_DIR_PERMISSION, true);
  @chmod($certdir, G5_DIR_PERMISSION);
}
$certfile = $uuid.'_'.$eform['penId'].'_'.$eform['entId'].'_cert_'.date("YmdHisw").'.pdf';
$certdir .= '/'.$certfile;
include_once('./lib/rendercertpdf.lib.php');

// 문자 발송
$send_hp = '02-830-1301';
$recv_hp = $eform['penConNum'];

$send_hp = str_replace('-', '', $send_hp);
$recv_hp = str_replace('-', '', $recv_hp);

$link = G5_SHOP_URL.'/eform/eformInquiry.php?id='.$uuid;
$msg = "[이로움]\n{$eform['penNm']}님 '".mb_substr($eform['entNm'], 0, 8, 'utf-8')."' 사업소와 전자계약이 체결되었습니다.\n\n* 문서확인 : {$link}";

$dc_send_sms = 'FALSE';
$port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);
if($port_setting !== false && $recv_hp) {
    $SMS = new LMS;
    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

    $strDest     = array();
    $strDest[]   = $recv_hp;
    $strCallBack = $send_hp;
    $strCaller   = iconv_euckr('이로움');
    $strSubject  = iconv_euckr('[이로움] 계약체결완료');
    $strURL      = '';
    $strData     = iconv_euckr($msg);
    $strDate     = '';
    $nCount      = count($strDest);

    $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

    $SMS->Send();

    $dc_send_sms = 'TRUE';
}

// 메일 발송
// 기초 수급자 체크
$is_gicho = $eform['penTypeCd'] == '04';

$file = [
  array('path' => $pdfdir, 'name' => "{$eform['dc_subject']}.pdf"),
  array('path' => $certdir, 'name' => "감사추적인증서_{$eform['dc_subject']}.pdf")
];

ob_start();
include_once ('./mail.eform.sign.php');
$content = ob_get_contents();
ob_end_clean();

mailer('이로움', 'no-reply@eroumcare.com', $eform['entMail'], "[이로움] {$eform['penNm']}님 {$eform['entNm']}사업소와 전자계약이 체결되었습니다.", $content, 1, $file);

$ent = sql_fetch(" SELECT * FROM g5_member WHERE mb_entId = '{$eform['entId']}' ");

// 알림톡 발송
$dc_id_b64 = base64_encode($eform['dc_id']);
$dc_id_b64 = str_replace(['+', '/', '='], ['-', '_', ''], $dc_id_b64);
send_alim_talk('PEN_EF_'.$dc_id_b64, $eform['penConNum'], 'pen_eform_result', "[이로움]\n\n{$eform['penNm']}님,\n{$eform['entNm']} 사업소와 전자계약이 체결되었습니다.", array(
  'button' => [
    array(
      'name' => '문서확인',
      'type' => 'WL',
      'url_mobile' => 'https://eroumcare.com/shop/eform/eformInquiry.php?id='.$uuid
    )
  ]
));
send_alim_talk('ENT_EFORM_'.$uuid, $ent['mb_hp'], 'ent_eform_result', "[이로움]\n\n{$eform['penNm']}님과 전자계약이 체결되었습니다.");

$dc_status = '2';
if($is_simple_efrom) {
  $dc_status = '3';

  // 간편 계약서 작성 시 바코드 입력한 상품 '재고소진' 상태로 재고 등록
  $sql = "
    SELECT
      i.*,
      x.it_id as id
    FROM
      eform_document_item i
    LEFT JOIN
      g5_shop_item x ON x.it_id = (
        select it_id
        from g5_shop_item
        where
          ProdPayCode = i.it_code and
          (
            ( i.gubun = '00' and ca_id like '10%' ) or
            ( i.gubun = '01' and ca_id like '20%' )
          )
        limit 1
      )
    WHERE
      dc_id = UNHEX('$uuid')
    ORDER BY
      i.it_id ASC
  ";
  $result = sql_query($sql);

  $stock_insert = [];
  $stock_update = [];
  $rental_data_table = [];
  while($row = sql_fetch_array($result)) {
    if(strlen($row['it_barcode']) == 12) { // 바코드 12자리 정상적으로 입력한 경우
      if($row['gubun'] == '00') {
        // 판매

        // 재고에 있는지 조회
        $stock_result = get_stock($row['id'], $row['it_barcode']);
        if($stock_result) {
          // 재고에 있으면
          $stock = $stock_result[0];
          // 재고가 판매완료 상태가 아니면 판매완료로 업데이트
          $stock['stateCd'] != '02';
          $stock_update[] = array(
            'stoId' => $stock['stoId'],
            'prodBarNum' => $row['it_barcode'],
            'stateCd' => '02'
          );
        } else {
          // 재고에 없으면
          // 보유재고로 판매완료로 등록
          $stock_insert[] = array(
            'prodId' => $row['id'],
            'prodBarNum' => $row['it_barcode'],
            'stateCd' => '02'
          );
        }
      } else {
        // 대여

        $str_date = substr($row['it_date'], 0, 10);
        $end_date = substr($row['it_date'], 11, 10);

        // rental_data_table에 입력해둠 (나중에 재고 업데이트/등록 후 대여로그 작성하기 위해)
        $rental_data_table["{$row['id']}-{$row['it_barcode']}"] = array(
          'strdate' => $str_date,
          'enddate' => $end_date
        );

        // 재고에 있는지 조회
        $stock_result = get_stock($row['id'], $row['it_barcode']);
        if($stock_result) {
          // 재고가 있으면
          $stock = $stock_result[0];
          // 재고 대여완료로 업데이트
          $stock_update[] = array(
            'stoId' => $stock['stoId'],
            'prodId' => $row['id'],
            'prodBarNum' => $row['it_barcode'],
            'stateCd' => '02'
          );
        } else {
          // 재고에 없으면
          // 보유재고에 등록
          $stock_insert[] = array(
            'prodId' => $row['id'],
            'prodBarNum' => $row['it_barcode'],
            'stateCd' => '02',
            'initialContractDate' => date('Y-m-d H:i:s', strtotime($str_date))
          );
        }
      }
    }
  }

  // 재고 insert
  if($stock_insert) {
    $insert_result = api_post_call(EROUMCARE_API_STOCK_INSERT, array(
      'usrId' => $member["mb_id"],
      'entId' => $member["mb_entId"],
      'prods' => $stock_insert
    ));

    // 대여로그 작성
    foreach($insert_result['data'] as $row) {
      $rental_data = $rental_data_table["{$row['prodId']}-{$row['prodBarNum']}"];
      if(!$rental_data) continue;

      $rental_log_id = "rental_log".round(microtime(true)).rand();
      $dis_total_date = G5_TIME_YMDHIS;

      sql_query("
        INSERT INTO
          g5_rental_log
        SET
          rental_log_Id = '{$rental_log_id}',
          stoId = '{$row['stoId']}',
          ordId = '',
          strdate = '{$rental_data['strdate']}',
          enddate = '{$rental_data['enddate']}',
          dis_total_date = '{$dis_total_date}',
          ren_person = '{$eform['penNm']}',
          rental_log_division = '2'
      ");
    }
  }

  // 재고 update
  if($stock_update) {
    $update_result = api_post_call(EROUMCARE_API_STOCK_UPDATE, array(
      'usrId' => $member["mb_id"],
      'entId' => $member["mb_entId"],
      'prods' => $stock_update
    ));

    // 대여로그 작성
    foreach($stock_update as $row) {
      $rental_data = $rental_data_table["{$row['prodId']}-{$row['prodBarNum']}"];
      if(!$rental_data) continue;

      // 이미 같은 기간동안의 대여로그가 작성되어있는지 검색
      $check_result = sql_fetch("
        SELECT
          rental_log_Id
        FROM
          g5_rental_log
        WHERE
          stoId = '{$row['stoId']}' and
          strdate = '{$rental_data['strdate']}' and
          enddate = '{$rental_data['enddate']}' and
          rental_log_division = '2'
      ");

      // 이미 작성된 로그면 건너뜀
      if($check_result['rental_log_Id']) continue;

      $rental_log_id = "rental_log".round(microtime(true)).rand();
      $dis_total_date = G5_TIME_YMDHIS;

      sql_query("
        INSERT INTO
          g5_rental_log
        SET
          rental_log_Id = '{$rental_log_id}',
          stoId = '{$row['stoId']}',
          ordId = '',
          strdate = '{$rental_data['strdate']}',
          enddate = '{$rental_data['enddate']}',
          dis_total_date = '{$dis_total_date}',
          ren_person = '{$eform['penNm']}',
          rental_log_division = '2'
      ");
    }
  }
}

// 계약서 정보 업데이트
sql_query("UPDATE `eform_document` SET
`dc_status` = '$dc_status',
`dc_sign_datetime` = '$datetime',
`dc_sign_ip` = '$ip',
`dc_pdf_file` = '$pdffile',
`dc_cert_pdf_file` = '$certfile',
`dc_send_sms` = $dc_send_sms,
`dc_send_email` = TRUE
WHERE `dc_id` = UNHEX('$uuid')
");

json_response(200, 'OK');
?>
