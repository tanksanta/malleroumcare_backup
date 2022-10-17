<?php
include_once("./_common.php");

if(!$member["mb_id"])
  json_response(400, '접근 권한이 없습니다.');

  /*
$mb_id = $_POST['mb_id'];
$name = $_POST['name'];
$num = $_POST['num'];
sql_query("INSERT INTO `macro_request` SET
    mb_id = '{$mb_id}',
    status = 'W',
    recipient_name = '{$name}',
    recipient_num = '{$num}'
");
*/

$mb_id = $_POST['mb_id'];
$name = $_POST['name'];
$num = $_POST['num'];
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

$default = "
    mb_id = '{$mb_id}',
    status = 'U',
    recipient_name = '{$name}',
    recipient_num = '{$num}',
    birth = '{$birth}',
    grade = '{$grade}',
    type = '{$type}',
    percent = '{$percent}',
    penApplyDtm = '{$penApplyDtm}',
    penExpiDtm = '{$penExpiDtm}',
    rem_amount = '{$rem_amount}'
";

$sql = "INSERT INTO `macro_request` SET ".$update.$default;
sql_query($sql);

error_log(var_export("finding : ".$sql,1));

json_response(200, 'OK', array(
  'sql' => $sql
));
?>
