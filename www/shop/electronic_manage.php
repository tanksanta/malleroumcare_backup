<?php
include_once('./_common.php');

$t_document = get_tutorial('document');
if ($t_document['t_state'] == '0') {
	$t_sql = "SELECT e.dc_status, e.od_id FROM tutorial as t INNER JOIN eform_document as e ON t.t_data = e.od_id
	WHERE 
		t.mb_id = '{$member['mb_id']}' AND
		t.t_type = 'recipient_order'
	";
	$t_result = sql_fetch($t_sql);

  if ($t_result['dc_status'] == '2' || $t_result['dc_status'] == '3') {
    set_tutorial('document', '1');
    set_tutorial('claim', '0');

    $open_tutorial_popup = false;
  } else if ($t_result['dc_status'] == 1 || !$t_result['dc_status']) {
    $open_tutorial_popup = true;
  }
}

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
  require_once(G5_SHOP_PATH.'/yc/orderinquiry.php');
  return;
}

define("_ORDERINQUIRY_", true);

// 회원인 경우
if ($is_member)
{
  $sql_common = " from {$g5['g5_shop_order_table']} where mb_id = '{$member['mb_id']}' AND od_del_yn = 'N' ";
}
else // 그렇지 않다면 로그인으로 가기
{
  goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/electronic_manage.php'));
}

// Page ID
$pid = ($pid) ? $pid : 'inquiry';
$at = apms_page_thema($pid);
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

$skin_row = array();
$skin_row = apms_rows('order_'.MOBILE_.'skin, order_'.MOBILE_.'set');
$skin_name = $skin_row['order_'.MOBILE_.'skin'];
$order_skin_path = G5_SKIN_PATH.'/apms/order/'.$skin_name;
$order_skin_url = G5_SKIN_URL.'/apms/order/'.$skin_name;

// 스킨 체크
list($order_skin_path, $order_skin_url) = apms_skin_thema('shop/order', $order_skin_path, $order_skin_url);

// 스킨설정
$wset = array();
if($skin_row['order_'.MOBILE_.'set']) {
  $wset = apms_unpack($skin_row['order_'.MOBILE_.'set']);
}

// 데모
if($is_demo) {
  @include ($demo_setup_file);
}

// 설정값 불러오기
$is_inquiry_sub = false;
@include_once($order_skin_path.'/config.skin.php');

$g5['title'] = '전자문서관리';


include_once('./_head.php');

$skin_path = $order_skin_path;
$skin_url = $order_skin_url;

