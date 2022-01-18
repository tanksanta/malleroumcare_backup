<?php
include_once('./_common.php');

$ps_run = false;

if($it['it_id']) {
    $sql = " select * from {$g5['g5_shop_item_option_table']} where io_type = '1' and it_id = '{$it['it_id']}' order by io_no asc ";
    $result = sql_query($sql);
    if(sql_num_rows($result))
        $ps_run = true;
} else if(!empty($_POST)) {
    $subject_count = count($_POST['subject']);
    $supply_count = count($_POST['supply']);

    if(!$subject_count || !$supply_count) {
        echo '추가옵션명과 추가옵션항목을 입력해 주십시오.';
        exit;
    }

    $ps_run = true;
}

if($ps_run) {
?>
<div class="sit_option_frm_wrapper">
    <table>
    <caption>추가옵션 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="spl_chk_all" class="sound_only">전체 추가옵션</label>
            <input type="checkbox" name="spl_chk_all" value="1">
        </th>
        <th scope="col">옵션명</th>
        <th scope="col">옵션항목</th>
        <th scope="col" style="width: 300px;">상품금액(일반,파트너,사업소,우수사업소)</th>
        <th scope="col">재고</th>
<!--        <th scope="col">통보수량</th>-->
        <th scope="col">사용여부</th>
        <th scope="col">품목코드</th>
        <th scope="col">규격</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if($it['it_id']) {
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $spl_id = $row['io_id'];
            $spl_val = explode(chr(30), $spl_id);
            $spl_subject = $spl_val[0];
            $spl = $spl_val[1];
            $spl_price = $row['io_price'];
            $spl_price_partner = $row['io_price_partner'];
            $spl_price_dealer = $row['io_price_dealer'];
            $spl_price_dealer2 = $row['io_price_dealer2'];
            $spl_stock_qty = $row['io_stock_qty'];
            $spl_noti_qty = $row['io_noti_qty'];
            $spl_use = $row['io_use'];
            $spl_thezone = $row['io_thezone'];
            $spl_standard = $row['io_standard'];
    ?>
    <tr>
        <td class="td_chk">
            <input type="hidden" name="spl_id[]" value="<?php echo $spl_id; ?>">
            <label for="spl_chk_<?php echo $i; ?>" class="sound_only"><?php echo $spl_subject.' '.$spl; ?></label>
            <input type="checkbox" name="spl_chk[]" id="spl_chk_<?php echo $i; ?>" value="1">
        </td>
        <td class="spl-subject-cell"><?php echo $spl_subject; ?></td>
        <td class="spl-cell"><?php echo $spl; ?></td>
        <td class="td_numsmall">
            <label for="spl_price_<?php echo $i; ?>" class="sound_only">상품금액</label>
            <input type="text" name="spl_price[]" value="<?php echo $spl_price; ?>" id="spl_price_<?php echo $i; ?>" class="frm_input" size="5">
            <input type="text" name="spl_price_partner[]" value="<?php echo $spl_price_partner; ?>" id="spl_price_partner_<?php echo $i; ?>" class="frm_input" size="5">
            <input type="text" name="spl_price_dealer[]" value="<?php echo $spl_price_dealer; ?>" id="spl_price_dealer_<?php echo $i; ?>" class="frm_input" size="5">
            <input type="text" name="spl_price_dealer2[]" value="<?php echo $spl_price_dealer2; ?>" id="spl_price_dealer2_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
        <td class="td_num">
            <label for="spl_stock_qty_<?php echo $i; ?>" class="sound_only">재고수량</label>
            <input type="text" name="spl_stock_qty[]" value="<?php echo $spl_stock_qty; ?>" id="spl_stock_qty_<?php echo $i; ?>" class="frm_input" size="5">
            <a class="btn_frmline" target="_blank" href="/adm/shop_admin/optionstocklist.php?sel_field=a.it_id&search=<?php echo $it['it_id'] ?>">관리</a>
        </td>
<!--        <td class="td_num">-->
<!--            <label for="spl_noti_qty_--><?php //echo $i; ?><!--" class="sound_only">통보수량</label>-->
<!--            <input type="text" name="spl_noti_qty[]" value="--><?php //echo $spl_noti_qty; ?><!--" id="spl_noti_qty_--><?php //echo $i; ?><!--" class="frm_input" size="5">-->
<!--        </td>-->
        <td class="td_mng">
            <label for="spl_use_<?php echo $i; ?>" class="sound_only">사용여부</label>
            <select name="spl_use[]" id="spl_use_<?php echo $i; ?>">
                <option value="1" <?php echo get_selected('1', $spl_use); ?>>사용함</option>
                <option value="0" <?php echo get_selected('0', $spl_use); ?>>사용안함</option>
            </select>
        </td>
        <td class="td_num">
            <label for="spl_thezone_<?php echo $i; ?>" class="sound_only">더존코드</label>
            <input type="text" name="spl_thezone[]" value="<?php echo $spl_thezone; ?>" id="spl_thezone_<?php echo $i; ?>" class="frm_input" size="35">
        </td>
        <td class="td_num">
            <label for="spl_standard_<?php echo $i; ?>" class="sound_only">규격</label>
            <input type="text" name="spl_standard[]" value="<?php echo $spl_standard; ?>" id="spl_standard_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
    </tr>
    <?php
        } // for
    } else {
        for($i=0; $i<$subject_count; $i++) {
            $spl_subject = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['subject'][$i])));
            $spl_val = explode(',', preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['supply'][$i]))));
            $spl_count = count($spl_val);

            for($j=0; $j<$spl_count; $j++) {
                $spl = strip_tags(trim($spl_val[$j]));
                if($spl_subject && strlen($spl)) {
                    $spl_id = $spl_subject.chr(30).$spl;
                    $spl_price = 0;
                    $spl_price_partner = 0;
                    $spl_price_dealer = 0;
                    $spl_price_dealer2 = 0;
                    $spl_stock_qty = 9999;
                    $spl_noti_qty = 100;
                    $spl_use = 1;
                    $spl_thezone = '';
                    $spl_standard = '';

                    // 기존에 설정된 값이 있는지 체크
                    if($_POST['w'] == 'u') {
                        $sql = " select io_price, io_stock_qty, io_noti_qty, io_use
                                    from {$g5['g5_shop_item_option_table']}
                                    where it_id = '{$_POST['it_id']}'
                                      and io_id = '$spl_id'
                                      and io_type = '1' ";
                        $row = sql_fetch($sql);

                        if($row) {
                            $spl_price = (int)$row['io_price'];
                            $spl_stock_qty = (int)$row['io_stock_qty'];
                            $spl_noti_qty = (int)$row['io_noti_qty'];
                            $spl_use = (int)$row['io_use'];
                        }
                    }
    ?>
    <tr class="new">
        <td class="td_chk">
            <input type="hidden" name="spl_id[]" value="<?php echo $spl_id; ?>">
            <label for="spl_chk_<?php echo $i; ?>" class="sound_only"><?php echo $spl_subject.' '.$spl; ?></label>
            <input type="checkbox" name="spl_chk[]" id="spl_chk_<?php echo $i; ?>" value="1">
        </td>
        <td class="spl-subject-cell"><?php echo $spl_subject; ?></td>
        <td class="spl-cell"><?php echo $spl; ?></td>
        <td class="td_numsmall">
            <label for="spl_price_<?php echo $i; ?>" class="sound_only">상품금액</label>
            <input type="text" name="spl_price[]" value="<?php echo $spl_price; ?>" id="spl_price_<?php echo $i; ?>" class="frm_input" size="9" placeholder="일반몰">
            <input type="text" name="spl_price_partner[]" value="<?php echo $spl_price_partner; ?>" id="spl_price_partner_<?php echo $i; ?>" class="frm_input" size="9" placeholder="파트너몰">
            <input type="text" name="spl_price_dealer[]" value="<?php echo $spl_price_dealer; ?>" id="spl_price_dealer_<?php echo $i; ?>" class="frm_input" size="9" placeholder="딜러1">
            <input type="text" name="spl_price_dealer2[]" value="<?php echo $spl_price_dealer2; ?>" id="spl_price_dealer2_<?php echo $i; ?>" class="frm_input" size="9" placeholder="딜러2">
        </td>
        <td class="td_num">
            <label for="spl_stock_qty_<?php echo $i; ?>" class="sound_only">재고수량</label>
            <input type="text" name="spl_stock_qty[]" value="<?php echo $spl_stock_qty; ?>" id="spl_stock_qty_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
<!--        <td class="td_num">-->
<!--            <label for="spl_noti_qty_--><?php //echo $i; ?><!--" class="sound_only">통보수량</label>-->
<!--            <input type="text" name="spl_noti_qty[]" value="--><?php //echo $spl_noti_qty; ?><!--" id="spl_noti_qty_--><?php //echo $i; ?><!--" class="frm_input" size="5">-->
<!--        </td>-->
        <td class="td_mng">
            <label for="spl_use_<?php echo $i; ?>" class="sound_only">사용여부</label>
            <select name="spl_use[]" id="spl_use_<?php echo $i; ?>">
                <option value="1" <?php echo get_selected('1', $spl_use); ?>>사용함</option>
                <option value="0" <?php echo get_selected('0', $spl_use); ?>>사용안함</option>
            </select>
        </td>
        <td class="td_num">
            <label for="spl_thezone_<?php echo $i; ?>" class="sound_only">더존코드</label>
            <input type="text" name="spl_thezone[]" value="<?php echo $spl_thezone; ?>" id="spl_thezone_<?php echo $i; ?>" class="frm_input" size="35">
        </td>
        <td class="td_num">
            <label for="spl_standard_<?php echo $i; ?>" class="sound_only">규격</label>
            <input type="text" name="spl_standard[]" value="<?php echo $spl_standard; ?>" id="spl_standard_<?php echo $i; ?>" class="frm_input" size="35">
        </td>
    </tr>
    <?php
                } // if
            } // for
        } // for
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_list01 btn_list">
    <button type="button" id="sel_supply_delete" class="btn btn_02">선택삭제</button>
