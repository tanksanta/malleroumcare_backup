<?php
$sub_menu = '400490';
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

// 데이터 처리
$data = [];


// 검색처리
$select = array();
$where = array();

if (!$fr_date) $fr_date = "";
if (!$to_date) $to_date = "";

$sel_stat = isset($_GET['sel_stat']) ? get_search_string($_GET['sel_stat']) : '';
$od_release = isset($_GET['od_release']) ? get_search_string($_GET['od_release']) : '0';
$fr_date = isset($_GET['fr_date']) ? get_search_string($_GET['fr_date']) : '';
$to_date = isset($_GET['to_date']) ? get_search_string($_GET['to_date']) : '';

$penId = isset($_GET['penId']) ? get_search_string($_GET['penId']) : '';
$search = isset($_GET['search']) ? get_search_string($_GET['search']) : '';
$sel_field = isset($_GET['sel_field']) && in_array($_GET['sel_field'], array('od_penNm', 'od_penLtmNum','mb_id','od_name','od_b_name','od_b_name2','od_b_id','od_d_addr','od_b_tel','od_b_hp','all','order_send_id')) ? $_GET['sel_field'] : '';





if($sel_stat !="" && $sel_stat != "all"){
	$where[] = " od_status = '$sel_stat' ";
}else{

}

$od_release_text = "생성일자";
$days_text = "전체";
if($fr_date != "" || $to_date != ""){//날짜 검색 조건이 있을 경우
	$where_od = "od_time";	
		
	if($to_date == ""){//시작 날짜만 있을 경우 >=
		$where[] = " $where_od >= '$fr_date 00:00:00' ";
	}elseif($fr_date == ""){//종료 날짜만 있을 경우 <=
		$where[] = " $where_od <= '$to_date 23:59:59' ";
	}else{// 둘다 있을 경우 between
		$where[] = " $where_od between '$fr_date 00:00:00' and '$to_date 23:59:59' "; 
	}
	$days_text = $fr_date."~".$to_date;
}

// 정렬 순서
$sql_order = ' ORDER BY ';
$index_order = '';
$index_order = 'DESC';
$sql_order .= 'O.od_time ' . $index_order;

//$select[] = ' m.mb_id ';
$select[] = ' O.* ';
$select[] = ' M.mb_entId as entId';
$sql_join = ' LEFT outer JOIN g5_member as M on M.mb_id = O.mb_id ';
//$sql_group = " GROUP BY E.dc_id";
$where2 = "";
$search_text = ($search == "")?"없음":$search;
$sel_text = "전체";
if ($search != '' && $sel_field != '') {
	if($sel_field == "all"){
		$where[] = " (O.order_send_id like '%{$search}%' or O.mb_id like '{$search}' or O.od_name like '%{$search}%' or O.od_b_id like '%{$search}%' or O.od_b_name like '%{$search}%' or O.od_b_tel like '%{$search}%' or O.od_b_hp like '%{$search}%' or O.od_b_addr1 like '%{$search}%' or O.od_b_addr2 like '%{$search}%' or O.od_b_addr3 like '%{$search}%' or O.od_b_name2 like '%{$search}%' or O.od_penNm like '%{$search}%'  or O.od_penLtmNum like '%{$search}%') ";	
	}elseif($sel_field == "od_b_addr"){
	  $where[] = " (O.od_b_addr1 like '%{$search}%' or O.od_b_addr2 like '%{$search}%') or O.od_b_addr3 like '%{$search}%') ";
		$sel_text = "배송지";
	}else{
	  $where[] = " $sel_field like '%{$search}%' ";
	  switch($sel_field){//계약상태
		case "order_send_id":
			$sel_text = "주문번호";
		break;
		case "mb_id":
			$sel_text = "사업소ID";
		break;
		case "od_name":
			$sel_text = "사업소명";
		break;
		case "od_b_id":
			$sel_text = "구매자ID";
		break;
		case "od_b_name":
			$sel_text = "구매자명";
		break;
		case "od_b_tel":
			$sel_text = "구매자연락처";
		break;
		case "od_penNm":
			$sel_text = "수급자명";
		break;
		case "od_penLtmNum":
			$sel_text = "수급자번호";
		break;
		case "od_b_addr":
			$sel_text = "배송지";
		break;
		case "od_b_name2":
			$sel_text = "수령인이름";
		break;
		case "od_b_hp":
			$sel_text = "수령인연락처";
		break;
		default:
			$sel_text = "전체";
		break;
	  }
	}
}

//검색조건
switch($sel_stat){//계약상태
	case "all":
		$stat_text = "전체";
	break;
	case "승인대기":
		$stat_text = "주문승인대기";
	break;
	case "승인완료":
		$stat_text = "주문승인완료";
	break;
	case "결제완료":
		$stat_text = "결제완료";
	break;
	case "주문완료":
		$stat_text = "주문완료";
	break;
	case "출고완료":
		$stat_text = "출고완료";
	break;
	case "작성완료":
		$stat_text = "계약서작성완료";
	break;
	case "서명완료":
		$stat_text = "계약서서명완료";
	break;
	case "주문취소":
		$stat_text = "주문취소";
	break;
	default:
		$stat_text = "전체";
	break;
}
$search_where = "상태-".$stat_text.",기간구분-".$od_release_text.",기간-".$days_text.",검색어-(".$sel_text.")".$search_text;

// select 배열 처리
$sql_select = implode(', ', $select);

// where 배열 처리
$sql_where = " WHERE 1 ";//" WHERE E.entId = '{$entId}' ";
if($where) {
  $sql_where .= ' AND '.implode(' AND ', $where);
}

