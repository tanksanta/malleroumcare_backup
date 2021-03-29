<?php
header('Content-Type: text/html; charset=UTF-8');
include_once('./_common.php');
$key = "MTA1NzQz";
$sel_member="select member_seq,group_seq,AES_DECRYPT(UNHEX(email),'{$key}') as email,AES_DECRYPT(UNHEX(phone),'{$key}') as phone,AES_DECRYPT(UNHEX(cellphone),'{$key}') as cellphone,zipcode,userid,password,user_name,address,address_street,address_detail,regist_date,nickname,ccode,login_addr,mtype from fm_member order by member_seq";
$result = sql_query($sel_member);
while($row = sql_fetch_array($result)){
    //암호화 확인필요
    $email  = $row['email'];
    $phone  = $row['phone'];
    $cellphone  = $row['cellphone'];

    $biniess_mem_row = sql_fetch("SELECT bzipcode,bname,bceo,bphone,bcellphone,bno,bitem,bstatus,baddress,baddress_detail,baddress_street,bemail FROM fm_member_business WHERE member_seq='".$row['member_seq']."'");
	$person_mem_row = sql_fetch("SELECT name,part,phone,mobile,duty,email FROM fm_member_person WHERE psno='".$row['member_seq']."'");

    $zipcode1  = substr($row['zipcode'],0,3);
    $zipcode2  = substr($row['zipcode'],3,2);

    $bzipcode1 = substr($biniess_mem_row['bzipcode'],0,3);
    $bzipcode2 = substr($biniess_mem_row['bzipcode'],3,2);

	$address   = $row['address'];
	$address_street   = $row['address_street'];
	$address_detail   = $row['address_detail'];

	$bemail = "";

	$name = $row['user_name'];

	if($row['mtype']=="business"){
		$name = $biniess_mem_row['bname'];
     	$bemail = ($biniess_mem_row['bemail'])?$biniess_mem_row['bemail']:$email;
		$phone = $biniess_mem_row['bphone'];
		$cellphone = $biniess_mem_row['bcellphone'];
		$zipcode1  = $bzipcode1;
		$zipcode2  = $bzipcode2;
		$address   = $biniess_mem_row['baddress'];
		$address_street   = $biniess_mem_row['baddress_street'];
		$address_detail   = $biniess_mem_row['baddress_detail'];
	}

    $in_sql = "insert into g5_member
               set mb_id='".$row['userid']."',
	           mb_password='".$row['password']."',
	           mb_level='".$row['group_seq']."',
			   mb_thezone='".$row['ccode']."',
			   mb_name='".$name."',
			   mb_nick='".$row['nickname']."',
			   mb_zip1='".$zipcode1."',
			   mb_zip2='".$zipcode2."',
			   mb_email='".$email."',
			   mb_tel='".$phone."',
			   mb_hp='".$cellphone."',
			   mb_addr1='".$address_street."',
			   mb_addr2='".$address_detail."',
			   mb_addr3='".$address."',
			   mb_datetime='".$row['regist_date']."',
			   mb_login_ip='".$row['login_addr']."',
			   mb_signature='',
			   mb_memo='',
			   mb_lost_certify='',
			   mb_profile='',
			   mb_giup_bname='".$biniess_mem_row['bname']."',
			   mb_giup_boss_name='".$biniess_mem_row['bceo']."',
			   mb_giup_btel='".$biniess_mem_row['bphone']."',
			   mb_giup_bnum='".$biniess_mem_row['bno']."',
			   mb_giup_buptae='".$biniess_mem_row['bitem']."',
			   mb_giup_bupjong='".$biniess_mem_row['bstatus']."',
			   mb_giup_addr1='".$biniess_mem_row['baddress_street']."',
			   mb_giup_addr2='".$biniess_mem_row['baddress_detail']."',
			   mb_giup_addr3='".$biniess_mem_row['baddress']."',
			   mb_giup_zip1='".$bzipcode1."',
			   mb_giup_zip2='".$bzipcode2."',
			   mb_giup_tax_email='".$bemail."';";
    //echo $in_sql."<br>";
    sql_query($in_sql);

	if($person_mem_row['name']){
		$person_sql = "insert into g5_member_giup_manager
		               set
					   mb_id = '".$row['userid']."',
					   mm_name = '".$person_mem_row['name']."',
					   mm_part = '".$person_mem_row['part']."',
					   mm_rank = '".$person_mem_row['duty']."',
					   mm_tel = '".$person_mem_row['phone']."',
					   mm_hp = '".$person_mem_row['mobile']."',
					   mm_email = '".$person_mem_row['email']."'";
        sql_query($person_sql);
	}
}
echo "완료";
?>