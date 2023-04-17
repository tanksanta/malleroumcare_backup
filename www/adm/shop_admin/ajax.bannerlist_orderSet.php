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
    /* // 파일명 : \www\adm\shop_admin\ajax.bannerlist_orderSet.php */
    /* // 파일 설명 :   배너 순서관리에 필요한 AJAX 파일 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

include_once("./_common.php");

if( ($_POST['mode_set']  == "up") || ($_POST['mode_set']  == "down") ) {
    $_order1 = sql_fetch("  SELECT `bn_order` FROM `g5_shop_banner` WHERE `bn_id` ='" . $_POST['order1'] . "' ");
    $_order2 = sql_fetch("  SELECT `bn_order` FROM `g5_shop_banner` WHERE `bn_id` ='" . $_POST['order2'] . "' ");

    sql_query("  UPDATE `g5_shop_banner` SET `bn_order` = '" . $_order2['bn_order'] . "' WHERE `bn_id` ='" . $_POST['order1'] . "' ");
    sql_query("  UPDATE `g5_shop_banner` SET `bn_order` = '" . $_order1['bn_order'] . "' WHERE `bn_id` ='" . $_POST['order2'] . "' ");    
} 
else if( $_POST['mode_set']  == "AllSet" ) {
    $result = sql_query(" SELECT * FROM `g5_shop_banner` WHERE bn_status = 'Y' AND bn_position = '" . $_POST['position'] . "' ORDER BY bn_order, bn_id DESC" );

    if( is_object($result) && $result->num_rows ) {
        
        for($i=0; $row=sql_fetch_array($result); $i++) {
            sql_query("  UPDATE `g5_shop_banner` SET `bn_order` = '" . ($i+1) . "' WHERE `bn_id` ='" . $row['bn_id'] . "' ");
        }

    }
}
else {
    json_response(400, 'error');
}

?>