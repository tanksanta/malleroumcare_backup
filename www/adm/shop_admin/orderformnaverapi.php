<?php
$sub_menu = '400400';
include_once('./_common.php');

$cart_title3 = '주문번호';
$cart_title4 = '배송완료';

auth_check($auth[$sub_menu], "w");

$g5['title'] = "주문 내역 수정";
include_once(G5_ADMIN_PATH.'/admin.head.php');

if ($default['de_naverpayorder_test']) {
    alert("테스트중에는 조회가 불가능합니다.");
}

// 완료된 주문에 포인트를 적립한다.
save_order_point("완료");

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

$od['mb_id'] = $od['mb_id'] ? $od['mb_id'] : "비회원";
//------------------------------------------------------------------------------

// 서버의 로드지연처리로 중복값이 발생할수있는데 중복된값을 제거함.
sql_query("delete from {$g5['g5_shop_cart_table']} where od_id = '".$od_id."' and ProductOrderID <> '' and ct_id not in ( select ct_id from (select ct_id from {$g5['g5_shop_cart_table']} WHERE od_id = '".$od_id."' and ProductOrderID <> '' group by ProductOrderID) as ct_id)");

include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');
$aor = new NHNAPIORDER();
$aor->oderdetailsync($od_id); // 주문정보 동기화

$query = "select GROUP_CONCAT(ProductOrderID SEPARATOR ',') as poids from {$g5['g5_shop_cart_table']} where od_id = '".$od_id."' ";
$ct = sql_fetch($query);
$poids = $ct['poids'];
$ProductOrderID = explode(',', $poids);
$xml = $aor->GetProductOrderInfoList($ProductOrderID);

include_once(G5_PLUGIN_PATH.'/wznaverpay/lib/core.lib.php');

if (strtoupper($ResponseType) !== 'SUCCESS') {
    alert("데이터 호출에 실패하였습니다.");
}

$od_cart_count = (int)$req->ReturnedDataCount;

