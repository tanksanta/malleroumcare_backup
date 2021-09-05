<?php
include_once("./_common.php");
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$ca_id_arr = array_filter(explode('|', $_GET['ca_id']));

$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];
$sendData["appCd"] = "01";

if($_GET["penNm"]){
  $sendData["penNm"] = $_GET["penNm"];
}

if($_GET["penTypeCd"]&&$_GET["penTypeCd"]!=="수급자구분"){
  $sendData["penTypeCd"] = $_GET["penTypeCd"];
}

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/recipient/selectList");
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
$res = json_decode($res, true);
curl_close($oCurl);

$list = [];
foreach($res['data'] as $data) {
  $checklist = ['penRecGraCd', 'penTypeCd', 'penExpiDtm', 'penBirth'];
  $is_incomplete = false;
  foreach($checklist as $check) {
    if(!$data[$check])
      $is_incomplete = true;
  }
  if(!in_array($data['penGender'], ['남', '여']))
    $is_incomplete = true;
  if($data['penTypeCd'] == '04' && !$data['penJumin'])
    $is_incomplete = true;
  if($data['penExpiDtm']) {
    // 유효기간 만료일 지난 수급자는 유효기간 입력 후 주문하게 함
    $expired_dtm = substr($data['penExpiDtm'], -10);
    if (strtotime(date("Y-m-d")) > strtotime($expired_dtm)) {
      $data['penExpiDtm'] = '';
      $is_incomplete = true;
    }
  }

  $data['incomplete'] = $is_incomplete;

  $list[] = $data;
}

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
<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-ui.min.js"></script>
<script src="<?php echo G5_JS_URL ?>/jquery-migrate-1.2.1.min.js"></script>
<script src="<?php echo G5_JS_URL;?>/common.js"></script>
</head>

<style>
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
  .pop_list ul>li a.disabled {
    background: #fff;
    color: #333;
    cursor: default;
  }
</style>

