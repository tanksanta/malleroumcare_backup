<?php
include_once('./_common.php');

$sql = "SELECT * FROM g5_firebase_push WHERE fp_state = 0 AND fp_date < now() ORDER BY fp_id ASC";
$result = sql_query($sql);

while($row = sql_fetch_array($result)) {

  // get fcm tokens
  $ids = json_decode($row['fp_ids']);

  // get fcm tokens by mb_id
  $mb_ids = json_decode($row['fp_mb_ids']);
  foreach($mb_ids as $mb_id) {
    $tmp_ids = get_token_by_id($mb_id);
    $ids = array_merge($ids, $tmp_ids);
    sort($ids);
  }

  $ids = array_unique($ids);

  $notification = array(
    "body" => $row['fp_body'],
    "title" => $row['fp_title'],
  );

  // print_r2($row);

  if ($row['fp_link']) {
    $fp_response = send_notification_link($ids, $notification, $row['fp_link']);
  } else {
    $fp_response = send_notification($ids, $notification);
  }

  try {
    $result_arr = json_decode($fp_response, true);
    // print_r2($result_arr);

    if (!$result_arr['success']) {
      throw new Exception('no success');
    }

    $fp_state = 1;
  }catch(Exception $e) {
    echo 'Message: ' .$e->getMessage();
    $fp_state = 2;
  }

  $sql = "UPDATE g5_firebase_push SET fp_state = '{$fp_state}', fp_response = '{$fp_response}' WHERE fp_id = '{$row['fp_id']}'";
  // echo $sql;
  sql_query($sql);
}

json_response(200, 'OK');