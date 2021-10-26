<?php
// $sub_menu = '400620';
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');

// auth_check($auth[$sub_menu], "w");

$title = '재고등록';
include_once('./pop.head.php');

$warehouse_list = get_warehouses();
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
</style>
<form name="foption" class="form" role="form" method="post" action="./pop.stock.add_result.php" onsubmit="return formcheck(this);">
<div id="pop_order_add" class="admin_popup admin_popup_padding">
    <h4 class="h4_header"><?php echo $title; ?></h4>
    <div class="pop_order_add_item">
        <div class="header">
            <h5 class="h5_header">상품정보</h5>
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
                <col width="15%" />
                <col width="10%" />
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
                        창고
                    </th>
                    <th>
                        메모
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
                    <select name="wh_name[]">
                        <?php foreach($warehouse_list as $warehouse) { ?>
                        <option value="<?=$warehouse?>"><?=$warehouse?></option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <input type="text" name="ws_memo[]" class="frm_input">
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

// 기본 설정
var mb_level = 3;
var item_sale_obj = {};

function formcheck(f) {
    var val, io_type, result = true;

    $("input[name^=qty]").each(function(index) {
        val = $(this).val();

        if(parseInt(val.replace(/[^0-9]/g, "")) === 0) {
            alert("수량을 입력해 주십시오.");
            result = false;
            return false;
        }
    });

    if(!result) {
        return false;
    }

    if (loading) {
        alert('재고등록 중 입니다.');
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

            // option
            if (obj.options.length) {
                var option_html = "<select name=\"io_id[]\">";
                for(var i = 0; i<obj.options.length; i++) {
                    option_html += "<option value=\"" + obj.options[i]['io_id'] + "\">" + obj.options[i]['io_id'].replace(//gi, " > ") + "</option>";
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

            // 기본 출고창고
            $(parent).find('select[name="wh_name[]"]').val(obj.it_warehousing_warehouse);
            $(parent).find('input[name="qty[]"]').val(1);

            if ($(parent).index() + 1 >= $('.pop_order_add_item_table tbody tr').length) {
                $('.add_cart').click();
            }
        });
    }

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
        $('#hd', parent.document).css('z-index', 10);
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
            $(this).closest('tr').find('select[name="wh_name[]"]').focus();
        }
    });

    $(document).on('keypress', 'select[name="wh_name[]"]', function(e) {
        var code = e.keyCode || e.which;
        if(code === 13) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('tr').find('input[name="ws_memo[]"]').focus();
        }
    });
    
    $(document).on('keypress', 'input[name="ws_memo[]"]', function(e) {
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

    //input 변경시 스타일 적용
    $(document).on('input propertychange paste', 'input[name="qty[]"]', function() {
        var input = $(this).val();

        input = input.replace(/[\D\s\._\-]+/g, "");

        if(input !== '') {
            input = input ? parseInt( input, 10 ) : 0;
            $(this).val(input.toLocaleString());
        } else {
            $(this).val('');
        }
    });

    // 초기
    $('.add_cart').click();
    $('.add_cart').click();
    $('.pop_order_add_item_table tbody tr').eq(0).find('.item_flexdatalist').focus();
});

</script>

</body>
</html>