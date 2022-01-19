<?php
$sub_menu = "200830";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '사용자 통계분석';

$type = 'user';
if ($_GET['type']) {
    $type = $_GET['type'];
}
include_once('./user_statistics.sub.php');

$startTime = strtotime($fr_date);
$endTime = strtotime($to_date);

$results = [];
if ($type == 'user') {
    // 회원등급

    // 누적
    $sql = "SELECT
            (SELECT COUNT(*) FROM g5_member WHERE mb_type = 'default') as default_cnt,
            (SELECT COUNT(*) FROM g5_member WHERE mb_level = '4') as level4_cnt,
            (SELECT COUNT(*) FROM g5_member WHERE mb_temp = '1') as temp_cnt,
            (SELECT COUNT(*) FROM g5_member WHERE mb_type = 'normal') as normal_cnt,
            (SELECT COUNT(*) FROM g5_member WHERE mb_type = 'partner') as partner_cnt,
            (SELECT COUNT(*) FROM g5_member WHERE mb_level = '9') as level9_cnt
    ";
    $total_cnt = sql_fetch($sql);
    
    // 일자별
    $sql = "SELECT DATE(mb_datetime) as mb_date, COUNT(*) as cnt FROM g5_member WHERE mb_type = 'default' AND mb_datetime BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY mb_date;";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['mb_date']] = $row['cnt'];
    }
    $results['default'] = $arr;

    $sql = "SELECT DATE(mb_datetime) as mb_date, COUNT(*) as cnt FROM g5_member WHERE mb_level = '4' AND mb_datetime BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY mb_date;";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['mb_date']] = $row['cnt'];
    }
    $results['level4_cnt'] = $arr;
    
    $sql = "SELECT DATE(mb_datetime) as mb_date, COUNT(*) as cnt FROM g5_member WHERE mb_temp = '1' AND mb_datetime BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY mb_date;";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['mb_date']] = $row['cnt'];
    }
    $results['temp_cnt'] = $arr;
    
    $sql = "SELECT DATE(mb_datetime) as mb_date, COUNT(*) as cnt FROM g5_member WHERE mb_type = 'normal' AND mb_datetime BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY mb_date;";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['mb_date']] = $row['cnt'];
    }
    $results['normal_cnt'] = $arr;
    
    $sql = "SELECT DATE(mb_datetime) as mb_date, COUNT(*) as cnt FROM g5_member WHERE mb_type = 'partner' AND mb_datetime BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY mb_date;";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['mb_date']] = $row['cnt'];
    }
    $results['partner_cnt'] = $arr;
    
    $sql = "SELECT DATE(mb_datetime) as mb_date, COUNT(*) as cnt FROM g5_member WHERE mb_mb_leveltemp = '9' AND mb_datetime BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY mb_date;";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['mb_date']] = $row['cnt'];
    }
    $results['level9_cnt'] = $arr;

    $colspan = 7;
}
else if ($type == 'region') {
    //누적
    $sql = "SELECT COUNT(*) as cnt, SUBSTRING_INDEX(mb_giup_addr1, ' ', 1) as sido FROM g5_member WHERE mb_type = 'default' GROUP BY sido;";
    $result = sql_query($sql);
    $total_arr = [];
    while($row=sql_fetch_array($result)) {
        $total_arr[] = $row;
        
        //각 지역 일자별
        $sub_sql = "SELECT DATE(mb_datetime) as mb_date, COUNT(*) as cnt, SUBSTRING_INDEX(mb_giup_addr1, ' ', 1) as sido FROM g5_member WHERE mb_type = 'default' AND SUBSTRING_INDEX(mb_giup_addr1, ' ', 1) = '{$row['sido']}' AND mb_datetime BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY sido, mb_date;";
        $sub_result = sql_query($sub_sql);
        $arr = [];
        while($sub_row=sql_fetch_array($sub_result)) {
            $arr[$sub_row['mb_date']] = $sub_row['cnt'];
        }
        $results[$row['sido']] = $arr;
    }
    // var_dump($results);
    $colspan = count($total_arr) + 1;
}
else if ($type == 'amount') {
    //누적
    $sql = "SELECT SUM(ct_price * ct_qty) as amount FROM g5_shop_cart WHERE ct_status = '배송' OR ct_status = '완료'; ";
    $total_amount = sql_fetch($sql);

    //각 일자별
    $sql = "SELECT SUM(ct_price * ct_qty) as amount, DATE(ct_time) as ct_time FROM g5_shop_cart WHERE (ct_status = '배송' OR ct_status = '완료') AND ct_time BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY ct_time; ";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['ct_time']] = $row['amount'];
    }
    $results['amount'] = $arr;
    // var_dump($results);
    $colspan = 2;
}
else if ($type == 'proposal_c') {
    //누적
    $sql = "SELECT COUNT(*) as cnt FROM recipient_item_msg";
    $total_cnt = sql_fetch($sql);

    //각 일자별
    $sql = "SELECT COUNT(*) as cnt, DATE(ms_created_at) as ms_date FROM recipient_item_msg WHERE ms_created_at BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY ms_date; ";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
    }
    $results['proposal_c'] = $arr;
    // var_dump($results);
    $colspan = 2;
}
else if ($type == 'proposal_s') {
    //누적
    $sql = "SELECT COUNT(*) as cnt FROM recipient_item_msg_log";
    $total_cnt = sql_fetch($sql);

    //각 일자별
    $sql = "SELECT COUNT(*) as cnt, DATE(ml_sent_at) as ms_date FROM recipient_item_msg_log WHERE ml_sent_at BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY ms_date; ";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
    }
    $results['proposal_s'] = $arr;
    // var_dump($results);
    $colspan = 2;
}
else if ($type == 'contract_c') {
    //누적
    $sql = "SELECT COUNT(*) as cnt FROM eform_document_log WHERE dl_log like '%생성%'";
    $total_cnt = sql_fetch($sql);

    //각 일자별
    $sql = "SELECT COUNT(*) as cnt, DATE(dl_datetime) as ms_date FROM eform_document_log WHERE dl_log like '%생성%' AND dl_datetime BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY ms_date; ";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
    }
    $results['contract_c'] = $arr;
    // var_dump($results);
    $colspan = 2;
}
else if ($type == 'contract_s') {
    //누적
    $sql = "SELECT COUNT(*) as cnt FROM eform_document_log WHERE dl_log like '%서명%'";
    $total_cnt = sql_fetch($sql);

    //각 일자별
    $sql = "SELECT COUNT(*) as cnt, DATE(dl_datetime) as ms_date FROM eform_document_log WHERE dl_log like '%서명%' AND dl_datetime BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY ms_date; ";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
    }
    $results['contract_s'] = $arr;
    // var_dump($results);
    $colspan = 2;
}
else if ($type == 'order_c') {
    //누적
    $sql = "SELECT COUNT(*) as cnt FROM g5_shop_order";
    $total_cnt = sql_fetch($sql);

    //각 일자별
    $sql = "SELECT COUNT(*) as cnt, DATE(od_time) as ms_date FROM g5_shop_order WHERE od_time BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY ms_date; ";
    $result = sql_query($sql);
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
    }
    $results['order_c'] = $arr;
    // var_dump($results);
    $colspan = 2;
}
?>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" style="width:100px;">날짜</th>
        <?php if ($type == 'user') { ?>
            <th scope="col">일반사업소</th>
            <th scope="col">우수사업소</th>
            <th scope="col">임시회원</th>
            <th scope="col">개인회원</th>
            <th scope="col">파트너사</th>
            <th scope="col">직원(9)</th>
        <?php } else if ($type == 'region') { ?>
        <?php 
            foreach($total_arr as $row) { 
                $region = $row['sido']; ?>
                <th scope="col"><?=$region?></th>
            <?php } ?>
        <?php } else if ($type == 'amount') { ?>
            <th scope="col">매출액</th>
        <?php } else if ($type == 'proposal_c') { ?>
            <th scope="col">제안서 생성</th>
        <?php } else if ($type == 'proposal_s') { ?>
            <th scope="col">제안서 발송</th>
        <?php } else if ($type == 'contract_c') { ?>
            <th scope="col">계약서 생성</th>
        <?php } else if ($type == 'contract_s') { ?>
            <th scope="col">계약서 서명</th>
        <?php } else if ($type == 'order_c') { ?>
            <th scope="col">주문서 생성 횟수</th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
    <tr class="bg0">
        <td>누적</td>
        <?php if ($type == 'user') { ?>
            <td><?php echo $total_cnt['default_cnt'] ?></td>
            <td><?php echo $total_cnt['level4_cnt'] ?></td>
            <td><?php echo $total_cnt['temp_cnt'] ?></td>
            <td><?php echo $total_cnt['normal_cnt'] ?></td>
            <td><?php echo $total_cnt['partner_cnt'] ?></td>
            <td><?php echo $total_cnt['level9_cnt'] ?></td>
        <?php } else if ($type == 'region') { ?>
        <?php 
            foreach($total_arr as $row) { ?>
                <td><?php echo $row['cnt'] ?></td>
            <?php } ?>
        <?php } else if ($type == 'amount') { ?>
            <td><?php echo number_format($total_amount['amount']) ?></td>
        <?php } else if ($type == 'proposal_c' || $type == 'proposal_s' || $type == 'contract_c' || $type == 'contract_s' || $type == 'order_c') { ?>
            <td><?php echo $total_cnt['cnt'] ?></td>
        <?php } ?>
    </tr>
    <?php
    // echo "1<br>";
    for ( $i = $startTime; $i <= $endTime; $i = $i + 86400 ) {
        $thisDate = date( 'Y-m-d', $i );
        $datas = [];
        array_push($datas, $thisDate);
        if ($type == 'user') {
            array_push($datas, $results['default'][$thisDate] ?: 0);
            array_push($datas, $results['level4_cnt'][$thisDate] ?: 0);
            array_push($datas, $results['temp_cnt'][$thisDate] ?: 0);
            array_push($datas, $results['normal_cnt'][$thisDate] ?: 0);
            array_push($datas, $results['partner_cnt'][$thisDate] ?: 0);
            array_push($datas, $results['level9_cnt'][$thisDate] ?: 0);
        } else if ($type == 'region') {
            $arr_keys = array_keys($results);
            foreach($arr_keys as $key) { 
                array_push($datas, $results[$key][$thisDate] ?: 0);
            }
        } else if ($type == 'amount') {
            $amount = number_format($results['amount'][$thisDate]);
            array_push($datas, $amount ?: 0);
        } else if ($type == 'proposal_c') {
            array_push($datas, $results['proposal_c'][$thisDate] ?: 0);
        } else if ($type == 'proposal_s') {
            array_push($datas, $results['proposal_s'][$thisDate] ?: 0);
        } else if ($type == 'contract_c') {
            array_push($datas, $results['contract_c'][$thisDate] ?: 0);
        } else if ($type == 'contract_s') {
            array_push($datas, $results['contract_s'][$thisDate] ?: 0);
        } else if ($type == 'order_c') {
            array_push($datas, $results['order_c'][$thisDate] ?: 0);
        }
    ?>
    <tr class="bg0">
        <?php foreach($datas as $data) { ?>
            <td><?php echo $data ?></td>
        <?php } ?>
    </tr>

    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없거나 관리자에 의해 삭제되었습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<?php
include_once('./admin.tail.php');
?>