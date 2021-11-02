<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

$mb_giup_bnum = trim($_POST['reg_mb_giup_bnum']);

set_session('ss_check_mb_giup_bnum', '');

if ($msg = valid_mb_giup_bnum($mb_giup_bnum))     die($msg);
if ($msg = exist_mb_giup_bnum($mb_giup_bnum))     die($msg);

set_session('ss_check_mb_giup_bnum', $mb_giup_bnum);
?>