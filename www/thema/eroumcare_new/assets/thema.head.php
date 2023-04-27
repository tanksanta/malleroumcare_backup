<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(!$is_index && !$is_main) { ?>

    <link type="text/css" rel="stylesheet" href="<?=THEMA_URL;?>/assets/css/font.css?ver=<?=APMS_SVER;?>"/>
    <link type="text/css" rel="stylesheet" href="<?=THEMA_URL;?>/assets/css/slick.css?ver=<?=APMS_SVER;?>"/>
    <link type="text/css" rel="stylesheet" href="<?=THEMA_URL;?>/assets/css/common_new.css?ver=<?=APMS_SVER;?>"/>
    <link type="text/css" rel="stylesheet" href="<?=THEMA_URL;?>/assets/css/admin_new.css?ver=<?=APMS_SVER;?>"/>

    <script src="<?=THEMA_URL;?>/assets/bs3/js/bootstrap.min.js?ver=<?=APMS_SVER;?>"></script>    
    <script src="<?=THEMA_URL;?>/assets/js/jquery.cookie.js?ver=<?=APMS_SVER;?>"></script>
    <script src="<?=THEMA_URL;?>/assets/js/slick.min.js?ver=<?=APMS_SVER;?>"></script>
    <script src="<?=THEMA_URL;?>/assets/js/common_new.js?ver=<?=APMS_SVER;?>"></script>

<?php } ?>