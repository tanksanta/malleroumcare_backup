<?php
// $sub_menu = '400400';
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

if(!$uid) {
    $uid = uuidv4();
}

// 상품정보
$sql  = " select * from {$g5['g5_shop_item_table']} where it_id = '{$it_id}' ";
$it = sql_fetch($sql);

// 주문제작
// $cs = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$od_id}' AND it_id = '{$it_id}' ");
$cs = sql_fetch(" select * from g5_shop_order_custom where od_id = '{$od_id}' AND odc_uid = '{$uid}' ");

$option_1 = samhwa_get_item_options($it['it_id'], $it['it_option_subject']);
$option_2 = samhwa_get_item_supply($it['it_id'], $it['it_supply_subject']);

$io = array();
$option = array();

$option['it_id'] = $it['it_id'];
$option['ct_price'] = $row2['ct_price'];
$option['ct_send_cost'] = $row2['ct_send_cost'];

$sql = "SELECT count(*) as cnt FROM `g5_shop_item_option` WHERE it_id = '{$it_id}' AND io_type = '0' ";
$option_cnt = sql_fetch($sql);
if ( !$option_cnt['cnt'] ) {

    $row = array(
        0 => $it
    );

    for($i=0; $i<count($row); $i++) {


        $it_stock_qty = get_it_stock_qty($row[$i]['it_id']);

        if($row['it_price'] < 0)
            $io_price = '('.number_format($row[$i]['it_price']).'원)';
        else
            $io_price = '(+'.number_format($row[$i]['it_price']).'원)';

        $cls = 'opt';

        $io[$i] = $row;
        $io[$i]['ct_qty'] = 1;
        $io[$i]['cls'] = $cls;
        $io[$i]['it_stock_qty'] = $it_stock_qty;
        $io[$i]['io_price'] = $row[$i]['it_price'];
        $io[$i]['io_price_partner'] = $row[$i]['it_price_partner'] ? $row[$i]['it_price_partner'] : $row[$i]['it_price'];
        $io[$i]['io_price_dealer'] = $row[$i]['it_price_dealer'] ? $row[$i]['it_price_dealer'] : $row[$i]['it_price'];
        $io[$i]['io_price_dealer2'] = $row[$i]['it_price_dealer2'] ? $row[$i]['it_price_dealer2'] : $row[$i]['it_price'];
        $io[$i]['io_display_price'] = $io_price;
        $io[$i]['pt_msg1'] = $row['pt_msg1'];
        $io[$i]['pt_msg2'] = $row['pt_msg2'];
        $io[$i]['pt_msg3'] = $row['pt_msg3'];
    }
}

$ct_discount = 0;
$dealer_price = false;
$dealer2_price = false;
if ( $w ) {

    $sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' and ct_uid = '$uid' order by io_type asc, ct_id asc ";
    // $sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' and it_id = '$it_id' order by io_type asc, ct_id asc ";
    $result = sql_query($sql);


    $io = array();
    $option = array();

    $option['it_id'] = $it['it_id'];
    $option['ct_price'] = $row2['ct_price'];
    $option['ct_send_cost'] = $row2['ct_send_cost'];

    for($i=0; $row=sql_fetch_array($result); $i++) {
        if(!$row['io_id'])
            $it_stock_qty = get_it_stock_qty($row['it_id']);
        else
            $it_stock_qty = get_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);

        if($row['io_price'] < 0)
            $io_price = '('.number_format($row['io_price']).'원)';
        else
            $io_price = '(+'.number_format($row['io_price']).'원)';

        $cls = 'opt';
        if($row['io_type'])
            $cls = 'spl';

        $io[$i] = $row;
        $io[$i]['cls'] = $cls;
        $io[$i]['it_stock_qty'] = $it_stock_qty;
        $io[$i]['io_price'] = $row['io_price'];
        $io[$i]['io_display_price'] = $io_price;
        $io[$i]['pt_msg1'] = $row['pt_msg1'];
        $io[$i]['pt_msg2'] = $row['pt_msg2'];
        $io[$i]['pt_msg3'] = $row['pt_msg3'];
        $io[$i]['ct_uid'] = $row['ct_uid'];

        if ( $row['ct_discount'] ) {
            $ct_discount = $row['ct_discount'];
        }
        if ($it['it_price_dealer'] && $it['it_price_dealer'] == $row['ct_price']) {
            $dealer_price = true;
        }
        if ($it['it_price_dealer2'] && $it['it_price_dealer2'] == $row['ct_price']) {
            $dealer2_price = true;
        }

        $ct_price_type = $row['ct_price_type'];
    
        $custom_item_price = $row['ct_price'];
    }
}

