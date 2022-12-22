<?php
$sub_menu = '400620';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '상품 입출고관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$it_id = get_search_string($_GET['it_id']);

$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

if(!$it['it_id']) alert('존재하지 않는 상품입니다.');

$gubun = $cate_gubun_table[substr($it['ca_id'], 0, 2)];
$gubun_text = '판매';
if($gubun == '01') $gubun_text = '대여';
else if($gubun == '02') $gubun_text = '비급여';

$option_sql = "SELECT *
  FROM
    {$g5['g5_shop_item_option_table']}
  WHERE
    it_id = '$it_id'
    and io_type = 0 -- 선택옵션
  ORDER BY
    io_no ASC
";
$option_result = sql_query($option_sql);

$options = [];
while ($option_row = sql_fetch_array($option_result)) {
  $io_value = '';
  $it_option_subjects = explode(',', $it['it_option_subject']);
  $io_ids = explode(chr(30), $option_row['io_id']);
  for($g = 0; $g < count($io_ids); $g++) {
    if ($g > 0) {
      $io_value .= ' / ';
    }
    $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
  }

  $option_row['io_value'] = $io_value;
  $options[] = $option_row;
}

$warehouse_list = get_warehouses();

$sql_common = "
  FROM
    warehouse_stock ws
  WHERE
    it_id = '$it_id' and
    ws_del_yn = 'N'
";

$qstr = "it_id=$it_id";
$where = [];

$sel_field = in_array($_GET['sel_field'], ['od_id', 'ws_memo']) ? $_GET['sel_field'] : '';
$search = clean_xss_tags($_GET['search']);

if($sel_field && $search) {
  $qstr .= "&sel_field=$sel_field&search=".urlencode($search);
  $where[] = " $sel_field like '%$search%' ";
}

$io_id = $_GET['io_id'] ?: [];
$ws_type = $_GET['ws_type'] ?: [];
$wh_name = $_GET['wh_name'] ?: [];

if(in_array(1, $ws_type)) {
  $qstr .= "&ws_type=1";
  $where[] = " ws_qty > 0 ";
}
if(in_array(2, $ws_type)) {
  $qstr .= "&ws_type=2";
  $where[] = " ws_qty < 0 ";
}

$attr = ['io_id', 'wh_name'];
foreach($attr as $x) {
  if($$x) {
    $search_sql = ' ( 1 <> 1 ';
    foreach($$x as $y) {
      $qstr .= "&{$x}%5B%5D={$y}";
      $search_sql .= " or {$x} = '{$y}' ";
    }
    $search_sql .= ' ) ';
    $where[] = $search_sql;
  }
}

$sql_search = $where ? ' and ' . implode(' and ', $where) : '';

$sql = "
  select count(*) as cnt
  {$sql_common}
  {$sql_search}
";
$result = sql_fetch($sql);
$total_count = $result['cnt'];

$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql = "
  select
    *,
	  (SELECT ct_is_direct_delivery FROM g5_shop_cart WHERE ct_id = ws.ct_id) AS ct_is_direct_delivery
  {$sql_common}
  {$sql_search}
  order by ws_id desc
  limit {$from_record}, {$page_rows}
";
$result = sql_query($sql);

