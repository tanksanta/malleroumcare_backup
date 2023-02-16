<?php

include_once("./_common.php");

if(!$member["mb_id"] || !$member["mb_entId"])
  json_response(400, '먼저 로그인하세요.');

header('Content-type: application/json');
if($_POST["mode"] == "pwd"){
	$cert_data_ref =  explode("|",$member["cert_data_ref"]);
	
	if(md5(base64_encode($_POST["Pwd"])) == $cert_data_ref[3]){	
		$_SESSION['Pwd'] = base64_encode($_POST["Pwd"]);
		json_response(200, '성공');		
	}else{
		json_response(400, '비밀번호를 확인해 주세요.');		
	}
	exit;
}

if($_POST["mode"] == "ent_num"){
	$sql = "update g5_member set mb_ent_num='{$_POST['ent_num']}' where mb_id='".$member["mb_id"]."'";
	sql_query($sql);

	$sql2 = "select mb_ent_num
	  from g5_member
	  where mb_id = '{$member['mb_id']}' LIMIT 1
	";
	$result2 = sql_fetch($sql2);
	
	if($result2["mb_ent_num"] == $_POST['ent_num']){
		json_response(200, '성공');		
	}else{
		json_response(400, '장기요양기관번호 등록에 실패하였습니다. 다시 시도해 주세요.'.$result2["ent_num"]);		
	}
	exit;
}

$upload_dir = $_SERVER['DOCUMENT_ROOT']."/data/file/member/tilko/";
if(!is_dir($upload_dir)){//인증서 파일 생성할 폴더 확인 
	@umask(0);
	@mkdir($upload_dir,0700);
	//@chmod($upload_dir, 0777);
}
//최초 공인인증서 등록 시 세션 처리하여 로그인 하고 있는 동안 API 조회를 자유롭게 사용 하게 함
$_SESSION['PriKey'] = $_POST["PriKey"];
$_SESSION['PubKey'] = $_POST["PubKey"];
$_SESSION['Pwd'] = $_POST["Pwd"];
//파일 생성
$cert_data_ref =  explode("|",$member["cert_data_ref"]);
if($cert_data_ref[0] != ""){
	if(file_exists($upload_dir.base64_encode($cert_data_ref[0]).".enc")){
		@unlink($upload_dir.base64_encode($cert_data_ref[0]).".enc");//기존 파일 삭제
	}
	if(file_exists($upload_dir.base64_encode($cert_data_ref[0]).".txt")){
		@unlink($upload_dir.base64_encode($cert_data_ref[0]).".txt");//기존 파일 삭제
	}
}
$file_name = date("YmdHis")."_".str_replace("-","",$member["mb_giup_bnum"]); //파일명
$file_name2 = base64_encode($file_name); //파일명
$file = fopen($upload_dir.$file_name2.".txt","w");
fwrite($file,iconv("UTF-8", "CP949",$_POST["PriKey"])."\r\n".iconv("UTF-8", "CP949",$_POST["PubKey"])."\r\n".iconv("UTF-8", "CP949",$_POST["Expire"]));//개인키,공용키,만료일만 기록
fclose($file);
@system('echo -n '.base64_decode($_POST["Pwd"]).' | openssl aes-256-cbc -in '.$upload_dir.$file_name2.'.txt -out '.$upload_dir.$file_name2.'.enc -pass stdin');//입력 받은 비밀번호로 파일 암호화 실행
//@system('echo -n '."thkc!@#".' | openssl aes-256-cbc -in '.$upload_dir.$file_name2.'.txt -out '.$upload_dir.$file_name2.'.enc -pass stdin');//고정값으로 파일 암호화 실행
for($i=0;$i<3;$i++){	
	if(file_exists($upload_dir.$file_name2.".enc")){//암호화 파일이 생성 되면 txt 파일 삭제
		@unlink($upload_dir.$file_name2.".txt");//암호화 안된 파일 삭제
		break;
	}else{
		@system('echo -n '.base64_decode($_POST["Pwd"]).' | openssl aes-256-cbc -in '.$upload_dir.$file_name2.'.txt -out '.$upload_dir.$file_name2.'.enc -pass stdin');//입력 받은 비밀번호로 파일 암호화 실행
	}
	sleep(1);
}


$sql = "update g5_member set cert_reg_sts='Y',cert_reg_date=now(),cert_data_ref='".$file_name."|".base64_encode($_POST["Name"])."|".base64_encode($_POST["Expire"])."|".md5($_POST["Pwd"])."' where mb_id='".$member["mb_id"]."'";
sql_query($sql);

$response2["api_stat"] = "0";
$url = "";
//$response2["PriKey"] = $_SESSION["PriKey"];
//$response2["PubKey"] = $_SESSION["PubKey"];
$response2["Pwd"] = $_SESSION['Pwd'];
$url = $response2["Pwd"];

if($url != ""){
		$response2["url"] = $url;
		$response2["api_stat"] = "1";
	}else{
		$response2["url"] = "url생성실패";
	}
echo json_encode($response2);
?>
