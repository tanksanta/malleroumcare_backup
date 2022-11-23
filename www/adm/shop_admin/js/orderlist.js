function list_select(obj) {
  var value = $(obj).data('value');
  // console.log(value);

  // console.log($(obj));

  var start_tr = $(obj).closest('tr');
  while (start_tr.next().length) {
    var item = start_tr.next();

    var checkbox = $(item).find('input[name="od_id[]"]');
    if ($(checkbox).length) {
      if (value === 'select') {
        $(checkbox).prop('checked', true);
      }
      if (value === 'not-select') {
        $(checkbox).prop('checked', false);
      }

      var important = $(checkbox).closest('tr').find('.list-important');
      if (value === 'important') {
        if ($(important).hasClass('on')) {
          $(checkbox).prop('checked', true);
        } else {
          $(checkbox).prop('checked', false);
        }
      }
      if (value === 'not-important') {
        if (!$(important).hasClass('on')) {
          $(checkbox).prop('checked', true);
        } else {
          $(checkbox).prop('checked', false);
        }
      }
    }

    if ($(item).attr('class') === 'step' || $(item).attr('class') === 'btns') {
      break;
    }

    start_tr = start_tr.next();
  }

  /*
    $('#samhwa_order_list_table .table:first-child tr').each(function(index, item){

        var checkbox = $(item).find('input[name="od_id[]"]');
        if ( $(checkbox).length ) {
            if (value === 'select') {
                $(checkbox).prop("checked", true);
            }
            if (value === 'not-select') {
                $(checkbox).prop("checked", false);
            }

            var important = $(checkbox).closest('tr').find('.list-important');
            if (value === 'important') {
                if ($(important).hasClass('on')) {
                    $(checkbox).prop("checked", true);
                }else{
                    $(checkbox).prop("checked", false);
                }
            }
            if (value === 'not-important') {
                if (!$(important).hasClass('on')) {
                    $(checkbox).prop("checked", true);
                }else{
                    $(checkbox).prop("checked", false);
                }
            }
        }
    });
    */
}

// 작업 지시서
var order_prints_pop;

function printOrderView(odid) {
  order_prints_pop = window.open(
    './pop.order.prints.php?od_id=' + odid + '|',
    'order_prints_pop',
    'width=850, height=800, resizable = no, scrollbars = no'
  );
}

