<?php

	include_once("./_common.php");

	auth_check($auth["400400"], "r");
	include_once(G5_LIB_PATH."/PHPExcel.php");
	function column_char($i) { return chr( 65 + $i ); }

    $sql = "
		SELECT *
		FROM g5_shop_order
	";
    $result = sql_query($sql);

    $rows = [];
    for($i=1; $od=sql_fetch_array($result); $i++) 
    {
		$itList = sql_query("
			SELECT *
			FROM g5_shop_cart a LEFT JOIN g5_shop_item b ON ( a.it_id = b.it_id )
			WHERE od_id = '{$od["od_id"]}'
			AND a.ct_delivery_yn = 'Y'
			GROUP BY a.it_id, a.ct_uid
			ORDER BY a.ct_id ASC
		");
		
		for($ii = 0; $it = sql_fetch_array($itList); $ii++){
			$it_name = $it["it_name"];
			
			if($it_name != $it["ct_option"]){
				$it_name .= " [{$it["ct_option"]}]";
			}
			
			$delivery_company_name = "";
			foreach($delivery_companys as $data){
				if($data["val"] == $it["ct_delivery_company"]){
					$delivery_company_name = $data["name"];
				}
			}
			
			$rows[] = [ 
				" {$it["ct_id"]} ",
				" {$od["od_id"]} ",
				date("Y-m-d", strtotime($od["od_time"])),
				$it_name,
				$delivery_company_name,
				$it["ct_delivery_num"],
				$it["ct_delivery_cnt"],
				$it["ct_delivery_price"]
			];
		}
    }

    $headers = array("고유번호", "주문번호", "일자", "품목명[규격]", "택배사", "송장번호", "박스수량", "배송비");
    $data = array_merge(array($headers), $rows);
    
    $widths  = array(15, 20, 15, 50, 30, 30, 20, 30);
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
    header("Content-Disposition: attachment; filename=\"orderdeliveryexcel.xls\"");
    header("Cache-Control: max-age=0");

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
    $writer->save('php://output');

?>