<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "w");

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

$ct_discount = (int)$ct_discount ? (int)$ct_discount : 0;

// print_r2($_POST);

$it_ids = $_POST['it_id'];

for($i=0; $i<count($it_ids); $i++) {

    $it_id = $it_ids[$i];

    if ( $w ) {

        if (!$uid) alert("잘못된 접근입니다(1).");

        // $sql = "DELETE FROM {$g5['g5_shop_cart_table']} WHERE od_id = '$od_id' AND it_id = '$it_id'";
        $sql = "DELETE FROM {$g5['g5_shop_cart_table']} WHERE od_id = '$od_id' AND ct_uid = '$uid'";
        sql_query($sql);
    }

    // 상품정보
    $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);

    if($it['it_sc_type'] == 1)
        $ct_send_cost = 2; // 무료
    else if($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1)
        $ct_send_cost = 1; // 착불
    else
        $ct_send_cost = 0;

    $opt_count = count($_POST['io_id'][$it_id]);

    if($opt_count && $_POST['io_type'][$it_id][0] != 0)
        alert('상품의 선택옵션을 선택해 주십시오.');

    for($k=0; $k<$opt_count; $k++) {
        if ($_POST['ct_qty'][$it_id][$k] < 1)
            alert('수량은 1 이상 입력해 주십시오.');
    }

    // 옵션정보를 얻어서 배열에 저장
    $opt_list = array();
    $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' and io_use = 1 order by io_no asc ";
    $result = sql_query($sql);
    $lst_count = 0;
    for($k=0; $row=sql_fetch_array($result); $k++) {
        $opt_list[$row['io_type']][$row['io_id']]['id'] = $row['io_id'];
        $opt_list[$row['io_type']][$row['io_id']]['use'] = $row['io_use'];
        $opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
        $opt_list[$row['io_type']][$row['io_id']]['price_partner'] = $row['io_price_partner'];
        $opt_list[$row['io_type']][$row['io_id']]['price_dealer'] = $row['io_price_dealer'];
        $opt_list[$row['io_type']][$row['io_id']]['price_dealer2'] = $row['io_price_dealer2'];
        $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];
        $opt_list[$row['io_type']][$row['io_id']]['io_thezone'] = $row['io_thezone'];

        // 선택옵션 개수
        if(!$row['io_type'])
            $lst_count++;
    }

    if (!$uid) {
        $uid = uuidv4();
    }

    $comma = '';
    $sql = " INSERT INTO {$g5['g5_shop_cart_table']}
                    ( od_id, mb_id, it_id, it_name, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty, ct_status, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_send_cost, ct_direct, ct_select, ct_select_time, pt_it, pt_msg1, pt_msg2, pt_msg3, ct_history, ct_discount, ct_price_type, ct_uid, io_thezone )
                VALUES ";

    $ct_select = 1;
    $ct_select_time = G5_TIME_YMDHIS;
    $sw_direct = 0;
    
    for($k=0;$k<$opt_count;$k++) {
        $io_id = preg_replace(G5_OPTION_ID_FILTER, '', $_POST['io_id'][$it_id][$k]);
        $io_type = preg_replace('#[^01]#', '', $_POST['io_type'][$it_id][$k]);
        $io_value = $_POST['io_value'][$it_id][$k];

        $pt_msg1 = get_text($_POST['pt_msg1'][$it_id][$k]);
        $pt_msg2 = get_text($_POST['pt_msg2'][$it_id][$k]);
        $pt_msg3 = get_text($_POST['pt_msg3'][$it_id][$k]);

        $io_price = $chk_dealer_price && $opt_list[$io_type][$io_id]['price_dealer'] ? $opt_list[$io_type][$io_id]['price_dealer'] : $opt_list[$io_type][$io_id]['price'];
        $io_price = $chk_dealer2_price && $opt_list[$io_type][$io_id]['price_dealer2'] ? $opt_list[$io_type][$io_id]['price_dealer2'] : $opt_list[$io_type][$io_id]['price'];
        $io_price = $chk_partner_price && $opt_list[$io_type][$io_id]['price_partner'] ? $opt_list[$io_type][$io_id]['price_partner'] : $io_price;
        // 임의 상품 옵션 가격 적용
        $io_price = $chk_custom_price ? $_POST['io_price'][$it_id][$k] : $opt_list[$io_type][$io_id]['price'];
        $io_thezone = $opt_list[$io_type][$io_id]['io_thezone'];
        
        $ct_qty = (int)$_POST['ct_qty'][$it_id][$k];
        $it_price = $chk_dealer_price && $it['it_price_dealer'] ? $it['it_price_dealer'] : $it['it_price'];
        $it_price = $chk_dealer2_price && $it['it_price_dealer2'] ? $it['it_price_dealer2'] : $it_price;
        $it_price = $chk_partner_price && $it['it_price_partner'] ? $it['it_price_partner'] : $it_price;
        // 임의 상품 가격 적용
        $it_price = $chk_custom_price ? $_POST['it_price_custom'] : $it_price;

        // ???가 적용
        $ct_price_type = $_POST['chk_partner_price'] ? '1' : '0';
        $ct_price_type = $_POST['chk_dealer_price'] ? '2' : $ct_price_type;
        $ct_price_type = $_POST['chk_dealer2_price'] ? '3' : $ct_price_type;
        $ct_price_type = $_POST['chk_custom_price'] ? '4' : $ct_price_type;

        //echo $ct_price_type;
        //exit;


        $sql2 = " select ct_id, io_type, ct_qty
                        from {$g5['g5_shop_cart_table']}
                        where od_id = '$od_id'
                          and it_id = '$it_id'
                          and io_id = '$io_id'
						  and pt_msg1 = '{$pt_msg1}'
						  and pt_msg2 = '{$pt_msg2}'
						  and pt_msg3 = '{$pt_msg3}'
						  and ct_status = '쇼핑' ";
        $row2 = sql_fetch($sql2);
        if($row2['ct_id']) {
            // 재고체크
            $tmp_ct_qty = $row2['ct_qty'];
            if(!$io_id)
                $tmp_it_stock_qty = get_it_stock_qty($it_id);
            else
                $tmp_it_stock_qty = get_option_stock_qty($it_id, $io_id, $row2['io_type']);

            if ($tmp_ct_qty + $ct_qty > $tmp_it_stock_qty)
            {
                alert($io_value." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($tmp_it_stock_qty) . " 개");
            }

            $sql3 = " update {$g5['g5_shop_cart_table']}
                        set ct_qty = ct_qty + '$ct_qty',
                        ct_uid = '$uid'
                        where ct_id = '{$row2['ct_id']}' ";
            sql_query($sql3);
            continue;
        }

        $io_value = sql_real_escape_string(strip_tags($io_value));
        $remote_addr = get_real_client_ip();

        $add_ct_discount = $i == 0 && $k == 0 ? $ct_discount : 0;

        $point = 0;

        $io_value = $io_value ? $io_value : addslashes($it['it_name']);

        $sql .= $comma."( '$od_id', '{$od['mb_id']}', '{$it['it_id']}', '".addslashes($it['it_name'])."', '{$it['it_sc_type']}', '{$it['it_sc_method']}', '{$it['it_sc_price']}', '{$it['it_sc_minimum']}', '{$it['it_sc_qty']}', '작성', '{$it_price}', '$point', '0', '0', '$io_value', '$ct_qty', '{$it['it_notax']}', '$io_id', '$io_type', '$io_price', '".G5_TIME_YMDHIS."', '$remote_addr', '$ct_send_cost', '$sw_direct', '$ct_select', '$ct_select_time', '{$it['pt_it']}', '$pt_msg1', '$pt_msg2', '$pt_msg3', '', '$add_ct_discount', '$ct_price_type', '$uid', '$io_thezone' )";
        $comma = ' , ';
        $ct_count++;

        // echo '<pre>'. $sql . '</pre>';
        set_order_admin_log($od_id, '상품: ' . addslashes($it['it_name']) . ', ' . $io_id .' 상품 추가 또는 수정');

    }
    
    sql_query($sql);
    //echo $it_id;
    //print_r2($io_types);
}

