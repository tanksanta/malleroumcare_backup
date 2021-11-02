<div id="popupWrap">
  <div class="popupHeadWrap flex">
    <h1 class="title" style="margin: 0;">수급자 추천</h1>
    <div class="menu">
      <button id="btnCloseEform" style="font-size: 20px;"><i class="fa fa-times" aria-hidden="true"></i></button>
    </div>
  </div>
  <div class="popupContentWrap">
    <?php if($link['status'] == 'link') { ?>
    <div class="link_info_wrap" style="color: #8a9600; font-weight: 500; border: 1px solid #c4d225; border-radius: 8px; font-size: 16px; text-align: center; padding: 20px 0; margin-bottom: 20px;">
      영업 활동 시작 : <?=date('Y-m-d', strtotime($link['updated_at']))?> <span style="color: #727272; font-size: 12px;">(+3일 후 자동 연결취소됨)</span>
    </div>
    <?php } ?>
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
        <div class="col-sm-10"><?=$rl['rl_pen_ltm_num'] ? 'L'.$rl['rl_pen_ltm_num'] : '예비수급자'?></div>
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
    <?php if($link['status'] == 'request') { ?>
    <button id="btnSubmitEform">활동시작</button>
    <?php } else if($link['status'] == 'link') { ?>
    <button id="btnSubmitEform" style="background-color:#c0cf16;">수급자등록</button>
    <?php } ?>
    <button id="btnCancelEform">연결취소</button>
  </div>
</div>

<script>
  $(function() {
    <?php if($link['status'] == 'link') { ?>
    // 수급자등록 버튼 클릭
    $('#btnSubmitEform').click(function() {
      $("#popup_recipient_link", window.parent.document).find("iframe").addClass('mini')
      .attr('src', 'my_recipient_link.php?rl_id=<?=urlencode($rl_id)?>&m=r');
    });
    <?php } else if($link['status'] == 'request') { ?>
    // 활동시작 버튼 클릭
    $('#btnSubmitEform').click(function() {
      if(confirm('수급자 영업활동을 시작하시겠습니까?')) {
        $.post('./ajax.my.recipient.link.php', {
          rl_id: '<?=get_text($rl_id)?>',
          w: 's'
        }, 'json')
        .done(function() {
          window.location.reload();
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
        });
      }
    });
    <?php } ?>

    // 연결취소 버튼 클릭
    $('#btnCancelEform').click(function() {
      if(confirm('수급자와 연결을 취소하시겠습니까?')) {
        $.post('./ajax.my.recipient.link.php', {
          rl_id: '<?=get_text($rl_id)?>',
          w: 'd'
        }, 'json')
        .done(function() {
          alert('수급자 연결요청이 취소되었습니다.');
          window.parent.location.reload();
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
        });
      }
    });

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
  });
</script>
