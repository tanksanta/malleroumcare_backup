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
        $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];

        // 선택옵션 개수
        if(!$row['io_type'])
            $lst_count++;
    }


    $comma = '';
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
        $io_price = $chk_partner_price && $opt_list[$io_type][$io_id]['price_partner'] ? $opt_list[$io_type][$io_id]['price_partner'] : $io_price;
        $ct_qty = (int)$_POST['ct_qty'][$it_id][$k];
        $it_price = $chk_dealer_price && $it['it_price_dealer'] ? $it['it_price_dealer'] : $it['it_price'];
        $it_price = $chk_partner_price && $it['it_price_partner'] ? $it['it_price_partner'] : $it_price;


        $sql2 = " select ct_id, io_type, ct_qty
                        from {$g5['g5_shop_cart_table']}
                        where od_id = '$od_id'
                          and it_id = '$it_id'
						  and ct_id = '$ct_id'
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
                        set ct_qty = ct_qty + '$ct_qty'
                        where ct_id = '{$row2['ct_id']}' ";
            sql_query($sql3);
            continue;
        }

        $io_value = sql_real_escape_string(strip_tags($io_value));
        $remote_addr = get_real_client_ip();

        $add_ct_discount = $i == 0 && $k == 0 ? $ct_discount : 0;

        $point = 0;

        $io_value = $io_value ? $io_value : addslashes($it['it_name']);

		//, it_sc_qty='{$it['it_sc_qty']}', ct_price='{$it_price}', ct_point='$point',

		$sql = "select * from {$g5['g5_shop_cart_table']} where od_id='".$od_id."' and it_id='".$oit_id."' and ct_id='".$ct_id."'";
		$cart_row = sql_fetch($sql);

		if($_POST['chk_maching_data']){
			$ma_row = sql_fetch("select count(*) as cnt from g5_shop_matching where oit_id='".$oit_id."'");
			if($io_id && $io_type) $add_query = ", io_id='$io_id', io_type='$io_type'";
			if($ma_row[0]==1){
				$up_sql = "update g5_shop_matching set oit_name='{$cart_row['it_name']}',oio_id='{$cart_row['ct_option']}',it_id='{$it['it_id']}',it_name='".addslashes($it['it_name'])."', it_sc_type='{$it['it_sc_type']}', it_sc_method='{$it['it_sc_method']}', it_sc_minimum='{$it['it_sc_minimum']}', ct_option='$io_value', ct_notax='{$it['it_notax']}'".$add_query." where oit_id='".$oit_id."'";
				sql_query($up_sql);
			}else{
				$in_sql = "insert into g5_shop_matching set oit_id='".$oit_id."',oit_name='{$cart_row['it_name']}',oio_id='{$cart_row['ct_option']}',it_id='{$it['it_id']}',it_name='".addslashes($it['it_name'])."', it_sc_type='{$it['it_sc_type']}', it_sc_method='{$it['it_sc_method']}', it_sc_minimum='{$it['it_sc_minimum']}', ct_option='$io_value', ct_qty='$ct_qty', ct_notax='{$it['it_notax']}'".$add_query;
				sql_query($in_sql);
			}
		}

		$sql = "UPDATE {$g5['g5_shop_cart_table']}
                SET  mb_id='{$od['mb_id']}', it_id='{$it['it_id']}', it_name='".addslashes($it['it_name'])."', it_sc_type='{$it['it_sc_type']}', it_sc_method='{$it['it_sc_method']}', it_sc_minimum='{$it['it_sc_minimum']}', it_sc_qty='{$it['it_sc_qty']}', ct_status='주문', ct_point_use='0', ct_stock_use='0', ct_option='$io_value', ct_qty='$ct_qty', ct_notax='{$it['it_notax']}', io_id='$io_id', io_type='$io_type', ct_time='".G5_TIME_YMDHIS."', ct_ip='$remote_addr', ct_send_cost='$ct_send_cost', ct_direct='$sw_direct', ct_select='$ct_select', ct_select_time='$ct_select_time', pt_it='{$it['pt_it']}', pt_msg1='$pt_msg1', pt_msg2='$pt_msg2', pt_msg3='$pt_msg3' where od_id='".$od_id."' and it_id='".$oit_id."' and ct_id='".$ct_id."'";

        sql_query($sql);

        $ct_count++;

        // echo '<pre>'. $sql . '</pre>';
        set_order_admin_log($od_id, '상품: ' . addslashes($it['it_name']) . ', ' . $io_id .' 상품 추가 또는 수정');

    }
    //echo $it_id;
    //print_r2($io_types);
}


// 주문페이지 계산하기
samhwa_order_calc($od_id);


// 비고
$sql = "DELETE FROM g5_shop_order_cart_memo WHERE od_id = '{$od_id}' AND it_id = '{$it_ids[0]}'";
sql_query($sql);

$sql = "INSERT INTO g5_shop_order_cart_memo SET
            od_id = '{$od_id}' ,
            it_id = '{$it_ids[0]}',
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
    lightpanel_led_direction           = '{$lightpanel_led_direction}',
    lightpanel_led_qty                  = '{$lightpanel_led_qty}',
    lightpanel_smps         = '{$lightpanel_smps}',
    lightpanel_power_line         = '{$lightpanel_power_line}',
    lightpanel_led_ea         = '{$lightpanel_led_ea}',
    lightpanel_led_k         = '{$lightpanel_led_k}',
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
if ( $cs_type ) {
    $data = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$od_id}' AND it_id = '{$it_ids[0]}' ");
    if($data['odc_no']) {
        sql_query(" update g5_shop_order_custom set {$custom_querys} WHERE od_id = '{$od_id}' AND it_id = '{$it_ids[0]}' ");
    } else {
        sql_query(" insert g5_shop_order_custom set {$custom_querys}, od_id = '{$od_id}', it_id = '{$it_ids[0]}' ");
    }
}

sql_query("update {$g5['g5_shop_order_table']} set od_status='주문' where od_id = '$od_id' ");


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