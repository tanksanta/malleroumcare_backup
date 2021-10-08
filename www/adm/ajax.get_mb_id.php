<?php
include_once('./_common.php');

$keyword = str_replace(' ', '', trim($keyword));

$sql = "SELECT
  mb_no as id,
  mb_id,
  mb_name,
  mb_nick,
  mb_email,
  mb_tel,
  mb_hp,
  CONCAT(mb_giup_addr1, mb_giup_addr2) as mb_giup_addr,
  REPLACE(mb_name, ' ', '') as mb_name_no_space,
  REPLACE(mb_nick, ' ', '') as mb_nick_no_space
FROM
  g5_member
WHERE
  (mb_id LIKE '%{$keyword}%') OR 
  (mb_name LIKE '%{$keyword}%') OR 
  (mb_nick LIKE '%{$keyword}%') OR 
  (mb_email LIKE '%{$keyword}%') OR 
  (mb_tel LIKE '%{$keyword}%') OR 
  (mb_hp LIKE '%{$keyword}%') OR
  (REPLACE(mb_name, ' ', '') LIKE '%{$keyword}%') OR
  (REPLACE(mb_nick, ' ', '') LIKE '%{$keyword}%')
";
$result = sql_query($sql);

$rows = array();
while ( $row = sql_fetch_array($result) ) {
    $rows[] = $row;
}

header('Content-type: application/json');
echo json_encode($rows);
?>