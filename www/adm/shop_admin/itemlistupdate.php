<?php
$sub_menu = '400300';
include_once('./_common.php');

check_demo();

check_admin_token();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

// 입고예정일 수정한 상품 목록
$affected_it_ids = [];

if ($_POST['act_button'] == "선택수정") {
	$tex = "수정";
    auth_check($auth[$sub_menu], 'w');

    for ($i=0; $i<count($_POST['chk']); $i++) {

        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        if( ! $_POST['ca_id'][$k]) {
            alert("기본분류는 반드시 선택해야 합니다.");
        }

        $sql = "select it_expected_warehousing_date from {$g5['g5_shop_item_table']} where it_id   = '".preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$k])."' ";
        $rs = sql_fetch($sql);
        $o_it_expected_warehousing_date = $rs['it_expected_warehousing_date'];

        $p_ca_id = is_array($_POST['ca_id']) ? strip_tags($_POST['ca_id'][$k]) : '';
        $p_ca_id2 = is_array($_POST['ca_id2']) ? strip_tags($_POST['ca_id2'][$k]) : '';
        $p_ca_id3 = is_array($_POST['ca_id3']) ? strip_tags($_POST['ca_id3'][$k]) : '';
        $p_ca_id4 = is_array($_POST['ca_id4']) ? strip_tags($_POST['ca_id4'][$k]) : '';
        $p_ca_id5 = is_array($_POST['ca_id5']) ? strip_tags($_POST['ca_id5'][$k]) : '';
        $p_ca_id6 = is_array($_POST['ca_id6']) ? strip_tags($_POST['ca_id6'][$k]) : '';
        $p_ca_id7 = is_array($_POST['ca_id7']) ? strip_tags($_POST['ca_id7'][$k]) : '';
        $p_ca_id8 = is_array($_POST['ca_id8']) ? strip_tags($_POST['ca_id8'][$k]) : '';
        $p_ca_id9 = is_array($_POST['ca_id9']) ? strip_tags($_POST['ca_id9'][$k]) : '';
        $p_ca_id10 = is_array($_POST['ca_id10']) ? strip_tags($_POST['ca_id10'][$k]) : '';

        $p_it_name = is_array($_POST['it_name']) ? strip_tags(clean_xss_attributes($_POST['it_name'][$k])) : '';
        $p_it_cust_price = is_array($_POST['it_cust_price']) ? strip_tags($_POST['it_cust_price'][$k]) : '';
        $p_it_price = is_array($_POST['it_price']) ? strip_tags($_POST['it_price'][$k]) : '';
        $p_it_price_partner = is_array($_POST['it_price_partner']) ? strip_tags($_POST['it_price_partner'][$k]) : '';
        $p_it_price_dealer = is_array($_POST['it_price_dealer']) ? strip_tags($_POST['it_price_dealer'][$k]) : '';
        $p_it_price_dealer2 = is_array($_POST['it_price_dealer2']) ? strip_tags($_POST['it_price_dealer2'][$k]) : '';
        //$p_it_skin = is_array($_POST['it_skin']) ? strip_tags($_POST['it_skin'][$k]) : '';
        //$p_it_mobile_skin = is_array($_POST['it_mobile_skin']) ? strip_tags($_POST['it_mobile_skin'][$k]) : '';
        $p_it_use = is_array($_POST['it_use']) ? strip_tags($_POST['it_use'][$k]) : '';
        $p_it_use_partner = is_array($_POST['it_use_partner']) ? strip_tags($_POST['it_use_partner'][$k]) : '';
        $p_it_soldout = is_array($_POST['it_soldout']) ? strip_tags($_POST['it_soldout'][$k]) : '';
        $p_it_order = is_array($_POST['it_order']) ? strip_tags($_POST['it_order'][$k]) : '';

		$p_pt_commission = is_array($_POST['pt_commission']) ? strip_tags($_POST['pt_commission'][$k]) : '';
		$p_pt_incentive = is_array($_POST['pt_incentive']) ? strip_tags($_POST['pt_incentive'][$k]) : '';
		$p_pt_it = is_array($_POST['pt_it']) ? strip_tags($_POST['pt_it'][$k]) : '';
		$p_pt_main = is_array($_POST['pt_main']) ? strip_tags($_POST['pt_main'][$k]) : '';
        $p_pt_comment_use = is_array($_POST['pt_comment_use']) ? strip_tags($_POST['pt_comment_use'][$k]) : '';
        
        $p_it_type1 = is_array($_POST['it_type1']) ? strip_tags($_POST['it_type1'][$k]) : '';
        $p_it_type2 = is_array($_POST['it_type2']) ? strip_tags($_POST['it_type2'][$k]) : '';
        $p_it_type3 = is_array($_POST['it_type3']) ? strip_tags($_POST['it_type3'][$k]) : '';
        $p_it_type4 = is_array($_POST['it_type4']) ? strip_tags($_POST['it_type4'][$k]) : '';
        $p_it_type5 = is_array($_POST['it_type5']) ? strip_tags($_POST['it_type5'][$k]) : '';
        $p_it_type6 = is_array($_POST['it_type6']) ? strip_tags($_POST['it_type6'][$k]) : '';
        $p_it_type7 = is_array($_POST['it_type7']) ? strip_tags($_POST['it_type7'][$k]) : '';
        $p_it_type8 = is_array($_POST['it_type8']) ? strip_tags($_POST['it_type8'][$k]) : '';
        $p_it_type9 = is_array($_POST['it_type9']) ? strip_tags($_POST['it_type9'][$k]) : '';
        $p_it_type10 = is_array($_POST['it_type10']) ? strip_tags($_POST['it_type10'][$k]) : '';

        $it_model = is_array($_POST['it_model']) ? strip_tags($_POST['it_model'][$k]) : '';
        $it_expected_warehousing_date = is_array($_POST['it_expected_warehousing_date']) ? strip_tags($_POST['it_expected_warehousing_date'][$k]) : '';
        /*
        $sql = "update {$g5['g5_shop_item_table']}
                   set ca_id          = '".sql_real_escape_string($p_ca_id)."',
                       ca_id2         = '".sql_real_escape_string($p_ca_id2)."',
                       ca_id3         = '".sql_real_escape_string($p_ca_id3)."',
                       it_name        = '".$p_it_name."',
                       it_cust_price  = '".sql_real_escape_string($p_it_cust_price)."',
                       it_price       = '".sql_real_escape_string($p_it_price)."',
                       it_stock_qty   = '".sql_real_escape_string($p_it_stock_qty)."',
                       it_use         = '".sql_real_escape_string($p_it_use)."',
                       it_use_partner = '".sql_real_escape_string($p_it_use_partner)."',
                       it_soldout     = '".sql_real_escape_string($p_it_soldout)."',
                       it_order       = '".sql_real_escape_string($p_it_order)."',
                       pt_commission  = '".sql_real_escape_string($p_pt_commission)."',
					   pt_incentive   = '".sql_real_escape_string($p_pt_incentive)."',
					   pt_it		  = '".sql_real_escape_string($p_pt_it)."',
					   pt_main		  = '".sql_real_escape_string($p_pt_main)."',
                       pt_comment_use = '".sql_real_escape_string($p_pt_comment_use)."',
                       it_type1       = '".sql_real_escape_string($p_it_type1)."',
                       it_type2       = '".sql_real_escape_string($p_it_type2)."',
                       it_type3       = '".sql_real_escape_string($p_it_type3)."',
                       it_type4       = '".sql_real_escape_string($p_it_type4)."',
                       it_type5       = '".sql_real_escape_string($p_it_type5)."',
					   it_update_time = '".G5_TIME_YMDHIS."'
                 where it_id   = '".preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$k])."' "; // APMS - 2014.07.20
        */
        $sql = "update {$g5['g5_shop_item_table']}
        set ca_id          = '".sql_real_escape_string($p_ca_id)."',
            ca_id2         = '".sql_real_escape_string($p_ca_id2)."',
            ca_id3         = '".sql_real_escape_string($p_ca_id3)."',
            ca_id4         = '".sql_real_escape_string($p_ca_id4)."',
            ca_id5         = '".sql_real_escape_string($p_ca_id5)."',
            ca_id6         = '".sql_real_escape_string($p_ca_id6)."',
            ca_id7         = '".sql_real_escape_string($p_ca_id7)."',
            ca_id8         = '".sql_real_escape_string($p_ca_id8)."',
            ca_id9         = '".sql_real_escape_string($p_ca_id9)."',
            ca_id10        = '".sql_real_escape_string($p_ca_id10)."',
            it_name        = '".$p_it_name."',
            it_price       = '".sql_real_escape_string($p_it_price)."',
            it_cust_price       = '".sql_real_escape_string($p_it_cust_price)."',
            it_price_partner       = '".sql_real_escape_string($p_it_price_partner)."',
            it_price_dealer       = '".sql_real_escape_string($p_it_price_dealer)."',
            it_price_dealer2       = '".sql_real_escape_string($p_it_price_dealer2)."',
            it_use         = '".sql_real_escape_string($p_it_use)."',
            it_use_partner = '".sql_real_escape_string($p_it_use_partner)."',
            it_soldout     = '".sql_real_escape_string($p_it_soldout)."',
            it_order       = '".sql_real_escape_string($p_it_order)."',
            pt_it		  = '".sql_real_escape_string($p_pt_it)."',
            pt_main		  = '".sql_real_escape_string($p_pt_main)."',
            it_type1       = '".sql_real_escape_string($p_it_type1)."',
            it_type2       = '".sql_real_escape_string($p_it_type2)."',
            it_type3       = '".sql_real_escape_string($p_it_type3)."',
            it_type4       = '".sql_real_escape_string($p_it_type4)."',
            it_type5       = '".sql_real_escape_string($p_it_type5)."',
            it_type6       = '".sql_real_escape_string($p_it_type6)."',
            it_type7       = '".sql_real_escape_string($p_it_type7)."',
            it_type8       = '".sql_real_escape_string($p_it_type8)."',
            it_type9       = '".sql_real_escape_string($p_it_type9)."',
            it_type10       = '".sql_real_escape_string($p_it_type10)."',
            it_model       = '".sql_real_escape_string($it_model)."',
            it_expected_warehousing_date       = '".sql_real_escape_string($it_expected_warehousing_date)."',
            it_update_time = '".G5_TIME_YMDHIS."'
        where it_id   = '".preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$k])."' "; // APMS - 2014.07.20
        sql_query($sql);

        // 입고예정일 변경시 g5_alimtalk al_id=3 에 알림톡 보내기
        if ($o_it_expected_warehousing_date !== $it_expected_warehousing_date) {
            $affected_it_ids[] = preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$k]);

            $sql = "select m.* 
            from g5_alimtalk_member a 
            left join g5_member m on a.mb_id = m.mb_id 
            where al_id = '3' 
            order by a.mb_id asc";
            $mb_result = sql_query($sql, true);

            if (empty($it_expected_warehousing_date)) {
                $it_expected_warehousing_date = '정상출고 가능';
            }
            $url = "https://eroumcare.com/shop/list_oos.php?ca_id=10&sort=custom";

            while($mb = sql_fetch_array($mb_result)) {
                $msg = "[이로움 긴급공지 안내]\n{$mb['mb_name']} 님,\n이로움 유통상품 중 현재 공급이 원활하지 않은 상품을 안내 드립니다.\n주문시 참고하여 주시기 바랍니다.\n\n■ 상품명 : {$p_it_name}\n■ 입고예정일 : {$it_expected_warehousing_date}";
                $num = $mb['mb_hp'];
                send_alim_talk('ENT_STO_'.$mb['mb_id'], $num, 'ent_stock_date_btn', $msg,
                    [
                        'button' => [
                            [
                                'name' => '품절상품 전체 확인하기',
                                'type' => 'WL',
                                'url_mobile' => $url,
                                'url_pc' => $url
                            ]
                        ]
                    ]
                );
            }
        }
    }
} else if ($_POST['act_button'] == "선택삭제") {
	$tex = "삭제";
    // if ($is_admin != 'super')
    //     alert('상품 삭제는 최고관리자만 가능합니다.');

    auth_check($auth[$sub_menu], 'd');

    // _ITEM_DELETE_ 상수를 선언해야 itemdelete.inc.php 가 정상 작동함
    define('_ITEM_DELETE_', true);

	// APMS - 2014.07.20
	include_once('../apms_admin/apms.config.php');

    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        // include 전에 $it_id 값을 반드시 넘겨야 함
        $it_id = preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$k]);
        include ('./itemdelete.inc.php');
    }
}

$it_ids = '';
foreach($affected_it_ids as $it_id) {
    $it_ids .= '&it_id%5B%5D=' . $it_id;
}

$searchProdSupYN=$_POST['searchProdSupYN'];
alert("선택 상품이 정상적으로 ".$tex." 되었습니다.","./itemlist.php?sca=$sca&amp;sst=$sst&amp;page_rows=$page_rows&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;page=$page&searchProdSupYN={$searchProdSupYN}{$it_ids}");
//goto_url("./itemlist.php?sca=$sca&amp;sst=$sst&amp;page_rows=$page_rows&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;page=$page&searchProdSupYN={$searchProdSupYN}{$it_ids}");
?>
