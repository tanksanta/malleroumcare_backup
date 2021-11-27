<?php
include_once('./_common.php');

if(!$member['mb_entNm'])
  alert('사업소 회원만 이용할 수 있습니다.');

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

$od_id = get_search_string($_GET['od_id']);
$dc_id = get_search_string($_GET['dc_id']);
if($od_id) {
  # 수급자 정보
  $pen = sql_fetch("
    SELECT
      penNm,
      penLtmNum
    FROM
      eform_document
    WHERE
      od_id = '{$od_id}'
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
      d.od_id = '{$od_id}'
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

  # 비급여 상품
  $sql_non_benefit = "
    SELECT
      ct_qty,
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
    {$sql_non_benefit}
    ORDER BY
      gubun asc,
      it_date asc,
      ca_name asc,
      it_name asc
  ");

  $row_count = 19;
  $total_non_benefit_price = 0;
  while($row = sql_fetch_array($sql_result)) {
    if($row_count < 25) {
      $excel->getActiveSheet()->setCellValue('E'.$row_count, $row['it_name'] . " {$row['ct_qty']}개" );
      $excel->getActiveSheet()->setCellValue('G'.$row_count, $row['it_price'] * $row['ct_qty'] );
    }
    $total_non_benefit_price += ($row['it_price'] * $row['ct_qty']);
    if(!$buy_txt) {
      $buy_txt = "판매품목 : {$row['ca_name']} {$row['it_name']}";
    }

    if($row_count == 19)
      $row_count += 2;
    else
      $row_count += 1;
  }
  $excel->getActiveSheet()->setCellValue('G25', $total_non_benefit_price);

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
  $excel->getActiveSheet()->setCellValue('M10', $total_price + $total_non_benefit_price);
  $excel->getActiveSheet()->setCellValue('M12', $total_price_pen + $total_non_benefit_price);
  $excel->getActiveSheet()->setCellValue('M14', 0);
  $excel->getActiveSheet()->setCellValue('J24', ($rent_txt ? ($rent_txt . "\n") : '') . $buy_txt);
}

else if($dc_id) {
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

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"급여비용명세서.xlsx\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
?>
