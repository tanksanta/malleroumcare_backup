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
    /* // 파일명 : /www/api/Webhook_paymenet_OnlineBilling.php */
    /* // 파일 설명 :   온라인 결제(관리자webhook) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  결제 완료 및 취소에 대한 세부적인 회신 내용을 webhook방식으로 재전송 받음. 결제에 대한 2차 검증 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

	include_once("./_common.php");


    $_postDATA = json_decode(file_get_contents( 'php://input' ), TRUE);


    //$_file_path = G5_DATA_PATH."\\cache\\".date("Ymd_His").".txt"; // WebHook 디버그용
    //$fp = fopen($_file_path, 'a+'); // WebHook 디버그용


    if( isset($_postDATA) && !isset($_postDATA['error_code']) ) {
        //fwrite($fp, "결제성공" );  // WebHook 디버그용
        

        $sql = "";
        $sql = ("  SELECT 
                        COUNT(bl_id) as cnt
                      FROM 
                        payment_api_request 
                      WHERE 
                        `bl_id`             = '" . $_postDATA['order_id'] . "'
                        AND `receipt_id`        = '" . $_postDATA['receipt_id'] . "'
                        AND `status_locale`     = '" . $_postDATA['status_locale'] . "'
        ");      
        $_sql = sql_fetch($sql);

        
        
        if( ( $_sql['cnt']==0 ) && isset($_postDATA['status_locale']) && ($_postDATA['status_locale']=="결제성공") ) {


            // 22.12.29 :  서원 - 온라인 결제 회신된 데이터 저장
            $sql = ("   INSERT `payment_api_request`
                        SET `bl_id`             = '" . $_postDATA['order_id'] . "',
                            `receipt_id`        = '" . $_postDATA['receipt_id'] . "',
                            `price`             = '" . $_postDATA['price'] . "',
                            `method_symbol`     = '" . $_postDATA['method_symbol'] ."',
                            `status_locale`     = '" . $_postDATA['status_locale'] . "',
                            `card_company`      = '" . $_postDATA['card_data']['card_company']  . "',
                            `card_quota`        = '" . $_postDATA['card_data']['card_quota'] . "',
                            `request_data`      = '" . file_get_contents( 'php://input' ) . "' 
            ");
            sql_query($sql);

            $sql = ("   UPDATE `payment_billing_list`
                        SET `billing_status`  = '" . $_postDATA['status_locale'] . "',
                        `pay_confirm_id`  = '" . $member['mb_id'] . "',
                        `pay_confirm_dt`  = NOW(),
                        `pay_confirm_receipt_id`  = '" . $_postDATA['receipt_id'] . "'
                        WHERE `bl_id` = '" . $_postDATA['order_id'] . "'
            ");
            sql_query($sql);
        }
        else if( ( $_sql['cnt']==0 ) && isset($_postDATA['status_locale']) && ($_postDATA['status_locale']=="결제취소완료") ) {

            // 22.12.29 :  서원 - 온라인 결제 회신된 데이터 저장
            $sql = ("   INSERT `payment_api_request`
                        SET `bl_id`             = '" . $_postDATA['order_id'] . "',
                            `receipt_id`        = '" . $_postDATA['receipt_id'] . "',
                            `price`             = '" . $_postDATA['price'] . "',
                            `method_symbol`     = '" . $_postDATA['method_symbol'] ."',
                            `status_locale`     = '" . $_postDATA['status_locale'] . "',
                            `card_company`      = '" . $_postDATA['card_data']['card_company']  . "',
                            `card_quota`        = '" . $_postDATA['card_data']['card_quota'] . "',
                            `request_data`      = '" . file_get_contents( 'php://input' ) . "' 
            ");
            sql_query($sql);

            $sql = ("   UPDATE `payment_billing_list`
                        SET `billing_status`  = '" . $_postDATA['status_locale'] . "',
                        `billing_yn` = 'N',
                        `pay_confirm_id`  = NULL,
                        `pay_confirm_dt`  = NULL,
                        `pay_confirm_receipt_id`  = NULL,
                        `error_code`  = 'API',
                        `error_event`  = 'system',
                        `error_msg`  = '".$_postDATA['status_locale']."',
                        `error_dt`  = NOW()
                        WHERE `bl_id` = '" . $_postDATA['order_id'] . "'
            ");
            sql_query($sql);
        }

    } else {
        //fwrite($fp, "결제실패" ); // WebHook 디버그용

        if( $_postDATA['payload'] ) {
          $sql = "";
          $sql = ("  SELECT 
                          COUNT(bl_id) as cnt
                        FROM 
                          payment_api_request 
                        WHERE 
                          `bl_id`             = '" . $_postDATA['payload']['order_id'] . "',
                          AND `receipt_id`        = '" . $_postDATA['payload']['receipt_id'] . "',
                          AND `status_locale`     = '" . $_postDATA['payload']['status_locale'] . "',
          ");      
          $_sql = sql_fetch($sql);

          // 22.12.29 :  서원 - 온라인 결제 회신된 데이터 저장
          if( $_sql['cnt']==0 ) {
              $sql = ("   INSERT `payment_api_request`
                          SET `bl_id`         = '" . $_postDATA['payload']['order_id'] . "',
                          `receipt_id`        = '" . $_postDATA['payload']['receipt_id'] . "',
                          `method_symbol`     = '" . $_postDATA['payload']['method_symbol'] ."',
                          `status_locale`     = '" . $_postDATA['payload']['status_locale'] . "',
                          `request_data`      = '" . file_get_contents( 'php://input' ) . "' 
              ");
              sql_query($sql);

              $sql = ("   UPDATE `payment_billing_list`
                          SET `billing_status`  = '" . $_postDATA['payload']['status_locale'] . "',
                          `pay_confirm_id`  = NULL,
                          `pay_confirm_dt`  = NULL,
                          `pay_confirm_receipt_id`  = NULL,
                          `error_code`  = 'API',
                          `error_event`  = 'system',
                          `error_msg`  = '".$_postDATA['payload']['status_locale']."',
                          `error_dt`  = NOW()
                          WHERE `bl_id` = '" . $_postDATA['payload']['order_id'] . "'
              ");
              sql_query($sql);

          }
        }
    }


    //fwrite($fp, file_get_contents( 'php://input' ) ); // WebHook 디버그용
    //fclose($fp); // WebHook 디버그용


    // Telegram Webhook
    $apikey = '5891345081:AAFGuIDq04PRw8_t825eAVWg7r5IxL4NESo';
    if( ($_SERVER["HTTP_HOST"] == 'www.eroumcare.com') || ($_SERVER["HTTP_HOST"] == 'eroumcare.com') ) {
      $chat_id = '-1001829530472'; // 상용
    } else {
      $chat_id = '-1001289883785'; // 테스트 & 개발
    }
    $params = 'chat_id='.$chat_id.'&text='.urlencode(file_get_contents( 'php://input' ));
    $webhook = 'https://api.telegram.org/bot'.$apikey.'/sendMessage';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhook); // Webhook URL
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $return = curl_exec($ch);
    curl_close($ch);


?>
{ "success": true }