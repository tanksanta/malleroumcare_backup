<?php
if (!defined('_GNUBOARD_')) exit;

// 상품옵션별재고 또는 상품재고에 더하기
function add_io_stock($it_id, $ct_qty, $io_id="", $io_type=0)
{
    global $g5;

    if($io_id) {
        $sql = " update {$g5['g5_shop_item_option_table']}
                    set io_stock_qty = io_stock_qty + '{$ct_qty}'
                    where it_id = '{$it_id}'
                      and io_id = '{$io_id}'
                      and io_type = '{$io_type}' ";
    } else {
        $sql = " update {$g5['g5_shop_item_table']}
                    set it_stock_qty = it_stock_qty + '{$ct_qty}'
                    where it_id = '{$it_id}' ";
    }
    return sql_query($sql);
}


// 상품옵션별재고 또는 상품재고에서 빼기
function subtract_io_stock($it_id, $ct_qty, $io_id="", $io_type=0)
{
    global $g5;

    if($io_id) {
        $sql = " update {$g5['g5_shop_item_option_table']}
                    set io_stock_qty = io_stock_qty - '{$ct_qty}'
                    where it_id = '{$it_id}'
                      and io_id = '{$io_id}'
                      and io_type = '{$io_type}' ";
    } else {
        $sql = " update {$g5['g5_shop_item_table']}
                    set it_stock_qty = it_stock_qty - '{$ct_qty}'
                    where it_id = '{$it_id}' ";
    }
    return sql_query($sql);
}


// 주문과 장바구니의 상태를 변경한다.
function change_status($od_id, $current_status, $change_status)
{
    global $g5;

    $sql = " update {$g5['g5_shop_order_table']} set od_status = '{$change_status}' where od_id = '{$od_id}' and od_status = '{$current_status}' ";
    sql_query($sql, true);

	// 개별 정산일
	$pt_datetime = ($change_status == "완료") ? ", pt_datetime = '".G5_TIME_YMDHIS."'" : "";
    $sql = " update {$g5['g5_shop_cart_table']} set ct_status = '{$change_status}' $pt_datetime where od_id = '{$od_id}' and ct_status = '{$current_status}' ";
    sql_query($sql, true);

	// 개별 배송비
	$pt_sendcost = ($change_status == "완료") ? "sc_flag = '1', pt_datetime = '".G5_TIME_YMDHIS."'" : "sc_flag = '0', pt_datetime = ''";
    $sql = " update {$g5['apms_sendcost']} set $pt_sendcost where od_id = '{$od_id}' ";
    sql_query($sql, true);
}


// 주문서에 입금시 update
function order_update_receipt($od_id)
{
    global $g5;

    $sql = " update {$g5['g5_shop_order_table']} set od_receipt_price = od_misu, od_misu = 0, od_receipt_time = '".G5_TIME_YMDHIS."' where od_id = '$od_id' and od_status = '입금' ";
    return sql_query($sql);
}


// 주문서에 배송시 update
function order_update_delivery($od_id, $mb_id, $change_status, $delivery)
{
    global $g5;

    if($change_status != '배송')
        return;

    $sql = " update {$g5['g5_shop_order_table']} set od_delivery_company = '{$delivery['delivery_company']}', od_invoice = '{$delivery['invoice']}', od_invoice_time = '{$delivery['invoice_time']}' where od_id = '$od_id' and od_status = '준비' ";
    sql_query($sql);

    $sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 재고를 사용하지 않았다면
        $stock_use = $row['ct_stock_use'];

        if(!$row['ct_stock_use'])
        {
            // 재고에서 뺀다.
            subtract_io_stock($row['it_id'], $row['ct_qty'], $row['io_id'], $row['io_type']);
            $stock_use = 1;

            $sql = " update {$g5['g5_shop_cart_table']} set ct_stock_use  = '$stock_use' where ct_id = '{$row['ct_id']}' ";
            sql_query($sql);
        }
    }
}

