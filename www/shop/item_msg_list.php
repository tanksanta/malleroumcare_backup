<?php
include_once("./_common.php");

if($member['mb_type'] !== 'default')
  alert('접근할 수 없습니다.');

$g5['title'] = '간편 제안서';
include_once("./_head.php");

$sql_common = "
  FROM
    recipient_item_msg ms
  LEFT JOIN
    recipient_item_msg_item mi ON ms.ms_id = mi.ms_id
  WHERE
    mb_id = '{$member['mb_id']}'
";

$searchtype = get_search_string($_GET['searchtype']);
$search = get_search_string($_GET['search']);

$qstr = '';
if($searchtype && $search) {
  if($searchtype === 'it_name') {
    $sql_common .= " and ( it_name LIKE '%{$search}%' OR REPLACE(it_name, ' ', '') LIKE '%{$search}%' ) ";
  } else if($searchtype === 'pen_nm') {
    $sql_common .= " and ms_pen_nm LIKE '%{$search}%' ";
  }
  $qstr .= "searchtype={$searchtype}&amp;search={$search}&amp;";
}

// 총 개수 구하기
$total_count = sql_fetch(" SELECT count(*) as cnt FROM ( select ms.ms_id {$sql_common} group by ms.ms_id ) u ")['cnt'];
$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit {$from_record}, {$page_rows} ";

$msg_result = sql_query("
  SELECT ms.*
  {$sql_common}
  GROUP BY ms.ms_id
  ORDER BY ms.ms_id desc
  {$sql_limit}
");

$list = [];
for($i = 0; $row = sql_fetch_array($msg_result); $i++) {
  // 순번 부여하기
  $row['index'] = $total_count - (($page - 1) * $page_rows) - $i;

  // 품목 정보 가져오기
  $row['items'] = [];
  $sql = "
    SELECT * FROM recipient_item_msg_item
    WHERE ms_id = '{$row['ms_id']}'
  ";
  $item_result = sql_query($sql);
  while($item = sql_fetch_array($item_result)) {
    $row['items'][] = $item;
  }

  // 마지막 전송일 가져오기
  $sql = "
    SELECT * FROM recipient_item_msg_log
    WHERE ms_id = '{$row['ms_id']}'
    ORDER BY ml_id desc
    limit 0, 1
  ";
  $last_log_result = sql_fetch($sql);
  if($last_log_result['ml_sent_at'])
    $row['last_sent_at'] = $last_log_result['ml_sent_at'];
  
  $row['ms_pen_nm'] = $row['ms_pen_nm'] ?: '???';
  $row['ms_pen_hp'] = $row['ms_pen_hp'] ?: ' - ';

  $list[] = $row;
}

if(!$list && !($searchtype && $search) && $page <= 1) {
  // 목록이 비었다면 바로 작성페이지로 이동
  //goto_url('item_msg_write.php');
}

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/item_msg.css">', 0);
?>

<section class="wrap">
  <?php /* 23.09.13 : 서원 - 스마트 서비스 매뉴얼 추가 */?>
  <div class="sub_section_tit">간편 제안서 <a href="javascript:void(0);" onclick="window.open('<?=G5_DATA_URL;?>/file/이로움_스마트_서비스_02_간편_제안서_작성.pdf');" class="thkc_btnManual">사용방법 확인하기</a></div>
  <div class="inner">
    <div class="im_desc_wr">
      <a href="item_msg_write.php" class="btn_im_send active">상품 제안서 작성하기</a>
      <div class="im_desc">
        <p>상품을 선택하여 수급자에게 전달할 제안서를 만들어 보세요.</p>
        <p>직접 만든 제안서는 수급자/보호자에게 문자로 전달 할 수 있습니다.</p>
      </div>
    </div>
    <p class="im_sch_hd">제안서 발송내역</p>
    <form method="GET">
      <div class="search_box">
        <select name="searchtype">
          <option value="it_name" <?=get_selected($searchtype, 'it_name')?>>상품명</option>
          <option value="pen_nm" <?=get_selected($searchtype, 'pen_nm')?>>수급자명</option>
        </select>
        <div class="input_search">
          <input name="search" value="<?=$_GET["search"]?>" type="text">
          <button type="submit"></button>
        </div>
      </div>
    </form>

    <div class="list_box">
      <div class="table_box">
        <table>
          <thead>
            <tr>
              <th>No.</th>
              <th>수급자정보</th>
              <th>품목</th>
              <th>계약서 작성</th>
              <th>전송일</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if(!$list) {
              echo '<tr><td colspan="5" class="empty_table">검색 결과가 없습니다</td></tr>';
            }
            ?>
            <?php foreach($list as $row) { ?>
            <tr>
              <td><?=$row['index']?></td>
              <td>
                <a href="item_msg_write.php?w=u&ms_id=<?=$row['ms_id']?>&show_expected=<?=$row['show_expected']?>">
                  <?="{$row['ms_pen_nm']} ({$row['ms_pen_hp']})"?>
                </a>
              </td>
              <td>
                <?php
                foreach($row['items'] as $item) {
                  echo "({$item['gubun']}) ";
                  echo $item['it_name'];
                  echo '<br>';
                }
                ?>
              </td>
              <td style="width: 100px; text-align: center;">
                <a href="simple_eform.php?ms_id=<?=$row['ms_id']?>" class="btn_basic">계약서 작성</a>
              </td>
              <td>
                <?php
                if($row['last_sent_at'])
                  echo date('Y년 m월 d일 (H:i)', strtotime($row['last_sent_at']));
                else
                  echo '';
                ?>
                <a href="item_msg.php?url=<?=$row['ms_url']?>" class="btn_basic" target="_blank">제안서 보기</a>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <div class="list-paging">
        <ul class="pagination pagination-sm en">  
          <?php echo apms_paging(5, $page, $total_page, "?{$qstr}page="); ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<?php include_once("./_tail.php"); ?>
