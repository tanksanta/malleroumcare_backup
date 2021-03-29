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

$str = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"1\">";
$str .= "<tr bgcolor=\"#eeeeee\">
                <th>번호</th>
                <th>주문번호</th>
                <th>상품</th>
                <th>모델명</th>
                <th>옵션</th>
                <th>수량</th>
            </tr>";

$sql_search1 = "where 1=1";

if ($type == 1) {
    $sql_search1 = "where od_status = '주문'";
}

if ($type == 2) {
    $sql_search1 = "where (od_status = '출고준비' OR od_status = '배송' OR od_status = '완료')";
}

$sql_search2 = "";

if ($ret_od_id) {
    $od_ids = explode('|', $ret_od_id);
    
    $where = array();
    foreach ($od_ids as $od_id) {
        $where[] = " od_id = '{$od_id}' ";
    }
    $sql_search2 = ' and (' . implode(' OR ', $where) . ')';
}

$sql = "select *
            from {$g5['g5_shop_order_table']} a
            left join (select od_id as cart_od_id, it_name, it_id, io_id, ct_qty, io_type from {$g5['g5_shop_cart_table']}) b
            on a.od_id = b.cart_od_id
            left join (select it_model, it_id from {$g5['g5_shop_item_table']}) c
            on b.it_id = c.it_id 
            {$sql_search1} {$sql_search2}
            order by od_id asc, io_type asc, io_id asc";

$result = sql_query($sql);

$index = 1;

while ($row = sql_fetch_array($result)) {
    $str .= "<tr>";
    
    $str .= "<td align='center'>" . $index . "</td>"; // 번호
    $str .= "<td align='center' style='mso-number-format:\"\@\";'>" . $row['od_id'] . "</td>"; // 주문번호
    $str .= "<td align='center'>" . $row['it_name'] . "</td>"; // 상품
    // $str .= "<td align='center' style='mso-number-format:\"\@\";'> " . $row['it_id'] . "</td>"; // 상품번호
    $str .= "<td align='center' style='mso-number-format:\"\@\";'> " . $row['it_model'] . "</td>"; // 모델명
    $str .= "<td align='center'>" . ($row['io_id'] ? $row['io_id'] : "-") . "</td>"; // 옵션
    $str .= "<td align='center'>" . ($row['ct_qty'] ? $row['ct_qty'] : "-") . "</td>"; // 수량
    
    $str .= "</tr>";
    
    $index++;
}
$str .= "</table>";

echo $str;

?>