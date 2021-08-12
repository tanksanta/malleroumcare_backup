<?php
include_once("./_common.php");

if(!$is_member) {
  alert('먼저 로그인하세요.');
}

$sql = "SELECT * FROM {$g5['g5_shop_order_table']} WHERE `od_id` = '$od_id'";
if($is_member && !$is_admin)
  $sql .= " AND mb_id = '{$member['mb_id']}' ";
$od = sql_fetch($sql);
if(!$od['mb_id']) {
  alert('계약서를 생성할 권한이 없습니다.');
}

$eform = sql_fetch("SELECT HEX(`dc_id`) as uuid, e.* FROM `eform_document` as e WHERE od_id = '$od_id' and dc_status='0'");
if(!$eform['uuid']) alert('전자계약서를 생성할 수 없는 상태입니다.');

# 수급자 유효기간이 없으면
if(!$eform['penExpiDtm']) {
  $res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
    'usrId' => $od["mb_id"],
    'entId' => $member["mb_entId"],
    'penId' => $od["od_penId"]
  ));
  if(!$res["data"]) {
    alert('존재하지 않는 수급자에 대한 주문입니다.');
  }
  $penData = $res["data"][0];

  # 시스템 DB에도 수급자 유효기간이 없으면
  if(!$penData['penExpiDtm']) {
    alert('수급자 정보에서 수급자 유효기간을 설정해주세요.');
  }

  # 업데이트
  sql_query("UPDATE `eform_document` SET
    `penExpiDtm` = '{$penData['penExpiDtm']}'
    WHERE `dc_id` = UNHEX('{$eform['uuid']}')
  ");
}

# 전자계약서 정보 업데이트(최신화) - 바코드 정보 새로 계속 가져오기

// 상품 분류별 내구연한 (구매가능 개수)
$limit = get_pen_order_limit($od['od_penId'], $od['od_id']);
$limit_msg = '구매제한 개수를 초과한 상품이 있습니다.\\n구매제한 개수를 초과한 상품은 계약서에 반영되지 않습니다.\\n\\n';

$od_item_ca_id_table = [];
$ca_id_limit_table = [];
foreach($limit as $lm) {
  foreach($lm['od_items'] as $od_item) {
    $od_item_ca_id_table[$od_item] = $lm['ca_id'];
  }
  $limit_msg .= "{$lm['ca_name']}: {$lm['month']}개월 동안 {$lm['limit']}개 구매 가능 (현재 {$lm['current']}개 구매)\\n";
}

sql_query("DELETE FROM `eform_document_item` WHERE `dc_id` = UNHEX('{$eform["uuid"]}')");
$res = api_post_call(EROUMCARE_API_EFORM_SELECT_INITIAL_STATE_LIST, array('penOrdId' => $od["ordId"]));

