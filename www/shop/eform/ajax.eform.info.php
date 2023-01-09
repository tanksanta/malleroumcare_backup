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
	'applicantNm' => $row['applicantNm'],//신청인 이름
	'contract_sign_name' => $row['contract_sign_name'],//대리인 이름
	'contract_sign_type' => $row['contract_sign_type'],//대리인 체크
	'applicantRelation' => $row['applicantRelation'],//신청인 관계
	'dc_subject' => $row['dc_subject'],//계약서 제목
);
echo json_encode($rows);