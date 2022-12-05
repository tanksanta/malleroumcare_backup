<?php
include_once('./_common.php');

// 비회원 상품주문 불가능
if(!$member['mb_id']) {
  alert('비회원은 상품주문을 할 수 없습니다.');
}

// 사업소 회원이 아니면 상품주문 불가능
if($member['mb_type'] !== 'default') {
  alert('상품 주문을 할 수 없습니다.');
}

// 보관기간이 지난 상품 삭제
//cart_item_clean();

// cart id 설정
set_cart_id($sw_direct, $_SESSION['recipient']['penId'] ?? null);

if(!defined('THEMA_PATH')) {
  include_once(G5_LIB_PATH.'/apms.thema.lib.php');
}

if($sw_direct)
  $tmp_cart_id = get_session('ss_cart_direct');
else
  $tmp_cart_id = get_session('ss_cart_id');

// 브라우저에서 쿠키를 허용하지 않은 경우라고 볼 수 있음.
if (!$tmp_cart_id)
{
  alert('더 이상 작업을 진행할 수 없습니다.\\n\\n브라우저의 쿠키 허용을 사용하지 않음으로 설정한것 같습니다.\\n\\n브라우저의 인터넷 옵션에서 쿠키 허용을 사용으로 설정해 주십시오.\\n\\n그래도 진행이 되지 않는다면 쇼핑몰 운영자에게 문의 바랍니다.');
}

$tmp_cart_id = preg_replace('/[^a-z0-9_\-]/i', '', $tmp_cart_id);

// 대여 내구연한: 판매가능기간 지난 재고 정리
foreach($_POST['it_id'] as $prodId) {
  expired_rental_item_clean($prodId);
}

// 레벨(권한)이 상품구입 권한보다 작다면 상품을 구입할 수 없음.
if ($member['mb_level'] < $default['de_level_sell'])
{
  alert('상품을 구입할 수 있는 권한이 없습니다.');
}

