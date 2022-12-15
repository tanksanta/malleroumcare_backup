<?php
include_once("./_common.php");

// include_once("./_head.php"); // 필요시에만 작성
# 회원검사
if(!$member["mb_id"])
  alert("접근 권한이 없습니다.");

# 회원 접근 권한 체크
if(!($is_samhwa_partner || 
$member['mb_type'] === 'manager' || 
$member['mb_type'] === 'partner' || 
$member['mb_type'] === 'default')) {
alert("파트너 회원만 접근 가능한 페이지입니다.");
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>파트너 일정표 > 이로움 장기요양기관 통합관리시스템</title>
  <?php include("include.php"); ?>
</head>

<body id="root" class="flex items-center justify-center" style="background: #edf2f7;">
  <?php include("m_calendar.php"); ?>
  <div class="popup_box">
    <div></div>
  </div>
</body>

</html>