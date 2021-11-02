<?php
$sub_menu = "777106";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

auth_check($auth[$sub_menu], 'w');

//check_admin_token();

$dt_no = (int)$_POST['dt_no'];

$sql_common = "  
                 dt_domain = '{$_POST['dt_domain']}',
                 dt_thema = '{$_POST['dt_thema']}',
                 dt_key = '{$_POST['dt_key']}',
                 dt_memo = '{$_POST['dt_memo']}'
                 ";

if ($w == '')
{
    sql_query(" insert into g5_domain_thema set {$sql_common} ");
    $dt_no = sql_insert_id();
}
else if ($w == 'u')
{
    $sql = " update g5_domain_thema
                set {$sql_common} 
                where dt_no = '{$dt_no}' ";
    sql_query($sql);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

goto_url('./domain_thema_list_form.php?'.$qstr.'&amp;w=u&amp;dt_no='.$dt_no, false);
?>