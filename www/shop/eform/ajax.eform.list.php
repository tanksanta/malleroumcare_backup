<?php
include_once("./_common.php");

// 검색처리
$select = array();
$where = array();

$penId = isset($_GET['penId']) ? get_search_string($_GET['penId']) : '';
$search = isset($_GET['search']) ? get_search_string($_GET['search']) : '';
$sel_field = isset($_GET['sel_field']) && in_array($_GET['sel_field'], array('penNm', 'it_name')) ? $_GET['sel_field'] : '';
$sel_order = isset($_GET['sel_order']) && in_array($_GET['sel_order'], array('dc_datetime', 'penNm')) ? $_GET['sel_order'] : '';

if($member['mb_type'] === 'normal') {
  $penId = get_session('ss_pen_id');
  $entId = get_session('ss_ent_id');
  if(!$penId || !$entId)
    alert('선택된 사업소가 없습니다.');
} else {
  $entId = $member['mb_entId'];
  if(!$entId)
    alert('로그인이 필요합니다.');
}

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
    $qstr .= 'sel_order=dc_datetime';
    $index_order = 'DESC';
    $sql_order .= 'E.dc_datetime ' . $index_order;
}

//
if($incompleted) {
  $qstr .= '&amp;incompleted=1';
  $where[] = " dc_status = '11' ";
}

// 작성 완료된 계약서 & 마이그레이션 된 계약서만 + 간편 계약서로 생성된 계약서
$where[] = " (dc_status = '2' OR dc_status = '3' OR dc_status = '11') ";

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
$sql_where = " WHERE E.entId = '{$entId}' ";
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
<style>
  .btn_grey {
    display: inline-block;
    background: #f0f0f0;
    border: 1px solid #ddd;
    border-radius: 3px;
    color: #333;
    line-height: 1;
    padding: 6px;
    margin-top: 5px;
  }
  .text_c .btn_basic {
    width: 112px;
  }