$(function () {
  // 테이블 th고정
  var offset = $('#samhwa_order_ajax_list_table').offset();

  function fixed_container() {
    if ($(document).scrollTop() > offset.top) {
      $('#samhwa_order_list_table>div.table thead tr').addClass('fixed');
    } else {
      $('#samhwa_order_list_table>div.table thead tr').removeClass('fixed');
    }
  }

  $(window).scroll(function () {
    fixed_container();
  });
  fixed_container();

  // 셀렉트 박스
  $(document).on('click', '.drop_multi_sub', function () {
    $(this).next().toggle();
  });

  $(document).on(
    'click',
    '.custom-select-box-multi li, .drop_multi_main',
    function () {
      list_select(this);
      if ($(this).prop('tagName') === 'LI') {
        $(this).closest('ul').toggle();
      }
    }
  );

  $(document).on('click', '.important-25', function () {
    //var od_id = $(this).closest('tr').find('input[name="order_seq[]"]').val();
    var od_id = $(this).data('od_id');
    var type = $(this).hasClass('list-important') ? 1 : 2;

    var data = {
      od_id: od_id,
      type: type,
      val: $(this).hasClass('on'),
    };
    var obj = this;

    var ajax = $.ajax({
      method: 'POST',
      url: './ajax.important.php',
      data: data,
    }).done(function (data) {
      if (data.msg) {
        alert(data.msg);
      }
      if (data.result === 'success') {
        if (data.value === true) {
          $(obj).addClass('on');
        } else {
          $(obj).removeClass('on');
        }
      }
    });
  });

  function get_selected(obj) {
    var start_tr = $(obj).closest('tr');
    var ret = [];
    while (start_tr.next().length) {
      var item = start_tr.next();
      var checkbox = $(item).find('input[name="od_id[]"]:checked')[0];
      if ($(checkbox).length) {
        ret.push(checkbox);
      }

      if (
        $(item).attr('class') === 'step' ||
        $(item).attr('class') === 'btns'
      ) {
        break;
      }

      start_tr = start_tr.next();
    }

    return ret;
  }

  $(document).on('click', '#change_cancel_step', function () {
    var next_step_val = '취소';
    //var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    var od_id = $(get_selected(this)).serializeObject();
    if (od_id['od_id[]'] === undefined) {
      alert('선택해주세요.');
      return;
    }

    change_step(od_id['od_id[]'], next_step_val, 'true');
  });

  $(document).on('click', '#change_to_invalid_step', function () {
    var next_step_val = '주문무효';
    //var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    var od_id = $(get_selected(this)).serializeObject();
    if (od_id['od_id[]'] === undefined) {
      alert('선택해주세요.');
      return;
    }
    $.ajax({
      method: 'POST',
      url: '/shop/schedule/ajax.update_schedule_status.php',
      data: {
        ct_id: od_id['od_id[]'],
        status: next_step_val,
      },
    }).done(function () {
      change_step(od_id['od_id[]'], next_step_val, 'true');
    }).fail(function () {
      alert('알 수 없는 문제 발생');
      return;
    });
  });

  $(document).on('click', '#change_next_step', function () {
    var next_step_val = $(this).data('next-step-val');
    //var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    var $selected_ods = get_selected(this);
    for (var i = 0; i < $selected_ods.length; i++) {
      var $selected_od = $selected_ods[i];
      if ($($selected_od).closest('tr').hasClass('cancel_requested')) {
        alert('취소요청이 있는 주문은 단계이동이 불가능합니다.');
        return;
      }
    }
    var od_id = $($selected_ods).serializeObject();
    if (od_id['od_id[]'] === undefined) {
      alert('선택해주세요.');
      return;
    }

    change_step(od_id['od_id[]'], next_step_val, 'true');
  });

  $(document).on('click', '#change_prev_step', function () {
    var prev_step_val = $(this).data('prev-step-val');
    //var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    var od_id = $(get_selected(this)).serializeObject();

    if (od_id['od_id[]'] === undefined) {
      alert('선택해주세요.');
      return;
    }

    change_step(od_id['od_id[]'], prev_step_val, 'true');
  });

  $(document).on('click', '#list_order_prints', function () {
    var prev_step_val = $(this).data('prev-step-val');
    //var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    var od_id = $(get_selected(this)).serializeObject();

    if (od_id['od_id[]'] === undefined) {
      alert('선택해주세요.');
      return;
    }

    var ret_od_id;
    if (Array.isArray(od_id['od_id[]'])) {
      ret_od_id = od_id['od_id[]'].join('|');
    } else {
      ret_od_id = od_id['od_id[]'] + '|';
    }

    var list_order_prints_pop = window.open(
      './pop.order.prints.php?od_id=' + ret_od_id,
      'order_prints_pop',
      'width=850, height=800, resizable = no, scrollbars = yes'
    );
  });

  // 상품 매칭 취소
  $(document).on('click', '#list_matching_cancel', function () {
    var matching_cancel_pop = window.open(
      './pop.openmarket.item.list.php',
      'matching_cancel_pop',
      'width=1080, height=900, resizable = no, scrollbars = no'
    );
  });


  /****** 발주 관련 함수 *******/

  $(document).on('click', '#change_cancel_step_for_purchase_order', function () {
    var next_step_val = '취소';
    //var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    var od_id = $(get_selected(this)).serializeObject();
    if (od_id['od_id[]'] === undefined) {
      alert('선택해주세요.');
      return;
    }

    change_step_for_purchase_order(od_id['od_id[]'], next_step_val, 'true');
  });

  $(document).on('click', '#change_next_step_for_purchase_order', function () {
    var next_step_val = $(this).data('next-step-val');
    //var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    var $selected_ods = get_selected(this);
    for (var i = 0; i < $selected_ods.length; i++) {
      var $selected_od = $selected_ods[i];
      if ($($selected_od).closest('tr').hasClass('cancel_requested')) {
        alert('취소요청이 있는 주문은 단계이동이 불가능합니다.');
        return;
      }
    }
    var od_id = $($selected_ods).serializeObject();
    if (od_id['od_id[]'] === undefined) {
      alert('선택해주세요.');
      return;
    }

    change_step_for_purchase_order(od_id['od_id[]'], next_step_val, 'true');
  });

  $(document).on('click', '#change_prev_step_for_purchase_order', function () {
    var prev_step_val = $(this).data('prev-step-val');
    //var od_id = $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked').serializeObject();
    var od_id = $(get_selected(this)).serializeObject();

    if (od_id['od_id[]'] === undefined) {
      alert('선택해주세요.');
      return;
    }

    change_step_for_purchase_order(od_id['od_id[]'], prev_step_val, 'true');
  });
});

