<?php

	include_once("./_common.php");

	auth_check($auth["400400"], "r");
	include_once(G5_LIB_PATH."/PHPExcel.php");
	function column_char($i) { return chr( 65 + $i ); }

    
        $ct_id=$_POST['od_id'];
        $count_number=0;
        $count_od_id="";

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
            if($count_od_id !==$it['od_id']){$count_number++; $count_od_id=$it['od_id']; }
            #바코드
            $stoIdDataList = explode('|',$it['stoId']);
            $stoIdDataList=array_filter($stoIdDataList);
            $stoIdData = implode("|", $stoIdDataList);

            $barcode=[];
            $sendData["stoId"] = $stoIdData;
            $oCurl = curl_init();
			$res = get_eroumcare2(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
            $result_again = $res;
            $result_again =$result_again['data'];

            for($k=0; $k < count($result_again); $k++){
                if($result_again[$k]['prodBarNum']){
                    array_push($barcode,$result_again[$k]['prodBarNum']);
                }
            }
			asort($barcode);
            $barcode = implode(",", $barcode);
            $barcode = $barcode." "; 
            
			//할인적용 단가
			if($od['od_cart_price']){
				$price_d = ($it['ct_price']*$it["ct_qty"]-$it['ct_discount'])/$it["ct_qty"];
			}
			//영세 과세 구분
			$sql_taxInfo = 'select `it_taxInfo` from `g5_shop_item` where `it_id` = "'.$it['it_id'].'"';
			$it_taxInfo = sql_fetch($sql_taxInfo);
			$price_d_p ="";
			$price_d_s ="";
			if($it_taxInfo['it_taxInfo']=="영세"){
				$price_d_p = $price_d*$it['ct_qty'];
				$price_d_s = "0";
			}else{
				$price_d_p = round(($price_d ? $price_d : 0) / 1.1) * $it['ct_qty']; // 공급가액
				$price_d_s = round(($price_d ? $price_d : 0) / 1.1 / 10) * $it['ct_qty']; // 부가세
			}

            $it_name = $it["it_name"];
			
			if($it_name != $it["ct_option"]){
				$it_name .= " [{$it["ct_option"]}]";
			}

            $addr="";
            if($od_b_zip1){$addr= "(".$od_b_zip1.$od_b_zip2.")";}
			$addr = $addr.$od["od_b_addr1"].' '.$od["od_b_addr2"].' '.$od["od_b_addr3"];

			$mb = get_member($it['mb_id']);
			
			//영업담당자
			// $od_sales_manager = get_member($od['od_sales_manager']);
			$sql_manager = "SELECT `mb_manager` FROM `g5_member` WHERE `mb_id` ='".$od['mb_id']."'";
			$result_manager = sql_fetch($sql_manager);
			$od_sales_manager = get_member($result_manager['mb_manager']);
			
			if($it['io_type'])
				$opt_price = $it['io_price'];
			else
				$opt_price = $it['ct_price'] + $it['io_price'];

			$it["opt_price"] = $opt_price;

			$thezone_code = $it['io_thezone'] ? $it['io_thezone'] : $it['it_thezone2'];

			$delivery = '';
			if ($it['ct_delivery_num']) {
				$delivery = '(' . get_delivery_company_step($it['ct_delivery_company'])['name'] . ') ' . $it['ct_delivery_num'];
			}
			
			$date = "출고전";
            if($it["ct_ex_date"] !== "0000-00-00"){
                $date =date("Ymd", strtotime($it["ct_ex_date"]));
            }
			$rows[] = [ 
				$date,  //날짜
				$count_number,
				$mb['mb_thezone'],
				'',
				$od_sales_manager['mb_name'],
				'',
				'',
				'',
				'',
				$od["od_b_name"],
				$addr,
				'',
				'',
				$it['prodMemo'],
				'',
				$thezone_code, // 품목코드
				'',
				'',
				$it["ct_qty"],
				$price_d ? $price_d : 0, // 단가(판매가)
				'',
				$price_d_p, //공급가액
                $price_d_s, //부가세
				$barcode, // 바코드
				$delivery, // 로젠송장번호,
				'통합관리플랫폼', //적요
				'',
			];
		}

    $headers = array("일자", "순서", "거래처코드", "거래처명","담당자", "출하창고", "거래유형","통화", "환율","성명(상호명)", "배송처", "전잔액", "후잔액", "특이사항", "참고사항", "품목코드", "품목명", "규격", "수량", "단가(vat포함)", "외화금액", "공급가액", "부가세", "바코드", "로젠 송장번호", "적요", "생산전표생성");
    $data = array_merge(array($headers), $rows);
    
    $widths  = array(20, 50, 10, 30, 50, 30, 50);
    $header_bgcolor = 'FFABCDEF';
    $last_char = column_char(count($headers) - 1);

    $excel = new PHPExcel();
    // $excel->setActiveSheetIndex(0)
    //     ->getStyle( "A1:${last_char}1" )
    //     ->getFill()
    //     ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    //     ->getStartColor()
    //     ->setARGB($header_bgcolor);

    // $excel->setActiveSheetIndex(0)
    //     ->getStyle( "A:$last_char" )
    //     ->getAlignment()
    //     ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
    //     ->setWrapText(true);

    foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
    $excel->getActiveSheet()->fromArray($data,NULL,'A1');

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"orderexcel-".date("ymd", time()).".xls\"");
    header("Cache-Control: max-age=0");

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
    $writer->save('php://output');

?>