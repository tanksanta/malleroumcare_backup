<?php
include_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
include_once('./adm/apms_admin/apms.admin.lib.php');

header('Content-Type: application/json');

/* 외부이미지 저장 */
function FileSave($FileLink, $dir){

    if(!is_dir($dir)) {
        @mkdir($dir, G5_DIR_PERMISSION);
        @chmod($dir, G5_DIR_PERMISSION);
    }

    $PhotoInfo = pathinfo($FileLink);
    $PhotoName[] = md5($PhotoInfo['filename'])."_".time();
    $PhotoName[] = $PhotoInfo['extension'];
    $PhotoName = implode(".", $PhotoName);
    $Curl = curl_init();
    curl_setopt($Curl, CURLOPT_URL, $FileLink);
    curl_setopt($Curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($Curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($Curl, CURLOPT_SSL_VERIFYHOST, 0);
    $Result = curl_exec($Curl);
    $FileSave = fopen($dir.'/'.$PhotoName, 'a');
    fwrite($FileSave, $Result);
    fclose($FileSave);

	$file = str_replace(G5_DATA_PATH.'/item/', '', $dir.'/'.$PhotoName);
	return $file;
}

/*
$_POST['pt_it'] = 1;					//상품종류 - 1: 일반상품(배송가능), 2: 컨텐츠상품(배송불가)
$_POST['ca_id'] = '1080';				//상품분류
$_POST['it_id'] = ($_POST['it_id'])?$_POST['it_id']:time(); //상품코드
$_POST['it_thezone'] = 'it_thezone';	//상품코드
$_POST['it_name'] = '상품명';			//상품명
$_POST['it_basic'] = '기본설명';		//기본설명

$_POST['it_type1'] = 1;					//상품테그 - 메인상품(기본값:0, 선택:1)
$_POST['it_type2'] = 0;					//상품테그 - 대여상품(기본값:0, 선택:1)
$_POST['it_type3'] = 0;					//상품테그 - 주문상품(기본값:0, 선택:1)
$_POST['it_type4'] = 0;					//상품테그 - 상담상품(기본값:0, 선택:1)
$_POST['it_type5'] = 0;					//상품테그 - 택배상품(기본값:0, 선택:1)
$_POST['pt_main'] = 0;					//상품테그 - 메인(기본값:0, 선택:1)

$_POST['it_maker'] = '';				//제조사
$_POST['it_origin'] = '';				//원산지
$_POST['it_brand'] = '';				//브랜드
$_POST['it_model'] = '모델';			//모델

$_POST['it_option_subject'] = '옵션명'; //옵션명
$_POST['it_explan'] = '상품설명';		//상품설명

$_POST['it_price'] = '1500000';			//판매가격
$_POST['it_price_partner'] = '1200000'; //파트너몰 판매가격
$_POST['it_price_dealer'] = '1274900';	//딜러가격
$_POST['it_price_dealer2'] = '1274900'; //딜러가격

$_POST['it_use'] = 1;					//상품판매

$_POST['it_use_custom_order'] = 1;		//주문제작 가능


$_POST['it_stock_qty'] = 99999;			//재고수량

$_POST['it_info_gubun'] = 'it_info_gubun';	//상품요약
$_POST['it_info_value'] = 'a:8:{s:8:"material";s:28:"상세설명페이지 참고";s:5:"color";s:28:"상세설명페이지 참고";s:4:"size";s:28:"상세설명페이지 참고";s:5:"maker";s:28:"상세설명페이지 참고";s:7:"caution";s:28:"상세설명페이지 참고";s:16:"manufacturing_ym";s:28:"상세설명페이지 참고";s:8:"warranty";s:28:"상세설명페이지 참고";s:2:"as";s:28:"상세설명페이지 참고";}';	//상품요약



$_POST['it_img1'] = '1609479865/1.png';			//이미지
$_POST['pt_thumb'] = 'https://eroumcare.com/data/item/1609479865/1.png';			//이미지
*/


//상품분류
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


if (!$_POST['pt_it'])
	die(json_encode(array('error' => '서비스종류를 선택해 주십시오.')));

if(in_array($pt_it, $g5['apms_automation'])) {
	$it_sc_type = 1;
	$it_sc_method = 0;
	$it_sc_price = 0;
	$it_sc_minimum = 0;
	$it_sc_qty = 0;
}





$it_img_dir = G5_DATA_PATH.'/item';


// 파일정보
if($w == "u") {
    $sql = " select it_img1, it_img2, it_img3, it_img4, it_img5, it_img6, it_img7, it_img8, it_img9, it_img10
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
if ($_POST['it_img1']) {
    $it_img1 = FileSave($_POST['it_img1'], $it_img_dir.'/'.$it_id);
}
if ($_POST['it_img2']) {
    $it_img2 = FileSave($_POST['it_img2'], $it_img_dir.'/'.$it_id);
}
if ($_POST['it_img3']) {
    $it_img3 = FileSave($_POST['it_img3'], $it_img_dir.'/'.$it_id);
}
if ($_POST['it_img4']) {
    $it_img4 = FileSave($_POST['it_img4'], $it_img_dir.'/'.$it_id);
}
if ($_POST['it_img5']) {
    $it_img5 = FileSave($_POST['it_img5'], $it_img_dir.'/'.$it_id);
}
if ($_POST['it_img6']) {
    $it_img6 = FileSave($_POST['it_img6'], $it_img_dir.'/'.$it_id);
}
if ($_POST['it_img7']) {
    $it_img7 = FileSave($_POST['it_img7'], $it_img_dir.'/'.$it_id);
}
if ($_POST['it_img8']) {
    $it_img8 = FileSave($_POST['it_img8'], $it_img_dir.'/'.$it_id);
}
if ($_POST['it_img9']) {
    $it_img9 = FileSave($_POST['it_img9'], $it_img_dir.'/'.$it_id);
}
if ($_POST['it_img10']) {
    $it_img10 = FileSave($_POST['it_img10'], $it_img_dir.'/'.$it_id);
}






if($it['it_id']) {
    $opt_subject = explode(',', $it['it_option_subject']);
    $opt1_subject = $opt_subject[0];
    $opt2_subject = $opt_subject[1];
    $opt3_subject = $opt_subject[2];

    $sql = " select * from {$g5['g5_shop_item_option_table']} where io_type = '0' and it_id = '{$it['it_id']}' order by io_no asc ";
    $result = sql_query($sql);
    if(sql_num_rows($result))
        $po_run = true;
} else {
    $opt1_subject = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1_subject'])));
    $opt2_subject = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2_subject'])));
    $opt3_subject = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3_subject'])));

    $opt1_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1'])));
    $opt2_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2'])));
    $opt3_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3'])));

	$opt1_price_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1_price'])));
	$opt1_price_partner_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1_price_partner'])));
	$opt1_price_deale_valr = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1_price_dealer'])));
	$opt1_price_dealer2_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1_price_dealer2'])));
	$opt1_stock_qty_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1_stock_qty'])));
	$opt1_noti_qty_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1_noti_qty'])));
	$opt1_use_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1_use'])));
	$opt1_thezone_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1_thezone'])));

	$opt2_price_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2_price'])));
	$opt2_price_partner_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2_price_partner'])));
	$opt2_price_dealer_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2_price_dealer'])));
	$opt2_price_dealer2_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2_price_dealer2'])));
	$opt2_stock_qty_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2_stock_qty'])));
	$opt2_noti_qty_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2_noti_qty'])));
	$opt2_use_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2_use'])));
	$opt2_thezone_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2_thezone'])));

	$opt3_price_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3_price'])));
	$opt3_price_partner_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3_price_partner'])));
	$opt3_price_dealer_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3_price_dealer'])));
	$opt3_price_dealer2_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3_price_dealer2'])));
	$opt3_stock_qty_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3_stock_qty'])));
	$opt3_noti_qty_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3_noti_qty'])));
	$opt3_use_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3_use'])));
	$opt3_thezone_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3_thezone'])));

    if(!$opt1_subject || !$opt1_val) {
        echo '옵션1과 옵션1 항목을 입력해 주십시오.';
        exit;
    }

    $po_run = true;

    $opt1_count = $opt2_count = $opt3_count = 0;

    if($opt1_val) {
        $opt1 = explode(',', $opt1_val);
		$opt1_price = explode(',', $opt1_price_val);
		$opt1_price_partner = explode(',', $opt1_price_partner_val);
		$opt1_price_dealer = explode(',', $opt1_price_dealer_val);
		$opt1_price_dealer2 = explode(',', $opt1_price_dealer2_val);
		$opt1_stock_qty = explode(',', $opt1_stock_qty_val);
		$opt1_noti_qty = explode(',', $opt1_noti_qty_val);
		$opt1_use = explode(',', $opt1_use_val);
		$opt1_thezone = explode(',', $opt1_thezone_val);

        $opt1_count = count($opt1);
    }

    if($opt2_val) {
        $opt2 = explode(',', $opt2_val);
		$opt2_price = explode(',', $opt2_price_val);
		$opt2_price_partner = explode(',', $opt2_price_partner_val);
		$opt2_price_dealer = explode(',', $opt2_price_dealer_val);
		$opt2_price_dealer2 = explode(',', $opt2_price_dealer2_val);
		$opt2_stock_qty = explode(',', $opt2_stock_qty_val);
		$opt2_noti_qty = explode(',', $opt2_noti_qty_val);
		$opt2_use = explode(',', $opt2_use_val);
		$opt2_thezone = explode(',', $opt2_thezone_val);

        $opt2_count = count($opt2);
    }

    if($opt3_val) {
        $opt3 = explode(',', $opt3_val);
		$opt3_price = explode(',', $opt3_price_val);
		$opt3_price_partner = explode(',', $opt3_price_partner_val);
		$opt3_price_dealer = explode(',', $opt3_price_dealer_val);
		$opt3_price_dealer2 = explode(',', $opt3_price_dealer2_val);
		$opt3_stock_qty = explode(',', $opt3_stock_qty_val);
		$opt3_noti_qty = explode(',', $opt3_noti_qty_val);
		$opt3_use = explode(',', $opt3_use_val);
		$opt3_thezone = explode(',', $opt3_thezone_val);

        $opt3_count = count($opt3);
    }
}


if($it['it_id']) {

}else{

		$option_id = array();
		$option_price = array();
		$option_stock_qty = array();
		$option_noti_qty = array();
		$option_use = array();
		$option_price_partner = array();
		$option_price_dealer = array();
		$option_price_dealer2 = array();
		$option_thezone = array();

        for($i=0; $i<$opt1_count; $i++) {
            $j = 0;
            do {
                $k = 0;
                do {
                    $opt_1 = strip_tags(trim($opt1[$i]));
                    $opt_2 = strip_tags(trim($opt2[$j]));
                    $opt_3 = strip_tags(trim($opt3[$k]));

                    $opt_2_len = strlen($opt_2);
                    $opt_3_len = strlen($opt_3);

                    $opt_id = $opt_1;

					$opt_1_price = strip_tags(trim($opt1_price[$i]));
					$opt_1_price_partner = strip_tags(trim($opt1_price_partner[$i]));
					$opt_1_price_dealer = strip_tags(trim($opt1_price_dealer[$i]));
					$opt_1_price_dealer2 = strip_tags(trim($opt1_price_dealer2[$i]));
					$opt_1_stock_qty = strip_tags(trim($opt1_stock_qty[$i]));
					$opt_1_noti_qty = strip_tags(trim($opt1_noti_qty[$i]));
					$opt_1_use = strip_tags(trim($opt1_use[$i]));
					$opt_1_thezone = strip_tags(trim($opt1_thezone[$i]));



                    if($opt_2_len){
                        $opt_id .= chr(30).$opt_2;
					}
                    if($opt_3_len){
                        $opt_id .= chr(30).$opt_3;
					}


                    $opt_price = 0;
                    $opt_stock_qty = 9999;
                    $opt_noti_qty = 100;
                    $opt_use = 1;
                    $opt_thezone = '';
                    $opt_price_partner = 0;
                    $opt_price_dealer = 0;
                    $opt_price_dealer2 = 0;

                    // 기존에 설정된 값이 있는지 체크
                    if($_POST['w'] == 'u') {
                        $sql = " select io_price, io_stock_qty, io_noti_qty, io_use
                                    from {$g5['g5_shop_item_option_table']}
                                    where it_id = '{$_POST['it_id']}'
                                      and io_id = '$opt_id'
                                      and io_type = '0' ";
                        $row = sql_fetch($sql);

                        if($row) {
                            $opt_price = (int)$row['io_price'];
                            $opt_stock_qty = (int)$row['io_stock_qty'];
                            $opt_noti_qty = (int)$row['io_noti_qty'];
                            $opt_use = (int)$row['io_use'];
                        }
                    }

					$option_id[] = $opt_id;
					$option_price[] = ($opt_1_price)?$opt_1_price:$opt_price;
					$option_stock_qty[] = ($opt_1_stock_qty)?$opt_1_stock_qty:$opt_stock_qty;
					$option_noti_qty[] = ($opt_1_noti_qty)?$opt_1_noti_qty:$opt_noti_qty;
					$option_use[] = ($opt_1_use)?$opt_1_use:$opt_use;
					$option_price_partner[] = ($opt_1_price_partner)?$opt_1_price_partner:$opt_price_partner;
					$option_price_dealer[] = ($opt_1_price_dealer)?$opt_1_price_dealer:$opt_price_dealer;
					$option_price_dealer2[] = ($opt_1_price_dealer2)?$opt_1_price_dealer2:$opt_price_dealer2;
					$option_thezone[] = ($opt_1_thezone)?$opt_1_thezone:$opt_thezone;


                    $k++;
                } while($k < $opt3_count);

                $j++;
            } while($j < $opt2_count);
        } // for

}




$option_count = (isset($option_id) && is_array($option_id)) ? count($option_id) : array();
if($option_count) {
    // 옵션명
    $opt1_cnt = $opt2_cnt = $opt3_cnt = 0;
    for($i=0; $i<$option_count; $i++) {
        $option_id[$i] = preg_replace(G5_OPTION_ID_FILTER, '', strip_tags($option_id[$i]));

		$opt_val = explode(chr(30), $option_id[$i]);
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





// 상품요약정보
$it_info_gubun = 'wear';	//상품요약
$it_info_value = 'a:8:{s:8:"material";s:28:"상세설명페이지 참고";s:5:"color";s:28:"상세설명페이지 참고";s:4:"size";s:28:"상세설명페이지 참고";s:5:"maker";s:28:"상세설명페이지 참고";s:7:"caution";s:28:"상세설명페이지 참고";s:16:"manufacturing_ym";s:28:"상세설명페이지 참고";s:8:"warranty";s:28:"상세설명페이지 참고";s:2:"as";s:28:"상세설명페이지 참고";}';	//상품요약

// 포인트 비율 값 체크
if(($it_point_type == 1 || $it_point_type == 2) && $it_point > 99)
    die(json_encode(array('error' => '포인트 비율을 0과 99 사이의 값으로 입력해 주십시오.')));


$it_name = strip_tags(clean_xss_attributes(trim($_POST['it_name'])));


// KVE-2019-0708
$check_sanitize_keys = array(
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
'it_sc_add_sendcost',           // 산간지역 추가 배송비
'it_sc_add_sendcost_partner'    // 파트너 산간지역 추가 배송비
);

foreach( $check_sanitize_keys as $key ){
    $$key = isset($_POST[$key]) ? strip_tags(clean_xss_attributes($_POST[$key])) : '';
}

if ($it_name == "")
    die(json_encode(array('error' => '제목 또는 상품명을 입력해 주십시오.')));

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

$sql_common = " ca_id               = '$ca_id',
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
                it_basic            = '$it_basic',
                it_explan           = '$it_explan',
                it_explan2          = '".strip_tags(clean_xss_attributes(trim($_POST['it_explan'])))."',
                it_mobile_explan    = '$it_mobile_explan',
                it_reference        = '$it_reference',
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

		prodId				= '$prodId',
                gubun               = '$gubun',
                prodNm				= '$prodNm',
                itemId				= '$itemId',
                subItem				= '$subItem',
                prodSupPrice		= '$prodSupPrice',
                prodOflPrice		= '$prodOflPrice',
                ProdPayCode			= '$ProdPayCode',
                supId               = '$supId',
                prodColor			= '$prodColor',
                prodSym				= '$prodSym',
                prodWeig			= '$prodWeig',
                prodSize			= '$prodSize',
                prodQty				= '$prodQty',
                prodDetail			= '$prodDetail',
                regDtm				= '$regDtm',
                regUsrId			= '$regUsrId',
                regUsrIp			= '$regUsrIp',
                supNm               = '$supNm',
                prodImgAttr			= '$prodImgAttr',

                pt_it				= '$pt_it',
                pt_id				= '$pt_id',
                pt_img				= '$pt_img',
                pt_ccl				= '$pt_ccl',
                pt_main				= '$pt_main',
                pt_point			= '$pt_point',
				pt_order			= '$pt_order',
                pt_show				= '$pt_show',
				pt_tag				= '$pt_tag',
                pt_link1			= '$pt_link1',
                pt_link2			= '$pt_link2',
				pt_marketer			= '$pt_marketer',
				pt_review_use		= '$pt_review_use',
				pt_comment_use		= '$pt_comment_use',
				pt_day				= '$pt_day',
				pt_end				= '$pt_end',
				pt_reserve			= '$pt_reserve',
				pt_reserve_use		= '$pt_reserve_use',
				$pt_syndi_sql
				pt_explan	        = '$pt_explan',
                pt_mobile_explan    = '$pt_mobile_explan',
				pt_msg1			    = '$pt_msg1',
                pt_msg2			    = '$pt_msg2',
                pt_msg3			    = '$pt_msg3',
                it_thezone          = '$it_thezone',
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
                it_type             = '$it_type'
				"; // APMS : 2014.07.20

                // it_outsourcing_use  = '$it_outsourcing_use',
                // it_outsourcing_company  = '$it_outsourcing_company',
                // it_outsourcing_manager  = '$it_outsourcing_manager',
                // it_outsourcing_email    = '$it_outsourcing_email',
                // it_outsourcing_option   = '$it_outsourcing_option',










if ($w == "")
{
    $it_id = $_POST['it_id'];

    if (!trim($it_id)) {
		die(json_encode(array('error' => '코드가 없으므로 추가하실 수 없습니다.')));
    }

    $t_it_id = preg_replace("/[A-Za-z0-9\-_]/", "", $it_id);
    if($t_it_id)
		die(json_encode(array('error' => '코드는 영문자, 숫자, -, _ 만 사용할 수 있습니다.')));

	$pt_num = time();
    $sql_common .= " , it_time = '".G5_TIME_YMDHIS."' ";
    $sql_common .= " , it_update_time = '".G5_TIME_YMDHIS."' ";
    $sql = " insert {$g5['g5_shop_item_table']}
                set it_id = '$it_id',
					pt_num = '$pt_num',
					$sql_common	";

    if(sql_query($sql)){

		// 선택옵션등록
		if($option_count) {
			$comma = '';
			$sql2 = " INSERT INTO {$g5['g5_shop_item_option_table']}
							( `io_id`, `io_type`, `it_id`, `io_price`, `io_stock_qty`, `io_noti_qty`, `io_use`, `io_price_partner`, `io_price_dealer`, `io_price_dealer2`, `io_thezone` )
						VALUES ";
			for($i=0; $i<$option_count; $i++) {
				$sql2 .= $comma . " ( '{$option_id[$i]}', '0', '$it_id', '{$option_price[$i]}', '{$option_stock_qty[$i]}', '{$option_noti_qty[$i]}', '{$option_use[$i]}', '{$option_price_partner[$i]}', '{$option_price_dealer[$i]}', '{$option_price_dealer2[$i]}', '{$option_thezone[$i]}' )";
				$comma = ' , ';
			}

			sql_query($sql2);
		}

		$ret = array(
			'prodId'		=> $it_id,				//제품아이디
			'gubun'			=> $ca_id,				//구분 ("00")
			'prodNm'		=> $it_name,			//제품 명
			'itemId'		=> $it_id,				//품목 아이디
			'subItem'		=> '',					//하위품목
			'prodSupPrice'	=> $it_price,			//공급가격
			'prodOflPrice'	=> $it_price,			//판매금액
			'ProdPayCode'	=> '',					//급여코드
			'supId'			=> '',					//공급업체 아이디
			'prodColor'		=> $it_option_subject,	//색상 (ex “빨강|파랑|노랑” )
			'prodSym'		=> '',					//재질
			'prodWeig'		=> '',					//중량
			'prodSize'		=> '',					//사이즈
			'prodQty'		=> $it_stock_qty,		//주문가능수량
			'prodDetail'	=> $it_explan,			//상세정보
			'regDtm'		=> '',					//최초등록일시
			'regUsrId'		=> '',					//최초등록자 ID
			'regUsrIp'		=> '',					//최초등록자 IP (IPV6 포함 총 39자리)
			'supNm'			=> '',					//공급업체 이름
			'prodImgAttr'	=> '',					//[이미지 첨부파일 이름들]
			'file1'			=> $it_img1,			//[첫번쨰 이미지 파일]
			'file2'			=> $it_img2,			//[두번째 이미지 파일]
			'file3'			=> $it_img3,			//[두번째 이미지 파일]
			'file4'			=> $it_img4,			//[두번째 이미지 파일]
			'file5'			=> $it_img5,			//[두번째 이미지 파일]
			'file6'			=> $it_img6,			//[두번째 이미지 파일]
			'file7'			=> $it_img7,			//[두번째 이미지 파일]
			'file8'			=> $it_img8,			//[두번째 이미지 파일]
			'file9'			=> $it_img9,			//[두번째 이미지 파일]
			'file10'		=> $it_img10,			//[두번째 이미지 파일]
		);

		die(json_encode($ret));

	}else{
		die(json_encode(array('error' => '상품등록에 문제가 있습니다. 관리자에게 문의하세요.')));
	}
}
else if ($w == "u")
{
    $sql_common .= " , it_update_time = '".G5_TIME_YMDHIS."' ";
    $sql = " update {$g5['g5_shop_item_table']}
                set $sql_common
              where it_id = '$it_id' ";
    if(sql_query($sql)){

	}else{
		die(json_encode(array('error' => '상품수정에 문제가 있습니다. 관리자에게 문의하세요.')));
	}
}

?>
