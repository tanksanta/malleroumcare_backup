<?php
$sub_menu = '400460';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '수금등록';
include_once (G5_ADMIN_PATH.'/admin.head.php');
?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
