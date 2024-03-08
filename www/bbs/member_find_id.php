<?php 
  /* // */
  /* // */
  /* // */
  /* // */
  /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
  /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
  /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
  /* //  *  */
  /* //  *  */
  /* //  * (주)티에이치케이컴퍼 & 이로움 - [ THKcompany & E-Roum ] */
  /* //  *  */
  /* //  * Program Name : EROUMCARE Platform! = Renewal Ver:1.0 */
  /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
  /* //  * Copyright (c) 2023 THKC Co,Ltd.  All rights reserved. */
  /* //  *  */
  /* //  *  */
  /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
  /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
  /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
  /* // */
  /* // */
  /* // */
  /* // */

  /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */
  /* // 파일명 :  \www\bbs\member_find_id.php */
  /* // 파일 설명 : 신규파일 - 아이디 찾기 */
  /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

include_once('./_common.php');

$mb = get_member($_SESSION['ss_mb_id']);

// 회원정보가 있으면 초기 페이지로 이동
 if ($mb['mb_id'])
     goto_url(G5_URL);

$mb['mb_name'] = get_text($mb['mb_name']); 

// Page ID
$pid = ($pid) ? $pid : 'regresult';
$at = apms_page_thema($pid);
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

// 스킨 체크
list($member_skin_path, $member_skin_url) = apms_skin_thema('member', $member_skin_path, $member_skin_url); 

// 설정값 불러오기
$is_regresult_sub = false;
@include_once($member_skin_path.'/config.skin.php');

$g5['title'] = '아이디 찾기';

if($is_regresult_sub) {
	include_once(G5_PATH.'/head.sub.php');
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/head.sub.php');
} else {
	include_once('./_head.php');
}

$skin_path = $member_skin_path;
$skin_url = $member_skin_url;

// 스킨설정
$wset = (G5_IS_MOBILE) ? apms_skin_set('member_mobile') : apms_skin_set('member');

$setup_href = '';
if(is_file($skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
	$setup_href = './skin.setup.php?skin=member&amp;ts='.urlencode(THEMA);
}

include_once($skin_path.'/member_find_id.skin.php');

if($is_regresult_sub) {
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
	include_once(G5_PATH.'/tail.sub.php');
} else {
	include_once('./_tail.php');
}
?>
