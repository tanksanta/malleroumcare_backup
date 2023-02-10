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
    /* // 파일명 : /www/adm/shop_admin/ajax.payment_OnlineBilling_Excel.php */
    /* // 파일 설명 :   온라인 결제(관리자화면) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  사업소의 대금 청구 검색리스트 엑셀파일 다운로드*/
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

$sub_menu = '500150';
include_once("./_common.php");

function column_char($i) { return chr( 65 + $i ); }

function txt_pay_ENUM( $val ) {

    $_result = "";
  
    switch($val) {    
  
      case('phone'): $_result = "휴대폰"; break;
      case('card'): $_result = "카드"; break;
      case('bank'): $_result = "계좌이체"; break;
      case('vbank'): $_result = "가상계좌"; break;
      case('easy'): $_result = "간편"; break;
      case('easy_rebill'): $_result = "간편자동"; break;
      case('card_rebill'): $_result = "카드자동"; break;
      case('kakaopay'): $_result = "카카오페이"; break;
      case('naverpay'): $_result = "네이버페이"; break;
      case('payco'): $_result = "페이코"; break;
      case('toss'): $_result = "토스"; break;
      case('easy_card'): $_result = "간편카드"; break;
      case('easy_card_rebill'): $_result = "간편카드자동"; break;
      case('auth'): $_result = "본인인증"; break;
      case('digital_card'): $_result = "디지털카드"; break;
      case('digital_bank'): $_result = "디지털계좌이체"; break;
      case('digital_card_rebill'): $_result = "디지털카드자동"; break;
  
      default : $_result = "-"; break;
    }
  
    return $_result;
}

