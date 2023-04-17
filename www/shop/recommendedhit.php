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
    /* // 파일명 : \www\shop\recommendedhit.php */
    /* // 파일 설명 :   */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

include_once("./_common.php");

$pr_id = (int) $_GET['pr_id'];
$product = $_GET['product'];

$row = sql_fetch(" select sq, product1, product2, product3 from g5_shop_recommended where sq = '$pr_id' ");
if( !$row['sq'] ) { alert('해당 추천 데이터가 존재하지 않습니다.sq', G5_URL); }
if( ($row['product1']!=$product) && ($row['product2']!=$product) && ($row['product3']!=$product) ) { alert('해당 추천 데이터가 존재하지 않습니다.product', G5_URL); }


if( ($_COOKIE['ck_recommend1'] != $product) && ($row['product1'] == $product) ) {
    sql_query(" update g5_shop_recommended set product1_hit = product1_hit + 1 where sq = '$pr_id' ");

    // 하루 동안
    set_cookie("ck_recommend1", $product, 60*60*24);

} else if( ($_COOKIE['ck_recommend2'] != $product) && ($row['product2'] == $product) ) {
    sql_query(" update g5_shop_recommended set product2_hit = product2_hit + 1 where sq = '$pr_id' ");

    // 하루 동안
    set_cookie("ck_recommend2", $product, 60*60*24);

} else if( ($_COOKIE['ck_recommend3'] != $product) && ($row['product3'] == $product) ) {
    sql_query(" update g5_shop_recommended set product3_hit = product3_hit + 1 where sq = '$pr_id' ");

    // 하루 동안
    set_cookie("ck_recommend3", $product, 60*60*24);

}


$url = clean_xss_tags( G5_SHOP_URL . "/item.php?it_id=" . $product );
goto_url($url);

?>
