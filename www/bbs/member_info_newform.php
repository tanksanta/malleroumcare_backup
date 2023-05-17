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
  /* // 파일명 :  /www/bbs/member_info_newform.php */
  /* // 파일 설명 : 신규파일 - 회원정보 수정 페이지 */
  /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    include_once('./_common.php');
    include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
    include_once(G5_LIB_PATH.'/register.lib.php');

    // 불법접근을 막도록 토큰생성
    $token = md5(uniqid(rand(), true));
    set_session("ss_token", $token);
    set_session("ss_cert_no",   "");
    set_session("ss_cert_hash", "");
    set_session("ss_cert_type", "");

    $is_social_login_modify = false;

    if($is_admin == 'super')
        alert('관리자의 회원정보는 관리자 화면에서 수정해 주십시오.', G5_URL);

    if(!$is_member)
        alert('로그인 후 이용하여 주십시오.', G5_URL);

    if($member['mb_id'] != $_POST['mb_id'] && !$_GET['STEP'])
        alert('로그인된 회원과 넘어온 정보가 서로 다릅니다.', G5_URL);


    if( !$_GET['STEP'] && $_POST['mb_id'] && ! (isset($_POST['mb_password']) && $_POST['mb_password'])){
        if( ! $is_social_login_modify ){
            alert('비밀번호를 입력해 주세요.');
        }
    }

    if($_POST['mb_password'] && !$_GET['STEP']) {
        // 수정된 정보를 업데이트후 되돌아 온것이라면 비밀번호가 암호화 된채로 넘어온것임
        if ($_POST['is_update'])
            $tmp_password = $_POST['mb_password'];
        else
            $tmp_password = get_encrypt_string($_POST['mb_password']);

        if ($member['mb_password'] != $tmp_password) {
            // 비밀번호 틀릴 경우 
            $result = api_post_call(EROUMCARE_API_ACCOUNT_ENT_LOGIN, array(
                'usrId' => $_POST['mb_id'],
                'pw' => $_POST['mb_password']
            ));

            if(!$result || $result['errorYN'] != 'N')
                alert('비밀번호가 틀립니다.');
        }
    }

    $_referer = false;
    if( (strpos($_SERVER["HTTP_REFERER"],"member_info_newform.php")) ) { $_referer = true; };
    if( !$_referer && $_POST['w']!="u" ) { alert('회원 정보 페이지 접근 오류 입니다. \n비밀번호를 다시 입력해주세요.', G5_BBS_URL."/member_confirm.php?url=member_info_newform.php"); }
?>


<?php

    // Page ID
    $pid = ($pid) ? $pid : 'regform';
    $at = apms_page_thema($pid);
    include_once(G5_LIB_PATH.'/apms.thema.lib.php');

    // 스킨 체크
    list($member_skin_path, $member_skin_url) = apms_skin_thema('member', $member_skin_path, $member_skin_url);

    // 설정값 불러오기
    $is_regform_sub = false;
    @include_once($member_skin_path.'/config.skin.php');

    if($is_regform_sub) {
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


    if( !$_GET['STEP'] || ($_GET['STEP'] == "stop01")  ) {
        // 사업자정보 스킨 페이지
        include_once($skin_path.'/member_info_newForm01.skin.php');
    } else if( $_GET['STEP'] == "stop02" ) {
        // 계정관리 스킨 페이지
        include_once($skin_path.'/member_info_newForm02.skin.php');
    } else if( $_GET['STEP'] == "stop03" ) {
        // 배송지정보 스킨 페이지
        include_once($skin_path.'/member_info_newForm03.skin.php');
    } else if( $_GET['STEP'] == "stop04" ) {
        /// 서비스정보 스킨 페이지
        include_once($skin_path.'/member_info_newForm04.skin.php');
    } else {
        alert('잘못된 접근 방식 입니다.', G5_URL);        
    }


    if($is_regform_sub) {
        if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
        include_once(G5_PATH.'/tail.sub.php');
    } else {
        include_once('./_tail.php');
    }
  ?>