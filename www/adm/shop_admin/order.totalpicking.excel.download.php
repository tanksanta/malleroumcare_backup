<?php

include_once("./_common.php");

auth_check($auth["400400"], "r");
include_once(G5_LIB_PATH."/PHPExcel.php");
function column_char($i) { return chr( 65 + $i ); }

function group_by($column_name, $rows) {
	$result = [];
	$groups = distinct($rows, $column_name);
	foreach($groups as $group) {
		$result[$group] = where($rows, [$column_name=>$group]);
	}
	return $result;
}
function distinct($rows, $column_name) {
        $column_values = [];
        foreach($rows as $row) {
                $column_values[$row[$column_name]] = 1;
        }
        return array_keys($column_values);
}
function where($rows, $params) {
	$result = [];
	foreach($rows as $row) {
		$row_matched = true;
		foreach($params as $column_name => $column_value) {
			if( !array_key_exists($column_name, $row) 
			|| $row[$column_name] != $column_value ) {
				$row_matched = false;
				break;
			}
		}
		if( $row_matched ) $result[] = $row;
	}
	return $result;
}

// $od_id = array($_GET['od_id']);
$ct_ids = $od_id;
$ct_items = [];
$combine_ct_items = [];
for($i = 0; $i < count($ct_ids); $i++) {
    $it = sql_fetch("
        SELECT CA.ca_name, C.it_name, C.ct_qty, C.ct_option
        FROM g5_shop_cart AS C
        LEFT JOIN g5_shop_item as I ON I.it_id = C.it_id
        LEFT JOIN g5_shop_category as CA ON CA.ca_id = I.ca_id
        WHERE C.ct_id = '{$ct_ids[$i]}'
    ");
    $it_name = $it["it_name"];
    if($it_name != $it["ct_option"]){
        $it_name .= "({$it["ct_option"]})";
    } 
    $it["it_name"] = $it_name;
    array_push($ct_items, $it);
}

$items = group_by('it_name', $ct_items);
$items = array_keys($items);
asort($items);

$categories = group_by('ca_name', $ct_items);
$categories = array_keys($categories);
asort($categories);

$rows = [];
foreach($categories as $category) {
    foreach($items as $item) {
        $sum = 0;
        foreach($ct_items as $ct_item) {
            if ($ct_item['ca_name'] == $category && $ct_item['it_name'] == $item) {
                $sum += $ct_item['ct_qty'];
            }
        }
        if ($sum > 0)
            $rows[] = [$category, $item, $sum];
    }
}

// 날짜 표시
$now = time();
$am_or_pm = date('a', $now);
if($am == 'am')
  $date = date('Y/m/d', $now).'  오전 '.date('h:i:s', $now);
else
  $date = date('Y/m/d', $now).'  오후 '.date('h:i:s', $now);
$dates = [$date];

$headers = array("카테고리","품명","합계");
$data = array_merge(array($headers), $rows, [$dates]);

$widths  = array(20, 50, 10);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)
    ->getStyle( "A1:${last_char}1" )
    ->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB($header_bgcolor);

$excel->setActiveSheetIndex(0)
    ->getStyle( "A:$last_char" )
    ->getAlignment()
    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
    ->setWrapText(true);

foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

$merge_cell = "A".(count($rows)+2).":C".(count($rows)+2);
$excel->getActiveSheet()->mergeCells($merge_cell);
// 테두리 처리
$styleArray = array(
    'borders' => array(
      'allborders' => array(
        'style' => PHPExcel_Style_Border::BORDER_THIN
      )
    )
  );
// $excel->getActiveSheet()->getStyle('A0:C'.(count($rows)+1))->applyFromArray($styleArray);
  
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"totalpicking-".date("ymd", time()).".xls\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>