$arr_cart = array();
foreach ($req->ProductOrderInfoList as $k => $v) {

    $row = array();
    $row['ProductOrderID']  = (string)$v->ProductOrder->ProductOrderID; // 상품주문번호
    $row['it_id']           = (string)$v->ProductOrder->ProductID; // 상품 번호
    $row['it_name']         = (string)$v->ProductOrder->ProductName; // 상품명
    $row['ProductOption']   = (string)$v->ProductOrder->ProductOption; // 상품옵션
    $row['OptionCode']      = (string)$v->ProductOrder->OptionCode; // 주문 등록시 사용한 옵션 코드
    $row['ProductOrderStatus']      = (string)$v->ProductOrder->ProductOrderStatus; // 상품 주문 상태

    $row['ct_price']                = (int)$v->ProductOrder->UnitPrice ? (int)$v->ProductOrder->UnitPrice : (int)$v->ProductOrder->TotalPaymentAmount; // 상품가격
    $row['UnitPrice']               = (int)$v->ProductOrder->UnitPrice; // 상품가격
    $row['TotalProductAmount']      = (int)$v->ProductOrder->TotalProductAmount; // 상품 주문 금액(할인 적용 전 금액)
    $row['ProductDiscountAmount']   = (int)$v->ProductOrder->ProductDiscountAmount; // 상품별 할인액(즉시 할인+상품 할인 쿠폰+복수 구매 할인)
    $row['TotalPaymentAmount']      = (int)$v->ProductOrder->TotalPaymentAmount; // 총 결제 금액(할인 적용 후 금액)

    $row['ct_qty']                  = (int)$v->ProductOrder->Quantity; // 수량
    $row['MallManageCode']          = (string)$v->ProductOrder->MallManageCode; // MallManageCode
    $row['ClaimType']               = (string)$v->ProductOrder->ClaimType; // ClaimType
    $row['ClaimStatus']             = (string)$v->ProductOrder->ClaimStatus; // ClaimStatus
    $row['PlaceOrderStatus']        = (string)$v->ProductOrder->PlaceOrderStatus; // PlaceOrderStatus
    $row['DelayedDispatchReason']   = (string)$v->ProductOrder->DelayedDispatchReason; // DelayedDispatchReason
    $row['ShippingFeeType']         = (string)$v->ProductOrder->ShippingFeeType; // ShippingFeeType
    $row['DeliveryFeeAmount']       = (int)$v->ProductOrder->DeliveryFeeAmount; // DeliveryFeeAmount
    $row['ShippingDueDate']         = substr((string)$v->ProductOrder->ShippingDueDate, 0, 10); // 발송기한
    $row['ShippingMemo']            = (string)$v->ProductOrder->ShippingMemo; // 배송메모

    // ExchangeInfo
    $ClaimRequestDate               = (string)$v->ExchangeInfo->ClaimRequestDate; // 클레임 요청일
    if ($ClaimRequestDate) {
        $ClaimRequestDate               = substr(str_replace('T', ' ', $ClaimRequestDate), 0, 19);
        $ClaimRequestDate               = date('Y-m-d H:i:s', strtotime($ClaimRequestDate.'+9Hour'));
        $row['ExchangeInfo']['ClaimRequestDate']        = $ClaimRequestDate;
    }

    $row['ExchangeInfo']['ExchangeReason']        = (string)$v->ExchangeInfo->ExchangeReason;
    $row['ExchangeInfo']['ClaimStatus']           = (string)$v->ExchangeInfo->ClaimStatus;
    $row['ExchangeInfo']['HoldbackReason']        = (string)$v->ExchangeInfo->HoldbackReason;
    $row['ExchangeInfo']['HoldbackStatus']        = (string)$v->ExchangeInfo->HoldbackStatus;
    $row['ExchangeInfo']['RequestChannel']        = (string)$v->ExchangeInfo->RequestChannel;
    $row['ExchangeInfo']['CollectAddress']        = (string)$v->ExchangeInfo->CollectAddress; // 수거지(from) 주소
    $row['ExchangeInfo']['ReturnReceiveAddress']  = (string)$v->ExchangeInfo->ReturnReceiveAddress; // 수취지(to) 주소

    // CancelInfo
    $ClaimRequestDate               = (string)$v->CancelInfo->ClaimRequestDate; // 클레임 요청일
    if ($ClaimRequestDate) {
        $ClaimRequestDate               = substr(str_replace('T', ' ', $ClaimRequestDate), 0, 19);
        $ClaimRequestDate               = date('Y-m-d H:i:s', strtotime($ClaimRequestDate.'+9Hour'));
        $row['CancelInfo']['ClaimRequestDate']        = $ClaimRequestDate;
    }

    $row['CancelInfo']['CancelReason']          = (string)$v->CancelInfo->CancelReason;
    $row['CancelInfo']['ClaimStatus']           = (string)$v->CancelInfo->ClaimStatus;
    $row['CancelInfo']['HoldbackReason']        = (string)$v->CancelInfo->HoldbackReason;
    $row['CancelInfo']['HoldbackStatus']        = (string)$v->CancelInfo->HoldbackStatus;
    $row['CancelInfo']['RequestChannel']        = (string)$v->CancelInfo->RequestChannel;
    $row['CancelInfo']['LastTreatmentPerson']   = (string)$v->CancelInfo->LastTreatmentPerson;

    $CancelCompletedDate   = (string)$v->CancelInfo->CancelCompletedDate; // 2019-06-05 : 취소 완료일
    if ($CancelCompletedDate) {
        $CancelCompletedDate               = substr(str_replace('T', ' ', $CancelCompletedDate), 0, 19);
        $CancelCompletedDate               = date('Y-m-d H:i:s', strtotime($CancelCompletedDate.'+9Hour'));
        $row['CancelInfo']['CancelCompletedDate']        = $CancelCompletedDate;
    }

    $row['DeliveryCompany']         = (string)$v->Delivery->DeliveryCompany; // 택배사
    $row['DeliveryMethod']          = (string)$v->Delivery->DeliveryMethod; // 배송방법
    $row['DeliveryStatus']          = (string)$v->Delivery->DeliveryStatus; // 배송상태
    $row['SendDate']                = (string)$v->Delivery->SendDate; // 배송일
    $row['TrackingNumber']          = (string)$v->Delivery->TrackingNumber; // 송장번호

    // 반품정보
    $ClaimRequestDate               = (string)$v->ReturnInfo->ClaimRequestDate; // 클레임 요청일
    if ($ClaimRequestDate) {
        $ClaimRequestDate               = substr(str_replace('T', ' ', $ClaimRequestDate), 0, 19);
        $ClaimRequestDate               = date('Y-m-d H:i:s', strtotime($ClaimRequestDate.'+9Hour'));
        $row['ReturnInfo']['ClaimRequestDate']        = $ClaimRequestDate;
    }

    $row['ReturnInfo']['ClaimStatus']             = (string)$v->ReturnInfo->ClaimStatus; // 클레임 처리 상태 : NPI_CLAIMPROCESSCODE
    $row['ReturnInfo']['HoldbackDetailedReason']  = (string)$v->ReturnInfo->HoldbackDetailedReason; // 구매 확정 보류 상세 사유
    $row['ReturnInfo']['HoldbackReason']          = (string)$v->ReturnInfo->HoldbackReason; // 구매 확정 보류 사유 : NPI_HOLDBACKREASONCODE
    $row['ReturnInfo']['HoldbackStatus']          = (string)$v->ReturnInfo->HoldbackStatus; // 보류 상태 코드 : NPI_HOLDBACKSTATUSCODE
    $row['ReturnInfo']['RequestChannel']          = (string)$v->ReturnInfo->RequestChannel; // 접수 채널
    $row['ReturnInfo']['ReturnReason']            = (string)$v->ReturnInfo->ReturnReason; // 반품 사유 코드 : NPI_CLAIMREASONCODE_RETURN

    $arr_cart[] = $row;
}

