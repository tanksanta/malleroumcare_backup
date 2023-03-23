<?php
include_once("./_common.php");

if(!$is_samhwa_partner && !$is_admin) {
  alert("파트너 회원만 접근 가능한 페이지입니다.");
}

$od_id = get_search_string($_GET['od_id']);
if(!$od_id) {
  alert('정상적인 접근이 아닙니다.');
}
if (!$is_admin) {
  $check_member = "and ct_direct_delivery_partner = '{$member['mb_id']}'";
}
$check_result = sql_fetch("
  SELECT
    ct_id, o.mb_id, ct_direct_delivery_partner, o.od_id
  FROM
    {$g5['g5_shop_order_table']} o
  LEFT JOIN
    {$g5['g5_shop_cart_table']} c ON c.od_id = o.od_id
  WHERE
    o.od_id = '{$od_id}' {$check_member}
  LIMIT 1
", true);
if(!$check_result['ct_id'])
  alert('존재하지 않는 주문입니다.');

$report = sql_fetch("
  SELECT * FROM partner_install_report
  WHERE od_id = '{$od_id}'
");

$photos = [];
if($report && $report['od_id']) {
  // 이미 작성된 설치결과보고서가 있다면

  // 설치사진 가져오기
  $photo_result1 = sql_query("
    SELECT * FROM partner_install_photo
    WHERE od_id = '{$od_id}'
    AND img_type = '설치사진'
    ORDER BY ip_id ASC
  ");
  $photos1[] = array();
  while($row = sql_fetch_array($photo_result1)) {
    $photos1[] = $row;
  }
  array_shift($photos1);

  // 실물바코드사진(필수) 가져오기
  $photo_result2 = sql_query("
    SELECT * FROM partner_install_photo
    WHERE od_id = '{$od_id}'
    AND img_type = '실물바코드사진'
    ORDER BY ip_id ASC
  ");
  $photos2[] = array();
  while($row = sql_fetch_array($photo_result2)) {
    $photos2[] = $row;
  }
  array_shift($photos2);

  // 설치ㆍ회수ㆍ소독확인서(필수) 가져오기
  $photo_result3 = sql_query("
    SELECT * FROM partner_install_photo
    WHERE od_id = '{$od_id}'
    AND img_type = '설치ㆍ회수ㆍ소독확인서'
    ORDER BY ip_id ASC
  ");
  $photos3[] = array();
  while($row = sql_fetch_array($photo_result3)) {
    $photos3[] = $row;
  }
  array_shift($photos3);

  // 추가사진(선택) 가져오기
  $photo_result4 = sql_query("
    SELECT * FROM partner_install_photo
    WHERE od_id = '{$od_id}'
    AND img_type = '추가사진'
    ORDER BY ip_id ASC
  ");
  $photos4[] = array();
  while($row = sql_fetch_array($photo_result4)) {
    $photos4[] = $row;
  }
  array_shift($photos4);
} else {
  // 설치결과보고서 INSERT
  $insert_result = sql_query("
    INSERT INTO partner_install_report
    SET
      od_id = '$od_id',
      mb_id = '{$check_result['ct_direct_delivery_partner']}',
      ir_issue = '',
      ir_created_at = NOW(),
      ir_updated_at = NOW()
  ");
  $report = array(
    'ir_cert_name' => '',
    'ir_cert_url' => ''
  );
}

$sql = "
    SELECT * FROM
        g5_shop_cart
    WHERE
        od_id = '$od_id' and
        ct_direct_delivery_partner = '{$member['mb_id']}' and
        ct_status IN('준비', '출고준비', '배송', '완료')
    ORDER BY
        ct_id ASC
";
$result = sql_query($sql);
$carts = [];
while($ct = sql_fetch_array($result)) {
    $ct['it_name'] .= $ct['ct_option'] && $ct['ct_option'] != $ct['it_name'] ? " ({$ct['ct_option']})" : '';

    // 바코드 정보 가져오기
    $sto_id = [];

    foreach(array_filter(explode('|', $ct['stoId'])) as $id) {
        $sto_id[] = $id;
    }

    $stock_result = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(
        'stoId' => implode('|', $sto_id)
    ));

    $barcodes = [];
    if($stock_result['data']) {
      foreach($stock_result['data'] as $data) {
        $barcodes[$data['stoId']] = $data['prodBarNum'];
      }
    }

    $ct['barcode'] = $barcodes;

    $carts[] = $ct;
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>설치결과등록</title>
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/common_new.css">
  <link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/font.css">
  <link rel="shortcut icon" href="<?php echo THEMA_URL; ?>/assets/img/top_logo_icon.ico">
  <link rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo G5_CSS_URL; ?>/magnific-popup.css">
  <script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
  <script src="<?php echo G5_JS_URL ?>/common.js?v=1"></script>
  <script src="<?php echo G5_JS_URL ?>/jquery.wheelzoom.js"></script>
  <script src="<?php echo G5_JS_URL ?>/jquery.magnific-popup.js"></script>
  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    position: relative;
  }

  html,
  body {
    width: 100%;
    min-width: 100%;
    float: left;
    margin: 0 !important;
    padding: 0;
    font-family: "Noto Sans KR", sans-serif;
    font-size: 13px;
  }

  body {
    padding: 60px 0;
  }

  #popupHeaderTopWrap {
    position: fixed;
    width: 100%;
    height: 60px;
    left: 0;
    top: 0;
    z-index: 10;
    background-color: #333;
    padding: 0 20px;
  }

  #popupHeaderTopWrap>div {
    height: 100%;
    line-height: 60px;
  }

  #popupHeaderTopWrap>.title {
    float: left;
    font-weight: bold;
    color: #FFF;
    font-size: 22px;
  }

  #popupHeaderTopWrap>.close {
    float: right;
  }

  #popupHeaderTopWrap>.close>a {
    color: #FFF;
    font-size: 40px;
    top: -2px;
  }

  #table_ir {
    width: 100%;
  }

  #table_ir .ipt_file {
    display: none;
  }

  #table_ir .label_file {
    display: inline-block;
    border: 1px solid #d7d7d7;
    color: #666;
    border-radius: 3px;
    padding: 10px 30px;
    cursor: pointer;
  }

  #table_ir .tr_content {
    border-bottom: 1px solid #ccc;
  }

  #table_ir .tr_head {
    border: none;
  }

  #table_ir th,
  #table_ir td,
  .issue_wrap {
    padding: 15px;
    text-align: left;
    vertical-align: top;
  }

  #table_ir .tr_head th,
  #table_ir .tr_head td {
    padding-bottom: 0;
  }

  #table_ir .tr_content th,
  #table_ir .tr_content td {
    padding-top: 0;
  }

  .section_head {
    font-weight: 700;
    padding: 5px 0 0 5px;
    flex: 2;
  }

  #txt_issue {
    width: 100%;
    margin-top: 15px;
    border: 1px solid #d7d7d7;
    border-radius: 3px;
    padding: 6px;
    resize: vertical;
  }

  .list_file {
    margin-top: 10px;
    display: flex;
    overflow-x: auto;
    max-width: calc(100vw - 45px);
    min-height: 112px;
    cursor: pointer;
  }

  .list_file li {
    padding: 5px;
    vertical-align: middle;
  }

  .list_file li a.view_image {
    display: block;
    width: 100px;
    min-width: 100px;
    max-width: 100px;
    height: 100px;
    border: 1px solid #ddd;
    margin-bottom: 3px;
  }

  .list_file li a.view_image img {
    width: 100%;
    height: 100%;
  }

  .btn_remove {
    margin-left: 10px;
    width: 25px;
    height: 25px;
    background-color: #000;
    border-radius: 3px;
    color: #fff;
    font-size: 15px;
  }

  #popupFooterBtnWrap {
    position: fixed;
    width: 100%;
    height: 70px;
    background-color: #000;
    bottom: 0px;
    z-index: 10;
  }

  #popupFooterBtnWrap>button {
    font-size: 18px;
    font-weight: bold;
  }

  #popupFooterBtnWrap>.savebtn {
    float: left;
    width: 75%;
    height: 100%;
    background-color: #000;
    color: #FFF;
  }

  #popupFooterBtnWrap>.cancelbtn {
    float: right;
    width: 25%;
    height: 100%;
    color: #666;
    background-color: #DDD;
  }

  input[type="checkbox"],
  label {
    vertical-align: middle;
  }

  label {
    margin-right: 10px;
  }

  #table_ir .tbl_barcode {
    width: 100%;
    margin-top: 15px;
  }

  #table_ir .tbl_barcode thead th {
    background: #eee;
    border-top: 1px solid #e3e3e3;
    padding: 5px 10px;
    text-align: center;
  }

  #table_ir .tbl_barcode tbody td {
    background: #f5f5f5;
    border-top: 1px solid #e3e3e3;
    padding: 10px;
  }

  #table_ir .tbl_barcode input[type="text"] {
    display: block;
    width: 100%;
    padding: 5px;
    background: #fff;
    border: 1px solid #eaeaea;
  }

  #table_ir .tbl_barcode input[type="text"]+input[type="text"] {
    margin-top: 5px;
  }

  #table_ir .tbl_barcode input[type="text"]:read-only {
    background: #f0f0f0;
  }

  .link_wr {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    margin: 5px -5px 0 -5px;
  }

  .link_wr a {
    display: block;
    border-radius: 3px;
    width: 100%;
    margin: 5px;
    padding: 10px;
    text-align: center;
    font-weight: bold;
  }

  .link_wr a.btn_od_edit {
    background: #fff;
    border: 1px solid #8abf63;
    color: #8abf63;
  }

  .link_wr a.btn_ir_sign {
    background: #ef7c00;
    color: #fff;
  }

  .link_wr a.btn_ir_download {
    background: #fff;
    border: 1px solid #999;
    color: #333;
  }

  .link_wr a.btn_ir_unsign {
    background: #fff;
    border: 1px solid #ef7c00;
    color: #ef7c00;
  }

  .image_wrap_placeholder {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    border: 1px solid #ddd;
  }

  .th_col {
    display: flex;
    flex-direction: row;
    width: 100%;
  }

  .form_file_photo {
    flex: 1;
    display: flex;
    justify-content: flex-end;
  }

  .btn_photo {
    padding: 5px 8px;
    border: 1px solid #ddd;
    background: #f3f3f3;
    border-radius: 3px;
  }
  </style>
  <script src="/js/detectmobilebrowser.js">
  </script>