if( $_POST['mode_set']  == "ExcelDown" ) {

    $param = [];
    $params = explode("&", $_POST['data']);

    foreach($params as $key => $val) {
        $tmp = explode("=", $val);
        $param[ $tmp[0] ] = $tmp[1];
    }

    $sql_common = ("    FROM 
                            payment_billing_list bl
                            LEFT OUTER JOIN
                            payment_api_request par ON par.id = (
                                SELECT MAX(id)
                                FROM payment_api_request par2
                                WHERE par2.bl_id = bl.bl_id
                                ORDER BY par2.create_dt
                                LIMIT 1
                            )
                ");

    // 날짜검색
    if( $param['fr_date'] && $param['to_date'] ) {
        $where[] = "bl." . $param['select_date'] . " between '" . $param['fr_date'] . " 00:00:00' and '" . $param['to_date'] . " 23:59:59' ";
    } else {        
        $where[] = "bl.create_dt between '".date("Y-m-d",strtotime("-90 day", time()))."' and '".date("Y-m-d",strtotime("+1 day", time()))."' ";
    }

    // 검색어(거래처명)
    if( $param['stx'] ) {
        $where[] = " ( `mb_nm` LIKE '%{$param['stx']}%' OR `mb_bnm` LIKE '%{$param['stx']}%' ) ";
    }

    // 카드사명
    if( $param['select_card_company'] ) { 
        $where[] = " ( `card_company` LIKE '{$param['select_card_company']}%' ) ";
    }

    // 결제상태
    if( $param['select_status'] ) {

        if( $param['select_status'] == "미결제" ) {
            $where[] = " ( pay_confirm_id IS NULL OR pay_confirm_id = '' ) AND ( pay_confirm_receipt_id IS NULL OR pay_confirm_receipt_id = '' ) ";
        }
        else if( $param['select_status'] == "결제완료" ) {
            $where[] = " ( `status_locale` = '결제완료' ) ";
        }
        else if( $param['select_status'] == "결제취소" ) {
            $where[] = " ( `status_locale` = '결제취소완료' ) ";
        }
        else if( $param['select_status'] == "관리자취소" ) {
            $where[] = " ( `billing_yn` = 'N' ) ";
        }
    }
    
    // 할부구분
    if( $param['select_card_quota'] ) { 
        
        if( $param['select_card_quota'] == "일시불" ) {
            $where[] = " ( `card_quota` = '00' ) ";
        }
        else if( $param['select_card_quota'] == "할부" ) {
            $where[] = " ( `card_quota` <> '00' ) ";
        }
    }
    
    if ($where) {
        if ($sql_search) {
            $sql_search .= " AND ";
        }else{
            $sql_search .= " WHERE ";
        }

        $sql_search .= implode(' and ', $where);
    }

    $sql = (" SELECT    bl.bl_id,
                        bl.mb_id, 
                        bl.mb_bnm, 
                        bl.mb_thezone, 
                        bl.price_tax, 
                        bl.price_tax_free,
                        bl.price_total,

                        bl.billing_fee_yn,
                        bl.billing_fee,
                        ( price_total * (billing_fee/100) ) as billing_fee_price,
                        par.price,

                        bl.billing_type,
                        bl.billing_status,

                        bl.create_dt,
                        bl.create_id,

                        par.receipt_id,

                        bl.pay_confirm_dt,
                        par.method_symbol,
                        par.status_locale,
                        par.card_company,
                        par.card_quota,
                        
                        bl.error_msg
                {$sql_common}
                {$sql_search}
                ORDER BY bl.pay_confirm_dt DESC, bl.create_dt DESC
            ");
    $result = sql_query($sql);

    $widths  = [
            25,
            15,
            35,
            12,
            
            10,
            10,
            10,

            8,
            10,
            10,

            8,
            10,

            15,
            10,

            20,
            18,
            15,
            10,
            8,
            
            30 ];
    
    $headers = [
        '주문코드',
        '거래처 아이디',
        '거래처 명칭',
        '거래처 코드',

        '과세 구매 금액',
        '면세 구매 금액',
        '청구 금액',

        '수수료율(%)',
        '결제 수수료 금액',
        '결제 금액',

        '청구타입',
        '청구상태',

        '등록 일자',
        '등록 관리자',

        '영수증코드',
        '결제일자',
        '결제타입',
        '결제상태',
        '할부구분',

        '기타'
    ];

    $data = [];

    while( $row = sql_fetch_array($result) ) {

        if( $row['receipt_id'] ) {
            if( $row['card_quota'] == "00" ) {
                $row['card_quota'] = "일시불";
            } else { $row['card_quota'] = "할부(".$row['card_quota'].")"; }
        }

        if( ($row['receipt_id'])&&($row['method_symbol']) ) {
            $row['method_symbol'] = txt_pay_ENUM($row['method_symbol'])."(".$row['card_company'].")";
        }
        
        if( ( $row['billing_fee_yn']=="Y" ) && ($row['billing_fee_price'])&&($row['price']) ) {
            $row['billing_fee_price'] = ceil( $row['billing_fee_price'] );   
        } else {
            if( $row['billing_fee_yn']=="N" ){
                $row['billing_fee'] = "면제(".$row['billing_fee']."%)";
                $row['billing_fee_price'] = '0';
            } else {
                $row['billing_fee_price'] = ' ';
            }
        }



        unset( $row['billing_fee_yn'] );
        unset( $row['card_company'] );

        $data[] = $row;
    }

    include_once(G5_LIB_PATH."/PHPExcel.php");
    $excel = new PHPExcel();
    foreach($widths as $i => $w) {
        $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
    }
    $sheet = $excel->getActiveSheet();

    $data = array_merge(array($headers), $data);
    $sheet->fromArray($data,NULL,'A1');

    $last_col = count($headers);
    $last_char = column_char($last_col-1);
    $last_row = count($data);

    // 테두리 처리
    $styleArray = array(
        'font' => array(
            'size' => 7,
            'name' => 'Malgun Gothic'
        ),
        'borders' => array(
            'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        ),
        'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        )
    );

    $sheet
    ->getStyle('A1:'.$last_char.$last_row)
    ->applyFromArray($styleArray);

    // 헤더 배경
    $header_bgcolor = 'FFD3D3D3';
    $sheet
    ->getStyle( "A1:${last_char}1" )
    ->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB($header_bgcolor);

    // 헤더 폰트 굵기
    $sheet
    ->getStyle( "A1:${last_char}1" )
    ->getFont()
    ->setBold(true);

    // 열 높이
    for($i = 0; $i <= $last_row; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(22);
    }


    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"대금청구서관리_리스트-".$member['mb_id']."-".date("ymd").".xlsx\"");
    header("Cache-Control: max-age=0");
    header('Set-Cookie: fileDownload=true; path=/');


    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $writer->save('php://output');


}
else {
    json_response(400, 'error');
}
?>