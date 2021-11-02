<?php
include_once('./_common.php');

if(!$is_member){
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "소독관리";
include_once("./_head.php");
?>

<div>내용</div>

<?php
include_once('./_tail.php');
?>
