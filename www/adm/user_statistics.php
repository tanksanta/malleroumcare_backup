<?php
$sub_menu = "200830";
include_once('./_common.php');
ini_set("display_errors", 0);
auth_check($auth[$sub_menu], 'r');

$g5['title'] = '사용자 통계분석';

$type = 'user';
if ($_GET['type']) {
    $type = $_GET['type'];
}
include_once('./user_statistics.sub.php');

$startTime = strtotime($fr_date);
$endTime = strtotime($to_date);

$to_date = "{$to_date} 23:59:59";

$page = $_GET['page']==null ?'' :$_GET['page'];

$all_cnt = 0;

$results = [];
if ($type == 'user') {
    // 회원등급

    // 누적
    $sql = "SELECT
            (SELECT COUNT(*) FROM g5_member WHERE mb_type = 'default' AND mb_temp = 0 AND mb_manager != '') as default_cnt,
            (SELECT COUNT(*) FROM g5_member WHERE mb_level = '4') as level4_cnt,
            (SELECT COUNT(*) FROM g5_member WHERE mb_temp = '1') as temp_cnt,
            (SELECT COUNT(*) FROM g5_member WHERE mb_type = 'normal') as normal_cnt,
            (SELECT COUNT(*) FROM g5_member WHERE mb_type = 'partner') as partner_cnt,
            (SELECT COUNT(*) FROM g5_member WHERE mb_level = '9') as level9_cnt
    ";
    $total_cnt = sql_fetch($sql);
    
    // 일자별
    $sql = "SELECT DATE(mb_datetime) as mb_date, COUNT(*) as cnt FROM g5_member WHERE mb_type = 'default' AND mb_temp = 0 AND mb_manager != '' AND mb_datetime BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY mb_date;";
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
    $sql = "SELECT SUM(ct_price * ct_qty) as amount, DATE(ct_time) as ct_date FROM g5_shop_cart WHERE (ct_status = '배송' OR ct_status = '완료') AND ct_time BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY ct_date; ";
    $result = sql_query($sql);
    $arr = [];
    $sum = 0;
    while($row=sql_fetch_array($result)) {
        $arr[$row['ct_date']] = $row['amount'];
        $sum += $row['amount'];
    }
    $sum = number_format($sum);
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
    $sum = 0;
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
        $sum += $row['cnt'];
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
    $sum = 0;
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
        $sum += $row['cnt'];
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
    $sum = 0;
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
        $sum += $row['cnt'];
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
    $sum = 0;
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
        $sum += $row['cnt'];
    }
    $results['contract_s'] = $arr;
    // var_dump($results);
    $colspan = 2;
}
else if ($type == 'login_daily') {
    //누적
    $sql = "SELECT COUNT(*) as cnt FROM g5_statistics WHERE type = 'LOGIN';";
    $total_cnt = sql_fetch($sql);

    //각 일자별
    //사업소
    $sql = "SELECT COUNT(*) as cnt, DATE(regdt) as ms_date FROM g5_statistics WHERE type = 'LOGIN' AND regdt BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY ms_date; ";
    $result = sql_query($sql);
    $arr = [];
    $sum = 0;
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
        $sum += $row['cnt'];
    }
    $results['login_daily'] = $arr;
    // var_dump($results);
    $colspan = 2;
}
else if ($type == 'login_user') {
    //누적
    $sql = "SELECT COUNT(*) as cnt FROM g5_statistics WHERE type = 'LOGIN';";
    $total_cnt = sql_fetch($sql);
    
    //각 사업소별
    $sql = "SELECT COUNT(S.id) as cnt, S.mb_id, M.mb_name FROM g5_statistics as S LEFT JOIN g5_member as M ON M.mb_id = S.mb_id  WHERE ((M.mb_type = 'default' AND M.mb_temp = 0 AND M.mb_manager != '') OR M.mb_level = '4') AND S.type = 'LOGIN' AND S.regdt BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY S.mb_id ORDER BY m.mb_name ASC;";
    $sub_result = sql_query($sql);
    $arr = [];
    $sum = 0;
    while($row=sql_fetch_array($sub_result)) {
        $arr['mb_id'] = $row['mb_id'];
        $arr['name'] = $row['mb_name'];
        $arr['cnt'] = $row['cnt'];
        $sum += $row['cnt'];
        $results['login_user'][] = $arr;
    }
    // var_dump($results);
    $colspan = 2;
}
else if ($type == 'order_c') {
    //누적
    $sql = "SELECT COUNT(*) as cnt FROM g5_shop_order";
    $total_cnt = sql_fetch($sql);

    //각 일자별
    //사업소
    $sql = "SELECT COUNT(*) as cnt, DATE(regdt) as ms_date FROM g5_statistics WHERE type = 'ORDER' AND regdt BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY ms_date; ";
    $result = sql_query($sql);
    $arr = [];
    $sum_user = 0;
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
        $sum_user += $row['cnt'];
    }
    $results['order_c_user'] = $arr;

    //전체주문
    $sql = "SELECT COUNT(*) as cnt, DATE(od_time) as ms_date FROM g5_shop_order WHERE od_time BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY ms_date; ";
    $result = sql_query($sql);
    $arr = [];
    $admin_arr = [];
    $sum_all = 0;
    while($row=sql_fetch_array($result)) {
        $arr[$row['ms_date']] = $row['cnt'];
        $sum_all += $row['cnt'];
        $admin_arr[$row['ms_date']] = $row['cnt'] - ($results['order_c_user'][$row['ms_date']] ?: 0);
    }
    $sum_admin = $sum_all - $sum_user;
    $results['order_c_all'] = $arr;
    $results['order_c_admin'] = $admin_arr;

    // var_dump($results);
    $colspan = 4;
}
else if ($type == 'order_user') {
    //누적
    $sql = "SELECT COUNT(*) as cnt FROM g5_shop_order";
    $total_cnt = sql_fetch($sql);

    //각 일자별
    //사업소
    $sql = "SELECT mb_id, DATE(regdt) as ms_date FROM g5_statistics WHERE type = 'ORDER' AND regdt BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY mb_id; ";
    $result = sql_query($sql);
    $sum_user = 0;
    while($row = sql_fetch_array($result)) {
        $mb_id = $row['mb_id'];
        $arr = [];
        $sql = "SELECT COUNT(*) as cnt, DATE(regdt) as ms_date FROM g5_statistics WHERE type = 'ORDER' AND mb_id = '{$mb_id}' AND regdt BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY ms_date; ";
        $result2 = sql_query($sql);
        while($row2 = sql_fetch_array($result2)) {
            $arr[$row2['ms_date']] = $row2['cnt'];
            $results[$mb_id] = $arr;
            $sum_user += $row['cnt'];    
        }
    }
    $colspan = count($results) + 1;
}
else if ($type == 'inquire_data') {
    if ($_GET['page'] == 'all') {
        // 전체 사업소 집계
        $sql = "select ent_id, ent_nm, count(*) as cnt,COUNT(CASE WHEN resultMsg = 'success' THEN 1 END) AS s_cnt,COUNT(CASE WHEN resultMsg = 'fail' THEN 1 END) AS f_cnt from rep_inquiry_log";
        $where_sql = ' WHERE DATE(occur_date) BETWEEN "'.date("Y-m-d", $startTime).'" AND "'.date("Y-m-d", $endTime).'"';
        $group_order_sql = ' group by ent_id order by ent_nm;';
        $result = sql_query($sql.$where_sql.$group_order_sql);
        $list_head = ['No', '사업소명','사업소ID', '조회 횟수','성공 횟수','실패 횟수','비고'];
		
		$sql_detail = "select ent_id, ent_nm, pen_nm,pen_id, occur_date,resultMsg,err_msg from rep_inquiry_log";
        $where_sql .= " and resultMsg='fail'";
		$result_detail = sql_query($sql_detail.$where_sql);
        $arr_detail = [];
        while($row_detail = sql_fetch_array($result_detail)) {
            $arr_detail[$row_detail["ent_id"]][] =  $row_detail;   
        }
    } else if ($_GET['page'] == 'ent') {
        // 각 사업소 집계
        $sql = 'select ent_id, ent_nm, pen_id, pen_nm, count(*) as cnt from rep_inquiry_log';
        $where_sql = ' WHERE DATE(occur_date) BETWEEN "'.date("Y-m-d", $startTime).'" AND "'.date("Y-m-d", $endTime).'"';
        $group_order_sql = ' group by pen_id order by ent_nm, pen_nm;';
        $result = sql_query($sql.$where_sql.$group_order_sql);
        $list_head = ['No', '사업소명','사업소ID', '수급자 id', '수급자명', '조회 횟수'];
    } else {
        // 일자별 집계        
        // $sql = 'select ent_id, ent_nm, pen_id, pen_nm, occur_date, count(*) as cnt from rep_inquiry_log';
        $sql = "select ent_id, ent_nm, occur_date, count(*) as cnt,COUNT(CASE WHEN resultMsg = 'success' THEN 1 END) AS s_cnt,COUNT(CASE WHEN resultMsg = 'fail' THEN 1 END) AS f_cnt,err_msg,pen_nm,pen_id from rep_inquiry_log";
        $where_sql = ' WHERE DATE(occur_date) BETWEEN "'.date("Y-m-d", $startTime).'" AND "'.date("Y-m-d", $endTime).'"';
        // $group_order_sql = ' group by occur_date, pen_id order by occur_date, ent_nm;';
        $group_order_sql = ' group by occur_date, ent_nm order by occur_date, ent_nm;';
        $result = sql_query($sql.$where_sql.$group_order_sql);
        // $list_head = ['No', '조회 일자', '사업소명', '수급자명', '조회 횟수'];
        $list_head = ['No', '조회 일자', '사업소명','사업소ID', '조회 횟수','성공 횟수','실패 횟수','비고'];
        
        $sql_detail = 'select ent_id, ent_nm, pen_nm,pen_id, occur_date,resultMsg,err_msg from rep_inquiry_log';
        $result_detail = sql_query($sql_detail.$where_sql);
        $arr_detail = [];
        while($row_detail = sql_fetch_array($result_detail)) {
            $arr_detail[] =  $row_detail;   
        }
        // echo "<script>console.log('".json_encode($arr_detail)."');</script>";
    }
    $arr_inquiry = [];
    while($row_inquiry = sql_fetch_array($result)) {
        $arr_inquiry[] =  $row_inquiry;   
    }
}
else if ($type == 'recipient') {
    // 시작 --> 
    // 22.11.15 : 서원 - 등록한수급자 메뉴의 통계기능이 빠져있어 해당 기능 추가.

    //누적
    $sql = "SELECT COUNT(*) as cnt FROM recipient_grade_log";
    $total_cnt = sql_fetch($sql);
    
    // 일별 데이터 조회
    $sql = "SELECT COUNT(*) as cnt, DATE(created_at) as _date FROM recipient_grade_log WHERE created_at BETWEEN '{$fr_date}' AND '{$to_date}' GROUP BY _date; ";
    $result = sql_query($sql);
 
    $arr = [];
    while($row=sql_fetch_array($result)) {
        $arr[$row['_date']] = $row['cnt'];
    }
    
    $results['recipient'] = $arr;

    // 종료 -->
}