$list = [];
for($i = 0; $row = sql_fetch_array($result); $i++) {
  $row['index'] = $total_count - (($page - 1) * $page_rows) - $i;
  $list[] = $row;
}
?>
<div class="new_form">
  <form method="get">
    <input type="hidden" name="it_id" value="<?=$it_id?>">
    <table class="new_form_table">
      <tbody>
        <?php if($options) { ?>
        <tr>
          <th>옵션</th>
          <td>
            <input type="checkbox" value="1" id="io_id_all" <?=get_checked(count($options), count($io_id))?>><label for="io_id_all">전체</label>
            <?php foreach($options as $i => $opt) { ?>
            <input type="checkbox" name="io_id[]" value="<?=$opt['io_id']?>" id="io_id_<?=$i?>" <?=option_array_checked($opt['io_id'], $io_id)?>><label for="io_id_<?=$i?>"><?=$opt['io_value']?></label>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
        <tr>
          <th>분류</th>
          <td>
            <input type="checkbox" value="1" id="ws_type_all" <?=get_checked(count($ws_type), 2)?>><label for="ws_type_all">전체</label>
            <input type="checkbox" name="ws_type[]" value="1" id="ws_type_1" <?=option_array_checked(1, $ws_type)?>><label for="ws_type_1">입고</label>
            <input type="checkbox" name="ws_type[]" value="2" id="ws_type_2" <?=option_array_checked(2, $ws_type)?>><label for="ws_type_2">출고</label>
          </td>
        </tr>
        <tr>
          <th>창고</th>
          <td>
            <input type="checkbox" value="1" id="wh_name_all" <?=get_checked(count($warehouse_list), count($wh_name))?>><label for="wh_name_all">전체</label>
            <?php foreach($warehouse_list as $i => $warehouse) { ?>
            <input type="checkbox" name="wh_name[]" value="<?=$warehouse?>" id="wh_name_<?=$i?>" <?=option_array_checked($warehouse, $wh_name)?>><label for="wh_name_<?=$i?>"><?=$warehouse?></label>
            <?php } ?>
          </td>
        </tr>
        <tr>
          <th>검색어</th>
          <td>
            <select name="sel_field" id="sel_field">
              <option value="od_id" <?=get_selected($sel_field, 'od_id')?>>주문번호</option>
              <option value="ws_memo" <?=get_selected($sel_field, 'ws_memo')?>>메모</option>
            </select>
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
  <div class="local_ov01 flex-row justify-space-between" style="border:1px solid #e3e3e3;">
    <h1 style="border:0;padding:5px 0;margin:0;letter-spacing:0;">
      상품명 : <?=$it['it_name']?> (<?=$gubun_text?>)
    </h1>
    <div>
      <a href="/adm/shop_admin/itemstocklist.php" style="display: inline-block;line-height: 35px;border: 1px solid #E3E3E3;background: #383838;color: #fff;padding: 0 15px;">
        상품재고관리 목록으로 이동
      </a>
      <a href="/adm/shop_admin/itemstockedit.php?it_id=<?=$it_id?>" style="display: inline-block;line-height: 35px;border: 1px solid #E3E3E3;background: #383838;color: #fff;padding: 0 15px;">
        입/출고 관리자 권한 수정
      </a>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>번호</th>
        <th>분류</th>
        <th>창고</th>
        <th>옵션명</th>
        <th>등록일시</th>
        <th>입고</th>
        <th>출고</th>
        <th>메모</th>
        <th>비고</th>
        <th>삭제여부</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if(!$list) {
        echo '<tr><td colspan="10" class="empty_table">자료가 없습니다.</td></tr>';
      }
      ?>
      <?php foreach($list as $row) { ?>
      <tr>
        <td class="td_cntsmall"><?=$row['index']?></td>
        <td class="td_center td_mng_m">
          <?php
          if ($row['inserted_from'] == 'shop_cart') {
            if ($row['ct_is_direct_delivery'] == '0') {
              echo '주문';
            } else if ($row['ct_is_direct_delivery'] == '1') {
              echo '직배송';
            } else if ($row['ct_is_direct_delivery'] == '2') {
              echo '설치';
            } else {
              echo "ERROR {$row['ct_is_direct_delivery']}";
            }
          }
          else if ($row['inserted_from'] == 'purchase_cart') {
            echo '발주';
          }
          else if ($row['inserted_from'] == 'stock_move') {
            echo '창고이동';
          }
          else if ($row['inserted_from'] == 'stock_edit') {
            echo '입출관리';
          }
          else if ($row['inserted_from'] == 'stock_edit_excel') {
            echo '입출관리(엑셀)';
          }
          else if ($row['inserted_from'] == 'stock_add') {
            echo '재고등록';
          }
          
          ?>
        </td>
        <td class="td_center td_mng_m"><?=$row['wh_name']?></td>
        <td><?=$row['ws_option'] ?: $row['it_name']?></td>
        <td class="td_datetime"><?=date('Y-m-d (H:i)', strtotime($row['ws_created_at']))?></td>
        <td class="td_numsum"> <!-- 입고 -->
          <?php
          if ($row['ws_scheduled_qty'] > 0) {
            echo '대기(' . number_format($row['ws_scheduled_qty']) . ')';
          } else if ($row['ws_scheduled_qty'] < 0) { // 직배송, 설치
            echo number_format(abs($row['ws_scheduled_qty']));
          } else {
            echo $row['ws_qty'] > 0 ? number_format($row['ws_qty']) : '';
          }
          ?>
        </td>
        <td class="td_numsum"><?=($row['ws_qty'] < 0 ? number_format(abs($row['ws_qty'])) : '')?></td> <!-- 출고 -->
        <td><?=get_text($row['ws_memo'])?></td>
        <td class="td_center td_mng_m"> <!-- 비고 -->
          <?php
          if ($row['inserted_from'] == 'purchase_cart') { // 발주 파트너명
            echo sql_fetch("select od_name from purchase_order where od_id = {$row['od_id']}")['od_name'];
          }
          ?>
        </td>
        <td class="td_center td_mng_l">
          <?php
          if($row['ct_id']) {
            echo '주문취소시 삭제됨';
          } else {
            echo '<form onclick="submit_del();" action="itemstockviewdelete.php" method="POST">';
            echo '<input type="hidden" name="ws_id" value="'.$row['ws_id'].'">';
            echo '<button type="submit" class="btn btn_02" style="background: #000;">삭제</button>';
            echo '</form>';
          }
          ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "?{$qstr}&page="); ?>
</div>

<script>
function submit_del() {
  if(!confirm('정말 삭제하시겠습니까?'))
    return false;

  return true;
}

$(function() {
  var attr = ['io_id', 'ws_type', 'wh_name'];

  for(var i = 0; i < attr.length; i++) {
    (function(cur) {
      $('#' + cur + '_all').click(function() {
        if($(this).prop('checked')) {
          $('input[name="' + cur + '[]"]').prop('checked', true);
        } else {
          $('input[name="' + cur + '[]"]').prop('checked', false);
        }
      });
      $('input[name="' + cur + '[]"]').click(function() {
        var total = $('input[name="' + cur + '[]"]').length;
        var checked = $('input[name="' + cur + '[]"]:checked').length;

        if(total === checked) {
          $('#' + cur + '_all').prop('checked', true);
        } else {
          $('#' + cur + '_all').prop('checked', false);
        }
      });
    })(attr[i]);
  }

});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
