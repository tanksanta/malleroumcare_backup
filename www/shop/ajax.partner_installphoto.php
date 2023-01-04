<?php
include_once('./_common.php');

if(!$is_samhwa_partner && !$is_admin)
  json_response(400, '파트너 회원만 접근가능합니다.');


if (!$is_admin) {
  $check_member = "and mb_id = '{$member['mb_id']}'";
}

$od_id = get_search_string($_POST['od_id']);
$type = $_POST['type'];
$img_type = $_POST['img_type'];
$m = $_POST['m'];

if(!$od_id || !$type || !$m)
  json_response(400, '유효하지 않은 요청입니다.');

$report = sql_fetch("
  SELECT * FROM partner_install_report
  WHERE od_id = '{$od_id}' {$check_member}
");
if(!$report || !$report['od_id'])
  json_response(400, '설치보고서가 존재하지 않습니다.');

# 이미지 파일 경로
$img_dir = G5_DATA_PATH.'/partner/img';
if(!is_dir($img_dir)) {
  @mkdir($img_dir, G5_DIR_PERMISSION, true);
  @chmod($img_dir, G5_DIR_PERMISSION);
}

function img_file_name($ext_name) {
  global $od_id, $type, $member;

  $file_name = [];
  $file_name[] = $od_id;
  $file_name[] = $type;
  $file_name[] = $member['mb_id'];
  $file_name[] = round(microtime(true) * 1000);
  $file_name[] = bin2hex(random_bytes(5));
  $file_name = implode('_', $file_name);

  if ($ext_name !== "") {
    return $file_name.".".$ext_name;
  } else {
    return $file_name;
  }
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
      WHERE od_id = '{$od_id}' {$check_member}
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
    $src_ext_name = "";
    if (substr_count($src_name, ".") > 0) {
      $src_ext_name = explode('.', $src_name);
      if (count($src_ext_name) > 1) {
        $src_ext_name = end($src_ext_name);
      }
    }
    $dest_name = img_file_name($src_ext_name);
    if(!$src_name) $src_name = $dest_name;
    upload_file($file, $dest_name, $img_dir);
  
    $result = sql_query("
      UPDATE partner_install_report
      SET ir_cert_name = '{$src_name}', ir_cert_url = '{$dest_name}', ir_updated_at = NOW()
      WHERE od_id = '{$od_id}' {$check_member}
    ");
    if(!$result)
      json_response(500, 'DB 서버 오류 발생');
    
    $return = array(
      'name' => $src_name,
      'url' => $dest_name
    );
  }
}

else if($type == 'photo') {
  # 설치사진

  if($m == 'd') {
    # 파일 삭제

    $ip_id = get_search_string($_POST['ip_id']);
    $photo = sql_fetch("
      SELECT * FROM partner_install_photo
      WHERE ip_id = '{$ip_id}' and od_id = '{$od_id}' {$check_member}
    ");

    if(!$photo || !$photo['ip_id'])
      json_response(400, '존재하지 않는 파일입니다.');

    @unlink($img_dir.'/'.$photo['ip_photo_url']);
    $result = sql_query("
      DELETE FROM partner_install_photo
      WHERE ip_id = '{$ip_id}' and od_id = '{$od_id}' {$check_member}
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

    if ($img_type == "추가사진") {
      $photos = $_FILES['file_photo4'] ? re_array_files($_FILES['file_photo4']) : [];
    } else if ($img_type == "설치ㆍ회수ㆍ소독확인서") {
      $photos = $_FILES['file_photo3'] ? re_array_files($_FILES['file_photo3']) : [];
    } else if ($img_type == "실물바코드사진") {
      $photos = $_FILES['file_photo2'] ? re_array_files($_FILES['file_photo2']) : [];
    } else {
      $photos = $_FILES['file_photo1'] ? re_array_files($_FILES['file_photo1']) : [];
    }
    
    foreach($photos as $photo) {
      if(!$photo['name']) continue;
      $src_name = get_search_string($photo['name']);
      $src_ext_name = "";
      if (substr_count($src_name, ".") > 0) {
        $src_ext_name = explode('.', $src_name);
        if (count($src_ext_name) > 1) {
          $src_ext_name = end($src_ext_name);
        }
      }
      $dest_name = img_file_name($src_ext_name);
      if(!$src_name) $src_name = $dest_name;
      upload_file($photo['tmp_name'], $dest_name, $img_dir);

      $result = sql_query("
        INSERT INTO
          partner_install_photo
        SET
          od_id = '{$od_id}',
          mb_id = '{$report['mb_id']}',
          ip_photo_name = '{$src_name}',
          img_type = '{$img_type}',
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
      WHERE od_id = '{$od_id}' {$check_member}
      AND img_type = '{$img_type}'
      ORDER BY ip_id ASC
    ");
    while($row = sql_fetch_array($return_result)) {
      $return[] = $row;
    }
  }
}

json_response(200, 'OK', $return);
?>