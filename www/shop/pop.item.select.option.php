<?php
include_once('./_common.php');

if ($member['mb_type'] !== 'default') {
    alert("사업소 회원만 접근 가능합니다.");
}

// 상품정보
$sql  = " select * from {$g5['g5_shop_item_table']} where it_id = '{$it_id}' ";
$it = sql_fetch($sql);

$attrs = [
    'it_id', 'it_name', 'it_model', 'it_cust_price', 'it_buy_min_qty', 'it_buy_max_qty', 'it_buy_inc_qty',
    'ca_id', 'it_delivery_cnt', 'it_sc_type', 'it_sc_price', 'it_even_odd', 'it_even_odd_price',
    'it_sale_cnt', 'it_sale_cnt_02', 'it_sale_cnt_03', 'it_sale_cnt_04', 'it_sale_cnt_05',
    'it_sale_percent', 'it_sale_percent_02', 'it_sale_percent_03', 'it_sale_percent_04', 'it_sale_percent_05',
    'it_sale_percent_great', 'it_sale_percent_great_02', 'it_sale_percent_great_03', 'it_sale_percent_great_04', 'it_sale_percent_great_05',
    'it_type1', 'it_type2', 'it_type3', 'it_type4', 'it_type5', 'it_type6', 'it_type7', 'it_type8', 'it_type9', 'it_type10',
    'it_expected_warehousing_date'
];

$data = [];
foreach ($attrs as $attr) {
    $data[$attr] = $it[$attr];
}
if($member['mb_level'] == 4 && $it['it_price_dealer2']) {
    $data['it_price'] = $it['it_price_dealer2'];
} else {
    $data['it_price'] = $it['it_price'];
}
// 사업소별 판매가
$entprice = sql_fetch(" select it_price from g5_shop_item_entprice where it_id = '{$it['it_id']}' and mb_id = '{$member['mb_id']}' ");
if($entprice['it_price']) {
    $data['it_sale_cnt'] = 0;
    $data['it_sale_cnt_02'] = 0;
    $data['it_sale_cnt_03'] = 0;
    $data['it_sale_cnt_04'] = 0;
    $data['it_sale_cnt_05'] = 0;
    $data['it_price'] = $entprice['it_price'];
}
$data['it_img'] = $it['it_img1'];
$option_sql = "SELECT *
    FROM
        {$g5['g5_shop_item_option_table']}
    WHERE
        it_id = '$it_id'
        AND io_type = 0 -- 선택옵션
        AND io_use = 1 -- 사용중 옵션
    ORDER BY
        io_no ASC
";
$option_result = sql_query($option_sql);

$data['options'] = [];
while ($option_row = sql_fetch_array($option_result)) {
    $data['options'][] = $option_row;
}

$gubun = $cate_gubun_table[substr($it['ca_id'], 0, 2)];
$gubun_text = '판매';
if($gubun == '01') $gubun_text = '대여';
else if($gubun == '02') $gubun_text = '비급여';

$data['gubun'] = $gubun_text;

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

        $_ct_qty = 1;
        if( $data['it_buy_min_qty'] > $_ct_qty ) $_ct_qty = $data['it_buy_min_qty'];
        if( $data['it_buy_inc_qty'] > $data['it_buy_min_qty']  ) $_ct_qty = $data['it_buy_inc_qty'];

        $io[$i] = $row;
        $io[$i]['ct_qty'] = $_ct_qty;
        $io[$i]['min_qty'] = $data['it_buy_min_qty'];
        $io[$i]['max_qty'] = $data['it_buy_max_qty'];
        $io[$i]['buy_inc_qty'] = $data['it_buy_inc_qty'];
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

