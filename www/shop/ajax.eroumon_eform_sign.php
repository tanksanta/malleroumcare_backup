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
    /* // 파일명 : /www/shop/ajax.eroumon_eform_sign.php */
    /* // 파일 설명 : 이로움ON(1.5)에서 받은 주문을 처리하고, 계약서와 관련된 내용 부분을 처리 하는 페이지. */
    /*                "계약하기" 버튼을 실행하고 정상적으로 계약서가 작성이되고, 서명 요청이 되면 해당 페이지로 데이터가 넘어온다. */
    /*                해당 페이지는 웹훅으로 모두사인측에서 훅으로 받은 서명 완료 또는 거부에 대한 메시지를 같이 처리하는 루틴을 가진다. */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    include_once("./_common.php");

    // 처리 프로세스 타입
    $_type = sql_real_escape_string(strip_tags($_GET['type']));
    
    // 전자문서(계약서) 아이디값
    $_dcid = sql_real_escape_string(strip_tags($_GET['dcid']));

    // 모두싸인 회신 상태값
    $_event_type = sql_real_escape_string(strip_tags($_GET['event_type']));



    // 수급자 주문상세 페이지에서 "계약하기"버튼을 클릭 하고 정상처리되었을 경우 해당 페이지 GET 방식으로 호출하고 끝!!
    if( $_type == "SendOK" ) {

        if( $_dcid ) {
            
            $_dc = sql_fetch("  SELECT ED.*, API.order_send_id, API.od_sync_odid
                                FROM eform_document ED
                                LEFT JOIN g5_shop_order_api API ON API.od_sync_odid = ED.od_id 
                                WHERE `dc_id` = UNHEX('".$_dcid."') AND ED.dc_type='1'
            ");

            if( $_dc ) {

                sql_query(" UPDATE `g5_shop_order_api` SET od_status = '작성완료' WHERE order_send_id = '{$_dc['order_send_id']}' AND od_sync_odid = '{$_dc['od_sync_odid']}' ");
                api_log_write( $_dc['order_send_id'], $member["mb_id"], '3', "계약서 작성완료 및 서명요청 전달");

            }
        } 

    } else  if( $_type == "WebHook" ) {

        $_dc = sql_fetch("  SELECT ED.*, API.order_send_id, API.od_sync_odid
                            FROM eform_document ED
                            LEFT JOIN g5_shop_order_api API ON API.od_sync_odid = ED.od_id 
                            WHERE `dc_id` = UNHEX('".$_dcid."') AND ED.dc_type='1'
        ");
        
        if( $_dc ) {

            if( $_event_type == "document_all_signed" ) {

                // 서명완료
                sql_query(" UPDATE `g5_shop_order_api` SET od_status = '서명완료' WHERE order_send_id = '{$_dc['order_send_id']}' AND od_sync_odid = '{$_dc['od_sync_odid']}' ");
                api_log_write($_dc['order_send_id'], "WebHook", '3', "계약서 서명완료");

            } else if( 
                ($_event_type == "document_rejected") || 
                ($_event_type == "document_POST_canceled") || 
                ($_event_type == "document_signing_canceled")
            ) {

                // 서명거절 - document_rejected
                // 서명요청취소 - document_POST_canceled
                // 서명취소 - document_signing_canceled

                $_memo="";
                if ($_event_type == "document_rejected"){ $_memo = "서명거절"; }
                else if($_event_type == "document_POST_canceled"){ $_memo = "서명요청취소"; }
                else if($_event_type == "document_signing_canceled"){ $_memo = "서명취소"; }
                else { $_memo = "기타 문제"; }

                sql_query(" UPDATE `g5_shop_order_api` SET od_status = '출고완료' WHERE order_send_id = '{$_dc['order_send_id']}' AND od_sync_odid = '{$_dc['od_sync_odid']}' ");
                api_log_write($_dc['order_send_id'], "WebHook", '3', "계약서 {$_memo}로 인하여 상태값 변경.");

            }
            
        }
    }


?>