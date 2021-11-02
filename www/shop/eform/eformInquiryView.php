<?php
include_once("./_common.php");

$dc_id = $_POST['dc_id'];
$penLtmNum = 'L'.$_POST['penLtmNum'];
$penNm = $_POST['penNm'];
$penBirth1 = $_POST['penBirth1'];
$penBirth2 = $_POST['penBirth2'];
$penBirth3 = $_POST['penBirth3'];
$penBirth = $penBirth1.'.'.$penBirth2.'.'.$penBirth3;

if(!$dc_id) alert('잘못된 접근입니다.');
$sql = "select * from `eform_document` where
          dc_id = UNHEX('$dc_id') and
          penLtmNum = '$penLtmNum' and
          penNm = '$penNm' and
          penBirth = '$penBirth' and
          (dc_status = '2' or dc_status = '3')";

$eform = sql_fetch($sql);

if(!$eform['dc_id']) alert('존재하지 않는 계약서입니다.');

include_once(G5_SHOP_PATH.'/shop.head.php');
add_stylesheet('<link rel="stylesheet" href="css/eforminquiry.css">', 0);
?>

<div class="eform-inquiry-wrap">
  <div class="sub_section_tit">전자계약서 확인</div>
  <div class="panel panel-default">
    <div class="panel-heading"><strong>조회하실 전자문서를 선택하세요.</strong></div>
    <div class="panel-body" style="text-align: center; padding: 25px 0;">
        <a href="<?=G5_SHOP_URL."/eform/downloadEform.php?dc_id=$dc_id&penLtmNum=$penLtmNum&penNm=$penNm&penBirth=$penBirth"?>" class="btn btn-lg btn-warning primary">공급계약서</a>
        <?php if($eform['dc_status'] == '2') { ?>
        <a href="<?=G5_SHOP_URL."/eform/downloadCert.php?dc_id=$dc_id&penLtmNum=$penLtmNum&penNm=$penNm&penBirth=$penBirth"?>" class="btn btn-lg btn-info" style="margin-left: 10px;">감사추적인증서</a>
        <?php } ?>
      </div>
  </div>
</div>

<?php
include_once(G5_SHOP_PATH.'/shop.tail.php');
?>
