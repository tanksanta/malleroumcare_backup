<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default')
  alert('접근할 수 없습니다.');

$g5['title'] = '계약서 작성';
include_once("./_head.php");

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/simple_efrom.css">');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">');
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js"></script>');
?>

<section class="wrap">
  <div class="sub_section_tit">계약서 작성</div>
  <div class="inner">

    <form id="form_simple_eform" method="POST" class="form-horizontal" onsubmit="return false;">
      <input type="hidden" name="w" value="<?=$w?>">
      <input type="hidden" name="ms_id" value="<?=$ms_id?>">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <label for="penNm" class="col-md-2 control-label">
              <strong>수급자명</strong>
            </label>
            <div class="col-md-3">
              <input type="text" name="penNm" id="penNm" class="form-control input-sm pen_id_flexdatalist" value="" placeholder="수급자명">
            </div>
          </div>
          <div class="form-group">
            <label for="penLtmNum" class="col-md-2 control-label">
              <strong>요양인정번호</strong>
            </label>
            <div class="col-md-3">
              <input type="text" name="penLtmNum" id="penLtmNum" class="form-control input-sm" value="" placeholder="L**********">
            </div>
            <label for="penGender" class="col-md-2 control-label">
              <strong>성별</strong>
            </label>
            <div class="col-md-3">
              <div class="radio_wr">
                <label class="radio-inline">
                  <input type="radio" name="penGender" id="penGender_0" value="남" checked> 남
                </label>
                <label class="radio-inline">
                  <input type="radio" name="penGender" id="penGender_1" value="여"> 여
                </label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="penRecGraCd" class="col-md-2 control-label">
              <strong>인정등급</strong>
            </label>
            <div class="col-md-3">
              <select name="penRecGraCd" id="penRecGraCd" class="form-control input-sm">
                <option value="00">등급외</option>
                <option value="01">1등급</option>
                <option value="02">2등급</option>
                <option value="03">3등급</option>
                <option value="04">4등급</option>
                <option value="05">5등급</option>
              </select>
            </div>
            <label for="penTypeCd" class="col-md-2 control-label">
              <strong>본인부담금율</strong>
            </label>
            <div class="col-md-3">
              <select name="penTypeCd" id="penTypeCd" class="form-control input-sm">
                <option value="00">일반 15%</option>
                <option value="01">감경 9%</option>
                <option value="02">감경 6%</option>
                <option value="03">의료 6%</option>
                <option value="04">기초 0%</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="penBirth" class="col-md-2 control-label">
              <strong>생년월일</strong>
            </label>
            <div class="col-md-3">
              <input type="text" name="penBirth" id="penBirth" class="form-control input-sm" value="">
            </div>
            <label for="penExpiStDtm" class="col-md-2 control-label">
              <strong>유효기간</strong>
            </label>
            <div class="col-md-5">
              <input type="text" name="penExpiStDtm" id="penExpiStDtm" class="form-control input-sm" value=""> ~ <input type="text" name="penExpiEdDtm" id="penExpiEdDtm" class="form-control input-sm" value="">
            </div>
          </div>
          <div class="form-group">
            <label for="penJumin" class="col-md-2 control-label">
              <strong>주민번호(앞자리)</strong>
            </label>
            <div class="col-md-3">
              <input type="text" name="penJumin" id="penJumin" class="form-control input-sm" value="">
            </div>
          </div>
        </div>
        <div class="se_btn_wr">
          <button type="submit" class="btn_se_submit">
            <img src="<?=THEMA_URL?>/assets/img/icon_contract.png" alt="">
            계약서 작성
          </button>
        </div>
      </div>

      <div class="flex space-between">
        <div class="se_item_wr">
          <div class="se_sch_wr flex align-items">
            <div class="se_sch_hd">품목 목록</div>
            <input type="text" id="ipt_se_sch" class="ipt_se_sch" placeholder="품목명">
            <button class="btn_se_sch">품목찾기</button>
          </div>
          <div class="se_item_hd">판매품목</div>
          <ul id="buy_list" class="se_item_list">
            <?php for($i = 0; $i < 2; $i ++) { ?>
            <li>
              <div class="it_info">
                <img class="it_img" src="/img/no_img.png" onerror="this.src='/img/no_img.png';">
                <p class="it_cate">안전손잡이</p>
                <p class="it_name">ASH-120 (설치) (판매)</p>
                <p class="it_price">급여가 : 44,500원</p>
              </div>
              <div class="it_btn_wr flex align-items space-between">
                <div class="it_qty">
                  <div class="input-group">
                    <div class="input-group-btn">
                      <button type="button" class="it_qty_minus btn btn-lightgray btn-sm"><i class="fa fa-minus"></i><span class="sound_only">감소</span></button>
                    </div>
                    <input type="text" name="it_qty[]" value="1" class="form-control input-sm">
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
                    <input type="text" name="it_date[]" class="inline">
                  </div>
                </div>
                <div class="flex">
                  <div class="it_ipt_hd">바코드</div>
                  <div class="it_ipt">
                    <input type="text" name="it_barcode[]">
                    <input type="text" name="it_barcode[]">
                  </div>
                </div>
              </div>
            </li>
            <?php } ?>
          </ul>
          <div class="se_item_hd">대여품목</div>
          <ul id="rent_list" class="se_item_list"></ul>
        </div>
        <div class="se_preview_wr">
          <div class="se_preview_hd">
            수급자와 작성할 계약서 미리보기
          </div>
          <div id="se_preview" class="se_preview">
            <div class="empty">품목선택 시 생성됩니다.</div>
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
  // 이름 변경됨
})
.on("select:flexdatalist", function(event, obj, options) {
  $('#penLtmNum').val(obj.penLtmNum);
  if(obj.penGender == '남' || obj.penGender == '여')
    $('input[name="penGender"][value="' + obj.penGender + '"]').prop('checked', true);
  if(obj.penRecGraCd)
    $('#penRecGraCd').val(obj.penRecGraCd);
  if(obj.penTypeCd)
    $('#penTypeCd').val(obj.penTypeCd);
  $('#penBirth').val(obj.penBirth);
  $('#penExpiStDtm').val(obj.penExpiStDtm);
  $('#penExpiEdDtm').val(obj.penExpiEdDtm);
  $('#penJumin').val(obj.penJumin);
});
</script>

<?php include_once("./_tail.php"); ?>
