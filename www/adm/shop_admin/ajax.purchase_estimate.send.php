<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = clean_xss_tags(trim($_POST['od_id']));

$email_chk = trim($_POST['email_chk']);
$u_email = trim($_POST['u_email']);

$hp_chk = trim($_POST['hp_chk']);
$u_hp = trim($_POST['u_hp']);

$fax_chk = trim($_POST['fax_chk']);
$u_fax = trim($_POST['u_fax']);


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
$sql = " select * from purchase_order where od_id = '$od_id' ";
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
		  from purchase_cart a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
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
                from purchase_cart
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
                from purchase_cart
                where it_id = '{$row['it_id']}'
                    and od_id = '{$od['od_id']}' ";
    $sum = sql_fetch($sql);

    $row['sum'] = $sum;
    $amount['order'] += $sum['price'] - $sum['discount'];

    $carts[] = $row;
}

// 주문금액 = 상품구입금액 + 배송비 + 추가배송비 - 할인금액
if ( $od['od_cart_price'] ) {
    $amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'] - $od['od_cart_discount'] - $od['od_sales_discount'];
}
// 입금액 = 결제금액 + 포인트
$amount['receipt'] = $od['od_receipt_price'] + $od['od_receipt_point'];

// 쿠폰금액
$amount['coupon'] = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];

// 취소금액
$amount['cancel'] = $od['od_cancel_price'];



// 발주서 정보
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
                <p style="font-size:42px;padding:0;margin:0;"><b style="font-size:52px;">' . $od['od_name'] . '님</b><br/>구매발주서가 도착하였습니다.</p>
                <p>이로움을 이용해주셔서 감사합니다.</p>
            </div>
            <div style="width:30%;float:right;" >
            </div>
            <div style="clear:both;"></div>
        </div>
        <div style="margin-top:50px;border-bottom:1px solid #cfcfcf;padding-bottom:20px;text-align: center;">
            <p style="margin:0;text-align:center;padding-bottom:30px;">구매발주서 확인을 클릭하시면 전송된 내용을 확인할 수 있습니다.</p>
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
            <a href="' . G5_SHOP_URL . '/pop.purchase_estimate.php?od_id=' . $od['od_id'] . '" target="_blank" style="background-color:#0aa2cd;display:inline-block;text-align:center;padding: 12px 60px;color:white;text-decoration:none;margin:20px auto;font-size:18px;">구매발주서 확인</a>
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
    mailer($config['cf_admin_email_name'], $config['cf_admin_email'], trim($u_email), '[이로움] ' . $od['od_name'] . '님 구매발주서', $mail_contents, 1);

    // 22.10.27 : 서원 - 쿼리 실행
    $result = sql_query("
        UPDATE 
            purchase_order
        SET 
            od_send_yn = '1',
            od_send_mail_yn = '1'
        WHERE 
            od_id = '{$od_id}'
    ");

    set_purchase_order_admin_log($od_id, "발주서 - 메일(".trim($u_email).")발송 완료", '' , '1');
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

    $url = G5_URL . '/shop/pop.purchase_estimate.php?od_id=' . $od_id;
    $new_url = shorturl($url);
    if ( $new_url ) {
        $url = $new_url;
    }

    $sms_contents = '[이로움] {이름}님 구매발주서가 도착했습니다. '. $url;
    $sms_contents = str_replace("{이름}", $od['od_name'], $sms_contents);
    $sms_contents = str_replace("{회원아이디}", $od['mb_id'], $sms_contents);
    $sms_contents = str_replace("{회사명}", $default['de_admin_company_name'], $sms_contents);

    // 핸드폰번호에서 숫자만 취한다
    $receive_number = preg_replace("/[^0-9]/", "", $u_hp);  // 수신자번호 (회원님의 핸드폰번호)
    $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

    $strDest = array();
    $strDest[0] = $receive_number;

    // 22.11.03: 서원 - 발주서 문자 내용상 SMS 단문자 전송 불가능 함.
    //include_once(G5_LIB_PATH.'/icode.sms.lib.php');
    // 22.11.03: 서원 - 장문자 전송으로 변경
    include_once(G5_LIB_PATH.'/icode.lms.lib.php');

    $SMS = new LMS; // SMS 연결
    //$SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], '1');
    //$SMS->Add($receive_number, $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
    $SMS->Add($strDest, $send_number, $config['cf_icode_id'],"","", iconv("utf-8", "euc-kr", stripslashes($sms_contents)), "","1");
    $SMS->Send();
    $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.

    // 22.10.27 : 서원 - 쿼리 실행
    $result = sql_query("
        UPDATE 
            purchase_order
        SET 
            od_send_yn = '1',
            od_send_hp_yn = '1'
        WHERE 
            od_id = '{$od_id}'
    ");

    set_purchase_order_admin_log($od_id, "발주서 - SMS(".trim($u_hp).")발송 완료", '' , '2');
}

if( $fax_chk == 'true' ) {


    $url = G5_URL."/shop/pop.purchase_estimate.php?od_id=".$od_id;

    // PDF 파일 생성
    $pdfdir = G5_DATA_PATH . "/purchase/" . date("Ym");
    if(!is_dir($pdfdir)) {
        @mkdir($pdfdir, G5_DIR_PERMISSION, true);
        @chmod($pdfdir, G5_DIR_PERMISSION);
    }

    $mb_id = $member['mb_id'];
    $manager_mb_id = get_session('ss_manager_mb_id');
    if($manager_mb_id) {
      $mb_id = $manager_mb_id;
    }

    $pdffile = "PurchaseOrder_".$od_id."_FaxSend_".date("ymdHis")."_".$mb_id.".pdf";
    $pdfdir .= "/".$pdffile;

    // 서버 내 wkhtmltopdf 파일 경로 :  /usr/local/bin
    // 저장
    // @exec('C:/_THKC/_Dev/wkhtmltox/bin/wkhtmltopdf "'.$url.'" "'.$pdfdir.'" 2>&1');
    @exec('/usr/local/bin/wkhtmltopdf "'.$url.'" "'.$pdfdir.'" 2>&1');
    @exec('wkhtmltopdf "'.$url.'" "'.$pdfdir.'" 2>&1');

    $send_fax_arr = array();
    array_push($send_fax_arr, array(
        'type' => 'SendFAX',
        'filename' => $pdfdir,
        'rcvnm' => $od['od_name'],
        'rcv' => $u_fax
    ));

    if (count($send_fax_arr) > 0) {
        include_once(G5_LIB_PATH . '/fax.lib.php');
        $response = sendFax($send_fax_arr);
        if ($response) {
            $ret = array(
                'result' => 'fail',
                'msg' => $response
            );
            echo json_encode($ret);
            exit;
        }
    }

    // 22.10.27 : 서원 - 쿼리 실행
    $result = sql_query("
        UPDATE 
            purchase_order
        SET 
            od_send_yn = '1',
            od_send_fax_yn = '1'
        WHERE 
            od_id = '{$od_id}'
    ");

    set_purchase_order_admin_log($od_id, "발주서 - FAX(".trim($send_number).")발송 완료", '' , '2');

}


$ret = array(
    'result' => 'success',
    'msg' => '발송하였습니다.',
);

echo json_encode($ret);
?>