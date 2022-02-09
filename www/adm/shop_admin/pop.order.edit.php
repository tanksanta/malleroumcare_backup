<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');

auth_check($auth[$sub_menu], "w");

$title = '주문서 수정';
include_once('./pop.head.php');

$od_id = get_search_string($_GET['od_id']);
$od = sql_fetch(" select * from g5_shop_order where od_id = '$od_id' ");
if(!$od['od_id'])
    alert('존재하지 않는 주문입니다.');
$carts = get_carts_by_od_id($od_id);
$mb = get_member($od['mb_id']);

?>
<style>
.flexdatalist-results li {
    font-size:12px;
}
.flexdatalist-results li.mb_id {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.flexdatalist-results span:not(:first-child):not(.highlight) {
    font-size: 80%;
    color: rgba(0, 0, 0, 0.50);
}

.flexdatalist-results li .item-it_price:after {
    content: '원';
}
td {
    position: relative;
}
tr.strikeout {
    background: #dcdcdc;
}
tr.strikeout td:before {
    content: " ";
    position: absolute;
    top: 50%;
    left: 0;
    border-bottom: 1px solid #333;
    width: 100%;
}
</style>
<form name="foption" class="form" role="form" method="post" action="./pop.order.edit_result.php" onsubmit="return formcheck(this);" autocomplete="off">
<input type="hidden" name="od_id" value="<?=$od_id?>">
<input type="hidden" name="od_send_cost" value="<?=$od['od_send_cost']?>">
<div id="pop_order_add" class="admin_popup admin_popup_padding">
    <h4 class="h4_header"><?php echo $title; ?></h4>
    <div class="pop_order_add_item">
        <div class="header">
            <h5 class="h5_header">주문정보</h5>
            <div class="btns">
                <input type="button" class="shbtn lineblue add_cart" value="추가" />
                <input type="button" class="shbtn clear_cart" value="다시작성" />
            </div>
        </div>
        <table class="pop_order_add_item_table">
            <colgroup>
                <col width="5%" />
                <col />
                <col width="15%" />
                <col width="7%" />
                <col width="10%" />
                <col width="8%" />
                <col width="8%" />
                <col width="13%" />
                <col width="30px" />
            </colgroup>
            <thead>
                <tr>
                    <th>
                        No.
                    </th>
                    <th>
                        상품명
                    </th>
                    <th>
                        옵션명
                    </th>
                    <th>
                        수량
                    </th>
                    <th>
                        단가(VAT포함)
                    </th>
                    <th>
                        공급가액
                    </th>
                    <th>
                        부가세
                    </th>
                    <th>
                        요청사항
                    </th>
                    <th>
                        삭제
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $index = 0;
                $item_sale_obj = [];
                foreach($carts as $cart) {
                    $it = sql_fetch(" select * from g5_shop_item where it_id = '{$cart['it_id']}' ");

                    $item_sale_obj[$it['it_id']] = [
                        'it_sale_cnt' => [
                            $it['it_sale_cnt'],
                            $it['it_sale_cnt_02'],
                            $it['it_sale_cnt_03'],
                            $it['it_sale_cnt_04'],
                            $it['it_sale_cnt_05'],
                        ],
                        'it_sale_percent' => [
                            $it['it_sale_percent'],
                            $it['it_sale_percent_02'],
                            $it['it_sale_percent_03'],
                            $it['it_sale_percent_04'],
                            $it['it_sale_percent_05'],
                        ],
                        'it_sale_percent_great' => [
                            $it['it_sale_percent_great'],
                            $it['it_sale_percent_great_02'],
                            $it['it_sale_percent_great_03'],
                            $it['it_sale_percent_great_04'],
                            $it['it_sale_percent_great_05']
                        ],
                    ];

                    $option_sql = "SELECT *
                    FROM
                        {$g5['g5_shop_item_option_table']}
                    WHERE
                        it_id = '{$cart['it_id']}'
                        and io_type = 0 -- 선택옵션
                    ORDER BY
                        io_no ASC
                    ";

                    $option_result = sql_query($option_sql);
                    $options = [];
                    while ($option_row = sql_fetch_array($option_result)) {
                        $options[] = $option_row;
                    }
                    foreach($cart['options'] as $opt) {
                        // 공급가액
                        $opt["basic_price"] = $opt['ct_price_stotal'];
                        // 부가세
                        $opt["tax_price"] = 0;
                        if($opt['it_taxInfo'] != "영세" ) {
                            // 공급가액
                            $opt["basic_price"] = round($opt['ct_price_stotal'] / 1.1);
                            // 부가세
                            $opt["tax_price"] = round($opt['ct_price_stotal'] / 11);
                        }
                        // 단가 역산
                        $it_price = $opt['ct_price_stotal'] ? @round($opt['ct_price_stotal'] / ($opt["ct_qty"] - $opt["ct_stock_qty"])) : 0;
                ?>
                <tr>
                    <td class="no">
                        <span class="index"><?= (++$index) ?></span>
                        <input type="hidden" name="delete[]" value="0">
                        <input type="hidden" name="ct_id[]" value="<?=$opt['ct_id']?>">
                        <input type="hidden" name="it_id[]" value="<?=$opt['it_id']?>">
                        <input type="hidden" name="io_type[]" value="<?=$opt['io_type']?>">
                        <input type="hidden" name="price[]" class="price" value="<?=$opt['opt_price']?>">
                        <input type="hidden" name="it_sc_type[]" value="<?=$it['it_sc_type']?>">
                        <input type="hidden" name="it_sc_price[]" value="<?=$it['it_sc_price']?>">
                        <input type="hidden" name="it_even_odd[]" value="<?=$it['it_even_odd']?>">
                        <input type="hidden" name="it_even_odd_price[]" value="<?=$it['it_even_odd_price']?>">
                    </td>
                    <td>
                        <?php
                            if($opt['io_type'] == '1') {
                                echo '<input type="hidden" name="it_name[]" class="frm_input" value="' . $opt['it_name'] . '">';
                                echo '[추가옵션] '. $opt['ct_option'];
                            } else {
                                echo '<input type="text" name="it_name[]" class="frm_input item_flexdatalist" value="' . $opt['it_name'] . '">';
                            }
                        ?>
                    </td>
                    <td>
                        <div class="it_option">
                            <?php
                            if($options && $opt['io_type'] != '1') {
                                echo '<select name="io_id[]">';
                                foreach($options as $option) {
                                    echo '<option data-price="' . $option['io_price'] . '" value="' . $option['io_id'] . '" ' . get_selected($opt['io_id'], $option['io_id']) . '>' . str_replace(chr(30), ' > ', $option['io_id']) . '</option>';
                                }
                                echo '</select>';
                            } else {
                                echo '
                                    <input type="hidden" name="io_id[]" value="'. $opt['io_id'] . '">
                                    -
                                ';
                            }
                            ?>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="qty[]" class="frm_input" value="<?=$opt['ct_qty']?>">
                    </td>
                    <td>
                        <input type="text" name="it_price[]" class="frm_input" value="<?=$it_price?>">
                    </td>
                    <td class="basic_price">
                        <?=number_format($opt["basic_price"])?>원
                    </td>
                    <td class="tax_price">
                    <?=number_format($opt["tax_price"])?>원
                    </td>
                    <td>
                        <input type="text" name="memo[]" class="frm_input" value="<?=$opt['prodMemo']?>">
                    </td>
                    <td>
                        <input type="button" class="shbtn small delete_cart" value="삭제" />
                    </td>
                </tr>
                <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <table class="add_item_html" style="display:none;">
        <tbody>
            <tr>
                <td class="no">
                    <span class="index">1</span>
                    <input type="hidden" name="ct_id[]">
                    <input type="hidden" name="it_id[]">
                    <input type="hidden" name="price[]" class="price">
                </td>
                <td>
                    <input type="text" name="it_name[]" class="frm_input item_flexdatalist">
                </td>
                <td>
                    <div class="it_option">
                        <input type="hidden" name="io_id[]">
                        -
                    </div>
                </td>
                <td>
                    <input type="text" name="qty[]" class="frm_input" value="1">
                </td>
                <td>
                    <input type="text" name="it_price[]" class="frm_input" value="0">
                </td>
                <td class="basic_price">
                    0원
                </td>
                <td class="tax_price">
                    0원
                </td>
                <td>
                    <input type="text" name="memo[]" class="frm_input">
                </td>
                <td>
                    <input type="button" class="shbtn small delete_cart" value="삭제" />
                </td>
            </tr>
        </tbody>
    </table>

    <div id="popup_buttom">
        <div class="addoptionbuttons">
            <a href='#' class="order_add_close">
                취소
            </a>
            <input type="submit" value="수정 (F8)" />
        </div>
    </div>
</div>
</form>

<script>


// 주문금액계산
function calculate_order_price() {
  var $li = $('.pop_order_add_item_table tbody tr');

  var order_price = 0;
  var delivery_total = 0;
  var free_delivery = true;
  var odd_qty = 0;
  var odd_price = 0;
  var even_qty = 0;
  var even_price = 0;
  var charge_price = 0; // 유료배송비
  $li.each(function() {
    var it_id = $(this).find('input[name="it_id[]"]').val();
    var it_price = parseInt ( $(this).find('input[name="price[]"]').val() || 0 );
    var io_price = parseInt( $(this).find('select[name="io_id[]"] option:selected').data('price') || 0 );
    var ct_qty = parseInt( $(this).find('input[name="qty[]"]').val() || 0 );
    var it_sc_type = parseInt( $(this).find('input[name="it_sc_type[]"]').val() || 0 );
    var it_sc_price = parseInt( $(this).find('input[name="it_sc_price[]"]').val() || 0 );
    var it_even_odd = parseInt( $(this).find('input[name="it_even_odd[]"]').val() || 0 );
    var it_even_odd_price = parseInt( $(this).find('input[name="it_even_odd_price[]"]').val() || 0 );

    if(it_sc_type !== 1 && it_sc_type !== 5 && it_sc_type !== 3) {
      // 무료배송이 아닌 상품이 하나라도 있으면 유료배송
      free_delivery = false;
    }

    // 묶음할인 적용
    var sale_qty = 0;
    for(var i = 0; i < $li.length; i++) {
      var this_it_id = $($li).eq(i).find('input[name="it_id[]"]').val();
      if(this_it_id !== it_id) continue;

      var this_qty = parseInt( $li.eq(i).find('input[name="ct_qty[]"]').val() );
      if( this_qty > 0 ) {
        sale_qty += this_qty;
      }
    }
    var it_sale_cnt = 0;
    if(item_sale_obj[it_id] && item_sale_obj[it_id].it_sale_cnt) {
      for(var i = 0; i < item_sale_obj[it_id].it_sale_cnt.length; i++) {
        var sale_cnt = parseInt(item_sale_obj[it_id].it_sale_cnt[i]);
        if(sale_qty >= sale_cnt && sale_cnt > it_sale_cnt) {
          it_sale_cnt = sale_cnt;
          it_price = parseInt( mb_level === 4 ? item_sale_obj[it_id].it_sale_percent_great[i] : item_sale_obj[it_id].it_sale_percent[i] );
        }
      }
    }

    var ct_price = ( it_price + io_price ) * ct_qty;
    order_price += ct_price;

    // 유료 배송
    if(it_sc_type === 3) {
      charge_price += (it_sc_price * ct_qty);
    }

    // 홀수/짝수 배송
    if(it_sc_type === 5) {
      if(it_even_odd == 0) {
        // 홀수 배송

        // 홀수 배송중 가장 배송비가 높은 상품의 배송비를 홀수 배송비로 적용
        if(odd_price < it_even_odd_price)
          odd_price = it_even_odd_price;
        
        odd_qty += ct_qty;
      } else if(it_even_odd == 1) {
        // 짝수 배송

        // 짝수 배송중 가장 배송비가 높은 상품의 배송비를 짝수 배송비로 적용
        if(even_price < it_even_odd_price)
          even_price = it_even_odd_price;
        
        even_qty += ct_qty;
      }
    } else {
      delivery_total += ct_price;
    }
  });

  var delivery_price = 0;
  if(delivery_total < 100000 && !free_delivery) {
    // 주문금액 10만원 미만에 무료배송상품이 아닌 상품이 있는 경우 배송비
    delivery_price = 3300;
  }

  // 유료 배송비 적용
  delivery_price += charge_price;

  if(odd_qty > 0 && odd_qty % 2 === 1) {
    // 홀수 배송비 적용
    delivery_price += odd_price;
  }
  if(even_qty > 0 && even_qty % 2 === 0) {
    // 짝수 배송비 적용
    delivery_price += even_price;
  }

  // 배송비
//   $('#delivery_price').text(number_format(delivery_price));
  $('input[name="od_send_cost"]').val(delivery_price);
//   console.log($('input[name="od_send_cost"]').val());
}

var loading = false;

// 기본 설정
var mb_level = <?=($mb['mb_level'] ?: 3)?>;
var mb_id = '<?=$mb['mb_id']?>';
var item_sale_obj = <?php echo $item_sale_obj ? json_encode($item_sale_obj) : '{}' ?>;

function formcheck(f) {
    var val, io_type, result = true;

    $("input[name^=qty]").each(function(index) {
        val = $(this).val();

        if(parseInt(val.replace(/[^0-9]/g, "")) < 1) {
            alert("수량은 1이상 입력해 주십시오.");
            result = false;
            return false;
        }
    });

    
    $("input[name^=it_price]").each(function(index) {
        val = $(this).val();

        if(parseInt(val.replace(/[^0-9]/g, "")) < 0) {
            alert("단가는 0이상 입력해 주십시오.");
            result = false;
            return false;
        }
    });

    if(!result) {
        return false;
    }

    if (loading) {
        alert('주문서 수정중입니다.');
        return false;
    }

    calculate_order_price();

    loading = true;
    return true;
}

$(function() {
    // 기본값 저장
    var default_tbody = $('.pop_order_add_item_table tbody').html();

    function add_flexdatalist(node) {
        $(node).flexdatalist({
            minLength: 1,
            url: './ajax.get_item.php?mb_id=' + mb_id,
            cache: false, // cache
            searchContain: true, // %검색어%
            noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
            selectionRequired: true,
            focusFirstResult: true,
            searchIn: ["it_name","it_model","id", "it_name_no_space"],
            visibleCallback: function($li, item, options) {
                var $item = {};
                $item = $('<span>')
                    .html("[" + item.gubun + "] " + item.it_name + " (" + item.it_price + "원)");

                $item.appendTo($li);
                return $li;
            },
        }).on("select:flexdatalist",function(event, obj, options){
            var parent = $(this).closest('tr');

            // it_id
            $(parent).find('input[name="it_id[]"]').val(obj.id);

            // 우수사업소 할인 가격 적용
            if(mb_level == 4 && parseInt(obj.it_price_dealer2) > 0)
                obj.it_price = obj.it_price_dealer2;

            // option
            var it_price = parseInt(obj.it_price);
            if (obj.options.length) {
                var option_html = "<select name=\"io_id[]\">";
                for(var i = 0; i<obj.options.length; i++) {
                    if (i === 0) {
                        it_price += parseInt(obj.options[i]['io_price']);
                    }
                    option_html += "<option data\-price=\"" + obj.options[i]['io_price'] + "\" value=\"" + obj.options[i]['io_id'] + "\">" + obj.options[i]['io_id'].replace(//gi, " > ") + "</option>";
                }
                option_html += "</select>";
                $(parent).find('.it_option').html(option_html);
                setTimeout(function() {
                    $(parent).find('.it_option select').focus();
                }, 10);
            } else {
                var option_html = "<input type=\"hidden\" name=\"io_id[]\" value=\"\">";
                $(parent).find('.it_option').html(option_html).append('-');
                $(parent).find('input[name="qty[]"]').focus();
            }

            $(parent).find('input[name="qty[]"]').val(1);
            $(parent).find('input[name="it_price[]"]').val(addComma(it_price));

            // 공급가액, 부가세
            $(parent).find('.basic_price').text(addComma(Math.round(it_price / 1.1)) + "원");
            $(parent).find('.tax_price').text(addComma(Math.round(it_price / 11)) + "원");

            // 기본가격 저장
            $(parent).find('.price').val(obj.it_price);

            // 묶음 할인 저장
            item_sale_obj[obj.id] = {
                it_sale_cnt: [
                    obj.it_sale_cnt,
                    obj.it_sale_cnt_02,
                    obj.it_sale_cnt_03,
                    obj.it_sale_cnt_04,
                    obj.it_sale_cnt_05,
                ],
                it_sale_percent: [
                    obj.it_sale_percent,
                    obj.it_sale_percent_02,
                    obj.it_sale_percent_03,
                    obj.it_sale_percent_04,
                    obj.it_sale_percent_05,
                ],
                it_sale_percent_great: [
                    obj.it_sale_percent_great,
                    obj.it_sale_percent_great_02,
                    obj.it_sale_percent_great_03,
                    obj.it_sale_percent_great_04,
                    obj.it_sale_percent_great_05
                ],
            }

            if ($(parent).index() + 1 >= $('.pop_order_add_item_table tbody tr').length) {
                $('.add_cart').click();
            }
        });
    }

    $(document).on("click", ".delete_cart", function () {
        var parent = $(this).closest('tr');
        if(parent.find('input[name="ct_id[]"]').val() != '') {
            <?php if($od['od_penId']) { ?>
            var total = <?=($index ?: 0)?>;
            if($('input[name="delete[]"][value="1"]').length >= total - 1)
                return alert('주문의 모든 상품을 삭제할 수 없습니다.');
            <?php } ?>
            parent.find('input[name="delete[]"]').val('1');
            parent.addClass('strikeout');
            parent.find('input[type="text"], select').prop('readonly', true).css({'pointer-events': 'none'});
            $(this).css('visibility', 'hidden');
        } else {
            parent.remove();
            $('.pop_order_add_item_table tbody tr').each(function(index) {
                $(this).find('.index').text(index + 1)
            });
        }
    });
    
    $(document).on("click", ".add_cart", function () {
        var html_node = $('.add_item_html tbody');
        $('.pop_order_add_item_table tbody').append(
            $(html_node).html()
        );
        
        $('.pop_order_add_item_table tbody tr').each(function(index) {
            $(this).find('.index').text(index + 1)
        })

        add_flexdatalist(
            $('.pop_order_add_item_table tbody').find('tr').last().find('.item_flexdatalist')
        );
    });

    $(document).on("click", ".clear_cart", function () {
        $('.pop_order_add_item_table tbody').html(default_tbody);
    });

    $(document).on("click", ".order_add_close", function (e) {
        e.preventDefault();

        $('#popup_order_add', parent.document).hide();
        $('#hd', parent.document).css('z-index', 10);
    });

    $(document).on("change keyup paste", ".it_option select, input[name='qty[]'], input[name='it_price[]']", function (e) {
        var parent = $(this).closest('tr');

        // var io_price = $(this).find('option:selected').data('price');
        var io_price = $(parent).find('.it_option option:selected').data('price');
        var price = $(parent).find('.price').val();
        var it_price = parseInt(price || 0) + parseInt(io_price || 0);
        it_price = it_price ? parseInt( it_price, 10 ) : 0;
        var qty = $(parent).find('input[name="qty[]"]').val().replace(/[\D\s\._\-]+/g, "");
        qty = qty ? parseInt( qty, 10 ) : 0;

        if ($(this).attr('name') === 'qty[]' || $(this).attr('name') === 'io_id[]') {

            var it_id = $(parent).find('input[name="it_id[]"]').val();
            var it_sale_cnt = 0;

            // 묶음 할인
            var sale_qty = 0;
            var targets = [];
            $('.pop_order_add_item_table input[name="it_id[]"]').each(function() {
                var this_parent = $(this).closest('tr');
                var this_io_type = $(this_parent).find('input[name="io_type[]"]').val();
                if($(this).val() == it_id && this_io_type != '1') {
                    var this_qty = $(this_parent).find('input[name="qty[]"]').val().replace(/[\D\s\._\-]+/g, "");
                    sale_qty += parseInt(this_qty);
                    targets.push({
                        it_price: $(this_parent).find('input[name="it_price[]"]'),
                        qty: $(this_parent).find('input[name="qty[]"]'),
                        basic_price: $(this_parent).find('.basic_price'),
                        tax_price: $(this_parent).find('.tax_price'),
                    });
                }
            });
            if (item_sale_obj[it_id]['it_sale_cnt']) {
                for(var sale_cnt = 0; sale_cnt < item_sale_obj[it_id]['it_sale_cnt'].length; sale_cnt++){
                    var temp = parseInt(item_sale_obj[it_id]['it_sale_cnt'][sale_cnt])
                    if(temp <= sale_qty) {
                        if(it_sale_cnt < temp) {
                            it_sale_cnt = temp;
                            it_price = mb_level == 4 ? item_sale_obj[it_id]['it_sale_percent_great'][sale_cnt] : item_sale_obj[it_id]['it_sale_percent'][sale_cnt];
                        }
                    }
                }
            }
            
            for(var i = 0; i < targets.length; i++) {
                var target = targets[i];
                if (parseInt(it_price, 10) !== parseInt(target.it_price.val().replace(/[\D\s\._\-]+/g, ""), 10)) {
                    (function(it_price) {
                        it_price.animate({'opacity': 0} ,50 , function () {
                            it_price.animate({'opacity': 1}, 50);
                        });
                    })(target.it_price);

                    target.it_price.val(addComma(it_price || 0));

                    // 공급가액, 부가세
                    var target_qty = parseInt(target.qty.val().replace(/[\D\s\._\-]+/g, ""));
                    target.basic_price.text(addComma(Math.round(it_price * target_qty / 1.1) || 0) + "원");
                    target.tax_price.text(addComma(Math.round(it_price * target_qty / 11) || 0) + "원");
                }
            }
        }

        // 단가
        if ($(this).attr('name') === 'it_price[]') {
            it_price = $(parent).find('input[name="it_price[]"]').val().replace(/[\D\s\._\-]+/g, "");
            it_price = it_price ? parseInt( it_price, 10 ) : 0;
        }
        $(parent).find('input[name="it_price[]"]').val(addComma(it_price || 0));

        // 공급가액, 부가세
        $(parent).find('.basic_price').text(addComma(Math.round(it_price * qty / 1.1) || 0) + "원");
        $(parent).find('.tax_price').text(addComma(Math.round(it_price * qty / 11) || 0) + "원");

        calculate_order_price();
    });

    // 선택시 다음
    $(document).on('keypress', '.it_option', function(e) {
        var code = e.keyCode || e.which;
        if(code === 13) {
            e.preventDefault();
            $(this).closest('tr').find('input[name="qty[]"]').focus();
        }
    });

    $(document).on('keypress', 'input[name="qty[]"]', function(e) {
        var code = e.keyCode || e.which;
        if(code === 13) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('tr').find('input[name="it_price[]"]').focus();
        }
    });
    
    $(document).on('keypress', 'input[name="it_price[]"]', function(e) {
        var code = e.keyCode || e.which;
        if(code === 13) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('tr').find('input[name="memo[]"]').focus();
        }
    });
    
    $(document).on('keypress', 'input[name="memo[]"]', function(e) {
        var code = e.keyCode || e.which;
        if(code === 13) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('tr').next().find('.item_flexdatalist').focus();
        }
    });
    
    $(document).on('keypress', '.item_flexdatalist', function(e) {
        var code = e.keyCode || e.which;
        if(code === 13) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
    
    $(document).keydown(function(e) {
        if((e.which || e.keyCode) == 119) { // F8
            $('#popup_buttom input[type="submit"]').click();
        }
    });

    $(document).on('keydown','input',function (e) {
        var input_names = ["flexdatalist-it_name[]", "qty[]", "it_price[]", "memo[]"];
        var name = $(this).attr("name");
        var index = input_names.indexOf(name);
        if (e.which === 39) {
            //right
            if (index != 3) {
                index++;
                if (index == 4) {
                    index = 0;
                    $(this).closest("tr").next("tr").find("input[name='"+input_names[index]+"']").focus();
                }
                else {
                    $(this).closest("tr").find("input[name='"+input_names[index]+"']").focus();
                }
            }
        }
        else if (e.which === 37) {
            //left
            if (index != 3) {
                index--;
                if (index == -1) {
                    index = 3;
                    $(this).closest("tr").prev("tr").find("input[name='"+input_names[index]+"']").focus();
                }
                else {
                    $(this).closest("tr").find("input[name='"+input_names[index]+"']").focus();
                }
            }
        }
        else if (e.which === 38) {
            //up
            if (index > 0) {
                $(this).closest("tr").prev("tr").find("input[name='"+input_names[index]+"']").focus();
            }
        }
        else if (e.which === 40) {
            //down
            if (index > 0) {
                $(this).closest("tr").next("tr").find("input[name='"+input_names[index]+"']").focus();
            }
        }
    });

    //input 변경시 스타일 적용
    $(document).on('input propertychange paste', 'input[name="qty[]"], input[name="it_price[]"]', function() {
        var input = $(this).val();

        input = input.replace(/[\D\s\._\-]+/g, "");

        if(input !== '') {
            input = input ? parseInt( input, 10 ) : 0;
            $(this).val(input.toLocaleString());
        } else {
            $(this).val('');
        }
    });

    $('.pop_order_add_item_table .item_flexdatalist').each(function() {
        add_flexdatalist($(this));
    });

});

</script>

</body>
</html>