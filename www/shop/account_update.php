<?php
include_once('./_common.php');

if(!$member['mb_id'])
    alert('먼저 로그인해주세요.');

$mb_account = clean_xss_tags($_POST['mb_account']);

$sql = "
    UPDATE g5_member
    SET mb_account = '$mb_account'
    WHERE mb_id = '{$member['mb_id']}';
";

sql_query($sql);

goto_url('/shop/electronic_manage.php');
