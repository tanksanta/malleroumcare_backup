<?php
include('./_common.php');

// 메세지 전송할 때 필요한 포인트
$msg_point = 0; // 메세지 무료 이벤트

if($member['mb_type'] !== 'default')
  json_response(400, '접근할 수 없습니다.');

$ms_pen_id = trim(clean_xss_tags($_POST['ms_pen_id']));
$ms_pen_nm = clean_xss_tags($_POST['ms_pen_nm']);
$ms_pro_yn = clean_xss_tags($_POST['ms_pro_yn']);
$ms_pen_hp = clean_xss_tags($_POST['ms_pen_hp']);

if(!($ms_pen_nm && $ms_pro_yn && $ms_pen_hp))
  json_response(400, '수급자 정보를 입력해주세요.');

// 휴대폰 번호 하이픈 처리
$ms_pen_hp = hyphen_hp_number($ms_pen_hp);

$it_id_arr = $_POST['it_id'];
$it_name_arr = $_POST['it_name'];
$gubun_arr = $_POST['gubun'];

if(!($it_id_arr && $it_name_arr && $gubun_arr))
  json_response(400, '품목을 선택해주세요.');

if($msg_point > 0 && $member['mb_point'] < $msg_point)
  json_response(400, '포인트가 부족합니다.');

// 랜덤 url 생성
list($usec, $sec) = explode(" ", microtime());
$datestr = date("YmdHis", $sec) . substr($usec, 2, 3); //YYYYMMDDHHMMSSSSS
$bytes = random_bytes(16);
$ms_url = hash('sha256', $datestr . bin2hex($bytes));

$sql = "
  INSERT INTO
    recipient_item_msg
  SET
    mb_id = '{$member['mb_id']}',
    ms_pen_id = '{$ms_pen_id}',
    ms_pro_yn = '{$ms_pro_yn}',
    ms_pen_nm = '{$ms_pen_nm}',
    ms_pen_hp = '{$ms_pen_hp}',
    ms_url = '{$ms_url}',
    ms_created_at = NOW(),
    ms_updated_at = NOW()
";
$result = sql_query($sql);
$ms_id = sql_insert_id();

if(!$result)
  json_response(500, '메세지 전송에 실패했습니다. 잠시 후 다시 시도해주세요.');

foreach($it_id_arr as $idx => $it_id) {
  $sql = "
    INSERT INTO
      recipient_item_msg_item
    SET
      ms_id = '{$ms_id}',
      gubun = '{$gubun_arr[$idx]}',
      it_id = '{$it_id}',
      it_name = '{$it_name_arr[$idx]}',
      mi_created_at = NOW()
  ";
  sql_query($sql);
}

// 포인트 차감
if($msg_point > 0)
  insert_point($member['mb_id'], (-1) * $msg_point, "{$ms_pen_nm} 수급자에게 품목/정보 메시지 전달");

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
$msg_url = "eroumcare.com/shop/item_msg.php?url={$ms_url}";
send_alim_talk('ITEM_MSG_'.$ms_id, $ms_pen_hp, 'pen_item_msg', "[이로움 장기요양기관 통합관리시스템]\n\n{$ms_pen_nm}님에게 {$member['mb_entNm']} 사업소에서 추천 품목이 전송되었습니다.\n전송된 품목을 확인해주세요.\n\n전송 링크 : https://{$msg_url}");

json_response(200, 'OK');
