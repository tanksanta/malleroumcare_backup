<?php
include_once('./_common.php');

$dc_id = clean_xss_tags($_GET['dc_id']);
if($dc_id) {
  $eform = sql_fetch("
  SELECT HEX(`dc_id`) as uuid, e.*
  FROM `eform_document` as e
  WHERE dc_id = UNHEX('$dc_id') and entId = '{$member['mb_entId']}' and dc_status = '11' ");

  if(!$eform['uuid'])
    unset($eform);
}

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

if (!$is_member)
  goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/electronic_manage.php'));

$g5['title'] = '전자문서관리';
include_once('./_head.php');

// 미작성 내역 일단 숨기기
$incompleted_eform_count = get_incompleted_eform_count();
$incompleted_eform_count = 0;
?>

<!-- 내용 -->
<section class="wrap">
  <div class="sub_section_tit">전자문서관리</div>
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
    <form action="account_update.php" method="POST" autocomplete="off" onsubmit="return faccount_submit(this);">
      <div class="mb_account_wr">
        <span>내 계좌정보 : </span>
        <div class="account_view_wr" <?php if(!$member['mb_account']) echo 'style="display: none;"'; ?>>
          <?=$member['mb_account']?>
          <button type="button" class="btn_acc_edit btn_basic">수정</button> 
        </div>
        <div class="account_edit_wr" <?php if($member['mb_account']) echo 'style="display: none;"'; ?>>
          <input type="text" name="mb_account" value="<?=get_text($member['mb_account']) ?: ''?>">
          <button type="submit" class="btn_basic">저장</button>
          <?php if($member['mb_account']) { ?>
          <button type="button" class="btn_acc_cancel btn_basic">취소</button>
          <?php } ?>
        </div>
		<a href="/shop/electronic_manage_new.php" class="btn eroumcare_btn2" target="_blank">전자문서관리 NEW</a>
      </div>
    </form>
    <form id="form_search" method="get">
      <?php if($penId) { ?>
      <input type="hidden" name="penId" value="<?=$penId ? $penId : ''?>">
      <?php } else { ?>
      <?php if($member['mb_type'] !== 'normal') { ?>
      <div class="search_box">
        <label><input type="checkbox" name="incompleted" value="1" <?=get_checked($incompleted, '1')?>/> 대기중인 계약만 보기</label><br>
        <select name="sel_field" id="sel_field">
          <option value="penNm"<?php if($sel_field == 'penNm' || $sel_field == 'all') echo ' selected'; ?>>수급자</option>
          <option value="it_name"<?php if($sel_field == 'it_name') echo ' selected'; ?>>상품명</option>
        </select>
        <div class="input_search">
          <input name="search" id="search" value="<?=$search?>" type="text">
          <button id="btn_search" type="submit"></button>
        </div>
      </div>
      <?php } ?>
      <div class="r_btn_area">	  	
        <select name="sel_order" id="sel_order" style="float: none;">
          <option value="dc_datetime"<?php if(!$sel_order || $sel_order == 'dc_datetime') echo ' selected'; ?>>작성일정렬</option>
          <option value="penNm"<?php if($sel_order == 'penNm') echo ' selected'; ?>>수급자정렬</option>
        </select>
        <?php if($member['mb_type'] !== 'normal') { ?>
        <a href="<?=G5_SHOP_URL?>/eform/downloadReceipt.php">기본 급여비용 명세서 다운로드</a>
        <?php } ?>
      </div>
      <?php } ?>
    </form>
    <div id="list_wrap" class="list_box">
    </div>
  </div>
</section>

<script>
// 내 계좌정보 변경
function faccount_submit(f) {
  return true;
}

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

  $(document).on('click', '.btn_del_eform', function(e) {
    e.preventDefault();

    if(!confirm('정말 삭제하시겠습니까?'))
      return;

    $.post('ajax.simple_eform.php', {
      w: 'd',
      dc_id: $(this).data('id')
    }, 'json')
    .done(function() {
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 계약서 재전송
  $(document).on('click', '.btn_resend_eform', function(e) {
    e.preventDefault();

    var dc_id = $(this).data('id');
    var name = $(this).data('name');
    var hp = $(this).data('hp');
    var mail = $(this).data('mail');

    var confirm_msg = name + '(' + hp + (mail ? ' / ' + mail : '') + ') 수급자에게 계약서를 다시 전송하시겠습니까?';
    if(!confirm(confirm_msg))
      return;
    
    $.post('/shop/eform/ajax.eform.resend.php', {
      dc_id: dc_id
    }, 'json')
    .done(function() {
      alert('전송되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 내 계좌정보 변경
  $('.btn_acc_edit').click(function() {
    $('.account_view_wr').hide();
    $('.account_edit_wr').show();
  });
  $('.btn_acc_cancel').click(function() {
    $('.account_view_wr').show();
    $('.account_edit_wr').hide();
  });

  <?php if($eform) { ?>
  if(confirm('계약서가 생성되었습니다.\n지금 바로 <?=$eform['penNm']?> 수급자에게 서명을 받으시겠습니까?'))
    window.location.href = '/shop/eform/signEform.php?dc_id=<?=$dc_id?>';
  <?php } ?>
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
