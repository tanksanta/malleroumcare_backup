<?php

	include_once("./_common.php");
	include_once(G5_LIB_PATH."/PHPExcel.php");

    if ($_GET['it_ids']) {
        $it_ids = stripslashes($_GET['it_ids']);
        $sql ="select *, b.ca_name from `g5_shop_item` a left outer join `g5_shop_category` b on a.ca_id =b.ca_id where a.it_id IN ({$it_ids}) order by `it_id` desc";
    }
    else {
        $sql ="select *, b.ca_name from `g5_shop_item` a left outer join `g5_shop_category` b on a.ca_id =b.ca_id order by `it_id` desc";
    }
    $result = sql_query($sql);



    while ($row=sql_fetch_array($result)) {
        
        $pt_it=$row['pt_it'] == "1" ? "일반상품(배송가능)" : "컨텐츠상품(배송불가)"; //상품종류
        $prodSupYn=$row['prodSupYn'] == "Y" ? "유통" : "비유통"; //유통여부
        $it_use=$row['it_use'] == "1" ? "가능" : "불가능"; //아이템 사용여부
        $it_rental_use_persisting_year=$row['it_rental_use_persisting_year'] == "1" ? "사용" : "미사용"; //대여사용여부
        //묶음할인
        $it_sale_cnt1 =$row['it_sale_cnt'] ? "(".$row['it_sale_cnt']."개 이상) 사업소:".$row['it_sale_percent']." 우수사업소:".$row['it_sale_percent_great'] : "";
        $it_sale_cnt2 =$row['it_sale_cnt_02'] ? "(".$row['it_sale_cnt_02']."개 이상) 사업소:".$row['it_sale_percent_02']." 우수사업소:".$row['it_sale_percent_great_02'] : "";
        $it_sale_cnt3 =$row['it_sale_cnt_03'] ? "(".$row['it_sale_cnt_03']."개 이상) 사업소:".$row['it_sale_percent_03']." 우수사업소:".$row['it_sale_percent_great_03'] : "";
        $it_sale_cnt4 =$row['it_sale_cnt_04'] ? "(".$row['it_sale_cnt_04']."개 이상) 사업소:".$row['it_sale_percent_04']." 우수사업소:".$row['it_sale_percent_great_04'] : "";
        $it_sale_cnt5 =$row['it_sale_cnt_05'] ? "(".$row['it_sale_cnt_05']."개 이상) 사업소:".$row['it_sale_percent_05']." 우수사업소:".$row['it_sale_percent_great_05'] : "";
        $it_soldout = $row['it_soldout'] == "1" ? "품절":""; //상품품절
        
        //옵션
        $opt_subject = explode(',', $row['it_option_subject']);

        //옵션항목
        $opt_1=[];
        $opt_2=[];
        $opt_3=[];
        $sql_option = " select * from {$g5['g5_shop_item_option_table']} where io_type = '0' and it_id = '{$row['it_id']}' order by io_no asc ";
        $result_option = sql_query($sql_option);
        for($i=0; $row2=sql_fetch_array($result_option); $i++) {
            $opt_id = $row2['io_id'];
            $opt_val = explode(chr(30), $opt_id);
            array_push($opt_1,$opt_val[0]);
            array_push($opt_2,$opt_val[1]);
            array_push($opt_3,$opt_val[2]);
        }
        $opt_1 = array_unique($opt_1); $opt_1=implode(",",$opt_1);
        $opt_2 = array_unique($opt_2); $opt_2=implode(",",$opt_2);
        $opt_3 = array_unique($opt_3); $opt_3=implode(",",$opt_3);

        $it_is_direct_delivery = $row['it_is_direct_delivery'] == "1" ? "사용":""; //상품품절
        
        //배송비 상세조건
        $it_delivery_detail = $row['it_delivery_cnt'] ? $row['it_delivery_cnt']."개 마다 배송비".$row['it_delivery_price']."원 부과" :"";
        if($row['it_delivery_min_cnt']){
            $it_delivery_detail .=", 최소수량".$row['it_delivery_min_cnt']."개 이하".$row['it_delivery_min_price']."원 부과";
        }
        
        //배송비유형
        switch ($row['it_sc_type']) {
            case '0': $it_sc_type="쇼핑몰 기본설정 사용"; break;
            case '1': $it_sc_type="무료배송"; break;
            case '2': $it_sc_type="조건부 무료배송"; break;
            case '3': $it_sc_type="유료배송"; break;
            case '4': $it_sc_type="수량별 부과"; break;
            case '5': $it_sc_type="홀수/짝수 배송"; break;
            default:  $it_sc_type=""; break;
        }

        //급여구분
        switch (substr($row['ca_id'],0,2)) {
            case '70': $ca_id="비급여"; break;
			case '80': $ca_id="보장구"; break;
            default:  $ca_id="급여"; break;
        }

        //산간지역 추가 배송비
        $it_sc_add_sendcost = $row['it_sc_add_sendcost'] == "-1" ? "":number_format($row['it_sc_add_sendcost']); //상품품절


        $rows[] = [ 
            $pt_it,
            $ca_id,
            $row['ca_name'],
            $prodSupYn,
            $row['it_id'],
            $row['it_thezone'],
            $row['it_thezone2'],
            $row['it_name'],
            $row['it_default_warehouse'],
            $row['it_admin_memo'],
            $row['it_basic'],
            $row['prodSym'],
            $row['prodSizeDetail'],
            $row['prodWeig'],
            $row['pt_tag'],
            $row['entId'],
            $row['it_taxInfo'],
            $row['supId'],
            $row['ProdPayCode'],
            $row['it_maker'],
            $row['it_origin'],
            $it_use,
            number_format($row['it_price']),
            number_format($row['it_cust_price']),
            number_format($row['it_rental_price']),
            $it_rental_use_persisting_year,
            $row['it_rental_expiry_year']."년",
            $row['it_rental_persisting_year']."년",
            number_format($row['it_price_dealer']),
            number_format($row['it_price_dealer']),
            number_format($row['it_price_dealer2']),
            $it_sale_cnt1,
            $it_sale_cnt2,
            $it_sale_cnt3,
            $it_sale_cnt4,
            $it_sale_cnt5,
            number_format($row['it_price_partner']),
            $it_soldout,
            $row['it_stock_qty']."개",
            $row['it_noti_qty']."개",
            $row['it_buy_min_qty']."개",
            $row['it_buy_max_qty']."개",
            $opt_subject[0],
            $opt_1,
            $opt_subject[1],
            $opt_2,
            $opt_subject[2],
            $opt_3,
            $it_is_direct_delivery,
            $it_delivery_detail,
            $it_sc_type,
            $it_sc_add_sendcost,
            $row['it_expected_warehousing_date']
        ];
    }

    // return false;
    $headers = array("상품종류","급여구분","카테고리","유통구분","상품관리코드","분류코드","품목코드","상품명","출하창고","관리자 메모","기본설명","재질","사이즈 상세정보","중량","상품태그","업체 아이디","세무정보","공급자아이디","제품코드","제조사","원산지","판매가능","판매가격","급여가","대여금액(월)","대여(사용여부)","대여(판매가능기간)","대여(내구연한 설정 기간)","대여(기간 이후 대여금액)","사업소 판매가격(원)","우수사업소 판매가격(원)","묶음할인 조건1","묶음할인 조건2","묶음할인 조건3","묶음할인 조건4","묶음할인 조건5","파트너몰 판매가격","상품품절","재고수량","재고 통보수량","최소구매수량","최대구매수량","옵션1","옵션1 항목","옵션2","옵션2 항목","옵션3","옵션3 항목","직배송","배송비 상세조건","배송비 유형","산간지역 추가 배송비","입고예정일");
    $data = array_merge(array($headers), $rows);
    
    $widths  = array(30, 30, 30, 30, 30, 30, 30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30);
    $header_bgcolor = 'FFABCDEF';
    $excel = new PHPExcel();
    foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
    $excel->getActiveSheet()->fromArray($data,NULL,'A1');

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"orderexcel-".date("ymd", time()).".xls\"");
    header("Cache-Control: max-age=0");

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
    $writer->save('php://output');
    // $prevPage = $_SERVER['HTTP_REFERER'];
    function column_char($i) { return chr( 65 + $i ); }
?>
