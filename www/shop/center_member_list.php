<?php
include_once('./_common.php');

$g5['title'] = "직원관리";
include_once("./_head.php");

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/center.css">', 0);
?>

<section class="wrap">
  <div class="sub_section_tit">직원관리</div>
  <form id="form_search" method="get">
    <div class="search_box">
      <select name="sel_field" id="sel_field">
        <option value="emp_name">직원명</option>
      </select>
      <div class="input_search">
          <input name="search" id="search" value="" type="text">
          <button id="btn_search" type="submit"></button>
      </div>
    </div>
  </form>
  <div class="clear">
    <div class="emp_hd">직원목록</div>
    <div style="float: right;">
      <a href="center_member_form.php" class="btn eroumcare_btn2" title="직원 등록">직원 등록</a>
    </div>
  </div>

  <ul class="emp_list">
    <?php for($i = 0; $i < 3; $i++) { ?>
    <li>
      <div class="emp_info_wr flex">
        <img src="/img/no_img.png" alt="">
        <div class="emp_info">
          <p class="name">김보호</p>
          <p class="info">여(만 42세) / 계약직 / 요양보호사</p>
          <ul class="detail">
            <li>
              · 근무 : 2020-02-02(입사) ~ 활동중
            </li>
            <li>
              · 연락처 : 010-1111-2222
            </li>
            <li>
              · 주소 : 서울시 강남구 123
            </li>
          </ul>
        </div>
      </div>
      <div class="emp_btn_wr">
        <a href="#" class="btn_schedule">방문일정 (예정 2건)</a>
        <div class="emp_pay">2021년 11월 급여 (미지급)</div>
      </div>
    </li>
    <?php } ?>
  </ul>
</section>

<?php
include_once('./_tail.php');
?>
