var option_add = false;
var supply_add = false;
var isAndroid = navigator.userAgent.toLowerCase().indexOf('android') > -1;
var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

$(function () {
  // 선택옵션
  /* 가상커서 ctrl keyup 이베트 대응 */
  /*
    $(document).on("keyup", "select.it_option", function(e) {
		var sel_count = $("select.it_option").size();
        var idx = $("select.it_option").index($(this));
        var code = e.keyCode;
        var val = $(this).val();

        option_add = false;
        if(code == 17 && sel_count == idx + 1) {
            if(val == "")
                return;

            sel_option_process(true);
        }
    });
    */

  /* 키보드 접근 후 옵션 선택 Enter keydown 이벤트 대응 */
  $(document).on('keydown', 'select.it_option', function (e) {
    var sel_count = $('select.it_option').size();
    var idx = $('select.it_option').index($(this));
    var code = e.keyCode;
    var val = $(this).val();

    option_add = false;
    if (code == 13 && sel_count == idx + 1) {
      if (val == '') return;

      sel_option_process(true);
    }
  });

  if (isAndroid) {
    $(document).on('touchend', 'select.it_option', function () {
      option_add = true;
    });
  } else {
    var it_option_events = isSafari ? 'mousedown' : 'mouseup';

    $(document).on(it_option_events, 'select.it_option', function (e) {
      option_add = true;
    });
  }

  $(document).on('change', 'select.it_option', function () {
    var sel_count = $('select.it_option').size();
    var idx = $('select.it_option').index($(this));
    var val = $(this).val();
    var it_id = $("input[name='it_id[]']").val();

    // 선택값이 없을 경우 하위 옵션은 disabled
    if (val == '') {
      $('select.it_option:gt(' + idx + ')')
        .val('')
        .attr('disabled', true);
      return;
    }

    // 하위선택옵션로드
    if (sel_count > 1 && idx + 1 < sel_count) {
      var opt_id = '';

      // 상위 옵션의 값을 읽어 옵션id 만듬
      if (idx > 0) {
        $('select.it_option:lt(' + idx + ')').each(function () {
          if (!opt_id) opt_id = $(this).val();
          else opt_id += chr(30) + $(this).val();
        });

        opt_id += chr(30) + val;
      } else if (idx == 0) {
        opt_id = val;
      }

      $.post(
        './itembuyoption.php',
        { it_id: it_id, opt_id: opt_id, idx: idx, sel_count: sel_count },
        function (data) {
          $('select.it_option')
            .eq(idx + 1)
            .empty()
            .html(data)
            .attr('disabled', false);

          // select의 옵션이 변경됐을 경우 하위 옵션 disabled
          if (idx + 1 < sel_count) {
            var idx2 = idx + 1;
            $('select.it_option:gt(' + idx2 + ')')
              .val('')
              .attr('disabled', true);
          }
        }
      );
    } else if (idx + 1 == sel_count) {
      // 선택옵션처리
      if (option_add && val == '') return;

      var info = val.split(',');
      // 재고체크
      if (parseInt(info[2]) < 1) {
        alert('선택하신 선택옵션상품은 재고가 부족하여 구매할 수 없습니다.');
        return false;
      }

      if (option_add) sel_option_process(true);
    }
  });

  // 추가옵션
  /* 가상커서 ctrl keyup 이베트 대응 */
  /*
    $(document).on("keyup", "select.it_supply", function(e) {
		var $el = $(this);
        var code = e.keyCode;
        var val = $(this).val();

        supply_add = false;
        if(code == 17) {
            if(val == "")
                return;

            sel_supply_process($el, true);
        }
    });
    */

  /* 키보드 접근 후 옵션 선택 Enter keydown 이벤트 대응 */
  $(document).on('keydown', 'select.it_supply', function (e) {
    var $el = $(this);
    var code = e.keyCode;
    var val = $(this).val();

    supply_add = false;
    if (code == 13) {
      if (val == '') return;

      sel_supply_process($el, true);
    }
  });

  if (isAndroid) {
    $(document).on('touchend', 'select.it_supply', function () {
      supply_add = true;
    });
  } else {
    var it_supply_events = isSafari ? 'mousedown' : 'mouseup';

    $(document).on(it_supply_events, 'select.it_supply', function (e) {
      supply_add = true;
    });
  }

  $(document).on('change', 'select.it_supply', function () {
    var $el = $(this);
    var val = $(this).val();

    if (val == '') return;

    if (supply_add) sel_supply_process($el, true);
  });

  // 수량변경 및 삭제
  $(document).on('click', '#it_sel_option li button', function () {
    var mode = $(this).text();
    var this_qty,
      max_qty = 9999,
      min_qty = 1;
    var $el_qty = $(this).closest('li').find('input[name^=ct_qty]');
    var stock = parseInt($(this).closest('li').find('input.io_stock').val());
    var it_buy_inc_qty = parseInt($('#it_buy_inc_qty').val());
    if (it_buy_inc_qty < 1) it_buy_inc_qty = 1;
    if (it_buy_inc_qty > min_qty) min_qty = it_buy_inc_qty;

    switch (mode) {
      case '증가':
        this_qty =
          parseInt($el_qty.val().replace(/[^0-9]/, '')) + it_buy_inc_qty;
        if (this_qty > stock) {
          alert('재고수량 보다 많은 수량을 구매할 수 없습니다.');
          this_qty = stock;
        }

        if (this_qty > max_qty) {
          this_qty = max_qty;
          alert(
            '최대 구매수량은 ' + number_format(String(max_qty)) + ' 입니다.'
          );
        }

        $el_qty.val(this_qty);
        price_calculate();
        break;

      case '감소':
        this_qty =
          parseInt($el_qty.val().replace(/[^0-9]/, '')) - it_buy_inc_qty;
        if (this_qty < min_qty) {
          this_qty = min_qty;
          alert(
            '최소 구매수량은 ' + number_format(String(min_qty)) + ' 입니다.'
          );
        }
        $el_qty.val(this_qty);
        price_calculate();
        break;

      case '삭제':
        if (confirm('선택하신 옵션항목을 삭제하시겠습니까?')) {
          var $el = $(this).closest('li');
          var del_exec = true;

          if ($('#it_sel_option .it_spl_list').size() > 0) {
            // 선택옵션이 하나이상인지
            if ($el.hasClass('it_opt_list')) {
              if ($('.it_opt_list').size() <= 1) del_exec = false;
            }
          }

          if (del_exec) {
            $el.closest('li').remove();
            price_calculate();
          } else {
            alert('선택옵션은 하나이상이어야 합니다.');
            return false;
          }
        }
        break;

      default:
        alert('올바른 방법으로 이용해 주십시오.');
        break;
    }
  });

  // 수량직접입력
  $(document).on('keyup', 'input[name^=ct_qty]', function () {
    var val = $(this).val();

    if (val != '') {
      if (val.replace(/[0-9]/g, '').length > 0) {
        alert('수량은 숫자만 입력해 주십시오.');
        $(this).val(1);
      } else {
        var d_val = parseInt(val);
        if (d_val < 1 || d_val > 9999) {
          alert('수량은 1에서 9999 사이의 값으로 입력해 주십시오.');
          $(this).val(1);
        } else {
          var stock = parseInt(
            $(this).closest('li').find('input.io_stock').val()
          );
          if (d_val > stock) {
            alert('재고수량 보다 많은 수량을 구매할 수 없습니다.');
            $(this).val(stock);
          }
        }
      }

      price_calculate();
    }
  });
});

