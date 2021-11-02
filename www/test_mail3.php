<?php
include_once('./_common.php');

$od_id = '2020101309485295';
$uid = 'bfb31411-bd9b-41ce-8d43-a4a96659e68e';
$it_id = 'G5788';

$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    $ret = array(
        'result' => 'fail',
        'msg' => '해당 주문번호로 주문서가 존재하지 않습니다.',
    );
    echo json_encode($ret);
    exit;
}

// 상품정보
$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

// 카트 정보
// $sql = " select * from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and od_id = '$od_id' ";
$sql = " select * from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and ct_uid = '$uid' and od_id = '$od_id' ORDER BY ct_id ASC";
$result = sql_query($sql);
$carts = array();
while ($row = sql_fetch_array($result)) {
    $carts[] = $row;
}

$sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'order_outsourcing' AND ctf_uid = '{$uid}'";
$result = sql_query($sql);
$files = array();
while ($row = sql_fetch_array($result)) {
    $files[] = $row;
}


// --------------------------------------------- 추가되는 내용

$manager = get_member($it['it_outsourcing_id']);

$od_delivrey = get_delivery_step($od['od_delivery_type']);

$od_sales_manager = get_member($od['od_sales_manager']);

if ( $od['od_delivery_receiptperson'] == 0 ) {
    $od['mail_sender'] = '삼화';
    $od['mail_sender_phone'] = $default['de_admin_company_tel'];
    $od['mail_sender_addr'] = $default['de_admin_company_addr'];
}else{
    $od['mail_sender'] = $od['od_name'];
    $od['mail_sender_phone'] = $od['od_hp'] ? $od['od_hp'] : $od['od_tel'];
    $od['mail_sender_addr'] = $od['od_addr1'] . " " . $od['od_addr2'];
}

