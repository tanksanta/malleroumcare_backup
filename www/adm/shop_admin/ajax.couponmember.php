<?php
$sub_menu = '400800';
include_once('./_common.php');

$auth_check = auth_check($auth[$sub_menu], "w", true);
if($auth_check)
  json_response(400, $auth_check);

$sql_common = " from {$g5['member_table']} ";
$sql_where = " where mb_id <> '{$config['cf_admin']}' and mb_leave_date = '' and mb_intercept_date ='' ";
if($mb_name) {
  $mb_name = preg_replace('/\!\?\*$#<>()\[\]\{\}/i', '', strip_tags($mb_name));
  $sql_where .= " and mb_name like '%".sql_real_escape_string($mb_name)."%' ";
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common . $sql_where;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select mb_id, mb_name
            $sql_common
            $sql_where
            order by mb_id
            limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = 'mb_name='.urlencode($mb_name);

$html = '';
for($i=0; $row=sql_fetch_array($result); $i++) {
  $html .= '
    <tr>
      <td class="td_mbname">'.get_text($row['mb_name']).'</td>
      <td class="td_left">'.$row['mb_id'].'</td>
      <td class="scp_find_select td_mng td_mng_s"><button type="button" class="btn btn_03" data-id="'.$row['mb_id'].'" onclick="sel_member_id(\''.$row['mb_id'].'\');">선택</button></td>
    </tr>
  ';
}

if($i ==0)
  $html = '<tr><td colspan="3" class="empty_table">검색된 자료가 없습니다.</td></tr>';

$paging = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $qstr1.'&page=');

json_response(200, 'OK', array(
  'html' => $html,
  'paging' => $paging
));
?>
