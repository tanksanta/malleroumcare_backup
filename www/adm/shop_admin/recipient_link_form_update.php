<?php
$sub_menu = "500050";
include_once("./_common.php");
if ($w == 'u')
  check_demo();

auth_check($auth[$sub_menu], 'w');

// check_admin_token();

$rl_pen_zip1 = substr($_POST['rl_zip'], 0, 3);
$rl_pen_zip2 = substr($_POST['rl_zip'], 3);
$rl_pen_name = isset($_POST['rl_pen_name']) ? trim(strip_tags($rl_pen_name)) : '';

$mb_email = isset($_POST['mb_email']) ? get_email_address(trim($_POST['mb_email'])) : '';

$rl_ltm = $recipient === 'on' ? trim($rl_ltm) : null;

$rl_pen_hp = $_POST['rl_pen_hp1']."-".$_POST['rl_pen_hp2']."-".$_POST['rl_pen_hp3'];
$rl_pen_pro_hp = $_POST['rl_pen_pro_hp1']."-".$_POST['rl_pen_pro_hp2']."-".$_POST['rl_pen_pro_hp3'];

if ($rl_pen_pro_type != '11') {
  $rl_pen_pro_type_etc = '';
}

$sql_common = "
  rl_pen_name = '{$rl_pen_name}',
  rl_pen_hp = '{$rl_pen_hp}',
  rl_pen_addr1 = '{$rl_pen_addr1}',
  rl_pen_addr2 = '{$rl_pen_addr2}',
  rl_pen_addr3 = '{$rl_pen_addr3}',
  rl_pen_addr_jibeon = '{$rl_pen_addr_jibeon}',
  rl_pen_zip1 = '{$rl_pen_zip1}',
  rl_pen_zip2 = '{$rl_pen_zip2}',
  rl_pen_pro_type = '{$rl_pen_pro_type}',
  rl_pen_pro_type_etc = '{$rl_pen_pro_type_etc}',
  rl_pen_pro_name = '{$rl_pen_pro_name}',
  rl_pen_pro_hp = '{$rl_pen_pro_hp}',
  rl_request = '{$rl_request}', 
";

// 수급자 번호 없으면 예비수급자
if ($rl_ltm) {
  $sql_common .= "rl_ltm = 'L{$rl_ltm}'";
} else {
  $sql_common .= "rl_ltm = NULL";
}

if ($w == '')
{   
  sql_query("INSERT INTO recipient_link
    SET
      {$sql_common}
  ");

  $rl_id = sql_insert_id();
}
else if ($w == 'u')
{
  
  $sql = "SELECT * FROM recipient_link WHERE rl_id = '{$rl_id}'";
  $rl = sql_fetch($sql);

  if (!$rl['rl_id'])
    alert('존재하지 않는 수급자입니다.');

  sql_query("UPDATE recipient_link SET
      {$sql_common}
      , rl_updated_at = now()
    WHERE rl_id = '{$rl_id}'
  ");
}

alert('저장되었습니다.', './recipient_link_form.php?'.$qstr.'&amp;w=u&amp;rl_id='.$rl_id, false);
