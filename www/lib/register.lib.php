<?php
if (!defined('_GNUBOARD_')) exit;

function empty_mb_id($reg_mb_id)
{
    if (trim($reg_mb_id)=='')
        return aslang('alert', 'reg_id_empty'); //회원아이디를 입력해 주십시오.
    else
        return "";
}

function valid_mb_id($reg_mb_id)
{
    if (preg_match("/[^0-9a-z_]+/i", $reg_mb_id))
        return aslang('alert', 'reg_id_valid'); //회원아이디는 영문자, 숫자, _ 만 입력하세요.
    else
        return "";
}

function count_mb_id($reg_mb_id)
{
    if (strlen($reg_mb_id) < 3)
        return aslang('alert', 'reg_id_count'); //회원아이디는 최소 3글자 이상 입력하세요.
    else
        return "";
}

function exist_mb_id($reg_mb_id)
{
    global $g5;

    $reg_mb_id = trim($reg_mb_id);
    if ($reg_mb_id == "") return "";

    $sql = " select count(*) as cnt from `{$g5['member_table']}` where mb_id = '$reg_mb_id' ";
    $row = sql_fetch($sql);
    if ($row['cnt'])
        return aslang('alert', 'reg_id_exist'); //이미 사용중인 회원아이디 입니다.
    else
        return "";
}

function reserve_mb_id($reg_mb_id)
{
    global $config;
    if (preg_match("/[\,]?{$reg_mb_id}/i", $config['cf_prohibit_id']))
        return aslang('alert', 'reg_id_reserve'); //이미 예약된 단어로 사용할 수 없는 회원아이디 입니다.
    else
        return "";
}

function empty_mb_nick($reg_mb_nick)
{
    if (!trim($reg_mb_nick))
        return aslang('alert', 'reg_nick_empty'); //닉네임을 입력해 주십시오.
    else
        return "";
}

function valid_mb_nick($reg_mb_nick)
{
    if (!check_string($reg_mb_nick, G5_HANGUL + G5_ALPHABETIC + G5_NUMERIC + G5_SPECIAL + G5_SPACE))
        return aslang('alert', 'reg_nick_valid'); //닉네임은 공백없이 한글, 영문, 숫자만 입력 가능합니다.
    else
        return "";
}

function count_mb_nick($reg_mb_nick)
{
    if (strlen($reg_mb_nick) < 4)
        return aslang('alert', 'reg_nick_count'); //닉네임은 한글 2글자, 영문 4글자 이상 입력 가능합니다.
    else
        return "";
}

function exist_mb_nick($reg_mb_nick, $reg_mb_id)
{
    global $g5;
    $row = sql_fetch(" select count(*) as cnt from {$g5['member_table']} where mb_nick = '$reg_mb_nick' and mb_id <> '$reg_mb_id' ");
    if ($row['cnt'])
        return aslang('alert', 'reg_nick_exist'); //이미 존재하는 닉네임입니다.
    else
        return "";
}

function reserve_mb_nick($reg_mb_nick)
{
    global $config;
    if (preg_match("/[\,]?{$reg_mb_nick}/i", $config['cf_prohibit_id']))
        return aslang('alert', 'reg_nick_reserve'); //이미 예약된 단어로 사용할 수 없는 닉네임 입니다.
    else
        return "";
}

function empty_mb_email($reg_mb_email)
{
    if (!trim($reg_mb_email))
        return aslang('alert', 'reg_email_empty'); //E-mail 주소를 입력해 주십시오.
    else
        return "";
}

function valid_mb_email($reg_mb_email)
{
    if (!preg_match("/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/", $reg_mb_email))
        return aslang('alert', 'reg_email_valid'); //E-mail 주소가 형식에 맞지 않습니다.
    else
        return "";
}

// 금지 메일 도메인 검사
function prohibit_mb_email($reg_mb_email)
{
    global $config;

    list($id, $domain) = explode("@", $reg_mb_email);
    $email_domains = explode("\n", trim($config['cf_prohibit_email']));
    $email_domains = array_map('trim', $email_domains);
    $email_domains = array_map('strtolower', $email_domains);
    $email_domain = strtolower($domain);

    if (in_array($email_domain, $email_domains))
        return aslang('alert', 'reg_email_prohibit', array($domain)); //$domain 메일은 사용할 수 없습니다.

    return "";
}

function exist_mb_email($reg_mb_email, $reg_mb_id)
{
    global $g5;
    $row = sql_fetch(" select count(*) as cnt from `{$g5['member_table']}` where mb_email = '$reg_mb_email' and mb_id <> '$reg_mb_id' ");
    if ($row['cnt'])
        return aslang('alert', 'reg_email_exist'); //이미 사용중인 E-mail 주소입니다.
    else
        return "";
}

