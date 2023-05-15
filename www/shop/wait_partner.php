<?php
include_once('./_common.php');

$g5['title'] = '파트너 승인 대기중';

include_once(G5_PATH.'/head.php');

if ( !partner_daegi() ) {
    goto_url(G5_URL);
}

?>

<div id="wait_partner" style="margin: 100px 0;">

    <h1>파트너 승인 대기중 입니다.</h1>
    <div style="list-style: none; margin: 0 auto; padding: 0; width: 550px;">
        <li class="" style="color: #868686; padding: 10px 15px; border: 1px solid #e1e1e1; background-color: white; letter-spacing: -1px; font-weight: bold; text-align:center; margin: 0px 210px;">
            <a href="<?=G5_BBS_URL;?>/logout.php">로그아웃</a>
        </li>
    </div>
<!--
    <p class="h1after" style="margin: 50px 0;">
        신청서 및 계약서를 다운로드 하신 후 이메일 및 우편으로 보내주시면<br/>
        담당자 최종 승인 후 서비스 이용이 가능하십니다.
    </p>
    <ul class="wait-partner-btns">
        <li class="">
			<a href="#">파트너 가입신청서<img src="<?php echo THEMA_URL; ?>/assets/img/icon_down_b.png"></a>
		</li>
        <li class="">
			<a href="#">물품 공급계약서<img src="<?php echo THEMA_URL; ?>/assets/img/icon_down_b.png"></a>
		</li>
        <li class="">
			<a href="#">가입 및 유지 관련사항<img src="<?php echo THEMA_URL; ?>/assets/img/icon_down_b.png"></a>
		</li>
    </ul>
    <p class="content">
        <b class="h">가입신청서와 물품공급계약서<span class="gray">작성 후 아래 정보로 보내주세요</span></b>
        <b>Email</b><span class="neirong"><?php echo $default['de_admin_info_email']; ?></span><br/><br/>
        <b>Address</b><span ass="neirong"><?php echo $default['de_admin_company_addr']; ?></span><br/>
    </p>
-->
    <p style="height: 100px;"></p>

    <div class="left_csWrap" style="list-style: none; margin: 0 auto; padding: 0; width: 550px;">
        <div class="csTitle" style="text-align:center;">이로움 고객센터</div>
        <div class="csCall" style="text-align:center; font-size: 30px; font-weight: 500; color: #FF6B00;">1533-5088</div>
    </div>

</div>

<?php 
include_once(G5_PATH.'/tail.php'); 
?>