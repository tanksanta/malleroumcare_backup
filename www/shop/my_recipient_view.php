<?php
include_once("./_common.php");
define('_RECIPIENT_', true);

include_once("./_head.php");

// 수급자 연결 끊음
unset($_SESSION['recipient']);

# 회원검사
if(!$member["mb_id"])
  alert("접근 권한이 없습니다.");

if(!$_GET["id"])
  alert("정상적이지 않은 접근입니다.");

$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
  'usrId' => $member['mb_id'],
  'entId' => $member['mb_entId'],
  'penId' => $_GET['id']
));

if(!$res || $res['errorYN'] == 'Y')
  alert('서버 오류로 수급자 정보를 불러올 수 없습니다.');

$pen = $res['data'][0];
if(!$pen)
  alert('수급자 정보가 존재하지 않습니다.');

// 수급자 취급가능 제품
$items = get_items_by_recipient($pen['penId']);
$products = array(
  '00' => [], /* 판매제품 */
  '01' => [] /* 대여제품 */
);
foreach($items as $val) {
  $products[$val['gubun']][$val['itemId']] = $val['itemNm'];
}

function check_and_print($check, $prefix = '', $postfix = '') {
  if($check) return $prefix.$check.$postfix;
  return '';
}

// 메모 가져오기
$memos = get_memos_by_recipient($pen['penId']);

// 욕구사정기록지 가져오기
$recs = get_recs_by_recipient($pen['penId']);

