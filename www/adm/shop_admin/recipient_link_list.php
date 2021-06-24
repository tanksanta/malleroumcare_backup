<?php
$sub_menu = '500050';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '수급자연결관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

if (!$is_development) {
  alert('준비중 입니다.');
}

// 검색
$sel_field = get_search_string($sel_field);
if( !in_array($sel_field, array('rl.rl_pen_name')) ) {   //검색할 필드 대상이 아니면 값을 제거
  $sel_field = '';
  $search = '';
}

if ($sel_field != "" && $search) {
  $sql_search .= " AND $sel_field like '%$search%' ";
}

// 등록일
if ($fr_datetime && $to_datetime) {
  $sql_search .= " and ( rl_created_at between '$fr_datetime 00:00:00' and '$to_datetime 23:59:59' )";
}

// 상태 전체 선택시 unset
if ($rl_state && count($rl_state) >= count($recipient_link_state)) {
  unset($rl_state);
}

if ($rl_state) {
  $sql_state = ' 1 != 1 ';
  foreach($rl_state as $state) {
    if (!$recipient_link_state[$state]) continue;
    $sql_state .= " or rl_state = '{$state}' ";
  }
  $sql_search .= " and ($sql_state) ";
}

$sql_common = " from recipient_link as rl WHERE 1=1 ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
?>

<div class="local_ov01 local_ov">
  <form name="flist" class=" local_sch">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <div class="local_sch03 local_sch">
      <div class="sch_last">
        <strong>등록일</strong>
        <input type="text" id="fr_datetime" name="fr_datetime" value="<?php echo $fr_datetime; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off"> ~
        <input type="text" id="to_datetime" name="to_datetime" value="<?php echo $to_datetime; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off">
        <button type="button" onclick="javascript:set_date2('datetime', '오늘');">오늘</button>
        <button type="button" onclick="javascript:set_date2('datetime', '어제');">어제</button>
        <button type="button" onclick="javascript:set_date2('datetime', '3일');">3일</button>
        <button type="button" onclick="javascript:set_date2('datetime', '일주일');">일주일</button>
        <button type="button" onclick="javascript:set_date2('datetime', '이번주');">이번주</button>
        <button type="button" onclick="javascript:set_date2('datetime', '이번달');">이번달</button>
        <button type="button" onclick="javascript:set_date2('datetime', '지난주');">지난주</button>
        <button type="button" onclick="javascript:set_date2('datetime', '지난달');">지난달</button>
        <button type="button" onclick="javascript:set_date2('datetime', '전체');">전체</button>
      </div>
    </div>
    
    <div class="local_sch03 local_sch">
      <div class="sch_last">
        <strong>상태</strong>
        <input type="checkbox" id="state-all" class="rl_state" name="rl_state[]" <?php echo !$rl_state ? 'checked="chekced"' : ''; ?>><label for="state-all"> 전체</label>
        <?php foreach($recipient_link_state as $key => $state) { ?>
          <input type="checkbox" class="rl_state rl_state_child" id="state-<?php echo $key; ?>" name="rl_state[]" value="<?php echo $key; ?>" <?php echo !$rl_state || in_array($key, $rl_state) ? 'checked="chekced"' : ''; ?>>
          <label for="state-<?php echo $key; ?>"><?php echo $state; ?></label>
        <?php } ?>
      </div>
    </div>

    <select name="sel_field" id="sel_field">
      <option value="rl.rl_pen_name" <?php echo get_selected($sel_field, 'rl.rl_pen_name'); ?>>수급자명</option>
      <option value="test" <?php echo get_selected($sel_field, 'test'); ?>>사업소명</option>
    </select>

    <label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input">
    <input type="submit" value="검색" class="btn_submit">

  </form>

  <div style="text-align:right">
    <a class="btn btn_01 btn" href="<?php echo G5_ADMIN_URL; ?>/shop_admin/recipient_link_form.php">등록</a>
  </div>
</div>

<div class="btn_fixed_top">
  <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/recipient_link_form.php" class="btn_01 btn">등록</a>
</div>

<div class="tbl_head01 tbl_wrap">
  <table>
  <caption><?php echo $g5['title']; ?> 목록</caption>
  <thead>
  <tr>
    <th scope="col" id="th_id">ID</th>
    <th scope="col" id="th_info">수급자정보</th>
    <th scope="col" id="th_address">주소</th>
    <th scope="col" id="th_hp">연락처</th>
    <th scope="col" id="th_end">연결사업소</th>
    <th scope="col" id="th_state">상태</th>
    <th scope="col" id="th_datetime">등록일</th>
    <th scope="col" id="th_datetime">최근 수정</th>
    <th scope="col" id="th_edit" style="width:130px">비고</th>
  </tr>
  </thead>
  <tbody>
  <?php
  $sql = "SELECT * from recipient_link as rl
    WHERE 1=1 $sql_search
    order by rl_id desc
    limit $from_record, $rows
  ";
  $result = sql_query($sql);
  for ($i=0; $row=sql_fetch_array($result); $i++) {
    $bg = 'bg'.($i%2);
  ?>

  <tr class="<?php echo $bg; ?>">
    <td headers="th_id" class="td_num"><?php echo $row['rl_id']; ?></td>
    <td headers="th_info">
      <?php echo get_text($row['rl_pen_name']); ?>
      <?php echo $row['rl_pen_ltm_num'] ? '(L' . $row['rl_pen_ltm_num'] . ')' : ''; ?>
    </td>
    <td headers="th_address">
      <?php echo get_text($row['rl_pen_addr1']); ?>
      <?php echo get_text($row['rl_pen_addr2']); ?>
      <?php echo get_text($row['rl_pen_addr3']); ?>
    </td>
    <td headers="th_hp" class="">
      <?php echo get_text($row['rl_pen_hp']); ?>
    </td>
    <td headers="th_end" class="">

    </td>
    <td headers="th_state" class="" style="text-align:center">
      <?php echo $recipient_link_state[$row['rl_state']]; ?>
    </td>
    <td headers="th_datetime" class="td_datetime"><?php echo $row['rl_created_at']; ?></td>
    <td headers="th_datetime" class="td_datetime"><?php echo $row['rl_updated_at']; ?></td>
    <td headers="th_edit" class="" style="text-align:center;">
      <a href="./recipient_link_view.php?rl_id=<?php echo $row['rl_id']; ?>" class="btn btn_03">자세히</a>
      <a href="./recipient_link_form.php?<?php echo $qstr ; ?>&amp;w=u&amp;rl_id=<?php echo $row['rl_id']; ?>" class="btn btn_03">수정</a>
    </td>
  </tr>

  <?php
  }
  if ($i == 0) {
  echo '<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>';
  }
  ?>
  </tbody>
  </table>

</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
jQuery(function($) {
  // 상태 체크 박스
  $('.rl_state').click(function() {
    if ($(this).attr('id') === 'state-all') {
      var checked = $(this).is(":checked");
      $(".rl_state").prop('checked', checked);
      return;
    }

    var parent = $(this).parent('div');
    var total = $(parent).find('.rl_state_child').length;
    var checkedTotal = $(parent).find('.rl_state_child:checked').length;

    $("#state-all").prop('checked', total <= checkedTotal); 

    return;
  });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
