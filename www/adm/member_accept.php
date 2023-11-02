<?php
$sub_menu = "200100";
include_once("./_common.php");

if($auth_check = auth_check($auth[$sub_menu], 'w', true)) {
  json_response(400, $auth_check);
}

if (!$usrId) {
  json_response(400, '유효하지않은 요청입니다.');
}

// 시스템 승인
$result = post_formdata(EROUMCARE_API_ENT_UPDATE, array(
  'usrId' => $usrId,
  'entId' => $entId,
  'entConfirmCd' => '01'
));

if(!$result)
  json_response(500, '시스템 서버 오류로 처리하지 못했습니다.');

add_notification(
  array(),
  $usrId,
  '[이로움] 회원가입 승인 완료',
  '서비스 이용이 가능합니다.',
  G5_URL,
);

// 알림톡 발송
$ent = get_member($usrId);
send_alim_talk('ENT_REGISTER_'.$usrId, $ent['mb_hp'], 'ent_register_accept', "[이로움]\n\n가입승인이 완료되었습니다.\n\n플랫폼 이용문의 : 02-830-1301");

// 23.11.02 - 서원 : 회원중 사업소 레벨을 가진 계정만 이로움ON쪽으로 데이터 동기화 한다. 
if( ($ent['mb_level'] == 3) || ($ent['mb_level'] == 4)  ) {
  // 23.11.02 - 서원 : 이로움ON 등록에만 필요한 자료 리스트.
  $selectedKeys = array(
    'mb_id'
    ,'mb_giup_bname'
    ,'mb_giup_btel'
    ,'mb_fax'
    ,'mb_email'
    ,'mb_giup_boss_name'
    ,'mb_giup_bnum'
    ,'mb_ent_num'
    ,'mb_giup_buptae'
    ,'mb_giup_bupjong'
    ,'mb_giup_zip1'
    ,'mb_giup_zip2'
    ,'mb_giup_addr1'
    ,'mb_giup_addr2'
    ,'mb_zip1'
    ,'mb_zip2'
    ,'mb_addr1'
    ,'mb_addr2'
    ,'mb_giup_manager_name'
    ,'mb_hp'
    ,'mb_email'
    ,'mb_giup_tax_email'
    ,'mb_giup_manager_name'
    ,'mb_thezone'
  );

  // 23.11.02 - 서원 : 이로움Care 원본 데이터에서 이로움ON 등록에만 필요한 필수 데이터 리스트를 기준으로 데이터 추출.
  $selectedData = array_filter($ent, 
    function($key) use ($selectedKeys) { 
      return in_array($key, $selectedKeys);
    }, 
    ARRAY_FILTER_USE_KEY
  );

  // 23.11.02 - 서원 : 프로시저 CALL `PROC_EROUMCARE_BPLC`('모드','이로움ON 회원등록 필수 데이터');
  $sql = (" CALL `PROC_EROUMCARE_BPLC`('INSERT','".json_encode($selectedData, JSON_UNESCAPED_UNICODE)."'); ");
  $sql_result = "";
  $sql_result = sql_fetch( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);
}

json_response(200, 'OK');
