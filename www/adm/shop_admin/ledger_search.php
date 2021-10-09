<?php
$sub_menu = '400460';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '거래처원장';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$qstr = "";
$where = [];

# 영업담당자
if(!$mb_manager)
  $mb_manager = [];
$where_manager = [];
if(!$mb_manager_all && $mb_manager) {
  foreach($mb_manager as $man) {
    $qstr .= "mb_manager%5B%5D={$man}&amp;";
    $where_manager[] = " mb_manager = '$man' ";
  }
  $where[] = ' ( ' . implode(' or ', $where_manager) . ' ) ';
}
$manager_result = sql_query("
  SELECT
    a.mb_id,
    m.mb_name
  FROM
    g5_auth a
  LEFT JOIN
    g5_member m ON a.mb_id = m.mb_id
  WHERE
    au_menu = '400400' and
    au_auth LIKE '%w%'
");
$managers = [];
while($manager = sql_fetch_array($manager_result)) {
  $managers[$manager['mb_id']] = $manager['mb_name'];
}

# 검색어
$search = get_search_string($search);
if($search)
  $where[] = " mb_entNm LIKE '%{$search}%' ";

$sql_search = '';
if($where) {
  $sql_search = ' and '.implode(' and ', $where);
}

$sql_common = "
  FROM
    g5_member m
  WHERE
    mb_level IN (3, 4)
    {$sql_search}
";

// 총 개수 구하기
$total_count = sql_fetch(" SELECT count(*) as cnt {$sql_common} ")['cnt'];
$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit {$from_record}, {$page_rows} ";

$ent_result = sql_query("
  SELECT
    mb_id,
    mb_email,
    mb_fax,
    mb_entNm,
    (
      SELECT mb_name from g5_member WHERE mb_id = m.mb_manager
    ) as mb_manager
  {$sql_common}
  ORDER BY
    mb_entNm ASC
  {$sql_limit}
");

$ents = [];
$index = $from_record;
while($row = sql_fetch_array($ent_result)) {
  $row['index'] = ++$index;
  $row['sales'] = get_outstanding_balance($row['mb_id'], null, true);
  $row['balance'] = get_outstanding_balance($row['mb_id']);
  $ents[] = $row;
}

$qstr .= "search={$search}";

function get_ledger_history_lastmonth($mb_id) {
  //지난달 데이터 가져오기
  $ledger_result = sql_fetch("
    SELECT * FROM g5_send_ledger_history 
    WHERE YEAR(send_date) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
      AND MONTH(send_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
      AND mb_id = '{$mb_id}'
    ORDER BY send_date DESC 
    LIMIT 1;
  ");
  return $ledger_result;
}

function get_ledger_history_thismonth($mb_id) {
  //이번달 데이터 가져오기
  $ledger_result = sql_fetch("
    SELECT * FROM g5_send_ledger_history 
    WHERE YEAR(send_date) = YEAR(CURRENT_DATE)
      AND MONTH(send_date) = MONTH(CURRENT_DATE)
      AND mb_id = '{$mb_id}'
    ORDER BY send_date DESC 
    LIMIT 1;
  ");
  return $ledger_result;
}

function get_ledger_history_recent($mb_id) {
  //가장 마지막 데이터 가져오기
  $email_result = sql_fetch("
    SELECT * FROM g5_send_ledger_history 
    WHERE mb_id = '{$mb_id}'
    AND send_type = 'E'
    ORDER BY id DESC 
    LIMIT 1;
  ");
  
  //가장 마지막 데이터 가져오기
  $fax_result = sql_fetch("
    SELECT * FROM g5_send_ledger_history 
    WHERE mb_id = '{$mb_id}'
    AND send_type = 'F'
    ORDER BY id DESC 
    LIMIT 1;
  ");
  
  $result = array(
    'email_result' => $email_result,
    'fax_result' => $fax_result
  );

  return $result;
}

?>

<style type="text/css">
.td_sendledger_date {width:150px;text-align:left;}
.td_sendledger {width: 550px;text-align:left;}
.td_sendledger input[type=text] {
    margin-left: 5px;
    padding: 5px;
    border: 1px solid #ddd;
    height: 26px;
    font-size: 12px;
}
.td_sendledger input[type=text]:disabled { background-color: #ddd; }

.td_sendledger button {    
    margin-left: 5px;
    margin-right: 10px;
    width: 84px;
    height: 32px;
    border: 1px solid #ddd;
    text-align: center;
    background: #fff;
    padding: 8px;
}

.ajax-loader {
  visibility: hidden;
  background-color: rgba(255,255,255,0.7);
  position: absolute;
  z-index: +100 !important;
  width: 100%;
  height:100%;
}

.ajax-loader img {
  position: relative;
  top:50%;
  left:50%;
  transform: translate(-50%, -50%);
}
</style>

<div class="new_form">
  <div class="ajax-loader">
    <img src="img/ajax-loading.gif" class="img-responsive" />
  </div>
  <form method="get">
    <table class="new_form_table">
      <tbody>
        <tr>
          <th>영업담당자</th>
          <td>
            <input type="checkbox" name="mb_manager_all" value="1" id="chk_mb_manager_all" <?php if(!array_diff(array_keys($managers), $mb_manager)) echo 'checked'; ?>>
            <label for="chk_mb_manager_all">전체</label>
            <?php foreach($managers as $mb_id => $mb_name) { ?>
            <input type="checkbox" name="mb_manager[]" value="<?=$mb_id?>" id="manager_<?=$mb_id?>" class="chk_mb_manager" <?php if(in_array($mb_id, $mb_manager)) echo 'checked'; ?>>
            <label for="manager_<?=$mb_id?>"><?=$mb_name?></label>
            <?php } ?>
          </td>
        </tr>
        <tr>
          <th>사업소명</th>
          <td>
            <input type="text" name="search" value="<?=$search?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
          </td>
        </tr>
      </tbody>
    </table>
    <div class="submit">
      <button type="submit" id="search-btn"><span>검색</span></button>
    </div>
  </form>
</div>
<div class="r_btn_area" style="margin: 0px 20px 20px 0px;">
	<span style="color:#FF6600">	*이번달 10일까지는 지난달 거래처 원장이 발송됩니다. </span>
  <a href="javascript::" onclick="save_send_type()" style="padding: 8px 12px 8px 12px;">선택한 전송방법 저장</a>
  <a href="javascript::" onclick="send_all_at_once()" style="padding: 8px 12px 8px 12px; margin-left: 8px; background-color: #666; color: white;">선택한 거래처 일괄전송</a>
</div>
<div class="tbl_head01 tbl_wrap">
  <table>
    <thead>
      <tr>
        <th id="mb_list_chk">
          <label for="ent_chk_all" class="sound_only">사업소 전체</label>
          <input type="checkbox" name="ent_chk_all" value="1" id="ent_chk_all">
        </th>
        <th>No.</th>
        <th>사업소명</th>
        <th>영업담당자</th>
        <th>총 구매액</th>
        <th>총 미수금</th>
        <th>저번달(<?php echo date('m월', strtotime('-1 month', time()));?>) 전송일시</th>
        <th>이번달(<?php echo date('m월');?>) 전송일시</th>
        <th>거래처 전송</th>
        <th>선택</th>
      </tr>
    </thead>
    <tbody>
      <?php if(!$ents) { ?>
      <tr>
        <td colspan="5" class="empty_table">자료가 없습니다.</td>
      </tr>
      <?php } ?>
      <?php foreach($ents as $ent) { ?>
        <?php $ledger_last_month = get_ledger_history_lastmonth($ent['mb_id'])?>
        <?php $ledger_this_month = get_ledger_history_thismonth($ent['mb_id'])?>
        <?php $ledger_recent = get_ledger_history_recent($ent['mb_id'])?>
        <?php $btn_value_str = htmlspecialchars(json_encode(array("index" => $ent['index'], "ent_name" => $ent['mb_entNm'] ?: $ent_mb['mb_giup_bname'] ?: $ent_mb['mb_name'], "mb_manager" => $ent['mb_manager'], "ent_id" => $ent['mb_id'])))?>
      <?php $ent_mb = get_member($ent['mb_id']); ?>
      <tr>
        <td class="td_chk" id="mb_list_spare_chk">
          <input type="checkbox" name="ent_chk[]" value="<?=$ent['index']?>" class="ent_chk">
        </td>
        <td class="td_cntsmall"><?=$ent['index']?></td>
        <td><?php echo $ent['mb_entNm'] ?: $ent_mb['mb_giup_bname'] ?: $ent_mb['mb_name']; ?></td>
        <td class="td_payby"><?=$ent['mb_manager']?></td>
        <td class="td_numsum"><?=number_format($ent['sales'])?></td>
        <td class="td_numsum"><?=number_format($ent['balance'])?></td>
        <td class="td_sendledger_date"><?php echo $ledger_last_month['send_date'] ? $ledger_last_month['send_date'] : '미전송'?></td>
        <td class="td_sendledger_date"><?php echo $ledger_this_month['send_date'] ? $ledger_this_month['send_date'] : '미전송'?></td>
        <td class="td_sendledger">
          <input type="hidden" id="mb_manager_<?=$ent['index']?>" value="<?=$ent['mb_manager']?>">
          <input type="hidden" id="mb_entNm_<?=$ent['index']?>" value="<?=$ent['mb_entNm']?>">
          <!-- 이메일 -->
          <label for="send_email_chk_<?=$ent['index']?>" class="sound_only"></label>
          <input type="checkbox" value="<?=$ent['index']?>" class="send_email_chk" <?php echo ($ledger_recent['email_result'] != NULL && $ledger_recent['email_result']['receiver']) ? 'checked' : ''?>>
          <input type="hidden" id="send_email_mb_id_<?=$ent['index']?>" value="<?=$ent['mb_id']?>">
          <input type="text" id="send_email_<?=$ent['index']?>" value="<?=$ledger_recent['email_result']['receiver'] ?: $ent['mb_email']?>" <?php echo ($ledger_recent['email_result'] != NULL && $ledger_recent['email_result']['receiver']) ? '' : 'disabled'?>>
          <button value="<?php echo $btn_value_str?>" onclick="send_email(this)">이메일 전송</button>
          <!-- 이메일 -->
          <!-- 팩스 -->
          <label for="send_fax_chk_<?=$ent['index']?>" class="sound_only"></label>
          <input type="checkbox" value="<?=$ent['index']?>" class="send_fax_chk" <?php echo ($ledger_recent['fax_result'] != NULL && $ledger_recent['fax_result']['receiver']) ? 'checked' : ''?>>
          <input type="hidden" id="send_fax_mb_id_<?=$ent['index']?>" value="<?=$ent['mb_id']?>">
          <input type="text" id="send_fax_<?=$ent['index']?>" value="<?=$ledger_recent['fax_result']['receiver'] ?: $ent['mb_fax']?>" <?php echo ($ledger_recent['fax_result'] != NULL && $ledger_recent['fax_result']['receiver']) ? '' : 'disabled'?>>
          <button value="<?php echo $btn_value_str?>" onclick="send_fax(this)">웹팩스 전송</button>
          <!-- 팩스 -->
        </td>
        <td class="td_mng_s td_center"><a href="<?=G5_ADMIN_URL?>/shop_admin/ledger_list.php?mb_id=<?=$ent['mb_id']?>" class="btn btn_03">선택</a></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
</div>
<div class="l_btn_area" style="margin: 20px;">
  <a href="./downloadledgerexcel.php" style="padding: 8px 12px 8px 12px;">수금등록 일괄 업로드 양식 다운로드</a>
  <a href="./uploadledgerexcel.php" onclick="return excelform(this.href);" target="_blank" style="padding: 8px 12px 8px 12px;">수금등록 일괄 업로드</a>
</div>

<script>
function excelform(url)
{
    var opt = "width=600,height=450,left=10,top=10";
    window.open(url, "win_excel", opt);
    return false;
}

$(function() {
  // 영업담당자 - 전체 버튼
  $('#chk_mb_manager_all').change(function() {
    var checked = $(this).is(":checked");
    $(".chk_mb_manager").prop('checked', checked);
  });
  // 영업담당자 - 영업담당자 버튼
  $('.chk_mb_manager').change(function() {
    var total = $('.chk_mb_manager').length;
    var checkedTotal = $('.chk_mb_manager:checked').length;
    $("#chk_mb_manager_all").prop('checked', total <= checkedTotal); 
  });

  //거래처 전체선택
  $('#ent_chk_all').change(function() {
    var checked = $(this).is(":checked");
    $(".ent_chk").prop('checked', checked);
  });

  $('.send_email_chk').change(function() {
    var value = $(this).val();
    var checked = $(this).is(":checked");
    $("#send_email_" + value).attr('disabled', !checked);
  });
  $('.send_fax_chk').change(function() {
    var value = $(this).val();
    var checked = $(this).is(":checked");
    $("#send_fax_" + value).attr('disabled', !checked);
  });
});

function save_send_type() {
  var arr = new Array();
  $('.send_email_chk').each(function() {
    var index = $(this).val();
    if ($(this).is(":checked")) {
      var mb_id = $("#send_email_mb_id_" + index).val();
      if (mb_id) {
        var dict = {};
        dict['send_type'] = "E";
        dict['receiver'] = $("#send_email_" + index).val();
        dict['mb_id'] = mb_id;
        arr.push(dict);
      }
    }
  });
  $('.send_fax_chk').each(function() {
    var index = $(this).val();
    if ($(this).is(":checked")) {
      var mb_id = $("#send_fax_mb_id_" + index).val();
      if (mb_id) {
        var dict = {};
        dict['send_type'] = "F";
        dict['receiver'] = $("#send_fax_" + index).val();
        dict['mb_id'] = mb_id;
        arr.push(dict);
      }
    }
  });

  $.ajax({
    type: "POST",
    url: "/adm/shop_admin/ajax.ledger.send.type.save.php",
    data: {
      arr: arr
    },
    dataType: "json",
    async: false,     
    success: function(data) {
        alert(data);
        location.reload();
    },
    error: function (request, status, error) {
      console.log(request.responseText);
        alert("요청 중 에러가 발생했습니다.");
    }
  });
  // console.log(arr);
}

function send_all_at_once() {
  var send_data = new Array();
  $('.ent_chk').each(function() {
    if ($(this).is(":checked")) {
      var index = $(this).val();
      var mb_manager = $('#mb_manager_' + index).val();
      var mb_entNm = $('#mb_entNm_' + index).val();
      $('.send_email_chk').each(function() {
        var email_chk_index = $(this).val();
        if (index == email_chk_index && $(this).is(":checked")) {
          var mb_id = $("#send_email_mb_id_" + index).val();
          if (mb_id) {
            var dict = {};
            dict['send_type'] = "E";
            dict['receiver'] = $("#send_email_" + index).val();
            dict['ent_id'] = mb_id;
            dict['mb_manager'] = mb_manager;
            dict['ent_name'] = mb_entNm;
            send_data.push(dict);
          }
        }
      });
      $('.send_fax_chk').each(function() {
        var fax_chk_index = $(this).val();
        if (index == fax_chk_index && $(this).is(":checked")) {
          var mb_id = $("#send_fax_mb_id_" + index).val();
          if (mb_id) {
            var dict = {};
            dict['send_type'] = "F";
            dict['receiver'] = $("#send_fax_" + index).val();
            dict['ent_id'] = mb_id;
            dict['mb_manager'] = mb_manager;
            dict['ent_name'] = mb_entNm;
            send_data.push(dict);
          }
        }
      });
    }
  });
  // console.log(send_data);
  request_ajax_send(send_data);
}

function send_email(btn) {
  var btn_value = JSON.parse(btn.value);
  var index = btn_value["index"];
  var email = $('#send_email_' + index).val();
  var ent_id = btn_value["ent_id"];
  var ent_name = btn_value["ent_name"];
  var mb_manager = btn_value["mb_manager"];

  if (!email) {
    return false;
  }
  var send_data = new Array();
  send_data.push({
      ent_id: ent_id,
      ent_name: ent_name,
      mb_manager: mb_manager,
      send_type: 'E',
      receiver: email
  })
  request_ajax_send(send_data);
}

function send_fax(btn) {
  var btn_value = JSON.parse(btn.value);
  var index = btn_value["index"];
  var fax = $('#send_fax_' + index).val();
  var ent_id = btn_value["ent_id"];
  var ent_name = btn_value["ent_name"];
  var mb_manager = btn_value["mb_manager"];

  if (!fax) {
    return false;
  }
  var send_data = new Array();
  send_data.push({
      ent_id: ent_id,
      ent_name: ent_name,
      mb_manager: mb_manager,
      send_type: 'F',
      receiver: fax
  })
  request_ajax_send(send_data);
}

function request_ajax_send(send_data) {
  $.ajax({
      method: "POST",
      url: "/adm/shop_admin/ajax.ledger.send.php",
      data: {
          'send_data': send_data
      },
      beforeSend : function() {
          $('.ajax-loader').css("visibility", "visible");
      },
  })
  .done(function(data) {
    $('.ajax-loader').css("visibility", "hidden");
    if ( data.msg ) {
        alert(data.msg);
    }
    if ( data.result === 'success' ) {
        location.reload();
    }
  })
}

function upload_ledger_excel(send_data) {
  $.ajax({
      method: "POST",
      url: "/adm/shop_admin/ajax.ledger.send.php",
      data: {
          'send_data': send_data
      },
      beforeSend : function() {
          $('.ajax-loader').css("visibility", "visible");
      },
  })
  .done(function(data) {
    $('.ajax-loader').css("visibility", "hidden");
    if ( data.msg ) {
        alert(data.msg);
    }
    if ( data.result === 'success' ) {
        location.reload();
    }
  })
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
