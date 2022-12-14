<?php
include_once("./_common.php");

if($member['mb_type'] !== 'default')
    alert('접근할 수 없습니다.');

$g5['title'] = '보유재고 등록';
include_once("./_head.php");

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/simple_stock.css">');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">');
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js"></script>');
add_javascript(G5_POSTCODE_JS, 0);

?>

<section class="wrap">
    <div class="sub_section_tit">간편 보유재고 등록</div>
    <div class="inner">
        <form id="simple_stock" name="fstockform" class="form-horizontal" action="simple_stock_result.php" method="post" onsubmit="return form_submit(this);" onkeydown="if(event.keyCode==13) return false;">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="ss_desc_wr">
                    <p>현재 보유한 재고를 등록해주세요.</p>
                    <p>보유재고를 등록하시면 수급자 계약 시 활용이 가능합니다.</p>
                    <p class="accent">* 이로움에 주문은 접수되지 않으며, 재고가 등록됩니다.</p>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">
                        <strong>등록</strong>
                    </label>
                    <div class="col-sm-8">
                        <span class="total_count form_desc">0건</span>
                    </div>
                </div>
            </div>
            <div class="ss_btn_wr">
                <button type="submit" class="btn_ss_submit">
                    <img src="<?=THEMA_URL?>/assets/img/icon_order.png" alt="">
                    등록하기
                </button>
            </div>
        </div>

        <div class="ss_item_wr">
            <div class="ss_sch_wr">
            <div class="ss_sch_hd">상품정보</div>
            <div class="ipt_ss_sch_wr">
                <img src="<?php echo THEMA_URL; ?>/assets/img/icon_search.png" >
                <input type="text" id="ipt_ss_sch" class="ipt_ss_sch" placeholder="여기에 추가할 상품명을 입력해주세요">
            </div>
            <div class="ss_sch_pop">
                <p>상품명을 입력 후 간편하게 추가할 수 있습니다.<br> 상품명 일부만 입력해도 자동완성됩니다.</p>
                <!-- <p>상품명을 모르시면 '상품검색' 버튼을 눌러주세요.</p>
                <p><button type="button" class="btn_so_sch">상품검색</button></p> -->
            </div>
            </div>

            <div class="no_item_info">
                <img src="<?=THEMA_URL?>/assets/img/icon_box.png" alt=""><br>
                <p>상품을 검색한 후 추가해주세요.</p>
                <!-- <p class="txt_point">품목명을 모르시면 “품목찾기”버튼을 클릭해주세요.</p> -->
            </div>

            <div class="ss_item_list_hd">추가 된 상품 목록</div>
            <ul id="ss_item_list" class="ss_item_list">
            </ul>
            <div class="total_count_wr">
                총 등록 : 
                <span class="total_count">0건</span>
            </div>
        </div>
    </div>
    </form>
</section>

<iframe name="barcode_popup_iframe" id="barcode_popup_iframe" src="" scrolling="yes" frameborder="0" allowTransparency="false"></iframe>
<script>
function sendBarcode(text){
    $('#barcode_popup_iframe')[0].contentWindow.sendBarcode(text);
}
</script>
<form name="barcode_popup_form" class="hidden" id="barcode_popup_form">
	<input type=text name="it_id" value="">
    <input type=text name="uid" value="">
    <input type=text name="option_name" value="">
	<input type=text name="barcodes" value="">
	<input type="button" name="button1" value="전 송">
</form>

<script>
// 폼 전송
function form_submit(form) {
    // 바코드 값 적용
    var check = true;
    $('.list.item').each(function() {
        var it_barcode = [];
        $(this).find('.it_barcode').each(function() {
            it_barcode.push($(this).val());

            if($(this).val() == '')
                check = false;
        });

        $(this).parent().find('input[name="it_barcode[]"]').val(it_barcode.join(String.fromCharCode(30)));
    });

    if(!check) {
        alert('보유재고로 등록하려는 상품의 모든 바코드가 입력되어야 등록이 가능합니다.');
        return false;
    }

    return true;
}

