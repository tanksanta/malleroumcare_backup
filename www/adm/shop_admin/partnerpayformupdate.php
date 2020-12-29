<?php
$sub_menu = '400450';
include_once('./_common.php');

check_admin_token();

if( isset($_POST['pp_name']) ){
	$_POST['pp_name'] = strip_tags(clean_xss_attributes($_POST['pp_name']));
}

if($w == 'd') {
    auth_check($auth[$sub_menu], 'd');

    $sql = " select pp_id from g5_shop_partnerpay where pp_id = '{$_GET['pp_id']}' ";
    $row = sql_fetch($sql);
    if(!$row['pp_id'])
        alert('삭제하시려는 자료가 존재하지 않습니다.');

    sql_query(" delete from g5_shop_partnerpay where pp_id = '{$_GET['pp_id']}' ");

    goto_url('./personalpaylist.php?'.$qstr);
} else {
    auth_check($auth[$sub_menu], 'w');

    $_POST = array_map('trim', $_POST);


    if(!$_POST['mb_id'])
        alert('아이디를 입력해 주십시오.');

    $ptmb = get_member($_POST['mb_id'], 'mb_id');
    if ( !$ptmb['mb_id'] ) {
        alert('존재하지 않는 아이디입니다.');
    }

    if(!$_POST['pp_name'])
        alert('이름을 입력해 주십시오.');
        /*
    if(!$_POST['pp_price'])
        alert('주문금액을 입력해 주십시오.');
    if(preg_match('/[^0-9]/', $_POST['pp_price']))
        alert('주문금액은 숫자만 입력해 주십시오.');
        */

    $od_id = preg_replace('/[^0-9]/', '', $_POST['od_id']);

    if($_POST['od_id']) {
        $sql = " select od_id from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
        $row = sql_fetch($sql);
        if(!$row['od_id'])
            alert('입력하신 주문번호는 존재하지 않는 주문 자료입니다.');
    }

    $sql_common = " pp_name             = '{$_POST['pp_name']}',
                    pp_price            = '{$_POST['pp_price']}',
                    od_id               = '$od_id',
                    pp_content          = '{$_POST['pp_content']}',
                    pp_receipt_price    = '{$_POST['pp_receipt_price']}',
                    pp_settle_case      = '{$_POST['pp_settle_case']}',
                    pp_receipt_time     = '{$_POST['pp_receipt_time']}',
                    pp_shop_memo        = '{$_POST['pp_shop_memo']}',
                    mb_id               = '{$_POST['mb_id']}',
                    pp_use              = '{$_POST['pp_use']}' ";
}

if($w == '') {
    $pp_id = get_uniqid();
    $sql = " insert into g5_shop_partnerpay
                set pp_id = '$pp_id',
                    $sql_common ,
                    pp_ip   = '{$_SERVER['REMOTE_ADDR']}',
                    pp_time = '".G5_TIME_YMDHIS."' ";
    sql_query($sql);
} else if($w == 'u') {
    $sql = " select pp_id from g5_shop_partnerpay where pp_id = '{$_POST['pp_id']}' ";
    $row = sql_fetch($sql);
    if(!$row['pp_id'])
        alert('수정하시려는 자료가 존재하지 않습니다.');

    $sql = " update g5_shop_partnerpay
                set $sql_common
                where pp_id = '{$_POST['pp_id']}' ";
    sql_query($sql);
}
/*
if( $_POST['pp_receipt_price'] > 0 && $_POST['od_id'] ) {
    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
    $row = sql_fetch($sql);
    if(!$row['od_id'])
        alert('입력하신 주문번호는 존재하지 않는 주문 자료입니다.');

    $need_price = $row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2'];
    $od_misu = $need_price - $_POST['pp_receipt_price'];

    $sql = " UPDATE {$g5['g5_shop_order_table']} SET od_misu = '{$od_misu}', od_receipt_price = '{$_POST['pp_receipt_price']}' where od_id = '$od_id' ";
    sql_query($sql);
}
*/
if( $_POST['pp_receipt_price'] > 0 ) {
    $sql = " select * from g5_shop_partnerpay where pp_id = '{$_GET['pp_id']}' ";
    $row = sql_fetch($sql);

    if ( $row['pp_price'] != $_POST['pp_receipt_price'] ) {
        
        $r_price = (int)$_POST['pp_receipt_price'];
        
        $sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE mb_id = '{$_POST['mb_id']}' AND od_misu > 0 ORDER BY od_time ASC";
        $orders_result = sql_query($sql);
        $orders = array();
        while($orders_row = sql_fetch_array($orders_result)) {
            $orders[] = $orders_row;
        }

        $i = 0;
        while($r_price > 0) {
            
            //$r_price = $r_p
            if ( $r_price >= $orders[$i]['od_misu'] ) {
                $r_price = $r_price - $orders[0]['od_misu'];
                $add_price = $orders[$i]['od_misu'];
            }else{
                $add_price = $r_price;
                $r_price = 0;
            }


            $sql = " UPDATE {$g5['g5_shop_order_table']} SET od_misu = od_misu - {$add_price}, od_receipt_price = od_receipt_price + {$add_price} where od_id = '{$orders[$i]['od_id']}' ";
            //echo $sql . PHP_EOL;
            sql_query($sql);
            //echo $r_price . PHP_EOL;

            $sql = " INSERT INTO g5_shop_partnerpay_log SET pp_id = '{$pp_id}' , od_id = '{$orders[$i]['od_id']}' , lg_price = '{$add_price}' , mb_id = '{$member['mb_id']}', lg_time = now()";
            //echo $sql . PHP_EOL;
            sql_query($sql);

            $i++;
        }


    }
}

if($popup == 'yes')
    alert_close('파트너결제가 추가됐습니다.');
else
    goto_url('./partnerpayform.php?w=u&amp;pp_id='.$pp_id.'&amp;'.$qstr);
?>