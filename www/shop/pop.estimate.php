<?php
include_once('./_common.php');
//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);

$send_cost = $od['od_send_cost'] + $od['od_send_cost2'];

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
		  where a.od_id = '$od_id' and a.ct_status not in ('취소', '주문무효')
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

    /*
    // 합계금액 계산
    $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                    SUM(ct_qty) as qty,
                    SUM(ct_discount) as discount,
                    SUM(ct_send_cost) as sendcost
                from {$g5['g5_shop_cart_table']}
                where it_id = '{$row['it_id']}'
                    and od_id = '{$od_id}' ";
    $sum = sql_fetch($sql);

    $row['sum'] = $sum;
    $amount['order'] += $sum['price'] - $sum['discount'];

    $sql_taxInfo = 'select `it_taxInfo` from `g5_shop_item` where `it_id` = "'.$options[$k]['it_id'].'"';
    $it_taxInfo = sql_fetch($sql_taxInfo);
    if($it_taxInfo['it_taxInfo']=="과세"){ 
        $money1+=$sum['price'] - $sum['discount'];
    }else{
        $money2+=$sum['price'] - $sum['discount'];
    }
    */

    if ( !$od['od_send_cost'] ) {
        $od['od_send_cost'] += $sum['ct_send_cost'];
    }

    $carts[] = $row;
}

/*
// 주문금액 = 상품구입금액 + 배송비 + 추가배송비 - 할인금액 - 추가할인금액
if ( $od['od_cart_price'] ) {
    //$amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'] - $od['od_cart_discount'] - $od['od_cart_discount2'];
}
if ( $send_cost ) {
    $amount['order'] += $send_cost;
    $money1 += $send_cost;
}
if($od['od_sales_discount']) {
    $amount['order'] -= $od['od_sales_discount'];
    $money1 -= $od['od_sales_discount'];
}

// 입금액 = 결제금액 + 포인트
$amount['receipt'] = $od['od_receipt_price'] + $od['od_receipt_point'];

// 쿠폰금액
$amount['coupon'] = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];

// 취소금액
$amount['cancel'] = $od['od_cancel_price'];
*/

if ( !$od['od_name'] ) {
    $od['od_name'] = $member['mb_name'];
}



// 견적서 정보
$sql = " select * from g5_shop_order_estimate where od_id = '$od_id' ";
$est = sql_fetch($sql);

unset($w);
// 관리 권한 확인
if ( $is_samhwa_admin || $is_admin ) {
    $w = 'u';
}

$banks = explode(PHP_EOL, $default['de_bank_account']); 
for($i=0;$i<count($banks);$i++) {
    $banks2[$i] = explode(' ', $banks[$i]);
}
$banks = $banks2;

?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width" /><meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<title><?php $title; ?></title>
<link rel="stylesheet" href="/adm/css/popup.css?v=<?php echo time(); ?>">
<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-ui.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-migrate-1.2.1.min.js"></script>
<script src="<?php echo G5_JS_URL;?>/common.js"></script>
</head>
<style>
body, input, textarea, select, button, table {
    font-size: 12px;
}
</style>
<script>
    $(document).ready(function(){
        $(".layout-head .menu .one>li>a").click(function(){
            var parent = $(this).closest('li');
            var sub = parent.children('.sub');
            if ( parent.hasClass('on') ) {
                sub.slideUp();
                parent.removeClass('on');
                return;
            }
            $(".layout-head .menu .one>.on>a").click();
            sub.slideDown();
            parent.addClass('on');
        });
        $("#page-title-bar .page-buttons-right li.opener").mouseenter(function(){
            var sub = $(this).children('ul');
            sub.show();
            if ( sub.offset().left + sub.width() > $(window).width() ) {
                sub.css('right', '0').css('left', 'auto');
            }

            $(sub).mouseleave(function(){
                sub.hide();
            });
        });

        if ( $.trim($(".page-buttons-left").html()) == "" ) {
            $("#page-title-bar .page-title h2").addClass('afternone');
        }

        if ( $.trim($(".page-buttons-left").html()) != "" ) {
            $("#page-title-bar .page-title h2").mouseenter(function(){
                $(".page-buttons-left").toggle();
            });
            $(".page-buttons-left").mouseleave(function() {
                $(".page-buttons-left").toggle();
            });
        }

        if ( !$('#page-title-bar-area').length ) {
            $('#layout-body').css('paddingTop', '10px');
        }
    });
