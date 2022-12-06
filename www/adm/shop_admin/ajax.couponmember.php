<?php
$sub_menu = '400800';
include_once('./_common.php');

$auth_check = auth_check($auth[$sub_menu], "w", true);
if($auth_check)
  json_response(400, $auth_check);

$sql_common = " from {$g5['member_table']} ";
$sql_where = " where mb_id <> '{$config['cf_admin']}' and mb_leave_date = '' and mb_intercept_date ='' ";

if($keyword) { // $mb_name => $keyword로 변경
  $keyword = preg_replace('/\!\?\*$#<>()\[\]\{\}/i', '', strip_tags($keyword));
  $sql_where .= " and (mb_name like '%".sql_real_escape_string($keyword)."%' or mb_id like '%".sql_real_escape_string($keyword)."%' )"; // mb_id와 mb_name 동시 검색
}

if($page == "batchReg" && count($mbIdList)){
  $sql_where .= " and ("; // mb_id 검색(일괄등록)
  foreach ($mbIdList as $index => $mbId){
    $sql_where .= "mb_id = '".sql_real_escape_string($mbId)."' "; // mb_id 검색(일괄등록)
    if ($index != array_key_last($mbIdList)) { $sql_where .= "or "; }
  }
  $sql_where .= ")"; // mb_id 검색(일괄등록)
  $sql_limit = "";
} else { //일괄등록은 페이징이 필요없음
  // 테이블의 전체 레코드수만 얻음
  $sql = " select count(*) as cnt " . $sql_common . $sql_where;
  $row = sql_fetch($sql);
  $total_count = $row['cnt'];

  $rows = $config['cf_page_rows'];
  $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
  if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
  $from_record = ($page - 1) * $rows; // 시작 열을 구함
  $sql_limit = "limit $from_record, $rows ";
}

$sql = " select mb_id, mb_name
            $sql_common
            $sql_where
            order by mb_id
            $sql_limit ";
$result = sql_query($sql);

$check = [];
$uncheck = [];
if($page == "batchReg" && count($mbIdList)){
  for($i=0; $row=sql_fetch_array($result); $i++) {
    if(in_array($row['mb_id'], $mbIdList)){
      $check[] = $row['mb_id'];
    }
  }

  json_response(200, 'OK', array(
    'check' => $check
  ));
}

$qstr1 = 'mb_name='.urlencode($keyword);

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
