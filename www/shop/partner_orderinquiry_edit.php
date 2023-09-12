<?php
include_once('./_common.php');

$g5['title'] = '파트너 주문수정';
include_once("./_head.php");

$od_id = get_search_string($_GET['od_id']);
$od = sql_fetch("
  SELECT
    o.*,
    m.mb_temp,
    m.mb_name,
    mb_entNm
  FROM
    {$g5['g5_shop_order_table']} o
  LEFT JOIN
    {$g5['member_table']} m ON o.mb_id = m.mb_id
  WHERE
    od_id = '{$od_id}'
");
if(!$od['od_id'])
  alert('존재하지 않는 주문입니다.');

// 임시회원의경우 mb_entNm 대신 mb_name 출력
if($od['mb_temp']) {
    $od['mb_entNm'] = $od['mb_name'];
}

$cart_result = sql_query("
  SELECT
    c.*,
    i.ca_id,
    i.it_img1,
    i.it_cust_price
  FROM
    {$g5['g5_shop_cart_table']} c
  LEFT JOIN
    {$g5['g5_shop_item_table']} i ON c.it_id = i.it_id
  WHERE
    od_id = '{$od_id}' and
    ct_direct_delivery_partner = '{$member['mb_id']}' and
    ct_status IN('출고준비', '배송', '완료', '취소', '주문무효')
  ORDER BY
    ct_id ASC
");

$carts = [];
while($row = sql_fetch_array($cart_result)) {

  // 옵션 정보 가져오기
  $option_sql = "SELECT *
  FROM
      {$g5['g5_shop_item_option_table']}
  WHERE
      it_id = '{$row['it_id']}'
      and io_type = 0 -- 선택옵션
  ORDER BY
      io_no ASC
  ";

  $option_result = sql_query($option_sql);
  $options = [];
  while ($option_row = sql_fetch_array($option_result)) {
      $options[] = $option_row;
  }
  $row['options'] = $options;

  $gubun = $cate_gubun_table[substr($row["ca_id"], 0, 2)];
  $gubun_text = '판매';
  if($gubun == '01') $gubun_text = '대여';
  else if($gubun == '02') $gubun_text = '비급여';
  else if($gubun == '03') $gubun_text = '보장구';

  $row['gubun'] = $gubun_text;

  $carts[] = $row;
}

if(!$carts)
  alert('존재하지 않는 주문입니다.');

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/simple_order.css">');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">');
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js"></script>');
?>

<section class="wrap">
  <div class="sub_section_tit">주문 수정</div>
  <div class="inner">
    <form id="simple_order" name="forderform" class="form-horizontal" action="partner_orderinquiry_edit_result.php"
      method="post">
      <input type="hidden" name="od_id" value="<?=$od_id?>">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <label class="col-sm-2 control-label">
              <strong>사업소</strong>
            </label>
            <div class="col-sm-8">
              <?=$od['mb_entNm']?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">
              <strong>주문일시</strong>
            </label>
            <div class="col-sm-8">
              <?=date('Y-m-d H:i:s', strtotime($od['od_time']))?>
            </div>
          </div>
        </div>
        <div class="so_btn_wr">
          <button type="submit" class="btn_so_order active" style="padding: 18px">
            주문수정하기
          </button>
        </div>
      </div>

      <div class="so_item_wr">
        <div class="so_sch_wr">
          <div class="so_sch_hd">상품정보</div>
          <div class="ipt_so_sch_wr">
            <img src="<?php echo THEMA_URL; ?>/assets/img/icon_search.png">
            <input type="text" id="ipt_so_sch" class="ipt_so_sch" placeholder="여기에 추가할 상품명을 입력해주세요">
          </div>
          <div class="so_sch_pop">
            <p>상품명을 입력 후 간편하게 추가할 수 있습니다.<br> 상품명 일부만 입력해도 자동완성됩니다.</p>
            <!-- <p>상품명을 모르시면 '상품검색' 버튼을 눌러주세요.</p>
            <p><button type="button" class="btn_so_sch">상품검색</button></p> -->
          </div>
        </div>

        <div class="so_item_list_hd">추가 된 상품 목록</div>
        <ul id="so_item_list" class="so_item_list">
          <?php foreach($carts as $ct) { ?>
          <li class="flex">
            <input type="hidden" name="ct_id[]" value="<?=$ct['ct_id']?>">
            <input type="hidden" name="deleted[]" value="0">
            <input type="hidden" name="it_id[]" value="<?=$ct['it_id']?>">
            <input type="hidden" name="it_price[]" value="<?=$ct['it_cust_price']?>">
            <div class="it_info_wr">
              <img class="it_img" src="/data/item/<?=$ct["it_img1"]?>" onerror="this.src='/img/no_img.png';">
              <div class="it_info">
                <p class="it_name">
                  <?=$ct['it_name']?> (<?=$ct['gubun']?>)
                  <?php
                  if($ct['options'] && $ct['io_type'] != '1') {
                      echo '<select name="io_id[]">';
                      foreach($ct['options'] as $option) {
                          echo '<option data-price="' . $option['io_price'] . '" value="' . $option['io_id'] . '" ' . get_selected($ct['io_id'], $option['io_id']) . '>' . str_replace(chr(30), ' > ', $option['io_id']) . '</option>';
                      }
                      echo '</select>';
                  } else {
                      echo '
                          <input type="hidden" name="io_id[]" value="'. $ct['io_id'] . '">
                      ';
                  }
                  ?>
                </p>
                <p class="it_price">
                  판매가 : <?=number_format($ct['it_cust_price'])?>원
                </p>
                <div class="flex">
                  <div class="prod_memo_hd">요청사항</div>
                  <input type="text" class="ipt_prod_memo" name="prodMemo[]" value="<?=$ct['prodMemo']?>"
                    placeholder="상품관련 요청사항을 입력하세요.">
                </div>
              </div>
            </div>
            <div class="it_qty_wr">
              <div class="input-group">
                <div class="input-group-btn">
                  <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span
                      class="sound_only">감소</span></button>
                </div>
                <input type="text" name="ct_qty[]" value="<?=$ct['ct_qty']?>" class="form-control input-sm">
                <div class="input-group-btn">
                  <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus"></i><span
                      class="sound_only">증가</span></button>
                </div>
              </div>
            </div>
            <div class="it_price_wr flex space-between">
              <div>
                <p class="it_price">단가 : <span><?=number_format($ct['it_cust_price'])?>원</span></p>
                <p class="ct_price">0원</p>
              </div>
              <!-- button type="button" class="btn_del_item">삭제</button -->
          </li>
          <?php } ?>
        </ul>
        <div class="total_price_wr">
          총 결제 금액 :
          <span class="total_price">0원</span>
        </div>

      </div>

    </form>
  </div>
</section>

<script>
function select_item(obj, io_id, ct_qty) {
  var $li = $('<li class="flex">');
  $li.append('<input type="hidden" name="it_id[]" value="' + obj.it_id + '">')
    .append('<input type="hidden" name="it_price[]" value="' + obj.it_cust_price + '">')

  var $info_wr = $('<div class="it_info_wr">');
  $info_wr.append('<img class="it_img" src="/data/item/' + obj.it_img + '" onerror="this.src=\'/img/no_img.png\';">');

  var $info = $('<div class="it_info">');
  var $it_name = $('<p class="it_name">');

  $it_name.append(obj.it_name + ' (' + obj.gubun + ')');
  var it_price = parseInt(obj.it_cust_price);
  var ct_price = it_price;
  if (obj.options.length) {
    var option_html = "<select name=\"io_id[]\">";
    for (var i = 0; i < obj.options.length; i++) {
      if (i === 0) {
        ct_price += parseInt(obj.options[i]['io_price']);
      }
      option_html += "<option data\-price=\"" + obj.options[i]['io_price'] + "\" value=\"" + obj.options[i]['io_id'] +
        "\">" + obj.options[i]['io_id'].replace(//gi, " > ") + "</option>";
    }
    option_html += "</select>";
    $it_name.append(option_html);
  } else {
    var option_html = "<input type=\"hidden\" name=\"io_id[]\" value=\"\">";
    $it_name.append(option_html);
  }

  var $it_price = $('<p class="it_price">');
  $it_price.append('판매가 : ' + number_format(it_price));

  var $prod_memo = $('<div class="flex">');
  $prod_memo.append(
    '<div class="prod_memo_hd">요청사항</div>',
    '<input type="text" class="ipt_prod_memo" name="prodMemo[]" placeholder="상품관련 요청사항을 입력하세요.">'
  );

  $info.append(
      $it_name,
      $it_price,
      $prod_memo
    )
    .appendTo($info_wr);
  $li.append($info_wr);

  var $qty_wr = $('<div class="it_qty_wr">');
  $qty_wr.append('\
    <div class="input-group">\
      <div class="input-group-btn">\
          <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span class="sound_only">감소</span></button>\
      </div>\
      <input type="text" name="ct_qty[]" value="1" class="form-control input-sm">\
      <div class="input-group-btn">\
          <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus"></i><span class="sound_only">증가</span></button>\
      </div>\
  </div>\
  ');
  $qty_wr.appendTo($li);

  var $price_wr = $('<div class="it_price_wr flex space-between">');
  $price_wr
    .append(
      '<div><p class="it_price">단가 : <span>' + number_format(it_price) + '원</span></p>' +
      '<p class="ct_price">' + number_format(ct_price) + '원</p></div>',
      '<input type="hidden" name="ct_price[]" value="' + ct_price + '">',
      '<button type="button" class="btn_del_item">삭제</button>'
    )
    .appendTo($li);

  $('#so_item_list').append($li);

  if (io_id) {
    $li.find('select[name="io_id[]"]').val(io_id);
  }

  if (ct_qty) {
    $li.find('input[name="ct_qty[]"]').val(ct_qty);
  }

  calculate_order_price();
  $('#ipt_so_sch').val('').next().focus();
}

// 품목 검색
$('#ipt_so_sch').flexdatalist({
    minLength: 1,
    url: 'ajax.get_item_partner.php',
    cache: false, // cache
    searchContain: true, // %검색어%
    noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
    selectionRequired: true,
    focusFirstResult: true,
    searchIn: ["it_name", "it_model", "id", "it_name_no_space"],
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

// 주문금액계산
function calculate_order_price() {
  var $li = $('#so_item_list li');

  var order_price = 0;
  $li.each(function() {
    var it_id = $(this).find('input[name="it_id[]"]').val();
    var it_price = parseInt($(this).find('input[name="it_price[]"]').val() || 0);
    var io_price = parseInt($(this).find('select[name="io_id[]"] option:selected').data('price') || 0);
    var ct_qty = parseInt($(this).find('input[name="ct_qty[]"]').val() || 0);
    var deleted = $(this).find('input[name="deleted[]"]').val() == '1';

    if (deleted)
      return;

    var ct_price = (it_price + io_price) * ct_qty;
    $(this).find('.it_price_wr .it_price span').text(number_format(it_price + io_price) + '원');
    $(this).find('.it_price_wr .ct_price').text(number_format(ct_price) + '원');
    $(this).find('input[name="ct_price[]"]').val(ct_price);
    order_price += ct_price;
  });

  // 주문금액
  $('#order_price').text(number_format(order_price));

  // 총 결제금액
  $('#total_price').text(number_format(order_price));
  $('.total_price_wr .total_price').text(number_format(order_price) + '원');
}

// 상품수량변경
$(document).on('click', '.it_qty_wr button', function() {
  var mode = $(this).text();
  var this_qty;
  var $ct_qty = $(this).closest('.it_qty_wr').find('input[name^=ct_qty]');

  switch (mode) {
    case '증가':
      this_qty = parseInt($ct_qty.val().replace(/[^0-9]/, "")) + 1;
      $ct_qty.val(this_qty);
      break;
    case '감소':
      this_qty = parseInt($ct_qty.val().replace(/[^0-9]/, "")) - 1;
      if (this_qty < 1) this_qty = 1
      $ct_qty.val(this_qty);
      break;
  }

  calculate_order_price();
});
$(document).on('change paste keyup', 'input[name="ct_qty[]"]', function() {
  if ($(this).val() < 1)
    $(this).val(1);

  calculate_order_price();
});

// 품목 삭제
$(document).on('click', '.btn_del_item', function() {
  var $li = $(this).closest('li');
  if ($li.find('input[name="ct_id[]"]').length > 0) {
    // 기존 주문 상품이면
    $li.find('input[name="deleted[]"]').val('1');
    $li.hide();
  } else {
    // 신규 추가 상품이면
    $li.remove();
  }

  calculate_order_price();
});

calculate_order_price();
</script>

<?php
include_once('./_tail.php');
?>