$(function () {
  $('#fr_date, #to_date').datepicker({
    changeMonth: true,
    changeYear: true,
    dateFormat: 'yy-mm-dd',
    showButtonPanel: true,
    yearRange: 'c-99:c+99',
    maxDate: '+0d',
  });

  // 주문상품보기
  $('.orderitem').on('click', function () {
    var $this = $(this);
    var od_id = $this.text().replace(/[^0-9]/g, '');

    if ($this.next('#orderitemlist').size()) return false;

    $('#orderitemlist').remove();

    $.post('./ajax.orderitem.php', { od_id: od_id }, function (data) {
      $this.after('<div id="orderitemlist"><div class="itemlist"></div></div>');
      $('#orderitemlist .itemlist')
        .html(data)
        .append(
          '<div id="orderitemlist_close"><button type="button" id="orderitemlist-x" class="btn_frmline">닫기</button></div>'
        );
    });

    return false;
  });

  // 상품리스트 닫기
  $('.orderitemlist-x').on('click', function () {
    $('#orderitemlist').remove();
  });

  $('body').on('click', function () {
    $('#orderitemlist').remove();
  });

  // 엑셀배송처리창
  $('#order_delivery').on('click', function () {
    var opt = 'width=600,height=450,left=10,top=10';
    window.open(this.href, 'win_excel', opt);
    return false;
  });

  // 기본검색 설정창 닫기
  $('#fdefaultsettingform-exit').click(function () {
    $('#fdefaultsettingform').hide();
  });

  function set_input_val(parent_node, key, value) {
    var obj;
    if ($(parent_node).find('input[name="' + key + '"]').length) {
      obj = $(parent_node).find(
        'input[name="' + key + '"], select[name="' + key + '"]'
      );
    } else {
      obj = $(parent_node).find(
        'input[name="' + key + '[]"], select[name="' + key + '"]'
      );
    }

    if ($(obj).is('input')) {
      if ($(obj).attr('type') == 'hidden') return;

      if ($(obj).attr('type') == 'radio') {
        $(parent_node)
          .find('input:radio[name="' + key + '"]:radio[value="' + value + '"]')
          .prop('checked', true);
      }

      if ($(obj).attr('type') == 'checkbox') {
        $(parent_node)
          .find(
            'input:checkbox[name="' + key + '"]:checkbox[value="' + value + '"]'
          )
          .prop('checked', true); /* by NAME */
        $(parent_node)
          .find(
            'input:checkbox[name="' +
              key +
              '[]"]:checkbox[value="' +
              value +
              '"]'
          )
          .prop('checked', true); /* by NAME */
      }

      if ($(obj).attr('type') == 'text') {
        $(obj).val(value);
      }

      if ($(obj).attr('type') == 'button') {
        setTimeout(function () {
          $(parent_node)
            .find('input:button[value="' + value + '"]')
            .click();
        }, 100);
      }
    }

    if ($(obj).is('select')) {
      $(obj).val(value);
    }
  }
  function open_default_setting_form() {
    var ret = '';
    $('#search_detail_table tr')
      .each(function (index, item) {
        var title = $(item).find('th').html();
        var content = $(item).find('td');

        var content_ret = '';
        $(content)
          .find('input, select, h2, span.linear_span')
          .each(function (index2, item2) {
            if ($(this).is('input')) {
              var temp_id = $(item2).attr('id') || $(item2).attr('name');
              if ($(item2).attr('type') == 'button') {
                content_ret +=
                  '<input type="radio" name="' +
                  $(item2).attr('name') +
                  '" value="' +
                  $(item2).val() +
                  '" id="default_' +
                  temp_id +
                  '" />';
                var labelText = $(item2).val();
                content_ret +=
                  '<label for="default_' +
                  temp_id +
                  '">' +
                  labelText +
                  '</label>';
              } else {
                content_ret +=
                  '<input type="' +
                  $(item2).attr('type') +
                  '" name="' +
                  $(item2).attr('name') +
                  '" value="' +
                  $(item2).val() +
                  '" id="default_' +
                  temp_id +
                  '" />';
                if ($(item2).attr('name') == 'price_s') {
                  content_ret += '원 ~ ';
                }
                if ($(item2).attr('name') == 'price_e') {
                  content_ret += '원';
                }
                if (
                  $(item2).attr('type') == 'radio' ||
                  $(item2).attr('type') == 'checkbox'
                ) {
                  var labelText = $(
                    'label[for=' + $(item2).attr('id') + ']'
                  ).text();
                  // if (labelText !== '전체') {
                  content_ret +=
                    '<label for="default_' +
                    $(item2).attr('id') +
                    '">' +
                    labelText +
                    '</label>';
                  // }
                }
              }
            }
            if ($(this).is('select')) {
              content_ret += '<select name="' + $(item2).attr('name') + '">';

              $(item2)
                .find('option')
                .each(function (index3, item3) {
                  content_ret +=
                    '<option value="' +
                    $(item3).val() +
                    '">' +
                    $(item3).text() +
                    '</option>';
                });

              content_ret += '</select>';
            }
            if ($(this).is('h2')) {
              content_ret += '<b>' + $(item2).text() + '</b>';
            }
            if ($(this).is('span')) {
              content_ret += '<b>' + $(item2).text() + '</b>';
            }
            /*
                if ( $(this).is('button') ) {
                    console.log($(this));
                    var temp_id = $(item2).attr("id") || $(item2).attr("name");
                    content_ret += '<input type="' + $(item2).attr("type") + '" name="' + $(item2).attr("onclick") + '" value="' + $(item2).val() + '" id="default_' + temp_id + '" />';
                }
                */
          });

        ret += '<tr>';
        ret += '<th>' + title + '</th>';
        ret += '<td>' + content_ret + '</td>';
        ret += '</tr>';
      })
      .promise()
      .done(function () {
        $('#fdefaultsettingform_form tbody').html(ret);
        $('#default_fr_date, #default_to_date').datepicker({
          changeMonth: true,
          changeYear: true,
          dateFormat: 'yy-mm-dd',
          showButtonPanel: true,
          yearRange: 'c-99:c+99',
          maxDate: '+0d',
        });

        $.ajax({
          method: 'POST',
          url: './ajax.orderlist.defaultsetting.get.php',
          data: {
            menu_id: sub_menu,
          },
        }).done(function (data) {
          // clear_form("#fdefaultsettingform_form");

          var obj = data.data;
          var keys = Object.keys(obj);
          var form = $('#fdefaultsettingform');

          for (_key in obj) {
            if (Array.isArray(obj[_key])) {
              $(obj[_key]).each(function (index, item) {
                set_input_val(form, _key, item);
              });
            } else {
              set_input_val(form, _key, obj[_key]);
            }
          }
        });
      });
  }

  // 기본검색적용
  $('#set_default_apply_button').click(function () {
    $.ajax({
      method: 'POST',
      url: './ajax.orderlist.defaultsetting.get.php',
      data: {
        menu_id: sub_menu,
      },
    }).done(function (data) {
      if (data.data) {
        clear_form('#search_detail_table');

        var obj = data.data;
        var keys = Object.keys(obj);
        var form = $('#search_detail_table');

        for (_key in obj) {
          if (Array.isArray(obj[_key])) {
            $(obj[_key]).each(function (index, item) {
              set_input_val(form, _key, item);
            });
          } else {
            set_input_val(form, _key, obj[_key]);
          }
        }
      }
    });
  });

  // 기본검색설정
  $('#set_default_setting_button').click(function () {
    open_default_setting_form();
    $('#fdefaultsettingform').show();
  });

  // 기본검색설정 저장
  $('#fdefaultsettingform_submit').click(function () {
    var formdata = $.extend(
      {},
      $('#fdefaultsettingform_form').serializeObject(),
      {}
    );

    $.ajax({
      method: 'POST',
      url: './ajax.orderlist.defaultsetting.set.php',
      data: formdata,
    }).done(function (data) {
      if (data.msg) {
        alert(data.msg);
      }
      if (data.result === 'success') {
        $('#fdefaultsettingform').hide();
      }
    });
  });

  // 일괄 출고담당자 변경
  $(document).on('click', '#ct_manager_send_all', function () {
    var ct_id = [];
    var item = $("input[name='od_id[]']:checked");
    var type = $(this).data('type');
    
    var sb1 = $('#ct_manager_sb').val();
    if(!sb1){
        alert('출고담당자를 선택하신 후 변경을 눌러주세요. ');
        return false;
    }

    for (var i = 0; i < item.length; i++) {
      ct_id.push($(item[i]).val());
    }

    if (!ct_id.length) {
      alert('변경하실 주문을 선택해주세요.');
      return;
    }

    $.ajax({
      method: 'POST',
      url: './ajax.ct_manager.php',
      data: {
        ct_id: ct_id,
        ct_manager: sb1,
      },
    }).done(function (data) {
      // return false;
      if (data.msg) {
        alert(data.msg);
      }
      if (data.result === 'success') {
        alert('출고담당자가 지정되었습니다.');
        var arr = $('.ct_manager[data-ct-id="' + ct_id + '"]');
        if (arr.length === 0) {
          location.reload();
          return;
        }
        $.each(arr, function (index, el) {
          $(el).val(sb1).prop("selected", true);
        });
      }
    });
  });

  // 일괄 출하창고 변경
  $(document).on('click', '#ct_warehouse_all', function () {
    var ct_id = [];
    var item = $("input[name='od_id[]']:checked");
    var type = $(this).data('type');
    
    var sb1 = $('#ct_warehouse_sb').val();
    if(!sb1){
        alert('출하창고를 선택하신 후 변경을 눌러주세요. ');
        return false;
    }

    for (var i = 0; i < item.length; i++) {
      ct_id.push($(item[i]).val());
    }

    if (!ct_id.length) {
      alert('변경하실 주문을 선택해주세요.');
      return;
    }

    $.ajax({
      method: 'POST',
      url: './ajax.ct_warehouse_update.php',
      data: {
        ct_id: ct_id,
        ct_warehouse: sb1,
      },
    }).done(function (data) {
      // return false;
      if (data.msg) {
        alert(data.msg);
      }
      if (data.result === 'success') {
        alert('출하창고가 지정되었습니다.');
        // location.reload();
      }
    });
  });

  // 일괄 전송
  $(document).on('click', '#delivery_edi_send_all', function () {
    var ct_id = [];
    var item = $("input[name='od_id[]']:checked");
    var type = $(this).data('type');

    for (var i = 0; i < item.length; i++) {
      ct_id.push($(item[i]).val());
    }

    // if (type && type === 'resend' && !ct_id.length) {
    if (!ct_id.length) {
      alert('전송할 주문을 선택해주세요.');
      return;
    }

    $.ajax({
      method: 'POST',
      url: './ajax.order.delivery.edi.all.php',
      data: {
        ct_id: ct_id,
        type: type,
      },
    }).done(function (data) {
      // return false;
      if (data.msg) {
        alert(data.msg);
      }
      if (data.result === 'success') {
        location.reload();
      }
    });
  });

  // 송장 리턴
  $(document).on('click', '#delivery_edi_return_all', function () {
    var od_id = [];
    var item = $("input[name='od_id[]']:checked");

    for (var i = 0; i < item.length; i++) {
      od_id.push($(item[i]).val());
    }

    $.ajax({
      method: 'POST',
      url: './ajax.order.delivery.edi.return.all.php',
      data: {
        od_id: od_id,
      },
    }).done(function (data) {
      if (data.msg) {
        alert(data.msg);
      }
      if (data.result === 'success') {
        location.reload();
      }
    });
  });

  // 롯데택배 일괄 전송
  $(document).on('click', '#delivery_lotte_send', function () {

    $.ajax({
      method: 'POST',
      url: './ajax.order.delivery.lotte.php',
    }).done(function (data) {
      // return false;
      if (data.msg) {
        alert(data.msg);
      }
      if (data.result === 'success') {
        location.reload();
      }
    });
  });

  $('.select_date').click(function () {
    var val = $(this).val();
    set_date(val);
  });

  $(document).on('click', '.open_member_pop', function (e) {
    e.preventDefault();

    var mb_id = $(this).data('mb-id');
    // console.log(mb_id);

    if (!mb_id) {
      alert('비회원입니다.');
      return;
    }

    window.open(
      g5_admin_url +
        '/member_form.php?sst=&sod=&sfl=&stx=&page=&w=u&mb_id=' +
        mb_id,
      '_blank'
    );
  });

  // 선택 계산서 발행 확인
  $(document).on('click', '#select_important', function () {
    var has_selected = false;
    $.map(
      $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked'),
      function (obj, i) {
        has_selected = true;

        var star = $(obj).closest('tr').find('.list-important');

        if (!star.hasClass('on')) {
          star.click();
        }
      }
    );

    if (!has_selected) {
      alert('주문을 먼저 선택해주세요.');
      return;
    }
  });

  // 선택 계산서 발행 해지
  $(document).on('click', '#deselect_important', function () {
    var has_selected = false;
    $.map(
      $('#samhwa_order_list_table>div.table td input[type=checkbox]:checked'),
      function (obj, i) {
        has_selected = true;

        var star = $(obj).closest('tr').find('.list-important');

        if (star.hasClass('on')) {
          star.click();
        }
      }
    );

    if (!has_selected) {
      alert('주문을 먼저 선택해주세요.');
      return;
    }
  });

  $(document).on('click', 'input[name="od_id[]"]', function () {
    var od_id = $(this).val();
    $('.tr_' + od_id).toggleClass('on');
  });

  // 기본검색 설정 전체 버튼 추가
  $(document).on(
    'click',
    '#fdefaultsettingform_form input[type="checkbox"]',
    function () {
      var val = $(this).val();
      if (!val || val === 'all' || val === 'all2') {
        $(this)
          .nextUntil('b')
          .each(function (index, item) {
            if ($(item).attr('type') === 'checkbox') {
              $(item).click();
            }
          });
      }
    }
  );

  // 주문리스트, 출고리스트 마우스 오버시 같은 주문 강조
  $(document).on('mouseenter', 'tr.order_tr',
    function() {
      var od_id = $(this).data('od-id');
      var $tr = $('tr.order_tr[data-od-id="' + od_id + '"]');
      if($tr.length >= 1)
        $tr.addClass('hover');
    },
  );
  $(document).on('mouseleave', 'tr.order_tr',
    function() {
      var od_id = $(this).data('od-id');
      $('tr.order_tr[data-od-id="' + od_id + '"]').removeClass('hover');
    },
  );



  //엑셀 다운로드 완료 리셋
  $(document).on('click', '#excel_done', function() {
    if (confirm("다운로드 기록을 삭제하시겠습니까?")) {
      let span = $(this);
      $.ajax({
        method: 'POST',
        url: './ajax.excel.download.reset.php',
        data: {
          ct_id: span.data('ct-id'),
          type: 'excel',
        },
      }).done(function (data) {
        if (data.msg) {
          alert(data.msg);
          if (data.result == 'success')
            span.hide();
        }
      }); 
    }
  });
  // 이카운트 엑셀 다운로드 완료 리셋
  $(document).on('click', '#ecount_excel_done', function() {
    if (confirm("다운로드 기록을 삭제하시겠습니까?")) {
      let span = $(this);
      $.ajax({
        method: 'POST',
        url: './ajax.excel.download.reset.php',
        data: {
          ct_id: span.data('ct-id'),
          type: 'ecount',
        },
      }).done(function (data) {
        if (data.msg) {
          alert(data.msg);
          if (data.result == 'success')
            span.hide();
        }
      });
    }
  });
});

function changeDeliverySelect(x) {
  // var optionSelected = $(x).find("option:selected");
  // var valueSelected = optionSelected.val();
  // $(x).siblings('input[name=od_delivery_text]').attr("readonly", false);
  // if (valueSelected == 'ilogen') {
  //     $(x).siblings('input[name=od_delivery_text]').attr("readonly", true);
  // }
}

function changeDeliveryInfo(od_id, x) {
  var od_delivery_company = $(x)
    .siblings('select[name=od_delivery_company]')
    .val();
  var od_delivery_text = $(x).siblings('input[name=od_delivery_text]').val();

  $.ajax({
    method: 'POST',
    url: './ajax.order.delivery.change.info.php',
    data: {
      od_id: od_id,
      od_delivery_company: od_delivery_company,
      od_delivery_text: od_delivery_text,
    },
  }).done(function (data) {
    if (data.msg) {
      alert(data.msg);
    }
  });
}