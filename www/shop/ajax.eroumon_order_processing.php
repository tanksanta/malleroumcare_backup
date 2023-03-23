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
    /* // 파일명 : /www/shop/ajax.eroumon_order_processing.php */
    /* // 파일 설명 : 이로움ON(1.5)에서 받은 주문건에 대한 '주문처리'를 진행하는 페이지 */
    /*                주문건에 있는 상품에 대한 승인과 반려를 진행하며, 그에 대한 사유를 이로움ON(1.5)로 다시 보내면서 내부 DB의 상태 값을 변경한다.*/
    /*                단!!!! 상품에 대한 리스트 형식으로 API 보내고 있으며, 수신측 오류로 인한 부분은 Manual 처리 하기로 되어있어 재전송과 관련된 프로세스는 검증하지 않는다. */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */


    include_once("./_common.php");
    
    $result = [];

    if( !$_POST['order_send_id'] ) {
        $result["YN"] = "N";
        $result["YN_msg"] = "주문 정보를 확인할 수 없습니다.";

        echo json_encode($result);
        exit();
    }


    if( COUNT($_POST['data'])==0 ) {
        $result["YN"] = "N";
        $result["YN_msg"] = "상품 정보를 확인할 수 없습니다.";

        echo json_encode($result);
        exit();
    }

    
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 기본 order_api 정보에 대한 테이블 조회 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    $_od = sql_fetch("  SELECT API.*, MB.mb_name, MB.mb_email, MB.mb_tel, MB.mb_hp, MB.mb_zip1, MB.mb_zip2, MB.mb_addr1, MB.mb_addr2, MB.mb_addr3, MB.mb_password, MB.mb_entId
                        FROM g5_shop_order_api API
                        LEFT JOIN g5_member MB ON MB.mb_id = API.mb_id
                        WHERE API.order_send_id = '" . $_POST['order_send_id'] . "' AND API.mb_id = '" . $member['mb_id'] . "'
    ");

    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 기본 order_api 정보에 대한 테이블 조회 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==


    // 주문처리 로그.
    api_log_write($_POST['order_send_id'], $member["mb_id"], '3', $member["mb_name"] . "(".$member["mb_id"].") - 수급자 주문상세 [주문처리] 버튼 클릭.");


    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 수급자 정보 WMDS 조회 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    
    $send_data = [];
    $send_data["usrId"] = $_od["mb_id"];
    $send_data["entId"] = $_od["mb_entId"];
    $send_data['penNm'] = $_od["od_penNm"];
    $send_data['penLtmNum'] = "L".$_od["od_penLtmNum"]; 
    //var_dump($send_data);

    $res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);
    //var_dump($res);

    if( (COUNT($res)==0) || ($res["errorYN"]=="Y")  ) {
        
        $result["YN"] = "N";
        $result["YN_msg"] = "수급자 조회 정보가 정확하지 않습니다.\n\n수급자의 요양정보조회를 조회 후 처리 가능합니다.";
    
        // 주문처리 로그.
        api_log_write($_POST['order_send_id'], $member["mb_id"], '3', $member["mb_name"] . "(".$member["mb_id"].") - 사업소에 등록된 수급자 없음.");
        
        echo json_encode($result);
        exit();

    } else if( ($res["errorYN"]=="N") ){
        
        // 수급자 정보가 있으나, 주문처리 시점 업데이트를 하지아 수급자의 구매가능 여부 미확인.
        /* 테스트를 위해 주석 처리 */
        if( mb_substr($res["data"][0]["modifyDtm"],0,8) != date("Ymd") ){
            $result["YN"] = "N";
            $result["YN_msg"] = "수급자의 요양정보 확인 후 '주문처리' 가능합니다.\n\n수급자의 요양정보를 업데이트 해주세요.";
        
            // 주문처리 로그.
            api_log_write($_POST['order_send_id'], $member["mb_id"], '3', $member["mb_name"] . "(".$member["mb_id"].") - 수급자 최신 정보(".$res["data"][0]["modifyDtm"].") 업데이트 되지 않음.");
            
            echo json_encode($result);
            exit();
        }          

    } else {

    }



    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 수급자 정보 WMDS 조회 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==





    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 내부 Table Data 변경 처리
    // g5_shop_order_api : 주문 정보의 상태값을 변경 한다. ( od_status )
    // g5_shop_cart_api : 해당 주문 정보의 상품별 상태 값을 정의 한다. ( ct_status / ct_memo )
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    $_sql = [];
    
    // 이로움ON(1.5) 주문건에 대한 상태값 업데이트 ( 23.03.15 - API에서 업데이트 하는것으로 변경. )
    //$_sql[] = " UPDATE g5_shop_order_api SET od_status = '주문처리' WHERE order_send_id = '" . $_POST['order_send_id'] . "' AND mb_id = '" . $member['mb_id'] . "'";

    // 이로움ON(1.5) 주문의 하위 상품별 상태값 업데이트
    foreach( $_POST['data'] as $key => $val ) {
         

        $_sataus = $_memo = "";
        if( $val['reject_msg'] ) { $_sataus = "반려"; $_memo = $val['reject_msg']; } else { $_sataus = "승인"; $_memo = ""; }


        $_sql[]  = ("   UPDATE g5_shop_cart_api 
                        SET ct_status = '{$_sataus}', 
                            ct_memo = '{$_memo}' 
                        WHERE od_id = '" . $_POST['order_send_id'] . "' 
                        AND ct_id = '" . $val['ct_id'] . "' 
                        AND it_id = '" . $val['it_id'] . "' 
                        AND mb_id = '" . $member['mb_id'] . "'
        ");
    }

    //var_dump($_sql);

    // 23.03.08 : 서원 - 트랜잭션 시작
    sql_query("START TRANSACTION");

    try {

        foreach($_sql as $sql) {
            sql_query($sql);
        }

        // 23.03.08 : 서원 - 트랜잭션 커밋
        sql_query("COMMIT");

        $result["YN"] = "Y";
        $result["YN_msg"] = "";

    } catch (Exception $e) {
        // 23.03.08 : 서원 - 트랜잭션 롤백
        sql_query("ROLLBACK");

        $result["YN"] = "N";
        $result["YN_msg"] = "주문 처리 과정에 오류가 발생하였습니다.(sql)";
    }






    //http://192.168.0.229/eroumcareApi/bplcRecv/callback.json
    /*
    {
        "ORDR_CD": "O30220105516924",
        "_array_item": [{
            "ORDR_DTL_CD": "O30216113803874_1",
            "STTS_TY": "OR02"
        },
        {
            "ORDR_DTL_CD": "O30216113803874_2",
            "STTS_TY": "OR03",
            "RESN": "금액 초과"
        }]
    }
    */


    echo json_encode($result);

?>