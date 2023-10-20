<?php
include_once("./_common.php");

if($member['mb_type'] !== 'default' || !$member['mb_entId']) {
  alert('사업소 회원만 접근할 수 있습니다.');
}

if($_POST["mode"] == "w"){//등록 시 
	$sql11 = "SELECT MAX(SUBSTRING(it_date,12,10)) AS it_end_date FROM eform_document_item WHERE it_id IN (".$_POST['it_ids'].")";
	$row = sql_fetch($sql11);
	$it_end_date = $row["it_end_date"];
	$sql = "insert into eform_rent_hist SET entId='{$member['mb_entId']}',entNm='{$member['mb_entNm']}',entNum='{$member['mb_ent_num']}',penId='{$_POST['penId']}',confirm_date='{$_POST['confirm_date']}',create_month='{$_POST['create_month']}',entConAcc='{$_POST['entConAcc']}',penRecTypeCd='{$_POST['penRecTypeCd']}',it_ids='{$_POST['it_ids']}',it_dates='{$_POST['it_dates']}',it_end_date='{$it_end_date}',reg_date=now()";//급여제공기록 이력 등록
	sql_query($sql);
}else{//이력 조회 시
	$sql = "select * from eform_rent_hist where rh_id='{$_POST['rh_id']}'";
	$row = sql_fetch($sql);
	$_POST['penId'] = $row["penId"];
	$_POST['confirm_date'] = $row["confirm_date"];
	$_POST['create_month'] = $row["create_month"];
	$_POST['entConAcc'] = $row["entConAcc"];
	$_POST['penRecTypeCd'] = $row["penRecTypeCd"];
	$_POST['it_ids'] = $row["it_ids"];
	$_POST['it_dates'] = $row["it_dates"];
}

 //수급자 정보
$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
  'usrId' => $member['mb_id'],
  'entId' => $member['mb_entId'],
  'penId' => $_POST['penId']
));

$pen = $res['data'][0];
$pen_nm = $pen['penNm'];
$it_id = explode(",",$_POST['it_ids']);
$row_count = count($it_id);
$it_date = explode(",",$_POST['it_dates']);
for($ii = 0; $ii < $row_count; $ii++ ){
	$it_dates[$it_id[$ii]] = $it_date[$ii];
}

$title_text = $pen['penNm']."_".$pen['penLtmNum'];

$sql1 = "SELECT * FROM eform_document_item WHERE it_id IN (".$_POST['it_ids'].")";
$resutl = sql_query($sql1);
while($row2 = sql_fetch_array($resutl)){
	$ca_name[$row2["it_id"]] = $row2["ca_name"];//품목명
	$it_name[$row2["it_id"]] = $row2["it_name"];//제품명
	$it_code[$row2["it_id"]] = $row2["it_code"];//품목코드
	$it_barcode[$row2["it_id"]] = $row2["it_barcode"];//바코드
	$it_rental_price[$row2["it_id"]] = $row2["it_rental_price"];//기준급여비용
}


function calc_rental_price($str_date, $end_date, $price,$penTypeCd) {
    $rental_price = 0;
	$price22 = array();
    $str_time = strtotime($str_date);
    $end_time = strtotime($end_date);

    $year1 = date('Y', $str_time);
    $year2 = date('Y', $end_time);

    $month1 = date('m', $str_time);
    $month2 = date('m', $end_time);

	$day1 = date('d', $str_time);
    $day2 = date('d', $end_time);

    $diff = (($year2 - $year1) * 12) + ($month2 - $month1);

    // 중간달 계산
    if($diff > 1) {
        $rental_price1 += ( $price * ($diff - 1) );
    }

    
    if($diff == 0){ //년,월 차이 없이 일만 차이 있을 경우
		$rental_price2 += (int) (round(
			$price * (
				($end_date-$str_date+1)
				/
				( date('t', $end_time)*10 )
			))*10
		) ;
	}else{// 마지막 달 계산 
		$rental_price2 += (int) (round(
			$price * (
				date('j', $end_time)
				/
				( date('t', $end_time) * 10 )
			)
		)) * 10;
	}

    if($diff > 0) {
        // 첫째 달 계산
        $rental_price3 += (int) (round(
            $price * (
                ( date('t', $str_time) - date('j', $str_time) + 1 )
                /
                ( date('t', $str_time) * 10 )
            )
        )) * 10;
    }

	$rental_price = $rental_price1+$rental_price2+$rental_price3;
    $price22["calc_rental_price"] = $rental_price;//$rental_price;
	if($diff == 0){//단기계약
		$price22["calc_pen_price"] = calc_pen_price(($penTypeCd), ($rental_price2),2);
	}else{//일반계약
		$price22["calc_pen_price"] = calc_pen_price(($penTypeCd), $rental_price1+$rental_price2+$rental_price3,1);
	}

	return $price22;
}

