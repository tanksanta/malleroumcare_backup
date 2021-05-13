<?php
	include_once("./_common.php");

  $eform = sql_fetch("SELECT HEX(`dc_id`) as uuid, e.* FROM `eform_document` as e WHERE od_id = '$od_id'");
  $items = sql_query("SELECT * FROM `eform_document_item` WHERE dc_id = UNHEX('{$eform["uuid"]}')");
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
  <script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
  <style>
  * { margin: 0; padding: 0; box-sizing: border-box; position: relative; }
  html, body { width: 100%; min-width: 100%; height: 100%; min-height: 100%; margin: 0 !important; padding: 0; font-family: "Noto Sans KR", sans-serif; font-size: 13px; }
  button { display: inline-block; }

  #popupWrap {
    height: 100%;
  }

  #popupWrap .flex {
    display: -ms-flexbox;      /* TWEENER - IE 10 */
    display: -webkit-flex;     /* NEW - Chrome */
    display: flex;             /* NEW, Spec - Opera 12.1, Firefox 20+ */
    -webkit-justify-content: space-between;
    -ms-flex-pack: justify;
    justify-content: space-between;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
  }

  .popupHeadWrap {
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:60px;
    padding: 12px;
    border-bottom: 1px solid #ddd;
    background-color: #fff;
  }

  .popupHeadWrap .title {
    -webkit-flex: 1;          /* Chrome */
    -ms-flex: 1;              /* IE 10 */
    flex: 1;                  /* NEW, Spec - Opera 12.1, Firefox 20+ */
    font-size: 20px;
    font-weight: 500;
    padding: 0 6px;
  }

  .popupHeadWrap .menu {
  }

  .popupContentWrap {
    position: absolute;
    top: 60px;
    left: 0;
    width: 100%;
    bottom: 50px;
    padding-bottom: 12px;
    overflow-y: auto;
  }

  #btnResetEform {
    padding: 6px 12px;
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    color: #666;
  }

  #btnCloseEform {
    margin-left: 14px;
    padding: 6px;
    color: #666;
    font-size: 40px;
    line-height: 22px;
    vertical-align: middle;
  }

  #popupWrap .row {
    padding: 0 18px;
  }

  #popupWrap h3 {
    margin: 0;
    padding: 12px 0;
    font-size: 16px;
    font-weight: 500;
  }

  #tablePenInfo {
    border: 0;
    border-top: 12px solid #f5f5f5;
    border-bottom: 12px solid #f5f5f5;
    background-color: #f5f5f5;
    width: 100%;
  }

  #tablePenInfo th,
  #tablePenInfo td {
    padding: 2px 8px;
  }

  #tablePenInfo th {
    min-width: 126px;
    text-align: left;
    font-weight: normal;
  }

  #tablePenInfo th:before {
    display: inline;
    content: '·';
    padding-right: 2px;
  }

  #tablePenInfo td {
    width: 100%;
  }

  #prodRow .right {
    padding: 8px;
  }

  #prodRow .notice {
    color: red;
  }

  #prodRow .checkbox {
    display: inline-block;
    margin-left: 12px;
  }

  #prodRow .prodContentWrap {
    border: 1px solid #ddd;
    padding: 0 8px;
  }

  #prodRow .prodTableRow:first-child {
    border-bottom: 1px solid #ddd;
  }

  #prodRow .prodTableRow {
    padding: 10px 0;
  }

  #prodRow .prodHead {
    -webkit-flex: 1;          /* Chrome */
    -ms-flex: 1;              /* IE 10 */
    flex: 1;                  /* NEW, Spec - Opera 12.1, Firefox 20+ */
    font-weight: 500;
  }

  #prodRow .prodTableWrap {
    overflow-x: auto;
  }

  #prodRow table {
    width: 100%;
    min-width: 800px;
  }

  #prodRow thead {
    background-color: #f5f5f5;
    color: #999;
  }

  #prodRow td,
  #prodRow th {
    font-weight: normal;
    text-align: center;
    padding: 8px 6px;
  }

  #prodRow input[type=text] {
    width: 100%;
    height: 24px;
    border: 1px solid #ddd;
  }

  #prodRow input.period {
    width: 50%;
  }

  .btnDelProd {
    font-size: 24px;
    width: 24px;
    height: 24px;
    line-height: 13px;
    vertical-align: middle;
    color: #666;
  }

  #btnAddBuyProd, #btnAddRentProd {
    display: block;
    padding: 6px 18px;
    margin-bottom: 6px;
    color: #fff;
    background-color: #ee8102;
  }

  #chkConfirm {
    vertical-align: middle;
    margin-right: 6px;
  }

  #popupWrap .row.entConAcc textarea {
    display: block;
    width: 100%;
    height: 100px;
    border: 1px solid #ddd;
    resize: vertical;
    padding: 8px;
  }

  .popupFootWrap {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 50px;
  }

  .popupFootWrap button {
    height: 100%;
    font-weight: 500;
    font-size: 20px;
    color: #fff;
  }

  #btnSubmitEform {
    -webkit-flex: 1;          /* Chrome */
    -ms-flex: 1;              /* IE 10 */
    flex: 1;                  /* NEW, Spec - Opera 12.1, Firefox 20+ */
    background-color: #ee8102;
  }

  #btnCancelEform {
    background-color: #787878;
    width: 130px;
  }
  </style>
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
          <div class="right">
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
                  <tr>
                    <td>Aaaa</td>
                    <td>ddddd</td>
                    <td>Fddddd</td>
                    <td><input type="text" class="datePicker"></td>
                    <td>2</td>
                    <td>2021-02-02</td>
                    <td>10원</td>
                    <td>3원</td>
                    <td><button class="btnDelProd">&times;</button></td>
                  </tr>
                  <tr>
                    <td><input type="text" ></td>
                    <td><input type="text" ></td>
                    <td><input type="text" ></td>
                    <td><input type="text" class="datePicker"></td>
                    <td><input type="text" ></td>
                    <td><input type="text" ></td>
                    <td><input type="text" ></td>
                    <td><input type="text" ></td>
                    <td><button class="btnDelProd">&times;</button></td>
                  </tr>
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
                  <tr>
                    <td>Aaaa</td>
                    <td>ddddd</td>
                    <td>Fddddd</td>
                    <td><input type="text" ></td>
                    <td>2</td>
                    <td>21-02-02 ~ 25-02-02</td>
                    <td>10원</td>
                    <td>3원</td>
                    <td><button class="btnDelProd">&times;</button></td>
                  </tr>
                  <tr>
                    <td><input type="text" ></td>
                    <td><input type="text" ></td>
                    <td><input type="text" ></td>
                    <td><input type="text" ></td>
                    <td><input type="text" ></td>
                    <td><input type="text" class="datePicker period"><input type="text" class="datePicker period"></td>
                    <td><input type="text" ></td>
                    <td><input type="text" ></td>
                    <td><button class="btnDelProd">&times;</button></td>
                  </tr>
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
  function closePopup(e) {
    e.preventDefault();
    $("body", parent.document).removeClass("modal-open");
    $("#popupEformWrite", parent.document).hide();
    $("#popupEformWrite", parent.document).find("iframe").remove();
  }

  $(function(){
    $("#btnCloseEform").click(closePopup);
    $("#btnCancelEform").click(closePopup);

    var initialStatus = {
      agreement: false,
      entConAcc01: "<?=htmlspecialchars($eform["entConAcc01"])?>",
      entConAcc02: "<?=htmlspecialchars($eform["entConAcc02"])?>",
      buy: {
        items: [<?php while($item = sql_fetch_array($items)) {
          if($item['gubun'] == '00') { // 판매재고 ?>
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
<?php     }
        } ?>],
        customs: []
      },
      rent: {
        items: [<?php while($item = sql_fetch_array($items)) {
          if($item['gubun'] == '01') { // 대여재고 ?>
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
<?php     }
        } ?>],
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
    $(document).on('click', '.btnDelProd', function() { // 물품 삭제 버튼
      if(!confirm('정말 해당 물품을 삭제하시겠습니까?')) return;

      if($(this).data('type') === 'item') { // 실제로 구매/대여한 물품이면
        var items = status[$(this).data('gubun')].items;
        var it_id = $(this).data('id');
        for(var i = 0; i < items.length; i++) {
          if(items[i].it_id == it_id) {
            items[i].deleted = true;
            break;
          }
        }
      }

      repaintForm();
    });

    function repaintForm() {
      var renderItem = function(item, gubun) {
        if(item.deleted) return; // 삭제된 물품은 안보여줌
        var html = '<tr><td>'+item.ca_name+'</td><td>'+item.it_name+'</td><td>'+item.it_code+'</td><td><input type="text" class="inputItem" data-gubun="'+gubun+'" data-id="'+item.it_id+'" data-field="it_barcode" value="'+item.it_barcode+'"></td><td>'+item.it_qty+'</td><td>'+item.it_date+'</td><td>'+item.it_price+'</td><td>'+item.it_price_pen+'</td><td><button class="btnDelProd" data-type="item" data-id="'+item.it_id+'" data-gubun="'+gubun+'">&times;</button></td></tr>';
        $("#tableBuyProd tbody").append(html);
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

      //대여물품
      $("#tableRentProd tbody").empty();
      for(var i = 0; i < status.rent.items.length; i++) {
        renderItem(status.rent.items[i], 'rent');
      }
    }

    repaintForm();
  });
  </script>
</body>
</html>