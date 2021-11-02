<?php
include_once('./_common.php');

header('Content-Type: application/json');

$entNm = trim($_POST['entNm']);
$deviceInfo = trim($_POST['deviceInfo']);
$insert_id = trim($_POST['insert_id']);
$re_req = trim($_POST['re_req']);

if (!$u_email) {
    $ret = array(
        'result' => 'fail',
        'msg' => '이메일을 입력해주세요.',
    );
    json_response(400, $ret);
    exit;
}

$mail_contents = '
<div style="background-color:#ffffff;width:100%;max-width:600px;padding:30px;border:1px solid #cfcfcf;border-radius:5px;">
    <div style="padding-bottom:20px;border-bottom:1px solid #cfcfcf;">
        <div style="width:100%;text-align:center;">
            <img src="https://eroumcare.com/thema/eroumcare/assets/img/hd_logo.png" style="width:200px;"/>
        </div>
        <div style="margin-top:20px;color:#333333;text-align:center;">
            <p style="color:#333333;font-size:36px;padding:0;margin:auto;text-align:center;">새 기기에서 수급자 확인시도</p>
            <p style="color:#333333;font-size:24px;">'.$entNm.'</p>
        </div>
        <div style="clear:both;"></div>
    </div>
    <div style="width:100%;margin-top:30px;text-align:center;">
	<p style="font-size:16px;color:#656565;text-align:center;">' . $deviceInfo . '
		<br/>
		<br/>방금 이로움 계정이 새 기기에서 수급자 정보를 확인했습니다.
		<br/>이 이메일은 본인이 기기등록 한 것이 맞는지 확인하기 위해 발송되었습니다.</p>';

if ($re_req == "Y") {
    $mail_contents .= '
    <br/>
    <p style="margin:30px;"> <a href="' . G5_SHOP_URL . '/email_device_check.php?status=A&insert_id=' . $insert_id . '" target="_blank" style="background-color:#ef7d01;text-align:center;padding: 12px 60px;color:white;text-decoration:none;margin:30px;font-size:18px;">본인확인</a>
    </p>
    ';
}

$mail_contents .= '
        <br/> <a href="' . G5_SHOP_URL . '/email_device_check.php?status=D&insert_id=' . $insert_id . '" target="_blank" style="color:#656565;text-decoration:underline;font-size:18px;">본인아님 차단함</a>
    </div>
</div>
<div style="width:100%;max-width:600px;padding:0 30px;margin-top:30px;text-align:center;">
	<p style="font-size:14px;color:#656565;text-align:center;">이 이메일은 이로움 시스템에서 중요한 변경사항을 알려드리기 위해 발송되었습니다.
		<br/>Copyright ⓒEroumcare All righs reserved.</p>
</div>
';

include_once(G5_LIB_PATH.'/mailer.lib.php');
mailer($config['cf_admin_email_name'], $config['cf_admin_email'], trim($u_email), '보안알림', $mail_contents, 1);

$ret = array(
    'result' => 'success',
    'msg' => '발송하였습니다.',
);

json_response(200, 'OK', $ret);
?>