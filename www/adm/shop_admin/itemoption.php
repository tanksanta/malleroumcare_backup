<?php
include_once('./_common.php');

$query = "SHOW COLUMNS FROM g5_shop_item_option WHERE `Field` = 'io_sold_out';";//일시품절 없을 시 추가
$wzres = sql_fetch( $query );
if(!$wzres['Field']) {
    sql_query("ALTER TABLE `g5_shop_item_option`
	ADD `io_sold_out` tinyint(1) NULL DEFAULT '0' COMMENT '일시품절 0:사용안함,1:사용함' AFTER io_stock_manage_max_qty", true);
}

$po_run = false;

if($it['it_id']) {
    $opt_subject = explode(',', $it['it_option_subject']);
    $opt1_subject = $opt_subject[0];
    $opt2_subject = $opt_subject[1];
    $opt3_subject = $opt_subject[2];

    $sql = " select * from {$g5['g5_shop_item_option_table']} where io_type = '0' and it_id = '{$it['it_id']}' order by io_no asc ";
    $result = sql_query($sql);
    if(sql_num_rows($result))
        $po_run = true;
} else if(!empty($_POST)) {
    $opt1_subject = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1_subject'])));
    $opt2_subject = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2_subject'])));
    $opt3_subject = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3_subject'])));

    $opt1_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt1'])));
    $opt2_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt2'])));
    $opt3_val = preg_replace(G5_OPTION_ID_FILTER, '', trim(stripslashes($_POST['opt3'])));

    if(!$opt1_subject || !$opt1_val) {
        echo '옵션1과 옵션1 항목을 입력해 주십시오.';
        exit;
    }

    $po_run = true;

    $opt1_count = $opt2_count = $opt3_count = 0;

    if($opt1_val) {
        $opt1 = explode(',', $opt1_val);
        $opt1_count = count($opt1);
    }

    if($opt2_val) {
        $opt2 = explode(',', $opt2_val);
        $opt2_count = count($opt2);
    }

    if($opt3_val) {
        $opt3 = explode(',', $opt3_val);
        $opt3_count = count($opt3);
    }
}

if($po_run) {
?>

<div class="sit_option_frm_wrapper">
    <table>
    <caption>옵션 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="opt_chk_all" class="sound_only">전체 옵션</label>
            <input type="checkbox" name="opt_chk_all" value="1" id="opt_chk_all">
        </th>
        <th scope="col">옵션</th>
        <th scope="col" style="width: 300px;">추가금액(일반,파트너,사업소,우수사업소)</th>
        <th scope="col">재고수량</th>
        <th scope="col">안전재고</th>
        <th scope="col">최대재고</th>
<!--        <th scope="col">통보수량</th>-->
        <th scope="col">사용여부</th>
        <th scope="col">품목코드</th>
        <th scope="col">규격</th>
        <th scope="col">바코드 8자리 사용(보장구)</th>
		<th scope="col">일시품절사용</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if($it['it_id']) {
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $opt_id = $row['io_id'];
            $opt_val = explode(chr(30), $opt_id);
            $opt_1 = $opt_val[0];
            $opt_2 = $opt_val[1];
            $opt_3 = $opt_val[2];
            $opt_2_len = strlen($opt_2);
            $opt_3_len = strlen($opt_3);
            $opt_price = $row['io_price'];
            $opt_stock_qty = $row['io_stock_qty'];
            $opt_noti_qty = $row['io_noti_qty'];
            $opt_use = $row['io_use'];
            $opt_thezone = $row['io_thezone'];
            $opt_standard = $row['io_standard'];
            $opt_price_partner = $row['io_price_partner'];
            $opt_price_dealer = $row['io_price_dealer'];
            $opt_price_dealer2 = $row['io_price_dealer2'];
            $opt_use_short_barcode = $row['io_use_short_barcode'];
            $opt_stock_manage_min_qty = $row['io_stock_manage_min_qty'];
            $opt_stock_manage_max_qty = $row['io_stock_manage_max_qty'];
			$opt_sold_out = $row['io_sold_out'];
    ?>
    <tr>
        <td class="td_chk">
            <input type="hidden" name="opt_id[]" value="<?php echo $opt_id; ?>">
            <label for="opt_chk_<?php echo $i; ?>" class="sound_only"></label>
            <input type="checkbox" name="opt_chk[]" id="opt_chk_<?php echo $i; ?>" value="1">
        </td>
        <td class="opt-cell"><?php echo $opt_1; if ($opt_2_len) echo ' <small>&gt;</small> '.$opt_2; if ($opt_3_len) echo ' <small>&gt;</small> '.$opt_3; ?></td>
        <td class="td_numsmall">
            <label for="opt_price_<?php echo $i; ?>" class="sound_only"></label>
            <input type="text" name="opt_price[]" value="<?php echo $opt_price; ?>" id="opt_price_<?php echo $i; ?>" class="frm_input" size="9">
            <input type="text" name="opt_price_partner[]" value="<?php echo $opt_price_partner; ?>" id="opt_price_partner_<?php echo $i; ?>" class="frm_input" size="9">
            <input type="text" name="opt_price_dealer[]" value="<?php echo $opt_price_dealer; ?>" id="opt_price_dealer_<?php echo $i; ?>" class="frm_input" size="9">
            <input type="text" name="opt_price_dealer2[]" value="<?php echo $opt_price_dealer2; ?>" id="opt_price_dealer2_<?php echo $i; ?>" class="frm_input" size="9">
        </td>
        <td class="td_num">
            <label for="opt_stock_qty_<?php echo $i; ?>" class="sound_only"></label>
            <input type="text" name="opt_stock_qty[]" value="<?php echo $opt_stock_qty; ?>" id="opt_stock_qty_<?php echo $i; ?>" class="frm_input" size="5" readonly>
            <a class="btn_frmline" target="_blank" href="/adm/shop_admin/itemstocklist.php?&sel_field=it_id&search=<?php echo $it['it_id'] ?>">관리</a>
        </td>
        <td class="td_num">
          <label for="opt_stock_manage_min_qty_<?php echo $i; ?>" class="sound_only"></label>
          <input type="text" name="opt_stock_manage_min_qty[]" value="<?php echo $opt_stock_manage_min_qty; ?>" id="opt_stock_manage_min_qty_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
        <td class="td_num">
          <label for="opt_stock_manage_max_qty_<?php echo $i; ?>" class="sound_only"></label>
          <input type="text" name="opt_stock_manage_max_qty[]" value="<?php echo $opt_stock_manage_max_qty; ?>" id="opt_stock_manage_max_qty_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
<!--        <td class="td_num">-->
<!--            <label for="opt_noti_qty_--><?php //echo $i; ?><!--" class="sound_only"></label>-->
<!--            <input type="text" name="opt_noti_qty[]" value="--><?php //echo $opt_noti_qty; ?><!--" id="opt_noti_qty_--><?php //echo $i; ?><!--" class="frm_input" size="5">-->
<!--        </td>-->
        <td class="td_mng">
            <label for="opt_use_<?php echo $i; ?>" class="sound_only"></label>
            <select name="opt_use[]" id="opt_use_<?php echo $i; ?>">
                <option value="1" <?php echo get_selected('1', $opt_use); ?>>사용함</option>
                <option value="0" <?php echo get_selected('0', $opt_use); ?>>사용안함</option>
            </select>
        </td>
        <td class="td_num">
            <label for="opt_thezone_<?php echo $i; ?>" class="sound_only"></label>
            <input type="text" name="opt_thezone[]" value="<?php echo $opt_thezone; ?>" id="opt_thezone_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
        <td class="td_num">
            <label for="opt_standard_<?php echo $i; ?>" class="sound_only"></label>
            <input type="text" name="opt_standard[]" value="<?php echo $opt_standard; ?>" id="opt_standard_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
        <td class="td_num_c3">
			<!--select name="opt_use_short_barcode[]" id="opt_use_short_barcode_<?php echo $i; ?>">
                <option value="0" <?php echo get_selected('0', $opt_use_short_barcode); ?>>사용안함</option>
				<option value="1" <?php echo get_selected('1', $opt_use_short_barcode); ?>>사용함</option>                
            </select-->
            <input type="checkbox" name="opt_use_short_barcode[]" value="<?php echo $opt_id; ?>" id="opt_use_short_barcode_<?php echo $i; ?>"  <?=($opt_use_short_barcode == "1") ? "checked" : ""; ?>>
        </td>
		<td class="td_num_c3">
			<!--select name="opt_sold_out[]" id="opt_sold_out_<?php echo $i; ?>">
                <option value="0" <?php echo get_selected('0', $opt_sold_out); ?>>사용안함</option>
				<option value="1" <?php echo get_selected('1', $opt_sold_out); ?>>사용함</option>                
            </select-->
            <input type="checkbox" name="opt_sold_out[]" value="<?php echo $opt_id; ?>" id="opt_sold_out_<?php echo $i; ?>" <?=($opt_sold_out == "1") ? "checked" : ""; ?>>
        </td>
    </tr>
    <?php
        } // for
    } else {
        for($i=0; $i<$opt1_count; $i++) {
            $j = 0;
            do {
                $k = 0;
                do {
                    $opt_1 = strip_tags(trim($opt1[$i]));
                    $opt_2 = strip_tags(trim($opt2[$j]));
                    $opt_3 = strip_tags(trim($opt3[$k]));

                    $opt_2_len = strlen($opt_2);
                    $opt_3_len = strlen($opt_3);

                    $opt_id = $opt_1;
                    if($opt_2_len)
                        $opt_id .= chr(30).$opt_2;
                    if($opt_3_len)
                        $opt_id .= chr(30).$opt_3;
                    $opt_price = 0;
                    $opt_stock_qty = 9999;
                    $opt_noti_qty = 100;
                    $opt_use = 1;
                    $opt_thezone = '';
                    $opt_price_partner = 0;
                    $opt_price_dealer = 0;
                    $opt_price_dealer2 = 0;
                    $opt_use_short_barcode = 0;
					$opt_sold_out = 0;

                    // 기존에 설정된 값이 있는지 체크
                    if($_POST['w'] == 'u') {
                        $sql = " select io_price, io_stock_qty, io_noti_qty, io_use
                                    from {$g5['g5_shop_item_option_table']}
                                    where it_id = '{$_POST['it_id']}'
                                      and io_id = '$opt_id'
                                      and io_type = '0' ";
                        $row = sql_fetch($sql);

                        if($row) {
                            $opt_price = (int)$row['io_price'];
                            $opt_stock_qty = (int)$row['io_stock_qty'];
                            $opt_noti_qty = (int)$row['io_noti_qty'];
                            $opt_use = (int)$row['io_use'];
                        }
                    }
    ?>
    <tr class="new">
        <td class="td_chk">
            <input type="hidden" name="opt_id[]" value="<?php echo $opt_id; ?>">
            <label for="opt_chk_<?php echo $i; ?>" class="sound_only"></label>
            <input type="checkbox" name="opt_chk[]" id="opt_chk_<?php echo $i; ?>" value="1">
        </td>
        <td class="opt1-cell"><?php echo $opt_1; if ($opt_2_len) echo ' <small>&gt;</small> '.$opt_2; if ($opt_3_len) echo ' <small>&gt;</small> '.$opt_3; ?></td>
        <td class="td_numsmall">
            <label for="opt_price_<?php echo $i; ?>" class="sound_only"></label>
            <input type="text" name="opt_price[]" value="<?php echo $opt_price; ?>" id="opt_price_<?php echo $i; ?>" class="frm_input" size="9">
            <input type="text" name="opt_price_partner[]" value="<?php echo $opt_price_partner; ?>" id="opt_price_partner_<?php echo $i; ?>" class="frm_input" size="9">
            <input type="text" name="opt_price_dealer[]" value="<?php echo $opt_price_dealer; ?>" id="opt_price_dealer_<?php echo $i; ?>" class="frm_input" size="9">
            <input type="text" name="opt_price_dealer2[]" value="<?php echo $opt_price_dealer2; ?>" id="opt_price_dealer2_<?php echo $i; ?>" class="frm_input" size="9">
        </td>
        <td class="td_num">
            <label for="opt_stock_qty_<?php echo $i; ?>" class="sound_only"></label>
            <input type="text" name="opt_stock_qty[]" value="<?php echo $opt_stock_qty; ?>" id="opt_stock_qty_<?php echo $i; ?>" class="frm_input" size="5" readonly>
        </td>
        <td class="td_num">
          <label for="opt_stock_manage_min_qty_<?php echo $i; ?>" class="sound_only"></label>
          <input type="text" name="opt_stock_manage_min_qty[]" value="<?php echo $opt_stock_manage_min_qty; ?>" id="opt_stock_manage_min_qty_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
        <td class="td_num">
          <label for="opt_stock_manage_max_qty_<?php echo $i; ?>" class="sound_only"></label>
          <input type="text" name="opt_stock_manage_max_qty[]" value="<?php echo $opt_stock_manage_max_qty; ?>" id="opt_stock_manage_max_qty_<?php echo $i; ?>" class="frm_input" size="5">
        </td>
<!--        <td class="td_num">-->
<!--            <label for="opt_noti_qty_--><?php //echo $i; ?><!--" class="sound_only"></label>-->
<!--            <input type="text" name="opt_noti_qty[]" value="--><?php //echo $opt_noti_qty; ?><!--" id="opt_noti_qty_--><?php //echo $i; ?><!--" class="frm_input" size="5">-->
<!--        </td>-->
        <td class="td_mng">
            <label for="opt_use_<?php echo $i; ?>" class="sound_only"></label>
            <select name="opt_use[]" id="opt_use_<?php echo $i; ?>">
                <option value="1" <?php echo get_selected('1', $opt_use); ?>>사용함</option>
                <option value="0" <?php echo get_selected('0', $opt_use); ?>>사용안함</option>
            </select>
        </td>
        <td class="td_num">
            <label for="opt_thezone_<?php echo $i; ?>" class="sound_only"></label>
            <input type="text" name="opt_thezone[]" value="<?php echo $opt_thezone; ?>" id="opt_thezone_<?php echo $i; ?>" class="frm_input" size="35">
        </td>
        <td></td>
        <td class="td_num_c3">
            <!--select name="opt_use_short_barcode[]" id="opt_use_short_barcode_<?php echo $i; ?>">                
                <option value="0" <?php echo get_selected('0', $opt_use_short_barcode); ?>>사용안함</option>
				<option value="1" <?php echo get_selected('1', $opt_use_short_barcode); ?>>사용함</option>
            </select-->
			<input type="checkbox" name="opt_use_short_barcode[]" value="<?php echo $opt_id; ?>" id="opt_use_short_barcode_<?php echo $i; ?>" <?=($opt_use_short_barcode == "1") ? "checked" : ""; ?>>
        </td>
		<td class="td_num_c3">
            <!--select name="opt_sold_out[]" id="opt_sold_out_<?php echo $i; ?>">
                <option value="0" <?php echo get_selected('0', $opt_sold_out); ?>>사용안함</option>
				<option value="1" <?php echo get_selected('1', $opt_sold_out); ?>>사용함</option>
            </select-->
			<input type="checkbox" name="opt_sold_out[]" value="<?php echo $opt_id; ?>" id="opt_sold_out<?php echo $i; ?>" <?=($opt_sold_out == "1") ? "checked" : ""; ?>>
        </td>
    </tr>
    <?php
                    $k++;
                } while($k < $opt3_count);

                $j++;
            } while($j < $opt2_count);
        } // for
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_list01 btn_list">
    <input type="button" value="선택삭제" id="sel_option_delete" class="btn btn_02">
</div>

<fieldset>
    <legend>옵션 일괄 적용</legend>
    <?php echo help('전체 옵션의 추가금액, 재고/통보수량 및 사용여부를 일괄 적용할 수 있습니다. 단, 체크된 수정항목만 일괄 적용됩니다.'); ?>
    <label for="opt_com_price">추가금액</label>
    <label for="opt_com_price_chk" class="sound_only">추가금액일괄수정</label><input type="checkbox" name="opt_com_price_chk" value="1" id="opt_com_price_chk" class="opt_com_chk">
    <input type="text" name="opt_com_price" value="0" id="opt_com_price" class="frm_input" size="5">
    <label for="opt_com_stock">재고수량</label>
    <label for="opt_com_stock_chk" class="sound_only">재고수량일괄수정</label><input type="checkbox" name="opt_com_stock_chk" value="1" id="opt_com_stock_chk" class="opt_com_chk">
    <input type="text" name="opt_com_stock" value="0" id="opt_com_stock" class="frm_input" size="5">
<!--    <label for="opt_com_noti">통보수량</label>-->
<!--    <label for="opt_com_noti_chk" class="sound_only">통보수량일괄수정</label><input type="checkbox" name="opt_com_noti_chk" value="1" id="opt_com_noti_chk" class="opt_com_chk">-->
<!--    <input type="text" name="opt_com_noti" value="0" id="opt_com_noti" class="frm_input" size="5">-->
    <label for="opt_com_use">사용여부</label>
    <label for="opt_com_use_chk" class="sound_only">사용여부일괄수정</label><input type="checkbox" name="opt_com_use_chk" value="1" id="opt_com_use_chk" class="opt_com_chk">
    <select name="opt_com_use" id="opt_com_use">
        <option value="1">사용함</option>
        <option value="0">사용안함</option>
    </select>
    <button type="button" id="opt_value_apply" class="btn_frmline">일괄적용</button>
</fieldset>
<?php
}
?>
