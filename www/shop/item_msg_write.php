<?php
include_once("./_common.php");

if($member['mb_type'] !== 'default')
  alert('접근할 수 없습니다.');

$g5['title'] = '간편 제안서 작성';
include_once("./_head.php");

$sql = "
  select
    count(*) as cnt
  from
    recipient_item_msg_log l
  left join
    recipient_item_msg m ON l.ms_id = m.ms_id
  where
    m.mb_id = '{$member['mb_id']}' and
    date(l.ml_sent_at) = curdate()
";
$today_count = sql_fetch($sql)['cnt'] ?: 0;
$today_count = 5 - $today_count;
if(in_array($member['mb_id'], ['hula1202', 'joabokji'])) {
  $today_count = 100 - $today_count;
}

$w = get_search_string($_GET['w']);
$ms_id = get_search_string($_GET['ms_id']);

if($w && $ms_id) {
  $sql = " select * from recipient_item_msg where ms_id = '$ms_id' and mb_id = '{$member['mb_id']}' ";
  $ms = sql_fetch($sql);

  if(!$ms['ms_id'])
    alert('존재하지 않는 메시지입니다.');

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
  <div class="sub_section_tit">간편 제안서 작성</div>
  <div class="inner">

    <form id="form_item_msg" action="item_msg_update.php" method="POST" class="form-horizontal" onsubmit="return false;">
      <input type="hidden" name="w" value="<?=$w?>">
      <input type="hidden" name="ms_id" value="<?=$ms_id?>">
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
            <div class="col-sm-6 url">
              <span id="ms_pen_url">
                <?php
                if($ms['ms_url']) {
                  echo 'https://eroumcare.com/shop/item_msg.php?url='.$ms['ms_url'];
                } else {
                  echo '품목선택 후 저장 시 생성됩니다.';
                }
                ?>
              </span>
              <button class="btn_im_copy" onclick="copy_to_clipboard('#ms_pen_url');">주소복사</button>
            </div>
          </div>
        </div>
        <div class="im_send_wr im_desc_wr" style="border: none; <?php if($today_count <= 0) echo 'opacity: 10%;' ?>">
          <button type="submit" id="btn_im_send" class="btn_im_send">
            <img src="<?=THEMA_URL?>/assets/img/icon_kakao.png" alt="">
            알림 메시지 전달
          </button>
          <div class="im_desc">
          	<p>
          	간편 제안서 무료발송 이벤트 진행중<br>
          	오늘의 무료 5건 중 <span><?=($today_count)?>건 남음</span></p>
            <!-- <p>보유 <strong><?=number_format($member['mb_point']);?></strong>포인트, 1회 전송 시 <strong>10</strong>포인트 차감</p> -->
          </div>
        </div>
      </div>

      <div class="im_flex space-between">
        <div class="im_item_wr">
          <div class="im_tel_wr im_flex space-between">
            <div class="im_sch_hd">사업소 전화번호 공개</div>
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
            <input type="text" id="ipt_im_sch" class="ipt_im_sch" placeholder="여기에 추가할 상품명을 입력해주세요">
            <div class="im_sch_pop">
              <p>상품명을 입력 후 간편하게 추가할 수 있습니다. 상품명 일부만 입력해도 자동완성됩니다.</p>
              <p>상품명을 모르시면 '상품검색' 버튼을 눌러주세요.</p>
              <p><button type="button" class="btn_im_sel">상품검색</button></p>
            </div>
          </div>
          
            <div class="no_item_info">
	        	<img src="<?=THEMA_URL?>/assets/img/icon_box.png" alt=""><br>
	        	<p>수급자에게 전달할 품목을 검색한 후 추가하시면<br>추가된 모든 품목은 수급자에게 전달됩니다.</p>
	        	<p class="txt_point">품목명을 모르시면 “품목찾기”버튼을 클릭해주세요.</p>
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
                <p class="it_name"><?=get_text($item['it_name']) . " ({$item['gubun']})"?></p>
                <p class="it_price">급여가 : <?=number_format($item['it_cust_price'])?>원</p>
              </div>
              <button type="button" class="btn_del_item">삭제</button>
            </li>
            <?php
              }
            }
            ?>
          </ul>
          <button type="button" id="btn_im_save" onclick="save_item_msg();">저장</button>
          <div class="im_rec_wr">
            <div class="im_rec_hd im_flex space-between">
              <div class="im_sch_hd">추천정보</div>
              <div class="im_rec_desc">선택한 정보는 전송 시 함께 전달됩니다.</div>
            </div>
            <ul class="im_rec_list">
              <?php
              $notice_arr = sql_fetch(" select bo_notice from g5_board where bo_table = 'info' ");
              $notice_arr = explode(',', trim($notice_arr['bo_notice']));

              foreach($notice_arr as $wr_id) {
                if(trim($wr_id) == '') continue;
                $sql = " select * from g5_write_info where wr_id = '$wr_id' ";
                $rec = sql_fetch($sql);
              ?>
              <li>
                <div class="im_rec_desc">
                  <?=$rec['wr_subject']?>
                </div>
                <input class="im_switch" id="ms_rec_<?=$wr_id?>" type="checkbox" name="ms_rec[]" value="<?=$wr_id?>" <?=option_array_checked($wr_id, $ms['ms_rec'])?>>
                <label for="ms_rec_<?=$wr_id?>">
                  <div class="im_switch_slider">
                    <span class="on">선택</span>
                    <span class="off">미선택</span>
                  </div>
                </label>
              </li>
              <?php
              }
              ?>
            </ul>
          </div>
        </div>
        <div class="im_preview_wr">
          <div class="im_preview_hd">
            수급자에게 전송되는 화면 미리보기
          </div>
          <div id="im_preview" class="im_preview">
            <?php if($ms['ms_url']) { ?>
            <iframe src="item_msg.php?url=<?=$ms['ms_url']?>" frameborder="0"></iframe>
            <?php } else { ?>
            <div class="empty">품목선택 후 저장 시 생성됩니다.</div>
            <?php } ?>
          </div>
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
// 클립보드 복사
function copy_to_clipboard(selector) {
  var url = $(selector).text();
  $("body").append("<input type='text' id='copyTextBox' value='" + url + "'>");
  $("#copyTextBox").select();
  document.execCommand("copy");
  $("#copyTextBox").remove();

  alert('복사되었습니다.');
}

