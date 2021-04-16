<?php
// $sub_menu = '400400';
include_once('./_common.php');
//랜덤값 생성
function GenerateString($length)  
{  
    $characters  = "0123456789";  
    $characters .= "abcdefghijklmnopqrstuvwxyz";  
    $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";  
    $characters .= "_";  
    $string_generated = "";  
    $nmr_loops = $length;  
    while ($nmr_loops--)  
    {  
        $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];  
    }  
    return $string_generated;  
}  

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

$it_ids = $_POST['it_id'];
//관리자가 등록한 코드
$ct_admin_new=[];
for($i=0; $i<count($it_ids); $i++) {
    $it_id = $it_ids[$i];

    if ( $w ) {
        if (!$uid) alert("잘못된 접근입니다(1).");
        // $sql = "DELETE FROM {$g5['g5_shop_cart_table']} WHERE od_id = '$od_id' AND it_id = '$it_id'";

        #수정시 재고주문 stoId 컨트롤 
        $sql_d = "SELECT `ct_id`, `stoId` FROM `g5_shop_cart` WHERE `od_id` = '$od_id' AND `ct_uid` = '$uid'";
        $result_d = sql_query($sql_d);
        for($k=0; $row_k=sql_fetch_array($result_d); $k++) {
            //배열 정리
            $arr_d = explode('|',$row_k['stoId']);
            $arr_d1=array_filter($arr_d);
            $arr_d2=implode(',',$arr_d1);


            //시스템재고 삭제
            $sendData  = [];
            $sendData_stoId['stoId']=$arr_d1;
            $oCurl = curl_init();
            curl_setopt($oCurl, CURLOPT_PORT, 9901);
            curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/deleteMulti");
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData_stoId, JSON_UNESCAPED_UNICODE));
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            $res = curl_exec($oCurl);
            curl_close($oCurl);


            //order 테이블에 stoId, barcodetotalcount 값 빼기
            sql_query("update `g5_shop_order` set `stoId` = replace(stoId, '$arr_d2', '') where `od_id` = '$od_id'");
        }
        // return false;
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
                    ( od_id, mb_id, it_id, it_name, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty, ct_status, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_send_cost, ct_direct, ct_select, ct_select_time, pt_it, pt_msg1, pt_msg2, pt_msg3, ct_history, ct_discount, ct_price_type, ct_uid, io_thezone, ct_admin_new )
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
        $ct_admin_new_v = GenerateString(15);
        array_push($ct_admin_new,$ct_admin_new_v);
        $sql .= $comma."( '$od_id', '{$od['mb_id']}', '{$it['it_id']}', '".addslashes($it['it_name'])."', '{$it['it_sc_type']}', '{$it['it_sc_method']}', '{$it['it_sc_price']}', '{$it['it_sc_minimum']}', '{$it['it_sc_qty']}', '작성', '{$it_price}', '$point', '0', '0', '$io_value', '$ct_qty', '{$it['it_notax']}', '$io_id', '$io_type', '$io_price', '".G5_TIME_YMDHIS."', '$remote_addr', '$ct_send_cost', '$sw_direct', '$ct_select', '$ct_select_time', '{$it['pt_it']}', '$pt_msg1', '$pt_msg2', '$pt_msg3', '', '$add_ct_discount', '$ct_price_type', '$uid', '$io_thezone','$ct_admin_new_v' )";
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













#재고주문 stoId 업로드

//업로드할 mb_id 와 mb_entid 구하기 (관리자가 작성하기 때문에 주문자 정보가 필요.)
$sql_m = "select `mb_id`, `mb_entId` from `g5_member` where `mb_id` = '".$od['mb_id']."'";
$result_m = sql_fetch($sql_m);