// 선택옵션 추가처리
function sel_option_process(add_exec) {
  var it_price = parseInt($('input#it_price').val());
  var id = '';
  var value,
    info,
    sel_opt,
    item,
    price,
    stock,
    run_error = false;
  var option = (sep = '');
  info = $('select.it_option:last').val().split(',');

  $('select.it_option').each(function (index) {
    value = $(this).val();
    item = $(this).closest('tr').find('th label').text();

    if (!value) {
      run_error = true;
      return false;
    }

    // 옵션선택정보
    sel_opt = value.split(',')[0];

    if (id == '') {
      id = sel_opt;
    } else {
      id += chr(30) + sel_opt;
      sep = ' / ';
    }

    option += sep + item + ':' + sel_opt;
  });

  if (run_error) {
    alert(item + '을(를) 선택해 주십시오.');
    return false;
  }

  price = info[1];
  stock = info[2];

  // 금액 음수 체크
  if (it_price + parseInt(price) < 0) {
    alert('구매금액이 음수인 상품은 구매할 수 없습니다.');
    return false;
  }

  if (add_exec) {
    if (same_option_check(option)) return;

    add_sel_option(0, id, option, price, stock);
  }
}

// 추가옵션 추가처리
function sel_supply_process($el, add_exec) {
  var val = $el.val();
  var item = $el.closest('tr').find('th label').text();

  if (!val) {
    alert(item + '을(를) 선택해 주십시오.');
    return;
  }

  var info = val.split(',');

  // 재고체크
  if (parseInt(info[2]) < 1) {
    alert(info[0] + '은(는) 재고가 부족하여 구매할 수 없습니다.');
    return false;
  }

  var id = item + chr(30) + info[0];
  var option = item + ':' + info[0];
  var price = info[1];
  var stock = info[2];

  // 금액 음수 체크
  if (parseInt(price) < 0) {
    alert('구매금액이 음수인 상품은 구매할 수 없습니다.');
    return false;
  }

  if (add_exec) {
    if (same_option_check(option)) return;

    add_sel_option(1, id, option, price, stock);
  }
}

