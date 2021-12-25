<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

set_time_limit(0);
ini_set('memory_limit', '10000M');

Header("Content-type: application/vnd.ms-excel");
Header("Content-type: charset=utf-8");
header("Content-Disposition: attachment; filename=order_{$today}.xls");
Header("Content-Description: PHP3 Generated Data");
Header("Pragma: no-cache");
Header("Expires: 0");

echo "<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel;charset=utf-8\">";

function dz_add_oneline($color, $ccode, $regist_dates, $od_sales_manager_thezone, $due_dt, $sprice, $sprice2, $sprice3, $settleprice, $od_id, $card_nm, $settle_gb, $vat_fg, $remark_dc, $goods_code, $ct_qty, $nprice2, $nprice, $remarkd_dc, $sub_cnt, $end_email, $str_type, $so_nb) {

    $str     = "";
    $str	.= "<tr".$color.">";
    $str	.= "<td align='center' style=\"mso-number-format:'\@';\">".$ccode."</td>";
    $str	.= "<td align='center' style=\"mso-number-format:'\@';\">".$regist_dates."</td>";
    $str	.= "<td align='center'>".$od_sales_manager_thezone."</td>";
    $str	.= "<td align='center'>".$due_dt."</td>";
    $str	.= "<td align='center'>".$sprice."</td>";

    $str	.= "<td align='center'>".$sprice2."</td>";
    $str	.= "<td align='center'>".$sprice3."</td>";
    $str	.= "<td align='center'>".$settleprice."</td>";
    $str	.= "<td align='center' style=\"mso-number-format:'\@';\">".$so_nb."</td>";
    $str	.= "<td align='center'>".$card_nm."</td>";
    $str	.= "<td align='center'>".$settle_gb."</td>";

    $str	.= "<td align='center'>KRW</td>";
    $str	.= "<td align='center'>0</td>";
    $str	.= "<td align='center'>".$vat_fg."</td>";
    $str	.= "<td align='center'>".$remark_dc."</td>";
    $str	.= "<td align='center'></td>";

    $str	.= "<td align='center'>1</td>";
    $str	.= "<td align='center'>".$goods_code."</td>";
    $str	.= "<td align='center'>".$due_dt."</td>";
    $str	.= "<td align='center'>".$ct_qty."</td>";
    $str	.= "<td align='center'>".$nprice2."</td>";

    $str	.= "<td align='center'>".$nprice."</td>";
    $str	.= "<td align='center'>".$remarkd_dc."</td>";
    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'></td>";

    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'>".$sub_cnt."</td>";
    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'></td>";

    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'></td>";

    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'>".$end_email."</td>";
    $str	.= "<td align='center'></td>";
    $str	.= "<td align='center'></td>";

    $str	.= "<td align='center'>".$str_type."</td>";

    $str	.= "</tr>";

    return $str;
}

$today = date("YmdHis");

$where = array();
// $od_step = $od_step ? $od_step : 5;