$sql_from = " FROM `g5_shop_order_api` O";
$total_count = sql_fetch("SELECT COUNT(R.order_send_id) AS cnt FROM (SELECT O.order_send_id" . $sql_from . $sql_join . $sql_where . $sql_group . ') R')['cnt'];

$page_rows = isset($_POST['page_rows']) ? get_search_string($_POST['page_rows']) : $config['cf_page_rows'];
$total_page = ceil($total_count / $page_rows); // 전체 페이지 계산
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$result = sql_query("SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . $sql_group . $sql_order);
$i = 0;
while ($row = sql_fetch_array($result)) {
	switch($row["relation_code"]){
			case "0":
			$relation_code = "본인";
			break;
			case "1": 
			$relation_code = "가족";
			break;
			case "2":
			$relation_code= "친족";
			break;
			case "3":
			$relation_code = "기타";
			break;
			default : $relation_code = "본인";
	}		
	$od_b_addr = ($row["od_b_addr1"] != "")?mb_substr($row["od_b_addr1"],0,6)."*************":"";
			$data2 = array();
			$send_data = [];
			$send_data["penNm"] = $row["od_penNm"];
			$send_data["penLtmNum"] = "L".$row["od_penLtmNum"];//수급자번호에 L 제거 저장
			$send_data["usrId"] = $row["mb_id"];
			$send_data["entId"] = $row["entId"];
			$send_data["pageNum"] = $page;
			$send_data["pageSize"] = 1;
			$send_data["appCd"] = "01";

			$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);
			$list = [];
			foreach($res['data'] as $data2) {
			  $checklist = ['penRecGraCd', 'penTypeCd', 'penExpiDtm', 'penBirth'];
			  $is_incomplete = false;
			  foreach($checklist as $check) {
				if(!$data2[$check])
				  $is_incomplete = true;
			  }
			  if(!in_array($data2['penGender'], ['남', '여']))
				$is_incomplete = true;
			  if($data2['penTypeCd'] == '04' && !$data2['penJumin'])
				$is_incomplete = true;
			  if($data2['penExpiDtm']) {
				// 유효기간 만료일 지난 수급자는 유효기간 입력 후 주문하게 함
				$expired_dtm = substr($data2['penExpiDtm'], -10);
				if (strtotime(date("Y-m-d")) > strtotime($expired_dtm)) {
				  $data2['penExpiDtm'] = '';
				  $is_incomplete = true;
				}
			  }
			  $data2['incomplete'] = $is_incomplete;
			}
  
  $data[] = [
    $row["order_send_id"],
	$row["mb_id"],
	$row["od_name"],  
	mb_substr($row["od_penNm"],0,1)."*".mb_substr($row["od_penNm"],-1),
    "L".(substr(str_replace("L","",$row["od_penLtmNum"]),0,2)."******".substr(str_replace("L","",$row["od_penLtmNum"]),8,2)),
	$data2["penTypeNm"],
	$row["od_b_id"],
	mb_substr($row["od_b_name"],0,1)."*".mb_substr($row["od_b_name"],-1),
	substr($row["od_b_tel"],0,6)."****",
	$relation_code,
	$od_b_addr,
	(mb_substr($row["od_b_name2"],0,1)."*".mb_substr($row["od_b_name2"],-1)),	
	substr($row["od_b_hp"],0,6)."****",
	$row["od_time"],
	$row["od_status"]
  ];
  $i++;
}


$title = ['주문번호','사업소ID','사업소명','수급자명','수급자번호','본인부담금율','구매자ID','구매자명','구매자연락처','수급자와관계','배송지','수령인이름','수령인연락처','주문일자','상태'];
// 엑셀 라이브러리 설정
include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();
$excel->setActiveSheetIndex(0)->mergeCells('A1:O1');
$excel->setActiveSheetIndex(0)->mergeCells('I3:O3');

// 시트 네임
$sheet->setTitle("수급자주문관리");

$last_row = count($data) + 1;
if($last_row < 2) $last_row = 2;
// 전체 테두리 지정
$sheet -> getStyle(sprintf("A4:O%s", ($last_row+3))) -> getBorders() -> getAllBorders() -> setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
// 전체 가운데 정렬
$sheet -> getStyle(sprintf("A1:O%s", ($last_row+3))) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//A4 기준 틀고정
$sheet->freezePane('A5');
// 열 높이
for($i = 2; $i <= $last_row; $i++) {
  $sheet->getRowDimension($i)->setRowHeight(-1);
}
$sheet->getStyle("A4:O4")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('cccccc');
$sheet->getStyle('A1')->getFont()->setSize(15);
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle("A4:O4")->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$excel->setActiveSheetIndex(0)->setCellValue('A1', "수급자주문관리"); 
$sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$excel->setActiveSheetIndex(0)->setCellValue('A3', date("Y-m-d"));
$sheet->getStyle('I3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->setCellValue('I3', "검색 : ".$search_where);
$sheet->fromArray($title,NULL,'A4');
$sheet->fromArray($data,NULL,'A5');

//텍스트 크기에 맞춰 자동으로 크기를 조정한다.
$sheet->getColumnDimension('A')->setWidth(30);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(20);
$sheet->getColumnDimension('H')->setWidth(15);
$sheet->getColumnDimension('I')->setWidth(17);
$sheet->getColumnDimension('J')->setWidth(16);
$sheet->getColumnDimension('K')->setWidth(30);
$sheet->getColumnDimension('L')->setWidth(15);
$sheet->getColumnDimension('M')->setWidth(17);
$sheet->getColumnDimension('N')->setWidth(22);
$sheet->getColumnDimension('O')->setWidth(15);


header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"수급자주문관리_".date("Ymd").".xlsx\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');

?>