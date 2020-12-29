<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

set_time_limit(0);
ini_set('memory_limit', '10000M');

$today = date("YmdHis");

Header("Content-type: application/vnd.ms-excel");
Header("Content-type: charset=utf-8");
header("Content-Disposition: attachment; filename=bank_{$today}.xls");
Header("Content-Description: PHP3 Generated Data");
Header("Pragma: no-cache");
Header("Expires: 0");

echo "<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel;charset=utf-8\">";

$str	= "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"1\">";
$str	.= "<tr bgcolor=\"#eeeeee\">
                <th colspan=\"7\">기간별거래내역</th>
            </tr>";
$str	.= "<tr bgcolor=\"#eeeeee\">
                <th>일자</th>
                <th>은행명</th>
                <th>계좌번호</th>
                <th>거래내역</th>
                <th>입금내역</th>
                <th>출금내역</th>
                <th>메모</th>
            </tr>";
$str	.= "<tr bgcolor=\"#eeeeee\">
                <th>DATE</th>
                <th>BANK_NAME</th>
                <th>BANK_ADDRESS</th>
                <th>HISTORY</th>
                <th>PRICE_ADD</th>
                <th>PRICE_MINUS</th>
                <th>MEMO</th>
            </tr>";

$str	.= "<tr bgcolor=\"#eeeeee\">
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>";

$sql_common = " from tb_log_bank ";
if ($bankcode) {
    $sql_search = " where bankcode like '$bankcode' ";
}
if ($stx) {
    if ($sql_search) {
        $sql_search .= " AND ";
    }else{
        $sql_search .= " WHERE ";
    }

    $sql_search .= " name LIKE '%{$stx}%' ";
}

if ($fr_date && $to_date) {
    $where[] = " paydt between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
}

if ( $price_s && $price_e ) {
    $where[] = " price BETWEEN '{$price_s}' AND '{$price_e}' ";
}

if ($where) {
    if ($sql_search) {
        $sql_search .= " AND ";
    }else{
        $sql_search .= " WHERE ";
    }

    $sql_search .= implode(' and ', $where);
}

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            order by no desc
            ";
// limit {$from_record}, {$rows} 
$result = sql_query($sql);

$total_price = 0;

for ($i=0; $row=sql_fetch_array($result); $i++) {

    if ($is_admin == 'super')
        $ip = $row['vi_ip'];
    else
        $ip = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", G5_IP_DISPLAY, $row['vi_ip']);

    $bg = 'bg'.($i%2);

    $bank = get_albank_bank_step($row['bankcode']);

    // $row['account'] = '&nbsp;' . $row['account'];

    $row['paydate'] = substr(str_replace('-', '', $row['paydt']), 0, 8);

    $row['history'] =  $row['name'] . ' ' . ( $row['price'] > 0 ? '입금' : '출금' );

    // $row['add_price'] = number_format($row['price']);
    $row['add_price'] = $row['price'];

    $row['memo'] = htmlspecialchars($row['memo']);

    $total_price += $row['price'];

    $str	.= "<tr bgcolor=\"#eeeeee\">
                    <td>{$row['paydate']}</td>
                    <td>{$bank['name']}</td>
                    <td style=\"mso-number-format:'\@'\">{$row['account']}</td>
                    <td>{$row['history']}</td>
                    <td>{$row['add_price']}</td>
                    <td></td>
                    <td>{$row['memo']}</td>
                </tr>";

}

// $str	.= "<tr bgcolor=\"#000000\">
//                 <td></td>
//                 <td></td>
//                 <td></td>
//                 <td></td>
//                 <td></td>
//                 <td></td>
//                 <td></td>
//             </tr>";

$total_price = number_format($total_price);

$str	.= "<tr bgcolor=\"#bfbfbf\">
                <td>기간별 거래금액 누계</td>
                <td>{$total_price}원</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>";

$str .= "</table>";

echo $str;
?>