?>

<style>
.statistics_table {
  table-layout: fixed; 
  width: 100%;
  *margin-left: -100px; /*ie7*/
}
.statistics_table td, th {
  vertical-align: top;
  border-top: 1px solid #ccc;
  padding: 10px;
  width: 50px;
}
.fix {
  position: absolute;
  *position: relative; /*ie7*/
  margin-left: -100px;
  width: 100px;
}
.outer {
  position: relative;
}
.tbl_wrap {
  overflow-x: scroll;
  overflow-y: visible;
  width: 100%; 
}
.tbl_head01 thead th {
    border-color: #555;
    background: #383838;
    color: #fff;
    letter-spacing:0;
}

#stat td {
    background: #f5f5f5;
}
</style>

<div class="outer">
<div class="tbl_head01 tbl_wrap">
    <input type="hidden" id="type" value="<?php echo $type ?>"/>
    <input type="hidden" id="fr_date" value="<?php echo $fr_date ?>"/>
    <input type="hidden" id="to_date" value="<?php echo $to_date ?>"/>
    <!-- <caption><?php echo $g5['title']; ?> 목록</caption> -->
    <table class="statistics_table">
    <?php if ($type == 'inquire_data') {
			if($_GET['page'] == 'all'){?>
				<colgroup>
				  <col width="5%"/>
				  <col width="15%"/>
				  <col width="10%"/>
				  <col width="8%"/>
				  <col width="8%"/>
				  <col width="8%"/>
				  <col width="46%"/>
				</colgroup>
			<?php }elseif($_GET['page'] != 'ent'){?>
				<colgroup>
				  <col width="5%"/>
				  <col width="8%"/>
				  <col width="18%"/>
				  <col width="9%"/>
				  <col width="8%"/>
				  <col width="8%"/>
				  <col width="8%"/>
				  <col width="36%"/>
				</colgroup>

			<?php }
        }?>
	<thead>
    <tr>
        <?php if ($type == 'inquire_data') {
			foreach($list_head as $name) {  ?>
                <th scope="col"><?=$name?></th>
            <?php }
        } else { ?>
            <th scope="col"></th>
            <?php if ($type == 'user') { ?>
                <th scope="col">일반사업소</th>
                <th scope="col">우수사업소</th>
                <th scope="col">임시회원</th>
                <th scope="col">개인회원</th>
                <th scope="col">파트너사</th>
                <th scope="col">직원(9)</th>
            <?php } else if ($type == 'region') { 
                foreach($total_arr as $row) { 
                    $region = $row['sido']; ?>
                    <th scope="col"><?=($region)?$region:"주소없음"?></th>
                <?php }
            } else if ($type == 'login_daily') { 
                $to_date_str = date('Y-m-d',$endTime); ?>
                <th scope="col"><?=$fr_date.'~'.$to_date_str?></th>
            <?php } else if ($type == 'login_user') { 
                $to_date_str = date('Y-m-d',$endTime); ?>
                <th scope="col"></th>
                <th scope="col"><?=$fr_date.'~'.$to_date_str?></th>    
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
            <?php } else if ($type == 'recipient') { ?>
                <th scope="col">등록한 수급자</th>
            <?php } else if ($type == 'order_c') { ?>
                <th scope="col">전체 생성</th>
                <th scope="col">관리자 생성</th>
                <th scope="col">사용자 생성</th>
            <?php } else if ($type == 'order_user') { 
                foreach(array_keys($results) as $name) {  ?>
                    <th scope="col"><?=$name?></th>
                <?php } 
            } 
        } ?>


    </tr>
    </thead>
    <tbody id = "table_static">
    <?php if ($type != 'inquire_data' ) { ?>
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
            <?php } else if ($type == 'login_daily' || $type == 'login_user' || $type == 'proposal_c' || $type == 'proposal_s' || $type == 'contract_c' || $type == 'contract_s' || $type == 'order_c' || $type == 'order_user') { ?>
                <td><?php echo $total_cnt['cnt'] ?></td>
            <?php } else if ($type == 'recipient') { ?>
                <td><?php echo $total_cnt['cnt'] ?></td>
            <?php } ?>
        </tr>
    <?php } else { 
        $st_date = $arr_inquiry[0]['occur_date'] != null ?explode(' ', $arr_inquiry[0]['occur_date'])[0] :''; // 날짜 바뀌는 것을 체크
        $cnt_date = 0;
        $cnt_date_detail = 0;
		$s_cnt_date_detail = 0;
		$f_cnt_date_detail = 0;
        // echo "<script>console.log('".$st_date."');</script>";
        for ($ind = 0; $ind < count($arr_inquiry); $ind++) {
            $all_cnt = $all_cnt + $arr_inquiry[$ind]['cnt'];
			$all_s_cnt = $all_s_cnt + $arr_inquiry[$ind]['s_cnt'];
			$all_f_cnt = $all_f_cnt + $arr_inquiry[$ind]['f_cnt'];
			$pen_name = mb_substr($arr_detail[$arr_inquiry[$ind]['ent_id']][0]['pen_nm'],0,1)."*".mb_substr($arr_detail[$arr_inquiry[$ind]['ent_id']][0]['pen_nm'],2,1) ;
			$pen_id = substr($arr_detail[$arr_inquiry[$ind]['ent_id']][0]['pen_id'],0,2)."********";
			
			?>
            
			
			<?php if ($_GET['page'] == 'all') { ?>                
				<tr class="bg0">
                <td><?=$ind+1;?></td>
                <td><?=$arr_inquiry[$ind]['ent_nm'];?></td>
				<td><?=$arr_inquiry[$ind]['ent_id'];?></td>
                <td><?=$arr_inquiry[$ind]['cnt'];?></td>
				<td><?=$arr_inquiry[$ind]['s_cnt'];?></td>
				<td><?=$arr_inquiry[$ind]['f_cnt'];?></td>
				<td><?php if($arr_inquiry[$ind]['f_cnt'] > 0){echo "[".$arr_detail[$arr_inquiry[$ind]['ent_id']][0]['occur_date']."] | ".$pen_name."(".$pen_id.") | ".$arr_detail[$arr_inquiry[$ind]['ent_id']][0]['err_msg'];}?><?php if($arr_inquiry[$ind]['f_cnt'] > 1 && count($arr_detail[$arr_inquiry[$ind]['ent_id']]) > 0){?><input type="button" value="  외 <?=(count($arr_detail[$arr_inquiry[$ind]['ent_id']])-1)?>건 더보기  " data-prod-log-detail="<?=$ind+1?>" class="detail-toggler btn_submit" style="float:right;"><?php }?></td>
                </tr>
				<?php if($arr_inquiry[$ind]['f_cnt'] > 0){?>

				<?php }
			for($idx = 1; $idx < count($arr_detail[$arr_inquiry[$ind]['ent_id']]); $idx++) { 
                    $pen_name2 = mb_substr($arr_detail[$arr_inquiry[$ind]['ent_id']][$idx]['pen_nm'],0,1)."*".mb_substr($arr_detail[$arr_inquiry[$ind]['ent_id']][$idx]['pen_nm'],2,1) ;
					$pen_id2 = substr($arr_detail[$arr_inquiry[$ind]['ent_id']][$idx]['pen_id'],0,2)."********";
					//if($arr_detail[$idx]['ent_nm'] != $arr_inquiry[$ind]['ent_nm'] || $arr_detail[$idx]['occur_date'] != $arr_inquiry[$ind]['occur_date']) continue;?>
                    <tr  bgcolor="#f1f1f1" style="display:none;" id="detail<?=$ind+1?>" class="log-detail<?=$ind+1?>">
                    <td></td>
					<td></td>
                    <td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><?="[".$arr_detail[$arr_inquiry[$ind]['ent_id']][$idx]['occur_date']."] | ".$pen_name2."(".$pen_id2.") | ".$arr_detail[$arr_inquiry[$ind]['ent_id']][$idx]['err_msg'];?></td>
                    </tr>
            <?php }
				 } else if ($_GET['page'] == 'ent') { ?>
                <tr class="bg0">
                <td><?=$ind+1;?></td>
                <td><?=$arr_inquiry[$ind]['ent_nm'];?></td>
				<td><?=$arr_inquiry[$ind]['ent_id'];?></td>
                <td><?=$arr_inquiry[$ind]['pen_id'];?></td>
                <td><?=$arr_inquiry[$ind]['pen_nm'];?></td>
                <td><?=$arr_inquiry[$ind]['cnt'];?></td>
				<td><?=$arr_inquiry[$ind]['s_cnt'];?></td>
				<td><?=$arr_inquiry[$ind]['f_cnt'];?></td>
                </tr>
            <?php } else { 
                $cnt_date++;
                $cnt_date_detail = $cnt_date_detail + $arr_inquiry[$ind]['cnt'];
				$s_cnt_date_detail = $s_cnt_date_detail + $arr_inquiry[$ind]['s_cnt'];
				$f_cnt_date_detail = $f_cnt_date_detail + $arr_inquiry[$ind]['f_cnt'];
				$pen_name3 = mb_substr($arr_inquiry[$ind]['pen_nm'],0,1)."*".mb_substr($arr_inquiry[$ind]['pen_nm'],2,1) ;
				$pen_id3 = substr($arr_inquiry[$ind]['pen_id'],0,2)."********";
				?>
                <tr class="bg0 detail-toggler" id="detail<?=$ind+1?>" data-prod-log-detail="<?=$ind+1?>" style="cursor:pointer;<?php if($arr_inquiry[$ind]['f_cnt'] > 0){?>background:#f4eeee;<?php }?>">
                <td><?=$ind+1;?></td>
                <td><?=explode(' ', $arr_inquiry[$ind]['occur_date'])[0];?></td>
                <td><?=$arr_inquiry[$ind]['ent_nm'];?></td>
				<td><?=$arr_inquiry[$ind]['ent_id'];?></td>
                <td><?=$arr_inquiry[$ind]['cnt'];?></td>
				<td><?=$arr_inquiry[$ind]['s_cnt'];?></td>
				<td><?=$arr_inquiry[$ind]['f_cnt'];?></td>
				<td><?php if($arr_inquiry[$ind]['f_cnt']>0){ echo "[".$arr_inquiry[$ind]['occur_date']."] | ".$pen_name3."(".$pen_id3.") | ".$arr_inquiry[$ind]['err_msg'];}?></td>
                </tr>
                <tr id="detail<?=$ind+1?>" class="log-detail<?=$ind+1?>"  style="display:none;"  bgcolor="#e9e9e9">
                <td></td>
				<td>조회 시간</td>
                <td>사업소명</td>
				<td>수급자명</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
                </tr>
                <?php for($idx = 0; $idx < count($arr_detail); $idx++) { 
                    $pen_name4 = mb_substr($arr_detail[$idx]['pen_nm'],0,1)."*".mb_substr($arr_detail[$idx]['pen_nm'],2,1) ;
					$pen_id4 = substr($arr_detail[$idx]['pen_id'],0,2)."********";
					if($arr_detail[$idx]['ent_nm'] != $arr_inquiry[$ind]['ent_nm'] || $arr_detail[$idx]['occur_date'] != $arr_inquiry[$ind]['occur_date']) continue;?>
                    <tr id="detail<?=$ind+1?>" class="log-detail<?=$ind+1?>"  style="display:none;"  bgcolor="#f1f1f1">
                    <td></td>
					<td><?=$arr_detail[$idx]['occur_date'];?></td>
                    <td><?=$arr_detail[$idx]['ent_nm'];?></td>
                    <td><?=$pen_name4;?>(<?=$pen_id4;?>)</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
                    </tr>
            <?php }
                // (일자별로 모았을때 해당일의 마지막 아이템)이거나 (리스트 전체의 가장 마지막 아이템)이면 집계한 값을 출력한다.
                if(($ind != count($arr_inquiry)-1 && $st_date!=explode(' ', $arr_inquiry[$ind+1]['occur_date'])[0])||$ind == count($arr_inquiry)-1){ ?>
                    <tr class="bg0" id="stat">
                        <td></td>
                        <td><?=explode(' ', $arr_inquiry[$ind]['occur_date'])[0];?></td>
                        <td><?=$cnt_date;?>개 사업소</td>
						<td></td>
                        <td><?=$cnt_date_detail;?></td>
						<td><?=$s_cnt_date_detail;?></td>
						<td><?=$f_cnt_date_detail;?></td>
						<td></td>
                    </tr>
                <?php $f_cnt_date_detail = $s_cnt_date_detail = $cnt_date = $cnt_date_detail = 0; $st_date = $ind+1 != count($arr_inquiry) ?explode(' ', $arr_inquiry[$ind+1]['occur_date'])[0] :'';}
            } ?>
        <?php } 
        if($page == '') {?>
            <tr class="bg0" id="stat">
                <td colspan="4">누적</td>
                <td><?=$all_cnt;?></td>
				<?php if($type == 'inquire_data'){?>
				<td><?=$all_s_cnt;?></td>
				<td><?=$all_f_cnt;?></td>
				<td></td>
				<?php }?>
            </tr>
        <?php } else if ($page == 'all'){?>
            <tr class="bg0" id="stat">
                <td>누계</td>
                <td><?=count($arr_inquiry);?>개 사업소</td>
				<td></td>
                <td><?=$all_cnt;?></td>
				<?php if($type == 'inquire_data'){?>
				<td><?=$all_s_cnt;?></td>
				<td><?=$all_f_cnt;?></td>
				<td></td>
				<?php }?>
            </tr>
        <?php }  }
    if ($type != 'login_user') {
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
            } else if ($type == 'login_daily') {
                array_push($datas, $results['login_daily'][$thisDate] ?: 0);
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
            } else if ($type == 'recipient') {
                array_push($datas, $results['recipient'][$thisDate] ?: 0);
            } else if ($type == 'order_c') {
                array_push($datas, $results['order_c_all'][$thisDate] ?: 0);
                array_push($datas, $results['order_c_admin'][$thisDate] ?: 0);
                array_push($datas, $results['order_c_user'][$thisDate] ?: 0);
            } else if ($type == 'order_user') {
                $arr_keys = array_keys($results);
                foreach($arr_keys as $key) { 
                    array_push($datas, $results[$key][$thisDate] ?: 0);
                }
            } 
        if($type != 'inquire_data') {?>
        <tr class="bg0">
            <?php foreach($datas as $data) { ?>
                <td><?php echo $data ?></td>
            <?php } ?>
        </tr>
    <?php }
        }
    } else { ?>        
        <?php foreach($results['login_user'] as $data) { ?>
            <tr class="bg0">
            <td><?php echo $data['mb_id'] ?></td>
            <td><?php echo $data['name'] ?></td>
                <td><?php echo $data['cnt'] ?></td>
            </tr>
        <?php } ?>
        
    <?php
    }

    if ($type == 'amount' || $type == 'proposal_c' || $type == 'proposal_s' || $type == 'contract_c' || $type == 'contract_s' || $type == 'login_daily' || $type == 'login_user') {
        echo "<tr class='bg0'><td>소계</td><td>{$sum}</td></tr>";
    }
    else if ($type == 'order_c') {
        echo "<tr class='bg0'><td>소계</td><td>{$sum_all}</td><td>{$sum_admin}</td><td>{$sum_user}</td></tr>";
    }
    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없거나 관리자에 의해 삭제되었습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>
