<?php

    /* // */
    /* // */
    /* // */
    /* // */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* //  *  */
    /* //  *  */
    /* //  * (주)티에이치케이컴퍼 & 이로움 - [ THKcompany & E-Roum ] */
    /* //  *  */
    /* //  * Program Name : EROUMCARE Platform! = OnlineBilling Ver:0.1 */
    /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
    /* //  * Copyright (c) 2022 THKC Co,Ltd.  All rights reserved. */
    /* //  *  */
    /* //  *  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // */
    /* // */
    /* // */
    /* // */

    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */
    /* // 파일명 : /www/shop/popup.payment_OnlineBilling_Excel.php */
    /* // 파일 설명 :   온라인 결제(사업소화면) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  청구내역 PDF변환용 HTML 생성 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

	include_once("./_common.php");

    $bl_id = $_GET["bl_id"];



    $sql = "";
    $sql = ("  SELECT 
                    mb_id, mb_bnm, billing_ecount_title
                    FROM 
                    payment_billing_list
                WHERE 
                    bl_id = '" . $bl_id . "'
    ");      
    $_Billing = sql_fetch($sql);




    // 내용(본문 리스트)
    $_sql = ("  SELECT 
                    bld_id, 
                    item_nm, 
                    item_qty,
                    price_qty,
                    price_supply,
                    price_tax,
                    price_total,
                    item_delivery
                FROM 
                    payment_billing_list_data
                WHERE 
                    bl_id = '" . $bl_id . "'
                ORDER BY bld_id
    ");
    
    $result = sql_query($_sql);
    $data = [];






    while( $row = sql_fetch_array($result) ) {
        $data[] = $row;
    }

    $widths  = [20, 45, 10, 15, 15, 12, 15, 35];
    $headers = [
        '일자-No.',
        '품목명[규격]',
        '수량',
        '단가
(Vat포함)',
        '공급가액',
        '부가세',
        '판매',
        '출고처'
    ];


    include_once(G5_LIB_PATH."/PHPExcel.php");
    $excel = new PHPExcel();
    foreach($widths as $i => $w) {
        $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
    }
    $sheet = $excel->getActiveSheet();

    $data = array_merge(array($headers), $data);
    
    $sheet->mergeCells('A1:H1');
    $sheet->setCellValue('A1'," ".$_Billing['billing_ecount_title']);
    $sheet->fromArray($data,NULL,'A2');


    $last_col = count($headers);
    $last_char = column_char($last_col-1);
    $last_row = count($data)+1;


    // 테두리 처리
    $styleArray = array(
        'font' => array( 'size' => 14, 'name' => 'Malgun Gothic' ),
        'borders' => array( 'allborders' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ) ),
        'alignment' => array( 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT )
    );
    $sheet->getStyle('A1')->applyFromArray($styleArray);

    // 테두리 처리
    $styleArray = array(
        'font' => array( 'size' => 10, 'name' => 'Malgun Gothic' ),
        'borders' => array( 'allborders' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ) ),
        'alignment' => array( 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER )
    );
    $sheet->getStyle('A2:'.$last_char.$last_row)->applyFromArray($styleArray);

    // 헤더 배경
    $header_bgcolor = 'FFD3D3D3';
    $sheet
    ->getStyle( "A2:${last_char}2" )
    ->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB($header_bgcolor);

    // 헤더 폰트 굵기
    $sheet
    ->getStyle( "A2:${last_char}2" )
    ->getFont()
    ->setBold(true);

    // 열 높이
    for($i = 0; $i <= $last_row; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(30);
    }


    $writer = PHPExcel_IOFactory::createWriter($excel, 'HTML');
    $writer->save('php://output');
    
    function column_char($i) { return chr( 65 + $i ); }
?>

<style>
    body { margin-left: 0in; margin-right: 0in; margin-top: 0in; margin-bottom: 0in; }
    table { width: 100%; }
</style>
<script src="//code.jquery.com/jquery.min.js"></script>
<script type="application/javascript">
    $('tbody > tr:last').remove();
</script>