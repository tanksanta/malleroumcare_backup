<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default' || !$member['mb_entId'])
  alert('사업소 회원만 접근할 수 있습니다.');

$g5['title'] = '계약서 작성';
include_once("./_head.php");

$dc_id = clean_xss_tags($_GET['dc_id']);
if($dc_id) {
  $dc = sql_fetch("
  SELECT HEX(`dc_id`) as uuid, e.*
  FROM `eform_document` as e
  WHERE dc_id = UNHEX('$dc_id') and entId = '{$member['mb_entId']}' and dc_status = '11' ");

  if(!$dc['uuid'])
    unset($dc);
}

// 이전에 저장했던 간편계약서 삭제
$sql = "
  select hex(dc_id) as uuid
  from eform_document
  where dc_status = '10' and entId = '{$member['mb_entId']}'
";
$result = sql_query($sql);
while($row = sql_fetch_array($result)) {
  $dc_id = $row['uuid'];

  $sql = " DELETE FROM eform_document_item WHERE dc_id = UNHEX('$dc_id') ";
  sql_query($sql);
  $sql = " DELETE FROM eform_document_log WHERE dc_id = UNHEX('$dc_id') ";
  sql_query($sql);
  $sql = " DELETE FROM eform_document WHERE dc_id = UNHEX('$dc_id') ";
  sql_query($sql);
}

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/simple_efrom.css">');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">');
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js"></script>');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>

<section class="wrap">
  <div class="sub_section_tit">
    간편 계약서 작성
    <div class="r_btn_area">
        <a href="/shop/electronic_manage.php" class="btn eroumcare_btn2">계약서 목록보기</a>
    </div>
  </div>
  <div class="inner">

    <form id="form_simple_eform" method="POST" class="form-horizontal" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="w" value="<?php if($dc) echo 'u'; ?>">
      <input type="hidden" name="dc_id" value="<?php if($dc) echo $dc['uuid']; ?>">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <label for="penNm" class="col-md-2 control-label">
              <strong>수급자명</strong>
            </label>
            <div class="col-md-3" style="max-width: unset;">
              <input type="hidden" name="penId" id="penId" value="<?php if($dc) echo $dc['penId']; ?>">
              <input type="text" name="penNm" id="penNm" class="form-control input-sm pen_id_flexdatalist" value="<?php if($dc) echo $dc['penNm']; ?>" placeholder="수급자명">
            </div>
          </div>
          <div class="form-group">
            <label for="penLtmNum" class="col-md-2 control-label">
              <strong>요양인정번호</strong>
            </label>
            <div class="col-md-3">
              <input type="text" name="penLtmNum" id="penLtmNum" class="form-control input-sm" value="<?php if($dc) echo $dc['penLtmNum']; ?>" placeholder="L**********">
            </div>
            <label for="penGender" class="col-md-2 control-label">
              <strong>휴대폰번호</strong>
            </label>
            <div class="col-md-3">
              <input type="text" name="penConNum" id="penConNum" class="form-control input-sm" value="<?php if($dc) echo $dc['penConNum']; ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="penRecGraCd" class="col-md-2 control-label">
              <strong>인정등급</strong>
            </label>
            <div class="col-md-3">
              <select name="penRecGraCd" id="penRecGraCd" class="form-control input-sm">
                <option value="00" <?php if($dc) echo get_selected($dc['penRecGraCd'], '00'); ?>>등급외</option>
                <option value="01" <?php if($dc) echo get_selected($dc['penRecGraCd'], '01'); ?>>1등급</option>
                <option value="02" <?php if($dc) echo get_selected($dc['penRecGraCd'], '02'); ?>>2등급</option>
                <option value="03" <?php if($dc) echo get_selected($dc['penRecGraCd'], '03'); ?>>3등급</option>
                <option value="04" <?php if($dc) echo get_selected($dc['penRecGraCd'], '04'); ?>>4등급</option>
                <option value="05" <?php if($dc) echo get_selected($dc['penRecGraCd'], '05'); ?>>5등급</option>
              </select>
            </div>
            <label for="penTypeCd" class="col-md-2 control-label">
              <strong>본인부담금율</strong>
            </label>
            <div class="col-md-3">
              <select name="penTypeCd" id="penTypeCd" class="form-control input-sm">
                <option value="00" <?php if($dc) echo get_selected($dc['penTypeCd'], '00'); ?>>일반 15%</option>
                <option value="01" <?php if($dc) echo get_selected($dc['penTypeCd'], '01'); ?>>감경 9%</option>
                <option value="02" <?php if($dc) echo get_selected($dc['penTypeCd'], '02'); ?>>감경 6%</option>
                <option value="03" <?php if($dc) echo get_selected($dc['penTypeCd'], '03'); ?>>의료 6%</option>
                <option value="04 <?php if($dc) echo get_selected($dc['penTypeCd'], '04'); ?>">기초 0%</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="penBirth" class="col-md-2 control-label">
              <strong>생년월일</strong>
            </label>
            <div class="col-md-3">
              <input type="text" name="penBirth" id="penBirth" class="birthpicker form-control input-sm" value="<?php if($dc) echo $dc['penBirth']; ?>">
            </div>
            <label for="penExpiStDtm" class="col-md-2 control-label">
              <strong>유효기간</strong>
            </label>
            <div class="col-md-5">
              <input type="text" name="penExpiStDtm" id="penExpiStDtm" class="datepicker form-control input-sm" value="<?php if($dc) echo explode(' ~ ', $dc['penExpiDtm'])[0]; ?>"> ~ <input type="text" name="penExpiEdDtm" id="penExpiEdDtm" class="datepicker form-control input-sm" value="<?php if($dc) echo explode(' ~ ', $dc['penExpiDtm'])[1]; ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="penJumin" class="col-md-2 control-label">
              <strong>주민번호(앞자리)</strong>
            </label>
            <div class="col-md-3">
              <input type="text" name="penJumin" id="penJumin" class="form-control input-sm" value="<?php if($dc) echo $dc['penJumin']; ?>">
            </div>
          </div>
        </div>
        <div class="se_btn_wr">
          <button type="submit" id="btn_se_submit" class="btn_se_submit">
            <img src="<?=THEMA_URL?>/assets/img/icon_contract.png" alt="">
            계약서 생성
          </button>
        </div>
      </div>

      <div class="flex space-between">
        <div class="se_item_wr">
          <div class="se_sch_wr flex align-items">
            <div class="se_sch_hd">품목 목록</div>
            <input type="text" id="ipt_se_sch" class="ipt_se_sch" placeholder="품목명 검색 후 추가하세요.">
            <button id="btn_se_sch" class="btn_se_sch">품목찾기</button>
          </div>
          
            <div class="no_item_info">
	        	<img src="<?=THEMA_URL?>/assets/img/icon_box.png" alt=""><br>
	        	<p>수급자 계약서에 필요한 품목을 검색한 후 추가하시면<br>추가된 품목으로 계약서가 생성됩니다.</p>
	        	<p class="txt_point">품목명을 모르시면 “품목찾기”버튼을 클릭해주세요.</p>
	        </div>
          
          <div class="se_item_hd">판매품목</div>
          <ul id="buy_list" class="se_item_list">
            <?php
            if($dc) {
              $sql = "
                SELECT
                 i.*,
                 x.it_img1 as it_img,
                 x.it_id as id,
                 count(*) as qty
                FROM
                  eform_document_item i
                LEFT JOIN
                  g5_shop_item x ON i.it_code = x.ProdPayCode and
                  (
                    ( i.gubun = '00' and x.ca_id like '10%' ) or
                    ( i.gubun = '01' and x.ca_id like '20%' )
                  )
                WHERE
                  i.gubun = '00' and
                  dc_id = UNHEX('$dc_id')
                GROUP BY
                  i.it_code
                ORDER BY
                  i.it_id ASC
              ";

              $result = sql_query($sql, true);

              while($row = sql_fetch_array($result)) {
                $sql = "
                  SELECT it_barcode
                  FROM eform_document_item
                  WHERE
                    gubun = '00' and
                    dc_id = UNHEX('$dc_id') and
                    it_code = '{$row['it_code']}'
                  ORDER BY
                    it_id ASC
                ";

                $result_barcode = sql_query($sql);
                $barcodes = [];
                while($barcode = sql_fetch_array($result_barcode)) {
                  $barcodes[] = $barcode['it_barcode'];
                }
            ?>
            <li>
              <input type="hidden" name="it_id[]" value="<?=$row['id']?>">
              <input type="hidden" name="it_gubun[]" value="판매">
              <div class="it_info">
                <img class="it_img" src="/data/item/<?=$row['it_img']?>" onerror="this.src='/img/no_img.png';">
                <p class="it_cate"><?=$row['ca_name']?></p>
                <p class="it_name"><?=$row['it_name']?> (판매)</p>
                <p class="it_price">급여가 : <?=number_format($row['it_price'])?>원</p>
              </div>
              <div class="it_btn_wr flex align-items space-between">
                <div class="it_qty">
                  <div class="input-group">
                    <div class="input-group-btn">
                      <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span class="sound_only">감소</span></button>
                    </div>
                    <input type="text" name="it_qty[]" value="<?=$row['qty']?>" class="form-control input-sm">
                    <div class="input-group-btn">
                      <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus"></i><span class="sound_only">증가</span></button>
                    </div>
                  </div>
                </div>
                <button type="button" class="btn_del_item">삭제</button>
              </div>
              <div class="it_ipt_wr">
                <div class="flex">
                  <div class="it_ipt_hd">판매계약일</div>
                  <div class="it_ipt">
                    <input type="text" name="it_date[]" class="datepicker inline" value="<?=$row['it_date']?>">
                  </div>
                </div>
                <div class="flex">
                  <div class="it_ipt_hd">바코드</div>
                  <input type="hidden" name="it_barcode[]">
                  <div class="it_barcode_wr it_ipt">
                    <?php
                    for($x = 0; $x < $row['qty']; $x++) {
                      $barcode = $barcodes[$x];
                      echo '<input type="text" class="it_barcode" maxlength="12" oninput="max_length_check(this)" placeholder="12자리 숫자를 입력하세요." value="' . $barcode . '">';
                    }
                    ?>
                  </div>
                </div>
              </div>
            </li>
            <?php
              }
            }
            ?>
          </ul>
          <div class="se_item_hd">대여품목</div>
          <ul id="rent_list" class="se_item_list">
          <?php
            if($dc) {
              $sql = "
                SELECT
                 i.*,
                 x.it_img1 as it_img,
                 x.it_id as id,
                 x.it_rental_price,
                 count(*) as qty
                FROM
                  eform_document_item i
                LEFT JOIN
                  g5_shop_item x ON i.it_code = x.ProdPayCode and
                  (
                    ( i.gubun = '00' and x.ca_id like '10%' ) or
                    ( i.gubun = '01' and x.ca_id like '20%' )
                  )
                WHERE
                  i.gubun = '01' and
                  dc_id = UNHEX('$dc_id')
                GROUP BY
                  i.it_code
                ORDER BY
                  i.it_id ASC
              ";

              $result = sql_query($sql, true);

              while($row = sql_fetch_array($result)) {
                $sql = "
                  SELECT it_barcode
                  FROM eform_document_item
                  WHERE
                    gubun = '01' and
                    dc_id = UNHEX('$dc_id') and
                    it_code = '{$row['it_code']}'
                  ORDER BY
                    it_id ASC
                ";

                $result_barcode = sql_query($sql);
                $barcodes = [];
                while($barcode = sql_fetch_array($result_barcode)) {
                  $barcodes[] = $barcode['it_barcode'];
                }
            ?>
            <li>
              <input type="hidden" name="it_id[]" value="<?=$row['id']?>">
              <input type="hidden" name="it_gubun[]" value="대여">
              <div class="it_info">
                <img class="it_img" src="/data/item/<?=$row['it_img']?>" onerror="this.src='/img/no_img.png';">
                <p class="it_cate"><?=$row['ca_name']?></p>
                <p class="it_name"><?=$row['it_name']?> (대여)</p>
                <p class="it_price">월 대여가 : <?=number_format($row['it_rental_price'])?>원</p>
              </div>
              <div class="it_btn_wr flex align-items space-between">
                <div class="it_qty">
                  <div class="input-group">
                    <div class="input-group-btn">
                      <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span class="sound_only">감소</span></button>
                    </div>
                    <input type="text" name="it_qty[]" value="<?=$row['qty']?>" class="form-control input-sm">
                    <div class="input-group-btn">
                      <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus"></i><span class="sound_only">증가</span></button>
                    </div>
                  </div>
                </div>
                <button type="button" class="btn_del_item">삭제</button>
              </div>
              <div class="it_ipt_wr">
                <div class="flex">
                  <div class="it_ipt_hd">계약기간</div>
                  <div class="it_date_wr it_ipt">
                    <input type="hidden" name="it_date[]">
                    <?php
                    $str_date = substr($row['it_date'], 0, 10);
                    $end_date = substr($row['it_date'], 11, 10);
                    ?>
                    <input type="text" class="datepicker inline" data-range="from" value="<?=$str_date?>"> ~ <input type="text" class="datepicker inline" data-range="to" value="<?=$end_date?>">
                    <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 6)">6개월</button>
                    <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 12)">1년</button>
                    <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 24)">2년</button>
                  </div>
                </div>
                <div class="flex">
                  <div class="it_ipt_hd">바코드</div>
                  <input type="hidden" name="it_barcode[]">
                  <div class="it_barcode_wr it_ipt">
                    <?php
                    for($x = 0; $x < $row['qty']; $x++) {
                      $barcode = $barcodes[$x];
                      echo '<input type="text" class="it_barcode" maxlength="12" oninput="max_length_check(this)" placeholder="12자리 숫자를 입력하세요." value="' . $barcode . '">';
                    }
                    ?>
                  </div>
                </div>
              </div>
            </li>
            <?php
              }
            }
            ?>
          </ul>
          <div class="se_conacc">
            <p>특약사항1</p>
            <textarea name="entConAcc01"><?php if($dc) echo $dc['entConAcc01']; else echo $member['mb_entConAcc01']; ?></textarea>
          </div>
          <div class="se_conacc">
            <p>특약사항2</p>
            <textarea name="entConAcc02"><?php if($dc) echo $dc['entConAcc02']; else echo $member['mb_entConAcc02']; ?></textarea>
          </div>
          <button type="button" id="btn_se_save" onclick="save_eform();">저장</button>
        </div>
        <div class="se_preview_wr">
          <div class="se_preview_hd">
            수급자와 작성할 계약서 미리보기
          </div>
          <div id="se_preview" class="se_preview">
            <?php if($dc) { ?>
            <iframe src="/shop/eform/renderEform.php?preview=1&dc_id=<?=$dc['uuid']?>" frameborder="0"></iframe>
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
// 품목 없는지 체크
function check_no_item() {
  var total = 0;
  $('.se_item_list').each(function() {
    var selected = $(this).find('li').length;
    if(selected == 0) {
      $(this).prev('.se_item_hd').hide();
    } else {
      $(this).prev('.se_item_hd').show();
    }
    total += selected;
  });

  if(total == 0) {
    $('.no_item_info').show();
  } else {
    $('.no_item_info').hide();
  }
}

// 품목 선택
function select_item(obj) {

  $('body').removeClass('modal-open');
  $('#popup_box').hide();

  var $li = $('<li>')
    .append('<input type="hidden" name="it_id[]" value="' + obj.it_id + '">')
    .append('<input type="hidden" name="it_gubun[]" value="' + obj.gubun + '">');
  
  var $it_info = $('<div class="it_info">')
    .append(
      '<img class="it_img" src="/data/item/' + obj.it_img + '" onerror="this.src=\'/img/no_img.png\';">',
      '<p class="it_cate">' + obj.ca_name + '</p>',
      '<p class="it_name">' + obj.it_name + ' (' + obj.gubun + ')' + '</p>'
      );
  if(obj.gubun == '대여') {
    $it_info.append('<p class="it_price">월 대여가 : ' + parseInt(obj.it_rental_price).toLocaleString('en-US') + '원</p>'); 
  } else {
    $it_info.append('<p class="it_price">급여가 : ' + parseInt(obj.it_cust_price).toLocaleString('en-US') + '원</p>'); 
  }
  $li.append($it_info);

  $li.append('\
    <div class="it_btn_wr flex align-items space-between">\
      <div class="it_qty">\
        <div class="input-group">\
        <div class="input-group-btn">\
          <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span class="sound_only">감소</span></button>\
        </div>\
        <input type="text" name="it_qty[]" value="1" class="form-control input-sm">\
        <div class="input-group-btn">\
          <button type="button" class="it_qty_plus btn btn-lightgray btn-sm"><i class="fa fa-plus"></i><span class="sound_only">증가</span></button>\
        </div>\
        </div>\
      </div>\
      <button type="button" class="btn_del_item">삭제</button>\
    </div>\
  ');

  var $it_ipt = $('<div class="it_ipt_wr">');
  if(obj.gubun == '대여') {
    var id = obj.it_id + Date.now();
    $it_ipt.append('\
      <div class="flex">\
        <div class="it_ipt_hd">계약기간</div>\
        <div class="it_date_wr it_ipt">\
          <input type="hidden" name="it_date[]">\
          <input type="text" class="datepicker inline" data-range="from"> ~ <input type="text" class="datepicker inline" data-range="to">\
          <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 6)">6개월</button>\
          <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 12)">1년</button>\
          <button type="button" class="btn_rent_date" onclick="set_rent_date($(this).parent(), 24)">2년</button>\
        </div>\
      </div>\
    ');

    set_rent_date($it_ipt, 6);
  } else {
    $it_ipt.append('\
      <div class="flex">\
        <div class="it_ipt_hd">판매계약일</div>\
        <div class="it_ipt">\
        <input type="text" name="it_date[]" class="datepicker inline" value="' + format_date(new Date()) + '">\
        </div>\
      </div>\
    ');
  }
  $it_ipt.find('.datepicker').datepicker({ changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd' });
  $it_ipt.append('\
    <div class="flex">\
        <div class="it_ipt_hd">바코드</div>\
        <input type="hidden" name="it_barcode[]">\
        <div class="it_barcode_wr it_ipt">\
        <input type="text" class="it_barcode" maxlength="12" oninput="max_length_check(this)" placeholder="12자리 숫자를 입력하세요.">\
        <p>바코드 미입력 시 계약서 작성 후 이로움에 주문이 가능합니다.</p>\
        </div>\
    </div>\
  ');
  $li.append($it_ipt);

  if(obj.gubun == '대여') {
    $('#rent_list').append($li);
  } else {
    $('#buy_list').append($li);
  }

  $('#ipt_se_sch').val('').next().focus();

  check_no_item();
}

// 바코드 최대길이 체크
function max_length_check(object){
  object.value = object.value.replace(/[^0-9]/g,'');
  if (object.value.length > object.maxLength) {
    object.value = object.value.slice(0, object.maxLength);
  }
}

function format_date(date) {
  var year = date.getFullYear();
  var month = date.getMonth() + 1;
  var day = date.getDate();

  month = (month < 10) ? "0" + month : month;
  day = (day < 10) ? "0" + day : day;

  return year + "-" + month + "-" + day;
}

function set_rent_date($parent, months) {
  var date = new Date();
  var from = format_date(date);

  date.setMonth(date.getMonth() + months);
  date.setDate(date.getDate() - 1);

  var to = format_date(date);

  $parent.find('input[data-range="from"]').val(from);
  $parent.find('input[data-range="to"]').val(to);
}

// 계약서 저장
var loading = false;
function save_eform() {
  if(loading) return;

  if($('.pen_id_flexdatalist').val() !== $('.pen_id_flexdatalist').next().val())
    $('.pen_id_flexdatalist').val($('.pen_id_flexdatalist').next().val());

  // 바코드 값 적용
  $('.it_barcode_wr').each(function() {
    var it_barcode = [];
    $(this).find('.it_barcode').each(function() {
      it_barcode.push($(this).val());
    });

    $(this).parent().find('input[name="it_barcode[]"]').val(it_barcode.join(String.fromCharCode(30)));
  });

  // 대여제품 계약기간 값 적용
  $('.it_date_wr').each(function() {
    var from = $(this).find('input[data-range="from"]').val();
    var to = $(this).find('input[data-range="to"]').val();

    if(from && to) {
      $(this).find('input[name="it_date[]"]').val(from + '-' + to);
    }
  });

  loading = true;
  var $form = $('#form_simple_eform');
  $.post('ajax.simple_eform.php', $form.serialize(), 'json')
    .done(function(result) {
      var dc_id = result.data;

      var w = $('input[name="w"]').val();
      if(w === 'w')
        window.location.href = '/shop/electronic_manage.php?dc_id=' + dc_id;

      $('input[name="w"]').val('u');
      $('input[name="dc_id"]').val(dc_id);

      var preview_url = '/shop/eform/renderEform.php?preview=1&dc_id=' + dc_id;
      $('#se_preview').empty().append($('<iframe>').attr('src', preview_url).attr('frameborder', 0));
    })
    .fail(function ($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      loading = false;
    });
}

// 계약서 작성
$('#btn_se_submit').on('click', function() {
  if(loading) {
    alert('계약서 저장 중입니다. 잠시 기다려주세요.');
    return false;
  }

  var dc_id = $('input[name="dc_id"]').val();

  if(!dc_id)
      return alert('먼저 품목 선택 후 저장을 해주세요.');

  $('input[name="w"]').val('w');
  save_eform();
});

// 바코드 필드 개수 업데이트
function update_barcode_field() {
  $('.se_item_list').each(function() {
    $(this).find('li').each(function() {
      // 상품 개수
      var it_qty = $(this).find('input[name="it_qty[]"]').val();

      // 먼저 기존에 입력된 바코드값 저장
      var barcodes = [];
      var $barcode = $(this).find('input.it_barcode');
      $barcode.each(function() {
        barcodes.push($(this).val() || '');
      });

      var $barcode_wr = $(this).find('.it_barcode_wr').empty();
      for(var i = 0; i < it_qty; i++) {
        var val = barcodes.shift() || '';
        $barcode_wr.append('<input type="text" class="it_barcode" maxlength="12" oninput="max_length_check(this)" placeholder="12자리 숫자를 입력하세요." value="' + val + '">');
      }
    });
  });
}

// datepicker
$('.birthpicker').datepicker({ changeMonth: true, changeYear: true, yearRange: 'c-120:c+0', maxDate: '+0d', dateFormat: 'yy.mm.dd' });
$('.datepicker').datepicker({ changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd' });

// 수급자 검색
$('.pen_id_flexdatalist').flexdatalist({
  minLength: 1,
  url: 'ajax.get_pen_id.php',
  cache: true, // cache
  searchContain: true, // %검색어%
  noResultsText: '"{keyword}"으로 등록된 수급자가 없습니다. 수급자정보를 직접 입력 하시고 계약서 작성 시 자동으로 등록됩니다.',
  visibleProperties: ["penNm"],
  searchIn: ["penNm"],
  focusFirstResult: true,
})
.on('change:flexdatalist', function() {
  // 이름 변경됨
  $('#penId').val('');
})
.on("select:flexdatalist", function(event, obj, options) {
  $('#penId').val(obj.penId);
  $('#penLtmNum').val(obj.penLtmNum);
  $('#penConNum').val(obj.penConNum);
  if(obj.penRecGraCd)
    $('#penRecGraCd').val(obj.penRecGraCd);
  if(obj.penTypeCd)
    $('#penTypeCd').val(obj.penTypeCd);
  $('#penBirth').val(obj.penBirth);
  $('#penExpiStDtm').val(obj.penExpiStDtm);
  $('#penExpiEdDtm').val(obj.penExpiEdDtm);
  $('#penJumin').val(obj.penJumin);
});

// 품목찾기
$('#popup_box').click(function() {
  $('body').removeClass('modal-open');
  $('#popup_box').hide();
});
$('#btn_se_sch').click(function() {
  var url = 'pop.item.select.php?no_option=1';

  $('#popup_box iframe').attr('src', url);
  $('body').addClass('modal-open');
  $('#popup_box').show();
});

// 품목 검색
$('#ipt_se_sch').flexdatalist({
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

// 상품수량변경
$(document).on('click', '.it_qty button', function() {
  var mode = $(this).text();
  var this_qty;
  var $it_qty = $(this).closest('.it_qty').find('input[name="it_qty[]"]');

  switch(mode) {
    case '증가':
      this_qty = parseInt($it_qty.val().replace(/[^0-9]/, "")) + 1;
      $it_qty.val(this_qty);
      break;
    case '감소':
      this_qty = parseInt($it_qty.val().replace(/[^0-9]/, "")) - 1;
      if(this_qty < 1) this_qty = 1
      $it_qty.val(this_qty);
      break;
  }
  update_barcode_field();
});
$(document).on('change paste keyup', 'input[name="it_qty[]"]', function() {
  if($(this).val() < 1)
    $(this).val(1);
  update_barcode_field();
});

// 품목 삭제
$(document).on('click', '.btn_del_item', function() {
  $(this).closest('li').remove();
  check_no_item();
});

check_no_item();
</script>

<?php include_once("./_tail.php"); ?>
