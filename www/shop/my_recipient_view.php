<?php

	include_once("./_common.php");
	include_once("./_head.php");

	# 회원검사
	if(!$member["mb_id"])
		alert("접근 권한이 없습니다.");

  if(!$_GET["id"])
    alert("정상적이지 않은 접근입니다.");

  $res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'penId' => $_GET['id']
  ));

  if(!$res || $res['errorYn'] == 'Y')
    alert('서버 오류로 수급자 정보를 불러올 수 없습니다.');
  
  $pen = $res['data'][0];
  if(!$pen) { var_dump($res); exit; }
    //alert('수급자 정보가 존재하지 않습니다.');

  function combine_data($data = []) {
    $res = '';
    foreach($data as $val) {
      if($res)
        $res .= $val.', ';
    }
    return $res;
  }
?>
<link rel="stylesheet" href="<?=G5_CSS_URL?>/my_recipient.css">
<div class="recipient_view_wrap">
  <div class="title_wrap">
    <div class="sub_section_tit">
      <?=$pen['penNm']?> (<?=substr($pen['penBirth'], 2, 2)?>년생/<?=$pen['penGender']?>)
    </div>
    <div class="r_btn_wrap">
      <a class="c_btn" href="./my_recipient_list.php">목록</a>
    </div>
  </div>
  <div class="info_wrap">
    <a class="c_btn" href="./my_recipient_update.php?id=<?=$pen['penId']?>">기본정보 수정</a>
    <div class="row">
      <div class="col-sm-2">·연락처</div>
      <div class="col-sm-10">: <?=$pen['penConNum']?><?=($pen['penConPnum'] ? ", {$pen['penConPnum']}" : "")?></div>
    </div>
    <div class="row">
      <div class="col-sm-2">·주소</div>
      <div class="col-sm-10">: <?="{$pen['penAddr']} {$pen['penAddrDtl']}"?></div>
    </div>
    <div class="row">
      <div class="col-sm-2">·장기요양정보</div>
      <div class="col-sm-10">: <?=substr($pen['penLtmNum'], 0, 6)?>**** (<?=$pen['penRecGraNm']?>/<?=$pen['penTypeNm']?>)</div>
    </div>
    <div class="row">
      <div class="col-sm-2">·유효기간</div>
      <div class="col-sm-10">: <?=$pen['penExpiDtm']?></div>
    </div>
    <div class="row">
      <div class="col-sm-2">·보호자</div>
      <div class="col-sm-10">: (<?=$pen['penProRel']?>)<?=$pen['penProNm']?>, <?=substr($pen['penProBirth'], 2, 2)?>년생, <?=$pen['penProConNum']?>, <?=$pen['penProConPNum']?>, 서울시 종로구 111-22</div>
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
  <div class="section_wrap">
    <div class="item_wrap">
      <div class="item_head">판매품목</div>
      <div class="item_body">
        <a href="#">욕창예방매트리스</a>
        <a href="#">요실금팬티</a>
        <a href="#">자세변환용구</a>
      </div>
    </div>
    <div class="item_wrap">
      <div class="item_head">대여품목</div>
    </div>
  </div>

  <div class="sub_title_wrap">
    <div class="sub_title l_title">
      장바구니
    </div>
    <div class="r_btn_wrap">
      <a class="c_btn" href="#">신규추가하기</a>
      <a class="c_btn primary" href="#">장바구니 바로가기</a>
    </div>
  </div>

  <div class="memo_wrap">
    <div class="sub_title_wrap">
      <div class="sub_title l_title">
        메모
      </div>
    </div>
    <div class="section_wrap grey">
      <div class="sub_section_wrap">
        ㅇㅇㅇ
      </div>
      <div class="memo_row">
        <div class="memo_body">
          <div class="memo_date">2021년 03월 12일</div>
          <div class="memo_content">사업소가 작성한 메모가 여기에 보여집니다.</div>
        </div>
        <div class="memo_btn_wrap">
          <a class="c_btn" href="#">수정</a>
          <a class="c_btn" href="#">삭제</a>
        </div>
      </div>
      <div class="memo_row">
        <div class="memo_body">
          <div class="memo_date">2021년 03월 12일</div>
          <div class="memo_content">사업소가 작성한 메모가 여기에 보여집니다.</div>
        </div>
        <div class="memo_btn_wrap">
          <a class="c_btn" href="#">수정</a>
          <a class="c_btn" href="#">삭제</a>
        </div>
      </div>
    </div>
  </div>

  <div class="sub_title_wrap">
    <div class="sub_title l_title">
      욕구사정기록지
    </div>
  </div>
  <div class="section_wrap grey">
    <div class="sub_section_wrap">
      ㅇㅇㅇ
    </div>
  </div>
</div>
<?php include_once("./_tail.php"); ?>
