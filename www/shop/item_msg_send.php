<?php
include_once('./_common.php');

// 메세지 전송할 때 필요한 포인트
$msg_point = 10; // 메세지 무료 이벤트

$ms_id = get_search_string($_POST['ms_id']);

$sql = " select * from recipient_item_msg where ms_id = '$ms_id' and mb_id = '{$member['mb_id']}' ";
$ms = sql_fetch($sql);
if(!$ms['ms_id'])
  json_response(400, '존재하지 않는 메시지입니다.');

if($msg_point > 0 && $member['mb_point'] < $msg_point)
  json_response(400, '포인트가 부족합니다.');

// 포인트 차감
if($msg_point > 0)
  insert_point($member['mb_id'], (-1) * $msg_point, "{$ms['ms_pen_nm']} 수급자에게 품목/정보 메시지 전달");

// 전송 로그 작성
$sql = "
  INSERT INTO
    recipient_item_msg_log
  SET
    ms_id = '{$ms_id}',
    ml_sent_at = NOW()
";
sql_query($sql);

// 알림톡 발송
$msg_url = "eroumcare.com/shop/item_msg.php?url={$ms['ms_url']}";
send_alim_talk('ITEM_MSG_'.$ms_id, $ms['ms_pen_hp'], 'pen_item_msg', "[이로움 장기요양기관 통합관리시스템]\n\n{$ms['ms_pen_nm']}님에게 {$member['mb_entNm']} 사업소에서 추천 품목이 전송되었습니다.\n전송된 품목을 확인해주세요.\n\n전송 링크 : https://{$msg_url}", array(
  'button' => [
    array(
      'name' => '품목 확인하기',
      'type' => 'WL',
      'url_mobile' => 'https://'.$msg_url
    )
  ]
));

json_response(200, 'OK');
