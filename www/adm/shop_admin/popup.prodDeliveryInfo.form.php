<?php
include_once("./_common.php");

$g5["title"] = "주문 내역 바코드 수정";
include_once(G5_ADMIN_PATH."/admin.head.php");

$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
$prodList = [];
$prodListCnt = 0;
$deliveryTotalCnt = 0;

$carts = get_carts_by_od_id($od_id, 'Y');
if ($_GET['show_release_ready_only']) {
  $show_release_ready_only = $_GET['show_release_ready_only'];  
}
else {
  $show_release_ready_only = 'Y';
}
// $carts = get_carts_by_od_id($od_id, 'Y', " AND ct_status = '출고준비' ", null);

$show_direct_delivery_only = $_COOKIE['show_direct_delivery_only'];

$delivery_cnt = 0; // 배송목록 카운트
$delivery_input_cnt = 0; // 입력
$edi_success_cnt = 0; // 전송
$edi_return_cnt = 0; // 송장

$warehouse_list = get_warehouses();

foreach($carts as $c) { 
  foreach($c['options'] as $opt) {
    if (!$opt['ct_combine_ct_id']) {
      $delivery_cnt++;

      if($opt['ct_delivery_company'] === 'ilogen' && $opt['ct_delivery_cnt'] > 0) {
        $delivery_input_cnt++;
      }

      if($opt['ct_edi_result'] == '1') {
        $edi_success_cnt++;
      }

      if($opt['ct_delivery_num']) {
        $edi_return_cnt++;
      }
    }
  }
}


$deliveryCntBtnWord = " 입력 ({$delivery_input_cnt}/". $delivery_cnt .")";
$deliveryCntBtnWord .= ", 전송 ({$edi_success_cnt}/". $delivery_cnt .")";
$deliveryCntBtnWord .= ", 송장 ({$edi_return_cnt}/". $delivery_cnt .")";

// 파트너 회원
$partners = get_partner_members();
?>

