<?php
include_once("./_common.php");
define('_RECIPIENT_', true);

if(!$is_member){
  alert("접근 권한이 없습니다.");
  exit;
}

include_once("./_head.php");
?>

<section id="my_recipient_message_list">
  <div class="sub_section_tit">품목/정보 메시지 전달</div>

  <div>

  </div>

  <div class="search_box">
    <select name="sel_field" id="sel_field">
      <option value="penNm">수급자명</option>
      <option value="penProNm">보호자명</option>
      <option value="penLtmNum">장기요양번호</option>
    </select>
    <div class="input_search">
        <input name="search" id="search" value="" type="text">
        <button id="btn_search" type="submit"></button>
    </div>
  </div>

</section>

<?php

include_once("./_tail.php");

