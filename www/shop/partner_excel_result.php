<?php
include_once('./_common.php');

if (!$is_samhwa_partner && !$is_admin) {
    alert('파트너 회원만 접속하실 수 있습니다.');
}

include_once(G5_LIB_PATH.'/PHPExcel.php');
function column_char($i) { return chr( 65 + $i ); }

$sql = "SELECT 
            `shop_item`.*,
            COUNT(`shop_option`.`io_no`) AS `option_count`,
            GROUP_CONCAT(`shop_option`.`io_id`) AS `options`,
            GROUP_CONCAT(`shop_option`.`io_price`) AS `option_prices`
            FROM `{$g5['g5_shop_item_table']}` AS `shop_item`
            LEFT JOIN `{$g5['g5_shop_item_option_table']}` AS `shop_option`
                ON `shop_option`.`it_id` = `shop_item`.`it_id`";

$where = [];
$type = isset($_POST["type"])?$_POST["type"]:"";
if( $type == "date" )
{
    $fromDate = isset($_POST["fr_date"])?$_POST["fr_date"]:[];
    $toDate = isset($_POST["to_date"])?$_POST["to_date"]:[];
    if( $fromDate ) $where[] = "DATE(`shop_item`.`it_time`) >= '{$fromDate}'";
    if( $toDate ) $where[] = "DATE(`shop_item`.`it_time`) <= '{$toDate}'";
}
elseif( $type == "category" )
{
    $category = isset($_POST["category"])?$_POST["category"]:[];
    $newCategory = [];
    foreach($category as $cat)
    {
        $newCategory[] = "'".$cat."'";
    }
    if( !empty($newCategory) )
    {
        $categoryString = implode(",", $newCategory);
        $where[] = "( `shop_item`.`ca_id` IN ({$categoryString}) OR `shop_item`.`ca_id2` IN ({$categoryString}) OR `shop_item`.`ca_id3` IN ({$categoryString}) )";
    }
}

// 파트너몰 판매 체크 상품만 다운로드 
// $where[] = " it_use = 1 ";
$where[] = " it_use_partner = 1 ";

if( !empty($where) )
{
    $sql .= " WHERE ".implode(" AND ", $where);
}

$sql .= " GROUP BY `shop_item`.`it_id` ORDER BY `shop_item`.`it_id` DESC";

$result = sql_query($sql);

$img_uri_prefix = G5_DATA_URL . '/item/';

$rows = [];
for($i=1; $row=sql_fetch_array($result); $i++) 
{

    // if( $row["option_count"] > 0 )
    // {
    //     $options = explode(",", $row["options"]);
    //     $prices = explode(",", $row["option_prices"]);

    //     foreach($options as $k => $opt )
    //     {
    //         $rows[] = [ 
    //             "",
    //             "", 
    //             $opt, 
    //             "", 
    //             isset($prices[$k])?floatval($row['it_price']) + floatval($prices[$k]):"-",
    //             "", 
    //             "",
    //             "",
    //             ""
    //         ];
    //     }
    // }

    $sql = "SELECT * FROM {$g5['g5_shop_item_option_table']} WHERE it_id = '{$row['it_id']}'";
    $option_result = sql_query($sql);
    $options = '';
    while($option_row = sql_fetch_array($option_result)) {
        $price = $option_row['io_price_partner'] ? $option_row['io_price_partner'] : $option_row['io_price'];
        $price = $price > 0 ? '+' . $price : $price;
        $price = $price < 0 ? '-' . $price : $price;
        $options .= str_replace(chr(30), " > ", $option_row['io_id']) . ' (' . $price . '원)' . chr(10);
    }
    

    $rows[] = [ 
        $row['it_id'],
        $row['it_model'], 
        $row['it_name'], 
        $options, 
        $row['it_basic'], 
        $row['it_price'],
        $img_uri_prefix . $row['it_img1'], 
        $img_uri_prefix . $row['it_img2'],
        $img_uri_prefix . $row['it_img3'],
        $row['it_explan']
    ];
}

$headers = array('번호', '모델명', '제품명', '옵션명', '간략설명', '소비자가(옵션가)', '확대이미지', '기본이미지', '리스트이미지', '상품상세설명');
$data = array_merge(array($headers), $rows);

$widths  = array(15, 20, 20, 20, 30, 20, 50, 50, 50, 120);
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
header("Content-Disposition: attachment; filename=\"productexcel-".date("ymd", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>