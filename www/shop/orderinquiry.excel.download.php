<?php
include_once("./_common.php");

if($member['mb_type'] !== 'default' || !$member['mb_entId']) {
  alert('사업소 회원만 접근할 수 있습니다.');
}

$s_date = ($_POST["s_date"] == "" && $_POST["e_date"] == "")?date("Y-m")."-01":$_POST["s_date"];
$e_date = ($_POST["s_date"] == "" && $_POST["e_date"] == "")?date("Y-m")."-".date("t"):$_POST["e_date"];

	$sql_common = " from {$g5['g5_shop_order_table']} as o
		LEFT JOIN g5_shop_cart as c ON o.od_id = c.od_id
		LEFT JOIN g5_shop_item as i ON c.it_id = i.it_id
		where o.mb_id = '{$member['mb_id']}' AND od_del_yn = 'N' ";
	
	if($_POST["s_date"]){
		$sql_search .= " AND od_time >= '{$s_date} 00:00:00' ";
	}

	if($_POST["e_date"]){
		$sql_search .= " AND od_time <= '{$e_date} 23:59:59' ";
	}
	
	$sql = " select o.*, i.it_model, i.it_name, 
	c.ct_id, c.ct_status, c.ct_ex_date, c.ct_direct_delivery_date, c.ct_delivery_num, c.ct_delivery_company,c.ct_combine_ct_id, c.ct_option,c.ct_qty,
	c2.ct_delivery_num AS ct_delivery_num2, c2.ct_delivery_company AS ct_delivery_company2
		  from {$g5['g5_shop_order_table']} as o 
  		  LEFT JOIN g5_shop_cart as c ON o.od_id = c.od_id
		  LEFT JOIN g5_shop_item as i ON c.it_id = i.it_id
		  LEFT JOIN g5_shop_cart AS c2 ON c2.ct_id = c.ct_combine_ct_id AND c2.ct_id IS NOT NULL
		  where o.mb_id = '{$member['mb_id']}'
		  AND o.od_del_yn = 'N'
		  {$sql_search}
		  AND `od_hide_control` != '1' 
		  AND c.ct_status IN ('준비', '출고준비','배송') 
		  ORDER BY o.od_id ASC,c.ct_delivery_company ASC,c.ct_delivery_num ASC";
	$result = sql_query($sql);

	

//============================== 엑셀 영역 시작 =====================
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();

/*
//셀병합
$excel->setActiveSheetIndex(0)->mergeCells('B1:I1');
$excel->setActiveSheetIndex(0)->mergeCells('B2:I2');
$excel->setActiveSheetIndex(0)->mergeCells('B3:H3');
$excel->setActiveSheetIndex(0)->mergeCells('B4:C4');
$excel->setActiveSheetIndex(0)->mergeCells('D4:E4');
$excel->setActiveSheetIndex(0)->mergeCells('F4:G4');
$excel->setActiveSheetIndex(0)->mergeCells('H4:I4');
$excel->setActiveSheetIndex(0)->mergeCells('B5:C5');
$excel->setActiveSheetIndex(0)->mergeCells('D5:E5');
$excel->setActiveSheetIndex(0)->mergeCells('F5:G5');
$excel->setActiveSheetIndex(0)->mergeCells('H5:I5');
$excel->setActiveSheetIndex(0)->mergeCells('B6:E6');
$excel->setActiveSheetIndex(0)->mergeCells('F6:I6');
$excel->setActiveSheetIndex(0)->mergeCells('B7:E7');
$excel->setActiveSheetIndex(0)->mergeCells('F7:I7');
$excel->setActiveSheetIndex(0)->mergeCells('B8:I8');
$excel->setActiveSheetIndex(0)->mergeCells('B9:B11');
$excel->setActiveSheetIndex(0)->mergeCells('C9:C11');
$excel->setActiveSheetIndex(0)->mergeCells('D9:D11');
$excel->setActiveSheetIndex(0)->mergeCells('E9:E11');
$excel->setActiveSheetIndex(0)->mergeCells('F9:F11');
$excel->setActiveSheetIndex(0)->mergeCells('G9:I9');
$excel->setActiveSheetIndex(0)->mergeCells('G10:G11');
$excel->setActiveSheetIndex(0)->mergeCells('H10:H11');
$excel->setActiveSheetIndex(0)->mergeCells('I10:I11');
//대여품목 개수에 의해 숫자 변경 가능

$item_count = count($it_id);
$excel->setActiveSheetIndex(0)->mergeCells('B'.(12+$item_count).':C'.(12+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('D'.(12+$item_count).':I'.(12+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('B'.(13+$item_count).':B'.(15+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('D'.(13+$item_count).':I'.(13+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('C'.(14+$item_count).':C'.(15+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('D'.(14+$item_count).':I'.(14+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('D'.(15+$item_count).':I'.(15+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('B'.(16+$item_count).':C'.(16+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('D'.(16+$item_count).':F'.(16+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('H'.(16+$item_count).':I'.(16+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('B'.(17+$item_count).':I'.(17+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('B'.(18+$item_count).':I'.(18+$item_count));
$excel->setActiveSheetIndex(0)->mergeCells('B'.(19+$item_count).':I'.(19+$item_count));
*/

// 시트 네임
$sheet->setTitle("송장정보(".$s_date."~".$e_date.")");
$title = ['상태','택배사','송장번호','주문번호','주문일자','상품명(옵션)','수량','수령인','우편번호','배송지','전화번호','휴대폰번호'];
$sheet->fromArray($title,NULL,'A1');



