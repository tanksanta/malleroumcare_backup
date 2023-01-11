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
    /* // 파일명 : /www/shop/ajax.patment_OnlineBilling_Result.php */
    /* // 파일 설명 :   온라인 결제(사업소화면) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  대금 및 미수금 결제완료 후 PG사로부터 받은 데이터 저장 로직 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

include_once('./_common.php');

//var_dump($_POST);
//var_dump($_SERVER);

// 22.12.28 : 서원 - 접속 경로 확인 리퍼러(referer)
if( 
    ($_SERVER['REQUEST_URI'] == $_SERVER['SCRIPT_NAME']) &&
    ($_SERVER['REQUEST_URI'] == $_SERVER['PHP_SELF']) 
) {


    $_data = $_POST["data"];
    $_order_id = $_POST["order_id"];
    

    // 22.12.29 :  서원 - 회신된 데이터에 에러가 있을 경우.
    if( $_data['error_code'] ) {
        $sql = ("   UPDATE `payment_billing_list`
                    SET `error_code`  = '".$_data['error_code']."',
                        `error_event`  = '".$_data['event']."',
                        `error_msg`  = '".$_data['message']."',
                        `error_dt`  = NOW()
                    WHERE `bl_id` = '" . $_order_id . "'
        ");
        sql_query($sql);
        json_response(200, 'error');
    }
    
    else if( is_array($_data) && ($_data) ) {
        
        // 22.12.29 :  서원 - 온라인 결제 회신된 데이터 저장
        $sql = ("   INSERT `payment_api_request`
                    SET `bl_id`         = '" . $_order_id . "',
                        `receipt_id`        = '" . $_data['receipt_id'] . "',
                        `method_symbol`     = '" . $_data['method_symbol'] ."',
                        `status_locale`     = '" . $_data['status_locale'] . "',
                        `card_company`      = '" . $_data['card_data']['card_company']  . "',
                        `card_quota`        = '" . $_data['card_data']['card_quota'] . "',
                        `request_data`      = '" . json_encode($_data) . "' 
        ");
        sql_query($sql);
        
        // 22.12.29 :  서원 - 사업소 대금결제 정보 업데이트
        $sql = ("   UPDATE `payment_billing_list`
                    SET `billing_status`  = '" . $_data['status_locale'] . "',
                        `pay_confirm_id`  = '" . $member['mb_id'] . "',
                        `pay_confirm_dt`  = '" . $_data['purchased_at'] . "',
                        `pay_confirm_receipt_id`  = '" . $_data['receipt_id'] . "'
                    WHERE `bl_id` = '" . $_order_id . "'
        ");
        sql_query($sql);

        json_response(200, 'payconfirm');
    }

    else {

        json_response(200, 'etc');

    }

}


?>