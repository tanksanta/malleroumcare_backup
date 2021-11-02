<style>
  
</style>
<div id="popupWrap">
  <div class="popupHeadWrap flex">
    <h1 class="title" style="margin: 0;">수급자 등록</h1>
    <div class="menu">
      <button id="btnCloseEform" style="font-size: 20px;"><i class="fa fa-times" aria-hidden="true"></i></button>
    </div>
  </div>
  <div class="popupContentWrap" style="bottom: 0 !important;">
    <div class="pen_info_wrap">
      <div class="row">
        <div class="col-xs-2" style="font-weight: bold;">·수급자명</div>
        <div class="col-xs-3" style="font-weight: bold;"><?=get_text($rl['rl_pen_name'])?></div>
      </div>
    </div>
    <div class="notice_wrap" style="margin: 20px 0 10px 0; padding: 15px; background-color: #f5f5f5;">
      <h5>알림사항</h5>
      <ul>
        <li>- 활동 후 수급자 연결 완료 시 수급자에게 알림이 전송됩니다.</li>
        <li><span style="color: red">*수급자 수락없이 연결 완료 선택 시 향후 추천에 불이익이 있습니다.</span></li>
        <li><span style="color: red">*수급자 연결 완료는 수급자 수락 이후 진행해주세요.</span></li>
      </ul>
    </div>
    <form id="form_register">
      <input type="hidden" name="w" value="r">
      <input type="hidden" name="rl_id" value="<?=$rl['rl_id']?>">
      <label for="chk_agreement">
        <input type="checkbox" name="chk_agreement" id="chk_agreement"> 확인함
      </label>
      <div style="text-align: center; margin-top: 20px;">
        <input type="submit" value="수급자 등록" style="display: inline-block; background-color: #ee8102; padding: 15px 60px; border: none; border-radius: 8px; color: #fff; font-size: 18px;">
        <div style="color: #666; padding: 5px;">연결완료 시 수급자(보호자)에게 알림메시지가 전송됩니다.</div>
      </div>
    </form>
  </div>
</div>

<script>

$(function() {
  // 수급자 등록 시
  $('#form_register').on('submit', function(e) {
    e.preventDefault();

    if(!$('#chk_agreement').prop('checked'))
      return alert('알림사항을 확인 후 \'확인함\'에 체크해주세요.');

    var params = $(this).serialize();
    $.post('./ajax.my.recipient.link.php', params, 'json')
    .done(function() {
      alert('수급자 등록이 완료되었습니다.');
      window.parent.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 생년월일 변경시
  $('#year, #month, #day').change(function() {
    $('input[name=penBirth]').val(
      $('#year').val() + '-' + $('#month').val() + '-' + $('#day').val()
    );
  });

  setDateBox();
});

//생년월일
function setDateBox() {
  var dt = new Date();
  var year = "";
  var com_year = dt.getFullYear();

  // 발행 뿌려주기
  $(".year").append("<option value=''>년도</option>");

  // 올해 기준으로 -50년부터 +1년을 보여준다.
  for (var y = (com_year - 100); y <= (com_year); y++) {
    $(".year").append("<option value='" + y + "'>" + y + "</option>");
  }

  // 월 뿌려주기(1월부터 12월)
  var month;
  $(".month").append("<option value=''>월</option>");
  for (var i = 1; i <= 12; i++) {
    var first_num="";
    if(i<10) { first_num = 0; }
    $(".month").append("<option value='"+first_num + i + "'>"+first_num + i+"</option>");
  }

  // 일 뿌려주기(1일부터 31일)
  var day;
  $(".day").append("<option value=''>일</option>");
  for (var i = 1; i <= 31; i++) {
    var first_num="";
    if(i<10) { first_num = 0; }
    $(".day").append("<option value='" +first_num+ i + "'>" + first_num+i + "</option>");
  }
}
</script>