// 선택된 옵션 출력
function add_sel_option(type, id, option, price, stock) {
  var item_code = $("input[name='it_id[]']").val();
  var it_msg1 = $("input[name='it_msg1[]']").val();
  var it_msg2 = $("input[name='it_msg2[]']").val();
  var it_msg3 = $("input[name='it_msg3[]']").val();
  var product_price = Number($('#it_price').val());
  var it_buy_inc_qty = Number($('#it_buy_inc_qty').val());
  if (it_buy_inc_qty < 1) it_buy_inc_qty = 1;

  var opt = '';
  var li_class = 'it_opt_list';
  if (type) li_class = 'it_spl_list';

  var opt_prc;
  var total_price = Number(price) + product_price;

  if (type != 0) {
    total_price = price;
  }

  if (parseInt(price) >= 0)
    opt_prc = '' + number_format(String(total_price)) + '원';
  else opt_prc = '' + number_format(String(total_price)) + '원';

  opt += '<li class="' + li_class + ' list-group-item">';
  opt +=
    '<input type="hidden" name="io_type[' +
    item_code +
    '][]" value="' +
    type +
    '">';
  opt +=
    '<input type="hidden" name="io_id[' +
    item_code +
    '][]" value="' +
    id +
    '">';
  opt +=
    '<input type="hidden" name="io_value[' +
    item_code +
    '][]" value="' +
    option +
    '">';
  opt += '<input type="hidden" class="io_price" value="' + price + '">';
  opt += '<input type="hidden" class="io_stock" value="' + stock + '">';
  opt += '<div class="row"><div class="col-sm-7"><label>';
  opt += '<span class="it_opt_subj">' + option + '</span>';
  opt += '<span class="it_opt_prc">' + opt_prc + '</span>';
  opt += '</label></div><div class="col-sm-5">';
  opt += '<div class="input-group-btn-del">';
  opt +=
    '<button type="button" class="it_opt_del btn btn-sm btn-lightgray"><i class="fa fa-times-circle fa-lg"></i><span class="sound_only">삭제</span></button>';
  opt += '</div>';

  opt += '<div class="input-group">';
  opt += '<div class="input-group-btn">';
  opt +=
    '<button type="button" class="it_qty_minus btn btn-sm btn-lightgray"><i class="fa fa-minus-circle fa-lg"></i><span class="sound_only">감소</span></button>';
  opt += '</div>';
  opt +=
    '<input type="text" name="ct_qty[' +
    item_code +
    '][]" value="' +
    it_buy_inc_qty +
    '" class="form-control input-sm" size="5">';
  opt += '<div class="input-group-btn">';
  opt +=
    '<button type="button" class="it_qty_plus btn btn-sm btn-lightgray"><i class="fa fa-plus-circle fa-lg"></i><span class="sound_only">증가</span></button>';
  opt += '</div></div>';
  if (it_buy_inc_qty > 1) {
    opt += '<span class="inc_desc">' + it_buy_inc_qty + '개씩 증가</span>';
  }
  opt += '</div></div>';
  if (!type) {
    if (it_msg1) {
      opt +=
        '<div style="margin-top:10px;"><input type="text" name="pt_msg1[' +
        item_code +
        '][]" class="form-control input-sm" placeholder="' +
        it_msg1 +
        '"></div>';
    }
    if (it_msg2) {
      opt +=
        '<div style="margin-top:10px;"><input type="text" name="pt_msg2[' +
        item_code +
        '][]" class="form-control input-sm" placeholder="' +
        it_msg2 +
        '"></div>';
    }
    if (it_msg3) {
      opt +=
        '<div style="margin-top:10px;"><input type="text" name="pt_msg3[' +
        item_code +
        '][]" class="form-control input-sm" placeholder="' +
        it_msg3 +
        '"></div>';
    }
  }
  opt += '</li>';

  if ($('#it_sel_option > ul').size() < 1) {
    $('#it_sel_option').html('<ul id="it_opt_added" class="list-group"></ul>');
    $('#it_sel_option > ul').html(opt);
  } else {
    if (type) {
      if ($('#it_sel_option .it_spl_list').size() > 0) {
        $('#it_sel_option .it_spl_list:last').after(opt);
      } else {
        if ($('#it_sel_option .it_opt_list').size() > 0) {
          $('#it_sel_option .it_opt_list:last').after(opt);
        } else {
          $('#it_sel_option > ul').html(opt);
        }
      }
    } else {
      if ($('#it_sel_option .it_opt_list').size() > 0) {
        $('#it_sel_option .it_opt_list:last').after(opt);
      } else {
        if ($('#it_sel_option .it_spl_list').size() > 0) {
          $('#it_sel_option .it_spl_list:first').before(opt);
        } else {
          $('#it_sel_option > ul').html(opt);
        }
      }
    }
  }

  price_calculate();
}

