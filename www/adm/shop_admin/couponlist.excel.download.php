<?php
$sub_menu = '400800';
include_once('./_common.php');

$g5['title'] = '쿠폰관리';

auth_check($auth[$sub_menu], "r");

$type = "user";

    $sql_common = "
      from g5_shop_coupon_member cm
      left join {$g5['g5_shop_coupon_table']} c on c.cp_no = cm.cp_no
      left join g5_member m on cm.mb_id = m.mb_id
      left join g5_member x on c.mb_id = x.mb_id
      left join g5_shop_coupon_log cpl on cpl.cp_id = c.cp_id and cpl.mb_id = cm.mb_id
    ";

    $sql_group = "";

    $sql_select = "
        select cpl.cl_datetime, CAST(cpl.od_id AS CHAR(16)) AS fod, cm.mb_id as coupon_user_id, m.mb_name as coupon_user_name, c.*, x.mb_name as mb_name
    ";

    $colspan = 13;

$sql_search = ' where (1) ';

// 기간만료된 쿠폰은 제외
$cp_expiration = $search_yn?($cp_expiration?$cp_expiration:'0'):'1';
if($cp_expiration){
    $sql_search .= $cp_expiration == '1'?' and (c.cp_end >= DATE_FORMAT(NOW(),"%Y-%m-%d")) ':'';
	$search_where .= "기간 만료 된 쿠폰 제외 함";
}else{
	$search_where .= "기간 만료 된 쿠폰 제외 안함";
}

// 쿠폰 종류
if ($sel_cp_method) {
	$search_where .= " | 쿠폰종류 : ";
  switch ($sel_cp_method) {
    case 'cp_method_it' :
      $sql_search .= " and ( c.cp_method  = '0' ) ";
	  $search_where .= "개별상품할인";
      break;
    case 'cp_method_cate' :
      $sql_search .= " and ( c.cp_method  = '1' ) ";
	  $search_where .= "카테고리할인";
      break;
    case 'cp_method_od' :
      $sql_search .= " and ( c.cp_method  = '2' ) ";
	  $search_where .= "주문금액할인";
      break;
    case 'cp_method_del' :
      $sql_search .= " and ( c.cp_method  = '3' ) ";
	  $search_where .= "배송비할인";
      break;
	default:
	  $search_where .= "전체";
	  break;
  }
}

// 검색어 검색
if ($sel_field) {
	$search_where .= " | 검색어 : ".$search;
      switch ($sel_field) {
        case 'cp_all' :
          $sql_search .= " and ( m.mb_id like '%{$search}%' or m.mb_name like '%{$search}%' or c.cp_id like '%{$search}%' or c.cp_subject like '%{$search}%' or  cpl.od_id = '{$search}') ";
		  $search_where .= "(전체)";
          break;
        case 'mb_id' :
          $sql_search .= " and ( m.mb_id like '%{$search}%' ) ";
		  $search_where .= "(회원ID)";
          break;
        case 'mb_name' :
          $sql_search .= " and ( m.mb_name like '%{$search}%' ) ";
		  $search_where .= "(회원이름)";
          break;
        case 'cp_id' :
          $sql_search .= " and ( c.cp_id like '%{$search}%' ) ";
		  $search_where .= "(쿠폰번호)";
          break;
        case 'cp_name' :
          $sql_search .= " and ( c.cp_subject like '%{$search}%' ) ";
		  $search_where .= "(쿠폰이름)";
          break;
        case 'od_id' :
          $sql_search .= " and ( cpl.od_id = '{$search}' ) ";
		  $search_where .= "(주문번호)";
          break;
      }
}

