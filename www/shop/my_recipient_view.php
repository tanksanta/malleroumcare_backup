<?php

	include_once("./_common.php");
	include_once("./_head.php");

	# 회원검사
	if(!$member["mb_id"]){
		alert("접근 권한이 없습니다.");
		return false;
	}
?>
<link rel="stylesheet" href="<?=G5_CSS_URL?>/my_recipient.css">
<div class="recipient_view_wrap">
  <div class="title_wrap">
    <div class="sub_section_tit">
      홍길동 (40년생/남)
    </div>
    <a class="c_btn" href="./my_recipient_list.php">목록</a>
  </div>
  <div class="info_wrap">
    <a class="c_btn" href="./my_recipient_update.php?id=PENID_20210607000002">기본정보 수정</a>
    <div class="row">
      <div class="col-sm-2">·연락처</div>
      <div class="col-sm-10">: 010-1234-5678, 02-1111-2222</div>
    </div>
    <div class="row">
      <div class="col-sm-2">·주소</div>
      <div class="col-sm-10">: 서울시 종로구 111-22</div>
    </div>
    <div class="row">
      <div class="col-sm-2">·장기요양정보</div>
      <div class="col-sm-10">: L12345**** (1등급/일반15%)</div>
    </div>
    <div class="row">
      <div class="col-sm-2">·유효기간</div>
      <div class="col-sm-10">: 2021-01-01 ~ 2022-01-01</div>
    </div>
    <div class="row">
      <div class="col-sm-2">·보호자</div>
      <div class="col-sm-10">: (배우자)홍길동, 40년생/남, 010-1111-1111, 02-1111-2222, 서울시 종로구 111-22</div>
    </div>
    <div class="row">
      <div class="col-sm-2">·장기요양기록지</div>
      <div class="col-sm-10">: 확인자(수급자), 수령방법(방문)</div>
    </div>
  </div>

  <div class="sub_title_wrap">
    <div class="sub_title">
      제공가능 품목
    </div>
    <div class="sub_title_desc">* 카테고리 선택 시 회원이 선택된 상태로 이동합니다.</div>
  </div>
</div>
<?php include_once("./_tail.php"); ?>