$mail_contents = "
<div>
    <img src='". G5_IMG_URL ."/mail_logo.png'>
    <h3 style='margin-top:10px;color:#333333'>". $manager['mb_name'] ."님 주문이 접수되었습니다.</h3>
    <div style='margin-top:10px;margin-bottom:10px;padding-top:10px;padding-bottom:10px;border-top:1px solid #ddd;border-bottom:1px solid #ddd;'>
        <ul>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    전송일
                </span>
                <span style='color:#333;'>
                : ". date('Y년 m월 d일 H시 i분', time()) ."
                </span>
            </li>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    발신자
                </span>
                :
                <span style='color:#333;'>
                ". $default['de_admin_company_name'] . " " . $od_sales_manager['mb_name'] ."
                </span>
            </li>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    이메일
                </span>
                :
                <span style='color:#333;'>
                ". $config['cf_admin_email'] ."
                </span>
            </li>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    TEL
                </span>
                :
                <span style='color:#333;'>
                ". $default['de_admin_company_tel'] ."
                </span>
            </li>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    FAX
                </span>
                :
                <span style='color:#333;'>
                ". $default['de_admin_company_fax'] ."
                </span>
            </li>
        </ul>
    </div>
    <h3 style='margin-top:20px;color:#333333'>발주정보</h3>
    <div style='background-color:#f8f8f8;border:1px solid #ddd;padding:10px;padding-top:20px;'>
    <table style='width:100%;border-collapse: collapse;border-spacing: 0;'>
    <tbody>
    ";
    foreach($carts as $cart) {
        $image = get_it_image($cart['it_id'], 50, 50);
        $mail_contents .= '
        <tr>
            <td style="text-align:left;color:#656565;font-size:13px;height:50px;padding-left:20px;">
                <a href="' . G5_SHOP_URL .'/item.php?it_id='. $cart['it_id'] .'" style="text-decoration:none;color:#656565;">
                    ' . $image . ' <span style="line-height: 50px;vertical-align: top;margin-left:15px;font-size:2em;">' . $cart['it_name'] . '</span>
                    <p style="margin-top:0;margin-bottom:0;margin-left:70px;font-size:1.5em;">'. $cart['ct_option'] .'</p>
                    <p style="margin-top:10px;margin-left:70px;font-size:1.5em;margin-bottom:20px;font-weight:bold;">
                        '. $it_outsourcing_option . ( $it_outsourcing_option2 ? ' / ' . $it_outsourcing_option2 : '' ) . ( $it_outsourcing_option3 ? ' / ' . $it_outsourcing_option3 : '' ) .'
                        <span style="color:#d11313;">
                        수량:' . number_format($cart['ct_qty']) . '
                        </span>
                    </p>
                </a>
            </td>
        </tr>';
    }
    $mail_contents .= "
    </tbody>
    </table>
    </div>
    <h3 style='margin-top:20px;color:#333333'>배송정보</h3>
    <div style='background-color:#f8f8f8;border:1px solid #ddd;padding:10px;'>
        <h4 style='margin:0;padding:10px 15px;color:#333;'>보내실 곳(수령지)</h4>
        <ul>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    배송방법
                </span>
                :
                <span style='color:#d11313;'>
                    ". $od_delivrey['name'] ."
                </span>
            </li>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    수령인
                </span>
                :
                <span>
                    ". $od['od_b_name'] ."
                </span>
            </li>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    연락처
                </span>
                :
                <span>
                    ". ($od['od_b_hp'] ? $od['od_b_hp'] : $od['od_b_tel']) ."
                </span>
            </li>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    주소
                </span>
                :
                <span>
                    ". $od['od_b_addr1'] . " " . $od['od_b_addr2'] ."
                </span>
            </li>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    전달메세지
                </span>
                :
                <span>
                    ". ($od['od_memo'] ? $od['od_memo'] : '없음') ."
                </span>
            </li>
        </ul>

        <hr style='width:95%;margin:30px auto;color:#ddd;background-color:#ddd;border-top:1px solid #ddd;' />

        <h4 style='margin:0;padding:10px 15px;color:#333;'>보내는 사람(발송자)</h4>
        <ul>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    발송인 
                </span>
                :
                <span>
                    ". $od['mail_sender'] ."
                </span>
            </li>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    연락처
                </span>
                :
                <span>
                    ". $od['mail_sender_phone'] ."
                </span>
            </li>
            <li>
                <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                    주소
                </span>
                :
                <span>
                    ". $od['mail_sender_addr'] ."
                </span>
            </li>
        </ul>
    </div>

    <h3 style='margin-top:20px;color:#333333'>기타정보</h3>
    <ul>
        <li>
            <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                희망 납기일 
            </span>
            :
            <span>
                ". $od['od_ex_date'] ."
            </span>
        </li>
        <li>
            <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                특이사항
            </span>
            :
            <span>
                ". ($od['od_send_admin_memo'] ? $od['od_send_admin_memo'] : '없음') ."
            </span>
        </li>
        <li>
            <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                첨부 파일
            </span>
            :
            <span>
            ";
            if ( count($files) > 0 ) { 
                $mail_contents .= '
                    <div style="display:inline-block;">
                        <ul style="list-style:none;margin:0;padding:0;">';
                        foreach($files as $file) {
                            $mail_contents .= '<li style="list-style:none;margin:0;padding:0;margin-bottom:10px;display:inline-block;margin-right:15px;"><a href="'. G5_URL .'/data/order_cart/'. $file['file_name'] .'" style="text-decoration: underline;color: #0592ff;">' . $file['real_name'] . '</a></li>';
                        }
                        $mail_contents .= '
                        </ul>
                    </div>';
            }
            $mail_contents .= "
            </span>
        </li>
    </ul>

    <hr style='margin:30px auto;color:#ddd;background-color:#ddd;border-top:1px solid #ddd;' />

    <p style='font-size:1em;color:#656565;margin:30px auto;text-align:left;'>
        대표자: " . $default["de_admin_company_owner"] . " | 사업자등록번호: " . $default["de_admin_company_saupja_no"] . " | 통신판매신고번호: " . $default["de_admin_tongsin_no"] . " <br/>
        개인정보보호관리자: " . $default["de_admin_info_name"] . " | 주소: " . $default["de_admin_company_addr"] . "
        <br/><br/>
        Copyright © " . $default["de_admin_company_name"] . " All rights reserved.
    </p>
</div>
";


echo $mail_contents;

?>

