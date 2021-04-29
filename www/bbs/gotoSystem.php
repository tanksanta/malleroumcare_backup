<?php
include_once('./_common.php');

if($member['mb_id']){
$sql = "SELECT `mb_password2` FROM `g5_member` WHERE `mb_id` = '".$member['mb_id']."'";
$result = sql_fetch($sql);
$password= base64_decode($result['mb_password2']);
Header("Location:https://system.eroumcare.com/cmm/cmm2000/cmm2000/selectCmm2001Login.do?id=".$member['mb_id']."&pw=".$password);
}else{
Header("Location:https://system.eroumcare.com");
}

?>