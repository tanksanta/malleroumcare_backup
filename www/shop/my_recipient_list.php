<?php
include_once("./_common.php");
define('_RECIPIENT_', true);

include_once("./_head.php");

if(!$is_member){
  alert("접근 권한이 없습니다.");
  exit;
}

// 연결기간(3일) 지난 수급자 연결해제
recipient_link_clean();

// 수급자 활동 알림
// category_limit_noti();

$rows = 5;

$page = $_GET["page"] ?? 1;

$send_data = [];
$send_data["usrId"] = $member["mb_id"];
$send_data["entId"] = $member["mb_entId"];
$send_data["pageNum"] = $page;
$send_data["pageSize"] = $rows;
if ($sel_field === 'penNm') {
  $send_data['penNm'] = $search;
}
if ($sel_field === 'penLtmNum') {
  $send_data['penLtmNum'] = $search;
}
if ($sel_field === 'penProNm') {
  $send_data['penProNm'] = $search;
}
$res = get_eroumcare(EROUMCARE_API_RECIPIENT_SELECTLIST, $send_data);

$list = [];
if($res["data"]) {
  $list = $res["data"];
}

# 페이징
$total_count = $res["total"];
$total_page = ceil( $total_count / $rows ); # 총 페이지
$write_pages = 5; # 한 블록에 보여줄 페이지 갯수 5개


// 예비 수급자
$page_spare = $_GET["page_spare"] ?? 1;
$send_data = [];
$send_data["usrId"] = $member["mb_id"];
$send_data["entId"] = $member["mb_entId"];
$send_data["pageNum"] = $page_spare;
$send_data["pageSize"] = $rows;
if ($sel_field === 'penNm') {
  $send_data['penNm'] = $search;
}
if ($sel_field === 'penProNm') {
  $send_data['penProNm'] = $search;
}
$res = get_eroumcare(EROUMCARE_API_SPARE_RECIPIENT_SELECTLIST, $send_data);

$list_spare = [];
if($res["data"]) {
  $list_spare = $res["data"];
}

$total_count_spare = $res["total"];
$total_page_spare = ceil( $total_count_spare / $rows ); # 총 페이지


// 수급자 연결
$links = get_recipient_links($member['mb_id']);
?>
<script>
function excelform(url){
  var opt = "width=600,height=450,left=10,top=10";
  window.open(url, "win_excel", opt);
  return false;
}

$(function() {
    $(".BottomButton").click(function() {
        $('html').animate({scrollTop : ($('.footer_area').offset().top)}, 600);
    });
});

</script>

<style>
.no_content { width:100%; padding: 50px 0; text-align:center; }
#myRecipientListWrap > .titleWrap > .link_notice_wrap {
  position: absolute; top:-20px; right:0; font-weight: normal !important; font-size: 16px; line-height: 20px; height: 60px; padding: 20px 40px; text-align: center;
  color: #fff; background-color: #ee8102; border-radius: 8px;cursor: pointer;
}
@media (max-width: 960px) {
  #myRecipientListWrap > .titleWrap > .link_notice_wrap {
    position: static; margin-bottom: 20px;height:auto;
  }
}
</style>

