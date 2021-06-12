<?php
include_once('./_common.php');

if(!$is_member || !$member['mb_id']) json_response(400, '먼저 로그인 하세요.');

$entId = $member['mb_entId'];
if(!$entId) {
	alert('사업소 회원만 접근 가능합니다.');
}

if(!$selected_month) json_response(400, '잘못된 요청입니다.');

$where = "";
$search = get_search_string($search);
if(in_array($searchtype, ['penNm', 'penLtmNum']) && $search) {
	$where = " AND $searchtype LIKE '%$search%' ";
}

// 수급자만 모아보기
if($penId) {
	$where .= " AND penId = '$penId' ";
}

$eform_query = sql_query("
SELECT
	MIN(STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '3'), '%Y-%m-%d')) as start_date,
	SUM(`it_price`) as total_price,
	SUM(`it_price_pen`) as total_price_pen,
	(SUM(`it_price`) - SUM(`it_price_pen`)) as total_price_ent,
	penId, penNm, penLtmNum, penRecGraCd, penRecGraNm, penTypeCd, penTypeNm, penBirth
FROM `eform_document` a
LEFT JOIN `eform_document_item` b
ON a.dc_id = b.dc_id
WHERE
	entId = '$entId'
	AND dc_status = '2'
	$where
	AND
	(
		(
			gubun = '00' AND
			STR_TO_DATE(`it_date`, '%Y-%m-%d') BETWEEN '$selected_month' AND LAST_DAY('$selected_month')
		)
		OR
		(
			gubun = '01' AND
			(
				(STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '3'), '%Y-%m-%d') <= '$selected_month')
				AND
				(STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '-3'), '%Y-%m-%d') >= '$selected_month')
			)
		)
	)
GROUP BY `penId`
ORDER BY `penNm` ASC
");

$total_count = sql_num_rows($eform_query);
$page_rows = $config['cf_page_rows'];
$total_page = ceil($total_count / $page_rows); // 전체 페이지 계산
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$cl_query = sql_query("SELECT * FROM `claim_management` WHERE selected_month = '$selected_month' AND mb_id = '{$member['mb_id']}'");
$cl = [];
while($row = sql_fetch_array($cl_query)) {
	$cl[] = $row;
}

if(! function_exists('column_char')) {
  function column_char($i) {
    return chr( 65 + $i );
  }
}

include_once(G5_LIB_PATH.'/PHPExcel.php');

$headers = array('No.', '수급자 정보', '급여시작일', '급여비용총액', '본인부담금', '청구액', '검증상태');
$widths  = array(10, 40, 15, 15, 15, 15, 10);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);
$rows = array();

for($i=1; $row=sql_fetch_array($eform_query); $i++) {
  $index = $from_record + $i;
  $row['selected_month'] = $selected_month;
  if(strtotime($row['start_date']) < strtotime($selected_month))
    $row['start_date'] = $selected_month;
  
  $row['cl_status'] = '0';
  foreach($cl as $val) {
    if
    (
      $val['penId'] == $row['penId'] &&
      $val['penNm'] == $row['penNm'] &&
      $val['penLtmNum'] == $row['penLtmNum'] &&
      $val['penRecGraCd'] == $row['penRecGraCd'] &&
      $val['penTypeCd'] == $row['penTypeCd']
    ) {
      $row['cl_status'] = $val['cl_status'];
      $row['start_date'] = $val['start_date'];
      $row['total_price'] = $val['total_price'];
      $row['total_price_pen'] = $val['total_price_pen'];
      $row['total_price_ent'] = $val['total_price_ent'];
    }
  }

  $rows[] = array(
    $index,
    "{$row['penNm']}({$row['penLtmNum']} / {$row['penRecGraNm']} / {$row['penTypeNm']})",
    $row['start_date'],
    $row['total_price'],
    $row['total_price_pen'],
    $row['total_price_ent'],
    $row['cl_status'] == '0' ? '대기' : '변경'
  );
}

$data = array_merge(array($headers), $rows);

$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"".date('Y년 m월 청구내역', strtotime($selected_month)).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>