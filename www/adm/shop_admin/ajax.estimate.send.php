<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);
$email_chk = trim($_POST['email_chk']);
$u_email = trim($_POST['u_email']);
$hp_chk = trim($_POST['hp_chk']);
$u_hp = trim($_POST['u_hp']);

if (!$od_id) {
    $ret = array(
        'result' => 'fail',
        'msg' => '잘못된 요청입니다.',
    );
    echo json_encode($ret);
    exit;
}
//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

$it_names = array();

// 상품목록
$sql = " select a.it_id,
				a.it_name,
                a.cp_price,
                a.ct_notax,
                a.ct_send_cost,
                a.it_sc_type,
				a.pt_it,
				a.pt_id,
				b.ca_id,
				b.ca_id2,
                b.ca_id3,
                b.ca_id4,
                b.ca_id5,
                b.ca_id6,
                b.ca_id7,
                b.ca_id8,
                b.ca_id9,
                b.ca_id10,
				b.pt_msg1,
				b.pt_msg2,
                b.pt_msg3,
                a.ct_status,
                b.it_model
		  from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
		  where a.od_id = '$od_id'
		  group by a.it_id
		  order by a.ct_id ";

$result = sql_query($sql);

$carts = array();
$cate_counts = array();

for($i=0; $row=sql_fetch_array($result); $i++) {

    $cate_counts[$row['ct_status']] += 1;
    $it_names[] = $row['it_name'];

    // 상품의 옵션정보
    $sql = " select ct_id, mb_id, it_id, ct_price, ct_point, ct_qty, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, io_type, io_price, pt_msg1, pt_msg2, pt_msg3, ct_discount
                from {$g5['g5_shop_cart_table']}
                where od_id = '{$od['od_id']}'
                    and it_id = '{$row['it_id']}'
                order by io_type asc, ct_id asc ";
    $res = sql_query($sql);

    $row['options_span'] = sql_num_rows($res);

    $row['options'] = array();
    for($k=0; $opt=sql_fetch_array($res); $k++) {

        $opt_price = 0;

		if($opt['io_type'])
            $opt_price = $opt['io_price'];
        else
            $opt_price = $opt['ct_price'] + $opt['io_price'];

        $opt['opt_price'] = $opt_price;

        // 소계
        $opt['ct_price_stotal'] = $opt_price * $opt['ct_qty'] - $opt['ct_discount'];
        $opt['ct_point_stotal'] = $opt['ct_point'] * $opt['ct_qty'] - $opt['ct_discount'];

        $row['options'][] = $opt;
    }


    // 합계금액 계산
    $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                    SUM(ct_qty) as qty,
                    SUM(ct_discount) as discount,
                    SUM(ct_send_cost) as sendcost
                from {$g5['g5_shop_cart_table']}
                where it_id = '{$row['it_id']}'
                    and od_id = '{$od['od_id']}' ";
    $sum = sql_fetch($sql);

    $row['sum'] = $sum;

    $carts[] = $row;
}

// 주문금액 = 상품구입금액 + 배송비 + 추가배송비 - 할인금액
$amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'] - $od['od_cart_discount'];

// 입금액 = 결제금액 + 포인트
$amount['receipt'] = $od['od_receipt_price'] + $od['od_receipt_point'];

// 쿠폰금액
$amount['coupon'] = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];

// 취소금액
$amount['cancel'] = $od['od_cancel_price'];



// 견적서 정보
$sql = " select * from g5_shop_order_estimate where od_id = '$od_id' ";
$est = sql_fetch($sql);

$it_name = implode( ',', $it_names);