$title = $w ? '상품 수정 > 옵션선택' : '상품 추가 > 옵션선택';
?>
<style>
#pop_add_item .content {
    position:relative;
    width:100%;
    box-sizing:border-box;
    margin:0 !important;
    padding:0 20px;
}
#pop_add_item .content .item_options {
    float:left;
    width:30%;
    box-sizing:border-box;
    padding-right:10px;
    border-right:1px solid #ddd;
    min-height:calc(100% - 130px);
}
#pop_add_item .content #custom_order {
    width:68%;
    float:right;
    box-sizing:border-box;
    padding-right:20px;
}
#pop_add_item .content:after {
    clear:both;
    display:block;
    content: '';
}
#pop_add_item .content .item_options .item_info a {
    display:block;
}
#pop_add_item .content .item_options .item_info a img {
    vertical-align:middle;
    display:inline-block;
}
#pop_add_item .content .item_options .item_info a p {
    vertical-align:middle;
    display:inline-block;
    margin-left:15px;
    color:#666;
    font-weight:bold;
}
#pop_add_item .content .item_options .item_info a p span.model {
    color:#08a2cd;
}
#pop_add_item .content .addoptionbuttons {
    clear:both;
    position:fixed;
    left:0;
    bottom:0;
    width:100%;
    background-color:#333333;
    height:50px;
    font-size:15px;
    font-weight:bold;
}
#pop_add_item .content .addoptionbuttons input[type="submit"] {
    display:block;
    width:80%;
    float:right;
    height:100%;
    border:0;
    background:transparent;
    font-size:16px;
    font-weight:bold;
    color:white;
    cursor:pointer;
}
#pop_add_item .content .addoptionbuttons a {
    display:block;
    float:left;
    width:20%;
    color:white;
    height:100%;
    background-color:#a5a5a5;
    text-align:left;
    line-height:50px;
    padding-left:25px;
    box-sizing:border-box;
}
#pop_add_item .content .addoptionbuttons a img {
    vertical-align:middle;
    margin-top: -3px;
}
#pop_add_item .content .addoptionbuttons:after {
    clear:both;
    display:block;
    content: '';
}
#pop_add_item .content .list-group {
    list-style:none;
    margin:0;
    padding:0;    
}
#pop_add_item .content .list-group-item {
    margin:0;
    padding:0;
}
#pop_add_item .content .list-group-item.it_opt_list {
    margin-bottom:5px;
}
#pop_add_item .content .list-group-item .row .col-sm-5 {
    width:100%;
    padding:5px 0;
    position:relative;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group {
    border: 1px solid #dce0e3;
    display: block;
    width: 112px;
    background: #f0f2f4 !important;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group input {
    border:0;
    display:block;
    float:left;
    width:50px;
    text-align: center;
    font-weight:bold;
    background-color:white;
    min-width:50px;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group input[type="text"] {
    background: white !important;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group .it_qty_plus,
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group .it_qty_minus {
    background:#f0f2f4;
    border:0;
    width: 30px;
    height: 30px;
    position:relative;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group .it_qty_plus:before {
    content: '';
    border-top: 0;
    width: 1px;
    height: 11px;
    display: block;
    background-color: #555555;
    position: absolute;
    top: 9px;
    left: 15px;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group .it_qty_plus:after {
    content: '';
    border-top: 0;
    width: 11px;
    height: 1px;
    display: block;
    background-color: #555555;
    position: absolute;
    top: 14px;
    left: 10px;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group .it_qty_minus:after {
    content: '';
    border-top: 0;
    width: 11px;
    height: 1px;
    display: block;
    background-color: #555555;
    position: absolute;
    top: 14px;
    left: 9px;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group-btn-del i,
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group .it_qty_plus i,
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group .it_qty_minus i {
    display:none;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group .it_qty_minus:hover,
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group .it_qty_minus:hover {
    background:#f0f2f4;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group .input-group-btn {
    display:block;
    float: left;
    width: 30px;
    cursor:pointer;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group .input-group-btn button {
    cursor:pointer;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group:after {
    clear:both;
    display:block;
    content: '';
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group-btn-del {
    position: absolute;
    right: 0;
    top:5px;
    cursor:pointer;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group-btn-del .it_opt_del {
    border:0;
    background:#fff;
    cursor:pointer;
}
#pop_add_item .content .list-group-item .row .col-sm-5 .input-group-btn-del .it_opt_del:after {
    width:30px;
    height:30px;
    background: url('/adm/shop_admin/img/btn_close.png');
    cursor:pointer;
    content: '';
    display:block;
}
#pop_add_item .content .list-group-item .row .col-sm-7 .it_opt_prc {
    position: absolute;
    right: 38px;
    bottom: -28px;
    font-size: 18px;
    font-weight: bold;
    color: #333;
}
#pop_add_item .content .opt-tbl {
    width:100%;
}
#pop_add_item .content .opt-tbl th {
    display:none;
}
#pop_add_item .content .it_option,
#pop_add_item .content .it_supply {
    width:100%;
}
#pop_add_item .content .option_title {
    color:#666;
}
#pop_add_item #it_sel_option {
    margin-top:10px;
}

#pop_add_item .itm-option-group > .option-price-wrapper,
#pop_add_item .itm-option-group > .input-group {
    display: inline-block !important;
    vertical-align: top;
}

#pop_add_item .itm-option-group > .option-price-wrapper {
    width: 100px;
    margin-right: 5px;
}

#pop_add_item .itm-option-group > .option-price-wrapper input {
    height: 32px;
    min-width: unset;
    width: 100%;
}

#pop_add_item .itm-option-group > .option-price-wrapper input:read-only {
    background-color: #f1f1f1 !important;
    color: #a0a0a0;
}
</style>
<html>
<head>
<title><?php echo $title; ?></title>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css?v=<?php echo time(); ?>">
<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-ui.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-migrate-1.2.1.min.js"></script>
<!--<script src="<?php echo G5_URL;?>/skin/apms/order/basic/shop.js"></script>-->
<script src="<?php echo G5_JS_URL;?>/common.js"></script>
<script src="<?php echo G5_ADMIN_URL;?>/shop_admin/js/shop.js?v=<?php echo time(); ?>"></script>
</head>
<style>
</style>
<div id="pop_add_item" class="admin_popup">
    <div class="header">
        <ul class="add_item_header">
            <li class="">상품선택</li>
            <li class="arrow">
                <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/icon_arrow_next.png" />
            </li>
            <li class="on">옵션선택</li>
        </ul>
    </div>
    <div class="content">
        <form name="foption" class="form" role="form" method="post" action="./pop.order.item.add.option_result.php" onsubmit="return formcheck(this);">
            <div class="item_options">
                <div class="item_info">
                    <a href="./itemform.php?w=u&it_id=<?php echo $it['it_id']; ?>" target="_blank">
                        <?php echo get_it_image($it['it_id'], 50, 50); ?>
                        <p>
                            <?php echo htmlspecialchars2(cut_str($it['it_name'],250, "")); ?>
                            <br/>
                            <span class="model"><?php echo $it['it_model']; ?></span>
                            <br/>
                            <span id="it_price_wrapper">
                                <?php echo number_format($it['it_price']); ?>원
                            </span>
                        </p>
                    </a>
                    <div id="custom_it_price_wrapper" style="display: none;">
                        <?php if (empty($custom_item_price)) $custom_item_price = 0; ?>
                        <input type="text" id="custom_item_price_input" data-price-num="<?php echo $custom_item_price; ?>" value="<?php echo number_format($custom_item_price); ?>" onkeyup="_editItemPrice(this)">원
                    </div>
                </div>
                <div id="mod_option_form">
                        <input type="hidden" name="act" value="adminadd">
                        <input type="hidden" name="w" value="<?php echo $w; ?>">
                        <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
                        <input type="hidden" name="it_id[]" value="<?php echo $it['it_id']; ?>">
                        <input type="hidden" name="uid" value="<?php echo htmlspecialchars($uid); ?>">
                        <input type="hidden" name="it_msg1[]" value="<?php echo $it['pt_msg1']; ?>">
                        <input type="hidden" name="it_msg2[]" value="<?php echo $it['pt_msg2']; ?>">
                        <input type="hidden" name="it_msg3[]" value="<?php echo $it['pt_msg3']; ?>">
                        <input type="hidden" name="it_price_custom" id="it_price" value="<?php echo $it['it_price'] ? $it['it_price'] : 0; ?>">
                        <input type="hidden" id="it_price_origin" value="<?php echo $it['it_price']; ?>">
                        <input type="hidden" id="it_price_partner" value="<?php echo $it['it_price_partner'] ? $it['it_price_partner'] : $it['it_price']; ?>">
                        <input type="hidden" id="it_price_dealer" value="<?php echo $it['it_price_dealer'] ? $it['it_price_dealer'] : $it['it_price']; ?>">
                        <input type="hidden" id="it_price_dealer2" value="<?php echo $it['it_price_dealer2'] ? $it['it_price_dealer2'] : $it['it_price']; ?>">
                        <!--<input type="hidden" name="ct_send_cost" value="<?php echo $option['ct_send_cost']; ?>">-->
                        <input type="hidden" name="sw_direct">
                        <?php if($option_1) { ?>
                            <p class="option_title"><b>선택옵션</b></p>
                            <table class="opt-tbl">
                            <tbody>
                            <?php echo $option_1; // 선택옵션 ?>
                            </tbody>
                            </table>
                        <?php } ?>

                        <?php if($option_2) { ?>
                            <p class="option_title"><b>추가옵션</b></p>
                            <table class="opt-tbl">
                            <tbody>
                            <?php echo $option_2; // 추가옵션 ?>
                            </tbody>
                            </table>
                        <?php } ?>

                        <div id="it_sel_option">
                            <ul id="it_opt_added" class="list-group">
                                <?php for($i=0; $i < count($io); $i++) { ?>
                                <?php
                                $sql = "select * from {$g5['g5_shop_item_option_table']} where it_id = '{$it['it_id']}' and io_id= '{$io[$i]['io_id']}'";
                                $item_option = sql_fetch($sql);
                                
                                if (empty($w) && empty($io[$i]['io_type'])) $io[$i]['io_type'] = '0';
                                    ?>
                                    <li class="it_<?php echo $io[$i]['cls']; ?>_list list-group-item">
                                        <input type="hidden" name="io_type[<?php echo $it['it_id']; ?>][]" value="<?php echo $io[$i]['io_type']; ?>">
                                        <input type="hidden" name="io_id[<?php echo $it['it_id']; ?>][]" value="<?php echo $io[$i]['io_id']; ?>">
                                        <input type="hidden" name="io_value[<?php echo $it['it_id']; ?>][]" value="<?php echo $io[$i]['ct_option']; ?>">
                                        <input type="hidden" class="io_price" name="io_price[<?php echo $it['it_id']; ?>][]" value="<?php echo $io[$i]['io_price']; ?>">
                                        <input type="hidden" class="io_price_origin" value="<?php echo $item_option['io_price'] ? $item_option['io_price'] : $io[$i]['io_price']; ?>">
                                        <input type="hidden" class="io_price_before_custom" value="<?php echo $io[$i]['io_price']; ?>">
                                        <input type="hidden" class="io_price_partner" value="<?php echo $io[$i]['io_price_partner'] ? $io[$i]['io_price_partner'] : $io[$i]['io_price']; ?>">
                                        <input type="hidden" class="io_price_dealer" value="<?php echo $io[$i]['io_price_dealer'] ? $io[$i]['io_price_dealer'] : $io[$i]['io_price']; ?>">
                                        <input type="hidden" class="io_price_dealer2" value="<?php echo $io[$i]['io_price_dealer2'] ? $io[$i]['io_price_dealer2'] : $io[$i]['io_price']; ?>">
                                        <input type="hidden" class="io_stock" value="<?php echo $io[$i]['it_stock_qty']; ?>">
                                        <div class="row">
                                            <div class="col-sm-7">
                                                <label>
                                                    <span class="it_opt_subj"><?php echo $io[$i]['ct_option']; ?></span>
                                                    <span class="it_opt_prc"><?php echo $io[$i]['io_display_price']; ?></span>
                                                </label>
                                            </div>
                                            <?php
                                            //print_r2($io[$i]);
                                            if ($io[$i]['io_type']) // 0 == 선택옵션, 1 == 추가옵션
                                                $opt_price = $io[$i]['io_price'];
                                            else
                                                $opt_price = $io[$i]['ct_price'] + $io[$i]['io_price'];
                                            ?>
                                            <div class="col-sm-5 itm-option-group">
                                                <div class="option-price-wrapper">
                                                    <input class="option-price" type="text" value="<?php echo number_format($opt_price) ?>" data-price="<?php echo $opt_price ?>" onkeyup="_editOptionPrice(this)" readonly/>
                                                </div>
                                                <div class="input-group">
                                                    <label for="ct_qty_<?php echo $i; ?>" class="sound_only">수량</label>
                                                    <div class="input-group-btn">
                                                        <button type="button" class="it_qty_plus btn btn-black btn-sm"><i class="fa fa-plus-circle fa-lg"></i><span class="sound_only">증가</span></button>
                                                    </div>
                                                    <input type="text" name="ct_qty[<?php echo $it['it_id']; ?>][]" value="<?php echo $io[$i]['ct_qty']; ?>" id="ct_qty_<?php echo $i; ?>" class="form-control input-sm" size="5">
                                                    <div class="input-group-btn-del"><button type="button" class="it_opt_del btn btn-sm btn-lightgray"><i class="fa fa-times-circle fa-lg"></i><span class="sound_only">삭제</span></button></div>
                                                    <div class="input-group-btn">
                                                        <!--<button type="button" class="it_qty_plus btn btn-black btn-sm"><i class="fa fa-plus-circle fa-lg"></i><span class="sound_only">증가</span></button>-->
                                                        <button type="button" class="it_qty_minus btn btn-black btn-sm"><i class="fa fa-minus-circle fa-lg"></i><span class="sound_only">감소</span></button>
                                                        <!--<button type="button" class="it_opt_del btn btn-black btn-sm"><i class="fa fa-times-circle fa-lg"></i><span class="sound_only">삭제</span></button>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if($it['pt_msg1']) { ?>
                                            <div style="margin-top:10px;">
                                                <input type="text" name="pt_msg1[<?php echo $it['it_id']; ?>][]" class="form-control input-sm" placeholder="<?php echo $it['pt_msg1'];?>" value="<?php echo $io[$i]['pt_msg1'];?>">
                                            </div>
                                        <?php } ?>
                                        <?php if($it['pt_msg2']) { ?>
                                            <div style="margin-top:10px;">
                                                <input type="text" name="pt_msg2[<?php echo $it['it_id']; ?>][]" class="form-control input-sm" placeholder="<?php echo $it['pt_msg2'];?>" value="<?php echo $io[$i]['pt_msg2'];?>">
                                            </div>
                                        <?php } ?>
                                        <?php if($it['pt_msg3']) { ?>
                                            <div style="margin-top:10px;">
                                                <input type="text" name="pt_msg3[<?php echo $it['it_id']; ?>][]" class="form-control input-sm" placeholder="<?php echo $it['pt_msg3'];?>" value="<?php echo $io[$i]['pt_msg3'];?>">
                                            </div>
                                        <?php } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                            <div>
                                <input type="checkbox" id="chk_custom_price" name="chk_custom_price" value="1" onchange="toggleOptionCustom(this)">
                                <label for="chk_custom_price">제품 가격 임의 변경</label>
                            </div>
                        </div>
                        <style>
                        #add_item_options_table {
                            border-top:1px solid #dddddd;
                            margin-top:15px;
                            padding-top:10px;
                            width:100%;
                        }
                        #add_item_options_table th {
                            color:#666;
                            font-size:12px;
                            text-align:left;
                            width:38%;
                            line-height:35px;
                        }
                        #add_item_options_table td {
                            width:62%;
                            font-size:12px;
                            position:relative;
                            text-align:right;
                            color:#666;
                        }
                        #add_item_options_table td .chk_dealer_price_div {
                            position:absolute;
                            left:0;
                        }
                        #add_item_options_table #ct_discount {
                            width:120px;
                            min-width:120px;
                        }
                        #add_item_options_table td#it_tot_pay_price {
                            font-size:16px;
                            color:black;
                            font-weight:bold;
                        }
                        #add_item_options_table tr.head-line td,
                        #add_item_options_table tr.head-line th {
                            border-top:1px solid #ddd;
                        }
                        #g5_shop_order_cart_memo {
                            width: 100%;
                            background-color: white;
                            box-sizing: border-box;
                            padding: 15px;
                            height: 65px;
                            color: #656565;
                            border: 1px solid #d9d9d9;
                        }
                        #g5_shop_order_cart_file {
                            text-align:left;
                            margin-top:10px;
                        }
                        #pop_add_item .content .uploadbtn {
                            font-size:12px;
                            background-color:#a2a2a2;
                            border:none;
                            color:white;
                            border-radius:3px;
                        }
                        .popup_upload_files {
                            list-style:none;
                            padding:0;
                            margin:0;
                        }
                        .popup_upload_files li {
                            padding:0;
                            margin:0;
                            margin-top:10px;
                            margin-right:10px;
                            display:inline-block;
                        }
                        .popup_upload_files li a {
                            vertical-align:middle;
                        }
                        .popup_upload_files li .filelink {
                            text-decoration:underline;
                            color:#0592ff;
                        }
                        .popup_upload_files li .remove {
                            cursor:pointer;
                        }
                        </style>
                        <table id="add_item_options_table">
                            <tbody>
                                <tr>
                                    <th>총 금액</th>
                                    <td id="it_tot_price">0원</td>
                                </tr>
                                <tr class="special_price_tr">
                                    <th>파트너가 적용</th>
                                    <td>
                                        <div class="chk_dealer_price_div">
                                            <input type="checkbox" name="chk_partner_price" value="1" id="chk_partner_price" onclick="price_calculate(this);">
                                            사용
                                        </div>
                                        <span id="it_tot_price_partner">0원</span>
                                    </td>
                                </tr>
                                <tr class="special_price_tr">
                                    <th>사업소 적용</th>
                                    <td>
                                        <div class="chk_dealer_price_div">
                                            <input type="checkbox" name="chk_dealer_price" value="1" id="chk_dealer_price" onclick="price_calculate(this);">
                                            사용
                                        </div>
                                        <span id="it_tot_price_dealer">0원</span>
                                    </td>
                                </tr>
                                <tr class="special_price_tr">
                                    <th>우수사업소 적용</th>
                                    <td>
                                        <div class="chk_dealer_price_div">
                                            <input type="checkbox" name="chk_dealer2_price" value="1" id="chk_dealer2_price" onclick="price_calculate(this);">
                                            사용
                                        </div>
                                        <span id="it_tot_price_dealer2">0원</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>추가할인 금액</th>
                                    <td id="ct_discount">
                                        <input type="text" name="ct_discount" value="<?php echo $ct_discount; ?>" id="ct_discount" class="frm_input">
                                    </td>
                                </tr>
                                <tr>
                                    <th>총 금액</th>
                                    <td id="it_tot_pay_price">0원</td>
                                </tr>
                                <tr class="head-line">
                                    <th>첨부파일</th>
                                    <td>
                                        <div id="g5_shop_order_cart_file">
                                            <button type="button" class="shbtn uploadbtn">찾아보기</button>
                                            <ul class="popup_upload_files popup_upload_files_order">
                                            <?php
                                            // $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND it_id = '{$it_id}' AND ctf_type = 'order'";
                                            $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_uid = '{$uid}' AND ctf_type = 'order'";
                                            $result = sql_query($sql);
                                            while ($row = sql_fetch_array($result)) {
                                            ?>
                                                <li>
                                                    <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink" target="_blank"><?php echo $row['real_name']; ?></a>
                                                    <a href='#' class="remove" data-no="<?php echo $row['no']; ?>" data-it-id="<?php echo $it_id; ?>" data-uid="<?php echo $uid; ?>" data-od-id="<?php echo $od_id; ?>"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_del_s.png" /></a>
                                                </li>
                                            <?php } ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                // $sql = "SELECT * FROM g5_shop_order_cart_memo WHERE od_id = '{$od_id}' AND it_id = '{$it_id}'";
                                $sql = "SELECT * FROM g5_shop_order_cart_memo WHERE od_id = '{$od_id}' AND ctm_uid = '{$uid}'";
                                $memo = sql_fetch($sql);
                                ?>
                                <tr>
                                    <th colspan="2">요청사항</th>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <textarea name="g5_shop_order_cart_memo" rows="8"  id="g5_shop_order_cart_memo"><?php echo $_GET['memo']; ?></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p></p>
                </div>
            </div>
            <style>
            .input_width_m{width:60px !important;}
            #custom_order {
                height:770px;
                overflow-y:scroll;
                box-sizing:border-box;
            }
            #custom_order .ask {
                background-color:#dddddd;
                height:100%;
            }
            #custom_order .ask p {
                padding:250px 0 20px;
                text-align:center;
                color:#656565;
                margin:0;
            }
            #custom_order .ask ul {
                list-style:none;
                width:250px;
                padding:0;
                margin:0 auto;
            }
            #custom_order .ask ul li {
                padding:7px 0;
                margin-bottom:10px;
                border:1px solid #cccccc;
                cursor:pointer;
                color:#656565;
                text-align:center;
                background-color:white;
                font-weight:bold;
            }
            #custom_order .ask ul li:hover {
                background-color:#f3f3f3;
            }
            #custom_order .ask ul.disable li {
                cursor: not-allowed;
                background-color: #eee;
                opacity: 1;
            }
            #custom_order>.customs {
                height:100%;
                background-color:white;
                display:none;
            }
            #custom_order>.customs h1, h2 {
                padding:0;
                margin:0;
            }
            #custom_order>.customs>.header {
                border-bottom:1px solid #ddd;
            }
            #custom_order>.customs>.header:after {
                clear:both;
                content: '';
                display:block;
            }
            #custom_order>.customs>.header h1 {
                font-size:15px;
                color:#333;
                float:left;
                line-height:40px;
            }
            #custom_order>.customs>.header .cancel {
                float: right;
                cursor: pointer;
                margin-top: 3px;
            }
            #custom_order>.customs .custom_blocks {
                padding:0;
                margin:0;
                list-style:none;
                color:#656565;
            }
            #custom_order>.customs .custom_blocks .header {
                position:relative;
            }
            #custom_order>.customs .custom_blocks .header h1 {
                font-size:14px;
                line-height:30px;
            }
            #custom_order>.customs .custom_blocks .header .opener {
                position:absolute;
                font-size:17px;
                top: 8px;
                right: 3px;
                cursor:pointer;
            }
            #custom_order>.customs .custom_blocks .header .use_block {
                position:absolute;
                font-size:13px;
                top: 8px;
                right: 40px;
            }
            #custom_order>.customs .custom_blocks .header .use_block label {
                padding-left:5px;
                cursor:pointer;
                vertical-align:middle;
            }
            #custom_order>.customs .custom_blocks li {
                margin-top:10px;
            }
            #custom_order>.customs .custom_blocks .cs_contents {
                font-size:12px;
                background-color:#efefef;
                color:#656565;
                display:none;
            }
            #custom_order>.customs .custom_blocks .use .cs_contents {
                display:block;
            }
            #custom_order>.customs .custom_blocks .cs_contents .opened {
                padding:10px;
                display:none;
            }
            #custom_order>.customs .custom_blocks .cs_contents .opened .cs_table {
                width:100%;
            }
            #custom_order>.customs .custom_blocks .cs_contents .opened .cs_table th {
            	vertical-align: top;
                text-align:left;
                width:20%;
                font-weight:normal;
                font-size:13px;
                color:#656565;
                padding: 5px 0;
            }
            #custom_order>.customs .custom_blocks .cs_contents .opened .cs_table th:before {
                content: '·';
                margin-right:4px;
            }
            #custom_order>.customs .custom_blocks .cs_contents .opened .cs_table>tbody>tr>td {
                font-size:13px;
                color:#656565;
                line-height:30px;
            }
            #custom_order>.customs .custom_blocks .cs_contents .opened .cs_table>tbody>tr>td label + input[type="radio"] {
                margin-left:15px;
            }
            #custom_order>.customs .custom_blocks .cs_contents .opened .cs_table>tbody>tr>td label + input[type="text"] {
                margin-left:10px;
            }
            #custom_order>.customs .custom_blocks .cs_contents .opened .cs_table>tbody>tr>td input[type="text"] + label {
                margin-left:10px;
            }
            #custom_order>.customs .custom_blocks .cs_contents .opened .cs_table>tbody>tr>td>input[type="radio"] {
                margin-right:5px;
                vertical-align:middle;
            }
            #custom_order>.customs .custom_blocks .cs_contents .opened .cs_table>tbody>tr>td input[type="text"] {
                min-width: 40px;
                width:40px;
                background-color:white;
                background:white !important;
                border-radius:3px;
                margin-right:3px;
            }
            #custom_order>.customs .custom_blocks .cs_contents .closed {
                padding:15px 20px;
                display:block;
            }
            #custom_order>.customs .custom_blocks .header .opener .open {
                display:none;
            }
            #custom_order>.customs .custom_blocks .block.open .opener .open {
                display:block;
            }
            #custom_order>.customs .custom_blocks .block.open .opener .close {
                display:none;
            }
            #custom_order>.customs .custom_blocks .block.open .cs_contents .opened {
                display:block;
            }
            #custom_order>.customs .custom_blocks .block.open .cs_contents .closed {
                display:none;
            }
            .power_line_table {
                width:140px;
                height:180px;
            }
            .power_line_table td {
                line-height:20px;
                font-size:13px;
            }
            .content_box{
                
            }
            </style>
            <div id="custom_order">
                <div class="ask" style="font-size:20px;">
                        <p style=""><b>상품 등록 이용안내</b></p>
                        <p style="margin:0px;padding-top:20px; padding-bottom:0px;text-align:left;margin-left:25%;">
                            1.&nbsp;&nbsp;제품가격 임의 변경을 선택 후<br>
                            &emsp;각 상품의 금액을 변경할 수 있습니다.  

                        </p>
                        <p style="margin:0px;padding-top:20px; padding-bottom:0px;text-align:left;margin-left:25%;">
                           2.&nbsp;&nbsp;전체 금액에서 추가 할인 금액을<br>
                           &emsp; 적용 할 수 있습니다.
                        </p> 
                        <p style="padding-top:0px;text-align:left;margin-left:25%;padding-bottom:0px;">
                        </p>
                        <p style="padding-top:20px;text-align:left;margin-left:25%;padding-bottom:0px;">
                            3.&nbsp;&nbsp;해당 주문에 대한 관리자 확인을 위한<br>
                            &emsp; 메모를 남길 수 있습니다.
                        </p>    
                </div>
                <input type="hidden" value="" name="cs_type" id="cs_type" />
                <div class="customs">
                    <div class="header">
                        <h1>주문제작</h1>
                        <div class="cancel">
                            <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_close.png" />
                        </div>
                    </div>
                    <ul class="custom_blocks">
                        <li id="co_type_1" class="block use open">
                            <div class="header">
                                <h1>기본정보</h1>
                                <div class="opener">
                                    <div class="open">▲</div>
                                    <div class="close">▼</div>
                                </div>
                                <div class="use_block">
                                    <input type="hidden" value="1" name="size_use" id="size_use" />
                                </div>
                            </div>
                            <div class="cs_contents">
                                <div class="opened">
                                    <table class="cs_table">
                                        <tbody>
                                            <tr>
                                                <th>사이즈</th>
                                                <td>
                                                    <label for="size_width">가로</label><input type="text" class="input_width_m" name="size_width" id="size_width" value="<?php echo $cs['size_width']; ?>" /> mm
                                                    <span class="lrmg15">X</span>
                                                    <label for="size_height">세로</label><input type="text" class="input_width_m" name="size_height" id="size_height" value="<?php echo $cs['size_height']; ?>" /> mm
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="closed">
                                </div>
                            </div>
                        </li>
                        <li id="co_type_2" class="block">
                            <div class="header">
                                <h1>프레임 (도광판)</h1>
                                <div class="opener">
                                    <div class="open">▲</div>
                                    <div class="close">▼</div>
                                </div>
                                <div class="use_block">
                                    <input type="checkbox" value="1" name="frame_use" id="frame_use" /><label for="frame_use">사용</label>
                                </div>
                            </div>
                            <div class="cs_contents">
                                <div class="opened">
                                    <table class="cs_table">
                                        <tbody>
                                            <tr>
                                                <th>사이즈 기준 </th>
                                                <td>
                                                    <input type="radio" name="frame_standard" id="frame_standard_1" value="내각" <?php echo $cs['frame_standard'] == '내각' ? 'checked' : ''; ?> /><label for="frame_standard_1">내각</label>
                                                    <input type="radio" name="frame_standard" id="frame_standard_2" value="외각" <?php echo $cs['frame_standard'] == '외각' ? 'checked' : ''; ?> /><label for="frame_standard_2">외각</label>
                                                    <input type="radio" name="frame_standard" id="frame_standard_3" value="보이는면" <?php echo $cs['frame_standard'] == '보이는면' ? 'checked' : ''; ?> /><label for="frame_standard_3">보이는면</label><br>
                                                    <input type="radio" name="frame_standard" id="frame_standard_4" value="(도광판) LED포함" <?php echo $cs['frame_standard'] == '(도광판) LED포함' ? 'checked' : ''; ?> /><label for="frame_standard_4">(도광판) LED포함</label>
                                                    <input type="radio" name="frame_standard" id="frame_standard_5" value="(도광판) LED별도" <?php echo $cs['frame_standard'] == '(도광판) LED별도' ? 'checked' : ''; ?> /><label for="frame_standard_5">(도광판) LED별도</label>
                                                    <input type="radio" name="frame_standard" id="frame_standard_6" value="(도광판) 화면사이즈" <?php echo $cs['frame_standard'] == '(도광판) 화면사이즈' ? 'checked' : ''; ?> /><label for="frame_standard_6">(도광판) 화면사이즈</label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>색상</th>
                                                <td>
                                                    <input type="radio" name="frame_color" id="frame_color_1" value="은색" <?php echo $cs['frame_color'] == '은색' ? 'checked' : ''; ?> /><label for="frame_color_1">은색</label>
                                                    <input type="radio" name="frame_color" id="frame_color_2" value="흑색" <?php echo $cs['frame_color'] == '흑색' ? 'checked' : ''; ?> /><label for="frame_color_2">흑색</label>
                                                    <input type="radio" name="frame_color" id="frame_color_3" value="백색" <?php echo $cs['frame_color'] == '백색' ? 'checked' : ''; ?> /><label for="frame_color_3">백색</label>
                                                    <input type="radio" name="frame_color" id="frame_color_4" value="기타" <?php echo $cs['frame_color'] && $cs['frame_color'] != '은색' && $cs['frame_color'] != '흑색' && $cs['frame_color'] != '백색' ? 'checked' : ''; ?> /><label for="frame_color_4">기타</label>
                                                    <input type="text" name="frame_color_other" id="frame_color_other" value="<?php echo $cs['frame_color'] && $cs['frame_color'] != '은색' && $cs['frame_color'] != '흑색' && $cs['frame_color'] != '백색' ? $cs['frame_color'] : ''; ?>" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>앞판</th>
                                                <td>
                                                    <input type="radio" name="frame_front" id="frame_front_1" value="없음" <?php echo $cs['frame_front'] == '없음' ? 'checked' : ''; ?> /><label for="frame_front_1">없음</label>
                                                    <input type="radio" name="frame_front" id="frame_front_2" value="있음" <?php echo $cs['frame_front'] == '있음' ? 'checked' : ''; ?> /><label for="frame_front_2">있음</label>
                                                    <label for="frame_front_transparent_acrylic" style="margin-left:15px;">투명아크릴</label><input type="text" name="frame_front_transparent_acrylic" id="frame_front_transparent_acrylic" value="<?php echo $cs['frame_front_transparent_acrylic']; ?>" />T,
                                                    <label for="frame_front_optical_scatter">광학산판</label><input type="text" name="frame_front_optical_scatter" id="frame_front_optical_scatter" value="<?php echo $cs['frame_front_optical_scatter']; ?>" />T
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>뒷판</th>
                                                <td>
                                                    <input type="radio" name="frame_back" id="frame_back_1" value="없음" <?php echo $cs['frame_back'] == '없음' ? 'checked' : ''; ?> /><label for="frame_back_1">없음</label>
                                                    <input type="radio" name="frame_back" id="frame_back_2" value="있음" <?php echo $cs['frame_back'] == '있음' ? 'checked' : ''; ?> /><label for="frame_back_2">있음</label>
                                                    <label for="frame_back_transparent_acrylic" style="margin-left:15px;">투명아크릴</label><input type="text" name="frame_back_transparent_acrylic" id="frame_back_transparent_acrylic" value="<?php echo $cs['frame_back_transparent_acrylic']; ?>" />T,
                                                    <label for="frame_back_mdf">MDF</label><input type="text" name="frame_back_mdf" id="frame_back_mdf" value="<?php echo $cs['frame_back_mdf']; ?>" />T,
                                                    <label for="frame_back_formax">포맥스</label><input type="text" name="frame_back_formax" id="frame_back_formax" value="<?php echo $cs['frame_back_formax']; ?>" />T
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="closed">
                                </div>
                            </div>
                        </li>
                        <li id="co_type_3" class="block">
                            <div class="header">
                                <h1>라이트패널</h1>
                                <div class="opener">
                                    <div class="open">▲</div>
                                    <div class="close">▼</div>
                                </div>
                                <div class="use_block">
                                    <input type="checkbox" value="1" name="lightpanel_use" id="lightpanel_use" /><label for="lightpanel_use">사용</label>
                                </div>
                            </div>
                            <div class="cs_contents">
                                <div class="opened">
                                    <table class="cs_table">
                                        <tbody>
                                            <tr>
                                                <th>LED 설치방향</th>
                                                <td>
                                                    <input type="radio" name="lightpanel_led_direction" id="lightpanel_led_direction_1" value="장변" <?php echo $cs['lightpanel_led_direction'] == '장변' ? 'checked' : ''; ?> /><label for="lightpanel_led_direction_1">장변</label>
                                                    <input type="radio" name="lightpanel_led_direction" id="lightpanel_led_direction_2" value="단변" <?php echo $cs['lightpanel_led_direction'] == '단변' ? 'checked' : ''; ?> /><label for="lightpanel_led_direction_2">단변</label>
                                                    <!-- <span class="lrmg15">/</span>
                                                    <label for="frame_front_optical_scatter">LED수</label>
                                                    <input type="text" name="lightpanel_led_qty" id="lightpanel_led_qty" data-after-txt="개" value="<?php echo $cs['lightpanel_led_qty']; ?>"  />개 -->
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>전원(SMPS)</th>
                                                <td>
                                                    <input type="radio" name="lightpanel_smps" id="lightpanel_smps_1" value="없음" <?php echo $cs['lightpanel_smps'] == '없음' ? 'checked' : ''; ?> /><label for="lightpanel_smps_1">없음</label>
                                                    <input type="radio" name="lightpanel_smps" id="lightpanel_smps_2" value="내장SMPS" <?php echo $cs['lightpanel_smps'] == '내장SMPS' ? 'checked' : ''; ?> /><label for="lightpanel_smps_2">내장SMPS</label>
                                                    <input type="radio" name="lightpanel_smps" id="lightpanel_smps_3" value="외장-어댑터" <?php echo $cs['lightpanel_smps'] == '외장-어댑터' ? 'checked' : ''; ?> /><label for="lightpanel_smps_3">외장-어댑터</label>
                                                    <input type="radio" name="lightpanel_smps" id="lightpanel_smps_4" value="외장-파워서플라이" <?php echo $cs['lightpanel_smps'] == '외장-파워서플라이' ? 'checked' : ''; ?> /><label for="lightpanel_smps_4">외장-파워서플라이</label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>전원선 위치</th>
                                                    <td>
                                                        <table cellspacing="0" cellpadding="0" class="power_line_table">
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
                                            </tr>
                                            <tr>
                                                <th>LED</th>
                                                <td>
                                                    <select id="lightpanel_led_ea" name="lightpanel_led_ea" class="">
                                                       <!--  <option value="0" <?php echo $cs['lightpanel_led_ea'] == '0' ? 'selected' : ''; ?>>0개</option> -->
                                                        <option value="1" <?php echo $cs['lightpanel_led_ea'] == '1' ? 'selected' : ''; ?>>1개</option>
                                                        <option value="2" <?php echo $cs['lightpanel_led_ea'] == '2' ? 'selected' : ''; ?>>2개</option>
                                                        <option value="4" <?php echo $cs['lightpanel_led_ea'] == '4' ? 'selected' : ''; ?>>4개</option>
                                                    </select>
                                                    <span class="lrmg15">/</span>
                                                    <select id="lightpanel_led_k" name="lightpanel_led_k" class="">
                                                        <option value="9000K" <?php echo $cs['lightpanel_led_k'] == '9000K' ? 'selected' : ''; ?>>9000K</option>
                                                        <option value="6000K" <?php echo $cs['lightpanel_led_k'] == '6000K' ? 'selected' : ''; ?>>6000K</option>
                                                        <option value="5000K" <?php echo $cs['lightpanel_led_k'] == '5000K' ? 'selected' : ''; ?>>5000K</option>
                                                        <option value="4000K" <?php echo $cs['lightpanel_led_k'] == '4000K' ? 'selected' : ''; ?>>4000K</option>
                                                        <option value="3000K" <?php echo $cs['lightpanel_led_k'] == '3000K' ? 'selected' : ''; ?>>3000K</option>
                                                        <option value="직하" <?php echo $cs['lightpanel_led_k'] == '직하' ? 'selected' : ''; ?>>직하</option>
                                                        <option value="엣지" <?php echo $cs['lightpanel_led_k'] == '엣지' ? 'selected' : ''; ?>>엣지</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>전원선</th>
                                                <td>
                                                    <label for="lightpanel_power_line_ac">AC전원선</label><input type="text" class="input_width_m"  name="lightpanel_power_line_ac" id="lightpanel_power_line_ac" value="<?php echo $cs['lightpanel_power_line_ac']; ?>"  />mm
                                                    <span class="lrmg15">/</span>
                                                    <label for="lightpanel_power_line_dc">DC잭</label><input type="text" class="input_width_m"  name="lightpanel_power_line_dc" id="lightpanel_power_line_dc" value="<?php echo $cs['lightpanel_power_line_dc']; ?>"  />mm
                                                    <span class="lrmg15">/</span>
                                                    <label for="lightpanel_power_line_wire">와이어</label><input type="text" class="input_width_m"  name="lightpanel_power_line_wire" id="lightpanel_power_line_wire"  value="<?php echo $cs['lightpanel_power_line_wire']; ?>" />mm
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>레이저가공</th>
                                                <td>
                                                    <input type="radio" name="lightpanel_laser" id="lightpanel_laser" value="없음" <?php echo $cs['lightpanel_laser'] == '없음' ? 'checked' : ''; ?>  /><label for="lightpanel_laser">없음</label>
                                                    <input type="radio" name="lightpanel_laser" id="lightpanel_laser_2" value="사용" <?php echo $cs['lightpanel_laser'] == '사용' ? 'checked' : ''; ?>  /><label for="lightpanel_laser_2">사용</label>
                                                    <span class="lrmg15"></span>
                                                    <button type="button" class="shbtn uploadbtn lightpanel_laser_uploadbtn">파일추가</button>
                                                    <ul class="popup_upload_files popup_upload_files_lightpanel_laser">
                                                    <?php
                                                    // $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND it_id = '{$it_id}' AND ctf_type = 'lightpanel_laser'";
                                                    $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_uid = '{$uid}' AND ctf_type = 'lightpanel_laser'";
                                                    $result = sql_query($sql);
                                                    while ($row = sql_fetch_array($result)) {
                                                    ?>
                                                        <li>
                                                            <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink" target="_blank"><?php echo $row['real_name']; ?></a>
                                                            <a href='#' class="remove" data-no="<?php echo $row['no']; ?>" data-it-id="<?php echo $it_id; ?>" data-od-id="<?php echo $od_id; ?>"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_del_s.png" /></a>
                                                        </li>
                                                    <?php } ?>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>스위치</th>
                                                <td>
                                                    <input type="radio" name="lightpanel_switch_use" id="lightpanel_switch_use" value="없음" <?php echo $cs['lightpanel_switch_use'] == '없음' ? 'checked' : ''; ?> /><label for="lightpanel_switch_use">없음</label>
                                                    <input type="radio" name="lightpanel_switch_use" id="lightpanel_switch_use_2" value="사용" <?php echo $cs['lightpanel_switch_use'] == '사용' ? 'checked' : ''; ?> /><label for="lightpanel_switch_use_2">사용</label>
                                                    <input type="text" name="lightpanel_switch_explain" id="lightpanel_switch_explain" placeholder="설명을 입력하세요" style="width:150px;" value="<?php echo $cs['lightpanel_switch_explain']; ?>" />
                                                    <br/>
                                                    <table cellspacing="0" cellpadding="0" id="lightpanel_switch_tbl" style="">
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
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="closed">
                                </div>
                            </div>
                        </li>
                        <li id="co_type_4" class="block">
                            <div class="header">
                                <h1>천장걸이형/거치대</h1>
                                <div class="opener">
                                    <div class="open">▲</div>
                                    <div class="close">▼</div>
                                </div>
                                <div class="use_block">
                                    <input type="checkbox" value="1" name="holder_use" id="holder_use" /><label for="holder_use">사용</label>
                                </div>
                            </div>
                            <div class="cs_contents">
                                <div class="opened">
                                    <table class="cs_table">
                                        <tbody>
                                            <tr>
                                                <th>분류</th>
                                                <td>
                                                    <input type="radio" name="holder_class" id="holder_class_1" value="천장걸이" <?php echo $cs['holder_class'] == '천장걸이' ? 'checked' : ''; ?>><label for="holder_class_1">천장걸이</label>
                                                    <input type="radio" name="holder_class" id="holder_class_2" value="거치대" <?php echo $cs['holder_class'] == '거치대' ? 'checked' : ''; ?>><label for="holder_class_2">거치대</label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>파이프 간격</th>
                                                <td>
                                                    <label for="holder_pipe_interval_1" class="hidden"></label><input type="text" class="input_width_m"  name="holder_pipe_interval_1" id="holder_pipe_interval_1" value="<?php echo $cs['holder_pipe_interval_1']; ?>" />mm &nbsp;&nbsp;↔
                                                    <label for="holder_pipe_interval_2" class="hidden"></label><input type="text" class="input_width_m"  name="holder_pipe_interval_2" id="holder_pipe_interval_2" value="<?php echo $cs['holder_pipe_interval_2']; ?>" />mm &nbsp;&nbsp;↔
                                                    <label for="holder_pipe_interval_3" class="hidden"></label><input type="text" class="input_width_m"  name="holder_pipe_interval_3" id="holder_pipe_interval_3" value="<?php echo $cs['holder_pipe_interval_3']; ?>" />mm
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>길이</th>
                                                <td>
                                                    <label for="holder_pipe_length" class="hidden"></label><input type="text" class="input_width_m"  name="holder_pipe_length" id="holder_pipe_length" value="<?php echo $cs['holder_pipe_length']; ?>" />mm
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="closed">
                                </div>
                            </div>
                        </li>
                        <li id="co_type_5" class="block">
                            <div class="header">
                                <h1>출력물</h1>
                                <div class="opener">
                                    <div class="open">▲</div>
                                    <div class="close">▼</div>
                                </div>
                                <div class="use_block">
                                    <input type="checkbox" value="1" name="printout_use" id="printout_use" /><label for="printout_use">사용</label>
                                </div>
                            </div>
                            <div class="cs_contents">
                                <div class="opened">
                                    <table class="cs_table">
                                        <tbody>
                                            <tr>
                                                <th>디자인</th>
                                                <td>
                                                    <button type="button" class="shbtn uploadbtn printout_design_uploadbtn">파일추가</button>
                                                    <ul class="popup_upload_files popup_upload_files_printout_design">
                                                    <?php
                                                    // $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND it_id = '{$it_id}' AND ctf_type = 'printout_design'";
                                                    $sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND ctf_uid = '{$uid}' AND ctf_type = 'printout_design'";
                                                    $result = sql_query($sql);
                                                    while ($row = sql_fetch_array($result)) {
                                                    ?>
                                                        <li>
                                                            <a href='<?php echo G5_URL; ?>/data/order_cart/<?php echo $row['file_name']; ?>' class="filelink" target="_blank"><?php echo $row['real_name']; ?></a>
                                                            <a href='#' class="remove" data-no="<?php echo $row['no']; ?>" data-it-id="<?php echo $it_id; ?>" data-uid="<?php echo $uid; ?>" data-od-id="<?php echo $od_id; ?>"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_del_s.png" /></a>
                                                        </li>
                                                    <?php } ?>
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>출력물</th>
                                                <td>
                                                    <input type="radio" name="printout_printout" id="printout_printout_1" value="없음" <?php echo $cs['printout_printout'] == '없음' ? 'checked' : ''; ?>><label for="printout_printout_1">없음</label>
                                                    <input type="radio" name="printout_printout" id="printout_printout_2" value="백릿" <?php echo $cs['printout_printout'] == '백릿' ? 'checked' : ''; ?>><label for="printout_printout_2">백릿</label>
                                                    <input type="radio" name="printout_printout" id="printout_printout_3" value="페트지" <?php echo $cs['printout_printout'] == '페트지' ? 'checked' : ''; ?>><label for="printout_printout_3">페트지</label>
                                                    <input type="radio" name="printout_printout" id="printout_printout_4" value="유포지" <?php echo $cs['printout_printout'] == '유포지' ? 'checked' : ''; ?>><label for="printout_printout_4">유포지</label>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="closed">
                                </div>
                            </div>
                        </li>
                        <li id="co_type_6" class="block use open">
                            <div class="header">
                                <h1>작업요청내용</h1>
                                <div class="opener">
                                    <div class="open">▲</div>
                                    <div class="close">▼</div>
                                </div>
                                <div class="use_block">
                                    <input type="hidden" value="1" name="content_use" id="content_use" />
                                </div>
                            </div>
                            <div class="cs_contents">
                                <div class="opened">
                                    <table class="cs_table">
                                        <tbody>
                                            <tr>
                                                <th>공통내용</th>
                                                <td>
                                                    <input type="text" name="content_common" id="content_common" style="width:100%;" value="<?php echo $cs['content_common']; ?>" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>민아트</th>
                                                <td>
                                                    <input type="text" name="content_minart" id="content_minart" style="width:100%;" value="<?php echo $cs['content_minart']; ?>" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>쎌마텍</th>
                                                <td>
                                                    <input type="text" name="content_selmartec" id="content_selmartec" style="width:100%;" value="<?php echo $cs['content_selmartec']; ?>" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>LP팀</th>
                                                <td>
                                                    <input type="text" name="content_lp" id="content_lp" style="width:100%;" value="<?php echo $cs['content_lp']; ?>" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="closed">
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="addoptionbuttons">
                <a href="./pop.order.item.add.php?od_id=<?php echo $od_id; ?>">
                    <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/icon_arrow_prev_w.png" />상품선택
                </a>
                <input type="submit" value="확인" />
            </div>
        </form>
    </div>
