<?php
if(!defined('_PRINT_REC_')) {
  include_once("./_common.php");
  include_once("./_head.php");
}
 //수급자 조회 관련 추가, 개발완료 시 삭제 필요====================================================================?>
<script>
	swal("사용 주의","현재 수급자 조회조건 개선 작업으로 수급자 정보를\n업데이트할 수 없습니다.\n등록된 수급자의 정보가 정확하지 않을 수 있음을\n유의해 주시기 바랍니다.","warning");
	//history.back();
</script>
<?php //=======================================================================================================
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

$multi_check_member = [
  'walking_sub',
  'dementia_sub',
  'chronic',
  'circulatory',
  'nervous',
  'musculoskeletal',
  'mental',
  'breath',
  'kidney',
  'other',
  'exercise',
  'joint',
  'walking_disorder',
  'nurse_breath',
  'nurse_nutrition',
  'nurse_excretion',
  'nurse_wound',
  'nurse_bedsore_step',
  'nurse_bedsore_part',
  'nurse_ache',
  'nurse_ache_cancer',
  'nurse_ache_normal',
  'observe',
  'observe_orientation',
  'observe_memory',
  'observe_wender',
  'observe_hallucination',
  'observe_inappropriate',
  'observe_violent',
  'observe_eating',
  'observe_unrest',
  'observe_depressed',
  'family_inmate',
  'family_resource',
  'family_resource_sub',
  'welfare',
  'welfare_sub',
  'etc_daily',
  'etc_training',
  'etc_emotional',
  'etc_family_helper_mitigate'
];

