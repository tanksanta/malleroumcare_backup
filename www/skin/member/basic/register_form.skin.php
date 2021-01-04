<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

if($header_skin)
	include_once('./header.php');

add_javascript(G5_POSTCODE_JS, 0);
?>
<!--<script src="http://dmaps.daum.net/map_js_init/postcode.v2.js"></script>-->
<!--<script src="https://spi.maps.daum.net/imap/map_js_init/postcode.v2.js"></script>-->
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
<?php if($config['cf_cert_use'] && ($config['cf_cert_ipin'] || $config['cf_cert_hp'])) { ?>
    <script src="<?php echo G5_JS_URL ?>/certify.js?v=<?php echo APMS_SVER; ?>"></script>
<?php } ?>

<form class="form-horizontal register-form" role="form" id="fregisterform" name="fregisterform" action="<?php echo $action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
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

	<div class="panel panel-default">
		<div class="panel-heading"><strong><i class="fa fa-star fa-lg"></i> 사이트 이용정보 입력</strong></div>
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
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading"><strong><i class="fa fa-user fa-lg"></i> 개인정보 입력</strong></div>
		<div class="panel-body">

			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="reg_mb_name"><b>이름</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="reg_mb_name" name="mb_name" value="<?php echo get_text($member['mb_name']) ?>" <?php echo $required ?> <?php echo $readonly; ?> class="form-control input-sm" size="10">
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
					<input type="hidden" name="mb_nick" value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : ''; ?>" id="reg_mb_nick">
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

			<div class="form-group has-feedback<?php echo ($config['cf_use_email_certify']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="reg_mb_email"><b>E-mail</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-5">
					<input type="hidden" name="old_email" value="<?php echo $member['mb_email'] ?>">
					<input type="text" name="mb_email" value="<?php echo isset($member['mb_email'])?$member['mb_email']:''; ?>" id="reg_mb_email" required class="form-control input-sm email" size="70" maxlength="100">
					<span class="fa fa-envelope form-control-feedback"></span>
				</div>
			</div>
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
						<input type="text" name="mb_tel" value="<?php echo get_text($member['mb_tel']) ?>" id="reg_mb_tel" <?php echo $config['cf_req_tel']?"required":""; ?> class="form-control input-sm" maxlength="20">
						<span class="fa fa-phone form-control-feedback"></span>
					</div>
				</div>
			<?php }  ?>

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

			<!-- <div class="form-group has-feedback">
				<label class="col-sm-2 control-label" for="reg_mb_fax"><b>팩스번호</b></label>
				<div class="col-sm-3">
					<input type="text" name="mb_fax" value="<?php echo get_text($member['mb_fax']) ?>" id="reg_mb_fax" class="form-control input-sm" maxlength="13">
					<span class="fa fa-fax form-control-feedback"></span>
				</div>
			</div> -->

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

	<div class="panel panel-default">
		<div class="panel-heading"><strong><i class="fa fa-cog fa-lg"></i> 기타 개인설정</strong></div>
		<div class="panel-body">

			<?php if ($config['cf_use_signature']) {  ?>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="reg_mb_signature"><b>서명</b><?php if ($config['cf_req_signature']){ ?><strong class="sound_only">필수</strong><?php } ?></label>
					<div class="col-sm-8">
						<textarea name="mb_signature" rows=5 id="reg_mb_signature" <?php echo $config['cf_req_signature']?"required":""; ?> class="form-control input-sm"><?php echo $member['mb_signature'] ?></textarea>
					</div>
				</div>
			<?php }  ?>

			<?php if ($config['cf_use_profile']) {  ?>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="reg_mb_profile"><b>자기소개</b><?php if ($config['cf_req_profile']){ ?><strong class="sound_only">필수</strong><?php } ?></label>
					<div class="col-sm-8">
						<textarea name="mb_profile" rows=5 id="reg_mb_profile" <?php echo $config['cf_req_profile']?"required":""; ?> class="form-control input-sm"><?php echo $member['mb_profile'] ?></textarea>
					</div>
				</div>
			<?php }  ?>

			<?php if ($config['cf_use_member_icon'] && $member['mb_level'] >= $config['cf_icon_level']) {  ?>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="reg_mb_profile"><b>회원아이콘</b></label>
					<div class="col-sm-8">
						<input type="file" name="mb_icon" id="reg_mb_icon">
						<?php if ($w == 'u' && file_exists($mb_icon_path)) {  ?>
							<label for="del_mb_icon" class="checkbox-inline">
								<img src="<?php echo $mb_icon_url ?>" alt="회원아이콘">
								<input type="checkbox" name="del_mb_icon" value="1" id="del_mb_icon"> 삭제
							</label>
						<?php }  ?>
						<span class="help-block">
							아이콘 크기는 가로 <?php echo $config['cf_member_icon_width'] ?>픽셀, 세로 <?php echo $config['cf_member_icon_height'] ?>픽셀 이하로 해주세요.
							gif, jpg, png만 가능하며 용량 <?php echo number_format($config['cf_member_icon_size']) ?>바이트 이하만 등록됩니다.
						</span>
					</div>
				</div>
			<?php }  ?>

            <?php if ($member['mb_level'] >= $config['cf_icon_level'] && $config['cf_member_img_size'] && $config['cf_member_img_width'] && $config['cf_member_img_height']) {  ?>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="reg_mb_img"><b>회원이미지</b></label>
					<div class="col-sm-8">
		                <input type="file" name="mb_img" id="reg_mb_img" >
						<?php if ($w == 'u' && file_exists($mb_img_path)) {  ?>
						<img src="<?php echo $mb_img_url ?>" alt="회원이미지">
						<input type="checkbox" name="del_mb_img" value="1" id="del_mb_img">
						<label for="del_mb_img">삭제</label>
						<?php }  ?>
						<span class="help-block">
		                    이미지 크기는 가로 <?php echo $config['cf_member_img_width'] ?>픽셀, 세로 <?php echo $config['cf_member_img_height'] ?>픽셀 이하로 해주세요.
				            gif, jpg, png파일만 가능하며 용량 <?php echo number_format($config['cf_member_img_size']) ?>바이트 이하만 등록됩니다.
						</span>
					</div>
				</div>
			<?php }  ?>

			<?php
			//회원정보 수정인 경우 소셜 계정 출력
			if($config['cf_social_login_use'] && $w == 'u' && function_exists('social_member_provider_manage') ){
				social_member_provider_manage();
			}
			?>

			<div class="form-group">
				<label class="col-sm-2 control-label" for="reg_mb_mailling"><b>메일링서비스</b></label>
				<div class="col-sm-8">
					<label class="checkbox-inline">
						<input type="checkbox" name="mb_mailling" value="1" id="reg_mb_mailling" <?php echo ($w=='' || $member['mb_mailling'])?'checked':''; ?>>
						정보 메일을 받겠습니다.
					</label>
				</div>
			</div>

			<?php if ($config['cf_use_hp']) {  ?>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="reg_mb_sms"><b>SMS 수신여부</b></label>
					<div class="col-sm-8">
						<label class="checkbox-inline">
							<input type="checkbox" name="mb_sms" value="1" id="reg_mb_sms" <?php echo ($w=='' || $member['mb_sms'])?'checked':''; ?>>
							휴대폰 문자메세지를 받겠습니다.
						</label>
					</div>
				</div>
			<?php }  ?>

			<?php if (isset($member['mb_open_date']) && $member['mb_open_date'] <= date("Y-m-d", G5_SERVER_TIME - ($config['cf_open_modify'] * 86400)) || empty($member['mb_open_date'])) { // 정보공개 수정일이 지났다면 수정가능  ?>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="reg_mb_open"><b>정보공개</b></label>
					<div class="col-sm-8">
						<label class="checkbox-inline">
							<input type="hidden" name="mb_open_default" value="<?php echo $member['mb_open'] ?>">
							<input type="checkbox" name="mb_open" value="1" <?php echo ($w=='' || $member['mb_open'])?'checked':''; ?> id="reg_mb_open">
							다른분들이 나의 정보를 볼 수 있도록 합니다.
						</label>
						<span class="help-block">
							정보공개를 바꾸시면 앞으로 <?php echo (int)$config['cf_open_modify'] ?>일 이내에는 변경이 안됩니다.
						</span>
					</div>
				</div>
			<?php } else {  ?>
				<div class="form-group">
					<label class="col-sm-2 control-label"><b>정보공개</b></label>
					<div class="col-sm-8">
						<span class="help-block">
							정보공개는 수정후 <?php echo (int)$config['cf_open_modify'] ?>일 이내, <?php echo date("Y년 m월 j일", isset($member['mb_open_date']) ? strtotime("{$member['mb_open_date']} 00:00:00")+$config['cf_open_modify']*86400:G5_SERVER_TIME+$config['cf_open_modify']*86400); ?> 까지는 변경이 안됩니다.<br>
							이렇게 하는 이유는 잦은 정보공개 수정으로 인하여 쪽지를 보낸 후 받지 않는 경우를 막기 위해서 입니다.
						</span>
						<input type="hidden" name="mb_open" value="<?php echo $member['mb_open'] ?>">
					</div>
				</div>
			<?php } ?>

			<?php if ($w == "" && $config['cf_use_recommend']) {  ?>
				<div class="form-group has-feedback">
					<label class="col-sm-2 control-label" for="reg_mb_recommend"><b>추천인아이디</b></label>
					<div class="col-sm-3">
						<input type="text" name="mb_recommend" id="reg_mb_recommend" class="form-control input-sm">
						<span class="fa fa-user form-control-feedback"></span>
					</div>
				</div>
			<?php }  ?>

			<div class="form-group">
				<label class="col-sm-2 control-label"><b>자동등록방지</b></label>
				<div class="col-sm-8">
					<?php echo captcha_html(); ?>
				</div>
			</div>
		</div>
	</div>
	
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
			float:right;position:absolute;top:13px;right:10px;
		}
		.register-form .panel .panel-heading .giup span{
			margin-right: 15px;font-size:14px;color:red;font-weight:bold;
		}
		.register-form .panel .panel-heading .giup label.checkbox-inline {
			margin-top: -10px;
		}
		.register-form .panel .panel-body.panel-giup {
			display:none;
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
	</style>

	<div class="panel panel-default">
		<div class="panel-heading">
			<strong><i class="fa fa-briefcase"></i> 기업정보 입력 </strong> <span class="txt_point_red">세금계산서 발급을 원하시면 기업정보를 입력해주세요.</span> 
			<div class="giup">
				<span>사업자정보 입력 ▶</span>
				<label for="mb_giup" class="checkbox-inline">
					<input type="checkbox" name="mb_giup" value="1" id="mb_giup"> 사용
				</label>
			</div>
		</div>
		<div class="panel-body panel-giup">

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
				<label class="col-sm-2 control-label" for="mb_giup_boss_name"><b>대표자명</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_boss_name" name="mb_giup_boss_name" value="<?php echo get_text($member['mb_giup_boss_name']) ?>" class="form-control input-sm" size="10">
				</div>
			</div>
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_btel"><b>연락처</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_btel" name="mb_giup_btel" value="<?php echo get_text($member['mb_giup_btel']) ?>" class="form-control input-sm" size="10" maxlength="13">
				</div>
			</div>
   
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_bnum"><b>사업자번호</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
                    <label><input type="text" id="mb_giup_bnum" name="mb_giup_bnum" value="<?php echo get_text($member['mb_giup_bnum']) ?>" class="form-control input-sm" size="13" maxlength="12" <?php echo $member['mb_giup_bnum'] ? 'readonly' : ''; ?>></label>
                    <label><button type="button" id="mb_giup_bnum_check" class="btn btn-black btn-sm" onclick="check_giup_bnum('click');">중복확인</button></label>
                    <div id="form-bnum-feed-text">
                        <span style="display: none; color: #558ED5;" class="available">*사용 가능한 사업자번호 입니다.</span>
                        <span style="display: none; color: #FF0000;" class="unavailable">*사용 불가능한 사업자번호 입니다. <br>종사업자가 있는 경우 <a style="text-decoration: underline; color: #0e5ea8; font-weight: bold" href="javascript:void(0);" onclick="enable_giup_sbnum()">여기</a>를 눌러주세요.</span>
                    </div>
				</div>
			</div>

            <div id="sbnum" class="form-group has-feedback" style="display: none;">
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

			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_tax_email"><b>세금계산서 이메일</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_tax_email" name="mb_giup_tax_email" value="<?php echo get_text($member['mb_giup_tax_email']) ?>"  class="form-control input-sm" size="10">
				</div>
			</div>
			<!--
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_manager_name"><b>담당자명</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_manager_name" name="mb_giup_manager_name" value="<?php echo get_text($member['mb_giup_manager_name']) ?>"  class="form-control input-sm" size="10">
				</div>
			</div>
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label" for="mb_giup_manager_tel"><b>담당자 연락처</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-3">
					<input type="text" id="mb_giup_manager_tel" name="mb_giup_manager_tel" value="<?php echo get_text($member['mb_giup_manager_tel']) ?>"  class="form-control input-sm" size="10">
				</div>
			</div>
			-->
			<div class="form-group has-feedback<?php echo ($config['cf_cert_use']) ? ' text-gap' : '';?>">
				<label class="col-sm-2 control-label"><b>담당자</b><strong class="sound_only">필수</strong></label>
				<div class="col-sm-8" style="overflow-x:scroll;">


					<style>
					.manager_list {
						padding:0;
						width:90%;
					}
					.manager_list th {
						text-align:center;
					}
					.manager_list #mm_name {
						width:50%;
					}
					.manager_list #mm_tel {
						width:20%;
					}
					.manager_list #mm_thezone {
						width:20%;
					}
					.manager_list #mm_del {
						width:10%;
					}
					</style>
					<div class="tbl_head02 tbl_wrap manager_list">
						<button type="button" id="add_manager" class="btn_submit btn">담당자 추가</button><br/><br/>
						<table>
							<caption>담당자 목록</caption>
							<thead>
								<tr>
									<th scope="col" id="mm_name" >이름</th>
									<th scope="col" id="mm_part" >부서명</th>
									<th scope="col" id="mm_rank" >직급</th>
									<th scope="col" id="mm_work" >업무</th>
									<th scope="col" id="mm_hp" >전화번호</th>
									<th scope="col" id="mm_hp_extension" >내선번호</th>
									<th scope="col" id="mm_tel" >핸드폰</th>
									<!--<th scope="col" id="mm_thezone" >더존코드</th>-->
									<th scope="col" id="mm_email" >이메일</th>
									<th scope="col" id="mm_del" >삭제여부</th>
								</tr>
							</thead>
							<tbody id="manager_list_body">
								<?php
								$sql = "SELECT * FROM g5_member_giup_manager WHERE mb_id = '{$member['mb_id']}'";
								$result = sql_query($sql);
								$managers = array();
								while( $m_row = sql_fetch_array($result) ) {
									$managers[] = $m_row;
								}
								if (!count($managers)) {
									array_push($managers, array());
								}
								//foreach($manages as $manager) {
								for($m=0;$m<count($managers); $m++) {
								?>
								<tr>
									<td>
										<input type="hidden" name="mm_no[]" value="<?php echo $managers[$m]['mm_no'] ?>" />
										<input type="text" name="mm_name[]" value="<?php echo $managers[$m]['mm_name'] ?>" id="mm_name_<?php echo $m; ?>" class="frm_input" size="30" maxlength="20">
									</td>
									<td>
										<input type="text" name="mm_part[]" value="<?php echo $managers[$m]['mm_part'] ?>" id="mm_part_<?php echo $m; ?>" class="frm_input" size="10" maxlength="20">
									</td>
									<td>
										<input type="text" name="mm_rank[]" value="<?php echo $managers[$m]['mm_rank'] ?>" id="mm_rank_<?php echo $m; ?>" class="frm_input" size="10" maxlength="20">
									</td>
									<td>
										<input type="text" name="mm_work[]" value="<?php echo $managers[$m]['mm_work'] ?>" id="mm_work_<?php echo $m; ?>" class="frm_input" size="10" maxlength="20">
									</td>
									<td>
										<input type="text" name="mm_hp[]" value="<?php echo $managers[$m]['mm_hp'] ?>" id="mm_hp_<?php echo $m; ?>" class="frm_input" size="30" maxlength="20">
									</td>
									<td>
										<input type="text" name="mm_hp_extension[]" value="<?php echo $managers[$m]['mm_hp_extension'] ?>" id="mm_hp_extension_<?php echo $m; ?>" class="frm_input" size="10" maxlength="10">
									</td>
									<td>
										<input type="text" name="mm_tel[]" value="<?php echo $managers[$m]['mm_tel'] ?>" id="mm_tel_<?php echo $m; ?>" class="frm_input" size="30" maxlength="20">
									</td>
									<td>
										<input type="text" name="mm_email[]" value="<?php echo $managers[$m]['mm_email'] ?>" id="mm_email_<?php echo $m; ?>" class="frm_input" size="30" maxlength="50">
									</td>
									<td style="text-align:center;">
										<?php if ( $m == 0 ) { ?>
										필수
										<?php }else{ ?>
											<button type="button" class="btn-black btn delete_manager">삭제</button>
										<?php } ?>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>

				</div>
			</div>
		</div>
	</div>


	<div class="text-center" style="margin:30px 0px;">
		<button type="submit" id="btn_submit" class="btn btn-color" accesskey="s"><?php echo $w==''?'회원가입':'정보수정'; ?></button>
		<?php if(!$pim) { ?>
			<a href="<?php echo G5_URL ?>" class="btn btn-black" role="button">취소</a>
		<?php } ?>
	</div>

</form>

<script>
$(function() {

	$('#mb_giup').click(function() {
		$('.panel-giup').toggle();
	})
	$("#reg_zip_find").css("display", "inline-block");

	<?php if ( $member['mb_giup_type'] > 0 ) { ?>
		$('#mb_giup').click();
	<?php } ?>

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
function fregisterform_submit(f)
{
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
	} else {
		if (confirm("세금계산서 발급을 원하시면 기업정보를 입력해주세요.\r\n입력하시겠습니까? ")) {
			$('#mb_giup').click();
			return false;
		}
	}

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

	if (f.w.value == "") {
		if (f.mb_password.value.length < 3) {
			alert("비밀번호를 3글자 이상 입력하십시오.");
			f.mb_password.focus();
			return false;
		}
	}

	if (f.mb_password.value != f.mb_password_re.value) {
		alert("비밀번호가 같지 않습니다.");
		f.mb_password_re.focus();
		return false;
	}

	if (f.mb_password.value.length > 0) {
		if (f.mb_password_re.value.length < 3) {
			alert("비밀번호를 3글자 이상 입력하십시오.");
			f.mb_password_re.focus();
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

	<?php echo chk_captcha_js();  ?>

	document.getElementById("btn_submit").disabled = "disabled";

	return true;
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
<?php } ?>
</script>
