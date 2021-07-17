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
$data['appCd'] = '01';
$data['delYn'] = 'N';

# 예비수급자인지 체크
$is_spare = $data['penSpare'] == '1';

if($valid = valid_recipient_input($data, $is_spare)) {
  json_response(500, $valid);
}
$data = normalize_recipient_input($data);

if($is_spare)
  $res = api_post_call(EROUMCARE_API_SPARE_RECIPIENT_INSERT, $data);
else
  $res = api_post_call(EROUMCARE_API_RECIPIENT_INSERT, $data);

if(!$res || $res['errorYN'] != 'N')
  json_response(500, $res['message'] ?: '시스템서버가 응답하지 않습니다.');

// 등급 로그 추가
$pen_gra_edit_dtm = $data['penExpiStDtm'];
$pen_gra_apply_month = substr($data['penExpiStDtm'], 5, 2);
$pen_gra_apply_day = substr($data['penExpiStDtm'], 8, 2);

$sql = "INSERT INTO
					recipient_grade_log
				SET
					pen_id = '{$res['data']['penId']}',
					pen_rec_gra_cd = '{$data['penRecGraCd']}',
					pen_rec_gra_nm = '{$data['penRecGraNm']}',
					pen_type_cd = '{$data['penTypeCd']}',
					pen_type_nm = '{$data['penTypeNm']}',
					pen_gra_edit_dtm = '{$pen_gra_edit_dtm}',
					pen_gra_apply_month = '{$pen_gra_apply_month}',
					pen_gra_apply_day = '{$pen_gra_apply_day}',
					created_by = '{$member['mb_id']}' ";
sql_query($sql);

json_response(200, 'OK', array(
  'penId' => $res['data']['penId'],
  'isSpare' => $is_spare
));
?>
