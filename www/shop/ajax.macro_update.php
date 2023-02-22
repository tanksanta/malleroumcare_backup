<?php
include_once("./_common.php");

$mb_id = $_POST['mb_id'];
$recipient_name = $_POST['recipient_name'];
$recipient_num = $_POST['recipient_num'];

// $check_macro_req_sql = "SELECT * from macro_request
//                         WHERE mb_id = '{$mb_id}' AND 
//                         recipient_name = '{$recipient_name}' AND 
//                         recipient_num = '{$recipient_num}';"; 
// $res_check = sql_query($check_macro_req_sql);

// if(sql_num_rows($res_check) != 0){
//     $update_status = "UPDATE macro_request
//                     SET status = 'R', updated_at = NOW()
//                     WHERE mb_id = '{$mb_id}' AND 
//                         recipient_name = '{$recipient_name}' AND 
//                         recipient_num = '{$recipient_num}';";
//     sql_query($update_status);
// }
$sql_select = "SELECT count(*) as cnt from macro_request where mb_id = '{$mb_id}' and recipient_name = '{$recipient_name}' and recipient_num = '{$recipient_num}';";
$res_select = sql_fetch($sql_select);
if($res_select['cnt'] == 0){
    json_response(400, 'no data');
}

if($_POST['status'] != null && $_POST['status'] == "search"){
	$birth = $_POST['birth'];
	$grade = $_POST['grade'];
	$type = $_POST['type'];
	$percent = $_POST['percent'];
	$penApplyDtm = $_POST['penApplyDtm'];
	$penExpiDtm = $_POST['penExpiDtm'];
	$rem_amount = $_POST['rem_amount'];
	$item_data = $_POST['item_data'];
	$update = "";

	for($i = 0; $i < sizeof(array_keys($item_data)); $i++){
	  if(array_values($item_data)[$i] == -1){
		$update = $update.array_keys($item_data)[$i]." = '".array_values($item_data)[$i]."', ";
	  }
	}

    $sql_update = "UPDATE macro_request SET ".$update." birth = '{$birth}',
    grade = '{$grade}',
    type = '{$type}',
    percent = '{$percent}',
    penApplyDtm = '{$penApplyDtm}',
    penExpiDtm = '{$penExpiDtm}',
    rem_amount = '{$rem_amount}', updated_at = now() WHERE mb_id = '{$mb_id}' and recipient_name = '{$recipient_name}' and recipient_num = '{$recipient_num}';";
} else {
    $sql_update = "UPDATE macro_request SET updated_at = now(), status = 'R' WHERE mb_id = '{$mb_id}' and recipient_name = '{$recipient_name}' and recipient_num = '{$recipient_num}';";
}
sql_query($sql_update);

json_response(200, 'OK');

// # 이미지 파일 경로
// $img_dir = G5_DATA_PATH.'/person/img';
// if(!is_dir($img_dir)) {
//     @mkdir($img_dir, G5_DIR_PERMISSION, true);
//     @chmod($img_dir, G5_DIR_PERMISSION);
// }

// function img_file_name() {
//     global $ct_id;

//     $file_name = [];
//     $file_name[] = $ct_id;
//     $file_name[] = round(microtime(true) * 1000);
//     $file_name[] = bin2hex(random_bytes(5));

//     return implode('_', $file_name);
// }

// function re_array_files($arr) {
//     foreach( $arr as $key => $all ){
//         foreach( $all as $i => $val ){
//             $new[$i][$key] = $val;   
//         }   
//     }
//     return $new;
// }

// $result = sql_query("
//     UPDATE macro_request
//     SET status = 'D', birth = '{$birth}', grade = '{$grade}', type = '{$type}', percent = '{$percent}', updated_at = NOW()
//     WHERE id = '{$ct_id}'
// ");

// if(!$result)
//     json_response(500, 'DB 서버 오류 발생');

// $photos = $_FILES['file_photo'] ? re_array_files($_FILES['file_photo']) : [];
// foreach($photos as $photo) {
//     if(!$photo['name']) 
//         continue;

//     $src_name = get_search_string($photo['name']);
//     $dest_name = img_file_name();
//     if(!$src_name) 
//         $src_name = $dest_name;
        
//     upload_file($photo['tmp_name'], $dest_name, $img_dir);

//     $result = sql_query("
//         INSERT INTO
//             macro_request_image
//         SET
//             id = '{$ct_id}',
//             image_name = '{$src_name}',
//             image_url = '{$dest_name}',
//             regdt = NOW()
//     ");
//     if(!$result) {
//         @unlink($img_dir.'/'.$dest_name);
//         json_response(500, 'DB 서버 오류 발생');
//     }
// }       

// json_response(200, 'OK');
?>
