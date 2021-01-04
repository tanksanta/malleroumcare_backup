<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php'); 

auth_check($auth[$sub_menu], 'w');

$arr_operation = array_filter($_POST['operation']);
$arr_operation = array_unique($arr_operation);
$cnt_operation = count($arr_operation);

$none_sub_field = array('PlaceProductOrder', 'ReleaseReturnHold', 'ApproveCollectedExchange', 'ReleaseExchangeHold');

$g5['title'] = '네이버페이주문 상태변경처리';
include_once(G5_PATH.'/head.sub.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>

<script src="<?php echo G5_ADMIN_URL ?>/admin.js?ver=<?php echo G5_JS_VER; ?>"></script>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <form name="frm" id="frm" action="./orderformnaverapiwinupdate.php" onsubmit="return getAction(this);" method="post">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="od_id" value="<?php echo $_POST['od_id'];?>">
    
    <div class=" new_win_con">
    <?php
    $z = 0;
    $count = count($_POST['ct_chk']);
    foreach ($_POST['ct_chk'] as $k => $v) {
        
        $operation = $_POST['operation'][$v];
        $operation_str = $NPI_OPERATIONCODE[$operation];

        $is_sub = true;
        if (in_array($operation, $none_sub_field)) {
            $is_sub = false;
        }

        $ProductOrderID = $_POST['ProductOrderID'][$v];
        $it_id = $_POST['it_id'][$v];
        $ct = sql_fetch("select a.it_name, b.it_model from {$g5['g5_shop_cart_table']} as a left join {$g5['g5_shop_item_table']} b on (a.it_id = b.it_id) where a.ProductOrderID = '".$ProductOrderID."'");
        ?>
        <input type="hidden" name="idx[]" value="<?php echo $z;?>">
        <input type="hidden" name="operation[<?php echo $z;?>]" value="<?php echo $operation;?>">
        <input type="hidden" name="ProductOrderID[<?php echo $z;?>]" value="<?php echo $ProductOrderID;?>" />
        <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption><?php echo $g5['title']; ?></caption>
        <tbody>
        <tr>
            <th scope="col">상태</th>
            <td>[<?php echo $operation_str ?>] 로 변경 처리</td>
        </tr>
        <!--
        <tr>
            <th scope="col">상품명</th>
            <td><?php echo $ct['it_name'] ?></td>
        </tr>
        -->
        <tr>
            <th scope="col">모델명</th>
            <td><?php echo $ct['it_model'] ?></td>
        </tr>
        <?php if ($ct['ct_option']) {?>
        <tr>
            <th scope="col">옵션</th>
            <td><?php echo $ct['ct_option']; ?></td>
        </tr>
        <?php } ?>

        <?php
        if ($operation == 'DelayProductOrder') { 
            echo input_DelayProductOrder($z);
        } 
        else if ($operation == 'ShipProductOrder') { 
            echo input_ShipProductOrder($z);
        } 
        else if ($operation == 'ApproveCancelApplication') { 
            echo input_ApproveCancelApplication($z);
        }
        else if ($operation == 'RequestReturn') { 
            echo input_RequestReturn($z);
        }
        else if ($operation == 'ApproveReturnApplication') { 
            echo input_ApproveReturnApplication($z);
        } 
        else if ($operation == 'RejectReturn') { 
            echo input_RejectReturn($z);
        } 
        else if ($operation == 'WithholdReturn') { 
            echo input_WithholdReturn($z);
        } 
        else if ($operation == 'ReDeliveryExchange') { 
            echo input_ReDeliveryExchange($z);
        }
        else if ($operation == 'RejectExchange') { 
            echo input_RejectExchange($z);
        }
        else if ($operation == 'WithholdExchange') { 
            echo input_WithholdExchange($z);
        }
        else if ($operation == 'CancelSale') { 
            echo input_CancelSale($z);
        }
        ?>

        <?php if ($z == 0 && $cnt_operation == 1 && $count > 1 && $is_sub) {?>
        <tr>
            <th scope="col">일괄처리</th>
            <td><label><input type="checkbox" name="all_copy" value="1" /> 현재입력정보를 모두 동일하게 적용</label></td>
        </tr>
        <?php } ?>

        </tbody>
        </table>
        </div>
        <?php
        $z++;
    }
    ?>

    </div>

    <div class="win_btn ">
        <input type="submit" class="btn_submit btn" value="상태변경 처리">
        <input type="button" class="btn_close btn" value="창닫기" onclick="window.close();">
    </div>

    </form>

</div>

<script>
function getAction(f) {
    return true;
}
$(function(){
    $('.inputdate').datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-1:c+1"});
    $(document).on("click", "input[name='all_copy']", function() {

        var is_checked = false;
        if ($(this).is(':checked')) {
            is_checked = true;
        }
        else {
            is_checked = false;
        }
   
        var arrNames = new Array();
        var elname = '';
        $('.copy').each(
            function(){
                elname = $(this).attr('name');
                elname = elname.replace(/[[^>](.*?)]/g, '');
                if ($.inArray(elname, arrNames) != -1) {

                }
                else {
                    arrNames.push(elname);
                }
            }
        );
        
        var value = nodename = '';
        arrNames.forEach(function(e) {
            $('.'+e).each(
                function(idx){
                    if (idx == 0) {
                        value = this.value;
                        nodename = this.nodeName;
                    }
                    else {
                        if (is_checked) {
                            this.value = value;
                        }
                        else {
                            this.value = '';
                        }
                    }
                }
            );
        });

    });
});
</script>


<?php
include_once(G5_PATH.'/tail.sub.php');

function input_DelayProductOrder($idx) { 

    global $NPI_DELAYREASONCODE;

    $html = '
        <tr>
            <th scope="col">발송기한</th>
            <td><input type="text" name="DispatchDueDate['.$idx.']" value="" required class="required frm_input inputdate copy DispatchDueDate" size="12" maxlength="120"></td>
        </tr>
        <tr>
            <th scope="col">발송지연사유</th>
            <td>
                <select name="DispatchDelayReasonCode['.$idx.']" class="copy DispatchDelayReasonCode" required class="required">
                    <option value="">선택</option>'.PHP_EOL;
                    foreach ($NPI_DELAYREASONCODE as $k => $v) {
                        $html .= '<option value="'.$k.'">'.$v.'</option>'.PHP_EOL;
                    }
    $html .= '  </select>
            </td>
        </tr>
    ';

    return $html;
} 

function input_ShipProductOrder($idx) { 

    global $NPI_DELIVERYMETHODCODE, $NPI_DELIVERYCOMPANYCODE;

    $html = '
        <tr>
            <th scope="col">배송방법</th>
            <td>
                <select name="DeliveryMethodCode['.$idx.']" class="copy DeliveryMethodCode" required class="required">
                    <option value="">선택</option>'.PHP_EOL;
                    foreach ($NPI_DELIVERYMETHODCODE as $k => $v) {
                        $html .= '<option value="'.$k.'">'.$v.'</option>'.PHP_EOL;
                    }
    $html .= '  </select>
            </td>
        </tr>
        <tr>
            <th scope="col">택배사</th>
            <td>
                <select name="DeliveryCompanyCode['.$idx.']" class="copy DeliveryCompanyCode">
                    <option value="">선택</option>'.PHP_EOL;
                    foreach ($NPI_DELIVERYCOMPANYCODE as $k => $v) {
                        $html .= '<option value="'.$k.'">'.$v.'</option>'.PHP_EOL;
                    }
    $html .= '  </select>
            </td>
        </tr>
        <tr>
            <th scope="col">송장번호</th>
            <td><input type="text" name="TrackingNumber['.$idx.']" value="" class="frm_input copy TrackingNumber" maxlength="120"></td>
        </tr>
        <tr>
            <th scope="col">배송일</th>
            <td>
                <input type="text" name="DispatchDate['.$idx.']" value="" required class="required frm_input inputdate copy DispatchDate" size="12" maxlength="120">
                <select name="DispatchHour['.$idx.']" id="" class="copy DispatchHour">'.PHP_EOL;
                    for ($j=0;$j<=23;$j++) { 
                        $h = sprintf('%02d', $j);
                        $html .= '<option value="'.$h.'">'.$h.'시</option>'.PHP_EOL;
                    }
    $html .= '  </select>
            </td>
        </tr>
    ';

    return $html;
} 

function input_ApproveCancelApplication($idx) { 

    $html = '
        <tr>
            <th scope="col">기타비용청구액</th>
            <td><input type="text" name="EtcFeeDemandAmount['.$idx.']" value="0" required class="required frm_input copy EtcFeeDemandAmount" maxlength="120"> 원</td>
        </tr>
        <tr>
            <th scope="col">메모</th>
            <td><input type="text" name="Memo['.$idx.']" value="" class="frm_input copy Memo" maxlength="120" style="width:100%;"></td>
        </tr>
    ';

    return $html;
} 

function input_RequestReturn($idx) { 

    global $NPI_CLAIMREASONCODE_RETURN, $NPI_DELIVERYMETHODCODE, $NPI_DELIVERYCOMPANYCODE;

    $html = '
        <tr>
            <th scope="col">반품사유</th>
            <td>
                <select name="ReturnReasonCode['.$idx.']" class="copy ReturnReasonCode" required class="required">
                    <option value="">선택</option>'.PHP_EOL;
                    foreach ($NPI_CLAIMREASONCODE_RETURN as $k => $v) {
                        $html .= '<option value="'.$k.'">'.$v.'</option>'.PHP_EOL;
                    }
    $html .= '  </select>
            </td>
        </tr>
        <tr>
            <th scope="col">수거배송방법</th>
            <td>
                <div style="margin:4px 0;">네이버페이를 통한 자동수거지시가 필요하신경우에는 API사용이 아닌, 네이버페이 판매자센터를 통해서 처리바랍니다.</div>
                직접반송 <input type="hidden" name="CollectDeliveryMethodCode['.$idx.']" value="RETURN_INDIVIDUAL" />
            </td>
        </tr>
        <tr>
            <th scope="col">수거택배사</th>
            <td>
                <select name="CollectDeliveryCompanyCode['.$idx.']" class="copy CollectDeliveryCompanyCode" class="">
                    <option value="">선택</option>'.PHP_EOL;
                    foreach ($NPI_DELIVERYCOMPANYCODE as $k => $v) {
                        $html .= '<option value="'.$k.'">'.$v.'</option>'.PHP_EOL;
                    }
    $html .= '  </select>
            </td>
        </tr>
        <tr>
            <th scope="col">수거송장번호</th>
            <td><input type="text" name="CollectTrackingNumber['.$idx.']" value="" class="frm_input copy CollectTrackingNumber" maxlength="120"></td>
        </tr>
    ';

    return $html;
} 

function input_ApproveReturnApplication($idx) { 

    $html = '
        <tr>
            <th scope="col">기타비용청구액</th>
            <td><input type="text" name="EtcFeeDemandAmount['.$idx.']" value="0" required class="required frm_input copy EtcFeeDemandAmount" maxlength="120"> 원</td>
        </tr>
        <tr>
            <th scope="col">구매자에게 전달하는 메모</th>
            <td><input type="text" name="Memo['.$idx.']" value="" class="frm_input copy Memo" maxlength="120" style="width:100%;"></td>
        </tr>
    ';

    return $html;
} 

function input_RejectReturn($idx) { 

    $html = '
        <tr>
            <th scope="col">구매자에게 전달하는 메모</th>
            <td><input type="text" name="RejectDetailContent['.$idx.']" value="" class="frm_input copy RejectDetailContent" maxlength="120" style="width:100%;"></td>
        </tr>
    ';

    return $html;
} 

function input_WithholdReturn($idx) { 

    global $NPI_EXCHANGEHOLDREASONCODE;

    $html = '
        <tr>
            <th scope="col">반품보류사유</th>
            <td>
                <select name="ReturnHoldCode['.$idx.']" class="copy ReturnHoldCode" required class="required">
                    <option value="">선택</option>'.PHP_EOL;
                    foreach ($NPI_EXCHANGEHOLDREASONCODE as $k => $v) {
                        $html .= '<option value="'.$k.'">'.$v.'</option>'.PHP_EOL;
                    }
    $html .= '  </select>
            </td>
        </tr>
        <tr>
            <th scope="col">반품보류상세사유</th>
            <td><input type="text" name="ReturnHoldDetailContent['.$idx.']" value="" required class="required frm_input copy ReturnHoldDetailContent" maxlength="120" style="width:90%;"></td>
        </tr>
        <tr>
            <th scope="col">기타반품비용</th>
            <td>
                반품비용은 10원이상 입력해주세요.<br />
                <input type="text" name="EtcFeeDemandAmount['.$idx.']" value="" class="frm_input copy EtcFeeDemandAmount" maxlength="120"> 원
            </td>
        </tr>
    ';

    return $html;
} 

function input_ReDeliveryExchange($idx) { 

    global $NPI_DELIVERYMETHODCODE, $NPI_DELIVERYCOMPANYCODE;

    $html = '
        <tr>
            <th scope="col">배송방법</th>
            <td>
                <select name="ReDeliveryMethodCode['.$idx.']" class="copy ReDeliveryMethodCode" required class="required">
                    <option value="">선택</option>'.PHP_EOL;
                    foreach ($NPI_DELIVERYMETHODCODE as $k => $v) {
                        $html .= '<option value="'.$k.'">'.$v.'</option>'.PHP_EOL;
                    }
    $html .= '  </select>
            </td>
        </tr>
        <tr>
            <th scope="col">택배사</th>
            <td>
                <select name="ReDeliveryCompanyCode['.$idx.']" class="copy ReDeliveryCompanyCode">
                    <option value="">선택</option>'.PHP_EOL;
                    foreach ($NPI_DELIVERYCOMPANYCODE as $k => $v) {
                        $html .= '<option value="'.$k.'">'.$v.'</option>'.PHP_EOL;
                    }
    $html .= '  </select>
            </td>
        </tr>
        <tr>
            <th scope="col">송장번호</th>
            <td><input type="text" name="ReDeliveryTrackingNumber['.$idx.']" value="" class="frm_input copy ReDeliveryTrackingNumber" maxlength="120"></td>
        </tr>
    ';

    return $html;
}

function input_RejectExchange($idx) { 

    $html = '
        <tr>
            <th scope="col">교환거부사유</th>
            <td><input type="text" name="RejectDetailContent['.$idx.']" value="" required class="required frm_input copy RejectDetailContent" maxlength="120" style="width:90%;"></td>
        </tr>
    ';

    return $html;
}

function input_WithholdExchange($idx) { 

    global $NPI_EXCHANGEHOLDREASONCODE;

    $html = '
        <tr>
            <th scope="col">교환보류사유</th>
            <td>
                <select name="ExchangeHoldCode['.$idx.']" class="copy ExchangeHoldCode" required class="required">
                    <option value="">선택</option>'.PHP_EOL;
                    foreach ($NPI_EXCHANGEHOLDREASONCODE as $k => $v) {
                        $html .= '<option value="'.$k.'">'.$v.'</option>'.PHP_EOL;
                    }
    $html .= '  </select>
            </td>
        </tr>
        <tr>
            <th scope="col">교환보류상세사유</th>
            <td><input type="text" name="ExchangeHoldDetailContent['.$idx.']" value="" required class="required frm_input copy ExchangeHoldDetailContent" maxlength="120" style="width:90%;"></td>
        </tr>
        <tr>
            <th scope="col">기타교환비용</th>
            <td>
                교환비용은 10원이상 입력해주세요.<br />
                <input type="text" name="EtcFeeDemandAmount['.$idx.']" value="" class="frm_input copy EtcFeeDemandAmount" maxlength="120"> 원
            </td>
        </tr>
    ';

    return $html;
}

function input_CancelSale($idx) { 

    global $NPI_CLAIMREASONCODE_CANCEL;

    $html = '
        <tr>
            <th scope="col">판매취소사유</th>
            <td>
                <select name="CancelReasonCode['.$idx.']" class="copy CancelReasonCode" required class="required">
                    <option value="">선택</option>'.PHP_EOL;
                    foreach ($NPI_CLAIMREASONCODE_CANCEL as $k => $v) {
                        $html .= '<option value="'.$k.'">'.$v.'</option>'.PHP_EOL;
                    }
    $html .= '  </select>
            </td>
        </tr>
    ';

    return $html;
    
} 
?>
