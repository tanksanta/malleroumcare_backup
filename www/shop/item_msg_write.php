<?php
include_once("./_common.php");

if($member['mb_type'] !== 'default')
  alert('접근할 수 없습니다.');

$g5['title'] = '품목/정보 메시지 작성';
include_once("./_head.php");

$w = get_search_string($_GET['w']);
$ms_id = get_search_string($_GET['ms_id']);

if($w && $ms_id) {
  $sql = " select * from recipient_item_msg where ms_id = '$ms_id' and mb_id = '{$member['mb_id']}' ";
  $ms = sql_fetch($sql);

  if($ms['ms_pen_id']) {
    $pen = get_recipient($ms['ms_pen_id']);
    $filters = ['penId', 'penNm', 'penLtmNum', 'penRecGraNm', 'penTypeNm', 'penBirth', 'penGender', 'penConNum', 'penProConNum'];
    $filtered_pen = [];
    foreach($filters as $filter) {
      $filtered_pen[$filter] = $pen[$filter];
    }
    $pen = $filtered_pen;
    unset($filtered_pen);
  }

  $sql = "
    SELECT
      m.*,
      it_img1 as it_img,
      it_cust_price
    FROM
      recipient_item_msg_item m
    LEFT JOIN
      g5_shop_item i ON m.it_id = i.it_id
    WHERE
      ms_id = '{$ms['ms_id']}'
    ORDER BY
      mi_id ASC ";
  $result = sql_query($sql);

  $items = [];
  while($row = sql_fetch_array($result)) {
    $items[] = $row;
  }
}

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/item_msg.css">');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">');
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js"></script>');
?>

