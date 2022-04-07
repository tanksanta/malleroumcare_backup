<?php
$sub_menu = '400620';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '상품재고관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

// $type : hold, delete, release
if (!$it_id || !in_array($type, ['hold', 'delete', 'release'])) {
  alert('올바른 접근이 아닙니다');
}

$qstr .= "&amp;it_id={$it_id}";
$qstr .= "&amp;io_id={$io_id}";
$qstr .= "&amp;type={$type}";

$where_sql = '';
$sfl = get_search_string($sfl);
$sfl_arr = array('bc_barcode');
if (!in_array($sfl, $sfl_arr)) { // 검색할 필드 대상이 아니면 값을 제거
  $sfl = '';
}
$stx = get_search_string($stx);
if ($sfl && $stx) {
  $where_sql = " AND {$sfl} like '%{$stx}%' ";
}

$type_sql = '';
if ($type == 'hold') {
  $type_sql = " AND bc_status != '출고' AND bc_del_yn = 'N' ";
}
if ($type == 'delete') {
  $type_sql = " AND bc_status = '관리자삭제' AND bc_del_yn = 'Y' ";
}
if ($type == 'release') {
  $type_sql = " AND bc_status = '출고' AND bc_del_yn = 'Y' ";
}

// 페이지 계산
$sql = "
  SELECT count(*) AS cnt
  FROM g5_cart_barcode
  WHERE 
    it_id = '{$it_id}'
    AND io_id = '{$io_id}'
    {$type_sql}
    {$where_sql}
";
$total_count = sql_fetch($sql)['cnt'];
$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 테이블 데이터
$sql = "
	SELECT 
		bc_id, 
		ct_id, 
		it_id, 
		io_id, 
		bc_barcode, 
		bc_status,
		bc_is_check_yn,
		bc_del_yn,
		bc_del_yn AS origin_del_yn,
		checked_by,
		bc_memo,
		DATE_FORMAT(checked_at, '%m/%d') AS checked_at,
		DATE_FORMAT(rentaled_at, '%m/%d') AS rentaled_at,
		DATE_FORMAT(released_at, '%m/%d') AS released_at,
		DATE_FORMAT(deleted_at, '%m/%d') AS deleted_at,
		checked_at AS checked_at_full,
    (SELECT count(*) FROM g5_cart_barcode_log 
      WHERE bch_barcode = bc.bc_barcode 
      AND it_id = bc.it_id AND io_id = bc.io_id) AS history_cnt
  FROM g5_cart_barcode bc
  WHERE 
    it_id = '{$it_id}'
    AND io_id = '{$io_id}'
    {$type_sql}
    {$where_sql} 
  ORDER BY
    bc_id DESC
  LIMIT {$from_record}, {$rows}
";

$result = sql_query($sql);

$where = "WHERE it_id = '${it_id}' ";

if ($io_id) {
  $where .= " AND io_id = '{$io_id}' ";
}

$row = get_stock_item_info($it_id, $io_id);

$sql = "
  SELECT IFNULL(MAX(created_at), '미확인') AS last_checked_at
  FROM stock_barcode_check_log
  {$where}
";

$last_checked_at = sql_fetch($sql)['last_checked_at'];

$option = '';
$option_br = '';
if ($row['io_type']) {
  $opt = explode(chr(30), $row['io_id']);
  if ($opt[0] && $opt[1])
    $option .= $opt[0] . ' : ' . $opt[1];
} else {
  $subj = explode(',', $row['it_option_subject']);
  $opt = explode(chr(30), $row['io_id']);
  for ($k = 0; $k < count($subj); $k++) {
    if ($subj[$k] && $opt[$k]) {
      $option .= $option_br . $subj[$k] . ' : ' . $opt[$k];
      $option_br = ' / ';
    }
  }
}

$full_it_name = $row['it_name'];
if ($option) {
  $full_it_name .= " ({$option})";
}
?>

