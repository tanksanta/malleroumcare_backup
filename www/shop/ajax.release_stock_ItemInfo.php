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
    /* //  * Program Name : EROUMCARE Platform! = Renewal Ver:1.0 */
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
    /* // 파일 작성 일자 : 23.07.18 */
    /* // 파일 작성자 : 박서원 */
    /* // 파일명 : /www/shop/ajax.release_stock_ItemInfo.php */
    /* // 파일 설명 : 바코드 재고이관 관련 조회 전용 AJAX 파일. */
    /* //               일부 코드는 ajax.get_item.php 파일에서 가져옴. */

    
    /* // 최종 수정 일자 : 23.07.18 */
    /* // 최종 수정 내용 : 재고이관 조회용으로 파일을 신규 추가함. */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

include_once('./_common.php');

header('Content-type: application/json');

$it_id = $_POST["it_id"]; // 23.07.17 : 서원 - 기존 POST 글로벌 변수를 로컬 변수로 변경.
$ProdPayCode = $_POST["ProdPayCode"]; // 23.07.17 : 서원 - 급여코드 조회를 위한 변수 추가.

// 22.12.07 : 서원 - 검색어가 없을 경우 DB전체 검색되던 부분 중단 처리.
if( !$keyword ) {
  $rows = [];
  echo json_encode($rows);
  exit();
}

$sql = "    SELECT
                item.it_id,
                item.ca_id,
                CASE
                    WHEN item.ca_id LIKE '10%' THEN '[판매]'
                    WHEN item.ca_id LIKE '20%' THEN '[대여]'
                    ELSE NULL
                END AS 'ProdType',
                item.it_name,
                item.ProdPayCode,
                OPT.io_no,
                OPT.io_id
            FROM
                g5_shop_item AS item
            LEFT JOIN
                g5_shop_item AS PPC ON PPC.ProdPayCode = item.ProdPayCode
            LEFT JOIN
            	g5_shop_item_option AS OPT ON OPT.it_id = item.it_id
            WHERE
                (item.it_name LIKE '%{$keyword}%' OR item.it_id LIKE '%{$keyword}%')
                AND (item.ca_id LIKE '10%' OR item.ca_id LIKE '20%')
            GROUP BY
                item.it_id, OPT.io_no
            ORDER BY 
	            item.it_name, item.ca_id, OPT.io_no;
";
$result = sql_query($sql);

$rows = [];
while ( $row = sql_fetch_array($result) ) {

    $option_sql = "   SELECT *
                    FROM
                        g5_shop_item_option
                    WHERE
                        it_id = '{$row['it_id']}'
                    ORDER BY io_no ASC
    ";
    $option_result = sql_query($option_sql);

    $row['options'] = [];
    while ($option_row = sql_fetch_array($option_result)) { $row['options'][] = $option_row; }


    $ppc_sql = "  SELECT
                    item.it_id,
                    item.ca_id,
                    CASE
                        WHEN item.ca_id LIKE '10%' THEN '[판매]'
                        WHEN item.ca_id LIKE '20%' THEN '[대여]'
                        ELSE NULL
                    END AS 'ProdType',
                    item.it_name,
                    item.ProdPayCode
                FROM
                    g5_shop_item AS item
                WHERE
                    item.ProdPayCode = '{$row['ProdPayCode']}'
    ";
    $ppc_result = sql_query($ppc_sql);

    $row['ppc'] = [];
    while ($ppc_row = sql_fetch_array($ppc_result)) { $row['ppc'][] = $ppc_row; }

    $gubun = $cate_gubun_table[substr($row["ca_id"], 0, 2)];
    $gubun_text = '판매';
    if($gubun == '01') $gubun_text = '대여';
    else if($gubun == '02') $gubun_text = '비급여';

    $row['gubun'] = $gubun_text;


    $rows[] = $row;
}

echo json_encode($rows);