<!-- 210204 수급자목록 -->
<div id="myRecipientListWrap">
  <div class="titleWrap" style="margin-bottom:10px;">
    <?php if($links) { ?>
    <div class="link_notice_wrap BottomButton">
      <i class="fa fa-bell-o" aria-hidden="true"></i>
      신규 수급자(<?=get_text($links[0]['rl_pen_name'])?>) 추천되었습니다.
    </div>
    <?php } ?>
    수급자관리
  </div>

  <form id="form_search" method="get">
    <div class="search_box">
      <select name="sel_field" id="sel_field">
        <option value="penNm"<?php if($sel_field == 'penNm' || $sel_field == 'all') echo ' selected'; ?>>수급자명</option>
        <option value="penProNm"<?php if($sel_field == 'penProNm') echo ' selected'; ?>>보호자명</option>
        <option value="penLtmNum"<?php if($sel_field == 'penLtmNum') echo ' selected'; ?>>장기요양번호</option>
      </select>
      <div class="input_search">
          <input name="search" id="search" value="<?=$search?>" type="text">
          <button id="btn_search" type="submit"></button>
      </div>
    </div>
    <?php if($noti_count = get_recipient_noti_count() > 0) { ?>
    <div class="recipient_noti">
    	신규 확인이 필요한 알림 <?=$noti_count?>건이 있습니다.
    	<a href="./my_recipient_noti.php">바로확인</a>
    </div>
    <?php } ?>
    <div class="r_btn_area pc">
      <a href="./my_recipient_write.php" class="btn eroumcare_btn2" title="수급자 등록">수급자 등록</a>
      <a href="./recipientexcel.php" onclick="return excelform(this.href);" target="_blank" class="btn eroumcare_btn2" title="수급자일괄등록">수급자일괄등록</a>
      <a href="./recipientexcel_b.php" onclick="return excelform(this.href);" target="_blank" class="btn eroumcare_btn2" title="B사 엑셀 일괄등록">B사 엑셀 일괄등록</a>
    </div>
    <div class="r_btn_area mobile">
      <a href="./my_recipient_write.php" class="btn eroumcare_btn2" title="수급자 등록">수급자 등록</a>
    </div>
  </form>

  <div class="list_box pc">
    <div class="table_box">  
      <table>
        <tr>
          <th>No.</th>
          <th>수급자 정보</th>
          <th>장기요양정보</th>
          <th>1년사용</th>
          <th>장바구니</th>
          <th>비고</th>
        </tr>
        <?php $i = -1; ?>
        <?php foreach($list as $data){ ?>
        <?php $i++; ?>
        <tr>
          <td>
            <?php echo $total_count - (($page - 1) * $rows) - $i; ?>
          </td>
          <td>
            <a href='<?php echo G5_URL; ?>/shop/my_recipient_view.php?id=<?php echo $data['penId']; ?>'>
              <?php echo $data['penNm']; ?>
              (<?php echo substr($data['penBirth'], 2, 2); ?>년생/<?php echo $data['penGender']; ?>)
              <br/>
              <?php if ($data['penProNm']) { ?>
                보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProConNum'] ? '/' . $data['penProConNum'] : ''; ?>)
              <?php } ?>
            </a>
          </td>
          <td>
            <?php if ($data["penLtmNum"]) { ?>
              <?php echo $data["penLtmNum"]; ?>
              (<?php echo $data["penRecGraNm"]; ?><?php echo $pen_type_cd[$data['penTypeCd']] ? '/' . $pen_type_cd[$data['penTypeCd']] : ''; ?>)
              <br/>
              <?php echo $data['penExpiDtm']; ?>
            <?php }else{ ?>
              예비수급자
            <?php } ?>
          </td>
          <td style="text-align:center;">
            <?php
            $grade_year_info = get_recipient_grade_per_year($data['penId'])
            ?>
            <span class="<?php echo $grade_year_info['sum_price'] > 1400000 ? 'red' : ''; ?>"><?php echo number_format($grade_year_info['sum_price']); ?>원</span>
            <br/>
            계약 <?php echo $grade_year_info['count']; ?>건, 판매 <?php echo $grade_year_info['sell_count']; ?>건, 대여 <?php echo $grade_year_info['borrow_count']; ?>건
          </td>
          <td style="text-align:center;">
            <?php
              $cart_count = get_carts_by_recipient($data['penId']);
              echo $cart_count . '개';
            ?>
            <br/>
            <?php if ($data["penLtmNum"]) { ?>
            <a href="<?php echo G5_SHOP_URL; ?>/connect_recipient.php?pen_id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2 small" title="추가하기">추가하기</a>
            <?php } ?>
          </td>
          <td style="text-align:center;">
            <?php if ($data['recYn'] === 'N') { ?>
              욕구사정기록지 미작성<br/>
              <a href="<?php echo G5_SHOP_URL; ?>/my_recipient_rec_form.php?id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2 small" title="작성하기">작성하기</a>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
      </table>
    </div>
  </div>

  <?php if(!$list){ ?>
  <div class="no_content">
    내용이 없습니다
  </div>
  <?php } ?>

  <?php if($list) { ?>
  <div class="list_box mobile">
    <ul class="li_box">
      <?php foreach ($list as $data) { ?>
      <li>
        <div class="info">
          <a href='<?php echo G5_URL; ?>/shop/my_recipient_view.php?id=<?php echo $data['penId']; ?>'>
            <b>
              <?php echo $data['penNm']; ?>
              (<?php echo substr($data['penBirth'], 2, 2); ?>년생/<?php echo $data['penGender']; ?>)
            </b>
            <?php if ($data['penProNm']) { ?>
            <span class="li_box_protector">
              * 보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProTypeCd'] == '00' ? '/없음' : ''; ?><?php echo $data['penProTypeCd'] == '01' ? '/일반보호자' : ''; ?><?php echo $data['penProTypeCd'] == '02' ? '/요양보호사' : ''; ?>)
            </span>
            <?php } ?>
            <p>
              <?php if ($data["penLtmNum"]) { ?>
              <b>
                <?php echo $data["penLtmNum"]; ?>
                (<?php echo $data["penRecGraNm"]; ?><?php echo $pen_type_cd[$data['penTypeCd']] ? '/' . $pen_type_cd[$data['penTypeCd']] : ''; ?>)
              </b>
              <?php } else { ?>
              예비수급자
              <?php } ?>
            </p>
            <p>
              <b>
                1년사용: 
                <?php
                // 유효기간
                $exp_date = substr($data['penExpiStDtm'], 4, 4);
                $exp_now = date('m') . date('d');
                $exp_year = intval($exp_date) < intval($exp_now) ? intval(date('Y')) : intval(date('Y')) - 1; // 지금날짜보다 크면 올해, 작으면 작년

                $exp_start = date('Y-m-d', strtotime($exp_year . $exp_date));
                $exp_end = date('Y-m-d', strtotime('+ 1 years', strtotime($exp_start)));

                // $count = sql_fetch("SELECT COUNT(*) AS cnt FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2')")['cnt'];

                // 계약건수, 금액
                $contract = sql_fetch("SELECT count(*) as cnt, SUM(it_price) as sum_it_price from eform_document_item edi where edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");
                // 판매 건수
                $contract_sell = sql_fetch("SELECT count(*) as cnt from eform_document_item edi where edi.gubun = '00' and edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");
                // 대여 건수
                $contract_borrow = sql_fetch("SELECT count(*) as cnt from eform_document_item edi where edi.gubun = '01' and edi.dc_id in (SELECT dc_id FROM `eform_document` WHERE penId = '{$data['penId']}' AND dc_status IN ('1', '2') and dc_datetime BETWEEN '{$exp_start}' AND '{$exp_end}')");

                ?>
                <span class="<?php echo $contract['sum_it_price'] > 1400000 ? 'red' : ''; ?>"><?php echo number_format($contract['sum_it_price']); ?>원</span>
              </b>
              <span style="font-size:0.9em;">
                계약 <?php echo $contract['cnt']; ?>건, 판매 <?php echo $contract_sell['cnt']; ?>건, 대여 <?php echo $contract_borrow['cnt']; ?>건
              </span>
            </p>
          </a>
          <?php if ($data['recYn'] === 'N') { ?>
          <a href="<?php echo G5_SHOP_URL; ?>/my_recipient_rec_form.php?id=<?php echo $data['penId']; ?>" class="btn eroumcare_btn2" style="margin-top:10px;" title="작성하기">욕구사정기록지 작성</a>
          <?php } ?>
        </div>
        <?php if ($data["penLtmNum"]) { ?>
        <a href="<?php echo G5_SHOP_URL; ?>/connect_recipient.php?pen_id=<?php echo $data['penId']; ?>" class="li_box_right_btn" title="추가하기">
          장바구니
          <br/>
          <b><?php echo get_carts_by_recipient($data['penId']) . '개'; ?></b>
        </a>
        <?php } ?>
      </li>
      <?php } ?>
    </ul>
  </div>
  <?php } ?>

  <div class="list-paging">
    <ul class="pagination pagination-sm en">
      <?php echo apms_paging($write_pages, $page, $total_page, "?sel_field={$sel_field}&search={$search}&page_spare={$page_spare}&page="); ?>
    </ul>
  </div>

  <div class="titleWrap" style="margin-bottom:10px;">
    예비수급자관리
  </div>

  <div class="list_box pc">
    <div class="table_box">  
      <table>
        <tr>
          <th>No.</th>
          <th>수급자 정보</th>
          <th>장기요양정보</th>
          <th>비고</th>
        </tr>
        <?php $i = -1; ?>
        <?php foreach($list_spare as $data) { ?>
        <?php $i++; ?>
        <tr>
          <td>
            <?php echo $total_count_spare - (($page_spare - 1) * $rows) - $i; ?>
          </td>
          <td>
            <a href="<?=G5_SHOP_URL?>/my_recipient_update.php?penSpare=1&id=<?=$data['penId']?>">
              <?php echo $data['penNm']; ?>
              <?php
              $pen_desc_txt = '';
              if(substr($data['penBirth'], 2, 2)) $pen_desc_txt .= substr($data['penBirth'], 2, 2).'년생';
              if($pen_desc_txt) $pen_desc_txt .= '/';
              if($data['penGender']) $pen_desc_txt .= $data['penGender'];
              if($pen_desc_txt) $pen_desc_txt = ' (' . $pen_desc_txt . ')';
              echo $pen_desc_txt;
              ?>
              <br/>
              <?php if ($data['penProNm']) { ?>
                보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProConNum'] ? '/' . $data['penProConNum'] : ''; ?>)
              <?php } ?>
            </a>
          </td>
          <td>
            예비수급자
          </td>
          <td style="text-align:center;">
          </td>
        </tr>
        <?php } ?>
      </table>
    </div>
  </div>

  <?php if(!$list_spare) { ?>
  <div class="no_content">
    내용이 없습니다
  </div>
  <?php } ?>

  <?php if($list_spare) { ?>
  <div class="list_box mobile">
    <ul class="li_box">
      <?php foreach ($list_spare as $data) { ?>
      <li>
        <div class="info">
          <a href="<?=G5_SHOP_URL?>/my_recipient_update.php?penSpare=1&id=<?=$data['penId']?>">
            <b>
              <?php echo $data['penNm']; ?>
              <?php
              $pen_desc_txt = '';
              if(substr($data['penBirth'], 2, 2)) $pen_desc_txt .= substr($data['penBirth'], 2, 2).'년생';
              if($pen_desc_txt) $pen_desc_txt .= '/';
              if($data['penGender']) $pen_desc_txt .= $data['penGender'];
              if($pen_desc_txt) $pen_desc_txt = ' (' . $pen_desc_txt . ')';
              echo $pen_desc_txt;
              ?>
            </b>
            <?php if ($data['penProNm']) { ?>
            <span class="li_box_protector">
              * 보호자(<?php echo $data['penProNm']; ?><?php echo $data['penProTypeCd'] == '00' ? '/없음' : ''; ?><?php echo $data['penProTypeCd'] == '01' ? '/일반보호자' : ''; ?><?php echo $data['penProTypeCd'] == '02' ? '/요양보호사' : ''; ?>)
            </span>
            <?php } ?>
            <p>
              예비수급자
            </p>
          </a>
        </div>
      </li>
      <?php } ?>
    </ul>
  </div>
  <?php } ?>

  <div class="list-paging">
    <ul class="pagination pagination-sm en">
      <?php echo apms_paging($write_pages, $page_spare, $total_page_spare, "?sel_field={$sel_field}&search={$search}&page={$page}&page_spare="); ?>
    </ul>
  </div>

  <?php
  if($links) {
  ?>
  <div class="titleWrap" style="margin-bottom:10px;">
    대기중인 수급자관리
  </div>
  <div class="list_box pc">
    <div class="table_box">  
      <table id="tb_links">
        <thead>
          <tr>
            <th scope="col">No.</th>
            <th scope="col">수급자명</th>
            <th scope="col">인정정보</th>
            <th scope="col">주소</th>
            <th scope="col">연락처</th>
            <th scope="col">보호자정보</th>
            <th scope="col">연결일시(3일 후 자동취소)</th>
          </tr>
        </thead>
        <tbody>
          <?php
          for($i = 0; $i < count($links); $i++) {
          $rl = $links[$i];
          ?>
          <tr data-id="<?=$rl['rl_id']?>">
            <td><?=count($links) - $i?></td>
            <td style="text-align:center;"><?=get_text($rl['rl_pen_name'])?></td>
            <td style="text-align:center;"><?=$rl['rl_pen_ltm_num'] ? get_text('L'.$rl['rl_pen_ltm_num']) : '예비'?></td>
            <td style="max-width:300px;width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <?=get_text($rl['rl_pen_addr1'])?>
              <?=get_text($rl['rl_pen_addr2'])?>
              <?=get_text($rl['rl_pen_addr3'])?>
            </td>
            <td style="text-align:center;"><?=get_text($rl['rl_pen_hp'])?></td>
            <td style="text-align:center;">
              <?=get_text($rl['rl_pen_pro_name'])?>
              (<?=get_text($rl['rl_pen_pro_hp'])?>)
            </td>
            <td style="text-align:center;">
              <?php
              if($rl['status'] == 'request') {
                echo '미연결';
              } else {
                echo date('Y-m-d', strtotime($rl['updated_at']));
              }
              ?>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="list_box mobile">
    <ul id="ul_links" class="li_box">
      <?php
      for($i = 0; $i < count($links); $i++) {
        $rl = $links[$i];
      ?>
      <li data-id="<?=$rl['rl_id']?>">
        <div class="info">
          <b>
            <?=get_text($rl['rl_pen_name'])?>
          </b>
          <?php if ($rl['rl_pen_pro_name']) { ?>
          <span class="li_box_protector">
            * 보호자(<?=get_text($rl['rl_pen_pro_name'])?> / <?=get_text($rl['rl_pen_pro_hp'])?>)
          </span>
          <?php } ?>
          <p>
            <?=$rl['rl_pen_ltm_num'] ? get_text('L'.$rl['rl_pen_ltm_num']) : '예비'?>
          </p>
          <p>
            <b>
              <?=get_text($rl['rl_pen_addr1'])?>
              <?=get_text($rl['rl_pen_addr2'])?>
              <?=get_text($rl['rl_pen_addr3'])?>
            </b>
          </p>
          <p>
            <b>연결일시: </b>
            <span style="font-size:0.9em;">
              <?php
              if($rl['status'] == 'request') {
                echo '미연결';
              } else {
                echo date('Y-m-d', strtotime($rl['updated_at']));
              }
              ?>
            </span>
          </p>
        </div>
      </li>
      <?php } ?>
    </ul>
  </div>
  <div id="popup_recipient_link">
    <div></div>
  </div>
  <style>
  #tb_links td, #ul_links li { cursor: pointer }
  #tb_links tr:hover, #tb_links tr:active, #ul_links li:hover, #ul_links li:active { background-color: #f5f5f5; }
  #popup_recipient_link { position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
  #popup_recipient_link > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
  #popup_recipient_link iframe { position: relative; width: 1024px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -512px; }
  #popup_recipient_link iframe.mini { width: 600px; margin-left: -300px; }
  @media (max-width : 1240px){
    #popup_recipient_link iframe, #popup_recipient_link iframe.mini { width: 100%; height: 100%; left: 0; margin-left: 0; }
  }
  </style>
  <script>
  $(function() {
    $("#popup_recipient_link").hide();
	  $("#popup_recipient_link").css("opacity", 1);

    $('#tb_links td').click(function(e) {
      var rl_id = $(this).closest('tr').data('id');
      $("#popup_recipient_link > div").html("<iframe src='my_recipient_link.php?rl_id="+rl_id+"'>");
      $("#popup_recipient_link iframe").removeClass('mini');
      $("#popup_recipient_link iframe").load(function() {
        $("body").addClass('modal-open');
        $("#popup_recipient_link").show();
      });
    });

    $('#ul_links li').click(function(e) {
      var rl_id = $(this).data('id');
      $("#popup_recipient_link > div").html("<iframe src='my_recipient_link.php?rl_id="+rl_id+"'>");
      $("#popup_recipient_link iframe").removeClass('mini');
      $("#popup_recipient_link iframe").load(function() {
        $("body").addClass('modal-open');
        $("#popup_recipient_link").show();
      });
    });
  });
  </script>
  <?php } ?>
</div>

<?php include_once("./_tail.php"); ?>
