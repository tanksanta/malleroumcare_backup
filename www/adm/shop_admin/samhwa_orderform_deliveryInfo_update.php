<?php

include_once("./_common.php");

$sub_menu = '400400';
$auth_check = auth_check($auth[$sub_menu], 'w', true);
if($auth_check) {
  json_response(400, "권한이 없습니다.");
}

// partner01 푸쉬키 가져오기
$partner01_tokens = array();
$fb_result = sql_query("SELECT * FROM g5_firebase WHERE mb_id = 'partner01'");
while($fb_row = sql_fetch_array($fb_result)) {
    array_push($partner01_tokens, $fb_row['fcm_token']);
}

// partner01 푸쉬 보내기
function send_push($tokens, $it_name, $ct_is_direct_delivery, $od_id) {
  $msg = '위탁내용 : ' . ($ct_is_direct_delivery == 1 ? '배송' : '설치');
  add_notification(
      $tokens,
      array(),
      $it_name . '주문 신규추가',
      $msg,
      G5_URL . '/shop/partner_orderinquiry_view.php?od_id=' . $od_id
  );
}

$ct_id_list = $_POST["ct_id"];
$od_delivery_insert = 0;


// 23.01.25 : 서원 - 트랜잭션 시작
sql_query("START TRANSACTION");

$_sql_string = [];

