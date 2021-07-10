<?php
include_once('./_common.php');

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

// 상품목록
$sql = " select a.it_id,
				a.it_name,
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
		  where a.od_id = '$od_id'
		  group by a.it_id, a.ct_uid
		  order by a.ct_id ";

$result = sql_query($sql);

$carts = array();
$cate_counts = array();

for($i=0; $row=sql_fetch_array($result); $i++) {

    $cate_counts[$row['ct_status']] += 1;

    // 상품의 옵션정보
    $sql = " select ct_id, mb_id, it_id, it_name, ct_price, ct_point, ct_qty, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, io_type, io_price, pt_msg1, pt_msg2, pt_msg3, ct_discount
                from {$g5['g5_shop_cart_table']}
                where od_id = '{$od_id}'
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

        $opt['cs'] = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$od['od_id']}' AND it_id = '{$opt['it_id']}' ");

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
$amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'] - $od['od_cart_discount'] - $od['od_cart_discount2'] - $od['od_sales_discount'];

// 입금액 = 결제금액 + 포인트
$amount['receipt'] = $od['od_receipt_price'] + $od['od_receipt_point'];

// 쿠폰금액
$amount['coupon'] = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];

// 취소금액
$amount['cancel'] = $od['od_cancel_price'];



// 견적서 정보
$sql = " select * from g5_shop_order_estimate where od_id = '$od_id' ";
$est = sql_fetch($sql);

unset($w);
// 관리 권한 확인
if ( $member['mb_id'] ) {
    $sql = "SELECT * FROM g5_auth WHERE mb_id = '{$member['mb_id']}'";
    $result = sql_fetch($sql);

    if ( $result['mb_id'] ) {
        $w = 'u';
    }
    if ( $is_admin ) {
        $w = 'u';
    }
}

$banks = explode(PHP_EOL, $default['de_bank_account']); 
for($i=0;$i<count($banks);$i++) {
    $banks2[$i] = explode(' ', $banks[$i]);
}
$banks = $banks2;


header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=csorder_".date("YmdHi").".xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");
header("Content-charset=utf-8");

?>
<html xmlns:v='urn:schemas-microsoft-com:vml'
xmlns:o='urn:schemas-microsoft-com:office:office'
xmlns:x='urn:schemas-microsoft-com:office:excel'
xmlns='http://www.w3.org/TR/REC-html40'>
<head>
<!--[if gte mso 9]><xml>
<x:ExcelWorkbook>
<x:ExcelWorksheets>
<x:ExcelWorksheet>
<x:Name>".$today."_order</x:Name>
<x:WorksheetOptions>
 <x:DefaultRowHeight>270</x:DefaultRowHeight>
 <x:Selected/>
 <x:DoNotDisplayGridlines/>
 <x:ProtectContents>False</x:ProtectContents>
 <x:ProtectObjects>False</x:ProtectObjects>
 <x:ProtectScenarios>False</x:ProtectScenarios>
