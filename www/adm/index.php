<?php
$sub_menu = '100000';
include_once('./_common.php');
//if ($is_admin != 'super')
//    alert('최고관리자만 접근 가능합니다.');
//아미나빌더 설치체크
if(!isset($config['as_thema'])) {
	goto_url(G5_ADMIN_URL.'/apms_admin/apms.admin.php');
}

// 최초접속링크가 있으면 연결, 아니면 관리자메인(/adm/)으로 이동
$sql = ("   SELECT 
                count(*) AS cnt
                ,entry_link 
            FROM 
                g5_auth 
            WHERE 
                mb_id = '{$member['mb_id']}' 
                AND entry_menu = 'y'
");
$result = sql_fetch($sql);


$_goUrl = "";
if( $_SERVER['HTTPS'] === "on" ) { $_goUrl = "https://" . $_SERVER['SERVER_NAME']; } else { $_goUrl = "http://" . $_SERVER['SERVER_NAME']; }

if ( ($is_admin == 'super') || $result['cnt'] ) {

    $position = strpos($result['entry_link'], $_SERVER['SERVER_NAME']);

    if( $position !== false ) {
        $_uri_link = substr($result['entry_link'], $position + strlen($_SERVER['SERVER_NAME']));
    } else { 
        $_uri_link = $result['entry_link']; 
    }

    if( $result['entry_link'] ) { goto_url( $_goUrl . $_uri_link ); exit(); }

} else {
    
    alert('관리자만 접근 가능합니다.');
    goto_url( $_goUrl ); exit();

}

@include_once('./safe_check.php');
if(function_exists('social_log_file_delete')){
    social_log_file_delete(86400);      //소셜로그인 디버그 파일 24시간 지난것은 삭제
}

$g5['title'] = '관리자메인';
include_once ('./admin.head.php');
include_once (ADMIN_SKIN_PATH.'/index.php');
include_once ('./admin.tail.php');
?>
