<?php

	include_once("./_common.php");
	include_once(G5_LIB_PATH."/PHPExcel.php");

    $sql ="select * from `g5_shop_cart` where `od_id` ='".$od_id."'";
    $result = sql_query($sql);
    $flag = false;
    while ($row=sql_fetch_array($result)) {
        if($row['ct_status'] !== "취소" && $row['ct_status'] !== "주문무효"){

            $sto_imsi=$row['stoId'];
            $stoIdDataList = explode('|',$sto_imsi);
            $stoIdDataList=array_filter($stoIdDataList);
            $stoIdData = implode("|", $stoIdDataList);
            $sendData["stoId"] = $stoIdData;
            $res = get_eroumcare2(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
            $result_again =$res['data'];

            if($row['it_name']==$row['ct_option']){
                $it_name=$row['it_name'];
            }else{
                $it_name=$row['it_name'].'('.$row['ct_option'].")";

            }
            //정렬
            foreach ((array) $result_again as $key => $value) {
                $sort[$key] = $value['prodBarNum'];
            }
            array_multisort($sort, SORT_ASC, $result_again);

            for($i=0;$i<count($result_again);$i++){
                $rows[] = [ 
                    $it_name,  
                    $result_again[$i]['prodBarNum']." ",  
                ];
            }
            $flag=true;
        }

    }

    if($flag){
        $headers = array("상품명","바코드");
        $data = array_merge(array($headers), $rows);
        
        $widths  = array(20, 50, 10, 30, 50, 30, 50);
        $header_bgcolor = 'FFABCDEF';
        $excel = new PHPExcel();
        // foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
        $excel->getActiveSheet()->fromArray($data,NULL,'A1');

        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"orderexcel-".date("ymd", time()).".xls\"");
        header("Cache-Control: max-age=0");

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer->save('php://output');
        $prevPage = $_SERVER['HTTP_REFERER'];
        // 변수에 이전페이지 정보를 저장
    }else{
        alert('다운로드할 자료가 없습니다.');
    }
    
?>