</script>

<div id="wrap">

<head>
<style type="text/css" media="print">
.noprint {display:none;}
.prints {width:100%;}
.btn { width:100px; border:1px solid #CCC; padding:5px 15px; font:bold 12px dotumche; color:#333; text-align:center; background:#EEEEEE; }
body { margin-right:5; margin-top:5; margin-bottom:5; margin-left:5; font:14px batangche; color:#000;}
</style>
<body style="overflow-x:hidden;">
<div id="printBtns" class="noprint" style="background-color:#eee;line-height:30px;padding:10px;">
	<div style="padding-top:3px;">

        <?php if ( $w == 'u' ) {
        $sql = "SELECT * FROM g5_member_giup_manager WHERE mb_id = '{$od['mb_id']}'";
        $result = sql_query($sql);
        $managers = array();
        while ($m_row = sql_fetch_array($result)) {
            $managers[] = $m_row;
        }
        if (!count($managers)) {
            array_push($managers, array());
        }
            ?>
        <script>
            $(function () {
                $('#giup_manager_sel').change(function () {
                    var selectedManager = $(this).find("option:selected");
                    var selectedManagersEmail = selectedManager.data('email');
                    
                    $('input[name=u_email]').val(selectedManagersEmail);
                })
            })
        </script>
        <style>
            #giup_manager_sel {
                border: 1px solid #cccccc;
                color: #656565;
                font-size: 12px;
                height: 23px;
                display: inline-block;
                vertical-align: middle;
            }
        </style>
        <div style="display:inline-block">
            <input type="checkbox" name="email_chk" id="email_chk" style="vertical-align:middle">
            <label for="email_chk" style="width:70px;display:inline-block;font-size:12px;">이메일 전송</label>
            <?php if (count($managers) > 0) { ?>
            <select id="giup_manager_sel">
                <option data-email="<?php echo $od['od_email']; ?>" selected>담당자 선택</option>
                <?php for ($m = 0; $m < count($managers); $m++) { ?>
                <option data-email="<?php echo $managers[$m]['mm_email'] ?>"><?php echo $managers[$m]['mm_name'] ?></option>
                <?php } ?>
            </select>
            <?php } ?>
            <input type="text" placeholder="이메일" name="u_email" value="<?php echo $od['od_email']; ?>" style="vertical-align:middle;border: 1px solid #cccccc;font-size: 12px;padding: 4px 8px;height: auto;color: #656565;background-color: white;vertical-align: middle;" />
            <br />
            <input type="checkbox" name="hp_chk" id="hp_chk" style="vertical-align:middle">
            <label for="hp_chk" style="width:70px;display:inline-block;font-size:12px;">문자 전송</label>
            <input type="text" placeholder="핸드폰번호" name="u_hp" value="<?php echo $od['od_hp']; ?>" style="vertical-align:middle;border: 1px solid #cccccc;font-size: 12px;padding: 4px 8px;height: auto;color: #656565;background-color: white;vertical-align: middle;" />

	        <input type="button" style="vertical-align:middle;border: 1px solid #cccccc;font-size: 12px;cursor: pointer;padding: 4px 8px;height: auto;color: #656565;background-color: black;color:white;" value="견적서 발송" onclick="go_send();"> &nbsp; 
        </div>
        <?php } ?>
        <div style="display:inline-block">
            <button type="button" style="vertical-align:middle;border: 1px solid #cccccc;font-size: 12px;cursor: pointer;padding: 4px 8px;height: auto;color: #656565;background-color: white;" onclick="go_prints();" ><img src="/adm/shop_admin/img/printer.png" align="absmiddle" style="margin-right:5px;">인쇄하기</button>
            <?php if ( $w == 'u' ) { ?>
                <button type="button" style="vertical-align:middle;border: 1px solid #cccccc;font-size: 12px;cursor: pointer;padding: 4px 8px;height: auto;color: #656565;background-color: white;" onclick="go_exceldown();" ><img src="/adm/shop_admin/img/btn_img_ex.gif" align="absmiddle" style="margin-right:5px;">엑셀다운로드</button>
                <button type="button" style="vertical-align:middle;border: 1px solid #009845;font-size: 12px;cursor: pointer;padding: 4px 8px;height: auto;color: #fff;background-color: #009845;" onclick="go_submit();" >저장</button>
            <?php } ?>
        </div>

	</div>
</div>

<div id="idPrint" style="padding:25px 15px;">

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td align="center">

	<table width="300" cellpadding="0" cellspacing="0" border="0">
	<tr>
        <?php if ( $w == 'u') { ?>
            <td><input type="text" name="title" value="<?php echo $est['est_title'] ? $est['est_title'] : '견&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;적&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;서'; ?>" style="border:0px; font-weight:bold; font-size:12px; width:300px;font-size:26px;font-weight:bold;color:#000;text-align: center;"></td>
        <?php }else{ ?>
            <td style="border:0px; font-weight:bold; font-size:12px; width:300px;font-size:26px;font-weight:bold;color:#000;text-align: center;">
                <?php echo $est['est_title'] ? $est['est_title'] : '견&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;적&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;서'; ?>
            </td>
        <?php } ?>
	</tr>
	<tr><td height="1" bgcolor="000000"></td></tr>
	<tr><td height="1" bgcolor="ffffff"></td></tr>
	<tr><td height="1" bgcolor="000000"></td></tr>
	</table>
	
	</td>
</tr>
<tr><td height="40"></td></tr>
<tr>
	<td>

	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign="top">
		
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
            <td colspan="2"><span style="font-size:16px;letter-spacing:3px;"><u><input type="text" name="time" value="<?php echo $est['est_time'] ? $est['est_time'] : date('Y년 m월 d일', time()); ?>" style="font-size:16px;letter-spacing:3px; width:230px; border:0;text-decoration:inherit;"></u></span></td>
		</tr>
		<tr><td height="30"></td></tr>
		<tr>
            <td width="230"><u><input type="text" name="name" value="<?php echo $est['est_name'] ? $est['est_name'] : $od['od_name']; ?>" style="border:0px; font-weight:bold; font-size:14px; width:230px;"></u></td>
			<td><span style="font-size:16px; letter-spacing:3px;">귀하</span></td>
		</tr>
		<tr><td height="1" bgcolor="000000"></td></tr>
		<tr><td height="30"></td></tr>
		<tr>
			<td colspan="2"><span style="font-size:16px;letter-spacing:0px;">아래와 같이 견적합니다.</span></td>
		</tr>
		</table>

		</td>
		<td width="10"></td>
		<td valign="top" width="400">
		
		<div style="position:absolute;padding-left:300px;margin-top:-30px;"><img src="<?php echo $config['cf_4'] ?>" width="80"></div>
		<table width="100%" cellpadding="0" cellspacing="0" border="1">
		<tr height="25">
			<td align="center" width="18%">등록번호</td>
			<td style="padding:5px;" colspan="3"><?php echo $default['de_admin_company_saupja_no']; ?></td>
		</tr>
		<tr height="25">
			<td align="center" width="18%">상호</td>
			<td style="padding:5px;"><?php echo $default['de_admin_company_name']; ?></td>
			<td align="center" width="18%">성명</td>
			<td style="padding:5px;"><?php echo $default['de_admin_company_owner']; ?></td>
		</tr>
		<tr height="25">
			<td align="center" width="18%">주소</td>
			<td style="padding:5px;" colspan="3"><?php echo $default['de_admin_company_addr']; ?></td>
		</tr>
		<tr height="25">
			<td align="center" width="18%">업태</td>
			<td style="padding:5px;">제조,도소매 외</td>
			<td align="center" width="18%">종목</td>
			<td style="padding:5px;">간판 및 광고물외</td>
		</tr>
		<tr height="25">
			<td align="center" width="18%">TEL</td>
			<td style="padding:5px;"><?php echo $default['de_admin_company_tel']; ?></td>
			<td align="center" width="18%">FAX</td>
			<td style="padding:5px;"><?php echo $default['de_admin_company_fax']; ?></td>
		</tr>
		</table>
		
		</td>
	</tr>
	</table>

	</td>
</tr>
<tr><td height="20"></td></tr>
<tr>
	<td>

	<table width="100%" cellpadding="0" cellspacing="0" border="1">
	<tr height="40">
		<td colspan="7" style="padding-left:30px;">
			<span style="font-weight:bold;font-size:14px;">
			합 계 금 액&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			금&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight:bold;font-size:14px;letter-spacing:4px;" class="price_to_hangul"></span>&nbsp;&nbsp;&nbsp;정
			</span>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
			<span style="font-weight:bold;font-size:14px;letter-spacing:1px;" class="amount_order"></span>
		</td>
	</tr>
	<tr height="28">
		<td align="center">제품명</td>
		<td align="center">선택사항</td>
		<td align="center">수량</td>
		<td align="center">단가</td>
		<td align="center">공급가액</td>
		<td align="center">세액</td>
        <td align="center">합계</td>
	</tr>

    <?php
        $a = 0;
        $m_money=0;
        $price=0;  //공가금액
        $vat=0;  //세액

        for($i=0; $i<count($carts); $i++) { 
            $options = $carts[$i]['options'];

            for($k=0; $k<count($options); $k++) { 
                if( $options[$k]['ct_discount'] ) {
                    $_ct_discount = $options[$k]['ct_discount'] / $options[$k]['ct_qty'];
                    $options[$k]['opt_price'] = $options[$k]['opt_price']-$_ct_discount;
                }
    ?>
            <tr height="28" <?php echo $a % 2 ? 'bgcolor="#eeeeee"' : ''; ?>>
                <td align="left" style="padding-left:5px;"><div class="goods_name"><?php echo $carts[$i]['it_name']; ?></div></td>
                <td align="left" style="padding-left:5px;">
                <?php echo $options[$k]['ct_option'] != $options[$k]['it_name'] ? '옵션: ' . $options[$k]['ct_option'] : ''; ?>
                <?php echo $options[$k]['ct_option'] != $options[$k]['it_name'] && $options[$k]['cs'] && $k == 0 ? ', ' : ''; ?>
                <?php if($options[$k]['cs'] && $k == 0){ ?>
                    사이즈: 가로(<?php echo $options[$k]['cs']['size_width']; ?>mm) X 세로(<?php echo $options[$k]['cs']['size_height']; ?>mm)
                <?php } ?>
                </td>

                <?php
                $sql_taxInfo = 'select `it_taxInfo` from `g5_shop_item` where `it_id` = "'.$options[$k]['it_id'].'"';
                $it_taxInfo = sql_fetch($sql_taxInfo);
                if($it_taxInfo['it_taxInfo']=="영세"){ 
                    $m_money = $m_money + ($options[$k]['opt_price']* $options[$k]['ct_qty'] - $options[$k]['opt_price']* $options[$k]['ct_qty']/ 1.1);
                ?>
                <td align="center"><?php echo $options[$k]['ct_qty']; ?></td>
                <td align="center"><?php echo number_format($options[$k]['opt_price']); ?></td>
                <td align="center"><?php echo number_format($options[$k]['opt_price']* $options[$k]['ct_qty']); ?></td>
                <td align="center">0</td>
                <td align="center"><?php echo number_format(($options[$k]['opt_price'] * $options[$k]['ct_qty'])); ?></td>

                <?php 
                    $price = $price + ($options[$k]['opt_price'] * $options[$k]['ct_qty']); 
                }else{ 
                    
                ?>
                <td align="center"><?php echo $options[$k]['ct_qty']; ?></td>
                <td align="center"><?php echo number_format($options[$k]['opt_price']); ?></td>
                <td align="center"><?php echo number_format($options[$k]['opt_price'] / 1.1 * $options[$k]['ct_qty']); ?></td>
                <td align="center"><?php echo number_format($options[$k]['opt_price'] / 1.1 / 10 * $options[$k]['ct_qty']); ?></td>
                <td align="center"><?php echo number_format(($options[$k]['opt_price'] / 1.1 * $options[$k]['ct_qty']) + ($options[$k]['opt_price'] / 1.1 / 10 * $options[$k]['ct_qty'])); ?></td>
                <?php
                    $price = $price + ($options[$k]['opt_price'] / 1.1 * $options[$k]['ct_qty']);
                    $vat = $vat + ($options[$k]['opt_price'] / 1.1 / 10 * $options[$k]['ct_qty']);
                } ?>
            </tr>
            <?php $a++; ?>
        <?php } ?>
    <?php } ?>

    <?php if ( $od['od_send_cost'] ) { ?>
        <tr height="28" <?php echo $a % 2 ? 'bgcolor="#eeeeee"' : ''; ?>>
            <td align="left" style="padding-left:5px;"><div class="goods_name">배송비</div></td>
            <td align="left" style="padding-left:5px;"></td>
            <td align="center"></td>
            <td align="center"><?php echo number_format($od['od_send_cost']); ?></td>
            <td align="center"><?php echo number_format($od['od_send_cost'] / 1.1); ?></td>
            <td align="center"><?php echo number_format($od['od_send_cost'] / 1.1 / 10); ?></td>
            <td align="center"><?php echo number_format($od['od_send_cost'] / 1.1 + $od['od_send_cost'] / 1.1 / 10); ?></td>
        </tr>
        <?php $a++; ?>
    <?php 
            $price = $price + ($od['od_send_cost'] / 1.1);
            $vat = $vat + ($od['od_send_cost'] / 1.1 / 10);
        } ?>
    
    <?php if ( $od['od_send_cost2'] ) { ?>
        <tr height="28" <?php echo $a % 2 ? 'bgcolor="#eeeeee"' : ''; ?>>
            <td align="left" style="padding-left:5px;"><div class="goods_name">추가 배송비</div></td>
            <td align="left" style="padding-left:5px;"></td>
            <td align="center"></td>
            <td align="center"><?php echo number_format($od['od_send_cost2']); ?></td>
            <td align="center"><?php echo number_format($od['od_send_cost2'] / 1.1); ?></td>
            <td align="center"><?php echo number_format($od['od_send_cost2'] / 1.1 / 10); ?></td>
            <td align="center"><?php echo number_format($od['od_send_cost2'] / 1.1 + $od['od_send_cost2'] / 1.1 / 10); ?></td>
        </tr>
        <?php $a++; ?>
    <?php 
            $price = $price + ($od['od_send_cost2'] / 1.1);
            $vat = $vat + ($od['od_send_cost2'] / 1.1 / 10);
        } ?>

    <?php if ( $od['od_cart_discount'] ) { ?>
        <tr height="28" <?php echo $a % 2 ? 'bgcolor="#eeeeee"' : ''; ?>>
            <td align="left" style="padding-left:5px;"><div class="goods_name">할인</div></td>
            <td align="left" style="padding-left:5px;"></td>
            <td align="center"></td>
            <td align="center">- <?php echo number_format($od['od_cart_discount']); ?></td>
            <td align="center">- <?php echo number_format($od['od_cart_discount'] / 1.1); ?></td>
            <td align="center">- <?php echo number_format($od['od_cart_discount'] / 1.1 / 10); ?></td>
            <td align="center">- <?php echo number_format($od['od_cart_discount'] / 1.1 + $od['od_cart_discount'] / 1.1 / 10); ?></td>
        </tr>
        <?php $a++; ?>
    <?php 
            //$price = $price - ($od['od_cart_discount'] / 1.1);
            //$vat = $vat - ($od['od_cart_discount'] / 1.1 / 10);
        } ?>

    <?php if ( $od['od_cart_discount2'] ) { ?>
        <tr height="28" <?php echo $a % 2 ? 'bgcolor="#eeeeee"' : ''; ?>>
            <td align="left" style="padding-left:5px;"><div class="goods_name">추가할인</div></td>
            <td align="left" style="padding-left:5px;"></td>
            <td align="center"></td>
            <td align="center">- <?php echo number_format($od['od_cart_discount2']); ?></td>
            <td align="center">- <?php echo number_format($od['od_cart_discount2'] / 1.1); ?></td>
            <td align="center">- <?php echo number_format($od['od_cart_discount2'] / 1.1 / 10); ?></td>
            <td align="center">- <?php echo number_format($od['od_cart_discount2'] / 1.1 + $od['od_cart_discount2'] / 1.1 / 10); ?></td>
        </tr>
        <?php $a++; ?>
    <?php 
            $price = $price - ($od['od_cart_discount2'] / 1.1);
            $vat = $vat - ($od['od_cart_discount2'] / 1.1 / 10);
        } ?>

    <?php if ( $od['od_sales_discount'] ) { ?>
        <tr height="28" <?php echo $a % 2 ? 'bgcolor="#eeeeee"' : ''; ?>>
            <td align="left" style="padding-left:5px;"><div class="goods_name">매출할인</div></td>
            <td align="left" style="padding-left:5px;"></td>
            <td align="center"></td>
            <td align="center">- <?php echo number_format($od['od_sales_discount']); ?></td>
            <td align="center">- <?php echo number_format($od['od_sales_discount'] / 1.1); ?></td>
            <td align="center">- <?php echo number_format($od['od_sales_discount'] / 1.1 / 10); ?></td>
            <td align="center">- <?php echo number_format($od['od_sales_discount'] / 1.1 + $od['od_sales_discount'] / 1.1 / 10); ?></td>
        </tr>
        <?php $a++; ?>
    <?php 
            $price = $price - ($od['od_sales_discount'] / 1.1);
            $vat = $vat - ($od['od_sales_discount'] / 1.1 / 10);
        } ?>


	<tr height="28">
		<td align="center">합계</td>
		<td align="center"></td>
		<td align="center"></td>
		<td align="center"></td>
		<td align="center"><?php echo number_format($price); ?></td>
		<td align="center"><?php echo number_format($vat); ?></td>
        <td align="center"><?php echo number_format( $price + $vat ); ?></td>
	</tr>
	</table>




	</td>
</tr>
<tr><td height="10"></td></tr>
<tr>
    <div style="padding:10px;">
        <?php if ( $w == 'u') { ?>
            <td><textarea name="content" style="border:1px solid #ddd; font-size:12px; width:700px;min-height:150px; padding: 7px"><?php echo $est['est_content'] ? $est['est_content'] : '' ?></textarea></td>
        <?php }else{ ?>
            <td style="">
                <?php echo $est['est_content'] ? nl2br($est['est_content']) : get_samhwa_content('estimate_info'); ?>
            </td>
        <?php } ?>
	</div>	
</tr>
<P>
<tr height="10">
</tr>
<tr>
	<td>
	
	<table width="100%" cellpadding="0" cellspacing="0" border="1">
	<!-- <tr height="40">
		<td align="center">전화번호안내</td>
		<td align="center" colspan="2">영업1팀(규격품)</td>
		<td align="center" colspan="3">T. 02) 2267-8080<br>F. 02) 2267-6121</td>
		<td align="center" colspan="2">영업2팀(주문제작)</td>
		<td align="center">T. 02) 2268-2868<br>F. 02) 2268-2070</td>
	</tr> -->
	<tr height="30">
		<td align="center">입금계좌안내</td>
		<td align="center"><?php echo $banks[0][0]; ?> <?php echo $banks[0][1]; ?> <?php echo $banks[1][0]; ?> <?php echo $banks[1][1]; ?>  <?php echo $banks[2][0]; ?> <?php echo $banks[2][1]; ?> </td>
		<!-- <td align="center" colspan="2"><?php echo $banks[0][1]; ?></td>
		<td align="center"><?php echo $banks[1][0]; ?></td>
		<td align="center" colspan="2"><?php echo $banks[1][1]; ?></td> -->
		<td align="center">예금주</td>
		<td align="center"><?php echo $default['de_admin_company_name']; ?></td>
	</tr>
	</table>

	</td>
