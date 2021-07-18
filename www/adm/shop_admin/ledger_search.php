<?php
$sub_menu = '400460';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '수금등록사업소 검색';
include_once (G5_ADMIN_PATH.'/admin.head.php');

# 영업담당자
if(!$mb_manager)
  $mb_manager = [];
$where_manager = [];
if(!$mb_manager_all && $mb_manager) {
  foreach($mb_manager as $man) {
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

$ent_result = sql_query("
  SELECT
    mb_id,
    mb_entNm,
    (
      SELECT mb_name from g5_member WHERE mb_id = m.mb_manager
    ) as mb_manager
  FROM
    g5_member m
  WHERE
    mb_level IN (3, 4)
    {$sql_search}
  ORDER BY
    mb_entNm ASC
");

$ents = [];
$index = 0;
while($row = sql_fetch_array($ent_result)) {
  $row['index'] = ++$index;
  $row['balance'] = get_outstanding_balance($row['mb_id']);
  $ents[] = $row;
}
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
      <?php foreach($ents as $ent) { ?>
      <tr>
        <td><?=$ent['index']?></td>
        <td><?=$ent['mb_entNm']?></td>
        <td><?=$ent['mb_manager']?></td>
        <td><?=number_format($ent['balance'])?></td>
        <td><a href="<?=G5_ADMIN_URL?>/shop_admin/ledger_manager.php?mb_id=<?=$ent['mb_id']?>" class="btn btn_03">선택</a></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
