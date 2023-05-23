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
if(in_array($member['mb_id'], ['hula1202', 'joabokji'])) {
  $today_count = 100 - $today_count;
} else {
  $today_count = 5 - $today_count;
}

$w = get_search_string($_GET['w']);
$ms_id = get_search_string($_GET['ms_id']);
$show_expected = get_search_string($_GET['show_expected']);

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
      it_cust_price,
      it_expected_warehousing_date
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

$notice_arr = sql_fetch(" select bo_notice from g5_board where bo_table = 'info' ");
$notice_arr = explode(',', trim($notice_arr['bo_notice']));

$recs = [];
foreach($notice_arr as $wr_id) {
  if(trim($wr_id) == '') continue;
  $sql = " select * from g5_write_info where wr_id = '$wr_id' ";
  $rec = sql_fetch($sql);

  $recs[] = $rec;
}

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/item_msg.css?v=211125">');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">');
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js?v=220102"></script>');
?>

<script>
// 최대길이 체크
function max_length_check(object){
  object.value = object.value.replace(/[^0-9]/g,'');
  if (object.value.length > object.maxLength) {
    object.value = object.value.slice(0, object.maxLength);
  }
}

function agreement_confirm(a){
	if(a == "Y"){//동의 미처리
		$("#agreement").val("ok");
		$("#"+$("#send_type").val()).trigger("click");
	}else{//동의 
		$("#agreement").val("");
	}
	$('body').removeClass('modal-open');
	$(".thkc_pop_confirm").hide();
}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* reset */
        * { margin: 0; padding: 0; box-sizing: border-box; } 
 

        .thkc_pop_confirm { width: 100%; height: 100%; display: none; align-items: center; background: rgba(0, 0, 0, 0.5); position:fixed;top:0px;left:0px;z-index:9999999999999;} 

        .thkc_wrapPopup { margin: 0 auto; width: 320px; background: #fff; color: #666;}  
        .thkc_wrapPopup>p { padding: 10px 15px; display: flex; justify-content: flex-end;} 
        .thkc_wrapPopup path.svgX {stroke:#000; stroke-opacity: .5;} 
        .thkc_wrapPopup section p{padding: 10px 20px 50px 20px; text-align: center;}
        .thkc_wrapPopup section p>span{color: #001E9A; text-decoration: underline;}
        .thkc_wrapPopup section button{border:none !important; }      
        
        .thkc_wrapPopup .btnWrap { display: flex; flex-direction: row; align-content: flex-end; } 
        .thkc_wrapPopup .btnWrap button {height: 50px; width: 50%; padding: 0px 10px; display: flex; justify-content: center; align-items: center; font-size: 14px; font-weight: bold; border: none;} 
        .thkc_wrapPopup .btnWrap button.okType:hover, .btnWrap button.celType:hover { opacity:0.8; cursor: pointer;}
        .thkc_wrapPopup .btnWrap button.okType { background: #001E9A; color: #fff; } 
        .thkc_wrapPopup .btnWrap button.celType { background: #ddd; color: #444;} 
    </style>
 <div class="thkc_pop_confirm">
        <div class="thkc_wrapPopup">          
            <p>
                <a href="#" onClick="agreement_confirm('N')">
                    <svg width="12" height="11" fill="none"><path class="svgX"  d="m.646 10.646 10-10M1.354.646l10 10"/></svg>
                </a>
            </p>           
            <section>                              
                <p>복지용구 제안 수신인 (수급자 또는 수급자 보호자)에게 <span>제안서 전송을 위한 개인정보 수집·이용 안내 및 동의</span>를 받았음을<br>확인합니다.</p>                                    
            </section>            
            <div class="btnWrap">
                <button class="celType" onClick="agreement_confirm('N')">돌아가기 (미확인)</button><button class="btn okType" onClick="agreement_confirm('Y')">전송하기 (확인)</button>
				<input type="hidden" name="agreement" id="agreement" value="">
				<input type="hidden" name="send_type" id="send_type" value="">
            </div>
        </div>
    </div>
<section class="wrap">
  <div class="sub_section_tit">간편 제안서 작성</div>
  <div class="inner">

    <form id="form_item_msg" action="item_msg_update.php" method="POST" class="form-horizontal" onsubmit="return false;">
      <input type="hidden" name="w" id="w" value="<?=$w?>">
      <input type="hidden" name="ms_id" id="ms_id" value="<?=$ms_id?>">
	  <input type="hidden" name="ms_id2" id="ms_id2" value="<?=$ms_id?>">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="radio_wr" style="margin-top: -10px; margin-bottom: 10px; font-size: 14px;">
            <label class="radio-inline">
              <input type="radio" name="pen_type" id="pen_type_1" value="1" checked> 수급자 선택
            </label>
            <label class="radio-inline">
              <input type="radio" name="pen_type" id="pen_type_0" value="0"> 수급자 등록
            </label>
          </div>
          <div class="form-group">
            <label for="ms_pen_nm" class="col-sm-2 control-label">
              <strong>수급자명</strong>
            </label>
            <div class="col-sm-3 col-pen-nm" style="max-width: unset;">
              <img style="display: none;" src="<?php echo THEMA_URL; ?>/assets/img/icon_search.png" >
              <input type="hidden" name="ms_pen_id" id="ms_pen_id" value="<?=$ms['ms_pen_id'] ?: ''?>">
              <input type="text" name="ms_pen_nm" id="ms_pen_nm" class="form-control input-sm pen_id_flexdatalist" value="<?=$ms['ms_pen_nm'] ?: ''?>"  data-value="<?=$ms['ms_pen_nm'] ?: ''?>"  placeholder="수급자명"  onchange="pen_ch();">
              <span id="pen_id_flexdatalist_result" class="form_desc"></span>
            </div>
            <div class="col-sm-3 col-btn-pen">
              <button type="button" id="btn_pen">수급자 목록</button>
            </div>
          </div>
          <div class="form-group">
            <label for="ms_pen_hp" class="col-sm-2 control-label">
              <strong>휴대폰번호</strong>
            </label>
            <div class="col-sm-8">
              <div class="radio_wr">
                <label class="radio-inline">
                  <input type="radio" name="ms_pro_yn" id="ms_pro_yn_n" value="N" <?=option_array_checked($ms['ms_pro_yn'], ['', 'N'])?>> 수급자
                </label>
                <label class="radio-inline">
                  <input type="radio" name="ms_pro_yn" id="ms_pro_yn_y" value="Y" <?=option_array_checked($ms['ms_pro_yn'], ['Y'])?>> 보호자
                </label>
              </div>
              <select id="sel_pen_pro" style="display: none;"></select>
              <input type="text" maxlength="11" oninput="max_length_check(this)" name="ms_pen_hp" id="ms_pen_hp" class="form-control input-sm" value="<?=$ms['ms_pen_hp'] ?: ''?>" placeholder="휴대폰번호" data-value="<?=$ms['ms_pen_hp'] ?: ''?>" onchange="phone_ch();">
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
                  echo "https://".$_SERVER['HTTP_HOST'].'/shop/item_msg.php?url='.$ms['ms_url'].'&show_expected='.$show_expected;
                } else {
                  echo '상품이 추가되면 자동 생성됩니다.';
                }
                ?>
              </span>
              <button class="btn_im_copy" onclick="copy_to_clipboard('#ms_pen_url');" style="display: <?php if($ms['ms_url']) { echo 'inline-block;'; } else { echo 'none;'; } ?>">주소복사</button>
            </div>
          </div>
        </div>
        <div class="im_send_wr im_desc_wr" style="border: none;">
          <div class="im_send_button" style="<?php if($today_count <= 0) echo 'opacity: 50%;' ?>">
            <button type="submit" id="btn_im_send_alim" class="btn_im_send" style="display: block;">
              <img src="<?=THEMA_URL?>/assets/img/icon_kakao.png" alt="">
              알림 메시지 전달
            </button>
            <button type="submit" id="btn_im_send_sms" class="btn_im_send" style="display: block;">
              <img src="<?=THEMA_URL?>/assets/img/icon_email.png" width="40" height="40" alt="">
              문자 메시지 전달
            </button>
          </div>
          <div class="im_desc">
          	<p>
          	간편 제안서 무료발송 이벤트 진행중<br>
          	오늘의 무료 5건 중 <span><?=($today_count)?>건 남음</span></p>
            <!-- <p>보유 <strong><?=number_format($member['mb_point']);?></strong>포인트, 1회 전송 시 <strong>10</strong>포인트 차감</p> -->
          </div>
        </div>
      </div>

      <div id="im_body_wr" class="im_flex space-between <?php if($ms['ms_url']) echo 'active preview'; ?>">
        <div class="im_item_wr">
        <div class="im_tel_wr im_flex space-between">
            <div class="im_sch_hd">사업소 전화번호 공개</div>
            <input class="im_switch" id="ms_ent_tel" type="checkbox" name="ms_ent_tel" value="<?=get_text($member['mb_tel'])?>" checked="checked">
            <!-- <input class="im_switch" id="ms_ent_tel" type="checkbox" name="ms_ent_tel" value="<?=get_text($member['mb_tel'])?>" <?=get_checked($ms['ms_ent_tel'], get_text($member['mb_tel']))?>> -->
            <label for="ms_ent_tel">
              <div class="im_switch_slider">
                <span class="on">공개</span>
                <span class="off">숨김</span>
              </div>
            </label>
          </div>
          <div class="im_tel_wr">
            <div class="im_sch_hd">사업소 전화번호 선택</div>
            <input type="hidden" name="mb_tel" value="<?=get_text($member['mb_tel'])?>">
            <input type="hidden" name="mb_hp" value="<?=get_text($member['mb_hp'])?>">
            <input type="hidden" name="ms_ent_tel_new" value="<?=get_text($member['mb_tel'])?>">
            <div class="radio_wr">
              <label class="radio-inline">
                <input type="radio" name="im_tel_select_radio" id="im_tel_select_radio" value="0" checked="checked"> 일반전화
              </label>
              <label class="radio-inline">
                <input type="radio" name="im_tel_select_radio" id="im_tel_select_radio" value="1" > 휴대폰
              </label>
              <label class="radio-inline">
                <input type="radio" name="im_tel_select_radio" id="im_tel_select_radio" value="2" > 직접입력
              </label>
              <label class="radio-inline">
                <input type="text" maxlength="11" oninput="max_length_check(this)" name="ms_ent_tel_input" id="ms_ent_tel_input" class="form-control input-sm" value="" disabled>
              </label>
            </div>
          </div>
          <div class="im_sch_wr">
            <div class="im_flex space-between align-items">
              <div class="im_sch_hd">상품정보</div>
              <button class="btn_im_sel">품목찾기</button>
            </div>
            <div class="ipt_im_sch_wr">
              <img src="<?php echo THEMA_URL; ?>/assets/img/icon_search.png" >
              <input type="text" id="ipt_im_sch" class="ipt_im_sch" placeholder="여기에 추가할 상품명을 입력해주세요">
            </div>
            <div class="im_sch_pop">
              <p>상품을 검색한 후 추가해주세요.</p>
              <!-- <p>상품명을 모르시면 '상품검색' 버튼을 눌러주세요.</p>
              <p><button type="button" class="btn_im_sel">상품검색</button></p> -->
            </div>
          </div>
          
            <div class="no_item_info">
	        	<img src="<?=THEMA_URL?>/assets/img/icon_box.png" alt=""><br>
        		<p>상품을 검색한 후 추가해주세요.</p>
	        	<!-- <p class="txt_point">품목명을 모르시면 “품목찾기”버튼을 클릭해주세요.</p> -->
	        </div>
          <div class="im_list_hd">
            <span style="display: inline-flex;
                        min-height: 35px;
                        align-items: center;">
              추가 된 상품 목록
            </span>
            <span class="show_expected_switch" style="display:<?php echo ($show_expected == 'Y' ? 'block;' : 'none;')?>">
              <input class="im_switch " type="checkbox" name="show_expected_warehousing_date" id="show_expected_warehousing_date" <?php echo ($show_expected == 'Y' ? 'checked' : '')?>>입고예정일 알림표시
              <label for="show_expected_warehousing_date">
                <div class="im_switch_slider">
                  <span class="on">공개</span>
                  <span class="off">숨김</span>
                </div>
              </label>
            </span>
            <!-- <label style="text-align:right; width:78%; display:none;" for="show_expected_warehousing_date" id="show_expected_warehousing_date_label">
              <p>입고예정일 알림표시</p>
              <div class="im_switch_slider">
                <span class="on">공개</span>
                <span class="off">숨김</span>
              </div>
              <input type="checkbox" name="show_expected_warehousing_date" id="show_expected_warehousing_date" <?php echo ($show_expected == 'Y' ? 'checked' : '')?>> 입고예정일 알림표시
            </label> -->
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
                <?php if ($show_expected == 'Y') { ?>
                  <p style="display:block;" class="it_expected_warehousing_date"><?=$item['it_expected_warehousing_date']?></p>
                <?php } ?>
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
          <!--<button type="button" id="btn_im_save" onclick="save_item_msg();">저장</button>-->
          <?php if($recs) { ?>
          <div class="im_rec_wr">
            <div class="im_rec_hd im_flex space-between">
              <div class="im_sch_hd">추천정보</div>
              <div class="im_rec_desc">선택한 정보는 전송 시 함께 전달됩니다.</div>
            </div>
            <ul class="im_rec_list">
              <?php foreach($recs as $rec) { ?>
              <li>
                <div class="im_rec_desc">
                  <?=$rec['wr_subject']?>
                </div>
                <input class="im_switch" id="ms_rec_<?=$rec['wr_id']?>" type="checkbox" name="ms_rec[]" value="<?=$rec['wr_id']?>" <?=option_array_checked($rec['wr_id'], $ms['ms_rec'])?>>
                <label for="ms_rec_<?=$rec['wr_id']?>">
                  <div class="im_switch_slider">
                    <span class="on">선택</span>
                    <span class="off">미선택</span>
                  </div>
                </label>
              </li>
              <?php } ?>
            </ul>
          </div>
          <?php } ?>
        </div>
        <div class="im_preview_wr">
          <div class="im_preview_hd">
            수급자에게 전송되는 화면 미리보기
          </div>
          <div id="im_preview" class="im_preview">
            <?php if($ms['ms_url']) { ?>
            <iframe src="item_msg.php?preview=1&url=<?=$ms['ms_url']?>&show_expected=<?=$show_expected?>" frameborder="0" data-ms-url="<?=$ms['ms_url']?>"></iframe>
            <?php } else { ?>
            <div class="empty">상품이 추가되면 자동 생성됩니다.</div>
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
$(function(){

  $('#show_expected_warehousing_date').on('change', function() {
    var ms_url = $('#im_preview iframe').attr('data-ms-url');
    var url = 'item_msg.php?preview=1&url=' + ms_url;
    if ($(this).is(':checked')) {
      $('.it_expected_warehousing_date').show();
      url = url + "&show_expected=Y";
    }
    else {
      $('.it_expected_warehousing_date').hide();
      url = url + "&show_expected=N";
    }
    // $('#im_preview iframe').attr('src', url);
    save_item_msg();
  });
});

//휴대폰번호 변경 체크
function phone_ch(){
	if($("#ms_pen_hp").val() != $("#ms_pen_hp").data("value") && $("#ms_id").val() != ""){
		save_item_msg();
	}
}

//수급자정보 변경 체크
function pen_ch(){
	if($("#ms_pen_nm").val() != $("#ms_pen_nm").data("value") && $("#ms_id").val() != ""){
		if($("#ms_pen_nm").val() != ""){
			save_item_msg();
		}
	}
}
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
    $('.im_list_hd').hide();
    $('.btn_im_send').removeClass('active');
  } else {
    $('.no_item_info').hide();
    $('.im_list_hd').show();
    if($('#ms_pen_nm').val() && $('#ms_pen_hp').val())
      $('.btn_im_send').addClass('active');
  }
}

// 품목 선택
function select_item(obj) {
  $('body').removeClass('modal-open');
  $('#popup_box').hide();

  if (obj.it_expected_warehousing_date) {
    // window.location.href += "&show_expected=Y";
    $('.show_expected_switch').show();
  }

  // it_id
  var $li = $('<li>');
  $li.append('<input type="hidden" name="it_id[]" value="' + obj.it_id + '">');
  $li.append('<input type="hidden" name="it_name[]" value="' + obj.it_name + '">');
  $li.append('<input type="hidden" name="gubun[]" value="' + obj.gubun + '">');
  $li.append('<img class="it_img" src="/data/item/' + obj.it_img + '" onerror="this.src=\'/img/no_img.png\';">');
  $('<div class="it_info">')
    .append(
      '<p class="it_expected_warehousing_date">' + obj.it_expected_warehousing_date + '</p>',
      '<p class="it_name">' + obj.it_name + ' (' + obj.gubun + ')' + '</p>',
      '<p class="it_price">급여가 : ' + parseInt(obj.it_cust_price).toLocaleString('en-US') + '원</p>'
    )
    .appendTo($li);
  $li.append('<button type="button" class="btn_del_item">삭제</button>');
  $li.appendTo('#im_write_list');
  $('#ipt_im_sch').val('').next().focus();

  check_no_item();
  save_item_msg();
}

// 저장
var loading = false;
function save_item_msg(no_items) {
  if(loading)
    return;
  if($("#ms_id").val() == $("#ms_id2").val() && $("#ms_id2").val() != ""){
	$("#ms_id").val("");
	$("#w").val("");
  }
  var pen_type = $('input[name="pen_type"]:checked').val();
  var show_expected = ($('#show_expected_warehousing_date').is(':checked') ? 'Y' : 'N');

  if(pen_type == '1') {
    // 기존 수급자 검색일 경우
    if($('.pen_id_flexdatalist').val() !== $('.pen_id_flexdatalist').next().val())
      $('.pen_id_flexdatalist').val($('.pen_id_flexdatalist').next().val());
  }

  loading = true;
  $form = $('#form_item_msg');
  var query = $form.serialize();
  query += '&show_expected=' + show_expected;
  if(no_items)
    query += '&no_items=1';
  $.post($form.attr('action'), query, 'json')
  .done(function(result) {
    var data = result.data;
    var ms_url = 'item_msg.php?preview=1&url=' + data.ms_url + '&show_expected=' + show_expected;
    $('input[name="w"]').val('u');
    $('input[name="ms_id"]').val(data.ms_id);
    $('#ms_pen_url').text('https://<?=$_SERVER['HTTP_HOST']?>/shop/' + ms_url);
    $('.btn_im_copy').show();
    $('#im_preview').empty().append($('<iframe>').attr('src', ms_url).attr('frameborder', 0).attr('data-ms-url', data.ms_url));
    $('#im_body_wr').addClass('preview');
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  })
  .always(function() {
    loading = false;
  });
}


  <?php if(isset($pen) && $pen) { ?>
  var pen = <?=json_encode($pen)?>;
  update_pen_info();
  $('#pen_type_1').prop('checked', true);
  <?php } else { ?>
  var pen = null;
  <?php if(isset($ms)) { ?>
  $('#pen_type_0').prop('checked', true);
  <?php
    }
  }
  ?>
  var pen_id_flexdata = null;

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
    else if(pen.penRecGraNm == '')
      postfix.push( (pen.penRecGraCd).replace('0','')+"등급" ); // 아직 6등급은 penRecGraNm이 따로 저장되지 않음

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
    $('#ms_pen_nm').val(pen.penNm);

    // 보호자 정보 가져오기
    $.post('ajax.recipient.get_pros.php', {
      penId: pen.penId
    }, 'json')
    .done(function(result) {
      var data = result.data;
      
      $('#sel_pen_pro').empty();
      $(data).each(function(idx, pro) {
        var selected = '';
        if(pro.pro_hp && $('#ms_pen_hp').val().replace(/[^0-9]/g, '') == pro.pro_hp.replace(/[^0-9]/g, '')) {
         selected = 'selected="selected"'; 
        }

        $('#sel_pen_pro').append(          
          '<option value="' + pro.pro_hp + '" ' + selected + '>' + pro.pro_name + '</option>'
        );
      });

      if(data.length && $('input[name="ms_pro_yn"]:checked').val() == 'Y') {
        $('#sel_pen_pro').show();
      }
    });
  }

  // 신규수급자 or 기존수급자 선택
  function check_pen_type() {
    var pen_type = $('input[name="pen_type"]:checked').val();

    if(pen_type == '1') {
      // 기존수급자
      $('.pen_id_flexdatalist').addClass('active').attr('placeholder', '수급자명 검색');
      $('.col-pen-nm img').show();
      $("#btn_pen").show();
      toggle_pen_id_flexdatalist(true);
      $('input[name="ms_pro_yn"]').closest('.radio_wr').show();
      $('#ms_pen_nm').next().focus();
    } else {
      // 신규수급자
      $('.pen_id_flexdatalist').removeClass('active').attr('placeholder', '수급자명');;
      $('.col-pen-nm img').hide();
      $("#btn_pen").hide();
      toggle_pen_id_flexdatalist(false);
      $('input[name="ms_pro_yn"]').closest('.radio_wr').hide();
      $('#ms_pen_nm').focus();
    }
  }
  $('input[name="pen_type"]').change(function() {
    pen = null;
    update_pen_info();
    check_pen_type();
  });

  // 수급자 목록
  $('#btn_pen').click(function() {
    var url = 'pop_recipient.php';

    $('#popup_box iframe').attr('src', url);
    $('body').addClass('modal-open');
    $('#popup_box').show();
  });
  function selected_recipient(result) {

    $('body').removeClass('modal-open');
    $('#popup_box').hide();

    result = result.split('|');

    var pen = {
      penId: result[1],
      penNm: result[3],
      penLtmNum: result[4].substring(0, 6) + '*****',
      penConNum: result[20],
      penRecGraCd: result[5],
      penRecGraNm: result[6],
      penTypeCd: result[7],
      penTypeNm: result[8],
      penBirth: result[15],
      penGender: result[13],
    };

    select_pen(pen);
  }
  function select_pen(obj) {
    pen = obj;

    update_pen_info();
    setPenHp('N');

    $('#im_body_wr').addClass('active');
    // 처음 팝업
    $('.im_sch_pop').show();
    $('#ipt_im_sch').next().focus();
  }
  function toggle_pen_id_flexdatalist(on) {
    if(on) {
      if(pen_id_flexdata) return;
      pen_id_flexdata = $('.pen_id_flexdatalist').flexdatalist({
        minLength: 1,
        url: 'ajax.get_pen_id.php',
        cache: true, // cache
        searchContain: true, // %검색어%
        showResultsOnEnter: true,
        noResultsText: '"{keyword}"으로 등록된 수급자가 없습니다. 수급자정보를 직접 입력 하시고 제안서 작성 시 자동으로 등록됩니다.',
        visibleCallback: function($li, item, options) {
          var $item = {};
          $item = $('<span>')
            .html(item.penNm);

          $item.appendTo($li);

          $item = $('<span>')
            .html(" (" + ( item.penAge > 0 ? item.penAge + '/' : '' ) + ( item.penGender ? item.penGender + '/' : '' ) + ( item.penLtmNum ? item.penLtmNum : '' ) + ")");

          $item.appendTo($li);

          return $li;
        },
        searchIn: ["penNm"],
        focusFirstResult: true,
      })
      .on("select:flexdatalist", function(event, obj, options) {
        select_pen(obj);
      });
    } else {
      if(!pen_id_flexdata) return;
      pen_id_flexdata.flexdatalist('destroy');
      pen_id_flexdata = null;
    }
  }

  $('input[name="ms_pro_yn"]').click(function() {
    setPenHp($(this).val());
  });

  function setPenHp(proYN) {
    $('#sel_pen_pro').hide();

    if(proYN === 'Y') {
      $('#ms_pro_yn_y').prop('checked', true);
      $('#ms_pro_yn_n').prop('checked', false);

      if(pen) {
        $('#ms_pen_hp').val(pen.penProConNum);
        if($('#sel_pen_pro option').length > 0)
          $('#sel_pen_pro').show();
      }
    } else {
      $('#ms_pro_yn_y').prop('checked', false);
      $('#ms_pro_yn_n').prop('checked', true);

      if(pen)
        $('#ms_pen_hp').val(pen.penConNum);
    }
  }

  $('input[name="im_tel_select_radio"]').click(function() {
    if(loading) return false;

    var ms_id = $('input[name="ms_id"]').val();

    if(!ms_id) {
      alert('먼저 상품을 추가해주세요.');
      return false;
    }

    $('input[name="ms_ent_tel_input"]').prop('disabled', true);
    if ($(this).val() == 0) {
      $('input[name="ms_ent_tel_new"]').val($('input[name="mb_tel"]').val());
    }
    else if ($(this).val() == 1) {
      $('input[name="ms_ent_tel_new"]').val($('input[name="mb_hp"]').val());
    }
    else if ($(this).val() == 2) {
      $('input[name="ms_ent_tel_input"]').prop('disabled', false);
      $('input[name="ms_ent_tel_new"]').val($('#ms_ent_tel_input').val());
    }
    save_item_msg(true);

  });

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
        .html("[" + item.gubun + "] " + item.it_name + " (" + number_format(item.it_cust_price) + "원)");

      $item.appendTo($li);
      return $li;
    },
  }).on("select:flexdatalist", function(event, obj, options) {
    select_item(obj);
  });

  $(document).on('click', '.btn_del_item', function() {
    $(this).closest('li').remove();
    check_no_item();
    save_item_msg();
  });

  var sending = false;
  $('#btn_im_send_alim').on('click', function() {
    if(sending)
      return alert('전송 중입니다. 잠시만 기다려주세요.');
    
    var ms_id = $('input[name="ms_id"]').val();

    if(!ms_id)
      return alert('먼저 상품을 추가해주세요.');
	if($("#agreement").val() != "ok"){//미동의 시
		$('body').addClass('modal-open');
		$(".thkc_pop_confirm").css("display","flex");
		$("#send_type").val("btn_im_send_alim");
	}else{//동의 시
		sending = true;
		var show_expected = ($('#show_expected_warehousing_date').is(':checked') ? 'Y' : 'N');
		$form = $('#form_item_msg');
		$.post('item_msg_send.php', {
		  mode: 'alim',
		  ms_id: ms_id,
		  show_expected: show_expected
		}, 'json')
		.done(function(result) {
		  alert('전송이 완료되었습니다.');
		  window.location.href = 'item_msg_write.php?w=u&ms_id=' + ms_id + '&show_expected=' + show_expected;
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  alert(data && data.message);
		})
		.always(function() {
		  sending = false;
		});
	}
  });
  $('#btn_im_send_sms').on('click', function() {
    if(sending)
      return alert('전송 중입니다. 잠시만 기다려주세요.');
    
    var ms_id = $('input[name="ms_id"]').val();

    if(!ms_id)
      return alert('먼저 상품을 추가해주세요.');
	if($("#agreement").val() != "ok"){//미동의 시
		$('body').addClass('modal-open');
		$(".thkc_pop_confirm").css("display","flex");
		$("#send_type").val("btn_im_send_sms");
	}else{//동의 시
		sending = true;
		var show_expected = ($('#show_expected_warehousing_date').is(':checked') ? 'Y' : 'N');
		$form = $('#form_item_msg');
		$.post('item_msg_send.php', {
		  mode: 'sms',
		  ms_id: ms_id,
		  show_expected: show_expected
		}, 'json')
		.done(function(result) {
		  alert('전송이 완료되었습니다.');
		  window.location.href = 'item_msg_write.php?w=u&ms_id=' + ms_id + '&show_expected=' + show_expected;
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  alert(data && data.message);
		})
		.always(function() {
		  sending = false;
		});
	}
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
    if (this.id == 'show_expected_warehousing_date') return;
    if(loading) return false;

    var ms_id = $('input[name="ms_id"]').val();

    if(!ms_id) {
      alert('먼저 상품을 추가해주세요.');
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

  // 핸드폰 번호 입력창 선택시 - 지우기
  $('#ms_pen_hp').on('focus', function() {
    var $this = $(this);
    var ms_pen_hp = $(this).val();
    $(this).val(ms_pen_hp.replace(/-/g, ''));
  });

  // 핸드폰 번호 입력값체크 11자리 되면 번호에 - 넣고 상품입력창 보여주기
  $('#ms_pen_hp').on('change paste keyup input', function() {
    var $this = $(this);
    var ms_pen_hp = $(this).val();
    $(this).val(ms_pen_hp.replace(/-/g, ''));
    ms_pen_hp = $(this).val();

    if (ms_pen_hp.length > 10) {
      check_pen_input(ms_pen_hp);
    }
  });

  // 핸드폰 번호 입력창 포커스 아웃시 10자리 되면 번호에 - 넣고 상품입력창 보여주기
  $('#ms_pen_hp').on('blur', function() {
    var $this = $(this);
    var ms_pen_hp = $(this).val();
    $(this).val(ms_pen_hp.replace(/-/g, ''));
    ms_pen_hp = $(this).val();

    if (ms_pen_hp.length > 9) {
      check_pen_input(ms_pen_hp);
    }
  });

  $('#ms_ent_tel_input').on('blur', function() {
    var num = $(this).val();
    $('input[name="ms_ent_tel_new"]').val(num);
    
    if (num.length > 8)
      save_item_msg(true);
  });

  function check_pen_input(ms_pen_hp) {
    var hp_pattern = /01[016789]-[^0][0-9]{2,3}-[0-9]{3,4}/;
    ms_pen_hp = ms_pen_hp.replace(/[^0-9]/g, '').replace(/(^02.{0}|^01.{1}|[0-9]{3})([0-9]+)([0-9]{4})/, "$1-$2-$3");
    $('#ms_pen_hp').val(ms_pen_hp);

    if(hp_pattern.test(ms_pen_hp)) {
      $('#im_body_wr').addClass('active');
      // 처음 팝업
      $('.im_sch_pop').show();
      $('#ipt_im_sch').next().focus();
      check_no_item();
    }
  }


  $('#sel_pen_pro').change(function() {
    var hp = $(this).val();
    
    $('#ms_pen_hp').val(hp).change().blur();
  });

  check_no_item();
  check_pen_type();
  //setPenHp($('input[name="ms_pro_yn"]:checked').val());

  $(document).on("keydown", "form", function(event) { 
    return event.key != "Enter";
  });
</script>

<?php include_once("./_tail.php"); ?>
