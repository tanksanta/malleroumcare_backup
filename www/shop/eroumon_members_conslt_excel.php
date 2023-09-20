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
    /* //  * Program Name : EROUMCARE Platform! = EroumON_Order Ver:0.1 */
    /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
    /* //  * Copyright (c) 2023 THKC Co,Ltd.  All rights reserved. */
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
    /* // 파일명 : /www/shop/eroumon_members_conslt_excel.php */
    /* // 파일 설명 : 이로움ON에사 발생한 1:1 상담 리스트에 대하여 검색된 데이터 기준으로 엑셀데이터 파일로 추출하는 전용 페이지. */
    /*                */
    /*                */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    include_once('./_common.php');    
    include_once(G5_LIB_PATH.'/PHPExcel.php');

    if(!$is_member || !$member['mb_id']) { json_response(400, '먼저 로그인 하세요.'); exit(); }
    if(!$member['mb_entId']) {	json_response(400, '사업소 회원만 접근 가능합니다.'); exit(); }



    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    
    $_Search = "";    
    $search = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['search'] ) ) );
    $to_date = $to_date . ' 23:59:59';

    // 상태값 선택에 따른 SQL 쿼리문.
    if( $srchConsltSttus=="CS02" ) $_Search = "AND (MCR.CONSLT_STTUS=''CS02'')";
    else if( $srchConsltSttus=="CS05" ) $_Search = "AND (MCR.CONSLT_STTUS=''CS05'')";
    else if( $srchConsltSttus=="CANCEL" ) $_Search = "AND ((MCR.CONSLT_STTUS=''CS03'') OR (MCR.CONSLT_STTUS=''CS04'') OR (MCR.CONSLT_STTUS=''CS09''))";
    else if( $srchConsltSttus=="CS06" ) $_Search = "AND (MCR.CONSLT_STTUS=''CS06'')";
    
    // 검색에 따른 (전체, 이름, 연락처) SQL 쿼리문 - 해당 쿼리는 LIKE를 기반으로 한다.
    if( $sel_field=="NM" ) $_Search .= "AND (MC.MBR_NM LIKE ''%{$search}%'')";
    else if( $sel_field=="TELNO" ) $_Search .= "AND (MC.MBR_TELNO LIKE ''%{$search}%'')";
    else if( $sel_field=="all" && $search ) $_Search .= "AND ((MC.MBR_NM LIKE ''%{$search}%'') OR (MC.MBR_TELNO LIKE ''%{$search}%''))";

    // 페이지 진입에 따른 조건 기준으로 검색된 검색 개수.
    $sql = (" CALL `PROC_EROUMCARE_CONSLT`('cnt','{$member['mb_giup_bnum']}', '{$fr_date}', '{$to_date}', NULL, NULL, '{$_Search}'); ");
    $sql_result = "";
    $sql_result = sql_fetch( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);    
    $total_count = $sql_result['cnt'];
    //var_dump($sql_result);

    // 리스트용 데이터 호출
    $sql = (" CALL `PROC_EROUMCARE_CONSLT`('list','{$member['mb_giup_bnum']}', '{$fr_date}', '{$to_date}','0','{$total_count}','{$_Search}'); ");
    $sql_result = "";
    $sql_result = sql_query( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);
    //var_dump($sql_result);

    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    
    function column_char($i) { return chr( 65 + $i ); }

    // Execl  시트의 타이틀 부분
    $newHeaders = array('번호', '상담진행상태', '성명', '성별', '연락처', '만나이', '생년월일', '거주지주소', '상담배정일', '상담신청일');
    $widths  = array(6, 20, 12, 10, 15, 10, 12, 50, 20, 20);
    $header_bgcolor = 'FFABCDEF';
    $last_char = column_char(count($newHeaders) - 1);

    // 새로운 PHPExcel 객체 생성
    $objPHPExcel = new PHPExcel();

    // 새로운 시트 선택
    $sheet = $objPHPExcel->getActiveSheet();

    // 새로운 헤더 추가
    $sheet->fromArray(array($newHeaders), NULL, 'A1');
    foreach($widths as $i => $w) $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
    $objPHPExcel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
    $objPHPExcel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);

    // 기존 데이터를 한 행씩 뒤로 이동시키기
    for( $i=0; $row=sql_fetch_array($sql_result); $i++ ) {

        $birthday = new DateTime($row['BRDT']); // 생년월일
        $age = $birthday->diff( new DateTime(date('ymd')) )->y; // 만나이 계산

        $_conslt_st = false;
        if( $row['CONSLT_STTUS']==="CS03" || $row['CONSLT_STTUS']==="CS04" ) { $_conslt_st = true; }

        $sheet->setCellValue('A'.($i+2), ($total_count - $i) );
        $sheet->setCellValue('B'.($i+2), $row['Hangeul_CONSLT_STTUS'] );
        $sheet->setCellValue('C'.($i+2), (!$_conslt_st)?$row['MBR_NM']:Masking_Name($row['MBR_NM']) );
        $sheet->setCellValue('D'.($i+2), (!$_conslt_st)?$row['Hangeul_GENDER']:"-" );
        $sheet->setCellValue('E'.($i+2), (!$_conslt_st)?$row['MBR_TELNO']:"-" );
        $sheet->setCellValue('F'.($i+2), (!$_conslt_st)?$age."세":"-" );
        $sheet->setCellValue('G'.($i+2), (!$_conslt_st)?substr($row['BRDT'],0,4)."/".substr($row['BRDT'],4,2)."/".substr($row['BRDT'],6,2):"-" );
        $sheet->setCellValue('H'.($i+2), (!$_conslt_st)?"(".$row['ZIP'].") ".$row['ADDR']." ".$row['DADDR']:"-" );
        $sheet->setCellValue('I'.($i+2), $row['MCR_REG_DT']);
        $sheet->setCellValue('J'.($i+2), $row['MC_REG_DT']);
    }

    // 엑셀 파일 형식 및 이름 지정
    $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $filename = 'sample_excel.xlsx';

    // 브라우저로 엑셀 파일을 다운로드
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Cache-Control: max-age=0');    
    header('Set-Cookie: fileDownload=true; path=/');

    $writer->save('php://output');
?>