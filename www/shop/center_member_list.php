<?php
include_once('./_common.php');

$g5['title'] = "직원관리";
include_once("./_head.php");

$sel_field = in_array($_GET['sel_field'], ['cm_name', 'cm_hp', 'cm_addr']) ? $_GET['sel_field'] : '';
$search = get_search_string($_GET['search']);

$where = [];
if($sel_field && $search) {
  $where[] = " $sel_field like '%$search%' ";
}

$sql_where = $where ? (' and ' . implode(' and ', $where) ) : '';

$sql_common = "
  FROM
    center_member
  WHERE
    1 = 1
    $sql_where
";

// 총 개수 구하기
$total_count = sql_fetch(" SELECT count(*) as cnt {$sql_common} ", true)['cnt'];
$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit {$from_record}, {$page_rows} ";

$result = sql_query("
  SELECT *
  $sql_common
  $sql_limit
");

$list = [];
while($cm = sql_fetch_array($result)) {
  $cm['info'] = get_center_member_info_text($cm);

  $list[] = $cm;
}

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/center.css">', 0);
?>

<section class="wrap">
  <div class="sub_section_tit">직원관리</div>
  <form id="form_search" method="get">
    <div class="search_box">
      <select name="sel_field" id="sel_field">
        <option value="cm_name" <?=get_selected($sel_field, 'cm_name')?>>직원명</option>
        <option value="cm_hp" <?=get_selected($sel_field, 'cm_hp')?>>연락처</option>
        <option value="cm_addr" <?=get_selected($sel_field, 'cm_addr')?>>주소</option>
      </select>
      <div class="input_search">
          <input name="search" id="search" value="<?=$search?>" type="text">
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
    <?php foreach($list as $cm) { ?>
    <li>
      <div class="emp_info_wr flex">
        <a href="center_member_view.php?cm_code=<?=$cm['cm_code']?>">
          <img src="<?php echo G5_DATA_URL.'/center/member/'.$cm['cm_img']; ?>" class="emp_img" onerror="this.src='/img/no_img.png';">
        </a>
        <div class="emp_info">
          <p class="name">
            <a href="center_member_view.php?cm_code=<?=$cm['cm_code']?>">
              <?=$cm['cm_name']?>
            </a>
          </p>
          <p class="info">
            <?=$cm['info']?>
          </p>
          <ul class="detail">
            <li>
              · 근무 : <?php if($cm['cm_joindate']) { echo $cm['cm_joindate'] . '(입사) ~ '; if($cm['cm_retired']) { echo $cm['cm_retiredate'] . '(퇴사)'; } else { echo '활동중'; } } ?>
            </li>
            <li>
              · 연락처 : <?=$cm['cm_hp']?>
            </li>
            <li>
              · 주소 : <?=$cm['cm_addr']?>
            </li>
          </ul>
        </div>
      </div>
      <div class="emp_btn_wr">
        <a href="#" class="btn_schedule">방문일정</a>
        <div class="emp_pay"><?=date('Y년 m월')?> 급여 (미지급)</div>
      </div>
    </li>
    <?php } ?>
  </ul>
</section>

<?php
include_once('./_tail.php');
?>
