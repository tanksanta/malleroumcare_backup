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
    /* //  * Program Name : EROUMCARE Platform! = purchase Ver:0.1 */
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
    /* // 파일명 : /www/adm/shop_admin/ajax.payment_OnlineBilling_SetUpdate.php */
    /* // 파일 설명 :   온라인 결제(관리자화면) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  대금 청구서와 관련된 데이터 업데이트 파일( 버튼 On/Off ,  사업소 결제건 ) */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

$sub_menu = '500150';
include_once("./_common.php");

if( $_POST['mode_set']  == "cancelled" ) {

    // 관리자 화면 세부 청구취소
    $sql = ("   UPDATE `payment_billing_list`
                SET `billing_yn`  = 'N',
                `billing_status`  = '관리자취소',
                `pay_confirm_id`  = NULL,
                `pay_confirm_dt`  = NULL,
                `pay_confirm_receipt_id`  = NULL,
                `error_code`  = 'cancel',
                `error_event`  = '" . $member['mb_id'] . "',
                `error_msg`  = '사업소청구취소(" . $member['mb_name'] . ")',
                `error_dt`  = NOW()
                WHERE `bl_id` = '" . $_POST['bl_id'] . "'
    ");

    sql_query($sql);

} 
else if( $_POST['mode_set']  == "setting" ) {
    
    // 관리자 화면 설정 저장.
    $sql = ("   UPDATE `g5_shop_default` SET `de_paymenet_billing_OnOff`  = '" . json_encode( 
        array(
            "OnOff"=>$_POST['radio_onoff'],
            "start_dt"=>$_POST['select_start_dt'],
            "end_dt"=>$_POST['select_end_dt'],
            "fee_card"=>$_POST['fee_card']
        ) 
    ) . "' ");
    sql_query($sql);

}
else if( $_POST['mode_set']  == "fee_set" ) {

    // 사업소 수수료 개별 지정
    $sql = ("   UPDATE `payment_billing_list` 
                SET 
                    `billing_fee_yn`  = '" . $_POST['_yn'] . "', 
                    `billing_fee` = '" . $_POST['_fee'] . "'
                WHERE `bl_id` = '" . $_POST['bl_id'] . "'
            ");
    sql_query($sql);

}
else {
    json_response(400, 'error');
}

?>