$rec = null;
if($rd_id = get_search_string($_GET['rd_id'])) {
  $rec = sql_fetch("
    SELECT * FROM recipient_rec_detail
    WHERE rd_id = '{$rd_id}' and mb_id = '{$member['mb_id']}'
  ");
  if(!$rec['rd_id'])
    alert('욕구사정기록지가 존재하지 않습니다.');
  
  foreach($multi_check_member as $key) {
    $rec[$key] = explode(',', $rec[$key]);
  }
}

function print_name_and_value($name, $val) {
  global $rec;

  $res = "name=\"{$name}\" value=\"{$val}\"";
  if($rec) {
    if(preg_match('/^(.+)\[\]$/', $name, $matches)) {
      if(in_array($val, $rec[$matches[1]])) {
        $res .= ' checked';
      }
    } else if($rec[$name] == $val) {
      $res .= ' checked';
    }
  }

  if(defined('_PRINT_REC_')) {
    $res .= ' onclick="javascript: return false;"';
  }

  return $res;
}
?>

<link rel="stylesheet" href="<?=G5_CSS_URL?>/my_recipient.css">
<div class="recipient_rec_wrap wide">
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
  <?php if (!$rec && !defined('_PRINT_REC_')) { ?>
  <div class="detail-tab">
    <ul>
      <li>
        <a href="./my_recipient_rec_form.php?id=<?php echo $id; ?>">
          <span></span>
          <h4>간략 기록지</h4>
        </a>
      </li>
      <li class="on">
        <a href="./my_recipient_rec_detail_form.php?id=<?php echo $id; ?>">
          <span></span>
          <h4>전체 기록지</h4>
        </a>
      </li>
    </ul>
  </div>
  <?php } ?>

  <form id="rec_detail_form" action="my_recipient_rec_detail_post.php" method="post">
    <?php if($rec) { ?>
    <input type="hidden" name="rd_id" value="<?=$rec['rd_id']?>">
    <?php  } ?>
    <input type="hidden" name="penId" value="<?=$pen['penId']?>">
    <div class="sub_title_wrap">
      <div class="sub_title">
        1. 일반상태
      </div>
      <div class="sub_title_desc">* 해당시 선택</div>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th>분류</th>
            <th colspan="2"></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th rowspan="5">영양</th>
            <th>영양상태</th>
            <td>
              <label for="nutrition_0">
                <input type="radio" id="nutrition_0" <?=print_name_and_value("nutrition", "00")?>>양호 : 건강 및 섭식, 영양 등에 문제가 없는 상태
              </label>
              <br/>
              <label for="nutrition_1">
                <input type="radio" id="nutrition_1" <?=print_name_and_value("nutrition", "01")?>>불량 : 건강, 섭식, 영양 등에 문제가 있어 세심한 관찰이 요구
              </label>
              <br/>
              <label for="nutrition_2">
                <input type="radio" id="nutrition_2" <?=print_name_and_value("nutrition", "02")?>>심한불량 : 극도의 건강, 섭식, 영양 등에 문제가 있어 치료적 처치가 필요한 상태
              </label>
            </td>
          </tr>
          <tr>
            <th>식사형태</th>
            <td>
              <label for="meal_0">
                <input type="radio" id="meal_0" <?=print_name_and_value("meal", "00")?>>일반식
              </label>
              <label for="meal_1">
                <input type="radio" id="meal_1" <?=print_name_and_value("meal", "01")?>>다진식
              </label>
              <label for="meal_2">
                <input type="radio" id="meal_2" <?=print_name_and_value("meal", "02")?>>죽
              </label>
              <label for="meal_3">
                <input type="radio" id="meal_3" <?=print_name_and_value("meal", "03")?>>미음
              </label>
              <label for="meal_4">
                <input type="radio" id="meal_4" <?=print_name_and_value("meal", "04")?>>경관식
              </label>
              <label for="meal_5">
                <input type="radio" id="meal_5" <?=print_name_and_value("meal", "05")?>>기타
              </label>
              (<input type="text" name="meal_etc" value="<?=$rec['meal_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>소화상태</th>
            <td>
              <label for="digestion_0">
                <input type="radio" id="digestion_0" <?=print_name_and_value("digestion", "00")?>>저작곤란
              </label>
              <label for="digestion_1">
                <input type="radio" id="digestion_1" <?=print_name_and_value("digestion", "01")?>>소화불량
              </label>
              <label for="digestion_2">
                <input type="radio" id="digestion_2" <?=print_name_and_value("digestion", "02")?>>오심·구토
              </label>
              <label for="digestion_3">
                <input type="radio" id="digestion_3" <?=print_name_and_value("digestion", "03")?>>기타
              </label>
              (<input type="text" name="digestion_etc" value="<?=$rec['digestion_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>연하상태</th>
            <td>
              <label for="swallow_0">
                <input type="radio" id="swallow_0" <?=print_name_and_value("swallow", "00")?>>양호
              </label>
              <label for="swallow_1">
                <input type="radio" id="swallow_1" <?=print_name_and_value("swallow", "01")?>>가끔 사레걸림
              </label>
              <label for="swallow_2">
                <input type="radio" id="swallow_2" <?=print_name_and_value("swallow", "02")?>>자주 사레걸림
              </label>
              <label for="swallow_3">
                <input type="radio" id="swallow_3" <?=print_name_and_value("swallow", "03")?>>연하곤란
              </label>
            </td>
          </tr>
          <tr>
            <th>구강상태</th>
            <td>
              <label for="oral_0">
                <input type="radio" id="oral_0" <?=print_name_and_value("oral", "00")?>>양호
              </label>
              <label for="oral_1">
                <input type="radio" id="oral_1" <?=print_name_and_value("oral", "01")?>>청결불량
              </label>              
              <label for="oral_2">
                <input type="radio" id="oral_2" <?=print_name_and_value("oral", "02")?>>치아약함
              </label>
              <label for="oral_3">
                <input type="radio" id="oral_3" <?=print_name_and_value("oral", "03")?>>틀니
              </label>
              <label for="oral_4">
                <input type="radio" id="oral_4" <?=print_name_and_value("oral", "04")?>>잔존치아 없음
              </label>
            </td>
          </tr>
          <tr>
            <th rowspan="2">
              배설
            </th>
            <th>소변상태</th>
            <td>
              <label for="pee_0">
                <input type="radio" id="pee_0" <?=print_name_and_value("pee", "00")?>>양호
              </label>
              <label for="pee_1">
                <input type="radio" id="pee_1" <?=print_name_and_value("pee", "01")?>>요실금
              </label>
              <label for="pee_2">
                <input type="radio" id="pee_2" <?=print_name_and_value("pee", "02")?>>배뇨곤란
              </label>
              <label for="pee_3">
                <input type="radio" id="pee_3" <?=print_name_and_value("pee", "03")?>>기저귀
              </label>
              <label for="pee_4">
                <input type="radio" id="pee_4" <?=print_name_and_value("pee", "04")?>>유치도뇨·방광루
              </label>             
            </td>
          </tr>
          <tr>
            <th>대변상태</th>
            <td>
              <label for="feces_0">
                <input type="radio" id="feces_0" <?=print_name_and_value("feces", "00")?>>양호
              </label>
              <label for="feces_1">
                <input type="radio" id="feces_1" <?=print_name_and_value("feces", "01")?>>지속적인 설사
              </label>
              <label for="feces_2">
                <input type="radio" id="feces_2" <?=print_name_and_value("feces", "02")?>>변비
              </label>
              <label for="feces_3">
                <input type="radio" id="feces_3" <?=print_name_and_value("feces", "03")?>>기저귀
              </label>
              <label for="feces_4">
                <input type="radio" id="feces_4" <?=print_name_and_value("feces", "04")?>>장루
              </label>
            </td>
          </tr>
          <tr>
            <th colspan="2">보행</th>
            <td>
              <label for="walking_0">
                <input type="radio" id="walking_0" <?=print_name_and_value("walking", "00")?>>단독보행
              </label>              
              <label for="walking_1">
                <input type="radio" id="walking_1" <?=print_name_and_value("walking", "01")?>>보조기사용
              </label>              
              (
              <label for="walking_2">
                <input type="checkbox" id="walking_2" <?=print_name_and_value("walking_sub[]", "00")?>>지팡이
              </label>              
              <label for="walking_3">
                <input type="checkbox" id="walking_3" <?=print_name_and_value("walking_sub[]", "01")?>>보행기
              </label>
              <label for="walking_4">
                <input type="checkbox" id="walking_4" <?=print_name_and_value("walking_sub[]", "02")?>>휠체어
              </label>
              )
              <label for="walking_5">
                <input type="radio" id="walking_5" <?=print_name_and_value("walking", "02")?>>부축도움
              </label>
              <label for="walking_6">
                <input type="radio" id="walking_6" <?=print_name_and_value("walking", "03")?>>보행불가능
              </label>
              <label for="walking_7">
                <input type="radio" id="walking_7" <?=print_name_and_value("walking", "04")?>>기타
              </label>
              (<input type="text" name="walking_etc" value="<?=$rec['walking_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th colspan="2">치매</th>
            <td>
              <label for="dementia_0">
                <input type="radio" id="dementia_0" <?=print_name_and_value("dementia", "00")?>>기억력저하
              </label>              
              <label for="dementia_1">
                <input type="radio" id="dementia_1" <?=print_name_and_value("dementia", "01")?>>기억력저하 + 행동변화증상
              </label>              
              <label for="dementia_2">
                <input type="radio" id="dementia_2" <?=print_name_and_value("dementia", "02")?>>기타
              </label>
              (<input type="text" name="dementia_etc" value="<?=$rec['dementia_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th colspan="2">치매증상<br>행동변화</th>
            <td>
              <label for="dementia2_0">
                <input type="checkbox" id="dementia2_0" <?=print_name_and_value("dementia_sub[]", "00")?>>배회
              </label>              
              <label for="dementia2_1">
                <input type="checkbox" id="dementia2_1" <?=print_name_and_value("dementia_sub[]", "01")?>>야간수면장애
              </label>              
              <label for="dementia2_2">
                <input type="checkbox" id="dementia2_2" <?=print_name_and_value("dementia_sub[]", "02")?>>망상·환각
              </label>              
              <label for="dementia2_3">
                <input type="checkbox" id="dementia2_3" <?=print_name_and_value("dementia_sub[]", "03")?>>폭력성
              </label>              
              <label for="dementia2_4">
                <input type="checkbox" id="dementia2_4" <?=print_name_and_value("dementia_sub[]", "04")?>>우울·불안
              </label>
              <label for="dementia2_5">
                <input type="checkbox" id="dementia2_5" <?=print_name_and_value("dementia_sub[]", "05")?>>거부
              </label>
              <label for="dementia2_6">
                <input type="checkbox" id="dementia2_6" <?=print_name_and_value("dementia_sub[]", "06")?>>성적행동
              </label>
              <label for="dementia2_7">
                <input type="checkbox" id="dementia2_7" <?=print_name_and_value("dementia_sub[]", "07")?>>기타
              </label>
            </td>
          </tr>
          <tr>
            <th colspan="2">시력상태</th>
            <td>
              <label for="eyesight_0">
                <input type="radio" id="eyesight_0" <?=print_name_and_value("eyesight", "00")?>>정상(안경 사용 포함)
              </label>              
              <br>
              <label for="eyesight_1">
                <input type="radio" id="eyesight_1" <?=print_name_and_value("eyesight", "01")?>>1미터 정도 떨어진 글씨는 읽을 수 있다
              </label>              
              <br>
              <label for="eyesight_2">
                <input type="radio" id="eyesight_2" <?=print_name_and_value("eyesight", "02")?>>눈 앞에 근접한 글씨만  읽을 수 있다.
              </label>              
              <br>
              <label for="eyesight_3">
                <input type="radio" id="eyesight_3" <?=print_name_and_value("eyesight", "03")?>>거의 보이지 않는다
              </label>              
              <br>
              <label for="eyesight_4">
                <input type="radio" id="eyesight_4" <?=print_name_and_value("eyesight", "04")?>>보이는지 판단 불능
              </label>
            </td>
          </tr>
          <tr>
            <th colspan="2">청력상태</th>
            <td>
              <label for="hearing_0">
                <input type="radio" id="hearing_0" <?=print_name_and_value("hearing", "00")?>>정상(보청기 사용 포함)
              </label>              
              <br>
              <label for="hearing_1">
                <input type="radio" id="hearing_1" <?=print_name_and_value("hearing", "01")?>>가까운 곳에서 대화는 가능하나 먼곳의 말소리는 듣지 못한다
              </label>              
              <br>
              <label for="hearing_2">
                <input type="radio" id="hearing_2" <?=print_name_and_value("hearing", "02")?>>큰소리만 들을 수 있다.
              </label>              
              <br>
              <label for="hearing_3">
                <input type="radio" id="hearing_3" <?=print_name_and_value("hearing", "03")?>>소리에 거의 반응이 없다.
              </label>              
              <br>
              <label for="hearing_4">
                <input type="radio" id="hearing_4" <?=print_name_and_value("hearing", "04")?>>들리는지 판단 불능  
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="sub_title_wrap">
      <div class="sub_title">
        2. 주요질병상태
      </div>
      <div class="sub_title_desc">* □에 체크 후 주요질병은 종합의견에 서술</div>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th>분류</th>
            <th colspan="2"></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>만성질환</th>
            <td>
              <label for="chronic_0">
                <input type="checkbox" id="chronic_0" <?=print_name_and_value("chronic[]", "00")?>>당뇨
              </label>
              <label for="chronic_1">
                <input type="checkbox" id="chronic_1" <?=print_name_and_value("chronic[]", "01")?>>고혈압
              </label>
              <label for="chronic_2">
                <input type="checkbox" id="chronic_2" <?=print_name_and_value("chronic[]", "02")?>>만성호흡기질환
              </label>
              <label for="chronic_3">
                <input type="checkbox" id="chronic_3" <?=print_name_and_value("chronic[]", "03")?>>암
              </label>
              (<input type="text" name="chronic_etc" value="<?=$rec['chronic_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>순환기계</th>
            <td>
              <label for="circulatory_0">
                <input type="checkbox" id="circulatory_0" <?=print_name_and_value("circulatory[]", "00")?>>뇌경색
              </label>
              <label for="circulatory_1">
                <input type="checkbox" id="circulatory_1" <?=print_name_and_value("circulatory[]", "01")?>>뇌출혈
              </label>
              <label for="circulatory_2">
                <input type="checkbox" id="circulatory_2" <?=print_name_and_value("circulatory[]", "02")?>>협심증
              </label>
              <label for="circulatory_3">
                <input type="checkbox" id="circulatory_3" <?=print_name_and_value("circulatory[]", "03")?>>심근경색증
              </label>
              <label for="circulatory_4">
                <input type="checkbox" id="circulatory_4" <?=print_name_and_value("circulatory[]", "04")?>>기타
              </label>
              (<input type="text" name="circulatory_etc" value="<?=$rec['circulatory_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>신경계</th>
            <td>
              <label for="nervous_0">
                <input type="checkbox" id="nervous_0" <?=print_name_and_value("nervous[]", "00")?>>치매
              </label>
              <label for="nervous_1">
                <input type="checkbox" id="nervous_1" <?=print_name_and_value("nervous[]", "01")?>>파키슨병
              </label>
              <label for="nervous_2">
                <input type="checkbox" id="nervous_2" <?=print_name_and_value("nervous[]", "02")?>>간질
              </label>
              <label for="nervous_3">
                <input type="checkbox" id="nervous_3" <?=print_name_and_value("nervous[]", "03")?>>기타
              </label>
              (<input type="text" name="nervous_etc" value="<?=$rec['nervous_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>근골격계</th>
            <td>
              <label for="musculoskeletal_0">
                <input type="checkbox" id="musculoskeletal_0" <?=print_name_and_value("musculoskeletal[]", "00")?>>관절염
              </label>
              <label for="musculoskeletal_1">
                <input type="checkbox" id="musculoskeletal_1" <?=print_name_and_value("musculoskeletal[]", "01")?>>요통, 좌골통
              </label>
              <label for="musculoskeletal_2">
                <input type="checkbox" id="musculoskeletal_2" <?=print_name_and_value("musculoskeletal[]", "02")?>>골절 등 후유증
              </label>
              <label for="musculoskeletal_3">
                <input type="checkbox" id="musculoskeletal_3" <?=print_name_and_value("musculoskeletal[]", "03")?>>기타
              </label>
              (<input type="text" name="musculoskeletal_etc" value="<?=$rec['musculoskeletal_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>정신, 행동장애</th>
            <td>
              <label for="mental_0">
                <input type="checkbox" id="mental_0" <?=print_name_and_value("mental[]", "00")?>>우울증
              </label>
              <label for="mental_1">
                <input type="checkbox" id="mental_1" <?=print_name_and_value("mental[]", "01")?>>수면장애
              </label>
              <label for="mental_2">
                <input type="checkbox" id="mental_2" <?=print_name_and_value("mental[]", "02")?>>정신질환
              </label>
              <label for="mental_3">
                <input type="checkbox" id="mental_3" <?=print_name_and_value("mental[]", "03")?>>심근경색증
              </label>
              <label for="mental_4">
                <input type="checkbox" id="mental_4" <?=print_name_and_value("mental[]", "04")?>>기타
              </label>
              (<input type="text" name="mental_etc" value="<?=$rec['mental_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>호흡기계</th>
            <td>
              <label for="breath_0">
                <input type="checkbox" id="breath_0" <?=print_name_and_value("breath[]", "00")?>>호흡곤란
              </label>
              <label for="breath_1">
                <input type="checkbox" id="breath_1" <?=print_name_and_value("breath[]", "01")?>>결핵
              </label>
              <label for="breath_2">
                <input type="checkbox" id="breath_2" <?=print_name_and_value("breath[]", "02")?>>기타
              </label>
              (<input type="text" name="breath_etc" value="<?=$rec['breath_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>만성신장질환</th>
            <td>
              <label for="kidney_0">
                <input type="checkbox" id="chronic_kidney_0" <?=print_name_and_value("kidney[]", "00")?>>만성신부증
              </label>
              (
              <label for="chronic_kidney_1">
                <input type="radio" id="chronic_kidney_1" <?=print_name_and_value("kidney_sub", "00")?>>복막투석
              </label>
              <label for="chronic_kidney_2">
                <input type="radio" id="chronic_kidney_2" <?=print_name_and_value("kidney_sub", "01")?>>혈액투석
              </label>
              )
              <label for="chronic_kidney_3">
                <input type="checkbox" id="chronic_kidney_3" <?=print_name_and_value("kidney[]", "01")?>>기타
              </label>
              (<input type="text" name="kidney_etc" value="<?=$rec['kidney_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>기타질환</th>
            <td>
              <label for="other_0">
                <input type="checkbox" id="other_0" <?=print_name_and_value("other[]", "00")?>>알레르기
              </label>
              (
              <label for="other_1">
                <input type="checkbox" id="other_1" <?=print_name_and_value("other[]", "01")?>>식품
              </label>
              (<input type="text" name="other_etc1" value="<?=$rec['other_etc1'] ?: ''?>" />)
              <label for="other_2">
                <input type="checkbox" id="other_2" <?=print_name_and_value("other[]", "02")?>>기타
              </label>
              (<input type="text" name="other_etc2" value="<?=$rec['other_etc2'] ?: ''?>" />)
              )
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="sub_title_wrap">
      <div class="sub_title">
        3. 신체상태(일상생활동작 수행능력)
      </div>
      <div class="sub_title_desc">※ 도움필요도 표기 : 상(전적인 도움),  중(수행도움),  하(준비·지켜보기 도움), 최하(혼자수행)</div>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th style="width:200px">기본동작 항목</th>
            <th>확인</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>체위변경 하기</th>
            <td>
              <label for="physical1_0">
                <input type="radio" id="physical1_0" <?=print_name_and_value("physical1", "00")?>>상
              </label>
              <label for="physical1_1">
                <input type="radio" id="physical1_1" <?=print_name_and_value("physical1", "01")?>>중
              </label>
              <label for="physical1_2">
                <input type="radio" id="physical1_2" <?=print_name_and_value("physical1", "02")?>>하
              </label>
              <label for="physical1_3">
                <input type="radio" id="physical1_3" <?=print_name_and_value("physical1", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>일어나 앉기</th>
            <td>
              <label for="physical2_0">
                <input type="radio" id="physical2_0" <?=print_name_and_value("physical2", "00")?>>상
              </label>
              <label for="physical2_1">
                <input type="radio" id="physical2_1" <?=print_name_and_value("physical2", "01")?>>중
              </label>
              <label for="physical2_2">
                <input type="radio" id="physical2_2" <?=print_name_and_value("physical2", "02")?>>하
              </label>
              <label for="physical2_3">
                <input type="radio" id="physical2_3" <?=print_name_and_value("physical2", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>일어서기</th>
            <td>
              <label for="physical3_0">
                <input type="radio" id="physical3_0" <?=print_name_and_value("physical3", "00")?>>상
              </label>
              <label for="physical3_1">
                <input type="radio" id="physical3_1" <?=print_name_and_value("physical3", "01")?>>중
              </label>
              <label for="physical3_2">
                <input type="radio" id="physical3_2" <?=print_name_and_value("physical3", "02")?>>하
              </label>
              <label for="physical3_3">
                <input type="radio" id="physical3_3" <?=print_name_and_value("physical3", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>이동(옮겨 앉기)</th>
            <td>
              <label for="physical4_0">
                <input type="radio" id="physical4_0" <?=print_name_and_value("physical4", "00")?>>상
              </label>
              <label for="physical4_1">
                <input type="radio" id="physical4_1" <?=print_name_and_value("physical4", "01")?>>중
              </label>
              <label for="physical4_2">
                <input type="radio" id="physical4_2" <?=print_name_and_value("physical4", "02")?>>하
              </label>
              <label for="physical4_3">
                <input type="radio" id="physical4_3" <?=print_name_and_value("physical4", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>실내보행(보장구사용)</th>
            <td>
              <label for="physical5_0">
                <input type="radio" id="physical5_0" <?=print_name_and_value("physical5", "00")?>>상
              </label>
              <label for="physical5_1">
                <input type="radio" id="physical5_1" <?=print_name_and_value("physical5", "01")?>>중
              </label>
              <label for="physical5_2">
                <input type="radio" id="physical5_2" <?=print_name_and_value("physical5", "02")?>>하
              </label>
              <label for="physical5_3">
                <input type="radio" id="physical5_3" <?=print_name_and_value("physical5", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>휠체어 이동</th>
            <td>
              <label for="physical6_0">
                <input type="radio" id="physical6_0" <?=print_name_and_value("physical6", "00")?>>상
              </label>
              <label for="physical6_1">
                <input type="radio" id="physical6_1" <?=print_name_and_value("physical6", "01")?>>중
              </label>
              <label for="physical6_2">
                <input type="radio" id="physical6_2" <?=print_name_and_value("physical6", "02")?>>하
              </label>
              <label for="physical6_3">
                <input type="radio" id="physical6_3" <?=print_name_and_value("physical6", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>근거리 외출하기</th>
            <td>
              <label for="physical7_0">
                <input type="radio" id="physical7_0" <?=print_name_and_value("physical7", "00")?>>상
              </label>
              <label for="physical7_1">
                <input type="radio" id="physical7_1" <?=print_name_and_value("physical7", "01")?>>중
              </label>
              <label for="physical7_2">
                <input type="radio" id="physical7_2" <?=print_name_and_value("physical7", "02")?>>하
              </label>
              <label for="physical7_3">
                <input type="radio" id="physical7_3" <?=print_name_and_value("physical7", "03")?>>최하
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th style="width:200px">일상생활동작 항목</th>
            <th>확인</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>식사하기</th>
            <td>
              <label for="daily1_0">
                <input type="radio" id="daily1_0" <?=print_name_and_value("daily1", "00")?>>상
              </label>
              <label for="daily1_1">
                <input type="radio" id="daily1_1" <?=print_name_and_value("daily1", "01")?>>중
              </label>
              <label for="daily1_2">
                <input type="radio" id="daily1_2" <?=print_name_and_value("daily1", "02")?>>하
              </label>
              <label for="daily1_3">
                <input type="radio" id="daily1_3" <?=print_name_and_value("daily1", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>세수하기</th>
            <td>
              <label for="daily2_0">
                <input type="radio" id="daily2_0" <?=print_name_and_value("daily2", "00")?>>상
              </label>
              <label for="daily2_1">
                <input type="radio" id="daily2_1" <?=print_name_and_value("daily2", "01")?>>중
              </label>
              <label for="daily2_2">
                <input type="radio" id="daily2_2" <?=print_name_and_value("daily2", "02")?>>하
              </label>
              <label for="daily2_3">
                <input type="radio" id="daily2_3" <?=print_name_and_value("daily2", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>양치질(틀니관리)</th>
            <td>
              <label for="daily3_0">
                <input type="radio" id="daily3_0" <?=print_name_and_value("daily3", "00")?>>상
              </label>
              <label for="daily3_1">
                <input type="radio" id="daily3_1" <?=print_name_and_value("daily3", "01")?>>중
              </label>
              <label for="daily3_2">
                <input type="radio" id="daily3_2" <?=print_name_and_value("daily3", "02")?>>하
              </label>
              <label for="daily3_3">
                <input type="radio" id="daily3_3" <?=print_name_and_value("daily3", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>옷 벗고 입기</th>
            <td>
              <label for="daily4_0">
                <input type="radio" id="daily4_0" <?=print_name_and_value("daily4", "00")?>>상
              </label>
              <label for="daily4_1">
                <input type="radio" id="daily4_1" <?=print_name_and_value("daily4", "01")?>>중
              </label>
              <label for="daily4_2">
                <input type="radio" id="daily4_2" <?=print_name_and_value("daily4", "02")?>>하
              </label>
              <label for="daily4_3">
                <input type="radio" id="daily4_3" <?=print_name_and_value("daily4", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>화장실(이동변기)사용</th>
            <td>
              <label for="daily5_0">
                <input type="radio" id="daily5_0" <?=print_name_and_value("daily5", "00")?>>상
              </label>
              <label for="daily5_1">
                <input type="radio" id="daily5_1" <?=print_name_and_value("daily5", "01")?>>중
              </label>
              <label for="daily5_2">
                <input type="radio" id="daily5_2" <?=print_name_and_value("daily5", "02")?>>하
              </label>
              <label for="daily5_3">
                <input type="radio" id="daily5_3" <?=print_name_and_value("daily5", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>기저귀 갈기</th>
            <td>
              <label for="daily6_0">
                <input type="radio" id="daily6_0" <?=print_name_and_value("daily6", "00")?>>상
              </label>
              <label for="daily6_1">
                <input type="radio" id="daily6_1" <?=print_name_and_value("daily6", "01")?>>중
              </label>
              <label for="daily6_2">
                <input type="radio" id="daily6_2" <?=print_name_and_value("daily6", "02")?>>하
              </label>
              <label for="daily6_3">
                <input type="radio" id="daily6_3" <?=print_name_and_value("daily6", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>목욕하기</th>
            <td>
              <label for="daily7_0">
                <input type="radio" id="daily7_0" <?=print_name_and_value("daily7", "00")?>>상
              </label>
              <label for="daily7_1">
                <input type="radio" id="daily7_1" <?=print_name_and_value("daily7", "01")?>>중
              </label>
              <label for="daily7_2">
                <input type="radio" id="daily7_2" <?=print_name_and_value("daily7", "02")?>>하
              </label>
              <label for="daily7_3">
                <input type="radio" id="daily7_3" <?=print_name_and_value("daily7", "03")?>>최하
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th style="width:200px">수단적일상생활 항목</th>
            <th>확인</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>몸단장하기</th>
            <td>
              <label for="inst1_0">
                <input type="radio" id="inst1_0" <?=print_name_and_value("inst1", "00")?>>상
              </label>
              <label for="inst1_1">
                <input type="radio" id="inst1_1" <?=print_name_and_value("inst1", "01")?>>중
              </label>
              <label for="inst1_2">
                <input type="radio" id="inst1_2" <?=print_name_and_value("inst1", "02")?>>하
              </label>
              <label for="inst1_3">
                <input type="radio" id="inst1_3" <?=print_name_and_value("inst1", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>식사준비</th>
            <td>
              <label for="inst2_0">
                <input type="radio" id="inst2_0" <?=print_name_and_value("inst2", "00")?>>상
              </label>
              <label for="inst2_1">
                <input type="radio" id="inst2_1" <?=print_name_and_value("inst2", "01")?>>중
              </label>
              <label for="inst2_2">
                <input type="radio" id="inst2_2" <?=print_name_and_value("inst2", "02")?>>하
              </label>
              <label for="inst2_3">
                <input type="radio" id="inst2_3" <?=print_name_and_value("inst2", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>청소하기</th>
            <td>
              <label for="inst3_0">
                <input type="radio" id="inst3_0" <?=print_name_and_value("inst3", "00")?>>상
              </label>
              <label for="inst3_1">
                <input type="radio" id="inst3_1" <?=print_name_and_value("inst3", "01")?>>중
              </label>
              <label for="inst3_2">
                <input type="radio" id="inst3_2" <?=print_name_and_value("inst3", "02")?>>하
              </label>
              <label for="inst3_3">
                <input type="radio" id="inst3_3" <?=print_name_and_value("inst3", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>빨래하기</th>
            <td>
              <label for="inst4_0">
                <input type="radio" id="inst4_0" <?=print_name_and_value("inst4", "00")?>>상
              </label>
              <label for="inst4_1">
                <input type="radio" id="inst4_1" <?=print_name_and_value("inst4", "01")?>>중
              </label>
              <label for="inst4_2">
                <input type="radio" id="inst4_2" <?=print_name_and_value("inst4", "02")?>>하
              </label>
              <label for="inst4_3">
                <input type="radio" id="inst4_3" <?=print_name_and_value("inst4", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>약챙겨먹기</th>
            <td>
              <label for="inst5_0">
                <input type="radio" id="inst5_0" <?=print_name_and_value("inst5", "00")?>>상
              </label>
              <label for="inst5_1">
                <input type="radio" id="inst5_1" <?=print_name_and_value("inst5", "01")?>>중
              </label>
              <label for="inst5_2">
                <input type="radio" id="inst5_2" <?=print_name_and_value("inst5", "02")?>>하
              </label>
              <label for="inst5_3">
                <input type="radio" id="inst5_3" <?=print_name_and_value("inst5", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>전화사용하기</th>
            <td>
              <label for="inst6_0">
                <input type="radio" id="inst6_0" <?=print_name_and_value("inst6", "00")?>>상
              </label>
              <label for="inst6_1">
                <input type="radio" id="inst6_1" <?=print_name_and_value("inst6", "01")?>>중
              </label>
              <label for="inst6_2">
                <input type="radio" id="inst6_2" <?=print_name_and_value("inst6", "02")?>>하
              </label>
              <label for="inst6_3">
                <input type="radio" id="inst6_3" <?=print_name_and_value("inst6", "03")?>>최하
              </label>
            </td>
          </tr>
          <tr>
            <th>교통수단이용</th>
            <td>
              <label for="inst7_0">
                <input type="radio" id="inst7_0" <?=print_name_and_value("inst7", "00")?>>상
              </label>
              <label for="inst7_1">
                <input type="radio" id="inst7_1" <?=print_name_and_value("inst7", "01")?>>중
              </label>
              <label for="inst7_2">
                <input type="radio" id="inst7_2" <?=print_name_and_value("inst7", "02")?>>하
              </label>
              <label for="inst7_3">
                <input type="radio" id="inst7_3" <?=print_name_and_value("inst7", "03")?>>최하
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="textarea_head">종합의견</div>
    <textarea name="body_content"><?= $rec['body_content'] ?: '' ?></textarea>


    <div class="sub_title_wrap">
      <div class="sub_title">
        4. 재활상태
      </div>
      <div class="sub_title_desc">※ 표기 : □에 V표</div>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th colspan="3">확인</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>운동장애</th>
            <td>
              <label for="exercise_0">
                <input type="checkbox" id="exercise_0" <?=print_name_and_value("exercise[]", "00")?>>우측상지
              </label>
              <label for="exercise_1">
                <input type="checkbox" id="exercise_1" <?=print_name_and_value("exercise[]", "01")?>>좌측상지
              </label>
              <label for="exercise_2">
                <input type="checkbox" id="exercise_2" <?=print_name_and_value("exercise[]", "02")?>>우측하지
              </label>
              <label for="exercise_3">
                <input type="checkbox" id="exercise_3" <?=print_name_and_value("exercise[]", "03")?>>좌측하지
              </label>
            </td>
          </tr>
          <tr>
            <th>관절구축</th>
            <td>
              <label for="joint_0">
                <input type="checkbox" id="joint_0" <?=print_name_and_value("joint[]", "00")?>>어깨관절(좌/우) 
              </label>
              <label for="joint_1">
                <input type="checkbox" id="joint_1" <?=print_name_and_value("joint[]", "01")?>>팔꿈치관절(좌/우)  
              </label>
              <label for="joint_2">
                <input type="checkbox" id="joint_2" <?=print_name_and_value("joint[]", "02")?>>손목 및 수지관절(좌/우)    
              </label>
              <label for="joint_3">
                <input type="checkbox" id="joint_3" <?=print_name_and_value("joint[]", "03")?>>고관절 (좌/우)  
              </label>
              <label for="joint_4">
                <input type="checkbox" id="joint_4" <?=print_name_and_value("joint[]", "04")?>>무릎관절(좌/우)    
              </label>
              <label for="joint_5">
                <input type="checkbox" id="joint_5" <?=print_name_and_value("joint[]", "05")?>>발목관절(좌/우)
              </label>
            </td>
          </tr>
          <tr>
            <th rowspan="2">보행장애</th>
            <td>
              <label for="walking_disorder_1_0">
                <input type="checkbox" id="walking_disorder_1_0" <?=print_name_and_value("walking_disorder[]", "00")?>>지난 3개월 간 낙상 경험
              </label>
              (
              <label for="walking_disorder_1_1">
                <input type="radio" id="walking_disorder_1_1" <?=print_name_and_value("walking_disorder_sub1", "00")?>>매일
              </label>
              <label for="walking_disorder_1_2">
                <input type="radio" id="walking_disorder_1_2" <?=print_name_and_value("walking_disorder_sub1", "01")?>>주1회이상    
              </label>
              <label for="walking_disorder_1_3">
                <input type="radio" id="walking_disorder_1_3" <?=print_name_and_value("walking_disorder_sub1", "02")?>>월1회이상 
              </label>
              <label for="walking_disorder_1_4">
                <input type="radio" id="walking_disorder_1_4" <?=print_name_and_value("walking_disorder_sub1", "03")?>>가끔
              </label>
              )
            </td>
          </tr>
          <tr>
            <td>
              <label for="walking_disorder_2_0">
                <input type="checkbox" id="walking_disorder_2_0" <?=print_name_and_value("walking_disorder[]", "01")?>>걸음걸이 및 균형
              </label>
              (
              <label for="walking_disorder_2_1">
                <input type="radio" id="walking_disorder_2_1" <?=print_name_and_value("walking_disorder_sub2", "00")?>>서거나 걸을 때 균형을 유지하지 못함 
              </label>
              <label for="walking_disorder_2_2">
                <input type="radio" id="walking_disorder_2_2" <?=print_name_and_value("walking_disorder_sub2", "01")?>>일어서거나 걸을 때 어지러움    
              </label>
              <label for="walking_disorder_2_3">
                <input type="radio" id="walking_disorder_2_3" <?=print_name_and_value("walking_disorder_sub2", "02")?>>보조도구나 부축해서 걷기  
              </label>
              )
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="textarea_head">종합의견</div>
    <textarea name="rehabilitation_content"><?= $rec['rehabilitation_content'] ?: '' ?></textarea>


    <div class="sub_title_wrap">
      <div class="sub_title">
        5. 간호처치상태
      </div>
      <div class="sub_title_desc">※ 표기 : □에 V표</div>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th colspan="3">확인</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>호흡</th>
            <td colspan="2">
              <label for="nurse_breath_0">
                <input type="checkbox" id="nurse_breath_0" <?=print_name_and_value("nurse_breath[]", "00")?>>기관지 절개관 간호
              </label>
              <label for="nurse_breath_1">
                <input type="checkbox" id="nurse_breath_1" <?=print_name_and_value("nurse_breath[]", "01")?>>흡인
              </label>
              <label for="nurse_breath_2">
                <input type="checkbox" id="nurse_breath_2" <?=print_name_and_value("nurse_breath[]", "02")?>>산소요법
              </label>
              <label for="nurse_breath_3">
                <input type="checkbox" id="nurse_breath_3" <?=print_name_and_value("nurse_breath[]", "03")?>>기타
              </label>
              (<input type="text" name="nurse_breath_etc" value="<?=$rec['nurse_breath_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>영양</th>
            <td colspan="2">
              <label for="nurse_nutrition_0">
                <input type="checkbox" id="nurse_nutrition_0" <?=print_name_and_value("nurse_nutrition[]", "00")?>>기관영양
              </label>
              (
              <label for="nurse_nutrition_1">
                <input type="radio" id="nurse_nutrition_1" <?=print_name_and_value("nurse_nutrition_sub", "00")?>>비위관
              </label>
              <label for="nurse_nutrition_2">
                <input type="radio" id="nurse_nutrition_2" <?=print_name_and_value("nurse_nutrition_sub", "01")?>>위관
              </label>
              )
              <label for="nurse_nutrition_3">
                <input type="checkbox" id="nurse_nutrition_3" <?=print_name_and_value("nurse_nutrition[]", "01")?>>치료식이
              </label>
              (<input type="text" name="nurse_nutrition_etc1" value="<?=$rec['nurse_nutrition_etc1'] ?: ''?>" />)
              <label for="nurse_nutrition_4">
                <input type="checkbox" id="nurse_nutrition_4" <?=print_name_and_value("nurse_nutrition[]", "02")?>>기타
              </label>
              (<input type="text" name="nurse_nutrition_etc2" value="<?=$rec['nurse_nutrition_etc2'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>배설</th>
            <td colspan="2">
              <label for="nurse_excretion_0">
                <input type="checkbox" id="nurse_excretion_0" <?=print_name_and_value("nurse_excretion[]", "00")?>>투석간호
              </label>
              <label for="nurse_excretion_1">
                <input type="checkbox" id="nurse_excretion_1" <?=print_name_and_value("nurse_excretion[]", "01")?>>유치도뇨관
              </label>
              <label for="nurse_excretion_2">
                <input type="checkbox" id="nurse_excretion_2" <?=print_name_and_value("nurse_excretion[]", "02")?>>단순도뇨
              </label>
              <label for="nurse_excretion_3">
                <input type="checkbox" id="nurse_excretion_3" <?=print_name_and_value("nurse_excretion[]", "03")?>>방광루
              </label>
              <label for="nurse_excretion_4">
                <input type="checkbox" id="nurse_excretion_4" <?=print_name_and_value("nurse_excretion[]", "04")?>>장루간호
              </label>
            </td>
          </tr>
          <tr>
            <th>상처</th>
            <td colspan="2">
              <label for="nurse_wound_0">
                <input type="checkbox" id="nurse_wound_0" <?=print_name_and_value("nurse_wound[]", "00")?>>상처간호
              </label>
              (부위:<input type="text" name="nurse_wound_etc" value="<?=$rec['nurse_wound_etc'] ?: ''?>" />)
              <label for="nurse_wound_1">
                <input type="checkbox" id="nurse_wound_1" <?=print_name_and_value("nurse_wound[]", "01")?>>당뇨발간호
              </label>
              <label for="nurse_wound_2">
                <input type="checkbox" id="nurse_wound_2" <?=print_name_and_value("nurse_wound[]", "02")?>>기타
              </label>
            </td>
          </tr>
          <tr>
            <th rowspan="2">욕창</th>
            <th>단계</th>
            <td>
              <label for="nurse_bedsore_step_0">
                <input type="checkbox" id="nurse_bedsore_step_0" <?=print_name_and_value("nurse_bedsore_step[]", "00")?>>1단계
              </label>
              <label for="nurse_bedsore_step_1">
                <input type="checkbox" id="nurse_bedsore_step_1" <?=print_name_and_value("nurse_bedsore_step[]", "01")?>>2단계
              </label>
              <label for="nurse_bedsore_step_2">
                <input type="checkbox" id="nurse_bedsore_step_2" <?=print_name_and_value("nurse_bedsore_step[]", "02")?>>3단계
              </label>
              <label for="nurse_bedsore_step_3">
                <input type="checkbox" id="nurse_bedsore_step_3" <?=print_name_and_value("nurse_bedsore_step[]", "03")?>>4단계
              </label>
            </td>
          </tr>
          <tr>
            <th>부위</th>
            <td>
              <label for="nurse_bedsore_part_0">
                <input type="checkbox" id="nurse_bedsore_part_0" <?=print_name_and_value("nurse_bedsore_part[]", "00")?>>머리
              </label>
              <label for="nurse_bedsore_part_1">
                <input type="checkbox" id="nurse_bedsore_part_1" <?=print_name_and_value("nurse_bedsore_part[]", "01")?>>등
              </label>
              <label for="nurse_bedsore_part_2">
                <input type="checkbox" id="nurse_bedsore_part_2" <?=print_name_and_value("nurse_bedsore_part[]", "02")?>>어깨
              </label>
              <label for="nurse_bedsore_part_3">
                <input type="checkbox" id="nurse_bedsore_part_3" <?=print_name_and_value("nurse_bedsore_part[]", "03")?>>팔꿈치
              </label>
              <label for="nurse_bedsore_part_4">
                <input type="checkbox" id="nurse_bedsore_part_4" <?=print_name_and_value("nurse_bedsore_part[]", "04")?>>엉덩이
              </label>
              <label for="nurse_bedsore_part_5">
                <input type="checkbox" id="nurse_bedsore_part_5" <?=print_name_and_value("nurse_bedsore_part[]", "05")?>>뒤꿈치
              </label>
              <label for="nurse_bedsore_part_6">
                <input type="checkbox" id="nurse_bedsore_part_6" <?=print_name_and_value("nurse_bedsore_part[]", "06")?>>기타
              </label>
              (<input type="text" name="nurse_bedsore_part_etc" value="<?=$rec['nurse_bedsore_part_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th rowspan="3">통증</th>
            <th class="text-left">
              <label for="nurse_ache_0">
                <input type="checkbox" id="nurse_ache_0" <?=print_name_and_value("nurse_ache[]", "00")?>>암 발생 부위</th>
              </label>
            </th>
            <td>
              <label for="nurse_ache_cancer_0">
                <input type="checkbox" id="nurse_ache_cancer_0" <?=print_name_and_value("nurse_ache_cancer[]", "00")?>>폐
              </label>
              <label for="nurse_ache_cancer_1">
                <input type="checkbox" id="nurse_ache_cancer_1" <?=print_name_and_value("nurse_ache_cancer[]", "01")?>>위
              </label>
              <label for="nurse_ache_cancer_2">
                <input type="checkbox" id="nurse_ache_cancer_2" <?=print_name_and_value("nurse_ache_cancer[]", "02")?>>대장
              </label>
              <label for="nurse_ache_cancer_3">
                <input type="checkbox" id="nurse_ache_cancer_3" <?=print_name_and_value("nurse_ache_cancer[]", "03")?>>간
              </label>
              <label for="nurse_ache_cancer_4">
                <input type="checkbox" id="nurse_ache_cancer_4" <?=print_name_and_value("nurse_ache_cancer[]", "04")?>>전립선
              </label>
              <label for="nurse_ache_cancer_5">
                <input type="checkbox" id="nurse_ache_cancer_5" <?=print_name_and_value("nurse_ache_cancer[]", "05")?>>유방
              </label>
              <label for="nurse_ache_cancer_6">
                <input type="checkbox" id="nurse_ache_cancer_6" <?=print_name_and_value("nurse_ache_cancer[]", "06")?>>담낭 및 기타 담도
              </label>
              <label for="nurse_ache_cancer_7">
                <input type="checkbox" id="nurse_ache_cancer_7" <?=print_name_and_value("nurse_ache_cancer[]", "07")?>>기타
              </label>
              (<input type="text" name="nurse_ache_cancer_etc" value="<?=$rec['nurse_ache_cancer_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th class="text-left">
              <label for="nurse_ache_1">
                <input type="checkbox" id="nurse_ache_1" <?=print_name_and_value("nurse_ache[]", "01")?>>일반 통증 부위</th>
              </label>
            </th>
            <td>
              <label for="nurse_ache_normal_0">
                <input type="checkbox" id="nurse_ache_normal_0" <?=print_name_and_value("nurse_ache_normal[]", "00")?>>머리
              </label>
              <label for="nurse_ache_normal_1">
                <input type="checkbox" id="nurse_ache_normal_1" <?=print_name_and_value("nurse_ache_normal[]", "01")?>>상지
              </label>
              <label for="nurse_ache_normal_2">
                <input type="checkbox" id="nurse_ache_normal_2" <?=print_name_and_value("nurse_ache_normal[]", "02")?>>하지
              </label>
              <label for="nurse_ache_normal_3">
                <input type="checkbox" id="nurse_ache_normal_3" <?=print_name_and_value("nurse_ache_normal[]", "03")?>>허리
              </label>
              <label for="nurse_ache_normal_4">
                <input type="checkbox" id="nurse_ache_normal_4" <?=print_name_and_value("nurse_ache_normal[]", "04")?>>등
              </label>
              <label for="nurse_ache_normal_5">
                <input type="checkbox" id="nurse_ache_normal_5" <?=print_name_and_value("nurse_ache_normal[]", "05")?>>복부
              </label>
              (<input type="text" name="nurse_ache_normal_etc" value="<?=$rec['nurse_ache_normal_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th class="text-left">
              <label for="nurse_ache_2">
                <input type="checkbox" id="nurse_ache_2" <?=print_name_and_value("nurse_ache[]", "02")?>>기타</th>
              </label>
            <td>
              <input type="text" style="width:100%" name="nurse_ache_etc" value="<?=$rec['nurse_ache_etc'] ?: ''?>" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="textarea_head">종합의견</div>
    <textarea name="nurse_content"><?= $rec['nurse_content'] ?: '' ?></textarea>



    <div class="sub_title_wrap">
      <div class="sub_title">
        6. 인지상태(인지기능저하, 정신상태, 감정, 문제행동 등)
      </div>
      <div class="sub_title_desc">※ 표기 : □에 V표</div>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th colspan="2">구분</th>
            <th style="width:50px;">확인</th>
          </tr>
        </thead>
        <tbody>
        <tr>
            <th style="width:50px;">1</th>
            <td>
              지남력 저하
              (
              <label for="observe_orientation_0">
                <input type="checkbox" id="observe_orientation_0" <?=print_name_and_value("observe_orientation[]", "00")?>>날짜·시간
              </label>
              <label for="observe_orientation_1">
                <input type="checkbox" id="observe_orientation_1" <?=print_name_and_value("observe_orientation[]", "01")?>>장소
              </label>
              <label for="observe_orientation_2">
                <input type="checkbox" id="observe_orientation_2" <?=print_name_and_value("observe_orientation[]", "02")?>>사람
              </label>
              )
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "01")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">2</th>
            <td>
              기억력 저하
              (
              <label for="observe_memory_0">
                <input type="checkbox" id="observe_memory_0" <?=print_name_and_value("observe_memory[]", "00")?>>단기
              </label>
              <label for="observe_memory_1">
                <input type="checkbox" id="observe_memory_1" <?=print_name_and_value("observe_memory[]", "01")?>>장기
              </label>
              )
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "02")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">3</th>
            <td>
              주의집중력 저하
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "03")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">4</th>
            <td>
              계산력 저하
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "04")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">5</th>
            <td>
              판단력 저하
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "05")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">6</th>
            <td>
              부적절한 옷입기 (상하의 구분 못함, 겉옷과 속옷 구분 못함 등)
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "06")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">7</th>
            <td>
            망상 (부적절한 믿음, 편집증 등) 
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "07")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">8</th>
            <td>
              배회 
              (
              <label for="observe_wender_0">
                <input type="checkbox" id="observe_wender_0" <?=print_name_and_value("observe_wender[]", "00")?>>밖으로 나가려함
              </label>
              <label for="observe_wender_1">
                <input type="checkbox" id="observe_wender_1" <?=print_name_and_value("observe_wender[]", "01")?>>의미없는 서성거림
              </label>
              <label for="observe_wender_2">
                <input type="checkbox" id="observe_wender_2" <?=print_name_and_value("observe_wender[]", "02")?>>길 잃음
              </label>
              )
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "08")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">9</th>
            <td>
              환각 
              (
              <label for="observe_hallucination_0">
                <input type="checkbox" id="observe_hallucination_0" <?=print_name_and_value("observe_hallucination[]", "00")?>>환시
              </label>
              <label for="observe_hallucination_1">
                <input type="checkbox" id="observe_hallucination_1" <?=print_name_and_value("observe_hallucination[]", "01")?>>환청
              </label>
              <label for="observe_hallucination_2">
                <input type="checkbox" id="observe_hallucination_2" <?=print_name_and_value("observe_hallucination[]", "02")?>>환미
              </label>
              <label for="observe_hallucination_3">
                <input type="checkbox" id="observe_hallucination_3" <?=print_name_and_value("observe_hallucination[]", "03")?>>환촉
              </label>
              <label for="observe_hallucination_4">
                <input type="checkbox" id="observe_hallucination_4" <?=print_name_and_value("observe_hallucination[]", "04")?>>기타
              </label>
              )
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "09")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">10</th>
            <td>
              반복적인 행동 (물건감추기, 짐싸기 등 )
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "10")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">11</th>
            <td>
              부적절한 행동  
              (
              <label for="observe_inappropriate_0">
                <input type="checkbox" id="observe_inappropriate_0" <?=print_name_and_value("observe_inappropriate[]", "00")?>>부적절한 성적행동   
              </label>
              <label for="observe_inappropriate_1">
                <input type="checkbox" id="observe_inappropriate_1" <?=print_name_and_value("observe_inappropriate[]", "01")?>>부적절한 일반행동 
              </label>
              )
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "11")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">12</th>
            <td>
              폭력적 행동   
              (
              <label for="observe_violent_0">
                <input type="checkbox" id="observe_violent_0" <?=print_name_and_value("observe_violent[]", "00")?>>신체적인 공격   
              </label>
              <label for="observe_violent_1">
                <input type="checkbox" id="observe_violent_1" <?=print_name_and_value("observe_violent[]", "01")?>>폭언 
              </label>
              <label for="observe_violent_2">
                <input type="checkbox" id="observe_violent_2" <?=print_name_and_value("observe_violent[]", "02")?>>도움에의 저항 
              </label>
              )
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "12")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">13</th>
            <td>
              야간수면장애
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "13")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">14</th>
            <td>
              불결행동
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "14")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">15</th>
            <td>
              식습관 변화  
              (
              <label for="observe_eating_0">
                <input type="checkbox" id="observe_eating_0" <?=print_name_and_value("observe_eating[]", "00")?>>식욕저하   
              </label>
              <label for="observe_eating_1">
                <input type="checkbox" id="observe_eating_1" <?=print_name_and_value("observe_eating[]", "01")?>>식욕증가
              </label>
              <label for="observe_eating_2">
                <input type="checkbox" id="observe_eating_2" <?=print_name_and_value("observe_eating[]", "02")?>>기타
              </label>
              (<input type="text" name="observe_eating_etc" value="<?=$rec['observe_eating_etc'] ?: ''?>" />)
              )
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "15")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">16</th>
            <td>
              먹는 것이 아닌 물건을 먹음
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "16")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">17</th>
            <td>
              불안 
              (
              <label for="observe_unrest_0">
                <input type="checkbox" id="observe_unrest_0" <?=print_name_and_value("observe_unrest[]", "00")?>>혼자 남겨짐에 대한 공포    
              </label>
              <label for="observe_unrest_1">
                <input type="checkbox" id="observe_unrest_1" <?=print_name_and_value("observe_unrest[]", "01")?>>초조
              </label>
              <label for="observe_unrest_2">
                <input type="checkbox" id="observe_unrest_2" <?=print_name_and_value("observe_unrest[]", "02")?>>안절부절
              </label>
              <label for="observe_unrest_3">
                <input type="checkbox" id="observe_unrest_3" <?=print_name_and_value("observe_unrest[]", "03")?>>기타
              </label>
              )
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "17")?> style="margin:0;">
            </td>
          </tr>
          <tr>
            <th style="width:50px;">18</th>
            <td>
              우울 
              (
              <label for="observe_depressed_0">
                <input type="checkbox" id="observe_depressed_0" <?=print_name_and_value("observe_depressed[]", "00")?>>두려움    
              </label>
              <label for="observe_depressed_1">
                <input type="checkbox" id="observe_depressed_1" <?=print_name_and_value("observe_depressed[]", "01")?>>무기력함
              </label>
              <label for="observe_depressed_2">
                <input type="checkbox" id="observe_depressed_2" <?=print_name_and_value("observe_depressed[]", "02")?>>절망
              </label>
              )
            </td>
            <td class="text-center">
              <input type="checkbox" id="observe[]" <?=print_name_and_value("observe[]", "18")?> style="margin:0;">
            </td>
          </tr>
          
        </tbody>
      </table>
    </div>

    <div class="textarea_head">종합의견</div>
    <textarea name="observe_content"><?= $rec['observe_content'] ?: '' ?></textarea>


    <div class="sub_title_wrap">
      <div class="sub_title">
        7. 의사소통
      </div>
      <div class="sub_title_desc">※ 표기 : □에 V표</div>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th>구분</th>
            <th>확인</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>의사소통</th>
            <td>
              <label for="communication_communication_0">
                <input type="radio" id="communication_communication_0" <?=print_name_and_value("communication_communication", "00")?>>모두 이해하고 의사를 표현하다. 
              </label>
              <br>
              <label for="communication_communication_1">
                <input type="radio" id="communication_communication_1" <?=print_name_and_value("communication_communication", "01")?>>대부분 이해하고 의사를 표현한다.    
              </label>
              <br>
              <label for="communication_communication_2">
                <input type="radio" id="communication_communication_2" <?=print_name_and_value("communication_communication", "02")?>>가끔 이해하고 의사를 표현한다.
              </label>
              <br>
              <label for="communication_communication_3">
                <input type="radio" id="communication_communication_3" <?=print_name_and_value("communication_communication", "03")?>>거의 이해하지 못하고 의사를 전달하지 못한다.
              </label>
            </td>
          </tr>
          <tr>
            <th>발음능력</th>
            <td>
              <label for="communication_pronounce_0">
                <input type="radio" id="communication_pronounce_0" <?=print_name_and_value("communication_pronounce", "00")?>>정확하게 발음이 가능하다.   
              </label>
              <br>
              <label for="communication_pronounce_1">
                <input type="radio" id="communication_pronounce_1" <?=print_name_and_value("communication_pronounce", "01")?>>웅얼거리는 소리로만 한다.  
              </label>
              <br>
              <label for="communication_pronounce_2">
                <input type="radio" id="communication_pronounce_2" <?=print_name_and_value("communication_pronounce", "02")?>>간혹 어눌한 발음이 섞인다.  
              </label>
              <br>
              <label for="communication_pronounce_3">
                <input type="radio" id="communication_pronounce_3" <?=print_name_and_value("communication_pronounce", "03")?>>전혀 발음하지 못한다.
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="textarea_head">종합의견</div>
    <textarea name="communication_content"><?= $rec['communication_content'] ?: '' ?></textarea>


    <div class="sub_title_wrap">
      <div class="sub_title">
        8. 가족 및 지지체계
      </div>
      <div class="sub_title_desc">※ 표기 : □에 V표</div>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th>구분</th>
            <th colspan="4">확인</th>
          </tr>
        </thead>
        <tbody>
        <tr>
            <th>동거인</th>
            <td colspan="4">
              <label for="family_inmate_0">
                <input type="checkbox" id="family_inmate_0" <?=print_name_and_value("family_inmate[]", "00")?>>독거 
              </label>
              <label for="family_inmate_1">
                <input type="checkbox" id="family_inmate_1" <?=print_name_and_value("family_inmate[]", "01")?>>배우자   
              </label>
              <label for="family_inmate_2">
                <input type="checkbox" id="family_inmate_2" <?=print_name_and_value("family_inmate[]", "02")?>>부모    
              </label>
              <label for="family_inmate_3">
                <input type="checkbox" id="family_inmate_3" <?=print_name_and_value("family_inmate[]", "03")?>>자녀   
              </label>
              <label for="family_inmate_4">
                <input type="checkbox" id="family_inmate_3" <?=print_name_and_value("family_inmate[]", "04")?>>자부, 사위   
              </label>
              <label for="family_inmate_5">
                <input type="checkbox" id="family_inmate_3" <?=print_name_and_value("family_inmate[]", "05")?>>손자녀     
              </label>
              <label for="family_inmate_6">
                <input type="checkbox" id="family_inmate_3" <?=print_name_and_value("family_inmate[]", "06")?>>친척         
              </label>
              <label for="family_inmate_7">
                <input type="checkbox" id="family_inmate_3" <?=print_name_and_value("family_inmate[]", "07")?>>친구․이웃   
              </label>
              <label for="family_inmate_8">
                <input type="checkbox" id="family_inmate_3" <?=print_name_and_value("family_inmate[]", "08")?>>기타   
              </label>
              (<input type="text" name="family_inmate_etc" value="<?=$rec['family_inmate_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>자녀수</th>
            <td colspan="4">
              <label for="family_children_0">
                <input type="radio" id="family_children_0" <?=print_name_and_value("family_children", "00")?>>무 
              </label>
              <label for="family_children_1">
                <input type="radio" id="family_children_1" <?=print_name_and_value("family_children", "01")?>>유
              </label>
              (아들<input type="text" name="family_children_son" value="<?=$rec['family_children_son'] ?: '0'?>" />명, 딸<input type="text" name="family_children_daughter" value="<?=$rec['family_children_daughter'] ?: '0'?>" />명)
            </td>
          </tr>
          <tr>
            <th>주수발자</th>
            <td colspan="4">
              <label for="family_helper_0">
                <input type="radio" id="family_helper_0" <?=print_name_and_value("family_helper", "00")?>>무 
              </label>
              <label for="family_helper_1">
                <input type="radio" id="family_helper_1" <?=print_name_and_value("family_helper", "01")?>>유
              </label>
              (
              <label for="family_helper_age_0">
                <input type="radio" id="family_helper_age_0" <?=print_name_and_value("family_helper_age", "00")?>>10~20대    
              </label>
              <label for="family_helper_age_1">
                <input type="radio" id="family_helper_age_1" <?=print_name_and_value("family_helper_age", "01")?>>20~30대    
              </label>
              <label for="family_helper_age_2">
                <input type="radio" id="family_helper_age_2" <?=print_name_and_value("family_helper_age", "02")?>>30~40대    
              </label>
              <label for="family_helper_age_3">
                <input type="radio" id="family_helper_age_3" <?=print_name_and_value("family_helper_age", "03")?>>40~50대    
              </label>
              <label for="family_helper_age_4">
                <input type="radio" id="family_helper_age_4" <?=print_name_and_value("family_helper_age", "04")?>>50~60대    
              </label>
              <label for="family_helper_age_5">
                <input type="radio" id="family_helper_age_5" <?=print_name_and_value("family_helper_age", "05")?>>70대  
              </label>
              <label for="family_helper_age_6">
                <input type="radio" id="family_helper_age_6" <?=print_name_and_value("family_helper_age", "06")?>>80대 이상
              </label>
              )
            </td>
          </tr>
          <tr>
            <th>주수발자 관계</th>
            <td colspan="4">
              <label for="family_helper_relation_0">
                <input type="radio" id="family_helper_relation_0" <?=print_name_and_value("family_helper_relation", "00")?>>배우자   
              </label>
              <label for="family_helper_relation_1">
                <input type="radio" id="family_helper_relation_1" <?=print_name_and_value("family_helper_relation", "01")?>>자녀
              </label>
              <label for="family_helper_relation_2">
                <input type="radio" id="family_helper_relation_2" <?=print_name_and_value("family_helper_relation", "02")?>>자부
              </label>
              <label for="family_helper_relation_3">
                <input type="radio" id="family_helper_relation_3" <?=print_name_and_value("family_helper_relation", "03")?>>사위
              </label>
              <label for="family_helper_relation_4">
                <input type="radio" id="family_helper_relation_4" <?=print_name_and_value("family_helper_relation", "04")?>>형제자매
              </label>
              <label for="family_helper_relation_5">
                <input type="radio" id="family_helper_relation_5" <?=print_name_and_value("family_helper_relation", "05")?>>친척
              </label>
              <label for="family_helper_relation_6">
                <input type="radio" id="family_helper_relation_6" <?=print_name_and_value("family_helper_relation", "06")?>>기타
              </label>
              (<input type="text" name="family_helper_relation_etc" value="<?=$rec['family_helper_relation_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>주수발자 경제상태</th>
            <td colspan="4">
              <label for="family_helper_economy_0">
                <input type="radio" id="family_helper_economy_0" <?=print_name_and_value("family_helper_economy", "00")?>>안정   
              </label>
              <label for="family_helper_economy_1">
                <input type="radio" id="family_helper_economy_1" <?=print_name_and_value("family_helper_economy", "01")?>>불안정
              </label>
              <label for="family_helper_economy_2">
                <input type="radio" id="family_helper_economy_2" <?=print_name_and_value("family_helper_economy", "02")?>>연금생활
              </label>
              <label for="family_helper_economy_3">
                <input type="radio" id="family_helper_economy_3" <?=print_name_and_value("family_helper_economy", "03")?>>기초생활수급
              </label>
              <label for="family_helper_economy_4">
                <input type="radio" id="family_helper_economy_4" <?=print_name_and_value("family_helper_economy", "04")?>>의료급여
              </label>
            </td>
          </tr>
          <tr>
            <th>주수발자 부양부담</th>
            <td colspan="4">
              <label for="family_helper_burden_0">
                <input type="radio" id="family_helper_burden_0" <?=print_name_and_value("family_helper_burden", "00")?>>전혀 부담되지 않음   
              </label>
              <label for="family_helper_burden_1">
                <input type="radio" id="family_helper_burden_1" <?=print_name_and_value("family_helper_burden", "01")?>>아주 가끔 부담됨    
              </label>
              <label for="family_helper_burden_2">
                <input type="radio" id="family_helper_burden_2" <?=print_name_and_value("family_helper_burden", "02")?>>가끔 부담됨 
              </label>
              <label for="family_helper_burden_3">
                <input type="radio" id="family_helper_burden_3" <?=print_name_and_value("family_helper_burden", "03")?>>자주 부담됨   
              </label>
              <label for="family_helper_burden_4">
                <input type="radio" id="family_helper_burden_4" <?=print_name_and_value("family_helper_burden", "04")?>>항상 부담됨
              </label>
            </td>
          </tr>
          <tr>
            <th rowspan="2">진료병원</th>
            <th>병원명(진료과)</th>
            <td>
              <input type="text" style="width:100%" name="family_hospital_name" value="<?=$rec['family_hospital_name'] ?: ''?>" />
            </td>
            <th rowspan="2">전화번호</th>
            <td rowspan="2">
              <input type="text" style="width:100%" name="family_hospital_tel" value="<?=$rec['family_hospital_tel'] ?: ''?>" />
            </td>
          </tr>
          <tr>
            <th>정기진료</th>
            <td>
              <label for="family_hosptial_regularly_0">
                <input type="radio" id="family_hosptial_regularly_0" <?=print_name_and_value("family_hosptial_regularly", "00")?>>무 
              </label>
              <label for="family_hosptial_regularly_1">
                <input type="radio" id="family_hosptial_regularly_1" <?=print_name_and_value("family_hosptial_regularly", "01")?>>유
              </label>
          </tr>
          <tr>
            <th>약복용</th>
            <td colspan="4">
              <label for="family_hospital_medicine_0">
                <input type="radio" id="family_hospital_medicine_0" <?=print_name_and_value("family_hospital_medicine", "00")?>>있음  
              </label>
              (횟수 <input type="text" name="family_hospital_medicine_day" value="<?=$rec['family_hospital_medicine_day'] ?: ''?>" />/일, <input type="text" name="family_hospital_medicine_week" value="<?=$rec['family_hospital_medicine_week'] ?: ''?>" />/주, 1회 약복용개수 <input type="text" name="family_hospital_medicine_amount" value="<?=$rec['family_hospital_medicine_amount'] ?: ''?>" />개)
              <br>
              <label for="family_hospital_medicine_1">
                <input type="radio" id="family_hospital_medicine_1" <?=print_name_and_value("family_hospital_medicine", "01")?>>없음
              </label>
            </td>
          </tr>
          <tr>
            <th>종교활동</th>
            <td colspan="4">
              <label for="family_religion_0">
                <input type="radio" id="family_religion_0" <?=print_name_and_value("family_religion", "00")?>>천주교  
              </label>
              <label for="family_religion_1">
                <input type="radio" id="family_religion_1" <?=print_name_and_value("family_religion", "01")?>>기독교
              </label>
              <label for="family_religion_2">
                <input type="radio" id="family_religion_2" <?=print_name_and_value("family_religion", "02")?>>불교
              </label>
              <label for="family_religion_3">
                <input type="radio" id="family_religion_3" <?=print_name_and_value("family_religion", "03")?>>기타
              </label>
              (<input type="text" name="family_religion_etc" value="<?=$rec['family_religion_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>지역사회자원</th>
            <td colspan="4">
              <label for="family_resource_0">
                <input type="checkbox" id="family_resource_0" <?=print_name_and_value("family_resource[]", "00")?>>노인맞춤돌봄서비스 
              </label>
              (
                <label for="family_resource_0_0">
                  <input type="checkbox" id="family_resource_0_0" <?=print_name_and_value("family_resource_sub[]", "00")?>>노인돌봄기본서비스
                </label>
                <label for="family_resource_0_1">
                  <input type="checkbox" id="family_resource_0_1" <?=print_name_and_value("family_resource_sub[]", "01")?>>노인돌봄종합서비스 
                </label>
                <label for="family_resource_0_2">
                  <input type="checkbox" id="family_resource_0_2" <?=print_name_and_value("family_resource_sub[]", "02")?>>단기가사서비스 
                </label>
                <label for="family_resource_0_3">
                  <input type="checkbox" id="family_resource_0_3" <?=print_name_and_value("family_resource_sub[]", "03")?>>독거노인사회관계활성화 
                </label>
                <label for="family_resource_0_4">
                  <input type="checkbox" id="family_resource_0_4" <?=print_name_and_value("family_resource_sub[]", "04")?>>초기독거노인자립지원 
                </label>
              )
              <br>
              <label for="family_resource_1">
                <input type="checkbox" id="family_resource_1" <?=print_name_and_value("family_resource[]", "01")?>>가사간병   
              </label>
              <label for="family_resource_2">
                <input type="checkbox" id="family_resource_2" <?=print_name_and_value("family_resource[]", "02")?>>재가복지
              </label>
              <label for="family_resource_3">
                <input type="checkbox" id="family_resource_3" <?=print_name_and_value("family_resource[]", "03")?>>급식 및 도시락배달
              </label>
              <label for="family_resource_4">
                <input type="checkbox" id="family_resource_4" <?=print_name_and_value("family_resource[]", "04")?>>보건소사업
              </label>
              <label for="family_resource_5">
                <input type="checkbox" id="family_resource_5" <?=print_name_and_value("family_resource[]", "05")?>>개인간병인
              </label>
              <label for="family_resource_6">
                <input type="checkbox" id="family_resource_6" <?=print_name_and_value("family_resource[]", "06")?>>산업재해 간병인
              </label>
              <label for="family_resource_7">
                <input type="checkbox" id="family_resource_7" <?=print_name_and_value("family_resource[]", "07")?>>치매안심센터
              </label>
              <label for="family_resource_8">
                <input type="checkbox" id="family_resource_8" <?=print_name_and_value("family_resource[]", "08")?>>복지관(마을회관)
              </label>
              <label for="family_resource_9">
                <input type="checkbox" id="family_resource_9" <?=print_name_and_value("family_resource[]", "09")?>>노인정
              </label>
              <label for="family_resource_10">
                <input type="checkbox" id="family_resource_10" <?=print_name_and_value("family_resource[]", "10")?>>이동서비스
              </label>
              <label for="family_resource_11">
                <input type="checkbox" id="family_resource_11" <?=print_name_and_value("family_resource[]", "11")?>>종교단체
              </label>
              <label for="family_resource_12">
                <input type="checkbox" id="family_resource_12" <?=print_name_and_value("family_resource[]", "12")?>>이·미용
              </label>
              <label for="family_resource_13">
                <input type="checkbox" id="family_resource_13" <?=print_name_and_value("family_resource[]", "13")?>>주거개선사업
              </label>
            </td>
          </tr>

        </tbody>
      </table>
    </div>

    <div class="textarea_head">종합의견</div>
    <textarea name="family_content"><?= $rec['family_content'] ?: '' ?></textarea>


    <div class="sub_title_wrap">
      <div class="sub_title">
        9. 주거환경상태
      </div>
      <div class="sub_title_desc">※ 표기 : □에 V표</div>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th>구분</th>
            <th colspan="4">확인</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>난방</th>
            <td>
              <label for="living_heating_0">
                <input type="radio" id="living_heating_0" <?=print_name_and_value("living_heating", "00")?>>양호
              </label>
              <label for="living_heating_1">
                <input type="radio" id="living_heating_1" <?=print_name_and_value("living_heating", "01")?>>불량
              </label>
            </td>
            <th colspan="2">환기</th>
            <td>
              <label for="living_ventilation_0">
                <input type="radio" id="living_ventilation_0" <?=print_name_and_value("living_ventilation", "00")?>>양호
              </label>
              <label for="living_ventilation_1">
                <input type="radio" id="living_ventilation_1" <?=print_name_and_value("living_ventilation", "01")?>>불량
              </label>
            </td>
          </tr>
          <tr>
            <th rowspan="2">문턱</th>
            <td rowspan="2">
              <label for="living_threshold_0">
                <input type="radio" id="living_threshold_0" <?=print_name_and_value("living_threshold", "00")?>>양호
              </label>
              <label for="living_threshold_1">
                <input type="radio" id="living_threshold_1" <?=print_name_and_value("living_threshold", "01")?>>불량(높음)
              </label>
            </td>
            <th rowspan="2">계단</th>
            <th>실내</th>
            <td>
              <label for="living_stairs_inner_0">
                <input type="radio" id="living_stairs_inner_0" <?=print_name_and_value("living_stairs_inner", "00")?>>양호
              </label>
              <label for="living_stairs_inner_1">
                <input type="radio" id="living_stairs_inner_1" <?=print_name_and_value("living_stairs_inner", "01")?>>불량
              </label>
            </td>
          </tr>
          <tr>
            <th>
              실외
            </th>
            <td>
              <label for="living_stairs_out_0">
                <input type="radio" id="living_stairs_out_0" <?=print_name_and_value("living_stairs_out", "00")?>>양호
              </label>
              <label for="living_stairs_out_1">
                <input type="radio" id="living_stairs_out_1" <?=print_name_and_value("living_stairs_out", "01")?>>불량
              </label>
            </td>
          </tr>
          <tr>
            <th>화장실</th>
            <td>
              <label for="living_toilet_0">
                <input type="radio" id="living_toilet_0" <?=print_name_and_value("living_toilet", "00")?>>실내
              </label>
              <label for="living_toilet_1">
                <input type="radio" id="living_toilet_1" <?=print_name_and_value("living_toilet", "01")?>>실외
              </label>
            </td>
            <th colspan="2">좌변기</th>
            <td>
              <label for="living_western_0">
                <input type="radio" id="living_western_0" <?=print_name_and_value("living_western", "00")?>>있음
              </label>
              <label for="living_western_1">
                <input type="radio" id="living_western_1" <?=print_name_and_value("living_western", "01")?>>없음
              </label>
            </td>
          </tr>
          <tr>
            <th>온수여부</th>
            <td>
              <label for="living_hot_water_0">
                <input type="radio" id="living_hot_water_0" <?=print_name_and_value("living_hot_water", "00")?>>있음
              </label>
              <label for="living_hot_water_1">
                <input type="radio" id="living_hot_water_1" <?=print_name_and_value("living_hot_water", "01")?>>없음
              </label>
            </td>
            <th colspan="2">욕조</th>
            <td>
              <label for="living_bathtub_0">
                <input type="radio" id="living_bathtub_0" <?=print_name_and_value("living_bathtub", "00")?>>있음
              </label>
              <label for="living_bathtub_1">
                <input type="radio" id="living_bathtub_1" <?=print_name_and_value("living_bathtub", "01")?>>없음
              </label>
            </td>
          </tr>
          <tr>
            <th>세면대여부</th>
            <td>
              <label for="living_basin_0">
                <input type="radio" id="living_basin_0" <?=print_name_and_value("living_basin", "00")?>>있음
              </label>
              <label for="living_basin_1">
                <input type="radio" id="living_basin_1" <?=print_name_and_value("living_basin", "01")?>>없음
              </label>
            </td>
            <th colspan="2">주방</th>
            <td>
              <label for="living_kitchen_0">
                <input type="radio" id="living_kitchen_0" <?=print_name_and_value("living_kitchen", "00")?>>있음
              </label>
              <label for="living_kitchen_1">
                <input type="radio" id="living_kitchen_1" <?=print_name_and_value("living_kitchen", "01")?>>없음
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="textarea_head">종합의견</div>
    <textarea name="living_content"><?= $rec['living_content'] ?: '' ?></textarea>

    
    <div class="sub_title_wrap">
      <div class="sub_title">
        10. 이용하기를 희망하는 복지용구
      </div>
      <div class="sub_title_desc">※ 표기 : □에 V표</div>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <tbody>
          <tr>
            <td>
              <label for="welfare_0">
                <input type="checkbox" id="welfare_0" <?=print_name_and_value("welfare[]", "00")?>>이동변기
              </label>
            </td>
            <td>
              <label for="welfare_1">
                <input type="checkbox" id="welfare_1" <?=print_name_and_value("welfare[]", "01")?>>성인용보행기
              </label>
            </td>
            <td>
              <label for="welfare_2">
                <input type="checkbox" id="welfare_2" <?=print_name_and_value("welfare[]", "02")?>>안전손잡이
              </label>
            </td>
            <td>
              <label for="welfare_3">
                <input type="checkbox" id="welfare_3" <?=print_name_and_value("welfare[]", "03")?>>미끄럼방지용품
              </label>
            </td>
          </tr>
          <tr>
            <td>
              <label for="welfare_4">
                <input type="checkbox" id="welfare_4" <?=print_name_and_value("welfare[]", "04")?>>수동휠체어
              </label>
            </td>
            <td>
              <label for="welfare_5">
                <input type="checkbox" id="welfare_5" <?=print_name_and_value("welfare[]", "05")?>>전동침대
              </label>
            </td>
            <td>
              <label for="welfare_6">
                <input type="checkbox" id="welfare_6" <?=print_name_and_value("welfare[]", "06")?>>수동침대
              </label>
            </td>
            <td>
              <label for="welfare_7">
                <input type="checkbox" id="welfare_7" <?=print_name_and_value("welfare[]", "07")?>>배회감지기
              </label>
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <label for="welfare_8">
                <input type="checkbox" id="welfare_8" <?=print_name_and_value("welfare[]", "08")?>>경사로
              </label>
              (
                <label for="welfare_8_0">
                  <input type="checkbox" id="welfare_8_0" <?=print_name_and_value("welfare_sub[]", "00")?>>실내
                </label>
                <label for="welfare_8_1">
                  <input type="checkbox" id="welfare_8_1" <?=print_name_and_value("welfare_sub[]", "01")?>>실외
                </label>
              )
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="textarea_head">종합의견</div>
    <textarea name="welfare_content"><?= $rec['welfare_content'] ?: '' ?></textarea>

    <div class="sub_title_wrap">
      <div class="sub_title">
        11. 수급자 및 보호자 개별 욕구
      </div>
    </div>
    <div class="table_wrap">
      <table style="width:100%">
        <thead>
          <tr>
            <th>구분</th>
            <th>확인</th>
          </tr>
        </thead>
        <tbody>
        <tr>
            <th>일상생활</th>
            <td>
              <label for="etc_daily_0">
                <input type="checkbox" id="etc_daily_0" <?=print_name_and_value("etc_daily[]", "00")?>>개인위생(세수, 구강청결, 몸씻기 등)
              </label>
              <br>
              <label for="etc_daily_1">
                <input type="checkbox" id="etc_daily_1" <?=print_name_and_value("etc_daily[]", "01")?>>식사하기(식사준비, 식사도움)
              </label>
              <br>
              <label for="etc_daily_2">
                <input type="checkbox" id="etc_daily_2" <?=print_name_and_value("etc_daily[]", "02")?>>화장실이용하기(이동변기, 기저귀 교환 등)
              </label>
              <br>
              <label for="etc_daily_3">
                <input type="checkbox" id="etc_daily_3" <?=print_name_and_value("etc_daily[]", "03")?>>이동도움(부축, 보행기, 휠체어 등)
              </label>
              <label for="etc_daily_4">
                <input type="checkbox" id="etc_daily_4" <?=print_name_and_value("etc_daily[]", "04")?>>산책동행  
              </label>
              <label for="etc_daily_5">
                <input type="checkbox" id="etc_daily_5" <?=print_name_and_value("etc_daily[]", "05")?>>병원진료 동행  
              </label>
              <label for="etc_daily_6">
                <input type="checkbox" id="etc_daily_6" <?=print_name_and_value("etc_daily[]", "06")?>>관공서 동행  
              </label>
              <label for="etc_daily_7">
                <input type="checkbox" id="etc_daily_7" <?=print_name_and_value("etc_daily[]", "07")?>>여가활동 동행
              </label>
              <br>
              <label for="etc_daily_8">
                <input type="checkbox" id="etc_daily_8" <?=print_name_and_value("etc_daily[]", "08")?>>청소ㆍ주변정돈     
              </label>
              <label for="etc_daily_9">
                <input type="checkbox" id="etc_daily_9" <?=print_name_and_value("etc_daily[]", "09")?>>세탁     
              </label>
              <label for="etc_daily_10">
                <input type="checkbox" id="etc_daily_10" <?=print_name_and_value("etc_daily[]", "10")?>>장보기  
              </label>
              <br>
              <label for="etc_daily_11">
                <input type="checkbox" id="etc_daily_11" <?=print_name_and_value("etc_daily[]", "11")?>>기타
              </label>
              (<input type="text" name="etc_daily_etc" value="<?=$rec['etc_daily_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>기능회복훈련</th>
            <td>
              <label for="etc_training_0">
                <input type="checkbox" id="etc_training_0" <?=print_name_and_value("etc_training[]", "00")?>>신체기능훈련(연하훈련, 관절운동, 근력운동, 팔운동, 손가락운동 등)
              </label>
              <br>
              <label for="etc_training_1">
                <input type="checkbox" id="etc_training_1" <?=print_name_and_value("etc_training[]", "01")?>>기본동작훈연(체위변경, 일어나앉기, 일어서기, 서있기, 옮겨앉기, 보행 등)
              </label>
              <br>
              <label for="etc_training_2">
                <input type="checkbox" id="etc_training_2" <?=print_name_and_value("etc_training[]", "02")?>>일상생활동작훈련(식사동작, 양치동작, 옷갈아입기 동작, 화장실사용 등)
              </label>
              <br>
              <label for="etc_training_3">
                <input type="checkbox" id="etc_training_3" <?=print_name_and_value("etc_training[]", "03")?>>인지기능향상프로그램(회상훈련, 감각활동, 작업치료 등)
              </label>
              <br>
              <label for="etc_training_4">
                <input type="checkbox" id="etc_training_4" <?=print_name_and_value("etc_training[]", "04")?>>사회적응훈련(대중교통이용, 문화체험, 종교활동 등)  
              </label>
              <br>
              <label for="etc_training_5">
                <input type="checkbox" id="etc_training_5" <?=print_name_and_value("etc_training[]", "05")?>>기타
              </label>
              (<input type="text" name="etc_training_etc" value="<?=$rec['etc_training_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>정서지원</th>
            <td>
              <label for="etc_emotional_0">
                <input type="checkbox" id="etc_emotional_0" <?=print_name_and_value("etc_emotional[]", "00")?>>말벗 서비스 
              </label>
              <br>
              <label for="etc_emotional_1">
                <input type="checkbox" id="etc_emotional_1" <?=print_name_and_value("etc_emotional[]", "01")?>>여가·정서프로그램(개인활동, 취미활동, 요리활동, 산책 등)
              </label>
              <br>
              <label for="etc_emotional_2">
                <input type="checkbox" id="etc_emotional_2" <?=print_name_and_value("etc_emotional[]", "02")?>>기타
              </label>
              (<input type="text" name="etc_emotional_etc" value="<?=$rec['etc_emotional_etc'] ?: ''?>" />)
            </td>
          </tr>
          <tr>
            <th>가족수발경감</th>
            <td>
              <label for="etc_family_helper_mitigate_0">
                <input type="checkbox" id="etc_family_helper_mitigate_0" <?=print_name_and_value("etc_family_helper_mitigate[]", "00")?>>신체적 부양부담 완화    
              </label>
              <br>
              <label for="etc_family_helper_mitigate_1">
                <input type="checkbox" id="etc_family_helper_mitigate_1" <?=print_name_and_value("etc_family_helper_mitigate[]", "01")?>>정신적 부양부담 완화
              </label>
              <br>
              <label for="etc_family_helper_mitigate_2">
                <input type="checkbox" id="etc_family_helper_mitigate_2" <?=print_name_and_value("etc_family_helper_mitigate[]", "02")?>>경제적 부양부담 완화
              </label>
            </td>
          </tr>
		   <tr>
            <th>기타내용 및 종합의견</th>
            <td>
              <textarea name="etc_content" style="border:0px;"><?= $rec['etc_content'] ?: '' ?></textarea>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="sub_title_wrap">
      <div class="sub_title">
        12.종합의견
      </div>
    </div>
    <textarea name="total_review"><?= $rec['total_review'] ?: '' ?></textarea>

    <?php if(!defined('_PRINT_REC_')) { ?> 
    <div class="btn_wrap">
      <input type="submit" value="등록">
      <a href="<?=G5_SHOP_URL?>/my_recipient_view.php?id=<?=$pen['penId']?>">취소</a>
    </div>
    <?php } ?>
  </form>
</div>

<script>
  $(function() {
    $('#rec_detail_form').on('submit', function(e) {
      e.preventDefault();

      $.post($(this).attr('action'), $(this).serialize(), 'json')
      .done(function() {
        alert('작성이 완료되었습니다.');
        window.location.href = '<?=G5_SHOP_URL?>/my_recipient_view.php?id=<?=$pen['penId']?>';
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    });
  });
</script>

<?php
if(!defined('_PRINT_REC_')) {
  include_once("./_tail.php");
}
?>
