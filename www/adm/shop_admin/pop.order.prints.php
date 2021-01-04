<?php
include_once('./_common.php');

$od_ids = explode('|', $od_id);

$infos = array();
foreach($od_ids as $od_id) {

    //------------------------------------------------------------------------------
    // 주문서 정보
    //------------------------------------------------------------------------------
    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
    $od = sql_fetch($sql);
    if (!$od['od_id']) {
        continue;
    }

    // 상품목록
    $sql = " select a.it_id,
                    a.it_name,
                    a.od_id,
                    a.cp_price,
                    a.ct_notax,
                    a.ct_send_cost,
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
                    a.ct_uid
            from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
            where a.od_id = '{$od['od_id']}'
            group by a.it_id, a.ct_uid
            order by a.ct_id ";

    $result = sql_query($sql);

    $carts = array();
    $cate_counts = array();
    $it_ids = array();

    for($i=0; $row=sql_fetch_array($result); $i++) {

        $cate_counts[$row['ct_status']] += 1;

        // 상품의 옵션정보
        $sql = " select ct_id, mb_id, it_id, ct_price, ct_point, ct_qty, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, io_type, io_price, pt_msg1, pt_msg2, pt_msg3, ct_discount, od_id, ct_uid
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

            if (!in_array($opt['it_id'], $it_ids)) {
                $it_ids[] = $opt['it_id'];
            }

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
                        and od_id = '{$od['od_id']}' ";
        $sum = sql_fetch($sql);

        $row['sum'] = $sum;

        $carts[] = $row;

    }

    // 주문금액 = 상품구입금액 + 배송비 + 추가배송비 - 할인금액 - 추가할인금액
    $amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'] - $od['od_cart_discount'] - $od['od_cart_discount2'];

    // 입금액 = 결제금액 + 포인트
    $amount['receipt'] = $od['od_receipt_price'] + $od['od_receipt_point'];

    // 쿠폰금액
    $amount['coupon'] = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];

    // 취소금액
    $amount['cancel'] = $od['od_cancel_price'];

    // 견적서 정보
    $sql = " select * from g5_shop_order_estimate where od_id = '$od_id' ";
    $est = sql_fetch($sql);
    
    $delivery = get_delivery_step($od['od_delivery_type']);

    // 메모
    $sql = "SELECT * FROM g5_shop_order_admin_memo WHERE od_id = '{$od['od_id']}' ORDER BY om_no DESC";
    $memo = sql_fetch($sql);

    $infos[] = array(
        'od' => $od,
        'amount' => $amount,
        'carts' => $carts,
        'delivery' => $delivery,
        'it_ids' => $it_ids,
        'memo' => $memo,
    );

}

$title = '결제정보 수정';
include_once('./pop.head.php');
?>

<style>
.pbreak {page-break-after: always;}
#pbreak {page-break-after: always;}

body, input, textarea, select, button, table {
    font-family: '돋움',Dotum,AppleGothic,sans-serif;
    font-size: 12px;
    letter-spacing: 0px;
}

