<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

if($header_skin)
	include_once('./header.php');


	// ============================================	
	// 23.03.30 : 서원 - 회원가입 후 자동으로 로그인 되어 화면 전환됨.
	// 						승인 이후 사용가능 함으로 해당 가입 완료 페이지에서 다시 로그아웃 시켜버림.
	// ============================================	

	session_unset(); // 모든 세션변수를 언레지스터 시켜줌
	session_destroy(); // 세션해제함

	// 자동로그인 해제 --------------------------------
	set_cookie('ck_mb_id', '', 0);
	set_cookie('ck_auto', '', 0);
	// 자동로그인 해제 end --------------------------------

	// ============================================	
	// 23.03.30 : 서원 - 회원가입 후 자동으로 로그인 되어 화면 전환됨.
	// 						승인 이후 사용가능 함으로 해당 가입 완료 페이지에서 다시 로그아웃 시켜버림.
	// ============================================	
?>

<link rel="stylesheet" href="<?=G5_CSS_URL?>/new_css/thkc_join.css">

<?php if(!$pim) { ?>

    <!--  회원가입 완료 페이지 -->
    <div class="thkc_loginWrap thkc_containe">

        <section class="login_contWrap">
            <img src="<?=G5_IMG_URL;?>/new_common/thkc_ico_chackOk.svg" alt="">
            <h2>회원가입이 완료되었습니다.</h2>
            <h6>관리자 승인 후 서비스 이용이 가능합니다.</h6>
			
            <!-- 버튼 -->
            <div class="login_btn">
                <a href="<?=G5_URL;?>">
                    <p class="btn_submit_01">메인으로</p>
                </a>
            </div>
        </section>

    </div>

<?php } ?>