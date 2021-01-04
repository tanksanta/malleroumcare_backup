<?php
include_once('./_common.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
include_once(G5_LIB_PATH.'/register.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

if ($w == 'u' && $is_admin == 'super') {
    if (file_exists(G5_PATH.'/DEMO'))
        alert('데모 화면에서는 하실(보실) 수 없는 작업입니다.');
}

$mb_id = isset($_SESSION['ss_mb_id']);

$sql = " insert into {$g5['member_table']}
set mb_id = '{$mb_id}',
    mb_password = '',
    mb_name = 'hsy',
    mb_nick = 'hsy',
    mb_nick_date = '".G5_TIME_YMD."',
    mb_email = 'hsy@hsy.com',
    mb_homepage = '',
    mb_tel = '01011111111',
    mb_fax = '1',
    mb_zip1 = '12345',
    mb_zip2 = '12345',
    mb_addr1 = '서울',
    mb_addr2 = '관악',
    mb_addr3 = '봉천',
    mb_addr_jibeon = '123',
    mb_signature = '',
    mb_profile = '',
    mb_today_login = '".G5_TIME_YMDHIS."',
    mb_datetime = '".G5_TIME_YMDHIS."',
    mb_ip = '{$_SERVER['REMOTE_ADDR']}',
    mb_level = '{$config['cf_register_level']}',
    mb_recommend = '',
    mb_login_ip = '{$_SERVER['REMOTE_ADDR']}',
    mb_mailling = '',
    mb_sms = '',
    mb_open = '',
    mb_open_date = '".G5_TIME_YMD."',
    mb_1 = '',
    mb_2 = '',
    mb_3 = '',
    mb_4 = '',
    mb_5 = '',
    mb_6 = '',
    mb_7 = '',
    mb_8 = '',
    mb_9 = '',
    mb_10 = '',
    mb_type = '',
    mb_giup_type = '',
    mb_giup_bname = '',
    mb_giup_boss_name = '',
    mb_giup_btel = '',
    mb_giup_bnum = '',
    mb_giup_sbnum = '',
    mb_giup_sbnum_explain = '',
    mb_giup_buptae = '',
    mb_giup_bupjong = '',
    mb_giup_addr1 = '',
    mb_giup_addr2 = '',
    mb_giup_addr3 = '',
    mb_giup_addr_jibeon = '',
    mb_giup_zip1 = '',
    mb_giup_zip2 = '',
    mb_giup_tax_email = '',
    mb_giup_manager_name = '',
    mb_giup_manager_tel = '',
    mb_update_date = now(),
    mb_thezone = '{$mb_thezone_code}',
    mb_email_certify = '".G5_TIME_YMDHIS."'
    {$sql_certify} ";

    if ($member = get_member($_SESSION['ss_mb_id'])) {
        sql_query($sql);
    }    

// 회원 아이콘
$mb_dir = G5_DATA_PATH.'/member/'.substr($mb_id,0,2);

// 아이콘 삭제
if (isset($_POST['del_mb_icon'])) {
    @unlink($mb_dir.'/'.$mb_id.'.gif');
}

$msg = "";

// 아이콘 업로드
$mb_icon = '';
$image_regex = "/(\.(gif|jpe?g|png))$/i";
$mb_icon_img = $mb_id.'.gif';

if (isset($_FILES['mb_icon']) && is_uploaded_file($_FILES['mb_icon']['tmp_name'])) {
    if (preg_match($image_regex, $_FILES['mb_icon']['name'])) {
        // 아이콘 용량이 설정값보다 이하만 업로드 가능
        if ($_FILES['mb_icon']['size'] <= $config['cf_member_icon_size']) {
            @mkdir($mb_dir, G5_DIR_PERMISSION);
            @chmod($mb_dir, G5_DIR_PERMISSION);
            $dest_path = $mb_dir.'/'.$mb_icon_img;
            move_uploaded_file($_FILES['mb_icon']['tmp_name'], $dest_path);
            chmod($dest_path, G5_FILE_PERMISSION);
            if (file_exists($dest_path)) {
                //=================================================================\
                // 090714
                // gif 파일에 악성코드를 심어 업로드 하는 경우를 방지
                // 에러메세지는 출력하지 않는다.
                //-----------------------------------------------------------------
                $size = @getimagesize($dest_path);
                if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3)) { // jpg, gif, png 파일이 아니면 올라간 이미지를 삭제한다.
                    @unlink($dest_path);
                } else if ($size[0] > $config['cf_member_icon_width'] || $size[1] > $config['cf_member_icon_height']) {
                    $thumb = null;
                    if($size[2] === 2 || $size[2] === 3) {
                        //jpg 또는 png 파일 적용
                        $thumb = thumbnail($mb_icon_img, $mb_dir, $mb_dir, $config['cf_member_icon_width'], $config['cf_member_icon_height'], true, true);
                        if($thumb) {
                            @unlink($dest_path);
                            rename($mb_dir.'/'.$thumb, $dest_path);
                        }
                    }
                    if( !$thumb ){
                        // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                        @unlink($dest_path);
                    }
                }
                //=================================================================\
            }
        } else {
            $msg .= '회원아이콘을 '.number_format($config['cf_member_icon_size']).'바이트 이하로 업로드 해주십시오.';
        }

    } else {
        $msg .= $_FILES['mb_icon']['name'].'은(는) 이미지 파일이 아닙니다.';
    }
}