// 동일선택옵션있는지
function same_option_check(val) {
  var result = false;
  $('input[name^=io_value]').each(function () {
    if (val == $(this).val()) {
      result = true;
      return false;
    }
  });

  if (result) alert(val + ' 은(는) 이미 추가하신 옵션상품입니다.');

  return result;
}

// 가격계산
function price_calculate() {
  var it_price = parseInt($('input#it_price').val());

  if (isNaN(it_price)) return;

  var $el_prc = $('input.io_price');
  var $el_qty = $('input[name^=ct_qty]');
  var $el_type = $('input[name^=io_type]');
  var price,
    type,
    qty,
    total_qty = 0,
    total = 0;

  $el_prc.each(function (index) {
    price = parseInt($(this).val());
    qty = parseInt($el_qty.eq(index).val());
    total_qty += qty;
    type = $el_type.eq(index).val();

    if (type == '0') {
      // 선택옵션
      total += (it_price + price) * qty;
    } else {
      // 추가옵션
      total += price * qty;
    }
  });

  //할인가 적용 나타내기 --성훈완료
  var it_sale_percent = $('.it_sale_percent').get();
  $('#it_tot_price').html(number_format(String(total)) + '원');
  var count = 0;

  for (var i = 0; i < it_sale_percent.length; i++) {
    if (parseInt(it_sale_percent[i].getAttribute('data-toggle')) <= total_qty) {
      if (type == '0') {
        // 선택옵션
        total = parseInt(it_sale_percent[i].value) * total_qty;
        count++;
      } else {
        // 추가옵션
        total = parseInt(it_sale_percent[i].value) * total_qty;
        count++;
      }
      $('.it_opt_prc').html(
        number_format(String(parseInt(it_sale_percent[i].value))) + '원'
      );
      $('#it_tot_price').html(number_format(String(total)) + '원');
    }
    //할인가 적용 나타내기 --성훈완료
  }
  if (!count) {
    $('.it_opt_prc').html(number_format(String(it_price)) + '원');
  }
}

// php chr() 대응
function chr(code) {
  return String.fromCharCode(code);
}
