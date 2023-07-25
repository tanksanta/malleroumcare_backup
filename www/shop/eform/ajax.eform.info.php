<?php
include_once('./_common.php');

header('Content-type: application/json');

$dc_id = $_POST['dc_id'];


if( !$dc_id ) {
  $rows = [];
  echo json_encode($rows);
  exit();
}

$sql ="select * from eform_document where HEX(dc_id) = '$dc_id'";
$row=sql_fetch($sql);

$rows = array(
	'penNm' => $row['penNm'],//수급자 이름
	'penConNum' => preg_replace('/[^0-9]*/s', '',$row['penConNum']),//수급자 전화번호
	'contract_tel' => preg_replace('/[^0-9]*/s', '',$row['contract_tel']),//대리인 전화번호
	'pen_guardian_tel' => preg_replace('/[^0-9]*/s', '',$row['pen_guardian_tel']),//보호자 전화번호
	'pen_guardian_nm' => $row['pen_guardian_nm'],//보호자 이름
	'contract_sign_name' => $row['contract_sign_name'],//대리인 이름
	'contract_sign_type' => $row['contract_sign_type'],//대리인 체크
	'contract_sign_relation' => $row['contract_sign_relation'],//대리인 관계
	'applicantRelation' => $row['applicantRelation'],//신청인 관계
	'dc_subject' => $row['dc_subject'],//계약서 제목
);
echo json_encode($rows);
