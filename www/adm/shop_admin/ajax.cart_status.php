<?php
include_once('./_common.php');


if($_POST['ct_id'] && $_POST['step']) {

  //변수지정
  $stoId = "";
  $stoId_total = "";
  $usrId = "";
  $entId = "";
  $flag = true;
  $stoIdList = array();

  //상태값 치환
  switch ($_POST['step']) {
    case '보유재고등록': $ct_status_text="보유재고등록"; break;
    case '재고소진': $ct_status_text="재고소진"; break;
    case '작성': $ct_status_text="작성"; break;
    case '주문무효': $ct_status_text="주문무효"; break;
    case '취소': $ct_status_text="주문취소"; break;
    case '주문': $ct_status_text="주문접수"; break;
    case '입금': $ct_status_text="입금완료"; break;
    case '준비': $ct_status_text="상품준비"; break;
    case '출고준비': $ct_status_text="출고준비"; break;
    case '배송': $ct_status_text="출고완료"; break;
    case '완료': $ct_status_text="배송완료"; break;
  }

  $sql = [];
  $sql_ct = [];
  $sql_cp = [];
  $sql_stock = [];
  $combine_orders = []; // 자동 합포적용

  for ($i = 0; $i < count($_POST['ct_id']); $i++) {
    $sql_ct_s = "select
      a.od_id,
      a.it_id,
      a.it_name,
      a.ct_option,
      a.ct_status,
      a.mb_id,
      a.stoId,
      b.mb_entId,
      a.io_id,
      a.io_type,
      a.ct_price,
      a.io_price,
      a.ct_qty,
      a.ct_discount,
      a.prodSupYn,
      a.ct_stock_qty,
      a.ct_id,
      a.ct_combine_ct_id,
      a.ct_warehouse,
      a.ct_is_direct_delivery
    from `g5_shop_cart` a left join `g5_member` b on a.mb_id = b.mb_id where `ct_id` = '".$_POST['ct_id'][$i]."'";
    $result_ct_s = sql_fetch($sql_ct_s);
    $od_id = $result_ct_s['od_id'];

    $od = sql_fetch(" select * from g5_shop_order where od_id = '$od_id' ");
    if($od['od_is_editing'] == 1) {
      echo('해당 주문은 사업소에서 수정 중이므로 주문단계가 변경되지 않았습니다.'); exit();
    }

    if(in_array($result_ct_s['ct_status'], ['배송','완료'])) {
      if(!in_array($_POST['step'], ['출고준비', '완료', '취소', '주문무효'])) {
        echo("상품 상태값 제한적 변경 가능 안내.

          * 출고완료 => 출고준비, 배송완료, 취소, 주문무효
          * 배송완료 => 출고준비, 취소, 주문무효"); exit();
      }
    }
    else if(in_array($result_ct_s['ct_status'], ['취소', '주문무효'])) {
      echo('취소된 주문은 상태변경이 불가능합니다.'); exit();
    }
    

  
    $content=$result_ct_s['it_name'];
    if($result_ct_s['it_name'] !== $result_ct_s['ct_option']){
      $content = $content."(".$result_ct_s['ct_option'].")";
    }
    $it_name = $content;
    $content = $content."-".$ct_status_text." 변경";
    //로그 insert
    $sql[$i] = "INSERT INTO g5_shop_order_admin_log SET
      od_id = '{$od_id}',
      mb_id = '{$member['mb_id']}',
      ol_content = '{$content}',
      ol_datetime = now()
    ";

    //상태 update
    $add_sql = '';
    if($_POST['step'] == "배송") { $add_sql .= ", `ct_ex_date` = CURDATE()"; }
    if($_POST['step'] == "출고준비") { $add_sql .= ", `ct_rdy_date` = NOW()"; }

    $sql_ct[$i] = "update `g5_shop_cart` set `ct_status` = '".$_POST['step']."'".$add_sql.", `ct_move_date`= NOW() where `ct_id` = '".$_POST['ct_id'][$i]."'";

    // 재고관리 변경
    if ($_POST['step'] == '배송') {
      $ws_qty = $result_ct_s['ct_qty'] - $result_ct_s['ct_stock_qty'];
      if ($result_ct_s['ct_qty'] != '0') { // 직배송, 설치
        $ws_scheduled_qty_sql = "ws_scheduled_qty = '-{$ws_qty}', ";
      }

      if ($result_ct_s['io_type'] != 1) {
        $stock_count = sql_fetch("select count(*) as cnt from warehouse_stock where od_id = '{$od_id}' and ct_id = '{$_POST['ct_id'][$i]}}' ")['cnt'];

        if ($stock_count == 0) {
          $sql_stock[] = "
            insert into
              warehouse_stock
            set
              it_id = '{$result_ct_s['it_id']}',
              io_id = '{$result_ct_s['io_id']}',
              io_type = '{$result_ct_s['io_type']}',
              it_name = '{$result_ct_s['it_name']}',
              ws_option = '{$result_ct_s['ct_option']}',
              ws_qty = '-{$ws_qty}',
              mb_id = '{$result_ct_s['mb_id']}',
              ws_memo = '주문 출고완료({$od_id})',
              wh_name = '{$result_ct_s['ct_warehouse']}',
              od_id = '$od_id',
              ct_id = '{$_POST['ct_id'][$i]}',
              ws_created_at = NOW(),
              ws_updated_at = NOW()
          ";
        }
      }
    }
    if($_POST['step'] == '취소' || $_POST['step'] == '주문무효') {
      $sql_stock[] = "
        delete from
          warehouse_stock
        where
          od_id = '$od_id' and
          ct_id = '{$_POST['ct_id'][$i]}'
      ";
    }

    // 쿠폰 취소
    if($_POST['step'] == '취소' || $_POST['step'] == '주문무효') {
      $sql_cp[] = "
        DELETE FROM
          g5_shop_coupon_log
        WHERE
          od_id = '{$od_id}'
      ";
    }

    if ($_POST['step'] === '배송') {
      add_notification(
        array(),
        $result_ct_s['mb_id'],
        '[이로움] 주문상품 배송 시작',
        $it_name . ' 배송이 시작되었습니다.',
        G5_URL . '/shop/orderinquiryview.php?od_id=' . $result_ct_s['od_id'],
      );
    }

    if ($_POST['step'] === '출고준비') {
      if(!isset($combine_orders[$od_id]))
        $combine_orders[$od_id] = true;
      
      // 이미 수동으로 합포적용된 상품이 있으면 자동 합포적용 하지 않음
      if($result_ct_s['ct_combine_ct_id'])
        $combine_orders[$od_id] = false;
    }

    if ($_POST['step'] === '완료') {
      
      if($result_ct_s['io_type'])
        $opt_price = $result_ct_s['io_price'];
      else
        $opt_price = $result_ct_s['ct_price'] + $result_ct_s['io_price'];

      $result_ct_s["opt_price"] = $opt_price;

      // 소계
      $result_ct_s['ct_price_stotal'] = $opt_price * $result_ct_s['ct_qty'] - $result_ct_s['ct_discount'];
      if($result_ct_s["prodSupYn"] == "Y") {
        $result_ct_s["ct_price_stotal"] -= ($result_ct_s["ct_stock_qty"] * $opt_price);
      }

      $point_receiver = get_member($result_ct_s['mb_id']);
      $point = (int)($result_ct_s["ct_price_stotal"] / 100 * $default['de_it_grade' . $point_receiver['mb_grade'] . '_discount']);
      $point_content = "주문({$od_id}) {$it_name} 상품 배송완료 포인트 적립 ({$default['de_it_grade' . $point_receiver['mb_grade'] . '_name']} / {$default['de_it_grade' . $point_receiver['mb_grade'] . '_discount']}%)";

      insert_point($point_receiver['mb_id'], $point, $point_content, 'order_completed', $_POST['ct_id'][$i], $point_receiver['mb_id']);
    } else {
      $po_cancel_sql = " select * from {$g5['point_table']}
      where mb_id = '{$result_ct_s['mb_id']}'
        and po_rel_table = 'order_completed'
        and po_rel_id = '{$_POST['ct_id'][$i]}'
        and po_rel_action = '{$result_ct_s['mb_id']}' ";
      $po_cancel_row = sql_fetch($po_cancel_sql);
      if ($po_cancel_row['po_id']) {
        insert_point($po_cancel_row['mb_id'], $po_cancel_row['po_point'] * -1, $po_cancel_row['po_content'] . ' 취소 (포인트환수)', 'order_completed_cancel', $po_cancel_row['po_rel_id'], $po_cancel_row['po_rel_action']);
      }
    }

    //시스템 상태값 변경
    $stoId = $result_ct_s['stoId'];
    $usrId = $result_ct_s['mb_id'];
    $entId = $result_ct_s['mb_entId'];

    // 22.11.15 : 서원 - 주문서 전체 stoid 연속 저장
    $stoId_total .= $result_ct_s['stoId'];

    foreach( explode('|', $result_ct_s['stoId']) as $temp_sto_id) {
      if ($temp_sto_id) {
        $stoIdList[$temp_sto_id] = $result_ct_s['ct_id'];
      }
    }
  }

  // 취소 요청 체크
  $cancel_sql = "select *
  from g5_shop_order_cancel_request
  where od_id = '$od_id' and approved = 0";
  $cancel_request_row = sql_fetch($cancel_sql);
  if ($cancel_request_row['od_id']) {
    echo '취소요청이 있는 주문은 주문상태를 변경할 수 없습니다.';
    exit;
  }

  //완료 판매완료로 바꿈
  // 재고 주문 일시 배송 완료 -> 01
  // 수급자 신규 주문 일시 배송 완료 -> 02
  $stateCd = "06";
  switch ($_POST['step']) {
      case '배송':
      case '완료':
          $stateCd = is_pen_order($od_id) ? "02" : "01";
          break;
  }


  //$stoIdDataList = explode('|',$stoId);
  // 22.11.15 : 서원 - 변수 변경
  $stoIdDataList = explode('|',$stoId_total);
  $stoIdDataList = array_filter($stoIdDataList);
  $stoIdData = implode("|", $stoIdDataList);
  $sendData["stoId"] = $stoIdData;


  $res = get_eroumcare(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
  $result_again = $res['data'];
  $new_sto_ids = array_map(function($data) {
    global $stateCd;
    return array(
      'stoId' => $data['stoId'],
      'prodBarNum' => $data['prodBarNum'],
      'prodId' => $data['prodId'],
      'stateCd' => $stateCd
    );
  }, $result_again);


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

  // 주문정보에 적용된 바코드 정보는 mall DB에 저장되지 않아 API를 이용하여 WMDSDB를 조회함.
  $sendData["stoId"] = str_replace (",", "|", $od['stoId']);
  $tmpData = get_eroumcare(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);

  $_barCode_Sql = [];
  if( in_array($_POST['step'], ['배송', '완료', '출고준비', '취소', '주문무효']) ) {
    if( is_array($tmpData) && $tmpData['errorYN']=="N" ) {
      
      // story.sw : 22.08.25 - API에서 받아온 data 수량 만큼 데이터 체크
      foreach ($tmpData['data'] as $key => $val) {
        if( !$val['prodBarNum'] ) {continue;}

        $bch_content = "";
      
        if( in_array($_POST['step'], ['배송', '완료']) ) {   // 바코드 정상 처리
          $bch_content = "재고관리 - 출고처리(" . $_POST['step'] . ")";
          $_where = "AND `bc_del_yn`='N'";
        }        
        else if( in_array($_POST['step'], ['출고준비', '취소', '주문무효']) ) { // 완료 처리 이후 주문취소 또는 무효이벤트 발생시 해당 바코드 복원
          $bch_content = "재고관리 - 상태복원(" . $_POST['step'] . ")";
          $_where = "AND `bc_del_yn`='Y' AND `ct_id`='" . $stoIdList[$val['stoId']] . "'";
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
          $_barCode_Sql[] = "
            insert into g5_cart_barcode_log
            set
              bc_id = '{$bc_data['bc_id']}',
              ct_id = '{$stoIdList[$val['stoId']]}',
              it_id = '{$bc_data['it_id']}',
              io_id = '{$bc_data['io_id']}',
              bch_status = '" . ( ($bc_data['bc_del_yn']=="N")?("출고"):("정상") ) . "',
              bch_barcode = '{$bc_data['bc_barcode']}',
              bch_content = '{$bch_content}',
              created_by = '{$member['mb_id']}',
              created_at = NOW()
          "; 

          // story.sw : 22.08.24 - 바코드 데이터 업데이트(출고처리)
          $_barCode_Sql[] = "
            update g5_cart_barcode
            set
              ct_id = '" . ( ($bc_data['bc_del_yn']=="N")?($stoIdList[$val['stoId']]):(0) ) . "',
              bc_del_yn = '" . ( ($bc_data['bc_del_yn']=="N")?("Y"):("N") ) . "',
              bc_status = '" . ( ($bc_data['bc_del_yn']=="N")?("출고"):("정상") ) . "',
              released_by = '{$member['mb_id']}',
              released_at = NOW()
              where `it_id` ='" . $val['prodId'] . "' AND `bc_barcode`='" . $val['prodBarNum'] . "' AND `bc_del_yn`='" . $bc_data['bc_del_yn'] . "'
          "; 
        }

      }

    }

  }

  // 종료  --> 
  // story.sw : 22.08.24 - BarCode 데이터 수동 출고 처리에 따른 불편함 해소 요청건
  //        "[관리자_물류팀]출고처리시 상품재고관리 바코드 재고 차감 요청"
  // 종료  --> 
  

  if ($stateCd == "01") {
    for ($k = 0; $k < count($new_sto_ids); $k++) {
      $result_confirm = sql_fetch("select `prodSupYn` from `g5_shop_item` where `it_id` ='" . $new_sto_ids[$k]['prodId'] . "'");

      // 비급여 바코드 미입력 체크된 경우 패스
      $ct_result = sql_fetch("SELECT * FROM g5_shop_cart WHERE ct_id = '{$stoIdList[$new_sto_ids[$k]['stoId']]}'");
      if ($ct_result['ct_barcode_insert'] === $ct_result['ct_qty']) {
        continue;
      }

      if ($result_confirm['prodSupYn'] == "Y" && !$new_sto_ids[$k]['prodBarNum']) {
        echo "유통상품의 모든 바코드가 입력되어야 출고가 가능합니다";
        $flag = false;
        return false;
      }

      if ($ct_result['ct_barcode_insert_not_approved'] != 0) {
        echo "미승인된 미재고 바코드가 존재합니다. 모두 승인되어야 출고가 가능합니다.";
        $flag = false;
        return false;
      }
    }
  }

  // 출고 시 바코드 상태 출고 상태로 변경
  for ($i = 0; $i < count($_POST['ct_id']); $i++) {
    if ($_POST['step'] == '배송') {
      if ($ct_result['ct_barcode_insert'] > 0 &&
        $ct_result['ct_barcode_insert'] === $ct_result['ct_qty'] &&
        $ct_result['ct_barcode_insert_not_approved'] == 0) {

        $barcode_status_sql = "
          update g5_cart_barcode
          set 
            bc_status = '출고',
            released_by = '{$member['mb_id']}',
            released_at = NOW()
          where
            ct_id = '{$_POST['ct_id'][$i]}'
        ";
        sql_query($barcode_status_sql);
      }
    }
  }

  if($flag) {

    for($i=0; $i<count($sql); $i++) {
      sql_query($sql[$i]);
      sql_query($sql_ct[$i]);
    }

    foreach($sql_stock as $sql) {
      sql_query($sql);
    }

    foreach($sql_cp as $sql) {
      sql_query($sql);
    }

    // story.sw : 22.08.25 - 바코드 SQL 일괄 실행.
    foreach($_barCode_Sql as $sql) {
      sql_query($sql);
    }

    /*
    if($_POST['sendcost'] != 0){
      $od_send_cost = get_sendcost_new($od_id, 1);
      $sql = "
          update
              g5_shop_order
          set
              od_send_cost = '$od_send_cost'
          where
              od_id = '$od_id'
      ";
      sql_query($sql);
    }
    */

    $od_send_cost = get_sendcost_new($od_id, 1);
    $sql = "
        update
            g5_shop_order
        set
            od_send_cost = '$od_send_cost'
        where
            od_id = '$od_id'
    ";
    //sql_query($sql);

    $api_data = array(
      'usrId' => $usrId,
      'entId' => $entId,
      'prods' => $new_sto_ids,
    );
    $api_result = get_eroumcare(EROUMCARE_API_STOCK_UPDATE, $api_data);
    if ($api_result['errorYN'] === 'N') {
      // 자동 합포적용

      /*
      foreach($combine_orders as $od_id => $need_combine) {
        if(!$need_combine) continue;

        $carts_result = sql_query("
          select ct_id, ct_status, ct_delivery_cnt, ct_delivery_price, ct_combine_ct_id
          from g5_shop_cart
          where od_id = '$od_id' and ct_status not in ('취소', '주문무효')
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
          $packed = get_packed_boxes($od_id);

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
      echo "fail";
    }
  }
} else {
  echo "fail";
}
?>
