<?php
include_once("./_common.php");

if(!$member['mb_entId']) {
  alert('로그인이 필요합니다.');
}

// 검색처리
$select = array();
$where = array();

$penId = isset($_GET['penId']) ? get_search_string($_GET['penId']) : '';
$search = isset($_GET['search']) ? get_search_string($_GET['search']) : '';
$sel_field = isset($_GET['sel_field']) && in_array($_GET['sel_field'], array('penNm', 'it_name')) ? $_GET['sel_field'] : '';
$sel_order = isset($_GET['sel_order']) && in_array($_GET['sel_order'], array('dc_sign_datetime', 'penNm')) ? $_GET['sel_order'] : '';

$qstr = '';

// 수급자만 골라보기
if($penId != '') {
  $where[] = " penId = '$penId' ";
}

// 정렬 순서
$sql_order = ' ORDER BY ';
$index_order = '';
switch($sel_order) {
  case 'penNm':
    $qstr .= 'sel_order=penNm';
    $index_order = 'ASC';
    $sql_order .= 'E.penNm ' . $index_order;
    break;
  default:
    $qstr .= 'sel_order=dc_sign_datetime';
    $index_order = 'DESC';
    $sql_order .= 'E.dc_sign_datetime ' . $index_order;
}

// 작성 완료된 계약서 & 마이그레이션 된 계약서만
$where[] = " (dc_status = '2' OR dc_status = '3') ";

$select[] = ' I.it_name ';
$select[] = ' COUNT(E.dc_id) as it_count ';
$sql_join = ' LEFT JOIN `eform_document_item` I ON E.dc_id = I.dc_id ';
$sql_group = " GROUP BY E.dc_id";

if ($search != '' && $sel_field != '') {
  $qstr .= '&amp;search='.urlencode($search);
  $qstr .= '&amp;sel_field='.urlencode($sel_field);

  $where[] = " $sel_field like '%{$search}%' ";
}

// select 배열 처리
$select[] = "E.*";
$sql_select = "HEX(E.dc_id) as uuid, ".implode(', ', $select);

// where 배열 처리
$sql_where = " WHERE E.entId = '{$member['mb_entId']}' ";
if($where) {
  $sql_where .= ' AND '.implode(' AND ', $where);
}

$sql_from = " FROM `eform_document` E";
$total_count = sql_fetch("SELECT COUNT(R.dc_id) AS cnt FROM (SELECT E.dc_id" . $sql_from . $sql_join . $sql_where . $sql_group . ') R')['cnt'];

$page_rows = $config['cf_page_rows'];
$total_page = ceil($total_count / $page_rows); // 전체 페이지 계산
if ($page < 1) $page = 1;
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " LIMIT {$from_record}, {$page_rows} ";

$result = sql_query("SELECT " . $sql_select . $sql_from . $sql_join . $sql_where . $sql_group . $sql_order . $sql_limit);
?>
<div class="table_box">
<table id="table_list">
<thead>
<tr>
<th>No.</th>
<th>수급자 정보</th>
<th>상품정보</th>
<th>분류</th>
<th>작성일</th>
<th>전자문서</th>
<th>현금영수증</th>
</tr>
</thead>
<tbody>
<?php
$num_rows = sql_num_rows($result);
for($i = 0; $row = sql_fetch_array($result); $i++) {
  $index = $from_record + $i + 1;
  if($index_order == 'DESC') {
    $index = $total_count - $from_record - $i;
  }
?>
<tr>
<td><?=$index?></td>
<td><a href="<?=G5_SHOP_URL?>/my_recipient_view.php?id=<?=$row['penId']?>"><?="{$row["penNm"]}({$row["penLtmNum"]} / {$row["penRecGraNm"]} / {$row["penTypeNm"]})"?></a></td>
<td><?=$row["it_name"]?><?php if($row['it_count'] > 1) { echo ' 외 ' . ($row['it_count'] - 1) . '건'; } ?></td>
<td>일반계약</td>
<td class="text_c"><?=date('Y-m-d', strtotime($row['dc_sign_datetime']))?></td>
<td class="text_c">
  <a href="<?=G5_SHOP_URL?>/eform/downloadEform.php?od_id=<?=$row["od_id"]?>" class="btn_basic">계약서 다운로드</a>
  <?php if($row['dc_status'] != '3') { // 이전 계약서는 감사추적인증서가 없음 ?>
  <a href="<?=G5_SHOP_URL?>/eform/downloadCert.php?od_id=<?=$row["od_id"]?>" class="btn_basic">감사추적인증서</a>
  <?php } ?>
</td>
<td class="text_c">
  <!-- <a href="#">거래영수증</a> -->
</td>
</tr>
<?php
}
?>
</tbody>
</table>
</div>
<div class="list-paging">
<!--<ul class="pagination ">
<li></li>
<li><a href="#">&lt;</a></li>
<li class="active"><a href="#">1</a></li>
<li><a href="#">2</a></li>
<li><a href="#">3</a></li>
<li><a href="#">&gt;</a></li>
<li></li>
</ul>-->
<ul class="pagination pagination-sm en">
  <?php echo apms_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
</ul>
</div>