// 처리내용 SMS
function conv_sms_contents($od_id, $contents)
{
    global $g5, $config, $default;

    $sms_contents = '';

    if ($od_id && $config['cf_sms_use'] == 'icode')
    {
        $sql = " select od_id, od_name, od_invoice, od_receipt_price, od_delivery_company
                    from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
        $od = sql_fetch($sql);

        $sms_contents = $contents;
        $sms_contents = str_replace("{이름}", $od['od_name'], $sms_contents);
        $sms_contents = str_replace("{입금액}", number_format($od['od_receipt_price']), $sms_contents);
        $sms_contents = str_replace("{택배회사}", $od['od_delivery_company'], $sms_contents);
        $sms_contents = str_replace("{운송장번호}", $od['od_invoice'], $sms_contents);
        $sms_contents = str_replace("{주문번호}", $od['od_id'], $sms_contents);
        $sms_contents = str_replace("{회사명}", $default['de_admin_company_name'], $sms_contents);
    }

    return stripslashes($sms_contents);
}

function pg_setting_check($is_print=false){
	global $g5, $config, $default, $member;

	$msg = '';
	$pg_msg = '';

	if( $default['de_card_test'] ){
		if( $default['de_pg_service'] === 'kcp' && $default['de_kcp_mid'] && $default['de_kcp_site_key'] ){
			$pg_msg = 'NHN KCP';
		} else if ( $default['de_pg_service'] === 'lg' && $config['cf_lg_mid'] && $config['cf_lg_mert_key'] ){
			$pg_msg = 'LG유플러스';
		} else if ( $default['de_pg_service'] === 'inicis' && $default['de_inicis_mid'] && $default['de_inicis_sign_key'] ){
			$pg_msg = 'KG이니시스';
		}
	}

	if( $pg_msg ){
		$pg_test_conf_link = G5_ADMIN_URL.'/shop_admin/configform.php#de_card_test1';
		$msg .= '<div class="admin_pg_notice od_test_caution">(주의!) '.$pg_msg.' 결제의 결제 설정이 현재 테스트결제 로 되어 있습니다.<br>테스트결제시 실제 결제가 되지 않으므로, 쇼핑몰 운영중이면 반드시 실결제로 설정하여 운영하셔야 합니다.<br>아래 링크를 클릭하여 실결제로 설정하여 운영해 주세요.<br><a href="'.$pg_test_conf_link.'" class="pg_test_conf_link">'.$pg_test_conf_link.'</a></div>';
	}
	
	if( $is_print ){
		echo $msg;
	} else{
		return $msg;
	}
}

function check_order_inicis_tmps(){
    global $g5, $config, $default, $member;

    $admin_cookie_time = get_cookie('admin_visit_time');

    if( ! $admin_cookie_time ){

        if( $default['de_pg_service'] === 'inicis' && empty($default['de_card_test']) ){
            $sql = " select * from {$g5['g5_shop_inicis_log_table']} where P_TID <> '' and P_TYPE in ('CARD', 'ISP', 'BANK') and P_MID <> '' and P_STATUS = '00' and is_mail_send = 0 and substr(P_AUTH_DT, 1, 14) < '".date('YmdHis', strtotime('-3 minutes', G5_SERVER_TIME))."' ";

            $result = sql_query($sql, false);
            
            if( !$result ){
                return;
            }

            $mail_msg = '';

            for($i=0;$row=sql_fetch_array($result);$i++){
                
                $oid = $row['oid'];
                $p_tid = $row['P_TID'];
                $p_mid = strtolower($tmps['P_MID']);

                if( in_array($p_mid, array('iniescrow0', 'inipaytest')) ) continue;

                $sql = "update {$g5['g5_shop_inicis_log_table']} set is_mail_send = 1 where oid = '".$oid."' and P_TID = '".$p_tid."' ";
                sql_query($sql);

                $sql = " select od_id from {$g5['g5_shop_order_table']} where od_id = '$oid' and od_tno = '$p_tid' ";
                $tmp = sql_fetch($sql);

                if( $tmp['od_id'] ) continue;

                $sql = " select pp_id from {$g5['g5_shop_personalpay_table']} where pp_id = '$oid' and pp_tno = '$p_tid' ";
                $tmp = sql_fetch($sql);

                if( $tmp['pp_id'] ) continue;

                $mail_msg .= '<a href="'.G5_ADMIN_URL.'/shop_admin/inorderform.php?od_id='.$oid.'" target="_blank" >미완료 발생 주문번호 : '.$oid.'</a><br><br>';
                
            }
            
            if( $mail_msg ){
                include_once(G5_LIB_PATH.'/mailer.lib.php');

                $mails = array_unique(array($member['mb_email'], $config['cf_admin_email']));

                foreach($mails as $mail_address){
                    if (!preg_match("/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/", $mail_address)) continue;

                    mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mail_address, $config['cf_title'].' 사이트 미완료 주문 알림', '이니시스를 통해 결제한 주문건 중에서 미완료 주문이 발생했습니다.<br><br>발생된 원인으로는 장바구니 금액와 실결제 금액이 맞지 않는 경우, 네트워크 오류, 프로그램 오류, 알수 없는 오류 등이 있습니다.<br><br>아래 내용과 실제 주문내역, 이니시스 상점 관리자 에서 결제된 내용을 확인하여 조치를 취해 주세요.<br><br>'.$mail_msg, 0);
                }
            }
        }

        if( $default['de_pg_service'] == 'lg' && function_exists('check_log_folder') ){
            check_log_folder(G5_LGXPAY_PATH.'/lgdacom/log');
        }

        set_cookie('admin_visit_time', G5_SERVER_TIME, 3600);   //1시간 간격으로 체크
    }
}   //end function check_order_inicis_tmps

