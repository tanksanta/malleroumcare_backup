<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  json_response(400, '파트너 회원만 접근가능합니다.');

$ct_id = get_search_string($_POST['ct_id']);
$type = $_POST['type'];
$m = $_POST['m'];

if(!$ct_id || !$type || !$m)
  json_response(400, '유효하지 않은 요청입니다.');

$report = sql_fetch("
  SELECT * FROM partner_install_report
  WHERE ct_id = '{$ct_id}' and mb_id = '{$member['mb_id']}'
");
if(!$report || !$report['ct_id'])
  json_response(400, '설치보고서가 존재하지 않습니다.');

# 이미지 파일 경로
$img_dir = G5_DATA_PATH.'/partner/img';
if(!is_dir($img_dir)) {
  @mkdir($img_dir, G5_DIR_PERMISSION, true);
  @chmod($img_dir, G5_DIR_PERMISSION);
}

function img_file_name() {
  global $ct_id, $type, $member;

  $file_name = [];
  $file_name[] = $ct_id;
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
      WHERE ct_id = '{$ct_id}' and mb_id = '{$member['mb_id']}'
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
      WHERE ct_id = '{$ct_id}' and mb_id = '{$member['mb_id']}'
    ");
    if(!$result)
      json_response(500, 'DB 서버 오류 발생');
    
    $return = $src_name;
  }
}

else if($type == 'photo') {
  # 설치사진

  if($m == 'd') {
    # 파일 삭제

    $ip_id = get_search_string($_POST['ip_id']);
    $photo = sql_fetch("
      SELECT * FROM partner_install_photo
      WHERE ip_id = '{$ip_id}' and ct_id = '{$ct_id}' and mb_id = '{$member['mb_id']}'
    ");

    if(!$photo || !$photo['ip_id'])
      json_response(400, '존재하지 않는 파일입니다.');

    @unlink($img_dir.'/'.$photo['ip_photo_url']);
    $result = sql_query("
      DELETE FROM partner_install_photo
      WHERE ip_id = '{$ip_id}' and ct_id = '{$ct_id}' and mb_id = '{$member['mb_id']}'
    ");
    if(!$result)
      json_response(500, 'DB 서버 오류 발생');
  }

  else if($m == 'u') {
    # 파일 업로드

    function re_array_files($arr) {
      foreach( $arr as $key => $all ){
          foreach( $all as $i => $val ){
              $new[$i][$key] = $val;   
          }   
      }
      return $new;
    }

    $photos = $_FILES['file_photo'] ? re_array_files($_FILES['file_photo']) : [];
    foreach($photos as $photo) {
      if(!$photo['name']) continue;
      $src_name = get_search_string($photo['name']);
      $dest_name = img_file_name();
      if(!$src_name) $src_name = $dest_name;
      upload_file($photo['tmp_name'], $dest_name, $img_dir);

      $result = sql_query("
        INSERT INTO
          partner_install_photo
        SET
          ct_id = '{$ct_id}',
          mb_id = '{$member['mb_id']}',
          ip_photo_name = '{$src_name}',
          ip_photo_url = '{$dest_name}',
          ip_created_at = NOW()
      ");
      if(!$result) {
        @unlink($img_dir.'/'.$dest_name);
        json_response(500, 'DB 서버 오류 발생');
      }
    }

    $return = [];
    $return_result = sql_query("
      SELECT * FROM partner_install_photo
      WHERE ct_id = '{$ct_id}' and mb_id = '{$member['mb_id']}'
      ORDER BY ip_id ASC
    ");
    while($row = sql_fetch_array($return_result)) {
      $return[] = $row;
    }
  }
}

json_response(200, 'OK', $return);
?>
