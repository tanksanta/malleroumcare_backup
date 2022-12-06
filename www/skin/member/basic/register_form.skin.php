<?php
if($member['mb_id'] && !$w) {
  goto_url('/bbs/member_confirm.php?url=register_form.php');
}
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

if($header_skin)
  include_once('./header.php');

add_javascript(G5_POSTCODE_JS, 0);

?>
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
<?php if($config['cf_cert_use'] && ($config['cf_cert_ipin'] || $config['cf_cert_hp'])) { ?>
<script src="<?php echo G5_JS_URL ?>/certify.js?v=<?php echo APMS_SVER; ?>"></script>
<?php } ?>
<style>
.register-form .panel {

}
.register-form .panel .panel-heading {
  position: relative;
}
.register-form .panel .panel-heading:after {
  display:block;
  content:'';
  clear:both;
}
.register-form .panel .panel-heading .strong {
  display:block;
  float:left;
}
.register-form .panel .panel-heading .giup {
  float:right;position:absolute;top:11px;right:10px;
  font-size:12px;
}
.register-form .panel .panel-heading .giup span{
  margin-right: 15px;font-size:14px;color:red;font-weight:bold;
}
.register-form .panel .panel-heading .giup label.checkbox-inline {
  margin-top: -10px;
}
.register-form .panel .panel-body.panel-giup {
  /*display:none;*/
  display:block;
}
.register-form .panel .panel-body.panel-giup .half-container {
  font-size:0;
}
.register-form .panel .panel-body.panel-giup .half {
  display:inline-block;
  width:50%;
}
.register-form .panel .panel-body.panel-giup .half-container .half:first-child {
  margin-right:10px;
}
.register-form .panel .panel-body.panel-giup .control-label {
  padding-top: 3px;
}
.register-form input[type="radio"] {
  margin:0;
  margin-top: -5px;
}
.register-form label {
  margin-right: 5px;
}
#ui-datepicker-div { z-index: 999 !important; }
</style>
<form class="form-horizontal register-form" role="form" id="fregisterform" name="fregisterform" action="<?=$register_action_url?>" onsubmit="return fregisterform_submit();" method="post" enctype="multipart/form-data" autocomplete="off">
  <input type="hidden" name="w" value="<?php echo $w ?>">
  <input type="hidden" name="url" value="<?php echo $urlencode ?>">
  <input type="hidden" name="pim" value="<?php echo $pim;?>"> 
  <input type="hidden" name="agree" value="<?php echo $agree ?>">
  <input type="hidden" name="agree2" value="<?php echo $agree2 ?>">
  <input type="hidden" name="cert_type" value="<?php echo $member['mb_certify']; ?>">
  <input type="hidden" name="cert_no" value="">
  <?php if (isset($member['mb_sex'])) {  ?><input type="hidden" name="mb_sex" value="<?php echo $member['mb_sex'] ?>"><?php }  ?>
  <!--
  <?php if (isset($member['mb_nick_date']) && $member['mb_nick_date'] > date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400))) { // 닉네임수정일이 지나지 않았다면  ?>
    <input type="hidden" name="mb_nick_default" value="<?php echo get_text($member['mb_nick']) ?>">
    <input type="hidden" name="mb_nick" value="<?php echo get_text($member['mb_nick']) ?>">
  <?php }  ?>
  -->

  <div class="sub_section_tit">
    <?php echo $w==''?'회원가입':'정보수정'; ?>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading"><strong>기본정보</strong></div>
    <div class="panel-body">

      <div class="form-group has-feedback  ">
        <label class="col-sm-2 control-label" for="reg_mb_id"><b>아이디</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-3">
          <input type="text" name="mb_id" value="<?php echo $member['mb_id'] ?>" id="reg_mb_id" <?php echo $required ?> <?php echo $readonly ?> class="form-control input-sm" minlength="3" maxlength="20">
        </div>
        <div class="desc_txt">
          <span id="id_keyup"></span>
        </div>
      </div>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="reg_mb_password"><b>비밀번호</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-3">
          <input type="password" name="mb_password" id="reg_mb_password" <?php echo $required ?> class="form-control input-sm" minlength="3" maxlength="20">
          <div class="h15 hidden-lg hidden-md hidden-sm"></div>
        </div>
        <div class="desc_txt">
          <span id="pw_keyup">*영문/숫자를 반드시 포함한 8자리 이상 12자리 이하로 입력해 주세요.</span>
        </div>
      </div>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="reg_mb_password_re"><b>비밀번호 확인</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-3">
          <input type="password" name="mb_password_re" id="reg_mb_password_re" <?php echo $required ?> class="form-control input-sm" minlength="3" maxlength="20">
        </div>
        <div class="desc_txt">
          <span id="pw_re_keyup"></span>
        </div>
      </div>

      <div class="form-group has-feedback<?php echo ($config['cf_use_email_certify']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="reg_mb_email"><b>이메일</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-5">
          <input type="text" name="mb_email" value="<?php echo isset($member['mb_email'])?$member['mb_email']:''; ?>" id="reg_mb_email" required class="form-control input-sm email" maxlength="100">
        </div>
        <div class="desc_txt">
          <span id="email_keyup"></span>
        </div>
      </div>

      <?php if($w == '') { ?>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for=""><b>분류</b><strong class="sound_only">필수</strong></label>
        <div class="desc_txt">
          <input type="radio" name="mb_type" value="default" id="mb_type_default" <?php echo $member['mb_type'] === 'default' ? 'checked' : ''; ?>>
          <label for="mb_type_default">복지용구사업소</label>
          <input type="radio" name="mb_type" value="partner" id="mb_type_partner" <?php echo $member['mb_type'] === 'partner' ? 'checked' : ''; ?>>
          <label for="mb_type_partner">파트너(직배송, 설치, 소독)</label>
          <input type="radio" name="mb_type" value="normal" id="mb_type_normal" <?php echo $member['mb_type'] === 'normal' ? 'checked' : ''; ?>>
          <label for="mb_type_normal">일반회원</label>
        </div>
      </div>
      <?php } else { ?>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for=""><b>분류</b></label>
        <div class="desc_txt">
          <?php
          switch($member['mb_type']) {
            case 'default':
              echo '복지용구사업소';
              break;
            case 'partner':
              echo '파트너(직배송, 설치, 소독)';
              break;
            case 'normal':
              echo '일반회원';
              break;
          }
          ?>
        </div>
      </div>
      <?php } ?>
      <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="mb_giup_bname">
          <b id="mb_name_label">
            <?php
            if($w == 'u' && $member['mb_type'] === 'normal') {
              echo '이름';
            } else {
              echo '기업명';
            }
            ?>
          </b>
          <strong class="sound_only">필수</strong>
        </label>
        <div class="col-sm-3">
          <input type="text" id="mb_giup_bname" name="mb_giup_bname" value="<?php echo get_text($member['mb_giup_bname']) ?>" class="form-control input-sm" size="10">
        </div>
      </div>

      <?php if ($config['cf_use_hp'] || $config['cf_cert_hp']) {  ?>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="reg_mb_hp"><b>휴대폰번호</b><?php if ($config['cf_req_hp']) { ?><strong class="sound_only">필수</strong><?php } ?></label>
        <div class="col-sm-3">
          <input type="hidden" name="mb_hp" id="reg_mb_hp">
          <?php $mb_hp =explode('-',$member['mb_hp']); ?>
          <input type="text" class="form-control input-sm number_box1" name="mb_hp1" size="6" id="mb_hp1" title="전화번호(1)" maxlength="4"  value="<?=$mb_hp[0]?>" required>
          <input type="text" class="form-control input-sm number_box2" name="mb_hp2" size="6" id="mb_hp2" title="전화번호(2)" maxlength="4"  value="<?=$mb_hp[1]?>" required>
          <input type="text" class="form-control input-sm number_box2" name="mb_hp3" size="6" id="mb_hp3" title="전화번호(3)" maxlength="4"  value="<?=$mb_hp[2]?>" required>
          <?php if ($config['cf_cert_use'] && $config['cf_cert_hp']) { ?>
          <input type="hidden" name="old_mb_hp" value="<?php echo get_text($member['mb_hp']) ?>">
          <?php } ?>
        </div>
      </div>
      <?php }  ?>
              
      <?php if ($config['cf_use_tel']) {  ?>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="reg_mb_tel"><b>전화번호</b><?php if ($config['cf_req_tel']) { ?><strong class="sound_only">필수</strong><?php } ?></label>
        <div class="col-sm-3">
          <!-- <input type="text" name="mb_tel" value="<?php echo get_text($member['mb_tel']) ?>" id="reg_mb_tel" <?php echo $config['cf_req_tel']?"required":""; ?> class="form-control input-sm" maxlength="20"> -->
          <select name="mb_tel1" id="mb_tel1" class="form-control input-sm number_box1">
            <?php $mb_giup_btel =explode('-',$member['mb_giup_btel']); ?>
            <option value="02" <?=($mb_giup_btel[0] =="02")? "selected": "" ; ?> >02</option>
            <option value="010" <?=($mb_giup_btel[0] =="010")? "selected": "" ; ?>>010</option>
            <option value="031" <?=($mb_giup_btel[0] =="031")? "selected": "" ; ?>>031</option>
            <option value="032" <?=($mb_giup_btel[0] =="032")? "selected": "" ; ?>>032</option>
            <option value="033" <?=($mb_giup_btel[0] =="033")? "selected": "" ; ?>>033</option>
            <option value="041" <?=($mb_giup_btel[0] =="041")? "selected": "" ; ?>>041</option>
            <option value="042" <?=($mb_giup_btel[0] =="042")? "selected": "" ; ?>>042</option>
            <option value="043" <?=($mb_giup_btel[0] =="043")? "selected": "" ; ?>>043</option>
            <option value="044" <?=($mb_giup_btel[0] =="044")? "selected": "" ; ?>>044</option>
            <option value="051" <?=($mb_giup_btel[0] =="051")? "selected": "" ; ?>>051</option>
            <option value="052" <?=($mb_giup_btel[0] =="052")? "selected": "" ; ?>>052</option>
            <option value="053" <?=($mb_giup_btel[0] =="053")? "selected": "" ; ?>>053</option>
            <option value="054" <?=($mb_giup_btel[0] =="054")? "selected": "" ; ?>>054</option>
            <option value="055" <?=($mb_giup_btel[0] =="055")? "selected": "" ; ?>>055</option>
            <option value="061" <?=($mb_giup_btel[0] =="061")? "selected": "" ; ?>>061</option>
            <option value="062" <?=($mb_giup_btel[0] =="062")? "selected": "" ; ?>>062</option>
            <option value="063" <?=($mb_giup_btel[0] =="063")? "selected": "" ; ?>>063</option>
            <option value="064" <?=($mb_giup_btel[0] =="064")? "selected": "" ; ?>>064</option>
            <option value="070" <?=($mb_giup_btel[0] =="070")? "selected": "" ; ?>>070</option>
          </select>
          <input type="text" class="form-control input-sm number_box2" name="mb_tel2" size="6" id="mb_tel2" title="전화번호(2)" maxlength="4"  value="<?=$mb_giup_btel[1]?>" required>
          <input type="text" class="form-control input-sm number_box2" name="mb_tel3" size="6" id="mb_tel3" title="전화번호(3)" maxlength="4"  value="<?=$mb_giup_btel[2]?>" required>
        </div>
      </div>
      <?php }  ?>

      <div id="mb_fax_form" class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="reg_mb_fax"><b>팩스번호</b><?php if ($member['mb_type'] !== 'normal' && $config['cf_reg_fax']) { ?><strong class="sound_only">필수</strong><?php } ?></label>
        <div class="col-sm-3">
          <?php $mb_fax =explode('-',$member['mb_fax']); ?>
          <!-- <input type="text" name="mb_fax" value="<?php echo get_text($member['mb_fax']) ?>" id="reg_mb_fax" <?php echo ($member['mb_type'] !== 'normal' && $config['cf_reg_fax'])?"required":""; ?> class="form-control input-sm" maxlength="13"> -->
          <input type="text" class="form-control input-sm number_box1" name="mb_fax1" size="6" id="mb_fax1" title="전화번호(1)" maxlength="4"  value="<?=$mb_fax[0]?>" required>
          <input type="text" class="form-control input-sm number_box2" name="mb_fax2" size="6" id="mb_fax2" title="전화번호(2)" maxlength="4"  value="<?=$mb_fax[1]?>" required>
          <input type="text" class="form-control input-sm number_box2" name="mb_fax3" size="6" id="mb_fax3" title="전화번호(3)" maxlength="4"  value="<?=$mb_fax[2]?>" required>
        </div>
      </div>

    </div>
  </div>
  
  <div class="panel panel-default" id="panel-business">
    <div class="panel-heading">
      <strong>사업자 정보</strong>
    </div>
    <div class="panel-body panel-giup">
      <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="mb_giup_bnum"><b>사업자번호</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-3" style="min-width: unset; max-width: 140px;">
          <label><input type="text" <?php echo $member['mb_giup_bnum'] ? 'readonly' : ''; ?> id="mb_giup_bnum" name="mb_giup_bnum" value="<?php echo get_text($member['mb_giup_bnum']) ?>" class="form-control input-sm" size="13" maxlength="12" ></label>
        </div>
        <div class="desc_txt">
          <span id="bnum_keyup"></span>
        </div>
      </div>
      
      <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="mb_gi"><b>장기요양기관번호</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-3">
          <input type="text" id="mb_ent_num" name="mb_ent_num" value="<?php echo get_text($member['mb_ent_num']) ?>" class="form-control input-sm" size="10">
        </div>
      </div>

      <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="mb_giup_boss_name"><b>대표자명</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-3">
          <input type="text" id="mb_giup_boss_name" name="mb_giup_boss_name" value="<?php echo get_text($member['mb_giup_boss_name']) ?>" class="form-control input-sm" size="10">
        </div>
      </div>
            
     
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="mb_giup_buptae"><b>업태</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-3">
          <input type="text" id="mb_giup_buptae" name="mb_giup_buptae" value="<?php echo get_text($member['mb_giup_buptae']) ?>" class="form-control input-sm" size="10" >
          <div class="h15 hidden-lg hidden-md hidden-sm"></div>
        </div>
      </div>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="mb_giup_bupjong"><b>종목</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-3">
          <input type="text" id="mb_giup_bupjong" name="mb_giup_bupjong" value="<?php echo get_text($member['mb_giup_bupjong']) ?>" class="form-control input-sm" size="10" >
          <div class="h15 hidden-lg hidden-md hidden-sm"></div>
        </div>
      </div>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="reg_mb_giup_tax_email"><b>이메일(세금계산서 수신용)</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-5">
          <input type="text" name="mb_giup_tax_email" value="<?php echo isset($member['mb_giup_tax_email'])?$member['mb_giup_tax_email']:''; ?>" id="reg_mb_giup_tax_email" required class="form-control input-sm email" maxlength="100">
        </div>
      </div>
      <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="mb_giup_manager_name"><b>담당자명</b><strong class="sound_only">필수</strong></label>
        <div class="col-sm-3">
          <input type="text" id="mb_giup_manager_name" name="mb_giup_manager_name" value="<?php echo get_text($member['mb_giup_manager_name']) ?>" <?php echo $required ?> class="form-control input-sm" size="10">
        </div>
        <?php if($config['cf_cert_use']) { ?>
        <div class="col-sm-7">
          <div class="cert-btn">
            <?php 
              if($config['cf_cert_ipin'])
                echo '<button type="button" id="win_ipin_cert" class="btn btn-black btn-sm">아이핀 본인확인</button>'.PHP_EOL;
              if($config['cf_cert_hp'])
                echo '<button type="button" id="win_hp_cert" class="btn btn-black btn-sm">휴대폰 본인확인</button>'.PHP_EOL;
            ?>
          </div>
        </div>
        <?php } ?>
      </div>

      <?php if($config['cf_cert_use']) { ?>
      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-8 text-muted">
          <?php
          if ($config['cf_cert_use'] && $member['mb_certify']) {
            if($member['mb_certify'] == 'ipin')
              $mb_cert = '아이핀';
            else
              $mb_cert = '휴대폰';
          ?>
          <span class="black" id="msg_certify">
            [<strong><?php echo $mb_cert; ?> 본인확인</strong><?php if ($member['mb_adult']) { ?> 및 <strong>성인인증</strong><?php } ?> 완료]
          </span>
          <?php } ?>
          아이핀 본인확인 후에는 이름이 자동 입력되고 휴대폰 본인확인 후에는 이름과 휴대폰번호가 자동 입력되어 수동으로 입력할수 없게 됩니다.
          <noscript>본인확인을 위해서는 자바스크립트 사용이 가능해야합니다.</noscript>
        </div>
      </div>
      <?php } ?>

      <?php if ($req_nick) {  ?>
        <!-- 닉네임 수정 못하도록 변경 -->
        <?php if ($member['mb_id']) {  ?>
        <input type="hidden" name="mb_nick_default" value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : ''; ?>">
        <input type="hidden" name="mb_nick" value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : ''; ?>" id="reg_mb_nick" >
        <?php } else { ?>
        <input type="hidden" name="mb_nick_default" value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : ''; ?>">
        <?php } ?>
      <?php }  ?>


      <?php if ($config['cf_use_email_certify']) {  ?>
      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-8 text-muted">
          <?php if ($w=='') { echo "E-mail 로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다."; }  ?>
          <?php if ($w=='u') { echo "E-mail 주소를 변경하시면 다시 인증하셔야 합니다."; }  ?>
        </div>
      </div>
      <?php }  ?>

      <?php if ($config['cf_use_homepage']) {  ?>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="reg_mb_homepage"><b>홈페이지</b><?php if ($config['cf_req_homepage']){ ?><strong class="sound_only">필수</strong><?php } ?></label>
        <div class="col-sm-5">
          <input type="text" name="mb_homepage" value="<?php echo get_text($member['mb_homepage']) ?>" id="reg_mb_homepage" <?php echo $config['cf_req_homepage']?"required":""; ?> class="form-control input-sm" size="70" maxlength="255">
          <span class="fa fa-home form-control-feedback"></span>
        </div>
      </div>
      <?php }  ?>

      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label"><b>주소</b><?php if ($config['cf_req_addr']) { ?><strong class="sound_only">필수</strong><?php }  ?></label>
        <div class="col-sm-8">
          <label for="reg_mb_zip" class="sound_only">우편번호<?php echo $config['cf_req_addr']?'<strong class="sound_only"> 필수</strong>':''; ?></label>
          <label>
            <input type="text" name="mb_giup_zip" value="<?php echo $member['mb_giup_zip1'].$member['mb_giup_zip2'] ?>" id="mb_giup_zip" class="form-control input-sm" size="6" maxlength="6">
          </label>
          <label>
            <button type="button" class="btn btn-black btn-sm win_zip_find" style="margin-top:0px;" onclick="win_zip('fregisterform', 'mb_giup_zip', 'mb_giup_addr1', 'mb_giup_addr2', 'mb_giup_addr3', 'mb_giup_addr_jibeon');">주소 검색</button>
          </label>

          <div class="addr-line">
            <label class="sound_only" for="mb_giup_addr1">기본주소<?php echo $config['cf_req_addr']?'<strong class="sound_only"> 필수</strong>':''; ?></label>
            <input type="text" name="mb_giup_addr1" value="<?php echo get_text($member['mb_giup_addr1']) ?>" id="mb_giup_addr1" class="form-control input-sm" size="50" placeholder="기본주소">
          </div>

          <div class="addr-line">
            <label class="sound_only" for="mb_giup_addr2">상세주소</label>
            <input type="text" name="mb_giup_addr2" value="<?php echo get_text($member['mb_giup_addr2']) ?>" id="mb_giup_addr2" class="form-control input-sm" size="50" placeholder="상세주소">
          </div>

          <label class="sound_only" for="mb_giup_addr3">참고항목</label>
          <input type="text" name="mb_giup_addr3" value="<?php echo get_text($member['mb_giup_addr3']) ?>" id="mb_giup_addr3" class="form-control input-sm" size="50" readonly="readonly" placeholder="참고항목">
          <input type="hidden" name="mb_giup_addr_jibeon" value="<?php echo get_text($member['mb_giup_addr_jibeon']); ?>">
        </div>
      </div>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="mb_giup_file1 "><b>사업자등록증</b></label>
        <div class="col-sm-8 mb_giup_file1">
          <input type="file" name="crnFile" accept=".gif, .jpg, .png, .pdf" class="input-sm " id="mb_giup_file1">
          <?php if($member['crnFile']){ ?>
          <img style="max-width:100px; max-height:100px;"  src="<?=G5_DATA_URL?>/file/member/license/<?=$member['crnFile']?>" alt="">
          <?php }?>
          <p>*파일은 pdf, png, jpg, jpeg, gif 만 등록가능하며 10Mbyte 이하로 등록해주세요.</p>
        </div>
      </div>
      <?php if($w) { ?>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="mb_giup_file2"><b>사업자직인 (계약서 날인)</b></label>
        <div class="col-sm-8 mb_giup_file2">
          <input type="file" name="sealFile" accept=".gif, .jpg, .png, .pdf" class="input-sm" id="mb_giup_file2">
          <?php if($member['sealFile']){ ?>
          <img style="max-width:100px; max-height:100px;" src="<?=G5_DATA_URL?>/file/member/stamp/<?=$member['sealFile']?>" alt="">
          <?php }?>
          <p>*파일은 png, jpg, jpeg, gif 만 등록가능하며 10Mbyte 이하로 등록해주세요.</p>
        </div>
      </div>
      <?php } ?>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="mb_entConAcc01"><b>특약사항</b></label>
        <div class="col-sm-8">
          <textarea name="mb_entConAcc01" id="mb_entConAcc01" class="form-control input-sm" style="height: 80px;"><?php
            if($w) {
              echo $member["mb_entConAcc01"];
            } else {
              echo "1. 본 계약은 국민건강보험 노인장기요양보험 급여상품의 공급계약을 체결함에 목적이 있다.\n2. 본 계약서에 명시되지 아니한 사항이나 의견이 상이할 때에는 상호 협의하에 해결하는 것을 원칙으로 한다.";
            }
            ?></textarea>
        </div>
      </div>
      
      <!--<div class="form-group has-feedback">
        <label class="col-sm-2 control-label" for="mb_entConAcc02"><b>특약사항2</b></label>
        <div class="col-sm-8">
          <textarea name="mb_entConAcc02" id="mb_entConAcc02" class="form-control input-sm" style="height: 80px;"><?php
            if($w) {
              echo $member["mb_entConAcc02"];
            } else {
              echo '본 계약서에 명시되지 아니한 사항이나 의견이 상이할 때에는 상호 협의하에 해결하는 것을 원칙으로 한다.';
            }
            ?></textarea>
        </div>
      </div>-->
    </div>
  </div>
    
  <div class="panel panel-default">
    <div class="panel-heading">
      <strong>배송지 주소</strong>
      <div class="giup">
        <label for="mb_address_same" class="checkbox-inline">
          <input type="checkbox" name="mb_address_same" value="1" id="mb_address_same"> 대표 주소랑 동일
        </label>
      </div>
    </div>
    <div class="panel-body">
      <?php if ($config['cf_use_addr']) { ?>
      <div class="form-group has-feedback">
        <label class="col-sm-2 control-label"><b>주소</b><?php if ($config['cf_req_addr']) { ?><strong class="sound_only">필수</strong><?php }  ?></label>
        <div class="col-sm-8">
          <label for="reg_mb_zip" class="sound_only">우편번호<?php echo $config['cf_req_addr']?'<strong class="sound_only"> 필수</strong>':''; ?></label>
          <label>
            <input type="text" name="mb_zip" value="<?php echo $member['mb_zip1'].$member['mb_zip2'] ?>" id="reg_mb_zip" <?php echo $config['cf_req_addr']?"required":""; ?> class="form-control input-sm" size="6" maxlength="6">
          </label>
          <label>
            <button type="button" class="btn btn-black btn-sm win_zip_find" style="margin-top:0px;" onclick="win_zip('fregisterform', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');">주소 검색</button>
          </label>

          <div class="addr-line">
            <label class="sound_only" for="reg_mb_addr1">기본주소<?php echo $config['cf_req_addr']?'<strong class="sound_only"> 필수</strong>':''; ?></label>
            <input type="text" name="mb_addr1" value="<?php echo get_text($member['mb_addr1']) ?>" id="reg_mb_addr1" <?php echo $config['cf_req_addr']?"required":""; ?> class="form-control input-sm" size="50" placeholder="기본주소">
          </div>

          <div class="addr-line">
            <label class="sound_only" for="reg_mb_addr2">상세주소</label>
            <input type="text" name="mb_addr2" value="<?php echo get_text($member['mb_addr2']) ?>" id="reg_mb_addr2" class="form-control input-sm" size="50" placeholder="상세주소">
          </div>

          <label class="sound_only" for="reg_mb_addr3">참고항목</label>
          <input type="text" name="mb_addr3" value="<?php echo get_text($member['mb_addr3']) ?>" id="reg_mb_addr3" class="form-control input-sm" size="50" readonly="readonly" placeholder="참고항목">
          <input type="hidden" name="mb_addr_jibeon" value="<?php echo get_text($member['mb_addr_jibeon']); ?>">
        </div>
      </div>
      <?php } ?>
    </div>
  </div>

  <?php if ($member['mb_id'] && in_array($member['mb_type'], ['default', 'partner'])) { ?>
  <div class="panel panel-default" id="panel-business">
    <div class="panel-heading">
      <strong>담당자 관리</strong>
    </div>
    <div class="panel-body">
      <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="mm_id"><b>아이디</b></label>
        <div class="col-sm-3">
          <input type="text" id="mm_id" name="mm_id" value="" class="form-control input-sm" size="10">
        </div>
      </div>
      <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="mm_pw"><b>패스워드</b></label>
        <div class="col-sm-3">
          <input type="password" id="mm_pw" name="mm_pw" value="" class="form-control input-sm" size="10">
        </div>
      </div>
      <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="mm_name"><b>이름</b></label>
        <div class="col-sm-3">
          <input type="text" id="mm_name" name="mm_name" value="" class="form-control input-sm" size="10">
        </div>
      </div>
	  <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="mm_tel"><b>연락처</b></label>
        <div class="col-sm-3">
          <input type="text" id="mm_tel" name="mm_tel" value="" class="form-control input-sm" size="10">
        </div>
      </div>
      <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="mm_email"><b>이메일</b></label>
        <div class="col-sm-3">
          <input type="text" id="mm_email" name="mm_email" value="" class="form-control input-sm" size="10">
        </div>
      </div>
      <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
        <label class="col-sm-2 control-label" for="mm_memo"><b>메모</b></label>
        <div class="col-sm-3">
          <input type="text" id="mm_memo" name="mm_memo" value="" class="form-control input-sm" size="10">
        </div>
      </div>
      <div>
        <button type="button" id="add_member_manager" class="btn btn-black">담당자 추가</button>
      </div>
      <style>
        .manager_list {
          width: 100%;
          overflow-x: auto;
          white-space: nowrap;
        }
        .manager_list table {
          width: auto;
          min-width: 100%;
        }
        #add_member_manager {
          width: 100%;
          margin-bottom: 20px;
        }
      </style>
      <div class="tbl_head01 manager_list">
        <table>
          <thead>
            <tr>
              <th scope="col">아이디</th>
              <th scope="col">비밀번호</th>
              <th scope="col">이름</th>
			  <th scope="col">연락처</th>
              <th scope="col">이메일주소</th>
              <th scope="col">메모</th>
              <th scope="col" style="width: 100px;">설정</th>
            </tr>
          </thead>
          <tbody id="manager_list_body">
          <?php
          $mm_sql = "
            SELECT * FROM
              {$g5["member_table"]}
            WHERE
              mb_type = 'manager' and
              mb_manager = '{$member['mb_id']}'
          ";
          $mm_result = sql_query($mm_sql);

          while ($mm = sql_fetch_array($mm_result)) {
          ?>
            <tr>
              <td>
                <input type="hidden" class="mm_id" value="<?=$mm['mb_id']?>">
                <?=$mm['mb_id']?>
              </td>
              <td><input type="password" class="frm_input mm_pw" placeholder="비밀번호"></td>
              <td><input type="text" class="frm_input mm_name" placeholder="이름" value="<?=$mm['mb_name']?>"></td>
			  <td><input type="text" class="frm_input mm_tel" placeholder="연락처" value="<?=$mm['mb_tel']?>"></td>
              <td><input type="text" class="frm_input mm_email" placeholder="이메일" value="<?=$mm['mb_email']?>"></td>
              <td><input type="text" class="frm_input mm_memo" placeholder="메모" value="<?=$mm['mb_memo']?>"></td>
              <td class="td_center">
                <button type="button" class="btn btn-black btn-sm btn_mm_edit" data-id="<?=$mm['mb_id']?>">수정하기</button>
                <button type="button" class="btn btn-black btn-sm btn_mm_delete" data-id="<?=$mm['mb_id']?>">삭제</button>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php } ?>


  <div class="text-center" style="margin:30px 0px;">
    <button type="button" id="btn_submit" onclick="fregisterform_submit()"class="btn btn-color" accesskey="s"><?php echo $w==''?'회원가입':'정보수정'; ?></button>
    <?php if(!$pim) { ?>
    <a href="<?php echo G5_URL ?>" class="btn btn-black" role="button">취소</a>
    <?php } ?>
  </div>
