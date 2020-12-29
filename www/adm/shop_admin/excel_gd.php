<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");


$today = date("YmdHis");

Header("Content-type: application/vnd.ms-excel");
Header("Content-type: charset=utf-8");
header("Content-Disposition: attachment; filename=order_{$today}.xls");
Header("Content-Description: PHP3 Generated Data");
Header("Pragma: no-cache");
Header("Expires: 0");

echo "<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel;charset=utf-8\">";

$str	= "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"1\">";
$str	.= "<tr>
                <th colspan='13' style='font-size:18pt;'>송품의뢰서</th>
            </tr>";	
$str	.= "<tr>
                <th colspan='4' align='left'>".date("Y-m-d")."</th>
                <th colspan='9' align='right'>전화번호 : 031-994-4116</th>
            </tr>";
$str	.= "<tr bgcolor=\"#eeeeee\">
                <th>순번</th>
                <th>출고일</th>
                <th>보내는사람</th>
                <th>도착지</th>
                <th>내용물</th>
                <th>받는사람</th>

                <th>전화번호</th>
                <th>수량</th>
                <th>운임</th>
                <th>선불화물</th>
                <th>착불화물</th>
                <th>선불택배</th>

                <th>착불택배</th>
            </tr>";	

if ( $ret_od_id ) {
    $od_ids = explode('|', $ret_od_id);

    $where = array();
    foreach($od_ids as $od_id) {
        $where[] = " od_id = '{$od_id}' ";
    }
    $sql_search = ' where '.implode(' OR ', $where);
    $sql = "SELECT * FROM {$g5['g5_shop_order_table']} $sql_search ";
}else{
    $sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE ( od_status = '출고준비' OR od_status = '배송' ) AND ( od_delivery_type = 'gdhuamul1' OR od_delivery_type = 'gdhuamul2' OR od_delivery_type = 'huamul1' OR od_delivery_type = 'huamul2' ) ";
}

$result = sql_query($sql);

$index = 1;
while($row = sql_fetch_array($result)) {

    $sql = "SELECT * FROM g5_shop_cart WHERE od_id = '{$row['od_id']}'";
    $cart_result = sql_query($sql);
    $row['cart'] = array();
    while ( $row2 = sql_fetch_array($cart_result) ) {
        $row['cart'][] = $row2;
    }

    if ( $row['od_delivery_receiptperson'] == '1' ) {
        $order_name = $row['od_name'];
    }else{
        $order_name = '삼화에스엔디(주) 고양지점';
    }
    
    $goods_name = $row['cart'][0]['it_name'] ? $row['cart'][0]['it_name'] . '(' . count($row['cart']) . '개)' : '';

    /*
    // 총 금액
    $total_price = $row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2'] - $row['od_cart_discount'] - $row['od_cart_discount2'];
    $show_total_price = number_format($total_price);
    */


    $color_1 = $color_2 = $color_3 = $color_4 = "";
    $text_1			= "선불화물";
    $text_2			= "착불화물";
    $text_3			= "선불택배";
    $text_4			= "착불택배";
    if( $row['od_delivery_type'] == 'gdhuamul1' ){
        $color_1		= " bgcolor='yellow'";
        $text_1			= "<b>★".$text_1."★</b>";
    }
    if( $row['od_delivery_type'] == 'gdhuamul2' ){
        $color_2		= " bgcolor='yellow'";
        $text_2			= "<b>★".$text_2."★</b>";
    }
    if( $row['od_delivery_type'] == 'huamul1' ){
        $color_3		= " bgcolor='yellow'";
        $text_3			= "<b>★".$text_3."★</b>";
    }
    if( $row['od_delivery_type'] == 'huamul2' ){
        $color_4		= " bgcolor='yellow'";
        $text_4			= "<b>★".$text_4."★</b>";
    }

    if ($row['od_delivery_type'] == 'gdhuamul1' || $row['od_delivery_type'] == 'gdhuamul2') {
        $recipient_address = $row['od_b_addr1'] . ' ' . $row['od_b_addr2']; // 도로명 + 상세주소
        $recipient_address = $row['od_delivery_place'] ? $row['od_delivery_place'] : $recipient_address;
        // $recipient_address = $row['od_b_addr1'] . ' ' . $row['od_b_addr2']; // 도로명 + 상세주소
        //$recipient_address = $row['od_delivery_text'];
    }else{
        //$recipient_address = $row['od_b_addr3'] . $row['od_b_addr2']; // 지번 + 상세주소
        $recipient_address = $row['od_b_addr1'] . ' ' . $row['od_b_addr2']; // 도로명 + 상세주소
        // $recipient_address = $row['od_delivery_text'];
        $recipient_address = $row['od_delivery_place'] ? $row['od_delivery_place'] : $recipient_address;
    }

    $str	.= "<tr>";

    $str	.= "<td align='center'>".$index."</td>";
    $str	.= "<td align='center'>".$row['od_ex_date']."</td>";
    $str	.= "<td align='center'>".$order_name."</td>";
    $str	.= "<td align='center'>".$recipient_address."</td>";
    $str	.= "<td align='center'>".$goods_name."</td>";
    $str	.= "<td align='center'>".$row['od_b_name']."</td>";

    $str	.= "<td align='center'>".$row['od_b_hp']."</td>";
    $str	.= "<td align='center'>".$row['od_delivery_qty']."</td>";
    $str	.= "<td align='center'>".($row['od_delivery_price'])."</td>";
    $str	.= "<td align='center'".$color_1.">".$text_1."</td>";
    $str	.= "<td align='center'".$color_2.">".$text_2."</td>";
    $str	.= "<td align='center'".$color_3.">".$text_3."</td>";

    $str	.= "<td align='center'".$color_4.">".$text_4."</td>";	

    $str	.= "</tr>";

    $index++;
}


$str .= "</table>";

echo $str;

?>