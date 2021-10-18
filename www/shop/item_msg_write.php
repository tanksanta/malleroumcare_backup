<?php
include_once("./_common.php");

if($member['mb_type'] !== 'default')
  alert('접근할 수 없습니다.');

$g5['title'] = '품목/정보 메시지 작성';
include_once("./_head.php");

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/item_msg.css">');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">');
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js"></script>');
?>

<section class="wrap">
  <div class="sub_section_tit">품목/정보 메시지 작성</div>
  <div class="inner">

    <form action="item_msg_update.php" method="POST" class="form-horizontal" onsubmit="return false;">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <label for="ms_pen_nm" class="col-sm-2 control-label">
              <strong>수급자명</strong>
            </label>
            <div class="col-sm-8">
              <input type="hidden" name="ms_pen_id" id="ms_pen_id">
              <input type="text" name="ms_pen_nm" id="ms_pen_nm" class="form-control input-sm pen_id_flexdatalist" placeholder="수급자명">
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
                  <input type="radio" name="ms_pro_yn" id="ms_pro_yn_n" value="N" checked> 수급자
                </label>
                <label class="radio-inline">
                  <input type="radio" name="ms_pro_yn" id="ms_pro_yn_y" value="Y"> 보호자
                </label>
              </div>
              <input type="text" name="ms_pen_hp" id="ms_pen_hp" class="form-control input-sm" placeholder="휴대폰번호">
              <span class="form_desc">* 입력된 휴대폰 번호로 메시지가 전송됩니다.</span>
            </div>
          </div>
        </div>
      </div>

      <div class="im_sch_wr">
        <div class="im_sch_hd">품목 목록</div>
        <input type="text" id="ipt_im_sch" class="ipt_im_sch" placeholder="품목명">
      </div>

      <ul id="im_write_list" class="im_write_list">
        <?php /* ?>
        <li>
          <input type="hidden" name="it_id[]" value="">
          <input type="hidden" name="it_name[]" value="">
          <input type="hidden" name="gubun[]" value="">
          <img class="it_img" src="/data/item/" onerror="this.src='/img/no_img.png';">
          <div class="it_info">
            <p class="it_name">ABC품목 (대여)</p>
            <p class="it_price">급여가 : 17,000원</p>
          </div>
          <button type="button" class="btn_del_item">삭제</button>
        </li>
        <?php */ ?>
      </ul>

      <div class="im_desc_wr" style="border: none;">
        <button type="submit" style="width: 250px;" href="javascript:void();" class="btn_im_send">메시지 전달</button>
        <div class="im_desc">
          <p>현재 <strong><?=number_format($member['mb_point']);?></strong>포인트가 있습니다. 1회 전송 시 <strong>10</strong>포인트가 차감됩니다.</p>
          <p style="color: #ef7d01;">*(무료이벤트) 2021년 12월 31일까지 포인트가 차감되지 않습니다.</p>
        </div>
      </div>
    </form>
  </div>
</section>

<script>
$(function() {
  var pen = null;

  $('.pen_id_flexdatalist').flexdatalist({
    minLength: 1,
    url: 'ajax.get_pen_id.php',
    cache: true, // cache
    searchContain: true, // %검색어%
    noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
    visibleProperties: ["penNm"],
    searchIn: ["penNm"],
    selectionRequired: true,
    focusFirstResult: true,
  }).on("select:flexdatalist", function(event, obj, options) {
    pen = obj;

    var prefix = [];

    if(obj.penBirth)
      prefix.push( obj.penBirth.substring(2, 4) + '년생' );
    if(obj.penGender)
      prefix.push( obj.penGender );

    if(prefix.length > 0)
      prefix = '(' + prefix.join('/') + ') ';
    else
      prefix = '';
    
    var postfix = [];

    if(obj.penRecGraNm)
      postfix.push( obj.penRecGraNm );
    if(obj.penTypeNm)
      postfix.push( obj.penTypeNm );
    
    if(postfix.length > 0)
      postfix = ' (' + postfix.join('/') + ')';
    else
      postfix = '';

    $('#pen_id_flexdatalist_result').text(
      prefix + obj.penLtmNum + postfix
    );

    $('#ms_pen_id').val(obj.penId);
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
        .html("[" + item.gubun + "] " + item.it_name + " (" + item.it_price + "원)");

      $item.appendTo($li);
      return $li;
    },
  }).on("select:flexdatalist", function(event, obj, options) {
    // it_id
    //$(parent).find('input[name="it_id[]"]').val(obj.it_id);
    var $li = $('<li>');
    $li.append('<input type="hidden" name="it_id[]" value="' + obj.it_id + '">');
    $li.append('<input type="hidden" name="it_name[]" value="' + obj.it_name + '">');
    $li.append('<input type="hidden" name="gubun[]" value="' + obj.gubun + '">');
    $li.append('<img class="it_img" src="/data/item/' + obj.it_img + '" onerror="this.src=\'/img/no_img.png\';">');
    $('<div class="it_info">')
      .append(
        '<p class="it_name">' + obj.it_name + ' (' + obj.gubun + ')' + '</p>',
        '<p class="it_price">급여가 : ' + parseInt(obj.it_price).toLocaleString('en-US') + '원</p>'
      )
      .appendTo($li);
    $li.append('<button type="button" class="btn_del_item">삭제</button>');
    $li.appendTo('#im_write_list');
    $('#ipt_im_sch').val('').next().focus();
  });

  $(document).on('click', '.btn_del_item', function() {
    $(this).closest('li').remove();
  });
});
</script>

<?php include_once("./_tail.php"); ?>