</head>

<body>
  <div id="popupHeaderTopWrap">
    <div class="title">설치결과등록</div>
    <div class="close">
      <a href="#" id="popupCloseBtn">
        &times;
      </a>
    </div>
  </div>
  <table id="table_ir">
    <colgroup>
      <col style="width: 150px;">
      <col>
    </colgroup>
    <tbody style="max-width: calc(100vw - 45px);">
      <tr class="tr_head">
        <th colspan="1">
          <div class="section_head">결과보고서</div>
        </th>
      </tr>
      <tr class="tr_content">
        <td colspan="1">
          <form id="form_barcode" onsubmit="return false;">
            <input type="hidden" name="od_id" value="<?=$od_id?>">
            <table class="tbl_barcode">
              <thead>
                <tr>
                  <th>상품명 (수량)</th>
                  <th>바코드</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($carts as $ct) { ?>
                <tr>
                  <td><?="{$ct['it_name']} ({$ct['ct_qty']}개)"?></td>
                  <td>
                    <?php foreach($ct['barcode'] as $stoId => $barcode) { ?>
                    <input type="text" name="barcode[<?=$stoId?>]" value="<?=$barcode?>" placeholder="바코드 12자리 입력하세요."
                      maxlength="12" <?php if($report['ir_file_url'] || $report['ir_cert_url']) { echo 'readonly'; } ?>>
                    <?php } ?>
                  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </form>
          <div class="link_wr">
            <?php if($report['ir_file_url']) { ?>
            <a href="<?=G5_SHOP_URL?>/eform/install_report_download.php?od_id=<?=$od_id?>" class="btn_ir_download">결과보고서
              다운로드</a>
            <a href="<?=G5_SHOP_URL?>/eform/ajax.install_report.unsign.php?od_id=<?=$od_id?>"
              class="btn_ir_unsign">결과보고서 작성 취소</a>
            <?php } else { ?>
            <a href="partner_orderinquiry_edit.php?od_id=<?=$od_id?>" class="btn_od_edit">설치상품 변경</a>
            <a href="<?=G5_SHOP_URL?>/eform/install_report_sign.php?od_id=<?=$od_id?>" class="btn_ir_sign">결과보고서 작성</a>
            <?php } ?>
          </div>
        </td>
      </tr>
      <tr class="tr_content">
        <td colspan="1" style="display: flex; flex-direction: row;">
          <div class="section_head" style="padding-top:15px;">이슈사항</div>
          <form id="form_partner_installreport2">
            <div style="padding: 15px 10px 0 10px;">
              <input type="checkbox" name="ir_is_issue_1" <?php echo $report['ir_is_issue_1']?'checked':''; ?> value="1"
                id="ir_is_issue_1">
              <label for="ir_is_issue_1">상품변경</label>
              <input type="checkbox" name="ir_is_issue_2" <?php echo $report['ir_is_issue_2']?'checked':''; ?> value="1"
                id="ir_is_issue_2">
              <label for="ir_is_issue_2">상품추가</label>
              <input type="checkbox" name="ir_is_issue_3" <?php echo $report['ir_is_issue_3']?'checked':''; ?> value="1"
                id="ir_is_issue_3">
              <label for="ir_is_issue_3">방문 후 미설치</label>
            </div>
          </form>
        </td>
      </tr>
	  <tr class="tr_head">
        <th class="th_col">
			<div class="section_head">이슈사항 작성</div>
		</th>
      </tr>
	   <tr class="tr_content report-img-wrap">
        <td colspan="1">
          <form id="form_partner_installreport" class="form_file_photo">
			<input type="hidden" name="od_id" value="<?=$od_id?>">
			<textarea name="ir_issue" id="txt_issue" rows="7"><?=$report['ir_issue']?></textarea>
		  </form>
        </td>
      </tr>
      <tr class="tr_content">
      </tr>
      <?php if($report['ir_cert_url']) { ?>
      <tr class="tr_head">
        <th colspan="1">
          <div class="section_head">설치 확인서</div>
        </th>
      </tr>
      <tr class="tr_content">
        <td colspan="1">
          <ul id="list_file_cert" class="list_file">
            <li>
              <a href="<?=G5_BBS_URL?>/view_image.php?open_safari=1&fn=<?=urlencode(str_replace(G5_URL, "", G5_DATA_URL."/partner/img/{$report['ir_cert_url']}"))?>"
                target="_blank" class="view_image">
                <img src="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>"
                  onerror="this.src='/shop/img/no_image.gif';">
              </a>
              <?=$report['ir_cert_name']?>
              <button class="btn_remove" data-type="cert">
                <i class="fa fa-times" aria-hidden="true"></i>
              </button>
            </li>
          </ul>
        </td>
      </tr>
      <?php } ?>
      <tr class="tr_head">
        <th class="th_col">
          <div class="section_head">설치 사진 등록(필수)</div>
          <form id="form_file_photo1" class="form_file_photo">
            <input type="hidden" name="type" value="photo">
            <input type="hidden" name="img_type" value="설치사진">
            <input type="hidden" name="od_id" value="<?=$od_id?>">
            <input type="hidden" name="m" value="u">
            <input type="file" class="ipt_file" name="file_photo1[]" id="file_photo1" accept="image/*,.pdf" multiple>
            <button class="btn_photo" id="btn_photo1">파일 선택</button>
          </form>
        </th>
      </tr>
      <tr class="tr_content report-img-wrap">
        <td colspan="1">
          <ul id="list_file_photo1" class="list_file">
            <?php if ($photos1) { ?>
            <?php foreach($photos1 as $photo) { ?>
            <li>
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; ?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
              <?=$photo['ip_photo_name']?>
              <button class="btn_remove" data-type="photo" data-id="<?=$photo['ip_id']?>">
                <i class="fa fa-times" aria-hidden="true"></i>
              </button>
            </li>
            <?php }} else { ?>
            <p id="fileDragDesc" class="image_wrap_placeholder">첨부할 파일을 마우스로 끌어오세요.</p>
            <?php } ?>
          </ul>
        </td>
      </tr>

      <tr class="tr_head">
        <th class="th_col">
          <div class="section_head">실물바코드사진(필수)</div>
          <form id="form_file_photo2" class="form_file_photo">
            <input type="hidden" name="type" value="photo">
            <input type="hidden" name="img_type" value="실물바코드사진">
            <input type="hidden" name="od_id" value="<?=$od_id?>">
            <input type="hidden" name="m" value="u">
            <input type="file" class="ipt_file" name="file_photo2[]" id="file_photo2" accept="image/*,.pdf" multiple>
            <button class="btn_photo" id="btn_photo2">파일 선택</button>
          </form>
        </th>
      </tr>
      <tr class="tr_content report-img-wrap">
        <td colspan="1">
          <ul id="list_file_photo2" class="list_file">
            <?php if ($photos2) { ?>
            <?php foreach($photos2 as $photo) { ?>
            <li>
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; ?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
              <?=$photo['ip_photo_name']?>
              <button class="btn_remove" data-type="photo" data-id="<?=$photo['ip_id']?>">
                <i class="fa fa-times" aria-hidden="true"></i>
              </button>
            </li>
            <?php }} else { ?>
            <p id="fileDragDesc" class="image_wrap_placeholder">첨부할 파일을 마우스로 끌어오세요.</p>
            <?php } ?>
          </ul>
        </td>
      </tr>

      <tr class="tr_head">
        <th class="th_col">
          <div class="section_head">설치ㆍ회수ㆍ소독확인서(필수)</div>
          <form id="form_file_photo3" class="form_file_photo">
            <input type="hidden" name="type" value="photo">
            <input type="hidden" name="img_type" value="설치ㆍ회수ㆍ소독확인서">
            <input type="hidden" name="od_id" value="<?=$od_id?>">
            <input type="hidden" name="m" value="u">
            <input type="file" class="ipt_file" name="file_photo3[]" id="file_photo3" accept="image/*,.pdf" multiple>
            <button class="btn_photo" id="btn_photo3">파일 선택</button>
          </form>
        </th>
      </tr>
      <tr class="tr_content report-img-wrap">
        <td colspan="1">
          <ul id="list_file_photo3" class="list_file">
            <?php if ($photos3) { ?>
            <?php foreach($photos3 as $photo) { ?>
            <li>
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; ?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
              <?=$photo['ip_photo_name']?>
              <button class="btn_remove" data-type="photo" data-id="<?=$photo['ip_id']?>">
                <i class="fa fa-times" aria-hidden="true"></i>
              </button>
            </li>
            <?php }} else { ?>
            <p id="fileDragDesc" class="image_wrap_placeholder">첨부할 파일을 마우스로 끌어오세요.</p>
            <?php } ?>
          </ul>
        </td>
      </tr>

      <tr class="tr_head">
        <th class="th_col">
          <div class="section_head">추가사진(선택) 상품변경 혹은 특이사항 발생 시</div>
          <form id="form_file_photo4" class="form_file_photo">
            <input type="hidden" name="type" value="photo">
            <input type="hidden" name="img_type" value="추가사진">
            <input type="hidden" name="od_id" value="<?=$od_id?>">
            <input type="hidden" name="m" value="u">
            <input type="file" class="ipt_file" name="file_photo4[]" id="file_photo4" accept="image/*,.pdf" multiple>
            <button class="btn_photo" id="btn_photo4">파일 선택</button>
          </form>
        </th>
      </tr>
      <tr class="tr_content report-img-wrap">
        <td colspan="1">
          <ul id="list_file_photo4" class="list_file">
            <?php if ($photos4) { ?>
            <?php foreach($photos4 as $photo) { ?>
            <li>
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; ?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
              <?=$photo['ip_photo_name']?>
              <button class="btn_remove" data-type="photo" data-id="<?=$photo['ip_id']?>">
                <i class="fa fa-times" aria-hidden="true"></i>
              </button>
            </li>
            <?php }} else { ?>
            <p id="fileDragDesc" class="image_wrap_placeholder">첨부할 파일을 마우스로 끌어오세요.</p>
            <?php } ?>
          </ul>
        </td>
      </tr>
    </tbody>
  </table>

  <div id="popupFooterBtnWrap">
    <button type="button" class="savebtn" id="prodBarNumSaveBtn">저장</button>
    <button type="button" class="cancelbtn" onclick="closePopup();">취소</button>
  </div>

  <script type="text/javascript">
  // 팝업 닫기
  function closePopup() {
    try {
      $('#hd', parent.document).css('z-index', 10);
    } catch (e) {}
    $("body", parent.document).removeClass('modal-open');
    $("#popup_box", parent.document).hide();
    $("#popup_box", parent.document).find("iframe").remove();
  }
  $(function() {
    [1, 2, 3, 4].map((item) => {
      if (jQuery.browser.mobile) {
        $("#list_file_photo" + item + " >.image_wrap_placeholder").text("파일 선택 버튼을 눌러 첨부파일을 업로드 해주세요.");
      } else {
        $("#list_file_photo" + item + " >.image_wrap_placeholder").text("첨부할 파일을 마우스로 끌어오세요.");
      }
    });
    $(document).on("DOMNodeInserted", '.mfp-content', function() {
      window.wheelzoom($('.mfp-img'));
    });
    $('.report-img-wrap').click(function() {
      window.wheelzoom($('.mfp-img'));
    });

    $("#popupCloseBtn").click(function(e) {
      e.preventDefault();

      closePopup();
    });

    $('#prodBarNumSaveBtn').click(function() {
      $('#form_partner_installreport').submit();
    });

    // 설치상품 변경 버튼, 결과보고서 다운로드 버튼
    $('.btn_od_edit, .btn_ir_download').click(function(e) {
      e.preventDefault();

      parent.location.href = $(this).attr('href');
    });

    // 결과보고서 작성 취소 버튼
    $('.btn_ir_unsign').click(function(e) {
      e.preventDefault();


      if (!confirm('정말 작성된 결과보고서를 삭제하시겠습니까?'))
        return;

      let url = $(this).attr('href');
      $.get(url, {}, 'json')
        .done(function() {
          $.post('/shop/schedule/ajax.update_schedule_status.php', {
              od_id: '<?=$od_id?>',
              status: '출고준비'
            }, 'json')
            .done(function() {
              window.location.reload();
            })
            .fail(function($xhr) {
              let data = $xhr.responseJSON;
              alert(data && data.message);
            });
        })
        .fail(function($xhr) {
          let data = $xhr.responseJSON;
          alert(data && data.message);
        });
    });

    // 결과보고서 작성 버튼
    $('.btn_ir_sign').click(function(e) {
      e.preventDefault();
      if (!($("#list_file_photo1").children("li, a").length > 0 && $("#list_file_photo2").children("li, a")
          .length > 0 && $(
            "#list_file_photo3").children("li, a").length > 0)) {
        return alert('필수 파일들을 모두 업로드해야 결과보고서 작성이 가능합니다.');
      } else {
        let sign_url = $(this).attr('href');

        // 바코드 전부 입력되었는지 체크
        let is_barcode_completed = true;
        $('input[name^="barcode"]').each(function() {
          let barcode = $(this).val();

          if (barcode.length != 12)
            is_barcode_completed = false;
        });
        if (!is_barcode_completed)
          return alert('설치한 바코드 정보를 정상적으로 모두 입력 후 결과보고서 작성이 가능합니다.');

        // 바코드 부터 저장
        $.post('ajax.partner_installbarcode.php', $('#form_barcode').serializeObject(), 'json')
          .done(function() {
            parent.location.href = sign_url;
          })
          .fail(function($xhr) {
            let data = $xhr.responseJSON;
            alert(data && data.message);
          });
      }
    });

    // 설치 결과 등록
    $('#form_partner_installreport').on('submit', function(e) {
      // TODO : 필수 사진 데이터들이 모두 들어 있는지 확인 한 후 post API 호출 할 것.
      // TODO : 2022-12-14에 작업 할 것.
      e.preventDefault();

      // 바코드 부터 저장
      $.post('ajax.partner_installbarcode.php', $('#form_barcode').serializeObject(), 'json')
        .done(function() {

          let data = $.extend(
            $('#form_partner_installreport2').serializeObject(),
            $('#form_partner_installreport').serializeObject(),
          );

          $.post('ajax.partner_installreport.php', data, 'json')
            .done(function() {
              alert('저장이 완료되었습니다.');
              parent.window.location.reload();
            })
            .fail(function($xhr) {
              let data = $xhr.responseJSON;
              alert(data && data.message);
            });

        })
        .fail(function($xhr) {
          let data = $xhr.responseJSON;
          alert(data && data.message);
        });
    });

    // 설치 확인서 업로드
    $('#file_cert').on('change', function() {
      if ($(this).val()) {
        $('#form_file_cert').submit();
      }
    });
    $('#form_file_cert').on('submit', function(e) {
      e.preventDefault();

      $.ajax({
          url: 'ajax.partner_installphoto.php',
          type: 'POST',
          data: new FormData(this),
          cache: false,
          processData: false,
          contentType: false,
          dataType: 'json'
        })
        .done(function(result) {
          let photo = result.data;

          $('#list_file_cert').html('\
            <li>\
              <a href="/bbs/view_image.php?open_safari=1&fn=' + encodeURIComponent('/data/partner/img/' + photo[
            'url']) + '" target="_blank" class="view_image">\
                <img src="' + ('/data/partner/img/' + photo['url']) + '" onerror="this.src=\'' + (photo[
              'ip_photo_name']
            .endsWith('.pdf') ? '/shop/img/icon_pdf.png' : '/shop/img/no_image.gif') + '\';">\
              </a>\
              ' + photo.name + '\
              <button class="btn_remove" data-type="cert">\
                <i class="fa fa-times" aria-hidden="true"></i>\
              </button>\
            </li>\
          ');
        })
        .fail(function($xhr) {
          let data = $xhr.responseJSON;
          alert(data && data.message);
        })
        .always(function() {
          $('#file_cert').val('');
        });
    });

    // 삭제버튼
    $(document).on('click', '.btn_remove', function() {
      if (!confirm('정말 파일을 삭제하시겠습니까?')) return;

      let type = $(this).data('type');

      if (type === 'cert') {
        // 설치확인서
        $.post('ajax.partner_installphoto.php', {
            od_id: '<?=$od_id?>',
            type: 'cert',
            m: 'd'
          }, 'json')
          .done(function() {
            $('#list_file_cert').empty();
          })
          .fail(function($xhr) {
            let data = $xhr.responseJSON;
            alert(data && data.message);
          });
      } else if (type === 'photo') {
        let $li = $(this).closest('li');

        // 설치사진
        let ip_id = $(this).data('id');
        $.post('ajax.partner_installphoto.php', {
            od_id: '<?=$od_id?>',
            type: 'photo',
            m: 'd',
            ip_id: ip_id
          }, 'json')
          .done(function(result) {
            $li.remove();
          })
          .fail(function($xhr) {
            let data = $xhr.responseJSON;
            alert(data && data.message);
          });
      }
    });

    $('#form_partner_installreport').on('submit', function(e) {
      e.preventDefault();
    });
  });
  </script>

  <script>
  // 파일 리스트 번호
  let fileIndex = 0;
  // 등록할 전체 파일 사이즈
  let totalFileSize = 0;
  // 파일 리스트
  let fileList = new Array();
  // 파일 사이즈 리스트
  let fileSizeList = new Array();
  // 등록 가능한 파일 사이즈 MB
  let uploadSize = 50;
  // 등록 가능한 총 파일 사이즈 MB
  let maxUploadSize = 500;

  $(function() {
    // 파일 드롭 다운
    fileDropDown("#list_file_photo", "1");
    fileDropDown("#list_file_photo", "2");
    fileDropDown("#list_file_photo", "3");
    fileDropDown("#list_file_photo", "4");

    fileClick("1");
    fileClick("2");
    fileClick("3");
    fileClick("4");
  });

  function fileClick(id) {
    $('#btn_photo' + id).on('click', function(e) {
      e.preventDefault();
      $('#file_photo' + id).click();
    });
    $('#file_photo' + id).on('change', function() {
      if ($(this).val()) {
        $('#form_file_photo' + id).submit();
      }
    });
    $('#form_file_photo' + id).on('submit', function(e) {
      e.preventDefault();
      const formdata = new FormData(this);
      $.ajax({
          url: 'ajax.partner_installphoto.php',
          type: 'POST',
          data: formdata,
          cache: false,
          processData: false,
          contentType: false,
          dataType: 'json'
        })
        .done(function(result) {
          let photos = result.data;
          let list_photo_html = '';
          formdata.delete('file_photo' + id + '[]');
          for (let i = 0; i < photos.length; i++) {
            let photo = photos[i];
            list_photo_html += '\
              <li>\
                <a href="/bbs/view_image.php?open_safari=1&fn=' + encodeURIComponent('/data/partner/img/' + photo[
              'ip_photo_url']) + '" target="_blank" class="view_image">\
                  <img src="' + ('/data/partner/img/' + photo['ip_photo_url']) + '" onerror="this.src=\'' + (photo[
              'ip_photo_name'].endsWith('.pdf') ? '/shop/img/icon_pdf.png' : '/shop/img/no_image.gif') + '\';">\
                </a>\
                ' + photo['ip_photo_name'] + '\
                <button class="btn_remove" data-type="photo" data-id="' + photo['ip_id'] + '">\
                  <i class="fa fa-times" aria-hidden="true"></i>\
                </button>\
              </li>\
            ';
            $('#list_file_photo' + id).html(list_photo_html);
          }
        })
        .fail(function($xhr) {
          let data = $xhr.responseJSON;
          alert(data && data.message);
        })
        .always(function() {
          $('#form_file_photo' + id).val('');
        });
    });
  }

  function fileDropDown(id_str, id) {
    let dropZone = $(id_str + id);
    //Drag기능 
    dropZone.on('dragenter', function(e) {
      e.stopPropagation();
      e.preventDefault();
      // 드롭다운 영역 css
      dropZone.css('background-color', '#E3F2FC');
    });
    dropZone.on('dragleave', function(e) {
      e.stopPropagation();
      e.preventDefault();
      // 드롭다운 영역 css
      dropZone.css('background-color', '#FFFFFF');
    });
    dropZone.on('dragover', function(e) {
      e.stopPropagation();
      e.preventDefault();
      // 드롭다운 영역 css
      dropZone.css('background-color', '#E3F2FC');
    });
    dropZone.on('drop', function(e) {
      e.preventDefault();
      // 드롭다운 영역 css
      dropZone.css('background-color', '#FFFFFF');

      let files = e.originalEvent.dataTransfer.files;
      if (files != null) {
        if (files.length < 1) {
          /* alert("폴더 업로드 불가"); */
          return;
        } else {
          selectFile(files, id);
          fileList = new Array();
          fileSizeList = new Array();
        }
      } else {
        alert("ERROR");
      }
    });
  }

  function addFileList(id) {
    const obj = {
      "type": "photo",
      "od_id": "<?=$od_id?>",
      "m": "u",
    };
    switch (id) {
      case "1":
        obj["img_type"] = "설치사진";
        break;
      case "2":
        obj["img_type"] = "실물바코드사진";
        break;
      case "3":
        obj["img_type"] = "설치ㆍ회수ㆍ소독확인서";
        break;
      case "4":
        obj["img_type"] = "추가사진";
        break;
      default:
        obj["img_type"] = "설치사진";
        break;
    }

    let uploadFileList = Object.keys(fileList);
    let form = $('#form_file_photo' + id);
    let formData = new FormData(form[0]);
    for (let i = 0; i < uploadFileList.length; i++) {
      formData.append('file_photo' + id + '[]', fileList[uploadFileList[i]]);
    }

    $.ajax({
        url: 'ajax.partner_installphoto.php',
        type: 'POST',
        data: formData,
        cache: false,
        processData: false,
        contentType: false,
        dataType: 'json'
      })
      .done(function(result) {
        let photos = result.data;
        let list_photo_html = '';
        for (let i = 0; i < photos.length; i++) {
          let photo = photos[i];
          list_photo_html += '\
              <li>\
                <a href="/bbs/view_image.php?open_safari=1&fn=' + encodeURIComponent('/data/partner/img/' + photo[
            'ip_photo_url']) + '" target="_blank" class="view_image">\
                  <img src="' + ('/data/partner/img/' + photo['ip_photo_url']) + '" onerror="this.src=\'' + (photo[
            'ip_photo_name'].endsWith('.pdf') ? '/shop/img/icon_pdf.png' : '/shop/img/no_image.gif') + '\';">\
                </a>\
                ' + photo['ip_photo_name'] + '\
                <button class="btn_remove" data-type="photo" data-id="' + photo['ip_id'] + '">\
                  <i class="fa fa-times" aria-hidden="true"></i>\
                </button>\
              </li>\
            ';
          $('#list_file_photo' + id).html(list_photo_html);
        }
      })
      .fail(function($xhr) {
        let data = $xhr.responseJSON;
        alert(data && data.message);
      })
      .always(function() {
        $('#form_file_photo' + id).val('');
      });
  }

  // 파일 선택시
  function selectFile(fileObject, id) {
    let files = null;

    if (fileObject != null) {
      // 파일 Drag 이용하여 등록시
      files = fileObject;
    } else {
      // 직접 파일 등록시
      files = $('#multipaartFileList_' + fileIndex)[0].files;
    }

    // 다중파일 등록
    if (files != null) {
      if (files != null && files.length > 0) {
        $("#fileDragDesc" + id).hide();
      } else {
        $("#fileDragDesc" + id).show();
      }

      for (let i = 0; i < files.length; i++) {
        // 파일 이름
        let fileName = files[i].name;
        let fileNameArr = fileName.split("\.");
        // 확장자
        let ext = fileNameArr[fileNameArr.length - 1];

        let fileSize = files[i].size; // 파일 사이즈(단위 :byte)
        if (fileSize <= 0) {
          return;
        }

        let fileSizeKb = fileSize / 1024; // 파일 사이즈(단위 :kb)
        let fileSizeMb = fileSizeKb / 1024; // 파일 사이즈(단위 :Mb)

        let fileSizeStr = "";
        if ((1024 * 1024) <= fileSize) { // 파일 용량이 1메가 이상인 경우 
          fileSizeStr = fileSizeMb.toFixed(2) + " Mb";
        } else if ((1024) <= fileSize) {
          fileSizeStr = parseInt(fileSizeKb) + " kb";
        } else {
          fileSizeStr = parseInt(fileSize) + " byte";
        }

        if ($.inArray(ext.trim().toLowerCase(), ['png', 'pdf', 'jpg', 'jpeg']) == -1) {
          // 확장자 체크
          alert("등록이 불가능한 파일 입니다.(" + fileName + ")");
        } else if (fileSizeMb > uploadSize) {
          // 파일 사이즈 체크
          alert("용량 초과\n업로드 가능 용량 : " + uploadSize + " MB");
          break;
        } else {
          // 파일 배열에 넣기
          fileList[fileIndex] = files[i];

          // 파일 번호 증가
          fileIndex++;
        }
      }
      addFileList(id);
    } else {
      alert("ERROR");
    }
  }
  </script>

  <script>
  $(function() {
    $('.report-img-wrap').magnificPopup({
      delegate: 'a',
      type: 'image',
      image: {
        titleSrc: function(item) {

          let $div = $('<div>');

          // 원본크기
          let $btn_zoom_orig = $(
              '<button type="button" class="btn-bottom btn-zoom-orig">원본크기</button>')
            .click(function() {
              $btn_zoom_orig.hide();
              $btn_zoom_fit.show();

              $(item.img).css('max-width', 'unset');
              $(item.img).css('max-height', 'unset');
            });

          // 창맞추기
          let $btn_zoom_fit = $(
              '<button type="button" class="btn-bottom btn-zoom-fit">창맞추기</button>"')
            .hide()
            .click(function() {
              $btn_zoom_orig.show();
              $btn_zoom_fit.hide();

              $(item.img).css('max-width', '100%');
              $(item.img).css('max-height', '100%');
            });

          // 다운로드
          let $btn_download;
          if (item._src) {
            $btn_download = $('<a class="btn-bottom btn-download">다운로드</a>')
              .attr('href', item._src)
              .attr('download', '설치파일_' + item.index + '.pdf');
          } else {
            $btn_download = $('<a class="btn-bottom btn-download">다운로드</a>')
              .attr('href', item.src)
              .attr('download', '설치이미지_' + item.index + '.jpg');
          }

          // 회전
          let rotate_deg = 0;
          let $btn_rotate = $(
              '<button type="button" class="btn-bottom btn-rotate">회전</button>')
            .click(function() {
              rotate_deg = (rotate_deg + 90) % 360;
              $(item.img).css('transform', 'rotate(' + rotate_deg + 'deg)')
            });

          return $div.append(
            $btn_zoom_orig,
            $btn_zoom_fit,
            $btn_download,
            $btn_rotate);
        },
      },
      gallery: {
        enabled: true,
        tPrev: '이전', // title for left button
        tNext: '다음', // title for right button
        tCounter: '%curr% / %total%'
      },
    });
  });
  </script>
</body>

</html>