</tr>
<tr><td height="10"></td></tr>
<tr>
	<td>

	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td rowspan="3" valign="top"><img src="<?php echo $config['cf_3'] ?>"></td>
		<td><?php echo get_samhwa_content('footer_info'); ?></td>
	</tr>
	</table>

	</td>
</tr>
</table>






<script>
function go_prints(){
    samhwaprint($('html').html());
}
function go_excels(){
	var frm = document.getElementById("hFrm");
	frm.src = "/custom/e_excel";
}

function go_email(){
	$("#email_input").show();
}

function send_mail(){
	var email	= $("input[name='email']").val(); 
	var seq		= '<?php echo $od_id; ?>';
	
	if( !email || email=='' ){
		alert("이메일이 입력되어 있지 않습니다.");
		return;
	}
		
	var	name	= $("input[name='name']").val();
	var	deli	= $("input[name='comment']").val();
	var title   = $('input[name="title"]').val();
	var remarks = '<br />' + $('textarea[name="remarks"]').val().replace(/\n/g, "<br />");

	// hFrm.location.href = "../order_process/cs_estimate_send?seq="+seq+"&email="+email+"&name="+name+"&deli="+deli;
	$('#hFrm').attr('src', "../order_process/cs_estimate_send?seq="+seq+"&email="+email+"&name="+name+"&deli="+deli+"&title="+title + "&remarks=" + remarks);
}

