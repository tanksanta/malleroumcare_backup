<?php
include_once("./_common.php");
define('_RECIPIENT_', true);

include_once("./_head.php");

if(!$is_member){
  alert("접근 권한이 없습니다.");
  exit;
}

// 연결기간(3일) 지난 수급자 연결해제
recipient_link_clean();

// 수급자 활동 알림
// category_limit_noti();

$page_rows = $_COOKIE["recipient_page_rows"] ? $_COOKIE["recipient_page_rows"] : 10;
$page = $_GET["page"] ?? 1;

$send_data = [];
$send_data["usrId"] = $member["mb_id"];
$send_data["entId"] = $member["mb_entId"];
$send_data["pageNum"] = $page;
$send_data["pageSize"] = $page_rows;
if ($sel_field === 'penNm') {
  $send_data['penNm'] = $search;
}
if ($sel_field === 'penLtmNum') {
  $send_data['penLtmNum'] = $search;
}
if ($sel_field === 'penProNm') {
  $send_data['penProNm'] = $search;
}
$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);

$list = [];
if($res["data"]) {
  foreach($res['data'] as $data) {
    // 수급자 필수정보 입력 체크
    $checklist = ['penRecGraCd', 'penTypeCd', 'penExpiDtm', 'penBirth'];
    $is_incomplete = false;
    foreach($checklist as $check) {
      if(!$data[$check])
        $is_incomplete = true;
    }
    if(!in_array($data['penGender'], ['남', '여']))
      $is_incomplete = true;
    if($data['penTypeCd'] == '04' && !$data['penJumin'])
      $is_incomplete = true;
    $data['incomplete'] = $is_incomplete;

    // 욕구사정기록지 작성 체크
    $data['recYn'] = 'N';
    $rec_count = sql_fetch("
      SELECT count(*) as cnt
      FROM recipient_rec_simple
      WHERE penId = '{$data['penId']}' and mb_id = '{$member['mb_id']}'
    ");
    if($rec_count['cnt'] > 0)
      $data['recYn'] = 'Y';
    
    // 수급자 설명 텍스트 (00년생/남|여)
    $pen_desc_txt = [];
    if(substr($data['penBirth'], 2, 2)) $pen_desc_txt[] = substr($data['penBirth'], 2, 2).'년생';
    if($data['penGender']) $pen_desc_txt[] = $data['penGender'];
    if($pen_desc_txt) $pen_desc_txt = ' (' . implode('/', $pen_desc_txt) . ')';
    else $pen_desc_txt = '';
    $data['desc_text'] = $pen_desc_txt;

    // 수급자 1년 계약 건수
    $data['per_year'] = get_recipient_grade_per_year($data['penId'], $data['penExpiStDtm']);

    // 장바구니 개수
    $data['carts'] = get_carts_by_recipient($data['penId']);

    $list[] = $data;
  }
}

# 페이징
$total_count = $res["total"];
$total_page = ceil( $total_count / $page_rows ); # 총 페이지


// 예비 수급자
$rows_spare = 5;
$page_spare = $_GET["page_spare"] ?? 1;
$send_data = [];
$send_data["usrId"] = $member["mb_id"];
$send_data["entId"] = $member["mb_entId"];
$send_data["pageNum"] = $page_spare;
$send_data["pageSize"] = $rows_spare;
if ($sel_field === 'penNm') {
  $send_data['penNm'] = $search;
}
if ($sel_field === 'penProNm') {
  $send_data['penProNm'] = $search;
}
$res = get_eroumcare(EROUMCARE_API_SPARE_RECIPIENT_SELECTLIST, $send_data);

$list_spare = [];
if($res["data"]) {
  foreach($res['data'] as $data) {
    // 수급자 설명 텍스트 (00년생/남|여)
    $pen_desc_txt = [];
    if(substr($data['penBirth'], 2, 2)) $pen_desc_txt[] = substr($data['penBirth'], 2, 2).'년생';
    if($data['penGender']) $pen_desc_txt[] = $data['penGender'];
    if($pen_desc_txt) $pen_desc_txt = ' (' . implode('/', $pen_desc_txt) . ')';
    else $pen_desc_txt = '';
    $data['desc_text'] = $pen_desc_txt;
    
    $list_spare[] = $data;
  }
}

$total_count_spare = $res["total"];
$total_page_spare = ceil( $total_count_spare / $rows_spare ); # 총 페이지


// 수급자 연결
$links = get_recipient_links($member['mb_id']);
?>
<script>
function excelform(url){
  var opt = "width=600,height=450,left=10,top=10";
  window.open(url, "win_excel", opt);
  return false;
}

$(function() {
    $(".BottomButton").click(function() {
        $('html').animate({scrollTop : ($('.footer_area').offset().top)}, 600);
    });
});

$(document.body).on('change','#page_rows',function(){
  var recipient_page_rows = $("#page_rows option:selected").val();
  console.log(recipient_page_rows);
  $.cookie('recipient_page_rows', recipient_page_rows, { expires: 365 })
  window.location.reload();
})

function check_all_list(f)
{
  var chk = document.getElementsByName("chk[]");

  for (i=0; i<chk.length; i++)
      chk[i].checked = f.chkall.checked;
}

function check_all_list_spare(f)
{
  var chk = document.getElementsByName("spare_chk[]");

  for (i=0; i<chk.length; i++)
      chk[i].checked = f.chkall_spare.checked;
}

function form_check(act) {
  var requests = [];
  if (act == "seldelete")
  {
    var delete_count = $("input[name^=chk]:checked").size();
    if(delete_count < 1) {
      alert("삭제하실 항목을 하나이상 선택해 주십시오.");
      return false;
    }

    $("input[name^=chk]:checked").each(function() {
      var chk_value = this.value.split("|");
      var penId = chk_value[0];
      var sell_count = chk_value[1];
      if (sell_count > 0) {
        alert("선택한 수급자 중 주문이 있는 수급자는 일괄삭제가 불가능합니다. 삭제를 원하시면 상세화면에서 삭제해주시기 바랍니다.");
        requests = [];
        return false;  
      }
      requests.push(
        $.ajax({
          type: 'POST',
          url: './ajax.my.recipient.list.update.php',
          data: {penId : penId, delYn : 'Y'}
        })
      );
    });
  }
  else if (act == "selupdate") {
    var update_count = $("input[name^=chk]:checked").size();
    if(update_count < 1) {
      alert("수정하실 항목을 하나이상 선택해 주십시오.");
      return false;
    }

    $("input[name^=chk]:checked").each(function() {
      var chk_value = this.value.split("|");
      var penId = chk_value[0];

      var penRecGraCd = $("#sel_grade option:selected").val();
      var penTypeCd = $("#sel_type_cd option:selected").val();
      requests.push(
        $.ajax({
          type: 'POST',
          url: './ajax.my.recipient.list.update.php',
          data: {penId : penId, penRecGraCd : penRecGraCd, penTypeCd : penTypeCd}
        })
      );
    });
  }
  else if (act == "spare_seldelete")
  {
    var delete_count = $("input[name^=spare_chk]:checked").size();
    if(delete_count < 1) {
      alert("삭제하실 항목을 하나이상 선택해 주십시오.");
      return false;
    }

    $("input[name^=spare_chk]:checked").each(function() {
      requests.push(
        $.ajax({
          type: 'POST',
          url: './ajax.my.recipient.list.update.php',
          data: {penId : this.value, delYn : 'Y', isSpare : 'Y'}
        })
      );
    });
  }

  if (requests.length > 0) {
    $.when.apply($, requests).then(() => {
      alert('완료되었습니다');
      window.location.reload();
    }, error => {
      alert(error.message)
    });
    return true;
  }
  return false;
}

</script>

<style>
.no_content { width:100%; padding: 50px 0; text-align:center; }
#myRecipientListWrap > .titleWrap > .link_notice_wrap {
  position: absolute; top:-20px; right:0; font-weight: normal !important; font-size: 16px; line-height: 20px; height: 60px; padding: 20px 40px; text-align: center;
  color: #fff; background-color: #ee8102; border-radius: 8px;cursor: pointer;
}
@media (max-width: 960px) {
  #myRecipientListWrap > .titleWrap > .link_notice_wrap {
    position: static; margin-bottom: 20px;height:auto;
  }
}
.ajax-loader {
  display: none;
  background-color: rgba(255,255,255,0.7);
  position: absolute;
  z-index: 90;
  width: 100%;
  height:100%;
  top: 0;
  left: 0;
  text-align: center;
  padding-top: 25%;
}

