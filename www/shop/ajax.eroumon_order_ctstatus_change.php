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
    /* // 파일명 : /www/shop/ajax.eroumon_order_ctstatus_change.php */
    /* // 파일 설명 : 이로움ON(1.5)에서 받은 주문건에서 상품별 반려 사유 또는 승인에 대한 데이터를 임시 저장 하는 프로세스 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */


    include_once("./_common.php");
    
    $result = [];

    if( !$_POST['order_send_id'] ) {
        $result["YN"] = "N";
        $result["YN_msg"] = "주문 정보를 확인할 수 없습니다.";

        echo json_encode($result);
        exit();
    }

    if( !$_POST['ctid'] ) {
        $result["YN"] = "N";
        $result["YN_msg"] = "상품 정보를 확인할 수 없습니다.";

        echo json_encode($result);
        exit();
    }

    
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 기본 order_api 정보에 대한 테이블 조회 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    //var_dump($_POST);

    $_status = "";
    if( $_POST['txt'] ) {
        $_status = "반려";
    } else {
        $_status = "승인";
    }

    $_ct = sql_fetch("  SELECT *
                            FROM g5_shop_cart_api
                            WHERE order_send_id = '" . $_POST['order_send_id'] . "' 
                                AND ct_id = '" . $_POST['ctid'] . "' 
                                AND mb_id = '" . $member['mb_id'] . "'
    ");
    //var_dump($_ct);

    $_sql  = ("   UPDATE g5_shop_cart_api 
                    SET ct_status = '{$_status}', 
                        ct_memo = '{$_POST['txt']}' 
                    WHERE order_send_id = '" . $_POST['order_send_id'] . "' 
                    AND ct_id = '" . $_POST['ctid'] . "' 
                    AND mb_id = '" . $member['mb_id'] . "'
    "); 
    //var_dump($_sql);


    // 상품로그.
    api_log_write($_POST['order_send_id'], $member["mb_id"], '3', $member["mb_name"] . "(".$member["mb_id"].") - ".$_ct["it_name"].":".$_status.( ($_POST['txt'])?("[".$_POST['txt']."]"):"" )." / 상품 상태 변경.");


    // 23.03.08 : 서원 - 트랜잭션 시작
    sql_query("START TRANSACTION");

    try {
        
        sql_query($_sql);
        
        // 23.03.08 : 서원 - 트랜잭션 커밋
        sql_query("COMMIT");


        $result["YN"] = "Y";
        $result["YN_msg"] = "";

    } catch (Exception $e) {
        // 23.03.08 : 서원 - 트랜잭션 롤백
        sql_query("ROLLBACK");

        $result["YN"] = "N";
        $result["YN_msg"] = "처리 과정에 오류가 발생하였습니다.(sql)";
    }


    echo json_encode($result);

?>