<?php
include_once("./_common.php");
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width" /><meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<title><?php $title; ?></title>
<link rel="stylesheet" href="/adm/css/popup.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/common_new.css">
<link rel="stylesheet" href="<?php echo G5_PLUGIN_URL;?>/jquery-ui/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="<?php echo G5_PLUGIN_URL;?>/jquery-ui/style.css" type="text/css">
<link rel="stylesheet" href="<?php echo G5_JS_URL ?>/font-awesome/css/font-awesome.min.css">
<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-ui.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-migrate-1.2.1.min.js"></script>
<script src="<?php echo G5_JS_URL;?>/common.js"></script>
</head>

<style>
  .modal-open {
    overflow: hidden;
  }
  html, body { width: 100%; height: 100%; float: left; background-color: #FFF; padding: 0; }
  .pop_top_area { width: 100%; left: 0; top: 0; }
  .pop_top_area .btn_area a { top: 15px; right: 15px; }
  .pop_list { z-index: 2; position: relative; }
  .pop_top_area .search_area button{background:#666;color:#fff;font-size:14px;line-height: 36px;height: 36px;display:inline-block;text-align: center;width:50px;}
  body, input, textarea, select, button, table {
    font-size: 12px;
  }
  .empty_list {
    margin-top: 250px;
    text-align: center;
    font-size: 1.2em;
  }
  .notice_incom {
    display: inline-block;
    font-size: 12px;
    color: #ef7d01;
    line-height: 24px;
    vertical-align: middle;
  }
  .notice_incom img {
    display: inline-block;
    width: 24px;
    height: 24px;
    vertical-align: middle;
  }
  .form_recipient input[type="text"],
  .form_recipient select {
    border: 1px solid #ccc;
    width: 100px;
    font-size: 12px;
    padding: 5px;
  }
  .form_recipient input[readonly] {
    cursor: not-allowed;
    background-color: #eee;
  }
  .pop_list ul>li a.disabled {
    background: #fff;
    color: #333;
    cursor: default;
  }
  tr.edit_only {
    display: none;
  }
  .checkbox-inline {
    padding-top: 7px;
    margin-top: 0;
    margin-bottom: 0;
    display: inline-block;
    padding-left: 20px;
    font-weight: 400;
    vertical-align: middle;
    cursor: pointer;
    width: 140px;
    font-size: 12px;
    line-height: 22px;
  }
  .checkbox-inline input[type=checkbox] {
    position: absolute;
    margin: 4px 0 0;
    margin-left: -20px;
    line-height: normal;
  }

  .img_loading {
    display: block;
    margin: 0 auto;
    padding: 20px;
  }
</style>

<body>
<div id="zipAddrPopupWrap">
  <div>
    <div>
      <i class="fa fa-times-circle closeBtn" onclick="zipPopupClose();"></i>
      <div id="zipAddrPopupIframe"></div>
    </div>
  </div>
</div>

<div class="pop_top_area">
  <p>수급자 정보</p>
  <div class="btn_area"><a href="#none" id="thisPopupCloseBtn" attr-a="onclick : attr-a"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_menu_x.png" alt="" /></a></div>
  <form class="search_area" method="get">
    <select name="penTypeCd">
      <option>수급자구분</option>
      <option value="00" <?=($_GET["penTypeCd"] == "00") ? "selected" : ""?>>일반 15%</option>
      <option value="01" <?=($_GET["penTypeCd"] == "01") ? "selected" : ""?>>감경 9%</option>
      <option value="02" <?=($_GET["penTypeCd"] == "02") ? "selected" : ""?>>감경 6%</option>
      <option value="03" <?=($_GET["penTypeCd"] == "03") ? "selected" : ""?>>의료 6%</option>
      <option value="04" <?=($_GET["penTypeCd"] == "04") ? "selected" : ""?>>기초 0%</option>
    </select>
    <input type="text" name="penNm" value="<?=$_GET["penNm"]?>">
    <button type="submit">검색</button>
  </form>
</div>
<div class="pop_list">
  <ul id="recipient_list">
  </ul>
  <img src="<?php echo G5_URL; ?>/shop/img/loading.gif" class="img_loading">
</div>
</body>
</html>

<script>
$(function() {

  var page = 1;
  var loading = false;
  var is_last = false;
  var penNm = '<?=get_text($_GET["penNm"])?>';
  var penTypeCd = '<?=get_text($_GET["penTypeCd"])?>';

  load_recipient();

  function load_recipient() {
    if(loading || is_last)
      return;

    if(page === 1) {
      // 첫페이지면 비움
      $('#recipient_list').html('');
    }

    loading = true;
    $('.img_loading').show();
    $.get('ajax.pop.recipient_address.php', {
      page: page,
      penNm: penNm,
      penTypeCd: penTypeCd
    })
    .done(function(result) {
      var data = result.data;

      is_last = data.is_last;
      $html = $(data.html);
      $html.find('.datepicker').datepicker();
      if($('#recipient_list').html() === '') {
        $('#recipient_list').html($html);
      } else if(!is_last) {
        $('#recipient_list').append($html);
      }
      
      page += 1;
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      loading = false;
      $('.img_loading').hide();
    });
  }

  $(window).scroll(function() {
    if( ( $(window).scrollTop() + ($(window).height() * 2) ) >= $(document).height() ) {
      load_recipient();
    }
  });

  $(document).on("click", ".sel_address", function() {
    var addr = $(this).data('target').split(String.fromCharCode(30));

    var parent = window.parent ? window.parent : window.opener;

    var f = parent.forderform;
    f.od_b_name.value        = addr[0];
    f.od_b_tel.value         = addr[2];
    f.od_b_hp.value          = addr[1];
    f.od_b_zip.value         = addr[3];
    f.od_b_addr1.value       = addr[4];
    f.od_b_addr2.value       = addr[5];
    f.od_b_addr_jibeon.value = '';

    var zip = addr[3].replace(/[^0-9]/g, "");

    if(zip != "") {
      var code = String(zip);

      if(parent.zipcode != code) {
        parent.zipcode = code;
        parent.calculate_sendcost(code);
      }
    }

    close_popup();
  });

  $("#thisPopupCloseBtn").click(function(e){
    e.preventDefault();

    close_popup();
  });

  function close_popup() {
    if(parent.window && parent.window.close_popup_box)
      parent.window.close_popup_box();
    
    window.close();
  }
});
</script>
