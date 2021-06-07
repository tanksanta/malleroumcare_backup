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
            $barcode2=[];
            $barcode_string="";
            $y = 0;  
            foreach($barcode as $key=>$val)  
            {  
                $new_key = $y;  
                $barcode2[$new_key] = $val;  
                $y++;  
            }
            $k=0;
            $k2=0;
            $comma="";
            for($y=0; $y<=count($barcode2); $y++){


                if($barcode2[$y]-1 == $barcode2[$y-1]){
                    $k++;
                    //같음;
                }else{
                    //같지않음;
                    if($y==0){
                            #처음
                            $k=0;
                            $barcode_string .= $barcode2[$y];
                            $start=false;
                    }else{
                        $k2++;
                        if($y == count($barcode2)){
                            #마지막
                            //중간 끝 바코드가 현재 바코드와 같은경우
                            if(substr($barcode_string, -12)==$barcode2[$y-1]){
                                continue;
                            }
                            if($k>0){
                                $barcode_string.="-".($barcode2[$y-1]);
                                continue;
                            }else{
                                //끝문자가 ","로 끝나는 경우
                                if(substr($barcode_string, -1)==","){
                                    $barcode_string .= ($barcode2[$y-1]);
                                    continue;
                                }else{
                                    $barcode_string.=",".($barcode2[$y-1]);
                                    continue;
                                }
                            }
                        }else{
                            #중간
                            $k=0;
                            //처음과 같은경우
                            if($barcode_string==$barcode2[$y-1]){
                                $barcode_string.=",";
                                continue;
                            }
                            //끝문자가 ","로 끝나는 경우
                            if(substr($barcode_string, -1)==","){
                                $barcode_string .= ($barcode2[$y-1]);
                                continue;
                            }
                            //중간 끝 바코드가 현재 바코드와 같은경우
                            if(substr($barcode_string, -12)==$barcode2[$y-1]){
                                $barcode_string.=",";
                                continue;
                            }
                            $barcode_string .= "-".($barcode2[$y-1]).", ".$barcode2[$y];
                        }
                    }
                }
            }

            //끊김없이 연속인 경우
            if($k2 == 1){
                $barcode_string=$barcode2[0]."-".$barcode2[count($barcode2)-1];
            }
            //바코드가 한개인 경우
            if(count($barcode2) == 1 ){
                $barcode_string=$barcode2[0];
            }
            $barcode_string.=" "; 


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
			//송장번호 출력
			if ($it['ct_delivery_num']) {
				$delivery = '(' . get_delivery_company_step($it['ct_delivery_company'])['name'] . ') ' . $it['ct_delivery_num'];
			}
			//합포 송장번호 출력
            if ($it['ct_combine_ct_id']) {
                $sql_ct ="select `ct_delivery_company`, `ct_delivery_num` from g5_shop_cart where `ct_id` = '".$it['ct_combine_ct_id']."'";
                $result_ct = sql_fetch($sql_ct);
                $delivery = '(' . get_delivery_company_step($result_ct['ct_delivery_company'])['name'] . ') ' . $result_ct['ct_delivery_num'];
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
				$barcode_string, // 바코드
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