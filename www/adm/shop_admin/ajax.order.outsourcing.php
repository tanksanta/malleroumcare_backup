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
$it_outsourcing_option4 = get_text($_POST['it_outsourcing_option4']);
$it_outsourcing_option5 = get_text($_POST['it_outsourcing_option5']);
$sales_manager = get_text($_POST['sales_manager']);
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
// if (!$uid) {
//     $ret = array(
//         'result' => 'fail',
//         'msg' => '접근 오류입니다.',
//     );
//     echo json_encode($ret);
//     exit;
// }

// 상품정보
$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

// 카트 정보
// $sql = " select * from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and od_id = '$od_id' ";
// $sql = " select * from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and ct_uid = '$uid' and od_id = '$od_id' ORDER BY ct_id ASC";
$sql = " select * from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and ct_uid = '$uid' and od_id = '$od_id' and io_type = 0 ORDER BY ct_id ASC"; // 선택옵션만
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
if ($it['it_outsourcing_option4'] && !$it_outsourcing_option4) {
    $ret = array(
        'result' => 'fail',
        'msg' => '옵션4을 선택해 주세요.',
    );
    echo json_encode($ret);
    exit;
}
if ($it['it_outsourcing_option5'] && !$it_outsourcing_option5) {
    $ret = array(
        'result' => 'fail',
        'msg' => '옵션5을 선택해 주세요.',
    );
    echo json_encode($ret);
    exit;
}

if (!$sales_manager) {
    $ret = array(
        'result' => 'fail',
        'msg' => '담당자를 선택해주세요.',
    );
    echo json_encode($ret);
    exit;
}


$it_outsourcing_id = $it['it_outsourcing_id'];
// $it_outsourcing_id = $sales_manager;

$sql = "INSERT INTO g5_shop_order_outsourcing SET
                od_id = '{$od_id}',
                mb_id = '{$member['mb_id']}',
                it_id = '{$it_id}',
                oo_uid = '{$uid}',
                oo_outsourcing_option = '{$it_outsourcing_option}',
                oo_outsourcing_option2 = '{$it_outsourcing_option2}',
                oo_outsourcing_option3 = '{$it_outsourcing_option3}',
                oo_outsourcing_option4 = '{$it_outsourcing_option4}',
                oo_outsourcing_option5 = '{$it_outsourcing_option5}',
                oo_outsourcing_id = '$it_outsourcing_id'
                ";
sql_query($sql);

// oo_outsourcing_company = '{$it['it_outsourcing_company']}',
// oo_outsourcing_manager = '{$it['it_outsourcing_manager']}',
// oo_outsourcing_email = '{$it['it_outsourcing_email']}'

$manager = get_member($it['it_outsourcing_id']);

$od_delivrey = get_delivery_step($od['od_delivery_type']);

// $od_sales_manager = get_member($od['od_sales_manager']);
$od_sales_manager = get_member($sales_manager);

if ( $od['od_delivery_receiptperson'] == 0 ) {
    $od['mail_sender'] = '삼화';
    $od['mail_sender_phone'] = $default['de_admin_company_tel'];
    $od['mail_sender_addr'] = $default['de_admin_company_addr'];
}else{
    $od['mail_sender'] = $od['od_name'];
    $od['mail_sender_phone'] = $od['od_hp'] ? $od['od_hp'] : $od['od_tel'];
    $od['mail_sender_addr'] = $od['od_addr1'] . " " . $od['od_addr2'];
}

$sql = "SELECT * FROM g5_shop_order_cart_memo WHERE od_id = '{$od_id}' AND ctm_uid = '{$uid}'";
$item_memo = sql_fetch($sql);

