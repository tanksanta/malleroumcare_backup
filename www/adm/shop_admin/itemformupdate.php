<?php
$sub_menu = '400300';
include_once('./_common.php');

if ($w == "u" || $w == "d")
  check_demo();

if ($w == '' || $w == 'u')
  auth_check($auth[$sub_menu], "w");
else if ($w == 'd')
  auth_check($auth[$sub_menu], "d");

check_admin_token();

// APMS - 2014.07.20
include_once('../apms_admin/apms.admin.lib.php');

@mkdir(G5_DATA_PATH."/item", G5_DIR_PERMISSION);
@chmod(G5_DATA_PATH."/item", G5_DIR_PERMISSION);

$andQuery = "";

// input vars 체크
check_input_vars();

$ca_id = isset($ca_id) ? preg_replace('/[^0-9a-z]/i', '', $ca_id) : '';
$ca_id2 = isset($ca_id2) ? preg_replace('/[^0-9a-z]/i', '', $ca_id2) : '';
$ca_id3 = isset($ca_id3) ? preg_replace('/[^0-9a-z]/i', '', $ca_id3) : '';
$ca_id4 = isset($ca_id4) ? preg_replace('/[^0-9a-z]/i', '', $ca_id4) : '';
$ca_id5 = isset($ca_id5) ? preg_replace('/[^0-9a-z]/i', '', $ca_id5) : '';
$ca_id6 = isset($ca_id6) ? preg_replace('/[^0-9a-z]/i', '', $ca_id6) : '';
$ca_id7 = isset($ca_id7) ? preg_replace('/[^0-9a-z]/i', '', $ca_id7) : '';
$ca_id8 = isset($ca_id8) ? preg_replace('/[^0-9a-z]/i', '', $ca_id8) : '';
$ca_id9 = isset($ca_id9) ? preg_replace('/[^0-9a-z]/i', '', $ca_id9) : '';
$ca_id10 = isset($ca_id10) ? preg_replace('/[^0-9a-z]/i', '', $ca_id10) : '';

// APMS - 2014.07.20
if (!$_POST['pt_it'])
  alert("서비스종류를 선택해 주십시오.");

if(in_array($pt_it, $g5['apms_automation'])) {
  $it_sc_type = 1;
  $it_sc_method = 0;
  $it_sc_price = 0;
  $it_sc_minimum = 0;
  $it_sc_qty = 0;
}

// 파일정보
if($w == "u") {
  $sql = " select it_img1, it_img2, it_img3, it_img4, it_img5, it_img6, it_img7, it_img8, it_img9, it_img10, it_img_3d
            from {$g5['g5_shop_item_table']}
            where it_id = '$it_id' ";
  $file = sql_fetch($sql);

  $it_img1    = $file['it_img1'];
  $it_img2    = $file['it_img2'];
  $it_img3    = $file['it_img3'];
  $it_img4    = $file['it_img4'];
  $it_img5    = $file['it_img5'];
  $it_img6    = $file['it_img6'];
  $it_img7    = $file['it_img7'];
  $it_img8    = $file['it_img8'];
  $it_img9    = $file['it_img9'];
  $it_img10   = $file['it_img10'];
  $it_img3d   = $file['it_img_3d'];
}
$it_img_dir = G5_DATA_PATH.'/item';

// 파일삭제
if ($it_img1_del) {
  $file_img1 = $it_img_dir.'/'.$it_img1;
  @unlink($file_img1);
  delete_item_thumbnail(dirname($file_img1), basename($file_img1));
  $it_img1 = '';
}
if ($it_img2_del) {
  $file_img2 = $it_img_dir.'/'.$it_img2;
  @unlink($file_img2);
  delete_item_thumbnail(dirname($file_img2), basename($file_img2));
  $it_img2 = '';
}
if ($it_img3_del) {
  $file_img3 = $it_img_dir.'/'.$it_img3;
  @unlink($file_img3);
  delete_item_thumbnail(dirname($file_img3), basename($file_img3));
  $it_img3 = '';
}
if ($it_img4_del) {
  $file_img4 = $it_img_dir.'/'.$it_img4;
  @unlink($file_img4);
  delete_item_thumbnail(dirname($file_img4), basename($file_img4));
  $it_img4 = '';
}
if ($it_img5_del) {
  $file_img5 = $it_img_dir.'/'.$it_img5;
  @unlink($file_img5);
  delete_item_thumbnail(dirname($file_img5), basename($file_img5));
  $it_img5 = '';
}
if ($it_img6_del) {
  $file_img6 = $it_img_dir.'/'.$it_img6;
  @unlink($file_img6);
  delete_item_thumbnail(dirname($file_img6), basename($file_img6));
  $it_img6 = '';
}
if ($it_img7_del) {
  $file_img7 = $it_img_dir.'/'.$it_img7;
  @unlink($file_img7);
  delete_item_thumbnail(dirname($file_img7), basename($file_img7));
  $it_img7 = '';
}
if ($it_img8_del) {
  $file_img8 = $it_img_dir.'/'.$it_img8;
  @unlink($file_img8);
  delete_item_thumbnail(dirname($file_img8), basename($file_img8));
  $it_img8 = '';
}
if ($it_img9_del) {
  $file_img9 = $it_img_dir.'/'.$it_img9;
  @unlink($file_img9);
  delete_item_thumbnail(dirname($file_img9), basename($file_img9));
  $it_img9 = '';
}
if ($it_img10_del) {
  $file_img10 = $it_img_dir.'/'.$it_img10;
  @unlink($file_img10);
  delete_item_thumbnail(dirname($file_img10), basename($file_img10));
  $it_img10 = '';
}




