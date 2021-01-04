<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "w");

$g5['title'] = "주문 내역 수정";
include_once(G5_ADMIN_PATH.'/admin.head.php');

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}
$mb = get_member($od['mb_id']);
$od_status = get_step($od['od_status']);
$pay_status = get_pay_step($od['od_pay_state']);

// print_r2($od);

$od['mb_id'] = $od['mb_id'] ? $od['mb_id'] : "비회원";

// 상품목록
$sql = " select a.ct_id,
                a.it_id,
				a.it_name,
                a.cp_price,
                a.ct_notax,
                a.ct_send_cost,
                a.ct_sendcost,
                a.it_sc_type,
				a.pt_it,
				a.pt_id,
				b.ca_id,
				b.ca_id2,
				b.ca_id3,
				b.pt_msg1,
				b.pt_msg2,
                b.pt_msg3,
                a.ct_status,
                b.it_model,
                b.it_outsourcing_use,
                b.it_outsourcing_company,
                b.it_outsourcing_manager,
                b.it_outsourcing_email,
                b.it_outsourcing_option,
                b.it_outsourcing_option2,
                b.it_outsourcing_option3,
                b.it_outsourcing_option4,
                b.it_outsourcing_option5,
				a.pt_old_name,
                a.pt_old_opt,
                a.ct_uid
		  from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
		  where a.od_id = '$od_id'
		  group by a.it_id, a.ct_uid
		  order by a.ct_id ";

$result = sql_query($sql);

$carts = array();
$cate_counts = array();

for($i=0; $row=sql_fetch_array($result); $i++) {

    $cate_counts[$row['ct_status']] += 1;

    // 상품의 옵션정보
    $sql = " select ct_id, mb_id, it_id, ct_price, ct_point, ct_qty, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, ct_sendcost, io_type, io_price, pt_msg1, pt_msg2, pt_msg3, ct_discount, ct_uid
                from {$g5['g5_shop_cart_table']}
                where od_id = '{$od['od_id']}'
                    and it_id = '{$row['it_id']}'
                    and ct_uid = '{$row['ct_uid']}'
                order by io_type asc, ct_id asc ";
    $res = sql_query($sql);

    $row['options_span'] = sql_num_rows($res);

    $row['options'] = array();
    for($k=0; $opt=sql_fetch_array($res); $k++) {

        $opt_price = 0;

		if($opt['io_type'])
            $opt_price = $opt['io_price'];
        else
            $opt_price = $opt['ct_price'] + $opt['io_price'];

        $opt['opt_price'] = $opt_price;

        // 소계
        $opt['ct_price_stotal'] = $opt_price * $opt['ct_qty'] - $opt['ct_discount'];
        $opt['ct_point_stotal'] = $opt['ct_point'] * $opt['ct_qty'] - $opt['ct_discount'];

        $row['options'][] = $opt;
    }


    // 합계금액 계산
    $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                    SUM(ct_qty) as qty,
                    SUM(ct_discount) as discount,
                    SUM(ct_send_cost) as sendcost
                from {$g5['g5_shop_cart_table']}
                where it_id = '{$row['it_id']}'
                    and od_id = '{$od['od_id']}'
                    and ct_uid = '{$row['ct_uid']}'";
    $sum = sql_fetch($sql);

    $row['sum'] = $sum;

    $carts[] = $row;
}
//print_r2($carts);
//print_r2($cate_counts);


// 주문금액 = 상품구입금액 + 배송비 + 추가배송비 - 할인금액 - 추가할인금액
$amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'] - $od['od_cart_discount'] - $od['od_cart_discount2'];

// 입금액 = 결제금액 + 포인트
$amount['receipt'] = $od['od_receipt_price'] + $od['od_receipt_point'];

// 쿠폰금액
$amount['coupon'] = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];

// 취소금액
$amount['cancel'] = $od['od_cancel_price'];

// 미수금 = 주문금액 - 취소금액 - 입금금액 - 쿠폰금액
//$amount['미수'] = $amount['order'] - $amount['receipt'] - $amount['coupon'];

// 결제방법
$s_receipt_way = ($od['pt_case']) ? $od['pt_case'] : $od['od_settle_case'];

if($od['od_settle_case'] == '간편결제') {
    switch($od['od_pg']) {
        case 'lg':
            $s_receipt_way = 'PAYNOW';
            break;
        case 'inicis':
            $s_receipt_way = 'KPAY';
            break;
        case 'kcp':
            $s_receipt_way = 'PAYCO';
            break;
        default:
            $s_receipt_way = $row['od_settle_case'];
            break;
    }
}

//$sql = "SELECT * FROM g5_shop_order_typereceipt WHERE od_id = '{$od_id}'";
//$typereceipt = sql_fetch($sql);
$typereceipt = get_typereceipt_step($od_id);
$typereceipt_cate = get_typereceipt_cate($od_id);

$next_step = get_next_step($od['od_status']);
$prev_step = get_prev_step($od['od_status']);

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php'); // datepicker js