// $sendcost = get_sendcost($od_id);
// sql_query("UPDATE {$g5['g5_shop_order_table']} SET od_send_cost = '{$sendcost}' WHERE od_id = '{$od_id}'");


// 주문페이지 계산하기
samhwa_order_calc($od_id);


// 비고
// $sql = "DELETE FROM g5_shop_order_cart_memo WHERE od_id = '{$od_id}' AND it_id = '{$it_ids[0]}'";
$sql = "DELETE FROM g5_shop_order_cart_memo WHERE od_id = '{$od_id}' AND ctm_uid = '{$uid}'";
sql_query($sql);

// $sql = "INSERT INTO g5_shop_order_cart_memo SET
//             od_id = '{$od_id}' ,
//             it_id = '{$it_ids[0]}',
//             ctm_memo = '{$g5_shop_order_cart_memo}'
//         ";
$sql = "INSERT INTO g5_shop_order_cart_memo SET
            od_id = '{$od_id}' ,
            ctm_uid = '{$uid}',
            ctm_memo = '{$g5_shop_order_cart_memo}'
        ";
sql_query($sql);

function get_int($val) {
    return $val ? $val : 0;
}

// 주문제작
if ( $frame_color == '기타' ) {
    $frame_color = $frame_color_other;
}

$cs_type = get_int($cs_type);
$size_width = get_int($size_width);
$size_height = get_int($size_height);
$frame_front_transparent_acrylic = get_int($frame_front_transparent_acrylic);
$frame_front_optical_scatter = get_int($frame_front_optical_scatter);
$frame_back_transparent_acrylic = get_int($frame_back_transparent_acrylic);
$frame_back_mdf = get_int($frame_back_mdf);
$frame_back_formax = get_int($frame_back_formax);
$lightpanel_led_qty = get_int($lightpanel_led_qty);
$lightpanel_led_ea = get_int($lightpanel_led_ea);
$lightpanel_led_k = get_int($lightpanel_led_k);
$holder_pipe_interval_1 = get_int($holder_pipe_interval_1);
$holder_pipe_interval_2 = get_int($holder_pipe_interval_2);
$holder_pipe_interval_3 = get_int($holder_pipe_interval_3);
$holder_pipe_length = get_int($holder_pipe_length);