try {  

  $result = [];
  foreach($ct_id_list as $ct_id) {
    $ct_it_name = $_POST["ct_it_name_{$ct_id}"];
    $ct_delivery_company = $_POST["ct_delivery_company_{$ct_id}"];
    $ct_delivery_num = $_POST["ct_delivery_num_{$ct_id}"];
	$ct_delivery_box_type = $_POST["box_size_option_{$ct_id}"];
    if (is_array($ct_delivery_num)) {
      $n_ct_delivery_num = array();
      foreach($ct_delivery_num as $delivery_num) {
        if (!empty($delivery_num)) {
          array_push($n_ct_delivery_num, $delivery_num);
        }
      }
      $ct_delivery_num = implode('|', $n_ct_delivery_num);
    }
    $ct_delivery_cnt = $_POST["ct_delivery_cnt_{$ct_id}"];
    $ct_delivery_price = $_POST["ct_delivery_price_{$ct_id}"];
    $ct_delivery_combine = $_POST["ct_combine_{$ct_id}"];
    $ct_delivery_combine_ct_id = (int)$_POST["ct_combine_ct_id_{$ct_id}"];

    $ct_is_direct_delivery = (int)$_POST["ct_is_direct_delivery_{$ct_id}"] ?: 0;
    $ct_is_direct_delivery_sub = (int)$_POST["ct_is_direct_delivery_sub_{$ct_id}"] ?: 0;
    if($ct_is_direct_delivery && $ct_is_direct_delivery_sub) {
      $ct_is_direct_delivery = $ct_is_direct_delivery_sub;
      $ct_direct_delivery_partner = get_search_string($_POST["ct_direct_delivery_partner_{$ct_id}"]);
      $ct_direct_delivery_price = (int)$_POST["ct_direct_delivery_price_{$ct_id}"] ?: 0;
    } else {
      $ct_direct_delivery_partner = '';
      $ct_direct_delivery_price = 0;
    }
    //$ct_warehouse = get_search_string($_POST["ct_warehouse_{$ct_id}"]);
	$ct_warehouse = $_POST["ct_warehouse_{$ct_id}"];
    
    
    if($ct_delivery_num||$ct_delivery_combine){
      $od_delivery_insert++;
    }

    $ct = sql_fetch(" select * from g5_shop_cart where ct_id = '$ct_id' ");
    $was_combined = $ct['ct_combine_ct_id'] ? 1 : 0;
    $was_direct_delivery = $ct['ct_is_direct_delivery'] > 0 ? 1 : 0;

    if ($ct_delivery_combine) {
      $combine_where = "ct_combine_ct_id = '{$ct_delivery_combine_ct_id}',";
    } else {
      $combine_where = "ct_combine_ct_id = NULL,";
    }
    
    if($update_type == "popup") {
      $_sql_string[] = ("
        UPDATE g5_shop_cart SET
          $combine_where
          ct_delivery_company = '{$ct_delivery_company}',
          ct_delivery_num = '{$ct_delivery_num}',
          ct_edi_result = 0
        WHERE ct_id = '{$ct_id}'
      ");

      // 배송로그 작성을 위해 변수 할당
      $ct_delivery_combine = $was_combined;
      $ct_delivery_combine_ct_id = $ct['ct_combine_ct_id'];
      $ct_is_direct_delivery = $ct['ct_is_direct_delivery'];
      $ct_direct_delivery_partner = $ct['ct_direct_delivery_partner'];
      $ct_direct_delivery_price = $ct['ct_direct_delivery_price'];
    } else {
      $_sql_string[] = ("
        UPDATE g5_shop_cart SET
          $combine_where
          ct_delivery_company = '{$ct_delivery_company}',
          ct_delivery_num = '{$ct_delivery_num}',
          ct_delivery_cnt = '{$ct_delivery_cnt}',
          ct_delivery_price = '{$ct_delivery_price}',
		  ct_delivery_box_type = '{$ct_delivery_box_type}',
          ct_edi_result = 0,
          ct_warehouse = '{$ct_warehouse}',
          ct_is_direct_delivery = '{$ct_is_direct_delivery}',
          ct_direct_delivery_partner = '{$ct_direct_delivery_partner}',
          ct_direct_delivery_price = '{$ct_direct_delivery_price}'
        WHERE ct_id = '{$ct_id}'
      ");
	  sql_query("UPDATE g5_shop_cart SET
          $combine_where
          ct_delivery_company = '{$ct_delivery_company}',
          ct_delivery_num = '{$ct_delivery_num}',
          ct_delivery_cnt = '{$ct_delivery_cnt}',
          ct_delivery_price = '{$ct_delivery_price}',
		  ct_delivery_box_type = '{$ct_delivery_box_type}',
          ct_edi_result = 0,
          ct_warehouse = '{$ct_warehouse}',
          ct_is_direct_delivery = '{$ct_is_direct_delivery}',
          ct_direct_delivery_partner = '{$ct_direct_delivery_partner}',
          ct_direct_delivery_price = '{$ct_direct_delivery_price}'
        WHERE ct_id = '{$ct_id}'");
    }

    //배송 로그
    $od_id=$_POST["od_id"];
    $mb_id=$member["mb_id"];
    $data=date("Y-m-d H:i:s");
    if ($ct_delivery_combine) {
      $combine_where2 = "ct_combine_ct_id = '{$ct_delivery_combine_ct_id}',";
    } else {
      $combine_where2 = "ct_combine_ct_id = '',";
    }

    $set_warehouse = 0;
    $d_content = '';
    if($ct_warehouse) {
      $d_content = "출하창고: {$ct_warehouse}";
      $set_warehouse = 1;
    }

    $_sql_string[] = " insert into `g5_delivery_log`
    set od_id = '{$od_id}',
        ct_id = '{$ct_id}',
        mb_id = '{$mb_id}',
        d_content = '{$d_content}',
        $combine_where2
        ct_delivery_company = '{$ct_delivery_company}',
        ct_delivery_num = '{$ct_delivery_num}',
        ct_delivery_cnt = '{$ct_delivery_cnt}',
        ct_delivery_price = '{$ct_delivery_price}',
        ct_edi_result = '0',
        ct_is_direct_delivery = '{$ct_is_direct_delivery}',
        ct_direct_delivery_partner = '{$ct_direct_delivery_partner}',
        ct_direct_delivery_price = '{$ct_direct_delivery_price}',
        was_combined = '$was_combined',
        was_direct_delivery = '$was_direct_delivery',
        set_warehouse = '$set_warehouse',
        d_date = '{$data}'
    ";

    foreach($delivery_companys as $company){ 
        if($ct_delivery_company == $company["val"] ){
          $result_company2 = $company["name"];
          break;
        }
    }
	
	$result_status = '';
    $result_text = '배송정보';
    if($ct_is_direct_delivery) {
      $result_status = 'disable';
      $result_text = '입력완료(직배송)';
    }
    if($ct_delivery_combine) {
      
	  $sql_ctd ="select `ct_delivery_company`,`ct_delivery_num` from `g5_shop_cart` where `ct_id` = '".$ct_delivery_combine_ct_id."'";
      $result_ctd = sql_fetch($sql_ctd);

      foreach($delivery_companys as $data){ 
        if($result_ctd['ct_delivery_company'] == $data["val"] ){
            $result_company2=$data["name"];
        }
      }
	  
	  $result_status = 'disable';
      $result_text = '입력완료(합포)';
    }
    if($ct_delivery_num) {
      foreach($delivery_companys as $company){ 
        if($ct_delivery_company == $company["val"] ){
          $result_company = $company["name"];
          break;
        }
      }
      $result_status = 'disable';
      $result_text = "입력완료({$result_company} {$ct_delivery_num})";
    }
    $result[] = array(
      'ct_id' => $ct_id,
      'status' => $result_status,
      'text' => $result_text,
	  'text2' => $result_company2,
    );

    // partner01 위탁인 경우 푸쉬 전송
    if ($ct_is_direct_delivery > 0 && $ct_direct_delivery_partner == 'partner01') {
      if (count($partner01_tokens) > 0)
        send_push($partner01_tokens, $ct_it_name, $ct_is_direct_delivery, $od_id);
    }
  }

  // 23.02.10 : 서원 - 트랜젝션을 통한 SQL 실행관련 메모리 업로드 후 일괄 실행.
  foreach($_sql_string as $sql) { sql_query($sql); }

  sql_query("
    UPDATE g5_shop_order SET
      od_delivery_insert = '{$od_delivery_insert}'
    WHERE od_id = '{$_POST["od_id"]}'
  ");


  // 23.01.25 : 서원 - 트랜잭션 커밋
  sql_query("COMMIT");

} catch (Exception $e) {
  // 23.01.25 : 서원 - 트랜잭션 롤백
  sql_query("ROLLBACK");
}

json_response(200, 'OK', $result);
?>
