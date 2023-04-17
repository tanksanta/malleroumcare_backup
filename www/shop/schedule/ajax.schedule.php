<?php
include_once('./_common.php');

if(!$member['mb_id'])
  json_response(400, '로그인이 필요합니다.');

if(!$_POST['od_id'])
  json_response(400, '유효하지않은 요청입니다.');

$post_data = array();
//$check_sum_duplicate = duplicate_partner_install_schedule($_POST['partner_manager_mb_id'], $_POST['delivery_date'], $_POST['delivery_datetime']);
//if (!$check_sum_duplicate) json_response(400, '설치파트너 매니저 일정을 확인해주세요.'); //설치일정 중복 허용 요청 2023.01.19 정한진

if($_POST['update_type'] == 'list_manager'){
    $sql = "select ct_id, ct_direct_delivery_date from g5_shop_cart where od_id = '{$_POST['od_id']}' and ct_is_direct_delivery = 2 and ct_status in ('출고준비', '배송', '완료')";
    $sql_result = sql_query($sql);

    $cnt = 0;
    for ($i=0; $row=sql_fetch_array($sql_result); $i++) {

        $delivery_date = explode(' ', $row['ct_direct_delivery_date']);
        $delivery_time = explode(':', $delivery_date[1]);


        $check_sum_duplicate = duplicate_partner_deny_schedule($_POST['partner_manager_mb_id'], $delivery_date[0]);
        if (!$check_sum_duplicate) json_response(400, "해당 날짜(".$delivery_date[0].")는 담당자의 설치불가일입니다.\n다른 날짜를 지정하거나 담당자를 변경해주세요.");

        $post_data[$row['ct_id']]['delivery_date'] = $delivery_date[0];
        $post_data[$row['ct_id']]['delivery_datetime'] = $delivery_time[0].":00";
        $post_data[$row['ct_id']]['partner_manager_mb_id'] = $_POST['partner_manager_mb_id'];
        $post_data[$row['ct_id']]['direct_delivery'] = '2';
        $cnt++;
    }

    if($cnt == 0) json_response(201, '설치 대상이 없습니다.');
} else {
    $ct_id = explode(',', $_POST['ct_id']);
    foreach($ct_id as $value){
        if($_POST['ct_is_direct_delivery_'.$value] != '2') continue;

        $check_sum_duplicate = duplicate_partner_deny_schedule($_POST['partner_manager_mb_id'], $_POST['ct_direct_delivery_date_'.$value]);
        if (!$check_sum_duplicate) json_response(400, "해당 날짜(".$_POST['ct_direct_delivery_date_'.$value].")는 담당자의 설치불가일입니다.\n다른 날짜를 지정하거나 담당자를 변경해주세요.");

        $post_data[$value]['delivery_date'] = $_POST['ct_direct_delivery_date_'.$value];
        $post_data[$value]['delivery_datetime'] = $_POST['ct_direct_delivery_time_'.$value].":00";
        $post_data[$value]['partner_manager_mb_id'] = $_POST['partner_manager_mb_id'];
        $post_data[$value]['direct_delivery'] = $_POST['ct_is_direct_delivery_'.$value];
    }
}

$check_sum = exit_partner_install_schedule($_POST['od_id']);
$code = 201;

if ($check_sum) {
    // 이미 생성 된 일정이 있는 경우
    foreach($post_data as $key => $value) {

        if(explode('_', $_POST['update_type'])[1] == 'manager'){ // 담당자 지정인 경우
            $res = update_partner_install_schedule_partner_by_ct_id($key, $value['partner_manager_mb_id']);
            if ($res) $code = 200;
        } else { // 출고예정일 변경인 경우
            $res = update_partner_install_schedule_delivery_date_and_delivery_datetime_by_ct_id($key, $value['delivery_date'], $value['delivery_datetime']);
            if ($res) $code = 200;
        }
    }
} else {
    // 생성 된 일정이 없는 경우
    $res = create_partner_install_schedule($_POST['od_id']);
    if (!$res) json_response(400, '에러');

    foreach($post_data as $key => $value) {
        if(explode('_', $_POST['update_type'])[1] == 'manager'){ // 담당자 지정인 경우
            $res = update_partner_install_schedule_partner_by_ct_id($key, $value['partner_manager_mb_id']);
            if ($res) $code = 200;
        } else { // 출고예정일 변경인 경우
            $res = update_partner_install_schedule_delivery_date_and_delivery_datetime_by_ct_id($key, $value['delivery_date'], $value['delivery_datetime']);
            if ($res) $code = 200;
        }
    }
}

if ($res) json_response($code, 'OK', $res);
else json_response(400, '유효하지않은 요청입니다.');
?>
