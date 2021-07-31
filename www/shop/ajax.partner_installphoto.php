<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  json_response(400, '파트너 회원만 접근가능합니다.');

$od_id = get_search_string($_POST['od_id']);
$type = $_POST['type'];
$m = $_POST['m'];

if(!$od_id || !$type || !$m)
  json_response(400, '유효하지 않은 요청입니다.');

$check_result = sql_fetch("
  SELECT od_id FROM {$g5['g5_shop_cart_table']}
  WHERE od_id = '{$od_id}' and ct_direct_delivery_partner = '{$member['mb_id']}'
  LIMIT 1
");
if(!$check_result['od_id'])
  json_response(400, '존재하지 않는 주문입니다.');

$report = sql_fetch("
  SELECT * FROM partner_install_report
  WHERE od_id = '{$od_id}' and mb_id = '{$member['mb_id']}'
");
if(!$report || !$report['od_id'])
  json_response(400, '설치보고서가 존재하지 않습니다.');

# 이미지 파일 경로
$img_dir = G5_DATA_PATH.'/partner/img';
if(!is_dir($img_dir)) {
  @mkdir($img_dir, G5_DIR_PERMISSION, true);
  @chmod($img_dir, G5_DIR_PERMISSION);
}

function img_file_name() {
  global $od_id, $type, $member;

  $file_name = [];
  $file_name[] = $od_id;
  $file_name[] = $type;
  $file_name[] = $member['mb_id'];
  $file_name[] = round(microtime(true) * 1000);
  $file_name[] = bin2hex(random_bytes(5));

  return implode('_', $file_name);
}

$return = null;

if($type == 'cert') {
  # 설치확인서

  if($m == 'd') {
    # 파일 삭제

    if(!$report['ir_cert_url'])
      json_response(400, '존재하지 않는 파일입니다.');

    @unlink($img_dir.'/'.$report['ir_cert_url']);
    $result = sql_query("
      UPDATE partner_install_report
      SET ir_cert_name = '', ir_cert_url = '', ir_updated_at = NOW()
      WHERE od_id = '{$od_id}' and mb_id = '{$member['mb_id']}'
    ");
    if(!$result)
      json_response(500, 'DB 서버 오류 발생');
  }

  else if($m == 'u') {
    # 파일 업로드
    
    if($report['ir_cert_url'])
      json_response(400, "이미 설치확인서 파일이 존재합니다.\n기존 파일 삭제 후 다시 등록해주세요.");

    $file = $_FILES['file_cert']['tmp_name'];
    if(!$file)
      json_response(400, '설치확인서 파일을 등록해주세요.');
    $src_name = get_search_string($_FILES['file_cert']['name']);
    $dest_name = img_file_name();
    if(!$src_name) $src_name = $dest_name;
    upload_file($file, $dest_name, $img_dir);
  
    $result = sql_query("
      UPDATE partner_install_report
      SET ir_cert_name = '{$src_name}', ir_cert_url = '{$dest_name}', ir_updated_at = NOW()
      WHERE od_id = '{$od_id}' and mb_id = '{$member['mb_id']}'
    ");
    if(!$result)
      json_response(500, 'DB 서버 오류 발생');
    
    $return = $src_name;
  }
}

else if($type == 'photo') {
  # 설치사진
}

json_response(200, 'OK', $return);
?>