use DVDoug\BoxPacker\InfalliblePacker;
use DVDoug\BoxPacker\EroumBox;
use DVDoug\BoxPacker\EroumItem;

// 박스 합포 계산
function get_packed_boxes($od_id) {
  global $default;

  if(!$od_id)
    throw new Exception('해당 주문이 존재하지 않습니다.', 400);

  $packer = new InfalliblePacker();

  // 쇼핑몰 박스규격 설정
  for($i = 1; $i <= 15; $i++) {
    $box_size = explode(chr(30), $default['de_box_size'.($i)]);
    list($name, $width, $length, $depth, $price) = $box_size;

    if(!($name && $width && $length && $depth))
      continue;

    // cm -> mm 변환, 소수점 내림
    $width = (int) floor($width * 10);
    $length = (int) floor($length * 10);
    $depth = (int) floor($depth * 10);

    $price = $price ? (int) $price : 0;

    $packer->addBox(new EroumBox($name, $price, $width, $length, $depth, 0, $width, $length, $depth, 1000));
  }

  // 출고준비 단계만 계산
  $sql = "
    select
      c.*,
      i.it_box_size,
      i.it_delivery_cnt
    from
      g5_shop_cart c
    left join
      g5_shop_item i ON c.it_id = i.it_id
    where
      c.od_id = '{$od_id}' and
      c.prodSupYn = 'Y' and
      c.ct_status = '출고준비'
    group by
      c.it_id
  ";
  $result = sql_query($sql);

  $compPacked = []; // 완전포장
  $unPacked = []; // 단일포장
  $joinPacked = []; // 합포추천
  $dirPacked = []; // 위탁상품
  while($row = sql_fetch_array($result)) {

    $box_size = explode(chr(30), $row['it_box_size']);
    list($width, $length, $depth) = $box_size;

    $name = $row['it_name'];

    $sql = "
      select * from g5_shop_cart
      where od_id = '{$od_id}' and prodSupYn = 'Y' and ct_status = '출고준비' and it_id = '{$row['it_id']}'
      order by ct_id asc
    ";
    $res = sql_query($sql);

    $ct_qty = 0; // 소계
    $row['opt'] = [];
    while($opt = sql_fetch_array($res)) {
      $opt_name = $name;
      if($opt_name != $opt['ct_option']) {
        $opt_name .= " ({$opt['ct_option']})";
      }

      $ct_id = $opt['ct_id'];

      $opt_qty = $opt['ct_qty'] - $opt['ct_stock_qty'];

      // 위탁배송인 경우
      if($opt['ct_is_direct_delivery'])
      {
        $dirPacked[$ct_id] = array(
          'name' => $opt_name,
          'qty' => $opt_qty
        );
      }

      // 상품 규격 입력 안되어있으면 단일포장
      else if(!($width && $length && $depth))
      {
        $unPacked[$ct_id] = array(
          'name' => $opt_name,
          'qty' => $opt_qty
        );
      }

      else
      {
        $ct_qty += $opt_qty;
        $row['opt'][] = $opt;
      }
    }

    // 합포 계산할 상품이 없으면 continue
    if(!$row['opt'])
      continue;

    if($row['it_delivery_cnt'] > 1) {
      $it_id = $row['it_id'];
      $div = $row['it_delivery_cnt'];
      $comp_qty = (int) floor( $ct_qty / $div ); // 몫
      $sub = $comp_qty * $div;

      if($comp_qty > 0) {
        // 완전포장은 it_id 기준 (다른 포장은 ct_id 기준)
        $compPacked[$it_id] = array(
          'name' => $name . " ({$row['it_delivery_cnt']}개)",
          'qty' => $comp_qty
        );

        foreach($row['opt'] as &$opt) {
          $opt_qty = $opt['ct_qty'] - $opt['ct_stock_qty'];

          if($sub > $opt_qty) {
            $sub -= $opt_qty;
            $opt['ct_qty'] = $opt['ct_stock_qty'];
          } else {
            // 끝
            $opt['ct_qty'] = $opt_qty - $sub + $opt['ct_stock_qty'];
            break;
          }
        }
        unset($opt);
      }
    }

    foreach($row['opt'] as $opt) {
      $opt_name = $name;
      if($opt_name != $opt['ct_option']) {
        $opt_name .= " ({$opt['ct_option']})";
      }

      $ct_id = $opt['ct_id'];

      $opt_qty = $opt['ct_qty'] - $opt['ct_stock_qty'];

      // cm -> mm 변환, 소수점 올림
      $width = (int) ceil($width * 10);
      $length = (int) ceil($length * 10);
      $depth = (int) ceil($depth * 10);

      if($opt_qty > 0)
        $packer->addItem(new EroumItem($opt_name, $ct_id, $width, $length, $depth, 0, false), $opt_qty);
    }
  }

  $packedBoxes = $packer->pack();

  foreach($packedBoxes as $packedBox) {
    $boxType = $packedBox->getBox();

    $items = [];
    $packedItems = $packedBox->getItems();
    foreach ($packedItems as $packedItem) {
      $ct_id = $packedItem->getItem()->getCtId();
      $name = $packedItem->getItem()->getDescription();
      if(!$items[$ct_id])
        $items[$ct_id] = array(
          'name' => $name,
          'qty' => 0
        );
      $items[$ct_id]['qty']++;
    }

    // 박스에 단일상품밖에 없으면 단일포장
    if(count($items) === 1) {
      foreach($items as $ct_id => $item) {
        $unPacked[$ct_id] = array(
          'name' => $item['name'],
          'qty' => $item['qty']
        );
      }
    } else {
      $joinPacked[$boxType->getReference()] = $items;
    }
  }

  // 포장 불가능한 상품은 단일상품에 추가
  $unpackedItems = $packer->getUnpackedItems();
  foreach($unpackedItems as $unpackedItem) {
    $ct_id = $packedItem->getItem()->getCtId();
    $name = $packedItem->getItem()->getDescription();
    if(!$unPacked[$ct_id])
      $unPacked[$ct_id] = array(
        'name' => $name,
        'qty' => 0
      );

    $unPacked[$ct_id]['qty']++;
  }

  $ret = '';

  if($compPacked) {
    $ret .= '[완전포장]' . PHP_EOL;
    foreach($compPacked as $ct_id => $item) {
      $ret .= "{$item['name']} * {$item['qty']}" . PHP_EOL;
    }
    $ret .= PHP_EOL;
  }

  if($unPacked) {
    $ret .= '[단일상품]' . PHP_EOL;

    foreach($unPacked as $ct_id => $item) {
      $ret .= "{$item['name']} * {$item['qty']}" . PHP_EOL;
    }

    $ret .= PHP_EOL;
  }

  if($joinPacked) {
    $ret .= '[합포추천]' . PHP_EOL;
    foreach($joinPacked as $box => $items) {
      $ret .= "● {$box}" . PHP_EOL;

      foreach($items as $ct_id => $item) {
        $ret .= "{$item['name']} * {$item['qty']}" . PHP_EOL;
      }

      $ret .= PHP_EOL;
    }
  }

  if($dirPacked) {
    $ret .= '[위탁상품]' . PHP_EOL;
    foreach($dirPacked as $ct_id => $item) {
      $ret .= "{$item['name']} * {$item['qty']}" . PHP_EOL;
    }
    $ret .= PHP_EOL;
  }

  if(!$ret) {
    throw new Exception('합포가 가능한 상품이 없습니다.', 500);
  }

  return array(
    'html' => $ret,

    'compPacked' => $compPacked, // 완전포장 { it_id: { name, qty } }
    'unPacked' => $unPacked, // 단일포장 { ct_id: { name, qty } }
    'joinPacked' => $joinPacked, //합포추천 { box: { ct_id: { name, qty } } }
    'dirPacked' => $dirPacked, //위탁상품 { ct_id: { name, qty } }
  );

}
?>