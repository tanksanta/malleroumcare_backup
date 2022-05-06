<?php
include_once("./_common.php");

if(!$is_samhwa_partner) {
  alert("파트너 회원만 접근 가능한 페이지입니다.");
}

$ct_id = get_search_string($_GET['ct_id']);
if(!$ct_id) {
  alert('정상적인 접근이 아닙니다.');
}

$sql = "
  SELECT * FROM {$g5['g5_shop_cart_table']} 
  WHERE ct_id = '{$ct_id}' and ct_direct_delivery_partner = '{$member['mb_id']}'
";

$cart = sql_fetch($sql);

if(!$cart || !$cart['ct_id'])
  alert('상품이 존재하지 않습니다.');

$sto_id = [];
$cart['it_name'] .= $cart['ct_option'] && $cart['ct_option'] != $cart['it_name'] ? " ({$cart['ct_option']})" : '';

foreach(array_filter(explode('|', $cart['stoId'])) as $id) {
  $sto_id[] = $id;
}

$stock_result = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(
  'stoId' => implode('|', $sto_id)
));

$barcodes = [];
if($stock_result['data']) {
  foreach($stock_result['data'] as $data) {
    $barcodes[] = $data;
  }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>바코드</title>
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/common_new.css">
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/font.css">
  <link rel="shortcut icon" href="<?php echo THEMA_URL; ?>/assets/img/top_logo_icon.ico">
  <link rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; position: relative; }
    html, body { width: 100%; min-width: 100%; float: left; margin: 0 !important; padding: 0; font-family: "Noto Sans KR", sans-serif; font-size: 13px; }
    body { padding: 60px 0; }

    #popupHeaderTopWrap { position: fixed; width: 100%; height: 60px; left: 0; top: 0; z-index: 10; background-color: #333; padding: 0 20px; }
    #popupHeaderTopWrap > div { height: 100%; line-height: 60px; }
    #popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 22px; }
    #popupHeaderTopWrap > .close { float: right; }
    #popupHeaderTopWrap > .close > a { color: #FFF; font-size: 40px; top: -2px; }

    .imfomation_box{ margin:0px;width:100%;position:relative; padding:0px;display:block; width:100%; height:auto; }
    .imfomation_box li { width: 100%; padding: 20px; border-bottom: 1px solid #DDD; }
    .imfomation_box .li_box{ width:100%; height:auto;text-align:center;}
    .imfomation_box .li_box .li_box_line1{ width: 100%; height:auto; margin:auto; color:#000; }
    .imfomation_box .li_box .li_box_line1 .p1{ width:100%; color:#000; text-align:left; box-sizing: border-box; display: table; table-layout: fixed; }
    .imfomation_box .li_box .li_box_line1 .p1 > span { height: 100%; display: table-cell; vertical-align: middle; }
    .imfomation_box .li_box .li_box_line1 .p1 .span1{ font-size: 18px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-weight: bold; }

    .imfomation_box .li_box .deliveryInfoWrap { width: 100%; margin-top: 15px; }
    .imfomation_box .li_box .deliveryInfoWrap:after { display: table; content: ''; clear: both; }
    .imfomation_box .li_box .deliveryInfoWrap > input[type="text"] { display: block; width: 100%; height: 40px; border: 1px solid #DDD; font-size: 17px; color: #666; padding: 0 40px 0 10px; border-radius: 5px; }
    .imfomation_box .li_box .deliveryInfoWrap > input[type="text"] + input[type="text"] { margin-top: 10px; }

    #popupFooterBtnWrap { position: fixed; width: 100%; height: 70px; background-color: #000; bottom: 0px; z-index: 10; }
    #popupFooterBtnWrap > button { font-size: 18px; font-weight: bold; }
    #popupFooterBtnWrap > .savebtn{ float: left; width: 75%; height: 100%; background-color:#000; color: #FFF; }
    #popupFooterBtnWrap > .cancelbtn{ float: right; width: 25%; height: 100%; color: #666; background-color: #DDD; }
  </style>
</head>
<body>
  <div id="popupHeaderTopWrap">
    <div class="title">바코드</div>
    <div class="close">
      <a href="#" id="popupCloseBtn">
        &times;
      </a>
    </div>
  </div>

  <div id="itInfoWrap">
    <ul class="imfomation_box" id="imfomation_box">
      <li class="li_box">
        <div class="li_box_line1">
          <p class="p1">
            <span class="span1"><?=$cart['it_name']?></span>
          </p>
        </div>

        <form id="form_partner_barcode">
          <div class="deliveryInfoWrap">
            <input type="hidden" name="ct_id" value="<?=$ct_id?>">
            <?php foreach($barcodes as $barcode) { ?>
            <input type="hidden" name="stoId[]" value="<?=$barcode['stoId']?>">
            <input type="text" name="<?=$barcode['stoId']?>" value="<?=$barcode['prodBarNum'] ?: '' ?>" placeholder="없음" maxlength="12">
            <?php } ?>
          </div>
        </form>
      </li>
    </ul>
  </div>
  
  <div id="popupFooterBtnWrap">
    <button type="button" class="savebtn" id="prodBarNumSaveBtn">저장</button>
    <button type="button" class="cancelbtn" onclick="closePopup();">취소</button>
  </div>
  
  <script type="text/javascript">
    // 팝업 닫기
    function closePopup() {
      $("body", parent.document).removeClass('modal-open');
      $("#popup_box", parent.document).hide();
      $("#popup_box", parent.document).find("iframe").remove();
    }
    $(function() {
      $("#popupCloseBtn").click(function(e) {
        e.preventDefault();
        
        closePopup();
      });

      $('#prodBarNumSaveBtn').click(function() {
        $('#form_partner_barcode').submit();
      });
      
      $('#form_partner_barcode').on('submit', function(e) {
        e.preventDefault();

        var params = $(this).serialize();
        $.post('ajax.partner_barcode.php', params, 'json')
        .done(function() {
          alert('바코드가 저장되었습니다.');
          parent.window.location.reload();
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
        });
      });
    });
  </script>
</body>
</html>
