<?php
include_once("./_common.php");
include_once('./lib/eform.lib.php');

$dc_id = $_GET['id'];
if(!$dc_id) alert('잘못된 접근입니다.');

$g5['title'] = '전자계약서 확인';

include_once(G5_SHOP_PATH.'/shop.head.php');
add_stylesheet('<link rel="stylesheet" href="css/eforminquiry.css">', 0);
?>
<div class="eform-inquiry-wrap">
<div class="sub_section_tit">전자계약서 확인</div>
<form class="form-horizontal" action="eformInquiryView.php" method="post">
  <input type="hidden" name="dc_id" value="<?=$dc_id?>">
  <div class="panel panel-default">
    <div class="panel-heading"><strong>공급계약서 확인을 위해 정보를 입력해주세요.</strong></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-sm-4 control-label" for="penLtmNum"><b>요양인정번호</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-8">
          <input type="text" name="penLtmNum" value="" id="penLtmNum" required  class="form-control input-sm">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label" for="penNm"><b>수급자명</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-8">
          <input type="text" name="penNm" value="" id="penNm" required  class="form-control input-sm">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label" for="penBirth1"><b>생년월일</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-8">
          <select name="penBirth1" id="penBirth1" title="년도" class="form-control input-sm year " style="display:inline-block;width:32%;"></select>
          <select name="penBirth2" id="penBirth2" title="월" class="form-control input-sm month" style="display:inline-block;width:32%;"></select>
          <select name="penBirth3" id="penBirth3" title="일"  class="form-control input-sm day" style="display:inline-block;width:32%;"></select>
        </div>
      </div>
    </div>
  </div>
  <input id="btnEformInquiry" type="submit" value="계약서 확인">
</form>
</div>

<script>
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
    if(i<10){first_num = 0;}
    $(".month").append("<option value='"+first_num + i + "'>"+first_num + i+"</option>");
  }

  // 일 뿌려주기(1일부터 31일)
  var day;
  $(".day").append("<option value=''>일</option>");
  for (var i = 1; i <= 31; i++) {
    var first_num="";
    if(i<10){first_num = 0;}
    $(".day").append("<option value='" + first_num+i + "'>" +first_num+ i + "</option>");
  }
}

$(function() {
  setDateBox();
});
</script>

<?php
include_once(G5_SHOP_PATH.'/shop.tail.php');
?>
