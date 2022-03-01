<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_PHPMAILER_PATH.'/PHPMailerAutoload.php');

// 메일 보내기 (파일 여러개 첨부 가능)
// type : text=0, html=1, text+html=2
function mailer($fname, $fmail, $to, $subject, $content, $type=0, $file="", $cc="", $bcc="")
{
    global $config;
    global $g5;

    // 메일발송 사용을 하지 않는다면
    if (!$config['cf_email_use']) return;

    if ($type != 1)
        $content = nl2br($content);

    $mail = new PHPMailer(); // defaults to using php "mail()"
    if (defined('G5_SMTP') && G5_SMTP) {
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->Host = G5_SMTP; // SMTP server
        if(defined('G5_SMTP_PORT') && G5_SMTP_PORT)
            $mail->Port = G5_SMTP_PORT;
        
        if(defined('G5_SMTP_USERNAME') && G5_SMTP_USERNAME) {
            $mail->SMTPAuth = true;
            $mail->Username = G5_SMTP_USERNAME;
            $mail->Password = G5_SMTP_PASSWORD;
        }
        
        if(defined('G5_SMTP_SSL') && G5_SMTP_SSL) {
            $mail->SMTPSecure = "ssl";
        }
    }
    $mail->CharSet = 'UTF-8';
    $mail->From = $fmail;
    $mail->FromName = $fname;
    $mail->Subject = $subject;
    $mail->AltBody = ""; // optional, comment out and test
    $mail->msgHTML($content);
    $mail->addAddress($to);
    if ($cc)
        $mail->addCC($cc);
    if ($bcc)
        $mail->addBCC($bcc);
    //print_r2($file); exit;
    if ($file != "") {
        foreach ($file as $f) {
            if ($f['filetype'] == "base64") {
                $mail->addStringAttachment($f['path'], $f['name']);
            }
            else {
                $mail->addAttachment($f['path'], $f['name']);
            }
        }
    }
    return $mail->send();
}

// 동시에 여러명에게 메일 보내기
// type : text=0, html=1, text+html=2
function mailer_multiple($fname, $fmail, $datas)
{
    global $config;
    global $g5;

    // 메일발송 사용을 하지 않는다면
    if (!$config['cf_email_use']) return;

    $mail = new PHPMailer(); // defaults to using php "mail()"
    if (defined('G5_SMTP') && G5_SMTP) {
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->Host = G5_SMTP; // SMTP server
        if(defined('G5_SMTP_PORT') && G5_SMTP_PORT)
            $mail->Port = G5_SMTP_PORT;
        
        if(defined('G5_SMTP_USERNAME') && G5_SMTP_USERNAME) {
            $mail->SMTPAuth = true;
            $mail->Username = G5_SMTP_USERNAME;
            $mail->Password = G5_SMTP_PASSWORD;
        }
        
        if(defined('G5_SMTP_SSL') && G5_SMTP_SSL) {
            $mail->SMTPSecure = "ssl";
        }
    }

    $mail->CharSet = 'UTF-8';
    foreach($datas as $data) {
        if ($data['type'] != 1)
            $data['content'] = nl2br($data['content']);

        $mail->From = $fmail;
        $mail->FromName = $fname;
        $mail->Subject = $data['subject'];
        $mail->AltBody = ""; // optional, comment out and test

        $mail->clearAllRecipients();
        $mail->clearAttachments();
        $mail->addAddress($data['receiver']);
        $mail->msgHTML($data['content']);

        if($data['file']) {
            if ($data['file']['encoding'] == 'remove') {
                $mail->addStringAttachment($data['file']['data'], $data['file']['name']);
            }
            else {
                $mail->addStringAttachment($data['file']['data'], $data['file']['name'], 'binary');
            }
            
        }

        $mail->send();
    }
    return true;
}

// 파일을 첨부함
function attach_file($filename, $tmp_name)
{
    // 서버에 업로드 되는 파일은 확장자를 주지 않는다. (보안 취약점)
    $dest_file = G5_DATA_PATH.'/tmp/'.str_replace('/', '_', $tmp_name);
    move_uploaded_file($tmp_name, $dest_file);
    $tmpfile = array("name" => $filename, "path" => $dest_file);
    return $tmpfile;
}
?>