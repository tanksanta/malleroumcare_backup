<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

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

$ot_typereceipt_cate = (int)$ot_typereceipt_cate ? (int)$ot_typereceipt_cate : 0;
$ot_typereceipt = (int)$ot_typereceipt ? (int)$ot_typereceipt : 0;

if ( $ot_confirm_number && !is_numeric($ot_confirm_number) ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '승인번호는 숫자만 입력 가능합니다.',
    );
    echo json_encode($ret);
    exit;
}


$sql_str = "
        ot_typereceipt_cate = '$ot_typereceipt_cate',
        ot_typereceipt      = '$ot_typereceipt',
";

// 현금영수증
if ( $ot_typereceipt == 31 ) {
    $sql_str .= "
        ot_typereceipt_cuse = '$ot_typereceipt_cuse',
        ot_btel             = '$p_typereceipt_btel',
        ot_bnum             = '$p_typereceipt_bnum',
        ot_tax_email        = '$p_typereceipt_email',
    ";
}

// 세금계산서
if ( $ot_typereceipt == 11 ) {

    $ot_location_zip1 = preg_replace('/[^0-9]/', '', substr($_POST['ot_location_zip'], 0, 3));
    $ot_location_zip2 = preg_replace('/[^0-9]/', '', substr($_POST['ot_location_zip'], 3));

    $sql_str .= "
        ot_typereceipt_cuse = '0',
        ot_bname = '{$ot_bname}',
        ot_boss_name = '{$ot_boss_name}',
        ot_btel = '{$ot_btel}',
        ot_bnum = '{$ot_bnum}',
        ot_buptae = '{$ot_buptae}',
        ot_bupjong = '{$ot_bupjong}',
        ot_tax_email = '{$ot_tax_email}',
        ot_manager_name = '{$ot_manager_name}',
        ot_location_zip1 = '{$ot_location_zip1}',
        ot_location_zip2 = '{$ot_location_zip2}',
        ot_location_addr1 = '{$ot_location_addr1}',
        ot_location_addr2 = '{$ot_location_addr2}',
        ot_location_addr3 = '{$ot_location_addr3}',
        ot_location_jibeon = '{$ot_location_jibeon}',
    ";

}

$ot_etc = htmlspecialchars($ot_etc);
$ot_confirm_number = $ot_confirm_number ? htmlspecialchars($ot_confirm_number) : 0;
$ot_time_hour = sprintf('%02d',(int)$ot_time_hour);
$ot_time_date = $ot_time_date ? $ot_time_date : '0000-00-00';

sql_query("DELETE FROM g5_shop_order_typereceipt WHERE od_id = '{$od_id}'");

$sql = " insert g5_shop_order_typereceipt
            set od_id               = '$od_id',
                {$sql_str}
                ot_time_date        = '{$ot_time_date}',
                ot_time_hour        = '{$ot_time_hour}',
                ot_confirm_number   = '{$ot_confirm_number}',
                ot_etc              = '{$ot_etc}'

";
sql_query($sql);

$ret = array(
    'result' => 'success',
    'msg' => '매출증빙 내용이 수정되었습니다.',
);
$json = json_encode($ret);
echo $json;
?>