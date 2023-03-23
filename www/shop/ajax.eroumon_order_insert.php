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
  /* // 파일명 : /www/shop/ajax.eroumon_order_insert.php */
  /* // 파일 설명 : 이로움ON(1.5)에서 발생한 주문건에 대한 이용자가 이로움ON(1.5)에서 결제 완료 후 관리자가 직접 주문처리하기 위한 페이지 */
  /*                해당 프로세스 파일은 일반적인 주문저장 방식이 아닌 이로움ON(1.5)를 처리하기 위한 전용 파일임으로 주문처리 방식이 변경될 경우 해당 파일을 반드시 수정 해야함.*/
  
  /* 
  //    SQL 관려 코멘트
  //    ALTER TABLE `g5_shop_order`
  //      ADD COLUMN `od_type` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT '주문 타입( 0-이로움1.0 / 1-이로움ON(1.5) )' AFTER `tr_date`;
  //
  //    ALTER TABLE `g5_shop_cart`
  //      ADD COLUMN `ct_type` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT '주문 타입( 0-이로움1.0 / 1-이로움ON(1.5) )' AFTER `ct_time_int`;
  //
  //    ALTER TABLE `eform_document`
  //      ADD COLUMN `dc_type` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT '계약서 타입( 0-이로움1.0 / 1-이로움ON(1.5) )' AFTER `contract_sign_relation`;
  */
  /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

  include_once('./_common.php');


  if( !$_POST['order_send_id'] ) {

    $result["YN"] = "N";
    $result["YN_msg"] = "[이로움ON]의 주문정보를 찾을 수 없습니다.";

    echo json_encode($result); exit();
  }


  // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
  // SQL 처리 부분 시작
  // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

  // Order 강제 테스트용
  //$_POST = array( "order_send_id"=> "O3031413590934" );

  $_od = sql_fetch("  SELECT API.*, MB.mb_name, MB.mb_email, MB.mb_tel, MB.mb_hp, MB.mb_zip1, MB.mb_zip2, MB.mb_addr1, MB.mb_addr2, MB.mb_addr3, MB.mb_password, MB.mb_entId
                      FROM g5_shop_order_api API
                      LEFT JOIN g5_member MB ON MB.mb_id = API.mb_id
                      WHERE API.order_send_id = '" . $_POST['order_send_id'] . "' AND API.mb_id = '" . $member['mb_id'] . "'
  ");
  $_ct = sql_query("  SELECT API.*,
                              IT.it_name, IT.it_default_warehouse, IT.it_price, IT.it_price_dealer, IT.it_price_dealer2, IT.prodSupYn,
                              IT.it_delivery_min_cnt, IT.it_delivery_min_price, IT.it_delivery_cnt, IT.it_delivery_price, IT.it_is_direct_delivery,
                              IT.it_option_subject, IT.pt_it, IT.prodPayCode,
	                            ITOP.io_no, ITOP.io_id, ITOP.io_price, ITOP.io_thezone
                      FROM g5_shop_cart_api API
                      LEFT JOIN g5_shop_item IT ON IT.it_id = API.it_id
                      LEFT JOIN g5_shop_item_option ITOP ON (ITOP.it_id = API.it_id) AND (ITOP.io_id = API.io_id)
                      WHERE API.order_send_id = '" . $_POST['order_send_id'] . "' AND API.mb_id = '" . $member['mb_id'] . "' AND API.ct_status='승인'
                      ORDER BY API.ct_id DESC
  ");

  if( !$_od['order_send_id'] || ( $_ct->num_rows == 0 ) ) {

      $result["YN"] = "N";
      $result["YN_msg"] = "[이로움ON]의 주문 데이터를 확인할 수 없습니다.";

      echo json_encode($result); exit();
  }

  if( $_od['od_sync_odid'] ) {

      $result["YN"] = "N";
      $result["YN_msg"] = "[주문하기] 완료 처리된 주문건 입니다.";
      $result["YN_reload"] = "1";

      // 이로움ON(1.5) 주문건에 대한 상태값 업데이트
      sql_query("  UPDATE `g5_shop_order_api` SET `od_status` = '주문완료' WHERE `order_send_id` = '" . $_POST['order_send_id'] . "' AND `mb_id` = '" . $member['mb_id'] . "'");

      echo json_encode($result); exit();
  }

  // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
  // SQL 처리 부분 종료
  // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
 
  
  
    // 주문하기 로그.
    api_log_write($_POST['order_send_id'], $member["mb_id"], '3', $member["mb_name"] . "(".$member["mb_id"].") - 수급자 주문상세 [주문하기] 버튼 클릭.");


  
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
    $result["YN_msg"] = "사업소와 수급자 조회 정보가 정확하지 않습니다.\n\n수급자의 요양정보조회를 조회 후 '주문처리' 가능합니다.";

    echo json_encode($result); exit();
  }

  // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
  // 수급자 정보 WMDS 조회 종료
  // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
  


  
  
  // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

  $od_id = get_uniqid();
  $so_nb = get_uniqid_so_nb();


  // 주문서 저장
  $_sql_order = ("  INSERT g5_shop_order
                    SET `od_id`             = '{$od_id}',
                        `mb_id`             = '{$_od['mb_id']}',
                        `od_name`           = '{$_od['mb_name']}',
                        `od_email`          = '{$_od['mb_email']}',
                        `od_tel`            = '{$_od['mb_tel']}',
                        `od_hp`             = '{$_od['mb_hp']}',
                        `od_zip1`           = '{$_od['mb_zip1']}',
                        `od_zip2`           = '{$_od['mb_zip2']}',
                        `od_addr1`          = '{$_od['mb_addr1']}',
                        `od_addr2`          = '{$_od['mb_addr2']}',
                        `od_addr3`          = '{$_od['mb_addr3']}',
                        `od_b_name`         = '{$_od['od_b_name2']}',
                        `od_b_tel`          = '{$_od['od_b_tel']}',
                        `od_b_hp`           = '{$_od['od_b_hp']}',
                        `od_b_zip1`         = '{$_od['od_b_zip1']}',
                        `od_b_zip2`         = '{$_od['od_b_zip2']}',
                        `od_b_addr1`        = '{$_od['od_b_addr1']}',
                        `od_b_addr2`        = '{$_od['od_b_addr2']}',
                        `od_b_addr3`        = '{$_od['od_b_addr3']}',
                        `od_pwd`            = '{$_od['mb_password']}',
                        `od_time`           = NOW(),
                        `od_ip`             = '{$_SERVE['REMOTE_ADDR']}',
                        `od_send_cost`      = '0',
                        `od_settle_case`    = '월 마감 정산',
                        `od_status`         = '준비',
                        `od_writer`         = '{$member['mb_id']}',
                        `od_add_admin`      = '0',
                        `od_type`           = '1',
                        `od_memo`           = '{$_od['od_memo']}',

                        `od_penId`          = '{$res['data'][0]['penId']}',
                        `od_penNm`          = '{$res['data'][0]['penNm']}',
                        `od_penRecGraNm`    = '{$res['data'][0]['penRecGraNm']}',
                        `od_penTypeNm`      = '{$res['data'][0]['penTypeNm']}',
                        `od_penExpiDtm`     = '{$res['data'][0]['penExpiDtm']}',
                        `od_penAppEdDtm`    = '{$res['data'][0]['penAppEdDtm']}',
                        `od_penConPnum`     = '{$res['data'][0]['penProConPnum']}',
                        `od_penConNum`      = '{$res['data'][0]['penProConNum']}',
                        `od_penzip1`        = '" . mb_substr($res['data'][0]['penProZip'],0,3) . "',
                        `od_penzip2`        = '" . mb_substr($res['data'][0]['penProZip'],3,3) . "',
                        `od_penAddr`        = '" . $res['data'][0]['penProAddr'] . " ". $res['data'][0]['penProAddrDtl'] . "',
                        `od_penLtmNum`      = '{$res['data'][0]['penLtmNum']}',
                                              
                        `so_nb`             = '{$so_nb}',
                        `recipient_yn`      = 'Y'
  ");

  // 상품별 개별 카트 저장 프로세스
  $_sql_cart = []; // 카트 입력용 SQL 저장 배열
  $_sql_eroumon_cart = []; // 이로움(1.5)주문건에 상품별(CART테이블) 실주문건의 ct_id 매칭을 위한 SQL 저장 배열

  // === === === === === === === === === ===
  // == foreach 시작 ==
  // === === === === === === === === === ===
  foreach( $_ct as $key => $val ) {
    //var_dump( $val );

    // === === === === === === === === === === === === === === === === === === === === === === === === === === === === === ===
    // CART 테이블 저장 전 데이터 정리 시작
    // === === === === === === === === === === === === === === === === === === === === === === === === === === === === === ===

    // 대여기간
    $sqlOrdLendStrDtm = 'NULL';
    $sqlOrdLendEndDtm = 'NULL';
    if ($ordLendStartDtm && $ordLendEndDtm) {
      $sqlOrdLendStrDtm = "'{$ordLendStartDtm}'";
      $sqlOrdLendEndDtm = "'{$ordLendEndDtm}'";
    }

    // 수급자 여부
    $_ct_pen_id = 'NULL';
    if($res['data'][0]['penId']) { $_ct_pen_id = $res['data'][0]['penId']; }

    // 출하창고
    $ct_warehouse = '검단창고';
    if($val['it_default_warehouse']) { $ct_warehouse = $val['it_default_warehouse']; }

    $ct_delivery_company = 'cjlogistics';
    $uid = uuidv4();


    // ---------------------------
    // 옵션 정보 처리 시작 ---------------------------
    // ---------------------------
    $io_id = $val['io_id'];
    $io_type = preg_replace('#[^01]#', '', 0);
    $io_value = '';
    if ($io_id) {
      $it_option_subjects = explode(',', $val['it_option_subject']);
      $io_ids = explode(chr(30), $io_id);
      for($g = 0; $g< count($io_ids); $g++) {
        if ($g > 0) {
          $io_value .= ' / ';
        }
        $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
      }
    }
    $io_value = sql_real_escape_string(strip_tags($io_value));
    $io_value = $io_value ? $io_value : addslashes($val['it_name']);
    // ---------------------------
    // 옵션 정보 처리 종료 ---------------------------
    // ---------------------------

    

    // ---------------------------
    // 가격 관련 시작 ---------------------------
    // ---------------------------
    // 상품 기본 가격
    $it_price = $val['it_price'];

    // 우수사업소 할인
    if($member['mb_level'] == 4 && $val['it_price_dealer2']) {
        $it_price = $val['it_price_dealer2'];
    }

    // 사업소별 판매가
    $entprice = sql_fetch(" select it_price from g5_shop_item_entprice where it_id = '{$val['it_id']}' and mb_id = '{$_od['mb_id']}' ");
    if($entprice['it_price'] > 0) {
        $it_price = $entprice['it_price'];
    }
    // ---------------------------
    // 가격 관련 종료 ---------------------------
    // ---------------------------



    // ---------------------------
    // 배송박스 계산 처리 시작  ---------------------------
    // ---------------------------
    if($val['it_delivery_min_cnt']) {
      //박스 개수 큰것 +작은것 - >ceil
      $ct_delivery_cnt = $val['it_delivery_cnt'] ? ceil($val['ct_qty'] / $val['it_delivery_cnt']) : 0;
      //큰박스 floor 한 가격을 담음
      $ct_delivery_bigbox = $val['it_delivery_cnt'] ? floor($val['ct_qty'] / $val['it_delivery_cnt']) : 0;
      $ct_delivery_price = $val['it_delivery_cnt'] ? ($ct_delivery_bigbox * $val['it_delivery_price']) : 0;
      //나머지
      $remainder = $val['ct_qty'] % $val['it_delivery_cnt'];
      //나머지가 있으면
      if($remainder) {
        //나머지가 최소수량보다 작으면
        if($remainder <= $val['it_delivery_min_cnt']) {
          //작은 박스 가격 더해줌
          $ct_delivery_price = $ct_delivery_price + $val['it_delivery_min_price'];
        } else {
          //큰 박스 가격 더해줌
          $ct_delivery_price = $ct_delivery_price + $val['it_delivery_price'];
        }
      }
    } else {
      //없으면 큰박스로만 진행
      $ct_delivery_cnt = $val['it_delivery_cnt'] ? ceil($val['ct_qty'] / $val['it_delivery_cnt']) : 0;
      $ct_delivery_price = $ct_delivery_cnt * $val['it_delivery_price'];
    }
    // ---------------------------
    // 배송박스 계산 처리 시작  ---------------------------
    // ---------------------------


    // === === === === === === === === === === === === === === === === === === === === === === === === === === === === === ===
    // CART 테이블 저장 전 데이터 정리 종료
    // === === === === === === === === === === === === === === === === === === === === === === === === === === === === === ===


    //var_dump( $val );
    // 카트 데이터 입력을 위한 INSERT 생성.
    $_sql_cart[] = (" INSERT `g5_shop_cart`
                      SET `od_id`                       = '{$od_id}',
                          `mb_id`                       = '{$_od['mb_id']}',
                          
                          `ct_status`                   = '준비',
                          `ct_price`                    = '{$it_price}',
                          `ct_qty`                      = '{$val['ct_qty']}',
                          `ct_notax`                    = '{$val['ct_notax']}',
                          `ct_time`                     = NOW(),
                          `ct_ip`                       = '',
                          `ct_send_cost`                = '0',
                          `ct_direct`                   = '1',
                          `ct_select`                   = '1',
                          `ct_select_time`              = NOW(),
                          `ct_history`                  = '',
                          `ct_discount`                 = '',
                          `ct_price_type`               = '',
                          `ct_uid`                      = '{$uid}',
                          `ct_delivery_cnt`             = '{$ct_delivery_cnt}',
                          `ct_delivery_price`           = '0', /* {$ct_delivery_price} 이로움ON 주문건 배송비 무료*/
                          `ct_delivery_company`         = '{$ct_delivery_company}',
                          `ct_is_direct_delivery`       = '{$val['it_is_direct_delivery']}',
                          `ct_direct_delivery_price`    = '0',
                          `ct_pen_id`                   = '{$_ct_pen_id}',
                          `ct_warehouse`                = '{$ct_warehouse}',
                          `ct_type`                     = '1',
                          `ct_option`                   = '{$io_value}',

                          `it_id`                       = '{$val['it_id']}',
                          `it_name`                     = '{$val['it_name']}',

                          `io_id`                       = '{$val['io_id']}',
                          `io_type`                     = '{$val['io_type']}',
                          `io_price`                    = '{$val['io_price']}',
                          `io_thezone`                  = '{$val['io_thezone']}',

                          `pt_it`                       = '{$val['pt_it']}',
                          `pt_msg1`                     = '',
                          `pt_msg2`                     = '',
                          `pt_msg3`                     = '',
                          
                          `prodMemo`                    = '이로움ON주문',
                          `prodSupYn`                   = '{$val['prodSupYn']}',
                          `ordLendStrDtm`               = '{$val['ordLendStrDtm']}',
                          `ordLendEndDtm`               = '{$val['ordLendEndDtm']}'

    ");

    $_sql_eroumon_cart[] = ("  UPDATE g5_shop_cart_api
                                SET ct_sync_ctid = '##sync_ctid##'
                                WHERE ct_id = '{$val['ct_id']}'
    ");

  }
  // === === === === === === === === === ===
  // == foreach 종료 ==
  // === === === === === === === === === ===



  //var_dump( $_sql_order );  
  //var_dump( $_sql_cart );





  // ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ====
  // 주문건에 대한 INSERT SQL 처리구간 시작
  // ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ====
  
  // 23.03.09 : 서원 - 트랜잭션 시작
  sql_query("START TRANSACTION");

  try {
    
    // SQL 쿼리 실행
    sql_query($_sql_order);

    foreach($_sql_cart as $key => $sql) {
      
      // SQL 쿼리 실행
      sql_query($sql);

      // 직전에 INSERT된 ID 값을 받아 온다.
      $_ctid = sql_insert_id();

      // g5_shop_cart에 INSERT된 ID 값을 받아와서 기존에 만들어져 있는 SQL문에서 값을 치환 한다.
      sql_query( str_replace("##sync_ctid##", $_ctid, $_sql_eroumon_cart[$key]) );
      
    }

    // 23.03.09 : 서원 - 트랜잭션 커밋
    sql_query("COMMIT");

  } catch (Exception $e) {
    // 23.03.09 : 서원 - 트랜잭션 롤백
    sql_query("ROLLBACK");

    $result["YN"] = "N";
    $result["YN_msg"] = "주문 과정에 오류가 발생 하였습니다. 다시 시도해주세요.";
    
    echo json_encode($result); exit();
  }

  // ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ====
  // 주문건에 대한 INSERT SQL 처리구간 종료
  // ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ====









  
  // ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ====
  // WMDS에 사업소 주문건엔 대핸 데이터 입력 처리 부분 시작
  // ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ====
  
  $sendData = []; // WMDS 주문 입력을 위한 배열
  $productList = []; // WMDS 주문 입력에 필여한 상품 배열

  // 최종 주문완료  처리된 정보를 기준으로 WMDS에 데이터를 저장하기 위해 해당 정보를 재 조회 한다. (사유: 입력된 ID값 ct_id값이 없어 다시 조회하여 입력하도 전달 한다.)
  $_ct = sql_query("  SELECT API.*,
                              IT.it_name, IT.it_default_warehouse, IT.it_price, IT.it_price_dealer, IT.it_price_dealer2, IT.prodSupYn,
                              IT.it_delivery_min_cnt, IT.it_delivery_min_price, IT.it_delivery_cnt, IT.it_delivery_price, IT.it_is_direct_delivery,
                              IT.it_option_subject, IT.pt_it, IT.prodPayCode,
	                            ITOP.io_no, ITOP.io_id, ITOP.io_price, ITOP.io_thezone
                      FROM g5_shop_cart_api API
                      LEFT JOIN g5_shop_item IT ON IT.it_id = API.it_id
                      LEFT JOIN g5_shop_item_option ITOP ON (ITOP.it_id = API.it_id) AND (ITOP.io_id = API.io_id)
                      WHERE API.order_send_id = '" . $_POST['order_send_id'] . "' AND API.mb_id = '" . $member['mb_id'] . "' AND API.ct_status='승인'
  ");


  // === === === === === === === === === ===
  // == foreach 시작 ==
  // === === === === === === === === === ===
  foreach( $_ct as $key => $val ) {

    // ---------------------------
    // WMDS 사업소에 상품을 등록하기 위한 배열 시작 ---------------------------
    // ---------------------------
    # 옵션값 가져오기
    $prodColor = $prodSize = $prodOption = '';
    $prodOptions = [];

    if ($val["io_id"]) { // 옵션값이 있으면
      $io_subjects = explode(',', $val['it_option_subject']);
      $io_ids = explode(chr(30), $val["io_id"]);

      for ($io_idx = 0; $io_idx < count($io_subjects); $io_idx++) {
        switch ($io_subjects[$io_idx]) {
          case '색상': $prodColor = $io_ids[$io_idx]; break;
          case '사이즈': $prodSize = $io_ids[$io_idx]; break;
          default: $prodOptions[] = $io_ids[$io_idx]; break;
        }
      }
    }

    if ($prodOptions && count($prodOptions)) { $prodOption = implode('|', $prodOptions); }

    // ---------------------------
    // 옵션 정보 처리 시작 ---------------------------
    // ---------------------------
    $io_id = $val['io_id'];
    $io_type = preg_replace('#[^01]#', '', 0);
    $io_value = '';
    if ($io_id) {
      $it_option_subjects = explode(',', $val['it_option_subject']);
      $io_ids = explode(chr(30), $io_id);
      for($g = 0; $g< count($io_ids); $g++) {
        if ($g > 0) { $io_value .= ' / '; }
        $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
      }
    }
    $io_value = sql_real_escape_string(strip_tags($io_value));
    $io_value = $io_value ? $io_value : addslashes($val['it_name']);
    // ---------------------------
    // 옵션 정보 처리 종료 ---------------------------
    // ---------------------------

    for ($ii = 0; $ii < $val["ct_qty"]; $ii++) {
      $thisProductData = [];
      $thisProductData["prodId"] = $val['it_id'];
      $thisProductData["prodColor"] = $prodColor;
      $thisProductData["prodSize"] = $prodSize;
      $thisProductData["prodOption"] = $prodOption;
      $thisProductData["prodBarNum"] = "";
      $thisProductData["penStaSeq"] = "".(count($productList) + 1)."";
      $thisProductData["prodPayCode"] = $val["prodPayCode"];
      $thisProductData["itemNm"] = $io_value;
      $thisProductData["ordLendStrDtm"] = date("Y-m-d", strtotime($val['ordLendStrDtm']));
      $thisProductData["ordLendEndDtm"] = date("Y-m-d", strtotime($val['ordLendEndDtm']));
      $thisProductData["ct_id"] = $val['ct_sync_ctid'];
      array_push($productList, $thisProductData);
    }
    // ---------------------------
    // WMDS 사업소에 상품을 등록하기 위한 배열 시작 ---------------------------
    // ---------------------------


  }
  // === === === === === === === === === ===
  // == foreach 종료 ==
  // === === === === === === === === === ===

  $od_member = get_member($_od['mb_id']);
  
  $sendData["usrId"] = $od_member['mb_id'];
  $sendData["entId"] = $od_member["mb_entId"];
  $sendData["penId"] = $res['data'][0]['penId'];
  $sendData["delGbnCd"] = "";
  $sendData["ordWayNum"] = "";
  $sendData["delSerCd"] = "";
  $sendData["ordNm"] = $_od['od_b_name2'];
  $sendData["ordCont"] = ($_od["od_b_hp"]) ? $_od["od_b_hp"] : $_od["od_b_tel"];
  $sendData["ordMeno"] = $_od["od_memo"];
  $sendData["ordZip"] = $_od['od_b_zip1'] . $_od['od_b_zip2'];
  $sendData["ordAddr"] = $_od['od_b_addr1'];
  $sendData["ordAddrDtl"] = $_od['od_b_addr2']." ".$_od['od_b_addr3'];
  $sendData["finPayment"] = strval(calc_order_price($od_id));
  $sendData["payMehCd"] = "0";
  $sendData["regUsrId"] = $member["mb_id"];
  $sendData["regUsrIp"] = $_SERVER["REMOTE_ADDR"];
  $sendData["prods"] = $productList;
  $sendData["documentId"] = ($res['data'][0]['penTypeCd'] == "04") ? "THK101_THK102_THK001_THK002_THK003" : "THK001_THK002_THK003";
  $sendData["eformType"] = ($res['data'][0]['penTypeCd'] == "04") ? "21" : "00";
  $sendData["conAcco1"] = $od_member["entConAcc01"];
  $sendData["conAcco2"] = $od_member["entConAcc02"];
  $sendData["returnUrl"] = "NULL";
  //var_dump($sendData);
  // ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ====
  // WMDS에 사업소 주문건엔 대핸 데이터 입력 처리 부분 종료
  // ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ==== ====

  $insert_ser = get_eroumcare(EROUMCARE_API_ORDER_INSERT, $sendData);
  //var_dump($insert_ser);

  //결과 값
  if ($insert_ser["errorYN"] == "N") {
    $insert_ser_data = $insert_ser['data']['stockList'];
    $_stoId = [];
    foreach($insert_ser_data as $key => $val) {
      if( $_stoId[$val['ct_id']] ) {
        $_stoId[$val['ct_id']] .= "|" . $val['stoId'];
      } else {
        $_stoId[$val['ct_id']] = $val['stoId'];
      }
    }
  }   
  //var_dump( $_stoId );


  // 이로움ON(1.5) 주문건에 대한 상태값 업데이트
  sql_query("  UPDATE `g5_shop_order_api` 
                            SET `od_sync_odid` = '" . $od_id . "',
                                `od_status` = '주문완료'
                            WHERE `order_send_id` = '" . $_POST['order_send_id'] . "' 
                            AND `mb_id` = '" . $member['mb_id'] . "'
  ");


  // WMDS에 주문 등록된 주문 아이디 등록(연결)
  sql_query(" UPDATE `g5_shop_order` 
              SET `ordId` = '{$insert_ser["data"]["penOrdId"]}',
                  `uuid` = '{$insert_ser["data"]["uuid"]}'
              WHERE `od_id` = '{$od_id}'
  ");


  // 바코드 매칭에 필요한 WMDS에서 상품 수량만큼 발급받은 stoId 값 저장.
  foreach ($_stoId as $key => $val) {
    sql_query("  UPDATE `g5_shop_cart` SET `stoId` = '{$val}|' WHERE `ct_id` ='{$key}' ");
  }


  $result["YN"] = "Y";
  $result["YN_msg"] = "";

  echo json_encode($result);

exit();

?>
