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
    /* // 파일 작성 일자 : 23.07.25 */
    /* // 파일 작성자 : 박서원 */
    /* // 파일명 : /www/shop/ajax.barcode_transfer_moveit.php */
    /* // 파일 설명 : 바코드의 이관 자료를 ajax로 받아 처리하는 파일 */
    /* //             */
    
    /* // 최종 수정 일자 : 23.07.25 */
    /* // 최종 수정 내용 : 재고이관을 위해 신규 파일 생성.*/
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

include_once('./_common.php');
header('Content-type: application/json');


    // 23.07.25 : 서원 - POST로 넘어온 변수 글로벌 변수에서 내부 변수로 변경.
    $_borcodeData = $_POST['borcodeData'];
    $_prevItid = $_POST['prev_itid'];
    $_prevIoid = $_POST['prev_ioid'];
    $_nextItid = $_POST['next_itid'];
    $_nextIoid = $_POST['next_ioid'];


    $result = array();

    if( !$_borcodeData ) {
        $result["YN"] = "N";
        $result["YN_msg"] = "BarCode 데이터 오류.";
        echo json_encode($result); exit();
    } else if( !$_prevItid || !$_nextItid ) {
        $result["YN"] = "N";
        $result["YN_msg"] = "Itid 데이터 오류.";
        echo json_encode($result); exit();
    }


    $_sql_barcode = $_sql_barcode_prev_log = $_sql_barcode_next_log = [];
    foreach ($_borcodeData as $key => $val) {

        $temp = sql_fetch(" SELECT bc_id, it_id, io_id, bc_barcode
                            FROM `g5_cart_barcode` 
                            WHERE `bc_id` = '{$val}' AND `it_id` = '{$_prevItid}' AND `io_id` = '{$_prevIoid}' AND `bc_del_yn` = 'N' AND `bc_status` NOT IN ('출고', '관리자승인대기', '관리자승인완료', '관리자삭제')
        ");

        // 바코드 이관에 필요한 SQL 처리 부분.
        $_sql_barcode[] = ("    UPDATE `g5_cart_barcode`
                                SET it_id = '{$_nextItid}', `io_id` = '{$_nextIoid}', `released_at` = NOW()
                                WHERE `bc_id` = '{$val}' AND `it_id` = '{$_prevItid}' AND `io_id` = '{$_prevIoid}' AND `bc_del_yn` = 'N' AND `bc_status` NOT IN ('출고', '관리자승인대기', '관리자승인완료', '관리자삭제');
        ");

        // 바코드 이관에 따른 로그기록을 위한 처리 부분.
        $_sql_barcode_prev_log[] = ("   INSERT INTO `g5_cart_barcode_log`
                                        SET `bc_id` = '{$temp['bc_id']}', `it_id` = '{$temp['it_id']}', `io_id` = '{$temp['io_id']}', `bch_barcode` = '{$temp['bc_barcode']}', `bch_status` = '관리자삭제'
                                        ,`bch_content` = '재고이동 - {$_prevItid} {$_prevIoid} → {$_nextItid} {$_nextIoid}', `created_by` = '{$member['mb_id']}';
        ");
        // 바코드 이관에 따른 로그기록을 위한 처리 부분.
        $_sql_barcode_next_log[] = ("   INSERT INTO `g5_cart_barcode_log`
                                        SET `bc_id` = '{$temp['bc_id']}', `it_id` = '{$_nextItid}', `io_id` = '{$_nextIoid}', `bch_barcode` = '{$temp['bc_barcode']}', `bch_status` = '정상'
                                        ,`bch_content` = '재고이동 - {$_prevItid} {$_prevIoid} → {$_nextItid} {$_nextIoid}', `created_by` = '{$member['mb_id']}';

        ");

    }

    //var_dump( $_sql_barcode );
    //var_dump( $_sql_barcode_prev_log );
    //var_dump( $_sql_barcode_next_log );

    // 23.07.25 : 서원 - 트랜잭션 시작
    sql_query("START TRANSACTION");

    $_error = [];
    foreach($_sql_barcode as $key => $sql) {

        $sql_result = sql_query( $sql );

        if ($sql_result) {
            // 쿼리가 성공적으로 실행되었습니다.
            sql_query( $_sql_barcode_prev_log[$key] );
            sql_query( $_sql_barcode_next_log[$key] );

        } else {
            // 쿼리가 정상적으로 실행되지 않았습니다.
            $temp = sql_fetch(" SELECT bc_barcode, bc_status FROM `g5_cart_barcode` WHERE `bc_id` = '{$_borcodeData[$key]}' AND `it_id` = '{$_prevItid}' AND `io_id` = '{$_prevIoid} '");
            $_error[$key] = $temp['bc_barcode'] . " - " . $temp['bc_status'];
        }

    }

    // 23.07.25 : 서원 - 트랜잭션 커밋
    //sql_query("ROLLBACK");

    // 23.07.25 : 서원 - 트랜잭션 커밋
    sql_query("COMMIT");


    $result["YN"] = "Y";
    $result["YN_msg"] = "처리완료";

    if( COUNT($_error) ) { 
         // 23.07.25 : 서원 - 바코드 이동 과정에 출고나, 삭제 등의 문제로 인한 이동에 문제 발생하였을 경우해당 바코드에 대한 안내를 위한 항목.
        $result["ERROR"] = $_error;    
        $result["YN_msg"] = "부분 처리완료";
    }
    
    echo json_encode($result); exit();

?>