<section class="wrap">
  <div class="sub_section_tit">품목/정보 메시지 작성</div>
  <div class="inner">

    <form id="form_item_msg" action="item_msg_update.php" method="POST" class="form-horizontal" onsubmit="return false;">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <label for="ms_pen_nm" class="col-sm-2 control-label">
              <strong>수급자명</strong>
            </label>
            <div class="col-sm-8">
              <input type="hidden" name="ms_pen_id" id="ms_pen_id" value="<?=$ms['ms_pen_id'] ?: ''?>">
              <input type="text" name="ms_pen_nm" id="ms_pen_nm" class="form-control input-sm pen_id_flexdatalist" value="<?=$ms['ms_pen_nm'] ?: ''?>" placeholder="수급자명">
              <span id="pen_id_flexdatalist_result" class="form_desc"></span>
            </div>
          </div>
          <div class="form-group">
            <label for="ms_pen_hp" class="col-sm-2 control-label">
              <strong>휴대폰번호</strong>
            </label>
            <div class="col-sm-8">
              <div class="radio_wr">
                <label class="radio-inline">
                  <input type="radio" name="ms_pro_yn" id="ms_pro_yn_n" value="N" <?=option_array_checked($ms['ms_pro_yn_n'], ['', 'N'])?>> 수급자
                </label>
                <label class="radio-inline">
                  <input type="radio" name="ms_pro_yn" id="ms_pro_yn_y" value="Y" <?=option_array_checked($ms['ms_pro_yn_n'], ['Y'])?>> 보호자
                </label>
              </div>
              <input type="text" name="ms_pen_hp" id="ms_pen_hp" class="form-control input-sm" value="<?=$ms['ms_pen_hp'] ?: ''?>" placeholder="휴대폰번호">
              <span class="form_desc">* 입력된 휴대폰 번호로 메시지가 전송됩니다.</span>
            </div>
          </div>
          <div class="form-group">
            <label for="ms_pen_url" class="col-sm-2 control-label" style="padding-top: 0;">
              <strong>전송 URL</strong>
            </label>
            <div class="col-sm-8 url">
              품목선택 후 저장 시 생성됩니다.
            </div>
          </div>
        </div>
        <div class="im_send_wr im_desc_wr" style="border: none;">
          <button type="submit" href="javascript:void();" id="btn_im_send" class="btn_im_send">
            <img src="<?=THEMA_URL?>/assets/img/icon_kakao.png" alt="">
            알림 메시지 전달
          </button>
          <div class="im_desc">
            <p>보유 <strong><?=number_format($member['mb_point']);?></strong>포인트, 1회 전송 시 <strong>10</strong>포인트 차감</p>
          </div>
        </div>
      </div>

      <div class="im_flex space-between">
        <div class="im_item_wr">
          <div class="im_tel_wr im_flex space-between">
            <div class="im_sch_hd">전화번호 공개</div>
            <input class="im_switch" id="ms_ent_tel" type="checkbox" name="ms_ent_tel" value="<?=get_text($member['mb_tel'])?>" <?=get_checked($ms['ms_ent_tel'], get_text($member['mb_tel']))?>>
            <label for="ms_ent_tel">
              <div class="im_switch_slider">
                <span class="on">공개</span>
                <span class="off">숨김</span>
              </div>
            </label>
          </div>
          <div class="im_sch_wr">
            <div class="im_sch_hd">품목 목록</div>
            <input type="text" id="ipt_im_sch" class="ipt_im_sch" placeholder="품목명">
            <button class="btn_im_sel">품목찾기</button>
          </div>
          <ul id="im_write_list" class="im_write_list">
            <?php
            if(isset($items) && is_array($items)) {
              foreach($items as $item) {
            ?>
            <li>
              <input type="hidden" name="it_id[]" value="<?=$item['it_id']?>">
              <input type="hidden" name="it_name[]" value="<?=get_text($item['it_name'])?>">
              <input type="hidden" name="gubun[]" value="<?=$item['gubun']?>">
              <img class="it_img" src="/data/item/<?=$item['it_img']?>" onerror="this.src='/img/no_img.png';">
              <div class="it_info">
                <p class="it_name"><?=get_text($item['it_name']) . " ({$item['gubun']})"?> (대여)</p>
                <p class="it_price">급여가 : <?=number_format($item['it_cust_price'])?>원</p>
              </div>
              <button type="button" class="btn_del_item">삭제</button>
            </li>
            <?php
              }
            }
            ?>
          </ul>
          <button type="button" id="btn_im_save">저장</button>
          <div class="im_rec_wr">
            <div class="im_rec_hd im_flex space-between">
              <div class="im_sch_hd">추천정보</div>
              <div class="im_rec_desc">선택한 정보는 전송 시 함께 전달됩니다.</div>
            </div>
            <ul class="im_rec_list">
              <li>
                <div class="im_rec_desc">
                  초기 수급자가 꼭 알아야하는 10가지 정보를 공유합니다.
                </div>
                <input class="im_switch" id="ms_rec_1" type="checkbox" name="ms_rec_1" value="1">
                <label for="ms_rec_1">
                  <div class="im_switch_slider">
                    <span class="on">선택</span>
                    <span class="off">미선택</span>
                  </div>
                </label>
              </li>
              <li>
                <div class="im_rec_desc">
                  보호자가 숙지해야 하는 정보와 건강보험공단 자료실 활용방법을 소개합니다.
                </div>
                <input class="im_switch" id="ms_rec_2" type="checkbox" name="ms_rec_2" value="1">
                <label for="ms_rec_2">
                  <div class="im_switch_slider">
                    <span class="on">선택</span>
                    <span class="off">미선택</span>
                  </div>
                </label>
              </li>
            </ul>
          </div>
        </div>
        <div class="im_preview_wr">
          <div class="im_preview_hd">
            수급자에게 전송되는 화면 미리보기
          </div>
          <div class="im_preview"><div class="empty">품목선택 후 저장 시 생성됩니다.</div></div>
        </div>
      </div>
    </form>
  </div>
</section>

<div id="popup_box">
    <div class="popup_box_close">
        <i class="fa fa-times"></i>
    </div>
    <iframe name="iframe" src="" scrolling="yes" frameborder="0" allowTransparency="false"></iframe>
</div>

<script>
// 품목 선택
function select_item(obj) {
  $('body').removeClass('modal-open');
  $('#popup_box').hide();

  // it_id
  var $li = $('<li>');
  $li.append('<input type="hidden" name="it_id[]" value="' + obj.it_id + '">');
  $li.append('<input type="hidden" name="it_name[]" value="' + obj.it_name + '">');
  $li.append('<input type="hidden" name="gubun[]" value="' + obj.gubun + '">');
  $li.append('<img class="it_img" src="/data/item/' + obj.it_img + '" onerror="this.src=\'/img/no_img.png\';">');
  $('<div class="it_info">')
    .append(
      '<p class="it_name">' + obj.it_name + ' (' + obj.gubun + ')' + '</p>',
      '<p class="it_price">급여가 : ' + parseInt(obj.it_cust_price).toLocaleString('en-US') + '원</p>'
    )
    .appendTo($li);
  $li.append('<button type="button" class="btn_del_item">삭제</button>');
  $li.appendTo('#im_write_list');
  $('#ipt_im_sch').val('').next().focus();
}