$doc = strip_tags($doc);
$sort1 = in_array($sort1, array('od_id', 'od_cart_price', 'od_receipt_price', 'od_cancel_price', 'od_misu', 'od_cash')) ? $sort1 : '';
$sort2 = in_array($sort2, array('desc', 'asc')) ? $sort2 : 'desc';
$sel_field = get_search_string($sel_field);
if( !in_array($sel_field, array('od_all', 'it_name', 'od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'od_invoice')) ){   //검색할 필드 대상이 아니면 값을 제거
    $sel_field = '';
}
// $od_status = get_search_string($od_status);
$search = get_search_string($search);
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

$od_misu = preg_replace('/[^0-9a-z]/i', '', $od_misu);
$od_cancel_price = preg_replace('/[^0-9a-z]/i', '', $od_cancel_price);
$od_refund_price = preg_replace('/[^0-9a-z]/i', '', $od_refund_price);
$od_receipt_point = preg_replace('/[^0-9a-z]/i', '', $od_receipt_point);
$od_coupon = preg_replace('/[^0-9a-z]/i', '', $od_coupon); 

$sql_search = "";
if ($search != "") {
    $search = trim($search);
    if ($sel_field != "" && $sel_field != "od_all") {
        $where[] = " $sel_field like '%$search%' ";
    }

    if ($save_search != $search) {
        // $page = 1;
    }
}

// 전체 검색
if ($sel_field == 'od_all') {
    $sel_arr = array('it_name', 'o.od_id', 'o.mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'od_invoice');
    
    foreach ($sel_arr as $key => $value) {
        $sel_arr[$key] = "$value like '%$search%'";
    }
    
    $where[] = "(".implode(' or ', $sel_arr).")";
}

if ( $od_sales_manager ) {
    $where_od_sales_manager = array();
    for($i=0;$i<count($od_sales_manager);$i++) {
        $where_od_sales_manager[] = " o.od_sales_manager = '{$od_sales_manager[$i]}'";
    }
    if ( count($where_od_sales_manager) ) {
        $where[] = " ( " . implode(' OR ', $where_od_sales_manager) . " ) ";
    }
}

if ( $od_release_manager ) {
    $where_od_release_manager = array();
    for($i=0;$i<count($od_release_manager);$i++) {
        $where_od_release_manager[] = " o.od_release_manager = '{$od_release_manager[$i]}'";
    }
    if ( count($where_od_release_manager) ) {
        $where[] = " ( " . implode(' OR ', $where_od_release_manager) . " ) ";
    }
}

if ($partner_issue) {
    $where_partner_issue = array();
    for($i=0;$i<count($partner_issue);$i++) {
        $where_partner_issue[] = " pir.ir_is_issue_{$partner_issue[$i]} = TRUE ";
    }
    if ( count($where_partner_issue) ) {
        $where[] = " ( " . implode(' OR ', $where_partner_issue) . " ) ";
    }
}

if ( $price ) {
    $where[] = " (o.od_cart_price + o.od_send_cost + o.od_send_cost2 - o.od_cart_discount - o.od_cart_discount2 - o.od_sales_discount) BETWEEN '{$price_s}' AND '{$price_e}' ";
}

/*
if ($od_settle_case) {
    $where[] = " od_settle_case = '$od_settle_case' ";
}
*/

if ($od_settle_case) {
    if ( is_array($od_settle_case) ) {

        $od_settle_case_where = array();
        foreach($od_settle_case as $s) {
            $od_settle_case_where[] = " od_settle_case = '{$s}'";
        }
        $where[] = ' ( '.implode(' OR ', $od_settle_case_where).' ) ';
    } else {
        $where[] = " od_settle_case = '{$od_settle_case}'";
    }
}

if ($od_openmarket) {
    if ( is_array($od_openmarket) ) {

        $od_openmarket_where = array();
        foreach($od_openmarket as $s) {
		  if($s=="my"){
            $od_openmarket_where[] = " od_writer != 'openmarket'";
		  }else{
            $od_openmarket_where[] = " sabang_market = '{$s}'";
		  }
        }
        $where[] = ' ( '.implode(' OR ', $od_openmarket_where).' ) ';
    } else {
	  if($od_openmarket=="my"){
        $where[] = " od_writer != 'openmarket'";
	  }else{
        $where[] = " sabang_market = '{$od_openmarket}'";
	  }
    }
}

//// 등급 검색 ////
if ($member_level_s) {
    if ( is_array($member_level_s) ) {
        $member_level_s_where = array();
        foreach($member_level_s as $s) {
            $member_level_s_where[] = " mb_level = '{$s}'";
        }
        $temp_where[] = ' ( '.implode(' OR ', $member_level_s_where).' ) ';
    } else {
        $temp_where[] = " ( mb_level = '{$member_level_s}' )";
    }
}

if ($member_type_s) {
    if ( is_array($member_type_s) ) {
        $member_type_s_where = array();
        foreach($member_type_s as $s) {
            $member_type_s_where[] = " mb_type = '{$s}'";
        }
        $temp_where[] = ' ( '.implode(' OR ', $member_type_s_where).' ) ';
    } else {
        $temp_where[] = " ( mb_type = '{$member_type_s}' )";
    }
}

if ($is_member_s) {
    if ( is_array($is_member_s) ) {
        $is_member_s_where = array();
        foreach($is_member_s as $s) {
            $is_member_s_where[] = " mb_level is {$s}";
        }
        $temp_where[] = ' ( '.implode(' OR ', $is_member_s_where).' ) ';
    } else {
        $temp_where[] = " mb_level is {$is_member_s}";
    }
}

if ($temp_where) {
    foreach($temp_where as $s) {
        $where[] = ' ( '.implode(' OR ', $temp_where).' ) ';
    }
}
//////////////////

if ($od_misu) {
    $where[] = " o.od_misu != 0 ";
}

if ($od_cancel_price) {
    $where[] = " o.od_cancel_price != 0 ";
}

if ($od_refund_price) {
    $where[] = " o.od_refund_price != 0 ";
}

if ($od_receipt_point) {
    $where[] = " o.od_receipt_point != 0 ";
}

if ($od_coupon) {
    $where[] = " ( o.od_cart_coupon > 0 or o.od_coupon > 0 or o.od_send_coupon > 0 ) ";
}

if ($od_escrow) {
    $where[] = " o.od_escrow = 1 ";
}

if ($fr_date && $to_date) {
    $where[] = " {$sel_date_field} between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
}

if ($where) {
    $where2 = $where;
}

if ( $od_status ) {
    if ( is_array($od_status) ) {
        
        $order_steps_where = array();
        foreach($od_status as $s) {
            $order_steps_where[] = " o.od_status = '{$s}'";
        }
        $where[] = ' ( '.implode(' OR ', $order_steps_where).' ) ';
    }else{
        $where[] = " o.od_status = '{$od_status}'";
    }
}else{
    $order_steps_where = array();
    foreach($order_steps as $order_step) { 
        if (!$order_step['orderlist']) continue;

        $order_steps_where[] = " o.od_status = '{$order_step['val']}' ";
    }
    $where[] = ' ( '.implode(' OR ', $order_steps_where).' ) ';
}

// 최고관리자가 아닐때
if ( $od_status == '작성' && $is_admin != 'super' ) {
    $where[] = " o.od_writer = '{$member['mb_id']}' ";
}

$where[] = " (m.mb_intercept_date = '' OR m.mb_intercept_date IS NULL) ";

if ($where) {
    $sql_search = ' where '.implode(' and ', $where);
}

if ($sel_field == "")  $sel_field = "o.od_id";
if ($sort1 == "") $sort1 = "o.od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " $sql_search ";

foreach($order_steps as $order_step) { 
    if (!$order_step['orderlist']) continue;
    $order_by_steps[] = "'".$order_step['val']."'";
}

$order_by_step = implode(' , ', $order_by_steps);

$sql_common .= " ORDER BY o.od_id desc, FIELD(o.od_status, " . $order_by_step . " ) ";

$str	= "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"1\">";
$str	.= "<tr bgcolor=\"#eeeeee\">
                <th>고객코드</th>
                <th>주문일자</th>
                <th>담당자코드</th>
                <th>납기일</th>
                <th>공급가</th>

                <th>부가세</th>
                <th>합계액</th>
                <th>화면금액</th>
                <th>주문번호</th>
                <th>프로젝트코드</th>
                <th>관리구분코드</th>
                
                <th>환종</th>
                <th>거래구분</th>
                <th>과세구분</th>
                <th>비고(건)</th>
                <th>납품처코드</th>
                
                <th>단가구분</th>
                <th>품번</th>
                <th>출하예정일</th>
                <th>주문수량</th>
                <th>주문단가(부가세미포함)</th>

                <th>주문단가(부가세포함)</th>
                <th>비고(내역)</th>
                <th>외화단가</th>
                <th>외화금액</th>
                <th>검사구분</th>

                <th>단가유형</th>
                <th>LC번호</th>
                <th>주문순번</th>
                <th>견적번호</th>
                <th>견적순번</th>

                <th>모품목코드</th>
                <th>거래처명</th>
                <th>사업자번호</th>
                <th>주민번호</th>
                <th>대표자명</th>
                
                <th>업태</th>
                <th>종목</th>
                <th>주소(계산서변경메일)</th>
                <th>전화번호</th>
                <th>팩스번호</th>

                <th>계산서첨부파일</th>
            </tr>";
$str	.= "<tr bgcolor=\"#eeeeee\">
                <th>TR_CD</th>
                <th>SO_DT</th>
                <th>PLN_CD</th>
                <th>DUE_DT</th>
                <th>SOG_AM</th>

                <th>SOV_AM</th>
                <th>SOH_AM</th>
                <th>비교후삭제</th>
                <th>SO_NB</th>
                <th>PJT_CD</th>
                <th>MGMT_CD</th>
                
                <th>EXCH_CD</th>
                <th>SO_FG</th>
                <th>VAT_FG</th>
                <th>REMARK_DC</th>
                <th>SHIP_CD</th>

                <th>UMVAT_FG</th>
                <th>ITEM_CD</th>
                <th>SHIPREQ_DT</th>
                <th>SO_QT</th>
                <th>SO_UM</th>

                <th>VAT_UM</th>
                <th>REMARKD_DC</th>
                <th>EXCH_UM</th>
                <th>EXCH_AM</th>
                <th>QC_FG</th>

                <th>UM_FG</th>
                <th>LC_NB</th>
                <th>SO_SQ</th>
                <th>EST_NB</th>
                <th>EST_SQ</th>

                <th>SETITEM_CD</th>
                <th>STRADE_TR_NM</th>
                <th>STRADE_REG_NB</th>
                <th>STRADE_PPL_NB</th>
                <th>STRADE_CEO_NM</th>
                
                <th>STRADE_BUSINESS</th>
                <th>STRADE_JONGMOK</th>
                <th>STRADE_DIV_ADDR1</th>
                <th>STRADE_TEL</th>
                <th>STRADE_FAX</th>

                <th></th>
            </tr>";

$str	.= "<tr bgcolor=\"#eeeeee\">
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>

                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>

                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>

                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>

                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>

                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>

                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>

                <th></th>
            </tr>";


if ( $ret_od_id ) {
    $od_ids = explode('|', $ret_od_id);

    $where = array();
    foreach($od_ids as $od_id) {
        $where[] = " o.od_id = '{$od_id}' ";
    }
    $sql_search = ' where '.implode(' OR ', $where);
    $sql = "SELECT c.*, o.*, m.mb_thezone, g.mm_thezone, m.mb_email, m.mb_giup_tax_email, m.mb_giup_type, t.ot_typereceipt_cate, t.ot_typereceipt, t.ot_typereceipt_cuse, i.it_thezone FROM g5_shop_cart as c 
        LEFT JOIN g5_shop_order as o ON c.od_id = o.od_id
        LEFT JOIN g5_member as m ON c.mb_id = m.mb_id
        LEFT JOIN g5_member_giup_manager as g ON g.mm_no = o.od_giup_manager 
        LEFT JOIN g5_shop_order_typereceipt as t ON o.od_id = t.od_id 
        LEFT JOIN g5_shop_item as i ON i.it_id = c.it_id 
        LEFT JOIN partner_install_report pir ON c.od_id = pir.od_id 
        $sql_search
        ";

}else{
    $sql = "SELECT c.*, o.*, m.mb_thezone, g.mm_thezone, m.mb_email, m.mb_giup_tax_email, m.mb_giup_type, t.ot_typereceipt_cate, t.ot_typereceipt, t.ot_typereceipt_cuse, i.it_thezone FROM g5_shop_cart as c 
        LEFT JOIN g5_shop_order as o ON c.od_id = o.od_id
        LEFT JOIN g5_member as m ON c.mb_id = m.mb_id
        LEFT JOIN g5_member_giup_manager as g ON g.mm_no = o.od_giup_manager 
        LEFT JOIN g5_shop_order_typereceipt as t ON o.od_id = t.od_id 
        LEFT JOIN g5_shop_item as i ON i.it_id = c.it_id 
        LEFT JOIN partner_install_report pir ON c.od_id = pir.od_id 
        $sql_common
        ";

    // echo $sql;
}

// echo $sql;
$result = sql_query($sql);

$saved_od_id = '';
$index = 1;
$datas = array();
while($row = sql_fetch_array($result)) {
    $datas[] = $row;
}
$index = 0;
foreach($datas as $row) {
    $so_nb = $row['so_nb'];
    
    // $ccode; // 멤버 더존 코드
    
    // if ($row['mb_id'] == null)
    //     $ccode = '06012';
    // if ($row['customer_code'] != null)
    //     $ccode = $row['customer_code'];
    // if ($row['mb_thezone'] != null)
    //     $ccode = $row['mb_thezone'];

    $ccode = get_customer_code($row['od_id']);
    
    $regist_dates = str_replace('-', '', substr($row['od_time'], 0, 10)); // 주문일
    $due_dt = $row['od_ex_date'] === '0000-00-00' ? '' : str_replace('-', '', substr($row['od_ex_date'], 0, 10)); // 희망출고일
    //$price = $row['ct_price'] - $row['ct_discount']; // 할인가격

    $opt_price = 0;

    if($row['io_type'])
        $opt_price = $row['io_price'];
    else
        $opt_price = $row['ct_price'] + $row['io_price'];

    $row['opt_price'] = $opt_price;

    //$price = $row['ct_price']; // 할인전가격
    $price = $row['opt_price'] * $row['ct_qty'];
    $sprice = round($price / 1.1);
    $sprice2 = round($price / 11);
    $sprice3 = $price;

    if ( $saved_od_id == $row['od_id'] ) {
        $settleprice = '';
    }else{
        //$settleprice = $row['od_cart_price'] - $row['od_cart_discount'] - $row['od_cart_discount2']; // 할인가격
        //$settleprice = $row['od_cart_price']; // 할인전가격
        $settleprice = $row['od_cart_price'] - $row['od_cart_discount'] - $row['od_cart_discount2'] - $row['od_sales_discount'] + $row['od_send_cost'] + $row['od_send_cost2']; // 할인 + 배송가격
    }

    $vat_fg = 0;
    //$remark_dc = $row['od_name'] . '/' . $row['od_b_name'] . '/' . $row['od_id'];
    $mb = get_member($row['mb_id']);
    $remark_dc = $mb['mb_name'] . '/' . $mb['mb_giup_bname'] . '/' . $row['od_id'];

    // 회원가입시 이메일 정보와 매출증빙 이메일이 다를때
    if ( $row['ot_tax_email'] && $row['ot_tax_email'] != $row['mb_email'] ) {
        $end_email = $row['ot_tax_email'];
    }else{
        $end_email = '';
    }
    // 기업 담당자 이메일로 메일을 받을 경우
    if ( $row['mb_giup_type'] && $row['mb_giup_tax_email'] ) {
        $end_email = $row['mb_giup_tax_email'];
    }

    // // 제품 금액, 품번(상품코드) ITEM_CD 가져오기
    // if ( $row['io_id'] ) { // 옵션이 있을때
    //     $sql = "SELECT a.*, b.it_price FROM g5_shop_item_option as a JOIN g5_shop_item as b ON a.it_id = b.it_id WHERE a.io_id = '{$row['io_id']}' AND a.it_id = '{$row['it_id']}'";
    //     $r = sql_fetch($sql);
    //     print_r2($r);

    //     // $nprice = $r['it_price'] + $r['io_price'];
    //     $goods_code = $r['io_thezone'];
    // }else{
    //     $sql = "SELECT * FROM g5_shop_item WHERE it_id = '{$row['it_id']}'";
    //     $r = sql_fetch($sql);

    //     // $nprice = $r['it_price'];
    //     $goods_code = $r['it_thezone'];
    // }

    // 품번(상품코드) ITEM_CD 가져오기
    // $goods_code = $row['it_id'];
    $goods_code = $row['it_thezone'];

    $nprice = $price;
    $nprice2 = round($nprice / 1.1);

    $sql = "SELECT * FROM g5_shop_order_typereceipt WHERE od_id = '{$row['od_id']}'";
    $gb = sql_fetch($sql);

    $settle_gb = $gb['ot_typereceipt_cate'] ? $gb['ot_typereceipt_cate'] : ''; // 관리구분코드(매출증빙 분류)
    
    $card_nm = '';
    if ($settle_gb == '17') {
        $card_nm = $row['od_receipt_bank'];
    }
    
    if ($settle_gb == '11' || $settle_gb == '25' || $settle_gb == '17' || $settle_gb == '26' || $settle_gb == '99') {
        $vat_fg = 0;
    }
    
    if ($settle_gb == '16') {
        $vat_fg = 1;
    }
    
    if ($settle_gb == '14' || $settle_gb == '31') {
        $vat_fg = 3;
    }

    $od_sales_manager_thezone = '';
    if ($row['od_sales_manager']) {
        $temp_mb = get_member($row['od_sales_manager']);
        $od_sales_manager_thezone = $temp_mb['mb_thezone'];
    }

    // $sql = "SELECT * FROM g5_shop_order_custom WHERE od_id = '{$row['od_id']}' AND it_id = '{$row['it_id']}'";
    $sql = "SELECT * FROM g5_shop_order_custom WHERE od_id = '{$row['od_id']}' AND odc_uid = '{$row['ct_uid']}'";
    $custom = sql_fetch($sql);
    if ($custom['size_width']) {
        $remarkd_dc = $custom['size_width'] . '*' . $custom['size_height'];
    }else{
        $remarkd_dc = '';
    }

    // $sql = "SELECT * FROM g5_shop_order_cart_memo WHERE od_id = '{$row['od_id']}' AND it_id = '{$row['it_id']}'";
    $sql = "SELECT * FROM g5_shop_order_cart_memo WHERE od_id = '{$row['od_id']}' AND odc_uid = '{$row['ct_uid']}'";
    $memo = sql_fetch($sql);

    // $remarkd_dc .= '/' . $row['od_b_name'] . '/' . $memo['ctm_memo'];
    $remarkd_dc .= '/' . $row['od_b_name'] . '/' . $gb['ot_etc']; // 매출증빙 비고

    if ( $saved_od_id == $row['od_id'] ) {
        $sub_cnt++;
    }else{
        $sub_cnt = 1;
    }

    $str .= dz_add_oneline($color, $ccode, $regist_dates, $od_sales_manager_thezone, $due_dt, $sprice, $sprice2, $sprice3, $settleprice, $row['od_id'], $card_nm, $settle_gb, $vat_fg, $remark_dc, $goods_code, $row['ct_qty'], $nprice2, $nprice, $remarkd_dc, $sub_cnt, $end_email, $str_type, $so_nb);

    if ($row['od_id'] != $datas[($index + 1)]['od_id']) {
        // 배송비 
        $price = $row['od_send_cost'] + $row['od_send_cost2'];
        if ($price !== 0) {
            $sprice = round($price / 1.1);
            $sprice2 = round($price / 11);
            $sprice3 = $price;

            $nprice = $price;
            $nprice2 = round($nprice / 1.1);

            $goods_code = '28100001';
            $ct_qty = $row['ct_qty'];
            $settleprice = '';

            $sub_cnt++;

            $str .= dz_add_oneline($color, $ccode, $regist_dates, $od_sales_manager_thezone, $due_dt, $sprice, $sprice2, $sprice3, $settleprice, $row['od_id'], $card_nm, $settle_gb, $vat_fg, $remark_dc, $goods_code, $ct_qty, $nprice2, $nprice, $remarkd_dc, $sub_cnt, $end_email, $str_type, $so_nb);
        }

        // 추가할인
        $price = ($row['od_cart_discount'] + $row['od_cart_discount2'] + $row['od_sales_discount']) * -1;
        if ($price !== 0) {
            $sprice = round($price / 1.1);
            $sprice2 = round($price / 11);
            $sprice3 = $price;

            $nprice = $price * -1;
            $nprice2 = round($nprice / 1.1);

            $goods_code = '29100001';
            $ct_qty = $row['ct_qty'] * -1;
            $settleprice = '';

            $sub_cnt++;

            $str .= dz_add_oneline($color, $ccode, $regist_dates, $od_sales_manager_thezone, $due_dt, $sprice, $sprice2, $sprice3, $settleprice, $row['od_id'], $card_nm, $settle_gb, $vat_fg, $remark_dc, $goods_code, $ct_qty, $nprice2, $nprice, $remarkd_dc, $sub_cnt, $end_email, $str_type, $so_nb);
        }
    }
    
    $saved_od_id = $row['od_id'];
    $index++;
}
$str .= "</table>";

echo $str;
?>