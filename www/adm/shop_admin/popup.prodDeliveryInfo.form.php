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

$delivery_cnt = 0; // 배송목록 카운트
$delivery_input_cnt = 0; // 입력
$edi_success_cnt = 0; // 전송
$edi_return_cnt = 0; // 송장

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
  #prodBarNumFormWrap > .titleWrap .btn_boxpacker {
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
  .boxpacker_load{ width: 100px; }
  .boxpacker_wr { display: none; text-align: center; }
  #boxpacker_ta { border: 1px solid #ddd; padding: 10px; color: #333; }
  
  #prodBarNumFormWrap > .tableWrap { width: 100%; float: left; }
  #prodBarNumFormWrap > .tableWrap > table { width: 100%; float: left; table-layout: fixed; }
  #prodBarNumFormWrap > .tableWrap > table thead > tr > th { border-top: 1px solid #3366CC; border-bottom: 1px solid #3366CC; padding: 10px 0; font-weight: bold; font-size: 13px; }
  #prodBarNumFormWrap > .tableWrap > table tbody > tr > td { border-left: 0; border-right: 0; padding: 10px; vertical-align: top; }
  #prodBarNumFormWrap > .tableWrap > table tbody > tr:last-of-type > td { border-bottom: 0; }
  
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

</style>
  
<form id="prodBarNumFormWrap">
  <input type="hidden" name="od_id" value="<?=$od["od_id"]?>">
  
  <div class="titleWrap">
    배송정보입력
    <span>
      <?php echo $deliveryCntBtnWord; ?>
    </span>
    <button type="button" class="btn_boxpacker">합포 자동계산</button>
  </div>

  <div class="boxpacker_wr">
    <img src="img/ajax-loading.gif" class="boxpacker_load" />
    <textarea id="boxpacker_ta" cols="30" rows="10"></textarea>
  </div>
  
  <div class="tableWrap">
    <table>
      <colgroup>
        <col width="">
        <col width="10%">
        <col width="130px">
        <col width="100px">
        <col width="20%">
        <col width="70px">
        <col width="70px">
        <col width="100px">
      </colgroup>
      
      <thead>
        <tr>
          <th>상품(옵션)</th>
          <th>박스수량</th>
          <th>배송비</th>
          <th>분류</th>
          <th>송장번호</th>
          <th>합포</th>
          <th>위탁</th>
          <th>출하창고</th>
        </tr>
      </thead>
      
      <tbody>
        <?php
        for($i = 0; $i < count($carts); $i++) {
          $options = $carts[$i]["options"];

          for($k = 0; $k < count($options); $k++) {
        ?>
          <tr data-price="<?=$options[$k]["it_delivery_price"]?>" data-cnt="<?=$options[$k]["it_delivery_cnt"]?>">
            <td>
              <input type="hidden" name="ct_id[]" value="<?=$options[$k]["ct_id"]?>">
              <input type="hidden" name="ct_it_name_<?=$options[$k]["ct_id"]?>" value="<?=$carts[$i]["it_name"]?>">
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
              <select class="frm_input" name="ct_delivery_company_<?=$options[$k]["ct_id"]?>">
                <option value="">선택하세요.</option>
              <?php foreach($delivery_companys as $data){ ?>
                <option value="<?=$data["val"]?>" <?=($options[$k]["ct_delivery_company"] == $data["val"]) ? "selected" : ""?>><?=$data["name"]?></option>
              <?php } ?>
              </select>
            </td>
            <td class="combine combine_n <?php if(!$options[$k]['ct_combine_ct_id']) echo ' active ';?>">
              <input type="text" value="<?=$options[$k]["ct_delivery_num"]?>" class="frm_input" name="ct_delivery_num_<?=$options[$k]["ct_id"]?>">
            </td>
            <td class="combine combine_y <?php if($options[$k]['ct_combine_ct_id']) echo ' active ';?>" colspan="4">
              <select name="ct_combine_ct_id_<?php echo $options[$k]["ct_id"]; ?>" class="ct_combine_ct_id">
                <?php
                foreach($carts as $c) {
                  foreach($c['options'] as $o) {
                    if ($o['ct_id'] === $options[$k]['ct_id']) continue;
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
              <select name="ct_warehouse_<?=$options[$k]["ct_id"]?>" class="frm_input ct_warehouse">
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
            <td colspan="8">
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
              >
                <option value="">파트너선택</option>
                <?php foreach($partners as $partner) { ?>
                <option value="<?=$partner['mb_id']?>" <?=get_selected($options[$k]['ct_direct_delivery_partner'], $partner['mb_id'])?>><?=$partner['mb_name']?></option>
                <?php } ?>
              </select>
              1개당 <input type="text" value="<?=$options[$k]['ct_direct_delivery_price']?>" class="frm_input" name="ct_direct_delivery_price_<?=$options[$k]["ct_id"]?>" style="width: 80px;"> 원 (VAT 포함)
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
    $('.btn_boxpacker').click(function() {
      $('.boxpacker_load').show();
      $('#boxpacker_ta').hide();
      $('.boxpacker_wr').show();

      $.post('ajax.boxpacker.php?od_id=<?=$od_id?>')
      .done(function(result) {
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
  });
</script>

<?php include_once(G5_ADMIN_PATH."/admin.tail.php"); ?>
