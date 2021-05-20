


<style>
    .alert-info{ background-color:#ffff; text-align:center; color:#5b5b5b; margin-bottom:0px; border-color:#fff;}
    .info_box  { margin-bottom:20px; width:100%;height:100%; font-size:20px;}
    .fregister{ background-color:#fff;}
    .panel-heading{background-color:#fff; }
    /* .input_txt{font-size:13px;} */
    
</style>
<div class="alert alert-info" role="alert">
<div class="info_box"><b>복지용구사업소 회원가입</b></div>
<form  class="fregister" name="fregister" id="fregister" action="<?php echo G5_BBS_URL ?>/register_form.php" onsubmit="return fregister_submit(this);" method="POST" autocomplete="off" class="form" role="form">
<input type="hidden" name="pim" value="<?php echo $pim;?>">
	<div class="panel panel-default">
		<div class="panel-heading"><strong>이용약관</strong></div>
		<div class="panel-body">
            <div class="register-term">
                <textarea readonly class="input_txt"><?php echo get_text($config['cf_stipulation']) ?></textarea>
            </div>
		</div>
		<div class="panel-footer">
            <label class="checkbox-inline"><input type="checkbox" name="agree" value="1" id="agree11">이용약관에 동의합니다.</label>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			<strong>개인정보취급방침</strong>
		</div>
        <div class="panel-body">
            <div class="register-term">
                <textarea readonly class="input_txt"><?php echo get_text($config['cf_privacy']) ?></textarea>
            </div>
		</div>
		<div class="panel-footer">
            <label class="checkbox-inline"><input type="checkbox" name="agree2" value="1" id="agree21" >개인정보취급방침에 동의 합니다.</label>
		</div>
	</div>
    
    <div class="text-center" style="margin-bottom:30px;">
        <label ><input type="checkbox" id="check_all"> 전체 동의 합니다.</label>
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-color">확인</button>
        <button type="button" class="btn btn-color" onclick ="(location.href='<?=G5_URL?>')" >취소</button>
    </div>
</form>

<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

if($header_skin)
	include_once('./header.php');

if($config['cf_social_login_use']) { //소셜 로그인 사용시 

	$social_pop_once = false;

	$self_url = G5_BBS_URL."/login.php";

	//새창을 사용한다면
	if( G5_SOCIAL_USE_POPUP ) {
		$self_url = G5_SOCIAL_LOGIN_URL.'/popup.php';
	}
?>
	<?php if ( THEMA_KEY == 'default' ) { ?>
	<div class="sns-wrap-over" id="sns_register">
		<div class="panel panel-primary">
			<div class="panel-heading"><strong> SNS 계정으로 가입</strong></div>
			<div class="panel-body">
			   <div class="sns-wrap">
					<?php if( social_service_check('naver') ) {     //네이버 로그인을 사용한다면 ?>
					<a href="<?php echo $self_url;?>?provider=naver&amp;url=<?php echo $urlencode;?>" class="sns-icon social_link sns-naver" title="네이버">
						<span class="ico"></span>
						<span class="txt">네이버으로 회원가입하기</span>
					</a>
					<?php }     //end if ?>
					<?php if( social_service_check('kakao') ) {     //카카오 로그인을 사용한다면 ?>
					<a href="<?php echo $self_url;?>?provider=kakao&amp;url=<?php echo $urlencode;?>" class="sns-icon social_link sns-kakao" title="카카오">
						<span class="ico"></span>
						<span class="txt">카카오로 회원가입하기</span>
					</a>
					<?php }     //end if ?>
					<?php if( social_service_check('facebook') ) {     //페이스북 로그인을 사용한다면 ?>
					<a href="<?php echo $self_url;?>?provider=facebook&amp;url=<?php echo $urlencode;?>" class="sns-icon social_link sns-facebook" title="페이스북">
						<span class="ico"></span>
						<span class="txt">페이스북로 회원가입하기</span>
					</a>
					<?php }     //end if ?>
					<?php if( social_service_check('google') ) {     //구글 로그인을 사용한다면 ?>
					<a href="<?php echo $self_url;?>?provider=google&amp;url=<?php echo $urlencode;?>" class="sns-icon social_link sns-google" title="구글">
						<span class="ico"></span>
						<span class="txt">구글+로 회원가입하기</span>
					</a>
					<?php }     //end if ?>
					<?php if( social_service_check('twitter') ) {     //트위터 로그인을 사용한다면 ?>
					<a href="<?php echo $self_url;?>?provider=twitter&amp;url=<?php echo $urlencode;?>" class="sns-icon social_link sns-twitter" title="트위터">
						<span class="ico"></span>
						<span class="txt">트위터로 회원가입하기</span>
					</a>
					<?php }     //end if ?>
					<?php if( social_service_check('payco') ) {     //페이코 로그인을 사용한다면 ?>
					<a href="<?php echo $self_url;?>?provider=payco&amp;url=<?php echo $urlencode;?>" class="sns-icon social_link sns-payco" title="페이코">
						<span class="ico"></span>
						<span class="txt">페이코로 회원가입하기</span>
					</a>
					<?php }     //end if ?>

					<?php if( G5_SOCIAL_USE_POPUP && !$social_pop_once ){
					$social_pop_once = true;
					?>
					<script>
						jQuery(function($){
							$(".sns-wrap").on("click", "a.social_link", function(e){
								e.preventDefault();

								var pop_url = $(this).attr("href");
								var newWin = window.open(
									pop_url, 
									"social_sing_on", 
									"location=0,status=0,scrollbars=1,width=600,height=500"
								);

								if(!newWin || newWin.closed || typeof newWin.closed=='undefined')
									 alert('브라우저에서 팝업이 차단되어 있습니다. 팝업 활성화 후 다시 시도해 주세요.');

								return false;
							});
						});
					</script>
					<?php } ?>

				</div>
			</div>
		</div>
	</div>
	<?php } ?>
<?php } ?>


<script>

$("#check_all").click(function(){ 
    var chk = $(this).attr('checked');//.attr('checked'); 
    if(chk){
        $("#agree11").prop('checked', true);
        $("#agree21").prop('checked', true);
    }else{
        $("#agree11").prop('checked', false);
        $("#agree21").prop('checked', false);
    } 
});


    
    
    function fregister_submit(f) {
        if (!f.agree.checked) {
            alert("이용약관에 내용에 동의하셔야 회원가입 하실 수 있습니다.");
            f.agree.focus();
            return false;
        }

        if (!f.agree2.checked) {
            alert("개인정보처리방침의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
            f.agree2.focus();
            return false;
        }

        return true;
    }
</script>
</div>