</x:WorksheetOptions>
</x:ExcelWorksheet>
</x:ExcelWorksheets>
<x:WindowHeight>12825</x:WindowHeight>
<x:WindowWidth>18945</x:WindowWidth>
<x:WindowTopX>120</x:WindowTopX>
<x:WindowTopY>30</x:WindowTopY>
<x:ProtectStructure>False</x:ProtectStructure>
<x:ProtectWindows>False</x:ProtectWindows>
</x:ExcelWorkbook>
</xml><![endif]-->
<meta http-equiv="Content-Type" content="application/vnd.ms-excel;charset=utf-8">
<style>
<!--table
	{mso-displayed-decimal-separator:\"\.\";
	mso-displayed-thousand-separator:\"\,\";}
@page
	{margin:.4in .4in .4in .4in;
	mso-header-margin:.5in;
	mso-footer-margin:.5in;}
.number {mso-number-format:\"@\"}
 *{
	color:black;
	font-family:돋움, monospace;
	font-size:10.0pt;
 }
td  {mso-number-format:\@;}
	-->
</style>
<table width="741" cellpadding="0" cellspacing="0" border="0" style="font-size:12px;">
<tr>
	<td width="60" height="30px">&nbsp;</td><td width="40">&nbsp;</td><td width="26">&nbsp;</td><td width="36">&nbsp;</td><td width="30">&nbsp;</td>
	<td width="50">&nbsp;</td><td width="59">&nbsp;</td><td width="24">&nbsp;</td><td width="34">&nbsp;</td><td width="24">&nbsp;</td>
	<td width="39">&nbsp;</td><td width="39">&nbsp;</td><td width="70">&nbsp;</td><td width="18">&nbsp;</td><td width="33">&nbsp;</td>
	<td width="93">&nbsp;</td><td width="16">&nbsp;</td>
</tr>
<tr>
	<td colspan="5"></td>
	<td colspan="7" align="center"><span style="font-size:26px;font-weight:bold;color:#000;"><?php echo $est['est_title'] ? $est['est_title'] : '견&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;적&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;서'; ?></span></td>
	<td colspan="5"></td>
</tr>
<tr height="1">
	<td colspan="5"></td>
	<td colspan="7" align="center" bgcolor="000000"></td>
	<td colspan="5"></td>
</tr>
<tr height="1">
	<td colspan="5"></td>
	<td colspan="7" align="center" bgcolor="ffffff"></td>
	<td colspan="5"></td>
</tr>
<tr height="1">
	<td colspan="5"></td>
	<td colspan="7" align="center" bgcolor="000000"></td>
	<td colspan="5"></td>
</tr>
<tr><td colspan="15" height="25"></td>
	<td colspan="2" style="text-align:right;"><div style="position:absolute;margin-top:-30px;margin-right: -10px;"><img src="<?php echo $config['cf_4'] ?>" width="80"></div></td></tr>
<tr height="25">
	<td colspan="5" align="left"><span style="font-size:16px;letter-spacing:3px;"><u><?php echo date('Y년 m월 d일', time()); ?></u></span></td>
	<td colspan="4"></td>
	<td colspan="2" align="center" style="border:1px solid #000;">등록번호</td>
	<td colspan="6"  style="border:1px solid #000;padding: 5px;"><?php echo $default['de_admin_company_saupja_no']; ?></td>
</tr>
<tr height="25">
	<td colspan="9"></td>
	<td colspan="2" align="center" style="border:1px solid #000;">상호</td>
	<td colspan="2"   style="border:1px solid #000;padding: 5px;"><?php echo $default['de_admin_company_name']; ?></td>
	<td colspan="2" align="center" style="border:1px solid #000;">성명</td>
	<td colspan="2"  style="border:1px solid #000;padding: 5px;"><?php echo $default['de_admin_company_owner']; ?></td>
</tr>
<tr height="25">
	<td colspan="6" align="left"><u><?php echo $est['est_name'] ? $est['est_name'] : $od['od_name']; ?></u></td>
	<td><span style="font-size:16px;letter-spacing:3px;">귀하</span></td>
	<td colspan="2"></td>
	<td colspan="2" align="center" style="border:1px solid #000;">주소</td>
	<td colspan="6"  style="border:1px solid #000;padding: 5px;"><?php echo $default['de_admin_company_addr']; ?></td>
</tr>
<tr height="25">
	<td colspan="9"></td>
	<td colspan="2" align="center" style="border:1px solid #000;">업태</td>
	<td colspan="2"   style="border:1px solid #000;padding: 5px;">제조,도소매 외</td>
	<td colspan="2" align="center" style="border:1px solid #000;">종목</td>
	<td colspan="2"  style="border:1px solid #000;padding: 5px;">간판 및 광고물외</td>
</tr>
<tr height="25">
	<td colspan="5" align="left"><span style="font-size:16px;letter-spacing:3px;">아래와 같이 견적합니다.</span></td>
	<td colspan="4"></td>
	<td colspan="2" align="center" style="border:1px solid #000;">TEL</td>
	<td colspan="2"   style="border:1px solid #000;padding: 5px;"><?php echo $default['de_admin_company_tel']; ?></td>
	<td colspan="2" align="center" style="border:1px solid #000;">FAX</td>
	<td colspan="2"   style="border:1px solid #000;padding: 5px;"><?php echo $default['de_admin_company_fax']; ?></td>
</tr>
<tr><td colspan="17" height="25"></td></tr>


<tr height="30">
	<td colspan="17" style="border:1px solid #000;" align="center">
		<span style="font-weight:bold;font-size:14px;">
		합 계 금 액&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		금&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold;font-size:14px;letter-spacing:4px;"><?php echo samhwa_price_to_hangul($amount['order']); ?>원</span>&nbsp;&nbsp;&nbsp;정
		</span>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<span style="font-weight:bold;font-size:14px;letter-spacing:1px;">(&nbsp;&nbsp;&nbsp;\<?php echo number_format($amount['order']); ?>&nbsp;&nbsp;&nbsp;)</span>
	</td>
</tr>
<tr height="25">
	<td align="center" colspan="4" style="border:1px solid #000;">제품명</td>
	<td align="center" colspan="3" style="border:1px solid #000;">선택사항</td>
	<td align="center" colspan="2" style="border:1px solid #000;">수량</td>
	<td align="center" colspan="3" style="border:1px solid #000;">단가</td>
	<td align="center" colspan="3" style="border:1px solid #000;">공급가액</td>
	<td align="center" colspan="2" style="border:1px solid #000;">세액</td>
</tr>

<?php
    for($i=0; $i<count($carts); $i++) { 
    $options = $carts[$i]['options'];
?>
    <?php for($k=0; $k<count($options); $k++) { ?>
        <tr height="28">
            <td align="center" colspan="4" style="border:1px solid #000;"><div class="goods_name"><?php echo $carts[$i]['it_model']; ?></div></td>
            <td align="center" colspan="3" style="border:1px solid #000;">
			<?php echo $options[$k]['ct_option'] != $options[$k]['it_name'] ? '옵션: ' . $options[$k]['ct_option'] : ''; ?>
			<?php echo $options[$k]['ct_option'] != $options[$k]['it_name'] && $options[$k]['cs'] && $k == 0 ? ', ' : ''; ?>
			<?php if($options[$k]['cs'] && $k == 0){ ?>
				사이즈: 가로(<?php echo $options[$k]['cs']['size_width']; ?>mm) X 세로(<?php echo $options[$k]['cs']['size_height']; ?>mm)
			<?php } ?>
            </td>
            <td align="center" colspan="2" style="border:1px solid #000;"><?php echo $options[$k]['ct_qty']; ?></td>
            <td align="center" colspan="3" style="border:1px solid #000;"><?php echo number_format($options[$k]['opt_price'] / 1.1); ?></td><!-- (.tot_price) 수정 : 단가이므로--> 
            <td align="center" colspan="3" style="border:1px solid #000;"><?php echo number_format($options[$k]['opt_price'] / 1.1 * $options[$k]['ct_qty']); ?></td>
            <td align="center" colspan="2" style="border:1px solid #000;"><?php echo number_format($options[$k]['opt_price'] / 1.1 / 10 * $options[$k]['ct_qty']); ?></td>
        </tr>
    <?php } ?>
<?php } ?>


<?php if ( $od['od_send_cost'] ) { ?>
<tr height="28">
	<td align="center" colspan="4" style="border:1px solid #000;">배송비</td>
	<td align="center" colspan="3" style="border:1px solid #000;"></td>
	<td align="center" colspan="2" style="border:1px solid #000;"></td>
	<td align="center" colspan="3" style="border:1px solid #000;"><?php echo number_format($od['od_send_cost'] / 1.1); ?></td>
	<td align="center" colspan="3" style="border:1px solid #000;"><?php echo number_format($od['od_send_cost'] / 1.1); ?></td>
	<td align="center" colspan="2" style="border:1px solid #000;"><?php echo number_format($od['od_send_cost'] / 1.1 / 10); ?></td>
</tr>
<?php } ?>


<?php if ( $od['od_send_cost2'] ) { ?>
<tr height="28">
	<td align="center" colspan="4" style="border:1px solid #000;">배송비</td>
	<td align="center" colspan="3" style="border:1px solid #000;"></td>
	<td align="center" colspan="2" style="border:1px solid #000;"></td>
	<td align="center" colspan="3" style="border:1px solid #000;"><?php echo number_format($od['od_send_cost2'] / 1.1); ?></td>
	<td align="center" colspan="3" style="border:1px solid #000;"><?php echo number_format($od['od_send_cost2'] / 1.1); ?></td>
	<td align="center" colspan="2" style="border:1px solid #000;"><?php echo number_format($od['od_send_cost2'] / 1.1 / 10); ?></td>
</tr>
<?php } ?>

<?php if ( $od['od_cart_discount'] ) { ?>
<tr height="28">
	<td align="center" colspan="4" style="border:1px solid #000;">할인</td>
	<td align="center" colspan="3" style="border:1px solid #000;"></td>
	<td align="center" colspan="2" style="border:1px solid #000;"></td>
	<td align="center" colspan="3" style="border:1px solid #000;">-<?php echo number_format($od['od_cart_discount'] / 1.1); ?></td>
	<td align="center" colspan="3" style="border:1px solid #000;">-<?php echo number_format($od['od_cart_discount'] / 1.1); ?></td>
	<td align="center" colspan="2" style="border:1px solid #000;">-<?php echo number_format($od['od_cart_discount'] / 1.1 / 10); ?></td>
</tr>
<?php } ?>

<?php if ( $od['od_cart_discount2'] ) { ?>
<tr height="28">
	<td align="center" colspan="4" style="border:1px solid #000;">추가할인</td>
	<td align="center" colspan="3" style="border:1px solid #000;"></td>
	<td align="center" colspan="2" style="border:1px solid #000;"></td>
	<td align="center" colspan="3" style="border:1px solid #000;">-<?php echo number_format($od['od_cart_discount2'] / 1.1); ?></td>
	<td align="center" colspan="3" style="border:1px solid #000;">-<?php echo number_format($od['od_cart_discount2'] / 1.1); ?></td>
	<td align="center" colspan="2" style="border:1px solid #000;">-<?php echo number_format($od['od_cart_discount2'] / 1.1 / 10); ?></td>
</tr>
<?php } ?>

<tr height="28">
	<td align="center" colspan="4" style="border:1px solid #000;">합계</td>
	<td align="center" colspan="3" style="border:1px solid #000;"></td>
	<td align="center" colspan="2" style="border:1px solid #000;"></td>
	<td align="center" colspan="3" style="border:1px solid #000;"></td>
	<td align="center" colspan="3" style="border:1px solid #000;"><?php echo number_format($amount['order'] / 1.1); ?></td>
	<td align="center" colspan="2" style="border:1px solid #000;"><?php echo number_format($amount['order'] / 1.1 / 10); ?></td>
</tr>
<tr><td colspan="17" height="10"></td></tr>
<tr height="25">
    <td colspan="17" style="border:1px solid #000;">
        <?php echo $est['est_content'] ? nl2br($est['est_content']) : '-특기사항<br/><br/>*사업자등록증 팩스로 첨부요망(전자세금계산서 수신용 이메일 기입요망)<br/><br/>*규격품은 입금 후 당일 출고 가능<br/><br/>*주문제작시 입금 후 제작되며, 기간은 담당자와 상담후 결정<br/><br/>*데이터는 일러스트.ai 또는 .eps용 / 견적 유효기간-1개월<br/><br/>*운임: 규격품 10만원 이상구매시 택배무료, 설치비는 별도.'; ?>	
	</td>
</tr>
<tr><td colspan="17" height="10"></td></tr>
<tr height="30">
	<td align="center">입금계좌안내</td>
	<td align="center"><?php echo $banks[0][0]; ?> <?php echo $banks[0][1]; ?> <?php echo $banks[1][0]; ?> <?php echo $banks[1][1]; ?>  <?php echo $banks[2][0]; ?> <?php echo $banks[2][1]; ?> </td>
	<!-- <td align="center" colspan="2"><?php echo $banks[0][1]; ?></td>
	<td align="center"><?php echo $banks[1][0]; ?></td>
	<td align="center" colspan="2"><?php echo $banks[1][1]; ?></td> -->
	<td align="center">예금주</td>
	<td align="center"><?php echo $default['de_admin_company_name']; ?></td>
</tr>
<tr><td colspan="17" height="10"></td></tr>
<tr height="25">
	<td colspan="3"><img src="<?php echo $config['cf_3'] ?>"></td>
	<td><?php echo get_samhwa_content('footer_info'); ?></td>
</tr>
</tr>
</table>