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


//if( $_POST['mode_set']  == "ExcelDown" ) {

    $param = [];
    $params = explode("&", $_POST['data']);

    foreach($params as $key => $val) {
        $tmp = explode("=", $val);
        $param[ $tmp[0] ] = $tmp[1];
    }


    if(!$fr_date){ $fr_date = date("Y-m-d", strtotime(date("Y", time() )."-".date("m", time() )."-".date("d", time() )." -".date("w", time() )." day"));  }
    if(!$to_date){ $to_date = date("Y-m-d", strtotime($fr_date." +6 day")); }

    $sql_common = (" 
        FROM (

            SELECT 
                A.od_id ,
                date_format(A.od_time, '%Y-%m-%d %H:%i:%s') as '주문일시',
                A.od_name as '사업소명',
                C.mb_id as '사업소아이디',
                (CASE when A.od_name=A.od_b_name then null else A.od_b_name end) as '받는이',
                B.상품명들 as '상품명들',
                B.품목수 as '품목수',
                B.개수 as '총수량',
                B.총금액-B.할인금액 as '주문금액',
                A.od_send_cost+A.od_send_cost2 as '배송비',
                A.od_coupon*-1 as '쿠폰할인',
                B.총금액-B.할인금액+A.od_send_cost+A.od_send_cost2-A.od_coupon  as '총액',
                (CASE A.od_memo	WHEN '' THEN NULL ELSE CONCAT(LEFT(A.od_memo,10),'...') END) as '주문요청사항',
                (CASE A.od_add_admin When '' then NULL else '관리자' end) as '관리자',
                (CASE WHEN B.품목수 is null then '주문취소' WHEN B.준비수=B.품목수 then '주문접수' WHEN B.품목수=B.완료수 then '배송완료' else '배송진행' END) as '상태'
        
            FROM g5_shop_order A
            LEFT JOIN 
            (
                SELECT
                    od_id ,
                    concat(it_name) as '품목명',
                    group_concat(it_name) as '상품명들',
                    sum(ct_price*ct_qty) as '총금액',
                    sum(ct_discount) as '할인금액',
                    count(ct_status) as '품목수',
                    sum(Ct_qty) as '개수',
                    count(CASE when ct_status = '완료' then 1 end ) as '완료수',
                    count(CASE when ct_status = '배송' then 1 end )as '배송수',
                    count(CASE when ct_status = '준비' then 1 end ) as '준비수'
                FROM g5_shop_cart
                WHERE ct_status in('준비','배송','완료')
                GROUP by od_id 
            )
            B on A.od_id=B.od_id
            LEFT JOIN g5_member C on C.mb_id=A.mb_id
            WHERE (A.od_time between '$fr_date 00:00:00' and '$to_date 23:59:59' )
        
        ) D
        ");

    // 날짜검색
    if ($fr_date && $to_date) {
        $where[] = "(`주문일시` between '$fr_date 00:00:00' and '$to_date 23:59:59' )";
    }


    if($od_admin_yn=="Y"){ $where[] = "`관리자` = '관리자' "; }
    else if($od_admin_yn=="N"){ $where[] = "`관리자` IS NULL "; } 
    else{ }


    if( $od_status =="receipt" ) { $where[] = "`상태` = '주문접수' "; }
    else if( $od_status =="progress" ) { $where[] = "`상태` = '배송진행' "; }
    else if( $od_status =="completed" ) { $where[] = "`상태` = '배송완료' "; }
    else if( $od_status =="cancel" ) { $where[] = "`상태` = '주문취소' "; }
    else { }

    if($mb_name){
        $where[] = "`사업소명` LIKE '%" . $mb_name . "%' ";
    }

    if ($where) {
        if ($sql_search) {
            $sql_search .= " AND ";
        }else{
            $sql_search .= " WHERE ";
        }

        $sql_search .= implode(' and ', $where);
    }
    $sql = " SELECT *
                {$sql_common}
                {$sql_search}
                ORDER BY `주문일시` DESC
    ";
    $result = sql_query($sql);


    $headers = [
        "주문아이디",
        "주문일자",
        "사업소명",
        "사업소아이디",
        "수령인",
        "품목명",
        "품목수",
        "총수량",
        "총금액",
        "배송비",
        "쿠폰할인",
        "총액",
        "주문요청사항",
        "관리자주문",
        "상태"
    ];


    $data = [];
    while( $row = sql_fetch_array($result) ) {

        if( $row['od_id'] ) {
            $row['od_id'] = " ".$row['od_id'];
        }
        $data[] = $row;
    }

    include_once(G5_LIB_PATH."/PHPExcel.php");
    $excel = new PHPExcel();

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

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"주문서리스트_".$member['mb_id']."-".date("ymdH").".xlsx\"");
    header("Cache-Control: max-age=0");
    header('Set-Cookie: fileDownload=true; path=/');


    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $writer->save('php://output');


//}
//else {
//    json_response(400, 'error');
//}
?>