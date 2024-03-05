<?php
if(!defined('_PRINT_REC_')) {
  include_once("./_common.php");
  include_once("./_head.php");
}
 //수급자 조회 관련 추가, 개발완료 시 삭제 필요====================================================================?>
<script>
	//swal("사용 주의","현재 수급자 조회조건 개선 작업으로 수급자 정보를\n업데이트할 수 없습니다.\n등록된 수급자의 정보가 정확하지 않을 수 있음을\n유의해 주시기 바랍니다.","warning");
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

$rec = null;
if($rs_id = get_search_string($_GET['rs_id'])) {
  $rec = sql_fetch("
    SELECT * FROM recipient_rec_simple
    WHERE rs_id = '{$rs_id}' and mb_id = '{$member['mb_id']}'
  ");
  if(!$rec['rs_id'])
    alert('욕구사정기록지가 존재하지 않습니다.');
  $rec['inmate'] = explode(',', $rec['inmate']);
}

function print_name_and_value($name, $val) {
  global $rec;

  $res = "name=\"{$name}\" value=\"{$val}\"";
  if($rec) {
    if($name == 'inmate[]') {
      if(in_array($val, $rec['inmate'])) {
        $res .= ' checked';
      }
    } else if($name == 'helper_type_etc') {
      if($rec['helper_type'] != '05') {
        $res .= ' disabled';
      } else {
        $res = "name=\"{$name}\" value=\"{$rec['helper_type_etc']}\"";
      }
    } else if($name == 'child') {
      $res = "name=\"{$name}\" value=\"{$rec['child']}\"";
    } else if($rec[$name] == $val) {
      $res .= ' checked';
    }
  } else {
    if($name == 'helper_type_etc') {
      $res .= ' disabled';
    }
  }

  if(defined('_PRINT_REC_')) {
    $res .= ' onclick="javascript: return false;"';
  }

  return $res;
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
  <?php if (!$rec && !defined('_PRINT_REC_')) { ?>
  <div class="detail-tab">
    <ul>
      <li class="on" data-type="order">
        <a href="./my_recipient_rec_form.php?id=<?php echo $id; ?>">
          <span></span>
          <h4>간략 기록지</h4>
        </a>
      </li>
      <li data-type="order_pen" id="c_recipient">
        <a href="./my_recipient_rec_detail_form.php?id=<?php echo $id; ?>">
          <span></span>
          <h4>전체 기록지</h4>
        </a>
      </li>
    </ul>
  </div>
  <?php } ?>

  <form id="rec_form" action="my_recipient_rec_post.php" method="post">
    <?php if($rec) { ?>
    <input type="hidden" name="rs_id" value="<?=$rec['rs_id']?>">
    <?php  } ?>
    <input type="hidden" name="penId" value="<?=$pen['penId']?>">
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
            <td><input type="radio" <?=print_name_and_value("pscl_state1", "00")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state1", "01")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state1", "02")?>></td>
          </tr>
          <tr>
            <th>식사 하기</th>
            <td><input type="radio" <?=print_name_and_value("pscl_state3", "00")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state3", "01")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state3", "02")?>></td>
          </tr>
          <tr>
            <th>목욕 하기</th>
            <td><input type="radio" <?=print_name_and_value("pscl_state5", "00")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state5", "01")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state5", "02")?>></td>
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
            <td><input type="radio" <?=print_name_and_value("pscl_state2", "00")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state2", "01")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state2", "02")?>></td>
          </tr>
          <tr>
            <th>방밖으로 나오기</th>
            <td><input type="radio" <?=print_name_and_value("pscl_state4", "00")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state4", "01")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state4", "02")?>></td>
          </tr>
          <tr>
            <th>화장실 사용하기</th>
            <td><input type="radio" <?=print_name_and_value("pscl_state6", "00")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state6", "01")?>></td>
            <td><input type="radio" <?=print_name_and_value("pscl_state6", "02")?>></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="textarea_head">판단근거</div>
    <textarea name="pscl_reason"><?= $rec['pscl_reason'] ?: '' ?></textarea>

    <div class="sub_title_wrap">
      <div class="sub_title">
        2.인지상태 (인지기능저하, 정신상태, 감정, 문제행동 등)
      </div>
    </div>
    <div class="textarea_head">판단근거</div>
    <textarea name="recog_reason"><?= $rec['recog_reason'] ?: '' ?></textarea>

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
          <label for="careyes_select"><input type="radio" id="careyes_select" <?=print_name_and_value("helper_yn", "Y")?>> 유</label>
          <label for="careno_select"><input type="radio" id="careno_select" <?=print_name_and_value("helper_yn", "N")?>> 무</label>
        </div>
      </div>
      <div class="row">
        <div class="head">주수발자 관계</div>
        <div class="content">
          <label for="spouse_select"><input type="radio" id="spouse_select" <?=print_name_and_value("helper_type", "00")?>> 배우자</label>
          <label for="children_select"><input type="radio" id="children_select" <?=print_name_and_value("helper_type", "01")?>> 자녀</label>
          <label for="soninlow_select"><input type="radio" id="soninlow_select" <?=print_name_and_value("helper_type", "02")?>> 사위</label>
          <label for="sibling_select"><input type="radio" id="sibling_select" <?=print_name_and_value("helper_type", "03")?>> 형제자매</label>
          <label for="kin_select"><input type="radio" id="kin_select" <?=print_name_and_value("helper_type", "04")?>> 친척</label>
          <label for="etc_select"><input type="radio" id="etc_select" <?=print_name_and_value("helper_type", "05")?>> 기타</label>
          <input type="text" id="helperTypeEtc" <?=print_name_and_value("helper_type_etc", "")?>>
        </div>
      </div>
      <div class="row">
        <div class="head">자녀수</div>
        <div class="content">
          <input type="text" <?=print_name_and_value("child", "0")?>> 명
        </div>
      </div>
      <div class="row">
        <div class="head">거주환경</div>
        <div class="content">
          <label for="apt_select"><input type="radio" id="apt_select" <?=print_name_and_value("home_env", "00")?>> 아파트</label>
          <label for="villa_select"><input type="radio" id="villa_select" <?=print_name_and_value("home_env", "01")?>> 연립/빌라</label>
          <label for="house_select"><input type="radio" id="house_select" <?=print_name_and_value("home_env", "02")?>> 단독주택</label>
        </div>
      </div>
      <div class="row">
        <div class="head">거주형태</div>
        <div class="content">
          <label for="onehome_select"><input type="radio" id="onehome_select" <?=print_name_and_value("home_type", "00")?>> 자가</label>
          <label for="jeonse_select"><input type="radio" id="jeonse_select" <?=print_name_and_value("home_type", "01")?>> 전세</label>
          <label for="rent_select"><input type="radio" id="rent_select" <?=print_name_and_value("home_type", "02")?>> 월세</label>
        </div>
      </div>
      <div class="row">
        <div class="head">동거인</div>
        <div class="content">
          <label for="one_select"><input type="checkbox" id="one_select" <?=print_name_and_value("inmate[]", "00")?>> 독거</label>
          <label for="marry_select"><input type="checkbox" id="marry_select" <?=print_name_and_value("inmate[]", "01")?>> 부부</label>
          <label for="parent_select"><input type="checkbox" id="parent_select" <?=print_name_and_value("inmate[]", "02")?>> 부모</label>
          <label for="child_select"><input type="checkbox" id="child_select" <?=print_name_and_value("inmate[]", "03")?>> 자녀</label>
          <label for="grandchild_select"><input type="checkbox" id="grandchild_select" <?=print_name_and_value("inmate[]", "04")?>> 손자녀</label>
          <label for="kins_select"><input type="checkbox" id="kins_select" <?=print_name_and_value("inmate[]", "05")?>> 친척</label>
          <label for="friend_select"><input type="checkbox" id="friend_select" <?=print_name_and_value("inmate[]", "06")?>> 친구·이웃</label>
        </div>
      </div>
    </div>

    <div class="sub_title_wrap">
      <div class="sub_title">
        4.총평
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
    $('input[name="helper_type"]').change(function() {
      if($(this).val() == '05') {
        $('#helperTypeEtc').prop('disabled', false);
      } else {
        $('#helperTypeEtc').prop('disabled', true);
      }
    });

    $('#rec_form').on('submit', function(e) {
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
