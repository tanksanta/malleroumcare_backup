<?php
include_once("./_common.php");

if($is_admin != 'super') alert('접근 ㄴㄴ');

$result = send_alim_talk('test', $hp, 'ent_eform_result', '홍길동님과 전자계약이 체결되었습니다.');
var_dump($result);
?>
