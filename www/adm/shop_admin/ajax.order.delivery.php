<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_id = trim($_POST['od_id']);

if (!$od_id) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

$od_b_zip1 = preg_replace('/[^0-9]/', '', substr($_POST['od_b_zip'], 0, 3));
$od_b_zip2 = preg_replace('/[^0-9]/', '', substr($_POST['od_b_zip'], 3));
$od_email = strip_tags(clean_xss_attributes($od_email));

$od_delivery_text = $_POST['od_delivery_text'][$od_delivery_type_data];
$od_delivery_place = $_POST['od_delivery_place'][$od_delivery_type_data];
$od_delivery_tel = $_POST['od_delivery_tel'][$od_delivery_type_data];
$od_delivery_receiptperson = $_POST['od_delivery_receiptperson'][$od_delivery_type_data];
$od_delivery_qty = $_POST['od_delivery_qty'][$od_delivery_type_data];
$od_delivery_company = $_POST['od_delivery_company'][$od_delivery_type_data];
$od_delivery_price = $_POST['od_delivery_price'][$od_delivery_type_data];
$od_delivery_price = (int)$od_delivery_price ? $od_delivery_price : 0;

$sql = " update {$g5['g5_shop_order_table']}
                set 
                    od_b_name = '$od_b_name',
                    od_b_tel = '$od_b_tel',
                    od_b_hp = '$od_b_hp',
                    od_b_zip1 = '$od_b_zip1',
                    od_b_zip2 = '$od_b_zip2',
                    od_b_addr1 = '$od_b_addr1',
                    od_b_addr2 = '$od_b_addr2',
                    od_b_addr3 = '$od_b_addr3',
                    od_b_addr_jibeon = '$od_b_addr_jibeon',
                    od_ex_date = '$od_ex_date',
                    od_memo = '$od_memo',
                    od_send_cost2 = '$od_send_cost2',
                    od_delivery_type = '$od_delivery_type',
                    od_delivery_company = '$od_delivery_company',
                    od_delivery_text = '$od_delivery_text',
                    od_delivery_place = '$od_delivery_place',
                    od_delivery_tel = '$od_delivery_tel',
                    od_delivery_receiptperson = '$od_delivery_receiptperson',
                    od_delivery_qty = '$od_delivery_qty',
                    od_delivery_price = '$od_delivery_price',
                    od_send_admin_memo = '$od_send_admin_memo',
                    od_invoice_time = now(),
                    od_invoice = '$od_delivery_text'
                    ";
$sql .= " where od_id = '$od_id' ";
sql_query($sql);

if ( $type == 'print') {
    set_order_admin_log($od_id, '배송정보 프린트');    
}else{
    set_order_admin_log($od_id, '배송정보 변경');
}

$ret = array(
    'result' => 'success',
    'msg' => '배송정보가 변경되었습니다.',
);
$json = json_encode($ret);
echo $json;
?>