// 저장
function save_item_msg() {
  
}

$(function() {
  <?php if(isset($pen) && $pen) { ?>
  var pen = <?=json_encode($pen)?>;
  update_pen_info();
  <?php } else { ?>
  var pen = null;
  <?php } ?>

  function update_pen_info() {
    var prefix = [];

    if(pen.penBirth)
      prefix.push( pen.penBirth.substring(2, 4) + '년생' );
    if(pen.penGender)
      prefix.push( pen.penGender );

    if(prefix.length > 0)
      prefix = '(' + prefix.join('/') + ') ';
    else
      prefix = '';
    
    var postfix = [];

    if(pen.penRecGraNm)
      postfix.push( pen.penRecGraNm );
    if(pen.penTypeNm)
      postfix.push( pen.penTypeNm );
    
    if(postfix.length > 0)
      postfix = ' (' + postfix.join('/') + ')';
    else
      postfix = '';

    $('#pen_id_flexdatalist_result').text(
      prefix + pen.penLtmNum + postfix
    );

    $('#ms_pen_id').val(pen.penId);
  }

  $('.pen_id_flexdatalist').flexdatalist({
    minLength: 1,
    url: 'ajax.get_pen_id.php',
    cache: true, // cache
    searchContain: true, // %검색어%
    noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
    visibleProperties: ["penNm"],
    searchIn: ["penNm"],
    focusFirstResult: true,
  })
  .on('change:flexdatalist', function() {
    $('#ms_pen_id').val('');
    $('#pen_id_flexdatalist_result').text('');
  })
  .on("select:flexdatalist", function(event, obj, options) {
    pen = obj;

    update_pen_info();
    setPenHp('N');

    $('#ipt_im_sch').next().focus();
  });

  $('input[name="ms_pro_yn"]').click(function() {
    setPenHp($(this).val());
  });

  function setPenHp(proYN) {
    if(proYN === 'Y') {
      $('#ms_pro_yn_y').prop('checked', true);
      $('#ms_pro_yn_n').prop('checked', false);

      $('#ms_pen_hp').val(pen ? pen.penProConNum : '');
    } else {
      $('#ms_pro_yn_y').prop('checked', false);
      $('#ms_pro_yn_n').prop('checked', true);

      $('#ms_pen_hp').val(pen ? pen.penConNum : '');
    }
  }

  $('#ipt_im_sch').flexdatalist({
    minLength: 1,
    url: 'ajax.get_item.php',
    cache: true, // cache
    searchContain: true, // %검색어%
    noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
    selectionRequired: true,
    focusFirstResult: true,
    searchIn: ["it_name","it_model","it_id", "it_name_no_space"],
    visibleCallback: function($li, item, options) {
      var $item = {};
      $item = $('<span>')
        .html("[" + item.gubun + "] " + item.it_name + " (" + item.it_cust_price + "원)");

      $item.appendTo($li);
      return $li;
    },
  }).on("select:flexdatalist", function(event, obj, options) {
    select_item(obj);
  });

  $(document).on('click', '.btn_del_item', function() {
    $(this).closest('li').remove();
  });

  var loading = false;
  $('#btn_im_send').on('click', function() {
    if(loading)
      return alert('전송 중입니다. 잠시만 기다려주세요.');

    loading = true;
    $form = $('#form_item_msg');
    $.post($form.attr('action'), $form.serialize(), 'json')
    .done(function(result) {
      var ms_id = result.data;
      window.location.href = 'item_msg_view.php?ms_id=' + ms_id;
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      loading = false;
    });
  });

  // 품목찾기 팝업
  $('#popup_box').click(function() {
    $('body').removeClass('modal-open');
    $('#popup_box').hide();
  });
  $('.btn_im_sel').click(function(e) {
    var url = 'pop.item.select.php?no_option=1';

    $('#popup_box iframe').attr('src', url);
    $('body').addClass('modal-open');
    $('#popup_box').show();
  });

});
</script>

<?php include_once("./_tail.php"); ?>