function go_exceldown(){
    var od_id		= '<?php echo $od_id; ?>';
    
    location.href= "./pop.estimate.excel.php?od_id=" + od_id;
}

function go_down(){
	var seq		= '<?php echo $od_id; ?>';
    hFrm.location.href = "../order/cs_estimate_down?seq="+seq;
    
}

function go_send() {
	var od_id		= '<?php echo $od_id; ?>';
    var email_chk   = !!$("input[name='email_chk']").is(":checked") == true;
    var u_email     = $("input[name='u_email']").val();
    var hp_chk      = !!$("input[name='hp_chk']").is(":checked") == true;
    var u_hp        = $("input[name='u_hp']").val();

    if (!email_chk && !hp_chk) {
        alert('전송방법을 선택해주세요.');
        return;
    }

    $.ajax({
        method: "POST",
        url: "/adm/shop_admin/ajax.estimate.send.php",
        data: {
            od_id: od_id,
            email_chk: email_chk,
            u_email: u_email,
            hp_chk: hp_chk,
            u_hp: u_hp
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

}

function go_submit() {
	var od_id		= '<?php echo $od_id; ?>';
	var	name	= $("input[name='name']").val();
	var	content	= $("textarea[name='content']").val();
    var title   = $('input[name="title"]').val();
    var	time	= $("input[name='time']").val();
    

    $.ajax({
        method: "POST",
        url: "/adm/shop_admin/ajax.estimate.edit.php",
        data: {
            od_id: od_id,
            name: name,
            content: content,
            title: title,
            time: time,
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
}

$(".price_to_hangul").text("<?=samhwa_price_to_hangul( $price + $vat ); ?>원");
$(".amount_order").text("(  ￦<?=number_format( $price + $vat ); ?>  ) VAT포함");

</script>

</div>
</body>

<iframe id="hFrm" style="display:none;"></iframe>


</div>

<div id="main_demo" class="hide"></div>
<div id="openDialogLayer" style="display: none">
	<div align="center" id="openDialogLayerMsg"></div>
</div>

<div id="ajaxLoadingLayer" style="display: none"></div>

<div id="noliteService" class="hide">

</body>
</html>