//admin이 신규 주문한 주문 select
$where_ct_admin_new = "";
$or = "";
foreach ($ct_admin_new as $key => $value) {
    if($where_ct_admin_new){$or ="or";}
    $where_ct_admin_new .= $or." `ct_admin_new` = '".$value."'";
}
$sql = " select MT.it_id,
                MT.ct_qty,
                MT.it_name,
                MT.io_id,
                MT.io_type,
                MT.ct_option,
                MT.ct_qty,
                MT.ct_id,
				( SELECT it_time FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_time,
				( SELECT prodSupYn FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodSupYn,
				( SELECT ProdPayCode FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodPayCode,
				( SELECT it_delivery_cnt FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_delivery_cnt,
				( SELECT it_delivery_price FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_delivery_price
           from {$g5['g5_shop_cart_table']} MT
          where od_id = '$od_id'
            and ct_select = '1'  and ($where_ct_admin_new)";
$result = sql_query($sql);
$productList = [];
$od_prodBarNum_total =0;

//통신하기 위한 정보 벼열에 담기.
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	# 상품목록
	for($ii = 0; $ii < $row["ct_qty"]; $ii++){
			$thisProductData = [];
			$thisProductData["prodId"] = $row["it_id"];
			$thisProductData["prodColor"] = explode(chr(30), $row["io_id"])[0];
			$thisProductData["prodSize"] = explode(chr(30), $row["io_id"])[1];
			$thisProductData["prodBarNum"] = "";
			$thisProductData["prodManuDate"] = date("Y-m-d");
			$thisProductData["stoMemo"] = $_POST["od_memo"];
			$thisProductData["ct_id"] = $row["ct_id"];
			array_push($productList, $thisProductData);
            $od_prodBarNum_total++;
	}
}



//통신하기 위한 배열준비
$stoIdList = [];
$sendData = [];
$sendData["usrId"] = $result_m["mb_id"];
$sendData["entId"] = $result_m["mb_entId"];
$prodsSendData = [];
$prodsData = [];
foreach($productList as $key => $value){
    $prodsData["prodId"] = $value["prodId"];
    $prodsData["prodColor"] = $value["prodColor"];
    $prodsData["prodSize"] = $value["prodSize"];
    $prodsData["prodManuDate"] = $value["prodManuDate"];
    $prodsData["prodBarNum"] = $value["prodBarNum"];
    $prodsData["stoMemo"] = $value["stoMemo"];
    $prodsData["ct_id"] = $value["ct_id"];
    array_push($prodsSendData, $prodsData);
}

//통신
$sendData["prods"] = $prodsSendData;
$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/insert");
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
$res = json_decode($res, true);
curl_close($oCurl);


//결과 값
if($res["errorYN"] == "N"){
    //성공시 ct_id에 업로드
    for($k=0; $k<count($res['data']);$k++){
        // ct_id에 업로드
        array_push($stoIdList, $res['data'][$k]["stoId"]);
        $sql_ct = "update `g5_shop_cart` set `stoId` = CONCAT(`stoId`,'".$res['data'][$k]["stoId"]."|') where `ct_id` ='".$res['data'][$k]["ct_id"]."'";
        sql_query($sql_ct);
    }
} else {
    //실패시 ct_id 삭제
    for($k=0; $k<count($res['data']);$k++){
        //실패하면 ct_id 삭제
        sql_query($sql_ct);
            sql_query("
            DELETE FROM `g5_shop_cart`
            WHERE `ct_id` = '".$res['data'][$k]["ct_id"]."'
            ");
    }
    alert($res["message"],G5_URL);
    return false;
}
//통신 성공시 order table 에 stoId 추가, total stoId 개수 갱신
$stoIdList = implode(",", $stoIdList);

$sql_q = "select `stoId` from `g5_shop_order` where `od_id` = '".$od_id."'";
$result_q=sql_fetch($sql_q);

//수정시 불필요한 , 정리
$result_q['stoId'] = explode(',',$result_q['stoId']);
$result_q['stoId']=array_filter($result_q['stoId']);
$result_q['stoId']=implode(',',$result_q['stoId']);
if($result_q['stoId']){
    $stoIdList=$result_q['stoId'].','.$stoIdList;
}

//정리된 stoId update
sql_query("
UPDATE `g5_shop_order` SET
    `stoId` = '".$stoIdList."'
WHERE od_id = '{$od_id}'
");
//들어있는 바코드수 구하기
$sto_imsi="";
$sql_ct = " select `stoId` from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
$result_ct = sql_query($sql_ct);
while($row_ct = sql_fetch_array($result_ct)) {
    $sto_imsi .=$row_ct['stoId'];
}

$stoIdDataList = explode('|',$sto_imsi);
$stoIdDataList=array_filter($stoIdDataList);
$stoIdData = implode("|", $stoIdDataList);

$count_b=0;
$sendData["stoId"] = $stoIdData;
$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/pro/pro2000/pro2000/selectPro2000ProdInfoAjaxByShop.do");
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
curl_close($oCurl);
$result_again = json_decode($res, true);
$result_again =$result_again['data'];
for($k=0; $k < count($result_again); $k++){
    if($result_again[$k]['prodBarNum']){
        $count_b ++;
    }
}
//바코드 od_prodBarNum_insert 조정
$sql = "update `g5_shop_order` set `od_prodBarNum_insert` = ".$count_b." where `od_id` = '".$od_id."'";
sql_query($sql);

//order total 수 조정
$sql = "update `g5_shop_order` set `od_prodBarNum_total` = ".count($result_again)." where `od_id` = '".$od_id."'";
sql_query($sql);
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