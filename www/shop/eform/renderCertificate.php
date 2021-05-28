<?php
include_once("./_common.php");
include_once("./lib/eform.lib.php");

$eform = sql_fetch("SELECT HEX(`dc_id`) as uuid, e.* FROM `eform_document` as e WHERE od_id = '$od_id'");

if($eform['uuid'] !== $uuid || $eform['entId'] !== $entId || $eform['penId'] !== $penId) {
  alert('계약서를 확인할 수 없습니다.');
}

// 서명 정보 가져오기
$contents = sql_query("SELECT * FROM `eform_document_content` WHERE dc_id = UNHEX('{$eform["uuid"]}')");
$state = array();
while($ct = sql_fetch_array($contents)) {
  $id = $ct['ct_id'];
  $val = $ct['ct_content'];

  $key = explode('_', $id);
  if($key[0] === 'seal') {
    if(!$state[$key[1]]) $state[$key[1]] = array();
    $state[$key[1]][] = array('type' => 'seal', 'val' => $val);
  } else if ($key[0] === 'sign') {
    if(!$state[$key[1]]) $state[$key[1]] = array();
    $state[$key[1]][] = array('type' => 'sign', 'val' => $val);
  }
}

// 로그 정보 가져오기
$logs = sql_query("SELECT * FROM `eform_document_log` WHERE dc_id = UNHEX('{$eform["uuid"]}') ORDER BY dl_datetime ASC");

// 기초수급자 체크
$is_gicho = $eform['penTypeCd'] == '04';


// 감사추적 인증서 제작시간
$timestamp = time();
$datetime = date('Y-m-d H:i:s', $timestamp);

$sign_status = '완료 됨';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>감사추적 인증서</title>
  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/certificate.css">
</head>
<body>
<div class="a4">
  <div class="head">감사 추적 인증서 (제작시간 : <?=$datetime?>)</div>
  <div class="body">
    <div class="section">
      <h3>감사 추적 인증서</h3>
      <table>
        <colgroup>
          <col style="width: 20%;">
          <col style="width: 80%;">
        </colgroup>
        <tr>
          <th>문서 이름</th>
          <td><?=$eform['dc_subject']?></td>
        </tr>
        <tr>
          <th>문서 ID</th>
          <td><?=$eform['uuid']?></td>
        </tr>
        <tr>
          <th>서명 상태</th>
          <td><?=$sign_status?></td>
        </tr>
        <tr>
          <th>기준 시간</th>
          <td>(UTC+09:00) 한국 표준시</td>
        </tr>
        <tr>
          <th>문서 페이지 수</th>
          <td><?=($is_gicho ? '6P' : '4P')?></td>
        </tr>
      </table>
    </div>
    <div class="section">
      <h3>서명 정보</h3>
      <?php
      foreach($state as $code => $arrs) {
        foreach($arrs as $arr) {
     ?>
      <table class="table-sign-info">
        <colgroup>
          <col style="width: 20%;">
          <col style="">
          <col style="width: 300px;">
        </colgroup>
        <thead>
          <tr>
            <th colspan="2"><?=$arr['type'] === 'seal' ? '사업소' : '수급자'?></th>
            <th>서명</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>문서</th>
            <td><?=get_document_name($code)?></td>
            <td class="img" rowspan="4"><div class="image" style="background-image: url('<?=$arr['val']?>');"></div></td>
          </tr>
          <tr>
            <th>이름</th>
            <td><?=$arr['type'] === 'seal' ? $eform['entNm'] : $eform['penNm']?></td>
          </tr>
          <tr>
            <th>이메일</th>
            <td><?=$arr['type'] === 'seal' ? $eform['entMail'] : $eform['penMail']?></td>
          </tr>
          <tr>
            <th>진행 정보</th>
            <td>서명 완료</td>
          </tr>
        </tbody>
      </table>
      <?php
        }
      }
      ?>
    </div>
    <div class="section">
      <h3>진행 이력</h3>
      <table class="table-history">
        <colgroup>
          <col style="width: 20%;">
          <col style="width: 80%;">
        </colgroup>
        <thead>
          <tr>
            <th>진행 시점 및 환경</th>
            <th>진행 내용</th>
          </tr>
        </thead>
        <tbody>
          <?php
          while($log = sql_fetch_array($logs)) {
          ?>
          <tr>
            <th>
              <div class="time"><?=$log['dl_datetime']?></div>
              <div class="ip"><?=$log['dl_ip']?></div>
            </th>
            <td><?=$log['dl_log']?></td>
          </tr>
          <?php
          }
          ?>
        </tbody>
      </table>
    </div>
    <div class="section">
      <h3>전자서명 이용약관</h3>
    </div>
  </div>
</div>
</body>
</html>