// 적용기간 기준일
$pen_gra_apply_txt = '없음';
$pen_gra_apply_result = sql_fetch("
  SELECT
    pen_gra_apply_month,
    pen_gra_apply_day
  FROM
    recipient_grade_log
  WHERE
    pen_id = '{$_GET['id']}' and
    del_yn = 'N'
  ORDER BY
    seq desc
  LIMIT 1
");
$pen_gra_apply_month = $pen_gra_apply_result['pen_gra_apply_month'];
$pen_gra_apply_day = $pen_gra_apply_result['pen_gra_apply_day'];
if($pen_gra_apply_month && $pen_gra_apply_day)
  $pen_gra_apply_txt = "{$pen_gra_apply_month}월 {$pen_gra_apply_day}일";
?>
<link rel="stylesheet" href="<?=G5_CSS_URL?>/my_recipient.css?v=210829">
<div class="recipient_view_wrap">
  <div class="title_wrap">
    <div class="sub_section_tit">
      <?=$pen['penNm']?> (<?=substr($pen['penBirth'], 2, 2)?>년생/<?=$pen['penGender']?>)
    </div>
    <div class="r_btn_wrap">
      <a class="c_btn" href="./my_recipient_list.php">목록</a>
    </div>
  </div>
  <div class="info_wrap">
    <div class="row">
      <div class="col-sm-2">· 연락처</div>
      <div class="col-sm-10">: <?=$pen['penConNum']?><?=($pen['penConPnum'] ? ", {$pen['penConPnum']}" : "")?></div>
    </div>
    <div class="row">
      <div class="col-sm-2">· 주소</div>
      <div class="col-sm-10">: <?="{$pen['penAddr']} {$pen['penAddrDtl']}"?></div>
    </div>
    <div class="row">
      <div class="col-sm-2">· 장기요양정보</div>
      <div class="col-sm-10">: <?=substr($pen['penLtmNum'], 0, 6)?>**** (<?=$pen['penRecGraNm']?>/<?=$pen['penTypeNm']?>)</div>
    </div>
    <div class="row">
      <div class="col-sm-2">· 유효기간</div>
      <div class="col-sm-10">: <?=$pen['penExpiDtm']?></div>
    </div>
    <div class="row">
      <div class="col-sm-2">· 적용기간 기준일</div>
      <div class="col-sm-10">: <?=$pen_gra_apply_txt?></div>
    </div>
    <div class="row">
      <div class="col-sm-2">· 보호자</div>
      <?php if($pen['penProTypeCd'] == '00') { // 보호자 없음 ?>
      <div class="col-sm-10">: 없음</div>
      <?php } else { ?>
      <div class="col-sm-10">: <?php if($pen['penProTypeCd'] == '02') { echo '(요양보호사)'; } ?><?=check_and_print($pen_pro_rel_cd[$pen['penProRel']], '(', ')')?><?=$pen['penProNm']?><?=check_and_print(substr($pen['penProBirth'], 2, 2), ', ', '년생')?><?=check_and_print($pen['penProConNum'], ', ')?><?=check_and_print($pen['penProConPNum'], ', ')?><?=check_and_print($pen['penProAddr'], ', ')?><?=check_and_print($pen['penProAddrDtl'], ' ')?></div>
      <?php } ?>
    </div>
    <div class="row">
      <div class="col-sm-2">· 장기요양기록지</div>
      <div class="col-sm-10">: 확인자(<?=$pen_cnm_type_cd[$pen['penCnmTypeCd']]?>), 수령방법(<?=$pen_rec_type_cd[$pen['penRecTypeCd']]?>) <?=$pen['penRecTypeTxt']?></div>
    </div>
    <a class="c_btn" href="./my_recipient_update.php?id=<?=$pen['penId']?>">기본정보 수정</a>
    <div class="tel_btn_wrap">
      <a href="tel:<?=$pen['penConNum'] ?: $pen['penConPNum']?>" class="tel_btn"><i class="fa fa-phone" aria-hidden="true"></i>수급자 전화연결</a>
      <a href="tel:<?=$pen['penProConNum'] ?: $pen['penProConPnum']?>" class="tel_btn"><i class="fa fa-phone" aria-hidden="true"></i>보호자 전화연결</a>
    </div>
  </div>

  <div class="sub_title_wrap">
    <div class="sub_title">
      제공가능 품목
    </div>
    <div class="sub_title_desc">* 카테고리 선택 시 회원이 선택된 상태로 이동합니다.</div>
  </div>
  <div class="section_wrap">
    <div class="item_wrap">
      <div class="item_head">판매품목</div>
      <div class="item_body">
      <?php
      foreach($products['00'] as $id => $name) {
        $ca_id = $sale_product_cate_table[$id];
      ?>
        <a href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$pen['penId'].'&redirect='.urlencode('/shop/list.php?ca_id='.substr($ca_id, 0, 2).'&ca_sub%5B%5D='.substr($ca_id, 2, 2))?>"><?=$name?></a>
      <?php } ?>
      </div>
    </div>
    <div class="item_wrap">
      <div class="item_head">대여품목</div>
      <div class="item_body">
      <?php
      foreach($products['01'] as $id => $name) {
        $ca_id = $rental_product_cate_table[$id];
      ?>
        <a href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$pen['penId'].'&redirect='.urlencode('/shop/list.php?ca_id='.substr($ca_id, 0, 2).'&ca_sub%5B%5D='.substr($ca_id, 2, 2))?>"><?=$name?></a>
      <?php } ?>
      </div>
    </div>
  </div>

  <div class="sub_title_wrap">
    <div class="sub_title l_title">
      장바구니
      <?php
      $cart_count = get_carts_by_recipient($pen['penId']);
      echo " : {$cart_count}개"
      ?>
    </div>
    <div class="cart_btn_wrap r_btn_wrap">
      <a class="c_btn" href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$pen['penId'].'&redirect='.urlencode('/shop/list.php?ca_id=10')?>">신규추가하기</a>
      <a class="c_btn primary" href="<?=G5_SHOP_URL.'/connect_recipient.php?pen_id='.$pen['penId'].'&redirect='.urlencode('/shop/cart.php')?>">장바구니 바로가기</a>
    </div>
  </div>

  <div class="memo_wrap">
    <div class="sub_title_wrap">
      <div class="sub_title l_title">
        메모
      </div>
    </div>
    <div class="section_wrap grey">
      <div class="sub_section_wrap">
        <textarea name="memo" class="memo" rows="4"></textarea>
        <input type="submit" class="btn_write_memo c_btn primary" value="등록">
      </div>
      <?php foreach($memos as $memo) { ?>
      <div class="memo_row">
        <div class="memo_body">
          <div class="memo_date"><?=date('Y년 m월 d일', strtotime($memo['me_created_at']))?></div>
          <div class="memo_content"><?=nl2br($memo['memo'])?></div>
        </div>
        <div class="memo_btn_wrap">
          <button class="btn_edit_memo c_btn" data-id="<?=$memo['me_id']?>">수정</button>
          <button class="btn_delete_memo c_btn" data-id="<?=$memo['me_id']?>">삭제</button>
        </div>
      </div>
      <?php } ?>
    </div>
    <div class="main_btn_wrap">
      <a href="<?=G5_SHOP_URL.'/electronic_manage.php?penId='.$pen['penId']?>" class="primary">전자문서 확인</a>
      <a href="<?=G5_SHOP_URL.'/claim_manage.php?penId='.$pen['penId']?>" class="secondary">청구관리</a>
      <a href="<?=G5_SHOP_URL.'/orderinquiry.php?sel_field=all&search='.$pen['penId']?>">주문내역</a>
    </div>
  </div>

  <div class="sub_title_wrap">
    <div class="sub_title l_title">
      욕구사정기록지
    </div>
  </div>
  <div class="section_wrap grey">
    <div class="sub_section_wrap" style="text-align: center">
      <a href="<?=G5_SHOP_URL."/my_recipient_rec_form.php?id={$pen['penId']}"?>" class="b_btn">신규등록</a>
    </div>
    <?php foreach($recs as $rec) { ?>
    <div class="memo_row">
      <div class="memo_body">
        <div class="memo_date"><?=date('Y년 m월 d일', strtotime($rec['created_at']))?></div>
        <div class="memo_content"><?=nl2br($rec['total_review'])?></div>
      </div>
      <div class="memo_btn_wrap">
        <?php if($rec['type'] == 'simple') { ?>
        <button class="btn_print_rec c_btn primary" data-type="simple" data-id="<?=$rec['recId']?>">인쇄</button>
        <a href="<?=G5_SHOP_URL."/my_recipient_rec_form.php?id={$pen['penId']}&rs_id={$rec['recId']}"?>" class="c_btn" data-id="<?=$rec['recId']?>">수정</a>
        <button class="btn_delete_rec c_btn" data-type="simple" data-id="<?=$rec['recId']?>">삭제</button>
        <?php } else if($rec['type'] == 'detail') { ?>
        <button class="btn_print_rec c_btn primary" data-type="detail" data-id="<?=$rec['recId']?>">인쇄</button>
        <a href="<?=G5_SHOP_URL."/my_recipient_rec_detail_form.php?id={$pen['penId']}&rd_id={$rec['recId']}"?>" class="c_btn" data-id="<?=$rec['recId']?>">수정</a>
        <button class="btn_delete_rec c_btn" data-type="detail" data-id="<?=$rec['recId']?>">삭제</button>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
  </div>

  <style>
  .list-more a {
    margin-top:50px;
    font-weight: 400;
    padding: 9px 49px;
    display: inline-block;
    font-size: 14px;
    color: #232323;
    border: 1px solid #ddd;
  }
  </style>

  <div class="list-more">
    <p><a href="javascript:void(0)" id="delete_recipient">수급자 삭제</a></p>
    <p>
      *대여중인 품목이 있는 경우 수급자 삭제가 불가능합니다.<br/>
      *수급자를 삭제하시면 복구를 할 수 없습니다.
    </p>
  </div>
</div>

<!-- 인쇄 팝업 -->
<div id="popup_rec">
  <div></div>
</div>

<?php
$t_recipient_order = get_tutorial('recipient_order');
$t_recipient_add = get_tutorial('recipient_add');
if ($t_recipient_order['t_state'] == '0' && $t_recipient_add['t_data'] == $pen['penId']) {
?>
<script>
show_eroumcare_popup({
  title: '수급자 주문하기',
  content: '수급자 주문을 체험하시겠습니까?<br/>판매품목 1개, 대여품목1개<br/>선택되어 주문을 체험할 수 있습니다.',
  activeBtn: {
    text: '\'<?php echo htmlspecialchars($pen['penNm']); ?>\' 수급자로 주문체험하기',
    href: '/shop/tutorial_order.php'
  },
  hideBtn: {
    text: '다음에',
  }
});

</script>
<?php
} 
?>

<script>
$(function() {

  $(document).on('click', '#delete_recipient', function() {
    if(!confirm('수급자 삭제하시면 복구를 할 수 없습니다.\r\n삭제하시겠습니까?')) {
      return;
    }

    $.post('ajax.my.recipient.delete.php', {
      id: '<?=$pen['penId']?>',
    }, 'json')
    .done(function() {
      alert('삭제되었습니다.');
      location.href = './my_recipient_list.php';
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 메모 작성
  $(document).on('click', '.btn_write_memo', function() {
    var val = $(this).closest('div').find('.memo').val();
    $.post('ajax.my.recipient.memo.php', {
      id: '<?=$pen['penId']?>',
      memo: val
    }, 'json')
    .done(function() {
      location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 메모 수정 등록
  $(document).on('click', '.btn_update_memo', function() {
    var val = $(this).closest('div').find('.memo').val();
    var me_id = $(this).data('id');

    $.post('ajax.my.recipient.memo.php', {
      id: '<?=$pen['penId']?>',
      me_id: me_id,
      memo: val
    }, 'json')
    .done(function() {
      location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 메모 수정 취소
  $(document).on('click', '.btn_cancel_memo', function() {
    var $row = $(this).closest('.memo_row');

    $row.find('.memo_body').show();
    $row.find('.memo_btn_wrap').show();
    $row.find('.edit_memo_wrap').remove();
  });

  // 메모 수정
  $(document).on('click', '.btn_edit_memo', function() {
    var $row = $(this).closest('.memo_row');
    var val = $row.find('.memo_content').text();
    var me_id = $(this).data('id');

    $row.find('.memo_body').hide();
    $row.find('.memo_btn_wrap').hide();
    $('\
      <div class="edit_memo_wrap sub_section_wrap">\
        <textarea name="memo" class="memo" rows="4">'+val+'</textarea>\
        <input type="submit" class="btn_update_memo c_btn primary" data-id="'+me_id+'" value="등록">\
        <input type="button" class="btn_cancel_memo c_btn" value="취소">\
      </div>\
    ')
    .appendTo($row);
  });

  // 메모 삭제
  $(document).on('click', '.btn_delete_memo', function() {
    var me_id = $(this).data('id');

    if(confirm('메모를 삭제하시겠습니까?')) {
      $.post('ajax.my.recipient.memo.php', {
        id: '<?=$pen['penId']?>',
        me_id: me_id,
        del: true
      }, 'json')
      .done(function() {
        location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    }
  });

  // 욕구사정기록지 삭제
  $(document).on('click', '.btn_delete_rec', function() {
    var recId = $(this).data('id');
    var type = $(this).data('type');

    if(confirm('욕구사정기록지를 삭제하시겠습니까?')) {
      $.post('ajax.my.recipient.rec.delete.php', {
        penId: '<?=$pen['penId']?>',
        recId: rs_id,
        type: type
      }, 'json')
      .done(function() {
        location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    }
  });

  // 욕구사정기록지 인쇄
  $(document).on('click', '.btn_print_rec', function() {
    var recId = $(this).data('id');
    var type = $(this).data('type');

    $("#popup_rec > div").html("<iframe src='my_recipient_rec_print.php?id=<?=$pen['penId']?>&type="+type+"&recId="+recId+"'>");
    $("#popup_rec iframe").removeClass('mini');
    $("#popup_rec iframe").load(function() {
      $("html,body").addClass('modal-open');
      $("#popup_rec").show();
    });
  });
});
</script>

<?php include_once("./_tail.php"); ?>
