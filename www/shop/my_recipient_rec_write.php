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
if(!$pen)
  alert('수급자 정보가 존재하지 않습니다.');

function check_and_print($check, $prefix = '', $postfix = '') {
  if($check) return $prefix.$check.$postfix;
  return '';
}
?>

<link rel="stylesheet" href="<?=G5_CSS_URL?>/my_recipient.css">
<div class="recipient_rec_wrap">
  <div class="title_wrap">
    <div class="sub_section_tit">욕구사정기록지</div>
  </div>
  <div class="info_wrap">
    <div class="row">
      <div class="col-sm-12">
        <?=$pen['penNm']?>(<?=substr($pen['penBirth'], 2, 2)?>년생/<?=$pen['penGender']?>)
        <span>*보호자(<?=$pen['penProNm'] ? $pen['penProNm'].'/' : ''?><?=get_pen_pro_rel($pen['penProTypeCd'], $pen['penProRel'])?>)</span>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <?=substr($pen['penLtmNum'], 0, 6)?>**** (<?=$pen['penRecGraNm']?>/<?=$pen['penTypeNm']?>)
      </div>
    </div>
  </div>

  <form action="" method="post">
  <div class="sub_title_wrap">
    <div class="sub_title">
      1.신체상태 (일상생활동작 수행능력 등)
    </div>
    <div class="sub_title_desc">* 해당시 선택</div>
  </div>
  <div class="table_wrap">
    <table>
      <colgroup>
        <col style="width: 34%">
        <col style="width: 22%">
        <col style="width: 22%">
        <col style="width: 22%">
      </colgroup>
      <thead>
        <tr>
          <th>항목</th>
          <th>완전도움</th>
          <th>부분도움</th>
          <th>완전자립</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th>옷 벗고 입기</th>
          <td><input type="radio" name="psclState1" value="00" class=""></td>
          <td><input type="radio" name="psclState1" value="01" class=""></td>
          <td><input type="radio" name="psclState1" value="02" class=""></td>
        </tr>
        <tr>
          <th>식사 하기</th>
          <td><input type="radio" name="psclState3" value="00" class=""></td>
          <td><input type="radio" name="psclState3" value="01" class=""></td>
          <td><input type="radio" name="psclState3" value="02" class=""></td>
        </tr>
        <tr>
          <th>목욕 하기</th>
          <td><input type="radio" name="psclState5" value="00" class=""></td>
          <td><input type="radio" name="psclState5" value="01" class=""></td>
          <td><input type="radio" name="psclState5" value="02" class=""></td>
        </tr>
      </tbody>
    </table>
    <table>
      <colgroup>
        <col style="width: 34%">
        <col style="width: 22%">
        <col style="width: 22%">
        <col style="width: 22%">
      </colgroup>
      <thead>
        <tr>
          <th>항목</th>
          <th>완전도움</th>
          <th>부분도움</th>
          <th>완전자립</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th>일어나 앉기</th>
          <td><input type="radio" name="psclState2" value="00" class=""></td>
          <td><input type="radio" name="psclState2" value="01" class=""></td>
          <td><input type="radio" name="psclState2" value="02" class=""></td>
        </tr>
        <tr>
          <th>방밖으로 나오기</th>
          <td><input type="radio" name="psclState4" value="00" class=""></td>
          <td><input type="radio" name="psclState4" value="01" class=""></td>
          <td><input type="radio" name="psclState4" value="02" class=""></td>
        </tr>
        <tr>
          <th>화장실 사용하기</th>
          <td><input type="radio" name="psclState6" value="00" class=""></td>
          <td><input type="radio" name="psclState6" value="01" class=""></td>
          <td><input type="radio" name="psclState6" value="02" class=""></td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="textarea_head">판단근거</div>
  <textarea name="psclReason"></textarea>

  <div class="sub_title_wrap">
    <div class="sub_title">
      2.인지상태 (인지기능저하, 정신상태, 감정, 문제행동 등)
    </div>
  </div>
  <div class="textarea_head">판단근거</div>
  <textarea name="recogReason"></textarea>

  <div class="sub_title_wrap">
    <div class="sub_title">
      3.가족 및 환경상태 (가족상황, 거주환경, 수발부담 등)
    </div>
    <div class="sub_title_desc">* 해당시 선택</div>
  </div>
  <div class="family_wrap">
    <div class="row">
      <div class="head">주수발자</div>
      <div class="content">
        <input type="radio" name="helperYn" id="careyes_select" value="Y">
				<label for="careyes_select">유</label>
        <input type="radio" name="helperYn" id="careno_select" value="N">
        <label for="careno_select">무</label>
      </div>
    </div>
    <div class="row">
      <div class="head">주수발자 관계</div>
      <div class="content">
        <input type="radio" name="helperType" id="spouse_select" value="00">
        <label for="spouse_select">배우자</label>
        <input type="radio" name="helperType" id="children_select" value="01">
        <label for="children_select">자녀</label>
        <input type="radio" name="helperType" id="soninlow_select" value="02">
        <label for="soninlow_select">사위</label>
        <input type="radio" name="helperType" id="sibling_select" value="03">
        <label for="sibling_select">형제자매</label>
        <input type="radio" name="helperType" id="kin_select" value="04">
        <label for="kin_select">친척</label>
        <input type="radio" name="helperType" id="etc_select" value="05">
        <label for="etc_select">기타</label>
        <input type="text" name="helperTypeEtc" value="" disabled="disabled">
      </div>
    </div>
    <div class="row">
      <div class="head">자녀수</div>
      <div class="content">
        <input type="text" name="child" value=""> 명
      </div>
    </div>
    <div class="row">
      <div class="head">거주환경</div>
      <div class="content">
        <input type="radio" name="homeEnv" id="apt_select" value="00">
        <label for="apt_select">아파트</label>
        <input type="radio" name="homeEnv" id="villa_select" value="01">
        <label for="villa_select">연립/빌라</label>
        <input type="radio" name="homeEnv" id="house_select" value="02">
        <label for="house_select">단독주택</label>
      </div>
    </div>
    <div class="row">
      <div class="head">거주형태</div>
      <div class="content">
        <input type="radio" name="homeType" id="onehome_select" value="00">
        <label for="onehome_select">자가</label>
        <input type="radio" name="homeType" id="jeonse_select" value="01">
        <label for="jeonse_select">전세</label>
        <input type="radio" name="homeType" id="rent_select" value="02">
        <label for="rent_select">월세</label>
      </div>
    </div>
    <div class="row">
      <div class="head">동거인</div>
      <div class="content">
        <input type="checkbox" name="inmate[]" id="one_select" value="00">
        <label for="one_select">독거</label>
        <input type="checkbox" name="inmate[]" id="marry_select" value="01">
        <label for="marry_select">부부</label>
        <input type="checkbox" name="inmate[]" id="parent_select" value="02">
        <label for="parent_select">부모</label>
        <input type="checkbox" name="inmate[]" id="child_select" value="03">
        <label for="child_select">자녀</label>
        <input type="checkbox" name="inmate[]" id="grandchild_select" value="04">
        <label for="grandchild_select">손자녀</label>
        <input type="checkbox" name="inmate[]" id="kins_select" value="05">
        <label for="kins_select">친척</label>
        <input type="checkbox" name="inmate[]" id="friend_select" value="06">
        <label for="friend_select">친구·이웃</label>
      </div>
    </div>
  </div>

  <div class="sub_title_wrap">
    <div class="sub_title">
      4.총평
    </div>
  </div>
  <textarea name="totalReview"></textarea>

  <div class="btn_wrap">
    <input type="submit" value="등록">
    <a href="<?=G5_SHOP_URL?>/my_recipient_view.php?id=<?=$pen['penId']?>">취소</a>
  </div>
  </form>
</div>

<?php include_once("./_tail.php"); ?>
