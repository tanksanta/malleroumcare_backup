<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<?php 
    add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/bs3/css/bootstrap.min.css?ver='.time().'" type="text/css">',0);
    add_stylesheet('<link rel="stylesheet" href="'.COLORSET_URL.'/colorset.css?ver='.time().'" type="text/css">',0);
?>

<?php if(!$is_index && !$is_main) { ?>
    <link type="text/css" rel="stylesheet" href="<?=THEMA_URL;?>/assets/css/font.css?ver=<?=time();?>"/>
    <link type="text/css" rel="stylesheet" href="<?=THEMA_URL;?>/assets/css/slick.css?ver=<?=time();?>"/>
    <link type="text/css" rel="stylesheet" href="<?=THEMA_URL;?>/assets/css/common_new.css?ver=<?=time();?>"/>
    <link type="text/css" rel="stylesheet" href="<?=THEMA_URL;?>/assets/css/admin_new.css?ver=<?=time();?>"/>

    <script src="<?=THEMA_URL;?>/assets/bs3/js/bootstrap.min.js?ver=<?=time();?>"></script>    
    <script src="<?=THEMA_URL;?>/assets/js/jquery.cookie.js?ver=<?=time();?>"></script>
    <script src="<?=THEMA_URL;?>/assets/js/slick.min.js?ver=<?=time();?>"></script>
    <script src="<?=THEMA_URL;?>/assets/js/common_new.js?ver=<?=time();?>"></script>  
<?php } ?>