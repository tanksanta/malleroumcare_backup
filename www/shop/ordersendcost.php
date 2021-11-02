<?php
include_once('./_common.php');

$code = preg_replace('#[^0-9]#', '', $_POST['zipcode']);

if(!$code)
    die('0');

$sql = " select sc_id, sc_price
            from {$g5['g5_shop_sendcost_table']}
            where sc_zip1 <= $code
              and sc_zip2 >= $code ";
$row = sql_fetch($sql);

if(!$row['sc_id'])
    die('0');

/*

$it_sc_add_sendcost = 'it_sc_add_sendcost';
$it_sc_type = 'it_sc_type';
if ($member['mb_type'] == 'partner') {
    $it_sc_add_sendcost = 'it_sc_add_sendcost_partner';
    $it_sc_type = 'it_sc_type_partner';
}

$last_item_sc_price = 0;
$total_item_sc_price = 0;

if($it_ids) {
    foreach($it_ids as $it_id) {
        $sql = "SELECT * FROM {$g5['g5_shop_item_table']} WHERE it_id = {$it_id}";
        $result = sql_fetch($sql);

        $send_cost = $result[$it_sc_add_sendcost]

        // 수량별 부과일경우 
        if ($result[$it_sc_type] == 4) {
            $total_item_sc_price += $result[$it_sc_add_sendcost];
        }else{
            // 가장큰 금액 적용
            if ($last_item_sc_price < $result[$it_sc_add_sendcost]) {
                $last_item_sc_price = $result[$it_sc_add_sendcost];
            }
        }

    }
}

if ($last_item_sc_price) {
    die($last_item_sc_price + $total_item_sc_price);
}

die($row['sc_price'] + $total_item_sc_price);
*/

$total_item_sc_price = 0;

$it_sc_add_sendcost = 'it_sc_add_sendcost';
if ($member['mb_type'] == 'partner') {
    $it_sc_add_sendcost = 'it_sc_add_sendcost_partner';
}

if($it_ids) {
    foreach($it_ids as $it_id) {
        $sql = "SELECT * FROM {$g5['g5_shop_item_table']} WHERE it_id = {$it_id}";
        $result = sql_fetch($sql);

        if ($result[$it_sc_add_sendcost] > -1) { // 추가배송비가 설정되어 있는 경우
            $total_item_sc_price += $result[$it_sc_add_sendcost];
        } else { // 없는경우 기본 관리자에 있는걸 가져온다.
            $total_item_sc_price += $row['sc_price'];
        }
        
    }

}

if ($total_item_sc_price) {
    echo $total_item_sc_price;
    die();
}

die($row['sc_price']);
?>