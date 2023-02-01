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
    $sql = ("   SELECT bl.*, par.method_symbol, par.price, par.status_locale, par.card_company, par.card_quota
                FROM 
                    payment_billing_list bl
                    LEFT OUTER JOIN
                    payment_api_request par ON par.id = (
                        SELECT MAX(id)
                        FROM payment_api_request par2
                        WHERE par2.bl_id = bl.bl_id
                        ORDER BY par2.create_dt
                        LIMIT 1
                    )
                WHERE bl.bl_id = '" . $bl_id . "'
    ");

    $_Billing = sql_fetch($sql);

    
    // 23.02.01 : 서원 - 정산 내역서 타이틀 부분 정리
    $_billing_txt = "";
    $_billing_txt .= "\r\n";    
    $_billing_txt .= " " . $_Billing['billing_month'] . "월  대금청구서 (품목: ##cnt##건) \r\n" . $_Billing['mb_bnm'] . "";
    $_billing_txt .= "\r\n\r\n";


    // 23.02.01 : 서원 - 정산 내역서 결제 금액 관련 부분 정리
    $_info_txt = "";
    $_info_txt .= "\r\n";
    $_info_txt .= "과세 물품 구매 금액: " . number_format($_Billing['price_tax']) . "원　　\r\n";
    $_info_txt .= "면세 물품 구매 금액: " . number_format($_Billing['price_tax_free']) . "원　　\r\n";
    $_info_txt .= "총 청구 금액: " . number_format($_Billing['price_total']) . "원　　";
    if( $_Billing['price'] > 0 ) {
        $_info_txt .= "\r\n\r\n";
        if( $_Billing['billing_fee_yn'] == "Y" ) { $_info_txt .= "수수료(" . $_Billing['billing_fee'] . "%) 금액: " . number_format( $_Billing['price_total'] * ($_Billing['billing_fee']/100) )  . "원　　\r\n"; }
        $_info_txt .= "결제 금액: " . number_format( $_Billing['price'] ) . "원　　";
    }

    // 결제 수수료가 있는 경우
    $_billing_fee_info_txt = "";
    if( $_Billing['billing_fee_yn'] == "Y" && $_Billing['billing_yn'] == "Y" ) { 

        if( !$_Billing['price'] || ($_Billing['price'] <= 0) ) {
            $_billing_fee_info_txt .= "\r\n";
            $_billing_fee_info_txt .= "* 온라인 결제 시에는 결제 수수료( " . $_Billing['billing_fee'] . "% / " . number_format( $_Billing['price_total'] * ($_Billing['billing_fee']/100) ). "원 ) 포함한 금액( " . number_format( $_Billing['price_total'] + ($_Billing['price_total'] * ($_Billing['billing_fee']/100)) ) . "원 )으로 결제합니다.　";
            $_billing_fee_info_txt .= "\r\n\r\n";
        }
    }
    $_info_txt .= "\r\n\r\n";


    // 내용(본문 리스트)
    $_sql = ("  SELECT 
                    item_dt, 
                    item_nm, 
                    item_qty,
                    price_qty,
                    price_supply,
                    price_tax,
                    price_total
                FROM 
                    payment_billing_list_data
                WHERE 
                    bl_id = '" . $bl_id . "'
                ORDER BY item_dt
    ");
    $result = sql_query($_sql);


    $data = []; 
    // SQL 데이터 오브젝트에서 배열 처리
    while( $row = sql_fetch_array($result) ) { $data[] = $row; }


    $widths  = [15, 50, 10, 15, 15, 12, 15];
    $headers = [
        '일자',
        '품목명[규격]',
        '수량',
        '단가
(Vat포함)',
        '공급가액',
        '부가세',
        '판매'
    ];


    include_once(G5_LIB_PATH."/PHPExcel.php");
    $excel = new PHPExcel();
    foreach($widths as $i => $w) {
        $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
    }
    $sheet = $excel->getActiveSheet();

    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

    $sheet->getPageMargins()->setTop(1);
    $sheet->getPageMargins()->setRight(0.75);
    $sheet->getPageMargins()->setLeft(0.75);
    $sheet->getPageMargins()->setBottom(1);

    $data = array_merge(array($headers), $data);
    
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', str_replace("##cnt##", $result->num_rows, $_billing_txt));


    $last_col = count($headers);
    $last_char = column_char($last_col-1);
    $last_row = count($data)+2;


    $sheet->mergeCells('A'.$last_row.':G'.$last_row);
    $sheet->setCellValue('A'.$last_row,$_info_txt);

    if( $_Billing['billing_fee_yn'] == "Y" && ($_billing_fee_info_txt) ) { 
        $last_row = count($data)+3;
        $sheet->mergeCells('A'.$last_row.':G'.$last_row);
        $sheet->setCellValue('A'.$last_row,$_billing_fee_info_txt);
    }

    $sheet->fromArray($data,NULL,'A2');


    // 문서 타이틀 부분 - 높이 지정
    $sheet->getRowDimension(1)->setRowHeight(100);
    // 문서 타이틀 부분 - 폰트 정렬, 굵기, 사이즈
    $sheet->getStyle( "A1:${last_char}1" )->applyFromArray(
        array(
            'borders' => array( 
                'allborders' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK ) 
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
            ,'font' => array( 'bold' => true, 'size' => 18 )
        )
    );


    // 컬럼 타이틀 부분 - 높이 지정
    $sheet->getRowDimension(2)->setRowHeight(50);
    // 컬럼 타이틀 부분 - 셀 배경색 지정
    $sheet->getStyle( "A2:${last_char}2" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');
    // 컬럼 타이틀 부분 - 폰트 정렬, 굵기, 사이즈
    $sheet->getStyle( "A2:${last_char}2" )->applyFromArray(
        array(
            'borders' => array( 
                'outline' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK ),
                'inside' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
            ,'font' => array( 'bold' => true, 'size' => 12 )
        )
    );


    // 전체 부분 - 폰트 적용
    $sheet->getStyle( "A1:".$last_char.$last_row )->getFont()->setName('Malgun Gothic');

    // 리스트 데이터 부분 - 폰트 크기 지정
    $sheet->getStyle( "A3:".$last_char.($last_row-1) )->getFont()->setSize(10);
    // 리스트 데이터 부분 - ROW의 높이 지정
    for($i = 3; $i <= $last_row; $i++) { $sheet->getRowDimension($i)->setRowHeight(25); }
    // 리스트 데이터 부분 - 폰트 정렬, 굵기, 사이즈
    $sheet->getStyle( "A3:".$last_char.($last_row-1) )->applyFromArray(
        array(
            'borders' => array( 
                'outline' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK ),
                'inside' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
            ,'font' => array( 'bold' => false, 'size' => 10 )
        )
    );


    // 하단 결제 정보 부분 - 폰트 정렬, 굵기, 사이즈
    $sheet->getStyle( "A".(count($data)+2).":".$last_char.(count($data)+2) )->applyFromArray(
        array(
            'borders' => array( 
                'allborders' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
            ,'font' => array( 'bold' => true, 'size' => 12 )
        )
    );


    if( $_Billing['billing_fee_yn'] == "Y" && ($_billing_fee_info_txt) ) { 
        // 하단 결제 정보 부분 - 폰트 정렬, 굵기, 사이즈
        $sheet->getStyle( "A".$last_row.":".$last_char.$last_row )->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
                ,'font' => array( 'bold' => true, 'size' => 10, 'color' => array('rgb'=>'FF0000') )
            )
        );    
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