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
    /* // 파일명 : /www/shop/ajax.patment_OnlineBilling_Check.php */
    /* // 파일 설명 :   온라인 결제(사업소화면) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  대금 및 미수금 결제시 해당 결제건이 정상 상태인지 확인 체크 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

include_once('./_common.php');

//var_dump($_POST);
//var_dump($_SERVER);

// 22.12.28 : 서원 - 접속 경로 확인 리퍼러(referer)
if( 
    ($_SERVER['REQUEST_URI'] == $_SERVER['SCRIPT_NAME']) &&
    ($_SERVER['REQUEST_URI'] == $_SERVER['PHP_SELF']) 
) {

    $_order_id = $_POST["order_id"];
    
    $sql = ("   SELECT COUNT(bl_id) as cnt 
                FROM `payment_billing_list` 
                WHERE (`bl_id` = '" . $_order_id . "') 
                AND billing_yn = 'Y'
                AND ( pay_confirm_id IS NULL OR pay_confirm_id = '' )
                AND ( pay_confirm_receipt_id IS NULL OR pay_confirm_receipt_id = '' )
            ");
    $_sql_bl = sql_fetch($sql);

    if( $_sql_bl['cnt'] > 0 ) {
        json_response(200, 'OK');
    } else {
        json_response(400, 'ERROR');
    }

}
json_response(400, 'ERROR');

?>