</div>

<fieldset>
    <?php echo help('전체 추가 옵션의 상품금액, 재고/통보수량 및 사용여부를 일괄 적용할 수 있습니다.  단, 체크된 수정항목만 일괄 적용됩니다.'); ?>
    <label for="spl_com_price">상품금액</label>
    <label for="spl_com_price_chk" class="sound_only">상품금액일괄수정</label><input type="checkbox" name="spl_com_price_chk" value="1" id="spl_com_price_chk" class="spl_com_chk">
    <input type="text" name="spl_com_price" value="0" id="spl_com_price" class="frm_input" size="9">
    <label for="spl_com_stock">재고수량</label>
    <label for="spl_com_stock_chk" class="sound_only">재고수량일괄수정</label><input type="checkbox" name="spl_com_stock_chk" value="1" id="spl_com_stock_chk" class="spl_com_chk">
    <input type="text" name="spl_com_stock" value="0" id="spl_com_stock" class="frm_input" size="5">
<!--    <label for="spl_com_noti">통보수량</label>-->
<!--    <label for="spl_com_noti_chk" class="sound_only">통보수량일괄수정</label><input type="checkbox" name="spl_com_noti_chk" value="1" id="spl_com_noti_chk" class="spl_com_chk">-->
<!--    <input type="text" name="spl_com_noti" value="0" id="spl_com_noti" class="frm_input" size="5">-->
    <label for="spl_com_use">사용여부</label>
    <label for="spl_com_use_chk" class="sound_only">사용여부일괄수정</label><input type="checkbox" name="spl_com_use_chk" value="1" id="spl_com_use_chk" class="spl_com_chk">
    <select name="spl_com_use" id="spl_com_use">
        <option value="1">사용함</option>
        <option value="0">사용안함</option>
    </select>
    <button type="button" id="spl_value_apply" class="btn_frmline">일괄적용</button>
</fieldset>
<?php
}
?>