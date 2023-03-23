<?php
include_once('./_common.php');

// 22.09 : 서원 - 파일전체 내용 재구성

//변수지정
$flag = true;

$result = "";
$result_api = "";
$OrderList = [];

$sql = [];
$sql_ct = [];
$sql_stock = [];
$sql_barcode = [];

$combine_orders = []; // 자동 합포적용
$alim_orders = []; // 알림톡 보낼 주문들
$errors = [];

// 호출한 페이지에서의 POST값이 전달되어 왔는지 체크.
if( $_POST['od_id'] && $_POST['step'] ) {


  // 호출한 페이지의 order id값을 POST값에서 가져 온다.
  $_OrderID = $_POST['od_id'];


  //상태값 치환
  switch( $_POST['step'] ) {
    case '보유재고등록':  $ct_status_text="보유재고등록"; break;
    case '재고소진':      $ct_status_text="재고소진"; break;
    case '작성':          $ct_status_text="작성"; break;
    case '주문무효':      $ct_status_text="주문무효"; break;
    case '취소':          $ct_status_text="주문취소"; break;
    case '주문':          $ct_status_text="주문접수"; break;
    case '입금':          $ct_status_text="입금완료"; break;
    case '준비':          $ct_status_text="상품준비"; break;
    case '출고준비':      $ct_status_text="출고준비"; break;
    case '배송':          $ct_status_text="출고완료"; break;
    case '완료':          $ct_status_text="배송완료"; break;
  }


  // Order 건에 대한 개별 검증 작업 시작
  for( $i=0 ; $i<count($_OrderID) ; $i++ ) {

    // 22.09 : 서원 - 보유재고관리 오류 및 소스정리(카트 아이디에 해당하는 주문 정보 검색)
    $result = sql_fetch("
      SELECT a.*, b.mb_entId
      FROM `g5_shop_cart` a
      LEFT JOIN `g5_member` b ON a.mb_id = b.mb_id
      WHERE `ct_id` = '".$_OrderID[$i]."'
    ");


    $t_mod = sql_fetch("
      SELECT *
      FROM g5_shop_order
      WHERE `od_id` = '" . $result['od_id'] . "'
    ");

    if($t_result_mod['od_is_editing'] == 1) {
      // 사업소가 주문상품 변경 중이면 무시
      $errors[] = "주문번호 " . $result['od_id'] . "는 주문수정중 상태입니다.";
      continue;
    }
    unset($t_result_mod); // 임시 사용변수 제거


    // 배송되면 재고 상태 판매완료로 바꿈
    // 재고 주문 일시 배송대기(06) -> 판매가능(01)
    // 수급자 신규 주문 일시 배송대기(06) -> 판매완료(02)
    $state_cd = '06';
    if( in_array($_POST['step'], ['배송', '완료']) ) {
      $state_cd = is_pen_order($result['od_id']) ? "02" : "01";

      // 알림톡 발송 목록에 추가
      if( !isset($alim_orders[$result['od_id']]) ) { $alim_orders[$result['od_id']] = true; }
    }

    // 취소 요청 체크
    $t_result_cancel = sql_fetch("
      SELECT *
      FROM g5_shop_order_cancel_request
      WHERE od_id = '". $result['od_id'] ."'
        AND approved = 0
    ");

    if ($t_result_cancel['od_id']) {
      echo '취소요청이 있는 주문은 주문상태를 변경할 수 없습니다.';
      exit;
    }
    unset($t_result_cancel); // 임시 사용변수 제거


    // 추가 옵션 선택이 있을 경우 선택옵션값에 대하 로그 데이터 추가
    $content = $result['it_name'];
    if( $result['it_name'] !== $result['ct_option'] ){ $content = $content."(".$result['ct_option'].")"; }
    $content = $content."-".$ct_status_text." 변경";

    //로그 INSERT
    $sql[$i]= "
      INSERT INTO
        `g5_shop_order_admin_log`
      SET
        `od_id` = '". $result['od_id'] ."',
        `mb_id` = '" . $member['mb_id'] . "',
        `ol_content` = '" . $content . "',
        `ol_datetime` = now()
    ";


    // 단계상태에 따른 sql 수정
    $add_sql = '';
    if( in_array($_POST['step'], ['출고준비']) ) { $add_sql .= ", `ct_rdy_date` = NOW()"; }
    if( in_array($_POST['step'], ['배송']) ) { $add_sql .= ", `ct_ex_date` = CURDATE()"; }

     //상태 UPDATE
    $sql_ct[$i] = "
      UPDATE `g5_shop_cart`
      SET
        `ct_status` = '" . $_POST['step'] . "'" . $add_sql . ",
        `ct_move_date`= NOW()
      WHERE `ct_id` = '" . $_OrderID[$i] . "'
    ";


    // 재고관리 변경
    if( in_array($_POST['step'], ['배송', '완료']) ) {

      $t_ws_qty = $result['ct_qty'] - $result['ct_stock_qty'];
      $t_direct_delivery_qty_sql = '';
      if( $result['ct_is_direct_delivery'] != '0' ) { // 직배송, 설치
        $t_direct_delivery_qty_sql = "ws_scheduled_qty = '-" . $t_ws_qty . "', ";
      }

      if( $result['io_type'] != 1 ) {

        // 이미 존재하는 로그값인지 체크.
        $t_stock_cnt = sql_fetch("
          SELECT count(*) as cnt
          FROM warehouse_stock
          WHERE od_id = '" .  $result['od_id']  . "'
            AND ct_id = '" . $_OrderID[$i] . "'
        ")['cnt'];

        // 이미 존재 하는 로그의 경우 추가로 입력하지 않음.
        if( $t_stock_cnt == 0 ) {
          $sql_stock[] = "
            INSERT INTO
              warehouse_stock
            SET
              it_id = '" . $result['it_id'] . "',
              io_id = '" . $result['io_id'] . "',
              io_type = '" . $result['io_type'] . "',
              it_name = '" . $result['it_name'] . "',
              ws_option = '" . $result['ct_option'] . "',
              ws_qty = '-" . $t_ws_qty . "',
              " . $t_direct_delivery_qty_sql . "
              mb_id = '" . $result['mb_id'] . "',
              ws_memo = '주문 출고완료(" . $result['od_id'] . ")',
              wh_name = '" . $result['ct_warehouse'] . "',
              od_id = '" . $result['od_id'] . "',
              ct_id = '" . $_OrderID[$i] . "',
              ws_created_at = NOW(),
              ws_updated_at = NOW()
          ";
        }

        unset($t_ws_qty); // 임시 사용변수 제거
        unset($t_stock_cnt); // 임시 사용변수 제거
        unset($t_direct_delivery_qty_sql); // 임시 사용변수 제거

      }

    }


    // 주문이 최소 또는 무효가 될 경우 재고 부분에 대한 데이터 삭제
    if( in_array($_POST['step'], ['취소', '주문무효']) ) {
      $sql_stock[] = "
        DELETE FROM
          warehouse_stock
        WHERE od_id = '" . $result['od_id'] . "'
        AND ct_id = '" . $_OrderID[$i] . "'";
    }


    if ($_POST['step'] === '출고준비') {
      if( !isset($combine_orders[ $result['od_id'] ]) ) { $combine_orders[ $result['od_id'] ] = true; }

      // 이미 수동으로 합포적용된 상품이 있으면 자동 합포적용 하지 않음
      if( $result['ct_combine_ct_id'] ) { $combine_orders[ $result['od_id'] ] = false; }
    }


    if ($_POST['step'] === '완료') {

      $it_name = $result['it_name'];
      if( $result['it_name'] !== $result['ct_option'] ) { $it_name = $it_name . "(" . $result['ct_option'] . ")"; }

      if( $result['io_type'] ) { $opt_price = $result['io_price']; }
      else { $opt_price = $result['ct_price'] + $result['io_price']; }
      $result["opt_price"] = $opt_price;

      // 소계
      $result['ct_price_stotal'] = $opt_price * $result['ct_qty'] - $result['ct_discount'];
      if( $result["prodSupYn"] == "Y" ) { $result["ct_price_stotal"] -= ($result["ct_stock_qty"] * $opt_price); }

      // 포인트 적립에 따른 계산 및 문구 정리
      $point_receiver = get_member($result['mb_id']);
      $point = (int)($result["ct_price_stotal"] / 100 * $default['de_it_grade' . $point_receiver['mb_grade'] . '_discount']);
      $point_content = "주문(" . $result['od_id'] . ") " . $it_name . " 상품 배송완료 포인트 적립 (" . $default['de_it_grade' . $point_receiver['mb_grade'] . '_name'] . " / " . $default['de_it_grade' . $point_receiver['mb_grade'] . '_discount'] . "%)";

      // 포인트 적립
      insert_point(
        $point_receiver['mb_id'],
        $point,
        $point_content,
        'order_completed',
        $_OrderID[$i],
        $point_receiver['mb_id']
      );

    } else {

      // 포인트 - 적립된 포인트에 대한 회수 처리
      $t_result_point = sql_fetch("
        SELECT *
        FROM " . $g5['point_table'] . "
        WHERE mb_id = '" . $result['mb_id'] . "'
          AND po_rel_table = 'order_completed'
          AND po_rel_id = '" . $_OrderID[$i] . "'
          AND po_rel_action = '" . $result['mb_id'] . "'
      ");

      if( $t_result_point['po_id'] ) {
        // 주문건과 관련된 포인트 지급건이 있을 경우 해당 포인트 차감
        insert_point(
          $t_result_point['mb_id'],
          $t_result_point['po_point'] * -1,
          $t_result_point['po_content'] . ' 취소 (포인트환수)',
          'order_completed_cancel',
          $t_result_point['po_rel_id'],
          $t_result_point['po_rel_action']
        );
      }
      unset($t_result_point); // 임시 사용변수 제거

    }


    // Order정보 재 가공
    if( !isset( $OrderList[ $i ][ 'mb_id' ] ) ) {

      $OrderList[ $i ][ 'mb_id' ] = $result['mb_id'];
      $OrderList[ $i ][ 'mb_entId' ] = $result['mb_entId'];
      $OrderList[ $i ][ 'stoId' ] = $result['stoId'];
      $OrderList[ $i ][ 'OrderStatus' ] = $state_cd;

      // wmds에서 바코드에 대한 정보 값을 받기 위해 API 호출 ( 기존 통합 1회 호출에서 주문건별 호출로 변경 바코드 재고관련 프로세스)
      $result_api = get_eroumcare(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $OrderList[ $i ]);
      $OrderList[ $i ][ 'api' ] = $result_api[ 'data' ];

      if( in_array($_POST['step'], ['배송', '완료']) ) {
        foreach( $result_api[ 'data' ] as $key => $val ) {

          // 유통상픔 체크
          $t_result_yn = sql_fetch("
            SELECT `prodSupYn`
            FROM `g5_shop_item`
            WHERE `it_id` ='" . $val['prodId'] . "'
          ");

          // 비급여 바코드 미입력 체크된 경우 패스
          $t_result_ct = sql_fetch("
            SELECT *
            FROM g5_shop_cart
            WHERE ct_id = '" . $result['ct_id'] . "'
          ");

          if( $t_result_ct['ct_barcode_insert'] === $t_result_ct['ct_qty'] ) { continue; }

          if( $t_result_yn['prodSupYn'] == "Y" && !$val['prodBarNum'] ) {
            echo "유통상품의 모든 바코드가 입력되어야 출고가 가능합니다\n상품명: " . $result['it_name'];
            $flag = false;
            return false;
          }

          if( $t_result_ct['ct_barcode_insert_not_approved'] != 0 ) {
            echo "미승인된 미재고 바코드가 존재합니다. 모두 승인되어야 출고가 가능합니다.\n상품명: " . $result['it_name'];
            $flag = false;
            return false;
          }

          unset($t_result_yn); // 임시 사용변수 제거
          unset($t_result_ct); // 임시 사용변수 제거

        }
      }

      // 시작  -->
      // story.sw : 22.08.24 - BarCode 데이터 수동 출고 처리에 따른 불편함 해소 요청건
      //        "[관리자_물류팀]출고처리시 상품재고관리 바코드 재고 차감 요청"
      //
      // 설명 : 주문내역상 상품의 상태 값이 '출고완료' 또는 '배송완료' 처리 될 경우 해당 상품에 입력되어 있던 바코드 정보를 찾아
      //        g5_cart_barcode 테이블에서 해당 바코드 데이터를 출고 처리하고, 이와 관련된 바코드 처리내역을 g5_cart_barcode_log테이블에 저장 한다.
      //        또한, '출고완료'또는 '배송완료' 이후 상품이 회수되어 '주문취소'나 '주문무효' 처리될 경우 해당 상품의 바코드 정보의 상태가 복원 된다.
      //
      // 의견 : 추후 mall DB쪽에도 주문상품에 입력된 바코드 값이 같이 저장 되어야할 것 같음.
      //         어떠한 의미에서 출고 입력된 바코드 정보를 API를 통해서 WMDSDB에서만 관리 하는지 확인 불가.
      //
      // 기타 : 파일(ajax.cart_status.php)이 수정되면 다른 파일(ajax.order.step.php)도 수정또는 동일 적용검토 필요.
      //        ajax.cart_status.php : 주문번호를 통한 주문내역(상세)에서 상태값을 변경 할 경우 사용.
      //        ajax.order.step.php : 주문내역(리스트)에서 상태값을 변경 할 경우 사용.
      //
      // 시작  -->

      if( in_array($_POST['step'], ['배송', '완료', '출고준비', '취소', '주문무효']) ) {

        if( is_array($result_api) ) {

          // story.sw : 22.08.25 - API에서 받아온 data 수량 만큼 데이터 체크
          foreach ($result_api['data'] as $key => $val) {
            if( !$val['prodBarNum'] ) {continue;}

            $t_content = "";
            if( in_array($_POST['step'], ['배송', '완료']) ) {   // 바코드 정상 처리
              $t_content = "재고관리 - 출고처리(" . $_POST['step'] . ")";
              $_where = "AND `bc_del_yn`='N'";
            }
            else if( in_array($_POST['step'], ['출고준비', '취소', '주문무효']) ) { // 완료 처리 이후 주문취소 또는 무효이벤트 발생시 해당 바코드 복원
              $t_content = "재고관리 - 상태복원(" . $_POST['step'] . ")";
              $_where = "AND `bc_del_yn`='Y' AND `ct_id`='" . $result['ct_id'] . "'";
            }


            // 기존 g5_cart_barcode 테이블에 해당 제품의 바코드 유뮤 확인(검색).
            $bc_data = sql_fetch("
              SELECT *
              FROM `g5_cart_barcode`
              WHERE `it_id` ='" . $val['prodId'] . "'
                AND `bc_barcode`='" . $val['prodBarNum'] . "' " . $_where
            );


            // story.sw : 22.08.24 - 바코드 출고되지 않은 바코드데이터가 존재하는 경우.
            if( is_array($bc_data) ) {

              // story.sw : 22.08.24 - 바코드 로그 삽입
              $sql_barcode[] = "
                INSERT INTO
                  g5_cart_barcode_log
                SET
                  ct_id = '" . $result['ct_id'] . "',
                  it_id = '" . $result['it_id'] . "',
                  io_id = '" . $result['io_id'] . "',
                  bc_id = '" . $bc_data['bc_id'] . "',
                  bch_status = '" . ( ($bc_data['bc_del_yn']=="N")?("출고"):("정상") ) . "',
                  bch_barcode = '" . $bc_data['bc_barcode'] . "',
                  bch_content = '" . $t_content . "',
                  created_by = '" . $member['mb_id'] . "',
                  created_at = NOW()
              ";

              // story.sw : 22.08.24 - 바코드 데이터 업데이트(출고처리)
              $sql_barcode[] = "
                UPDATE g5_cart_barcode
                SET
                  ct_id = '" . ( ($bc_data['bc_del_yn']=="N")?($result['ct_id']):(0) ) . "',
                  bc_del_yn = '" . ( ($bc_data['bc_del_yn']=="N")?("Y"):("N") ) . "',
                  bc_status = '" . ( ($bc_data['bc_del_yn']=="N")?("출고"):("정상") ) . "',
                  released_by = '" . $member['mb_id'] . "',
                  released_at = NOW()
                WHERE `it_id` ='" . $val['prodId'] . "'
                  AND `bc_barcode`='" . $val['prodBarNum'] . "'
                  AND `bc_del_yn`='" . $bc_data['bc_del_yn'] . "'
              ";
            }

          }

        }
      }
      // 종료  -->
      // story.sw : 22.08.24 - BarCode 데이터 수동 출고 처리에 따른 불편함 해소 요청건
      //        "[관리자_물류팀]출고처리시 상품재고관리 바코드 재고 차감 요청"
      // 종료  -->
      
    }

    // ====================================================================================================================================================

  }

  if ($flag) {

    for( $i=0 ; $i<count($sql) ; $i++ ) {
      sql_query($sql[$i]);
      sql_query($sql_ct[$i]);
    }

    foreach( $sql_stock as $sql ) {
      sql_query($sql);
    }

    // story.sw : 22.08.25 - 바코드 SQL 일괄 실행.
    foreach( $sql_barcode as $sql ) {
      sql_query($sql);
    }

    // 22.09 : 서원 - wmds 기존 재고 관리 부분 전면 수정
    // 단건 통합 처리에 따른 오류 발생으로 주문건에 대한 처리 방식으로 변경
    // 이전 작업되어 있던 내용은 주문자가 1명이 여러 주문을 했을 경우 빠른 처리를 위한 방법인 것 같음.
    // 여러명이 여러 주문을 했을 경우 기존의 경우 최종 마지막 구매자정보에 재고가 입력되는 방식.
    foreach( $OrderList as $key => $val ) {

      $t_array = [];
      foreach( $val['api'] as $data_key => $data_val ) {

        $t_array[] = array(
          'stoId' => $data_val[ "stoId" ],
          'prodBarNum' => $data_val[ "prodBarNum" ],
          'prodId' => $data_val[ "prodId" ],
          'stateCd' => $val[ "OrderStatus" ]
        );

      }

      $t_dataApi = array( 'usrId' => $val["mb_id"], 'entId' => $val["mb_entId"], 'prods' => $t_array );
      $t_result_api = get_eroumcare(EROUMCARE_API_STOCK_UPDATE, $t_dataApi);

      //var_dump( $t_array );
      //var_dump( $t_result_api );

      unset( $t_stoId ); // 임시 사용변수 제거
      unset( $t_dataApi ); // 임시 사용변수 제거
      unset( $t_array ); // 임시 사용변수 제거
    }

    if ($t_result_api['errorYN'] === 'N') {

      // 자동 합포적용
      /*
      foreach($combine_orders as $result['od_id'] => $need_combine) {
        if(!$need_combine) continue;

        $carts_result = sql_query("
          select ct_id, ct_status, ct_delivery_cnt, ct_delivery_price, ct_combine_ct_id
          from g5_shop_cart
          where od_id = '$result['od_id']' and ct_status not in ('취소', '주문무효')
          and ct_is_direct_delivery = 0
          order by ct_id asc
        ");

        $greatest = 0;
        $target = null;
        $carts = [];
        while($cart = sql_fetch_array($carts_result)) {
          // 이미 수동으로 합포 적용한 상품이 있으면 continue
          if($cart['ct_combine_ct_id']) continue 2;

          // 출고준비가 아닌 상품이 포함되어있으면 continue
          if($cart['ct_status'] !== '출고준비') continue 2;

          // 가장 박스수량이 많은 상품을 찾아 합포 대상으로 설정
          if($cart['ct_delivery_cnt'] > $greatest) {
            $greatest = $cart['ct_delivery_cnt'];
            $target = $cart['ct_id'];
          }

          $carts[$cart['ct_id']] = $cart;
        }

        try {
          $packed = get_packed_boxes($result['od_id']);

          $boxes = $packed['joinPacked'];
          if(!$boxes) continue; // 합포대상이 없으면 continue;

          // 합포 대상에 합포 적용
          foreach($boxes as $box) {
            foreach($box['items'] as $ct_id => $item) {
              $box_qty = $carts[$ct_id]['ct_delivery_cnt'];
              $price = $carts[$ct_id]['ct_delivery_price'];

              if($box_qty > 1 || $ct_id == $target) {
                // 박스수량이 여러개인 경우 마지막 한 박스만 합포. 나머지 박스들은 완포임.

                $unit_price = (int) ($price / $box_qty);

                // 합포될 배송박스의 수량 및 가격을 뺀다
                $box_qty -= 1;
                $price = $unit_price * $box_qty;

                if($ct_id == $target) {
                  // 박스가 합포 대상이면 합포박스의 수량 및 배송비를 더함
                  $box_qty += 1;
                  $price += $box['price'];
                }

                sql_query("
                  update g5_shop_cart
                  set ct_delivery_cnt = '$box_qty', ct_delivery_price = '$price',
                  ct_is_auto_combined = 1
                  where ct_id = '$ct_id'
                ");
              } else {
                // 나머지 모두 합포인 상품들은 합포 체크
                sql_query("
                  update g5_shop_cart
                  set ct_combine_ct_id = '$target',
                  ct_is_auto_combined = 1
                  where ct_id = '$ct_id'
                ");
              }
            }
          }

        } catch(Exception $e) {
          // 합포 오류 발생
        }
      }
      */


      echo "success";
    } else {
      if (count($errors)) {
        echo $errors[0];
      }
      else {
        echo "fail";
      }
    }
  }
} else {
  echo "fail.";
  exit;
}
?>
