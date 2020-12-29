<?php
include_once('./_common.php');

$g5['title'] = '파트너 승인 대기중';

include_once(G5_PATH.'/head.php');

if ( !partner_daegi() ) {
    goto_url(G5_URL);
}

?>

<div id="wait_partner">

    <h1>파트너 승인 대기중 입니다.</h1>
    <p class="h1after">
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
    <p class="call">
        <a href="tel:<?php echo $default['de_admin_company_tel']; ?>"><img src="<?php echo THEMA_URL; ?>/assets/img/icon_tel.png" title=""><?php echo $default['de_admin_company_tel']; ?></a>
    </p>
</div>

<?php 
include_once(G5_PATH.'/tail.php'); 
?>