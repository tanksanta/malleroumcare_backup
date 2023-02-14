<?php
// $sub_menu = '400480';
include_once('./_common.php');


$od_id = clean_xss_tags(trim($_GET['od_id']));
if (!$od_id) { alert("잘못된 주문 번호 입니다."); }

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
if( $od_id ) $sql = " select * from purchase_order where od_id = '$od_id' AND od_del_yn = 'N' ";
$od = sql_fetch($sql);

if (!$od['od_id']) { alert("해당 주문번호로 주문서가 존재하지 않습니다."); }

$sql_ct = "
  SELECT 
    ca_id, ct.*
  FROM 
    purchase_cart ct
  LEFT JOIN
    g5_shop_item it
  ON
    ct.it_id=it.it_id
  WHERE
    od_id = '$od_id'
    AND ct_status NOT IN ('관리자발주취소')";
$result_ct = sql_query($sql_ct);

$_array_ct_list = [];
for( $i=0; $_ct=sql_fetch_array($result_ct); $i++ ) {
  $_array_ct_list[$i] = $_ct;
}

$ca_arr = ['1010','1040','1050','10a0','2010','2080','7020','7030','7040','7050','7060','7070'];

$title = '발주서 수정';
include_once('./pop.head.php');

?>
<!doctype html>
<html lang="ko">

  <head>
    <script>
      
      var LOADING = false;
      var MB_LEVEL = 3;
      var MB_ID = '';
      var ITEM_SALE_OBJ = {};
      var ca_arr = ['1010','1040','1050','10a0','2010','2080','7020','7030','7040','7050','7060','7070'];

      $(function () {
        initialDateTimePicker();

        $(document).on("click", ".order_mod_close", function (e) {
          e.preventDefault();
          $('#popup_order_mod', parent.document).hide();
          $('#hd', parent.document).css('z-index', 10);
        });

        $(document).on("click", ".add_cart", function () {
          var html_node = $('.add_item_html tbody');
          $(this).closest('.form_section').find('.pop_order_add_item_table tbody').append(
            $(html_node).html()
          );

          $(this).closest('.form_section').find('.pop_order_add_item_table tbody tr').each(function (index) {
            $(this).find('.index').text(index + 1)
          })

          add_flexdatalist(
            $(this).closest('.form_section').find('.pop_order_add_item_table tbody').find('tr').last().find('.item_flexdatalist')
          );
        });

        $(document).on("change keyup paste", ".it_option select, input[name='qty[]'], input[name='it_price[]']", function (e) {
          var parent = $(this).closest('tr');

          // var io_price = $(this).find('option:selected').data('price');
          var io_price = $(parent).find('.it_option option:selected').data('price');
          var price = $(parent).find('.price').val();
          var it_price = parseInt($(parent).find('input[name="it_price[]"]').val().replace(/[\D\s\._\-]+/g, "")) || 0;
          // var it_price = parseInt(price || 0) + parseInt(io_price || 0);
          it_price = it_price ? parseInt(it_price, 10) : 0;
          var qty = $(parent).find('input[name="qty[]"]').val().replace(/[\D\s\._\-]+/g, "");
          qty = qty ? parseInt(qty, 10) : 0;

          if ($(this).attr('name') === 'qty[]' || $(this).attr('name') === 'io_id[]') {
            var it_id = $(parent).find('input[name="it_id[]"]').val();
            var it_sale_cnt = 0;
          }

          // 최수 수량, 최소 단위 강조 표시
          if ($(this).attr('name') === 'qty[]') {
            var currentQty = Number($(this).val().replace(/[^0-9]/g, ''));
            var warnFlag = false;
            // 최소 수량
            if (Number($(this).data('it_purchase_order_min_qty')) > 0) {
              if (currentQty < Number($(this).data('it_purchase_order_min_qty'))) { warnFlag = true; }
            }

            // 최소 단위
            if (Number($(this).data('it_purchase_order_unit')) > 0) {
              if (currentQty % Number($(this).data('it_purchase_order_unit')) !== 0) { warnFlag = true; }
            }

            if (warnFlag) { $(this).css('border', '1px solid red'); } else { $(this).removeAttr("style"); }
          }

          // 단가
          if ($(this).attr('name') === 'it_price[]') {
            it_price = $(parent).find('input[name="it_price[]"]').val().replace(/[\D\s\._\-]+/g, "");
            it_price = it_price ? parseInt(it_price, 10) : 0;
          }

          $(parent).find('input[name="it_price[]"]').val(addComma(it_price || 0));

          // 공급가액, 부가세
          if(jQuery.inArray($(parent).find('.ca_id').val(), ca_arr) > -1){
            $(parent).find('.basic_price').text(addComma(it_price * qty) + "원");
            $(parent).find('.tax_price').text("0원");
          } else {
            $(parent).find('.basic_price').text(addComma(Math.round(it_price * qty / 1.1) || 0) + "원");
            $(parent).find('.tax_price').text(addComma(Math.round(it_price * qty / 11) || 0) + "원");
          }

          // 총 발주 금액
          var totalPrice = 0;
          $('input[name="qty[]"]').each(function () {
            var _qty = $(this).val().replace(/[^0-9]/g, "");
            var _price = $(this).closest('tr').find('input[name="it_price[]"]').val().replace(/[^0-9]/g, "");

            totalPrice += (Number(_qty) * Number(_price));
          })

          $('.total_price_span').text(addComma(totalPrice));
        });

        $(document).on("click", ".delete_cart", function () {
          var result = true;
          //var parent = $(this).closest('tr').remove();
          var parent = $(this).closest('tr');
          var qty = $(parent).find('input[name="qty[]"]').val().replace(/[\D\s\._\-]+/g, "");
          var it_id = $(parent).find('input[name="it_id[]"]').val();
          var it_name = $(parent).find('input[name="it_name[]"]').val();

          // 파트너에서 발송된 수량 및 입력된 수량 체크
          $.ajax({
            url: 'ajax.purchase.order.mod_result.php',
            type: 'POST',
            data: {
              mode: 'check_del', 
              od_id: '<?php echo $od_id ?>',
              it_id: it_id,
              qty: qty
            },
            dataType: 'json',
            async: false,
            success : function(val){
              if( val['message'].yn == 'S' ) {
                alert('발주서 발송 완료로 인하여 삭제할 수 없습니다.\n상품 삭제가 필요한 경우, 해당 발주는 ‘발주취소’ 처리하고 신규로 생성해주세요.');
                result = false;
                return false;
              } else if ( val['message'].yn == 'N' ){
                alert('[ 상품명: '+it_name+' ]\n입고완료(or부분출고) 정보가 확인되어 삭제할 수 없습니다.\n\n출고수량: '+val['message'].qty);
                result = false;
                return false;
              } 
            }
          });
          if(!result) { return false; }

          var parent = $(this).closest('tr').remove();
          $('.pop_order_add_item_table tbody tr').each(function (index) { $(this).find('.index').text(index + 1) })

          var totalPrice = 0;
          $('input[name="qty[]"]').each(function () {
            var _qty = $(this).val().replace(/[^0-9]/g, "");
            var _price = $(this).closest('tr').find('input[name="it_price[]"]').val().replace(/[^0-9]/g, "");
            totalPrice += (Number(_qty) * Number(_price));
          })

          // 총 할인 금액
          var totaldiscount = 0;
          $('input[name="r_discount_qty[]"]').each(function () {
            var _discount_qty = $(this).val().replace(/[^0-9]/g, "");
            var _r_discount_price = $(this).closest('tr').find('input[name="r_discount_it_price[]"]').val().replace(/[^0-9]/g, "");
            totaldiscount += (Number(_discount_qty) * Number(_r_discount_price));
          })
          $('input[name="d_discount_qty[]"]').each(function () {
            var _discount_qty = $(this).val().replace(/[^0-9]/g, "");
            var _d_discount_price = $(this).closest('tr').find('input[name="d_discount_it_price[]"]').val().replace(/[^0-9]/g, "");
            totaldiscount += (Number(_discount_qty) * Number(_d_discount_price));
          })

          $('.total_price_span').text(addComma(totalPrice-totaldiscount));
        });

        $(document).on("click", ".add_discount_r", function () {
          var html_node = $('.add_discount_r_html tbody');
          $(this).closest('.form_section').find('.pop_order_add_discount_r_table tbody').append(
            $(html_node).html()
          );

          $(this).closest('.form_section').find('.pop_order_add_discount_r_table tbody tr').each(function (index) {
            $(this).find('.index').text(index + 1)
          })

          add_flexdatalist(
            $(this).closest('.form_section').find('.pop_order_add_discount_r_table tbody').find('tr').last().find('.discount_flexdatalist')
          );
        });

        $(document).on("click", ".add_discount_d", function () {
          var html_node = $('.add_discount_d_html tbody');
          $(this).closest('.form_section').find('.pop_order_add_discount_d_table tbody').append(
            $(html_node).html()
          );

          $(this).closest('.form_section').find('.pop_order_add_discount_d_table tbody tr').each(function (index) {
            $(this).find('.index').text(index + 1)
          })

          add_flexdatalist(
            $(this).closest('.form_section').find('.pop_order_add_discount_d_table tbody').find('tr').last().find('.discount_flexdatalist')
          );
        });


        $(document).on("change keyup paste", "input[name='r_discount_qty[]'], input[name='r_discount_it_price[]'],input[name='d_discount_qty[]'], input[name='d_discount_it_price[]']", function (e) {
          var parent = $(this).closest('tr');

          var price = $(parent).find('.price').val();
          var it_price = parseInt($(parent).find('.discount_it_price').val().replace(/[\D\s\._\-]+/g, "")) || 0;
          it_price = it_price ? parseInt(it_price, 10) : 0;

          var qty = $(parent).find('.discount_qty').val().replace(/[\D\s\._\-]+/g, "");
          qty = qty ? parseInt(qty, 10) : 0;
          
          // 단가
          if ($(this).attr('name').substring(2,19) === 'discount_it_price[]') {
            it_price = $(parent).find('.discount_it_price').val().replace(/[\D\s\._\-]+/g, "");
            it_price = it_price ? parseInt(it_price, 10) : 0;
          }

          $(parent).find('.discount_qty').val(addComma(qty || 0));
          $(parent).find('.discount_it_price').val(addComma(it_price || 0));

          // 공급가액, 부가세
          $(parent).find('.basic_price').text(addComma(Math.round(it_price * qty / 1.1) || 0) + "원");
          $(parent).find('.tax_price').text(addComma(Math.round(it_price * qty / 11) || 0) + "원");
          
          // 총 발주 금액
          var totalPrice = 0;
          $('input[name="qty[]"]').each(function () {
            var _qty = $(this).val().replace(/[^0-9]/g, "");
            var _price = $(this).closest('tr').find('input[name="it_price[]"]').val().replace(/[^0-9]/g, "");

            totalPrice += (Number(_qty) * Number(_price));
          })

          // 총 할인 금액
          var totaldiscount = 0;
          $('input[name="r_discount_qty[]"]').each(function () {
            var _discount_qty = $(this).val().replace(/[^0-9]/g, "");
            var _r_discount_price = $(this).closest('tr').find('input[name="r_discount_it_price[]"]').val().replace(/[^0-9]/g, "");

            totaldiscount += (Number(_discount_qty) * Number(_r_discount_price));
          })
          $('input[name="d_discount_qty[]"]').each(function () {
            var _discount_qty = $(this).val().replace(/[^0-9]/g, "");
            var _d_discount_price = $(this).closest('tr').find('input[name="d_discount_it_price[]"]').val().replace(/[^0-9]/g, "");

            totaldiscount += (Number(_discount_qty) * Number(_d_discount_price));
          })

          $('.total_price_span').text(addComma(totalPrice-totaldiscount));
        });
        

        $(document).on("click", ".delete_discount", function () {
          var parent = $(this).closest('tr').remove();
          $('.pop_order_add_item_table tbody tr').each(function (index) { $(this).find('.index').text(index + 1) })

          var totalPrice = 0;
          $('input[name="qty[]"]').each(function () {
            var _qty = $(this).val().replace(/[^0-9]/g, "");
            var _price = $(this).closest('tr').find('input[name="it_price[]"]').val().replace(/[^0-9]/g, "");
            totalPrice += (Number(_qty) * Number(_price));
          })

          // 총 할인 금액
          var totaldiscount = 0;
          $('input[name="r_discount_qty[]"]').each(function () {
            var _discount_qty = $(this).val().replace(/[^0-9]/g, "");
            var _r_discount_price = $(this).closest('tr').find('input[name="r_discount_it_price[]"]').val().replace(/[^0-9]/g, "");
            totaldiscount += (Number(_discount_qty) * Number(_r_discount_price));
          })
          $('input[name="d_discount_qty[]"]').each(function () {
            var _discount_qty = $(this).val().replace(/[^0-9]/g, "");
            var _d_discount_price = $(this).closest('tr').find('input[name="d_discount_it_price[]"]').val().replace(/[^0-9]/g, "");
            totaldiscount += (Number(_discount_qty) * Number(_d_discount_price));
          })

          $('.total_price_span').text(addComma(totalPrice-totaldiscount));
        });


      });

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

      function add_flexdatalist(node) {
        $(node).flexdatalist({
          minLength: 1,
          url: './ajax.get_item.php?mb_id=' + MB_ID,
          cache: false, // cache
          searchContain: true, // %검색어%
          noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
          selectionRequired: true,
          focusFirstResult: true,
          searchIn: ["it_name", "it_model", "id", "it_name_no_space"],
          visibleCallback: function ($li, item, options) {
          var $item = {};
          $item = $('<span>')
          .html("[" + item.gubun + "] " + item.it_name + " (" + item.it_price + "원)");

          $item.appendTo($li);
          return $li;
          },
        }).on("select:flexdatalist", function (event, obj, options) {
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
            for (var i = 0; i < obj.options.length; i++) {
              if (i === 0) {
                it_price += parseInt(obj.options[i]['io_price']);
              }
              option_html += "<option data\-price=\"" + obj.options[i]['io_price'] + "\" value=\"" + obj.options[i]['io_id'] + "\">" + obj.options[i]['io_id'].replace(//gi, " > ") + "</option>";
            }
            option_html += "</select>";
            $(parent).find('.it_option').html(option_html);
            setTimeout(function () {
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
          $(parent).find('input[name="ca_id[]"]').val(obj.ca_id);

          // 공급가액, 부가세 (영세 상품은 부가세 따로 붙지 않음)
          if(jQuery.inArray(obj.ca_id, ca_arr) > -1) {
            $(parent).find('.basic_price').text(addComma(it_price) + "원");
            $(parent).find('.tax_price').text("0원");
          } else {
            $(parent).find('.basic_price').text(addComma(Math.round(it_price / 1.1)) + "원");
            $(parent).find('.tax_price').text(addComma(Math.round(it_price / 11)) + "원");
          }

          // 기본가격 저장
          // $(parent).find('.price').val(obj.it_price);
          $(parent).find('.price').val(it_price);

          if ($(parent).index() + 1 >= $('.pop_order_add_item_table tbody tr').length) {
            $('.add_cart').click();
          }

          $(parent).find('input[name="qty[]"]').trigger('change');
        });
      }

      function formcheck(f) {
        var it_id, it_name, qty, it_price, wh_name, result = true;

        $('.form_section').each(function (formIndex) {
          if (!$(this).find("input[name^=mb_id]").val()) {
            alert("주문서의 물품공급파트너를 입력하세요.");
            result = false;
            return false;
          }

          $(this).find('.pop_order_add_item_table tbody tr').each(function (trIndex) {
            it_id = $(this).find("input[name^=it_id]").val();
            it_name = $(this).find("input[name^=it_name]").val();
            qty = $(this).find("input[name^=qty]").val();
            it_price = $(this).find("input[name^=it_price]").val();

            if (it_id === '') { return true; }

            if (parseInt(qty.replace(/[^0-9]/g, "")) < 1) {
              alert((formIndex + 1) + "번째 주문서의 " + (trIndex + 1 )+ "번째 아이템의 수량을 1이상으로 입력하세요.");
              result = false;
              return false;
            }

            if (parseInt(it_price.replace(/[^0-9]/g, "")) < 0) {
              alert((formIndex + 1) + "번째 주문서의 " + (trIndex + 1 )+ "번째 아이템의 구매가를 1이상으로 입력하세요.");
              result = false;
              return false;
            }

            console.log('<?php echo $od_id ?>');
            console.log(it_id);
            console.log(qty);
            // 파트너에서 발송된 수량 및 입력된 수량 체크
            $.ajax({
              url: 'ajax.purchase.order.mod_result.php',
              type: 'POST',
              data: {
                mode: 'check_qty', 
                od_id: '<?php echo $od_id ?>',
                it_id: it_id,
                qty: qty
              },
              dataType: 'json',
              async: false,
              success : function(val){
                if( val['message'].yn == 'N' ) {
                  alert('[ 상품명: '+it_name+' ]\n부분출고된 수량 이하로 변경이 불가능 합니다.\n\n출고수량: '+val['message'].qty);
                  result = false;
                  return false;
                } 
              }
            })
            if(!result) { return false; }

          });

          wh_name = $(this).find("select[name^=wh_name]").val();
          if(!wh_name) {
            alert((formIndex + 1) + "번째 주문서의 배송 창고를 선택해주세요.");
            result = false;
            return false;
          }

          if(!result) { return false; }
        });

        if(!result) { return false; }
        if(LOADING) { alert('주문서 생성중입니다.'); return false; }

        LOADING = true;

        submitForm();
      }

      function submitForm() {
        $('.form_section').each(function (index, item) {

          console.log($(this).serialize());

          $.ajax({
            type: 'POST',
            url: './pop.purchase.order.mod_result.php',
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
          })
          .always(function() {
            LOADING = false;
          });
        });

        alert('저장 되었습니다.');
        parent.window.location.reload();
      }
    </script>
  </head>

  <body>
    <div style="padding-bottom: 60px">
      <div id="pop_order_add" class="admin_popup admin_popup_padding purchase_order">
        <h4 class="h4_header"><?php echo $title; ?></h4>
        <p style="position: absolute;left: 436px;top: 20px;font-size: 14px;font-weight: 800;">총 발주 금액 : <span class="total_price_span">0</span>원</p>
        
        <form class="form_section active">
          <input type="hidden" name="od_id" value="<?=$od['od_id']?>">
          <div class="info">
            <table>
              <tr>
                <th>입고예정일</th>
                <td>
                  <div style="position:relative;">
                    <input type="text" name="od_datetime_date" value="<?=date("Y-m-d", strtotime($_array_ct_list[0]['ct_delivery_expect_date']));?>" class="frm_input datepicker" style="min-width: 100px;width: 100px;" autocomplete="off">
                  </div>
                </td>
                <th>배송지</th>
                <td>
                  <select class="it_warehousing_warehouse" name="wh_name">
                    <option value="">창고선택</option>
                    <?php
                      $warehouse_list = get_warehouses();

                      foreach ($warehouse_list as $warehouse) {
                        if ($warehouse == '미지정') continue;
                        echo '<option value="'.$warehouse.'" '.get_selected($_array_ct_list[0]['ct_warehouse'], $warehouse).'>'.$warehouse.'</option>';
                      }
                    ?>
                  </select>
                <td>
              </tr>
              <tr>
                <th>물품공급파트너</th>
                <td colspan="3" >
                  <div>
                    <input type="hidden" name="mb_id" value="<?=$od['mb_id']?>">
                    <?=$od['od_name']?>(<?=$od['mb_id']?>) / HP : <?=$od['od_hp']?> / FAX : <?=$od['od_fax']?>
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
                  <th>No.</th>
                  <th>상품명</th>
                  <th>옵션명</th>
                  <th>수량</th>
                  <th>구매가</th>
                  <th>공급가액</th>
                  <th>부가세</th>
                  <th>요청사항</th>
                  <th>삭제</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($_array_ct_list as $key => $val) { ?>
                <tr>
                  <td class="no">
                      <span class="index"><?=($key+1)?></span>
                      <input type="hidden" name="it_id[]" value="<?=$val['it_id']?>">
                      <input type="hidden" name="price[]" class="price" value="<?=$val['ct_price']?>">
                      <input type="hidden" name="ct_id[]" class="ct_id" value="<?=$val['ct_id']?>">
                  </td>
                  <td><input type="text" name="it_name[]" class="frm_input item_flexdatalist" value="<?=$val['it_name']?>" autocomplete="off"> <p class="qty_info"></p> </td>
                  <td><div class="it_option"> <input type="hidden" name="io_id[]" value="<?=$val['io_id']?>"> - </div></td>
                  <td><input type="text" name="qty[]" class="frm_input" value="<?=$val['ct_qty']?>" autocomplete="off"></td>
                  <td><input type="text" name="it_price[]" class="frm_input" value="<?=$val['ct_price']?>" autocomplete="off"></td>
                  <td class="basic_price"><?=in_array($val['ca_id'], $ca_arr)?number_format($val['ct_price'] * $val['ct_qty']):number_format(round($val['ct_price'] * $val['ct_qty'] / 1.1)) ?>원</td>
                  <td class="tax_price"><?=in_array($val['ca_id'], $ca_arr)?'0':number_format(round($val['ct_price'] * $val['ct_qty'] / 11)) ?>원</td>
                  <td><input type="text" name="memo[]" class="frm_input" value="<?=$val['memo']?>"></td>
                  <td><input type="button" class="shbtn small delete_cart" value="삭제" /></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>

          <div class="pop_order_add_discount">
            <div class="header">
              <h5 class="h5_header">반품 정보</h5>
              <div class="btns">
                <input type="button" class="shbtn lineblue add_discount_r" value="추가" />
              </div>
            </div>
            <table class="pop_order_add_discount_r_table">
              <colgroup>
                <col width="5%" />
                <col />
                <col width="7%" />
                <col width="10%" />
                <col width="8%" />
                <col width="8%" />
                <col width="13%" />
                <col width="30px" />
              </colgroup>
              <thead>
              <tr>
                <th>No.</th>
                <th>상품명</th>
                <th>수량</th>
                <th>가격</th>
                <th>공급가액</th>
                <th>부가세</th>
                <th>요청사항</th>
                <th>삭제</th>
              </tr>
              </thead>
              <tbody>
              <?php 
                $_tr = json_decode($od['od_discount_info'],true);
                $_index = 1;
                if( is_array($_tr) ) { foreach ($_tr as $key => $val) { if($val['discount_type'] == 'r') {
              ?>
              <tr>
                <td class="no"><span class="index"><?=$_index?></span></td>
                <td><input type="text" name="r_discount_it_name[]" class="frm_input item_flexdatalist" value="<?=$val['discount_it_name'];?>" autocomplete="off"><p class="qty_info"></p></td>
                <td><input type="text" name="r_discount_qty[]" class="frm_input discount_qty" value="<?=$val['discount_qty'];?>" autocomplete="off"></td>
                <td><input type="text" name="r_discount_it_price[]" class="frm_input discount_it_price" value="<?=$val['discount_it_price'];?>" autocomplete="off"></td>
                <td class="basic_price"><?=number_format(round($val['discount_it_price'] * $val['discount_qty'] / 1.1)) ?>원</td>
                <td class="tax_price"><?=number_format(round($val['discount_it_price'] * $val['discount_qty'] / 11)) ?>원</td>
                <td><input type="text" name="r_discount_memo[]" class="frm_input" value="<?=$val['discount_memo'];?>"></td>
                <td><input type="button" class="shbtn small delete_discount" value="삭제" /></td>
              </tr>
              <?php $_index++; } } } ?>
              </tbody>
            </table>
          </div>
          <div class="pop_order_add_discount">
            <div class="header">
              <h5 class="h5_header">할인 정보</h5>
              <div class="btns">
                <input type="button" class="shbtn lineblue add_discount_d" value="추가" />
              </div>
            </div>
            <table class="pop_order_add_discount_d_table">
              <colgroup>
                <col width="5%" />
                <col />
                <col width="7%" />
                <col width="10%" />
                <col width="8%" />
                <col width="8%" />
                <col width="13%" />
                <col width="30px" />
              </colgroup>
              <thead>
              <tr>
                <th>No.</th>
                <th>상품명</th>
                <th>수량</th>
                <th>가격</th>
                <th>공급가액</th>
                <th>부가세</th>
                <th>요청사항</th>
                <th>삭제</th>
              </tr>
              </thead>
              <tbody>
              <?php
                $_tr = json_decode($od['od_discount_info'],true);
                $_index = 1;
                if( is_array($_tr) ) { foreach ($_tr as $key => $val) { if($val['discount_type'] == 'd') {
              ?>
              <tr>
                <td class="no"><span class="index"><?=$_index?></span></td>
                <td><input type="text" name="d_discount_it_name[]" class="frm_input item_flexdatalist" value="<?=$val['discount_it_name'];?>" autocomplete="off"><p class="qty_info"></p></td>
                <td><input type="text" name="d_discount_qty[]" class="frm_input discount_qty" value="<?=$val['discount_qty'];?>" autocomplete="off"></td>
                <td><input type="text" name="d_discount_it_price[]" class="frm_input discount_it_price" value="<?=$val['discount_it_price'];?>" autocomplete="off"></td>
                <td class="basic_price"><?=number_format(round($val['discount_it_price'] * $val['discount_qty'] / 1.1)) ?>원</td>
                <td class="tax_price"><?=number_format(round($val['discount_it_price'] * $val['discount_qty'] / 11)) ?>원</td>
                <td><input type="text" name="d_discount_memo[]" class="frm_input" value="<?=$val['discount_memo'];?>"></td>
                <td><input type="button" class="shbtn small delete_discount" value="삭제" /></td>
              </tr>
              <?php $_index++; } } } ?>
              </tbody>
            </table>
          </div>

        </form>


    <table class="add_item_html" style="display:none;">
    <tbody>
    <tr>
      <td class="no"><span class="index">1</span><input type="hidden" name="it_id[]"><input type="hidden" name="price[]" class="price"><input type="hidden" name="ca_id[]" class="ca_id"></td>
      <td><input type="text" name="it_name[]" class="frm_input item_flexdatalist" autocomplete="off"> <p class="qty_info"></p> </td>
      <td><div class="it_option"> <input type="hidden" name="io_id[]"> - </div></td>
      <td><input type="text" name="qty[]" class="frm_input" value="1" autocomplete="off"></td>
      <td><input type="text" name="it_price[]" class="frm_input" value="0" autocomplete="off"></td>
      <td class="basic_price">0원</td>
      <td class="tax_price">0원</td>
      <td><input type="text" name="memo[]" class="frm_input"></td>
      <td><input type="button" class="shbtn small delete_cart" value="삭제" /></td>
    </tr>
    </tbody>
    </table>


    <table class="add_discount_r_html" style="display:none;">
      <tbody>
      <tr>
        <td class="no"><span class="index">1</span></td>
        <td><input type="text" name="r_discount_it_name[]" class="frm_input item_flexdatalist" autocomplete="off"><p class="qty_info"></p></td>
        <td><input type="text" name="r_discount_qty[]" class="frm_input discount_qty" value="1" autocomplete="off"></td>
        <td><input type="text" name="r_discount_it_price[]" class="frm_input discount_it_price" value="0" autocomplete="off"></td>
        <td class="basic_price">0원</td>
        <td class="tax_price">0원</td>
        <td><input type="text" name="r_discount_memo[]" class="frm_input"></td>
        <td><input type="button" class="shbtn small delete_discount" value="삭제" /></td>
      </tr>
      </tbody>
    </table>


    <table class="add_discount_d_html" style="display:none;">
      <tbody>
      <tr>
        <td class="no"><span class="index">1</span></td>
        <td><input type="text" name="d_discount_it_name[]" class="frm_input item_flexdatalist" autocomplete="off"><p class="qty_info"></p></td>
        <td><input type="text" name="d_discount_qty[]" class="frm_input discount_qty" value="1" autocomplete="off"></td>
        <td><input type="text" name="d_discount_it_price[]" class="frm_input discount_it_price" value="0" autocomplete="off"></td>
        <td class="basic_price">0원</td>
        <td class="tax_price">0원</td>
        <td><input type="text" name="d_discount_memo[]" class="frm_input"></td>
        <td><input type="button" class="shbtn small delete_discount" value="삭제" /></td>
      </tr>
      </tbody>
    </table>


    <div id="popup_buttom">
      <div class="addoptionbuttons">
        <a href='#' class="order_mod_close">취소</a>
        <input type="button" class="submitInput" onclick="formcheck()" value="수정" />
      </div>
    </div>

    <script>
      window.onload = function(){
        // 총 발주 금액
        var totalPrice = 0;
        $('input[name="qty[]"]').each(function () {
          var _qty = $(this).val().replace(/[^0-9]/g, "");
          var _price = $(this).closest('tr').find('input[name="it_price[]"]').val().replace(/[^0-9]/g, "");

          totalPrice += (Number(_qty) * Number(_price));
        })

        // 총 할인 금액
        var totaldiscount = 0;
        $('input[name="r_discount_qty[]"]').each(function () {
          var _discount_qty = $(this).val().replace(/[^0-9]/g, "");
          var _r_discount_price = $(this).closest('tr').find('input[name="r_discount_it_price[]"]').val().replace(/[^0-9]/g, "");

          totaldiscount += (Number(_discount_qty) * Number(_r_discount_price));
        })
        $('input[name="d_discount_qty[]"]').each(function () {
          var _discount_qty = $(this).val().replace(/[^0-9]/g, "");
          var _d_discount_price = $(this).closest('tr').find('input[name="d_discount_it_price[]"]').val().replace(/[^0-9]/g, "");

          totaldiscount += (Number(_discount_qty) * Number(_d_discount_price));
        })

        $('.total_price_span').text(addComma(totalPrice-totaldiscount));
      }
    </script>

  </body>

</html>
