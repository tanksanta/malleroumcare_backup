<?php
$sub_menu = '400310';
include_once("./_common.php");

//auth_check($auth[$sub_menu], "r");

// 데이터 처리
$data = [];


// 검색처리
$select = array();
$where = array();

$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sel_field = isset($_REQUEST['sel_field']) && in_array($_REQUEST['sel_field'], array('it_thezone2', 'it_name','it_id','it_admin_memo')) ? $_REQUEST['sel_field'] : '';
$page_rows = ($_REQUEST['page_rows'] != "") ? get_search_string($_REQUEST['page_rows']) : $config['cf_page_rows'];//


$qstr = '';


/////////////////////////////////////////////////
$where = " and ";
$sql_search = "";
if ($search != "") {
        if($sel_field == '') {
            $attrs = ['it_thezone2', 'it_name', 'it_id','it_admin_memo'];
            $sql_search .= " $where ( 1 != 1 ";
            $where = ' or ';
            foreach($attrs as $attr) {
                $sql_search .= " $where $attr like '%$search%' ";
            }
            $sql_search .= ' ) ';
            $where = ' and ';
        } else {
            $sql_search .= " $where $sel_field like '%$search%' ";
            $where = " and ";
        }
	$qstr .="&amp;search=".$search."&amp;sel_field=".$sel_field;
}
switch($sel_field){
	case "it_name":$sel_field_text = "상품명"; break;
	case "it_thezone2":$sel_field_text = "품목코드"; break;
	case "it_id":$sel_field_text = "상품관리코드"; break;
	case "it_admin_memo":$sel_field_text = "관리자메모"; break;
	default:$sel_field_text = "전체"; break;
}
$search_text = $search;


if($_REQUEST["prodSupYn"] != ""){//유통구분
	if($_REQUEST["prodSupYn"] != "all"){
		$sql_search .= " AND prodSupYn = '{$_REQUEST["prodSupYn"]}' ";
		$prodSupYn_text = ($_REQUEST["prodSupYn"]=="Y")?"유통":"비유통";
	}else{
		$prodSupYn_text = "전체";
	}
	$qstr .="&amp;prodSupYn=".$_REQUEST["prodSupYn"];
}else{
	$sql_search .= " AND prodSupYn = 'Y' ";
	$prodSupYn_text = "유통";
}

if($_REQUEST["gubun"] != ""){//급여구분
	if($_REQUEST["gubun"] == "70"){//비급여
		$sql_search .= " AND a.ca_id like '70%' ";
		$gubun_text = "비급여";
	}elseif($_REQUEST["gubun"] == "80"){//보장구
		$sql_search .= " AND a.ca_id like '80%' ";
		$gubun_text = "보장구";
	}else{//급여
		$sql_search .= " AND (a.ca_id like '10%' or a.ca_id like '20%') ";
		$gubun_text = "급여";
	}
	$qstr .="&amp;gubun=".$_REQUEST["gubun"];
}else{
	$gubun_text = "전체";
}

if($_REQUEST["it_deadline"] != ""){//마감시간
	switch($_REQUEST["it_deadline"]){
		case 1: $sql_search .= " AND it_deadline between '09:00:00' and '09:59:59' "; $it_deadline_text = "09:00~10:00"; break;
		case 2: $sql_search .= " AND it_deadline between '10:00:00' and '10:59:59' "; $it_deadline_text = "10:00~11:00"; break;
		case 3: $sql_search .= " AND it_deadline between '11:00:00' and '11:59:59' "; $it_deadline_text = "11:00~12:00"; break;
		case 4: $sql_search .= " AND it_deadline between '12:00:00' and '12:59:59' "; $it_deadline_text = "12:00~13:00"; break;
		case 5: $sql_search .= " AND it_deadline between '13:00:00' and '13:59:59' "; $it_deadline_text = "13:00~14:00"; break;
		case 6: $sql_search .= " AND it_deadline between '14:00:00' and '14:59:59' "; $it_deadline_text = "14:00~15:00"; break;
		case 7: $sql_search .= " AND it_deadline between '15:00:00' and '15:59:59' "; $it_deadline_text = "15:00~16:00"; break;
		case 8: $sql_search .= " AND it_deadline between '16:00:00' and '16:59:59' "; $it_deadline_text = "16:00~17:00"; break;
		case 9: $sql_search .= " AND it_deadline between '17:00:00' and '17:59:59' "; $it_deadline_text = "17:00~18:00"; break;
		case 10: $sql_search .= " AND (it_deadline between '18:00:00' and '23:59:59' or it_deadline between '00:00:00' and '08:59:59') "; $it_deadline_text = "기타/시간미등록"; break;
		default: $sql_search .= " AND it_deadline between '09:00:00' and '09:59:59' "; $it_deadline_text = "09:00~10:00"; break;
	}
	$qstr .="&amp;it_deadline=".$_REQUEST["it_deadline"];
}else{
	$it_deadline_text = "전체";
}

