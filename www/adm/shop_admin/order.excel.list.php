<?php

	include_once("./_common.php");

	auth_check($auth["400400"], "r");
	include_once(G5_LIB_PATH."/PHPExcel.php");
	function column_char($i) { return chr( 65 + $i ); }

	$od_id = [];
	for($i = 0; $i < count($_POST["od_id"]); $i++){
		if($_POST["od_id"][$i]){
			array_push($od_id, "'{$_POST["od_id"][$i]}'");
		}
	}

	$od_id = implode(",", $od_id);

    $sql = "
		SELECT *
		FROM g5_shop_order
		WHERE od_id IN ( {$od_id} )
	";
    $result = sql_query($sql);

    $rows = [];
    for($i=1; $od=sql_fetch_array($result); $i++) 
    {
		$itList = sql_query("
			SELECT *
			FROM g5_shop_cart
			WHERE od_id = '{$od["od_id"]}'
			ORDER BY ct_id ASC
		");
		
		for($ii = 0; $it = sql_fetch_array($itList); $ii++){
			$it_name = $it["it_name"];
			
			if($it_name != $it["ct_option"]){
				$it_name .= " [{$it["ct_option"]}]";
			}
			
			$rows[] = [ 
				date("Y-m-d", strtotime($od["od_time"]))."-".($i),
				$it_name,
				$it["ct_qty"],
                $it_name." / ".$it["ct_qty"].' EA',
				$od["od_b_name"],
				$od["od_b_addr1"],
				$od["od_b_tel"],
				$it["prodMemo"]
			];
		}
    }

    $headers = array("일자-No.", "품목명[규격]", "수량", "품목&수량","성함(상호명)", "배송처", "연락처", "적요");
    $data = array_merge(array($headers), $rows);
    
    $widths  = array(20, 50, 10, 30, 50, 30, 50);
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
    header("Content-Disposition: attachment; filename=\"orderexcel-".date("ymd", time()).".xls\"");
    header("Cache-Control: max-age=0");

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
    $writer->save('php://output');

?>