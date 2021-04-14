<?php
//$sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "w");

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

$od['cart'] = array();
$sql = "SELECT * FROM g5_shop_cart WHERE od_id = '{$od['od_id']}'";
$cart_result = sql_query($sql);
while ( $row2 = sql_fetch_array($cart_result) ) {
    $od['cart'][] = $row2;
}


if( count($od['cart']) > 1 ) {
    $od_cart_count = ' 외 ' . (count($od['cart']) - 1) .'개';
}else{
    $od_cart_count = '';
}

$od['goodsName']		= $od['cart'][0]['it_name'] . $od_cart_count;

$html_receipt_chk = '<input type="checkbox" id="od_receipt_chk" value="'.$od['od_misu'].'" onclick="chk_receipt_price()">
<label for="od_receipt_chk">결제금액 입력</label><br>';

$title = '결제정보 수정';
include_once('./pop.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php'); // datepicker js
?>
<style>
#payment_edit {
    padding:15px 20px;
    color:#656565;
}
#payment_edit h1 {
    font-size:15px;
    color:#333333;
    padding-bottom:20px;
    border-bottom:1px solid #ddd;
    margin-bottom:20px;
}
#payment_edit .box h2 {
    font-size:13px;
    color:#656565;
    padding:0;
    margin:0;
    padding-bottom:15px;
}
#payment_edit .box .content {
    background-color:#efefef;
    padding:15px;
}
#payment_edit .box .content table {
    width:100%;
}
#payment_edit .box .content table th {
    text-align: left;
    width: 20%;
    font-weight: normal;
    font-size: 12px;
    color: #656565;
    padding-bottom:15px;
}
#payment_edit .box .content table th:before {
    content: '·';
    margin-right: 4px;
}
#payment_edit .box .content table td {
    font-size:12px;
    padding-bottom:15px;
}
#payment_edit .box .content table td select {
    width:220px;
}
#payment_edit .box .content table td label {
    cursor:pointer;
}
#payment_edit .box .content table td label + input[type="radio"] {
    margin-left:10px;
}
</style>
<form name="foption" class="form" role="form" method="post" action="./pop.order.payment.edit_result.php" onsubmit="return formcheck(this);">
    <input type="hidden" name="od_id" value="<?php echo $od['od_id']; ?>" />
    <div id="payment_edit" class="admin_popup">
        <h1>결제정보 변경</h1>
        <div class="box">
            <h2>결제</h2>
            <div class="content">
                <table>
                    <tbody>
                    <tr>
                        <th>결제수단</th>
                        <td>
                            <?php if ($od['od_settle_case'] == '네이버페이') { ?>
                                <?php echo $od['od_settle_case'];?> <input type="hidden" name="od_settle_case" value="<?php echo $od['od_settle_case'];?>" />
                            <?php } else { ?>
                                <input type="radio" name="od_settle_case" id='od_settle_case_6' value="월 마감 정산" <?php echo $od['od_settle_case'] == '월 마감 정산' ? 'checked' : ''; ?>/>
                                <label for="od_settle_case_6">월 마감 정산</label>
                                <input type="radio" name="od_settle_case" id='od_settle_case_0' value="신용카드" <?php echo $od['od_settle_case'] == '신용카드' ? 'checked' : ''; ?>/>
                                <label for="od_settle_case_0">신용카드</label>
                                <input type="radio" name="od_settle_case" id='od_settle_case_1' value="계좌이체" <?php echo $od['od_settle_case'] == '계좌이체' ? 'checked' : ''; ?>/>
                                <label for="od_settle_case_1">계좌이체</label>
                                <input type="radio" name="od_settle_case" id='od_settle_case_2' value="가상계좌" <?php echo $od['od_settle_case'] == '가상계좌' ? 'checked' : ''; ?>/>
                                <label for="od_settle_case_2">가상계좌</label>
                                <input type="radio" name="od_settle_case" id='od_settle_case_3' value="무통장" <?php echo $od['od_settle_case'] == '무통장' ? 'checked' : ''; ?>/>
                                <label for="od_settle_case_3">무통장</label>
                                <input type="radio" name="od_settle_case" id='od_settle_case_4' value="휴대폰" <?php echo $od['od_settle_case'] == '휴대폰' ? 'checked' : ''; ?>/>
                                <label for="od_settle_case_4">휴대폰</label>
                                <input type="radio" name="od_settle_case" id='od_settle_case_5' value="간편결제" <?php echo $od['od_settle_case'] == '간편결제' ? 'checked' : ''; ?>/>
                                <label for="od_settle_case_5">간편결제</label>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '계좌이체') { ########## 시작?>
                    <?php
                    if ($od['od_settle_case'] == '무통장')
                    {
                        // 은행계좌를 배열로 만든후
                        $str = explode("\n", $default['de_bank_account']);
                        $bank_account .= '<select name="od_bank_account" id="od_bank_account">'.PHP_EOL;
                        $bank_account .= '<option value="">선택하십시오</option>'.PHP_EOL;
                        for ($i=0; $i<count($str); $i++) {
                            $str[$i] = str_replace("\r", "", $str[$i]);
                            $bank_account .= '<option value="'.$str[$i].'" '.get_selected($od['od_bank_account'], $str[$i]).'>'.$str[$i].'</option>'.PHP_EOL;
                        }
                        $bank_account .= '</select> ';
                    }
                    else if ($od['od_settle_case'] == '가상계좌')
                        $bank_account = $od['od_bank_account'].'<input type="hidden" name="od_bank_account" value="'.$od['od_bank_account'].'">';
                    else if ($od['od_settle_case'] == '계좌이체')
                        $bank_account = $od['od_settle_case'];
                    ?>

                    <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌') { ?>
                    <tr>
                        <th scope="row"><label for="od_bank_account">계좌번호</label></th>
                        <td><?php echo $bank_account; ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <th scope="row"><label for="od_receipt_price"><?php echo $od['od_settle_case']; ?> 입금액</label></th>
                        <td>
                            <?php echo $html_receipt_chk; ?>
                            <input type="text" name="od_receipt_price" value="<?php echo $od['od_receipt_price']; ?>" id="od_receipt_price" class="frm_input"> 원
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="od_deposit_name">입금자명</label></th>
                        <td>
                            <?php if ($config['cf_sms_use'] && $default['de_sms_use4']) { ?>
                            <input type="checkbox" name="od_sms_ipgum_check" id="od_sms_ipgum_check">
                            <label for="od_sms_ipgum_check">SMS 입금 문자전송</label>
                            <br>
                            <?php } ?>
                            <input type="text" name="od_deposit_name" value="<?php echo get_text($od['od_deposit_name']); ?>" id="od_deposit_name" class="frm_input">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="od_receipt_time">입금일</label></th>
                        <td>
                            <input type="checkbox" name="od_bank_chk" id="od_bank_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="set_current_datetime(this)">
                            <label for="od_bank_chk">현재 시간으로 설정</label><br>
                            <input type="hidden" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time" class="frm_input" maxlength="19">
                            <div id="datetime-wrapper" class="row">
                                <div class='col-xs-3'>
                                    <input type='text' class="form-control datetimepicker" name="datetimepicker" placeholder="날짜 입력" autocomplete="off"/>
                                </div>
                                <div class='col-xs-3'>
                                    <input type='text' class="form-control datetimepicker2" name="datetimepicker2" placeholder="시간 입력" autocomplete="off"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                    
                    <?php if ($od['od_settle_case'] == '네이버페이') { ?>
                    <tr>
                        <th scope="row" class="sodr_sppay"><label for="od_receipt_bank">카드사</label></th>
                        <td>
                            <select name="od_receipt_bank" id="od_receipt_bank">
                            <option value="">없음</option>
                                <?php foreach($receipt_bank_codes as $bank) { ?>
                                    <?php if (!$bank['val']) continue; ?>
                                    <option value="<?php echo $bank['val']; ?>" <?php echo $bank['val'] == $od['od_receipt_bank'] ? 'selected' : ''; ?>><?php echo $bank['name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <?php } ?>

                    <?php if ($od['od_settle_case'] == '휴대폰') { ?>
                    <tr>
                        <th scope="row">휴대폰번호</th>
                        <td><?php echo get_text($od['od_bank_account']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="od_receipt_price"><?php echo $od['od_settle_case']; ?> 결제액</label></th>
                        <td>
                            <?php echo $html_receipt_chk; ?>
                            <input type="text" name="od_receipt_price" value="<?php echo $od['od_receipt_price']; ?>" id="od_receipt_price" class="frm_input"> 원
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="op_receipt_time">휴대폰 결제일시</label></th>
                        <td>
                            <input type="checkbox" name="od_hp_chk" id="od_hp_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="set_current_datetime(this)">
                            <label for="od_hp_chk">현재 시간으로 설정</label><br>
                            <input type="hidden" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time" class="frm_input" maxlength="19">
                            <div id="datetime-wrapper" class="row">
                                <div class='col-xs-3'>
                                    <input type='text' class="form-control datetimepicker" name="datetimepicker" placeholder="날짜 입력" autocomplete="false"/>
                                </div>
                                <div class='col-xs-3'>
                                    <input type='text' class="form-control datetimepicker2" name="datetimepicker2" placeholder="시간 입력" autocomplete="false"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>

                    <?php if ($od['od_settle_case'] == '신용카드') { ?>
                    <tr>
                        <th scope="row" class="sodr_sppay"><label for="od_receipt_price">신용카드 결제금액</label></th>
                        <td>
                            <?php echo $html_receipt_chk; ?>
                            <input type="text" name="od_receipt_price" id="od_receipt_price" value="<?php echo $od['od_receipt_price']; ?>" class="frm_input" size="10"> 원
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="sodr_sppay"><label for="od_receipt_bank">카드사</label></th>
                        <td>
                            <select name="od_receipt_bank" id="od_receipt_bank">
                                <?php foreach($receipt_bank_codes as $bank) { ?>
                                    <?php if (!$bank['val']) continue; ?>
                                    <option value="<?php echo $bank['val']; ?>" <?php echo $bank['val'] == $od['od_receipt_bank'] ? 'selected' : ''; ?>><?php echo $bank['name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="sodr_sppay"><label for="od_receipt_bank_no">승인번호</label></th>
                        <td>
                            <input type="text" name="od_receipt_bank_no" id="od_receipt_bank_no" value="<?php echo $od['od_receipt_bank_no'] ?>" class="frm_input" size="10">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="sodr_sppay"><label for="od_receipt_time">카드 승인일시</label></th>
                        <td>
                            <input type="checkbox" name="od_card_chk" id="od_card_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="set_current_datetime(this)">
                            <label for="od_card_chk">현재 시간으로 설정</label><br>
                            <input type="hidden" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time" class="frm_input" maxlength="19">
                            <div id="datetime-wrapper" class="row">
                                <div class='col-xs-3'>
                                    <input type='text' class="form-control datetimepicker" name="datetimepicker" placeholder="날짜 입력" autocomplete="false"/>
                                </div>
                                <div class='col-xs-3'>
                                    <input type='text' class="form-control datetimepicker2" name="datetimepicker2" placeholder="시간 입력" autocomplete="false"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>

                    <?php if ($od['od_settle_case'] == 'KAKAOPAY') { ?>
                    <tr>
                        <th scope="row" class="sodr_sppay"><label for="od_receipt_price">KAKAOPAY 결제금액</label></th>
                        <td>
                            <?php echo $html_receipt_chk; ?>
                            <input type="text" name="od_receipt_price" id="od_receipt_price" value="<?php echo $od['od_receipt_price']; ?>" class="frm_input" size="10"> 원
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="sodr_sppay"><label for="od_receipt_time">KAKAOPAY 승인일시</label></th>
                        <td>
                            <input type="checkbox" name="od_card_chk" id="od_card_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="set_current_datetime(this)">
                            <label for="od_card_chk">현재 시간으로 설정</label><br>
                            <input type="hidden" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time" class="frm_input" maxlength="19">
                            <div id="datetime-wrapper" class="row">
                                <div class='col-xs-3'>
                                    <input type='text' class="form-control datetimepicker" name="datetimepicker" placeholder="날짜 입력" autocomplete="false"/>
                                </div>
                                <div class='col-xs-3'>
                                    <input type='text' class="form-control datetimepicker2" name="datetimepicker2" placeholder="시간 입력" autocomplete="false"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>

                    <?php if ($od['od_settle_case'] == '간편결제' || ($od['od_pg'] == 'inicis' && is_inicis_order_pay($od['od_settle_case']) )) { ?>
                    <tr>
                        <th scope="row" class="sodr_sppay"><label for="od_receipt_price"><?php echo $s_receipt_way; ?> 결제금액</label></th>
                        <td>
                            <?php echo $html_receipt_chk; ?>
                            <input type="text" name="od_receipt_price" id="od_receipt_price" value="<?php echo $od['od_receipt_price']; ?>" class="frm_input" size="10"> 원
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="sodr_sppay"><label for="od_receipt_time"><?php echo $s_receipt_way; ?> 승인일시</label></th>
                        <td>
                            <input type="checkbox" name="od_card_chk" id="od_card_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="set_current_datetime(this)">
                            <label for="od_card_chk">현재 시간으로 설정</label><br>
                            <input type="hidden" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time" class="frm_input" maxlength="19">
                            <div id="datetime-wrapper" class="row">
                                <div class='col-xs-3'>
                                    <input type='text' class="form-control datetimepicker" name="datetimepicker" placeholder="날짜 입력" autocomplete="false"/>
                                </div>
                                <div class='col-xs-3'>
                                    <input type='text' class="form-control datetimepicker2" name="datetimepicker2" placeholder="시간 입력" autocomplete="false"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>

                    <tr>
                        <th scope="row"><label for="od_receipt_point">포인트 결제액</label></th>
                        <td><input type="text" name="od_receipt_point" value="<?php echo $od['od_receipt_point']; ?>" id="od_receipt_point" class="frm_input" size="10"> 점</td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="od_refund_price">결제취소/환불 금액</label></th>
                        <td>
                            <input type="text" name="od_refund_price" value="<?php echo $od['od_refund_price']; ?>" class="frm_input" size="10"> 원
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="od_pay_state">상태</label></th>
                        <td>
                            <input type="radio" name="od_pay_state" id='od_pay_state_0' value="0" <?php echo $od['od_pay_state'] == '0' ? 'checked' : ''; ?>/>
                            <label for="od_pay_state_0">미결제</label>
                            <input type="radio" name="od_pay_state" id='od_pay_state_1' value="1" <?php echo $od['od_pay_state'] == '1' ? 'checked' : ''; ?>/>
                            <label for="od_pay_state_1">결제</label>
                            <input type="radio" name="od_pay_state" id='od_pay_state_2' value="2" <?php echo $od['od_pay_state'] == '2' ? 'checked' : ''; ?>/>
                            <label for="od_pay_state_2">결제후 출고</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="od_pay_time_type">매출증빙일시</label></th>
                        <td>
                            <input type="radio" name="od_pay_time_type" id='od_pay_time_type_0' value="0" <?php echo $od['od_pay_time_type'] == '0' ? 'checked' : ''; ?>/>
                            <label for="od_pay_time_type_0">당일결제</label>
                            <input type="radio" name="od_pay_time_type" id='od_pay_time_type_1' value="1" <?php echo $od['od_pay_time_type'] == '1' ? 'checked' : ''; ?>/>
                            <label for="od_pay_time_type_1">월말결제</label>
                            <input type="radio" name="od_pay_time_type" id='od_pay_time_type_2' value="2" <?php echo $od['od_pay_time_type'] == '2' ? 'checked' : ''; ?>/>
                            <label for="od_pay_time_type_2">익월결제</label>
                            <input type="radio" name="od_pay_time_type" id='od_pay_time_type_3' value="3" <?php echo $od['od_pay_time_type'] == '3' ? 'checked' : ''; ?>/>
                            <label for="od_pay_time_type_3">결제일시선택</label>
                            <input type="text" name="od_receipt_time2" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time2" class="frm_input" style="margin-left:10px;min-width: 120px;">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="od_pay_memo">비고</label></th>
                        <td>
                            <input type="text" name="od_pay_memo" value="<?php echo $od['od_pay_memo']; ?>" class="frm_input" style="width:90%;">
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="popup_submit">
        <a class="cancel">
            취소
        </a>
        <input type="submit" value="확인" />
    </div>
</form>

<script>
var od_id = '<?php echo $od['od_id']; ?>';


// 현재 시간으로 배송일시 설정
function chk_invoice_time()
{
    var chk = document.getElementById("od_invoice_chk");
    var time = document.getElementById("od_invoice_time");
    time.value = chk.checked ? chk.value : time.defaultValue;
}

// 결제금액 수동 설정
function chk_receipt_price()
{
    var chk = document.getElementById("od_receipt_chk");
    var price = document.getElementById("od_receipt_price");
    price.value = chk.checked ? (parseInt(chk.value) + parseInt(price.defaultValue)) : price.defaultValue;
}

function formcheck(obj) {
    if ($('.datetimepicker').val() == '' && $('.datetimepicker2').val() != '') {
        alert("날짜를 입력해주세요");
        return false;
    }

    if ($('.datetimepicker2').val() == '' && $('.datetimepicker').val() != '') {
        alert("시간을 입력해주세요");
        return false;
    }

    if ($('.datetimepicker').val() != '' && $('.datetimepicker2').val() != '') {
        $('input[name=od_receipt_time]').val($('.datetimepicker').val() + ' ' + $('.datetimepicker2').val());
    }
}

$(document).ready(function() {

    $("#od_receipt_time2").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd 00:00:00",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        maxDate: "+365d"
    });

    $('.popup_submit .cancel').click(function () {
        try{
            window.opener.document.location.href=window.opener.document.URL;
            window.close();
        }catch(e){
            window.close();
        }
    });


    //결제수단 변경
    $('input[name="od_settle_case"]').click(function() {

        var od_settle_case = $(this).val();

        $.ajax({
            method: "POST",
            url: "./ajax.order.payment.change_payment.php",
            data: {
                od_id: od_id,
                od_settle_case: od_settle_case,
            },
        })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if ( data.result === 'success' ) {
                if (od_settle_case == '신용카드') {
                    $(opener.document).find('select[name=ot_typereceipt_cate]').val('17').prop('selected', true);
                } else {
                    $(opener.document).find('select[name=ot_typereceipt_cate]').val('31').prop('selected', true);
                }
                opener.parent.submit_typereceipt_after(false);

                location.reload();
            }
        })
    });
});

$(function () {
    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD'
    });

    $('.datetimepicker2').datetimepicker({
        format: 'HH:mm:ss'
    });

    if ($('input[name=od_receipt_time]').val() != '') {
        var date_time =  $('input[name=od_receipt_time]').val().split(' ');

        $('.datetimepicker').val(date_time[0]);
        $('.datetimepicker2').val(date_time[1]);
    }
});

function set_current_datetime(x) {
    var date_time =  x.value.split(' ');
    console.log(date_time)

    if (x.checked == true) {
        $('.datetimepicker').val(date_time[0]);
        $('.datetimepicker2').val(date_time[1]);
    } else {
        $('.datetimepicker').val($('.datetimepicker').prop("defaultValue"));
        $('.datetimepicker2').val($('.datetimepicker2').prop("defaultValue"));
    }
}
</script>
<?php
include_once('./pop.tail.php');
?>