// 회원 프로필 이미지
if( $config['cf_member_img_size'] && $config['cf_member_img_width'] && $config['cf_member_img_height'] ){
    $mb_tmp_dir = G5_DATA_PATH.'/member_image/';
    $mb_dir = $mb_tmp_dir.substr($mb_id,0,2);
    if( !is_dir($mb_tmp_dir) ){
        @mkdir($mb_tmp_dir, G5_DIR_PERMISSION);
        @chmod($mb_tmp_dir, G5_DIR_PERMISSION);
    }

    // 아이콘 삭제
    if (isset($_POST['del_mb_img'])) {
        @unlink($mb_dir.'/'.$mb_icon_img);
    }

    // 회원 프로필 이미지 업로드
    $mb_img = '';
    if (isset($_FILES['mb_img']) && is_uploaded_file($_FILES['mb_img']['tmp_name'])) {

        $msg = $msg ? $msg."\\r\\n" : '';

        if (preg_match($image_regex, $_FILES['mb_img']['name'])) {
            // 아이콘 용량이 설정값보다 이하만 업로드 가능
            if ($_FILES['mb_img']['size'] <= $config['cf_member_img_size']) {
                @mkdir($mb_dir, G5_DIR_PERMISSION);
                @chmod($mb_dir, G5_DIR_PERMISSION);
                $dest_path = $mb_dir.'/'.$mb_icon_img;
                move_uploaded_file($_FILES['mb_img']['tmp_name'], $dest_path);
                chmod($dest_path, G5_FILE_PERMISSION);
                if (file_exists($dest_path)) {
                    $size = @getimagesize($dest_path);
                    if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3)) { // gif jpg png 파일이 아니면 올라간 이미지를 삭제한다.
                        @unlink($dest_path);
                    } else if ($size[0] >= $config['cf_member_img_width'] || $size[1] >= $config['cf_member_img_height']) {
                        $thumb = null;
                        if($size[2] === 2 || $size[2] === 3) {
                            //jpg 또는 png 파일 적용
                            $thumb = thumbnail($mb_icon_img, $mb_dir, $mb_dir, $config['cf_member_img_width'], $config['cf_member_img_height'], true, true);
                            if($thumb) {
                                @unlink($dest_path);
                                rename($mb_dir.'/'.$thumb, $dest_path);
								
								//회원정보 업데이트
								sql_query(" update {$g5['member_table']} set as_photo = '1' where mb_id = '$mb_id' ", false);
							}
                        }
                        if( !$thumb ){
                            // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                            @unlink($dest_path);
                        }
                    }
                    //=================================================================\
                }
            } else {
                $msg .= '회원이미지를 '.number_format($config['cf_member_img_size']).'바이트 이하로 업로드 해주십시오.';
            }

        } else {
            $msg .= $_FILES['mb_img']['name'].'은(는) gif/jpg 파일이 아닙니다.';
        }
    }
}

// 사용자 코드 실행
@include_once ($member_skin_path.'/register_form_update.tail.skin.php');

unset($_SESSION['ss_cert_type']);
unset($_SESSION['ss_cert_no']);
unset($_SESSION['ss_cert_hash']);
unset($_SESSION['ss_cert_birth']);
unset($_SESSION['ss_cert_adult']);

if ($w == '') {
	if($pim) {
		goto_url(G5_HTTP_BBS_URL.'/register_result.php?pim='.$pim);
	} else {
		goto_url(G5_HTTP_BBS_URL.'/register_result.php');
	}
}
?>