if($act == "buy")
{
  if(!count($_POST['ct_chk']))
    alert("주문하실 상품을 하나이상 선택해 주십시오.");

  // 선택필드 초기화
  $sql = " update {$g5['g5_shop_cart_table']} set ct_select = '0' where od_id = '$tmp_cart_id' ";
  sql_query($sql);

  $fldcnt = count($_POST['it_id']);
  for($i=0; $i<$fldcnt; $i++) {
    $ct_chk = $_POST['ct_chk'][$i];
    if($ct_chk) {
      $it_id = $_POST['it_id'][$i];

      // 본인인증, 성인인증체크
      if(!$is_admin) {
        $msg = shop_member_cert_check($it_id, 'item');
        if($msg)
          alert($msg, G5_SHOP_URL);
      }
	// 상품정보
    $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);
      // 주문 상품의 재고체크
      $sql = " select ct_qty, it_name, ct_option, io_id, io_type
                from {$g5['g5_shop_cart_table']}
                where od_id = '$tmp_cart_id'
                  and it_id = '$it_id' ";
      $result = sql_query($sql);

      for($k=0; $row=sql_fetch_array($result); $k++) {
        $sql = " select SUM(ct_qty) as cnt from {$g5['g5_shop_cart_table']}
                  where od_id <> '$tmp_cart_id'
                    and it_id = '$it_id'
                    and io_id = '{$row['io_id']}'
                    and io_type = '{$row['io_type']}'
                    and ct_stock_use = 0
                    and ct_status = '쇼핑'
                    and ct_select = '1' ";

          $sum = sql_fetch($sql);
          $sum_qty = $sum['cnt'];
		
          // 재고 구함
          $ct_qty = $row['ct_qty'];
          if(!$row['io_id'])
            $it_stock_qty = get_it_stock_qty($it_id);
          else
            $it_stock_qty = get_option_stock_qty($it_id, $row['io_id'], $row['io_type']);
		if($it["pt_end"] == "" || $it["it_price"] !="100"){
          if ($ct_qty + $sum_qty > $it_stock_qty)
          {
            $item_option = $row['it_name'];
            if($row['io_id'])
              $item_option .= '('.$row['ct_option'].')';

            alert($item_option." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty - $sum_qty) . " 개");
          }
		}
      }

      $sql = " update {$g5['g5_shop_cart_table']}
                set ct_select = '1',
                  ct_select_time = '".G5_TIME_YMDHIS."'
                where od_id = '$tmp_cart_id'
                  and it_id = '$it_id' ";
      sql_query($sql);
    }
  }

  // 그누테마 사용에 따라 비회원 로직 변경
  if ($is_member) { // 회원인 경우
    goto_url(G5_SHOP_URL.'/simple_order.php?od_id=' . $tmp_cart_id);
    //goto_url(G5_SHOP_URL.'/orderform.php?only_recipient=' . $only_recipient);
  } else if (USE_G5_THEME) {
    goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/orderform.php'));
  } else {
    goto_url(G5_SHOP_URL.'/orderform.php?sw_guest=1');
  }
}
else if ($act == "alldelete") // 모두 삭제이면
{
  $sql = " delete from {$g5['g5_shop_cart_table']}
            where od_id = '$tmp_cart_id' ";
  sql_query($sql);
}
else if ($act == "seldelete") // 선택삭제
{
    if(!count($_POST['ct_chk']))
      alert("삭제하실 상품을 하나이상 선택해 주십시오.");

    $fldcnt = count($_POST['it_id']);
    for($i=0; $i<$fldcnt; $i++) {
      $ct_chk = $_POST['ct_chk'][$i];
      if($ct_chk) {
        $it_id = $_POST['it_id'][$i];
        $sql = " delete from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and od_id = '$tmp_cart_id' ";
        sql_query($sql);
      }
    }
}
else // 장바구니에 담기
{
  $count = count($_POST['it_id']);
  if ($count < 1)
    alert('장바구니에 담을 상품을 선택하여 주십시오.');

  $ct_count = 0;
  for($i=0; $i<$count; $i++) {
    // 보관함의 상품을 담을 때 체크되지 않은 상품 건너뜀
    if($act == 'multi' && !$_POST['chk_it_id'][$i])
      continue;

    $it_id = $_POST['it_id'][$i];
    $opt_count = count($_POST['io_id'][$it_id]);

    if($opt_count && $_POST['io_type'][$it_id][0] != 0)
      alert('상품의 선택옵션을 선택해 주십시오.');

    for($k=0; $k<$opt_count; $k++) {
      if ($_POST['ct_qty'][$it_id][$k] < 1)
        alert('수량은 1 이상 입력해 주십시오.');
    }

    // 본인인증, 성인인증체크
    if(!$is_admin) {
      $msg = shop_member_cert_check($it_id, 'item');
      if($msg)
        alert($msg, G5_SHOP_URL);
    }

    // 상품정보
    $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);

    if(!$it['it_id'])
      alert('상품정보가 존재하지 않습니다.');

    // 파트너몰 가격 구분
    // $it['it_price'] = samhwa_price($it, THEMA_KEY);

    // 바로구매에 있던 장바구니 자료를 지운다.
    if($i == 0 && $sw_direct)
      sql_query(" delete from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and ct_direct = 1 ", false);

    // 최소, 최대 수량 체크
    if($it['it_buy_min_qty'] || $it['it_buy_max_qty']) {
      $sum_qty = 0;
      for($k=0; $k<$opt_count; $k++) {
        if($_POST['io_type'][$it_id][$k] == 0)
          $sum_qty += (int)$_POST['ct_qty'][$it_id][$k];
      }

      if($it['it_buy_min_qty'] > 0 && $sum_qty < $it['it_buy_min_qty'])
        alert($it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_min_qty']).'개 이상 주문해 주십시오.');

      if($it['it_buy_max_qty'] > 0 && $sum_qty > $it['it_buy_max_qty'])
        alert($it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_max_qty']).'개 이하로 주문해 주십시오.');

      // 기존에 장바구니에 담긴 상품이 있는 경우에 최대 구매수량 체크
      if($it['it_buy_max_qty'] > 0) {
        $sql4 = " select sum(ct_qty) as ct_sum
                    from {$g5['g5_shop_cart_table']}
                    where od_id = '$tmp_cart_id'
                      and it_id = '$it_id'
                      and io_type = '0'
                      and ct_status = '쇼핑' ";
        $row4 = sql_fetch($sql4);

        $option_sum_qty = ($act === 'optionmod') ? $sum_qty : $sum_qty + $row4['ct_sum'];

        if($option_sum_qty > $it['it_buy_max_qty'])
          alert($it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_max_qty']).'개 이하로 주문해 주십시오.', './cart.php');
      }
    }

    // 옵션정보를 얻어서 배열에 저장
    $opt_list = array();
    $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' and io_use = 1 order by io_no asc ";
    $result = sql_query($sql);
    $lst_count = 0;
    for($k=0; $row=sql_fetch_array($result); $k++) {
      $opt_list[$row['io_type']][$row['io_id']]['id'] = $row['io_id'];
      $opt_list[$row['io_type']][$row['io_id']]['use'] = $row['io_use'];
      $opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
      $opt_list[$row['io_type']][$row['io_id']]['io_price'] = $row['io_price'];
      $opt_list[$row['io_type']][$row['io_id']]['io_price_partner'] = $row['io_price_partner'];
      $opt_list[$row['io_type']][$row['io_id']]['io_price_dealer'] = $row['io_price_dealer'];
      $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];
      $opt_list[$row['io_type']][$row['io_id']]['io_thezone'] = $row['io_thezone'];

      // 선택옵션 개수
      if(!$row['io_type'])
        $lst_count++;
    }

    //--------------------------------------------------------
    //  재고 검사, 바로구매일 때만 체크
    //--------------------------------------------------------
    // 이미 주문폼에 있는 같은 상품의 수량합계를 구한다.
    if($sw_direct) {
      for($k=0; $k<$opt_count; $k++) {
        $io_id = preg_replace(G5_OPTION_ID_FILTER, '', $_POST['io_id'][$it_id][$k]);
        $io_type = preg_replace('#[^01]#', '', $_POST['io_type'][$it_id][$k]);
        $io_value = $_POST['io_value'][$it_id][$k];

        $sql = " select SUM(ct_qty) as cnt from {$g5['g5_shop_cart_table']}
                  where od_id <> '$tmp_cart_id'
                    and it_id = '$it_id'
                    and io_id = '$io_id'
                    and io_type = '$io_type'
                    and ct_stock_use = 0
                    and ct_status = '쇼핑'
                    and ct_select = '1' ";
        $row = sql_fetch($sql);
        $sum_qty = $row['cnt'];

        // 재고 구함
        $ct_qty = (int)$_POST['ct_qty'][$it_id][$k];
        if(!$io_id)
          $it_stock_qty = get_it_stock_qty($it_id);
        else
          $it_stock_qty = get_option_stock_qty($it_id, $io_id, $io_type);

        if($it["pt_end"] == "" || $it["it_price"] !="100"){
		if ($ct_qty + $sum_qty > $it_stock_qty)
        {
          alert($io_value." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty - $sum_qty) . " 개");
        }
		}
      }
    }
    //--------------------------------------------------------

    // 옵션수정일 때 기존 장바구니 자료를 먼저 삭제
    if($act == 'optionmod')
      sql_query(" delete from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and it_id = '$it_id' ");

    // 장바구니에 Insert
    // 바로구매일 경우 장바구니가 체크된것으로 강제 설정
    if($sw_direct) {
      $ct_select = 1;
      $ct_select_time = G5_TIME_YMDHIS;
    } else {
      $ct_select = 0;
      $ct_select_time = '0000-00-00 00:00:00';
    }    

    $uid = uuidv4();

    // 장바구니에 Insert
    $comma = '';
    $sql = " INSERT INTO {$g5['g5_shop_cart_table']}
                ( od_id, mb_id, it_id, it_name, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty, ct_status, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_send_cost, ct_direct, ct_select, ct_select_time, pt_it, pt_msg1, pt_msg2, pt_msg3, ct_uid, ct_discount, prodSupYn, io_thezone, ct_delivery_cnt, ct_delivery_price, ct_delivery_company, ct_is_direct_delivery, ct_direct_delivery_partner, ct_direct_delivery_price, ct_warehouse, ct_pen_id )
              VALUES ";

    for($k=0; $k<$opt_count; $k++) {
      $io_id = preg_replace(G5_OPTION_ID_FILTER, '', $_POST['io_id'][$it_id][$k]);
      $io_type = preg_replace('#[^01]#', '', $_POST['io_type'][$it_id][$k]);
      $io_value = $_POST['io_value'][$it_id][$k];

      $pt_msg1 = get_text($_POST['pt_msg1'][$it_id][$k]);
      $pt_msg2 = get_text($_POST['pt_msg2'][$it_id][$k]);
      $pt_msg3 = get_text($_POST['pt_msg3'][$it_id][$k]);

      // 선택옵션정보가 존재하는데 선택된 옵션이 없으면 건너뜀
      if($lst_count && $io_id == '')
        continue;

      // 구매할 수 없는 옵션은 건너뜀
      if($io_id && !$opt_list[$io_type][$io_id]['use'])
        continue;

      // $io_price = $opt_list[$io_type][$io_id]['price'];
      $io_price = samhwa_opt_price($opt_list[$io_type][$io_id], THEMA_KEY);
      $io_thezone = $opt_list[$io_type][$io_id]['io_thezone'];
      $ct_qty = (int)$_POST['ct_qty'][$it_id][$k];

      // 구매가격이 음수인지 체크
      if($io_type) {
        if((int)$io_price < 0)
          alert('구매금액이 음수인 상품은 구매할 수 없습니다.');
      } else {
        if((int)$it['it_price'] + (int)$io_price < 0)
          alert('구매금액이 음수인 상품은 구매할 수 없습니다.');
      }

      // 동일옵션의 상품이 있으면 수량 더함
      $sql2 = " select ct_id, io_type, ct_qty
                  from {$g5['g5_shop_cart_table']}
                  where od_id = '$tmp_cart_id'
                    and it_id = '$it_id'
                    and io_id = '$io_id'
                    and pt_msg1 = '{$pt_msg1}'
                    and pt_msg2 = '{$pt_msg2}'
                    and pt_msg3 = '{$pt_msg3}'
                    and ct_status = '쇼핑' ";
      $row2 = sql_fetch($sql2);

      if($row2['ct_id']) {
        // 재고체크
        $tmp_ct_qty = $row2['ct_qty'];
        if(!$io_id)
          $tmp_it_stock_qty = get_it_stock_qty($it_id);
        else
          $tmp_it_stock_qty = get_option_stock_qty($it_id, $io_id, $row2['io_type']);

        if ($tmp_ct_qty + $ct_qty > $tmp_it_stock_qty)
        {
          alert($io_value." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($tmp_it_stock_qty) . " 개");
        }

        //무조건 판매가, 비유통이면 0 원 , 우수사업소 할인가
        $sql_i = "SELECT * FROM `g5_shop_item` WHERE `it_id` ='".$it['it_id']."'";
        $result_i = sql_fetch($sql_i);
        $it['it_price']=$result_i['it_price'];

        if($member['mb_level']=="4"&&$result_i['it_price_dealer2']) {
          $it['it_price']=$result_i['it_price_dealer2'];
        }

        // 사업소별 판매가
        $entprice = sql_fetch(" select it_price from g5_shop_item_entprice where it_id = '{$it['it_id']}' and mb_id = '{$member['mb_id']}' ");
        $it['entprice'] = $entprice['it_price'];

        if($it['entprice'] > 0)
          $it['it_price'] = $it['entprice'];

        if($it['prodSupYn']=="N") {
          $it['it_price']=0;
        }

        #묶음할인
        $ct_discount = 0;
        $ct_sale_qty = 0;
        //해당 상품의 모든 옵션값 개수 총합
        $sql3 = " select sum(ct_qty) as ct_qty from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id'
        and it_id = '$it_id'
        and pt_msg1 = '{$pt_msg1}'
        and pt_msg2 = '{$pt_msg2}'
        and pt_msg3 = '{$pt_msg3}'
        and ct_status = '쇼핑' ";
        $row3 = sql_fetch($sql3);
        $tmp_ct_qty_array = $_POST["ct_qty"][$it_id];
        $ct_sale_qty_list = $tmp_ct_qty_array;
              
        for($tmp_i = 0; $tmp_i < count($_POST["ct_qty"][$it_id]); $tmp_i++) {
          if (!$_POST["io_type"][$it_id][$tmp_i])
            $ct_sale_qty += $_POST["ct_qty"][$it_id][$tmp_i];
        }
        //기존 장바구니 개수 + 주문개수
        $ct_sale_qty = $row3['ct_qty']+$ct_sale_qty;

        $itSaleCntList = [$it["it_sale_cnt"], $it["it_sale_cnt_02"], $it["it_sale_cnt_03"], $it["it_sale_cnt_04"], $it["it_sale_cnt_05"]];
        $itSalePriceList = [$it["it_sale_percent"], $it["it_sale_percent_02"], $it["it_sale_percent_03"], $it["it_sale_percent_04"], $it["it_sale_percent_05"]];
        //우수사업소고 우수사업소 할인가가 있으면 적용
        if($member['mb_level']=="4"&&$it['it_sale_percent_great']){
          $itSalePriceList = [$it["it_sale_percent_great"], $it["it_sale_percent_great_02"], $it["it_sale_percent_great_03"], $it["it_sale_percent_great_04"], $it["it_sale_percent_great_05"]];
        }
        $itSaleCnt = 0;

        //기존 + 신규주문 개수
        $ct_qty2=$row2['ct_qty']+$ct_qty;
        //할인율 적용: 사업소별 가격이 지정되어있는경우는 묶음할인 미적용
        if (!$io_type && !$it['entprice']) {
          for($saleCnt = 0; $saleCnt < count($itSaleCntList); $saleCnt++){
            if($itSaleCntList[$saleCnt] <= $ct_sale_qty){
              if($itSaleCnt < $itSaleCntList[$saleCnt]){
                $ct_discount = $itSalePriceList[$saleCnt] * $ct_qty2;
                $ct_discount = ($it['it_price'] * $ct_qty2) - $ct_discount;
                $itSaleCnt = $itSaleCntList[$saleCnt];
              }
            }
          }
        }

        // 임시조치: 할인금액 마이너스면 0으로 초기화
        if($ct_discount < 0) $ct_discount = 0;
              
        $sql3 = " update {$g5['g5_shop_cart_table']}
                    set ct_qty = ct_qty + '$ct_qty',
                    ct_uid = '$uid',
                    ct_discount = '{$ct_discount}'
                    where ct_id = '{$row2['ct_id']}' ";
        sql_query($sql3);
        continue;
      }

      // 포인트
      $point = 0;
      if($config['cf_use_point']) {
        if($io_type == 0) {
          $point = get_item_point($it, $io_id);
        } else {
          $point = $it['it_supply_point'];
        }

        if($point < 0)
          $point = 0;
      }

      // 배송비결제
      if($it['it_sc_type'] == 1)
        $ct_send_cost = 2; // 무료
      else if($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1)
        $ct_send_cost = 1; // 착불

      $io_value = sql_real_escape_string(strip_tags($io_value));
      $remote_addr = get_real_client_ip();
      
      if ($member['mb_type'] == 'partner') {
        $it_sc_type = $it['it_sc_type_partner'];
        $it_sc_method = $it['it_sc_method_partner'];
        $it_sc_price = $it['it_sc_price_partner'];
        $it_sc_minimum = $it['it_sc_minimum_partner'];
        $it_sc_qty = $it['it_sc_qty_partner'];
      } else {
        $it_sc_type = $it['it_sc_type'];
        $it_sc_method = $it['it_sc_method'];
        $it_sc_price = $it['it_sc_price'];
        $it_sc_minimum = $it['it_sc_minimum'];
        $it_sc_qty = $it['it_sc_qty'];
      }   

      //무조건 판매가, 비유통이면 0 원 , 우수사업소 할인가
      $sql_i = "SELECT * FROM `g5_shop_item` WHERE `it_id` ='".$it['it_id']."'";
      $result_i = sql_fetch($sql_i);
      $it['it_price']=$result_i['it_price'];

      if($member['mb_level']=="4"&&$result_i['it_price_dealer2']){
        $it['it_price']=$result_i['it_price_dealer2'];
      }

      // 사업소별 판매가
      $entprice = sql_fetch(" select it_price from g5_shop_item_entprice where it_id = '{$it['it_id']}' and mb_id = '{$member['mb_id']}' ");
      $it['entprice'] = $entprice['it_price'];

      if($it['entprice'] > 0)
        $it['it_price'] = $it['entprice'];

      if($it['prodSupYn']=="N"){
        $it['it_price']=0;
      }

      # 210121 묶음할인
      $ct_discount = 0;
      $ct_sale_qty = 0;
      $ct_sale_qty_list = $_POST["ct_qty"][$it_id];
      for($tmp_i = 0; $tmp_i < count($_POST["ct_qty"][$it_id]); $tmp_i++) {
        if (!$_POST["io_type"][$it_id][$tmp_i])
          $ct_sale_qty += $_POST["ct_qty"][$it_id][$tmp_i];
      }
          
      $itSaleCntList = [$it["it_sale_cnt"], $it["it_sale_cnt_02"], $it["it_sale_cnt_03"], $it["it_sale_cnt_04"], $it["it_sale_cnt_05"]];
      $itSalePriceList = [$it["it_sale_percent"], $it["it_sale_percent_02"], $it["it_sale_percent_03"], $it["it_sale_percent_04"], $it["it_sale_percent_05"]];
      //우수사업소고 우수사업소 할인가가 있으면 적용
      if($member['mb_level']=="4"&&$it['it_sale_percent_great']){
        $itSalePriceList = [$it["it_sale_percent_great"], $it["it_sale_percent_great_02"], $it["it_sale_percent_great_03"], $it["it_sale_percent_great_04"], $it["it_sale_percent_great_05"]];
      }
      $itSaleCnt = 0;
        

      // 사업소별 가격이 지정되어있는경우는 묶음할인 미적용
      if (!$io_type && !$it['entprice']) {
        for($saleCnt = 0; $saleCnt < count($itSaleCntList); $saleCnt++) {
          if($itSaleCntList[$saleCnt] <= $ct_sale_qty){
            if($itSaleCnt < $itSaleCntList[$saleCnt]){
              $ct_discount = $itSalePriceList[$saleCnt] * $ct_qty;
              $ct_discount = ($it['it_price'] * $ct_qty) - $ct_discount;
              $itSaleCnt = $itSaleCntList[$saleCnt];
            }
          }
        }
      }

      // 임시조치: 할인금액 마이너스면 0으로 초기화
      if($ct_discount < 0) $ct_discount = 0;

      // 배송정보 기본설정
      //최소값 적용이 있는 상품인 경우
      if($result_i['it_delivery_min_cnt']) {
        //박스 개수 큰것 +작은것 - >ceil
        $ct_delivery_cnt = $result_i['it_delivery_cnt'] ? ceil($ct_qty / $result_i['it_delivery_cnt']) : 0;
        //큰박스 floor 한 가격을 담음
        $ct_delivery_bigbox = $result_i['it_delivery_cnt'] ? floor($ct_qty / $result_i['it_delivery_cnt']) : 0;
        $ct_delivery_price = $result_i['it_delivery_cnt'] ? ($ct_delivery_bigbox * $result_i['it_delivery_price']) : 0;
        //나머지
        $remainder = $ct_qty % $result_i['it_delivery_cnt'];
        //나머지가 있으면
        if($remainder) {
          //나머지가 최소수량보다 작으면
          if($remainder <= $result_i['it_delivery_min_cnt']) {
            //작은 박스 가격 더해줌
            $ct_delivery_price = $ct_delivery_price + $result_i['it_delivery_min_price'];
          } else {
            //큰 박스 가격 더해줌
            $ct_delivery_price = $ct_delivery_price + $result_i['it_delivery_price'];
          }
        }
      } else {
        //없으면 큰박스로만 진행
        $ct_delivery_cnt = $result_i['it_delivery_cnt'] ? ceil($ct_qty / $result_i['it_delivery_cnt']) : 0;
        $ct_delivery_price = $ct_delivery_cnt * $result_i['it_delivery_price'];
      }
      $ct_delivery_company = 'ilogen';

      $ct_pen_sql = $_SESSION['recipient']['penId'] ? "'" . $_SESSION['recipient']['penId'] . "'" : "null";

      $sql .= $comma."( '$tmp_cart_id', '{$member['mb_id']}', '{$it['it_id']}', '".addslashes($it['it_name'])."', '{$it_sc_type}', '{$it_sc_method}', '{$it_sc_price}', '{$it_sc_minimum}', '{$it_sc_qty}', '쇼핑', '{$it['it_price']}', '$point', '0', '0', '$io_value', '$ct_qty', '{$it['it_notax']}', '$io_id', '$io_type', '$io_price', '".G5_TIME_YMDHIS."', '$remote_addr', '$ct_send_cost', '$sw_direct', '$ct_select', '$ct_select_time', '{$it['pt_it']}', '$pt_msg1', '$pt_msg2', '$pt_msg3', '$uid', '{$ct_discount}', '{$it["prodSupYn"]}', '{$io_thezone}', '$ct_delivery_cnt', '$ct_delivery_price', '$ct_delivery_company', '{$it['it_is_direct_delivery']}', '{$it['it_direct_delivery_partner']}', '{$it['it_direct_delivery_price']}', '{$it['it_default_warehouse']}',{$ct_pen_sql} )";
      $comma = ' , ';
      $ct_count++;
    }

    if($ct_count > 0)
      sql_query($sql);
  }
}

// 바로 구매일 경우
if ($sw_direct) {
//  $sw_url = G5_SHOP_URL.'/orderform.php?sw_direct='.$sw_direct;

  $from_sales_Inventory_datail="";
  if($_POST['penId_r']){
    $penId_r="&penId_r=".$_POST['penId_r'];
  }
  if($_POST['penId_r']){
    $barcode_r="&barcode_r=".$_POST['barcode_r'];
  }
  $from_sales_Inventory_datail=$penId_r.$barcode_r;
  
  $sw_url = G5_SHOP_URL.'/orderform.php?sw_direct='.$sw_direct.'&ct_sc_method_sel='.$ct_sc_method_sel.$from_sales_Inventory_datail;

  // 그누테마 사용에 따라 비회원 로직 변경
  if ($is_member) {
    goto_url(G5_SHOP_URL.'/simple_order.php?od_id=' . $tmp_cart_id);
    // goto_url($sw_url);
  } else if (USE_G5_THEME) {
    goto_url(G5_BBS_URL."/login.php?url=".urlencode($sw_url));
  } else {
    goto_url($sw_url.'&amp;sw_guest=1');
  }
} else {
  goto_url(G5_SHOP_URL.'/cart.php');
}
?>
