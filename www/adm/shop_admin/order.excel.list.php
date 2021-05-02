<?php

	include_once("./_common.php");

	auth_check($auth["400400"], "r");
	include_once(G5_LIB_PATH."/PHPExcel.php");
	function column_char($i) { return chr( 65 + $i ); }

        $ct_id=$_POST['od_id'];
        for($ii = 0; $ii < count($ct_id); $ii++){

            $it = sql_fetch("
                SELECT cart.*, item.it_thezone2
                FROM g5_shop_cart as cart
                INNER JOIN g5_shop_item as item ON cart.it_id = item.it_id
                WHERE cart.ct_id = '{$ct_id[$ii]}'
                ORDER BY cart.ct_id ASC
            ");
        
            $od = sql_fetch(" 
                SELECT * FROM g5_shop_order WHERE od_id = '".$it['od_id']."'
            ");


			$it_name = $it["it_name"];
			
			if($it_name != $it["ct_option"]){
				$it_name .= " [{$it["ct_option"]}]";
			}
            $addr="";
            if($od_b_zip1){$addr= "(".$od_b_zip1.$od_b_zip2.")";}
			$addr = $addr.$od["od_b_addr1"].' '.$od["od_b_addr2"].' '.$od["od_b_addr3"];
			$rows[] = [ 
				date("Y-m-d", strtotime($od["od_time"]))."-".($i),
				$it_name,
				$it["ct_qty"],
                $it_name." / ".$it["ct_qty"].' EA',
				$od["od_b_name"],
				$addr,
				$od["od_b_tel"],
				$od["od_b_hp"],
				$it["prodMemo"],
				$od["od_memo"]
			];
		}

    $headers = array("일자-No.", "품목명[규격]", "수량", "품목&수량","성함(상호명)", "배송처", "연락처","휴대폰", "적요","배송지요청사항");
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