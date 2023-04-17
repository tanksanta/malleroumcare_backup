<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if($header_skin)
	include_once('./header.php');
?>

	<link rel="stylesheet" href="<?=G5_CSS_URL?>/new_css/thkc_join.css">

    <!--  로그인 페이지 -->
    <div class="thkc_loginWrap thkc_containe">

		<form class="form" role="form" name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
		<input type="hidden" name="url" value='<?php echo $login_url ?>'>

        <section>
            <div class="imgView">
                <img src="<?=G5_IMG_URL?>/new_common/thkc_login_main.png" alt="">
            </div>
            <div class="lineWrap"></div>
            <div class="login_contWrap">
                <p class="login_Title01">안녕하세요.<br>
                    시니어 라이프 케어 플랫폼<br>
                    이로움입니다.</p>
                <div class="joinWrap">
                    <!-- 입력 폼 -->
                    <form action="#" method="get">
                        <fieldset>
                            <legend class="blind">아이디/비밀번호</legend>
                            <p class="field table-box">
                                <label for="user-id" class="blind">아이디</label>
                                <input type="text" id="user-id" name="mb_id" placeholder="아이디 입력" class="inp-field" tabIndex="1" >
                                <span class="inTitleWrap"><input type="checkbox" name="auto_login" id="login_auto_login" tabIndex="3"><span class="inTitle">자동로그인</span></span>
                            </p>
                            <p class="field show">
                                <label for="user-pass" class="blind">패스워드</label>
                                <input type="password" id="user-pass" name="mb_password" placeholder="패스워드 입력" class="inp-field _error_input_inner" tabIndex="2"> <!-- error css -->
                                <i><img class="icon icon-eyes-on" src="<?=G5_IMG_URL?>/new_common/icon_input_eye.png">
                                   <img class="icon icon-eyes-off" src="<?=G5_IMG_URL?>/new_common/icon_input_slash.png">
                                </i>
                            </p>
                            <span class="_error-text" style="display: none;">8글자 이상 입력해 주세요.</span>
                        </fieldset>
                    </form>
                </div>
                <!-- 버튼 -->
                <div class="login_btn">
                    <input type="submit" value="로그인" class="btn_submit_01">
                    <a href="<?=G5_BBS_URL?>/register.php"><p class="btn_submit_02">회원가입</p></a>
                </div>
                <div class="login_btn_etc f_s14">
                    <span><a href="<?=G5_BBS_URL ?>/member_find_id.php" class="text_under">아이디 찾기</a></span>
                    <div class="linebar">
                        <sapn></span>
                    </div>
                    <span><a href="<?=G5_BBS_URL ?>/member_find_pw.php" class="text_under">비밀번호 찾기</a></span>
                    <div class="linebar">
                        <sapn></span>
                    </div>
                    <span><a href="<?=G5_URL;?>" class="text_under">메인으로</a></span>
                </div>
            </div>
        </section>
		</form>
    </div>


	<script>
	$(function(){
		$("#login_auto_login").click(function(){
			if (this.checked) {
				this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
			}
		});
	});

	function flogin_submit(f) {
		return true;
	}
	</script>
	<!-- } 로그인 끝 -->