<style>
  
  #hd, #text_size, .page_title, #ft { display: none; }
  html, body { width: 100%; height: 100%; min-width: 100%; float: left; margin: 0 !important; padding: 0 !important; overflow: hidden; }
  #tbl_wrap { top: 0; min-height: 100%; }
  #wrapper { min-height: 100%; padding: 0; border: 0; }
  
  .barNumGuideBox { position: absolute; width: 380px; border: 1px solid #DDD; background-color: #FFF; text-align: left; padding: 15px 20px; display: none; margin-left: 35px; margin-top: 5px; right: 10px; }
  .barNumGuideBox > .title { width: 100%; font-weight: bold; margin-bottom: 15px; position: relative; }
  .barNumGuideBox > .title > button { float: right; }
  .barNumGuideBox > p { width: 100%; padding: 0; }
  
  #container { position: absolute; width: 100%; height: 100%; left: 0; top: 0; }
  #prodBarNumFormWrap { width: 100%; height: calc(100% - 60px); float: left; overflow: auto; }
  
  #prodBarNumFormWrap > .titleWrap { width: 100%; float: left; font-weight: bold; font-size: 21px; padding: 20px; }
  #prodBarNumFormWrap > .titleWrap span {
    font-size: 12px;
    vertical-align: middle;
    font-weight: normal;
    padding-left: 10px;
  }
  #prodBarNumFormWrap > .titleWrap .btn_boxpacker, .btn_boxpacker_apply {
    display: block;
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 8px 12px;
    background: #3366cc;
    border-radius: 5px;
    color: #fff;
    font-size: 16px;
  }
  .btn_boxpacker_apply { background: #333; right: 145px; }
  .boxpacker_load{ width: 100px; }
  .boxpacker_wr { display: none; text-align: center; }
  #boxpacker_ta { border: 1px solid #ddd; padding: 10px; color: #333; }
  
  #prodBarNumFormWrap > .tableWrap { width: 100%; float: left; }
  #prodBarNumFormWrap > .tableWrap > table { width: 100%; float: left; table-layout: fixed; }
  #prodBarNumFormWrap > .tableWrap > table thead > tr > th { border-top: 1px solid #3366CC; border-bottom: 1px solid #3366CC; padding: 10px 0; font-weight: bold; font-size: 13px; }
  #prodBarNumFormWrap > .tableWrap > table tbody > tr > td { border-left: 0; border-right: 0; padding: 10px; vertical-align: top; }
  #prodBarNumFormWrap > .tableWrap > table tbody > tr:last-of-type > td { border-bottom: 0; }
  .btn_send_direct_delivery { 
    width: 100px; 
    height: 25px;
    background-color: #3366CC; 
    margin-left: 10px;
    color: white;
  }

  #prodBarNumBtnWrap { width: 100%; height: 60px; float: left; background-color: #F1F1F1; padding: 10px; }
  #prodBarNumBtnWrap > button { width: 100px; height: 40px; line-height: 28px; float: left; font-size: 13px; font-weight: bold; color: #FFF; background-color: #333; margin-left: 5px; }
  #prodBarNumBtnWrap > button:first-of-type { margin-left: 0; }
  #prodBarNumBtnWrap > button.main { width: calc(100% - 105px); background-color: #3366CC; }
  
  .frm_input { width: 100%; font-size: 13px !important; padding: 0 5px; }

  .combine {
    display:none;
  }
  .combine.active {
    display:table-cell;
  }
  .ct_combine_ct_id {
    width:100%;
    text-align-last:center;
  }

  .tr_direct_delivery {
    display: none;
  }

  .tr_direct_delivery.active {
    display: table-row;
  }

  .lotte_api_send {
    height: 30px;
    line-height: 30px !important;
    padding: 0 13px;
    vertical-align: top;
    font-weight: bold;
    letter-spacing: -1px;
    background-color: white;
    border: 1px solid #ff6600;
    color:#ff6600 !important;
  }
  .lotte_api_send:disabled {
    color:#ddd !important;
	  border: 1px solid #b5b5b5;
  }
</style>
  
<form id="prodBarNumFormWrap">
  <input type="hidden" name="od_id" value="<?=$od["od_id"]?>">
  
  <div class="titleWrap">
    배송정보입력 
    <span>
      <?php echo $deliveryCntBtnWord; ?>
    </span>
    <label style="font-size:12px; margin-left:10px;">
      <input type="checkbox" id="show_release_ready_only" <?php echo ($show_release_ready_only == 'Y' ? 'checked' : '')?>> 출고준비만 보기
    </label>
    <label style="font-size:12px; margin-left:10px;">
      <input type="checkbox" id="show_direct_delivery_only" <?php echo ($show_direct_delivery_only == 'Y' ? 'checked' : '')?>> 물류출고만 보기
    </label>
    <button type="button" class="btn_boxpacker_apply" data-apply="1">합포 적용</button>
    <button type="button" class="btn_boxpacker">합포 자동계산</button>
  </div>

  <div class="boxpacker_wr">
    <img src="img/ajax-loading.gif" class="boxpacker_load" />
    <textarea id="boxpacker_ta" cols="30" rows="10"></textarea>
  </div>
  
  <div class="tableWrap">
    <table>
      <colgroup>
        <col width="70px">
        <col width="">
        <col width="10%">
        <col width="130px">
        <col width="100px">
        <col width="20%">
        <col width="70px">
        <col width="70px">
        <col width="70px">
        <col width="100px">
      </colgroup>
      
      <thead>
        <tr>
          <th>상태</th>
          <th>상품(옵션)</th>
          <th>박스수량</th>
          <th>배송비</th>
          <th>분류</th>
          <th>송장번호</th>
          <th></th>
          <th>합포</th>
          <th>위탁</th>
          <th>출하창고</th>
        </tr>
      </thead>
      
      <tbody>
        <?php
        for($i = 0; $i < count($carts); $i++) {
          $options = [];
          if ($show_release_ready_only == 'Y') {
            if ($carts[$i]['ct_status'] == "출고준비") {
              $options = $carts[$i]["options"];
            }
          }
          else {
            $options = $carts[$i]["options"];
          }

          for($k = 0; $k < count($options); $k++) {
            if ($show_direct_delivery_only == 'Y') {
              if ($options[$k]['ct_is_direct_delivery']) continue;
            }
            $delivery_num_arr = explode('|', $options[$k]["ct_delivery_num"]);
        ?>
          <tr data-price="<?=$options[$k]["it_delivery_price"]?>" data-cnt="<?=$options[$k]["it_delivery_cnt"]?>">
            <td><?php echo get_custom_ct_status_text($options[$k]['ct_status']); ?></td>
            <td>
              <input type="hidden" name="ct_id[]" value="<?=$options[$k]["ct_id"]?>">
              <input type="hidden" name="ct_it_name_<?=$options[$k]["ct_id"]?>" value="<?=$carts[$i]["it_name"]?>">
              <input type="hidden" name="ct_status_<?=$options[$k]["ct_id"]?>" value="<?=$carts[$i]["ct_status"]?>">
              <?=stripslashes($carts[$i]["it_name"])?>
              <?php if($carts[$i]["it_name"] != $options[$k]["ct_option"]) { ?>
                (<?=$options[$k]["ct_option"]?>) (<?=$options[$k]["ct_qty"]?>개)
              <?php } else { ?>
                (<?=$options[$k]["ct_qty"]?>개)
              <?php } ?>
            </td>
            <td class="combine combine_n <?php if(!$options[$k]['ct_combine_ct_id']) echo ' active ';?>">
              <input type="number" value ="<?=$options[$k]["ct_delivery_cnt"]?>" class="frm_input ct_delivery_cnt" name="ct_delivery_cnt_<?=$options[$k]["ct_id"]?>" data-it-cnt="<?php echo $options[$k]['it_delivery_cnt']; ?>" data-it-cnt-price="<?php echo $carts[$i]['it_delivery_price']; ?>">
            </td>
            <td class="combine combine_n <?php if(!$options[$k]['ct_combine_ct_id']) echo ' active ';?>">
              <input type="text" value="<?=$options[$k]["ct_delivery_price"]?>" class="frm_input ct_delivery_price" name="ct_delivery_price_<?=$options[$k]["ct_id"]?>" style="width: 80px;">
              <span>원</span>
            </td>
            <td class="combine combine_n <?php if(!$options[$k]['ct_combine_ct_id']) echo ' active ';?>">
              <select id="select_delivery_company" class="frm_input" name="ct_delivery_company_<?=$options[$k]["ct_id"]?>" data-ct-id="<?=$options[$k]["ct_id"]?>">
                <option value="">선택하세요.</option>
              <?php foreach($delivery_companys as $data){ ?>
                <option value="<?=$data["val"]?>" <?=($options[$k]["ct_delivery_company"] == $data["val"]) ? "selected" : ""?>><?=$data["name"]?></option>
              <?php } ?>
              </select>
            </td>
            <td id="td_delivery_num_<?=$options[$k]["ct_id"]?>" class="td_delivery_num combine combine_n <?php if(!$options[$k]['ct_combine_ct_id']) echo ' active ';?>" data-ct_id="<?=$options[$k]["ct_id"]?>" data-box_type="<?=$options[$k]["ct_delivery_box_type"]?>">
              <input type="text" value="<?=$delivery_num_arr[0]?>" class="frm_input" name="ct_delivery_num_<?=$options[$k]["ct_id"]?>[]">
              <?php
                if ($options[$k]['ct_delivery_company'] == 'lotteglogis') {
                  $box_cnt = $options[$k]["ct_delivery_cnt"];
                  for ($m = 1; $m < $box_cnt; $m++) {
                    $delivery_num = '';
                    if ($delivery_num_arr[$m])
                      $delivery_num = $delivery_num_arr[$m];
                    $input_name = "ct_delivery_num_" . $options[$k]["ct_id"] . "[]";
                    echo '<input type="text" value="' . $delivery_num . '" class="frm_input" name="' . $input_name . '">';
                  }
              ?>
                <select class="box_size_option" name="box_size_option_<?=$options[$k]["ct_id"]?>" style="width: 100%">
                  <option value="A" <?php echo $options[$k]["ct_delivery_box_type"] == 'A' ? 'selected' : ''  ?> >A(~100cm ~10kg)</option>
                  <option value="B" <?php echo $options[$k]["ct_delivery_box_type"] == 'B' ? 'selected' : ''  ?> >B(~120cm ~15kg)</option>
                  <option value="C" <?php echo $options[$k]["ct_delivery_box_type"] == 'C' ? 'selected' : '' ?> >C(~140cm ~20kg)</option>
                  <option value="D" <?php echo $options[$k]["ct_delivery_box_type"] == 'D' ? 'selected' : '' ?> >D(~160cm ~25kg)</option>
                  <option value="E" <?php echo $options[$k]["ct_delivery_box_type"] == 'E' ? 'selected' : '' ?> >E(~180cm ~28kg)</option>
                  <option value="F" <?php echo $options[$k]["ct_delivery_box_type"] == 'F' ? 'selected' : '' ?> >F(~200cm ~30kg)</option>
                </select>
              <?php
                }
              ?>
				<?php
                if ($options[$k]['ct_delivery_company'] == 'cjlogistics') {
                  
              ?>
                <select class="box_size_option" name="box_size_option_<?=$options[$k]["ct_id"]?>" style="width: 100%">
                  <option value="극소" <?php echo $options[$k]["ct_delivery_box_type"] == '극소' ? 'selected' : ''  ?> >극소</option>
                  <option value="소" <?php echo $options[$k]["ct_delivery_box_type"] == '소' ? 'selected' : ''  ?> >소</option>
                  <option value="중" <?php echo $options[$k]["ct_delivery_box_type"] == '중' || $options[$k]["ct_delivery_box_type"] == '' ? 'selected' : '' ?> >중</option>
                  <option value="대1" <?php echo $options[$k]["ct_delivery_box_type"] == '대1' ? 'selected' : '' ?> >대1</option>
                  <option value="대2" <?php echo $options[$k]["ct_delivery_box_type"] == '대2' ? 'selected' : '' ?> >대2</option>
                  <option value="이형" <?php echo $options[$k]["ct_delivery_box_type"] == '이형' ? 'selected' : '' ?> >이형</option>
				  <option value="취급제한" <?php echo $options[$k]["ct_delivery_box_type"] == '취급제한' ? 'selected' : '' ?> >취급제한</option>
                </select>
              <?php
                }
              ?>
            </td>
            <td class="combine combine_n <?php if(!$options[$k]['ct_combine_ct_id']) echo ' active ';?>">
              <?php
                $show_btn = false;
                if ($options[$k]['ct_delivery_company'] == 'lotteglogis') { 
                  $show_btn = true;
                }
              ?>
              <button class="lotte_api_send" style="<?php echo $show_btn ? '' : 'display:none;'?>" data-ct-id="<?=$options[$k]["ct_id"]?>" <?php echo ($options[$k]["ct_edi_result"] == 0) ? '' : 'disabled'?>>전송</button>
            </td>
            <td class="combine combine_y <?php if($options[$k]['ct_combine_ct_id']) echo ' active ';?>" colspan="5">
              <select name="ct_combine_ct_id_<?php echo $options[$k]["ct_id"]; ?>" class="ct_combine_ct_id">
                <?php
                foreach($carts as $c) {
                  foreach($c['options'] as $o) {
                    if ($o['ct_id'] === $options[$k]['ct_id']) continue;
                    if ($o['ct_status'] !== '출고준비') continue;
                ?>
                <option value="<?php echo $o['ct_id']; ?>" <?php echo ($options[$k]['ct_combine_ct_id'] === $o['ct_id']) ? ' selected ' : '' ; ?>>
                  <?php
                  echo stripslashes($o["it_name"]);
                  if($c["it_name"] != $o["ct_option"]) {
                    echo ' ('.$o["ct_option"].')';
                  }
                  ?>
                </option>
                <?php
                  }
                }
                ?>
              </select>
            </td>
            <td style="text-align:center;">
              <label>
                <input 
                  type="checkbox" 
                  name="ct_combine_<?php echo $options[$k]["ct_id"]; ?>" 
                  class="chk_ct_combine" 
                  value="1" 
                  <?php if($options[$k]['ct_combine_ct_id']) echo ' checked';?>
                  <?php if(count($options) === 1 && count($carts) === 1) echo ' disabled';?>
                >
                합포
              </label>
            </td>
            <td style="text-align:center;">
              <label>
                <input
                  type="checkbox"
                  name="ct_is_direct_delivery_<?=$options[$k]["ct_id"]?>"
                  class="chk_ct_is_direct_delivery" 
                  value="1"
                  data-id="<?=$options[$k]["ct_id"]?>"
                  <?php if($options[$k]['ct_is_direct_delivery']) echo ' checked';?>
                >
                위탁
              </label>
            </td>
            <td style="text-align:center;">
              <select name="ct_warehouse_<?=$options[$k]["ct_id"]?>" id="ct_warehouse_<?=$options[$k]["ct_id"]?>" class="frm_input ct_warehouse">
                <?php
                foreach($warehouse_list as $warehouse) {
                  echo '<option value="'.$warehouse.'" '.get_selected($options[$k]["ct_warehouse"], $warehouse).'>'.$warehouse.'</option>';
                }
                ?>
              </select>
            </td>
          </tr>
          <tr
            id="tr_direct_delivery_<?=$options[$k]["ct_id"]?>"
            class="tr_direct_delivery <?=($options[$k]['ct_is_direct_delivery'] ? 'active' : '')?>"
            style="background-color:#e3e3e3;"
          >
            <td colspan="10">
              위탁
              <select
                name="ct_is_direct_delivery_sub_<?=$options[$k]["ct_id"]?>"
                class="frm_input"
                style="width: 100px; margin-left: 40px;"
              >
                <option value="1" <?=get_selected($options[$k]['ct_is_direct_delivery'], '1')?>>배송</option>
                <option value="2" <?=get_selected($options[$k]['ct_is_direct_delivery'], '2')?>>설치</option>
              </select>
              <select
                name="ct_direct_delivery_partner_<?=$options[$k]["ct_id"]?>"
                class="frm_input"
                style="width: 100px"
                data-ct-id="<?=$options[$k]["ct_id"]?>"
				onChange="select_wh('<?=$options[$k]["ct_id"]?>',this.value,'<?=$options[$k]["it_id"]?>')";
              >
                <option value="">파트너선택</option>
                <?php foreach($partners as $partner) { ?>
                <option value="<?=$partner['mb_id']?>" <?=get_selected($options[$k]['ct_direct_delivery_partner'], $partner['mb_id'])?>><?=$partner['mb_name']?></option>
                <?php } ?>
              </select>
              1개당 <input type="text" value="<?=$options[$k]['ct_direct_delivery_price']?>" class="frm_input" name="ct_direct_delivery_price_<?=$options[$k]["ct_id"]?>" style="width: 80px;"> 원 (VAT 포함)
              <button type="button" class="btn_send_direct_delivery" id="btn_send_direct_delivery" data-ct-id="<?=$options[$k]["ct_id"]?>">직배송 전송</button>
              <?php $display = ($carts[$i]['ct_send_direct_delivery'] ? "display=''" : "display='none'") ?>
              <?php 
                $text = '';
                if ($carts[$i]['ct_send_direct_delivery']) {
                  if (!$carts[$i]['ct_send_direct_delivery_fax'] && !$carts[$i]['ct_send_direct_delivery_email']) {
                    $text = '발주전송(지정된 전송방법이 없어 전송 실패)';
                  } else if ($carts[$i]['ct_send_direct_delivery_fax'] && !$carts[$i]['ct_send_direct_delivery_email']) {
                    $text = "발주전송(Fax : {$carts[$i]['ct_send_direct_delivery_fax']})";
                  } else if (!$carts[$i]['ct_send_direct_delivery_fax'] && $carts[$i]['ct_send_direct_delivery_email']) {
                    $text = "발주전송(Email : {$carts[$i]['ct_send_direct_delivery_email']})";
                  } else {
                    $text = "발주전송(Fax : {$carts[$i]['ct_send_direct_delivery_fax']}, Email : {$carts[$i]['ct_send_direct_delivery_email']})";
                  }
                }
              ?>
              <span id="send_direct_delivery_result" style="<?=$display?>;"><?=$text?></span>
            </td>
          </tr>
        <?php
          }
        }
        ?>
      </tbody>
    </table>
  </div>
</form>
  
<div id="prodBarNumBtnWrap">
  <button type="button" class="main" id="prodBarNumSaveBtn">저장</button>
  <button type="button" onclick="window.close();">취소</button>
</div>

<script type="text/javascript">
	function select_wh(ct_id,partner,it_id){
		$.ajax({
          method: 'POST',
          url: './ajax.ct_warehouse.php',
          data: {
            partner: partner,
            it_id: it_id,
          }
        }).done(function (data) {
          // return false;
          if (data.ct_wh != '') {
            $("#ct_warehouse_"+ct_id).val(data.ct_wh);//지정 출하창고 선택
          }else{
			$("#ct_warehouse_"+ct_id).val("");//지정 출하창고 없음
		  }
        });
	}
  $(function() {
    // 박스 가격 계산
    $(".ct_delivery_cnt").change(function(){
      var parent = $(this).closest("tr");
      var cnt = $(this).data('it-cnt');
      var price = $(this).data('it-cnt-price');

      var val = $(this).val();
      
      if(cnt){
        var tmpCnt = Math.floor(val / cnt);
        
        if(tmpCnt < (val / cnt)){
          tmpCnt += 1;
        }
        
        $(parent).find(".ct_delivery_price").val(tmpCnt * price);
      }
    });

    // 택배사 변경 
    $("select[id='select_delivery_company']").change(function() {
      var ct_id = $(this).attr('data-ct-id');
      $('#td_delivery_num_' + ct_id).children().not(':first').remove();
	  if (this.value === 'lotteglogis') {
        var box_cnt = $('input[name=ct_delivery_cnt_' + ct_id + ']').val();
        var html = '';
        html += '<input type="text" value="" class="frm_input" name="">';

        for (var i = 1; i < box_cnt; i++) {
          $('#td_delivery_num_' + ct_id).append(html);
          //$('#td_delivery_num_' + ct_id + ' .box_size_option').before(html);
        }

        var boxSizeSelectHtml = '';
        boxSizeSelectHtml += '<select class="box_size_option" name="box_size_option_'+$(this).closest('tr').find('.td_delivery_num').data('ct_id')+' style="width: 100%">';
        boxSizeSelectHtml += '  <option value="A">A(~100cm ~10kg)</option>';
        boxSizeSelectHtml += '  <option value="B">B(~120cm ~15kg)</option>';
        boxSizeSelectHtml += '  <option value="C">C(~140cm ~20kg)</option>';
        boxSizeSelectHtml += '  <option value="D">D(~160cm ~25kg)</option>';
        boxSizeSelectHtml += '  <option value="E">E(~180cm ~28kg)</option>';
        boxSizeSelectHtml += '  <option value="F">F(~200cm ~30kg)</option>';
        boxSizeSelectHtml += '</select>';

        $('#td_delivery_num_' + ct_id).append(boxSizeSelectHtml);

        var boxType = $(this).closest('tr').find('.td_delivery_num').data('box_type');
        if (boxType) {
          $(this).closest('tr').find('.box_size_option').val(boxType);
        }

        $('.lotte_api_send').show();
      }else if (this.value === 'cjlogistics') {//대한통운 선택 시 박스타입 선택 노출

        var boxSizeSelectHtml = '';
        boxSizeSelectHtml += '<select class="box_size_option" name="box_size_option_'+$(this).closest('tr').find('.td_delivery_num').data('ct_id')+'" style="width: 100%">';
        boxSizeSelectHtml += '  <option value="극소">극소</option>';
        boxSizeSelectHtml += '  <option value="소">소</option>';
        boxSizeSelectHtml += '  <option value="중">중</option>';
        boxSizeSelectHtml += '  <option value="대1">대1</option>';
        boxSizeSelectHtml += '  <option value="대2">대2</option>';
        boxSizeSelectHtml += '  <option value="이형">이형</option>';
		boxSizeSelectHtml += '  <option value="취급제한">취급제한</option>';
        boxSizeSelectHtml += '</select>';

        $('#td_delivery_num_' + ct_id).append(boxSizeSelectHtml);

        var boxType = $(this).closest('tr').find('.td_delivery_num').data('box_type');
        if (boxType) {
          $(this).closest('tr').find('.box_size_option').val(boxType);
        }else{
		  $(this).closest('tr').find('.box_size_option').val("중");
		}
   

        
      } else {
        var children = $('#td_delivery_num_' + ct_id).children().length;
        if (children > 1) {
          $('#td_delivery_num_' + ct_id).children().not(':first').remove();
        }
        $('.lotte_api_send').hide();
      }
    });

    // 합포
    $('.chk_ct_combine').click(function() {
      var parent = $(this).closest('tr');

      if ($(this).is(":checked")) {
        $(parent).find('.combine_y').addClass('active');
        $(parent).find('.combine_n').removeClass('active');
        return;
      }

      $(parent).find('.combine_n').addClass('active');
      $(parent).find('.combine_y').removeClass('active');
    });

    // 위탁
    $('.chk_ct_is_direct_delivery').click(function() {
      var ct_id = $(this).data('id');
      if($(this).is(':checked')) {
        $('#tr_direct_delivery_'+ct_id).addClass('active');
      } else {
        $('#tr_direct_delivery_'+ct_id).removeClass('active');
      }
    });

    // 롯데택배 전송
    $(document).on("click", ".lotte_api_send", function(e){
      e.preventDefault();
      var ct_id = $(this).attr('data-ct-id');
      var box_size = $(this).closest('tr').find('.box_size_option').val();

      console.log(box_size);
      return;

      if (!$(this).prop('disabled')) {
        $.ajax({
          method: 'POST',
          url: './ajax.order.delivery.lotte.php',
          data: {
            ct_id: ct_id,
            box_size: box_size,
          }
        }).done(function (data) {
          // return false;
          if (data.result === 'success') {
            location.reload();
          }
        });
      }
    });

    //직배송 전송
    $("#btn_send_direct_delivery").click(function() {
      // console.log($(this).data('ct-id'));
      var ct_id = $(this).data('ct-id');
      var partner_id = $('select[name=ct_direct_delivery_partner_' + ct_id + '] option:selected').val();
      if (!partner_id) {
        alert('파트너를 선택하세요');
        return false;
      }
      // window.open(`ajax.send_direct_delivery.php?ct_id=${ct_id}&partner_id=${partner_id}`);
      // return;
      $.ajax({
          method: "POST",
          url: "ajax.send_direct_delivery.php",
          data: {
            'ct_id': ct_id,
            'partner_id': partner_id
          },
      })
      .done(function(data) {
        if ( data.msg ) {
            alert(data.msg);
        }
        if ( data.result === 'success' ) {
            location.reload();
        }
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      })
    });

    $("#prodBarNumSaveBtn").click(function() {
      var ordId = "<?=$od["ordId"]?>";
      var changeStatus = true;
      var insertBarCnt = 0;

      $.ajax({
        url : "./samhwa_orderform_deliveryInfo_update.php",
        type : "POST",
        async : false,
        data : $("#prodBarNumFormWrap").serialize(),
        success : function(result){
          var data = result.data;
          if(data && window.opener.$('#samhwa_order_list_table').length > 0) {
            for(var i = 0; i < data.length; i++) {
              var row = data[i];
              var $tr = window.opener.$('.tr_'+row['ct_id']);
              if(row['status'] === 'disable') {
                $tr.addClass('complete2');
                $tr.find('a.deliveryCntBtn').addClass('disable').text(row['text']);
              } else {
                $tr.removeClass('complete2');
                $tr.find('a.deliveryCntBtn').removeClass('disable').text(row['text']);
              }
            }
          } else {
            window.opener.location.reload();
            alert("저장이 완료되었습니다.");
          }
          window.close();
        }
      });
    });

    // 합포 자동계산
    $('.btn_boxpacker, .btn_boxpacker_apply').click(function() {
      $('.boxpacker_load').show();
      $('#boxpacker_ta').hide();
      $('.boxpacker_wr').show();

      var apply = $(this).data('apply');

      $.post('ajax.boxpacker.php?od_id=<?=$od_id?>')
      .done(function(result) {
        if(apply) { // 합포 적용
          var boxes = result.data.joinPacked; // 합포추천 박스들
          $.each(boxes, function(index, box) {
            var greatest = 0, target = null;

            // 첫번째 출고준비 상품을 합포 대상으로 설정
            $.each(box.items, function(ct_id, item) {
              var ct_status = parseInt($('input[name="ct_status_' + ct_id + '"]').val());
              if(ct_status == '출고준비') {
                target = ct_id;
                return false;
              }
            });

            // 출고준비 상태가 없다면 가장 박스수량이 많은 상품을 찾아 합포 대상으로 설정
            if (!target) {
              $.each(box.items, function(ct_id, item) {
                var box_qty = parseInt($('input[name="ct_delivery_cnt_' + ct_id + '"]').val());
                if(box_qty > greatest) {
                  greatest = box_qty;
                  target = ct_id;
                }
              });
            }

            // 합포 대상에 합포 적용
            $.each(box.items, function(ct_id, item) {
              var $box_qty = $('input[name="ct_delivery_cnt_' + ct_id + '"]');
              var $price = $('input[name="ct_delivery_price_' + ct_id + '"]');

              var box_qty = parseInt($box_qty.val());
              var price = parseInt($price.val());

              if(box_qty > 1 || ct_id === target) {
                // 박스수량이 여러개인 경우 마지막 한 박스만 합포. 나머지 박스들은 완포임

                var unit_price = parseInt(price / box_qty); // 단가

                // 합포될 배송박스의 수량 및 가격을 뺀다
                box_qty -= 1;
                price = unit_price * box_qty;
                $box_qty.val( box_qty );
                $price.val( price );

                if(ct_id === target) {
                  // 박스가 합포 대상이면 합포박스의 수량 및 배송비를 더함
                  $box_qty.val( box_qty + 1 );
                  $price.val( price + parseInt(box.price) );
                }

                return;
              }

              var $chk_combine = $('input[name="ct_combine_' + ct_id+ '"]');
              var $sel_combine = $('select[name="ct_combine_ct_id_' + ct_id + '"]');

              $sel_combine.val(target).change();
              if(!$chk_combine.prop('checked'))
                $chk_combine.click();
            });
          });
        }

        $('#boxpacker_ta').val(result.data.html).show();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      })
      .always(function() {
        $('.boxpacker_load').hide();
      });
    });

    // 출고준비만 보기
    $("#show_release_ready_only").click(function() {
      let searchParams = new URLSearchParams(window.location.search);
      let param = searchParams.get('show_release_ready_only');
      if ($(this).is(":checked")) {
        if (param == 'N') {
          window.location.search = window.location.search.replace('show_release_ready_only=N', 'show_release_ready_only=Y');
        }
        else {
          window.location.search += '&show_release_ready_only=Y';
        }
      }
      else {
        if (param == 'Y') {
          window.location.search = window.location.search.replace('show_release_ready_only=Y', 'show_release_ready_only=N');
        }
        else {
          window.location.search += '&show_release_ready_only=N';
        }
      }
    });

    // 물류출고만(위탁체크안됨만) 보기
    $("#show_direct_delivery_only").click(function() {
      if ($(this).is(":checked")) {
        $.cookie('show_direct_delivery_only', 'Y', { expires: 365 })
      }
      else {
        $.cookie('show_direct_delivery_only', 'N', { expires: 365 })
      }
      location.reload();
    });
  });
</script>

<?php include_once(G5_ADMIN_PATH."/admin.tail.php"); ?>