/* 주문 요약 테이블 */
table.order-summary-table {width:100%; border-collapse:collapse; border-bottom:1px solid #ddd; background-color:#ffffe8; }
table.order-summary-table.summary-mode {border-top:0px;}
table.order-summary-table th {padding:0px; height:25px; font-weight:normal; !important; border:1px solid #bcbfc1;}
table.order-summary-table th.dark {background-color:#f1f1f1 !important; border:1px solid #bcbfc1}
table.order-summary-table .lth th{text-align: center;}
/*table.order-summary-table tbody.otb tr:hover {background-color:#dfeaff;}*/
table.order-summary-table tbody.otb tr.order-item-row td {padding:3px 3px; letter-spacing:0px;height:30px;border:1px solid #bcbfc1;}
table.order-summary-table tbody.otb tr.order-item-row td.suboption {background-color:#f6f6f6;padding:3px 3px; letter-spacing:0px;height:25px;border:1px solid #bcbfc1;}
table.order-summary-table tbody.otb tr.order-item-row td.info {}
table.order-summary-table tbody.otb tr.order-item-row td.title {}
table.order-summary-table tbody.otb tr.order-item-row {}
table.order-summary-table tbody.otb tr.order-item-option-row {}
table.order-summary-table tbody.otb tr.order-item-row div.order-item-name-wrap {position:relative; height:32px; overflow:hidden;}
table.order-summary-table tbody.otb tr.order-item-row div.order-item-name-place {position:absolute;}
table.order-summary-table tbody.otb tr.order-item-option-row div.order-item-name-wrap {position:relative; height:15px; overflow:hidden;}
table.order-summary-table tbody.otb tr.order-item-option-row div.order-item-name-place {position:absolute;}
table.order-summary-table tbody.otb span.order-item-image {display:inline-block;}
table.order-summary-table tbody.otb span.order-item-image img {border:1px solid #ccc; width:30px; height:30px; vertical-align:middle;}
div.order-view-control-navigation-bar {border-bottom:1px solid #ccc; background-color:#f0f0f0;}
div.order-view-control-navigation-bar .summary-mode {display:none;}
.goods_required{display:inline-block; width:18px;height:8px;background:url('/admin/skin/default/images/common/icon_must.gif') no-repeat; vertical-align:middle;}


/* write */ 
table.info-table-style {border-collapse:collapse; border-top:1px solid #aaa; border-right:1px solid #dadada;}
table.info-table-style .its-th {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 0px 8px 28px; text-align:left; background-color:#f1f1f1; font-weight:normal;}
table.info-table-style .its-td {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 15px; line-height:180%; letter-spacing:0px;}
table.info-table-style .its-th-align {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 0px 8px 0; background-color:#f1f1f1; font-weight:normal;}
table.info-table-style .its-td-align {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px; line-height:180%; letter-spacing:0px;}
table.info-table-style textarea {background-color:#f0f0f0;}
table.info-table-style textarea.input-box-default-text {color:#a5a5a5 !important}
p {
    margin:0;
}
.endline{page-break-before:always}


.custom_order {
    background-color:#eaeaea;
}
.custom_order h4 {
    font-size:12px;
    margin-left:5px;
}
.custom_order_table,
.custom_order_table2 {
    width:98%;
    margin:0 auto;
    background-color:white;
    font-size:13px;
    border-spacing:0;
    border: 1px solid #ddd;
}
.custom_order_table th {
    font-weight:bold !important;
    border:none !important;
    padding-left:20px !important;
    box-sizing:border-box;
    width:15%;
    vertical-align: top;
    padding:10px !important;
}
.custom_order_table td {
    vertical-align: top;
    padding:10px;
    font-size:14px;
    font-weight:bold;
	
}
.custom_order_table tr{
	border-bottom:1px solid #ddd;
}
.custom_order_content {
    background-color:#fff;
    border-top:1px solid #eaeaea;
    width:98%;
    margin:0 auto 10px auto;
    padding:15px;
    border: 1px solid #ddd;
}
.custom_order_table2 th {
    font-size:12px;
    background-color:#a2a2a2;
    border:none;
    color:white;
    text-align:center;
    border:0 !important;
}
.custom_order_table2 td.br1 {
    border-right: 1px solid #eaeeea;
}
.custom_order_table .red {
    color:red;
    font-weight:bold;
}
</style>

<?php
$index = 0;
foreach($infos as $info) {
?>

    <div class="pbreak" id="pbreak">


<table style="width:100%;" class="search-form-table">
<tr>
	<td colspan="3" style="text-align:center; font-size:20px; font-weight:bold; padding-top:20px;">작 업 지 시 서</td>
</tr>
<tr>
	<td width="40%" style="text-align:left; font-size:15px; font-weight:bold; padding-left:20px; border: 1px solid #555; height:30px;">
		영업담당자 : 
        <?php
        if ( $info['od']['od_sales_manager'] ) {
            $od_sales_manager = get_member($info['od']['od_sales_manager']);
            echo $od_sales_manager['mb_name'];
        }; 
        ?>
	</td>
	<td></td>
	<td width="30%" style="text-align:left; font-size:15px; font-weight:bold; padding-left:20px; border: 1px solid #555;">
		출고담당자 :  
        <?php
        if ( $info['od']['od_release_manager'] ) {
            $od_release_manager = get_member($info['od']['od_release_manager']);
            echo $od_release_manager['mb_name'];
        }; 
        ?>
	</td>
</tr>
</table>

<table style="width:100%; margin-top: 15px;" class="search-form-table">
<tr>
	<td class="bold fx18" >주문자 : <?php echo $info['od']['od_name']; ?> ☎ <?php echo $info['od']['od_hp']; ?></td>
	<td style="text-align:right;" width="50%"> 주문일 : <?php echo $info['od']['od_time']; ?> / 주문번호 : <?php echo $info['od']['od_id']; ?></td>
</tr>
</table>

	<div class="item-title">배송지정보</div>
	<table class="info-table-style" style="width:100%">
	<colgroup>
		<col width="10%" />
		<col width="50%" />
		<col width="10%" />
		<col width="30%" />
	</colgroup>
	<tbody>
	<tr>
		<th class="its-th-align center">수령인</th>
		<td style="border: 3px solid #000; font-size: 18px; font-weight: bold;" class="its-td"><?php echo $info['od']['od_b_name']; ?></td>
		<th class="its-th-align center">배송방법</th>
		<td style="border: 3px solid #000; font-size: 18px; font-weight: bold;" class="its-td"><?php echo $info['delivery']['name']; ?>
</td>
	</tr>


	<tr>
		<th class="its-th-align center">출고예정일</th>
		<td colspan="3" style="border: 3px solid #ff0000; font-size: 18px; font-weight: bold; color:#ff0000;" class="its-td">
            <?php echo $info['od']['od_ex_date']; ?>
		</td>
	</tr>
	<tr>
		<th class="its-th-align center">수령지주소</th>
		<td class="its-td"><?php echo $info['od']['od_b_addr1']; ?><?php echo $info['od']['od_b_addr2']; ?></td>
		<th class="its-th-align center">연락처</th>
		<td class="its-td">핸드폰 : <?php echo $info['od']['od_b_hp']; ?><br>
		일반전화 : <?php echo $info['od']['od_b_tel']; ?></td>
	</tr>
	<tr>
		<th class="its-th-align center">영업담당자<br>메모</th>
		<td class="its-td" style="border: 3px solid #ff0000; font-size: 18px; font-weight: bold; color:#ff0000;" ><?php echo $info['memo']['om_content']; ?></td>
		<th class="its-th-align center">배송메모</th>
		<td class="its-td bold"><?php echo $info['od']['od_memo']; ?></td>
	</tr>
	


<table style="width:100%; margin-bottom:10px; margin-top:20px;">
<tr>
	<td align="center">

	<table class="order-summary-table" style="width:100%;" border=0>
	<!-- 테이블 헤더 : 시작 -->
	<colgroup>
		<col />
		<col width="200" />
		<col width="80" />
		<col width="100" />
		<col width="90" />
		<col width="90" />
	</colgroup>
	<thead class="lth">
	<tr>
		<th>상품명</th>
		<th>상품옵션</th>
        <th>수량</th>
        <th>참고사항</th>
		<!-- <th>판매가 <br />(기본배송비, D/C)</th> -->
	</tr>
	</thead>
	<tbody class="otb">

    <?php
    //print_r2($info['carts']);
    for($i=0; $i<count($info['carts']); $i++) { 
    $options = $info['carts'][$i]['options'];
    $image = get_it_image($info['carts'][$i]['it_id'], 30, 30);
    ?>
    <?php for($k=0; $k<count($options); $k++) { ?>
		<tr class="order-item-row">

            <?php if($k == 0) { ?>
            <td style="border: 3px solid #000;" class="info left" rowspan="<?php echo count($options); ?>">
                <span class="order-item-image"><?php echo $image; ?></span>
                <span class="goods_name2"><font size="3"><b><?php echo $info['carts'][$i]['it_model']; ?></b></font></span>
            </td>
            <?php } ?>

			<td style="border: 3px solid #000;" class="title"><?php echo $options[$k]['ct_option']; ?></td>

            <td  style="border: 3px solid #000;" class="price info" align="center"><font size="5"><b><?php echo $options[$k]['ct_qty']; ?></b></font></td>
            <td class="price">
                <?php
                    // 상품목록
                    /*
                    $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '{$info['carts'][$i]['it_id']}'";
                    $result = sql_fetch($sql);
                    echo $result['it_reference'];
                    */
                    if ( $k == 0 ) {
                        // $sql = "SELECT * FROM g5_shop_order_cart_memo WHERE od_id = '{$info['carts'][$i]['od_id']}' AND it_id = '{$options[$k]['it_id']}'";
                        $sql = "SELECT * FROM g5_shop_order_cart_memo WHERE od_id = '{$info['carts'][$i]['od_id']}' AND ctm_uid = '{$options[$k]['ct_uid']}'";
                        $item_memo = sql_fetch($sql);
                        echo htmlspecialchars($item_memo['ctm_memo']);
                    }
                ?>
            </td>
			<!-- <td class="price info" align="center"><?php echo number_format($options[$k]['opt_price']); ?> <br /> (<?php echo number_format($options[$k]['ct_send_cost']); ?>, <?php echo number_format($options[$k]['ct_discount']); ?>)</td> -->
		</tr>
        <?php

        // 주문 제작이 있을때
        // $cs = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$info['carts'][$i]['od_id']}' AND it_id = '{$carts[$i]['it_id']}' ");
        $cs = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$info['carts'][$i]['od_id']}' AND odc_uid = '{$options[$k]['ct_uid']}'");
        // if ($cs['odc_no'] && $saved_it_id != $carts[$i]['it_id']) {
        // if ($cs['odc_no'] && $saved_uid != $carts[$i]['ct_uid']) {
        if ($cs['odc_no'] && $options[$k]['ct_uid'] != $options[$k + 1]['ct_uid']) {
        ?>
        <tr>
            <td colspan="4" class="custom_order">
                <h4>주문제작정보</h4>
                <table class="custom_order_table">
                    <tr>
                        <th>기본정보</th>
                        <td>
                            사이즈 가로(<?php echo $cs['size_width']; ?>mm) X 세로(<?php echo $cs['size_height']; ?>mm)
                            <?php echo $cs['frame_standard']; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>LED정보</th>
                        <td <?php echo $cs['lightpanel_led_k'] !== '9000K' ? 'class="red"' : ''; ?>>
                            <span class="red">
                                <?php echo $cs['lightpanel_led_ea'] ? $cs['lightpanel_led_ea'].'개': ''; ?>
                                <?php echo $cs['lightpanel_led_direction'] ? ' / ' . $cs['lightpanel_led_direction']: ''; ?>
                            </span>
                            <?php echo $cs['lightpanel_led_k'] ? ' / ' . $cs['lightpanel_led_k']: ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>프레임정보</th>
                        <td>
                            <?php echo $cs['frame_standard'] ? $cs['frame_standard']: ''; ?>
                            <?php echo $cs['frame_color'] ? ' / ' . $cs['frame_color']: ''; ?>

                            

                            <?php echo $cs['frame_front'] ? '<br> 앞판: ' . $cs['frame_front']: ''; ?>
                            <?php echo $cs['frame_front_transparent_acrylic'] ? ' / 투명아크릴: <span class="red">' . $cs['frame_front_transparent_acrylic']. 'T</span>': ''; ?>
                            <?php echo $cs['frame_front_optical_scatter'] ? ' / 광학산판: <span class="red">' . $cs['frame_front_optical_scatter']. 'T</span>': ''; ?>

                            

                            <?php echo $cs['frame_back'] ? '<br> 뒷판: ' . $cs['frame_back']: ''; ?>
                            <?php echo $cs['frame_back_transparent_acrylic'] ? ' / 투명아크릴: <span class="red">' . $cs['frame_back_transparent_acrylic']. 'T</span>': ''; ?>
                            <?php echo $cs['frame_back_mdf'] ? ' / MDF: <span class="red">' . $cs['frame_back_mdf']. 'T</span>': ''; ?>
                            <?php echo $cs['frame_back_formax'] ? ' / 포맥스: <span class="red">' . $cs['frame_back_formax']. 'T</span>': ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>출력물</th>
                        <td>
                            <?php echo $cs['printout_printout'] ? $cs['printout_printout']: ''; ?>
                            &nbsp;
                            <?php
                            $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$info['carts'][$i]['od_id']}' AND it_id = '{$carts[$i]['it_id']}' AND ctf_type = 'printout_design'";
                            $result = sql_query($sql);
                            $g = 0;
                            while ($row = sql_fetch_array($result)) {
                                $g++;
                            ?>
                                <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink" target="_blank"><?php echo $row['real_name']; ?></a>
                            <?php } 
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="4" class="custom_order">
                <table class="custom_order_table2">
                    <tr>
                        <?php if ($cs['lightpanel_power_line']) { ?>
                        <th colspan="2">전원</th>
                        <?php } ?>
                        <?php if ($cs['lightpanel_switch']) { ?>
                        <th>스위치</th>
                        <?php } ?>
                        <?php if ($cs['holder_pipe_length']) { ?>
                        <th>천장걸이/거치대</th>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if ($cs['lightpanel_power_line']) { ?>
                        <td>
                            <table cellspacing="0" cellpadding="0" class="power_line_table" style="margin:15px auto;">
                                <tbody>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="22" class="center blrb pdb5 darkorange">H<br>
                                            <input type="radio" name="lightpanel_power_line" value="a2" <?php echo $cs['lightpanel_power_line'] == 'a2' ? 'checked' : ''; ?> ></td>
                                        <td width="22" class="center blrb pdb5 darkorange"><br>						<input type="radio" name="lightpanel_power_line" value="a3"  <?php echo $cs['lightpanel_power_line'] == 'a3' ? 'checked' : ''; ?>></td>
                                        <td width="22" class="center blrb pdb5 darkorange">G<br>
                                            <input type="radio" name="lightpanel_power_line" value="a4" <?php echo $cs['lightpanel_power_line'] == 'a4' ? 'checked' : ''; ?> ></td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td height="26" class="right pdr5 darkorange">A <input type="radio" name="lightpanel_power_line" value="b1"  <?php echo $cs['lightpanel_power_line'] == 'b1' ? 'checked' : ''; ?> ></td>
                                        <td class="center blrl pdt10 darkorange fx10"><input type="radio" name="lightpanel_power_line" value="b2"  <?php echo $cs['lightpanel_power_line'] == 'b2' ? 'checked' : ''; ?> ><br>1</td>
                                        <td class="center pdt10 darkorange fx10"><input type="radio" name="lightpanel_power_line" value="b3"  <?php echo $cs['lightpanel_power_line'] == 'b3' ? 'checked' : ''; ?>><br>2</td>
                                        <td class="center blrr pdt10 darkorange fx10"><input type="radio" name="lightpanel_power_line" value="b4"  <?php echo $cs['lightpanel_power_line'] == 'b4' ? 'checked' : ''; ?>><br>3</td>
                                        <td class="left pdl5 darkorange"><input type="radio" name="lightpanel_power_line" value="b5"  <?php echo $cs['lightpanel_power_line'] == 'b5' ? 'checked' : ''; ?>> F</td>
                                    </tr>
                                    <tr>
                                        <td class="right pdr5"><input type="radio" name="lightpanel_power_line" value="c1"  <?php echo $cs['lightpanel_power_line'] == 'c1' ? 'checked' : ''; ?>></td>
                                        <td class="center blrl pdt10 darkorange fx10"><input type="radio" name="lightpanel_power_line" value="c2"  <?php echo $cs['lightpanel_power_line'] == 'c2' ? 'checked' : ''; ?>><br>4</td>
                                        <td class="center pdt10 darkorange fx10"><input type="radio" name="lightpanel_power_line" value="c3"  <?php echo $cs['lightpanel_power_line'] == 'c3' ? 'checked' : ''; ?>><br>5</td>
                                        <td class="center blrr pdt10 darkorange fx10"><input type="radio" name="lightpanel_power_line" value="c4"  <?php echo $cs['lightpanel_power_line'] == 'c4' ? 'checked' : ''; ?>><br>6</td>
                                        <td class="center"><input type="radio" name="lightpanel_power_line" value="c5"  <?php echo $cs['lightpanel_power_line'] == 'c5' ? 'checked' : ''; ?>>&nbsp;&nbsp;&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td height="26" class="right pdr5 darkorange">B <input type="radio" name="lightpanel_power_line" value="d1"  <?php echo $cs['lightpanel_power_line'] == 'd1' ? 'checked' : ''; ?>></td>
                                        <td class="center blrl pdt10 darkorange fx10"><input type="radio" name="lightpanel_power_line" value="d2"  <?php echo $cs['lightpanel_power_line'] == 'd1' ? 'checked' : ''; ?>><br>7</td>
                                        <td class="center pdt10 darkorange fx10"><input type="radio" name="lightpanel_power_line" value="d3"  <?php echo $cs['lightpanel_power_line'] == 'd3' ? 'checked' : ''; ?>><br>8</td>
                                        <td class="center blrr pdt10 darkorange fx10"><input type="radio" name="lightpanel_power_line" value="d4"  <?php echo $cs['lightpanel_power_line'] == 'd4' ? 'checked' : ''; ?>><br>9</td>
                                        <td class="left pdl5 darkorange"><input type="radio" name="lightpanel_power_line" value="d5"  <?php echo $cs['lightpanel_power_line'] == 'd5' ? 'checked' : ''; ?>>	E</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td class="center blrt pdt5 darkorange"><input type="radio" name="lightpanel_power_line" value="e2"  <?php echo $cs['lightpanel_power_line'] == 'e2' ? 'checked' : ''; ?>><br>C</td>
                                        <td class="center blrt pdt5 darkorange"><input type="radio" name="lightpanel_power_line" value="e3"  <?php echo $cs['lightpanel_power_line'] == 'e3' ? 'checked' : ''; ?>><br>&nbsp;</td>
                                        <td class="center blrt pdt5 darkorange"><input type="radio" name="lightpanel_power_line" value="e4"  <?php echo $cs['lightpanel_power_line'] == 'e4' ? 'checked' : ''; ?>><br>D</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td class="br1" style="font-size:14px;">
                            <b><?php echo $cs['lightpanel_smps'] ? $cs['lightpanel_smps']: ''; ?></b>
                            <br/>
                            - AC전원선 <?php echo $cs['lightpanel_power_line_ac'] ? $cs['lightpanel_power_line_ac'].'mm': '없음'; ?> <br/>
                            - 전원선 DC잭 <?php echo $cs['lightpanel_power_line_dc'] ? $cs['lightpanel_power_line_dc'].'mm': '없음'; ?> <br/>
                            - 와이어 <?php echo $cs['lightpanel_power_line_wire'] ? $cs['lightpanel_power_line_wire'].'mm': '없음'; ?> <br/>
                            <br/>
                            <span style="color:red;">* 레이저가공 <?php echo $cs['lightpanel_laser'] ? $cs['lightpanel_laser']: ''; ?></span>
                        </td>
                        <?php } ?>
                        <?php if ($cs['lightpanel_switch']) { ?>
                        <td class="br1" style="text-align:center;">
                            <table cellspacing="0" cellpadding="0" id="lightpanel_switch_tbl" style="margin:15px auto">
                                <tbody>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="22" class="center blrb"><input type="radio" name="lightpanel_switch" value="a2" <?php echo $cs['lightpanel_switch'] == 'a2' ? 'checked' : ''; ?> ></td>
                                        <td width="36" class="center blrb"><input type="radio" name="lightpanel_switch" value="a3" <?php echo $cs['lightpanel_switch'] == 'a3' ? 'checked' : ''; ?> ></td>
                                        <td width="22" class="center blrb"><input type="radio" name="lightpanel_switch" value="a4" <?php echo $cs['lightpanel_switch'] == 'a4' ? 'checked' : ''; ?> ></td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td height="20" class="center"><input type="radio" name="lightpanel_switch" value="b1" <?php echo $cs['lightpanel_switch'] == 'b1' ? 'checked' : ''; ?> ></td>
                                        <td colspan="3" rowspan="3" class="center blrl blrr"><input type="radio" name="lightpanel_switch" value="b3" <?php echo $cs['lightpanel_switch'] == 'b3' ? 'checked' : ''; ?> >							<br>
                                            중간스위치<br>
                                            <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/img_switch.gif"></td>
                                        <td class="center"><input type="radio" name="lightpanel_switch" value="b5" <?php echo $cs['lightpanel_switch'] == 'b5' ? 'checked' : ''; ?>></td>
                                    </tr>
                                    <tr>
                                        <td class="center"><input type="radio" name="lightpanel_switch" value="c1" <?php echo $cs['lightpanel_switch'] == 'c1' ? 'checked' : ''; ?>></td>
                                        <td class="center"><input type="radio" name="lightpanel_switch" value="c5" <?php echo $cs['lightpanel_switch'] == 'c5' ? 'checked' : ''; ?>></td>
                                    </tr>
                                    <tr>
                                        <td height="20" class="center"><input type="radio" name="lightpanel_switch" value="d1" <?php echo $cs['lightpanel_switch'] == 'd1' ? 'checked' : ''; ?>></td>
                                        <td class="center"><input type="radio" name="lightpanel_switch" value="d5" <?php echo $cs['lightpanel_switch'] == 'd5' ? 'checked' : ''; ?>></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td class="center blrt"><input type="radio" name="lightpanel_switch" value="e2" <?php echo $cs['lightpanel_switch'] == 'e2' ? 'checked' : ''; ?>></td>
                                        <td class="center blrt"><input type="radio" name="lightpanel_switch" value="e3" <?php echo $cs['lightpanel_switch'] == 'e3' ? 'checked' : ''; ?>></td>
                                        <td class="center blrt"><input type="radio" name="lightpanel_switch" value="e4" <?php echo $cs['lightpanel_switch'] == 'e4' ? 'checked' : ''; ?>></td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>
                            <br/>
                            <?php echo $cs['lightpanel_switch_explain'] ? ' ' . $cs['lightpanel_switch_explain'].'': ''; ?>
                        </td>
                        <?php } ?>
                        <?php if ($cs['holder_pipe_length']) { ?>
                        <td>
                            <table align="center" border="0" cellspacing="0" cellpadding="0" id="g3_pipe_tbl" style="margin:15px auto;">
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="blrr center"><?php echo $cs['holder_pipe_interval_1'] ? $cs['holder_pipe_interval_1']: '0'; ?>mm</td>
                                        <td width="5"></td>
                                        <td class="blrr blrl center" width="40"><?php echo $cs['holder_pipe_interval_2'] ? $cs['holder_pipe_interval_2']: '0'; ?>mm</td>
                                        <td width="5"></td>
                                        <td colspan="2" class="blrl center"><?php echo $cs['holder_pipe_interval_3'] ? $cs['holder_pipe_interval_3']: '0'; ?>mm</td>
                                    </tr>
                                    <tr>
                                        <td width="15">&nbsp;</td>
                                        <td width="25" class="blrr"></td>
                                        <td></td>
                                        <td class="center blrr blrl"></td>
                                        <td></td>
                                        <td width="25" class="blrl"></td>
                                        <td width="15">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td height="40" colspan="5" class="blrl blrr blrt blrb center">길이 : <?php echo $cs['holder_pipe_length']; ?>mm</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <?php } ?>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="4" class="custom_order">
                <div class="custom_order_content">
                    <?php echo $cs['content_common'] ? '공통내용: ' . $cs['content_common'].'<br/>': ''; ?>
                    <?php echo $cs['content_lp'] ? 'LP팀: ' . $cs['content_lp'].'<br/>': ''; ?>
                </div>
            </td>
        </tr>
        <?php
        // $saved_it_id = $carts[$i]['it_id'];
        $saved_uid = $carts[$i]['ct_uid'];
        } 
        ?>
    <?php } ?>
    <?php } ?>
	</tbody>
	</table>
	</tbody>
	</table>
	</td>
</tr>
</table>
<!--
    <?php
    foreach($info['it_ids'] as $it_id) { 

        // 상품목록
        $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id'";
        $result = sql_fetch($sql);
    ?>
    <table style="width:100%; margin-bottom:10px; margin-top:10px;background-color: #f1f1f1;border: 1px solid #dadada;padding:15px;">
        <tr>
	        <td>
                <h3 style="margin:0;font-weight:bold;"><?php echo $result['it_name']; ?> 참고사항</h3>
                <p style="margin:0;padding:0;" class="parent_p">
                    <?php echo $result['it_reference']; ?>
                </p>
            </td>
        </tr>
    </table>

    <?php } ?>
-->
<?php if ( count($infos) - 1 > $index ) { ?>
    <div class="endline"></div><br style="height:0; line-height:0">
<?php
}
$index++;
}
?>

<script type="text/javascript">
$(document).ready(function() {
    // samhwaprint($('html').html());
    document.execCommand('print', false, null) || window.print();
});
</script>

<?php 
include_once('./pop.tail.php');
?>