</form>
<script>

$(function() {
  
  $('#add_member_manager').on("click", function() {
    $.post('/bbs/ajax.member_manager.php', {
      mm_id: $('#mm_id').val(),
      mm_pw: $('#mm_pw').val(),
      mm_name: $('#mm_name').val(),
	  mm_tel: $('#mm_tel').val(),
      mm_email: $('#mm_email').val(),
      mm_memo: $('#mm_memo').val()
    }, 'json')
    .done(function() {
      alert('담당자 등록이 완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  $('.btn_mm_edit').on('click', function() {
    $tr = $(this).closest('tr');
    $.post('/bbs/ajax.member_manager.php', {
      w: 'u',
      mm_id: $tr.find('.mm_id').val(),
      mm_pw: $tr.find('.mm_pw').val(),
      mm_name: $tr.find('.mm_name').val(),
	  mm_tel: $tr.find('.mm_tel').val(),
      mm_email: $tr.find('.mm_email').val(),
      mm_memo: $tr.find('.mm_memo').val()
    }, 'json')
    .done(function() {
      alert('담당자 수정이 완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  $('.btn_mm_delete').on('click', function() {
    $tr = $(this).closest('tr');
    $.post('/bbs/ajax.member_manager.php', {
      w: 'd',
      mm_id: $tr.find('.mm_id').val()
    }, 'json')
    .done(function() {
      alert('담당자 삭제가 완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  $(document).on("click", '.delete_manager', function() {
    $(this).closest('tr').remove();
  });
  
  <?php if ($w && $member['mb_type'] === 'normal') { ?>
    $('#panel-business').hide();
    $('.giup').hide();
    $('#mb_fax_form').hide();
  <?php } ?>
  <?php if (!$w) { ?>
  $('input[name="mb_type"]').click(function() {
    if ($(this).val() === 'normal') {
      $('#mb_name_label').text('이름');
      $('#panel-business').hide();
      $('.giup').hide();
      $('#mb_fax_form').hide();
    } else {
      $('#mb_name_label').text('기업명');
      $('#panel-business').show();
      $('.giup').show();
      $('#mb_fax_form').show();
    }
  })
  $('#mb_type_default').click();
  <?php } ?>

  $('#mb_giup').click(function() {
    $('.panel-giup').toggle();
  })
  $("#reg_zip_find").css("display", "inline-block");

  <?php if ( $member['mb_giup_type'] > 0 ) { ?>
  $('#mb_giup').click();
  <?php } ?>

  $('#mb_address_same').click(function() {
    if (!$('#mb_giup_zip').val()) {
      alert('사업자 정보 주소를 입력해주세요.');
      $('#mb_address_same').prop("checked", false);
      return false;
    }

    $('#reg_mb_zip').val($('#mb_giup_zip').val());
    $('#reg_mb_addr1').val($('#mb_giup_addr1').val());
    $('#reg_mb_addr2').val($('#mb_giup_addr2').val());
    $('#reg_mb_addr3').val($('#mb_giup_addr3').val());
    $('#mb_addr_jibeon').val($('#mb_giup_addr_jibeon').val());
  });

  <?php if($config['cf_cert_use'] && $config['cf_cert_ipin']) { ?>
  // 아이핀인증
  $("#win_ipin_cert").click(function(e) {
    if(!cert_confirm())
      return false;

    var url = "<?php echo G5_OKNAME_URL; ?>/ipin1.php";
    certify_win_open('kcb-ipin', url, e);
    return;
  });

  <?php } ?>
  <?php if($config['cf_cert_use'] && $config['cf_cert_hp']) { ?>
  // 휴대폰인증
  $("#win_hp_cert").click(function(e) {
    if(!cert_confirm())
      return false;

    <?php
    switch($config['cf_cert_hp']) {
      case 'kcb':
        $cert_url = G5_OKNAME_URL.'/hpcert1.php';
        $cert_type = 'kcb-hp';
        break;
      case 'kcp':
        $cert_url = G5_KCPCERT_URL.'/kcpcert_form.php';
        $cert_type = 'kcp-hp';
        break;
      case 'lg':
        $cert_url = G5_LGXPAY_URL.'/AuthOnlyReq.php';
        $cert_type = 'lg-hp';
        break;
      default:
        echo 'alert("기본환경설정에서 휴대폰 본인확인 설정을 해주십시오");';
        echo 'return false;';
        break;
    }
    ?>

    certify_win_open("<?php echo $cert_type; ?>", "<?php echo $cert_url; ?>", e);
    return;
  });
  <?php } ?>


  $('#mb_entBusiNum').on('keyup', function() {
    var num = $('#mb_entBusiNum').val();
    num.trim();
    this.value = only_num(num) ;
  });
  $('#mb_entBusiNum').on('keyup', function() {
    var num = $('#mb_entBusiNum').val();
    num.trim();
    this.value = only_num(num) ;
  });
  $('#mb_entBusiNum').on('keyup', function() {
    var num = $('#mb_entBusiNum').val();
    num.trim();
    this.value = only_num(num) ;
  });
  $('#mb_entBusiNum').on('keyup', function() {
    var num = $('#mb_entBusiNum').val();
    num.trim();
    this.value = only_num(num) ;
  });

  $('#add_manager').on("click", function() {
    var el = $('#manager_list_body');

    var str = '<tr>';
    str +=      '<td>';
    str +=        '<input type="text" name="mm_name[]" value="" class="frm_input" size="30" maxlength="20" style="width:100%">';
    str +=      '</td>';
    str +=      '<td>';
    str +=        '<input type="text" name="mm_part[]" value="" class="frm_input" size="10" maxlength="20">';
    str +=      '</td>';
    str +=      '<td>';
    str +=        '<input type="text" name="mm_rank[]" value="" class="frm_input" size="10" maxlength="20">';
    str +=      '</td>';
    str +=      '<td>';
    str +=        '<input type="text" name="mm_work[]" value="" class="frm_input" size="10" maxlength="20">';
    str +=      '</td>';
    str +=      '<td>';
    str +=        '<input type="text" name="mm_hp[]" value="" class="frm_input" size="30" maxlength="20">';
    str +=      '</td>';
    str +=      '<td>';
    str +=        '<input type="text" name="mm_hp_extension[]" value="" class="frm_input" size="10" maxlength="10">';
    str +=      '</td>';
    str +=      '<td>';
    str +=        '<input type="text" name="mm_tel[]" value="" class="frm_input" size="30" maxlength="20">';
    str +=      '</td>';
    str +=      '<td>';
    str +=        '<input type="text" name="mm_email[]" value="" class="frm_input" size="30" maxlength="50">';
    str +=      '</td>';
    str +=      '<td style="text-align:center;">';
    str +=        '<button type="button" class="btn-black btn delete_manager">삭제</button>';
    str +=      '</td>';
    str +=    '</tr>';

    $(el).append(str);

    $('input[name="mm_tel[]"]').on('keyup', function() {
      var num = $(this).val();
      num.trim();
      this.value = auto_phone_hypen(num) ;
    });

    $('input[name="mm_hp[]"]').on('keyup', function() {
      var num = $(this).val();
      num.trim();
      this.value = auto_phone_hypen(num) ;
    });
  });

  $(document).on("click", '.delete_manager', function() {
    $(this).closest('tr').remove();
  });

  $('#mb_giup_bnum').on('keyup', function() {
    disable_giup_sbnum(); // 사업자 재입력시 종사업자 리셋
    $('#form-bnum-feed-text > span').hide();
  
    var num = $('#mb_giup_bnum').val();
    num.trim();
    this.value = auto_saup_hypen(num) ;
  });

    
  $('#mb_giup_sbnum').on('keyup', function() {
    var num = $('#mb_giup_sbnum').val();
    num.trim();
    this.value = only_num(num) ;
  });

  $('#mb_entBusiNum').on('keyup', function() {
    var num = $('#mb_entBusiNum').val();
    num.trim();
    this.value = only_num(num) ;
  });
    
  
  $('#mb_giup_btel').on('keyup', function() {
    var num = $('#mb_giup_btel').val();
    num.trim();
    this.value = auto_phone_hypen(num) ;
  });
  
  $('input[name="mm_tel[]"]').on('keyup', function() {
    var num = $(this).val();
    num.trim();
    this.value = auto_phone_hypen(num) ;
  });

  $('input[name="mm_hp[]"]').on('keyup', function() {
    var num = $(this).val();
    num.trim();
    this.value = auto_phone_hypen(num) ;
  });
  
  $('#reg_mb_hp').on('keyup', function() {
    var num = $(this).val();
    num.trim();
    this.value = auto_phone_hypen(num) ;
  });
  
  $('#reg_mb_fax').on('keyup', function() {
    var num = $(this).val();
    num.trim();
    this.value = auto_phone_hypen(num) ;
  });

  //아이디 체크
  var mb_id_check_timer = null;
  $('#reg_mb_id').on('keyup change input', function() {

    if(mb_id_check_timer)
      clearTimeout(mb_id_check_timer);

    var $this = $(this);
    
    mb_id_check_timer = setTimeout(function() {
      if($this.val().length < 3) {
        $('#id_keyup').html("아이디(은)는 3자 이상 입력하셔야합니다.");
        $('#id_keyup').css( "color", "#d44747" );
        return false;
      }
      var msg = reg_mb_id_check();
      if(msg) {
        $('#id_keyup').html("사용 불가능한 아이디 입니다.");
        $('#id_keyup').css( "color", "#d44747" );
      } else {
        $('#id_keyup').html("사용 가능한 아이디 입니다.");
        $('#id_keyup').css( "color", "#4788d4" );
      }
    }, 500);
  });
  //비밀번호 체크
  var mb_pw_check_timer = null;
  $('#reg_mb_password').on('keyup change input', function() {

    if(mb_pw_check_timer)
      clearTimeout(mb_pw_check_timer);
    
    var $this = $(this);

    mb_pw_check_timer = setTimeout(function() {
      var msg = check_pw($this.val());
      if(msg) {
        $('#pw_keyup').html(msg);
        $('#pw_keyup').css( "color", "#d44747" );
      } else {
        $('#pw_keyup').html("등록 가능한 비밀번호입니다.");
        $('#pw_keyup').css( "color", "#4788d4" );
      }
    }, 500);
  });
  //비밀번호 확인 체크
  var mb_pw_re_check_timer = null;
  $('#reg_mb_password_re').on('keyup change input', function() {

    if(mb_pw_re_check_timer)
      clearTimeout(mb_pw_re_check_timer);
    
    var $this = $(this);

    mb_pw_re_check_timer = setTimeout(function() {
      if($this.val() && $this.val() == $('#reg_mb_password').val()) {
        $('#pw_re_keyup').html("동일하게 입력하셨습니다.");
        $('#pw_re_keyup').css( "color", "#4788d4" );
      } else {
        $('#pw_re_keyup').html("");
      }
    }, 500);
  });
  //이메일 체크
  var mb_email_check_timer = null;
  $('#reg_mb_email').on('keyup change input', function() {

    if(mb_email_check_timer)
      clearTimeout(mb_email_check_timer);

    var $this = $(this);
    
    mb_email_check_timer = setTimeout(function() {
      var msg = reg_mb_email_check();
      if(msg) {
        $('#email_keyup').html(msg);
        $('#email_keyup').css( "color", "#d44747" );
      } else {
        $('#email_keyup').html("사용 가능한 이메일 입니다.");
        $('#email_keyup').css( "color", "#4788d4" );
      }
    }, 500);
  });
  //사업자번호 체크
  var giup_bnum_check_timer = null;
  $('#mb_giup_bnum').on('keyup change input', function() {

    if(giup_bnum_check_timer)
      clearTimeout(giup_bnum_check_timer);

    var $this = $(this);
    
    giup_bnum_check_timer = setTimeout(function() {
      var msg = reg_mb_giup_bnum_check();
      if(msg) {
        $('#bnum_keyup').html(msg);
        $('#bnum_keyup').css( "color", "#d44747" );
      } else {
        $('#bnum_keyup').html("사용 가능한 사업자번호입니다.");
        $('#bnum_keyup').css( "color", "#4788d4" );
      }
    }, 500);
  });

  //전화번호 숫자만
  $('#mb_hp1').on('keyup', function() {
    var num = $('#mb_hp1').val();
    num.trim();
    this.value = only_num(num) ;
  });
  $('#mb_hp2').on('keyup', function() {
    var num = $('#mb_hp2').val();
    num.trim();
    this.value = only_num(num) ;
  });
  $('#mb_hp3').on('keyup', function() {
    var num = $('#mb_hp3').val();
    num.trim();
    this.value = only_num(num) ;
  });
  $('#mb_tel2').on('keyup', function() {
    var num = $('#mb_tel2').val();
    num.trim();
    this.value = only_num(num) ;
  });
  $('#mb_tel3').on('keyup', function() {
    var num = $('#mb_tel3').val();
    num.trim();
    this.value = only_num(num) ;
  });
  $('#mb_fax1').on('keyup', function() {
    var num = $('#mb_fax1').val();
    num.trim();
    this.value = only_num(num) ;
  });
  $('#mb_fax2').on('keyup', function() {
    var num = $('#mb_fax2').val();
    num.trim();
    this.value = only_num(num) ;
  });
  $('#mb_fax3').on('keyup', function() {
    var num = $('#mb_fax3').val();
    num.trim();
    this.value = only_num(num) ;
  });
});

// submit 최종 폼체크
function fregisterform_submit() {
  var f = document.getElementById("fregisterform");
  // 회원아이디 검사
  if (f.w.value == "") {
    var msg = reg_mb_id_check();
    if (msg) {
      alert(msg);
      f.mb_id.select();
      return false;
    }
  }
  
  <?php if($w == '') { ?>
  if(!$('input[name=mb_type]:checked').val()) {
    alert("회원 분류를 선택해주세요.");
    return false;
  }
  <?php } ?>
    
  if (f.mb_password.value.length < 6 || f.mb_password.value.length > 12) {
    alert("영문/숫자를 반드시 포함한 8자리 이상 12자리 이하로 입력해 주세요.");
    f.mb_password.focus();
    return false;
  }

  if (f.mb_password_re.value.length < 6 || f.mb_password_re.value.length > 12) {
    alert("영문/숫자를 반드시 포함한 8자리 이상 12자리 이하로 입력해 주세요.");
    f.mb_password_re.focus();
    return false;
  }

  if(f.mb_password_re.value.search(/\s/) != -1){
    alert("비밀번호는 공백 없이 입력해주세요.");
    return false;
  }
  if (f.mb_password.value != f.mb_password_re.value) {
    alert("비밀번호가 같지 않습니다.");
    f.mb_password_re.focus();
    return false;
  }

  var num = f.mb_password.value.search(/[0-9]/g);
  var eng = f.mb_password.value.search(/[a-z]/ig);

  if(num < 0 || eng < 0 ){
    alert("비밀번호는 영문,숫자를 혼합하여 입력해주세요.");
    return false;
  }

  if(!f.mb_giup_bname.value){
    alert('기업명을 입력하세요.');
    f.mb_giup_bname.focus();
    return false;
  }

  var mb_hp = $("#mb_hp1").val() + "-" + $("#mb_hp2").val() + "-" + $("#mb_hp3").val();
  if(!$("#mb_hp1").val()){
    alert('휴대폰번호를 입력해주세요.');
    $("#mb_hp1").focus();
    return false;
  }
  if(!$("#mb_hp2").val()){
    alert('휴대폰번호를 입력해주세요');
    $("#mb_hp2").focus();
    return false;
  }
  if(!$("#mb_hp3").val()){
    alert('휴대폰번호를 입력해주세요');
    $("#mb_hp3").focus();
    return false;
  }

  var mb_tel = $("#mb_tel1").val() + "-" + $("#mb_tel2").val() + "-" + $("#mb_tel3").val();
  if(!$("#mb_tel1").val()){
    alert('전화번호를 입력해주세요.');
    $("#mb_tel1").focus();
    return false;
  }
  if(!$("#mb_tel2").val()){
    alert('전화번호를 입력해주세요');
    $("#mb_tel2").focus();
    return false;
  }
  if(!$("#mb_tel3").val()){
    alert('전화번호를 입력해주세요');
    $("#mb_tel3").focus();
    return false;
  }
  var msg = reg_mb_email_check();
  if (msg) {
    alert(msg);
    f.reg_mb_email.select();
    return false;
  }

  <?php if($w) { ?>
  var mb_type = '<?=$member['mb_type']?>';
  <?php } else { ?>
  var mb_type = $('input[name=mb_type]:checked').val();
  <?php } ?>
  if (mb_type === 'default' || mb_type === 'partner') {
    //mb_fax
    var mb_fax = $("#mb_fax1").val() + "-" + $("#mb_fax2").val() + "-" + $("#mb_fax3").val();
    if(!$("#mb_fax1").val()){
      alert('팩스를 입력해주세요.');
      $("#mb_fax1").focus();
      return false;
    }
    if(!$("#mb_fax2").val()){
      alert('팩스를 입력해주세요.');
      $("#mb_fax2").focus();
      return false;
    }
    if(!$("#mb_fax3").val()){
      alert('팩스를 입력해주세요.');
      $("#mb_fax3").focus();
      return false;
    }
    if (!f.mb_giup_tax_email.value) {
        alert('세금계산서 수신용 이메일을 입력하세요.');
        f.mb_giup_tax_email.focus();
        return false;
    }
    if (!f.mb_giup_bnum.value) {
        alert('사업자 번호를 입력하세요.');
        f.mb_giup_bnum.focus();
        return false;
    }
    if (!f.mb_giup_boss_name.value) {
        alert('대표자명을 입력하세요.');
        f.mb_giup_boss_name.focus();
        return false;
    }
    if (!f.mb_giup_bupjong.value) {
        alert('업종을 입력하세요.');
        f.mb_giup_bupjong.focus();
        return false;
    }
    if (!f.mb_giup_buptae.value) {
        alert('업태를 입력하세요.');
        f.mb_giup_buptae.focus();
        return false;
    }
    if (!f.mb_giup_manager_name.value) {
        alert('담당자명을 입력하세요.');
        f.mb_giup_manager_name.focus();
        return false;
    }

    if (!f.mb_giup_zip.value) {
        alert('(사업자정보) 우편번호를 입력하세요');
        f.mb_giup_zip.focus();
        return false;
    }
    if (!f.mb_giup_addr1.value) {
        alert('(사업자정보) 주소를 입력하세요');
        f.mb_giup_addr1.focus();
        return false;
    }
    if (!f.mb_giup_addr2.value&&!f.mb_giup_addr3.value) {
        alert('(사업자정보) 주소상세를 입력하세요');
        f.mb_giup_addr2.focus();
        return false;
    }

    if (!f.mb_zip.value) {
        alert('(배송지 주소) 우편번호를 입력하세요');
        f.mb_zip.focus();
        return false;
    }
    if (!f.mb_addr1.value) {
        alert('(배송지 주소) 주소를 입력하세요');
        f.mb_addr1.focus();
        return false;
    }
    if (!f.mb_addr2.value&&!f.mb_addr3.value) {
        alert('(배송지 주소) 주소상세를 입력하세요');
        f.mb_addr2.focus();
        return false;
    }
  }
  //체크 끝

  <?php if($w){ ?>
  //직인파일
  var imgFileItem2 = $(".mb_giup_file2 input[type='file']");
  for(var i = 0; i < imgFileItem2.length; i++){
    if($(imgFileItem2[i])[0].files[0]){
      if($(imgFileItem2[i])[0].files[0].size > 1024 * 1024 * 10){
        alert('사업자직인 (계약서 날인) : 10MB 이하 파일만 등록할 수 있습니다.\n\n' + '현재파일 용량 : ' + (Math.round($(imgFileItem2[i])[0].files[0].size / 1024 / 1024 * 100) / 100) + 'MB');
        return false;
      }
    }
  }
  <?php } ?>

  //사업자등록증
  var flag ='<?=$member['crnFile']?>';
  var imgFileItem1 = $(".mb_giup_file1 input[type='file']");
  for(var i = 0; i < imgFileItem1.length; i++) {
    
    if (mb_type === 'default' || mb_type === 'partner') {
      if(!flag) {
        if(!$(imgFileItem1[i])[0].files[0]) {
          alert('사업자등록증을 첨부해주세요.');
          return false; 
        }
        if($(imgFileItem1[i])[0].files[0].size > 1024 * 1024 * 10) {
          alert('사업자등록증 : 10MB 이하 파일만 등록할 수 있습니다.\n\n' + '현재파일 용량 : ' + (Math.round($(imgFileItem1[i])[0].files[0].size / 1024 / 1024 * 100) / 100) + 'MB');
          return false;
        }
      }
    }
  }

  var info = "<?php echo $w==''?'회원가입 하시겠습니까?':'수정 하시겠습니까?'; ?>";
  if(confirm(info)) {
    f.submit();
  }
  return false;
}

function check_giup_sbnum(type) {
  var msg = reg_mb_giup_sbnum_check();

  if (type == "click") {
    if (msg === "") {
      $('#form-sbnum-feed-text > span').hide();
      $('#form-sbnum-feed-text > .available').show();
      return true;
    }

    else if (msg === "이미 사용중인 종사업자번호입니다.") {
      $('#form-sbnum-feed-text > span').hide();
      $('#form-sbnum-feed-text > .unavailable').show();
      return false;
    }

    else if (msg === "종사업자번호를 올바르게 입력해 주십시오.") {
      $('#form-sbnum-feed-text > span').hide();
      alert("종사업자번호를 올바르게 입력해 주십시오.")
      return false;
    }

    else if (msg === "종사업자번호를 입력해 주십시오.") {
      $('#form-sbnum-feed-text > span').hide();
      alert("종사업자번호를 입력해 주십시오.")
      return false;
    }
    
    else {
      $('#form-sbnum-feed-text > span').hide();
      alert(msg);
      return false;
    }
  } else {
    if (msg) {
      alert(msg);
      return false;
    } else {
      return true;
    }
  }
}

function enable_giup_sbnum() {
  $('#form-bnum-feed-text > span').hide();
  $('#form-sbnum-feed-text > span').hide();
  
  $('#sbnum').show();
}

function disable_giup_sbnum() {
  $('#mb_giup_sbnum').val("");
  $('#mb_giup_sbnum_explain').val("");

  $('#sbnum').hide();
}

<?php if ($w == "u") { ?>
$(function () {
  $('#mb_giup_bnum_check').hide();
  $('#mb_giup_sbnum_check').hide();
  
  if ($('#mb_giup_sbnum').val()) {
    $('#sbnum').show();
  }
});
<?php } ?>

function check_pw(pw) {
  var pw = pw;
  var num = pw.search(/[0-9]/g);
  var eng = pw.search(/[a-z]/ig);
  if(pw.length < 8 || pw.length > 12) {
    return "8자리 ~ 12자리 이내로 입력해주세요.";
  } else if(pw.search(/\s/) != -1) {
    return "비밀번호는 공백 없이 입력해주세요.";
  } else if(num < 0 || eng < 0 ) {
    return "영문,숫자를 혼합하여 입력해주세요.";
  } else {
    return false;
  }
}
</script>
