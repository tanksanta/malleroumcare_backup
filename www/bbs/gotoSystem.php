<?php
include_once('./_common.php');
if(!$member['mb_id']){
    alert('잘못된 접근입니다.',G5_URL );
}
$mb_id = $member['mb_id'];
$password= $_SESSION[$mb_id];
if(!$mb_id||!$password){
    alert('시스템으로 이동할 수 없습니다. 관리자에 문의하세요.',G5_URL);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<body>
<form  id="AjaxForm" method="post" type ="json" action="https://system.eroumcare.com/cmm/cmm2000/cmm2000/selectCmm2001Login.do">
<input type="hidden" name="id" value="<?=$mb_id?>">
<input type="hidden" name="pw" value="<?=$password?>">
</form>
    <script type="text/javascript">
        var AjaxForm = document.getElementById('AjaxForm');
        AjaxForm.submit();
    </script>
</body>
</html>