<div id="item_stock_barcode_list">

  <div class="header">
    <div>
      <p class="title"><?php echo $full_it_name ?></p>
      <span style="font-size: 13px">재고수량 : <?= $row['sum_ws_qty'] ?> / 바코드 : <?= $row['sum_barcode_qty'] ?>, 마지막 확인 일시 : <?= $last_checked_at ?></span>
    </div>
    <form class="search_wrap" method="get">
      <input type="hidden" name="sfl" value="bc_barcode">
      <input type="hidden" name="it_id" value="<?php echo $it_id ?>">
      <input type="hidden" name="io_id" value="<?php echo $io_id ?>">
      <input type="hidden" name="type" value="<?php echo $type ?>">

      <input type="text" name="stx" class="search_text" placeholder="바코드 입력" value="<?php echo $stx; ?>">
      <button type="submit" class="search_submit_btn">검색</button>
    </form>
  </div>

  <div class="body">
    <div class="tab flex-row">
      <?php
      $common_href = "/adm/shop_admin/itemstockbarcodelist.php?it_id={$it_id}&io_id={$io_id}"
      ?>
      <a href="<?php echo $common_href . '&type=hold' ?>" class="<?php echo $type == 'hold' ? 'active' : '' ?>">보유재고</a>
      <a href="<?php echo $common_href . '&type=delete' ?>" class="<?php echo $type == 'delete' ? 'active' : '' ?>">삭제됨</a>
      <a href="<?php echo $common_href . '&type=release' ?>" class="<?php echo $type == 'release' ? 'active' : '' ?>">출고완료</a>
    </div>

    <div class="tbl_head01 tbl_wrap">
      <table>
        <caption>상품재고관리 목록</caption>
        <thead>
        <tr>
          <th scope="col" class="no">No</th>
          <th scope="col" class="barcode">바코드</th>
          <th scope="col" class="date">마지막 기록</th>
          <th scope="col" class="btn">비고</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i = 0; $row = sql_fetch_array($result); $i++) {
          if ($row['bc_status'] == '대여') {
            $check_status = '대여 중 (' . $row['rentaled_at'] . ')';
            if ($row['bc_memo']) {
              $check_status .= '<br/>' . $row['bc_memo'];
            }

          } else if ($row['bc_status'] == '출고') {
            $check_status = '출고 완료 (' . $row['released_at'] . ')';
            if ($row['bc_memo']) {
              $check_status .= '<br/>' . $row['bc_memo'];
            }

          } else if ($row['bc_status'] == '관리자삭제') {
            $check_status = '삭제함 (' . $row['deleted_at'] . ')';

          } else if ($row['checked_at']) {
            $check_status = '확인 완료 (' . $row['checked_at'] . ')';

          } else {
            $check_status = '미확인';
          }
        ?>
        <tr class="bg0 warn">
          <td class="no"><?php echo $total_count - (($page - 1) * $rows + $i) ?></td>
          <td class="barcode"><?php echo $row['bc_barcode'] ?></td>
          <td class="date"><?php echo $check_status ?></td>
          <td class="btns">
            <button class="history_btn" data-it_id="<?php echo $row['it_id'] ?>" data-io_id="<?php echo $row['io_id'] ?>">기록 <?php echo $row['history_cnt'] ?></button>
          </td>
        </tr>
        <?php
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>


  <div id="barcodeHistory">
    <div class="mask"></div>
    <div class="historyContent">
      <div class="header flex-row justify-space-between align-center">
        <div class="barcode">barcode</div>
        <button class="close" onclick="closeBarcodeHistory()">×</button>
      </div>
      <div class="content">
        <ul>
          <li>
            <p class="subtitle">2022-01-01 13:11 홍길동 담당자</p>
            <p class="title">상품 출고 (NO 222222)</p>
          </li>
          <li>
            <p class="subtitle">2022-01-01 13:11 홍길동 담당자</p>
            <p class="title">상품 출고 (NO 222222)</p>
          </li>
          <li>
            <p class="subtitle">2022-01-01 13:11 홍길동 담당자</p>
            <p class="title">상품 출고 (NO 222222)</p>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>


<script>
  $(function() {
    $('.history_btn').on('click', function() {
      var barcode = $(this).closest('tr').find('.barcode').text().trim();
      var it_id = $(this).data('it_id');
      var io_id = $(this).data('io_id');

      showBarcodeHistory(barcode, it_id, io_id);
    });
  });

  function showBarcodeHistory(barcode, it_id, io_id) {
    var data = null;

    $('#barcodeHistory .header .barcode').empty();
    $('#barcodeHistory .content ul').empty();

    $.ajax({
      url: './ajax.barcode_history.php',
      type: 'POST',
      async: false,
      data: {
        barcode: barcode,
        it_id: it_id,
        io_id: io_id,
      },
      dataType: 'json',
    })
    .done(function(result) {
      data = result.data;
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });

    $('#barcodeHistory').show();
    $('#barcodeHistory .header .barcode').text(barcode);

    if (data.length > 0) {
      var subtitle = '';
      var title = '';
      var html = '';

      // $('body').css('overflow', 'hidden');

      data.forEach(function (obj) {
        subtitle = obj.created_at + ' ' + obj.mb_name + ' 담당자';
        title = obj.bch_content;

        html += '<li>'
        html += '<p class="subtitle">' + subtitle + '</p>'
        html += '<p class="title">' + title + '</p>'
        html += '</li>'
      });

    } else {
      html = '<li>내역이 없습니다.</li>';
    }

    $('#barcodeHistory .content ul').append(html);
  }

  function closeBarcodeHistory() {
    // $('body').css('overflow', 'auto');
    $('#barcodeHistory').hide();
  }
</script>