<?php
include_once("./_common.php");

if(!$is_samhwa_partner) {
  alert("파트너 회원만 접근 가능한 페이지입니다.");
}

$od_id = get_search_string($_GET['od_id']);
if(!$od_id) {
  alert('정상적인 접근이 아닙니다.');
}

$sql = "
  SELECT
    ct_id,
    it_id,
    it_name,
    ct_option,
    ct_delivery_company,
    ct_delivery_num,
    ct_part_info
  FROM
    purchase_cart c
  LEFT JOIN
    purchase_order o ON c.od_id = o.od_id
  WHERE
    od_del_yn = 'N' 
    AND ct_supply_partner = '{$member['mb_id']}'
    AND c.od_id = '{$od_id}'
    AND c.ct_status IN('발주완료','발주승인','부분출고','출고완료','부분입고','입고완료','파트너발주취소')
";

$result = sql_query($sql);

$carts = [];
while($row = sql_fetch_array($result)) {
  $row['it_name'] .= $row['ct_option'] && $row['ct_option'] != $row['it_name'] ? " ({$row['ct_option']})" : '';

  $ct_delivery_num_name = '';
//  if($row['ct_delivery_company'] == 'install' && $row['ct_delivery_num']) {
//    // 설치배송이면
//    $name_num = explode(' / ', $row['ct_delivery_num']);
//    $name = array_shift($name_num);
//    $num = implode(' / ', $name_num);
//
//    $ct_delivery_num_name = $name;
//    $row['ct_delivery_num'] = $num;
//  }

  $row['ct_delivery_num_name'] = $ct_delivery_num_name;

  $carts[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>배송정보</title>
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
    .imfomation_box .li_box .li_box_line1{ width: 100%; height:auto; margin:auto; color:#000; text-align: left; }
    .imfomation_box .li_box .li_box_line1 .p1{ width:100%; color:#000; text-align:left; box-sizing: border-box; display: table; table-layout: fixed; }
    .imfomation_box .li_box .li_box_line1 .p1 > span { height: 100%; display: table-cell; vertical-align: middle; }
    .imfomation_box .li_box .li_box_line1 .p1 .span1{ font-size: 18px; overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-weight: bold; }

    .imfomation_box .li_box .deliveryInfoWrap { width: 100%; margin-top: 15px; }
    .imfomation_box .li_box .deliveryInfoWrap:after { display: table; content: ''; clear: both; }
    .imfomation_box .li_box .deliveryInfoWrap > select { width: 34%; height: 40px; float: left; margin-right: 1%; border: 1px solid #DDD; font-size: 17px; color: #666; padding-left: 10px; border-radius: 5px; }
    .imfomation_box .li_box .deliveryInfoWrap > input[type="text"] { width: 65%; height: 40px; float: left; border: 1px solid #DDD; font-size: 17px; color: #666; padding: 0 10px; border-radius: 5px; }

    .ct_delivery_num_name { display: none; width: 25% !important; margin-right: 1%; }
    .ct_delivery_num.install { width: 39% !important; }

    #popupFooterBtnWrap { position: fixed; width: 100%; height: 70px; background-color: #000; bottom: 0px; z-index: 10; }
    #popupFooterBtnWrap > button { font-size: 18px; font-weight: bold; }
    #popupFooterBtnWrap > .savebtn{ float: left; width: 75%; height: 100%; background-color:#000; color: #FFF; }
    #popupFooterBtnWrap > .cancelbtn{ float: right; width: 25%; height: 100%; color: #666; background-color: #DDD; }
  </style>
</head>
<body>
  <div id="popupHeaderTopWrap">
    <div class="title">배송정보</div>
    <div class="close">
      <a href="#" id="popupCloseBtn">
        &times;
      </a>
    </div>
  </div>

  <div id="itInfoWrap">
    <form id="form_partner_deliveryinfo">
      <input type="hidden" name="od_id" value="<?=$od_id?>">
      <ul class="imfomation_box" id="imfomation_box">
        <?php foreach($carts as $row) {
            $_part_info = json_decode($row['ct_part_info'],true);
            if( is_array( $_part_info ) ) { krsort($_part_info); }
        ?>
        <li class="li_box">
          <div class="li_box_line1">
            <p class="p1">
              <span class="span1"><?=$row['it_name']?></span>
            </p>
          </div>

          <div class="deliveryInfoWrap">
            <input type="hidden" name="ct_id[]" value="<?=$row['ct_id']?>">
            <select name="ct_delivery_company_<?=$row['ct_id']?>" class="ct_delivery_company">
              <?php foreach($delivery_companys as $data) { ?>
              <option value="<?=$data["val"]?>" <?=get_selected($data['val'], $row['ct_delivery_company'])?>><?=$data["name"]?></option>
              <?php } ?>
            </select>
            <input type="text" value="<?=$row['ct_delivery_num_name']?>" name="ct_delivery_num_name_<?=$row['ct_id']?>" class="ct_delivery_num_name" placeholder="담당자명">
            <input type="text" value="<?=$row['ct_delivery_num']?>" name="ct_delivery_num_<?=$row['ct_id']?>" class="ct_delivery_num" placeholder="송장번호/연락처 입력">
          </div>
        </li>
        <?php } ?>
      </ul>
    </form>
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

    function changeDeliveryCompany() {
      var $li = $(this).closest('.li_box');
      var $ct_delivery_num_name = $li.find('.ct_delivery_num_name');
      var $ct_delivery_num = $li.find('.ct_delivery_num');
      // 설치배송 선택시
      if($(this).val() === 'install') {
        $ct_delivery_num_name.show();
        $ct_delivery_num.addClass('install');
        $ct_delivery_num.attr('placeholder', '연락처 입력');
      } else {
        $ct_delivery_num_name.hide();
        $ct_delivery_num.removeClass('install');
        $ct_delivery_num.attr('placeholder', '송장번호/연락처 입력');
      }
    }
    $(function() {
      $('.ct_delivery_company').each(function() {
        changeDeliveryCompany.call(this);
      });

      $("#popupCloseBtn").click(function(e) {
        e.preventDefault();
        
        closePopup();
      });

      $('.ct_delivery_company').change(function() {
        changeDeliveryCompany.call(this);
      });

      $('#prodBarNumSaveBtn').click(function() {
        $('#form_partner_deliveryinfo').submit();
      });
      
      $('#form_partner_deliveryinfo').on('submit', function(e) {
        e.preventDefault();

        var params = $(this).serialize();
        $.post('ajax.supply_partner_deliveryinfo.php', params, 'json')
        .done(function() {
          alert('배송정보가 저장되었습니다.');
          parent.window.location.reload();
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
        });
      });

      $('.part_select').change(function() {
        $.ajax({
          url: 'ajax.supply_partner_partinfo_view.php',
          type: 'POST',
          data: {
            od_id: '<?php echo $od_id ?>',
            ct_id: $(this).find("option:selected").data("ct"),
            it_id: $(this).find("option:selected").data("it"),
            part_num: $(this).find("option:selected").val()
          },
          dataType: 'json',
          async: false,
          success : function(result){
            if(result) {
              //alert( result.ct_id );
              //let str = JSON.stringify(result); // <> parse()
              //alert(str['ct_part_info']);
              //$.each(result, function(index, item) {

                var _out_delivery = '';
                if( result.ct_part_info._out_delivery_company ) {
                  $("select[name=ct_delivery_company_"+result.ct_id+"]").val(result.ct_part_info._out_delivery_company_cd).prop("selected", true);
                } else { $("select[name=ct_delivery_company_"+result.ct_id+"]").val('cjlogistics').prop("selected", true); }

                if(  result.ct_part_info._out_delivery_num ) {
                  $("input[name=ct_delivery_num_"+result.ct_id+"]").val(result.ct_part_info._out_delivery_num);
                } else { $("input[name=ct_delivery_num_"+result.ct_id+"]").val(''); }

              //});
              return false;
            }
          }
        })
        .fail(function($xhr) {
        });
      });
    });
  </script>
</body>
</html>
