<?php
include_once("./_common.php");
include_once('./lib/eform.lib.php');

json_response(400, '접근 ㄴㄴ');

$data = api_post_call('https://system.eroumcare.com/api/eform/selectEform001', array('penOrdId' => 'ORDER-20210604000013'));
echo '<pre>';
var_dump($data);
echo '</pre>';

/*function dtmToYmd($dtm) {
  return substr($dtm, 0, 4).'-'.substr($dtm, 4, 2).'-'.substr($dtm, 6, 2);
}

$result = sql_query("select * from g5_shop_order where recipient_yn = 'Y' and od_del_yn = 'N' and ordId is not null");
while($od = sql_fetch_array($result)) {
  try {
    $data_arr = api_post_call('https://eroumcare.com/api/order/selectList', array(
      'uuid' => $od['uuid'],
      'penOrdId' => $od['ordId']
    ));
    $eformUrl = str_replace('/&gubn=ALL', '', $data_arr[0]['eformUrl']);
    $pdf_file = explode('/share/eform/eformALL/', $eformUrl)[1];

    if(!$data_arr['data']) {
      echo $od['ordId'].' : ';
      echo $od['mb_id']. ' : ';
      echo var_dump($data_arr);
      echo '<br>';
      continue;
    }

    $entData = sql_fetch("SELECT `mb_entId`, `mb_entNm`, `mb_email`, `mb_giup_boss_name`, `mb_giup_bnum`, `mb_entConAcc01`, `mb_entConAcc02` FROM `g5_member` WHERE mb_id = '{$od["mb_id"]}'");
    $penData = api_post_call('https://system.eroumcare.com/api/recipient/selectList', array(
      'usrId' => $od["mb_id"],
      'entId' => $entData["mb_entId"],
      'pageNum' => 1,
      'pageSize' => 1,
      'penId' => $od["od_penId"]
    ))['data'][0];

    $od_time = $od['od_time'];
    $eformUrl = str_replace('/&gubn=ALL', '', $data_arr['data'][0]['eformUrl']);
    $pdf_file = explode('/share/eform/eformALL/', $eformUrl)[1];

    // 문서 제목 생성
    $subject = $entData["mb_entNm"]."_".str_replace('-', '', $entData["mb_giup_bnum"])."_".$penData["penNm"].substr($penData["penLtmNum"], 0, 6)."_".date("Ymd", strtotime($od_time))."_";
    $subject_count_postfix = sql_fetch("SELECT COUNT(`dc_id`) as cnt FROM `eform_document` WHERE `dc_subject` LIKE '{$subject}%'")["cnt"];
    $subject_count_postfix = str_pad($subject_count_postfix + 1, 3, '0', STR_PAD_LEFT); // zerofill
    $subject .= $subject_count_postfix;

    //sql_query("UPDATE `eform_document` SET dc_subject = '$subject' WHERE od_id = '{$od["od_id"]}'");

    /*$dc_id = sql_fetch("SELECT REPLACE(UUID(),'-','') as uuid")["uuid"];
    
    sql_query("INSERT INTO `eform_document` SET
    `dc_id` = UNHEX('$dc_id'),
    `dc_subject` = '$subject',
    `dc_status` = '3',
    `od_id` = '{$od["od_id"]}',
    `entId` = '{$entData["mb_entId"]}',
    `entNm` = '{$entData["mb_entNm"]}',
    `entCrn` = '{$entData["mb_giup_bnum"]}',
    `entMail` = '{$entData["mb_email"]}',
    `entCeoNm` = '{$entData["mb_giup_boss_name"]}',
    `entConAcc01` = '{$entData["mb_entConAcc01"]}',
    `entConAcc02` = '{$entData["mb_entConAcc02"]}',
    `penId` = '{$penData["penId"]}',
    `penNm` = '{$penData["penNm"]}',
    `penConNum` = '{$penData["penConNum"]}', # 휴대전화번호인데 전화번호랑 둘중에 어떤거 입력해야될지?
    `penBirth` = '{$penData["penBirth"]}',
    `penLtmNum` = '{$penData["penLtmNum"]}',
    `penRecGraCd` = '{$penData["penRecGraCd"]}', # 장기요양등급
    `penRecGraNm` = '{$penData["penRecGraNm"]}',
    `penTypeCd` = '{$penData["penTypeCd"]}', # 본인부담금율
    `penTypeNm` = '{$penData["penTypeNm"]}',
    `penExpiDtm` = '{$penData["penExpiDtm"]}', # 수급자 이용기간
    `penJumin` = '{$penData["penJumin"]}',
    `penZip` = '{$penData["penZip"]}',
    `penAddr` = '{$penData["penAddr"]}',
    `penAddrDtl` = '{$penData["penAddrDtl"]}',
    `dc_ip` = '{$od["od_ip"]}',
    `dc_sign_ip` = '{$od["od_ip"]}',
    `dc_datetime` = '$od_time',
    `dc_sign_datetime` = '$od_time',
    `dc_pdf_file` = '$pdf_file'
    ");

    foreach($data_arr['data'] as $it) {
      $gubun = $it['ordStatus'];
      $it_date = $gubun == '00' ? date('Y-m-d', strtotime($od_time)) : dtmToYmd($it["ordLendStrDtm"]).'-'.dtmToYmd($it["ordLendEndDtm"]);
      sql_query("INSERT INTO `eform_document_item` SET
      `dc_id` = UNHEX('$dc_id'),
      `gubun` = '{$gubun}',
      `ca_name` = '{$it["itemNm"]}',
      `it_name` = '{$it["prodNm"]}',
      `it_code` = '{$it["prodPayCode"]}',
      `it_barcode` = '{$it["prodBarNum"]}',
      `it_qty` = '1',
      `it_date` = '{$it_date}',
      `it_price` = '{$it["prodOflPrice"]}'
      ");
    }*/
/*  } catch(Exception $e) {
    echo $od['ordId'].' 를 처리 하는 도중 오류 발생<br>';
  }
}*/

/*
$data_arr = api_post_call('https://eroumcare.com/api/order/selectList', array(
  'uuid' => '3131c972-b14a-4213-bf74-382cacaf685e',
  'penOrdId' => 'ORDER-20210526000004'
))['data'];

foreach($data_arr as $it) {
  var_dump($it['ordStatus']);
}
*/

/*
$data = api_post_call('https://eroumcare.com/api/order/selectList', array(
  'uuid' => '3131c972-b14a-4213-bf74-382cacaf685e',
  'penOrdId' => 'ORDER-20210526000004'
));

$eformUrl = str_replace('/&gubn=ALL', '', $data['data'][0]['eformUrl']);
$pdf_file = explode('/share/eform/eformALL/', $eformUrl)[1];

echo $pdf_file;

echo '<pre>';
var_dump($data);
echo '</pre>';
*/

echo '완료';
?>