$pg_anchor = '<ul class="anchor">
<li><a href="#anc_sodr_list">주문상품 목록</a></li>
<li><a href="#anc_sodr_pay">주문결제 내역</a></li>
<li><a href="#anc_sodr_memo">상점메모</a></li>
<li><a href="#anc_sodr_orderer">주문하신 분</a></li>
<li><a href="#anc_sodr_taker">받으시는 분</a></li>
</ul>';

$qstr1 = "od_status=".urlencode($od_status)."&amp;od_settle_case=".urlencode($od_settle_case)."&amp;od_misu=$od_misu&amp;od_cancel_price=$od_cancel_price&amp;od_refund_price=$od_refund_price&amp;od_receipt_point=$od_receipt_point&amp;od_coupon=$od_coupon&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
if($default['de_escrow_use'])
    $qstr1 .= "&amp;od_escrow=$od_escrow";
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";
?>

<style>
.tbl_into,.tbl_into th,.tbl_into td{border:0;}
.tbl_into{width:400px;text-align:center;margin:0px;}
.tbl_into.list, .tbl_into.wide {width:100%;}
.tbl_into table {clear:both;width:100%;border-collapse:collapse;border-spacing:0;}
.tbl_into tbody, .tbl_into tr, .tbl_into td {vertical-align:middle}
.tbl_into caption{display:none}
.tbl_into th{padding:4px 0;border:1px solid #dcdcdc;color:#666;text-align:center;font-weight:bold;background-color: #fbfbfb;}
.tbl_into tbody td{padding:4px;border:1px solid #e5e5e5;color:#4c4c4c;background-color: #fff;text-align:left}
.tbl_into.list th, .tbl_into.list td {border:none}
.tbl_into th span.last:after {border:none;}
.tbl_into td .numberic, .tbl_into td.numberic {text-align:right;padding-right:4px;}
.tbl_into td .input_alignc {text-align:center;}
.tbl_into td.alignc {text-align:center}
.shippingmemo {padding:4px 2px;border:1px solid #dcdcdc;margin-bottom:3px;background-color:#fff;}
</style>

<section id="anc_sodr_list">
    <h2 class="h2_frm">주문상품 목록</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p><strong>네이버페이로 주문된 내역 입니다.</strong></p>
        <p>
            현재 주문상태 <strong><?php echo $od['od_status'] ?></strong>
            |
            네이버페이 주문번호 <strong><?php echo $od['od_naver_orderid'] ?></strong>
            |
            주문일시 <strong><?php echo substr($od['od_time'],0,16); ?> (<?php echo get_yoil($od['od_time']); ?>)</strong>
            |
            주문총액 <strong><?php echo number_format($od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2']); ?></strong>원
        </p>
        <?php if ($default['de_hope_date_use']) { ?><p>희망배송일은 <?php echo $od['od_hope_date']; ?> (<?php echo get_yoil($od['od_hope_date']); ?>) 입니다.</p><?php } ?>
        <?php if($od['od_mobile']) { ?>
        <p>모바일 쇼핑몰의 주문입니다.</p>
        <?php } ?>
    </div>

    <form name="frmorderform" id="frmorderform" method="post">
    <input type="hidden" name="od_id" id="od_id" value="<?php echo $od_id;?>" />

    <div class="tbl_head01 tbl_wrap">
        <table>
        <caption>주문 상품 목록</caption>
        <thead>
        <tr>
            <th scope="col"><input type="checkbox" id="sit_select_all"></th>
            <th scope="col">변경</th>
            <th scope="col">상품주문번호</th>
            <th scope="col">상품명</th>
            <th scope="col">옵션항목</th>
            <th scope="col">상태</th>
            <th scope="col">수량</th>
            <th scope="col">판매가</th>
            <th scope="col">배송비</th>
            <th scope="col">발주상태</th>
            <th scope="col">클래임</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $chk_cnt = 0;
        foreach ($arr_cart as $k => $v) {
            // 상품이미지
            $image = get_it_image($v['it_id'], 50, 50);

            $arr_statuscode = npi_statususercode($v['ProductOrderStatus'], $v['PlaceOrderStatus'], $v['ClaimType'], $v['ClaimStatus'], $v['ReturnInfo']['HoldbackStatus'], $v['DelayedDispatchReason']);
            $is_status = false;
            if ($arr_statuscode && is_array($arr_statuscode)) {
                $is_status = true;
            }

            // 장바구니 상태정보 동기화
            sql_query("update {$g5['g5_shop_cart_table']} set ct_status = '".productorderstatus_to_yc($v['ProductOrderStatus'])."' where ProductOrderID = '".$v['ProductOrderID']."'");
            ?>
            <tr>
                <td class="td_num">
                    <input type="checkbox" name="ct_chk[<?php echo $chk_cnt; ?>]" id="ct_chk_<?php echo $chk_cnt; ?>" value="<?php echo $chk_cnt; ?>" class="status_chk">
                    <input type="hidden" name="ProductOrderID[<?php echo $chk_cnt; ?>]" value="<?php echo $v['ProductOrderID']; ?>">
                    <input type="hidden" name="it_id[<?php echo $chk_cnt; ?>]" value="<?php echo $v['it_id']; ?>">
                </td>
                <td class="td_mngsmall">
                    <?php if ($is_status) {?>
                    <select name="operation[<?php echo $chk_cnt; ?>]" id="operation_<?php echo $chk_cnt;?>">
                        <option value="" selected="selected">선택</option>
                        <?php
                        foreach ($arr_statuscode as $k2 => $v2) {
                            echo '<option value="'.$v2.'">'.$NPI_OPERATIONCODE[$v2].'</option>';
                        }
                        ?>
                    </select>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
                <td class="td_center"><?php echo get_text($v['ProductOrderID']); ?></td>
                <td class="td_left">
                    <a href="./itemform.php?w=u&amp;it_id=<?php echo $v['it_id']; ?>"><?php echo $image; ?> <?php echo stripslashes($v['it_name']); ?></a>
                </td>
                <td class="td_left"><?php echo get_text($v['ProductOption']); ?></td>
                <td class="td_mngsmall"><?php echo productorderstatus_to_string($v['ProductOrderStatus']); ?></td>
                <td class="td_num"><?php echo $v['ct_qty']; ?></td></td>
                <td class="td_pt"><?php echo number_format($v['TotalProductAmount']);?></td>
                <td class="td_center"><?php echo $v['ShippingFeeType']; ?></td>
                <td class="td_center">
                    <?php
                    if ($v['ShippingMemo']) {
                        echo '<div class="shippingmemo">배송메모 : '.$v['ShippingMemo'].'</div>';
                    }
                    echo placeorderstatus_to_string($v['PlaceOrderStatus']);
                    if ($v['DelayedDispatchReason']) {
                        echo '<br />(발송지연 : '.$NPI_DELAYREASONCODE[$v['DelayedDispatchReason']].', 발송기한 : '.$v['ShippingDueDate'].')';
                    }
                    if ($v['DeliveryCompany']) {
                        echo '<br />('.$NPI_DELIVERYCOMPANYCODE[$v['DeliveryCompany']].', '.$NPI_DELIVERYMETHODCODE[$v['DeliveryMethod']].', '.$v['DeliveryStatus'].', '.$v['TrackingNumber'].')';
                    }
                    ?>
                </td>
                <td class="td_center">
                    <?php
                    if ($v['ReturnInfo']['ClaimStatus'] && is_array($v['ReturnInfo'])) {
                        ?>
                        <table class="tbl_into">
                            <?php if ($v['ReturnInfo']['ClaimRequestDate']) {?>
                            <tr>
                                <td>요청일</td>
                                <td><?php echo $v['ReturnInfo']['ClaimRequestDate'];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['ReturnInfo']['ClaimStatus']) {?>
                            <tr>
                                <td>상태</td>
                                <td><?php echo $NPI_CLAIMPROCESSCODE[$v['ReturnInfo']['ClaimStatus']];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['ReturnInfo']['HoldbackDetailedReason']) {?>
                            <tr>
                                <td>사유</td>
                                <td><?php echo $v['ReturnInfo']['HoldbackDetailedReason'];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['ReturnInfo']['HoldbackReason']) {?>
                            <tr>
                                <td>보류사유</td>
                                <td><?php echo $NPI_HOLDBACKREASONCODE[$v['ReturnInfo']['HoldbackReason']];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['ReturnInfo']['HoldbackStatus']) {?>
                            <tr>
                                <td>보류상태</td>
                                <td><?php echo $NPI_HOLDBACKSTATUSCODE[$v['ReturnInfo']['HoldbackStatus']];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['ReturnInfo']['RequestChannel']) {?>
                            <tr>
                                <td>접수채널</td>
                                <td><?php echo $v['ReturnInfo']['RequestChannel'];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['ReturnInfo']['ReturnReason']) {?>
                            <tr>
                                <td>사유</td>
                                <td><?php echo $NPI_CLAIMREASONCODE_RETURN[$v['ReturnInfo']['ReturnReason']];?></td>
                            </tr>
                            <?php } ?>
                        </table>
                        <?php
                    }
                    else if ($v['CancelInfo']['ClaimStatus'] && is_array($v['CancelInfo'])) {
                        ?>
                        <table class="tbl_into">
                            <?php if ($v['CancelInfo']['ClaimRequestDate']) {?>
                            <tr>
                                <td>취소요청일</td>
                                <td><?php echo $v['CancelInfo']['ClaimRequestDate'];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['CancelInfo']['CancelCompletedDate']) {?>
                            <tr>
                                <td>취소완료일</td>
                                <td><?php echo $v['CancelInfo']['CancelCompletedDate'];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['CancelInfo']['ClaimStatus']) {?>
                            <tr>
                                <td>상태</td>
                                <td><?php echo $NPI_CLAIMPROCESSCODE[$v['CancelInfo']['ClaimStatus']];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['CancelInfo']['CancelReason']) {?>
                            <tr>
                                <td>사유</td>
                                <td><?php echo $NPI_CLAIMREASONCODE_RETURN[$v['CancelInfo']['CancelReason']];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['CancelInfo']['HoldbackReason']) {?>
                            <tr>
                                <td>보류사유</td>
                                <td><?php echo $NPI_HOLDBACKREASONCODE[$v['CancelInfo']['HoldbackReason']];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['CancelInfo']['RequestChannel']) {?>
                            <tr>
                                <td>접수채널</td>
                                <td><?php echo $v['CancelInfo']['RequestChannel'];?></td>
                            </tr>
                            <?php } ?>
                        </table>
                        <?php
                    }
                    else if ($v['ExchangeInfo']['ClaimStatus'] && is_array($v['ExchangeInfo'])) {
                        ?>
                        <table class="tbl_into">
                            <?php if ($v['ExchangeInfo']['ClaimRequestDate']) {?>
                            <tr>
                                <td>교환요청일</td>
                                <td><?php echo $v['ExchangeInfo']['ClaimRequestDate'];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['ExchangeInfo']['ClaimStatus']) {?>
                            <tr>
                                <td>상태</td>
                                <td><?php echo $NPI_CLAIMPROCESSCODE[$v['ExchangeInfo']['ClaimStatus']];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['ExchangeInfo']['ExchangeReason']) {?>
                            <tr>
                                <td>사유</td>
                                <td><?php echo $NPI_CLAIMREASONCODE_RETURN[$v['ExchangeInfo']['ExchangeReason']];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['ExchangeInfo']['HoldbackReason']) {?>
                            <tr>
                                <td>보류사유</td>
                                <td><?php echo $NPI_EXCHANGEHOLDREASONCODE[$v['ExchangeInfo']['HoldbackReason']];?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($v['ExchangeInfo']['RequestChannel']) {?>
                            <tr>
                                <td>접수채널</td>
                                <td><?php echo $v['ExchangeInfo']['RequestChannel'];?></td>
                            </tr>
                            <?php } ?>
                        </table>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
            $chk_cnt++;
        }
        ?>
        </tbody>
        </table>
    </div>

    <div class="btn_list02 btn_list">
        <p>
            <input type="button" value="체크항목 상태변경 처리" class="btn_02 color_06" id="btn-status">
        </p>
    </div>

    <div class="local_desc01 local_desc">
        <p>주문, 입금, 준비, 배송, 완료는 장바구니와 주문서 상태를 모두 변경하지만, 취소, 반품, 품절은 장바구니의 상태만 변경하며, 주문서 상태는 변경하지 않습니다.</p>
        <p>개별적인(이곳에서의) 상태 변경은 모든 작업을 수동으로 처리합니다. 예를 들어 주문에서 입금으로 상태 변경시 입금액(결제금액)을 포함한 모든 정보는 수동 입력으로 처리하셔야 합니다.</p>
    </div>

    </form>

    <?php if ($od['od_mod_history']) { ?>
    <section id="sodr_qty_log">
        <h3>주문 수량변경 및 주문 전체취소 처리 내역</h3>
        <div>
            <?php echo conv_content($od['od_mod_history'], 0); ?>
        </div>
    </section>
    <?php } ?>

</section>

<?php if($od['od_test']) { ?>
<div class="od_test_caution">주의) 이 주문은 테스트용으로 실제 결제가 이루어지지 않았으므로 절대 배송하시면 안됩니다.</div>
<?php } ?>

<section id="anc_sodr_pay">
    <h2 class="h2_frm">주문결제 내역</h2>
    <?php echo $pg_anchor; ?>

    <?php
    // 주문금액 = 상품구입금액 + 배송비 + 추가배송비
    $amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'];

    // 입금액 = 결제금액 + 포인트
    $amount['receipt'] = $od['od_receipt_price'] + $od['od_receipt_point'];

    // 쿠폰금액
    $amount['coupon'] = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];

    // 취소금액
    $amount['cancel'] = $od['od_cancel_price'];
    ?>

    <div class="tbl_head01 tbl_wrap">
        <table>
        <caption>주문결제 내역</caption>
        <thead>
        <tr>
            <th scope="col">주문번호</th>
            <th scope="col">주문총액</th>
            <th scope="col">배송비</th>
            <th scope="col">총결제액</th>
            <th scope="col">주문취소</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php echo $od['od_id']; ?></td>
            <td class="td_numbig td_numsum"><?php echo display_price($amount['order']); ?></td>
            <td class="td_numbig"><?php echo display_price($od['od_send_cost'] + $od['od_send_cost2']); ?></td>
            <td class="td_numbig td_numincome"><?php echo number_format($amount['receipt']); ?>원</td>
            <td class="td_numbig td_numcancel"><?php echo number_format($amount['cancel']); ?>원</td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_sodr_memo">
    <h2 class="h2_frm">상점메모</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            현재 열람 중인 주문에 대한 내용을 메모하는곳입니다.
        </p>
    </div>

    <form name="frmorderform2" action="./orderformnaverapiupdate.php" method="post">
    <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="mod_type" value="memo">

    <div class="tbl_wrap">
        <label for="od_shop_memo" class="sound_only">상점메모</label>
        <textarea name="od_shop_memo" id="od_shop_memo" rows="8"><?php echo stripslashes($od['od_shop_memo']); ?></textarea>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="메모 수정" class="btn_submit btn">
    </div>

    </form>
</section>

<section>
    <h2 class="h2_frm">주문자/배송지 정보</h2>
    <?php echo $pg_anchor; ?>

    <div class="compare_wrap">

        <div class="local_desc01 local_desc">
            <p>주문하신 분, 받으시는 분 정보는 네이버페이측으로 동기화할 수 없으며 일방적으로 가져오는것만 가능하므로 수정이 불가합니다.</p>
            <p>수정이 필요할경우 네이버페이관리자화면에서 수정해주시기 바랍니다.</p>
        </div>

        <section id="anc_sodr_orderer" class="compare_left">
            <h3>주문하신 분</h3>

            <div class="tbl_frm01">
                <table>
                <caption>주문자/배송지 정보</caption>
                <colgroup>
                    <col class="grid_4">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <th scope="row"><label for="od_name"><span class="sound_only">주문하신 분 </span>이름</label></th>
                    <td><?php echo get_text($od['od_name']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_tel"><span class="sound_only">주문하신 분 </span>전화번호1</label></th>
                    <td><?php echo get_text($od['od_tel']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_hp"><span class="sound_only">주문하신 분 </span>전화번호2</label></th>
                    <td><?php echo get_text($od['od_hp']); ?></td>
                </tr>
                </tbody>
                </table>
            </div>
        </section>

        <section id="anc_sodr_taker" class="compare_right">
            <h3>받으시는 분</h3>

            <div class="tbl_frm01">
                <table>
                <caption>받으시는 분 정보</caption>
                <colgroup>
                    <col class="grid_4">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <th scope="row"><span class="sound_only">받으시는 분 </span>이름</th>
                    <td><?php echo get_text($od['od_b_name']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">받으시는 분 </span>전화번호1</th>
                    <td><?php echo get_text($od['od_b_tel']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">받으시는 분 </span>전화번호2</th>
                    <td><?php echo get_text($od['od_b_hp']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">받으시는 분 </span>주소</th>
                    <td>(<?php echo $od['od_b_zip1'].$od['od_b_zip2']; ?>) <?php echo get_text($od['od_b_addr1']).' '.get_text($od['od_b_addr2']); ?></td>
                </tr>
                <tr>
                    <th scope="row">전달 메세지</th>
                    <td><?php if ($od['od_memo']) echo get_text($od['od_memo'], 1);else echo "없음";?></td>
                </tr>
                </tbody>
                </table>
            </div>
        </section>

    </div>

    <div class="btn_confirm01 btn_confirm">
        <a href="./orderlist.php?<?php echo $qstr; ?>" class="btn">목록</a>
    </div>

</section>

<script>
$(function() {
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

    $('#btn-status').click(function() {

        var f = document.forms.frmorderform;

        var check = false;
        var select = true;
        var idx = 0;
        $('.status_chk').each(
            function(){
                idx = this.value;
                if (this.checked == true) {
                    check = true;
                    if (!document.getElementById('operation_'+idx)) {
                        select = false;
                    }
                    else {
                        if (document.getElementById('operation_'+idx).value == '') {
                            select = false;
                        }
                    }
                }
            }
        );

        if (check == false) {
            alert("처리할 자료를 하나 이상 선택해 주십시오.");
            return false;
        }
        if (select == false) {
            alert("항목의 변경상태정보를 선택해 주십시오.");
            return false;
        }

        opt = 'scrollbars=yes,width=550,height=385,top=10,left=20';
        popup_window('', 'npistatuswin', opt);
        f.target = 'npistatuswin';
        f.action = './orderformnaverapiwin.php';
        f.submit();

    });
});
</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>