if($_REQUEST["it_is_direct_delivery"] != ""){//위탁여부
	$sql_search .= " AND it_is_direct_delivery = '{$_REQUEST["it_is_direct_delivery"]}' ";
	$qstr .="&amp;it_is_direct_delivery=".$_REQUEST["it_is_direct_delivery"];
	$it_is_direct_delivery_text = ($_REQUEST["it_is_direct_delivery"] == "0")?"N":"Y";
}else{
	$it_is_direct_delivery_text = "전체";
}


if($_REQUEST["it_direct_delivery_partner"] != ""){//파트너
	if($_REQUEST["it_direct_delivery_partner"] == "no_reg"){//미등록
		$sql_search .= " AND it_direct_delivery_partner = '' ";
		$it_direct_delivery_partner_text = "미등록";
	}else{
		$sql_search .= " AND it_direct_delivery_partner = '{$_REQUEST["it_direct_delivery_partner"]}' ";
		$mb2 = get_member($_REQUEST['it_direct_delivery_partner']);
		$it_direct_delivery_partner_text = $mb2["mb_name"];
	}
	$qstr .="&amp;it_direct_delivery_partner=".$_REQUEST["it_direct_delivery_partner"];
}else{
	$it_direct_delivery_partner_text = "전체";
}

if($_REQUEST["it_sc_type"] != ""){//배송비 유형
	$sql_search .= " AND it_sc_type = '{$_REQUEST["it_sc_type"]}' ";
	$qstr .="&amp;it_sc_type=".$_REQUEST["it_sc_type"];
	switch($_REQUEST["it_sc_type"]){
		case 0: $it_sc_type_text = "쇼핑몰 기본 설정";break;
		case 1: $it_sc_type_text = "무료 배송";break;
		case 5: $it_sc_type_text = "홀수/짝수 배송";break;
	}
}else{
	$it_sc_type_text = "전체";
}

if($_REQUEST["it_default_warehouse"] != ""){//출하창고
	if($_REQUEST["it_default_warehouse"] == "미지정"){//미지정
		$sql_search .= " AND it_default_warehouse = '' ";
		$it_default_warehouse_text = "미정";
	}else{
		$sql_search .= " AND it_default_warehouse = '{$_REQUEST["it_default_warehouse"]}' ";
		$it_default_warehouse_text = $_REQUEST["it_default_warehouse"];
	}
	$qstr .="&amp;it_default_warehouse=".$_REQUEST["it_default_warehouse"];
}else{
	$it_default_warehouse_text = "전체";
}



//if ($sel_field == "")  $sel_field = "all";