$custom_querys .= "
    cs_type                             = '{$cs_type}',
    size_use                            = '{$size_use}',
    size_width                          = '{$size_width}',
    size_height                         = '{$size_height}',
    frame_use                           = '{$frame_use}',
    frame_standard                      = '{$frame_standard}',
    frame_color                         = '{$frame_color}',
    frame_front                         = '{$frame_front}',
    frame_front_transparent_acrylic     = '{$frame_front_transparent_acrylic}',
    frame_front_optical_scatter         = '{$frame_front_optical_scatter}',
    frame_back                          = '{$frame_back}',
    frame_back_transparent_acrylic      = '{$frame_back_transparent_acrylic}',
    frame_back_mdf                      = '{$frame_back_mdf}',
    frame_back_formax                   = '{$frame_back_formax}',
    lightpanel_use                      = '{$lightpanel_use}',
    lightpanel_led_direction           = '{$lightpanel_led_direction}',
    lightpanel_led_qty                  = '{$lightpanel_led_qty}',
    lightpanel_smps         = '{$lightpanel_smps}',
    lightpanel_power_line         = '{$lightpanel_power_line}',
    lightpanel_led_ea         = '{$lightpanel_led_ea}',
    lightpanel_led_k         = '{$lightpanel_led_k}',
    lightpanel_power_line_ac         = '{$lightpanel_power_line_ac}',
    lightpanel_power_line_dc         = '{$lightpanel_power_line_dc}',
    lightpanel_power_line_wire         = '{$lightpanel_power_line_wire}',
    lightpanel_laser         = '{$lightpanel_laser}',
    lightpanel_switch_use         = '{$lightpanel_switch_use}',
    lightpanel_switch_explain         = '{$lightpanel_switch_explain}',
    lightpanel_switch         = '{$lightpanel_switch}',
    holder_use         = '{$holder_use}',
    holder_class         = '{$holder_class}',
    holder_pipe_interval_1         = '{$holder_pipe_interval_1}',
    holder_pipe_interval_2         = '{$holder_pipe_interval_2}',
    holder_pipe_interval_3         = '{$holder_pipe_interval_3}',
    holder_pipe_length         = '{$holder_pipe_length}',
    printout_use         = '{$printout_use}',
    printout_printout         = '{$printout_printout}',
    content_use         = '{$content_use}',
    content_common         = '{$content_common}',
    content_minart         = '{$content_minart}',
    content_selmartec         = '{$content_selmartec}',
    content_lp         = '{$content_lp}'
";

// if ( $cs_type ) {
//     $data = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$od_id}' AND it_id = '{$it_ids[0]}' ");
//     if($data['odc_no']) {
//         sql_query(" update g5_shop_order_custom set {$custom_querys} WHERE od_id = '{$od_id}' AND it_id = '{$it_ids[0]}' ");
//     } else {
//         sql_query(" insert g5_shop_order_custom set {$custom_querys}, od_id = '{$od_id}', it_id = '{$it_ids[0]}' ");
//     }
// }
if ( $cs_type ) {
    $data = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$od_id}' AND odc_uid = '{$uid}' ");
    if($data['odc_no']) {
        sql_query(" update g5_shop_order_custom set {$custom_querys} WHERE od_id = '{$od_id}' AND odc_uid = '{$uid}' ");
    } else {
        sql_query(" insert g5_shop_order_custom set {$custom_querys}, od_id = '{$od_id}', odc_uid = '{$uid}' ");
    }
}

// 상품수 수정
$sql = " select COUNT(distinct it_id, ct_uid) as cart_count
            from {$g5['g5_shop_cart_table']} where od_id = '$od_id'  ";
$row = sql_fetch($sql);

sql_query("update {$g5['g5_shop_order_table']} set od_cart_count = '{$row['cart_count']}' where od_id = '$od_id' ");

$title = $w ? '상품 수정 > 옵션선택' : '상품 추가 > 옵션선택';
?>
<html>
<head>
<title><?php echo $title; ?></title>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css">
</head>
<script>

 alert('완료되었습니다.');

try{

    window.opener.document.location.href=window.opener.document.URL;
    window.close();

}catch(e){ 
    window.close();
}

</script>