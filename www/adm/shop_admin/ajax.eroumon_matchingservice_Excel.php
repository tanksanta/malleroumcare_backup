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
    /* //  * Program Name : EROUMCARE Platform! = matchingservice Ver:1 */
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
    /* // 파일명 : www\adm\shop_admin\ajax.eroumon_matchingservice_Excel.php */
    /* // 파일 설명 : [관리자] 이로움ON의 1:1 매칭 신청을 진행했던 사업소 리스트에 대한 엑셀 파일 추출 파일 */
    /*                 화면상 리스트 검색 조건을 기준으로 엑셀 파일로 데이터를 추출한다. */
    /*                   */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

$sub_menu = '500060';
include_once("./_common.php");

function column_char($i) { return chr( 65 + $i ); }

if( $_POST['mode_set']  == "ExcelDown" ) {

    $param = [];
    $params = explode("&", $_POST['data']);

    foreach($params as $key => $val) {
        $tmp = explode("=", $val);
        $param[ $tmp[0] ] = $tmp[1];
    }

    $sql_common = (" FROM g5_member ");
    //$where[] = "(mb_matching_dt IS NOT NULL OR mb_matching_dt <> '') AND ( mb_level IN (3,4) ) ";
    
    // 날짜검색
    if ($param['$fr_date'] && $param['$to_date']) {
      $where[] = "mb_matching_dt BETWEEN '" . $param['$fr_date'] . " 00:00:00' AND '" . $param['$to_date'] . " 23:59:59' ";
    } else {        
      $where[] = "mb_matching_dt BETWEEN '".date("Y-m-d",strtotime("-90 day", time()))."' AND '".date("Y-m-d",strtotime("+1 day", time()))."' ";
    }
    
    // 신청여부
    if( $param['matchingY'] && !$param['matchingN'] ) { $where[] = "mb_giup_matching = 'Y'"; }
    if( !$param['matchingY'] && $param['matchingN'] ) { $where[] = "mb_giup_matching = 'N'"; }
    
    // 검색어
    if( $param['search'] ) {
    
      if( $param['sel_field'] == "all" ) {
          $where[] = " 
            (
              ( `mb_id` LIKE '%" . $param['search'] . "%' ) 
              OR ( `mb_giup_bnum` LIKE '%" . $param['search'] . "%' ) 
              OR ( `mb_giup_bname` LIKE '%" . $param['search'] . "%' ) 
              OR ( `mb_matching_manager_tel` LIKE '%" . $param['search'] . "%' )
            )
          ";
      }
      else if( $param['sel_field'] == "mb_id" ) {
          $where[] = " ( `mb_id` LIKE '%" . $param['search'] . "%' ) ";
      }
      else if( $param['sel_field'] == "mb_giup_bnum" ) {
          $where[] = " ( `mb_giup_bnum` LIKE '%" . $param['search'] . "%' ) ";
      }
      else if( $param['sel_field'] == "mb_giup_bname" ) {
          $where[] = " ( `mb_giup_bname` LIKE '%" . $param['search'] . "%' ) ";
      }
      else if( $param['sel_field'] == "mb_matching_manager_tel" ) {
          $where[] = " ( `mb_matching_manager_tel` LIKE '%" . $param['search'] . "%' ) ";
      }
    }
    
    if ($where) {
      if ($sql_search) {
          $sql_search .= " AND ";
      }else{
          $sql_search .= " WHERE ";
      }
    
      $sql_search .= implode(' AND ', $where);
    }

    $sql = (" SELECT mb_id
                    ,mb_giup_bnum
                    ,mb_giup_bname
                    ,mb_matching_manager_nm                    
                    ,mb_matching_manager_tel                    
                    ,mb_referee_cd
                    ,mb_matching_dt
                    ,mb_matching_forms

                    {$sql_common}
                    {$sql_search}
                ORDER BY mb_matching_dt DESC
            ");
    $result = sql_query($sql);

    $widths  = [
            15,
            30,
            30,
            35,
            35,
            15,
            25,
            40
        ];
    
    $headers = [
        '회원ID',        
        '사업소 코드(가입시 기재된 사업자번호)',
        '사업업소명(회원정보 내 기업명)',
        '매칭 담당자 성명(설문에 기재한 담당자)',
        '매칭 담당자 휴대폰번호 (설문에 기재한 번호)',
        '사업소 추천코드',
        '상담신청일시(매칭동의일시)',
        '문항답변'
    ];

    $data = [];
    while( $row = sql_fetch_array($result) ) {
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
            'size' => 8,
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
    header("Content-Disposition: attachment; filename=\"매칭상담서비스관리_리스트-".$member['mb_id']."-".date("ymd").".xlsx\"");
    header("Cache-Control: max-age=0");
    header('Set-Cookie: fileDownload=true; path=/');


    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $writer->save('php://output');


}
else {
    json_response(400, 'error');
}
?>