// 품목 없는지 체크
function check_no_item() {
  if($('#ss_item_list li').length == 0) {
    $('.no_item_info').show();
    $('.ss_item_list_hd').hide();
    $('.btn_ss_submit').removeClass('active');
  } else {
    $('.no_item_info').hide();
    $('.ss_item_list_hd').show();
    $('.btn_ss_submit').addClass('active');
  }
}

// 품목 선택
function select_item(obj) {
    var $li = $('<li class="flex list item" data-code="' + obj.it_id + '" data-uid="' + Date.now().toString(36) + Math.random().toString(36).substr(2) + '">');
    $li.append('<input type="hidden" name="it_id[]" value="' + obj.it_id + '">')

    var $info_wr = $('<div class="it_info_wr">');
    $info_wr.append('<img class="it_img" src="/data/item/' + obj.it_img + '" onerror="this.src=\'/img/no_img.png\';">');
    
    var $info = $('<div class="it_info">');
    var $it_name = $('<p class="it_name">');
    $it_name.append(obj.it_name + ' (' + obj.gubun + ')');

    if (obj.options.length) {
        var option_html = "<select name=\"io_id[]\">";
        for(var i = 0; i < obj.options.length; i++) {
            option_html += "<option data\-price=\"" + obj.options[i]['io_price'] + "\" value=\"" + obj.options[i]['io_id'] + "\">" + obj.options[i]['io_id'].replace(//gi, " > ") + "</option>";
        }
        option_html += "</select>";
        $it_name.append(option_html);
    } else {
        var option_html = "<input type=\"hidden\" name=\"io_id[]\" value=\"\">";
        $it_name.append(option_html);
    }

    $info.append(
        $it_name,
        '<a class="prodBarNumCntBtn open_input_barcode">바코드 (0/1)</a>',
        '<input type="hidden" name="it_barcode[]">',
        '<div class="barcode_wr"><input type="hidden" class="it_barcode barcode_input"></div>'
    ).appendTo($info_wr);
    $li.append($info_wr);

    var $qty_wr = $('<div class="it_qty_wr">');
    $qty_wr.append('\
        <div class="input-group">\
        <div class="input-group-btn">\
            <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span class="sound_only">감소</span></button>\
        </div>\
        <input type="text" name="it_qty[]" value="1" class="form-control input-sm">\
        <div class="input-group-btn">\
            <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus"></i><span class="sound_only">증가</span></button>\
        </div>\
    </div>\
    ');
    $qty_wr.appendTo($li);

    var $price_wr = $('<div class="it_price_wr flex space-between">');
    $price_wr
    .append(
        '<div></div>',
        '<button type="button" class="btn_del_item">삭제</button>'
    )
    .appendTo($li);

    $('#ss_item_list').append($li);
    $('#ipt_ss_sch').val('').next().focus();

    check_no_item();
    update_total_count();
}

// 품목 검색
$('#ipt_ss_sch').flexdatalist({
    minLength: 1,
    url: 'ajax.get_item.php?eform=1',
    cache: false, // cache
    searchContain: true, // %검색어%
    noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
    selectionRequired: true,
    focusFirstResult: true,
    searchIn: ["it_name","it_model","id", "it_name_no_space"],
    visibleCallback: function($li, item, options) {
        var $item = {};
        $item = $('<span>')
        .html("[" + item.gubun + "] " + item.it_name + " (" + number_format(item.it_price) + "원)");

        $item.appendTo($li);
        return $li;
    },
})
.on("select:flexdatalist", function(event, obj, options) {
    select_item(obj);
});

// 상품수량변경
$(document).on('click', '.it_qty_wr button', function() {
    var mode = $(this).text();
    var this_qty;
    var $it_qty = $(this).closest('.it_qty_wr').find('input[name="it_qty[]"]');

    switch(mode) {
        case '증가':
        this_qty = parseInt($it_qty.val().replace(/[^0-9]/, "")) + 1;
        $it_qty.val(this_qty);
        break;
        case '감소':
        this_qty = parseInt($it_qty.val().replace(/[^0-9]/, "")) - 1;
        if(this_qty < 1) this_qty = 1
        $it_qty.val(this_qty);
        break;
    }
    update_barcode_field();
    update_total_count();
});

$(document).on('blur change paste', 'input[name="it_qty[]"]', function() {
    var val = parseInt($(this).val());

    if( isNaN(val) == false ) {
        if( val < 1 )
            $(this).val(1);

        update_barcode_field();
        update_total_count();
    } else {
        if ( $(this).val().replace(/[0-9]/g, '').length > 0 ) {
            alert('수량은 숫자만 입력해 주십시오.');
            $(this).val( 1 );
        }
        else {
            alert('수량이 입력되지 않았습니다.');
            $(this).val( 1 );
        }
    }

});

// 품목 삭제
$(document).on('click', '.btn_del_item', function() {
    $(this).closest('li').remove();
    check_no_item();
    update_total_count();
});

function update_total_count() {
    var count = 0;
    $('input[name="it_qty[]"]').each(function() {
        count += (parseInt($(this).val()) || 0);
    });

    $('.total_count').text(number_format(count) + '건');
}

// 바코드 필드 업데이트
function update_barcode_field() {
    $('.ss_item_list li').each(function() {
        // 상품 개수
        var it_qty = parseInt($(this).find('input[name="it_qty[]"]').val());

        // 먼저 기존에 입력된 바코드값 저장
        var barcodes = [];
        var $barcode = $(this).find('.it_barcode');
        $barcode.each(function() {
            barcodes.push($(this).val() || '');
        });

        $barcode_wr = $(this).find('.barcode_wr');
        $barcode_wr.empty();

        var inserted_count = 0;
        for(var i = 0; i < it_qty; i++) {
            var val = barcodes.shift() || '';
            $barcode_wr.append('<input type="hidden" class="it_barcode barcode_input" value="' + val + '">');
            if(val != '') {
                inserted_count++;
            }
        }
        $(this).find('.prodBarNumCntBtn').text('바코드 (' + inserted_count + '/' + it_qty + ')');
    });
}

// 바코드 입력
$(document).on("click", "a.open_input_barcode", function() {
    var it_id = $(this).closest('.item').data('code');
    var barcode_nodes = $(this).closest('.item').find('.barcode_input');
    var barcodes = [];
    var is_mobile = navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1;

    var uid = $(this).closest('.item').data('uid');
    var option_name = $(this).closest('.item').find('select[name="io_id[]"] option:selected').text() || '';

    $(barcode_nodes).each(function(i, item) {
        barcodes.push($(item).val());
    });

    window.name = "barcode_parent";
    var url = "./popup.order_barcode_form.php";
    var open_win;

    if(is_mobile) {
        $('#barcode_popup_iframe').show();
    } else {
        open_win = window.open("", "barcode_child", "width=683, height=800, resizable = no, scrollbars = no");
    }

    $('#barcode_popup_form').attr('target', is_mobile ? 'barcode_popup_iframe' : 'barcode_child');

    $('#barcode_popup_form').attr('action', url);
    $('#barcode_popup_form').attr('method', 'post');
    $('#barcode_popup_form input[name="it_id"]').val(it_id);
    $('#barcode_popup_form input[name="uid"]').val(uid);
    $('#barcode_popup_form input[name="option_name"]').val(option_name);
    $('#barcode_popup_form input[name="barcodes"]').val(barcodes.join('|'));
    $('#barcode_popup_form').submit();
});

check_no_item();
</script>

<?php include_once("./_tail.php"); ?>