</div>

<script>
$(function() {
    // 요양정보조회 전체 사업소 카운트 클릭이벤트
    $('#table_static').on('click', '.detail-toggler', function(e){
        e.preventDefault();
        // console.log("click!s : ", e.target.parentElement.parentElement.id); // detail{n}
        // if ($('#'+e.target.parentElement.parentElement.id).hasClass('detail-open')) {
        //     $('#'+e.target.parentElement.parentElement.id).attr('style', 'border: none;');
        // } else {
        //     $('#'+e.target.parentElement.parentElement.id).attr('style', 'border: 2px solid #000;');
        // }
        $('#'+e.target.parentElement.parentElement.id).toggleClass("detail-open");
        $('.log-detail'+$(this).attr('data-prod-log-detail')).map((idx, child) => {
            if ($(child).hasClass('detail-open')) {
                $(child).toggleClass("detail-open");
                $(child).attr('style', 'border: none;display:none;');
            } else {
                $(child).toggleClass("detail-open");
                if (idx != $('.log-detail'+$(this).attr('data-prod-log-detail')).length-1) {
                    //$(child).attr('style', 'border: 2px solid #000;border-bottom:none;border-top:none;display:table-row;');
                    $(child).attr('style', 'display:table-row;');
                } else {
                    //$(child).attr('style', 'border: 2px solid #000;border-top:none;display:table-row;');
                    $(child).attr('style', 'display:table-row;');
                }
            }
        });
    });

    $('#download_excel').click(function(e) {
        var body = encodeURIComponent(document.getElementsByTagName('table')[0].innerHTML);
        body = body.replace(/\s+/g,"");
        var type = $('#type').val();
        // var url = 'user_statistics_excel_download.php?body=' + body;
        // window.location.href = url;

        var form = document.createElement('form');
        form.method = 'post';
        form.action = 'user_statistics_excel_download.php';
        form.target='_blank';

        var hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = 'table_body';
        hiddenField.value = body;
        form.appendChild(hiddenField);

        var hiddenField2 = document.createElement('input');
        hiddenField2.type = 'hidden';
        hiddenField2.name = 'type';
        hiddenField2.value = type;
        form.appendChild(hiddenField2);

        if(type == 'inquire_data'){
            var hiddenField3 = document.createElement('input');
            hiddenField3.type = 'hidden';
            hiddenField3.name = 'page';
            hiddenField3.value = '<?=$page?>';
            form.appendChild(hiddenField3);
            
            var hiddenField4 = document.createElement('input');
            hiddenField4.type = 'hidden';
            hiddenField4.name = 'todate';
            hiddenField4.value = '<?=date("Y-m-d", $startTime);?>';
            form.appendChild(hiddenField4);
        }

        document.body.appendChild(form);
        form.submit();
    });
});
</script>
<?php
include_once('./admin.tail.php');
?>