function empty_mb_name($reg_mb_name)
{
    if (!trim($reg_mb_name))
        return aslang('alert', 'reg_name_empty'); //이름을 입력해 주십시오.
    else
        return "";
}

function valid_mb_name($mb_name)
{
    if (!check_string($mb_name, G5_HANGUL))
        return aslang('alert', 'reg_name_valid'); //이름은 공백없이 한글만 입력 가능합니다.
    else
        return "";
}

function valid_mb_hp($reg_mb_hp)
{
    $reg_mb_hp = preg_replace("/[^0-9]/", "", $reg_mb_hp);
    if(!$reg_mb_hp)
        return aslang('alert', 'reg_hp_empty'); //휴대폰번호를 입력해 주십시오.
    else {
        if(preg_match("/^01[0-9]{8,9}$/", $reg_mb_hp))
            return "";
        else
            return aslang('alert', 'reg_hp_valid'); //휴대폰번호를 올바르게 입력해 주십시오.
    }
}

function exist_mb_hp($reg_mb_hp, $reg_mb_id)
{
   global $g5;

   if (!trim($reg_mb_hp)) return "";

   $reg_mb_hp = hyphen_hp_number($reg_mb_hp);

   $sql = "select count(*) as cnt from {$g5['member_table']} where mb_hp = '$reg_mb_hp' and mb_id <> '$reg_mb_id' ";
   $row = sql_fetch($sql);

   if($row['cnt'])
       return aslang('alert', 'reg_hp_exist', array($reg_mb_hp)); //이미 사용 중인 휴대폰번호입니다.
   else
       return "";
}

function valid_mb_giup_bnum($reg_mb_giup_bnum)
{
    if(!$reg_mb_giup_bnum)
        return aslang('alert', 'reg_giup_bnum_empty'); //사업자번호를 입력해 주십시오.
    else {
        if(preg_match("/([0-9]{3})-?([0-9]{2})-?([0-9]{5})/", $reg_mb_giup_bnum))
            return "";
        else
            return aslang('alert', 'reg_giup_bnum_valid'); //사업자번호를 올바르게 입력해 주십시오.
    }
}

function exist_mb_giup_bnum($reg_mb_giup_bnum)
{
    global $g5;
    
    $reg_mb_giup_bnum = trim($reg_mb_giup_bnum);
    if ($reg_mb_giup_bnum == "") return "";
    
    $sql = " select count(*) as cnt from `{$g5['member_table']}` where mb_giup_bnum = '$reg_mb_giup_bnum' ";
    $row = sql_fetch($sql);
    if ($row['cnt'])
        return aslang('alert', 'reg_giup_bnum_exist'); //이미 사용 중인 사업자번호 입니다.
    else
        return "";
}

function valid_mb_giup_sbnum($reg_mb_giup_sbnum)
{
    if(!$reg_mb_giup_sbnum)
        return aslang('alert', 'reg_giup_sbnum_empty'); //종사업자번호를 입력해 주십시오.
    else {
        if(preg_match("/(?!0{4})[0-9]{4}/", $reg_mb_giup_sbnum))
            return "";
        else
            return aslang('alert', 'reg_giup_sbnum_valid'); //종사업자번호를 올바르게 입력해 주십시오.
    }
}

function exist_mb_giup_sbnum($reg_mb_giup_bnum, $reg_mb_giup_sbnum)
{
    global $g5;
    
    $reg_mb_giup_bnum = trim($reg_mb_giup_bnum);
    $reg_mb_giup_sbnum = trim($reg_mb_giup_sbnum);
    
    if ($reg_mb_giup_sbnum == "") return "";
    if ($reg_mb_giup_bnum == "" && $reg_mb_giup_sbnum != "") return "올바르지 않은 요청입니다.";
    if (valid_mb_giup_bnum($reg_mb_giup_bnum) != "" && $reg_mb_giup_sbnum != "") return "올바르지 않은 요청입니다.";
    
    $sql = " select count(*) as cnt from `{$g5['member_table']}` where mb_giup_bnum = '$reg_mb_giup_bnum' and mb_giup_sbnum = '$reg_mb_giup_sbnum' ";
    $row = sql_fetch($sql);
    if ($row['cnt'])
        return aslang('alert', 'reg_giup_sbnum_exist'); //이미 사용 중인 종사업자번호 입니다.
    else
        return "";
}
?>