$mail_contents = "
<div style='width: 100%;max-width: 800px;'>
    <img src='". G5_IMG_URL ."/mail_logo.png'>
    <h3 style='margin-top:10px;color:#333333'>". $manager['mb_name'] ."님 주문이 접수되었습니다.</h3>
    <div style='margin-top:5px;margin-bottom:5px;padding-top:5px;padding-bottom:5px;border-top:1px solid #ddd;border-bottom:1px solid #ddd;'>
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
    <h3 style='margin-top:10px;color:#333333'>발주정보</h3>
    <div style='background-color:#f8f8f8;border:1px solid #ddd;padding:5px;padding-top:10px;'>
    <table style='width:97%;border-collapse: collapse;border-spacing: 0;'>
    <tbody>
    ";
    foreach($carts as $cart) {
        $image = get_it_image($cart['it_id'], 50, 50);
        $mail_contents .= '
        <tr>
            <td style="text-align:left;color:#333333;font-size:13px;height:50px;padding-left:10px;">
                <a href="' . G5_SHOP_URL .'/item.php?it_id='. $cart['it_id'] .'" style="text-decoration:none;color:#656565;">
                    ' . $image . ' <span style="line-height: 50px;vertical-align: top;margin-left:15px;font-size:1.4em;">'. $it_outsourcing_option . ( $it_outsourcing_option2 ? ' / ' . $it_outsourcing_option2 : '' ) . ( $it_outsourcing_option3 ? ' / ' . $it_outsourcing_option3 : '' ) . ( $it_outsourcing_option4 ? ' / ' . $it_outsourcing_option4 : '' ) . ( $it_outsourcing_option5 ? ' / ' . $it_outsourcing_option5 : '' ) .'</span>
                    
                    <p style="margin-top:10px;margin-left:70px;font-size:1.4em;font-weight:bold;">
                        
                        <div style="color:#d11313;font-weight:bold;font-size:1.3em;margin-left:70px;margin-bottom:20px;">
                        수량:' . number_format($cart['ct_qty']) . '
                        </div>
                    </p>
                </a>
            </td>
        </tr>';
    }
    $mail_contents .= "
    </tbody>
    </table>
    </div>
    <h3 style='margin-top:10px;color:#333333'>배송정보</h3>
    <div style='background-color:#f8f8f8;border:1px solid #ddd;padding:10px;'>
        <h4 style='margin:0;padding:5px 10px;color:#333;'>보내실 곳(수령지)</h4>
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

        <hr style='width:100%;margin:5px auto;color:#ddd;background-color:#ddd;border-top:1px solid #ddd;' />

        <h4 style='margin:0;padding:5px 10px;color:#333;'>보내는 사람(발송자)</h4>
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
            <span style='color:#d11313;font-weight:bold;font-size:1em;'>
                ". $od['od_ex_date'] ."
            </span>
        </li>
        <li>
            <span style='color:#333;width:130px;font-weight:bold;display:inline-block;'>
                특이사항
            </span>
            :
            <span>
                ". ($item_memo['ctm_memo'] ? htmlspecialchars($item_memo['ctm_memo']) : '없음') ."
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

    <p style='font-size:0.9em;color:#656565;margin:30px auto;text-align:left;'>
        대표자: " . $default["de_admin_company_owner"] . " | 사업자등록번호: " . $default["de_admin_company_saupja_no"] . " | 통신판매신고번호: " . $default["de_admin_tongsin_no"] . " <br/>
        개인정보보호관리자: " . $default["de_admin_info_name"] . " | 주소: " . $default["de_admin_company_addr"] . "
        <br/><br/>
        Copyright © " . $default["de_admin_company_name"] . " All rights reserved.
    </p>
</div>
";

include_once(G5_LIB_PATH.'/mailer.lib.php');

$receiver = get_member($it['it_outsourcing_id']);
$email = $receiver['mb_email'];

$mail_title = $default['de_admin_company_name'] . '-' . $receiver['mb_name'] . date('-Ymd-His-', time()) . $od_sales_manager['mb_name'];
// echo $mail_title;
// exit;

mailer($config['cf_admin_email_name'], $config['cf_admin_email'], trim($receiver['mb_email']), $mail_title, $mail_contents, 1);
// mailer($config['cf_admin_email_name'], $config['cf_admin_email'], trim($it['it_outsourcing_email']), '[삼화에스앤디] ' . $it['it_outsourcing_manager'] . '님 주문이 접수되었습니다.', $mail_contents, 1);

set_order_admin_log($od_id, '상품: ' . addslashes($it['it_name']) . ' 외부발주 전송 ');


$ret = array(
    'result' => 'success',
    'msg' => '전송되었습니다.',
);
echo json_encode($ret);
exit;

?>