// 날짜 검색
if ($fr_date || $to_date) {
	$search_where .= " | 날짜 : ".$fr_date." ~ ".$to_date;
  switch ($date_searching_option) {
    case '0' : // 생성일자
      $sql_fr_date = $fr_date?" date_format(c.cp_datetime, '%Y-%m-%d') >= date_format('{$fr_date}', '%Y-%m-%d') " :"";
      $sql_to_date = $to_date?" date_format(c.cp_datetime, '%Y-%m-%d') <= date_format('{$to_date}', '%Y-%m-%d') " :"";
	  $search_where .= "(생성일자)";
      break;
    case '1' : // 사용일자
      $sql_fr_date = $fr_date?" date_format(cpl.cl_datetime, '%Y-%m-%d') >= date_format('{$fr_date}', '%Y-%m-%d') " :"";
      $sql_to_date = $to_date?" date_format(cpl.cl_datetime, '%Y-%m-%d') <= date_format('{$to_date}', '%Y-%m-%d') " :"";
	  $search_where .= "(사용일자)";
      break;
    case '2' : // 사용가능기간
      $sql_fr_date = $fr_date?" date_format(c.cp_end, '%Y-%m-%d') >= date_format('{$fr_date}', '%Y-%m-%d') " :"";
      $sql_to_date = $to_date?" date_format(c.cp_start, '%Y-%m-%d') <= date_format('{$to_date}', '%Y-%m-%d') " :"";
	  $search_where .= "(사용가능기간)";
      break;
  }
  if($fr_date && $to_date) {
      $sql_search .= " and (".$sql_fr_date." and ".$sql_to_date.") ";
  } else {
      $sql_search .= " and (".$sql_fr_date.$sql_to_date.") ";
  }
}

// 쿠폰 사용여부 체크
if ($sel_field_used) {
  $search_where .= " | 쿠폰사용여부 : ";
  switch ($sel_field_used) {
    case 'cp_used_use' :
      $sql_search .= " and (cpl.cl_datetime is not null) ";
	  $search_where .= "사용";
      break;
    case 'cp_used_non' :
      $sql_search .= " and (cpl.cl_datetime is null) ";
	  $search_where .= "미사용";
      break;
	default: $search_where .= "전체";break;
  }
}

if (!$sst) {
    $sst  = "cp_no";
    $sod = "desc";
}

// sst : 정렬 어떤걸로(생성일자), sod : 오름차순/내림차순(내림차순)
$sql_order = " order by {$sst} {$sod} ";

$sql = "
  select count(*) as cnt
  from (
    select c.*
    {$sql_common}
    {$sql_search}
    {$sql_group}
    {$sql_order}
  ) u
";
$row = sql_fetch($sql, true);
$total_count = $row['cnt'];

$sql = "
  {$sql_select}
  {$sql_common}
  {$sql_search}
  {$sql_group}
  {$sql_order}
";
$result = sql_query($sql, true);

// 초기 3개월 범위 적용
if (!$fr_date && !$to_date&&!$search_yn) {
    $fr_date = date("Y-m-d", strtotime("-3 month"));
    $to_date = date("Y-m-d");
}

