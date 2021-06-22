<?php
include_once("./_common.php");

if(!$member['mb_id'])
  alert('먼저 로그인하세요.');

$link = get_recipient_link($rl_id, $member['mb_id']);
if(!$link || $link['status'] == 'wait')
  alert('유효하지 않은 요청입니다.');

$rl = sql_fetch("
  SELECT * FROM `recipient_link`
  WHERE rl_id = {$link['rl_id']}
");

$address = "{$rl['rl_pen_addr1']} {$rl['rl_pen_addr2']} {$rl['rl_pen_addr3']}";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>수급자 추천</title>
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/common_new.css">
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/font.css">
  <link rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?=G5_SHOP_URL?>/eform/css/writeeform.css">
  <link rel="stylesheet" href="<?=THEMA_URL?>/assets/bs3/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=c388393d4f69be5b284710964239c932"></script>
  <style>
  .popupContentWrap { padding: 20px;}
  .popupContentWrap .row { padding: 0 !important; }
  .popupContentWrap .row + .row { margin-top: 6px; }
  .pen_info_wrap { overflow: hidden; }
  .notice_wrap h5 { margin: 0; font-weight: bold; font-size: 14px; }
  #map { margin: 20px 0; }
  </style>
</head>
<body>
  <div id="popupWrap">
    <div class="popupHeadWrap flex">
      <h1 class="title" style="margin: 0;">수급자 추천</h1>
      <div class="menu">
        <button id="btnCloseEform" style="font-size: 20px;"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
    </div>
    <div class="popupContentWrap">
      <div class="pen_info_wrap">
        <div class="row">
          <div class="col-sm-2">·수급자명</div>
          <div class="col-sm-10"><?=$rl['rl_pen_name']?></div>
        </div>
        <div class="row">
          <div class="col-sm-2">·연락처</div>
          <div class="col-sm-10"><?=$rl['rl_pen_hp']?></div>
        </div>
        <div class="row">
          <div class="col-sm-2">·주소</div>
          <div class="col-sm-10"><?=$address?></div>
        </div>
        <div class="row">
          <div class="col-sm-2">·인정정보</div>
          <div class="col-sm-10"><?=$rl['rl_pen_ltm_num'] ?: '예비수급자'?></div>
        </div>
        <div class="row">
          <div class="col-sm-2">·보호자정보</div>
          <div class="col-sm-10">
            <?=$rl['rl_pen_pro_name']?>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-2">·요청사항</div>
          <div class="col-sm-10"><?=nl2br($rl['rl_request'])?></div>
        </div>
      </div>
      <div id="map" style="width:100%;height:300px;"></div>
      <div class="notice_wrap">
      <h5>알림사항</h5>
      <ul>
        <li>- 활동 시작 후 3일 이내 수급자 등록 미 진행 시 자동 해지됩니다.</li>
        <li>- 활동 후 수급자 연결 완료 시 수급자에게 알림이 전송됩니다.</li>
        <li><span style="color: red">*수급자 수락없이 연결 완료 선택 시 향후 추천에 불이익이 있습니다.</span></li>
        <li><span style="color: red">*수급자 연결 완료는 수급자 수락 이후 진행해주세요.</span></li>
      </ul>
    </div>
    </div>
    <div class="popupFootWrap flex">
      <button id="btnSubmitEform">활동시작</button>
      <button id="btnCancelEform">연결취소</button>
    </div>
  </div>
  <script>
    $(function() {
      var mapContainer = document.getElementById('map'), // 지도를 표시할 div
      mapOption = { 
        center: new kakao.maps.LatLng(<?=$rl['rl_pen_addr_lat']?>, <?=$rl['rl_pen_addr_lng']?>), // 지도의 중심좌표
        level: 4, // 지도의 확대 레벨
      };
      var map = new kakao.maps.Map(mapContainer, mapOption); // 지도를 생성합니다
      var markerPosition  = new kakao.maps.LatLng(<?=$rl['rl_pen_addr_lat']?>, <?=$rl['rl_pen_addr_lng']?>);
      var marker = new kakao.maps.Marker({
        position: markerPosition
      });
      marker.setMap(map);
      $(window).resize(function() {
        map.relayout();
        map.setCenter(markerPosition);
      });

      function closePopup() {
        $("body", parent.document).removeClass("modal-open");
        $("#popup_recipient_link", parent.document).hide();
        $("#popup_recipient_link", parent.document).find("iframe").remove();
      }

      // 창닫기 버튼
      $('#btnCloseEform').click(closePopup);
    });
  </script>
</body>
</html>