// 셋업
$setup_href = '';
if(is_file($skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
  $setup_href = './skin.setup.php?skin=order&amp;name='.urlencode($skin_name).'&amp;ts='.urlencode(THEMA);
}

// 미작성 내역 일단 숨기기
$incompleted_eform_count = get_incompleted_eform_count();
$incompleted_eform_count = 0;
?>

<!-- 내용 -->
<title>판매재고목록</title>
<section class="wrap">
  <div class="sub_section_tit">전자문서관리</div>
<!--   <ul class="list_tab">
    <li><a href="<?=G5_SHOP_URL?>/claim_manage.php">청구관리</a></li>
    <li class="active"><a href="<?=G5_SHOP_URL?>/electronic_manage.php">전자문서관리<?php //echo ($incompleted_eform_count ? '<span class="red_info">미작성: '.$incompleted_eform_count.'건</span>' : ''); ?></a></li>
   </ul> -->
  <div class="inner">
    <div class="list_box" style="display: none;">
      <div class="point_box">
        <div class="subtit">
        전자문서 미 작성내역
        </div>
        <div class="table_box">
          <table>
            <tr>
              <th>No.</th>
              <th>수급자 정보</th>
              <th>상품정보</th>
              <th>기준월</th>
              <th>전자문서</th>
              <th>비고</th>
            </tr>
            <tr>
              <td>3</td>
              <td>홍길동(L2233321333 / 3등급 /기초0%)</td>
              <td>상품명(11111)</td>
              <td>2021년 3월</td>
              <td class="text_c">
                <a href="#" class="btn_basic">다운로드</a>
              </td>
              <td class="text_c">
                <a href="#">공급계약서 다운로드</a>
                <p>2021-01-01~ 2022-01-01</p>
              </td>
            </tr>
            <tr>
              <td>2</td>
              <td>홍길동(L2233321333 / 3등급 /기초0%)</td>
              <td>상품명(11111)</td>
              <td>2021년 3월</td>
              <td class="text_c">
                <a href="#" class="btn_point">서명하기</a>
              </td>
              <td class="text_c">
                <a href="#">공급계약서 다운로드</a>
                <p>2021-01-01~ 2022-01-01</p>
              </td>
            </tr>
            <tr>
              <td>1</td>
              <td>홍길동(L2233321333 / 3등급 /기초0%)</td>
              <td>상품명(11111)</td>
              <td>2021년 3월</td>
              <td class="text_c">
                <a href="#" class="btn_basic">다운로드</a>
              </td>
              <td class="text_c">
                <a href="#">공급계약서 다운로드</a>
                <p>2021-01-01~ 2022-01-01</p>
              </td>
            </tr>
          </table>
        </div>
        <p>
          *대여제품인 경우 수급자 분류에 따라서 서명이 필요한 문서가 노출됩니다.
        </p>
        <div class="list-paging">
          <ul class="pagination ">
            <li> </li>
            <li><a href="#">&lt;</a></li>
            <li class="active"><a href="#">1</a></li>
            <li><a href="#">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#">&gt;</a></li>
            <li></li>
          </ul>
        </div>
      </div>
    </div>
    <form id="form_search" method="get">
      <?php if($penId) { ?>
      <input type="hidden" name="penId" value="<?=$penId ? $penId : ''?>">
      <?php } else { ?>
      <div class="search_box">
        <select name="sel_field" id="sel_field">
          <option value="penNm"<?php if($sel_field == 'penNm' || $sel_field == 'all') echo ' selected'; ?>>수급자</option>
          <option value="it_name"<?php if($sel_field == 'it_name') echo ' selected'; ?>>상품명</option>
        </select>
        <div class="input_search">
          <input name="search" id="search" value="<?=$search?>" type="text">
          <button id="btn_search" type="submit"></button>
        </div>
      </div>
      <div class="r_btn_area">
        <select name="sel_order" id="sel_order" style="float: none;">
          <option value="dc_sign_datetime"<?php if(!$sel_order || $sel_order == 'dc_sign_datetime') echo ' selected'; ?>>작성일정렬</option>
          <option value="penNm"<?php if($sel_order == 'penNm') echo ' selected'; ?>>수급자정렬</option>
        </select>
        <a href="<?=G5_SHOP_URL?>/eform/downloadReceipt.php">기본 거래영수증 다운로드</a>
      </div>
      <?php } ?>
    </form>
    <div id="list_wrap" class="list_box">
    </div>
  </div>
</section>

<script>
$(function() {
  search();

  function search(queryString) {
    if(!queryString) queryString = '';
    var params = $('#form_search').serialize();
    var $listWrap = $('#list_wrap');

    $.ajax({
      method: 'GET',
      url: '<?=G5_SHOP_URL?>/eform/ajax.eform.list.php?' + queryString,
      data: params,
      beforeSend: function() {
        $listWrap.html('<div style="text-align:center;"><img src="<?=G5_URL?>/img/loading-modal.gif"></div>');
      }
    })
    .done(function(data) {
      $listWrap.html(data);
      // 페이지네이션 처리
      $('#list_wrap .pagination a').on('click', function(e) {
        e.preventDefault();
        var params = $(this).attr('href').replace('?', '');
        search(params);
      });
    })
    .fail(function() {
      $listWrap.html('');
    });
  }

  $('#btn_search').click(function(e) {
    $('#form_search').submit();
  });

  $('#search').keyup(function(e) {
    if(e.key === 'Enter') {
      $('#form_search').submit();
    }
  });

  $('#sel_order').change(function(e) {
    $('#form_search').submit();
  });
});
</script>

<?php
$t_claim = get_tutorial('claim');
if ($t_claim['t_state'] == '0') { 
?>
<script>
  show_eroumcare_popup({
    title: '청구내역 확인',
    content: '수급자 주문 후 누적된 청구내역을<br/>확인 하시겠습니까?',
    activeBtn: {
      text: '청구내역 확인',
      href: '/shop/claim_manage.php'
    },
    hideBtn: {
      text: '다음에',
    }
  });
</script>

<?php } ?>


<?php
if ($open_tutorial_popup) { 
?>
<script>
  show_eroumcare_popup({
    title: '튜토리얼 알림',
    content: '튜토리얼 진행중이시라면,<br/> 먼저 튜토리얼 주문의 계약서 작성을 완료해주세요.',
    activeBtn: {
      text: '확인',
      href: '/shop/orderinquiryview.php?od_id=<?php echo $t_result['od_id']; ?>'
    },
    hideBtn: {
      text: '다음에',
    }
  });
</script>

<?php } ?>

<?php
include_once('./_tail.php');
?>