if ( $email_chk == 'true' ) {

    if (!$u_email) {
        $ret = array(
            'result' => 'fail',
            'msg' => '이메일을 입력해주세요.',
        );
        echo json_encode($ret);
        exit;
    }

    $mail_contents = '
        <div style="background-color:#f9f9f9;width:100%;max-width:800px;padding:30px;">
        <div style="padding-bottom:30px;border-bottom:1px solid #cfcfcf;">
            <div style="color:#333333;position:relative;width:70%;float:left;">
                <p style="font-size:42px;padding:0;margin:0;"><b style="font-size:52px;">' . $od['od_name'] . '님</b><br/>견적서가 도착하였습니다.</p>
                <p>삼화에스앤디를 이용해주셔서 감사합니다.</p>
            </div>
            <div style="width:30%;float:right;" >
                <img src="'. G5_IMG_URL. '/logo_big.png" style="width:100%;" />
            </div>
            <div style="clear:both;"></div>
        </div>
        <div style="margin-top:50px;border-bottom:1px solid #cfcfcf;padding-bottom:20px;text-align: center;">
            <p style="margin:0;text-align:center;padding-bottom:30px;">견적서확인을 클릭하시면 전송된 내용을 확인할 수 있습니다.</p>
            <table style="border:1px solid #c6c6c6;width:80%;margin:0 auto;border-collapse: collapse;border-spacing: 0;">
                <thead>
                    <tr>
                        <th style="background-color:#ffffff;border-bottom:1px solid #cfcfcf;font-weight:normal;line-height:30px;font-size:13px;">상품</th>
                        <th style="background-color:#ffffff;border-bottom:1px solid #cfcfcf;font-weight:normal;line-height:30px;font-size:13px;">총 금액</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;">
                            ' . $it_name . '
                        </td>
                        <td style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;">
                            ' . number_format($amount['order']) . '원
                        </td>
                    </tr>
                </tbody>
            </table>
            <a href="' . G5_SHOP_URL . '/pop.estimate.php?od_id=' . $od['od_id'] . '" target="_blank" style="background-color:#0aa2cd;display:inline-block;text-align:center;padding: 12px 60px;color:white;text-decoration:none;margin:20px auto;font-size:18px;">견적서확인</a>
        </div>
        <p style="font-size:12px;color:#656565;margin:30px auto;text-align:center;">
            대표자: ' . $default['de_admin_company_owner'] . ' | 사업자등록번호: ' . $default['de_admin_company_saupja_no'] . ' | 통신판매신고번호: ' . $default['de_admin_tongsin_no'] . ' <br/>
            개인정보보호관리자: ' . $default['de_admin_info_name'] . ' | 주소: ' . $default['de_admin_company_addr'] . '
            <br/><br/>
            Copyright © ' . $default['de_admin_company_name'] . ' All rights reserved.
        </p>
        </div>
    ';

    include_once(G5_LIB_PATH.'/mailer.lib.php');
    mailer($config['cf_admin_email_name'], $config['cf_admin_email'], trim($u_email), '[삼화] ' . $od['od_name'] . '님 견적서', $mail_contents, 1);

}


if ( $hp_chk == 'true' ) {

    if (!$u_hp) {
        $ret = array(
            'result' => 'fail',
            'msg' => '핸드폰번호를 입력해주세요.',
        );
        echo json_encode($ret);
        exit;
    }

    $url = G5_URL . '/shop/pop.estimate.php?od_id=' . $od_id;
    $new_url = shorturl($url);
    if ( $new_url ) {
        $url = $new_url;
    }

    $sms_contents = '[삼화] {이름}님 견적서가 도착했습니다. '. $url;
    $sms_contents = str_replace("{이름}", $od['od_name'], $sms_contents);
    $sms_contents = str_replace("{회원아이디}", $od['mb_id'], $sms_contents);
    $sms_contents = str_replace("{회사명}", $default['de_admin_company_name'], $sms_contents);

    // 핸드폰번호에서 숫자만 취한다
    $receive_number = preg_replace("/[^0-9]/", "", $u_hp);  // 수신자번호 (회원님의 핸드폰번호)
    $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

    include_once(G5_LIB_PATH.'/icode.sms.lib.php');

    $SMS = new SMS; // SMS 연결
    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
    $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
    $SMS->Send();
    $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
   
}

set_order_admin_log($od_id, '견적서 발송');

$ret = array(
    'result' => 'success',
    'msg' => '발송하였습니다.',
);

echo json_encode($ret);
?>