foreach($res["data"] as $it) {
  $priceEnt = intval($it["prodPrice"]) - intval($it["penPrice"]);
    
  // 비급여 품목은 계약서에서 제외 & 상품분류별 내구연한으로 구매제한개수 초과 품목은 계약서에서 제외
  if ($it["gubun"] != '02' && !$od_item_ca_id_table[$it['prodPayCode']]) {
    sql_query("INSERT INTO `eform_document_item` SET
      `dc_id` = UNHEX('{$eform["uuid"]}'),
      `gubun` = '{$it["gubun"]}',
      `ca_name` = '{$it["itemNm"]}',
      `it_name` = '{$it["prodNm"]}',
      `it_code` = '{$it["prodPayCode"]}',
      `it_barcode` = '{$it["prodBarNum"]}',
      `it_qty` = '1',
      `it_date` = '{$it["contractDate"]}',
      `it_price` = '{$it["prodPrice"]}',
      `it_price_pen` = '{$it["penPrice"]}',
      `it_price_ent` = '$priceEnt'
    ");
  }
}

$items = sql_query("SELECT * FROM `eform_document_item` WHERE dc_id = UNHEX('{$eform["uuid"]}')");

$buy = [];
$rent = [];
while($item = sql_fetch_array($items)) {
  if($item['gubun'] == '00') array_push($buy, $item); // 판매 재고
  else if ($item['gubun'] == '01') array_push($rent, $item); // 대여 재고
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>계약서 생성</title>
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/common_new.css">
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/font.css">
  <link rel="shortcut icon" href="<?php echo THEMA_URL; ?>/assets/img/top_logo_icon.ico">
  <link rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="./css/writeeform.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <?php include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php'); ?>
</head>
<body>
  <div id="popupWrap">
    <div class="popupHeadWrap flex">
      <h1 class="title">계약서 생성</h1>
      <div class="menu">
        <button id="btnResetEform">변경사항 초기화</button>
        <button id="btnCloseEform">&times;</button>
      </div>
    </div>
    <div class="popupContentWrap">
      <div id="penRow" class="row">
        <h3>수급자정보</h3>
        <table id="tablePenInfo">
          <tr>
            <th>수급자</th>
            <td><?=$eform["penNm"]?></td>
          </tr>
          <tr>
            <th>장기요양인정번호</th>
            <td><?=$eform["penLtmNum"]?></td>
          </tr>
          <tr>
            <th>인정등급</th>
            <td><?=$eform["penRecGraNm"]?></td>
          </tr>
          <tr>
            <th>구분</th>
            <td><?=$eform["penTypeNm"]?></td>
          </tr>
        </table>
      </div>
      <div id="prodRow" class="row">
        <div class="flex">
          <h3>공급물품</h3>
          <div class="right" id="agreeAddProd">
            <span class="notice">*계약서 작성을 위해 추가하는 물품은 통합시스템에서 관리되지 않고 계약서 작성에만 활용됩니다.</span>
            <label class="checkbox"><input id="chkConfirm" type="checkbox">확인함</label>
          </div>
        </div>
        <div class="prodContentWrap">
          <div class="prodTableRow">
            <div class="flex">
              <div class="prodHead">구매물품</div>
              <button id="btnAddBuyProd">추가</button>
            </div>
            <div class="prodTableWrap">
              <table id="tableBuyProd">
                <colgroup>
                  <col style="width: 10%">
                  <col style="width: 10%">
                  <col style="width: 15%">
                  <col>
                  <col style="width: 5%">
                  <col>
                  <col style="width: 10%">
                  <col style="width: 10%">
                  <col style="width: 40px">
                </colgroup>
                <thead>
                  <tr>
                    <th>품목명</th>
                    <th>제품명</th>
                    <th>제품기호</th>
                    <th>일련번호(바코드)</th>
                    <th>개수</th>
                    <th>판매계약일</th>
                    <th>고시가</th>
                    <th>본인부담금</th>
                    <th>삭제</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
          <div class="prodTableRow">
            <div class="flex">
              <div class="prodHead">대여물품</div>
              <button id="btnAddRentProd">추가</button>
            </div>
            <div class="prodTableWrap">
              <table id="tableRentProd">
                <colgroup>
                  <col style="width: 10%">
                  <col style="width: 10%">
                  <col style="width: 15%">
                  <col>
                  <col style="width: 5%">
                  <col>
                  <col style="width: 10%">
                  <col style="width: 10%">
                  <col style="width: 40px">
                </colgroup>
                <thead>
                  <tr>
                    <th>품목명</th>
                    <th>제품명</th>
                    <th>제품기호</th>
                    <th>일련번호(바코드)</th>
                    <th>개수</th>
                    <th>계약기간</th>
                    <th>고시가</th>
                    <th>본인부담금</th>
                    <th>삭제</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div id="entConAcc01Row" class="row entConAcc">
        <h3>특약사항1</h3>
        <textarea name="entConAcc01" id="entConAcc01"></textarea>
      </div>
      <div id="entConAcc02Row" class="row entConAcc">
        <h3>특약사항2</h3>
        <textarea name="entConAcc02" id="entConAcc02"></textarea>
      </div>
    </div>
    <div class="popupFootWrap flex">
      <button id="btnSubmitEform">계약서 생성</button>
      <button id="btnCancelEform">취소</button>
    </div>
  </div>
  <script type="text/javascript">
  parent = parent || window.parent;

  function closePopup(e) {
    e.preventDefault();
    $("body", parent.document).removeClass("modal-open");
    $("#popupEformWrite", parent.document).hide();
    $("#popupEformWrite", parent.document).find("iframe").remove();
  }

  $(function(){
    $("#btnCloseEform").click(closePopup);
    $("#btnCancelEform").click(closePopup);

    <?php if($limit) { ?>
    alert('<?=$limit_msg?>');
    <?php } ?>

    var initialStatus = {
      customCounter: 0,
      agreement: false,
      entConAcc01: <?=json_encode($member['mb_entConAcc01'])?>,
      entConAcc02: <?=json_encode($member['mb_entConAcc02'])?>,
      buy: {
        items: [<?php foreach($buy as $item) { ?>
          {
            it_id: '<?=$item['it_id']?>',
            ca_name: '<?=htmlspecialchars($item['ca_name'])?>',
            it_name: '<?=htmlspecialchars($item['it_name'])?>',
            it_code: '<?=htmlspecialchars($item['it_code'])?>',
            it_barcode: '<?=htmlspecialchars($item['it_barcode'])?>',
            it_qty: '<?=$item['it_qty']?>',
            it_date: '<?=htmlspecialchars($item['it_date'])?>',
            it_price: '<?=$item['it_price']?>',
            it_price_pen: '<?=$item['it_price_pen']?>',
            deleted: false
          },
<?php   } ?>],
        customs: []
      },
      rent: {
        items: [<?php foreach($rent as $item) { ?>
          {
            it_id: '<?=$item['it_id']?>',
            ca_name: '<?=htmlspecialchars($item['ca_name'])?>',
            it_name: '<?=htmlspecialchars($item['it_name'])?>',
            it_code: '<?=htmlspecialchars($item['it_code'])?>',
            it_barcode: '<?=htmlspecialchars($item['it_barcode'])?>',
            it_qty: '<?=$item['it_qty']?>',
            it_date: '<?=htmlspecialchars($item['it_date'])?>',
            it_price: '<?=$item['it_price']?>',
            it_price_pen: '<?=$item['it_price_pen']?>',
            deleted: false
          },
<?php   } ?>],
        customs: []
      }
    };

    var status = JSON.parse(JSON.stringify(initialStatus)); // deep copy

    $('#btnResetEform').click(function(e) { // 변경사항 초기화
      e.preventDefault();
      status = JSON.parse(JSON.stringify(initialStatus));
      repaintForm();
    });
    $('#chkConfirm').click(function(e) { // 확인함 체크박스
      status.agreement = !status.agreement;
      repaintForm();
    });
    $('#entConAcc01').on('input propertychange paste', function() { // 특약사항1
      status.entConAcc01 = $(this).val();
    });
    $('#entConAcc02').on('input propertychange paste', function() { // 특약사항2
      status.entConAcc02 = $(this).val();
    });
    $(document).on('input propertychange paste', '.inputItem', function() { // 실제 구매/대여 아이템 필드
      var it_id = $(this).data('id'); 
      var field = $(this).data('field');
      var items = status[$(this).data('gubun')].items;
      for(var i = 0; i < items.length; i++) {
        if(items[i].it_id == it_id) {
          items[i][field] = $(this).val();
        }
      }
    });
    $(document).on('input propertychange paste', '.inputCustom', function() { // 계약서 상 임의로 추가한 아이템 필드
      var temp_id = $(this).data('id'); 
      var field = $(this).data('field');
      var customs = status[$(this).data('gubun')].customs;
      for(var i = 0; i < customs.length; i++) {
        if(customs[i].temp_id == temp_id) {
          customs[i][field] = $(this).val();
        }
      }
    });
    $(document).on('input propertychange paste', '.inputNumber', function() { // 숫자 전용 필드
      var temp_id = $(this).data('id'); 
      var field = $(this).data('field');
      var input = $(this).val();

      input = input.replace(/[\D\s\._\-]+/g, "");
      if(input !== '') {
        input = input ? parseInt( input, 10 ) : 0;
        $(this).val(input.toLocaleString('en-US'));
      } else {
        $(this).val('');
      }

      var customs = status[$(this).data('gubun')].customs;
      for(var i = 0; i < customs.length; i++) {
        if(customs[i].temp_id == temp_id) {
          customs[i][field] = $(this).val();
        }
      }
    });
    var dateFormat = 'yy-mm-dd';
    function getDate(element) {
      var date;
      try {
        date = $.datepicker.parseDate(dateFormat, element.value);
      } catch( error ) {
        date = null;
      }
      return date;
    }
    $(document).on('change', '.datePicker', function() { // 구매물품 날짜 선택
      var temp_id = $(this).data('id');
      var field = $(this).data('field');
      var customs = status.buy.customs;
      for(var i = 0; i < customs.length; i++) {
        if(customs[i].temp_id == temp_id) {
          customs[i][field] = $(this).val();
        }
      }
    });
    $(document).on('change', '.rangePicker', function() { // 대여물품 기간 선택
      var temp_id = $(this).data('id');
      var range = $(this).data('range'); // 'from' or 'to'
      if(range === 'from') {
        var to = $(".rangePicker[data-id="+temp_id+"][data-range=to]");
        to.datepicker("option", "minDate", getDate(this));
      } else {
        var from = $(".rangePicker[data-id="+temp_id+"][data-range=from]");
        from.datepicker("option", "maxDate", getDate(this));
      }
      var customs = status.rent.customs;
      for(var i = 0; i < customs.length; i++) {
        if(customs[i].temp_id == temp_id) {
          customs[i]['range_'+range] = $(this).val();
        }
      }
    });
    $(document).on('click', '.btnDelProd', function() { // 물품 삭제 버튼
      if(!confirm('해당 물품을 삭제하시겠습니까?')) return;

      var cnt = status['buy'].customs.length + status['rent'].customs.length;
      if (cnt <= 1) {
        $('#agreeAddProd').hide();
      }

      if($(this).data('type') === 'item') { // 실제로 구매/대여한 물품이면
        var items = status[$(this).data('gubun')].items;
        var it_id = $(this).data('id');
        for(var i = 0; i < items.length; i++) {
          if(items[i].it_id == it_id) {
            items[i].deleted = true;
            break;
          }
        }
      } else { // 계약서 상 임의로 추가한 물품이면
        var customs = status[$(this).data('gubun')].customs;
        var temp_id = $(this).data('id');
        for(var i = 0; i < customs.length; i++) {
          if(customs[i].temp_id == temp_id) {
            customs.splice(i, 1);
            break;
          }
        }
      }

      repaintForm();
    });
    var addProd = function(gubun) {
      status[gubun].customs.push({
        temp_id: status.customCounter++,
        ca_name: '',
        it_name: '',
        it_code: '',
        it_barcode: '',
        it_qty: '',
        it_date: '',
        it_price: '',
        it_price_pen: '',
        range_from: '',
        range_to: ''
      });
      repaintForm();
      $('#agreeAddProd').show();
    };
    $('#btnAddBuyProd').click(function() { // 구매물품 추가
      addProd('buy');
    });
    $('#btnAddRentProd').click(function() { // 대여물품 추가
      addProd('rent');
    });
    $('#btnSubmitEform').click(function() { // 계약서 생성
      
      var cnt = status['buy'].customs.length + status['rent'].customs.length;
      // todo: 폼 무결성 체크
      if(cnt && !status.agreement) {
        alert('계약서 작성 유의사항을 읽고 \'확인함\'에 체크해주세요.');
        return;
      }
      // 210806 계약서 생성 알림 삭제
      // if(!confirm('계약서를 생성하시겠습니까?')) return;
      $.post('./ajax.eform.write.php', {status: JSON.stringify(status), uuid: '<?=$eform["uuid"]?>'}, 'json')
      .done(function(data) {
        // 생성 완료
        // alert('계약서 생성이 완료되었습니다.');
        parent.location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    });

    function repaintForm() {
      var renderItem = function(item, gubun) {
        if(item.deleted) return; // 삭제된 물품은 안보여줌
        var html = '<tr><td>'+item.ca_name+'</td><td>'+item.it_name+'</td><td>'+item.it_code+'</td><td><input type="text" class="inputItem" data-gubun="'+gubun+'" data-id="'+item.it_id+'" data-field="it_barcode" value="'+item.it_barcode+'"></td><td>'+item.it_qty+'</td><td>'+item.it_date+'</td><td>'+parseInt(item.it_price).toLocaleString('en-US')+'</td><td>'+parseInt(item.it_price_pen).toLocaleString('en-US')+'</td><td><button class="btnDelProd" data-type="item" data-id="'+item.it_id+'" data-gubun="'+gubun+'">&times;</button></td></tr>';
        if(gubun == 'buy') $("#tableBuyProd tbody").append(html);
        else $("#tableRentProd tbody").append(html);
      };
      var renderCustom = function(custom, gubun) {
        var datefield = '<td><input type="text" class="datePicker" data-id="'+custom.temp_id+'" data-field="it_date" value="'+custom.it_date+'"></td>';
        if(gubun === 'rent') datefield = '<td><input type="text" class="rangePicker" data-id="'+custom.temp_id+'" data-range="from" value="'+custom.range_from+'"><input type="text" class="rangePicker" data-id="'+custom.temp_id+'" data-range="to" value="'+custom.range_to+'"></td>';
        var $html = $('<tr><td><input type="text" class="inputCustom" data-gubun="'+gubun+'" data-id="'+custom.temp_id+'" data-field="ca_name" value="'+custom.ca_name+'"></td><td><input type="text" class="inputCustom" data-gubun="'+gubun+'" data-id="'+custom.temp_id+'" data-field="it_name" value="'+custom.it_name+'"></td>\
                    <td><input type="text" class="inputCustom" data-gubun="'+gubun+'" data-id="'+custom.temp_id+'" data-field="it_code" value="'+custom.it_code+'"></td>\
                    <td><input type="text" class="inputCustom" data-gubun="'+gubun+'" data-id="'+custom.temp_id+'" data-field="it_barcode" value="'+custom.it_barcode+'"></td>\
                    <td><input type="text" class="inputCustom" data-gubun="'+gubun+'" data-id="'+custom.temp_id+'" data-field="it_qty" value="'+custom.it_qty+'"></td>'
                    + datefield + 
                    '<td><input type="text" class="inputNumber" data-gubun="'+gubun+'" data-id="'+custom.temp_id+'" data-field="it_price" value="'+custom.it_price+'"></td>\
                    <td><input type="text" class="inputNumber" data-gubun="'+gubun+'" data-id="'+custom.temp_id+'" data-field="it_price_pen" value="'+custom.it_price_pen+'"></td>\
                    <td><button class="btnDelProd" data-type="custom" data-id="'+custom.temp_id+'" data-gubun="'+gubun+'">&times;</button></td></tr>');
        if(gubun == 'buy') $html.appendTo("#tableBuyProd tbody").find('.datePicker').datepicker({ changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd' });
        else $html.appendTo("#tableRentProd tbody").find('.rangePicker').datepicker({ defaultDate: '+1w', changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd' });
      };

      // 확인함 체크박스
      $('#chkConfirm').prop('checked', status.agreement);

      // 특약사항
      $('#entConAcc01').val(status.entConAcc01);
      $('#entConAcc02').val(status.entConAcc02);

      //구매물품
      $("#tableBuyProd tbody").empty();
      for(var i = 0; i < status.buy.items.length; i++) {
        renderItem(status.buy.items[i], 'buy');
      }
      for(var i = 0; i < status.buy.customs.length; i++) {
        renderCustom(status.buy.customs[i], 'buy');
      }

      //대여물품
      $("#tableRentProd tbody").empty();
      for(var i = 0; i < status.rent.items.length; i++) {
        renderItem(status.rent.items[i], 'rent');
      }
      for(var i = 0; i < status.rent.customs.length; i++) {
        renderCustom(status.rent.customs[i], 'rent');
      }
    }

    repaintForm();
  });
  </script>
</body>
</html>