.ajax-loader p {
  margin: 20px;
  color: #666;
}
</style>

<!-- 210204 수급자목록 -->
<div id="myRecipientListWrap">
  <div class="titleWrap" style="margin-bottom:10px;">
    <?php if($links) { ?>
    <div class="link_notice_wrap BottomButton">
      <i class="fa fa-bell-o" aria-hidden="true"></i>
      신규 수급자(<?=get_text($links[0]['rl_pen_name'])?>) 추천되었습니다.
    </div>
    <?php } ?>
    수급자관리
    <div class="page_rows">
        <!-- <select name="orderby" id="orderby">
            <option value="it_id" <?php echo $orderby == 'it_id' || !$orderby ? 'selected' : ''; ?>>최근등록순 정렬</option>
            <option value="it_name" <?php echo $orderby == 'it_name' ? 'selected' : ''; ?>>가나다순 정렬</option>
        </select> -->
        <select name="page_rows" id="page_rows" style="font-weight: normal;">
            <option value="10" <?php echo $page_rows == '10' ? 'selected' : ''; ?>>10개씩보기</option>
            <option value="15" <?php echo $page_rows == '15' ? 'selected' : ''; ?>>15개씩보기</option>
            <option value="20" <?php echo $page_rows == '20' ? 'selected' : ''; ?>>20개씩보기</option>
            <option value="50" <?php echo $page_rows == '50' ? 'selected' : ''; ?>>50개씩보기</option>
            <option value="100" <?php echo $page_rows == '100' ? 'selected' : ''; ?>>100개씩보기</option>
            <option value="200" <?php echo $page_rows == '200' ? 'selected' : ''; ?>>200개씩보기</option>
        </select>
    </div>
  </div>

  <?php if ($is_development || $member['mb_id'] === 'hula1202') { ?>
  <div class="recipient_security">
    <div>
      <img src="<?php echo G5_SHOP_URL; ?>/img/icon_security.png" />
    </div>
    <div class="recipient_security_content">
      <h4>이로움 정보 보안관리 시스템</h4>
      <p>
        수급자 정보(이름, 요양인정번호, 연락처)는 암호화되어 저장됩니다.<br/>
        수급자계약서 체결 및 엑셀다운로드 시 본인 인증 확인(동일IP확인 및 비밀번호 확인) 후 진행됩니다.
      </p>
      <a href="#">[온라인 개인정보 처리방침 자세히보기]</a>
    </div>
    <div class="recipient_security_check">
      <img src="<?php echo G5_SHOP_URL; ?>/img/icon_security_check.png" />
      <span><?php echo $member['mb_name']; ?> 확인됨</span>
    </div>
  </div>
  <?php } ?>

  <form id="form_search" method="get">
    <div class="search_box">
      <select name="sel_field" id="sel_field">
        <option value="penNm"<?php if($sel_field == 'penNm' || $sel_field == 'all') echo ' selected'; ?>>수급자명</option>
        <option value="penProNm"<?php if($sel_field == 'penProNm') echo ' selected'; ?>>보호자명</option>
        <option value="penLtmNum"<?php if($sel_field == 'penLtmNum') echo ' selected'; ?>>장기요양번호</option>
      </select>
      <div class="input_search">
          <input name="search" id="search" value="<?=$search?>" type="text">
          <button id="btn_search" type="submit"></button>
      </div>
    </div>
    <?php if($noti_count = get_recipient_noti_count() > 0) { ?>
    <div class="recipient_noti">
      신규 확인이 필요한 알림 <?=$noti_count?>건이 있습니다.
      <a href="./my_recipient_noti.php">바로확인</a>
    </div>
    <?php } ?>
    <div class="r_btn_area pc">
      <a href="./my_recipient_excel.php" class="btn eroumcare_btn2" title="수급자 엑셀 다운로드">수급자 엑셀 다운로드</a>
      <a href="./my_recipient_write.php" class="btn eroumcare_btn2" title="수급자 등록">수급자 등록</a>
      <a href="./recipientexcel.php" onclick="return excelform(this.href);" target="_blank" class="btn eroumcare_btn2" title="수급자일괄등록">수급자일괄등록</a>
      <div class="tooltip_btn">
        <a href="./recipientexcel_b.php" onclick="return excelform(this.href);" target="_blank" class="btn eroumcare_btn2" title="B사 엑셀 일괄등록">
          B사 엑셀 일괄등록
          <span class="question">?</span>
        </a>
        <div class="btn_tooltip">
          B사 수급자 목록 일괄등록 방법<br>
          <br>
          1. B사 복지용구프로그램 로그인<br>
          2. (고객관리 > 고객등록 > 조회) 메뉴 선택 <br>
          3. 엑셀(F8) 클릭 후 엑셀 다운로드<br>
          4. 다운로드된 엑셀파일을 이로움에 업로드<br>
          <br>
          <a href="https://blog.naver.com/poongki_/222493454657" class="blog" target="_blank">도움말보기<img src="<?php echo G5_URL; ?>/img/icon_blog_naver.png" /></a>
          
        </div>
      </div>
    </div>
    <div class="r_btn_area mobile">
      <a href="./my_recipient_write.php" class="btn eroumcare_btn2" title="수급자 등록">수급자 등록</a>
    </div>
  </form>
  
  <?php
  if (!get_tutorial('recipient_list_tooltip')) { 
    set_tutorial('recipient_list_tooltip', 1);
  ?>
  <script>
    $(document).ready(function(){
      $('.tooltip_btn .btn_tooltip').fadeIn(1000);
      setTimeout(function() {
        $('.tooltip_btn .btn_tooltip').fadeOut(1000, function() {
          $('.tooltip_btn .btn_tooltip').css('display', '');
        });
      }, 4000);
    });
  </script>
  <?php } ?>

  <div class="list_box pc">
    <form name="fmemberlist" id="fmemberlist" action="#" onsubmit="return fmemberlist_submit(this);" method="post">
    <div class="table_box">  
      <table>
        <tr>
          <th id="mb_list_chk">
            <label for="chkall" class="sound_only">수급자 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all_list(this.form)">
          </th>
          <th>No.</th>
          <th>수급자 정보</th>
          <th>장기요양정보</th>
          <th>1년사용</th>
          <th>장바구니</th>
          <th>비고</th>
        </tr>
        <?php $i = -1; ?>
        <?php foreach($list as $data) { ?>
        <?php $i++; ?>
        <tr>
          <td headers="mb_list_chk" id="mb_list_chk">
            <?php
            $contract_sell = get_recipient_contract_sell($data['penId']);
            ?>
            <input type="hidden" name="chk[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>" id="chk_<?php echo $i ?>" class="chk_input">
            <label for="spare_chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $data['penId'] . '|' . $contract_sell['sell_count'] ?>" id="chk_<?php echo $i ?>">
          </td>
          <td>
            <?php echo $total_count - (($page - 1) * $page_rows) - $i; ?>
          </td>
          <td>
            <a href='<?php echo G5_URL; ?>/shop/my_recipient_view.php?id=<?php echo $data['penId']; ?>'>
              <?php echo $data['penNm'].$data['desc_text']; ?>
              <?php if($data['incomplete']) echo '<img src="'.THEMA_URL.'/assets/img/icon_notice_recipient.png" style="vertical-align:bottom;">'; ?>
              <br/>
              <?php if ($data['penProNm']) { ?>
                보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProConNum'] ? '/' . $data['penProConNum'] : ''; ?>)
              <?php } ?>
            </a>
          </td>
          <td>
            <?php if ($data["penLtmNum"]) { ?>
              <?php echo $data["penLtmNum"]; ?>
              (<?php echo $data["penRecGraNm"]; ?><?php echo $pen_type_cd[$data['penTypeCd']] ? '/' . $pen_type_cd[$data['penTypeCd']] : ''; ?>)
              <br/>
              <?php echo $data['penExpiDtm']; ?>
            <?php }else{ ?>
              예비수급자
            <?php } ?>
          </td>
          <td style="text-align:center;">
            <span class="<?php echo $data['per_year']['sum_price'] > 1400000 ? 'red' : ''; ?>"><?php echo number_format($data['per_year']['sum_price']); ?>원</span>
            <br/>
            계약 <?php echo $data['per_year']['count']; ?>건, 판매 <?php echo $data['per_year']['sell_count']; ?>건, 대여 <?php echo $data['per_year']['borrow_count']; ?>건
          </td>
          <td style="text-align:center;">
            <?php
              echo $data['carts'] . '개';
            ?>
            <br/>
            <?php if ($data["penLtmNum"]) { ?>
            <a href="<?php echo G5_SHOP_URL; ?>/connect_recipient.php?pen_id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2 small" title="추가하기">추가하기</a>
            <?php } ?>
          </td>
          <td style="text-align:center;">
            <?php if ($data['recYn'] === 'N') { ?>
              욕구사정기록지 미작성<br/>
              <a href="<?php echo G5_SHOP_URL; ?>/my_recipient_rec_form.php?id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2 small" title="작성하기">작성하기</a>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
      </table>
    </div>
    </form>
  </div>


  <?php if(!$list){ ?>
  <div class="no_content">
    내용이 없습니다
  </div>
  <?php } ?>

  <?php if($list) { ?>
  <div class="list_box mobile">
    <ul class="li_box">
      <?php foreach ($list as $data) { ?>
      <li>
        <div class="info">
          <a href='<?php echo G5_URL; ?>/shop/my_recipient_view.php?id=<?php echo $data['penId']; ?>'>
            <b>
              <?php echo $data['penNm'].$data['desc_text']; ?>
              <?php if($data['incomplete']) echo '<img src="'.THEMA_URL.'/assets/img/icon_notice_recipient.png" style="vertical-align:bottom;">'; ?>
            </b>
            <?php if ($data['penProNm']) { ?>
            <span class="li_box_protector">
              * 보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProTypeCd'] == '00' ? '/없음' : ''; ?><?php echo $data['penProTypeCd'] == '01' ? '/일반보호자' : ''; ?><?php echo $data['penProTypeCd'] == '02' ? '/요양보호사' : ''; ?>)
            </span>
            <?php } ?>
            <p>
              <?php if ($data["penLtmNum"]) { ?>
              <b>
                <?php echo $data["penLtmNum"]; ?>
                (<?php echo $data["penRecGraNm"]; ?><?php echo $pen_type_cd[$data['penTypeCd']] ? '/' . $pen_type_cd[$data['penTypeCd']] : ''; ?>)
              </b>
              <?php } else { ?>
              예비수급자
              <?php } ?>
            </p>
            <p>
            <br/>
              <b>
                1년사용: 
                <span class="<?php echo $data['per_year']['sum_price'] > 1400000 ? 'red' : ''; ?>"><?php echo number_format($data['per_year']['sum_price']); ?>원</span>
              </b>
              <span style="font-size:0.9em;">
                계약 <?php echo $data['per_year']['count']; ?>건, 판매 <?php echo $data['per_year']['sell_count']; ?>건, 대여 <?php echo $data['per_year']['borrow_count']; ?>건
              </span>
            </p>
          </a>
          <?php if ($data['recYn'] === 'N') { ?>
          <a href="<?php echo G5_SHOP_URL; ?>/my_recipient_rec_form.php?id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2" style="margin-top:10px;" title="작성하기">욕구사정기록지 작성</a>
          <?php } ?>
        </div>
        <?php if ($data["penLtmNum"]) { ?>
        <a href="<?php echo G5_SHOP_URL; ?>/connect_recipient.php?pen_id=<?php echo $data['penId']; ?>" class="li_box_right_btn" title="추가하기">
          장바구니
          <br/>
          <b><?php echo $data['carts'] . '개'; ?></b>
        </a>
        <?php } ?>
      </li>
      <?php } ?>
    </ul>
  </div>
  <?php } ?>

  <div class="list-paging">
    <ul class="pagination pagination-sm en">
      <?php echo apms_paging($page_rows, $page, $total_page, "?sel_field={$sel_field}&search={$search}&page_spare={$page_spare}&page="); ?>
    </ul>
  </div>

  <div class="l_btn_area pc" style="margin-bottom:10px;">
    <button type="button" class="btn eroumcare_btn2" onclick="return form_check('seldelete');">선택삭제</button>
    &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; 일괄수정 : &nbsp;&nbsp;&nbsp;
    <select name="sel_grade" id="sel_grade">
        <option value="">등급</option>
        <option value="00">등급외</option>
        <option value="01">1등급</option>
        <option value="02">2등급</option>
        <option value="03">3등급</option>
        <option value="04">4등급</option>
        <option value="05">5등급</option>     
    </select>
    <select name="sel_type_cd" id="sel_type_cd">
        <option value="">본인부담금</option>
        <option value="00">일반 15%</option>
        <option value="01">감경 9%</option>
        <option value="02">감경 6%</option>
        <option value="03">의료 6%</option>
        <option value="04">기초 0%</option>
    </select>
    <button type="button" class="btn eroumcare_btn2" onclick="return form_check('selupdate');">선택수정</button>
    <!-- <a href="./my_recipient_write.php" class="btn eroumcare_btn2" title="수급자 등록">본인부담금</a> -->
  </div>
  <br/><br/><br/>

  <div class="titleWrap" style="margin-bottom:10px;">
    예비수급자관리
  </div>

  <div class="list_box pc">
    <form name="fsparememberlist" id="fsparememberlist" action="#" method="post">
    <div class="table_box">  
      <table>
        <tr>
          <th id="mb_list_chk">
            <label for="chkall" class="sound_only">예비수급자 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall_spare" onclick="check_all_list_spare(this.form)">
          </th>
          <th class="number_area">No.</th>
          <th>수급자 정보</th>
          <th>장기요양정보</th>
          <th>비고</th>
        </tr>
        <?php $i = -1; ?>
        <?php foreach($list_spare as $data) { ?>
        <?php $i++; ?>
        <tr>
          <td headers="mb_list_spare_chk" id="mb_list_spare_chk">
            <input type="hidden" name="spare_chk[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>" id="spare_chk_<?php echo $i ?>" class="spare_chk_input">
            <label for="spare_chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
            <input type="checkbox" name="spare_chk[]" value="<?php echo $data['penId'] ?>" id="spare_chk_<?php echo $i ?>">
          </td>
          <td>
            <?php echo $total_count_spare - (($page_spare - 1) * $rows_spare) - $i; ?>
          </td>
          <td>
            <a href="<?=G5_SHOP_URL?>/my_recipient_update.php?penSpare=1&id=<?=$data['penId']?>">
              <?php echo $data['penNm'].$data['desc_text']; ?>
              <br/>
              <?php if ($data['penProNm']) { ?>
                보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProConNum'] ? '/' . $data['penProConNum'] : ''; ?>)
              <?php } ?>
            </a>
          </td>
          <td>
            예비수급자
          </td>
          <td style="text-align:center;">
          </td>
        </tr>
        <?php } ?>
      </table>
    </div>
  </form>
  </div>

  <?php if(!$list_spare) { ?>
  <div class="no_content">
    내용이 없습니다
  </div>
  <?php } ?>

  <?php if($list_spare) { ?>
  <div class="list_box mobile">
    <ul class="li_box">
      <?php foreach ($list_spare as $data) { ?>
      <li>
        <div class="info">
          <a href="<?=G5_SHOP_URL?>/my_recipient_update.php?penSpare=1&id=<?=$data['penId']?>">
            <b>
              <?php echo $data['penNm'].$data['desc_text']; ?>
            </b>
            <?php if ($data['penProNm']) { ?>
            <span class="li_box_protector">
              * 보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProTypeCd'] == '00' ? '/없음' : ''; ?><?php echo $data['penProTypeCd'] == '01' ? '/일반보호자' : ''; ?><?php echo $data['penProTypeCd'] == '02' ? '/요양보호사' : ''; ?>)
            </span>
            <?php } ?>
            <p>
              예비수급자
            </p>
          </a>
        </div>
      </li>
      <?php } ?>
    </ul>
  </div>
  <?php } ?>
  <div class="list-paging">
    <ul class="pagination pagination-sm en">
      <?php echo apms_paging($rows_spare, $page_spare, $total_page_spare, "?sel_field={$sel_field}&search={$search}&page={$page}&page_spare="); ?>
    </ul>
  </div>
  <div class="l_btn_area pc" style="margin-bottom: 30px;">
    <button type="button" class="btn eroumcare_btn2" onclick="return form_check('spare_seldelete');">선택삭제</a>
  </div>

  <?php
  if($links) {
  ?>
  <div class="titleWrap" style="margin-bottom:10px;">
    대기중인 수급자관리
  </div>
  <div class="list_box pc">
    <div class="table_box">  
      <table id="tb_links">
        <thead>
          <tr>
            <th scope="col">No.</th>
            <th scope="col">수급자명</th>
            <th scope="col">인정정보</th>
            <th scope="col">주소</th>
            <th scope="col">연락처</th>
            <th scope="col">보호자정보</th>
            <th scope="col">연결일시(3일 후 자동취소)</th>
          </tr>
        </thead>
        <tbody>
          <?php
          for($i = 0; $i < count($links); $i++) {
          $rl = $links[$i];
          ?>
          <tr data-id="<?=$rl['rl_id']?>">
            <td><?=count($links) - $i?></td>
            <td style="text-align:center;"><?=get_text($rl['rl_pen_name'])?></td>
            <td style="text-align:center;"><?=$rl['rl_pen_ltm_num'] ? get_text('L'.$rl['rl_pen_ltm_num']) : '예비'?></td>
            <td style="max-width:300px;width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <?=get_text($rl['rl_pen_addr1'])?>
              <?=get_text($rl['rl_pen_addr2'])?>
              <?=get_text($rl['rl_pen_addr3'])?>
            </td>
            <td style="text-align:center;"><?=get_text($rl['rl_pen_hp'])?></td>
            <td style="text-align:center;">
              <?=get_text($rl['rl_pen_pro_name'])?>
              (<?=get_text($rl['rl_pen_pro_hp'])?>)
            </td>
            <td style="text-align:center;">
              <?php
              if($rl['status'] == 'request') {
                echo '미연결';
              } else {
                echo date('Y-m-d', strtotime($rl['updated_at']));
              }
              ?>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="list_box mobile">
    <ul id="ul_links" class="li_box">
      <?php
      for($i = 0; $i < count($links); $i++) {
        $rl = $links[$i];
      ?>
      <li data-id="<?=$rl['rl_id']?>">
        <div class="info">
          <b>
            <?=get_text($rl['rl_pen_name'])?>
          </b>
          <?php if ($rl['rl_pen_pro_name']) { ?>
          <span class="li_box_protector">
            * 보호자(<?=get_text($rl['rl_pen_pro_name'])?> / <?=get_text($rl['rl_pen_pro_hp'])?>)
          </span>
          <?php } ?>
          <p>
            <?=$rl['rl_pen_ltm_num'] ? get_text('L'.$rl['rl_pen_ltm_num']) : '예비'?>
          </p>
          <p>
            <b>
              <?=get_text($rl['rl_pen_addr1'])?>
              <?=get_text($rl['rl_pen_addr2'])?>
              <?=get_text($rl['rl_pen_addr3'])?>
            </b>
          </p>
          <p>
            <b>연결일시: </b>
            <span style="font-size:0.9em;">
              <?php
              if($rl['status'] == 'request') {
                echo '미연결';
              } else {
                echo date('Y-m-d', strtotime($rl['updated_at']));
              }
              ?>
            </span>
          </p>
        </div>
      </li>
      <?php } ?>
    </ul>
  </div>
  <div id="popup_recipient_link">
    <div></div>
  </div>
  <style>
  #tb_links td, #ul_links li { cursor: pointer }
  #tb_links tr:hover, #tb_links tr:active, #ul_links li:hover, #ul_links li:active { background-color: #f5f5f5; }
  #popup_recipient_link { position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
  #popup_recipient_link > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
  #popup_recipient_link iframe { position: relative; width: 1024px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -512px; }
  #popup_recipient_link iframe.mini { width: 600px; margin-left: -300px; }
  @media (max-width : 1240px){
    #popup_recipient_link iframe, #popup_recipient_link iframe.mini { width: 100%; height: 100%; left: 0; margin-left: 0; }
  }
  </style>
  <script>
  $(function() {
    $("#popup_recipient_link").hide();
    $("#popup_recipient_link").css("opacity", 1);

    $('#tb_links td').click(function(e) {
      var rl_id = $(this).closest('tr').data('id');
      $("#popup_recipient_link > div").html("<iframe src='my_recipient_link.php?rl_id="+rl_id+"'>");
      $("#popup_recipient_link iframe").removeClass('mini');
      $("#popup_recipient_link iframe").load(function() {
        $("body").addClass('modal-open');
        $("#popup_recipient_link").show();
      });
    });

    $('#ul_links li').click(function(e) {
      var rl_id = $(this).data('id');
      $("#popup_recipient_link > div").html("<iframe src='my_recipient_link.php?rl_id="+rl_id+"'>");
      $("#popup_recipient_link iframe").removeClass('mini');
      $("#popup_recipient_link iframe").load(function() {
        $("body").addClass('modal-open');
        $("#popup_recipient_link").show();
      });
    });
  });
  </script>
  <?php } ?>
</div>
<div class="ajax-loader">
  <div class="loader-wr">
    <img src="<?php echo G5_URL; ?>/shop/img/loading.gif">
    <p>수급자 일괄 등록 중입니다...</p>
  </div>
</div>

<script>
// 엑셀 일괄등록, 로딩
function excelPost(action, data) {
  $('.ajax-loader').show();
  $.ajax({
    url: action,
    type: 'POST',
    data: data,
    processData: false,
    contentType: false,
    dataType: 'json'
  })
  .done(function(result) {
    alert(result.message);
    window.location.reload();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  })
  .always(function() {
    $('.ajax-loader').hide();
  });
}
</script>
<div id="popup_recipient">
  <div></div>
</div>
<style>
#popup_recipient { position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
#popup_recipient > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
#popup_recipient iframe { position: relative; width: 1024px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -512px; }
#popup_recipient iframe.mini { width: 600px; margin-left: -300px; }
#popup_recipient iframe.security {
  width: 600px;
  margin-left: -300px;
  max-height: 500px;
}
@media (max-width : 1240px){
  #popup_recipient iframe,
  #popup_recipient iframe.mini,
  #popup_recipient iframe.security {
    width: 100%;
    height: 100%;
    left: 0;
    margin-left: 0;
  }
}
</style>
<script>
  $(function() {
    $("#popup_recipient").hide();
    $("#popup_recipient").css("opacity", 1);

    $('.recipient_security_check').click(function(e) {
      $("#popup_recipient > div").html("<iframe src='my_recipient_security.php'>");
      $("#popup_recipient iframe").addClass('security');
      $("#popup_recipient iframe").load(function() {
        $("body").addClass('modal-open');
        $("#popup_recipient").show();
      });
    });
  });
</script>
<?php include_once("./_tail.php"); ?>