// 이미지업로드
if ($_FILES['it_img1']['name']) {
  if($w == 'u' && $it_img1) {
    $file_img1 = $it_img_dir.'/'.$it_img1;
    @unlink($file_img1);
    delete_item_thumbnail(dirname($file_img1), basename($file_img1));
  }
  $it_img1 = it_img_upload($_FILES['it_img1']['tmp_name'], $_FILES['it_img1']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img2']['name']) {
  if($w == 'u' && $it_img2) {
    $file_img2 = $it_img_dir.'/'.$it_img2;
    @unlink($file_img2);
    delete_item_thumbnail(dirname($file_img2), basename($file_img2));
  }
  $it_img2 = it_img_upload($_FILES['it_img2']['tmp_name'], $_FILES['it_img2']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img3']['name']) {
  if($w == 'u' && $it_img3) {
    $file_img3 = $it_img_dir.'/'.$it_img3;
    @unlink($file_img3);
    delete_item_thumbnail(dirname($file_img3), basename($file_img3));
  }
  $it_img3 = it_img_upload($_FILES['it_img3']['tmp_name'], $_FILES['it_img3']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img4']['name']) {
  if($w == 'u' && $it_img4) {
    $file_img4 = $it_img_dir.'/'.$it_img4;
    @unlink($file_img4);
    delete_item_thumbnail(dirname($file_img4), basename($file_img4));
  }
  $it_img4 = it_img_upload($_FILES['it_img4']['tmp_name'], $_FILES['it_img4']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img5']['name']) {
  if($w == 'u' && $it_img5) {
    $file_img5 = $it_img_dir.'/'.$it_img5;
    @unlink($file_img5);
    delete_item_thumbnail(dirname($file_img5), basename($file_img5));
  }
  $it_img5 = it_img_upload($_FILES['it_img5']['tmp_name'], $_FILES['it_img5']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img6']['name']) {
  if($w == 'u' && $it_img6) {
    $file_img6 = $it_img_dir.'/'.$it_img6;
    @unlink($file_img6);
    delete_item_thumbnail(dirname($file_img6), basename($file_img6));
  }
  $it_img6 = it_img_upload($_FILES['it_img6']['tmp_name'], $_FILES['it_img6']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img7']['name']) {
  if($w == 'u' && $it_img7) {
    $file_img7 = $it_img_dir.'/'.$it_img7;
    @unlink($file_img7);
    delete_item_thumbnail(dirname($file_img7), basename($file_img7));
  }
  $it_img7 = it_img_upload($_FILES['it_img7']['tmp_name'], $_FILES['it_img7']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img8']['name']) {
  if($w == 'u' && $it_img8) {
    $file_img8 = $it_img_dir.'/'.$it_img8;
    @unlink($file_img8);
    delete_item_thumbnail(dirname($file_img8), basename($file_img8));
  }
  $it_img8 = it_img_upload($_FILES['it_img8']['tmp_name'], $_FILES['it_img8']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img9']['name']) {
  if($w == 'u' && $it_img9) {
    $file_img9 = $it_img_dir.'/'.$it_img9;
    @unlink($file_img9);
    delete_item_thumbnail(dirname($file_img9), basename($file_img9));
  }
  $it_img9 = it_img_upload($_FILES['it_img9']['tmp_name'], $_FILES['it_img9']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES['it_img10']['name']) {
  if($w == 'u' && $it_img10) {
    $file_img10 = $it_img_dir.'/'.$it_img10;
    @unlink($file_img10);
    delete_item_thumbnail(dirname($file_img10), basename($file_img10));
  }
  $it_img10 = it_img_upload($_FILES['it_img10']['tmp_name'], $_FILES['it_img10']['name'], $it_img_dir.'/'.$it_id);
}
if ($_FILES["it_img_3d"]["name"]) {
  $it_img_3d = [];

  $uploadCnt = 0;
  foreach($_FILES["it_img_3d"]["name"] as $key => $data){
    $filename = it_img_upload($_FILES["it_img_3d"]["tmp_name"][$key], "3d_{$_FILES["it_img_3d"]["name"][$key]}", $it_img_dir.'/'.$it_id);

    if($filename){
      $uploadCnt++;
      array_push($it_img_3d, $filename);
    }
  }

  $it_img_3d = json_encode($it_img_3d);
  if($uploadCnt){
    $andQuery .= " it_img_3d = '{$it_img_3d}', ";
  }
}
//3d파일삭제
$it_img_3d_del=$_POST['it_img_3d_del'];
if ($it_img_3d_del) {
  $it_img3d = json_decode($it_img3d, true);
  if($it_img3d) {
    foreach($it_img3d as $data) {
      $file_3d = $it_img_dir.'/'.$data;
      @unlink($data);
      delete_item_thumbnail(dirname($file_3d), basename($file_3d));
    }
  }
  delete_item_thumbnail(dirname($file_3d), basename($file_3d));
  // return false;
  $it_img_3d = '';
  $andQuery .= " it_img_3d = '', ";
}

if ($w == "" || $w == "u")
{
  // 다음 입력을 위해서 옵션값을 쿠키로 한달동안 저장함
  //@setcookie("ck_ca_id",  $ca_id,  time() + 86400*31, $default[de_cookie_dir], $default[de_cookie_domain]);
  //@setcookie("ck_maker",  stripslashes($it_maker),  time() + 86400*31, $default[de_cookie_dir], $default[de_cookie_domain]);
  //@setcookie("ck_origin", stripslashes($it_origin), time() + 86400*31, $default[de_cookie_dir], $default[de_cookie_domain]);
  @set_cookie("ck_ca_id", $ca_id, time() + 86400*31);
  @set_cookie("ck_ca_id2", $ca_id2, time() + 86400*31);
  @set_cookie("ck_ca_id3", $ca_id3, time() + 86400*31);
  @set_cookie("ck_ca_id4", $ca_id4, time() + 86400*31);
  @set_cookie("ck_ca_id5", $ca_id5, time() + 86400*31);
  @set_cookie("ck_ca_id6", $ca_id6, time() + 86400*31);
  @set_cookie("ck_ca_id7", $ca_id7, time() + 86400*31);
  @set_cookie("ck_ca_id8", $ca_id8, time() + 86400*31);
  @set_cookie("ck_ca_id9", $ca_id9, time() + 86400*31);
  @set_cookie("ck_ca_id10", $ca_id10, time() + 86400*31);
  @set_cookie("ck_maker", stripslashes($it_maker), time() + 86400*31);
  @set_cookie("ck_origin", stripslashes($it_origin), time() + 86400*31);
}

// 관련상품을 우선 삭제함
sql_query(" delete from {$g5['g5_shop_item_relation_table']} where it_id = '$it_id' ");

// 관련상품의 반대도 삭제
sql_query(" delete from {$g5['g5_shop_item_relation_table']} where it_id2 = '$it_id' ");

// 이벤트상품을 우선 삭제함
sql_query(" delete from {$g5['g5_shop_event_item_table']} where it_id = '$it_id' ");

// 선택옵션
sql_query(" delete from {$g5['g5_shop_item_option_table']} where io_type = '0' and it_id = '$it_id' "); // 기존선택옵션삭제

$option_count = (isset($_POST['opt_id']) && is_array($_POST['opt_id'])) ? count($_POST['opt_id']) : array();
if($option_count) {
  // 옵션명
  $opt1_cnt = $opt2_cnt = $opt3_cnt = 0;
  for($i=0; $i<$option_count; $i++) {
    $_POST['opt_id'][$i] = preg_replace(G5_OPTION_ID_FILTER, '', strip_tags($_POST['opt_id'][$i]));

  $opt_val = explode(chr(30), $_POST['opt_id'][$i]);
    if($opt_val[0])
      $opt1_cnt++;
    if($opt_val[1])
      $opt2_cnt++;
    if($opt_val[2])
      $opt3_cnt++;
  }

  if($opt1_subject && $opt1_cnt) {
    $it_option_subject = $opt1_subject;
    if($opt2_subject && $opt2_cnt)
      $it_option_subject .= ','.$opt2_subject;
    if($opt3_subject && $opt3_cnt)
      $it_option_subject .= ','.$opt3_subject;
  }
}

// 추가옵션
sql_query(" delete from {$g5['g5_shop_item_option_table']} where io_type = '1' and it_id = '$it_id' "); // 기존추가옵션삭제

$supply_count = (isset($_POST['spl_id']) && is_array($_POST['spl_id'])) ? count($_POST['spl_id']) : array();
if($supply_count) {
  // 추가옵션명
  $arr_spl = array();
  for($i=0; $i<$supply_count; $i++) {
    $_POST['spl_id'][$i] = preg_replace(G5_OPTION_ID_FILTER, '', strip_tags($_POST['spl_id'][$i]));

    $spl_val = explode(chr(30), $_POST['spl_id'][$i]);
    if(!in_array($spl_val[0], $arr_spl))
      $arr_spl[] = $spl_val[0];
  }

  $it_supply_subject = implode(',', $arr_spl);
}

// 상품요약정보
$value_array = array();
for($i=0; $i<count($_POST['ii_article']); $i++) {
  $key = $_POST['ii_article'][$i];
  $val = $_POST['ii_value'][$i];
  $value_array[$key] = $val;
}
$it_info_value = addslashes(serialize($value_array));

// 포인트 비율 값 체크
if(($it_point_type == 1 || $it_point_type == 2) && $it_point > 99)
  alert("포인트 비율을 0과 99 사이의 값으로 입력해 주십시오.");

$it_name = strip_tags(clean_xss_attributes(trim($_POST['it_name'])));

// KVE-2019-0708
$check_sanitize_keys = array(
  'it_rental_use_persisting_year', // 대여 내구연한 사용
  'it_rental_expiry_year', //  대여 판매가능기간
  'it_rental_persisting_year', // 대여 내구연한
  'it_rental_persisting_price', // 대여 내구연한 이후 대여금액
  'it_admin_memo',        // 관리자 메모
  'it_order',             // 출력순서
  'it_maker',             // 제조사
  'it_origin',            // 원산지
  'it_brand',             // 브랜드
  'it_model',             // 모델
  'it_tel_inq',           // 전화문의
  'it_use',               // 판매가능
  'it_use_partner',       // 판매가능
  'it_use_custom_order',  // 주문제작가능
  'it_nocoupon',          // 쿠폰적용안함
  'ec_mall_pid',          // 네이버쇼핑 상품ID
  'it_sell_email',        // 판매자 e-mail
  'it_price',             // 판매가격
  'it_price_partner',             // 판매가격
  'it_price_dealer',
  'it_price_dealer2',
  'it_cust_price',        // 시중가격
  'it_point_type',        // 포인트 유형
  'it_point',             // 포인트
  'it_supply_point',      // 추가옵션상품 포인트
  'it_soldout',           // 상품품절
  'it_stock_sms',         // 재입고SMS 알림
  'it_stock_qty',         // 재고수량
  'it_noti_qty',          // 재고 통보수량
  'it_buy_min_qty',       // 최소구매수량
  'it_notax',             // 상품과세 유형
  'it_sc_type',           // 배송비 유형
  'it_sc_method',         // 배송비 결제
  'it_sc_price',          // 기본배송비
  'it_sc_minimum',        // 배송비 상세조건
  'it_sc_type_partner',           // 배송비 유형
  'it_sc_method_partner',         // 배송비 결제
  'it_sc_price_partner',          // 기본배송비
  'it_sc_minimum_partner',        // 배송비 상세조건
  'it_thezone',           // 더존코드
  'it_thezone2',           // 더존코드
  'it_sc_add_sendcost',           // 산간지역 추가 배송비
  'it_sc_add_sendcost_partner',    // 파트너 산간지역 추가 배송비
  'it_box_size_width', // 박스 규격 (가로)
  'it_box_size_length', // 박스 규격 (세로)
  'it_box_size_height', // 박스 규격 (높이)
  'it_standard', // 규격
  'it_even_odd',
  'it_even_odd_price',
  'it_stock_manage_min_qty',
  'it_stock_manage_max_qty',
  'it_purchase_order_price',
  'it_purchase_order_min_qty',
  'it_purchase_order_unit'
);

foreach( $check_sanitize_keys as $key ) {
  $$key = isset($_POST[$key]) ? strip_tags(clean_xss_attributes($_POST[$key])) : '';
}

$it_buy_inc_qty = get_search_string($_POST['it_buy_inc_qty']) ?: 1; // 수량증가단위

if ($it_name == "")
  alert("제목 또는 상품명을 입력해 주십시오.");

// APMS - 2014.07.20
$is_reserve = ($default['pt_reserve_end'] > 0 && $default['pt_reserve_day'] > 0 && $default['pt_reserve_cache'] > 0) ? true : false;

if($pt_reserve_use && $is_reserve) {
  $pt_reserve_time = "{$pt_reserve_date} {$pt_reserve_hour}:{$pt_reserve_minute}:00";
  $pt_reserve = strtotime($pt_reserve_time);
  $it_use = 0;
} else {
  $pt_reserve_use = 0;
  $pt_reserve = 0;
}

if($pt_end_date && $default['pt_reserve_cache'] > 0) {
  $pt_end_time = "{$pt_end_date} {$pt_end_hour}:{$pt_end_minute}:00";
  $pt_end = strtotime($pt_end_time);
} else {
  $pt_end = 0;
}

$pt_syndi_sql = ($is_admin == 'super') ? " pt_syndi = '$pt_syndi', pt_commission = '$pt_commission', pt_incentive = '$pt_incentive', " : "";

$it_sale_cnt = ($_POST["it_sale_cnt"]) ? $_POST["it_sale_cnt"] : 0;
$it_sale_percent = ($_POST["it_sale_percent"]) ? $_POST["it_sale_percent"] : 0;
$it_sale_percent_great = ($_POST["it_sale_percent_great"]) ? $_POST["it_sale_percent_great"] : 0;

$it_sale_cnt_02 = ($_POST["it_sale_cnt_02"]) ? $_POST["it_sale_cnt_02"] : 0;
$it_sale_percent_02 = ($_POST["it_sale_percent_02"]) ? $_POST["it_sale_percent_02"] : 0;
$it_sale_percent_great_02 = ($_POST["it_sale_percent_great_02"]) ? $_POST["it_sale_percent_great_02"] : 0;

$it_sale_cnt_03 = ($_POST["it_sale_cnt_03"]) ? $_POST["it_sale_cnt_03"] : 0;
$it_sale_percent_03 = ($_POST["it_sale_percent_03"]) ? $_POST["it_sale_percent_03"] : 0;
$it_sale_percent_great_03 = ($_POST["it_sale_percent_great_03"]) ? $_POST["it_sale_percent_great_03"] : 0;

$it_sale_cnt_04 = ($_POST["it_sale_cnt_04"]) ? $_POST["it_sale_cnt_04"] : 0;
$it_sale_percent_04 = ($_POST["it_sale_percent_04"]) ? $_POST["it_sale_percent_04"] : 0;
$it_sale_percent_great_04 = ($_POST["it_sale_percent_great_04"]) ? $_POST["it_sale_percent_great_04"] : 0;

$it_sale_cnt_05 = ($_POST["it_sale_cnt_05"]) ? $_POST["it_sale_cnt_05"] : 0;
$it_sale_percent_05 = ($_POST["it_sale_percent_05"]) ? $_POST["it_sale_percent_05"] : 0;
$it_sale_percent_great_05 = ($_POST["it_sale_percent_great_05"]) ? $_POST["it_sale_percent_great_05"] : 0;

$prodId = $it_id;
$entId = $_POST["entId"];
$prodSupYn = $_POST["prodSupYn"];
$it_taxInfo = $_POST["it_taxInfo"];
$ProdPayCode = $_POST["prodPayCode"];

$_POST["it_delivery_cnt"] = ($_POST["it_delivery_cnt"]) ? $_POST["it_delivery_cnt"] : 0;
$_POST["it_delivery_price"] = ($_POST["it_delivery_price"]) ? $_POST["it_delivery_price"] : 0;

$_POST["it_delivery_min_cnt"] = ($_POST["it_delivery_min_cnt"]) ? $_POST["it_delivery_min_cnt"] : 0;
$_POST["it_delivery_min_price"] = ($_POST["it_delivery_min_price"]) ? $_POST["it_delivery_min_price"] : 0;

$_POST["it_delivery_cnt2"] = ($_POST["it_delivery_cnt2"]) ? $_POST["it_delivery_cnt2"] : 0;
$_POST["it_delivery_price2"] = ($_POST["it_delivery_price2"]) ? $_POST["it_delivery_price2"] : 0;

$_POST["it_delivery_min_cnt2"] = ($_POST["it_delivery_min_cnt2"]) ? $_POST["it_delivery_min_cnt2"] : 0;
$_POST["it_delivery_min_price2"] = ($_POST["it_delivery_min_price2"]) ? $_POST["it_delivery_min_price2"] : 0;

$it_rental_price = ($_POST["it_rental_price"]) ? $_POST["it_rental_price"] : 0;

$it_is_direct_delivery = (int)$it_is_direct_delivery ?: 0;
$it_is_direct_release_ready = (int)$it_is_direct_release_ready ?: 0;
$it_direct_delivery_partner = '';
$it_direct_delivery_price = 0;
if($it_is_direct_delivery == 1) {
  $it_direct_delivery_partner = $_POST['it_direct_delivery_partner1'] ?: '';
  $it_direct_delivery_price = (int)$_POST['it_direct_delivery_price1'] ?: 0;
}
else if($it_is_direct_delivery == 2) {
  $it_direct_delivery_partner = $_POST['it_direct_delivery_partner2'] ?: '';
  $it_direct_delivery_price = (int)$_POST['it_direct_delivery_price2'] ?: 0;
}

$warehouse_list = get_warehouses();
$it_warehousing_warehouse = in_array($_POST['it_warehousing_warehouse'], $warehouse_list) ? $_POST['it_warehousing_warehouse'] : '';
$it_default_warehouse = in_array($_POST['it_default_warehouse'], $warehouse_list) ? $_POST['it_default_warehouse'] : '';

// 박스 규격
$it_box_size = [
  $it_box_size_width,
  $it_box_size_length,
  $it_box_size_height
];
$it_box_size = implode(chr(30), $it_box_size);

$it_show_partner_search = ($_POST["it_show_partner_search"]) ? $_POST["it_show_partner_search"] : 0;
$it_use_short_barcode = ($_POST["it_use_short_barcode"]) ? $_POST["it_use_short_barcode"] : 0;

// 안전재고, 최대재고
$average_sales_qty = get_average_sales_qty($it_id, 3);

if (!$it_stock_manage_min_qty) {
  $it_stock_manage_min_qty= (int)($average_sales_qty / 2);
}

if (!$it_stock_manage_max_qty) {
  $it_stock_manage_max_qty = (int)($average_sales_qty * 1.5);
}

$sql_common = "
  ca_id               = '$ca_id',
  ca_id2              = '$ca_id2',
  ca_id3              = '$ca_id3',
  ca_id4              = '$ca_id4',
  ca_id5              = '$ca_id5',
  ca_id6              = '$ca_id6',
  ca_id7              = '$ca_id7',
  ca_id8              = '$ca_id8',
  ca_id9              = '$ca_id9',
  ca_id10             = '$ca_id10',
  it_name             = '$it_name',
  it_admin_memo       = '$it_admin_memo',
  it_maker            = '$it_maker',
  it_origin           = '$it_origin',
  it_brand            = '$it_brand',
  it_model            = '$it_model',
  it_option_subject   = '$it_option_subject',
  it_supply_subject   = '$it_supply_subject',
  it_type1            = '$it_type1',
  it_type2            = '$it_type2',
  it_type3            = '$it_type3',
  it_type4            = '$it_type4',
  it_type5            = '$it_type5',
  it_type6            = '$it_type6',
  it_type7            = '$it_type7',
  it_type8            = '$it_type8',
  it_type9            = '$it_type9',
  it_type10           = '$it_type10',
  it_type11           = '$it_type11',
  it_type12           = '$it_type12',
  it_type13           = '$it_type13',
  it_deadline           = '$it_deadline',
  it_basic            = '$it_basic',
  it_explan           = '$it_explan',
  it_explan2          = '".strip_tags(clean_xss_attributes(trim($_POST['it_explan'])))."',
  it_mobile_explan    = '$it_mobile_explan',
  it_reference        = '$it_reference',
  it_rental_price       = '$it_rental_price',
  it_cust_price       = '$it_cust_price',
  it_price            = '$it_price',
  it_price_partner    = '$it_price_partner',
  it_price_dealer     = '$it_price_dealer',
  it_price_dealer2    = '$it_price_dealer2',
  it_point            = '$it_point',
  it_point_type       = '$it_point_type',
  it_supply_point     = '$it_supply_point',
  it_notax            = '$it_notax',
  it_sell_email       = '$it_sell_email',
  it_use              = '$it_use',
  it_use_partner      = '$it_use_partner',
  it_use_custom_order = '$it_use_custom_order',
  it_nocoupon         = '$it_nocoupon',
  it_soldout          = '$it_soldout',
  it_stock_qty        = '$it_stock_qty',
  it_stock_sms        = '$it_stock_sms',
  it_noti_qty         = '$it_noti_qty',
  it_sc_type          = '$it_sc_type',
  it_sc_method        = '$it_sc_method',
  it_sc_price         = '$it_sc_price',
  it_sc_minimum       = '$it_sc_minimum',
  it_sc_qty           = '$it_sc_qty',
  it_sc_type_partner          = '$it_sc_type_partner',
  it_sc_method_partner        = '$it_sc_method_partner',
  it_sc_price_partner         = '$it_sc_price_partner',
  it_sc_minimum_partner       = '$it_sc_minimum_partner',
  it_sc_qty_partner           = '$it_sc_qty_partner',
  it_buy_min_qty      = '$it_buy_min_qty',
  it_buy_max_qty      = '$it_buy_max_qty',
  it_buy_inc_qty      = '$it_buy_inc_qty',
  it_head_html        = '$it_head_html',
  it_tail_html        = '$it_tail_html',
  it_mobile_head_html = '$it_mobile_head_html',
  it_mobile_tail_html = '$it_mobile_tail_html',
  it_ip               = '{$_SERVER['REMOTE_ADDR']}',
  it_order            = '$it_order',
  it_tel_inq          = '$it_tel_inq',
  it_info_gubun       = '$it_info_gubun',
  it_info_value       = '$it_info_value',
  it_shop_memo        = '$it_shop_memo',
  ec_mall_pid         = '$ec_mall_pid',
  it_img1             = '$it_img1',
  it_img2             = '$it_img2',
  it_img3             = '$it_img3',
  it_img4             = '$it_img4',
  it_img5             = '$it_img5',
  it_img6             = '$it_img6',
  it_img7             = '$it_img7',
  it_img8             = '$it_img8',
  it_img9             = '$it_img9',
  it_img10            = '$it_img10',
  it_1_subj           = '$it_1_subj',
  it_2_subj           = '$it_2_subj',
  it_3_subj           = '$it_3_subj',
  it_4_subj           = '$it_4_subj',
  it_5_subj           = '$it_5_subj',
  it_6_subj           = '$it_6_subj',
  it_7_subj           = '$it_7_subj',
  it_8_subj           = '$it_8_subj',
  it_9_subj           = '$it_9_subj',
  it_10_subj          = '$it_10_subj',
  it_1                = '$it_1',
  it_2                = '$it_2',
  it_3                = '$it_3',
  it_4                = '$it_4',
  it_5                = '$it_5',
  it_6                = '$it_6',
  it_7                = '$it_7',
  it_8                = '$it_8',
  it_9                = '$it_9',
  it_10               = '$it_10',
  {$andQuery}
  gubun               = '$gubun',
  prodNm              = '$prodNm',
  itemId              = '$itemId',
  subItem             = '$subItem',
  prodSupPrice        = '$prodSupPrice',
  prodOflPrice        = '$prodOflPrice',
  ProdPayCode         = '$ProdPayCode',
  supId               = '$supId',
  prodColor           = '$prodColor',
  prodSym             = '$prodSym',
  prodWeig            = '$prodWeig',
  prodSize            = '$prodSize',
  prodQty             = '$prodQty',
  prodDetail          = '$prodDetail',
  regDtm              = '$regDtm',
  regUsrId            = '$regUsrId',
  regUsrIp            = '$regUsrIp',
  supNm               = '$supNm',
  prodImgAttr         = '$prodImgAttr',
  pt_it               = '$pt_it',
  pt_id               = '$pt_id',
  pt_img              = '$pt_img',
  pt_ccl              = '$pt_ccl',
  pt_main             = '$pt_main',
  pt_point            = '$pt_point',
  pt_order            = '$pt_order',
  pt_show             = '$pt_show',
  pt_tag              = '$pt_tag',
  pt_link1            = '$pt_link1',
  pt_link2            = '$pt_link2',
  pt_marketer         = '$pt_marketer',
  pt_review_use       = '$pt_review_use',
  pt_comment_use      = '$pt_comment_use',
  pt_day              = '$pt_day',
  pt_end              = '$pt_end',
  pt_reserve          = '$pt_reserve',
  pt_reserve_use      = '$pt_reserve_use',
  {$pt_syndi_sql}
  pt_explan           = '$pt_explan',
  pt_mobile_explan    = '$pt_mobile_explan',
  pt_msg1             = '$pt_msg1',
  pt_msg2             = '$pt_msg2',
  pt_msg3             = '$pt_msg3',
  it_thezone          = '$it_thezone',
  it_thezone2         = '$it_thezone2',
  it_youtube_link     = '$it_youtube_link',
  it_outsourcing_use  = '$it_outsourcing_use',
  it_outsourcing_id   = '$it_outsourcing_id',
  it_outsourcing_option   = '$it_outsourcing_option',
  it_outsourcing_option2  = '$it_outsourcing_option2',
  it_outsourcing_option3  = '$it_outsourcing_option3',
  it_outsourcing_option4  = '$it_outsourcing_option4',
  it_outsourcing_option5  = '$it_outsourcing_option5',
  it_sc_add_sendcost      = '$it_sc_add_sendcost',
  it_sc_add_sendcost_partner = '$it_sc_add_sendcost_partner',
  it_type             = '$it_type',
  it_sale_cnt             = '$it_sale_cnt',
  it_sale_percent             = '$it_sale_percent',
  it_sale_percent_great             = '$it_sale_percent_great',
  it_sale_cnt_02             = '$it_sale_cnt_02',
  it_sale_percent_02             = '$it_sale_percent_02',
  it_sale_percent_great_02             = '$it_sale_percent_great_02',
  it_sale_cnt_03             = '$it_sale_cnt_03',
  it_sale_percent_03             = '$it_sale_percent_03',
  it_sale_percent_great_03             = '$it_sale_percent_great_03',
  it_sale_cnt_04             = '$it_sale_cnt_04',
  it_sale_percent_04             = '$it_sale_percent_04',
  it_sale_percent_great_04             = '$it_sale_percent_great_04',
  it_sale_cnt_05             = '$it_sale_cnt_05',
  it_sale_percent_05             = '$it_sale_percent_05',
  it_sale_percent_great_05             = '$it_sale_percent_great_05',
  it_warehousing_warehouse = '$it_warehousing_warehouse',
  it_default_warehouse = '$it_default_warehouse',
  it_expected_warehousing_date = '$it_expected_warehousing_date',

  entId = '$entId',
  prodSupYn = '$prodSupYn',
  prodSizeDetail = '$prodSizeDetail',
  it_taxInfo = '$it_taxInfo',

  it_delivery_cnt = '{$_POST["it_delivery_cnt"]}',
  it_delivery_price = '{$_POST["it_delivery_price"]}',
  it_delivery_min_cnt = '{$_POST["it_delivery_min_cnt"]}',
  it_delivery_min_price = '{$_POST["it_delivery_min_price"]}',
  it_delivery_company = '{$_POST["it_delivery_company"]}',

  it_delivery_cnt2 = '{$_POST["it_delivery_cnt2"]}',
  it_delivery_price2 = '{$_POST["it_delivery_price2"]}',
  it_delivery_min_cnt2 = '{$_POST["it_delivery_min_cnt2"]}',
  it_delivery_min_price2 = '{$_POST["it_delivery_min_price2"]}',
  it_delivery_company2 = '{$_POST["it_delivery_company2"]}',

  it_is_direct_delivery = '$it_is_direct_delivery',
  it_direct_delivery_partner = '$it_direct_delivery_partner',
  it_direct_delivery_price = '$it_direct_delivery_price',
  it_is_direct_release_ready = '$it_is_direct_release_ready',

  it_rental_use_persisting_year = '$it_rental_use_persisting_year',
  it_rental_expiry_year = '$it_rental_expiry_year',
  it_rental_persisting_year = '$it_rental_persisting_year',
  it_rental_persisting_price = '$it_rental_persisting_price',
  it_box_size = '$it_box_size',
  it_standard = '$it_standard',
  it_show_partner_search = '$it_show_partner_search',
  it_use_short_barcode = '$it_use_short_barcode',
  prodassistingdevicescode = '$prodassistingdevicescode',
  it_even_odd = '$it_even_odd',
  it_even_odd_price = '$it_even_odd_price',
  
  it_stock_manage_min_qty = '$it_stock_manage_min_qty',
  it_stock_manage_max_qty = '$it_stock_manage_max_qty',
  it_purchase_order_price = '$it_purchase_order_price',
  it_purchase_order_min_qty = '$it_purchase_order_min_qty',
  it_purchase_order_unit = '$it_purchase_order_unit',
  it_purchase_order_partner = '$it_purchase_order_partner'
"; // APMS : 2014.07.20

if ($w == "")
{
  // 먼저 시스템에 등록
  $gubun = $cate_gubun_table[substr($ca_id, 0, 2)];
  $tax_info = $it_taxInfo == '영세' ? '01' : '02';
  $prod_color = [];
  $prod_size = [];
  $opt_subject_arr = explode(',', $it_option_subject);
  if($option_count) {
    $prod_color_idx = -1;
    $prod_size_idx = -1;
    for($i = 0; $i < count($opt_subject_arr); $i++) {
      if($opt_subject_arr[$i] == '색상') {
        $prod_color_idx = $i;
      } else if($opt_subject_arr[$i] == '사이즈') {
        $prod_size_idx = $i;
      }
    }
    for($i=0; $i<$option_count; $i++) {
      $opt_arr = explode(chr(30), $_POST['opt_id'][$i]);
      if($prod_color_idx >= 0 && !in_array($opt_arr[$prod_color_idx], $prod_color)) {
        $prod_color[] = $opt_arr[$prod_color_idx];
      }
      if($prod_size_idx >= 0 && !in_array($opt_arr[$prod_size_idx], $prod_size)) {
        $prod_size[] = $opt_arr[$prod_size_idx];
      }
    }
  }
  $prod_color = implode('|', $prod_color);
  $prod_size = implode('|', $prod_size);

  $result = post_formdata(EROUMCARE_API_PROD_INSERT, array(
    'usrId' => $member["mb_id"],
    'entId' => $entId,
    'prodNm' => $it_name, // 제품 명
    'prodSym' => $prodSym, // 재질
    'prodWeig' => $prodWeig, // 중량
    'prodColor' => $prod_color, // 컬러
    'prodSize' => $prod_size, // 사이즈
    'prodSizeDetail' => $prodSizeDetail, // 사이즈 상세정보
    'prodDetail' => $it_explan, // 상세정보
    'prodPayCode' => $ProdPayCode, // 제품코드
    'prodSupYn' => $prodSupYn, //  유통 미유통
    'prodSupPrice' => $it_cust_price, // 공급가격
    'prodOflPrice' => $it_price, // 판매가격
    'rentalPrice' => $it_rental_price, // 대여가격(1일)
    'rentalPriceExtn' => $it_rental_price, //  대여연장가격(1일)
    'prodStateCode' => '03', // 제품 등록상태 (01:등록신청 / 02:수정신청 / 03:등록)
    'supId' => $supId, //  공급자아이디
    'itemId' => $it_thezone, //  아이템 아이디
    'subItem' => '', //  서브 아이템
    'gubun' => $gubun, //  00=구매 01=대여
    'taxInfoCd' => $tax_info, //  01=영세 02=과세
    'file1' => $it_img1 ? new cURLFile($it_img_dir.'/'.$it_img1) : '',
    'file2' => $it_img2 ? new cURLFile($it_img_dir.'/'.$it_img2) : '',
    'file3' => $it_img3 ? new cURLFile($it_img_dir.'/'.$it_img3) : '',
    'file4' => $it_img4 ? new cURLFile($it_img_dir.'/'.$it_img4) : '',
    'file5' => $it_img5 ? new cURLFile($it_img_dir.'/'.$it_img5) : '',
    'file6' => $it_img6 ? new cURLFile($it_img_dir.'/'.$it_img6) : '',
    'file7' => $it_img7 ? new cURLFile($it_img_dir.'/'.$it_img7) : '',
    'file8' => $it_img8 ? new cURLFile($it_img_dir.'/'.$it_img8) : '',
    'file9' => $it_img9 ? new cURLFile($it_img_dir.'/'.$it_img9) : '',
    'file10' => $it_img10 ? new cURLFile($it_img_dir.'/'.$it_img10) : ''
  ));

  if($result['errorYN'] != 'N' || !$result['data']['prodId'])
    alert('시스템 오류 발생: ' . $result['message']);

  $it_id = $result['data']['prodId'];

  if (!trim($it_id)) {
    alert('코드가 없으므로 추가하실 수 없습니다.');
  }

  $t_it_id = preg_replace("/[A-Za-z0-9\-_]/", "", $it_id);
  if($t_it_id)
    alert('코드는 영문자, 숫자, -, _ 만 사용할 수 있습니다.');

  $pt_num = time();
  $sql_common .= " , it_time = '".G5_TIME_YMDHIS."' ";
  $sql_common .= " , it_update_time = '".G5_TIME_YMDHIS."' ";
  $sql = "
    insert {$g5['g5_shop_item_table']}
    set
      it_id = '$it_id',
      pt_num = '$pt_num',
      prodId = '$prodId',
    {$sql_common}
  ";
  sql_query($sql);
}
else if ($w == "u")
{
  $gubun = $cate_gubun_table[substr($ca_id, 0, 2)];
  $tax_info = $it_taxInfo == '영세' ? '01' : '02';
  $prod_color = [];
  $prod_size = [];
  $opt_subject_arr = explode(',', $it_option_subject);
  if($option_count) {
    $prod_color_idx = -1;
    $prod_size_idx = -1;
    for($i = 0; $i < count($opt_subject_arr); $i++) {
      if($opt_subject_arr[$i] == '색상') {
        $prod_color_idx = $i;
      } else if($opt_subject_arr[$i] == '사이즈') {
        $prod_size_idx = $i;
      }
    }
    for($i=0; $i<$option_count; $i++) {
      $opt_arr = explode(chr(30), $_POST['opt_id'][$i]);
      if($prod_color_idx >= 0 && !in_array($opt_arr[$prod_color_idx], $prod_color)) {
        $prod_color[] = $opt_arr[$prod_color_idx];
      }
      if($prod_size_idx >= 0 && !in_array($opt_arr[$prod_size_idx], $prod_size)) {
        $prod_size[] = $opt_arr[$prod_size_idx];
      }
    }
  }
  $prod_color = implode('|', $prod_color);
  $prod_size = implode('|', $prod_size);

  $sendData = array(
    'prodId' => $it_id,
    'usrId' => $member["mb_id"],
    'entId' => $entId,
    'prodNm' => $it_name, // 제품 명
    'prodSym' => $prodSym, // 재질
    'prodWeig' => $prodWeig, // 중량
    'prodColor' => $prod_color, // 컬러
    'prodSize' => $prod_size, // 사이즈
    'prodSizeDetail' => $prodSizeDetail, // 사이즈 상세정보
    'prodDetail' => $it_explan, // 상세정보
    'prodPayCode' => $ProdPayCode, // 제품코드
    'prodSupYn' => $prodSupYn, //  유통 미유통
    'prodSupPrice' => $it_cust_price, // 공급가격
    'prodOflPrice' => $it_price, // 판매가격
    'rentalPrice' => $it_rental_price, // 대여가격(1일)
    'rentalPriceExtn' => $it_rental_price, //  대여연장가격(1일)
    'prodStateCode' => '03', // 제품 등록상태 (01:등록신청 / 02:수정신청 / 03:등록)
    'supId' => $supId, //  공급자아이디
    'itemId' => $it_thezone, //  아이템 아이디
    'subItem' => '', //  서브 아이템
    'gubun' => $gubun, //  00=구매 01=대여
    'taxInfoCd' => $tax_info, //  01=영세 02=과세
    'file1' => $it_img1 ? new cURLFile($it_img_dir.'/'.$it_img1) : '',
    'file2' => $it_img2 ? new cURLFile($it_img_dir.'/'.$it_img2) : '',
    'file3' => $it_img3 ? new cURLFile($it_img_dir.'/'.$it_img3) : '',
    'file4' => $it_img4 ? new cURLFile($it_img_dir.'/'.$it_img4) : '',
    'file5' => $it_img5 ? new cURLFile($it_img_dir.'/'.$it_img5) : '',
    'file6' => $it_img6 ? new cURLFile($it_img_dir.'/'.$it_img6) : '',
    'file7' => $it_img7 ? new cURLFile($it_img_dir.'/'.$it_img7) : '',
    'file8' => $it_img8 ? new cURLFile($it_img_dir.'/'.$it_img8) : '',
    'file9' => $it_img9 ? new cURLFile($it_img_dir.'/'.$it_img9) : '',
    'file10' => $it_img10 ? new cURLFile($it_img_dir.'/'.$it_img10) : ''
  );
  $result = post_formdata(EROUMCARE_API_PROD_UPDATE, $sendData);

  $sql_common .= " , it_update_time = '".G5_TIME_YMDHIS."' ";
  $sql = "
    update {$g5['g5_shop_item_table']}
    set $sql_common
    where it_id = '$it_id'
  ";
  sql_query($sql);
}
/*
else if ($w == "d")
{
    if ($is_admin != 'super')
    {
        $sql = " select it_id from {$g5['g5_shop_item_table']} a, {$g5['g5_shop_category_table']} b
                  where a.it_id = '$it_id'
                    and a.ca_id = b.ca_id
                    and b.ca_mb_id = '{$member['mb_id']}' ";
        $row = sql_fetch($sql);
        if (!$row['it_id'])
            alert("\'{$member['mb_id']}\' 님께서 삭제 할 권한이 없는 상품입니다.");
    }

    itemdelete($it_id);
}
*/

if ($w == "" || $w == "u")
{
  // 관련상품 등록
  $it_id2 = explode(",", $it_list);
  for ($i=0; $i<count($it_id2); $i++)
  {
    if (trim($it_id2[$i]))
    {
      $sql = "
      insert into {$g5['g5_shop_item_relation_table']}
      set
        it_id  = '$it_id',
        it_id2 = '$it_id2[$i]',
        ir_no = '$i'
      ";
      sql_query($sql, false);

      // 관련상품의 반대로도 등록
      $sql = "
        insert into {$g5['g5_shop_item_relation_table']}
        set
          it_id  = '$it_id2[$i]',
          it_id2 = '$it_id',
          ir_no = '$i'
      ";
      sql_query($sql, false);
    }
  }

  // 이벤트상품 등록
  $ev_id = explode(",", $ev_list);
  for ($i=0; $i<count($ev_id); $i++)
  {
    if (trim($ev_id[$i]))
    {
      $sql = "
        insert into {$g5['g5_shop_event_item_table']}
        set
          ev_id = '$ev_id[$i]',
          it_id = '$it_id'
      ";
      sql_query($sql, false);
    }
  }
}

// 선택옵션등록
if($option_count) {
  $comma = '';
  $sql = "
    INSERT INTO {$g5['g5_shop_item_option_table']}
    ( `io_id`, `io_type`, `it_id`, `io_price`, `io_stock_qty`, `io_noti_qty`, `io_use`, `io_price_partner`, `io_price_dealer`, `io_price_dealer2`, `io_thezone`, `io_standard`, `io_use_short_barcode`, `io_stock_manage_min_qty`, `io_stock_manage_max_qty`,`io_sold_out` )
    VALUES
  ";
  for($i=0; $i<$option_count; $i++) {
	  if(count($_POST['opt_sold_out']) == 0){
		$opt_sold_out = "0";
	  }else{
		$opt_sold_out = (in_array($_POST['opt_id'][$i],$_POST['opt_sold_out']))?"1" : "0";
	  }
	  if(count($_POST['opt_use_short_barcode']) == 0){
		$opt_use_short_barcode = "0";
	  }else{
		$opt_use_short_barcode = (in_array($_POST['opt_id'][$i],$_POST['opt_use_short_barcode']))?"1" : "0";
	  }
    $sql .= $comma . " ( '{$_POST['opt_id'][$i]}', '0', '$it_id', '{$_POST['opt_price'][$i]}', '{$_POST['opt_stock_qty'][$i]}', '{$_POST['opt_noti_qty'][$i]}', '{$_POST['opt_use'][$i]}', '{$_POST['opt_price_partner'][$i]}', '{$_POST['opt_price_dealer'][$i]}', '{$_POST['opt_price_dealer2'][$i]}', '{$_POST['opt_thezone'][$i]}', '{$_POST['opt_standard'][$i]}', '{$opt_use_short_barcode}', '{$_POST['opt_stock_manage_min_qty'][$i]}', '{$_POST['opt_stock_manage_max_qty'][$i]}','{$opt_sold_out}' )";
    $comma = ' , ';
  }

  sql_query($sql);
}

// 추가옵션등록
if($supply_count) {
  $comma = '';
  $sql = "
    INSERT INTO {$g5['g5_shop_item_option_table']}
    ( `io_id`, `io_type`, `it_id`, `io_price`, `io_stock_qty`, `io_noti_qty`, `io_use`, `io_price_partner`, `io_price_dealer`, `io_price_dealer2`, `io_thezone`, `io_standard` )
    VALUES
  ";
  for($i=0; $i<$supply_count; $i++) {
    $sql .= $comma . " ( '{$_POST['spl_id'][$i]}', '1', '$it_id', '{$_POST['spl_price'][$i]}', '{$_POST['spl_stock_qty'][$i]}', '{$_POST['spl_noti_qty'][$i]}', '{$_POST['spl_use'][$i]}', '{$_POST['spl_price_partner'][$i]}', '{$_POST['spl_price_dealer'][$i]}', '{$_POST['spl_price_dealer2'][$i]}', '{$_POST['spl_thezone'][$i]}', '{$_POST['spl_standard'][$i]}' )";
    $comma = ' , ';
  }

  sql_query($sql);
}

// APMS : 태그 및 파일 등록 - 2014.07.20
$file_upload_msg = '';
if ($w == "" || $w == "u") {
  // 태그등록
  $it_time = G5_TIME_YMDHIS;
  if($w == "u") {
    $row = sql_fetch("select it_time from {$g5['g5_shop_item_table']} where it_id = '{$it_id}' ");
    $it_time = $row['it_time'];
  }

  apms_add_tag($it_id, $pt_tag, $it_time, '', '', $pt_id);

  // 파일등록
  $file_upload_msg = apms_upload_file('item', $it_id);

  // 네이버 신디
  if ($is_admin == 'super' && $it_use && $pt_syndi) {
    apms_naver_syndi_ping($it_id);
  }
}

// 썸네일 업데이트
$it = apms_it($it_id);
$it['chk_img'] = true;
$it_thumb = apms_it_thumbnail($it, 0, 0, false, true);
$it_thumb = ($it_thumb) ? $it_thumb : 1;
sql_query(" update {$g5['g5_shop_item_table']} set pt_thumb = '".addslashes($it_thumb)."' where it_id = '{$it_id}' ", false);

// 동일 분류내 상품 동일 옵션 적용
$ca_fields = '';
if(is_checked('chk_ca_it_skin'))                $ca_fields .= " , it_skin = '$it_skin' ";
if(is_checked('chk_ca_it_mobile_skin'))         $ca_fields .= " , it_mobile_skin = '$it_mobile_skin' ";
if(is_checked('chk_ca_it_basic'))               $ca_fields .= " , it_basic = '$it_basic' ";
if(is_checked('chk_ca_it_order'))               $ca_fields .= " , it_order = '$it_order' ";
if(is_checked('chk_ca_it_type'))                $ca_fields .= " , it_type1 = '$it_type1', it_type2 = '$it_type2', it_type3 = '$it_type3', it_type4 = '$it_type4', it_type5 = '$it_type5', it_type6 = '$it_type6', it_type7 = '$it_type7', it_type8 = '$it_type8', it_type9 = '$it_type9', it_type10 = '$it_type10', it_type11 = '$it_type11', it_type12 = '$it_type12', it_type13 = '$it_type13', it_deadline = '$it_deadline' ";
if(is_checked('chk_ca_it_maker'))               $ca_fields .= " , it_maker = '$it_maker' ";
if(is_checked('chk_ca_it_origin'))              $ca_fields .= " , it_origin = '$it_origin' ";
if(is_checked('chk_ca_it_brand'))               $ca_fields .= " , it_brand = '$it_brand' ";
if(is_checked('chk_ca_it_model'))               $ca_fields .= " , it_model = '$it_model' ";
if(is_checked('chk_ca_it_notax'))               $ca_fields .= " , it_notax = '$it_notax' ";
if(is_checked('chk_ca_it_sell_email'))          $ca_fields .= " , it_sell_email = '$it_sell_email' ";
if(is_checked('chk_ca_it_tel_inq'))             $ca_fields .= " , it_tel_inq = '$it_tel_inq' ";
if(is_checked('chk_ca_it_use'))                 $ca_fields .= " , it_use = '$it_use' ";
if(is_checked('chk_ca_it_use_partner'))         $ca_fields .= " , it_use_partner = '$it_use_partner' ";
if(is_checked('chk_ca_it_show_partner_search'))         $ca_fields .= " , it_show_partner_search = '$it_show_partner_search' ";
if(is_checked('chk_ca_it_use_short_barcode'))         $ca_fields .= " , it_use_short_barcode = '$it_use_short_barcode' ";
if(is_checked('chk_ca_prodassistingdevicescode'))         $ca_fields .= " , prodassistingdevicescode = '$prodassistingdevicescode' ";
if(is_checked('chk_ca_it_use_custom_order'))    $ca_fields .= " , it_use_custom_order = '$it_use_custom_order' ";
if(is_checked('chk_ca_it_nocoupon'))            $ca_fields .= " , it_nocoupon = '$it_nocoupon' ";
if(is_checked('chk_ca_it_soldout'))             $ca_fields .= " , it_soldout = '$it_soldout' ";
if(is_checked('chk_ca_it_info'))                $ca_fields .= " , it_info_gubun = '$it_info_gubun', it_info_value = '$it_info_value' ";
if(is_checked('chk_ca_it_price'))               $ca_fields .= " , it_price = '$it_price' ";
if(is_checked('chk_ca_it_price_partner'))       $ca_fields .= " , it_price_partner = '$it_price_partner' ";
if(is_checked('chk_ca_it_price_dealer'))        $ca_fields .= " , it_price_dealer = '$it_price_dealer' ";
if(is_checked('chk_ca_it_cust_price'))          $ca_fields .= " , it_cust_price = '$it_cust_price' ";
if(is_checked('chk_ca_it_point'))               $ca_fields .= " , it_point = '$it_point' ";
if(is_checked('chk_ca_it_point_type'))          $ca_fields .= " , it_point_type = '$it_point_type' ";
if(is_checked('chk_ca_it_supply_point'))        $ca_fields .= " , it_supply_point = '$it_supply_point' ";
if(is_checked('chk_ca_it_stock_qty'))           $ca_fields .= " , it_stock_qty = '$it_stock_qty' ";
if(is_checked('chk_ca_it_noti_qty'))            $ca_fields .= " , it_noti_qty = '$it_noti_qty' ";
if(is_checked('chk_ca_it_sendcost'))            $ca_fields .= " , it_sc_type = '$it_sc_type', it_sc_method = '$it_sc_method', it_sc_price = '$it_sc_price', it_sc_minimum = '$it_sc_minimum', it_sc_qty = '$it_sc_qty' ";
if(is_checked('chk_ca_it_sendcost'))            $ca_fields .= " , it_sc_type_partner = '$it_sc_type_partner', it_sc_method_partner = '$it_sc_method_partner', it_sc_price_partner = '$it_sc_price_partner', it_sc_minimum_partner = '$it_sc_minimum_partner', it_sc_qty_parnter = '$it_sc_qty_parnter' ";
if(is_checked('chk_ca_it_buy_min_qty'))         $ca_fields .= " , it_buy_min_qty = '$it_buy_min_qty' ";
if(is_checked('chk_ca_it_buy_max_qty'))         $ca_fields .= " , it_buy_max_qty = '$it_buy_max_qty' ";
if(is_checked('chk_ca_it_head_html'))           $ca_fields .= " , it_head_html = '$it_head_html' ";
if(is_checked('chk_ca_it_tail_html'))           $ca_fields .= " , it_tail_html = '$it_tail_html' ";
if(is_checked('chk_ca_it_mobile_head_html'))    $ca_fields .= " , it_mobile_head_html = '$it_mobile_head_html' ";
if(is_checked('chk_ca_it_mobile_tail_html'))    $ca_fields .= " , it_mobile_tail_html = '$it_mobile_tail_html' ";
if(is_checked('chk_ca_1'))                      $ca_fields .= " , it_1_subj = '$it_1_subj', it_1 = '$it_1' ";
if(is_checked('chk_ca_2'))                      $ca_fields .= " , it_2_subj = '$it_2_subj', it_2 = '$it_2' ";
if(is_checked('chk_ca_3'))                      $ca_fields .= " , it_3_subj = '$it_3_subj', it_3 = '$it_3' ";
if(is_checked('chk_ca_4'))                      $ca_fields .= " , it_4_subj = '$it_4_subj', it_4 = '$it_4' ";
if(is_checked('chk_ca_5'))                      $ca_fields .= " , it_5_subj = '$it_5_subj', it_5 = '$it_5' ";
if(is_checked('chk_ca_6'))                      $ca_fields .= " , it_6_subj = '$it_6_subj', it_6 = '$it_6' ";
if(is_checked('chk_ca_7'))                      $ca_fields .= " , it_7_subj = '$it_7_subj', it_7 = '$it_7' ";
if(is_checked('chk_ca_8'))                      $ca_fields .= " , it_8_subj = '$it_8_subj', it_8 = '$it_8' ";
if(is_checked('chk_ca_9'))                      $ca_fields .= " , it_9_subj = '$it_9_subj', it_9 = '$it_9' ";
if(is_checked('chk_ca_10'))                     $ca_fields .= " , it_10_subj = '$it_10_subj', it_10 = '$it_10' ";

if($ca_fields) {
  sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id = '$ca_id' ");
  if($ca_id2)
    sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id2 = '$ca_id2' ");
  if($ca_id3)
    sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id3 = '$ca_id3' ");
  if($ca_id4)
    sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id4 = '$ca_id4' ");
  if($ca_id5)
    sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id5 = '$ca_id5' ");
  if($ca_id6)
    sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id6 = '$ca_id6' ");
  if($ca_id7)
    sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id7 = '$ca_id7' ");
  if($ca_id8)
    sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id8 = '$ca_id8' ");
  if($ca_id9)
    sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id9 = '$ca_id9' ");
  if($ca_id10)
    sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$ca_fields} where ca_id10 = '$ca_id10' ");
}

// 모든 상품 동일 옵션 적용
$all_fields = '';
if(is_checked('chk_all_it_skin'))                $all_fields .= " , it_skin = '$it_skin' ";
if(is_checked('chk_all_it_mobile_skin'))         $all_fields .= " , it_mobile_skin = '$it_mobile_skin' ";
if(is_checked('chk_all_it_basic'))               $all_fields .= " , it_basic = '$it_basic' ";
if(is_checked('chk_all_it_order'))               $all_fields .= " , it_order = '$it_order' ";
if(is_checked('chk_all_it_type'))                $all_fields .= " , it_type1 = '$it_type1', it_type2 = '$it_type2', it_type3 = '$it_type3', it_type4 = '$it_type4', it_type5 = '$it_type5', it_type6 = '$it_type6', it_type7 = '$it_type7', it_type8 = '$it_type8', it_type9 = '$it_type9', it_type10 = '$it_type10', it_type11 = '$it_type11', it_type12 = '$it_type12', it_type13 = '$it_type13', it_deadline = '$it_deadline' ";
if(is_checked('chk_all_it_maker'))               $all_fields .= " , it_maker = '$it_maker' ";
if(is_checked('chk_all_it_origin'))              $all_fields .= " , it_origin = '$it_origin' ";
if(is_checked('chk_all_it_brand'))               $all_fields .= " , it_brand = '$it_brand' ";
if(is_checked('chk_all_it_model'))               $all_fields .= " , it_model = '$it_model' ";
if(is_checked('chk_all_it_notax'))               $all_fields .= " , it_notax = '$it_notax' ";
if(is_checked('chk_all_it_sell_email'))          $all_fields .= " , it_sell_email = '$it_sell_email' ";
if(is_checked('chk_all_it_tel_inq'))             $all_fields .= " , it_tel_inq = '$it_tel_inq' ";
if(is_checked('chk_all_it_use'))                 $all_fields .= " , it_use = '$it_use' ";
if(is_checked('chk_all_it_use_partner'))         $all_fields .= " , it_use_partner = '$it_use_partner' ";
if(is_checked('chk_all_it_show_partner_search'))         $all_fields .= " , it_show_partner_search = '$it_show_partner_search' ";
if(is_checked('chk_all_it_use_short_barcode'))         $all_fields .= " , it_use_short_barcode = '$it_use_short_barcode' ";
if(is_checked('chk_all_prodassistingdevicescode'))         $all_fields .= " , prodassistingdevicescode = '$prodassistingdevicescode' ";
if(is_checked('chk_all_it_use_custom_order'))    $all_fields .= " , it_use_custom_order = '$it_use_custom_order' ";
if(is_checked('chk_all_it_nocoupon'))            $all_fields .= " , it_nocoupon = '$it_nocoupon' ";
if(is_checked('chk_all_it_soldout'))             $all_fields .= " , it_soldout = '$it_soldout' ";
if(is_checked('chk_all_it_info'))                $all_fields .= " , it_info_gubun = '$it_info_gubun', it_info_value = '$it_info_value' ";
if(is_checked('chk_all_it_price'))               $all_fields .= " , it_price = '$it_price' ";
if(is_checked('chk_all_it_cust_price'))          $all_fields .= " , it_cust_price = '$it_cust_price' ";
if(is_checked('chk_all_it_point'))               $all_fields .= " , it_point = '$it_point' ";
if(is_checked('chk_all_it_point_type'))          $all_fields .= " , it_point_type = '$it_point_type' ";
if(is_checked('chk_all_it_supply_point'))        $all_fields .= " , it_supply_point = '$it_supply_point' ";
if(is_checked('chk_all_it_stock_qty'))           $all_fields .= " , it_stock_qty = '$it_stock_qty' ";
if(is_checked('chk_all_it_noti_qty'))            $all_fields .= " , it_noti_qty = '$it_noti_qty' ";
if(is_checked('chk_all_it_sendcost'))            $all_fields .= " , it_sc_type = '$it_sc_type', it_sc_method = '$it_sc_method', it_sc_price = '$it_sc_price', it_sc_minimum = '$it_sc_minimum', it_sc_qty = '$it_sc_qty' ";
if(is_checked('chk_all_it_sendcost'))            $all_fields .= " , it_sc_type_partner = '$it_sc_type_partner', it_sc_method_partner = '$it_sc_method_partner', it_sc_price_partner = '$it_sc_price_partner', it_sc_minimum_partner = '$it_sc_minimum_partner', it_sc_qty_parnter = '$it_sc_qty_parnter' ";
if(is_checked('chk_all_it_buy_min_qty'))         $all_fields .= " , it_buy_min_qty = '$it_buy_min_qty' ";
if(is_checked('chk_all_it_buy_max_qty'))         $all_fields .= " , it_buy_max_qty = '$it_buy_max_qty' ";
if(is_checked('chk_all_it_head_html'))           $all_fields .= " , it_head_html = '$it_head_html' ";
if(is_checked('chk_all_it_tail_html'))           $all_fields .= " , it_tail_html = '$it_tail_html' ";
if(is_checked('chk_all_it_mobile_head_html'))    $all_fields .= " , it_mobile_head_html = '$it_mobile_head_html' ";
if(is_checked('chk_all_it_mobile_tail_html'))    $all_fields .= " , it_mobile_tail_html = '$it_mobile_tail_html' ";
if(is_checked('chk_all_1'))                      $all_fields .= " , it_1_subj = '$it_1_subj', it_1 = '$it_1' ";
if(is_checked('chk_all_2'))                      $all_fields .= " , it_2_subj = '$it_2_subj', it_2 = '$it_2' ";
if(is_checked('chk_all_3'))                      $all_fields .= " , it_3_subj = '$it_3_subj', it_3 = '$it_3' ";
if(is_checked('chk_all_4'))                      $all_fields .= " , it_4_subj = '$it_4_subj', it_4 = '$it_4' ";
if(is_checked('chk_all_5'))                      $all_fields .= " , it_5_subj = '$it_5_subj', it_5 = '$it_5' ";
if(is_checked('chk_all_6'))                      $all_fields .= " , it_6_subj = '$it_6_subj', it_6 = '$it_6' ";
if(is_checked('chk_all_7'))                      $all_fields .= " , it_7_subj = '$it_7_subj', it_7 = '$it_7' ";
if(is_checked('chk_all_8'))                      $all_fields .= " , it_8_subj = '$it_8_subj', it_8 = '$it_8' ";
if(is_checked('chk_all_9'))                      $all_fields .= " , it_9_subj = '$it_9_subj', it_9 = '$it_9' ";
if(is_checked('chk_all_10'))                     $all_fields .= " , it_10_subj = '$it_10_subj', it_10 = '$it_10' ";

if($all_fields) {
  sql_query(" update {$g5['g5_shop_item_table']} set it_name = it_name {$all_fields} ");
}

$qstr = "$qstr&amp;sca=$sca&amp;page=$page&searchProdSupYN=$searchProdSupYN";

if ($w == "u") {
  goto_url("./itemform.php?w=u&amp;it_id=$it_id&amp;fn=$fn&amp;$qstr");
}
/*
else if ($w == "d")  {
    $qstr = "ca_id=$ca_id&amp;sfl=$sfl&amp;sca=$sca&amp;page=$page&amp;stx=".urlencode($stx)."&amp;save_stx=".urlencode($save_stx);
    goto_url("./itemlist.php?$qstr");
}
*/

echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
?>
<script>
if (confirm("계속 입력하시겠습니까?")) {
  //location.href = "<?php echo "./itemform.php?it_id=$it_id&amp;sort1=$sort1&amp;sort2=$sort2&amp;sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page"?>";
  location.href = "<?php echo "./itemform.php?fn=".$fn."&".str_replace('&amp;', '&', $qstr); ?>";
} else {
  location.href = "<?php echo "./itemlist.php?".str_replace('&amp;', '&', $qstr); ?>";
}
</script>
