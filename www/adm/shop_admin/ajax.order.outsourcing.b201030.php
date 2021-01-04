<?php

$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

$od_id = $_POST['od_id'];
$it_id = $_POST['it_id'];
$it_outsourcing_option = get_text($_POST['it_outsourcing_option']);
$it_outsourcing_option2 = get_text($_POST['it_outsourcing_option2']);
$it_outsourcing_option3 = get_text($_POST['it_outsourcing_option3']);
$uid = $_POST['uid'];

if ( !$od_id || !$it_id ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
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
    $ret = array(
        'result' => 'fail',
        'msg' => '해당 주문번호로 주문서가 존재하지 않습니다.',
    );
    echo json_encode($ret);
    exit;
}
if (!$uid) {
    $ret = array(
        'result' => 'fail',
        'msg' => '접근 오류입니다.',
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

//print_r($carts);
//exit;

// 파일 정보
// $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'order_outsourcing' AND it_id = '{$it_id}'";
$sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'order_outsourcing' AND ctf_uid = '{$uid}'";
$result = sql_query($sql);
$files = array();
while ($row = sql_fetch_array($result)) {
    $files[] = $row;
}

if ($it['it_outsourcing_option'] && !$it_outsourcing_option) {
    $ret = array(
        'result' => 'fail',
        'msg' => '옵션을 선택해 주세요.',
    );
    echo json_encode($ret);
    exit;
}
if ($it['it_outsourcing_option2'] && !$it_outsourcing_option2) {
    $ret = array(
        'result' => 'fail',
        'msg' => '옵션2을 선택해 주세요.',
    );
    echo json_encode($ret);
    exit;
}
if ($it['it_outsourcing_option3'] && !$it_outsourcing_option3) {
    $ret = array(
        'result' => 'fail',
        'msg' => '옵션3을 선택해 주세요.',
    );
    echo json_encode($ret);
    exit;
}

$sql = "INSERT INTO g5_shop_order_outsourcing SET
                od_id = '{$od_id}',
                mb_id = '{$member['mb_id']}',
                it_id = '{$it_id}',
                oo_uid = '{$uid}',
                oo_outsourcing_option = '{$it_outsourcing_option}',
                oo_outsourcing_option2 = '{$it_outsourcing_option2}',
                oo_outsourcing_option3 = '{$it_outsourcing_option3}',
                oo_outsourcing_id = '{$it['it_outsourcing_id']}'
                ";
sql_query($sql);

// oo_outsourcing_company = '{$it['it_outsourcing_company']}',
// oo_outsourcing_manager = '{$it['it_outsourcing_manager']}',
// oo_outsourcing_email = '{$it['it_outsourcing_email']}'



$mail_contents = '
<div style="background-color:#f9f9f9;width:100%;max-width:800px;padding:30px;">
<div style="padding-bottom:30px;border-bottom:1px solid #cfcfcf;">
    <div style="color:#333333;position:relative;width:70%;float:left;">
        <p style="font-size:42px;padding:0;margin:0;"><b style="font-size:52px;">' . $it['it_outsourcing_company'] . '  ' . $it['it_outsourcing_manager'] . '님</b><br/>주문이 접수되었습니다.</p>
        <p>삼화에스앤디를 이용해주셔서 감사합니다.</p>
    </div>
    <div style="width:30%;float:right;" >
        <img src="'. G5_IMG_URL. '/logo_big.png" style="width:100%;" />
    </div>
    <div style="clear:both;"></div>
</div>
<div style="margin-top:50px;border-bottom:1px solid #cfcfcf;padding-bottom:20px;text-align: center;">
    <p style="margin:0;text-align:center;padding-bottom:30px;">신규주문정보</p>
    <table style="border:1px solid #c6c6c6;width:80%;margin:0 auto;border-collapse: collapse;border-spacing: 0;">
        <thead>
            <tr>
                <th style="background-color:#ffffff;border-bottom:1px solid #cfcfcf;font-weight:normal;line-height:30px;font-size:13px;">상품</th>
                <th style="background-color:#ffffff;border-bottom:1px solid #cfcfcf;font-weight:normal;line-height:30px;font-size:13px;">수량</th>
            </tr>
        </thead>
        <tbody>
';
foreach($carts as $cart) {
    $image = get_it_image($cart['it_id'], 50, 50);
    $mail_contents .= '
            <tr>
                <td style="text-align:left;color:#656565;font-size:13px;height:50px;background-color:#ffffff;padding-left:20px;">
                    <a href="' . G5_SHOP_URL .'/item.php?it_id='. $cart['it_id'] .'" style="text-decoration:none;color:#656565;">
                        ' . $image . ' <span style="line-height: 50px;vertical-align: top;">' . $cart['it_name'] . '</span>
                        <p style="margin-left:60px;">'. $cart['ct_option'] .'</p>
                        <p style="margin-left:60px;margin-bottom:20px;font-weight:bold;">'. $it_outsourcing_option .'</p>
                    </a>
                </td>
                <td style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;">
                    ' . number_format($cart['ct_qty']) . '
                </td>
            </tr>';
}
$mail_contents .= '
        </tbody>
    </table>
</div>
<div style="margin-top:50px;border-bottom:1px solid #cfcfcf;padding-bottom:20px;text-align: center;">
    <p style="margin:0;text-align:center;padding-bottom:30px;">배송정보</p>
    <table style="border:1px solid #c6c6c6;width:80%;margin:0 auto;border-collapse: collapse;border-spacing: 0;">
        <tbody>
            <tr>
                <th style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:20%;font-weight:normal;">
                    수령인
                </th>
                <td style="text-align:left;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:80%;">
                    ' . $od['od_b_name'] . '
                </td>
            </tr>
            <tr>
                <th style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:20%;font-weight:normal;">
                    전화번호
                </th>
                <td style="text-align:left;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:80%;">
                    ' . $od['od_b_tel'] . '
                </td>
            </tr>
            <tr>
                <th style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:20%;font-weight:normal;">
                    핸드폰
                </th>
                <td style="text-align:left;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:80%;">
                    ' . $od['od_b_hp'] . '
                </td>
            </tr>
            <tr>
                <th style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:20%;font-weight:normal;">
                    주소
                </th>
                <td style="text-align:left;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:80%;">
                    ' . $od['od_b_addr1'] . '(' . $od['od_b_addr3'] . ') ' . $od['od_b_addr2'] . '
                </td>
            </tr>
            <tr>
                <th style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:20%;font-weight:normal;">
                    전달메세지
                </th>
                <td style="text-align:left;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:80%;">
                    ' . $od['od_memo'] . '
                </td>
            </tr>
        </tbody>
    </table>
';
if ( count($files) > 0 ) { 
    $mail_contents .= '
    <div style="text-align: left;width: 80%;margin: 20px auto;">
        <img src="'. G5_IMG_URL. '/icon_file.png" style="float:left;display:block;" />
        <div style="float:left;margin-left:10px;">
            <span style="line-height:30px;font-size:13px;">첨부파일</span>
            <ul style="list-style:none;margin:0;padding:0;">';
            foreach($files as $file) {
                $mail_contents .= '<li style="list-style:none;margin:0;padding:0;margin-bottom:10px;"><a href="'. G5_URL .'/data/order_cart/'. $file['file_name'] .'" style="text-decoration: underline;color: #0592ff;font-size:12px;">' . $file['real_name'] . '</a></li>';
            }
            /*
                <li style="list-style:none;margin:0;padding:0;margin-bottom:10px;"><a href="#" style="text-decoration: underline;color: #0592ff;font-size:12px;">외부출고</a></li>
                <li style="list-style:none;margin:0;padding:0;margin-bottom:10px;"><a href="#" style="text-decoration: underline;color: #0592ff;font-size:12px;">외부출고</a></li>
            */
            $mail_contents .= '
            </ul>
        </div>
        <div style="clear:both;"></div>
    </div>';
}

$mail_contents .= '
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

$receiver = get_member($it['it_outsourcing_id']);
$email = $receiver['mb_email'];
mailer($config['cf_admin_email_name'], $config['cf_admin_email'], trim($receiver['mb_email']), '[삼화에스앤디] ' . $receiver['mb_name'] . '님 주문이 접수되었습니다.', $mail_contents, 1);
// mailer($config['cf_admin_email_name'], $config['cf_admin_email'], trim($it['it_outsourcing_email']), '[삼화에스앤디] ' . $it['it_outsourcing_manager'] . '님 주문이 접수되었습니다.', $mail_contents, 1);

set_order_admin_log($od_id, '상품: ' . addslashes($it['it_name']) . ' 외부발주 전송 ');


$ret = array(
    'result' => 'success',
    'msg' => '전송되었습니다.',
);
echo json_encode($ret);
exit;

?>