$title = '보유재고 등록 > 옵션선택';
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta name="viewport" content="initial-scale=1.0,user-scalable=yes,maximum-scale=2,width=device-width" /><meta http-equiv="imagetoolbar" content="no">
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css?v=<?php echo time(); ?>">
<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-ui.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-migrate-1.2.1.min.js"></script>
<!--<script src="<?php echo G5_URL;?>/skin/apms/order/basic/shop.js"></script>-->
<script src="<?php echo G5_JS_URL;?>/common.js"></script>
<script src="<?php echo G5_ADMIN_URL;?>/shop_admin/js/shop.js?v=<?php echo time(); ?>"></script>
</head>
<style>
#pop_add_item .itm-option-group > .option-price-wrapper,
#pop_add_item .content .list-group-item .row .col-sm-7 .it_opt_prc {
    display:none !important;
}
#pop_add_item .content .addoptionbuttons a {
    width: 40%;
}
#pop_add_item .content .addoptionbuttons input[type="submit"] {
    width: 60%;
}
#pop_add_item .content .item_options {
    float: none;
    width:100%;
    border-right: none;
    padding-right: 0;
}
.option-barcode { display: none !important; }
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
        <form name="foption" class="form" role="form" method="post" onsubmit="return formcheck(this);">
            <div class="item_options">
                <div class="item_info">
                    <a href="./item.php?it_id=<?php echo $it['it_id']; ?>" target="_blank">
                        <?php echo get_it_image($it['it_id'], 50, 50); ?>
                        <p>
                            <?php echo htmlspecialchars2(cut_str($it['it_name'],250, "")); ?>
                            <br/>
                            <span class="model"><?php echo $it['it_model']; ?></span>
                            <br/>
                            <span id="it_price_wrapper" style="display:none">
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
                        <input type="hidden" name="act" value="stockadd">
                        <input type="hidden" name="it_id[]" value="<?php echo $it['it_id']; ?>">
                        <input type="hidden" name="uid" value="<?php echo htmlspecialchars($uid); ?>">
                        <input type="hidden" name="it_msg1[]" value="<?php echo $it['pt_msg1']; ?>">
                        <input type="hidden" name="it_msg2[]" value="<?php echo $it['pt_msg2']; ?>">
                        <input type="hidden" name="it_msg3[]" value="<?php echo $it['pt_msg3']; ?>">
                        <input type="hidden" name="it_buy_min_qty" value="<?php echo $it['it_buy_min_qty']; ?>">
                        <input type="hidden" name="it_buy_max_qty" value="<?php echo $it['it_buy_max_qty']; ?>">
                        <input type="hidden" name="it_buy_inc_qty" value="<?php echo $it['it_buy_inc_qty']; ?>">
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
                                
                                if (empty($io[$i]['io_type'])) $io[$i]['io_type'] = '0';
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
                                                    <div class="input-group-btn"><button type="button" class="it_qty_minus btn btn-black btn-sm"><i class="fa fa-minus-circle fa-lg"></i><span class="sound_only">감소</span></button></div>
                                                    <input type="text" name="ct_qty[<?php echo $it['it_id']; ?>][]" value="<?php echo $io[$i]['ct_qty']; ?>" id="ct_qty_<?php echo $i; ?>" class="form-control input-sm" size="5">
                                                    <div class="input-group-btn-del"><button type="button" class="it_opt_del btn btn-sm btn-lightgray"><i class="fa fa-times-circle fa-lg"></i><span class="sound_only">삭제</span></button></div>
                                                    <div class="input-group-btn"><button type="button" class="it_qty_plus btn btn-black btn-sm"><i class="fa fa-plus-circle fa-lg"></i><span class="sound_only">증가</span></button></div>
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
                        </div>

                        <p></p>
                </div>
            </div>
            <div class="addoptionbuttons">
                <?php
                    $ref = $_GET['ref'];
                    $b_url = './pop.stock.item.add.php';
                    if ($ref == 'select') {
                        $b_url = './pop.item.select.php';
                    }
                ?>
                <a href="<?=$b_url?>">
                    <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/icon_arrow_prev_w.png" />상품선택
                </a>
                <input type="submit" value="확인" />
            </div>
        </form>
    </div>
</div>

<script>
var it_id = '<?php echo $it_id; ?>';
var data = <?php echo json_encode($data); ?>;

$(function() {

  $("select.it_option").addClass("form-control input-sm");
  $("select.it_supply").addClass("form-control input-sm");

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

    var items = [];
    $('.it_opt_list').each(function() {
        var io_id = $(this).find('input[name^="io_id"]').val();
        var ct_qty = $(this).find('input[name^="ct_qty"]').val();
        var item = {
            io_id: io_id,
            ct_qty: ct_qty
        };

        items.push(item);
    });

    window.parent.select_items(data, items);

    return false;
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