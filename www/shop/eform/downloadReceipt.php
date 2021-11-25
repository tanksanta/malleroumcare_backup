<?php
include_once('./_common.php');

if(!$member['mb_entNm'])
  alert('사업소 회원만 이용할 수 있습니다.');

include_once(G5_LIB_PATH.'/PHPExcel.php');

$reader = PHPExcel_IOFactory::createReader('Excel5');
$excel = $reader->load('receiptForm.xls');

# 사업소 정보
$excel->getActiveSheet()->setCellValue('E5', $member['mb_entNm']);
$excel->getActiveSheet()->setCellValue('L5', $member['mb_giup_btel']);

# 직인
$excel->getActiveSheet()->setCellValue('A29', "{$member['mb_entNm']} 사업소 (인)");
$seal_path = G5_DATA_PATH."/file/member/stamp/{$member['sealFile']}";
$seal = new PHPExcel_Worksheet_Drawing();
$seal->setName('직인');
$seal->setDescription('직인 이미지');
$seal->setPath($seal_path);
$seal->setCoordinates('H29');
$seal->setOffsetX(20);
$seal->setOffsetY(5);
$seal->setHeight(100);
$seal->setResizeProportional(true);
$seal->setWorksheet($excel->getActiveSheet());

$od_id = get_search_string($_GET['od_id']);
$dc_id = get_search_string($_GET['dc_id']);
if($od_id) {
  # 수급자 정보
  $pen = sql_fetch("
    SELECT
      penNm,
      penBirth
    FROM
      eform_document
    WHERE
      od_id = '{$od_id}'
  ");

  if(!$pen['penNm'])
    alert('해당 주문이 존재하지 않습니다.');
  
  $pen_birth = str_replace('.', '-', $pen['penBirth']);
  $pen_jumin = date('ymd', strtotime($pen_birth)).'-*******';
  $excel->getActiveSheet()->setCellValue('E4', $pen['penNm']);
  $excel->getActiveSheet()->setCellValue('L4', $pen_jumin);

  $data = [];

  # 계약서 품목
  $sql_document = "
    SELECT
      gubun,
      ca_name,
      it_name,
      it_price,
      it_price_pen,
      it_price_ent,
      STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', 3), '%Y-%m-%d') as it_date
    FROM
      eform_document d
    LEFT JOIN
      eform_document_item i ON d.dc_id = i.dc_id
    WHERE
      d.od_id = '{$od_id}'
  ";

  # 비급여 상품
  $sql_non_benefit = "
    SELECT
      '02' as gubun,
      x.ca_name,
      c.it_name,
      it_cust_price as it_price,
      it_cust_price as it_price_pen,
      0 as it_price_ent,
      ct_time as it_date
    FROM
      g5_shop_cart c
    LEFT JOIN
      g5_shop_item i ON c.it_id = i.it_id
    LEFT JOIN
      g5_shop_category x ON i.ca_id = x.ca_id
    WHERE
      c.od_id = '{$od_id}' and
      x.ca_id like '70%'
  ";

  $sql_result = sql_query("
    SELECT
      *
    FROM
    (
      ({$sql_document})
      UNION ALL
      ({$sql_non_benefit})
    ) u
    ORDER BY
      gubun asc,
      it_date asc,
      ca_name asc,
      it_name asc
  ");

  while($row = sql_fetch_array($sql_result)) {
    $gubun = '구입';
    if($row['gubun'] == '01') $gubun = '대여';
    else if($row['gubun'] == '02') $gubun = '비급여';

    $data[] = [
      $gubun,
      $row['ca_name'],
      $row['it_name'],
      '',
      $row['it_price'],
      $row['it_price_ent'],
      ($gubun == '비급여' ? 0 : $row['it_price_pen']),
      0,
      0,
      0,
      ($gubun == '비급여' ? $row['it_price_pen'] : 0),
      $row['it_price_pen'],
      date('Y.m.d', strtotime($row['it_date']))
    ];
  }

  // 데이터 입력
  $excel->getActiveSheet()->fromArray($data,NULL,'B13');
}

else if($dc_id) {
  # 수급자 정보
  $pen = sql_fetch("
    SELECT
      penNm,
      penBirth
    FROM
      eform_document
    WHERE
      dc_id = unhex('{$dc_id}')
  ");

  if(!$pen['penNm'])
    alert('해당 주문이 존재하지 않습니다.');
  
  $pen_birth = str_replace('.', '-', $pen['penBirth']);
  $pen_jumin = date('ymd', strtotime($pen_birth)).'-*******';
  $excel->getActiveSheet()->setCellValue('E4', $pen['penNm']);
  $excel->getActiveSheet()->setCellValue('L4', $pen_jumin);

  $data = [];

  # 계약서 품목
  $sql_document = "
    SELECT
      gubun,
      ca_name,
      it_name,
      it_price,
      it_price_pen,
      it_price_ent,
      STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', 3), '%Y-%m-%d') as it_date
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

  while($row = sql_fetch_array($sql_result)) {
    $gubun = '구입';
    if($row['gubun'] == '01') $gubun = '대여';
    else if($row['gubun'] == '02') $gubun = '비급여';

    $data[] = [
      $gubun,
      $row['ca_name'],
      $row['it_name'],
      '',
      $row['it_price'],
      $row['it_price_ent'],
      ($gubun == '비급여' ? 0 : $row['it_price_pen']),
      0,
      0,
      0,
      ($gubun == '비급여' ? $row['it_price_pen'] : 0),
      $row['it_price_pen'],
      date('Y.m.d', strtotime($row['it_date']))
    ];
  }

  // 데이터 입력
  $excel->getActiveSheet()->fromArray($data,NULL,'B13');
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"거래영수증.xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>
