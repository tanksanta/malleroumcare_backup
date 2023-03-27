<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default') 
  json_response(400, '사업소 회원만 이용하실 수 있습니다.');

// 승인여부
$is_approved = false;
$res = api_post_call(EROUMCARE_API_ENT_ACCOUNT, array(
  'usrId' => $member['mb_id']
));
if($res['data']['entConfirmCd'] == '01' || $member['mb_level'] >= 5 ) {
  $is_approved = true;
}

if(!$is_approved)
  json_response(400, '승인된 회원만 이용하실 수 있습니다.');

$query = "SHOW COLUMNS FROM recipient_item_msg_log WHERE `Field` = 'agreement_datetime';";//개인정보제공동의확인시간
$wzres = sql_fetch( $query );
if(!$wzres['Field']) {
    sql_query("ALTER TABLE `recipient_item_msg_log`
	ADD `agreement_datetime` datetime NOT NULL COMMENT '동의확인시간' AFTER ml_sent_at", true);
}


$sql = "
  select
    count(*) as cnt
  from
    recipient_item_msg_log l
  left join
    recipient_item_msg m ON l.ms_id = m.ms_id
  where
    m.mb_id = '{$member['mb_id']}' and
    date(l.ml_sent_at) = curdate()
";
$today_count = sql_fetch($sql)['cnt'] ?: 0;
$today_count = 5 - $today_count;
if(in_array($member['mb_id'], ['hula1202', 'joabokji'])) {
  $today_count = 100 - $today_count;
}

if($today_count <= 0) {
  json_response(400, '사용가능한 메시지가 전부 소진되었습니다.');
}

// 메세지 전송할 때 필요한 포인트
$msg_point = 0; // 메세지 무료 이벤트

$ms_id = get_search_string($_POST['ms_id']);
$show_expected = get_search_string($_POST['show_expected']);

$sql = " select * from recipient_item_msg where ms_id = '$ms_id' and mb_id = '{$member['mb_id']}' ";
$ms = sql_fetch($sql);
if(!$ms['ms_id'])
  json_response(400, '존재하지 않는 메시지입니다.');

if(!($ms['ms_pen_nm'] && $ms['ms_pen_hp']))
  json_response(400, '수급자 정보를 입력해주세요.');

if($msg_point > 0) {
  if($member['mb_point'] < $msg_point)
    json_response(400, '포인트가 부족합니다.');

  // 포인트 차감
  insert_point($member['mb_id'], (-1) * $msg_point, "{$ms['ms_pen_nm']} 수급자에게 품목/정보 메시지 전달");
}

// 전송 로그 작성
$sql = "
  INSERT INTO
    recipient_item_msg_log
  SET
    ms_id = '{$ms_id}',
    ml_sent_at = NOW(),
	agreement_datetime = NOW()
";
sql_query($sql);

$msg_url = "eroumcare.com/shop/item_msg.php?url={$ms['ms_url']}&show_expected={$show_expected}";

$mode = $_POST['mode'];
if($mode === 'alim') {
  // 알림톡 발송
  send_alim_talk('ITEM_MSG_'.$ms_id, $ms['ms_pen_hp'], 'pen_item_msg', "[이로움 장기요양기관 통합관리시스템]\n\n수급자 {$ms['ms_pen_nm']}님이 {$member['mb_entNm']}사업소에 요청하신 제안 품목이 전송되었습니다. 전송된 품목을 확인해주세요\n\n전송 링크 : https://{$msg_url}", array(
    'button' => [
      array(
        'name' => '품목 확인하기',
        'type' => 'WL',
        'url_mobile' => 'https://'.$msg_url
      )
    ]
  ));
} else if($mode === 'sms') {
  // 문자 전송
  include_once(G5_LIB_PATH.'/icode.lms.lib.php');

  $msg = "[이로움 장기요양기관 통합관리시스템]\n\n수급자 {$ms['ms_pen_nm']}님이 {$member['mb_entNm']}사업소에 요청하신 제안 품목이 전송되었습니다. 전송된 품목을 확인해주세요\n\n전송 링크 : https://{$msg_url}";

  $send_hp = '02-830-1301';
  $recv_hp = $ms['ms_pen_hp'];
  
  $send_hp = str_replace('-', '', $send_hp);
  $recv_hp = str_replace('-', '', $recv_hp);

  $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);
  if($port_setting !== false && $recv_hp) {
    $SMS = new LMS;
    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

    $strDest     = array();
    $strDest[]   = $recv_hp;
    $strCallBack = $send_hp;
    $strCaller   = iconv_euckr('이로움');
    $strSubject  = iconv_euckr('[이로움]');
    $strURL      = '';
    $strData     = iconv_euckr($msg);
    $strDate     = '';
    $nCount      = count($strDest);

    $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

    $SMS->Send();
  }
} else {
  json_response(400, '유효하지 않은 요청입니다.');
}

json_response(200, 'OK');