function calc_pen_price($penTypeCd, $price,$round_floor) {
	switch($penTypeCd) {
        case '00':
            $rate = 15;
            break;
        case '01':
            $rate = 9;
            break;
        case '02':
        case '03':
            $rate = 6;
            break;
        case '04':
            return 0;
        default:
            $rate = 15;
            break;
    }
	
	if($round_floor == 2){
		$pen_price =  (int)round(
			$price * ($rate / 100)/10
		) * 10;
	}else{
		$pen_price =  (int)floor(
			$price * ($rate/ 100)/10
		)* 10 ;
	}

    return $pen_price;
}






//============================== 엑셀 영역 시작 =====================
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();

 // 문서 사이즈 설정
    $sheet->getPageSetup()
    ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

// 문서 좌우 여백 설정
    $sheet->getPageMargins()->setTop(0.3);	
    $sheet->getPageMargins()->setBottom(0.3);
    $sheet->getPageMargins()->setRight(0.3);
    $sheet->getPageMargins()->setLeft(0.3);
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


// 시트 네임
$sheet->setTitle($title_text);

$excel->setActiveSheetIndex(0)->setCellValue('B1', "■ 노인장기요양보험법 시행규칙 [별지 제16호의2서식] <개정 2019. 6. 12.>"); 
$excel->setActiveSheetIndex(0)->setCellValue('B2', "장기요양급여 제공기록지(복지용구)");
$excel->setActiveSheetIndex(0)->setCellValue('B3', "※ 뒤쪽의 아래의 유의사항을 읽고 작성하여 주시기 바라며, [   ]에는 해당되는 곳에 √표를 합니다.");
$excel->setActiveSheetIndex(0)->setCellValue('I3', "(앞쪽)");
//수급자 정보
$excel->setActiveSheetIndex(0)->setCellValue('B4', "수급자 성명");
$excel->setActiveSheetIndex(0)->setCellValue('D4', "생년월일");
$excel->setActiveSheetIndex(0)->setCellValue('F4', "장기요양등급");
$excel->setActiveSheetIndex(0)->setCellValue('H4', "장기요양인정번호");
$excel->setActiveSheetIndex(0)->setCellValue('B5', $pen['penNm']);
$excel->setActiveSheetIndex(0)->setCellValue('D5', $pen['penBirth']);
$excel->setActiveSheetIndex(0)->setCellValue('F5', $pen['penRecGraNm']);
$excel->setActiveSheetIndex(0)->setCellValue('H5', $pen['penLtmNum']);
//사업소 정보
$excel->setActiveSheetIndex(0)->setCellValue('B6', "장기요양기관명");
$excel->setActiveSheetIndex(0)->setCellValue('F6', "장기요양기관기호");
$excel->setActiveSheetIndex(0)->setCellValue('B7', $member["mb_entNm"]);
$excel->setActiveSheetIndex(0)->setCellValue('F7', $member["mb_ent_num"]);
//대여품목 정보
$excel->setActiveSheetIndex(0)->setCellValue('B8', "[   ]구입 [●]대여");
$excel->setActiveSheetIndex(0)->setCellValue('B9', "①품목명");
$excel->setActiveSheetIndex(0)->setCellValue('C9', "②제품명");
$excel->setActiveSheetIndex(0)->setCellValue('D9', "③복지용구\n표준코드");
$excel->setActiveSheetIndex(0)->setCellValue('E9', "④급여비용");
$excel->setActiveSheetIndex(0)->setCellValue('F9', "⑤판매일\n또는\n대여기간");
$excel->setActiveSheetIndex(0)->setCellValue('G9', "급여비 내역(원)");
$excel->setActiveSheetIndex(0)->setCellValue('G10', "⑥총액");
$excel->setActiveSheetIndex(0)->setCellValue('H10', "⑦본인부담금");
$excel->setActiveSheetIndex(0)->setCellValue('I10', "⑧공단부담액");
//대여품목 들어갈 자리
for($i = 0;$i < $item_count; $i++){
$price = calc_rental_price(str_replace("-","",substr($it_dates[$it_id[$i]],0,10)), str_replace("-","",substr($it_dates[$it_id[$i]],11,10)), $it_rental_price[$it_id[$i]],$pen['penTypeCd']);
$it_price = $price["calc_rental_price"];//대여가(추가)
$it_price_pen = $price["calc_pen_price"];//본인부담금(추가)
$it_price_ent = $it_price - $it_price_pen;//공단부담금

$excel->setActiveSheetIndex(0)->setCellValue('B'.(12+$i), $ca_name[$it_id[$i]]);//품목명
$excel->setActiveSheetIndex(0)->setCellValue('C'.(12+$i), $it_name[$it_id[$i]]);//제품명
$excel->setActiveSheetIndex(0)->setCellValue('D'.(12+$i), $it_code[$it_id[$i]]."\n-".$it_barcode[$it_id[$i]]);//복지용구 표준코드
$excel->setActiveSheetIndex(0)->setCellValue('E'.(12+$i), number_format($it_price));//급여비용
$excel->setActiveSheetIndex(0)->setCellValue('F'.(12+$i), substr($it_dates[$it_id[$i]],0,10)."\n".substr($it_dates[$it_id[$i]],10,11));//대여기간
$excel->setActiveSheetIndex(0)->setCellValue('G'.(12+$i), number_format($it_price));//);//총액
$excel->setActiveSheetIndex(0)->setCellValue('H'.(12+$i), number_format($it_price_pen));//봉인부담금
$excel->setActiveSheetIndex(0)->setCellValue('I'.(12+$i), number_format($it_price_ent));//공단부담액
}
//기타정보
$excel->setActiveSheetIndex(0)->setCellValue('B'.(12+$item_count), "특이사항");
$excel->setActiveSheetIndex(0)->setCellValue('D'.(12+$item_count), $_POST["entConAcc"]);
$excel->setActiveSheetIndex(0)->setCellValue('B'.(13+$item_count), "확인자");
$excel->setActiveSheetIndex(0)->setCellValue('C'.(13+$item_count), "사업소\n담당자");
$excel->setActiveSheetIndex(0)->setCellValue('D'.(13+$item_count), "( 서명 또는 인 )   ");
$excel->setActiveSheetIndex(0)->setCellValue('C'.(14+$item_count), "수급자\n또는\n보호자");
$excel->setActiveSheetIndex(0)->setCellValue('D'.(14+$item_count), "( 서명 또는 인 )  ");
$excel->setActiveSheetIndex(0)->setCellValue('D'.(15+$item_count), "수급자와의 관계 : [     ] 본인   [     ] 가족   [     ] 친족   [     ] 기타(                   )");
$excel->setActiveSheetIndex(0)->setCellValue('B'.(16+$item_count), "확인일시");
$excel->setActiveSheetIndex(0)->setCellValue('D'.(16+$item_count), substr($_POST["confirm_date"],0,4)." 년    ".substr($_POST["confirm_date"],5,2)." 월    ".substr($_POST["confirm_date"],8,2)." 일");
$excel->setActiveSheetIndex(0)->setCellValue('G'.(16+$item_count), "확인방법");
$excel->setActiveSheetIndex(0)->setCellValue('H'.(16+$item_count), "[ ".(($_POST["penRecTypeCd"]=="02")?"√":"  ")." ] 방문   [ ".(($_POST["penRecTypeCd"]=="02")?"  ":"√")." ] 유선");

