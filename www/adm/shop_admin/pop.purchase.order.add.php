<?php
// $sub_menu = '400480';
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');

// auth_check($auth[$sub_menu], "w");

$title = '발주서 생성';
include_once('./pop.head.php');
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

  .form_section {
    padding: 10px;
    border: 1px solid #fff;
  }

  .form_section.active {
    border: 1px solid red;
  }
</style>
<div style="padding-bottom: 60px">
  <div id="pop_order_add" class="admin_popup admin_popup_padding purchase_order">
    <h4 class="h4_header"><?php echo $title; ?></h4>
    <p style="position: absolute;left: 436px;top: 20px;font-size: 14px;font-weight: 800;">총 발주 금액 : <span class="total_price_span">0</span>원</p>
    <input type="button" class="shbtn lineblue add_form_section" value="파트너 추가" style="position: absolute;right: 20px;top: 13px;">
    <form class="form_section active">
      <div class="info">
        <table>
          <tr>
            <th>입고예정일</th>
            <td>
              <div style="position:relative;">
                <input type="text" name="od_datetime_date" value="" class="frm_input datepicker" style="min-width: 100px;width: 100px;" autocomplete="off">
                <input type="text" name="od_datetime_time" value="" class="frm_input timepicker" style="min-width: 35px;width: 35px;" autocomplete="off"> 시
                <input type="button" class="shbtn remove_form_section" value="삭제" style="position: absolute;right: 0;line-height: 17px;"/>
              </div>
            </td>
          </tr>
          <tr>
            <th>물품공급파트너</th>
            <td>
              <div>
                <input type="text" name="mb_id" value=""class="frm_input mb_id_flexdatalist">
                <span class="mb_id_flexdatalist_result">파트너를 검색하세요.</span>
              </div>
            </td>
          </tr>
        </table>
      </div>
      <div class="pop_order_add_item">
        <div class="header">
          <h5 class="h5_header">발주정보</h5>
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
              구매가
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
              배송지
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
    </form>

    <div class="form_section_html" style="display: none">
      <div class="info">
        <table>
          <tr>
            <th>입고예정일</th>
            <td>
              <div style="position:relative;">
                <input type="text" name="od_datetime_date" value="" class="frm_input datepicker" style="min-width: 100px;width: 100px;">
                <input type="text" name="od_datetime_time" value="" class="frm_input timepicker" style="min-width: 35px;width: 35px;"> 시
                <input type="button" class="shbtn remove_form_section" value="삭제" style="position: absolute;right: 0;line-height: 17px;"/>
              </div>
            </td>
          </tr>
          <tr>
            <th>물품공급파트너</th>
            <td>
              <div>
                <input type="text" name="mb_id" value=""class="frm_input mb_id_flexdatalist">
                <span class="mb_id_flexdatalist_result">파트너를 검색하세요.</span>
              </div>
            </td>
          </tr>
        </table>
      </div>
      <div class="pop_order_add_item">
        <div class="header">
          <h5 class="h5_header">발주정보</h5>
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
              구매가
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
              배송지
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
          <p class="qty_info"></p>
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
          <select name="wh_name[]" id="it_warehousing_warehouse" name="wh_name">
            <option value="">창고선택</option>
            <?php
            $warehouse_list = get_warehouses();
            foreach($warehouse_list as $warehouse) {
              echo '<option value="'.$warehouse.'" '.get_selected($it['it_warehousing_warehouse'], $warehouse).'>'.$warehouse.'</option>';
            }
            ?>
          </select>
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
        <input type="button" class="submitInput" onclick="formcheck()" value="생성 (F8)" />
      </div>
    </div>
  </div>
</div>

