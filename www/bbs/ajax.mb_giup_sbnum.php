<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

$mb_giup_bnum = trim($_POST['reg_mb_giup_bnum']);
$mb_giup_sbnum = trim($_POST['reg_mb_giup_sbnum']);

set_session('ss_check_mb_giup_sbnum', '');

if ($msg = valid_mb_giup_sbnum($mb_giup_sbnum))     die($msg);
if ($msg = exist_mb_giup_sbnum($mb_giup_bnum, $mb_giup_sbnum))     die($msg);

set_session('ss_check_mb_giup_sbnum', $mb_giup_sbnum);
?>