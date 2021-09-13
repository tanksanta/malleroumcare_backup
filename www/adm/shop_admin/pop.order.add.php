<?php
// $sub_menu = '400400';
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');

// auth_check($auth[$sub_menu], "w");

$title = '주문서 생성';
include_once('./pop.head.php');
?>
<style>
.flexdatalist-results li {
    font-size:12px;
}

.flexdatalist-results li .item-it_price:after {
    content: '원';
}
</style>
<form name="foption" class="form" role="form" method="post" action="./pop.order.add_result.php" onsubmit="return formcheck(this);">
<div id="pop_order_add" class="admin_popup admin_popup_padding">
    <h4 class="h4_header"><?php echo $title; ?></h4>
    <div class="info">
        <table>
            <tr>
                <th>주문일시</th>
                <td>
                    <div style="position:relative;">
                        <input type="text" name="od_datetime_date" value="" class="frm_input datepicker" style="min-width: 100px;width: 100px;">
                        <input type="text" name="od_datetime_time" value="" class="frm_input timepicker" style="min-width: 50px;width: 95px;">
                    </div>
                </td>
            </tr>
            <tr>
                <th>사업소정보</th>
                <td>
                    <div>
                        <input type="text" name="mb_id" value=""class="frm_input mb_id_flexdatalist" style="width: 300px;">
                        <span id="mb_id_flexdatalist_result">
                            사업소를 검색하세요.
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
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
                <col width="9%" />
                <col width="10%" />
                <col width="10%" />
                <col width="7%" />
                <col width="20%" />
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
            </tbody>
        </table>
    </div>

    <table class="add_item_html" style="display:none;">
        <tbody>
            <tr>
                <td class="no">
                    <span class="index">1</span>
                    <input type="hidden" name="it_id[]">
                    <input type="hidden" name="price[]" class="price">
                </td>
                <td>
                    <input type="text" name="it_name[]" class="frm_input item_flexdatalist">
                </td>
                <td>
                    <div class="it_option">
                        -
                    </div>
                </td>
                <td>
                    <input type="number" name="qty[]" class="frm_input" value="1">
                </td>
                <td>
                    <input type="number" name="it_price[]" class="frm_input" value="0">
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
            <input type="submit" value="생성 (F8)" />
        </div>
    </div>
</div>
</form>

<script>

var loading = false;

function formcheck(f) {
    var val, io_type, result = true;

    if (!$("input[name^=mb_id]").val()) {
        alert("사업소를 입력하세요.");
        result = false;
        return false;
    }

    $("input[name^=qty]").each(function(index) {
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
    });

    
    $("input[name^=it_price]").each(function(index) {
        val = $(this).val();

        if(val.length < 1) {
            alert("단가를 입력해 주십시오.");
            result = false;
            return false;
        }

        if(val.replace(/[0-9]/g, "").length > 0) {
            alert("단가는 숫자로 입력해 주십시오.");
            result = false;
            return false;
        }

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
        alert('주문서 생성중입니다.');
        return false;
    }
    
    loading = true;
    return true;
}

$(function() {
    function add_flexdatalist(node) {
        $(node).flexdatalist({
            minLength: 1,
            url: './ajax.get_item.php',
            cache: true, // cache
            searchContain: true, // %검색어%
            noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
            selectionRequired: true,
            focusFirstResult: true,
            searchIn: ["it_name","it_model","id"],
            visibleCallback: function($li, item, options) {
                var $item = {};
                $item = $('<span>')
                    .html(item.it_name + " (" + item.it_price + "원)");

                $item.appendTo($li);
                return $li;
            },
        }).on("select:flexdatalist",function(event, obj, options){
            var parent = $(this).closest('tr');

            // it_id
            $(parent).find('input[name="it_id[]"]').val(obj.id);

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
                $(parent).find('.it_option').text('-');
                $(parent).find('input[name="qty[]"]').focus();
            }

            $(parent).find('input[name="qty[]"]').val(1);
            $(parent).find('input[name="it_price[]"]').val(it_price);

            // 공급가액, 부가세
            $(parent).find('.basic_price').text(addComma(Math.round(it_price / 1.1)) + "원");
            $(parent).find('.tax_price').text(addComma(Math.round(it_price / 11)) + "원");

            // 기본가격 저장
            $(parent).find('.price').val(obj.it_price);

            if ($(parent).index() + 1 >= $('.pop_order_add_item_table tbody tr').length) {
                $('.add_cart').click();
            }
        });
    }

    $('.datepicker').datetimepicker({
        locale: 'kr',
        format: 'YYYY-MM-DD',
        defaultDate: new Date(),
    });
    $('.timepicker').datetimepicker({
        locale: 'kr',
        format: 'hh:mm',
        defaultDate: new Date(),

    });
    
    $('.mb_id_flexdatalist').flexdatalist({
        minLength: 1,
        url: '/adm/ajax.get_mb_id.php',
        cache: true, // cache
        searchContain: true, // %검색어%
        noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
        visibleProperties: ["mb_name", "mb_id"],
        searchIn: ["mb_id","mb_name","mb_nick","mb_tel", "mb_hp","mb_email"],
        selectionRequired: true,
        focusFirstResult: true,
    }).on("select:flexdatalist",function(event, obj, options){
        $('#mb_id_flexdatalist_result').text(
            obj.mb_name + "(" + obj.mb_id + ")" + " / HP: " + obj.mb_hp + " / Tel: " + obj.mb_tel
        )
        $('.item_flexdatalist').first().next().focus();
    });

    $(document).on("click", ".delete_cart", function () {
        var parent = $(this).closest('tr').remove();
        
        $('.pop_order_add_item_table tbody tr').each(function(index) {
            $(this).find('.index').text(index + 1)
        })
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
        $('.pop_order_add_item_table tbody').html('');

        $('.add_cart').click();
        $('.add_cart').click();
    });
    
    $(document).on("click", ".order_add_close", function (e) {
        e.preventDefault();

        $('#popup_order_add', parent.document).hide();
        $('#hd').css('z-index', 10);
    });

    $(document).on("change keyup paste", ".it_option select, input[name='qty[]'], input[name='it_price[]']", function (e) {
        var parent = $(this).closest('tr');

        // var io_price = $(this).find('option:selected').data('price');
        var io_price = $(parent).find('.it_option option:selected').data('price');
        var price = $(parent).find('.price').val();
        var it_price = parseInt(price || 0) + parseInt(io_price || 0);
        var qty = $(parent).find('input[name="qty[]"]').val();

        // 단가
        if ($(this).attr('name') === 'it_price[]') {
            it_price = $(parent).find('input[name="it_price[]"]').val();
        }
        $(parent).find('input[name="it_price[]"]').val(it_price || 0);

        // 공급가액, 부가세
        $(parent).find('.basic_price').text(addComma(Math.round(it_price * qty / 1.1) || 0) + "원");
        $(parent).find('.tax_price').text(addComma(Math.round(it_price * qty / 11) || 0) + "원");
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
    
    $(document).keypress(function(e) {
        if((e.which || e.keyCode) == 119) { // F8
            $('#popup_buttom input[type="submit"]').click();
        }
    });

    // 초기
    $('.mb_id_flexdatalist').focus();
    $('.add_cart').click();
    $('.add_cart').click();
});

</script>

</body>
</html>