<script>

  var loading = false;

  // 기본 설정
  var mb_level = 3;
  var mb_id = '';
  var item_sale_obj = {};

  function formcheck(f) {
    var val, result = true;

    $('.form_section').each(function (index) {
      if (!$(this).find("input[name^=mb_id]").val()) {
        alert((index + 1) + "번째 주문서의 물품공급파트너를 입력하세요.");
        result = false;
        return false;
      }

      $(this).find("input[name^=qty]").each(function(index) {
        val = $(this).val();

        if(parseInt(val.replace(/[^0-9]/g, "")) < 1) {
          alert((index + 1) + "번째 주문서 아이템의 수량을 1이상으로 입력하세요.");
          result = false;
          return false;
        }
      });


      $(this).find("input[name^=it_price]").each(function(index) {
        val = $(this).val();

        if(parseInt(val.replace(/[^0-9]/g, "")) < 0) {
          alert((index + 1) + "번째 주문서 아이템의 단가를 0이상으로 입력하세요.");
          result = false;
          return false;
        }
      });

      if(!result) {
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

    submitForm();
  }

  function submitForm() {
    $('.form_section').each(function(index, item) {
      $.ajax({
        type: 'POST',
        url: 'pop.purchase.order.add_result.php',
        data: $(this).serialize(),
        dataType: 'json',
        async: false
      })
      .done(function () {
        console.log('저장 완료');
      })
      .fail(function ($xhr) {
        var data = $xhr.responseJSON;
        console.error(data && data.message);
      });
    });

    alert('저장 되었습니다.');
    parent.window.location.reload();
  }

  function initialDateTimePicker() {
    $('.datepicker').datetimepicker({
      locale: 'kr',
      format: 'YYYY-MM-DD',
      defaultDate: new Date(),
    });
    $('.timepicker').datetimepicker({
      locale: 'kr',
      format: 'hh',
      defaultDate: new Date(),
    });
  }

  function initialMbIdFlexDataList(node) {
    var target = $('.form_section').first().find('.mb_id_flexdatalist');
    if (node) {
      target = node;
    }

    target.flexdatalist({
      minLength: 1,
      url: '/adm/ajax.get_mb_id.php?only_supply_partner=true',
      cache: false, // cache
      searchContain: true, // %검색어%
      noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
      visibleProperties: ["mb_name", "mb_nick"],
      // visibleClassName: 'mb_id',
      searchIn: ["mb_id","mb_name","mb_nick","mb_tel", "mb_hp","mb_email", "mb_name_no_space", "mb_nick_no_space"],
      selectionRequired: true,
      focusFirstResult: true,
      visibleCallback: function($li, item, options) {
        var $item = {};
        $item = $('<span>')
          .html(item.mb_name + " (" + item.mb_nick + ")");

        $item.appendTo($li);
        return $li;
      },
    }).on("select:flexdatalist",function(event, obj, options){
      $(this).closest('.form_section').find('.mb_id_flexdatalist_result').text(
        obj.mb_name + "(" + obj.mb_id + ")" + " / HP: " + obj.mb_hp + " / Tel: " + obj.mb_tel
      );

      mb_level = obj.mb_level;
      mb_id = obj.mb_id;

      $(this).closest('.form_section').find('.pop_order_add_item_table tbody tr').each(function() {
        $(this).find('.item_flexdatalist').flexdatalist('url', './ajax.get_item.php?mb_id=' + mb_id);
      });


      $(this).closest('.form_section').find('.item_flexdatalist').first().next().focus();
    });
  }

  $(function() {
    initialDateTimePicker();
    initialMbIdFlexDataList();

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
        console.log(obj)
        var parent = $(this).closest('tr');

        // it_id
        $(parent).find('input[name="it_id[]"]').val(obj.id);

        // 우수사업소 할인 가격 적용
        // if(mb_level == 4 && parseInt(obj.it_price_dealer2) > 0)
        //   obj.it_price = obj.it_price_dealer2;

        // 발주 구매 가격 설정이 안되어있을 경우
        if (!obj.it_purchase_order_price) {
          obj.it_purchase_order_price = 0
        }

        // option
        var it_price = parseInt(obj.it_purchase_order_price);
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

        // 발주 최소 수량 데이터 저장
        var it_purchase_order_min_qty = 0
        if (obj.it_purchase_order_min_qty) {
          it_purchase_order_min_qty = Number(obj.it_purchase_order_min_qty);
        }
        $(parent).find('input[name="qty[]"]').data('it_purchase_order_min_qty', it_purchase_order_min_qty);

        // 발주 최소 단위 데이터 저장
        var it_purchase_order_unit = 0
        if (obj.it_purchase_order_unit) {
          it_purchase_order_unit = Number(obj.it_purchase_order_unit);
        }
        $(parent).find('input[name="qty[]"]').data('it_purchase_order_unit', it_purchase_order_unit);

        // 수량 info 반영
        var infoArr = [];
        if (it_purchase_order_min_qty > 0) {
          infoArr.push('최소 : ' + addComma(it_purchase_order_min_qty) + '개');
        }
        if (it_purchase_order_unit > 0) {
          infoArr.push('단위 : ' + addComma(it_purchase_order_unit) + '개');
        }

        if (infoArr.length > 0) {
          $(parent).find('.qty_info').text(infoArr.join(', '));
        }

        $(parent).find('input[name="qty[]"]').val(it_purchase_order_min_qty === 0 ? 1 : it_purchase_order_min_qty);
        $(parent).find('input[name="it_price[]"]').val(addComma(it_price));

        // 공급가액, 부가세
        $(parent).find('.basic_price').text(addComma(Math.round(it_price / 1.1)) + "원");
        $(parent).find('.tax_price').text(addComma(Math.round(it_price / 11)) + "원");

        // 기본가격 저장
        // $(parent).find('.price').val(obj.it_price);
        $(parent).find('.price').val(it_price);

        // // 묶음 할인 저장
        // item_sale_obj[obj.id] = {
        //   it_sale_cnt: [
        //     obj.it_sale_cnt,
        //     obj.it_sale_cnt_02,
        //     obj.it_sale_cnt_03,
        //     obj.it_sale_cnt_04,
        //     obj.it_sale_cnt_05,
        //   ],
        //   it_sale_percent: [
        //     obj.it_sale_percent,
        //     obj.it_sale_percent_02,
        //     obj.it_sale_percent_03,
        //     obj.it_sale_percent_04,
        //     obj.it_sale_percent_05,
        //   ],
        //   it_sale_percent_great: [
        //     obj.it_sale_percent_great,
        //     obj.it_sale_percent_great_02,
        //     obj.it_sale_percent_great_03,
        //     obj.it_sale_percent_great_04,
        //     obj.it_sale_percent_great_05
        //   ],
        // }

        if ($(parent).index() + 1 >= $('.pop_order_add_item_table tbody tr').length) {
          $('.add_cart').click();
        }

        $('input[name="qty[]"]').trigger('change');
      });
    }

    $(document).on("click", ".apply_address", function (e) {
      e.preventDefault();

      var name = $(this).data('name');
      var tel = $(this).data('tel');
      var hp = $(this).data('hp');
      var addr = $(this).data('addr');

      $('input[name="od_b_name"]').val(name);
      $('input[name="od_b_tel"]').val(tel);
      $('input[name="od_b_addr1"]').val(addr);
    });

    $(document).on("click", ".delete_cart", function () {
      var parent = $(this).closest('tr').remove();

      $('.pop_order_add_item_table tbody tr').each(function(index) {
        $(this).find('.index').text(index + 1)
      })
    });

    $(document).on("click", ".add_cart", function () {
      var html_node = $('.add_item_html tbody');
      $(this).closest('.form_section').find('.pop_order_add_item_table tbody').append(
        $(html_node).html()
      );

      $(this).closest('.form_section').find('.pop_order_add_item_table tbody tr').each(function(index) {
        $(this).find('.index').text(index + 1)
      })

      add_flexdatalist(
        $(this).closest('.form_section').find('.pop_order_add_item_table tbody').find('tr').last().find('.item_flexdatalist')
      );
    });

    $(document).on("click", ".clear_cart", function () {
      $(this).closest('.form_section').find('.pop_order_add_item_table tbody').html('');

      $(this).siblings('.add_cart').click();
      $(this).siblings('.add_cart').click();
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
        // var sale_qty = 0;
        // var targets = [];
        // $('.pop_order_add_item_table input[name="it_id[]"]').each(function() {
        //   var this_parent = $(this).closest('tr');
        //   if($(this).val() == it_id) {
        //     var this_qty = $(this_parent).find('input[name="qty[]"]').val().replace(/[\D\s\._\-]+/g, "");
        //     sale_qty += parseInt(this_qty);
        //     targets.push({
        //       it_price: $(this_parent).find('input[name="it_price[]"]'),
        //       qty: $(this_parent).find('input[name="qty[]"]'),
        //       basic_price: $(this_parent).find('.basic_price'),
        //       tax_price: $(this_parent).find('.tax_price'),
        //     });
        //   }
        // });
        // if (item_sale_obj[it_id]['it_sale_cnt']) {
        //   for(var sale_cnt = 0; sale_cnt < item_sale_obj[it_id]['it_sale_cnt'].length; sale_cnt++){
        //     var temp = parseInt(item_sale_obj[it_id]['it_sale_cnt'][sale_cnt])
        //     if(temp <= sale_qty) {
        //       if(it_sale_cnt < temp) {
        //         it_sale_cnt = temp;
        //         it_price = mb_level == 4 ? item_sale_obj[it_id]['it_sale_percent_great'][sale_cnt] : item_sale_obj[it_id]['it_sale_percent'][sale_cnt];
        //       }
        //     }
        //   }
        // }

        // for(var i = 0; i < targets.length; i++) {
        //   var target = targets[i];
        //   if (parseInt(it_price, 10) !== parseInt(target.it_price.val().replace(/[\D\s\._\-]+/g, ""), 10)) {
        //     (function(it_price) {
        //       it_price.animate({'opacity': 0} ,50 , function () {
        //         it_price.animate({'opacity': 1}, 50);
        //       });
        //     })(target.it_price);
        //
        //     target.it_price.val(addComma(it_price || 0));
        //
        //     // 공급가액, 부가세
        //     var target_qty = parseInt(target.qty.val().replace(/[\D\s\._\-]+/g, ""));
        //     target.basic_price.text(addComma(Math.round(it_price * target_qty / 1.1) || 0) + "원");
        //     target.tax_price.text(addComma(Math.round(it_price * target_qty / 11) || 0) + "원");
        //   }
        // }
      }

      // 최수 수량, 최소 단위 강조 표시
      if ($(this).attr('name') === 'qty[]') {
        var currentQty = Number($(this).val());
        var warnFlag = false;
        // 최소 수량
        if (Number($(this).data('it_purchase_order_min_qty')) > 0) {
          if (currentQty < Number($(this).data('it_purchase_order_min_qty'))) {
            warnFlag = true;
          }
        }

        // 최소 단위
        if (Number($(this).data('it_purchase_order_unit')) > 0) {
          if (currentQty % Number($(this).data('it_purchase_order_unit')) !== 0) {
            warnFlag = true;
          }
        }

        if (warnFlag) {
          $(this).css('border', '1px solid red');
        } else {
          $(this).removeAttr("style");
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

      // 총 발주 금액
      var totalPrice = 0;
      $('input[name="qty[]"]').each(function() {
        var _qty = $(this).val().replace(/[^0-9]/g, "");
        var _price = $(this).closest('tr').find('input[name="it_price[]"]').val().replace(/[^0-9]/g, "");

        totalPrice += (Number(_qty) * Number(_price));
      })

      $('.total_price_span').text(addComma(totalPrice));
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

    $('input[name="od_b_tel"]').on('keyup', function() {
      var num = $(this).val();
      num.trim();
      this.value = auto_phone_hypen(num) ;
    });

    // 초기
    $('.add_cart').click();
    $('.add_cart').click();
  });

</script>

<script>
  $(function() {
    $(document).on('click', '.form_section', function() {
      $('.form_section').removeClass('active');
      $(this).addClass('active');
    });

    $(document).on("click", ".add_form_section", function () {
      var formSectionHtml = $('.form_section_html').html();
      var lastFormSection = $('.form_section').last();

      var html = '<form class="form_section">';
      html += formSectionHtml;
      html += '</form>';

      lastFormSection.after(html);

      $('.form_section').last().find('.add_cart').click();
      $('.form_section').last().find('.add_cart').click();

      initialDateTimePicker();
      initialMbIdFlexDataList($('.form_section').last().find('.mb_id_flexdatalist'));
    });

    $(document).on("click", ".remove_form_section", function () {
      var formSectionCount = $('.form_section').length;

      if (formSectionCount <= 1) {
        alert('최소 1개의 파트너 발주가 필요합니다.');
        return;
      }

      $(this).closest('.form_section').remove();
    });
  })
</script>

</body>
</html>