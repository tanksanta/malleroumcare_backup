<?php
include_once("./_common.php");

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
          <span style="float: left; width: 10px; height: 30px; line-height: 30px; margin-right: 5px;">L</span>
          <input type="number" min="0" maxlength="10" oninput="maxLengthCheck(this)" name="penLtmNum" value="" id="penLtmNum" required style="width: calc(100% - 16px)" class="form-control input-sm">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label" for="penNm"><b>수급자명</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-8">
          <input type="text" name="penNm" value="" id="penNm" required  class="form-control input-sm">
        </div>
      </div>
    </div>
  </div>
  <input id="btnEformInquiry" type="submit" value="계약서 확인">
</form>
</div>

<script>
function maxLengthCheck(object){
  if (object.value.length > object.maxLength){
    object.value = object.value.slice(0, object.maxLength);
  }
}

</script>

<?php
include_once(G5_SHOP_PATH.'/shop.tail.php');
?>