$excel->setActiveSheetIndex(0)->setCellValue('B'.(18+$item_count), "유의사항");
$excel->setActiveSheetIndex(0)->setCellValue('B'.(19+$item_count), "① ~ ②:「복지용구 품목별 제품목록 및 급여비용 등에 관한 고시」에 명시된 품목명과 제품명을 적습니다.
③ 복지용구표준코드: 제공 제품의 복지용구 바코드 라벨을 확인하여 제품코드 및 제조번호를 포함한 복지용구표준코드를 적습니다.
④ 급여비용:「복지용구 품목별 제품목록 및 급여비용 등에 관한 고시」에 명시된 제품별 급여비용을 적습니다.
⑤ 판매일 또는 대여기간: 구입인 경우 판매일을 적고, 대여인 경우 대여기간(시작일과 종료일)을 적습니다.
⑥ 총액: 구입인 경우 판매일(⑤)에 판매한 제품의 급여비용(④)을 적고, 대여인 경우 대여기간(⑤)에 해당하는 급여비용의 총액을 적습니다. 다만, 제품별 월 대여가격 산정방법에 따라 산출된 금액의 10원 미만은 반올림합니다.
⑦ 본인부담금:「노인장기요양보험법」제40조에 따른 법정 본인부담금을 적습니다.
⑧ 공단부담액: 총액에서 본인부담금을 뺀 금액을 적습니다.
※ 장기요양급여 제공기록지(복지용구)는 구입제품은 제공할 때, 대여제품은 최초 제공할 때와 매월 기록합니다.
※ 의료기관 입원, 시설 입소, 복지용구 점검 사항 등은 특이사항에 기록합니다.");

//셀 색상 지정
$sheet->getStyle("B4:I4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('dddddd');
$sheet->getStyle("B6:I6")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('dddddd');
$sheet->getStyle("B9:I11")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('dddddd');

$sheet->getStyle("B".(12+$item_count).":C".(16+$item_count))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('dddddd');
$sheet->getStyle("G".(16+$item_count))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('dddddd');
$sheet->getStyle("B".(18+$item_count))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');

//텍스트 크기에 맞춰 자동으로 크기를 조정한다.
$sheet->getColumnDimension('A')->setWidth(1);
$sheet->getColumnDimension('B')->setWidth(12);
$sheet->getColumnDimension('C')->setWidth(12);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(12);
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(12);
$sheet->getColumnDimension('H')->setWidth(12);
$sheet->getColumnDimension('I')->setWidth(12);

//줄바꿈 허용
$sheet->getStyle('B4:I'.(19+$item_count))->getAlignment()->setWrapText(true); // 줄바꿈 허용

//셀 높이 조절
for($i=2;$i<(20+$item_count);$i++){
$sheet->getRowDimension($i)->setRowHeight(20);
}

$sheet->getRowDimension(2)->setRowHeight(30);
$sheet->getRowDimension((12+$item_count))->setRowHeight(30);
$sheet->getRowDimension((13+$item_count))->setRowHeight(30);
$sheet->getRowDimension((17+$item_count))->setRowHeight(15);
$sheet->getRowDimension((19+$item_count))->setRowHeight(120);

//폰트 사이즈
$sheet->getStyle('B1')->getFont()->setSize(7);
$sheet->getStyle('B2')->getFont()->setSize(14);
$sheet->getStyle('B3')->getFont()->setSize(7);
$sheet->getStyle('I3')->getFont()->setSize(7);
$sheet->getStyle('B4:I'.(19+$item_count))->getFont()->setSize(8);

//폰트 색상
$sheet->getStyle('D'.(13+$item_count))->getFont()->getColor()->setARGB("aaaaaa");
$sheet->getStyle('D'.(14+$item_count))->getFont()->getColor()->setARGB("aaaaaa");

// 전체 테두리 지정
$border = [
	'borders' => [
		'allborders' => [
			'style' => PHPExcel_Style_Border::BORDER_THIN
		]
	]
];
$sheet ->getStyle("B4:I".(19+$item_count))->applyFromArray($border);

// 전체 가운데 정렬
$sheet -> getStyle("B2:I".(18+$item_count)) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet -> getStyle("B1:I".(19+$item_count)) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
//정렬
$sheet -> getStyle("B3") -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet -> getStyle("I3") -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet -> getStyle("D".(13+$item_count).":D".(14+$item_count)) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$sheet -> getStyle("D".(12+$item_count)) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$sheet -> getStyle("B".(19+$item_count)) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

//이미지 추가
$path =  $_SERVER['DOCUMENT_ROOT']."/data/file/member/stamp/".$member["sealFile"];
$objDrawing = new PHPExcel_Worksheet_Drawing(); 
$objDrawing->setPath($path); 
$objDrawing->setWidth(70); 
$objDrawing->setHeight(70);
$objDrawing->setOffsetX(-35);  // 이미지가 시작할 위치를 퍼센트로 적용 셀의 크기에 가로가 35%만큼 이동해서 시작
$objDrawing->setOffsetY(-5);  // 이미지가 시작할 위치를 퍼센트로 적용 셀의 크기에 세로가 5%만큼 이동해서 시작
$objDrawing->setCoordinates('I'.(13+$item_count)); 
$objDrawing->setWorksheet($excel->getActiveSheet());
/*
$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setPath('../../img/shinjongho.tif');
$objDrawing->setCoordinates('K3');
$objDrawing->setOffsetX(10); 
$objDrawing->setOffsetY(-20);
$objDrawing->setWidth(52); 
$objDrawing->setHeight(52); 
$objDrawing->setWorksheet($excel->getActiveSheet());
*/

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"장기요양급여제공기록지(".$pen['penNm']."_".$pen['penLtmNum']."_".$create_month.")_".date("Ymd").".xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