<body>
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
    <?php
    if($list) {
      foreach($list as $data) {
        $warning = [];
        if(is_array($ca_id_arr)) {
          foreach($ca_id_arr as $ca_id) {
            $limit = get_pen_category_limit($data["penLtmNum"], $ca_id);
            if($limit) {
              $cur = intval($limit['num']) - intval($limit['current']);
              if($cur <= 0) {
                // 구매불가능
                $warning_text = "\"{$limit['ca_name']}\" 구매가능 개수가 초과되었습니다.";
                if(!in_array($warning_text, $warning))
                  $warning[] = $warning_text;
              }
            }
          }
        }
        $grade_year_info = get_recipient_grade_per_year($data['penId']);
        $recipient = $data["rn"]."|".$data["penId"]."|".$data["entId"]."|".$data["penNm"]."|".$data["penLtmNum"]."|".$data["penRecGraCd"]."|".$data["penRecGraNm"]."|".$data["penTypeCd"]."|".$data["penTypeNm"]."|".$data["penExpiStDtm"]."|".$data["penExpiEdDtm"]."|".$data["penExpiDtm"]."|".$data["penExpiRemDay"]."|".$data["penGender"]."|".$data["penGenderNm"]."|".$data["penBirth"]."|".$data["penAge"]."|".$data["penAppEdDtm"]."|".$data["penAddr"]."|".$data["penAddrDtl"]."|".$data["penConNum"]."|".$data["penConPnum"]."|".$data["penProNm"]."|".$data["usrId"]."|".$data["appCd"]."|".$data["appCdNm"]."|".$data["caCenYn"]."|".$data["regDtm"]."|".$data["regDt"]."|".$data["ordLendEndDtm"]."|".$data["ordLendRemDay"]."|".$data["usrNm"]."|".$data["penAppRemDay"]."|800,000원";
    ?>
    <li>
      <form class="form_recipient" autocomplete="off">
        <input type="hidden" name="penId" value="<?=$data['penId']?>">
        <input type="hidden" name="penNm" value="<?=$data['penNm']?>">
        <input type="hidden" name="penLtmNum" value="<?=$data['penLtmNum']?>">
        <table>
          <tr>
            <td>수급자명</td>
            <td>
              <?php
              echo $data["penNm"];
              if($data['incomplete']) {
                echo '<span class="notice_incom"><img src="'.THEMA_URL.'/assets/img/icon_notice_recipient.png"> 필수정보 입력 후 선택가능</span>';
              }
              ?>
            </td>
          </tr>
          <tr>
            <td>성별</td>
            <td>
              <?php
              if(in_array($data["penGender"], ['남', '여'])) {
                echo $data["penGender"];
                echo '<input type="hidden" name="penGender" value="'.$data["penGender"].'">';
              } else {
                echo '
                  <label class="checkbox-inline">
                    <input type="radio" name="penGender" value="남" style="vertical-align: middle; margin: 0 5px 0 0;">남
                  </label>
                  <label class="checkbox-inline">
                    <input type="radio" name="penGender" value="여" style="vertical-align: middle; margin: 0 5px 0 0;">여
                  </label>
                ';
              }
              ?>
            </td>
          </tr>
          <tr>
            <td>장기요양번호</td>
            <td><?=($data["penLtmNum"]) ? $data["penLtmNum"] : '-'?></td>
          </tr>
          <tr>
            <td>인정등급</td>
            <td>
              <?php
              if($data["penRecGraNm"]) {
                echo $data["penRecGraNm"];
                echo '<input type="hidden" name="penRecGraCd" value="'.$data["penRecGraCd"].'">';
              } else {
                echo '
                  <select name="penRecGraCd">
                    <option value="00">등급외</option>
                    <option value="01">1등급</option>
                    <option value="02">2등급</option>
                    <option value="03">3등급</option>
                    <option value="04">4등급</option>
                    <option value="05">5등급</option>
                  </select>
                ';
              }
              ?>
            </td>
          </tr>
          <tr>
            <td>본인부담금율</td>
            <td>
              <?php
              if($data['penTypeNm']) {
                echo $data["penTypeNm"];
                echo '<input type="hidden" name="penTypeCd" value="'.$data["penTypeCd"].'">';
              } else {
                echo '
                <select name="penTypeCd">
                  <option value="00">일반 15%</option>
                  <option value="01">감경 9%</option>
                  <option value="02">감경 6%</option>
                  <option value="03">의료 6%</option>
                  <option value="04">기초 0%</option>
                </select>
                ';
              }
              ?>
            </td>
          </tr>
          <tr>
            <td>유효기간</td>
            <td>
              <?php
              if($data["penExpiDtm"]) {
                $penExpiDtm = explode(' ~ ', $data["penExpiDtm"]);
                echo $data["penExpiDtm"];
                echo '<input type="hidden" name="penExpiStDtm" value="'.$penExpiDtm[0].'">';
                echo '<input type="hidden" name="penExpiEdDtm" value="'.$penExpiDtm[1].'">';
              } else {
                echo '
                  <input type="text" name="penExpiStDtm" class="datepicker">
                  ~
                  <input type="text" name="penExpiEdDtm" class="datepicker">
                ';
              }
              ?>
            </td>
          </tr>
          <tr>
            <td>생년월일</td>
            <td>
              <?php
              if($data["penBirth"] && !($data['penTypeCd'] == '04' && !$data['penJumin'])) {
                $penBirth = preg_replace("/[^0-9]/", "", $data["penBirth"]);
                $penBirth = DateTime::createFromFormat('Ymd', $penBirth);
                $penBirth = $penBirth->format('Y-m-d');

                echo $penBirth;
                echo '<input type="hidden" name="penBirth" value="'.$penBirth.'">';
                echo '<input type="hidden" name="penJumin" value="'.$data["penJumin"].'">';
              } else {
                echo '<input type="text" name="penBirth" class="datepicker">';
              }
              ?>
            </td>
          </tr>
          <tr>
            <td>연 사용금액</td>
            <td><?php echo number_format($grade_year_info['sum_price']); ?> 원</td>
          </tr>
          <?php foreach($warning as $warning_text) { ?>
          <tr>
            <td colspan="2" style="color: red"><?=$warning_text?></td>
          </tr>
          <?php } ?>
        </table>
        <?php if($warning) { ?>
        <div class="warning">구매가능초과</div>
        <?php } else if($grade_year_info['sum_price'] > 1600000) { ?>
        <div class="warning">사용금액초과</div>
        <?php } else { ?>
        <a href="javascript:void(0)" class="sel_address" data-target="<?=$recipient?>" data-incomplete="<?=$data['incomplete'] ? 'true' : 'false'?>" title="선택">선택</a>
        <?php } ?>
      </form>
    </li>
    <?php
      }
    } else {
    ?>
    <div class="empty_list">
      수급자가 없습니다.
    </div>
    <?php } ?>
  </ul>
</div>
</body>
</html>

<script>
$(function() {
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
  $('.datepicker').datepicker();

  $(".sel_address").on("click", function() {
    var $this = $(this);

    if($this.prop('disabled'))
      return false;
    
    var is_incomplete = $this.data('incomplete');
    if(is_incomplete) {
      // 필수정보 입력 필요한 경우
      var form = $this.closest('li').find('.form_recipient');
      var form_data = form.serializeObject();

      var checklist = ['penGender', 'penRecGraCd', 'penTypeCd', 'penExpiStDtm', 'penExpiEdDtm', 'penBirth'];
      for(var i = 0; i < checklist.length; i++) {
        var check = checklist[i];
        if(!form_data[check])
          return alert('필수정보를 입력해주세요.');
      }

      if(form.find('input[name="penBirth"]').attr('type') === 'text') {
        var penBirth = form_data['penBirth'];
        var penJumin = penBirth.slice(2,4) + penBirth.slice(5,7) + penBirth.slice(8,10);
        form_data['penJumin'] = penJumin;
      }

      $this.prop('disabled', true).addClass('disabled').text('...');
      $.post('./ajax.my.recipient.update.php', form_data, 'json')
      .done(function() {
        if(form.find('select[name="penRecGraCd"]').length > 0 || form.find('select[name="penTypeCd"]').length > 0 || form.find('input[name="penExpiStDtm"]').attr('type') === 'text') {
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
        } else {
          var penId = $this.data("target");
          parent.selected_recipient(penId);
          $("#order_recipientBox", parent.document).hide();
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