$ct_delivery_num2 = "";
$i2 = "";
//주문정보

for($i = 2;$row=sql_fetch_array($result); $i++){
	switch($row["ct_status"]){//상태
		case "준비": $ct_status = "상품준비";break;
		case "출고준비": $ct_status = "출고준비";break;
		case "배송": $ct_status = "출고완료";break;
		//default : $ct_status = "상품준비";break;
	}
	if($row["ct_combine_ct_id"] != ""){//합포여부
		$ct_delivery_company = $row["ct_delivery_company2"];//택배사
		$ct_delivery_num =  $row["ct_delivery_num2"];//송장번호
	}else{
		$ct_delivery_company = $row["ct_delivery_company"];//택배사
		$ct_delivery_num =  $row["ct_delivery_num"];//송장번호
	}

	foreach($delivery_companys as $data){ 
        if($ct_delivery_company == $data["val"] ){
            $delivery_company=$data["name"];
        }
	}
	$od_time = substr($row["od_time"],0,10);	
		
	if($ct_delivery_num == "" || ($ct_delivery_num2 != $ct_delivery_num)){
		if($i2 != ""){
			$excel->setActiveSheetIndex(0)->mergeCells('A'.$i2.':A'.($i-1));
			$sheet->getStyle('A'.$i2.':A'.($i-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$excel->setActiveSheetIndex(0)->mergeCells('B'.$i2.':B'.($i-1));
			$sheet->getStyle('B'.$i2.':B'.($i-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$excel->setActiveSheetIndex(0)->mergeCells('C'.$i2.':C'.($i-1));
			$sheet->getStyle('C'.$i2.':C'.($i-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$excel->setActiveSheetIndex(0)->mergeCells('H'.$i2.':H'.($i-1));
			$sheet->getStyle('H'.$i2.':H'.($i-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$excel->setActiveSheetIndex(0)->mergeCells('I'.$i2.':I'.($i-1));
			$sheet->getStyle('I'.$i2.':I'.($i-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$excel->setActiveSheetIndex(0)->mergeCells('J'.$i2.':J'.($i-1));
			$sheet->getStyle('J'.$i2.':J'.($i-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$excel->setActiveSheetIndex(0)->mergeCells('K'.$i2.':K'.($i-1));
			$sheet->getStyle('K'.$i2.':K'.($i-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$excel->setActiveSheetIndex(0)->mergeCells('L'.$i2.':L'.($i-1));
			$sheet->getStyle('L'.$i2.':L'.($i-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		}
		$excel->setActiveSheetIndex(0)->setCellValue('A'.$i, $ct_status);//상태
		$excel->setActiveSheetIndex(0)->setCellValue('B'.$i, $delivery_company);//택배사
		$excel->setActiveSheetIndex(0)->setCellValue('C'.$i, $ct_delivery_num);//송장번호
		$excel->setActiveSheetIndex(0)->setCellValue('H'.$i, $row["od_b_name"]);//수령인
		$excel->setActiveSheetIndex(0)->setCellValue('I'.$i, $row["od_b_zip1"].$row["od_b_zip2"]);//우편번호
		$excel->setActiveSheetIndex(0)->setCellValue('J'.$i, $row["od_b_addr1"]." ".$row["od_b_addr2"]." ".$row["od_b_addr3"]);//배송지
		$excel->setActiveSheetIndex(0)->setCellValue('K'.$i, $row["od_b_tel"]);//전화번호
		$excel->setActiveSheetIndex(0)->setCellValue('L'.$i, $row["od_b_hp"]);//휴대폰번호
		$i2 = $i;
	}

	
	$excel->getActiveSheet()->setCellValueExplicit('C'.$i , $ct_delivery_num , PHPExcel_Cell_DataType::TYPE_STRING);
	$excel->setActiveSheetIndex(0)->setCellValue('D'.$i, $row["od_id"]);//주문번호
	$excel->getActiveSheet()->setCellValueExplicit('D'.$i , $row["od_id"] , PHPExcel_Cell_DataType::TYPE_STRING);
	$excel->setActiveSheetIndex(0)->setCellValue('E'.$i, $od_time);//주문일자
	$excel->setActiveSheetIndex(0)->setCellValue('F'.$i, $row["it_name"]."(".$row["ct_option"].")");//상품명(옵션)
	$excel->setActiveSheetIndex(0)->setCellValue('G'.$i, number_format($row["ct_qty"]));//수량
	$ct_delivery_num2 = $ct_delivery_num;	
}

//정렬
$sheet->getStyle('A1:L1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//셀 색상 지정
$sheet->getStyle("A1:L1")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('dddddd');


//폰트 색상
$sheet->getStyle('A1:L1')->getFont()->setBold(true);

//텍스트 크기에 맞춰 자동으로 크기를 조정한다.
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(10);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(60);
$sheet->getColumnDimension('G')->setWidth(10);
$sheet->getColumnDimension('H')->setWidth(20);
$sheet->getColumnDimension('I')->setWidth(10);
$sheet->getColumnDimension('J')->setWidth(50);
$sheet->getColumnDimension('K')->setWidth(20);
$sheet->getColumnDimension('L')->setWidth(20);


// 전체 테두리 지정
$border = [
	'borders' => [
		'allborders' => [
			'style' => PHPExcel_Style_Border::BORDER_THIN
		]
	]
];
$sheet ->getStyle("A1:L".($i-1))->applyFromArray($border);



header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"송장정보_".date("YmdHis").".xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
