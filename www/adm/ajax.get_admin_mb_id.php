<?php
include_once('./_common.php');

if ($is_admin != 'super')
  json_response(400, '최고관리자만 접근 가능합니다.');

$sql = "SELECT mb_id, mb_name
FROM
  g5_member
WHERE
  (
    (mb_id LIKE '%{$keyword}%') OR 
    (mb_name LIKE '%{$keyword}%')
  ) AND
  mb_level >= 9
";
$result = sql_query($sql);

$rows = array();
while ( $row = sql_fetch_array($result) ) {
  $rows[] = $row;
}

header('Content-type: application/json');
echo json_encode($rows);
?>