</style>
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
<th>급여비용 명세서</th>
<th>전송하기</th>
</tr>
</thead>
<tbody>
<?php
$num_rows = sql_num_rows($result);
if(!$num_rows) {
  echo '<tr><td colspan="7" class="empty_table">자료가 없습니다.</td></tr>';
}
for($i = 0; $row = sql_fetch_array($result); $i++) {
  $index = $from_record + $i + 1;
  if($index_order == 'DESC') {
    $index = $total_count - $from_record - $i;
  }
?>
<tr <?=($_REQUEST["dc_id"] == $row["uuid"])?"bgcolor='#efefef'": "" ;?>>
<td><?=$index?></td>
<td>
  <?php
  if(!$row['penId']) {
    // 01,02,03,04,05,06 : penRecGraCd : n등급
    $penTypeCd_list = array('00'=>'일반 15%', '01'=>'감경 9%', '02'=>'감경 6%', '03'=>'의료 6%', '04'=>'기초 0%');

    echo "{$row["penNm"]}({$row["penLtmNum"]} / {$row["penRecGraNm"]} / {$row["penTypeNm"]})";

    $row['penRecGraCd'] = $row['penRecGraCd'] == '00' ?"등급외" :str_replace('0','',$row['penRecGraCd'])."등급";
    $row['penTypeCd'] = $penTypeCd_list[$row['penTypeCd']];

    $attrs = ['penNm', 'penLtmNum', 'penBirth', 'penRecGraCd', 'penTypeCd', 'penConNum', 'penJumin'];

    $q = '';
    foreach($attrs as $attr) {
      if($attr == 'penTypeCd') { $q .= 'penTypeCd=' . urlencode(explode(' ', $row[$attr])[0]) . '&penPayRate=' . urlencode(explode(' ', $row[$attr])[1]) . '&SbaCd=' . urlencode($row[$attr]) . '&' ;}
      else { $q .= $attr . '=' . urlencode($row[$attr]) . '&'; }
    }

    $penExpiDtm = explode(' ~ ', $row['penExpiDtm']);
    $q .= 'penExpiStDtm=' . urlencode($penExpiDtm[0]) . '&penExpiEdDtm=' . urlencode($penExpiDtm[1]) . '&';
    $q .= 'page=' . urlencode("eform") . '&uuid=' . urlencode($row['uuid']);

    echo '<br><a href="/shop/my_recipient_write.php?'.$q.'" class="btn_grey">미등록 수급자 신규추가</a>';
  } else {
    echo '<a href="'.G5_SHOP_URL.'/my_recipient_view.php?id='.$row['penId'].'">'."{$row["penNm"]}({$row["penLtmNum"]} / {$row["penRecGraNm"]} / {$row["penTypeNm"]})".'</a>';
  }
  ?>
</td>
<td>
  <?=$row["it_name"]?><?php if($row['it_count'] > 1) { echo ' 외 ' . ($row['it_count'] - 1) . '건'; } ?>
  <?php
  if(!$row['od_id']) {
    // 바코드 입력이 없는 상품만 주문 가능
    // - 전부 바코드 입력이 되어있는 경우는 상품 주문하기 출력 X
    $it_count = sql_fetch(" select count(*) as cnt from eform_document_item where dc_id = unhex('{$row['uuid']}') and it_barcode = '' ");
    if($it_count['cnt'] > 0)
      echo '<br><a href="/shop/simple_order.php?dc_id='.$row["uuid"].'" class="btn_grey">상품 주문하기</a>';
  }
  ?>
</td>
<td>
  <?php
  if($row['dc_status'] == '11') {
    echo '<span style="color:#ef8505; font-weight: bold;">계약대기</span>';
  } else {
    echo '일반계약';
  }
  ?>
</td>
<td class="text_c">
  <?=date('Y-m-d', strtotime($row['dc_datetime']))?>
</td>
<td class="text_c">
  <?php
  if($row['dc_status'] == '11') {
      echo '<a href="' . G5_SHOP_URL . '/eform/signEform.php?dc_id=' . $row["uuid"] . '" class="btn_basic" style="background: #6e9254; color: #fff;">계약서 서명</a>';
      echo '<br>';
      echo '<a href="' . G5_SHOP_URL . '/eform/renderEform.php?download=1&dc_id=' . $row["uuid"] . '" class="btn_basic">계약서 다운로드</a>';
      echo '<br>';
      echo '<a href="' . G5_SHOP_URL . '/simple_eform.php?dc_id=' . $row["uuid"] . '" class="btn_basic" style="width: 53px;">수정</a>';
      echo '<a href="javascript:void(0);" class="btn_basic btn_del_eform" data-id="' . $row["uuid"] . '" style="width: 53px;">삭제</a>';
  } else {
    if($row['dc_status'] == '3' && !$row['od_id']) {
      echo '<a href="' . G5_SHOP_URL . '/eform/downloadEform.php?dc_id=' . $row["uuid"] . '" class="btn_basic">계약서 다운로드</a>';
    } else {
      echo '<a href="' . G5_SHOP_URL . '/eform/downloadEform.php?od_id=' . $row["od_id"] . '" class="btn_basic">계약서 다운로드</a>';
    }
  }
  ?>
  <?php
  if($row['dc_status'] == '2') { // 이전 계약서는 감사추적인증서가 없음
    echo '<br><a href="' . G5_SHOP_URL . '/eform/downloadCert.php?od_id=' . $row["od_id"] . '" class="btn_basic">감사추적 인증서</a>';
  } else if($row['dc_status'] == '3' && !$row['od_id']) {
    echo '<br><a href="' . G5_SHOP_URL . '/eform/downloadCert.php?dc_id=' . $row["uuid"] . '" class="btn_basic">감사추적 인증서</a>';
  }
  ?>
</td>
<td class="text_c">
  <?php
  if($row['od_id']) {
    echo '<a href="' . G5_SHOP_URL . '/eform/downloadReceipt.php?od_id=' . $row["od_id"] . '" class="btn_basic">급여비용 명세서</a>';
  } else {
    echo '<a href="' . G5_SHOP_URL . '/eform/downloadReceipt.php?dc_id=' . $row["uuid"] . '" class="btn_basic">급여비용 명세서</a>';
  }
  ?>
</td>
<td class="text_c">
  <?php
  if ($row['dc_send_kakao'] == 1) {
    echo '수급자 휴대폰으로<br>' . '<span style="color:#6e9254; font-weight: bold;">계약서 전송완료</span><br>';
  } else if ($row['dc_send_kakao'] == 2) {
    echo '수급자 휴대폰으로<br>' . '<span style="color:#6e9254; font-weight: bold;">계약서 재 전송완료</span><br>';
  }
  if($row['dc_status'] != '11') {
    echo '<a href="javascript:void(0);" class="btn_basic btn_resend_eform" data-id="' . $row["uuid"] . '" data-name="' . $row["penNm"] . '" data-hp="' . $row["penConNum"] . '" data-mail="' . $row["penMail"] . '">계약서 재전송</a>';
  }
  ?>
</td>
</tr>
<?php
}
?>
</tbody>
</table>
</div>
<div class="list-paging">
<ul class="pagination pagination-sm en">
  <?php echo apms_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
</ul>
</div>