// 기간 구분 초기화
if(!$date_searching_option) $date_searching_option = '0';


 for ($i=0; $row=sql_fetch_array($result); $i++) {
            switch($row['cp_method']) { // 쿠폰 종류 분류
                case '0':
                    $sql3 = " select it_name from {$g5['g5_shop_item_table']} where it_id = '{$row['cp_target']}' ";
                    $row3 = sql_fetch($sql3);
                    $cp_method = '개별상품할인';
                    $cp_target = get_text($row3['it_name']);
                    break;
                case '1':
                    $sql3 = " select ca_name from {$g5['g5_shop_category_table']} where ca_id = '{$row['cp_target']}' ";
                    $row3 = sql_fetch($sql3);
                    $cp_method = '카테고리할인';
                    $cp_target = get_text($row3['ca_name']);
                    break;
                case '2':
                    $cp_method = '주문금액할인';
                    $cp_target = '주문금액';
                    break;
                case '3':
                    $cp_method = '배송비할인';
                    $cp_target = '배송비';
                    break;
            }

            $link1 = '<a href="./orderform.php?od_id='.$row['od_id'].'">';
            $link2 = '</a>';

            // 쿠폰사용일자
            $used_date = $row['cl_datetime'];

            switch($row['cp_type']) { //할인금액(정액할인/정률할인)
                case '0':
                    $cp_type = "정액할인";
                    $cp_price = number_format($row['cp_price']);
                    break;
                case '1':
                    $cp_type = "정률할인";
                    $cp_price = $row['cp_price'].'%';
                    break;
            }

            //남은기간 계산
            $datetime_end = new DateTime(date("Y-m-d", strtotime($row['cp_end'])));
            $datetime_now = new DateTime(date("Y-m-d"));

            if ($datetime_end >= $datetime_now) { // 쿠폰 종료일이 오늘보다 뒤이면
                $interval = date_diff( $datetime_end, $datetime_now );
            } else {
                $interval = '기간만료';
            }
			$interval2 = ($interval=='기간만료')?$interval: ($interval->days+1).'일';//남은기간
			$used_date2 = ($used_date)?date("Y-m-d H:m:i", strtotime($used_date)) : "";//사용일
			$fod = ($row['fod'])? $row['fod']:"" ;//주문번호
			$data[] = [
				$total_count-$i,//일련번호
				$row['cp_id'],//쿠폰번호
				$row['coupon_user_id'],//회원ID  
				$row['coupon_user_name'],//회원이름
				$row['cp_subject'],//쿠폰이름
				$cp_method,//쿠폰종류
				$cp_price,//쿠폰금액
				$interval2,//남은기간
				$row['cp_start']." ~ ".$row['cp_end'],//사용가능일자
				date("Y-m-d H:m:i", strtotime($row['cp_datetime'])),//사용일자
				$used_date2,//주문번호
				$fod,
			  ];
         }

$title = ['No.','쿠폰번호','회원ID','회원이름','쿠폰이름','쿠폰종류','쿠폰금액','남은기간','사용가능일자','생성일자','사용일자','주문번호'];
// 엑셀 라이브러리 설정
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();
$excel->setActiveSheetIndex(0)->mergeCells('A1:L1');
$excel->setActiveSheetIndex(0)->mergeCells('F3:L3');
$excel->getActiveSheet()->getStyle('L')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
// 시트 네임
$sheet->setTitle("쿠폰관리");

$last_row = count($data) + 1;
if($last_row < 2) $last_row = 2;
// 전체 테두리 지정
$sheet -> getStyle(sprintf("A4:L%s", ($last_row+3))) -> getBorders() -> getAllBorders() -> setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
// 전체 가운데 정렬
$sheet -> getStyle(sprintf("A1:L%s", ($last_row+3))) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//A4 기준 틀고정
$sheet->freezePane('A5');
// 열 높이
for($i = 2; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(-1);
}
$sheet->getStyle("A4:L4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle('A1')->getFont()->setSize(15);
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle("A4:L4")->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$excel->setActiveSheetIndex(0)->setCellValue('A1', "쿠폰관리"); 
$sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$excel->setActiveSheetIndex(0)->setCellValue('A3', date("Y-m-d"));
$sheet->getStyle('F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->setCellValue('F3', "검색 : ".$search_where);
$sheet->fromArray($title,NULL,'A4');
$sheet->fromArray($data,NULL,'A5');

//텍스트 크기에 맞춰 자동으로 크기를 조정한다.
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(25);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(30);
$sheet->getColumnDimension('E')->setWidth(45);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(10);
$sheet->getColumnDimension('H')->setWidth(10);
$sheet->getColumnDimension('I')->setWidth(25);
$sheet->getColumnDimension('J')->setWidth(25);
$sheet->getColumnDimension('K')->setWidth(20);
$sheet->getColumnDimension('L')->setWidth(20);



header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"쿠폰관리(회원별)_".date("Ymd").".xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');

?>