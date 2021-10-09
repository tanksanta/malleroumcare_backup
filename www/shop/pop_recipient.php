<?php
include_once("./_common.php");
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
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
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
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

  #zipAddrPopupWrap { position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index: 100; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
  #zipAddrPopupWrap > div { position: relative; width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
  #zipAddrPopupWrap > div > div { position: relative; width: 700px; height: 500px; background-color: #FFF; padding-top: 50px; left: 50%; margin-left: -350px; }
  #zipAddrPopupWrap #zipAddrPopupIframe { position: relative; width: 100%; height: 100%; float: left; border: 0; background-color: #FFF; border-top: 1px solid #DDD; }
  #zipAddrPopupWrap .closeBtn { position: absolute; font-size: 32px; color: #AAA; top: 10px; right: 10px; cursor: pointer; }
  @media (max-width : 750px){
    #zipAddrPopupWrap > div > div { width: 100%; height: 100%; left: 0; margin-left: 0; }
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
</div>
</body>
</html>

<script>
var zipPopupDom = document.getElementById("zipAddrPopupIframe");

function zipPopupClose() {
  $('body').removeClass('modal-open');
  $("#zipAddrPopupWrap").hide();
}

function zipPopupOpen(target) {
  $('body').addClass('modal-open');
  new daum.Postcode({
    oncomplete: function(data) {
      var parent = $(target).closest(".tbl_edit");

      $(parent).find(".penZip").val(data.zonecode);
      $(parent).find(".penAddr").val(data.address);

      zipPopupClose();
    },
    width : "100%",
    height : "100%",
    maxSuggestItems : 5
  }).embed(zipPopupDom);

  $("#zipAddrPopupWrap").show();
}

$(function() {
  $("#zipAddrPopupWrap").css("opacity", 1);
  $("#zipAddrPopupWrap").hide();

  var page = 1;
  var loading = false;
  var is_last = false;
  var ca_id = '<?=get_text($_GET["ca_id"])?>';
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
    $.get('ajax.pop_recipient.php', {
      page: page,
      ca_id: ca_id,
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
    });
  }

  $(window).scroll(function() {
    if((window.innerHeight + window.scrollY) >= document.body.offsetHeight / 2) {
      load_recipient();
    }
  });

  $.fn.serializeObject = function() {
    "use strict"
    var result = {}
    var extend = function(i, element) {
      var node = result[element.name]
      if ("undefined" !== typeof node && node !== null) {
        if ($.isArray(node)) {
          node.push(element.value)
        } else {
          result[element.name] = [node, element.value]
        }
      } else {
        result[element.name] = element.value
      }
    }
  
    $.each(this.serializeArray(), extend)
    return result
  }

  $.datepicker.setDefaults({
    dateFormat : 'yy-mm-dd',
    prevText: '이전달',
    nextText: '다음달',
    monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
    monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
    dayNames: ["일", "월", "화", "수", "목", "금", "토"],
    dayNamesShort: ["일", "월", "화", "수", "목", "금", "토"],
    dayNamesMin: ["일", "월", "화", "수", "목", "금", "토"],
    showMonthAfterYear: true,
    changeMonth: true,
    changeYear: true,
    yearRange : "-150:+10"
  });

  // 정보수정 버튼
  $(document).on('click', '.btn_edit', function(e) {
    e.preventDefault();
    $li = $(this).closest('li');
    $li.find('.tbl_static').hide();
    $li.find('.tbl_edit').show();
    $li.find('tr.edit_only').show();
    $li.find('.sel_address').data('edit', 1);
    $(this).hide();
  });

  $(document).on("click", ".sel_address", function() {
    var $this = $(this);

    if($this.prop('disabled'))
      return false;
    
    var is_edit = $this.data('edit') == 1;

    if(is_edit) {
      // 필수정보 입력 필요한 경우
      var form = $this.closest('li').find('.form_recipient');
      var form_data = form.serializeObject();

      var checklist = ['penGender', 'penRecGraCd', 'penTypeCd', 'penExpiStDtm', 'penExpiEdDtm', 'penBirth'];
      for(var i = 0; i < checklist.length; i++) {
        var check = checklist[i];
        if(!form_data[check])
          return alert('필수정보를 입력해주세요.');
      }

      if(form_data['penTypeCd'] === '04' && !form_data['penJumin'])
        return alert('기초수급자는 주민등록번호를 입력해주세요.');

      $this.prop('disabled', true).addClass('disabled').text('...');
      $.post('./ajax.my.recipient.update.php', form_data, 'json')
      .done(function() {
        var itemList=[];
        form.find('[name^=ITM]:checked').each(function() {
          itemList.push($(this).val());
        });

        if(itemList.length > 0) {
          $.post('./ajax.my.recipient.setItem.php', {
            penId: form_data['penId'],
            itemList: itemList
          }, 'json')
          .always(function(result) {
            var penExpiStDtm = form_data['penExpiStDtm'];
            form_data['penGraEditDtm'] = penExpiStDtm;
            form_data['penGraApplyMonth'] = penExpiStDtm.slice(5,7);
            form_data['penGraApplyDay'] = penExpiStDtm.slice(8,10);
            $.post('./ajax.my.recipient.grade.log.update.php', form_data, 'json')
            .done(function() {
              $this.prop('disabled', false).removeClass('disabled').text('선택');
              var penId = $this.data("target");
              parent.selected_recipient(penId);
              $("#order_recipientBox", parent.document).hide();
            })
            .fail(function($xhr) {
              $this.prop('disabled', false).removeClass('disabled').text('선택');
              var data = $xhr.responseJSON;
              alert(data && data.message);
            });
          });
        } else {
          var penExpiStDtm = form_data['penExpiStDtm'];
          form_data['penGraEditDtm'] = penExpiStDtm;
          form_data['penGraApplyMonth'] = penExpiStDtm.slice(5,7);
          form_data['penGraApplyDay'] = penExpiStDtm.slice(8,10);
          $.post('./ajax.my.recipient.grade.log.update.php', form_data, 'json')
          .done(function() {
            $this.prop('disabled', false).removeClass('disabled').text('선택');
            var penId = $this.data("target");
            parent.selected_recipient(penId);
            $("#order_recipientBox", parent.document).hide();
          })
          .fail(function($xhr) {
            $this.prop('disabled', false).removeClass('disabled').text('선택');
            var data = $xhr.responseJSON;
            alert(data && data.message);
          });
        }
      })
      .fail(function($xhr) {
        $this.prop('disabled', false).removeClass('disabled').text('선택');
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    } else {
      // 필수정보 입력 필요 X (바로 선택)
      $this.prop('disabled', false).removeClass('disabled').text('선택');
      var penId = $this.data("target");
      parent.selected_recipient(penId);
      $("#order_recipientBox", parent.document).hide();
    }
  });

  $("#thisPopupCloseBtn").click(function(e){
    e.preventDefault();
    $("#order_recipientBox", parent.document).hide();
          parent.$('#mask').css({'width':'0px','height':'0px'});
  });
});
</script>
