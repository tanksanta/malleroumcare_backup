<?php
// 회원정보 찾기 안내 메일입니다. 
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

// 메일제목
$subject = "[".$config['cf_title']."] - 아이디 찾기 결과 안내 메일.";

?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>아이디 찾기 결과 안내 메일</title>
</head>

<body>

<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">
	<div style="border:1px solid #dedede">
		<h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">
			[<?=$config['cf_title']?>]<br/>
			 - 아이디 찾기 결과 안내 메일.<br/><br/>
		</h1>
		<p style="margin:20px 0 0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">
			<?php echo addslashes($mb['mb_name']);?> (<?php echo addslashes($mb['mb_nick']);?>) 회원님은 <?php echo G5_TIME_YMDHIS;?> 에 회원정보 찾기 요청을 하셨습니다.<br>
		</p>
		<p style="margin:0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">
			<span style="display:inline-block;width:100px">회원아이디</span> <?php echo $mb['mb_id'];?><br>
		</p>
		<a href="<?php echo $href;?>" target="_blank" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center">이로움 바로가기</a>
	</div>
</div>

</body>
</html>
