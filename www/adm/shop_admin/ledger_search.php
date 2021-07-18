<?php
$sub_menu = '400460';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '수금등록사업소 검색';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$qstr = "";
$where = [];

# 영업담당자
if(!$mb_manager)
  $mb_manager = [];
$where_manager = [];
if(!$mb_manager_all && $mb_manager) {
  foreach($mb_manager as $man) {
    $qstr .= "mb_manager%5B%5D={$man}&amp;";
    $where_manager[] = " mb_manager = '$man' ";
  }
  $where[] = ' ( ' . implode(' or ', $where_manager) . ' ) ';
}
$manager_result = sql_query("
  SELECT
    a.mb_id,
    m.mb_name
  FROM
    g5_auth a
  LEFT JOIN
    g5_member m ON a.mb_id = m.mb_id
  WHERE
    au_menu = '400400' and
    au_auth LIKE '%w%'
");
$managers = [];
while($manager = sql_fetch_array($manager_result)) {
  $managers[$manager['mb_id']] = $manager['mb_name'];
}

# 검색어
$search = get_search_string($search);
if($search)
  $where[] = " mb_entNm LIKE '%{$search}%' ";

$sql_search = '';
if($where) {
  $sql_search = ' and '.implode(' and ', $where);
}

$sql_common = "
  FROM
    g5_member m
  WHERE
    mb_level IN (3, 4)
    {$sql_search}
";

// 총 개수 구하기
$total_count = sql_fetch(" SELECT count(*) as cnt {$sql_common} ")['cnt'];
$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit {$from_record}, {$page_rows} ";

$ent_result = sql_query("
  SELECT
    mb_id,
    mb_entNm,
    (
      SELECT mb_name from g5_member WHERE mb_id = m.mb_manager
    ) as mb_manager
  {$sql_common}
  ORDER BY
    mb_entNm ASC
  {$sql_limit}
");

$ents = [];
$index = $from_record;
while($row = sql_fetch_array($ent_result)) {
  $row['index'] = ++$index;
  $row['balance'] = get_outstanding_balance($row['mb_id']);
  $ents[] = $row;
}

$qstr .= "search={$search}";
?>

<div class="new_form">
  <form method="get">
    <table class="new_form_table">
      <tbody>
        <tr>
          <th>영업담당자</th>
          <td>
            <input type="checkbox" name="mb_manager_all" value="1" id="chk_mb_manager_all" <?php if(!array_diff(array_keys($managers), $mb_manager)) echo 'checked'; ?>>
            <label for="chk_mb_manager_all">전체</label>
            <?php foreach($managers as $mb_id => $mb_name) { ?>
            <input type="checkbox" name="mb_manager[]" value="<?=$mb_id?>" id="manager_<?=$mb_id?>" class="chk_mb_manager" <?php if(in_array($mb_id, $mb_manager)) echo 'checked'; ?>>
            <label for="manager_<?=$mb_id?>"><?=$mb_name?></label>
            <?php } ?>
          </td>
        </tr>
        <tr>
          <th>사업소명</th>
          <td>
            <input type="text" name="search" value="<?=$search?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
          </td>
        </tr>
      </tbody>
    </table>
    <div class="submit">
      <button type="submit" id="search-btn"><span>검색</span></button>
    </div>
  </form>
</div>
<div class="tbl_head01 tbl_wrap">
  <table>
    <thead>
      <tr>
        <th>No.</th>
        <th>사업소명</th>
        <th>영업담당자</th>
        <th>총 미수금</th>
        <th>선택</th>
      </tr>
    </thead>
    <tbody>
      <?php if(!$ents) { ?>
      <tr>
        <td colspan="5" class="empty_table">자료가 없습니다.</td>
      </tr>
      <?php } ?>
      <?php foreach($ents as $ent) { ?>
      <tr>
        <td class="td_cntsmall"><?=$ent['index']?></td>
        <td><?=$ent['mb_entNm']?></td>
        <td class="td_payby"><?=$ent['mb_manager']?></td>
        <td class="td_numsum"><?=number_format($ent['balance'])?></td>
        <td class="td_mng_s td_center"><a href="<?=G5_ADMIN_URL?>/shop_admin/ledger_manage.php?mb_id=<?=$ent['mb_id']?>" class="btn btn_03">선택</a></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
</div>

<script>
$(function() {
  // 영업담당자 - 전체 버튼
  $('#chk_mb_manager_all').change(function() {
    var checked = $(this).is(":checked");
    $(".chk_mb_manager").prop('checked', checked);
  });
  // 영업담당자 - 영업담당자 버튼
  $('.chk_mb_manager').change(function() {
    var total = $('.chk_mb_manager').length;
    var checkedTotal = $('.chk_mb_manager:checked').length;
    $("#chk_mb_manager_all").prop('checked', total <= checkedTotal); 
  });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
