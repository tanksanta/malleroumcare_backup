<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

// 목록헤드
if(isset($wset['ivhead']) && $wset['ivhead']) {
	add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/head/'.$wset['ivhead'].'.css" media="screen">', 0);
	$head_class = 'list-head';
} else {
	$head_class = (isset($wset['ivcolor']) && $wset['ivcolor']) ? 'tr-head border-'.$wset['ivcolor'] : 'tr-head border-black';
}

// 헤더 출력
if($header_skin)
	include_once('./header.php');
?>

<div class="vertical-spacer">
    <div class="well well-sm">
        <i class="fa fa-shopping-cart fa-lg"></i> 주문번호 : <strong><?php echo $od['order_seq']; ?></strong>
    </div>

    <style>
        .delivery-info { margin:0px; padding:0px; padding-left:15px; line-height:22px; white-space:nowrap; }
    </style>

    <div class="table-responsive">
        <table class="div-table table bsk-tbl bg-white">
            <tbody>
                <tr class="<?php echo $head_class;?>">
                    <th scope="col"><span>상품명 / 옵션명</span></th>
                    <th scope="col"><span>수량</span></th>
                    <th scope="col"><span>판매가</span></th>
                    <th scope="col"><span>소계</span></th>
                    <th scope="col"><span>포인트</span></th>
                    <th scope="col"><span>상태</span></th>
                </tr>
                <?php for($i=0; $i < count($item); $i++) { ?>
                    <tr>
                        <td class="text-center"><?php echo $item[$i]["goods_name"]; ?></td>
                        <td class="text-center"><?php echo $item[$i]["ea"]; ?></td>
                        <td class="text-center"><?php echo $item[$i]["price"]/$item[$i]["ea"]; ?></td>
                        <td class="text-center"><?php echo $item[$i]["ori_price"]; ?></td>
                        <td class="text-center"><?php echo $item[$i]["point"]; ?></td>
                        <td class="text-center"></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="well">
        <div class="row">
            <div class="col-xs-6">주문총액</div>
            <div class="col-xs-6 text-right">
                <strong><?php echo number_format($od['settleprice'] - $od["shipping_cost"] - $od["coupons_sale"] - $od["enuri"]); ?> 원</strong>
            </div>

            <?php if($od['coupon_sale'] > 0) { ?>
                <div class="col-xs-6">주문금액 쿠폰할인</div>
                <div class="col-xs-6 text-right">
                    <strong><?php echo number_format($od['coupon_sale']); ?> 원</strong>
                </div>
            <?php } ?>

            <?php if ($od['shipping_cost'] > 0) { ?>
                <div class="col-xs-6">배송비</div>
                <div class="col-xs-6 text-right">
                    <strong><?php echo number_format($od['shipping_cost']); ?> 원</strong>
                </div>
            <?php } ?>

            <?php if ($od['enuri'] > 0) { ?>
                <div class="col-xs-6">할인금액</div>
                <div class="col-xs-6 text-right">
                    <strong>- <?php echo number_format($od['enuri']); ?> 원</strong>
                </div>
            <?php } ?>
            
            <div class="col-xs-6 red"> <b>합계금액</b></div>
            <div class="col-xs-6 text-right red od_tot_price">
                <strong class="print_price"><?php echo number_format($od["settleprice"]); ?> 원</strong>
            </div>

        </div>
    </div>

    <?php
        switch($od['payment']){
            case "card":
                $payment_nm = "카드";
                break;
            case "point";
                $payment_nm = "포인트";
                break;
            case "account";
                $payment_nm = "무통장 (".$row['bank_account'].")";
                break;
            case "vitual";
                $payment_nm = "가상계좌";
                break;
            case "cellphone";
                $payment_nm = "휴대폰";
                break;
            default: 
                $payment_nm = "";
        }
    ?>
    <div class="panel panel-success">
        <div class="panel-heading"><strong><i class="fa fa-star fa-lg"></i> 결제정보</strong></div>
        <div class="table-responsive">
            <table class="div-table table bsk-tbl bg-white">
            <col width="120">
            <tbody>
            <tr>
                <th scope="row">주문번호</th>
                <td><?php echo $od['order_seq']; ?></td>
            </tr>
            <tr>
                <th scope="row">주문일시</th>
                <td><?php echo $od['regist_date']; ?></td>
            </tr>
            <tr>
                <th scope="row">결제방식</th>
                <td><?php echo $payment_nm; ?></td>
            </tr>
            <tr>
                <th scope="row">결제상태</th>
                <td><?php echo ($od["deposit_yn"] == "y")?"<b>결제</b>":"미결제"; ?></td>
            </tr>
            <tr class="active">
                <th scope="row">결제금액</th>
                <td><?php echo display_price($od["settleprice"]); ?></td>
            </tr>
            <?php if($od['od_receipt_price'] > 0) {	?>
                <tr>
                    <th scope="row">결제일시</th>
                    <td><?php echo $od['od_receipt_time']; ?></td>
                </tr>
            <?php } ?>
            <?php if($app_no_subj) { // 승인번호, 휴대폰번호, 거래번호 ?>
                <tr>
                    <th scope="row"><?php echo $app_no_subj; ?></th>
                    <td><?php echo $app_no; ?></td>
                </tr>
            <?php } ?>
            <?php if($disp_bank) { // 계좌정보 ?>
                <tr>
                    <th scope="row">입금자명</th>
                    <td><?php echo get_text($od['od_deposit_name']); ?></td>
                </tr>
                <tr>
                    <th scope="row">입금계좌</th>
                    <td><?php echo get_text($od['od_bank_account']); ?></td>
                </tr>
            <?php } ?>
            <?php if($disp_receipt_href) { ?>
                <tr>
                    <th scope="row">영수증</th>
                    <td><a <?php echo $disp_receipt_href;?>>영수증 출력</a></td>
                </tr>
            <?php } ?>
            <?php if ($od['od_receipt_point'] > 0) { ?>
                <tr>
                    <th scope="row">포인트사용</th>
                    <td><?php echo display_point($od['od_receipt_point']); ?></td>
                </tr>
            <?php } ?>
            <?php if ($od['od_refund_price'] > 0) { ?>
                <tr>
                    <th scope="row">환불 금액</th>
                    <td><?php echo display_price($od['od_refund_price']); ?></td>
                </tr>
            <?php } ?>
            <?php if($taxsave_href) { ?>
                <tr>
                    <th scope="row">현금영수증</th>
                    <td>
                        <a <?php echo $taxsave_href;?> class="btn btn-black btn-xs">
                            <?php echo ($taxsave_confirm) ? '현금영수증 확인하기' : '현금영수증을 발급하시려면 클릭하십시오.';?>
                        </a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
            </table>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><strong><i class="fa fa-user fa-lg"></i> 주문하신 분</strong></div>
        <div class="table-responsive">
            <table class="div-table table bsk-tbl bg-white">
                <col width="120">
                <tbody>
                    <tr>
                        <th scope="row">이 름</th>
                        <td><?php echo get_text($od['order_user_name']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">전화번호</th>
                        <td><?php echo get_text($od['order_phone']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">핸드폰</th>
                        <td><?php echo get_text($od['order_cellphone']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">E-mail</th>
                        <td><?php echo get_text($od['order_email']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><strong><i class="fa fa-gift fa-lg"></i> 받으시는 분</strong></div>
        <div class="table-responsive">
            <table class="div-table table bsk-tbl bg-white">
                <col width="120">
                <tbody>
                    <tr>
                        <th scope="row">이 름</th>
                        <td><?php echo get_text($od['recipient_user_name']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">전화번호</th>
                        <td><?php echo get_text($od['recipient_phone']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">핸드폰</th>
                        <td><?php echo get_text($od['recipient_cellphone']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">주 소</th>
                        <td><?php echo get_text($od["recipient_address"]. " ".$od["recipient_address_detail"]); ?></td>
                    </tr>

                    <?php if ($od['memo']) { ?>
                        <tr>
                            <th scope="row">전하실 말씀</th>
                            <td><?php echo conv_content($od['memo'], 0); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading"><strong><i class="fa fa-money fa-lg"></i> 결제합계</strong></div>
        <div class="table-responsive">
            <table class="div-table table bsk-tbl bg-white">
                <col width="120">
                <tbody>
                    <tr>	
                        <th scope="row">총구매액</th>
                        <td class="text-right"><strong><?php echo display_price($od['settleprice']); ?></strong></td>
                    </tr>
                    <tr class="active">
                        <th scope="row">배송비</th>
                        <td class="text-right"><strong><?php echo display_price($od['shipping_cost']);?></strong></td>
                    </tr>
                    <tr>
                        <th scope="row" id="alrdy">결제여부</th>
                        <td class="text-right"><strong><?php echo ($od["deposit_yn"] == "y")?"<b>결제</b>":"미결제"; ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <p class="print-hide text-center">
        <a class="btn btn-color btn-sm" href="./pastorderinquiry.php"><i class="fa fa-bars"></i> 목록으로</a>
        <button type="button" onclick="apms_print();" class="btn btn-black btn-sm"><i class="fa fa-print"></i> 프린트</button>
    </p>
</div>