// 품목 없는지 체크
function check_no_item() {
  if($('.im_write_list li').length == 0) {
    $('.no_item_info').show();
  } else {
    $('.no_item_info').hide();
  }
}

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

  check_no_item();
}

// 저장
var loading = false;
function save_item_msg(no_items) {
  if(loading)
    return alert('저장 중입니다. 잠시만 기다려주세요.');
  
  if($('.pen_id_flexdatalist').val() !== $('.pen_id_flexdatalist').next().val())
    $('.pen_id_flexdatalist').val($('.pen_id_flexdatalist').next().val());

  loading = true;
  $form = $('#form_item_msg');
  var query = $form.serialize();
  if(no_items)
    query += '&no_items=1';
  $.post($form.attr('action'), query, 'json')
  .done(function(result) {
    var data = result.data;
    var ms_url = 'item_msg.php?url=' + data.ms_url;
    $('input[name="w"]').val('u');
    $('input[name="ms_id"]').val(data.ms_id);
    $('#ms_pen_url').text('https://eroumcare.com/shop/' + ms_url);
    $('#im_preview').empty().append($('<iframe>').attr('src', ms_url).attr('frameborder', 0));
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  })
  .always(function() {
    loading = false;
  });
}

$(function() {
  <?php if(isset($pen) && $pen) { ?>
  var pen = <?=json_encode($pen)?>;
  update_pen_info();
  <?php } else { ?>
  var pen = null;
  <?php } ?>

  function update_pen_info() {
    if(!pen) {
      $('#ms_pen_id').val('');
      $('#pen_id_flexdatalist_result').text('');
      return;
    }

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
    noResultsText: '"{keyword}"으로 등록된 수급자가 없습니다. 수급자정보를 직접 입력 하시고 제안서 작성 시 자동으로 등록됩니다.',
    visibleProperties: ["penNm"],
    searchIn: ["penNm"],
    focusFirstResult: true,
  })
  .on('change:flexdatalist', function() {
    pen = null;
    update_pen_info();
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

      if(pen)
        $('#ms_pen_hp').val(pen.penProConNum);
    } else {
      $('#ms_pro_yn_y').prop('checked', false);
      $('#ms_pro_yn_n').prop('checked', true);

      if(pen)
        $('#ms_pen_hp').val(pen.penConNum);
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
    check_no_item();
  });

  var sending = false;
  $('#btn_im_send').on('click', function() {
    if(sending)
      return alert('전송 중입니다. 잠시만 기다려주세요.');
    
    var ms_id = $('input[name="ms_id"]').val();

    if(!ms_id)
      return alert('먼저 품목 선택 후 저장을 해주세요.');

    sending = true;
    $form = $('#form_item_msg');
    $.post('item_msg_send.php', {
      ms_id: ms_id
    }, 'json')
    .done(function(result) {
      alert('전송이 완료되었습니다.');
      window.location.href = 'item_msg_write.php?w=u&ms_id=' + ms_id;
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      sending = false;
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

  // 스위치 변경시
  $('.im_switch').click(function(e) {
    if(loading) return false;

    var ms_id = $('input[name="ms_id"]').val();

    if(!ms_id) {
      alert('먼저 품목 선택 후 저장을 해주세요.');
      return false;
    }

    save_item_msg(true);
  });

  // 상품검색 팝업
  $(document).on('focus', '.ipt_im_sch', function() {
    $('.im_sch_pop').show();
  });
  $(document).on('click', function(e) {
    if($(e.target).closest('.im_sch_wr').length > 0) 
      return;

    $('.im_sch_pop').hide();
  });

  check_no_item();
  
  // 처음 팝업
  $('.im_sch_pop').show();
});
</script>

<?php include_once("./_tail.php"); ?>
