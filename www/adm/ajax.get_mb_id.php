<?php
include_once('./_common.php');

$sql = "SELECT mb_no as id, mb_id, mb_name, mb_nick, mb_email, mb_tel, mb_hp 
FROM
  g5_member
WHERE
  (mb_id LIKE '%{$keyword}%') OR 
  (mb_name LIKE '%{$keyword}%') OR 
  (mb_nick LIKE '%{$keyword}%') OR 
  (mb_email LIKE '%{$keyword}%') OR 
  (mb_tel LIKE '%{$keyword}%') OR 
  (mb_hp LIKE '%{$keyword}%')
";
$result = sql_query($sql);

$rows = array();
while ( $row = sql_fetch_array($result) ) {
    $rows[] = $row;
}

header('Content-type: application/json');
echo json_encode($rows);
?>