// 파트너
$is_use_partner = (defined('USE_PARTNER') && USE_PARTNER) ? true : false;
?>
<script>
var od_id = '<?php echo $od['od_id']; ?>';
</script>
<div id="samhwa_order_form">
    <div class="block">
        <div class="header">
            <h2>주문정보<span>(주문일시:<?php echo $od['od_time']; ?>)</span></h2>
            <div class="right">
                <?php if ( $od['od_status'] == '주문' ||  $od['od_status'] == '입금' ||  $od['od_status'] == '작성' ) { ?>
                <?php if($od['od_writer']!="openmarket"){ ?>
                <span>*상품 준비단계 전까지 상품을 추가 할 수 있습니다.&nbsp;&nbsp;</span>
                <input type="button" value="상품추가" class="btn shbtn" id="add_item">
                <?php } ?>
                <?php } ?>
            </div>
        </div>
        <div class="item_list">
            <form name="frmsamhwaorderform" method="post" id="frmsamhwaorderform">
                <table>
                    <thead>
                        <tr>
                            <th class="chkbox">
                                <input type="checkbox" id="sit_select_all">
                            </th>
                            <th class="chkbox">&nbsp;</th>
                            <!--<th>분류</th>-->
                            <th class="item_name">상품</th>
                            <th class="item_qty">수량</th>
                            <th class="item_price">판매금액</th>
                            <th class="item_discount">할인금액</th>
                            <th class="item_sendcost">배송비</th>
                            <th class="item_stotal">결제금액</th>
                            <th class="item_status">상태</th>
                            <th class="item_memo">비고</th>
                            <th class="btncol"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pt_email = array();
                        $pt_name = array();
                        $chk_cnt = 0;
                        $tot_price = 0;
                        $tot_qty = 0;
                        $tot_discount = 0;
                        $tot_total = 0;
                        $tot_sendcost = 0;

                        for($i=0; $i<count($carts); $i++) {

                            // 상품이미지
                            $image = get_it_image($carts[$i]['it_id'], 50, 50);
                            $options = $carts[$i]['options'];

                            $chk_first = 0;

                            $tot_price += $carts[$i]['sum']['price'];
                            $tot_qty += $carts[$i]['sum']['qty'];
                            $tot_discount += $carts[$i]['sum']['discount'];
                            $tot_sendcost += $carts[$i]['sum']['sendcost'];
                            $tot_total += $carts[$i]['sum']['price'] - $carts[$i]['sum']['discount'];

                            for($k=0; $k<count($options); $k++) {

                                // $cs = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' ");
                                $cs = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$od_id}' AND odc_uid = '{$carts[$i]['ct_uid']}' ");

                                // 파일
                                $files = array();
                                if ( $k == 0 ) {
                                    // $sql = "SELECT * FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' AND ctf_type = 'order' ";
                                    $sql = "SELECT * FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_uid = '{$carts[$i]['ct_uid']}' AND ctf_type = 'order' ";
                                    $file_result = sql_query($sql);
                                    while($file_row = sql_fetch_array($file_result)) {
                                        $files[] = $file_row;
                                    }
                                }
                                ?>
                                <tr class="<?php echo $k==0 ? 'top-border' : ''; ?>">
                                    <?php if ( $k == 0 ) { ?>
                                        <td rowspan="<?php echo count($options); ?>" class="chkcbox">
                                            <label for="sit_sel_<?php echo $i; ?>" class="sound_only"><?php echo $carts[$i]['it_name']; ?> 옵션 전체선택</label>
                                            <input type="checkbox" id="sit_sel_<?php echo $i; ?>" name="it_sel[]">
                                        </td>
                                    <?php } ?>
                                    <td class="chkbox">
                                        <label for="ct_chk_<?php echo $chk_cnt; ?>" class="sound_only"><?php echo get_text($options[$k]['ct_option']); ?></label>
                                        <!--
                                        <input type="checkbox" name="ct_chk[<?php echo $chk_cnt; ?>]" id="ct_chk_<?php echo $chk_cnt; ?>" value="<?php echo $chk_cnt; ?>" class="sct_sel_<?php echo $i; ?>">
                                        <input type="hidden" name="ct_id[<?php echo $chk_cnt; ?>]" value="<?php echo $options[$k]['ct_id']; ?>">
                                        -->
                                        <input type="checkbox" name="ct_chk[]" id="ct_chk_<?php echo $chk_cnt; ?>" value="<?php echo $options[$k]['ct_id']; ?>" class="sct_sel_<?php echo $i; ?>" style="visibility: hidden;">
                                    </td>
                                    <td class="item_name">
                                        <div class="item_name_box">
                                            <div class="left">
                                                <?php if ( $options[$k]['io_type'] == 0 && $k == 0 ) { ?>
                                                    <a href="/shop/item.php?it_id=<?php echo $carts[$i]['it_id']; ?>" class="image" target="_blank"><?php echo $image; ?></a>
                                                    <div class="item_info">
	                                                    <b><?php echo stripslashes($carts[$i]['it_name']); ?> <a href="./itemform.php?w=u&amp;it_id=<?php echo $carts[$i]['it_id']; ?>" class="name">보기</a></b><br>
	                                                    <span><?php echo $carts[$i]['it_model']; ?></span>
	                                                    <?php if ( $carts[$i]['it_name'] != $options[$k]['ct_option']) { ?>
	                                                        [옵션] <?php echo $options[$k]['ct_option']; ?>
	                                                    <?php } ?>
														<?php
														  if($od['od_writer']=="openmarket"){
														    if($carts[$i]['it_name']!=$carts[$i]['pt_old_name']){ ?>
	                                                        <br>[매칭전]
															<?php echo $carts[$i]['pt_old_name']."(".$carts[$i]['pt_old_opt'].")"; ?>
														<?php }else{ ?>
	                                                        <br>[매칭대기]
														<?php }
	                										}
														?>
													</div>
                                                    
                                                    <?php if($od['od_tax_flag'] && $carts[$i]['ct_notax']) echo '<br/>[비과세상품]'; ?>
                                                <?php }else{ ?>
                                                    <span style="margin-right:60px;"></span>
                                                    <b>[옵션] <?php echo $options[$k]['ct_option']; ?></b>
													<?php
											          if($od['od_writer']=="openmarket"){
													    if($carts[$i]['it_name']!=$carts[$i]['pt_old_name']){ ?>
														<br>[매칭전]
														<?php echo $carts[$i]['pt_old_name']."(".$carts[$i]['pt_old_opt'].")"; ?>
													<?php }
													  }
													?>
                                                <?php } ?>
											    <?php if($od['od_writer']=="openmarket"){ ?>
												<input type="button" value="상품매칭" class="btn shbtn" id="matching_item_<?php echo $options[$k]['ct_id']; ?>" data-it-id="<?php echo $carts[$i]['it_id']; ?>">
												<?php } ?>
                                                <script>
                                                $(document).ready(function() {
                                                    // 상품 매칭
                                                    $('#matching_item_<?php echo $options[$k]['ct_id']; ?>').click(function() {
                                                        var it_id = $(this).data('it-id');
                                                        matching_item_pop = window.open('./pop.order.item.matching.php?od_id='+od_id+'&it_id='+it_id+'&ct_id=<?php echo $options[$k]['ct_id']; ?>', "matching_item_pop", "width=1080, height=900, resizable = no, scrollbars = no");
                                                    });
                                                });
                                                </script>
                                            </div>
                                            <div class="right">
                                                <?php if ( count($files) ) { ?>
                                                <div class="files">
                                                    <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/icon_file.png" />
                                                    <ul class="openlayer">
                                                        <?php foreach($files as $file) { ?>
                                                            <li>
                                                                <a target="_blank" href="<?php echo G5_DATA_URL; ?>/order_cart/<?php echo $file['ctf_name']; ?>"><?php echo $file['ctf_real_name']; ?></a>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                                <?php } ?>
                                                <?php if ( $cs['odc_no'] && $k == 0 ) { ?>
                                                <div class="custom_order">
                                                    <button type="button" class="shbtn">주문제작</button>
                                                    <div class="openlayer cs_openlayer">
                                                        <ul class="cs_list">
                                                            <?php if ( $cs['size_use'] ) { ?>
                                                            <li>
                                                                <h3>기본정보</h3>
                                                                <div>
                                                                    사이즈 (<?php echo $cs['size_width']; ?>mm X <?php echo $cs['size_height']; ?>mm)
                                                                </div>
                                                            </li>
                                                            <?php } ?>
                                                            <?php if ( $cs['frame_use'] ) { ?>
                                                            <li>
                                                                <h3>프레임 (도광판)</h3>
                                                                <div>
                                                                    <?php echo $cs['frame_standard'] ? $cs['frame_standard']: ''; ?>
                                                                    <?php echo $cs['frame_color'] ? ' / ' . $cs['frame_color']: ''; ?>

                                                                    <br/>

                                                                    <?php echo $cs['frame_front'] ? '앞판: ' . $cs['frame_front']: ''; ?>
                                                                    <?php echo $cs['frame_front_transparent_acrylic'] ? ' / 앞판 투명아크릴: ' . $cs['frame_front_transparent_acrylic']. 'T': ''; ?>
                                                                    <?php echo $cs['frame_front_optical_scatter'] ? ' / 앞판 광학산판: ' . $cs['frame_front_optical_scatter']. 'T': ''; ?>

                                                                    <br/>

                                                                    <?php echo $cs['frame_back'] ? '뒷판: ' . $cs['frame_back']: ''; ?>
                                                                    <?php echo $cs['frame_back_transparent_acrylic'] ? ' / 뒷판 투명아크릴: ' . $cs['frame_back_transparent_acrylic']. 'T': ''; ?>
                                                                    <?php echo $cs['frame_back_mdf'] ? ' / 뒷판 MDF: ' . $cs['frame_back_mdf']. 'T': ''; ?>
                                                                    <?php echo $cs['frame_back_formax'] ? ' / 뒷판 포맥스: ' . $cs['frame_back_formax']. 'T': ''; ?>
                                                                </div>
                                                            </li>
                                                            <?php } ?>
                                                            <?php if ( $cs['lightpanel_use'] ) { ?>
                                                            <li>
                                                                <h3>라이트패널</h3>
                                                                <div>
                                                                    <?php echo $cs['lightpanel_led_direction'] ? $cs['lightpanel_led_direction']: ''; ?>
                                                                    <?php echo $cs['lightpanel_led_qty'] ? ' / ' . $cs['lightpanel_led_qty'].'개': ''; ?>
                                                                    / 전원 <?php echo $cs['lightpanel_smps'] ? $cs['lightpanel_smps']: ''; ?>
                                                                    <?php echo $cs['lightpanel_power_line'] ? ' / ' . $cs['lightpanel_power_line']: ''; ?>

                                                                    <br/>
                                                                    LED <?php echo $cs['lightpanel_led_ea'] ? $cs['lightpanel_led_ea'].'개': ''; ?>
                                                                    <?php echo $cs['lightpanel_led_k'] ? ' / ' . $cs['lightpanel_led_k']: ''; ?>

                                                                    <br/>
                                                                    AC전원선 <?php echo $cs['lightpanel_power_line_ac'] ? $cs['lightpanel_power_line_ac'].'mm': '없음'; ?> /
                                                                    전원선 DC잭 <?php echo $cs['lightpanel_power_line_dc'] ? $cs['lightpanel_power_line_dc'].'mm': '없음'; ?> /
                                                                    와이어 <?php echo $cs['lightpanel_power_line_wire'] ? $cs['lightpanel_power_line_wire'].'mm': '없음'; ?>

                                                                    <br/>
                                                                    레이저가공 <?php echo $cs['lightpanel_laser'] ? $cs['lightpanel_laser']: ''; ?>
                                                                    <?php
                                                                    // $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' AND ctf_type = 'lightpanel_laser'";
                                                                    $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_uid = '{$carts[$i]['ct_uid']}' AND ctf_type = 'lightpanel_laser'";
                                                                    $result = sql_query($sql);
                                                                    $g = 0;
                                                                    while ($row = sql_fetch_array($result)) {
                                                                        if ( $g == 0 ) echo '(';
                                                                        $g++;
                                                                    ?>
                                                                        <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink" target="_blank"><?php echo $row['real_name']; ?></a>
                                                                    <?php }
                                                                    if ( $g > 0 ) echo ')';
                                                                    ?>

                                                                    <br />

                                                                    스위치 <?php echo $cs['lightpanel_switch_use'] ? $cs['lightpanel_switch_use']: ''; ?>
                                                                    <?php echo $cs['lightpanel_switch'] ? ' / ' . $cs['lightpanel_switch']: ''; ?>
                                                                    <?php echo $cs['lightpanel_switch_explain'] ? ' (' . $cs['lightpanel_switch_explain'].')': ''; ?>
                                                                </div>
                                                            </li>
                                                            <?php } ?>
                                                            <?php if ( $cs['holder_use'] ) { ?>
                                                            <li>
                                                                <h3>천장걸이형/거치대</h3>
                                                                <div>
                                                                    분류 <?php echo $cs['holder_class'] ? $cs['holder_class']: ''; ?><?php echo $cs['holder_pipe_length'] ? ' / 길이 '. $cs['holder_pipe_length'].'mm': ''; ?>
                                                                    <br/>
                                                                    간격 <?php echo $cs['holder_pipe_interval_1'] ? $cs['holder_pipe_interval_1']: '0'; ?>mm ↔ <?php echo $cs['holder_pipe_interval_2'] ? $cs['holder_pipe_interval_2']: '0'; ?>mm ↔ <?php echo $cs['holder_pipe_interval_3'] ? $cs['holder_pipe_interval_3']: '0'; ?>mm
                                                                </div>
                                                            </li>
                                                            <?php } ?>
                                                            <?php if ( $cs['printout_use'] ) { ?>
                                                            <li>
                                                                <h3>출력물</h3>
                                                                <div>
                                                                    디자인
                                                                    <?php
                                                                    // $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' AND ctf_type = 'printout_design'";
                                                                    $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_uid = '{$carts[$i]['ct_uid']}' AND ctf_type = 'printout_design'";
                                                                    $result = sql_query($sql);
                                                                    $g = 0;
                                                                    while ($row = sql_fetch_array($result)) {
                                                                        if ( $g == 0 ) echo '(';
                                                                        $g++;
                                                                    ?>
                                                                        <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink" target="_blank"><?php echo $row['real_name']; ?></a>
                                                                    <?php }
                                                                    if ( $g > 0 ) echo ')';
                                                                    ?>

                                                                    <br/>
                                                                    출력물 <?php echo $cs['printout_printout'] ? $cs['printout_printout']: ''; ?>
                                                                </div>
                                                            </li>
                                                            <?php } ?>
                                                            <?php if ( $cs['content_use'] ) { ?>
                                                            <li>
                                                                <h3>작업요청내용</h3>
                                                                <div>
                                                                    <?php echo $cs['content_common'] ? '공통내용: ' . $cs['content_common'].'<br/>': ''; ?>
                                                                    <?php echo $cs['content_minart'] ? '민아트: ' . $cs['content_minart'].'<br/>': ''; ?>
                                                                    <?php echo $cs['content_selmartec'] ? '쎌마텍: ' . $cs['content_selmartec'].'<br/>': ''; ?>
                                                                    <?php echo $cs['content_lp'] ? 'LP팀: ' . $cs['content_lp'].'<br/>': ''; ?>
                                                                </div>
                                                            </li>
                                                            <?php } ?>
                                                        </ul>
                                                        <a class="shbtn edit_item" data-it-id="<?php echo $carts[$i]['it_id']; ?>" data-uid="<?php echo $carts[$i]['ct_uid']; ?>">수정하기</a>
                                                    </div>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="item_qty">
                                        <label for="ct_qty_<?php echo $chk_cnt; ?>" class="sound_only"><?php echo get_text($options[$k]['ct_option']); ?> 수량</label>
                                        <!--
                                        <input type="text" name="ct_qty[<?php echo $chk_cnt; ?>]" id="ct_qty_<?php echo $chk_cnt; ?>" value="<?php echo $options[$k]['ct_qty']; ?>" required class="frm_input required" size="5">
                                        -->
                                        <!--
                                        <input type="text" name="ct_qty[<?php echo $options[$k]['ct_id']; ?>]" id="ct_qty_<?php echo $chk_cnt; ?>" value="<?php echo $options[$k]['ct_qty']; ?>" required class="frm_input required" size="5">
                                        -->
                                        <?php echo $options[$k]['ct_qty']; ?>
                                    </td>
                                    <td class="item_price">
                                        <?php echo number_format($options[$k]['opt_price']); ?>원
                                    </td>
                                    <td class="item_discount">

                                        <?php if ( $options[$k]['ct_discount'] != 0 ) { ?>
                                            - <?php echo number_format($options[$k]['ct_discount']); ?>원
                                        <?php }else{ ?>
                                            -
                                        <?php } ?>
                                    </td>
                                    <td class="item_sendcost">
                                        <?php echo number_format($options[$k]['ct_sendcost']); ?>원
                                    </td>
                                    <td class="item_stotal">
                                        <?php echo number_format($options[$k]['ct_price_stotal']); ?>원
                                    </td>
                                    <td class="item_status">
                                        <?php echo $options[$k]['ct_status']; ?>
                                    </td>
                                    <td class="item_memo">
                                        <?php if ( $k == 0 ) {
                                            // $sql = "SELECT * FROM g5_shop_order_cart_memo WHERE od_id = '{$od_id}' AND it_id = '{$options[$k]['it_id']}'";
                                            $sql = "SELECT * FROM g5_shop_order_cart_memo WHERE od_id = '{$od_id}' AND ctm_uid = '{$options[$k]['ct_uid']}'";
                                            $item_memo = sql_fetch($sql);
                                            echo htmlspecialchars($item_memo['ctm_memo']);
                                        }
                                        ?>
                                    </td>
                                    <td class="btncol">
<?php if($od['od_writer']!="openmarket"){ ?>
                                        <?php if ( $k == 0 ) { ?>
                                            <div class="more">
                                                <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_more_b.png" class="item_list_more" data-ct-id="<?php echo $options[$k]['ct_id']; ?>" />
                                                <ul class="openlayer">
                                                    <?php
                                                    $temp_ct_step = get_step($options[$k]['ct_status']);
                                                    ?>
                                                    <?php if ($temp_ct_step['cart_editable']) { ?>
                                                    <li class="edit_item" data-od-id="<?php echo $od_id; ?>" data-it-id="<?php echo $options[$k]['it_id']; ?>" data-uid="<?php echo $options[$k]['ct_uid']; ?>">수정</li>
                                                    <?php } ?>
                                                    <?php if ($temp_ct_step['cart_deletable']) { ?>
                                                    <li class="delete_item" data-od-id="<?php echo $od_id; ?>" data-it-id="<?php echo $options[$k]['it_id']; ?>" data-uid="<?php echo $options[$k]['ct_uid']; ?>">삭제</li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        <?php } ?>
<?php } ?>
                                    </td>
                                </tr>
                                <?php
                                $chk_first++;
                                $chk_cnt++;
                                }
                                if ($carts[$i]['it_outsourcing_use']) {
                                    if($carts[$i]['it_outsourcing_option']) {
                                        $outsourcing_options = explode(',', $carts[$i]['it_outsourcing_option']);
                                    }
                                    if($carts[$i]['it_outsourcing_option2']) {
                                        $outsourcing_options2 = explode(',', $carts[$i]['it_outsourcing_option2']);
                                    }
                                    if($carts[$i]['it_outsourcing_option3']) {
                                        $outsourcing_options3 = explode(',', $carts[$i]['it_outsourcing_option3']);
                                    }
                                    if($carts[$i]['it_outsourcing_option4']) {
                                        $outsourcing_options4 = explode(',', $carts[$i]['it_outsourcing_option4']);
                                    }
                                    if($carts[$i]['it_outsourcing_option5']) {
                                        $outsourcing_options5 = explode(',', $carts[$i]['it_outsourcing_option5']);
                                    }
                                ?>
                                <tr>
                                    <td colspan="2"></td>
                                    <?php
                                    // $outsourcing = sql_fetch("SELECT * FROM g5_shop_order_outsourcing WHERE od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' AND oo_state = '0' ORDER BY oo_id DESC");
                                    $outsourcing = sql_fetch("SELECT * FROM g5_shop_order_outsourcing WHERE od_id = '{$od_id}' AND it_id = '{$carts[$i]['it_id']}' AND oo_uid = '{$carts[$i]['ct_uid']}' AND oo_state = '0' ORDER BY oo_id DESC");
                                    if ( $outsourcing['oo_id'] ) {
                                    ?>
                                    <td colspan="9" class="item_outsourcing" data-id="<?php echo $carts[$i]['it_id']; ?>" data-uid="<?php echo $carts[$i]['ct_uid']; ?>">
                                        외부발주 : <?php echo $outsourcing['oo_outsourcing_option']; ?>,
                                        <?php echo $outsourcing['oo_outsourcing_option2'] ? $outsourcing['oo_outsourcing_option2'] . ', ' : ''; ?>
                                        <?php echo $outsourcing['oo_outsourcing_option3'] ? $outsourcing['oo_outsourcing_option3'] . ', ' : ''; ?>
                                        <?php echo $outsourcing['oo_outsourcing_option4'] ? $outsourcing['oo_outsourcing_option4'] . ', ' : ''; ?>
                                        <?php echo $outsourcing['oo_outsourcing_option5'] ? $outsourcing['oo_outsourcing_option5'] . ', ' : ''; ?>

                                        <div id="it_outsourcing_option_file_<?php echo $i; ?>" class="it_outsourcing_option_file" data-id="<?php echo $i; ?>">
                                            첨부파일:
                                            <ul class="upload_files upload_files_outsourcing_option_apply upload_files_outsourcing_option_apply_<?php echo $carts[$i]['it_id']; ?> upload_files_outsourcing_option_apply_<?php echo $carts[$i]['ct_uid']; ?>">
                                                <?php
                                                // $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'order_outsourcing' AND it_id = '{$carts[$i]['it_id']}'";
                                                $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'order_outsourcing' AND ctf_uid = '{$carts[$i]['ct_uid']}'";
                                                $result = sql_query($sql);
                                                $outsourcing_files = 0;
                                                while ($row = sql_fetch_array($result)) {
                                                    $outsourcing_files++;
                                                ?>
                                                    <li>
                                                        <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink" target="_blank"><?php echo $row['real_name']; ?></a>
                                                    </li>
                                                <?php } ?>
                                                <?php if (!$outsourcing_files) echo "없음&nbsp;&nbsp;&nbsp;"; ?>
                                            </ul>
                                        </div>
                                        <input type="button" value="취소" class="blue shbtn btn item_outsourcing_cancel" data-id="<?php echo $outsourcing['oo_id']; ?>">
                                        <span style="margin-left:15px;"><?php echo $outsourcing['oo_created_at']; ?></span>
                                    </td>
                                    <?php }else{ ?>
                                    <td colspan="9" class="item_outsourcing" data-id="<?php echo $carts[$i]['it_id']; ?>" data-uid="<?php echo $carts[$i]['ct_uid']; ?>">
                                        외부발주 :

                                        <select name="sales_manager">
                                            <option value="">담당자 선택</option>
                                            <?php
                                            $sql = "SELECT * FROM g5_auth WHERE au_menu = '400400' AND au_auth LIKE '%w%'";
                                            $auth_result = sql_query($sql);
                                            while($a_row = sql_fetch_array($auth_result)) {
                                                $a_mb = get_member($a_row['mb_id']);
                                            ?>
                                                <option value="<?php echo $a_mb['mb_id']; ?>" <?php echo $a_mb['mb_id'] == $od['od_sales_manager'] ? 'selected' : ''; ?>><?php echo $a_mb['mb_name']; ?></option>
                                            <?php } ?>
                                        </select>

                                        <?php if($carts[$i]['it_outsourcing_option']) { ?>
                                        <select name="it_outsourcing_option">
                                            <option value="">옵션1</option>
                                            <?php foreach ($outsourcing_options as $opt) { ?>
                                                <option value="<?php echo $opt; ?>"><?php echo $opt; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php } ?>
                                        <?php if($carts[$i]['it_outsourcing_option2']) { ?>
                                        <select name="it_outsourcing_option2">
                                            <option value="">옵션2</option>
                                            <?php foreach ($outsourcing_options2 as $opt) { ?>
                                                <option value="<?php echo $opt; ?>"><?php echo $opt; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php } ?>
                                        <?php if($carts[$i]['it_outsourcing_option3']) { ?>
                                        <select name="it_outsourcing_option3">
                                            <option value="">옵션3</option>
                                            <?php foreach ($outsourcing_options3 as $opt) { ?>
                                                <option value="<?php echo $opt; ?>"><?php echo $opt; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php } ?>
                                        <?php if($carts[$i]['it_outsourcing_option4']) { ?>
                                        <select name="it_outsourcing_option4">
                                            <option value="">옵션4</option>
                                            <?php foreach ($outsourcing_options4 as $opt) { ?>
                                                <option value="<?php echo $opt; ?>"><?php echo $opt; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php } ?>
                                        <?php if($carts[$i]['it_outsourcing_option5']) { ?>
                                        <select name="it_outsourcing_option5">
                                            <option value="">옵션5</option>
                                            <?php foreach ($outsourcing_options5 as $opt) { ?>
                                                <option value="<?php echo $opt; ?>"><?php echo $opt; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php } ?>
                                        <div id="it_outsourcing_option_file_<?php echo $i; ?>" class="it_outsourcing_option_file" data-id="<?php echo $i; ?>" data-uid="<?php echo $carts[$i]['ct_uid']; ?>">
                                            <button type="button" class="shbtn uploadbtn">찾아보기</button>
                                            <ul class="upload_files upload_files_outsourcing_option_apply upload_files_outsourcing_option_apply_<?php echo $carts[$i]['it_id']; ?> upload_files_outsourcing_option_apply_<?php echo $carts[$i]['ct_uid']; ?>">
                                                <?php
                                                // $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'order_outsourcing' AND it_id = '{$carts[$i]['it_id']}'";
                                                $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'order_outsourcing' AND ctf_uid = '{$carts[$i]['ct_uid']}'";
                                                $result = sql_query($sql);
                                                while ($row = sql_fetch_array($result)) {
                                                ?>
                                                    <li>
                                                        <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink" target="_blank"><?php echo $row['real_name']; ?></a>
                                                        <a href='#' class="remove" data-no="<?php echo $row['no']; ?>" ><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_del_s.png" /></a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                        <input type="button" value="전송" class="blue shbtn btn item_outsourcing_submit">
                                    </td>
                                    <?php } ?>
                                </tr>
                                <?php
                                }
                            }
                        ?>
                        <tr class="result">
                            <td class="chkbox">
                            </td>
                            <td class="chkbox">
                            </td>
                            <td class="item_name">
                            </td>
                            <td class="item_qty">
                                <?php echo number_format($tot_qty); ?>
                            </td>
                            <td class="item_price">
                                <?php echo number_format($tot_price); ?>원
                            </td>
                            <td class="item_discount">
                                - <?php echo number_format($tot_discount); ?>원
                            </td>
                            <td class="item_sendcost">
                                <!--<?php echo number_format($tot_sendcost); ?>원-->
                            </td>
                            <td class="item_stotal">
                                <?php echo number_format($tot_total); ?>원
                            </td>
                            <td class="item_status">
                            </td>
                            <td class="item_memo"></td>
                            <td class="btncol"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="frmsamhwaorderform_bottom">
                    <div class="change_status">
                        <span>선택한 상품 상태값</span>
                        <select name="step">
                            <?php
                            foreach($order_steps as $step) {
                            if (!$step['cart']) continue;
                            ?>
                                <option value="<?php echo $step['val']; ?>" <?php echo $step['val'] == $od['od_status'] ? 'selected' : ''; ?>><?php echo $step['name']; ?></option>
                            <?php } ?>
                        </select>
                        <input type="button" value="변경하기" class="btn shbtn" id="change_cart_status">
                    </div>
                    <div class="change_discount2">
                        <span>총 가격변경(추가할인/금액추가) *금액을 추가하려면 (-) 입력 후 금액 입력</span>
                        <input type="text" class="frm_input" name="od_cart_discount2" id="od_cart_discount2" value="<?php echo $od['od_cart_discount2']; ?>" />
                        <input type="button" value="적용" class="btn shbtn" id="change_discount2">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="block">
        <div class="header">
            <h2>수급자정보</h2>
        </div>
        <!-- 수급자 정보가 없는 경우 표시 
        <div class="block-box">
        	
        	<p>입력된 수급자 정보가 없습니다. </p> 
        	
        </div>
        -->
    	<table class="recipient_info">
			<tr>
				<th>수급자</th>
				<th>인정등급</th>
				<th>유효기간</th>
				<th>적용기간</th>
				<th>전화번호</th>
				<th>주소</th>
			</tr>
			<tr>
				<td>홍길동</td>
				<td>3등급</td>
				<td>2020.12.01 ~ 2020.12.01</td>
				<td>2020.12.01 ~ 2020.12.01</td>
				<td>010-1111-2222</td>
				<td>서울시 강남구 123-56</td>
			</tr>
		</table>
    	
    </div>
    <div class="block">
        <div class="header">
            <h2>배송정보</h2>
            <div class="right">
                <input type="button" value="기본정보 반영" class="btn shbtn" id="reset_od_info">
                <input type="button" value="출고 리스트" class="btn shbtn" id="release_list" onclick="location.href='./samhwa_deliverylist.php';">
            </div>
        </div>
        <div class="delivery_info block-box">
            <form id="frmsamhwaorderdeliveryform" name="frmsamhwaorderdeliveryform">

                <div class="tbl_frm01">
                    <table>
                    <caption>받으시는 분 정보</caption>
                    <colgroup>
                        <col class="grid_4">
                        <col>
                    </colgroup>
                    <tbody>
                    <tr>
                        <th scope="row"><label for="od_b_name"><span class="sound_only">받으시는 분 </span>수령인</label></th>
                        <td colspan="3"><input type="text" name="od_b_name" value="<?php echo get_text($od['od_b_name']); ?>" id="od_b_name" required class="frm_input required"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="od_b_tel"><span class="sound_only">받으시는 분 </span>전화번호</label></th>
                        <td style="width:250px;"><input type="text" name="od_b_tel" value="<?php echo get_text($od['od_b_tel']); ?>" id="od_b_tel" required class="frm_input required"></td>
                    <!-- </tr>
                    <tr> -->
                        <th scope="row" style="width:100px;"><label for="od_b_hp"><span class="sound_only">받으시는 분 </span>핸드폰</label></th>
                        <td><input type="text" name="od_b_hp" value="<?php echo get_text($od['od_b_hp']); ?>" id="od_b_hp" class="frm_input required"></td>
                    </tr>
                    <tr>
                        <th scope="row"><span class="sound_only">받으시는 분 </span>주소</th>
                        <td class="od_b_address" colspan="3">
                        <label for="od_b_zip" class="sound_only">우편번호</label>
                        <input type="text" name="od_b_zip" value="<?php echo get_text($od['od_b_zip1']).get_text($od['od_b_zip2']); ?>" id="od_b_zip" required class="frm_input required" size="35">
                        <button type="button" class="shbtn" onclick="win_zip('frmsamhwaorderdeliveryform', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');">주소 검색</button><br>
                        <input type="text" name="od_b_addr1" value="<?php echo get_text($od['od_b_addr1']); ?>" id="od_b_addr1" required class="frm_input required" size="35" placeholder="기본주소" readonly>
                        <input type="text" name="od_b_addr2" value="<?php echo get_text($od['od_b_addr2']); ?>" id="od_b_addr2" class="frm_input" size="35" placeholder="상세주소">
                        , 지번  : <input type="text" name="od_b_addr3" value="<?php echo get_text($od['od_b_addr3']); ?>" id="od_b_addr3" class="frm_input" size="35" placeholder="참고항목" readonly >
                        <input type="hidden" name="od_b_addr_jibeon" value="<?php echo get_text($od['od_b_addr_jibeon']); ?>">
                        <?php
                        $szip = get_text($od['od_b_zip1']).get_text($od['od_b_zip2']);
                        $sql = "SELECT * FROM g5_shop_sendcost WHERE sc_zip1 <= '{$szip}' AND sc_zip2 >= '{$szip}'";
                        $szip_result = sql_fetch($sql);

                        if ( $szip_result['sc_id'] ) {
                        ?>
                        <div class="add_sendcost_address">
                            <span class="red">* 도서산간지역</span>
                        </div>
                        <?php } ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">전달 메세지</th>
                        <td colspan="3"><input type="text" name="od_memo" value="<?php echo get_text($od['od_memo'], 1); ?>" id="od_memo" required class="frm_input" style="width:80%" ></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="od_ex_date">출고예정일</label></th>
                        <td colspan="3">
                            <input type="text" name="od_ex_date" value="<?php echo $od['od_ex_date']; ?>" id="od_ex_date" required class="frm_input required" maxlength="10" minlength="10">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">배송선택</th>
                        <td colspan="3">
                            <select name="od_delivery_type" id="od_delivery_type" style="width: 150px;">
                                <?php
                                $temp_delivery_type = '';
                                foreach($delivery_types as $type) {
                                ?>
                                    <option value="<?php echo $type['val']; ?>" <?php echo $type['val'] == $od['od_delivery_type'] ? 'selected' : ''; ?> data-type="<?php echo $type['type']; ?>"><?php echo $type['name']; ?></option>
                                <?php } ?>
                            </select>
                            <div class="delivery_block">
                                <!-- 택배 -->
                                <div class="delivery_types delivery" data-delivery-type="delivery">
                                    <select name="od_delivery_company[delivery]" style="width: 100px;">
                                        <?php
                                        foreach($delivery_companys as $company) {
                                        ?>
                                            <option value="<?php echo $company['val']; ?>" <?php echo $company['val'] == $od['od_delivery_company'] ? 'selected' : ''; ?>><?php echo $company['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <input type="text" name="od_delivery_text[delivery]" value="<?php echo $od['od_delivery_text']; ?>" required class="frm_input" placeholder="송장번호 입력" style="min-width:70px">
                                    <select name="od_delivery_qty[delivery]" style="width: 100px;">
                                        <?php
                                        for($i=0;$i<=20;$i++) {
                                        ?>
                                            <option value="<?php echo $i; ?>" <?php echo $i == $od['od_delivery_qty'] ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                        <?php } ?>
                                    </select>
                                    Box&nbsp;
                                    <input type="text" name="od_delivery_price[delivery]" value="<?php echo $od['od_delivery_price']; ?>" required class="frm_input" placeholder="운임비" style="min-width:70px">&nbsp;원&nbsp;
                                    송하인:
                                    <input type="radio" name="od_delivery_receiptperson[delivery]" id='od_delivery_receiptperson_0' value="0" <?php echo $od['od_delivery_receiptperson'] == 0 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_0">관리기업</label>

                                    <input type="radio" name="od_delivery_receiptperson[delivery]" id="od_delivery_receiptperson_1" value="1" <?php echo $od['od_delivery_receiptperson'] == 1 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_1">주문자(<?php echo $od['od_name']; ?>)</label>
                                    <div class="delivery_print">
                                        <?php if($od['od_delivery_company'] == "ilogen") { ?>
											<a href="https://www.ilogen.com/web/personal/trace/<?php echo $od['od_delivery_text']; ?>" target="_blank" class="btn_delivery"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_delivery.png" /></a>
										<?php } elseif ($od['od_delivery_company'] == "cjlogistics") { ?>
											<a href="https://www.doortodoor.co.kr/parcel/doortodoor.do?fsp_action=PARC_ACT_002&fsp_cmd=retrieveInvNoACT&invc_no=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_delivery.png" /></a>
										<?php } elseif ($od['od_delivery_company'] == "kdexp") { ?>
											<a href="https://kdexp.com/basicNewDelivery.kd?barcode=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_delivery.png" /></a>
										<?php } elseif ($od['od_delivery_company'] == "ds3211") { ?>
											<a href="http://home.daesinlogistics.co.kr/daesin/jsp/d_freight_chase/d_general_process2.jsp?billno1=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_delivery.png" /></a>
										<?php } elseif ($od['od_delivery_company'] == "hdexp") { ?>
											<a href="http://www.deliverytracking.kr/?dummy=one&deliverytype=hdexp&keyword=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_delivery.png" /></a>
										<?php } elseif ($od['od_delivery_company'] == "lotteglogis") { ?>
											<a href="http://www.deliverytracking.kr/?dummy=one&deliverytype=lotteglogis&keyword=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_delivery.png" /></a>
										<?php } elseif ($od['od_delivery_company'] == "chunilps") { ?>
											<a href="http://www.cyber1001.co.kr/kor/taekbae/HTrace.jsp?transNo=<?php echo $od['od_delivery_text']; ?>" target="_blank"  class="btn_delivery"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_delivery.png" /></a>
										<?php } ?>
                                    </div>
                                </div>
                                <!-- 퀵서비스 -->
                                <div class="delivery_types quick">
                                    <!--<input type="text" name="od_delivery_text[quick]" value="<?php echo $od['od_delivery_text']; ?>" required class="frm_input" placeholder="연락처 입력" style="min-width:70px">-->
                                    <input type="text" name="od_delivery_tel[quick]" value="<?php echo $od['od_delivery_tel']; ?>" required class="frm_input" placeholder="연락처 입력" style="min-width:70px">
                                    <input type="text" name="od_delivery_price[quick]" value="<?php echo $od['od_delivery_price']; ?>" required class="frm_input" placeholder="운임비" style="min-width:70px">&nbsp;원&nbsp;
                                    송하인:
                                    <input type="radio" name="od_delivery_receiptperson[quick]" id='od_delivery_receiptperson_quick0' value="0" <?php echo $od['od_delivery_receiptperson'] == 0 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_quick0">삼화</label>

                                    <input type="radio" name="od_delivery_receiptperson[quick]" id="od_delivery_receiptperson_quick1" value="1" <?php echo $od['od_delivery_receiptperson'] == 1 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_quick1">주문자(<?php echo $od['od_name']; ?>)</label>
                                    <div class="delivery_print">
                                        <a><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/printer.png" /></a>
                                    </div>
                                </div>
                                <!-- 매장수령 -->
                                <div class="delivery_types store">
                                    <!--<input type="text" name="od_delivery_text[store]" value="<?php echo $od['od_delivery_text']; ?>" required class="frm_input" placeholder="메모 입력" style="width:80%">-->
                                </div>
                                <!-- 오토바이퀵 -->
                                <div class="delivery_types autobike">
                                    <!--<input type="text" name="od_delivery_text[autobike]" value="<?php echo $od['od_delivery_text']; ?>" required class="frm_input" placeholder="연락처 입력" style="min-width:70px">-->
                                    <input type="text" name="od_delivery_tel[autobike]" value="<?php echo $od['od_delivery_tel']; ?>" required class="frm_input" placeholder="연락처 입력" style="min-width:70px">
                                    <input type="text" name="od_delivery_price[autobike]" value="<?php echo $od['od_delivery_price']; ?>" required class="frm_input" placeholder="운임비" style="min-width:70px">&nbsp;원&nbsp;
                                    송하인:
                                    <input type="radio" name="od_delivery_receiptperson[autobike]" id='od_delivery_receiptperson_autobike0' value="0" <?php echo $od['od_delivery_receiptperson'] == 0 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_autobike0">삼화</label>

                                    <input type="radio" name="od_delivery_receiptperson[autobike]" id="od_delivery_receiptperson_autobike1" value="1" <?php echo $od['od_delivery_receiptperson'] == 1 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_autobike1">주문자(<?php echo $od['od_name']; ?>)</label>
                                    <div class="delivery_print">
                                        <a><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/printer.png" /></a>
                                    </div>
                                </div>
                                <!-- 다마스퀵 -->
                                <div class="delivery_types damas">
                                    <!--<input type="text" name="od_delivery_text[damas]" value="<?php echo $od['od_delivery_text']; ?>" required class="frm_input" placeholder="연락처 입력" style="min-width:70px">-->
                                    <input type="text" name="od_delivery_tel[damas]" value="<?php echo $od['od_delivery_tel']; ?>" required class="frm_input" placeholder="연락처 입력" style="min-width:70px">
                                    <input type="text" name="od_delivery_price[damas]" value="<?php echo $od['od_delivery_price']; ?>" required class="frm_input" placeholder="운임비" style="min-width:70px">&nbsp;원&nbsp;
                                    송하인:
                                    <input type="radio" name="od_delivery_receiptperson[damas]" id='od_delivery_receiptperson_damas0' value="0" <?php echo $od['od_delivery_receiptperson'] == 0 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_damas0">삼화</label>

                                    <input type="radio" name="od_delivery_receiptperson[damas]" id="od_delivery_receiptperson_damas1" value="1" <?php echo $od['od_delivery_receiptperson'] == 1 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_damas1">주문자(<?php echo $od['od_name']; ?>)</label>
                                    <div class="delivery_print">
                                        <a><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/printer.png" /></a>
                                    </div>
                                </div>
                                <!-- 화물택배 -->
                                <div class="delivery_types huamul">
                                    <select name="od_delivery_company[huamul]" style="width: 100px;">
                                        <?php
                                        foreach($delivery_companys as $company) {
                                        ?>
                                            <option value="<?php echo $company['val']; ?>" <?php echo $company['val'] == $od['od_delivery_company'] ? 'selected' : ''; ?>><?php echo $company['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <input type="text" name="od_delivery_text[huamul]" value="<?php echo $od['od_delivery_text']; ?>" required class="frm_input" placeholder="송장번호 입력" style="min-width:70px">
                                    <select name="od_delivery_qty[huamul]" style="width: 100px;">
                                        <?php
                                        for($i=0;$i<=20;$i++) {
                                        ?>
                                            <option value="<?php echo $i; ?>" <?php echo $i == $od['od_delivery_qty'] ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                        <?php } ?>
                                    </select>
                                    Box&nbsp;
                                    <input type="text" name="od_delivery_price[huamul]" value="<?php echo $od['od_delivery_price']; ?>" required class="frm_input" placeholder="운임비" style="min-width:70px">&nbsp;원&nbsp;
                                    송하인:
                                    <input type="radio" name="od_delivery_receiptperson[huamul]" id='od_delivery_receiptperson_huamul0' value="0" <?php echo $od['od_delivery_receiptperson'] == 0 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_huamul0">삼화</label>

                                    <input type="radio" name="od_delivery_receiptperson[huamul]" id="od_delivery_receiptperson_huamul1" value="1" <?php echo $od['od_delivery_receiptperson'] == 1 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_huamul1">주문자(<?php echo $od['od_name']; ?>)</label>
                                    <div class="delivery_print">
                                        <a><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/printer.png" /></a>
                                    </div>
                                </div>
                                <!-- 경동화물 영업소 -->
                                <div class="delivery_types gdhuamul">
                                    <select name="od_delivery_company[gdhuamul]" style="width: 100px;">
                                        <?php
                                        foreach($delivery_companys as $company) {
                                        ?>
                                            <option value="<?php echo $company['val']; ?>" <?php echo $company['val'] == $od['od_delivery_company'] ? 'selected' : ''; ?>><?php echo $company['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <input type="text" name="od_delivery_place[gdhuamul]" value="<?php echo $od['od_delivery_place']; ?>" required class="frm_input" placeholder="영업소 입력" style="min-width:70px">
                                    <input type="text" name="od_delivery_text[gdhuamul]" value="<?php echo $od['od_delivery_text']; ?>" required class="frm_input" placeholder="송장번호 입력" style="min-width:70px">
                                    <select name="od_delivery_qty[gdhuamul]" style="width: 100px;">
                                        <?php
                                        for($i=0;$i<=20;$i++) {
                                        ?>
                                            <option value="<?php echo $i; ?>" <?php echo $i == $od['od_delivery_qty'] ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                        <?php } ?>
                                    </select>
                                    Box&nbsp;
                                    <input type="text" name="od_delivery_price[gdhuamul]" value="<?php echo $od['od_delivery_price']; ?>" required class="frm_input" placeholder="운임비" style="min-width:70px">&nbsp;원&nbsp;
                                    송하인:
                                    <input type="radio" name="od_delivery_receiptperson[gdhuamul]" id='od_delivery_receiptperson_gdhuamul0' value="0" <?php echo $od['od_delivery_receiptperson'] == 0 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_gdhuamul0">삼화</label>

                                    <input type="radio" name="od_delivery_receiptperson[gdhuamul]" id="od_delivery_receiptperson_gdhuamul1" value="1" <?php echo $od['od_delivery_receiptperson'] == 1 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_gdhuamul1">주문자(<?php echo $od['od_name']; ?>)</label>
                                    <div class="delivery_print">
                                        <a><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/printer.png" /></a>
                                    </div>
                                </div>
                                <!-- 전국화물 -->
                                <div class="delivery_types nationwidehuamul">
                                    <select name="od_delivery_company[nationwidehuamul]" style="width: 100px;">
                                        <?php
                                        foreach($delivery_companys as $company) {
                                        ?>
                                            <option value="<?php echo $company['val']; ?>" <?php echo $company['val'] == $od['od_delivery_company'] ? 'selected' : ''; ?>><?php echo $company['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <input type="text" name="od_delivery_text[nationwidehuamul]" value="<?php echo $od['od_delivery_text']; ?>" required class="frm_input" placeholder="송장번호 입력" style="min-width:70px">
                                    <!--<input type="text" name="od_delivery_text[nationwidehuamul]" value="<?php echo $od['od_delivery_text']; ?>" required class="frm_input" placeholder="메모 입력" style="min-width:200px">-->
                                    <select name="od_delivery_qty[nationwidehuamul]" style="width: 100px;">
                                        <?php
                                        for($i=0;$i<=20;$i++) {
                                        ?>
                                            <option value="<?php echo $i; ?>" <?php echo $i == $od['od_delivery_qty'] ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                        <?php } ?>
                                    </select>
                                    Box&nbsp;
                                    <input type="text" name="od_delivery_price[nationwidehuamul]" value="<?php echo $od['od_delivery_price']; ?>" required class="frm_input" placeholder="운임비" style="min-width:70px">&nbsp;원&nbsp;
                                    송하인:
                                    <input type="radio" name="od_delivery_receiptperson[nationwidehuamul]" id='od_delivery_receiptperson_nationwidehuamul0' value="0" <?php echo $od['od_delivery_receiptperson'] == 0 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_nationwidehuamul0">삼화</label>

                                    <input type="radio" name="od_delivery_receiptperson[nationwidehuamul]" id="od_delivery_receiptperson_nationwidehuamul1" value="1" <?php echo $od['od_delivery_receiptperson'] == 1 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_nationwidehuamul1">주문자(<?php echo $od['od_name']; ?>)</label>
                                    <div class="delivery_print">
                                        <a><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/printer.png" /></a>
                                    </div>
                                </div>
                                <!-- 고속버스 -->
                                <div class="delivery_types bus">
                                    <input type="text" name="od_delivery_place[bus]" value="<?php echo $od['od_delivery_place']; ?>" required class="frm_input" placeholder="버스정류장 입력" style="min-width:70px">
                                    <select name="od_delivery_qty[bus]" style="width: 100px;">
                                        <?php
                                        for($i=0;$i<=20;$i++) {
                                        ?>
                                            <option value="<?php echo $i; ?>" <?php echo $i == $od['od_delivery_qty'] ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                        <?php } ?>
                                    </select>
                                    Box&nbsp;
                                    <input type="text" name="od_delivery_price[bus]" value="<?php echo $od['od_delivery_price']; ?>" required class="frm_input" placeholder="운임비" style="min-width:70px">&nbsp;원&nbsp;
                                    송하인:
                                    <input type="radio" name="od_delivery_receiptperson[bus]" id='od_delivery_receiptperson_bus0' value="0" <?php echo $od['od_delivery_receiptperson'] == 0 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_bus0">삼화</label>

                                    <input type="radio" name="od_delivery_receiptperson[bus]" id="od_delivery_receiptperson_bus1" value="1" <?php echo $od['od_delivery_receiptperson'] == 1 ? 'checked' : ''; ?>/>
                                    <label for="od_delivery_receiptperson_bus1">주문자(<?php echo $od['od_name']; ?>)</label>
                                </div>
                            </div>
                            <div class="delivery_edi_div">
                                <input type="button" value="EDI 전송" class="btn shbtn green delivery_edi">
                                <input type="button" value="송장리턴" class="btn shbtn delivery_edi_return">
                                <span style="margin-left:5px;vertical-align:middle;">* 로젠택배인경우 EDI 전송 후 로젠 프로그램을 통해 송장을 출력합니다. '송장리턴'을 클릭하시면 송장번호가 발급됩니다.</span>
                                <?php if ($od['od_edi_date']) { ?>
                                <p style="padding:0;color:#9e9e9e;">
                                    * EDI 전송 내역이 있습니다. (전송일: <?php echo $od['od_edi_date']; ?>)
                                </p>
                                <?php } ?>
                            </div>

                            <?php if($od['od_writer']=="openmarket" && $od['od_delivery_text']){?>
                            <div class="delivery_sabangnet_div">
                                <input type="button" value="사방넷 배송정보기록" class="btn shbtn delivery_sabangnet_return">
							</div>
							<?php } ?>
                        </td>
                    </tr>
                    <!--
                    <tr>
                        <th scope="row">배송비</th>
                        <td><input type="text" name="od_send_cost" value="<?php echo $od['od_send_cost']; ?>" id="od_send_cost" required class="frm_input required" size="30">&nbsp;원</td>
                    </tr>
                    -->
                    <tr style="display:none">
                        <th scope="row">추가 배송비</th>
                        <td colspan="3"><input type="text" name="od_send_cost2" value="<?php echo $od['od_send_cost2']; ?>" id="od_send_cost2" required class="frm_input required" size="30" readonly>&nbsp;원&nbsp;&nbsp;* 추가배송비는 변경하실 수 없습니다.</td>
                    </tr>
                    <!--
                    <tr class="gray">
                        <th scope="row">관리자메모</th>
                        <td><textarea name="od_send_admin_memo" rows="8" placeholder="관리자메모를 입력하세요." id="od_send_admin_memo"><?php echo get_text($od['od_send_admin_memo'], 1); ?></textarea></td>
                    </tr>
                    -->
                    <input type="hidden" name="od_send_admin_memo" value="<?php echo get_text($od['od_send_admin_memo'], 1); ?>" />
                    </tbody>
                    </table>
                </div>
            </form>
        </div>
        <button id="delivery_info_btn">배송정보 수정</button>
    </div>
    <div class="block">
        <div class="header">
            <h2>결제정보/매출증빙</h2>
            <!-- <div class="right">
                <input type="button" value="매출증빙 리스트" class="btn shbtn" id="maechul_zhengming_list">
            </div> -->
        </div>
        <div class="payment block-box">
            <div class="pay">
                <h3 class="box_sub_title">결제내역</h3>
                <table class="payment-table">
                    <tbody>
                        <tr>
                            <th>결제수단</th>
                            <td><?php echo $od['od_settle_case']; ?></td>
                        </tr>
                        <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌') { ?>
                        <tr>
                            <th>입금계좌</th>
                            <td><?php echo $od['od_bank_account']; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $od['od_settle_case']; ?> 입금액</th>
                            <td><?php echo display_price($od['od_receipt_price']); ?></td>
                        </tr>
                        <tr>
                            <th>입금자</th>
                            <td><?php echo get_text($od['od_deposit_name']); ?></td>
                        </tr>
                        <tr>
                            <th>입금확인일시</th>
                            <td>
                                <?php echo $od['od_receipt_time']; ?> (<?php echo get_yoil($od['od_receipt_time']); ?>)
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if ($od['od_settle_case'] == '휴대폰') { ?>
                        <tr>
                            <th>휴대폰번호</th>
                            <td><?php echo get_text($od['od_bank_account']); ?></td>
                            </tr>
                        <tr>
                            <th><?php echo $od['od_settle_case']; ?> 결제액</th>
                            <td><?php echo display_price($od['od_receipt_price']); ?></td>
                        </tr>
                        <tr>
                            <th>결제 확인일시</th>
                            <td>
                                <?php if ($od['od_receipt_time'] == 0) { ?>결제 확인일시를 체크해 주세요.
                                <?php } else { ?><?php echo $od['od_receipt_time']; ?> (<?php echo get_yoil($od['od_receipt_time']); ?>)
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>

                        <?php if ($od['od_settle_case'] == '신용카드') { ?>
                        <tr>
                            <th class="sodr_sppay">신용카드 결제금액</th>
                            <td>
                                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>0원
                                <?php } else { ?><?php echo display_price($od['od_receipt_price']); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="sodr_sppay">카드사</th>
                            <td><?php echo get_receipt_bank_name_by_value($od['od_receipt_bank']) ?></td>
                        </tr>
                        <tr>
                            <th class="sodr_sppay">승인번호</th>
                            <td><?php echo $od['od_receipt_bank_no'] ?></td>
                        </tr>
                        <tr>
                            <th class="sodr_sppay">카드 승인일시</th>
                            <td>
                                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>신용카드 결제 일시 정보가 없습니다.
                                <?php } else { ?><?php echo substr($od['od_receipt_time'], 0, 20); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>

                        <?php if ($od['od_settle_case'] == 'KAKAOPAY') { ?>
                        <tr>
                            <th class="sodr_sppay">KAKOPAY 결제금액</th>
                            <td>
                                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>0원
                                <?php } else { ?><?php echo display_price($od['od_receipt_price']); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="sodr_sppay">KAKAOPAY 승인일시</th>
                            <td>
                                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>신용카드 결제 일시 정보가 없습니다.
                                <?php } else { ?><?php echo substr($od['od_receipt_time'], 0, 20); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>

                        <?php if ($od['od_settle_case'] == '간편결제' || ($od['od_pg'] == 'inicis' && is_inicis_order_pay($od['od_settle_case']) ) ) { ?>
                        <tr>
                            <th class="sodr_sppay"><?php echo $s_receipt_way; ?> 결제금액</th>
                            <td>
                                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>0원
                                <?php } else { ?><?php echo display_price($od['od_receipt_price']); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="sodr_sppay"><?php echo $s_receipt_way; ?> 승인일시</th>
                            <td>
                                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") { echo $s_receipt_way; ?> 결제 일시 정보가 없습니다.
                                <?php } else { ?><?php echo substr($od['od_receipt_time'], 0, 20); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th>상태</th>
                            <!--<td class="bold <?php echo $od['od_pay_state'] ? '' : 'red'; ?>"><?php echo $od['od_pay_state'] == '1' ? '결제완료' : ($od['od_pay_state'] == '2' ? '결제후 출고' : '미결제'); ?></td>-->
                            <td class="bold">
                                <span class="pay-state" style="color:<?php echo $pay_status['color']; ?>;"><?php echo $pay_status['name']; ?></span>
                            </td>
                        </tr>
                        <?php if( $od["od_pg"] == "kcp" && $od['od_settle_case'] == '신용카드' && $od['od_pay_state'] == '1'): ?>
                        <tr>
                            <th>매출전표</th>
                            <td class="bold">
                                <span><a href="javascript:showKcpWindow()" id="jonpyu">보기</a></span>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($od['od_settle_case'] == '네이버페이') {  ?>
                        <tr>
                            <th>결제방법</th>
                            <td>
                                <?php echo $od['od_naver_PaymentMeans'];?>
                            </td>
                        </tr>
                        <?php if ($od['od_receipt_bank']) { ?>
                        <tr>
                            <th class="sodr_sppay">카드사</th>
                            <td><?php echo get_receipt_bank_name_by_value($od['od_receipt_bank']) ?></td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th>매출증빙일시</th>
                            <td>
                                <?php echo $od['od_receipt_time'];?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="absolutebtndiv">
                    <a class="shbtn" id="edit_payment">변경</a>
                </div>
            </div>
            <div class="maechul_zhengming">
                <h3 class="box_sub_title">매출증빙</h3>
                <form id="typereceipt_after" class="typereceipt_after" name="typereceipt">
                    <table class="payment-table">
                        <tbody>
                            <tr>
                                <th>분류</th>
                                <td>
                                    <select name="ot_typereceipt_cate" class="type_select">
                                    <?php foreach($typereceipt_cates as $c) { ?>
                                        <option value="<?php echo $c['val']; ?>" <?php echo $typereceipt['ot_typereceipt_cate'] == $c['val'] ? 'selected' : ''; ?>><?php echo $c['name']; ?> <?php echo $c['val']; ?></option>
                                    <?php } ?>
                                    </select>
                                    <span class="tax_info" style="padding:5px 10px">*과세분류 (0)</span>

                                </td>
                                <script type="text/javascript" charset="utf-8">
                                    jQuery('.type_select').change(function() {
										var state = jQuery('.type_select option:selected').val();
										if ( state == '31' || state == '14') {
											$( ".tax_info" ).text( "*과세분류 (3)" );
										} else if (state == '16'){
											$( ".tax_info" ).text( "*과세분류 (1)" );
										} else{
											$( ".tax_info" ).text( "*과세분류 (0)" );
										}
									});
                                </script>
                            </tr>
                            <tr>
                                <th>매출증빙</th>
                                <td>
                                    <div style="display: inline-block;width:90%;">
                                        <input type="radio" name="ot_typereceipt" id="typereceipt0" value="0" <?php echo $typereceipt['ot_typereceipt'] == '0' ? 'checked="checked"' : ''; ?>> <label for="typereceipt0">발급안함</label>
                                        <input type="radio" name="ot_typereceipt" id="typereceipt2" value="31" <?php echo $typereceipt['ot_typereceipt'] == '31' ? 'checked="checked"' : ''; ?>> <label for="typereceipt2">현금영수증 </label>
                                        <input type="radio" name="ot_typereceipt" id="typereceipt1" value="11" <?php echo $typereceipt['ot_typereceipt'] == '11' ? 'checked="checked"' : ''; ?>> <label for="typereceipt1">세금계산서 </label>
                                        <div id="typereceipt2_view">
                                            <ul id="cash_container" class="typereceiptlay">
                                                <li>
                                                    <input type="radio" name="ot_typereceipt_cuse" class="typereceipt_cuse" id="cuse0" value="1" <?php echo $typereceipt['ot_typereceipt_cuse'] == '1' ? 'checked="checked"' : ''; ?>> <label for="cuse0">개인 소득공제</label>
                                                    <input type="radio" name="ot_typereceipt_cuse" class="typereceipt_cuse" id="cuse1" value="2" <?php echo $typereceipt['ot_typereceipt_cuse'] == '2' ? 'checked="checked"' : ''; ?>> <label for="cuse1">사업자 지출증빙</label>
                                                </li>
                                                <li class="personallay">
                                                    <input type="text" name="p_typereceipt_btel" value="<?php echo $typereceipt['ot_btel'] ?>" class="line number frm_input" maxlength="13" title="휴대폰번호('-' 없이 입력)" placeholder="휴대폰번호('-' 없이 입력)">
                                                </li>
                                                <li class="businesslay" style="display:none;">
                                                    <input type="text" name="p_typereceipt_bnum" value="<?php echo $typereceipt['ot_bnum'] ?>" class="line number frm_input" maxlength="12" title="사업자번호('-' 없이 입력)" placeholder="사업자번호('-' 없이 입력)">
                                                </li>
                                                <li>
                                                    <input type="text" name="p_typereceipt_email" value="<?php echo $typereceipt['ot_tax_email'] ?>" class="line frm_input" title="이메일주소" placeholder="이메일주소">
                                                </li>
                                            </ul>
                                        </div>
                                        <div id="typereceipt1_view">
                                        <ul id="tax_container" class="typereceiptlay">
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <th scope="row" style="width: 100px;">
                                                        <label for="ot_bname">기업명</label>
                                                    </th>
                                                    <td colspan="3">
                                                        <input type="text" name="ot_bname" value="<?php echo $typereceipt['ot_bname'] ?>" id="ot_bname" class="frm_input" size="30" maxlength="20">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">
                                                        <label for="ot_boss_name">대표자명</label>
                                                    </th>
                                                    <td colspan="3">
                                                        <input type="text" name="ot_boss_name" value="<?php echo $typereceipt['ot_boss_name'] ?>" id="ot_boss_name" class="frm_input" size="30" maxlength="20">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">
                                                        <label for="ot_btel">연락처</label>
                                                    </th>
                                                    <td colspan="3">
                                                        <input type="text" name="ot_btel" value="<?php echo $typereceipt['ot_btel'] ?>" id="ot_btel" class="frm_input" size="30" maxlength="13">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">
                                                        <label for="ot_bnum">사업자번호</label>
                                                    </th>
                                                    <td colspan="3">
                                                        <input type="text" name="ot_bnum" value="<?php echo $typereceipt['ot_bnum'] ?>" id="ot_bnum" class="frm_input" size="30" maxlength="12">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">
                                                        <label for="ot_location_zip">사업장소재지</label>
                                                    </th>
                                                    <td colspan="3">
                                                        <label for="ot_location_zip" class="sound_only">우편번호</label>
                                                        <input type="text" name="ot_location_zip" value="<?php echo get_text($typereceipt['ot_location_zip1']).get_text($typereceipt['ot_location_zip2']); ?>" id="ot_location_zip" required class="frm_input required" size="14">
                                                        <button type="button" class="shbtn" onclick="win_zip('typereceipt', 'ot_location_zip', 'ot_location_addr1', 'ot_location_addr2', 'ot_location_addr3', 'ot_location_jibeon');">주소 검색</button><br>
                                                        <input type="text" name="ot_location_addr1" value="<?php echo get_text($typereceipt['ot_location_addr1']); ?>" id="ot_location_addr1" required class="frm_input required" size="30" placeholder="기본주소">
                                                        <input type="text" name="ot_location_addr2" value="<?php echo get_text($typereceipt['ot_location_addr2']); ?>" id="ot_location_addr2" class="frm_input" size="30" placeholder="상세주소"><br/>
                                                        <input type="text" name="ot_location_addr3" value="<?php echo get_text($typereceipt['ot_location_addr3']); ?>" id="ot_location_addr3" class="frm_input" size="30" placeholder="지번주소">
                                                        <input type="hidden" name="ot_location_jibeon" value="<?php echo get_text($typereceipt['ot_location_jibeon']); ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">
                                                        <label for="ot_buptae">업태</label>
                                                    </th>
                                                    <td colspan="3">
                                                        <input type="text" name="ot_buptae" value="<?php echo $typereceipt['ot_buptae'] ?>" id="ot_buptae" class="frm_input" size="30" maxlength="20">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">
                                                        <label for="ot_bupjong">업종</label>
                                                    </th>
                                                    <td colspan="3">
                                                        <input type="text" name="ot_bupjong" value="<?php echo $typereceipt['ot_bupjong'] ?>" id="ot_bupjong" class="frm_input" size="30" maxlength="20">
                                                    </td>
                                                </tr>
                                                <?php
                                                $sql = "SELECT * FROM g5_member_giup_manager WHERE mb_id = '{$od['mb_id']}'";
                                                $result = sql_query($sql);
                                                $managers = array();
                                                $colspan = 2;
                                                while ($m_row = sql_fetch_array($result)) {
                                                    $managers[] = $m_row;
                                                }
                                                if (!count($managers)) {
                                                    array_push($managers, array());
                                                    $colspan = 3;
                                                }
                                                ?>
                                                <tr>
                                                    <th scope="row">
                                                        <label for="ot_tax_email">이메일</label>
                                                    </th>
                                                    <?php if (count($managers) > 0) { ?>
                                                    <style>
                                                        .reduce_width {
                                                            width: 219px !important;
                                                        }

                                                        #giup_manager_sel {
                                                            width: 105px !important;
                                                        }
                                                    </style>
                                                    <script>
                                                        $(function () {
                                                            $('#giup_manager_sel').change(function () {
                                                                var selectedManager = $(this).find("option:selected");
                                                                var selectedManagersEmail = selectedManager.data('email');

                                                                $('input[name=ot_tax_email]').val(selectedManagersEmail);
                                                            })
                                                        })
                                                    </script>
                                                    <td colspan="1" style="width: 105px">
                                                        <select id="giup_manager_sel">
                                                            <option data-email="<?php echo $typereceipt['ot_tax_email'] ?>" selected>담당자 선택</option>
                                                            <?php for ($m = 0; $m < count($managers); $m++) { ?>
                                                            <option data-email="<?php echo $managers[$m]['mm_email'] ?>"><?php echo $managers[$m]['mm_name'] ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <?php } ?>
                                                    <td colspan="<?php echo $colspan ?>">
                                                        <input type="text" name="ot_tax_email" value="<?php echo $typereceipt['ot_tax_email'] ?>" id="ot_tax_email" class="frm_input reduce_width" size="30" maxlength="30">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">
                                                        <label for="ot_manager_name">담당자명</label>
                                                    </th>
                                                    <td colspan="3">
                                                        <input type="text" name="ot_manager_name" value="<?php echo $typereceipt['ot_manager_name'] ?>" id="ot_manager_name" class="frm_input" size="30" maxlength="20">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            <!-- <tr>
                                <th>승인일</th>
                                <td>
                                    <input type="text" class="frm_input" name="ot_time_date" id="ot_time_date" value="<?php echo $typereceipt['ot_time_date']; ?>" style="width:30%;" />
                                    <input type="text" class="frm_input" name="ot_time_hour" value="<?php echo $typereceipt['ot_time_hour']; ?>" style="width:10%;" />&nbsp;시
                                </td>
                            </tr> -->
                            <tr>
                                <th>식별번호</th>
                                <td>
                                    <input type="text" class="frm_input" name="ot_confirm_number" value="<?php echo htmlspecialchars($typereceipt['ot_confirm_number']); ?>" style="width:150px;" /> 예) 전화번호/사업자번호/자진번호

                                </td>
                            </tr>
                            <tr>
                                <th>비고</th>
                                <td>
                                    <input type="text" class="frm_input" name="ot_etc" value="<?php echo htmlspecialchars($typereceipt['ot_etc']); ?>" style="width:70%;" />
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2">
                                    &nbsp;
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </form>
                <table id="typereceipt_before" class="typereceipt_before payment-table">
                    <tbody>
                        <?php if ( $typereceipt['ot_typereceipt_cate'] ) { ?>
                        <tr>
                            <th>분류</th>
                            <td>
                                <?php echo $typereceipt_cate['name']; ?>
                                <?php echo $typereceipt_cate['val'] ? $typereceipt_cate['val'] : ''; ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th>매출증빙</th>
                            <td>
                                <?php echo $typereceipt['name']; ?>
                                <?php echo $typereceipt['cuse'] ? '(' . $typereceipt['cuse']['name'] . ')' : ''; ?>
                            </td>
                        </tr>
                        <?php if ( $typereceipt['ot_bname'] ) { ?>
                        <tr>
                            <th>기업명</th>
                            <td>
                                <?php
                                    echo htmlspecialchars($typereceipt['ot_bname']);
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if ( $typereceipt['ot_boss_name'] ) { ?>
                        <tr>
                            <th>대표자명</th>
                            <td>
                                <?php
                                    echo htmlspecialchars($typereceipt['ot_boss_name']);
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if ( $typereceipt['ot_bnum'] ) { ?>
                        <tr>
                            <th>사업자번호</th>
                            <td>
                                <?php
                                    echo htmlspecialchars($typereceipt['ot_bnum']);
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if ( $typereceipt['ot_bnum']  && $typereceipt['name'] == '세금계산서') { ?>
                        <tr>
                            <th>사업장소재지</th>
                            <td>
                                <?php
                                    echo htmlspecialchars($typereceipt['ot_location_addr1']) . htmlspecialchars($typereceipt['ot_location_addr2']);
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if ( $typereceipt['ot_buptae'] ) { ?>
                        <tr>
                            <th>업태/업종</th>
                            <td>
                                <?php
                                    echo htmlspecialchars($typereceipt['ot_buptae']);
                                    echo ' / ';
                                    echo htmlspecialchars($typereceipt['ot_bupjong']);
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if ( $typereceipt['ot_tax_email'] ) { ?>
                        <tr>
                            <th>이메일</th>
                            <td>
                                <?php
                                    echo htmlspecialchars($typereceipt['ot_tax_email']);
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if ( $typereceipt['ot_btel'] && $typereceipt['cuse']['name'] == '개인소득공제') { ?>
                        <tr>
                            <th>연락처(현금영수증)</th>
                            <td>
                                <?php
                                    echo htmlspecialchars($typereceipt['ot_btel']);
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if ( $typereceipt['ot_manager_name'] ) { ?>
                        <tr>
                            <th>담당자명</th>
                            <td>
                                <?php
                                    echo htmlspecialchars($typereceipt['ot_manager_name']);
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if ( $typereceipt['ot_time_date'] ) { ?>
                        <!-- <tr>
                            <th>승인일</th>
                            <td>
                                <?php echo $typereceipt['ot_time_date']; ?>&nbsp;
                                <?php echo $typereceipt['ot_time_hour']; ?>시
                            </td>
                        </tr> -->
                        <?php } ?>
                        <?php if ( $typereceipt['ot_confirm_number'] ) { ?>
                        <tr>
                            <th>식별변호</th>
                            <td>
                                <?php echo htmlspecialchars($typereceipt['ot_confirm_number']); ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if ( $typereceipt['ot_etc'] ) { ?>
                        <tr>
                            <th>비고</th>
                            <td>
                                <?php echo htmlspecialchars($typereceipt['ot_etc']); ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th>상태</th>
                            <td>
                                <?php
                                    if ( $typereceipt['ot_state'] === NULL ) {
                                        echo '정보없음';
                                    }
                                    if ( $typereceipt['ot_state'] === '0' ) {
                                        echo '발급대기';
                                    }
                                    if ( $typereceipt['ot_state'] == 1 ) {
                                        echo '발급완료';
                                    }
                                    if ( $typereceipt['ot_state'] == 2 ) {
                                        echo '발급실패';
                                    }
                                ?>
                                <!--<button type="button" class="shbtn">발급완료</button>-->
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="absolutebtndiv">
                    <a class="shbtn typereceipt_before typereceipt_before_btn">수정</a>
                    <a class="shbtn typereceipt_after typereceipt_after_submit">완료</a>
                    <a class="shbtn typereceipt_after typereceipt_after_btn">취소</a>
                </div>
            </div>
        </div>
    </div>
    <div class="block">
        <div class="header">
            <h2>관리자메모</h2>
            <div class="right">
            </div>
        </div>
        <div class="memo">
            <div class="block-box memo">
                <?php
                $sql = "SELECT * FROM g5_shop_order_admin_memo WHERE od_id = '{$od['od_id']}' ORDER BY om_no DESC";
                $result = sql_fetch($sql);
                ?>
                <div class="om_write_box">
                        <textarea name="od_shop_memo" rows="8" placeholder="입력한 메모내용이 보여집니다." id="memo_content"><?php echo htmlspecialchars($result['om_content']); ?></textarea>
                        <input type="button" value="저장" class="btn" id="memo_submit">
                </div>
                <ul class="memo_logs">
                    <?php
                    $sql = "SELECT * FROM g5_shop_order_admin_memo WHERE od_id = '{$od['od_id']}' ORDER BY om_no DESC";
                    $result = sql_query($sql);
                    $memo_counts = 0;
                    while($row = sql_fetch_array($result)) {
                        $om_mb = get_member($row['mb_id']);
                        $memo_counts++;
                    ?>
                    <li>
                        <div class="om_info">
                            <span class="log_datetime"><?php echo $row['om_datetime']; ?></span><?php echo $om_mb['mb_name']; ?> 매니저 수정
                        </div>
                        <div class="om_content">
                            <?php echo nl2br(htmlspecialchars($row['om_content'])); ?>
                        </div>
                    </li>
                    <?php
                    }
                    if ( !$memo_counts ) {
                    ?>
                    <li>
                        기록이 없습니다.
                    </li>
                    <?php
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="block">
        <div class="header">
            <h2>주문취소/반품</h2>
            <div class="right">
                <?php
                $sql = "select *
                        from g5_shop_order_cancel_request
                        where od_id = '{$od['od_id']}' and approved = 0";

                $cancel_request_row = sql_fetch($sql);

                if ($cancel_request_row['request_type'] == 'cancel') {
                    $info = "* 취소 요청이 있습니다. 승인 시 주문이 취소됩니다.";
                }
                if ($cancel_request_row['request_type'] == 'return') {
                    $info = "* 반품 요청이 있습니다. 승인 시 반품 단계로 이동됩니다.";
                }

                if ($cancel_request_row['od_id']) {
                ?>
                <span id="cancel_info"><?php echo $info ?></span> <button type="button" onclick="approveCancel()">승인</button>
                <?php } ?>
                <script>
                    function approveCancel() {
                        if (confirm('승인처리 하시겠습니까?')) {
                            location.href = './orderinquirycancelapprove.php?od_id=<?php echo $od['od_id'] ?>';
                        }
                    }
                </script>
            </div>
        </div>
        <div class="cancel">
            <div class="block-box cancel">
                <div class="om_cancel_write_box">
                    <div class="om_cancel_header">
                        <select name="od_cancel_reason">
                            <option value="단순변심" <?php echo $od['od_cancel_reason'] == '단순변심' ? 'selected' : ''; ?>>단순변심</option>
                            <option value="제품파손" <?php echo $od['od_cancel_reason'] == '제품파손' ? 'selected' : ''; ?>>제품파손</option>
                            <option value="제품하자" <?php echo $od['od_cancel_reason'] == '기타' ? 'selected' : ''; ?>>제품하자</option>
                            <option value="오주문" <?php echo $od['od_cancel_reason'] == '오주문' ? 'selected' : ''; ?>>오주문</option>
                            <option value="오배송" <?php echo $od['od_cancel_reason'] == '오배송' ? 'selected' : ''; ?>>오배송</option>
                            <option value="A/S" <?php echo $od['od_cancel_reason'] == 'A/S' ? 'selected' : ''; ?>>A/S</option>
                            <option value="기타" <?php echo $od['od_cancel_reason'] == '기타' ? 'selected' : ''; ?>>기타</option>
                        </select>
                        <div id="g5_shop_order_cancel_file">
                            <button type="button" class="shbtn uploadbtn">찾아보기</button>
                            <ul class="upload_files upload_files_cancel_apply">
                                <?php
                                $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_type = 'cancel_apply'";
                                $result = sql_query($sql);
                                while ($row = sql_fetch_array($result)) {
                                ?>
                                    <li>
                                        <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink" target="_blank"><?php echo $row['real_name']; ?></a>
                                        <a href='#' class="remove" data-no="<?php echo $row['no']; ?>" ><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_del_s.png" /></a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <input name="od_cancel_memo" rows="8" placeholder="입력한 메모내용이 보여집니다." value="<?php echo get_text($od['od_cancel_memo']); ?>" id="cancel_memo_content" />
                    <input type="button" value="신청" class="btn shbtn" id="cancel_submit">
                </div>
            </div>
        </div>
    </div>

    <div class="block">
        <div class="header">
            <h2>기록</h2>
            <div class="right">
            </div>
        </div>
        <div class="block-box gray logs">
            <?php
            $logs = get_order_admin_log($od['od_id']);
            // print_r2($logs);
            foreach($logs as $log) {
                $log_mb = get_member($log['mb_id']);
                echo '<span class="log_datetime">'.$log['ol_datetime'] . '</span>(' . $log_mb['mb_name'] . ' 매니저) ' . $log['ol_content'] . '<br/>';
            }
            if (!count($logs)) {
                echo '기록이 없습니다.';
            }
            ?>
        </div>
    </div>
    <div id="order_summarize">
        <div class="header">
            <h1>
                <?php
                echo $od_status['name'];
                ?>
            </h1>
            <button class="shbtn order_prints">작업지시서 출력</button>
            <div class="more">
                <button><img src='<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_more_w.png' /></button>
                <ul class="openlayer">
                    <?php if ( $od['od_status'] != '주문무효' ) { ?>
                        <li id="order_cancel">주문무효 처리</li>
                    <?php } ?>
                    <li id="order_copy">주문서 복사</li>
                    <?php if ( $prev_step ) { ?>
                        <li id="order_prev_step" data-prev-step-val="<?php echo $prev_step['val']; ?>"><?php echo $prev_step['name']; ?>단계로 되돌리기</li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="content">
            <?php if ( $od_status['val'] == '작성' ) { ?>
            <div class="change_member" id="od_change_member">
                <a><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_order_member.png" /></a>
            </div>
            <?php } ?>
            <div class="block">
                <h2>주문번호 <?php echo $od['od_id']; ?></h2>
                <span class="so_nb"> SO-NB <?php echo $od['so_nb']; ?></span>
            </div>
            <div class="block">
                <?php if($mb['mb_id']) { ?>
                <a href="<?php echo G5_ADMIN_URL; ?>/member_form.php?&w=u&mb_id=<?php echo $mb['mb_id']; ?>" target="_blank" class="h2">
                    <?php echo $mb['mb_name']; ?><span>(<?php echo $mb['mb_id']; ?>)</span>
                </a>
                <?php }else{ ?>
                    <a href="#" class="h2">비회원</a>
                <?php } ?>
                <?php echo $od['od_send_admin_memo'] ?>
                <p>
                <?php if ( $od['od_deposit_name'] ) { ?>
                    <?php echo $od['od_deposit_name']; ?>, <?php echo $od['od_bank_account']; ?><br/>
                <?php } ?>
                <?php echo $od['od_name']; ?> (<?php echo $od['od_email']; ?>)<br/>
                HP : <?php echo $od['od_hp']; ?> / Tel : <?php echo $od['od_tel']; ?>
                </p>
                <?php
                $customer_code = get_customer_code($od['od_id']); 
                $customer_code_step = get_customer_step($customer_code);
                ?>
                고객코드: <?php echo $customer_code; ?> (<?php echo $customer_code_step; ?>)
                <br/><br/>
                <a class="shbtn send_estimate">
                    견적서 전송
                </a>
            </div>
            <div class="block">
                <h2>담당자</h2>
                <ul>
                    <li>
                        <span class="manager_name">- 영업담당자</span>
                        <div class="managers">
                            <div class="on">
                                <select name="od_sales_manager">
                                    <option value="">없음</option>
                                    <?php
                                    $sql = "SELECT * FROM g5_auth WHERE au_menu = '400400' AND au_auth LIKE '%w%'";
                                    $auth_result = sql_query($sql);
                                    while($a_row = sql_fetch_array($auth_result)) {
                                        $a_mb = get_member($a_row['mb_id']);
                                    ?>
                                        <option value="<?php echo $a_mb['mb_id']; ?>" <?php echo $a_mb['mb_id'] == $od['od_sales_manager'] ? 'selected' : ''; ?>><?php echo $a_mb['mb_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <a class="change_manager_on change_manager_submit" data-type="od_sales_manager">변경</a>
                                <a class="change_manager_on change_manager_cancel">취소</a>
                            </div>
                            <div class="off">
                                <?php
                                $od_sales_manager = get_member($od['od_sales_manager']);
                                if ($od_sales_manager) {
                                    echo $od_sales_manager['mb_name']; ?> 담당자 <a class="change_manager_off">변경</a>
                                <?php
                                } else {
                                ?>
                                    <a class="change_manager_off">선택</a>
                                <?php } ?>
                            </div>
                        </div>
                    </li>
                    <li>
                        <span class="manager_name">- 출고담당자</span>
                        <div class="managers">
                            <div class="on">
                                <select name="od_release_manager">
                                    <option value="">미지정</option>
                                    <option value="no_release" <?php echo 'no_release' == $od['od_release_manager'] ? 'selected' : ''; ?>>출고아님</option>
                                    <option value="-" <?php echo '-' == $od['od_release_manager'] ? 'selected' : ''; ?>>외부출고</option>
                                    <?php
                                    $sql = "SELECT * FROM g5_auth WHERE au_menu = '400402' AND au_auth LIKE '%w%'";
                                    $auth_result = sql_query($sql);
                                    while($a_row = sql_fetch_array($auth_result)) {
                                        $a_mb = get_member($a_row['mb_id']);
                                    ?>
                                        <option value="<?php echo $a_mb['mb_id']; ?>" <?php echo $a_mb['mb_id'] == $od['od_release_manager'] ? 'selected' : ''; ?>><?php echo $a_mb['mb_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <a class="change_manager_on change_manager_submit" data-type="od_release_manager">변경</a>
                                <a class="change_manager_on change_manager_cancel">취소</a>
                            </div>
                            <div class="off">
                                <?php
                                $od_release_manager = get_member($od['od_release_manager']);
                                if ($od_release_manager) {
                                    echo $od_release_manager['mb_name']; ?> 담당자 <a class="change_manager_off">변경</a>
                                <?php } else if ($od['od_release_manager'] == 'no_release') { ?>
                                    <span style="color: #ff3061;">출고아님</span> <a class="change_manager_off">변경</a>
                                <?php } else if ($od['od_release_manager'] == '-') { ?>
                                    외부출고 <a class="change_manager_off">변경</a>
                                <?php } else { ?>
                                    <a class="change_manager_off">선택</a>
                                <?php } ?>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="block">
                <h2>결제정보</h2>
                <ul class="bill_info">
                    <!--
                    <li>
                        <div class="left">주문금액</div>
                        <div class="right"><?php echo number_format($amount['order']); ?>원</div>
                    </li>
                    <li>
                        <div class="left">총결제액</div>
                        <div class="right"><?php echo number_format($amount['receipt']); ?>원</div>
                    </li>
                    <li>
                        <div class="left">쿠폰금액</div>
                        <div class="right"><?php echo number_format($amount['coupon']); ?>원</div>
                    </li>
                    <li>
                        <div class="left">취소금액</div>
                        <div class="right"><?php echo number_format($amount['cancel']); ?>원</div>
                    </li>
                    -->
                    <li>
                        <div class="left">판매금액</div>
                        <div class="right"><?php echo number_format($tot_price); ?>원</div>
                    </li>
                    <li>
                        <div class="left">배송비</div>
                        <div class="right"><?php echo number_format($od['od_send_cost'] + $od['od_send_cost2']); ?>원</div>
                    </li>
                    <li>
                        <div class="left">할인</div>
                        <div class="right"><span class="red"> <?php echo number_format($tot_discount); ?>원</span></div>
                    </li>
                    <li>
                        <div class="left">추가할인/금액추가</div>
                        <div class="right"><span class="red"> <?php echo number_format($od['od_cart_discount2']); ?>원</span></div>
                    </li>
                    <li>
                        <div class="left"><b>총금액</b></div>
                        <div class="right"><b><?php echo number_format($tot_total + $od['od_send_cost'] + $od['od_send_cost2'] - $od['od_cart_discount2']); ?>원</b></div>
                    </li>
                </ul>
            </div>
            <div class="block">
                <div class="oneline">
                    <div class="left">결제상태</div>
                    <div class="left">
                        <?php echo $od['od_pay_state'] == '1' ? '결제완료' : ($od['od_pay_state'] == '2' ? '결제후 출고' : '미결제'); ?>
                        (<?php echo $s_receipt_way; ?>)
                    </div>
                </div>
                <!-- <div class="oneline">
                    <div class="left">매출증빙</div>
                    <div class="left">
                        안녕하세요 <a class="view_maechul_zhengming">보기</a>
                    </div>
                </div> -->
            </div>
            <!--
            <div class="block">
                <h2>진행단계</h2>
                <?php
                $sub_menu_name = 'orderlist';
                $sub_menu_name = $sub_menu == '400400' ? 'orderlist' : $sub_menu_name;
                $sub_menu_name = $sub_menu == '400401' ? 'orderlist_complete' : $sub_menu_name;
                $sub_menu_name = $sub_menu == '400403' ? 'cancellist' : $sub_menu_name;
                $sub_menu_name = $sub_menu == '400402' ? 'deliverylist' : $sub_menu_name;
                ?>
                <table>
                    <tbody>
                        <tr>
                            <?php
                            foreach($order_steps as $order_step) {
                                if ( $order_step[$sub_menu_name] == true ) {
                                    echo '<th>'. $order_step['name'] .'</th>';
                                }
                            }
                            ?>
                        </tr>
                        <tr>
                            <?php
                            foreach($order_steps as $order_step) {
                                if ( $order_step[$sub_menu_name] == true ) {
                                    echo '<td>'. ( $cate_counts[$order_step['val']] ? $cate_counts[$order_step['val']] : 0 ) .'</td>';
                                }
                            }
                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            -->
        </div>
        <?php if ( $next_step ) { ?>
        <div class="submit">
            <button id="order_summarize_submit" data-next-step-val="<?php echo $next_step['val']; ?>">
                <?php echo $next_step['name']; ?>
            </button>
        </div>
        <?php } ?>
    </div>
</div>

<div class="btn_fixed_top">
    <?php if ($sub_menu == '400400') { ?>
    <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_orderlist.php" class="btn btn_02">목록</a>
    <?php } ?>
    <?php if ($sub_menu == '400401') { ?>
    <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_orderlist_complete.php" class="btn btn_02">목록</a>
    <?php } ?>
    <?php if ($sub_menu == '400402') { ?>
    <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_deliverylist.php" class="btn btn_02">목록</a>
    <?php } ?>
    <?php if ($sub_menu == '400403') { ?>
    <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_cancellist.php" class="btn btn_02">목록</a>
    <?php } ?>
    <a href="#" class="btn btn_01 order_prints">작업지시서 출력</a>
</div>

<script>
var change_member_pop, add_item_pop, matching_item_pop, edit_item_pop, delivery_print_pop, edit_payment_pop, send_estimate_pop, order_prints_pop;

$(document).ready(function() {
    // 오른쪽 고정
    /*
	$("#order_summarize").sticky({
		topSpacing: 0,
		className: "fixed"
	});
    */

    var offset = $('#order_summarize').offset();

    function fixed_container() {
        if ( $( document ).scrollTop() > offset.top ) {
            $('#order_summarize').addClass('fixed');
        }else {
            $('#order_summarize').removeClass('fixed');
        }
    }

    $( window ).scroll( function() {
        fixed_container();
    });
    fixed_container();

    // 담당자 변경
    $('.change_manager_off').click(function() {
        var off = $(this).closest('.off');
        var on = $(this).closest('.managers').find('.on');

        $(off).hide();
        $(on).show();
    });
    $('.change_manager_cancel').click(function() {
        var on = $(this).closest('.on');
        var off = $(this).closest('.managers').find('.off');

        $(on).hide();
        $(off).show();
    });
    $('.change_manager_submit').click(function() {
        var type = $(this).data('type');
        var mb_id = $('select[name="' + type + '"]').val();
        $.ajax({
                    method: "POST",
                    url: "./ajax.order.manager.php",
                    data: {
                        type: type,
                        mb_id: mb_id,
                        od_id: od_id,
                    },
                })
        .done(function(data) {
            // console.log(data);
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }

        })
    });

    $('#order_summarize_submit').click(function() {
        var next_step_val = $(this).data('next-step-val');
        change_step(od_id, next_step_val);
    });

    $('#order_prev_step').click(function() {
        var prev_step_val = $(this).data('prev-step-val');
        change_step(od_id, prev_step_val);
    });

    $('#order_cancel').click(function() {
        var step_val = '주문무효';
        change_step(od_id, step_val);
    });

    $('#order_copy').click(function() {
        if (confirm("주문서를 복사하시겠습니까?")) {
            location.href = "./samhwa_order_copy.php?od_id=" + od_id;
        }
    });

    $('#memo_submit').click(function() {
        var content = $('#memo_content').val();

        if ( !content.length ) {
            alert('메모 내용을 입력하세요.');
            return;
        }
        $.ajax({
                    method: "POST",
                    url: "./ajax.order.memo.php",
                    data: {
                        od_id: od_id,
                        content: content,
                    },
                })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }
        })
    });

    // 전체 옵션선택
    $("#sit_select_all").click(function() {
        if($(this).is(":checked")) {
            $("input[name='it_sel[]']").attr("checked", true);
            $("input[name^=ct_chk]").attr("checked", true);
        } else {
            $("input[name='it_sel[]']").attr("checked", false);
            $("input[name^=ct_chk]").attr("checked", false);
        }
    });

    // 상품의 옵션선택
    $("input[name='it_sel[]']").click(function() {
        var cls = $(this).attr("id").replace("sit_", "sct_");
        var $chk = $("input[name^=ct_chk]."+cls);
        if($(this).is(":checked"))
            $chk.attr("checked", true);
        else
            $chk.attr("checked", false);
    });

    $('#change_cart_status').click(function() {
        var formdata = $.extend(
            {},
            $('#frmsamhwaorderform').serializeObject(),
            {
                od_id: od_id,
            }
        );

        if (formdata['ct_chk[]'] === undefined) {
            alert('상품을 체크해주세요.');
            return;
        }

        $.ajax({
                    method: "POST",
                    url: "./ajax.cart.step.php",
                    data: formdata,
                })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }
        })
    });

    //배송정보 수정
    $('#delivery_info_btn').click(function() {
        var od_delivery_type_data = $('#od_delivery_type').find(':selected').data('type');
        var formdata = $.extend(
            {},
            $('#frmsamhwaorderdeliveryform').serializeObject(),
            {
                od_id: od_id,
                od_delivery_type_data: od_delivery_type_data,
            }
        );

        $.ajax({
                    method: "POST",
                    url: "./ajax.order.delivery.php",
                    data: formdata,
                })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }
        })
    });

    // 배송 선택
    function selected_delivery_type() {
        var checked = $('#od_delivery_type').find(':selected').data('type');

        $('.delivery_block .delivery_types').hide();
        $('.delivery_block .delivery_types.' + checked).show();
        selected_delivery_company();
    }
    $('#od_delivery_type').change(function() {
        selected_delivery_type();
        clear_form('.delivery_block');
    });
    selected_delivery_type();

    // 택배 배송 회사 선택
    function selected_delivery_company() {
        var checked = $('select[name="od_delivery_company[delivery]"]').find(':selected').val();
        var checked2 = $('#od_delivery_type').find(':selected').data('type');

        if (checked === 'ilogen' && checked2 === 'delivery') {
            $('.delivery_edi_div').show();
            // $('input[name="od_delivery_text[delivery]"]').attr("readonly",true);
        }else{
            $('.delivery_edi_div').hide();
            // $('input[name="od_delivery_text[delivery]"]').attr("readonly",false);
        }
    }
    $('select[name="od_delivery_company[delivery]"]').change(function() {
        selected_delivery_company();
    });
    selected_delivery_company();

    // 출고예정일
    $("#od_ex_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        maxDate: "+365d"
    });

    // 주문자 회원 변경
    $('#od_change_member').click(function() {
        change_member_pop = window.open('./pop.order.change_member.php?od_id=' + od_id, "change_member_pop", "width=430, height=600, resizable = no, scrollbars = no");
    });

    // 견적서 발송
    $('.send_estimate').click(function() {
        send_estimate_pop = window.open('<?php echo G5_SHOP_URL; ?>/pop.estimate.php?od_id=' + od_id, "send_estimate", "width=730, height=800, resizable = no, scrollbars = no");
    });

    // 결제정보 수정
    $('#edit_payment').click(function() {
        edit_payment_pop = window.open('./pop.order.payment.edit.php?od_id=' + od_id, "edit_payment_pop", "width=750, height=900, resizable = no, scrollbars = no");
    });

    // 상품 추가
    $('#add_item').click(function() {
        add_item_pop = window.open('./pop.order.item.add.php?od_id=' + od_id, "add_item_pop", "width=1080, height=900, resizable = no, scrollbars = yes");
    });


    // 상품 수정
    $('.edit_item').click(function() {

        var it_id = $(this).data('it-id');
        var uid = $(this).data('uid');

        edit_item_pop = window.open('./pop.order.item.add.option.php?w=1&od_id=' + od_id + '&it_id=' + it_id + '&uid=' + uid, "edit_item_pop", "width=1080, height=900, resizable = no, scrollbars = no");
    });

    // 상품 삭제
    $('.delete_item').click(function() {

        var it_id = $(this).data('it-id');
        var uid = $(this).data('uid');

        $.ajax({
                    method: "POST",
                    url: "./ajax.order.item.delete.php",
                    data: {
                        od_id: od_id,
                        it_id: it_id,
                        uid: uid,
                    },
                })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }
        })
    })

    // EDI 전송
    $('.delivery_edi').click(function() {
        var od_delivery_type_data = $('#od_delivery_type').find(':selected').data('type');
        var formdata = $.extend(
            {},
            $('#frmsamhwaorderdeliveryform').serializeObject(),
            {
                od_id: od_id,
                od_delivery_type_data: od_delivery_type_data,
            }
        );

        $.ajax({
                    method: "POST",
                    url: "./ajax.order.delivery.edi.php",
                    data: formdata,
                })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }
        })
    });

    // 송장 리턴
    $('.delivery_edi_return').click(function() {

        $.ajax({
            method: "POST",
            url: "./ajax.order.delivery.edi.return.php",
            data: {
                od_id: od_id
            },
        })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }
        })
    });

    //사방넷 배송정보 전송
    $('.delivery_sabangnet_return').click(function() {

        $.ajax({
            method: "POST",
            url: "./ajax.order.delivery.sabangnet.return.php",
            data: {
                od_id: od_id
            },
        })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
				alert('전송이 완료되었습니다.');
                location.reload();
            }
        })
    });

    // 배송정보 프린트
    $('.delivery_print').click(function() {
        var od_delivery_type_data = $('#od_delivery_type').find(':selected').data('type');
        var formdata = $.extend(
            {},
            $('#frmsamhwaorderdeliveryform').serializeObject(),
            {
                od_id: od_id,
                od_delivery_type_data: od_delivery_type_data,
                'type': 'print',
            }
        );

        $.ajax({
                    method: "POST",
                    url: "./ajax.order.delivery.php",
                    data: formdata,
                })
        .done(function(data) {
            if ( data.msg && data.result !== 'success' ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                // location.reload();
                delivery_print_pop = window.open('./pop.order.delivery.print.php?od_id=' + od_id, "delivery_print_pop", "width=855, height=900, resizable = yes, scrollbars = yes");
            }
        })
        //delivery_print_pop = window.open('./pop.order.delivery.print.php?od_id=' + od_id, "delivery_print_pop", "width=835, height=900, resizable = no, scrollbars = no");
    });

    // 작업 지시서
    $('.order_prints').click(function(e) {
        // e.preventdefault();
        order_prints_pop = window.open('./pop.order.prints.php?od_id=' + od_id + '|', "order_prints_pop", "width=850, height=800, resizable = no, scrollbars = yes");
    });



    // 주문취소 파일첨부
    $( document ).on( "click", '#g5_shop_order_cancel_file .uploadbtn', function() {

        var $form = $('<form class="hidden_form"></form>');
        $form.attr('action', './ajax.order.item.add.cart_file_upload.php');
        $form.attr('method', 'post');
        //$form.attr('target', 'iFrm');
        $form.appendTo('body');

        var str = $('<input type="file" name="file" class="g5_shop_order_file_cancel_apply">');
        $form.append(str);
        $form.append('<input type="hidden" name="od_id" value="' + od_id + '" />');
        $form.append('<input type="hidden" name="type" value="cancel_apply" />');

        $($form).find('input[type="file"]').click();
    });

    $( document ).on( "change", '.g5_shop_order_file_cancel_apply', function() {

        var form = $(this).closest('form')[0];

        var form_data = new FormData(form);

        $.ajax({
                type : 'POST',
                enctype: 'multipart/form-data',
                processData : false,
                contentType : false,
                url : "./ajax.order.item.add.cart_file_upload.php",
                data : form_data,
            })
            .done(function(data) {

                if ( data.msg ) {
                    alert(data.msg);
                }

                if ( data.result === 'success' ) {
                    var ret = '';

                    for(var i=0; i<data.data.length;i++) {
                        ret += '<li>';
                        ret += '<a href="/data/order_cart/' + data.data[i]['file_name'] + '" class="filelink" target="_blank">' + data.data[i]['real_name'] + '</a>&nbsp;';
                        ret += '<a class="remove" data-no="' + data.data[i]['no'] + '" ><img src="/adm/shop_admin/img/btn_del_s.png" /></a>';
                        ret += '</li>';
                    }

                    $('.upload_files_cancel_apply').html(ret);
                }
            })

    });

    $( document ).on( "click", '.upload_files_cancel_apply .remove', function() {

        var no = $(this).data('no');
        var obj = $(this);

        var formdata = {
            no: no,
        }
        $.ajax({
            method: "POST",
            url: "./ajax.order.item.add.cart_file_remove.php",
            data: formdata,
        })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }

            if ( data.result === 'success' ) {
                $(obj).closest('li').remove();
            }
        });

    });

    // 주문취소 신청 버튼
    $('#cancel_submit').click(function() {
        var od_cancel_reason = $('select[name="od_cancel_reason"]').val();
        var od_cancel_memo = $('#cancel_memo_content').val();

        $.ajax({
                    method: "POST",
                    url: "./ajax.order.cancel.apply.php",
                    data: {
                        od_id: od_id,
                        od_cancel_reason: od_cancel_reason,
                        od_cancel_memo: od_cancel_memo,
                    },
                })
        .done(function(data) {
            // console.log(data);
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                //location.reload();
                location.href='./samhwa_orderform.php?od_id=' + od_id + '&sub_menu=400403';
            }

        })
    });

    // 매출증빙
    $('#typereceipt2').click(function() {
        if ( $(this).is(':checked') ) {
            $('#typereceipt2_view').show();
            $('#typereceipt1_view').hide();
        }
    });
    $('#typereceipt1').click(function() {
        if ( $(this).is(':checked') ) {
            $('#typereceipt1_view').show();
            $('#typereceipt2_view').hide();
        }
    });
    $('#typereceipt0').click(function() {
        if ( $(this).is(':checked') ) {
            $('#typereceipt1_view').hide();
            $('#typereceipt2_view').hide();
        }
    });

    $('.typereceipt_cuse').click(function() {
        var val = $(this).val();

        if ( val == 1 ) {
            $('.personallay').show();
            $('.businesslay').hide();
        }else{
            $('.personallay').hide();
            $('.businesslay').show();
        }
    });

    $('.typereceipt_before_btn').click(function() {
        $('.typereceipt_before').hide();
        $('.typereceipt_after').show();

        var v = $("input[name='ot_typereceipt']:checked");
        $("input[name='ot_typereceipt']:checked").click();

        console.log(v.val());

        if ( v.val() === 31 ) {
            $("input[name='ot_typereceipt_cuse']:checked").click();
        }


    });

    $('.typereceipt_after_btn').click(function() {
        $('.typereceipt_before').show();
        $('.typereceipt_after').hide();
    });

    // 출고예정일
    $("#ot_time_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        maxDate: "+365d"
    });

    $('.typereceipt_after_submit').click(function() {
        submit_typereceipt_after();
    });

    $('#od_b_tel, #od_b_hp, #ot_btel, input[name="p_typereceipt_btel"]').on('keyup', function(){
        var num = $(this).val();
        num.trim();
        this.value = auto_phone_hypen(num) ;
    });
    $('input[name="p_typereceipt_bnum"], #ot_bnum').on('keyup', function(){
        var num = $(this).val();
        num.trim();
        this.value = auto_saup_hypen(num) ;
    });



    $('.pay-state').click(function() {
        $.ajax({
            method: "POST",
            url: "./ajax.order.paystate.toggle.php",
            data: {
                od_id: od_id,
            },
        })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }
        })
    });

    // 추가할인 적용
    $('#change_discount2').click(function() {

        var od_cart_discount2 = parseInt($('#od_cart_discount2').val());

        //2020-09-07 (-) 적용

        //if ( od_cart_discount2 < 0 ) {
        //    alert('추가할인 금액은 0보다 작은금액을 입력하실 수 없습니다.');
        //    return false;
        //}

        $.ajax({
                    method: "POST",
                    url: "./ajax.order.discount.change.php",
                    data: {
                        od_id: od_id,
                        od_cart_discount2: od_cart_discount2,
                    },
                })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }
        })
    });


    // 외부발주 파일첨부
    $( document ).on( "click", '.it_outsourcing_option_file .uploadbtn', function() {

        var it_id = $(this).closest('td').data('id');
        var uid = $(this).closest('td').data('uid');

        var $form = $('<form class="hidden_form"></form>');
        $form.attr('action', './ajax.order.item.add.cart_file_upload.php');
        $form.attr('method', 'post');
        //$form.attr('target', 'iFrm');
        $form.appendTo('body');

        var str = $('<input type="file" name="file" class="it_outsourcing_option_file_apply">');
        $form.append(str);
        $form.append('<input type="hidden" name="od_id" value="' + od_id + '" />');
        $form.append('<input type="hidden" name="it_id" value="' + it_id + '" />');
        $form.append('<input type="hidden" name="uid" value="' + uid + '" />');
        $form.append('<input type="hidden" name="type" value="order_outsourcing" />');

        $($form).find('input[type="file"]').click();
    });

    $( document ).on( "change", '.it_outsourcing_option_file_apply', function() {

        var form = $(this).closest('form')[0];

        var form_data = new FormData(form);

        var it_id = $(form).find('input[name="it_id"]').val();
        var uid = $(form).find('input[name="uid"]').val();

        $.ajax({
                type : 'POST',
                enctype: 'multipart/form-data',
                processData : false,
                contentType : false,
                url : "./ajax.order.item.add.cart_file_upload.php",
                data : form_data,
            })
            .done(function(data) {

                if ( data.msg ) {
                    alert(data.msg);
                }

                if ( data.result === 'success' ) {
                    var ret = '';

                    for(var i=0; i<data.data.length;i++) {
                        ret += '<li>';
                        ret += '<a href="/data/order_cart/' + data.data[i]['file_name'] + '" class="filelink" target="_blank">' + data.data[i]['real_name'] + '</a>&nbsp;';
                        ret += '<a class="remove" data-no="' + data.data[i]['no'] + '" ><img src="/adm/shop_admin/img/btn_del_s.png" /></a>';
                        ret += '</li>';
                    }

                    // $('.upload_files_outsourcing_option_apply_' + it_id).html(ret);
                    $('.upload_files_outsourcing_option_apply_' + uid).html(ret);
                }
            })
    });

    $( document ).on( "click", '.upload_files_outsourcing_option_apply .remove', function() {

        var no = $(this).data('no');
        var obj = $(this);

        var formdata = {
            no: no,
        }
        $.ajax({
            method: "POST",
            url: "./ajax.order.item.add.cart_file_remove.php",
            data: formdata,
        })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }

            if ( data.result === 'success' ) {
                $(obj).closest('li').remove();
            }
        });

    });

    // 외부 발주 요청
    $('.item_outsourcing_submit').click(function() {

        var parent = $(this).closest('td');
        var it_id = $(parent).data('id');
        var uid = $(parent).data('uid');
        var it_outsourcing_option = $(parent).find('select[name="it_outsourcing_option"]').val();
        var it_outsourcing_option2 = $(parent).find('select[name="it_outsourcing_option2"]').val();
        var it_outsourcing_option3 = $(parent).find('select[name="it_outsourcing_option3"]').val();
        var it_outsourcing_option4 = $(parent).find('select[name="it_outsourcing_option4"]').val();
        var it_outsourcing_option5 = $(parent).find('select[name="it_outsourcing_option5"]').val();
        var sales_manager = $(parent).find('select[name="sales_manager"]').val();

        if (!it_id) {
            alert('알수없는 오류입니다.');
            return false;
        }

        $.ajax({
                    method: "POST",
                    url: "./ajax.order.outsourcing.php",
                    data: {
                        od_id: od_id,
                        it_id: it_id,
                        uid: uid,
                        it_outsourcing_option: it_outsourcing_option,
                        it_outsourcing_option2: it_outsourcing_option2,
                        it_outsourcing_option3: it_outsourcing_option3,
                        it_outsourcing_option4: it_outsourcing_option4,
                        it_outsourcing_option5: it_outsourcing_option5,
                        sales_manager: sales_manager,
                    },
                })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }
        });

    });

    // 외부 발주 취소
    $('.item_outsourcing_cancel').click(function() {

        var oo_id = $(this).data('id');

        if (!oo_id) {
            alert('알수없는 오류입니다.');
            return false;
        }

        $.ajax({
                    method: "POST",
                    url: "./ajax.order.outsourcing.cancel.php",
                    data: {
                        od_id: od_id,
                        oo_id: oo_id,
                    },
                })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }
        });

    });

    // 배송정보 기본정보 반영
    $('#reset_od_info').click(function() {

        $.ajax({
            method: "POST",
            url: "./ajax.order.delivery.reset.php",
            data: {
                od_id: od_id
            },
        })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                location.reload();
            }
        })
    });

    $('#customer_code_sel').change(function () {
        var customer_code = $("option:selected", this).val();

        if (!customer_code) {
            return false;
        }

        $.ajax({
            method: "POST",
            url: "./ajax.order.change.customer.code.php",
            data: {
                od_id: od_id,
                customer_code: customer_code,
            },
        })
        .done(function (data) {
            if (data.msg) {
                alert(data.msg);
            }
            if (data.result === 'success') {
                location.reload();
            }
        });
    });

});

function showKcpWindow()
{
    window.open("https://admin8.kcp.co.kr/assist/bill.BillAction.do?cmd=card_bill&C_TRADE_NO=43A1DA77F005F7EF5F49B1E1D4AFE3FC", "kcpwindow", "width=400,height=600")
}

function submit_typereceipt_after(msgFlag) {
    var formdata = $.extend(
        {},
        $('#typereceipt_after').serializeObject(),
        {
            od_id: od_id,
        }
    );

    $.ajax({
        method: "POST",
        url: "./ajax.order.typereceipt.php",
        data: formdata,
    })
    .done(function (data) {
        if (data.msg) {
            if (msgFlag != false) {
                alert(data.msg);
            }
        }
        if (data.result === 'success') {
            location.reload();
        }
    })
}

</script>
<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>