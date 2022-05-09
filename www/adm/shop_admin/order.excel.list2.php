<?php

	include_once("./_common.php");

	auth_check($auth["400400"], "r");
	include_once(G5_LIB_PATH."/PHPExcel.php");
	function column_char($i) { return chr( 65 + $i ); }


    $od_id=$_POST['od_id'][0];

    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
	$od = sql_fetch($sql);
	$prodList = [];
	$prodListCnt = 0;
	$prodListCnt2 = 0;
	$deliveryTotalCnt = 0;
	$result_again=[];
	if (!$od['od_id']) {
		alert("해당 주문번호로 주문서가 존재하지 않습니다.");
	} else {
        $sto_imsi="";
        $sql_ct = " select `stoId` from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
        $result_ct = sql_query($sql_ct);
        while($row_ct = sql_fetch_array($result_ct)) {
            $sto_imsi .=$row_ct['stoId'];
        }
        $stoIdDataList = explode('|',$sto_imsi);
        $stoIdDataList=array_filter($stoIdDataList);
        $stoIdData = implode("|", $stoIdDataList);
        $sendData = [];
        $sendData["stoId"] = $stoIdData;
        $res = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
        $result_again = $res["data"];
	}
	// 상품목록
	$sql = " select a.ct_id,
					a.it_id,
					a.it_name,
					a.cp_price,
					a.ct_notax,
					a.ct_send_cost,
					a.ct_sendcost,
					a.it_sc_type,
					a.pt_it,
					a.pt_id,
					b.ca_id,
					b.ca_id2,
					b.ca_id3,
					b.pt_msg1,
					b.pt_msg2,
					b.pt_msg3,
					a.ct_status,
					b.it_model,
					b.it_outsourcing_use,
					b.it_outsourcing_company,
					b.it_outsourcing_manager,
					b.it_outsourcing_email,
					b.it_outsourcing_option,
					b.it_outsourcing_option2,
					b.it_outsourcing_option3,
					b.it_outsourcing_option4,
					b.it_outsourcing_option5,
					a.pt_old_name,
					a.pt_old_opt,
					a.ct_uid,
					a.prodMemo,
					a.prodSupYn,
					a.ct_qty,
					a.ct_stock_qty,
					b.it_img1,
					a.ct_delivery_company,
					a.ct_delivery_num
			  from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
			  where a.od_id = '$od_id'
			  group by a.it_id, a.ct_uid
			  order by a.ct_id ";

	$result = sql_query($sql);

	$carts = array();
	$cate_counts = array();

	$od_cart_count = 0;
	for($i=0; $row=sql_fetch_array($result); $i++) {

		$cate_counts[$row['ct_status']] += 1;

		// 상품의 옵션정보
		$sql = " select ct_id, mb_id, it_id, ct_price, ct_point, ct_qty, ct_stock_qty, ct_barcode, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, ct_sendcost, io_type, io_price, pt_msg1, pt_msg2, pt_msg3, ct_discount, ct_uid
						, ( SELECT prodSupYn FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodSupYn
						, prodMemo
					from {$g5['g5_shop_cart_table']} MT
					where od_id = '{$od['od_id']}'
						and it_id = '{$row['it_id']}'
						and ct_uid = '{$row['ct_uid']}'
					order by io_type asc, ct_id asc ";
		$res = sql_query($sql);

		$row['options_span'] = sql_num_rows($res);

		$row['options'] = array();
		for($k=0; $opt=sql_fetch_array($res); $k++) {

			$opt_price = 0;

			if($opt['io_type'])
				$opt_price = $opt['io_price'];
			else
				$opt_price = $opt['ct_price'] + $opt['io_price'];

			$opt["opt_price"] = $opt_price;
			$od_cart_count += $opt["ct_qty"];

			// 소계
			$opt['ct_price_stotal'] = $opt_price * $opt['ct_qty'] - $opt['ct_discount'];
			$opt['ct_point_stotal'] = $opt['ct_point'] * $opt['ct_qty'] - $opt['ct_discount'];

			if($opt["prodSupYn"] == "Y"){
				$opt["ct_price_stotal"] -= ($opt["ct_stock_qty"] * $opt_price);
			}

			$row['options'][] = $opt;
		}


		// 합계금액 계산
		$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * (ct_qty - ct_stock_qty)))) as price,
						SUM(ct_qty) as qty,
						SUM(ct_discount) as discount,
						SUM(ct_send_cost) as sendcost
					from {$g5['g5_shop_cart_table']}
					where it_id = '{$row['it_id']}'
						and od_id = '{$od['od_id']}'
						and ct_uid = '{$row['ct_uid']}'";
		$sum = sql_fetch($sql);

		$row['sum'] = $sum;

		$carts[] = $row;
	}
			// [주문자 ] [ 주문일시] [상품(옵션명)] [ 바코드  ]
			$headers = array("[주문자]", "[주문일시]", "[상품(옵션명)]", "[바코드]");
			$widths  = array(20, 20, 40, 30, 30);
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

			foreach($widths as $i => $w){
				$excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
			}

			$excel->getActiveSheet()->setCellValueExplicit('A1' , '[주문자]', PHPExcel_Cell_DataType::TYPE_STRING);
			$excel->getActiveSheet()->setCellValueExplicit('B1' , '[주문일시]', PHPExcel_Cell_DataType::TYPE_STRING);
			$excel->getActiveSheet()->setCellValueExplicit('C1' , '[상품(옵션명)]', PHPExcel_Cell_DataType::TYPE_STRING);
			$excel->getActiveSheet()->setCellValueExplicit('D1' , '[바코드]', PHPExcel_Cell_DataType::TYPE_STRING);



            if($od["od_name"]){  $name_v=$od["od_name"];}else{$name_v="";} //수급자 이름
            $date_v=date("y-m-d(H:i)", strtotime($od["od_time"]));     //날짜

            $count_plus=0;
            $array=[];
            for($i = 0; $i < count($carts); $i++){
                $option_v="";
                $product_v=stripslashes($carts[$i]["it_name"]); //상품명
                $options = $carts[$i]["options"];
                for($k = 0; $k < count($options); $k++){

                    if($carts[$i]["it_name"] != $options[$k]["ct_option"]){
                        $option_v="(".$options[$k]["ct_option"].")";    //옵션명
                    }
                    for($b = 0; $b< $options[$k]["ct_qty"]; $b++){
                        $barcode_num=$prodList[$b]["prodBarNum"];   //바코드
                        $rows[] =[
                            "date" => $date_v,
                            "product" => $product_v.$option_v,
                            "barcode_num" => $barcode_num,
                        ];
                        $prodListCnt++;
                    }
                }
            }

			//수급자 바코드가 없을때 통신한 바코드로 전환
			for($i=0;$i<count($rows);$i++){
				if(!$rows[$i]['barcode_num'])
                // echo print_r($result_again[$i]).'<br><br><br><br>';
                $sql_i = "select `it_name` from `g5_shop_item` where `it_id` = '".$result_again[$i]['prodId']."'";
                $result_i = sql_fetch($sql_i);
                if($result_again[$i]['prodColor']) $result_again[$i]['prodColor']="[".$result_again[$i]['prodColor']."]";
                if($result_again[$i]['prodSize']) $result_again[$i]['prodSize']="[".$result_again[$i]['prodSize']."]";
				$rows[$i]['product']=$result_i['it_name']." ".$result_again[$i]['prodColor']." ".$result_again[$i]['prodSize'];
				$rows[$i]['barcode_num']=$result_again[$i]['prodBarNum'];
				$rows[$i]['stoId']=$result_again[$i]['stoId'];
			}
            if($rows[0]['stoId']){
                $marks = array();
                foreach ($rows as $key => $row)
                {
                    $marks[$key] = $row['stoId'];
                }
                $marks2 = array();
                foreach ($rows as $key => $row)
                {
                    $marks2[$key] = $row['barcode_num'];
                }

                array_multisort(
                    $marks2, SORT_ASC, $rows,
                    $marks, SORT_DESC, $rows
                );
            }
            for($i=0;$i<count($rows);$i++){
                $count_plus=$i+2;//엑셀카운트
                $excel->getActiveSheet()->setCellValueExplicit('A'.$count_plus , $rows[$i]['name'] , PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('B'.$count_plus ,$rows[$i]['date'], PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('C'.$count_plus , $rows[$i]['product'], PHPExcel_Cell_DataType::TYPE_STRING);
                $excel->getActiveSheet()->setCellValueExplicit('D'.$count_plus , $rows[$i]['barcode_num'], PHPExcel_Cell_DataType::TYPE_STRING);
            }

			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"orderexcel-".date("ymd", time()).".xls\"");
			header("Cache-Control: max-age=0");
			$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
			$writer->save('php://output');





?>