$sql_common = " from {$g5['g5_shop_item_table']} a ,
                     {$g5['g5_shop_category_table']} b
               where (a.ca_id = b.ca_id";
// if ($is_admin != 'super')
//     $sql_common .= " and b.ca_mb_id = '{$member['mb_id']}'";
$sql_common .= ") ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];



if (!$sst) {
	$sst = "it_time";
    $sod = "desc";
}



$sql_order = "order by  $sst $sod ";

$sql  = " select *, CASE WHEN a.it_update_time = '0000-00-00 00:00:00' THEN a.it_time ELSE a.it_update_time END AS it_update_time2
           $sql_common
           $sql_order"; 
$result = sql_query($sql, true);

//$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;
$qstr .= '&amp;page='.$page.'&amp;page_rows='.$page_rows;


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// APMS - 2014.07.25
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');
$flist = array();
$flist = apms_form(1,0);
$warehouse_list = get_warehouses();
$i = 0;
while ($row = sql_fetch_array($result)) {
    $num = $total_count - $i ;
    $bg = 'bg'.($i%2);
	switch($row["it_sc_type"]){
			case "0":
			$it_sc_type = "쇼핑몰기본설정";
			break;
			case "1": case "3":
			$it_sc_type = "무료배송";
			break;
			case "2":
			$it_sc_type = "조건부무료배송";
			break;
			case "3":
			$it_sc_type = "유료배송";
			break;
			case "4":
			$it_sc_type = "수량별유료배송";
			break;
			case "5":
			$it_sc_type = "홀수/짝수배송";
			break;
			case "6":
			$it_sc_type = "포장수량무료배송";
			break;
		}
		$mb = get_member($row['it_direct_delivery_partner']);
		$prodSupYn =($row["prodSupYn"] == "Y") ? "유통" : "비유통";
		$ca_id = (substr($row["ca_id"],0,2) == "70")?"비급여":(substr($row["ca_id"],0,2) == "80")?"보장구":"급여";
		$it_is_direct_delivery =($row["it_is_direct_delivery"] == 0)?"":"Y";
  $data[] = [
    $num,
	$row["it_id"],
	$prodSupYn,
	$ca_id,  
	$row["it_thezone2"],
    $row["ca_name"],
	$row["it_name"],
	number_format($row["it_price"]),
	$row["it_default_warehouse"],
	$it_sc_type,
	$it_is_direct_delivery,
	$mb["mb_name"],
	$row["it_direct_delivery_partner"],
	$row["it_admin_memo"],
	$row["it_deadline"],
	substr($row["it_time"],0,10),
	$row["it_update_time2"],
  ];
  $i++;
}
$search_where = "유통구분-".$prodSupYn_text.", 급여구분-".$gubun_text.", 마감시간-".$it_deadline_text.", 위탁여부-".$it_is_direct_delivery_text.", 파트너-".$it_direct_delivery_partner_text.", 배송비유형-".$it_sc_type_text.", 출하창고-".$it_default_warehouse_text.", 검색어-(".$sel_field_text.")".$search_text;

$title = ['No.','상품관리코드','유통구분','급여구분','품목코드','카테고리','상품명','판매가격','출하창고','배송비유형','위탁사용','파트너명','회원ID','관리자 메모','주문마감시간','상품등록일자','상품수정일자'];
// 엑셀 라이브러리 설정
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();
$excel->setActiveSheetIndex(0)->mergeCells('A1:Q1');
$excel->setActiveSheetIndex(0)->mergeCells('J3:Q3');

// 시트 네임
$sheet->setTitle("직배송 상품관리");

$last_row = count($data) + 1;
if($last_row < 2) $last_row = 2;
// 전체 테두리 지정
$sheet -> getStyle(sprintf("A4:Q%s", ($last_row+3))) -> getBorders() -> getAllBorders() -> setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
// 전체 가운데 정렬
$sheet -> getStyle(sprintf("A1:Q%s", ($last_row+3))) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//A4 기준 틀고정
$sheet->freezePane('A5');
// 열 높이
for($i = 2; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(-1);
}
$sheet->getStyle("A4:Q4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle('A1')->getFont()->setSize(15);
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle("A4:Q4")->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$excel->setActiveSheetIndex(0)->setCellValue('A1', "직배송 상품관리"); 
$sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$excel->setActiveSheetIndex(0)->setCellValue('A3', date("Y-m-d"));
$sheet->getStyle('J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->setCellValue('J3', "검색 : ".$search_where);
$sheet->fromArray($title,NULL,'A4');
$sheet->fromArray($data,NULL,'A5');

//텍스트 크기에 맞춰 자동으로 크기를 조정한다.
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(10);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(10);
$sheet->getColumnDimension('F')->setWidth(30);
$sheet->getColumnDimension('G')->setWidth(50);
$sheet->getColumnDimension('H')->setWidth(10);
$sheet->getColumnDimension('I')->setWidth(30);
$sheet->getColumnDimension('J')->setWidth(25);
$sheet->getColumnDimension('K')->setWidth(10);
$sheet->getColumnDimension('L')->setWidth(30);
$sheet->getColumnDimension('M')->setWidth(20);
$sheet->getColumnDimension('N')->setWidth(40);
$sheet->getColumnDimension('O')->setWidth(15);
$sheet->getColumnDimension('P')->setWidth(15);
$sheet->getColumnDimension('Q')->setWidth(20);


header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"직배송_상품관리_".date("Ymd").".xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');

?>
