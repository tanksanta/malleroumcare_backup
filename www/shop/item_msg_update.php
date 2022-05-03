<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default')
  json_response(400, '접근할 수 없습니다.');

$ms_pen_id = trim(clean_xss_tags($_POST['ms_pen_id']));
$ms_pen_nm = clean_xss_tags($_POST['ms_pen_nm']);
$ms_pro_yn = clean_xss_tags($_POST['ms_pro_yn']);
$ms_pen_hp = clean_xss_tags($_POST['ms_pen_hp']);
$ms_ent_tel = clean_xss_tags($_POST['ms_ent_tel_new']);
$ms_rec = $_POST['ms_rec'];
$show_expected = $_POST['show_expected'];

if(is_array($ms_rec)) {
  $ms_rec = clean_xss_tags(implode(',', $ms_rec));
} else {
  $ms_rec = '';
}

/* if(!($ms_pen_nm && $ms_pro_yn && $ms_pen_hp))
  json_response(400, '수급자 정보를 입력해주세요.'); */

// 휴대폰 번호 하이픈 처리
$ms_pen_hp = hyphen_hp_number($ms_pen_hp);

$it_id_arr = $_POST['it_id'];
$it_name_arr = $_POST['it_name'];
$gubun_arr = $_POST['gubun'];

if(!($it_id_arr && $it_name_arr && $gubun_arr)) {
  $it_id_arr = $it_name_arr = $gubun_arr = [];
  // json_response(400, '품목을 선택해주세요.');
}

if($pen_type == '1') {
  // 기존 수급자
} else {
  // 신규 수급자
  $ms_pen_id = '';
  $ms_pro_yn = 'N';
}

if($w == 'u') {
  // 수정

  $ms_id = get_search_string($_POST['ms_id']);
  if(!$ms_id)
    json_response(400, '유효하지 않은 요청입니다.');

  $sql = "
    SELECT * FROM
      recipient_item_msg
    WHERE
      ms_id = '{$ms_id}' and
      mb_id = '{$member['mb_id']}'
  ";
  $ms = sql_fetch($sql);

  if(!$ms['ms_id'])
    json_response(400, '저장할 메시지를 찾을 수 없습니다.');
  
  $ms_url = $ms['ms_url'];

  $sql = "
    UPDATE
      recipient_item_msg
    SET
      ms_pen_id = '{$ms_pen_id}',
      ms_pro_yn = '{$ms_pro_yn}',
      ms_pen_nm = '{$ms_pen_nm}',
      ms_pen_hp = '{$ms_pen_hp}',
      ms_ent_tel = '{$ms_ent_tel}',
      ms_rec = '{$ms_rec}',
      show_expected = '{$show_expected}',
      ms_updated_at = NOW()
    WHERE
      ms_id = '{$ms_id}' and
      mb_id = '{$member['mb_id']}'
  ";
  $result = sql_query($sql);

  if(!$result)
    json_response(500, '메세지 저장에 실패했습니다. 잠시 후 다시 시도해주세요.');

  $no_items = $_POST['no_items'];
  if(!$no_items) {
    // 먼저 품목 삭제
    $sql = "
      DELETE FROM
        recipient_item_msg_item
      WHERE
        ms_id = '{$ms_id}'
    ";
    sql_query($sql);

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
  }
} else {
  // 작성

  // 랜덤 url 생성
  list($usec, $sec) = explode(" ", microtime());
  $datestr = date("YmdHis", $sec) . substr($usec, 2, 3); //YYYYMMDDHHMMSSSSS
  $bytes = random_bytes(16);
  $ms_url = base64_encode(hash('sha256', $datestr . bin2hex($bytes), true));
  $ms_url = str_replace(['+', '/', '='], ['-', '_', ''], $ms_url);

  $sql = "
    INSERT INTO
      recipient_item_msg
    SET
      mb_id = '{$member['mb_id']}',
      ms_pen_id = '{$ms_pen_id}',
      ms_pro_yn = '{$ms_pro_yn}',
      ms_pen_nm = '{$ms_pen_nm}',
      ms_pen_hp = '{$ms_pen_hp}',
      ms_ent_tel = '{$ms_ent_tel}',
      ms_rec = '{$ms_rec}',
      ms_url = '{$ms_url}',
      show_expected = '{$show_expected}',
      ms_created_at = NOW(),
      ms_updated_at = NOW()
  ";
  $result = sql_query($sql);
  $ms_id = sql_insert_id();

  if(!$result)
    json_response(500, '메세지 저장에 실패했습니다. 잠시 후 다시 시도해주세요.');

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
}
json_response(200, 'OK', [
  'ms_id' => $ms_id,
  'ms_url' => $ms_url
]);
