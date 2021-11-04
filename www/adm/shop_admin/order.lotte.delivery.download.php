<?php

include_once("./_common.php");

auth_check($auth["400400"], "r");
include_once(G5_LIB_PATH."/PHPExcel.php");
function column_char($i) { return chr( 65 + $i ); }

// $od_id = array($_GET['od_id']);
$ct_ids = $od_id;

// 합포 상품들 검색
$combine_ct_items = [];
foreach($ct_ids as $ct_id) {
    $it = sql_fetch("
      SELECT cart.*
      FROM g5_shop_cart as cart
      WHERE cart.ct_id = '{$ct_id}'
    ");
    if ($it['ct_combine_ct_id']) {
        array_push($combine_ct_items, $it['ct_combine_ct_id']);
        $result = sql_query("
            SELECT cart.* 
            FROM g5_shop_cart as cart 
            WHERE cart.ct_combine_ct_id = '{$it['ct_combine_ct_id']}'
        ");
        while($row = sql_fetch_array($result)) {
            array_push($combine_ct_items, $row['ct_id']);
        }
    }
}
$ct_ids = array_merge($ct_ids, $combine_ct_items);
$ct_ids = array_values(array_unique($ct_ids));

$ct_items = [];
$combine_ct_items = [];
for($i = 0; $i < count($ct_ids); $i++) {
    $it = sql_fetch("
      SELECT cart.*, item.it_thezone2
      FROM g5_shop_cart as cart
      INNER JOIN g5_shop_item as item ON cart.it_id = item.it_id
      WHERE cart.ct_id = '{$ct_ids[$i]}'
      ORDER BY cart.ct_id ASC
    ");

    $od = sql_fetch(" 
      SELECT * FROM g5_shop_order WHERE od_id = '".$it['od_id']."'
    ");

    $it['od_info'] = $od;
    $it_name = $it["it_name"];
    if($it_name != $it["ct_option"]){
        $it_name .= "({$it["ct_option"]})";
    } 
    $it["it_name"] = $it_name . "*" . $it["ct_qty"] . "개";

    if ($it['ct_combine_ct_id'] == NULL) {
        array_push($ct_items, $it);
    }
    else {
        array_push($combine_ct_items, $it);
    }
}

if (count($combine_ct_items) > 0) {
    foreach($combine_ct_items as $combine_item) {
        foreach($ct_items as $key => $item) {
            if ($combine_item['ct_combine_ct_id'] == $item['ct_id']) {     
                $it_name = $item['it_name'] . " / " . $combine_item['it_name'];
                $ct_items[$key]['it_name'] = $it_name;

                $box_cnt = intval($item['ct_delivery_cnt']);
                $ct_items[$key]['ct_delivery_cnt'] = $box_cnt + intval($combine_item['ct_delivery_cnt']);
            }
        }
    }
}

$rows = [];
foreach($ct_items as $it) {
    $od = $it['od_info'];
    $rows[] = [ 
        $od["od_b_name"],
        "",
        $od["od_b_addr1"]." ".$od["od_b_addr2"],
        $od["od_b_tel"],
        "",
        $it["ct_delivery_cnt"], //박스수량
        $od["od_send_cost"],
        "선불",
        $it['it_name'],
        "",
        $od["od_memo"],
        "",
        $od["od_zip1"].$od["od_zip2"]
    ];    
}
// return false;

$headers = array("받는사람","불필요항목","주소","전화번호1","불필요항목","수량(A타입)","운임","운임구분","상품명1","불필요항목","배송메시지","불필요항목","우편번호");
$data = array_merge(array($headers), $rows);

$widths  = array(20, 10, 50, 20, 10, 10, 10, 10, 20, 10, 20, 10, 10);
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

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"lottedelivery-".date("ymd", time()).".xls\"");
header("Cache-Control: max-age=0");
header('Set-Cookie: fileDownload=true; path=/');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>
