

<?php
if(!$w){
    if(!$_POST['agree']||! $_POST['agree2']){
        alert('이용약관에 동의해주세요.',G5_BBS_URL.'/register.php');
    }
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
.register-form .panel .panel-body.panel-giup input[type="radio"] {
	margin:0;
}
#ui-datepicker-div { z-index: 999 !important; }

</style>
<form class="form-horizontal register-form" role="form" id="fregisterform" name="fregisterform" action="<?php echo $action_url ?>" onsubmit="return fregisterform_submit();" method="post" enctype="multipart/form-data" autocomplete="off">
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
		회원가입
	</div>

	<div class="panel panel-default">
		<div class="panel-heading"><strong>관리자 계정 정보</strong></div>
		<div class="panel-body">

			<div class="form-group has-feedback text-gap">
				<label class="col-sm-2 control-label" for="reg_mb_id"><b>아이디</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" name="mb_id" value="<?php echo $member['mb_id'] ?>" id="reg_mb_id" <?php echo $required ?> <?php echo $readonly ?> class="form-control input-sm" minlength="3" maxlength="20">
					<span class="fa fa-check form-control-feedback"></span>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-8 text-muted">
					<div id="msg_mb_id"></div>
					영문자, 숫자, _ 만 입력 가능. 최소 3자이상 입력하세요.
				</div>
			</div>

			<div class="form-group has-feedback">
				<label class="col-sm-2 control-label" for="reg_mb_password"><b>비밀번호</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="password" name="mb_password" id="reg_mb_password" <?php echo $required ?> class="form-control input-sm" minlength="3" maxlength="20">
					<span class="fa fa-lock form-control-feedback"></span>
					<div class="h15 hidden-lg hidden-md hidden-sm"></div>
				</div>
				<label class="col-sm-2 control-label" for="reg_mb_password_re"><b>비밀번호 확인</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="password" name="mb_password_re" id="reg_mb_password_re" <?php echo $required ?> class="form-control input-sm" minlength="3" maxlength="20">
					<span class="fa fa-check form-control-feedback"></span>
				</div>
			</div>

			<!-- <div class="form-group has-feedback">
				<label class="col-sm-2 control-label" for=""><b>분류</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<span style="margin-top:10px;font-weight:bold;height:30px;line-height:30px;">복지용구사업소</span>
				</div>
			</div> -->

            
            <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_name"><b>관리자이름</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_name" name="mb_name" value="<?php echo get_text($member['mb_name']) ?>" class="form-control input-sm" size="10">
				</div>
			</div>

            <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_sex"><b>관리자 성별</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
                <?php
                    $mb_sex_check00="";
                    $mb_sex_check02="";
                    if($member['mb_sex'] =="00") $mb_sex_check00="checked";
                    if($member['mb_sex'] =="01") $mb_sex_check01="checked";
                ?>
					남<input type="radio" id="mb_sex" name="mb_sex" value="00" <?=$mb_sex_check00?> class="input-sm" size="10">
					여<input type="radio" id="mb_sex" name="mb_sex" value="01" <?=$mb_sex_check01?> class="input-sm" size="10">
				</div>
			</div>
            
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="penBirth"><b>관리자 생일</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
                        <select name="penBirth1" id="year" title="년도" class="form-control input-sm year" style="display:inline-block;width:32%;"></select>
                        <select name="penBirth2" id="month" title="월" class="form-control input-sm month" style="display:inline-block;width:32%;"></select>
                        <select name="penBirth3" id="day" title="일"  class="form-control input-sm day" style="display:inline-block;width:32%;"></select>
				</div>
			</div>

			<?php if ($config['cf_use_hp'] || $config['cf_cert_hp']) {  ?>
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label" for="reg_mb_hp"><b>휴대폰번호</b><?php if ($config['cf_req_hp']) { ?><strong class="sound_only">필수</strong><?php } ?></label>
					<div class="col-sm-3">
						<input type="text" name="mb_hp" value="<?php echo get_text($member['mb_hp']) ?>" id="reg_mb_hp" <?php echo ($config['cf_req_hp'])?"required":""; ?> class="form-control input-sm" maxlength="13">
						<span class="fa fa-mobile form-control-feedback"></span>
						<?php if ($config['cf_cert_use'] && $config['cf_cert_hp']) { ?>
							<input type="hidden" name="old_mb_hp" value="<?php echo get_text($member['mb_hp']) ?>">
						<?php } ?>
					</div>
				</div>
			<?php }  ?>

			<div class="form-group has-feedback<?php echo ($config['cf_use_email_certify']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="reg_mb_email"><b>이메일</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-5">
					<input type="hidden" name="old_email" value="<?php echo $member['mb_email'] ?>">
					<input type="text" name="mb_email" value="<?php echo isset($member['mb_email'])?$member['mb_email']:''; ?>" id="reg_mb_email" required class="form-control input-sm email" size="70" maxlength="100">
					<span class="fa fa-envelope form-control-feedback"></span>
				</div>
			</div>

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
			<?php }  ?>

            
		</div>
	</div>
	<!-- <div class="panel panel-default">
		<div class="panel-body">
			
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_manager_name"><b>담당자명</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_manager_name" name="mb_giup_manager_name" value="<?php echo get_text($member['mb_giup_manager_name']) ?>"  class="form-control input-sm" size="10">
				</div>
			</div>
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_manager_hp"><b>휴대폰번호</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_manager_hp" name="mb_giup_manager_hp" value="<?php echo get_text($member['mb_giup_manager_hp']) ?>"  class="form-control input-sm" size="10">
				</div>
			</div>
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_manager_tel"><b>전화번호(선택)</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_manager_tel" name="mb_giup_manager_tel" value="<?php echo get_text($member['mb_giup_manager_tel']) ?>"  class="form-control input-sm" size="10">
				</div>
			</div>
			
		</div>
	</div> -->
	
	<div class="panel panel-default">
		<div class="panel-heading">
			<strong>사업자 정보</strong>
			<!--
			<div class="giup">
				<span>사업자정보 입력 ▶</span>
				<label for="mb_giup" class="checkbox-inline">
					<input type="checkbox" name="mb_giup" value="1" id="mb_giup"> 사용
				</label>
			</div>
			-->
		</div>
		<div class="panel-body panel-giup">
			<!--
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_type"><b>회원유형</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<label for="mb_giup_type_1" class="" style="margin-right:10px;">
						<input type="radio" name="mb_giup_type" value="1" id="mb_giup_type_1" <?php echo $member['mb_giup_type'] == '1' ? ' checked ' : ''; ?>> 구매목적
					</label>
					<label for="mb_giup_type_2" class="">
						<input type="radio" name="mb_giup_type" value="2" id="mb_giup_type_2" <?php echo $member['mb_giup_type'] == '2' ? ' checked ' : ''; ?>> 납품판매목적
					</label>
				</div>
			</div>
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_bname"><b>기업명</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_bname" name="mb_giup_bname" value="<?php echo get_text($member['mb_giup_bname']) ?>" class="form-control input-sm" size="10">
				</div>
			</div>
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_btel"><b>연락처</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_btel" name="mb_giup_btel" value="<?php echo get_text($member['mb_giup_btel']) ?>" class="form-control input-sm" size="10" maxlength="13">
				</div>
			</div>
			-->
                
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_bname"><b>복지용구사업소명</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_bname" name="mb_giup_bname" value="<?php echo get_text($member['mb_giup_bname']) ?>" class="form-control input-sm" size="10">
				</div>
			</div>

            <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_boss_name"><b>대표자명</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_boss_name" name="mb_giup_boss_name" value="<?php echo get_text($member['mb_giup_boss_name']) ?>" class="form-control input-sm" size="10">
				</div>
			</div>


            <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_bnum"><b>사업자번호</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
                
                    <label><input type="text" <?php echo $member['mb_giup_bnum'] ? 'readonly' : ''; ?> id="mb_giup_bnum" name="mb_giup_bnum" value="<?php echo get_text($member['mb_giup_bnum']) ?>" class="form-control input-sm" size="13" maxlength="12" ></label>
                    <label><button type="button" id="mb_giup_bnum_check" class="btn btn-black btn-sm" onclick="check_giup_bnum('click');">중복확인</button></label>
                    <div id="form-bnum-feed-text">
                        <span style="display: none; color: #558ED5;" class="available">*사용 가능한 사업자번호 입니다.</span>
                        <span style="display: none; color: #FF0000;" class="unavailable">*사용 불가능한 사업자번호 입니다. <br>종사업자가 있는 경우 <a style="text-decoration: underline; color: #0e5ea8; font-weight: bold" href="javascript:void(0);" onclick="enable_giup_sbnum()">여기</a>를 눌러주세요.</span>
                    </div>
				</div>
			</div>

            <div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_sbnum"><b>종사업장식별번호</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
                    <label><input type="text" id="mb_giup_sbnum" name="mb_giup_sbnum" value="<?php echo get_text($member['mb_giup_sbnum']) ?>" class="form-control input-sm" size="13" maxlength="4"></label>
				</div>
			</div>
            
            <div class="form-group has-feedback">
				<label class="col-sm-2 control-label" for="mb_giup_buptae"><b>업태</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_buptae" name="mb_giup_buptae" value="<?php echo get_text($member['mb_giup_buptae']) ?>" class="form-control input-sm" size="10" >
					<div class="h15 hidden-lg hidden-md hidden-sm"></div>
				</div>
				<label class="col-sm-2 control-label" for="mb_giup_bupjong"><b>업종</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_bupjong" name="mb_giup_bupjong" value="<?php echo get_text($member['mb_giup_bupjong']) ?>" class="form-control input-sm" size="10" >
					<div class="h15 hidden-lg hidden-md hidden-sm"></div>
				</div>
			</div>

			
   

			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_manager_name"><b>담당자명</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_manager_name" name="mb_giup_manager_name" value="<?php echo get_text($member['mb_giup_manager_name']) ?>" <?php echo $required ?> class="form-control input-sm" size="10">
					<span class="fa fa-check form-control-feedback"></span>
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
				<?php }else{ ?>
					<input type="hidden" name="mb_nick_default" value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : ''; ?>">
					<!-- <input type="hidden" name="mb_nick" value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : ''; ?>" id="reg_mb_nick"> -->
				<!--
				<div class="form-group has-feedback text-gap">
					<label class="col-sm-2 control-label" for="reg_mb_nick"><b>닉네임</b><strong class="sound_only">필수</strong></label>
					<div class="col-sm-3">
						<input type="hidden" name="mb_nick_default" value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : ''; ?>">
						<input type="text" name="mb_nick" value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : ''; ?>" id="reg_mb_nick" required <?php echo $readonly ?> class="form-control input-sm nospace" size="10" maxlength="20">
						<span class="fa fa-user form-control-feedback"></span>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-8 text-muted">
						<div id="msg_mb_nick"></div>
						공백없이 한글,영문,숫자만 입력 가능 (한글2자, 영문4자 이상)
					</div>
				</div>
				-->
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

			<?php if ($config['cf_use_tel']) {  ?>
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label" for="reg_mb_tel"><b>전화번호</b><?php if ($config['cf_req_tel']) { ?><strong class="sound_only">필수</strong><?php } ?></label>
					<div class="col-sm-3">
						<!-- <input type="text" name="mb_tel" value="<?php echo get_text($member['mb_tel']) ?>" id="reg_mb_tel" <?php echo $config['cf_req_tel']?"required":""; ?> class="form-control input-sm" maxlength="20"> -->
                        <select name="mb_tel1" id="mb_tel1" class="form-control input-sm"  style="display:inline-block;width:32%;">
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
                        <input type="text" class="form-control input-sm" name="mb_tel2" size="6" id="mb_tel2" title="전화번호(2)" maxlength="4"  value="<?=$mb_giup_btel[1]?>" required style="display:inline-block;width:32%;">
                        <input type="text" class="form-control input-sm" name="mb_tel3" size="6" id="mb_tel3" title="전화번호(3)" maxlength="4"  value="<?=$mb_giup_btel[2]?>" required style="display:inline-block;width:32%;">
                        <span class="fa fa-phone form-control-feedback"></span>
					</div>
				</div>
			<?php }  ?>

			<div class="form-group has-feedback">
				<label class="col-sm-2 control-label" for="reg_mb_fax"><b>팩스번호</b><?php if ($config['cf_reg_fax']) { ?><strong class="sound_only">필수</strong><?php } ?></label>
				<div class="col-sm-3">
					<input type="text" name="mb_fax" value="<?php echo get_text($member['mb_fax']) ?>" id="reg_mb_fax" <?php echo ($config['cf_reg_fax'])?"required":""; ?> class="form-control input-sm" maxlength="13">
					<span class="fa fa-fax form-control-feedback"></span>
				</div>
			</div>

			<!-- <div class="form-group has-feedback">
				<label class="col-sm-2 control-label" for="reg_mb_fax"><b>팩스번호</b></label>
				<div class="col-sm-3">
					<input type="text" name="mb_fax" value="<?php echo get_text($member['mb_fax']) ?>" id="reg_mb_fax" class="form-control input-sm" maxlength="13">
					<span class="fa fa-fax form-control-feedback"></span>
				</div>
			</div> -->
				
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_tax_email"><b>세금계산서 이메일</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_tax_email" name="mb_giup_tax_email" value="<?php echo get_text($member['mb_giup_tax_email']) ?>"  class="form-control input-sm" size="10">
				</div>
			</div>

            <!-- <div id="sbnum" class="form-group has-feedback" style="display: none;">
                <label class="col-sm-2 control-label" for="mb_giup_sbnum"><b>종사업자번호</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-3">
                    <label><input type="text" id="mb_giup_sbnum" name="mb_giup_sbnum" value="<?php echo get_text($member['mb_giup_sbnum']) ?>" class="form-control input-sm" size="13" maxlength="4" <?php echo $member['mb_giup_sbnum'] ? 'readonly' : ''; ?>></label>
                    <label><button type="button" id="mb_giup_sbnum_check" class="btn btn-black btn-sm" onclick="check_giup_sbnum('click');">중복확인</button></label>
                    <input type="text" id="mb_giup_sbnum_explain" name="mb_giup_sbnum_explain" value="<?php echo get_text($member['mb_giup_sbnum_explain']) ?>" class="form-control input-sm" size="10" maxlength="80" placeholder="종사업자 관련 내용을 입력하세요.">
                    <div id="form-sbnum-feed-text">
                        <span style="display: none; color: #558ED5;" class="available">*사용 가능한 종사업자번호 입니다.</span>
                        <span style="display: none; color: #FF0000;" class="unavailable">*사용 불가능한 종사업자번호 입니다.</span>
                    </div>
                </div>
            </div> -->

			<div class="form-group has-feedback">
				<label class="col-sm-2 control-label"><b>주소</b><?php if ($config['cf_req_addr']) { ?><strong class="sound_only">필수</strong><?php }  ?></label>
				<div class="col-sm-8">
                <div class="giup">
                        <label for="mb_address_same" class="checkbox-inline">
                            <input type="checkbox" name="mb_address_same" value="1" id="mb_address_same"> 대표 주소랑 동일
                        </label>
                    </div>
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
					<input type="file" name="mb_giup_file1" class="input-sm " id="mb_giup_file1">
                    <?php if($member['crnFile']){ ?>
                        <img style="max-width:100px; max-height:100px;" src="<?=G5_DATA_URL?>/file/member/license/<?=$member['crnFile']?>" alt="">
                    <?php }?>
				</div>
			</div>
			<div class="form-group has-feedback">
				<label class="col-sm-2 control-label" for="mb_giup_file2"><b>사업자직인 (계약서 날인)</b></label>
				<div class="col-sm-8 mb_giup_file2">
					<input type="file" name="mb_giup_file2" class="input-sm" id="mb_giup_file2">
                    <?php if($member['sealFile']){ ?>
                    <img style="max-width:100px; max-height:100px;" src="<?=G5_DATA_URL?>/file/member/stamp/<?=$member['sealFile']?>" alt="">
                    <?php }?>
				</div>
			</div>

			<div class="form-group has-feedback">
				<label class="col-sm-2 control-label" for="mb_entConAcc01"><b>특약사항1</b></label>
				<div class="col-sm-8">
					<textarea name="mb_entConAcc01" id="mb_entConAcc01" class="form-control input-sm" style="height: 80px;"><?=$member["mb_entConAcc01"]?>본 계약은 국민건강보험 노인장기요양보험 급여상품의 공급계약을 체결함에 목적이 있다.</textarea>
				</div>
			</div>
			
			<div class="form-group has-feedback">
				<label class="col-sm-2 control-label" for="mb_entConAcc02"><b>특약사항2</b></label>
				<div class="col-sm-8">
					<textarea name="mb_entConAcc02" id="mb_entConAcc02" class="form-control input-sm" style="height: 80px;"><?=$member["mb_entConAcc02"]?>본 계약서에 명시되지 아니한 사항이나 의견이 상이할 때에는 상호 협의하에 해결하는 것을 원칙으로 한다.</textarea>
				</div>
			</div>
		</div>
	</div>

	<div class="text-center" style="margin:30px 0px;">
		<button type="button" id="btn_submit" onclick="fregisterform_submit()"class="btn btn-color" accesskey="s"><?php echo $w==''?'회원가입':'정보수정'; ?></button>
		<?php if(!$pim) { ?>
			<a href="<?php echo G5_URL ?>" class="btn btn-black" role="button">취소</a>
		<?php } ?>
	</div>
</form>
<script>

$(document).ready(function () {
                setDateBox();
            });
    //생년월일
    function setDateBox() {
        var dt = new Date();
        var year = "";
        var com_year = dt.getFullYear();
        var selected1 ="";
        var selected2 ="";
        var selected3 ="";
        // 발행 뿌려주기
        $(".year").append("<option value=''>년도</option>");

        // 올해 기준으로 -50년부터 +1년을 보여준다.
        for (var y = (com_year - 100); y <= (com_year); y++) {
        <?php if($w){ ?> if(y == <?=substr($member['mb_birth'], 0, 4)?>){selected1 = 'selected';} <?php } ?>
        $(".year").append("<option value='" + y + "' " + selected1 + ">" + y + "</option>");
        }

        // 월 뿌려주기(1월부터 12월)
        var month;
        $(".month").append("<option value=''>월</option>");
        for (var i = 1; i <= 12; i++) {
        var first_num="";
        if(i<10){first_num = 0;}
        <?php if($w){ ?> if(first_num + i == <?=substr($member['mb_birth'], 4, 2)?>) {selected2 = 'selected';}<?php } ?>
        $(".month").append("<option value='"+first_num + i + "' " + selected2 + ">"+first_num + i+"</option>");
        }

        // 일 뿌려주기(1일부터 31일)
        var day;
        $(".day").append("<option value=''>일</option>");
        for (var i = 1; i <= 31; i++) {
            var first_num="";
        if(i<10){first_num = 0;}
        <?php if($w){ ?> if(first_num + i == <?=substr($member['mb_birth'], 4, 2)?>) {selected3 = 'selected';}<?php } ?>
        $(".day").append("<option value='" +first_num+ i + "'" + selected3 + ">" + first_num+i + "</option>");
        }

    }
            
$(function() {

	$('#mb_giup').click(function() {
		$('.panel-giup').toggle();
	})
	$("#reg_zip_find").css("display", "inline-block");

	<?php if ( $member['mb_giup_type'] > 0 ) { ?>
		$('#mb_giup').click();
	<?php } ?>

	$('#mb_address_same').click(function() {
		if (!$('#reg_mb_zip').val()) {
			alert('사업자 정보 주소를 입력해주세요.');
			$('#mb_address_same').prop("checked", false);
			return false;
		}
        $('#mb_giup_zip').val($('#reg_mb_zip').val());
		$('#mb_giup_addr1').val($('#reg_mb_addr1').val());
		$('#mb_giup_addr2').val($('#reg_mb_addr2').val());
		$('#mb_giup_addr3').val($('#reg_mb_addr3').val());
		$('#mb_giup_addr_jibeon').val($('#mb_addr_jibeon').val());

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


    $('#add_manager').on("click", function() {

		var el = $('#manager_list_body');

		var str = '<tr>';
		str +=      '<td>';
		str +=          '<input type="text" name="mm_name[]" value="" class="frm_input" size="30" maxlength="20" style="width:100%">';
		str +=      '</td>';
		str +=      '<td>';
        str +=          '<input type="text" name="mm_part[]" value="" class="frm_input" size="10" maxlength="20">';
        str +=      '</td>';
        str +=      '<td>';
        str +=          '<input type="text" name="mm_rank[]" value="" class="frm_input" size="10" maxlength="20">';
        str +=      '</td>';
        str +=      '<td>';
        str +=          '<input type="text" name="mm_work[]" value="" class="frm_input" size="10" maxlength="20">';
        str +=      '</td>';
		str +=      '<td>';
        str +=          '<input type="text" name="mm_hp[]" value="" class="frm_input" size="30" maxlength="20">';
        str +=      '</td>';
        str +=      '<td>';
        str +=          '<input type="text" name="mm_hp_extension[]" value="" class="frm_input" size="10" maxlength="10">';
        str +=      '</td>';
		str +=      '<td>';
		str +=          '<input type="text" name="mm_tel[]" value="" class="frm_input" size="30" maxlength="20">';
		str +=      '</td>';
		str +=      '<td>';
        //str +=          '<input type="text" name="mm_thezone[]" value="" class="frm_input" size="30" maxlength="20">';
        str +=          '<input type="text" name="mm_email[]" value="" class="frm_input" size="30" maxlength="50">';
        str +=      '</td>';
		str +=      '<td style="text-align:center;">';
		str +=      '<button type="button" class="btn-black btn delete_manager">삭제</button>';
		str +=      '</td>';
		str +=     '</tr>';

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
});



// submit 최종 폼체크
function fregisterform_submit()
{   
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
    
    if (f.mb_password.value.length < 8 || f.mb_password.value.length > 12) {
        alert("영문/숫자를 반드시 포함한 8자리 이상 12자리 이하로 입력해 주세요.");
        f.mb_password.focus();
        return false;
    }

    if (f.mb_password_re.value.length < 3 || f.mb_password_re.value.length > 12) {
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
        alert("영문,숫자를 혼합하여 입력해주세요.");
        return false;
    }

    if (f.mb_name.value.length < 1) {
        alert("이름을 입력하십시오.");
        f.mb_name.focus();
        return false;
    }

    if (!f.mb_sex.value) {
        alert("성별을 선택해주세요");
        f.mb_sex.focus();
        return false;
    }
    
    var mb_birth = $("#year").val() + $("#month").val() + $("#day").val();
    var mb_tel = $("#mb_tel1").val() + "-" + $("#mb_tel2").val() + "-" + $("#mb_tel3").val();

    if(!$("#year").val()){
        alert('연도를 선택해주세요');
        $("#year").focus();
        return false;
    }
    if(!$("#month").val()){
        alert('월 선택해주세요');
        $("#month").focus();
        return false;
    }
    if(!$("#day").val()){
        alert('일을 선택해주세요');
        $("#day").focus();
        return false;
    }
    var msg = reg_mb_email_check();
    if (msg) {
        alert(msg);
        f.reg_mb_email.select();
        return false;
    }

	var msg = reg_mb_hp_check();
	if (msg) {
		alert(msg);
		f.reg_mb_hp.select();
		return false;
	}
    if(!f.mb_zip.value){
        alert('우편번호를 입력하세요');
        f.mb_zip.focus();
        return false;
    }
    if(!f.mb_addr1.value){
        alert('주소를 입력하세요');
        f.mb_addr1.focus();
        return false;
    }
    if(!f.mb_addr2.value&&!f.mb_addr3.value){
        alert('주소상세를 입력하세요');
        f.mb_addr2.focus();
        return false;
    }

    if(!f.mb_giup_bname.value){
        alert('복지용수사업소명을 입력하세요.');
        f.mb_giup_bname.focus();
        return false;
    }

    if(!f.mb_giup_boss_name.value){
        alert('대표자명을 입력하세요.');
        f.mb_giup_boss_name.focus();
        return false;
    }

    if(!f.mb_giup_bnum.value){
        alert('사업자 번호를 입력하세요.');
        f.mb_giup_bnum.focus();
        return false;
    }

    if(!f.mb_giup_buptae.value){
        alert('업태를 입력하세요.');
        f.mb_giup_buptae.focus();
        return false;
    }

    if(!f.mb_giup_bupjong.value){
        alert('업종을 입력하세요.');
        f.mb_giup_bupjong.focus();
        return false;
    }

    if(!f.mb_giup_manager_name.value){
        alert('담당자명을 입력하세요.');
        f.mb_giup_manager_name.focus();
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


    if(!f.mb_giup_zip.value){
        alert('우편번호를 입력하세요');
        f.mb_giup_zip.focus();
        return false;
    }
    if(!f.mb_giup_addr1.value){
        alert('주소를 입력하세요');
        f.mb_giup_addr1.focus();
        return false;
    }
    if(!f.mb_giup_addr2.value&&!f.mb_giup_addr3.value){
        alert('주소상세를 입력하세요');
        f.mb_giup_addr2.focus();
        return false;
    }
    //체크 끝


    //통신
    var sendData = new FormData();
    var sendData2 = new FormData();
    <?php if($w){ ?>
    sendData.append("entId", "<?=$member['mb_entId']?>");//아이디
    <?php } ?>
    sendData.append("usrId", $("#reg_mb_id").val());//아이디
    sendData.append("usrPw", $("#reg_mb_password").val());//비밀번호
    sendData.append("usrNm", $("#mb_name").val()); //관리자이름
    sendData.append("usrBirth", mb_birth);//생년월일
    sendData.append("usrMail", $("#reg_mb_email").val());//메일
    sendData.append("usrGender", $("#mb_sex").val());//성별
    sendData.append("usrPnum", $("#reg_mb_hp").val());//관리자 휴대폰번호
    sendData.append("usrZip", $("#reg_mb_zip").val()); //관리자 우편번호
    sendData.append("usrAddr", $("#reg_mb_addr1").val());//관리자 주소
    sendData.append("usrAddrDetail", $("#reg_mb_addr2").val())+$("#reg_mb_addr3").val();//관리자 주소 상세
    sendData.append("entNm", $("#mb_giup_bname").val()); //사업체명
    sendData.append("entCeoNm", $("#mb_giup_boss_name").val()); //사업소 대표
    sendData.append("entCrn", $("#mb_giup_bnum").val()); //사업자 등록번호
    sendData.append("entMail", $("#mb_giup_tax_email").val());//메일
    sendData.append("entPnum", mb_tel); //사업소 전화번호
    sendData.append("entFax", $("#reg_mb_fax").val()); //사업소 팩스
    sendData.append("entZip", $("#mb_giup_zip").val());  //사업소 우편번호
    sendData.append("entAddr", $("#mb_giup_addr1").val()); //사업소 주소
    sendData.append("entAddrDetail",$("#mb_giup_addr2").val() + $("#mb_giup_addr3").val() ); //사업소 주소 상세
    sendData.append("entBusiType",$("#mb_giup_bupjong").val()); //사업소 업종
    sendData.append("entBusiCondition",$("#mb_giup_buptae").val()); //사업소 업태
    sendData.append("entBusiNum",$("#mb_giup_sbnum").val()); //종사업장번호
    sendData.append("entTaxCharger",$("#mb_giup_manager_name").val()); //담당자
    sendData.append("entConAcco1",$("#mb_entConAcc01").val()); //담당자
    sendData.append("entConAcco2",$("#mb_entConAcc02").val()); //담당자
    //직인파일
    var imgFileItem2 = $(".mb_giup_file2 input[type='file']");
    for(var i = 0; i < imgFileItem2.length; i++){
        sendData.append("sealFile", $(imgFileItem2[i])[0].files[0]);
        sendData2.append("sealFile", $(imgFileItem2[i])[0].files[0]);
    }
    <?php if(!$w){
            $api_url = "https://system.eroumcare.com:9901/api/ent/insert";
        }else{
            $api_url = "https://system.eroumcare.com:9901/api/ent/update";
        } 
    ?>
        $.ajax({
                type: 'POST',
                url : "<?=$api_url?>",
                type : "POST",
                async : false,
                cache : false,
                processData : false,
                contentType : false,
                data : sendData,
            }).done(function (data) {

                if(data.message == "SUCCESS"){
                    //사업자등록증
                    var imgFileItem1 = $(".mb_giup_file1 input[type='file']");
                    for(var i = 0; i < imgFileItem1.length; i++){
                        sendData.append("crnFile", $(imgFileItem1[i])[0].files[0]);
                    }

                    $.ajax({
                        type: 'POST',
                        url : "<?=G5_BBS_URL?>/ajax.account.php",
                        type : "POST",
                        async : false,
                        cache : false,
                        processData : false,
                        contentType : false,
                        data : sendData,
					    success : function(result){
                            if(result =="N"){
                                alert('파일을 확인하세요');
                                return flase;
                            }else{
                                result = JSON.parse(result);
                            }
                            sendData2.append("entId", result.data['entId']); //entId
                            //이전 서버에 저장
                            if(data.message == "SUCCESS"){

                                $.ajax({
                                    type: 'POST',
                                    url : "https://ex.eroumcare.com:9001/api/ent/update",
                                    type : "POST",
                                    async : false,
                                    cache : false,
                                    processData : false,
                                    contentType : false,
                                    data : sendData2,
                                }).done(function (data) {
                                    alert("완료되었습니다.");
									<?php if(!$w){ ?>
										location.href='<?=G5_URL?>/bbs/register_result.php';
										<?php }else{ ?>
										location.href='<?=G5_URL?>';
									<?php } ?>
                                });
                            }else{
                                alert(data);
                                return false;
                            }
                        }
                    });
                }else{
                    alert(data.message);
                    return false;
                }
        });
        return false;
























        
	// 기업정보 사용시 기업 관련 필드 모두 필수 처리
	if($('#mb_giup').is(":checked") == true) {
		if (!$('input[name="mb_giup_type"]:checked').length) {
			alert("회원유형을 선택하십시오.");
			return false;
		}
		if (!$('#mb_giup_bname').val()) {
			alert("기업명을 입력하십시오.");
			return false;
		}
		if (!$('#mb_giup_boss_name').val()) {
			alert("대표자명을 입력하십시오.");
			return false;
		}
		if (!$('#mb_giup_btel').val()) {
			alert("기업정보 - 연락처를 입력하십시오.");
			return false;
		}
		if (!$('#mb_giup_bnum').val()) {
			alert("사업자번호를 입력하십시오.");
			return false;
		}

        if ($('#fregisterform input[name=w]').val() == "") {
            if (!$('#mb_giup_sbnum').val() && $('#mb_giup_bnum').val()) { // 종사업자 없다면 사업자 중복 체크
                if (!check_giup_bnum()) {
                    return false;
                }
            }

            if ($('#mb_giup_sbnum').val() && $('#mb_giup_bnum').val()) { // 종사업자 있다면 사업자 중복 우회
                if (!check_giup_sbnum()) {
                    return false;
                }
            }
        }

        if ($('#mb_giup_sbnum').val() && $('#mb_giup_bnum').val()) { // 종사업자 있다면
            if (!$('#mb_giup_sbnum_explain').val()) {
                alert("종사업자 관련 내용을 입력하세요.");
                return false;
            }
        }
		
		if (!$('#mb_giup_buptae').val()) {
			alert("업태를 입력하십시오.");
			return false;
		}
		if (!$('#mb_giup_bupjong').val()) {
			alert("업종을 입력하십시오.");
			return false;
		}
		if (!$('#mb_giup_zip').val()) {
			alert("주소를 입력하십시오.");
			return false;
		}
		if (!$('#mb_giup_tax_email').val()) {
			alert("세금계산서 이메일을 입력하십시오.");
			return false;
		}
		//if (!$('#mm_name_0').val()) {
		//	alert("담당자 이름을 입력하십시오.");
		//	return false;
		//}
		//if (!$('#mm_tel_0').val()) {
		//	alert("담당자 연락처를 입력하십시오.");
		//	return false;
		//}
	}
	//  else {
	// 	if (confirm("세금계산서 발급을 원하시면 기업정보를 입력해주세요.\r\n입력하시겠습니까? ")) {
	// 		$('#mb_giup').click();
	// 		return false;
	// 	}
	// }

	//console.log('ok');
	//return false;

	// 회원아이디 검사
	if (f.w.value == "") {
		var msg = reg_mb_id_check();
		if (msg) {
			alert(msg);
			f.mb_id.select();
			return false;
		}
	}






    
	// 이름 검사
	if (f.w.value=="") {
		if (f.mb_name.value.length < 1) {
			alert("이름을 입력하십시오.");
			f.mb_name.focus();
			return false;
		}

		/*
		var pattern = /([^가-힣\x20])/i;
		if (pattern.test(f.mb_name.value)) {
			alert("이름은 한글로 입력하십시오.");
			f.mb_name.select();
			return false;
		}
		*/
	}

	<?php if($w == '' && $config['cf_cert_use'] && $config['cf_cert_req']) { ?>
	// 본인확인 체크
	if(f.cert_no.value=="") {
		alert("회원가입을 위해서는 본인확인을 해주셔야 합니다.");
		return false;
	}
	<?php } ?>

	// 닉네임 검사
	/*
	if ((f.w.value == "") || (f.w.value == "u" && f.mb_nick.defaultValue != f.mb_nick.value)) {
		var msg = reg_mb_nick_check();
		if (msg) {
			alert(msg);
			f.reg_mb_nick.select();
			return false;
		}
	}
	*/

	// E-mail 검사
	if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
		var msg = reg_mb_email_check();
		if (msg) {
			alert(msg);
			f.reg_mb_email.select();
			return false;
		}
	}

	<?php if (($config['cf_use_hp'] || $config['cf_cert_hp']) && $config['cf_req_hp']) {  ?>
	// 휴대폰번호 체크
	var msg = reg_mb_hp_check();
	if (msg) {
		alert(msg);
		f.reg_mb_hp.select();
		return false;
	}
	<?php } ?>

	if (typeof f.mb_icon != "undefined") {
		if (f.mb_icon.value) {
			if (!f.mb_icon.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
				alert("회원아이콘이 이미지 파일이 아닙니다.");
				f.mb_icon.focus();
				return false;
			}
		}
	}

	if (typeof f.mb_img != "undefined") {
		if (f.mb_img.value) {
			if (!f.mb_img.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
				alert("회원이미지가 이미지 파일이 아닙니다.");
				f.mb_img.focus();
				return false;
			}
		}
	}

	if (typeof(f.mb_recommend) != "undefined" && f.mb_recommend.value) {
		if (f.mb_id.value == f.mb_recommend.value) {
			alert("본인을 추천할 수 없습니다.");
			f.mb_recommend.focus();
			return false;
		}

		var msg = reg_mb_recommend_check();
		if (msg) {
			alert(msg);
			f.mb_recommend.select();
			return false;
		}
	}

	<?php //echo chk_captcha_js();  ?>

	// document.getElementById("btn_submit").disabled = "disabled";

}

function check_giup_bnum(type) {
    var msg = reg_mb_giup_bnum_check();

    if (type == "click") {
        if (msg === "") {
            $('#form-bnum-feed-text > span').hide();
            $('#form-bnum-feed-text > .available').show();
            return true;
        }
        
        if (msg === "이미 사용중인 사업자번호입니다.") {
            $('#form-bnum-feed-text > span').hide();
            $('#form-bnum-feed-text > .unavailable').show();
            return false;
        }
        
        if (msg === "사업자번호를 올바르게 입력해 주십시오.") {
            $('#form-bnum-feed-text > span').hide();
            alert("사업자번호를 올바르게 입력해 주십시오.")
            return false;
        }
        
        if (msg === "사업자번호를 입력해 주십시오.") {
            $('#form-bnum-feed-text > span').hide();
            alert("사업자번호를 입력해 주십시오.")
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
})

function chkPW(pw){
    var pw = pw;
    var num = pw.search(/[0-9]/g);
    var eng = pw.search(/[a-z]/ig);
    if(pw.length < 8 || pw.length > 12){
    alert("8자리 ~ 20자리 이내로 입력해주세요.");
    return false;
    }else if(pw.search(/\s/) != -1){
    alert("비밀번호는 공백 없이 입력해주세요.");
    return false;
    }else if(num < 0 || eng < 0 ){
    alert("영문,숫자를 혼합하여 입력해주세요.");
    return false;
    }else {
    console.log("통과"); 
    return true;
    }

}


<?php } ?>
</script>
