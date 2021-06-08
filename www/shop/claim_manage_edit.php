<?php
include_once('./_common.php');

$cl_id = $_GET['cl_id'];
if (!$is_member) alert('먼저 로그인 하세요.');

if(!$cl_id) alert('잘못된 접근입니다.');

$cl = sql_fetch("SELECT * FROM `claim_management` WHERE cl_id = '$cl_id' AND mb_id = '{$member['mb_id']}'");
if(!$cl) alert('내용을 변경할 수 없습니다.');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>내용변경</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <link type="text/css" rel="stylesheet" href="/thema/eroumcare/assets/css/font.css">
  <link type="text/css" rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <style>
    * { margin: 0; padding: 0; position: relative; box-sizing: border-box; outline: none; }
    html, body { font-family: "Noto Sans KR", sans-serif; }
    body { padding: 50px 0 50px 0; }
    .table_box {}
    table {width: 100%; color: #333; border-collapse: collapse; border-spacing: 0;}
    th, td { padding: 6px 8px; border: none; text-align: center;}
    td.num { text-align: right; }
    th {background-color: #f5f5f5; font-weight: bold;}
    td:first-child { width: 65px; }
    .head_box {position: fixed;top:0;left:0;width:100%;height:50px;border-bottom: 1px solid #ddd;font-size:20px;line-height:1;padding:15px;}
    .head_box h1 {font-weight: 500; font-size:18px; line-height: 1;}
    #btn_close {position: absolute; width: 50px; height: 50px; font-size: 20px; line-height: 30px; top: 0; right: 0; text-align: center; vertical-align: middle; background:none; border: none; cursor: pointer;}
    .btn_box {position:fixed;bottom:0;left:0;width:100%;height: 50px;}
    .btn_box button{ border: 1px solid #ddd; background-color: #fff; color: #666; height: 100%; text-align: center; cursor: pointer; font-weight: bold;}
    #btn_submit {border: none; height: 100%; position: absolute; top: 0; left: 0; width: 70%; background-color: #ee8102; color: #fff;}
    #btn_cancel {position: absolute; top:0; left: 70%; width: 30%;}
    input { padding: 5px; width: 80%; text-align: center; border: 1px solid #999; }
    .num input { text-align: right; }
  </style>
</head>
<body>
  <div class="head_box">
    <h1><?="{$cl['penNm']}({$cl['penLtmNum']} / {$cl['penRecGraNm']} / {$cl['penTypeNm']})"?></h1>
    <button id="btn_close"><i class="fa fa-times" aria-hidden="true"></i></button>
  </div>
  <div class="table_box">
    <table>
      <thead>
        <tr>
          <th>분류</th>
          <th>급여시작일</th>
          <th>급여비용총액</th>
          <th>본인부담금</th>
          <th>청구액</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>입력</td>
          <td><?=$cl['start_date']?></td>
          <td class="num"><?=number_format($cl['total_price'])?>원</td>
          <td class="num"><?=number_format($cl['total_price_pen'])?>원</td>
          <td class="num"><?=number_format($cl['total_price_ent'])?>원</td>  
        </tr>
        <!--<tr>
          <td>공단</td>
          <td>2021-02-02</td>
          <td class="num">200,000원</td>
          <td class="num">10,000원</td>
          <td class="num">190,000원</td>  
        </tr>-->
        <tr>
          <td>수정</td>
          <td><input type="text" name="start_date" id="start_date" value="<?=$cl['start_date']?>"></td>
          <td class="num"><input type="number" min="0" name="total_price" id="total_price" value="<?=$cl['total_price']?>"></td>
          <td class="num"><input type="number" min="0" name="total_price_pen" id="total_price_pen" value="<?=$cl['total_price_pen']?>"></td>
          <td class="num"><input type="number" min="0" name="total_price_ent" id="total_price_ent" value="<?=$cl['total_price_ent']?>"></td>  
        </tr>
      </tbody>
    </table>
  </div>
  <div class="btn_box">
    <button id="btn_submit">확인</button>
    <button id="btn_cancel">취소</button>
  </div>
<script>
$(function() {
  var cl = '<?=json_encode($cl)?>';
  try {
    cl = JSON.parse(cl);
  } catch(e) {
    alert("서버 오류로 내용을 변경할 수 없습니다.");
  }

  function closePopup() {
    $("body", parent.document).removeClass("modal-open");
    $("#popupEdit", parent.document).hide();
    $("#popupEdit", parent.document).find("iframe").remove();
  }

  $("#btn_close, #btn_cancel").click(function(e) {
    e.preventDefault();
    closePopup();
  });

  $("#btn_submit").click(function(e) {
    e.preventDefault();
    var start_date = $("#start_date").val();
    var total_price = $("#total_price").val();
    var total_price_pen = $("#total_price_pen").val();
    var total_price_ent = $("#total_price_ent").val();

    cl['start_date'] = start_date;
    cl['total_price'] = total_price;
    cl['total_price_pen'] = total_price_pen;
    cl['total_price_ent'] = total_price_ent;

    $.post('./ajax.claim_manage.update.php?cl_id='+cl['cl_id'], JSON.stringify(cl), 'json')
    .done(function(data) {
      parent.updateClaim(cl['cl_id'], cl);
      closePopup();
    })
    .fail(function ($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });
});
</script>
</body>
</html>