</div>


<script>
var od_id = '<?php echo $od_id; ?>';
var it_id = '<?php echo $it_id; ?>';
var uid = '<?php echo $uid; ?>';

$(function() {
    function cs_summary() {
        var parent = $('.custom_blocks');
        var parent_li = $(parent).find('li.block');

        $.each(parent_li, function(index, item){
            var parent_li_id = $(item).attr('id');
            var summary_el = $(item).find('.cs_contents .closed');

            var ret = '';
            if ( parent_li_id == 'co_type_1' ) {
                ret = '';

                var width = $('#size_width').val() || 0;
                var height = $('#size_height').val() || 0;

                ret += '가로: ' + width + 'mm, 세로: ' + height + 'mm';
                $(summary_el).html(ret);
            }

            if ( parent_li_id == 'co_type_2' ) {
                ret = '';

                var frame_standard = $(":input:radio[name=frame_standard]:checked").val() || '선택안함';
                ret += '프레임기준: ' + frame_standard + ' ';

                var frame_color = $(":input:radio[name=frame_color]:checked").val() || '선택안함';
                ret += ', 색상: ' + frame_color + ' ';

                var frame_color_other = $('#frame_color_other').val() || '';
                if ( frame_color_other ) {
                    ret += '(' + frame_color_other + ') ';
                }

                var frame_front = $(":input:radio[name=frame_front]:checked").val() || '선택안함';
                ret += ', 앞판: ' + frame_front + ' ';

                var frame_front_transparent_acrylic = $('#frame_front_transparent_acrylic').val() || '';
                if ( frame_front_transparent_acrylic ) {
                    ret += ', 앞판 투명아크릴:' + frame_front_transparent_acrylic + 'T ';
                }

                var frame_front_optical_scatter = $('#frame_front_optical_scatter').val() || '';
                if ( frame_front_optical_scatter ) {
                    ret += ', 앞판 광학산판:' + frame_front_optical_scatter + 'T ';
                }

                var frame_back = $(":input:radio[name=frame_back]:checked").val() || '선택안함';
                ret += ', 뒷판: ' + frame_back + ' ';

                var frame_back_transparent_acrylic = $('#frame_back_transparent_acrylic').val() || '';
                if ( frame_back_transparent_acrylic ) {
                    ret += ', 뒷판 투명아크릴:' + frame_back_transparent_acrylic + 'T ';
                }

                var frame_back_mdf = $('#frame_back_mdf').val() || '';
                if ( frame_back_mdf ) {
                    ret += ', 뒷판 MDF:' + frame_back_mdf + 'T ';
                }

                var frame_back_formax = $('#frame_back_formax').val() || '';
                if ( frame_back_formax ) {
                    ret += ', 뒷판 포맥스:' + frame_back_formax + 'T ';
                }

                $(summary_el).html(ret);
            }

            if ( parent_li_id == 'co_type_3' ) {
                ret = '';

                var lightpanel_led_direction = $(":input:radio[name=lightpanel_led_direction]:checked").val() || '선택안함';
                ret += '설치방향: ' + lightpanel_led_direction + ' ';

                var lightpanel_led_qty = $('#lightpanel_led_qty').val() || '0';
                ret += ', LED수: ' + lightpanel_led_qty + '개 ';

                var lightpanel_smps = $(":input:radio[name=lightpanel_smps]:checked").val() || '선택안함';
                ret += ', 전원(SMPS): ' + lightpanel_smps + ' ';

                var lightpanel_power_line = $(":input:radio[name=lightpanel_power_line]:checked").val() || '선택안함';
                ret += ', 전원선 위치: ' + lightpanel_power_line + ' ';

                var lightpanel_led_ea = $('#lightpanel_led_ea option').filter(":selected").val();
                var lightpanel_led_k = $('#lightpanel_led_k option').filter(":selected").val();
                ret += ', LED: ' + lightpanel_led_ea + '개 / ' + lightpanel_led_k + 'K ';

                var lightpanel_power_line_dc = $('#lightpanel_power_line_dc').val() || '0';
                ret += ', DC잭: ' + lightpanel_power_line_dc + 'm ';

                var lightpanel_power_line_wire = $('#lightpanel_power_line_wire').val() || '0';
                ret += ', 와이어: ' + lightpanel_power_line_wire + 'm ';

                var lightpanel_laser = $(":input:radio[name=lightpanel_laser]:checked").val() || '선택안함';
                ret += ', 레이저가공: ' + lightpanel_laser + ' ';

                var lightpanel_switch_use = $(":input:radio[name=lightpanel_switch_use]:checked").val() || '선택안함';
                var lightpanel_switch = $(":input:radio[name=lightpanel_switch]:checked").val() || '선택안함';
                ret += ', 스위치: ' + lightpanel_switch_use + ', ' + lightpanel_switch + ' ';


                var lightpanel_switch_explain = $('#lightpanel_switch_explain').val() || '';
                if ( lightpanel_switch_explain ) {
                    ret += '(' + lightpanel_switch_explain + ') ';
                }

                $(summary_el).html(ret);
            }


            if ( parent_li_id == 'co_type_4' ) {
                ret = '';

                var holder_class = $(":input:radio[name=holder_class]:checked").val() || '선택안함';
                ret += '분류: ' + holder_class + ' ';

                var holder_pipe_interval_1 = $('#holder_pipe_interval_1').val() || '0';
                var holder_pipe_interval_2 = $('#holder_pipe_interval_2').val() || '0';
                var holder_pipe_interval_3 = $('#holder_pipe_interval_3').val() || '0';
                ret += ', 파이프 간격: ' + holder_pipe_interval_1 + 'm ↔ ' + holder_pipe_interval_2  + 'm ↔ ' + holder_pipe_interval_3 + 'm ';

                var holder_pipe_length = $('#holder_pipe_length').val() || '0';
                ret += ', 길이: ' + holder_pipe_length + 'm ';

                $(summary_el).html(ret);
            }

            if ( parent_li_id == 'co_type_5' ) {
                ret = '';

                var printout_printout = $(":input:radio[name=printout_printout]:checked").val() || '선택안함';
                ret += '출력물: ' + printout_printout + ' ';

                $(summary_el).html(ret);
            }
            if ( parent_li_id == 'co_type_6' ) {
                ret = '';

                var co_type_6_cnt = 0;

                var content_common = $('#content_common').val();
                if ( content_common ) {
                    ret += '공통내용: ' + content_common + '<br />';
                    co_type_6_cnt++;
                }

                var content_minart = $('#content_minart').val();
                if ( content_minart ) {
                    ret += '민아트: ' + content_minart + '<br />';
                    co_type_6_cnt++;
                }

                var content_selmartec = $('#content_selmartec').val();
                if ( content_selmartec ) {
                    ret += '쎌마텍: ' + content_selmartec + '<br />';
                    co_type_6_cnt++;
                }

                var content_lp = $('#content_lp').val();
                if ( content_lp ) {
                    ret += 'LP팀: ' + content_lp + '<br />';
                    co_type_6_cnt++;
                }

                if ( !co_type_6_cnt ) {
                    ret += '내용없음';
                }

                $(summary_el).html(ret);
                
            }
        });
    }
    $('#custom_order input, #custom_order select').on( "click propertychange change keyup paste input", function() {
        cs_summary();
    });
    cs_summary();

    // 주문제작 출력물 디자인 파일첨부
    $( document ).on( "click", '.printout_design_uploadbtn', function() {

        var $form = $('<form class="hidden_form"></form>');
        $form.attr('action', './ajax.order.item.add.cart_file_upload.php');
        $form.attr('method', 'post');
        //$form.attr('target', 'iFrm');
        $form.appendTo('body');

        var str = $('<input type="file" name="file" class="g5_shop_order_cart_file_printout_design">');
        $form.append(str);
        $form.append('<input type="hidden" name="it_id" value="' + it_id + '" />');
        $form.append('<input type="hidden" name="od_id" value="' + od_id + '" />');
        $form.append('<input type="hidden" name="uid" value="' + uid + '" />');
        $form.append('<input type="hidden" name="type" value="printout_design" />');

        $($form).find('input[type="file"]').click();
    });

    function cs_as_li_use(type) {
        if ( !type ) return;

        $('#co_type_' + type).find('.use_block').find('input[type="checkbox"]').click();
        $('#co_type_' + type).addClass('open use');
    }

    function cs_as_li_select(type) {
        if ( !type ) return;

        $('#custom_order .ask').hide();
        $('#custom_order>.customs').show();

        switch(type) {
            case 1:
                cs_as_li_use(1);
                cs_as_li_use(6);
                break;
            case 2:
                cs_as_li_use(1);
                cs_as_li_use(2);
                cs_as_li_use(3);
                cs_as_li_use(6);
                break;
            case 3:
                cs_as_li_use(1);
                cs_as_li_use(2);
                cs_as_li_use(3);
                cs_as_li_use(4);
                cs_as_li_use(6);
                break;
            case 4:
                cs_as_li_use(1);
                cs_as_li_use(2);
                cs_as_li_use(6);
                break;
            case 5:
                cs_as_li_use(1);
                cs_as_li_use(2);
                cs_as_li_use(6);
                break;
        }

        $('#cs_type').val(type);

        // 제품 가격 임의 변경 클릭
        $('#chk_custom_price').click();
    }

    <?php if ($cs['odc_no']) { ?>
    // 주문제작 값있을때 체크해주기
    cs_as_li_select(<?php echo $cs['cs_type']; ?>);
    <?php } ?>

    // 주문 제작하시겠습니까? 버튼
    $(document).on('click', '#custom_order .ask .enable li', function() {
        var type = $(this).data('type');
        cs_as_li_select(type);

        $('#cs_type').val(type);
    });

    $( document ).on( "click", '#custom_order>.customs .custom_blocks .header .use_block input', function() {

        var checked = $(this).is(":checked");
        var parent_li = $(this).closest('li.block');


        if ( checked ) {
            parent_li.addClass('open');
            parent_li.addClass('use');
        }else{
            parent_li.removeClass('open');
            parent_li.removeClass('use');
        }
    });

    $( document ).on( "change", '.g5_shop_order_cart_file_printout_design', function() {

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
                        ret += '<a class="remove" data-no="' + data.data[i]['no'] + '" data-it-id="' + data.data[i]['it_id'] + '" data-od-id="' + data.data[i]['od_id'] + '" ><img src="/adm/shop_admin/img/btn_del_s.png" /></a>';
                        ret += '</li>';
                    }

                    $('.popup_upload_files_printout_design').html(ret);
                }
            })

    });
    $( document ).on( "click", '.popup_upload_files_printout_design .remove', function() {

        var no = $(this).data('no');
        var it_id = $(this).data('it-id');
        var od_id = $(this).data('od-id');
        var uid = $(this).data('uid');
        var obj = $(this);

        var formdata = {
            no: no,
            it_id: it_id,
            od_id: od_id,
            uid: uid,
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



    // 주문제작 라이트패널 레이저 가공 파일첨부
    $( document ).on( "click", '.lightpanel_laser_uploadbtn', function() {

        var $form = $('<form class="hidden_form"></form>');
        $form.attr('action', './ajax.order.item.add.cart_file_upload.php');
        $form.attr('method', 'post');
        //$form.attr('target', 'iFrm');
        $form.appendTo('body');

        var str = $('<input type="file" name="file" class="g5_shop_order_cart_file_laser">');
        $form.append(str);
        $form.append('<input type="hidden" name="it_id" value="' + it_id + '" />');
        $form.append('<input type="hidden" name="od_id" value="' + od_id + '" />');
        $form.append('<input type="hidden" name="uid" value="' + uid + '" />');
        $form.append('<input type="hidden" name="type" value="lightpanel_laser" />');

        $($form).find('input[type="file"]').click();
    });

    $( document ).on( "change", '.g5_shop_order_cart_file_laser', function() {

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
                        ret += '<a class="remove" data-no="' + data.data[i]['no'] + '" data-it-id="' + data.data[i]['it_id'] + '" data-uid="' + data.data[i]['uid'] + '" data-od-id="' + data.data[i]['od_id'] + '" ><img src="/adm/shop_admin/img/btn_del_s.png" /></a>';
                        ret += '</li>';
                    }

                    $('.popup_upload_files_lightpanel_laser').html(ret);
                }
            })

    });
    $( document ).on( "click", '.popup_upload_files_lightpanel_laser .remove', function() {

        var no = $(this).data('no');
        var it_id = $(this).data('it-id');
        var od_id = $(this).data('od-id');
        var uid = $(this).data('uid');
        var obj = $(this);

        var formdata = {
            no: no,
            it_id: it_id,
            od_id: od_id,
            uid: uid,
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

    // 주문제작 펼치기 닫기
    $('#custom_order>.customs .custom_blocks .header .opener').click(function() {
        $(this).closest('li.block').toggleClass('open');
    });

    // 주문제작 취소 버튼
    $('#custom_order>.customs>.header .cancel').click(function() {

        $('#custom_order .ask').show();
        $('#custom_order>.customs').hide();
    });

    $("select.it_option").addClass("form-control input-sm");
    $("select.it_supply").addClass("form-control input-sm");

    price_calculate();

    $("#ct_discount").on("change keyup paste", function() {
        price_calculate();
    });

    <?php if ( $dealer_price ) { ?>
        $('#chk_dealer_price').click();
    <?php } ?>
    <?php if ( $dealer2_price ) { ?>
        $('#chk_dealer2_price').click();
    <?php } ?>

    // 옵션 첨부파일
    $( document ).on( "click", '#g5_shop_order_cart_file button', function() {

        var $form = $('<form class="hidden_form"></form>');
        $form.attr('action', './ajax.order.item.add.cart_file_upload.php');
        $form.attr('method', 'post');
        //$form.attr('target', 'iFrm');
        $form.appendTo('body');

        var str = $('<input type="file" name="file" class="g5_shop_order_cart_file_file">');
        $form.append(str);
        $form.append('<input type="hidden" name="it_id" value="' + it_id + '" />');
        $form.append('<input type="hidden" name="od_id" value="' + od_id + '" />');
        $form.append('<input type="hidden" name="uid" value="' + uid + '" />');
        $form.append('<input type="hidden" name="type" value="order" />');

        $($form).find('input[type="file"]').click();
    });

    $( document ).on( "change", '.g5_shop_order_cart_file_file', function() {

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
                        ret += '<a class="remove" data-no="' + data.data[i]['no'] + '" data-it-id="' + data.data[i]['it_id'] + '" data-uid="' + data.data[i]['uid'] + '" data-od-id="' + data.data[i]['od_id'] + '" ><img src="/adm/shop_admin/img/btn_del_s.png" /></a>';
                        ret += '</li>';
                    }

                    $('.popup_upload_files_order').html(ret);
                }
            })
        
    });

    $( document ).on( "click", '#g5_shop_order_cart_file .remove', function() {

        var no = $(this).data('no');
        var it_id = $(this).data('it-id');
        var od_id = $(this).data('od-id');
        var obj = $(this);

        var formdata = {
            no: no,
            it_id: it_id,
            od_id: od_id,
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

    <?php if ( $ct_price_type ) { ?>
    var ct_price_type = '<?php echo $ct_price_type; ?>';
    if ( ct_price_type === '1' ) {
        $('#chk_partner_price').click();
    }
    if ( ct_price_type === '2' ) {
        $('#chk_dealer_price').click();
    }
    if ( ct_price_type === '3' ) {
        $('#chk_dealer2_price').click();
    }
    if ( ct_price_type === '4' ) {
        $('#chk_custom_price').click();
    }
    <?php } ?>

});

function formcheck(f) {
    var val, io_type, result = true;
    var sum_qty = 0;
    var min_qty = parseInt(<?php echo $it['it_buy_min_qty']; ?>);
    var max_qty = parseInt(<?php echo $it['it_buy_max_qty']; ?>);
    var $el_type = $("input[name^=io_type]");

    $("input[name^=ct_qty]").each(function(index) {
        val = $(this).val();

        if(val.length < 1) {
            alert("수량을 입력해 주십시오.");
            result = false;
            return false;
        }

        if(val.replace(/[0-9]/g, "").length > 0) {
            alert("수량은 숫자로 입력해 주십시오.");
            result = false;
            return false;
        }

        if(parseInt(val.replace(/[^0-9]/g, "")) < 1) {
            alert("수량은 1이상 입력해 주십시오.");
            result = false;
            return false;
        }

        io_type = $el_type.eq(index).val();
        if(io_type == "0")
            sum_qty += parseInt(val);
    });

    if(!result) {
        return false;
    }

    if(min_qty > 0 && sum_qty < min_qty) {
        alert("선택옵션 개수 총합 "+number_format(String(min_qty))+"개 이상 주문해 주십시오.");
        return false;
    }

    if(max_qty > 0 && sum_qty > max_qty) {
        alert("선택옵션 개수 총합 "+number_format(String(max_qty))+"개 이하로 주문해 주십시오.");
        return false;
    }

    return true;
}
</script>

<script>
    function _editItemPrice(x) {
        var onlyNum = parseInt($(x).val().replace(/[^0-9]/g,""));
        
        if (isNaN(onlyNum)) {
            onlyNum = 0;
        }

        $(x).val(number_format(onlyNum));
        $(x).data('price-num', onlyNum);
        $('#it_price').val(onlyNum);
        price_calculate();
    }
    
    function _editOptionPrice(x) {
        var onlyNum = parseInt($(x).val().replace(/[^0-9]/g,""));
        var it_price = parseInt($("input#it_price").val());
        
        if (isNaN(onlyNum)) {
            onlyNum = 0;
        }
        
        $(x).data('price', onlyNum);
        $(x).val(number_format(onlyNum));
        
        if ($('#chk_custom_price').is(":checked")) {
            var type = $(x).parent().parent().parent().siblings('input[name^=io_type]').val(); // 0 = 선택옵션, 1 = 추가옵션
            var calOptionPrice;
            if (type === 0) {
                calOptionPrice = onlyNum - it_price;
            } else {
                calOptionPrice = onlyNum;
            }
            
            $(x).parent().parent().parent().siblings('input.io_price').val(calOptionPrice);
            

        } else {
            $('.special_price_tr').show();
        }
        
        price_calculate();
    }
    
    function toggleOptionCustom(x) {
        var itemPrice = parseInt($("input#it_price").val());
        if ($(x).is(":checked")) {
            $('.option-price').prop('readonly', false);
            
            // 상품가격 커스텀 키기
            // $('#it_price_wrapper').hide();
            $('#custom_it_price_wrapper').show();
            
            // 파트너, 사업소, 우수가 끄기
            $('#chk_partner_price').prop('checked', false);
            $('#chk_dealer_price').prop('checked', false);
            $('#chk_dealer2_price').prop('checked', false);

            $('.special_price_tr').hide();

            // 상품 커스텀 입력 가격 복구
            $('#it_price').val($('#custom_item_price_input').data('price-num'));
            
            // 옵션 커스텀 입력 가격 복구
            var beforeCustomOptionPrice;
            var optionType;
            var calculatedPrice;

            $("input.io_price").each(function (i, v) {
                beforeCustomOptionPrice = $(v).siblings('.io_price_before_custom').val();
                optionType = $(v).siblings('input[name^=io_type]').val();
                $(v).val(beforeCustomOptionPrice); // io_price 복구
                if (optionType === 0) {
                    calculatedPrice = parseInt(itemPrice) + parseInt(beforeCustomOptionPrice);
                } else {
                    calculatedPrice = beforeCustomOptionPrice;
                }

                $(v).parent().find('input.option-price').val(calculatedPrice);
                $(v).parent().find('input.option-price').trigger('keyup');
            })
            
        } else {
            $('.option-price').prop('readonly', true);
            $('.special_price_tr').show();

            // 상품가격 커스텀 끄기
            // $('#it_price_wrapper').show();
            $('#custom_it_price_wrapper').hide();
            
            // 상품 원래 가격 복구
            $('#it_price').val($('#it_price_origin').val());
            
            // 옵션 원래 가격 복구
            var originOptionPrice;
            var optionType;
            var calculatedPrice;
            
            $("input.io_price").each(function (i, v) {
                originOptionPrice = $(v).siblings('.io_price_origin').val();
                optionType = $(v).siblings('input[name^=io_type]').val();
                $(v).val(originOptionPrice); // io_price 복구
                
                if (optionType === 0) {
                    calculatedPrice = parseInt(itemPrice) + parseInt(originOptionPrice);
                } else {
                    calculatedPrice = originOptionPrice;
                }
                
                $(v).parent().find('input.option-price').val(calculatedPrice);
                $(v).parent().find('input.option-price').trigger('keyup');
            })
        }
        price_calculate();
    }
    
    $(function () {
        // $('#custom_it_price_wrapper *').click(function (e) {
        //     e.preventDefault(); // 하이퍼링크 방지
        // });
        
        toggleOptionCustom($('#chk_custom_price'));
    })
</script>

</body>
</html>