<?php
include_once('./_common.php');

header('Content-Type: application/json');

$send_mail_arr = array();
$send_fax_arr = array();
$sent_od_id_arr = array();

$cnt = 0;
$ct_ids = $_POST['ct_ids'];
$sendAllAgain = $_POST['sendAllAgain'];
foreach ($ct_ids as $ct_id) {
  $sql = "SELECT I.it_direct_delivery_partner, C.* FROM g5_shop_item AS I LEFT JOIN g5_shop_cart AS C ON I.it_id = C.it_id WHERE C.ct_id = '{$ct_id}' ";
  if ($sendAllAgain == 'Y') {
    $sql .= " AND C.ct_send_direct_delivery = 0 ";
  }
  $result = sql_fetch($sql);
  if (!$result) {
    continue;
  }

  if ($result['it_direct_delivery_partner']) {
    $partner = get_member($result['it_direct_delivery_partner']);

    $mail_contents = '
            <div style="background-color:#f9f9f9;width:100%;max-width:800px;padding:30px;">
            <div style="padding-bottom:30px;border-bottom:1px solid #cfcfcf;">
                <div style="color:#333333;position:relative;width:70%;float:left;">
                    <p style="font-size:20px;padding:0;margin:0;">이로움 장기요양기관 통합관리플랫폼</p>
                </div>
                <div style="clear:both;"></div>
            </div>
            <div style="margin-top:50px;border-bottom:1px solid #cfcfcf;padding-bottom:20px;text-align: center;">
                <p style="font-size:18px;margin:0;text-align:left;padding-bottom:30px;">안녕하세요. 이로움 입니다.<br>항상 저희 이로움 플랫폼을 이용해 주셔서 진심으로 감사드립니다.<br><br>구매발주서를 송부하였으니 확인 바랍니다.<br>더욱더 노력하는 이로움플랫폼이 되겠습니다.<br><br></p>
            </div>
            <p style="font-size:12px;color:#656565;margin:30px auto;text-align:center;">
                대표자: ' . $default['de_admin_company_owner'] . ' | 사업자등록번호: ' . $default['de_admin_company_saupja_no'] . ' | 통신판매신고번호: ' . $default['de_admin_tongsin_no'] . ' <br/>
                개인정보보호관리자: ' . $default['de_admin_info_name'] . ' | 주소: ' . $default['de_admin_company_addr'] . '
                <br/><br/>
                Copyright © ' . $default['de_admin_company_name'] . ' All rights reserved.
            </p>
            </div>
        ';

    ob_start();
    include('send_direct_delivery_excel.php');
    $excelData = ob_get_contents();
    ob_end_clean();

    $filename = "purchaseorder_{$cnt}.xlsx";

    $email_arr = array(
      'subject' => '[이로움 장기요양기관 통합관리플랫폼] 구매발주서 송부드립니다.',
      'content' => $mail_contents,
      'receiver' => $partner['send_transaction_e'],
      'file' => [
        'name' => $filename,
        'data' => $excelData,
        'encoding' => 'remove'
      ],
    );

    $sql = " update g5_shop_cart set ct_send_direct_delivery = 1 ";

    /*
    $send_type = $partner['send_transaction'];
    if ($send_type == 'A') {
      $sql .= " , ct_send_direct_delivery_email='{$partner['send_transaction_e']}', ct_send_direct_delivery_fax='{$partner['send_transaction_f']}' ";
      array_push($send_mail_arr, $email_arr);

      array_push($send_fax_arr, array(
        'excel' => $excelData,
        'filename' => $filename,
        'rcvnm' => $partner['mb_name'],
        'rcv' => $partner['send_transaction_f']
      ));
    } else if ($send_type == 'E') {
      $sql .= " , ct_send_direct_delivery_email='{$partner['send_transaction_e']}', ct_send_direct_delivery_fax='' ";
      array_push($send_mail_arr, $email_arr);
    } else if ($send_type == 'F') {
      $sql .= " , ct_send_direct_delivery_email='', ct_send_direct_delivery_fax='{$partner['send_transaction_f']}' ";
      array_push($send_fax_arr, array(
        'excel' => $excelData,
        'filename' => $filename,
        'rcvnm' => $partner['mb_name'],
        'rcv' => $partner['send_transaction_f']
      ));
    } else {
      $sql .= " , ct_send_direct_delivery_email='', ct_send_direct_delivery_fax='' ";
    }
    */

    if ($sendEmail) {
      $sql .= " , ct_send_direct_delivery_email='{$partner['send_transaction_e']}' ";
      array_push($send_mail_arr, $email_arr);
    }

    if ($sendFax) {
      $sql .= " , ct_send_direct_delivery_fax='{$partner['send_transaction_f']}' ";
      array_push($send_fax_arr, array(
        'excel' => $excelData,
        'filename' => $filename,
        'rcvnm' => $partner['mb_name'],
        'rcv' => $partner['send_transaction_f']
      ));
    }

    if ($sendTalk) {
      if (!in_array($result['od_id'], $sent_od_id_arr)) {
        $sql .= " , ct_send_direct_delivery_talk='{$partner['mb_hp']}' ";
        $od_sql = "select * from g5_shop_order where od_id = '{$result['od_id']}' ";
        $order_row = sql_fetch($od_sql);

        $items_text = '';
        $carts_sql = "select * from g5_shop_cart where od_id = '{$result['od_id']}' and ct_id in (". implode(',', $ct_ids) .") ";
        $carts_result = sql_query($carts_sql);
        while ($cart_row = sql_fetch_array($carts_result)) {
          $ct_it_name = $cart_row['it_name']; //상품이름
          $ct_option = ($cart_row["ct_option"] == $cart_row['it_name']) ? "" : "(" . $cart_row['ct_option'] . ")"; //옵션
          $ct_it_name = $ct_it_name.$ct_option;

          $items_text .= "　- 품명 : {$ct_it_name} / 수량 : {$cart_row['ct_qty']}개\n";
        }

        $talk_msg = "[구매발주안내]\n";
        $talk_msg .= "안녕하세요 테스터님 이로움입니다.\n";
        $talk_msg .= "항상 저희 이로움 플랫폼을 이용해주셔서 진심으로 감사드립니다.\n";
        $talk_msg .= "\n";
        $talk_msg .= "구매발주내역이 있으니 확인바랍니다.\n";
        $talk_msg .= "더욱더 노력하는 이로움플랫폼이 되겠습니다.\n";
        $talk_msg .= "\n";
        $talk_msg .= "■ 주문일시 : " . date('Y/m/d H:i', strtotime($order_row['od_time'])) . "\n";
        $talk_msg .= "■ 주문번호 : {$order_row['od_id']}\n";
        $talk_msg .= "■ 주문내역 :\n";
        $talk_msg .= "{$items_text}";
        $talk_msg .= "■ 배송지명 : {$order_row['od_b_name']}\n";
        $talk_msg .= "■ 배송주소 : {$order_row['od_b_addr1']} {$order_row['od_b_addr2']} {$order_row['od_b_addr3']} {$order_row['od_b_addr_jibeon']}\n";
        $talk_msg .= "■ 배송지연락처 : {$order_row['od_b_tel']} ({$order_row['od_b_hp']})\n";
        $talk_msg .= "■ 배송요청사항 : {$order_row['od_memo']}";

        send_alim_talk('ENT_DIRECT_DELIVERY_'.$result['od_id'], $partner['mb_hp'], 'ent_direct_delivery', $talk_msg);
        $sent_od_id_arr[] = $result['od_id'];
      }
    }

    $sql .= " where ct_id = '{$ct_id}' limit 1; ";
    sql_query($sql);
  }
  $cnt++;
}

header('Content-Type: application/json');

if (count($send_mail_arr) > 0) {
  // echo 'console.log("' . var_dump($send_mail_arr) . '")';
  include_once(G5_LIB_PATH . '/mailer.lib.php');
  mailer_multiple($config['cf_admin_email_name'], $config['cf_admin_email'], $send_mail_arr);
}

if (count($send_fax_arr) > 0) {
  include_once(G5_LIB_PATH . '/fax.lib.php');
  $response = sendFax($send_fax_arr);
  if ($response) {
    $ret = array(
      'result' => 'fail',
      'msg' => $response,
    );
    echo json_encode($ret);
    exit;
  }
}

$ret = array(
  'result' => 'success',
  'msg' => '발송하였습니다.',
);

echo json_encode($ret);