<?php
include_once('./_common.php');

if(!$member["mb_id"] || !$member["mb_entId"])
  json_response(400, '먼저 로그인하세요.');

if($_POST['penProTypeCd'] == '02') { // 보호자 : 요양보호사
	$_POST['penProRel'] = '11'; // 관계 : 직접입력
} else if($_POST['penProTypeCd'] == '00') { // 보호자 : 없음
	$_POST['penProNm'] = '';
	$_POST['penProBirth'] = '';
	$_POST['penProConNum'] = '';
	$_POST['penProConPnum'] = '';
	$_POST['penProEmail'] = '';
	$_POST['penProZip'] = '';
	$_POST['penProAddr'] = '';
	$_POST['penProAddrDtl'] = '';
}

$data = $_POST;
$data['entId'] = $member["mb_entId"];
$data['usrId'] = $member["mb_id"];
$data['delYn'] = 'N';

# 현재 예비수급자인지 체크
$is_spare_current = $data['isSpare'] == '1';
# 예비수급자로 변경할것인지 체크
$is_spare = $data['penSpare'] == '1';

if ($data['update_type'] != 'grade_edit' && $valid = valid_recipient_input($data, $is_spare)) {
  json_response(400, $valid);
}

$data = normalize_recipient_input($data);

if($is_spare_current != $is_spare) {
  // 수급자 <-> 예비수급자 변경이 일어났다면
  $delete_url = EROUMCARE_API_RECIPIENT_UPDATE;
  $insert_url = EROUMCARE_API_SPARE_RECIPIENT_INSERT;
  if($is_spare_current) {
    // 기존에 예비수급자였으면 예비수급자를 삭제하고 수급자로 등록해야함
    $delete_url = EROUMCARE_API_SPARE_RECIPIENT_UPDATE;
    $insert_url = EROUMCARE_API_RECIPIENT_INSERT;
  }
  $res = api_post_call($delete_url, array(
    'penId' => $data['penId'],
    'entId' => $member["mb_entId"],
    'usrId' => $member["mb_id"],
    'delYn' => 'Y'
  ));
  $res = api_post_call($insert_url, $data);
} else {
  if($is_spare)
    $res = api_post_call(EROUMCARE_API_SPARE_RECIPIENT_UPDATE, $data);
  else
    $res = api_post_call(EROUMCARE_API_RECIPIENT_UPDATE, $data);
}

if(!$res || $res['errorYN'] != 'N')
  json_response(500, $res['message'] ?: '시스템서버가 응답하지 않습니다.');

// 보호자
if (!$_POST['pros']) {
  json_response(400, '보호자 정보가 없습니다.');
}

$pros = $_POST['pros'];
foreach($pros as $pro) {
	foreach($pro as $key => $val) {
		$pro[$key] = clean_xss_tags($val);
	}

	if($pro['pro_type'] == '02') {
		$pro['pro_rel_type'] == '11';
	}

  if($pro['pro_id']) {
    if($pro['deleted']) {
      // 삭제
      $sql = "
        DELETE FROM
          recipient_protector
        WHERE
          mb_id = '{$member['mb_id']}' and
          pro_id = '{$pro['pro_id']}'
      ";
    } else {
      // 수정
      $sql = "
        UPDATE
          recipient_protector
        SET
          pro_name = '{$pro['pro_name']}',
          pro_type = '{$pro['pro_type']}',
          pro_rel_type = '{$pro['pro_rel_type']}',
          pro_rel = '{$pro['pro_rel']}',
          pro_birth = '{$pro['pro_birth']}',
          pro_email = '{$pro['pro_email']}',
          pro_hp = '{$pro['pro_hp']}',
          pro_tel = '{$pro['pro_tel']}',
          pro_zip = '{$pro['pro_zip']}',
          pro_addr1 = '{$pro['pro_addr1']}',
          pro_addr2 = '{$pro['pro_addr2']}'
        WHERE
          mb_id = '{$member['mb_id']}' and
          pro_id = '{$pro['pro_id']}'
      ";
    }

    sql_query($sql);
  } else {
    // 추가
    $sql = "
      INSERT INTO
        recipient_protector
      SET
        mb_id = '{$member['mb_id']}',
        penId = '{$data['penId']}',
        pro_name = '{$pro['pro_name']}',
        pro_type = '{$pro['pro_type']}',
        pro_rel_type = '{$pro['pro_rel_type']}',
        pro_rel = '{$pro['pro_rel']}',
        pro_birth = '{$pro['pro_birth']}',
        pro_email = '{$pro['pro_email']}',
        pro_hp = '{$pro['pro_hp']}',
        pro_tel = '{$pro['pro_tel']}',
        pro_zip = '{$pro['pro_zip']}',
        pro_addr1 = '{$pro['pro_addr1']}',
        pro_addr2 = '{$pro['pro_addr2']}'
    ";

    sql_query($sql);
  }
}
json_response(200, 'OK', array(
  'penId' => $data['penId'],
  'isSpare' => $is_spare
));
?>
