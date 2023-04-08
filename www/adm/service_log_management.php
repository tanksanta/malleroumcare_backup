<?php
$sub_menu = "200840";
include_once('./_common.php');
ini_set("display_errors", 0);
auth_check($auth[$sub_menu], 'r');

$g5['title'] = '서비스 로그 관리';

$type = $_GET['type'];
if (!$_GET['type']) {
    $type = 'login';
}

$search = '';
if ($_GET['search']) {
    $search = $_GET['search'];
}


include_once('./service_log_management.sub.php');

$page_rows = '15';
if ($_GET['page_rows']) {
    $page_rows = $_GET['page_rows'];
}

$startTime = strtotime($fr_date);
$endTime = strtotime($to_date);

$to_date = "{$to_date}";

$page = $_GET['page']==null ?0 :$_GET['page'];

$all_cnt = 0;

$service_type = [ 'login' => [['로그ID'=>10,'회원ID'=>15,'회원명'=>60,'접속일자'=>15],['regdt'=>'로그인 일자']]
              ,'order' => [['주문ID'=>10,'회원ID'=>15,'회원명'=>50,'관리자주문여부'=>10,'주문생성일자'=>15],['od_time'=>'주문서생성일']]
              ,'eform' => [['계약서ID'=>22,'회원ID'=>15,'회원명'=>33,'계약서생성일'=>15,'계약서서명일'=>15],['dc_datetime'=>'계약서생성일','dc_sign_datetime'=>'계약서서명일']]
              ,'item_msg' => [['제안서ID'=>10,'회원ID'=>15,'회원명'=>45,'계약서생성일'=>15,'계약서서명일'=>15],['ms_created_at'=>'제안서생성일','ml_sent_at'=>'제안서발송일']]
              ,'check_itcare' => [['조회ID'=>10,'회원ID'=>15,'회원명'=>45,'조회번호'=>15,'조회일자'=>15],['occur_date'=>'조회요청일']] ];
$type_data = $service_type[$type];

if ($_GET['sel_date']) {
  $sel_date = $_GET['sel_date'];
  foreach($type_data[1] as $key=>$value){
    if($key == $sel_date) $sel_date_val = $value;
  }
} else {
  $sel_date_val = array_values($type_data[1])[0];
  $sel_date = array_keys($type_data[1])[0];
}

$results = [];
if ($type == 'login') {
    $sql_count = "select count(*) as cnt ";

    $sql_search = "select 
            A.id as '로그ID',
            A.mb_id as '회원ID',
            B.mb_name as '회원이름',
            A.regdt as '로그인일자' ";

    $sql_common = "from g5_statistics A 
            left join g5_member B on B.mb_id = A.mb_id
            where A.type='LOGIN'
            and A.regdt > '{$fr_date}'
            and A.regdt < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
            and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
            order by A.regdt desc ";
}
else if ($type == 'order') {
    $sql_count = "select count(*) as cnt ";

    $sql_search = "select
            A.od_id '주문서ID',
            A.mb_id as '회원ID',
            B.mb_name as '회원이름',
            case when A.od_sales_manager='1202' then 'N' else 'Y' end as '관리자주문여부',
            A.od_time as '생성일자' ";

    $sql_common = "from g5_shop_order A
            left join g5_member B on B.mb_id = A.mb_id
            where od_id>0
            and A.od_time > '{$fr_date}'
            and A.od_time < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
            and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
            order by A.od_time desc ";
}
else if ($type == 'eform') {
    $sql_count = "select count(*) as cnt ";

    $sql_search = "select
              HEX(A.dc_id) as '계약서ID',
              B.mb_id as '회원ID',
              B.mb_name as '회원이름',
              A.dc_datetime as '계약서생성일',
              A.dc_sign_datetime as '계약서서명일' ";

    if($sel_date == 'dc_datetime'){
       $sql_common = "from eform_document A
              left join g5_member B on B.mb_entId=A.entId
              where A.dc_datetime > 0
              and B.mb_id is not null
              and A.dc_datetime > '{$fr_date}'
              and A.dc_datetime < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
              and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
              order by A.dc_datetime desc ";
    } else if ($sel_date == 'dc_sign_datetime'){
       $sql_common = "from eform_document A
              left join g5_member B on B.mb_entId=A.entId
              where A.dc_datetime > 0
              and B.mb_id is not null
              and A.dc_sign_datetime > '{$fr_date}'
              and A.dc_sign_datetime < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
              and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
              order by A.dc_sign_datetime desc ";
    }
}
else if ($type == 'item_msg') {
    $sql_count = "select count(*) as cnt ";

    $sql_search = "select 
                   A.ms_id as '제안서ID',
                   A.mb_id as '회원ID',
                   B.mb_name as '회원이름',
                   A.ms_created_at as '생성일자',
                   C.ml_sent_at as '발송일자' ";

    if($sel_date == 'ms_created_at'){
       $sql_common = "from recipient_item_msg A
              left join g5_member B on A.mb_id=B.mb_id
              left join recipient_item_msg_log C on C.ms_id =A.ms_id 
              where A.ms_created_at > '{$fr_date}'
              and A.ms_created_at < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
              and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
              order by A.ms_created_at desc ";
    } else if ($sel_date == 'ml_sent_at'){
       $sql_common = "from recipient_item_msg A
              left join g5_member B on A.mb_id=B.mb_id
              left join recipient_item_msg_log C on C.ms_id =A.ms_id 
              where C.ml_sent_at > '{$fr_date}'
              and C.ml_sent_at < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
              and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
              order by C.ml_sent_at desc ";
    }
}
else if ($type == 'check_itcare') {
    $sql_count = "select count(*) as cnt ";

    $sql_search = "select 
            A.log_id as '조회ID',
            A.ent_id as '회원ID',
            B.mb_name as '회원명',
            A.pen_id as '조회번호',
            A.occur_date as '조회일자' ";

    $sql_common = "from rep_inquiry_log A
            left join g5_member B on B.mb_id=A.ent_id
            where A.occur_date > '{$fr_date}'
            and A.occur_date <DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
            and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
            order by A.occur_date desc ";
}

$total_count = sql_fetch($sql_count.$sql_common)['cnt'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit {$from_record}, {$page_rows} ";
$results = sql_query("
  {$sql_search}
  {$sql_common}
  {$sql_limit}
");

if (empty($fr_date) || ! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = G5_TIME_YMD;
if (empty($to_date) || ! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = G5_TIME_YMD;

$qstr = "?type={$type}&amp;sel_date={$sel_date}&amp;fr_date={$fr_date}&amp;to_date={$to_date}&amp;sel_field={$sel_field}&amp;search={$search}&amp;page_rows={$page_rows}";

$results_all = sql_query("{$sql_search}
                          {$sql_common}");
$params_td = "";
while ($row = sql_fetch_array($results_all)) {
  $params_td .= '<tr class="bg0">';
  foreach ($row as $key => $value) {
    $value_str = $value != "0000-00-00 00:00:00" ? $value : "";
    $params_td .= '<td class="td_center" style="vertical-align: middle;">' . $value_str . '</td>';
  }
  $params_td .= '</tr>';
}
?>

<script>console.log("<?=$sel_date.':'.$sel_date_val?>");</script>

<style>
.statistics_table {
  table-layout: fixed;
  width: 100%;
  *margin-left: -100px; /*ie7*/
}
.statistics_table td, th {
  vertical-align: top;
  border-top: 1px solid #ccc;
  padding: 10px;
  width: 50px;
}
.fix {
  position: absolute;
  *position: relative; /*ie7*/
  margin-left: -100px;
  width: 100px;
}
.outer {
  position: relative;
}
.tbl_wrap {
  overflow-x: visible;
  overflow-y: visible;
  width: 100%;
}
.tbl_head01 thead th {
    border-color: #555;
    background: #383838;
    color: #fff;
    letter-spacing:0;
}

#stat td {
    background: #f5f5f5;
}
</style>
<div class="outer">

<div class="search_form" style="width: 100%; margin: 10px 0; padding: 0 20px; float: left;">
  <form name="excel" id="excel" class="excel" style="display: none">
    <table class="excel_table">
      <colgroup>
          <?php foreach($type_data[0] as $key => $value) { ?>
				  <col width="<?=$value?>%"/>
          <?php } ?>
		  </colgroup>
    <thead>
      <tr>
        <?php foreach($type_data[0] as $key => $value) { ?>
        <th scope="col"><?=$key?></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody id = "table_excel"><?php echo $params_td; ?>
    </tbody>
    </table></form>
  <form name="flist" id="flist" class="flist">
    <table class="new_form_table" id="search_detail_table">
        <tr>
            <th>날짜</th>
            <td style="padding: 5px 10px">
                <div style="float: left; vertical-align:middle;">
                <?php foreach($type_data[1] as $key=>$value){ $chkd=""; if($key == $sel_date){ $chkd = "checked";}?>
                 <script>console.log("<?=$key.":".$sel_date.":".$_GET['sel_date']?>");</script>
                <input type="radio" name="sel_date" id="<?=$key?>" value="<?=$key?>" <?php echo $chkd; ?>><label for="<?=$key?>"><?=$value?></label>
                <?php } ?>
                </div>
                <div class="sch_last" style="float: left; padding: 0 20px;">
                    <input type="button" value="오늘" id="select_date_today" name="select_date" class="select_date newbutton" />
                    <input type="button" value="어제" id="select_date_yesterday" name="select_date" class="select_date newbutton" />
                    <input type="button" value="일주일" id="select_date_sevendays" name="select_date" class="select_date newbutton" />
                    <input type="button" value="이번달" id="select_date_thismonth" name="select_date" class="select_date newbutton" />
                    <input type="button" value="지난달" id="select_date_lastmonth" name="select_date" class="select_date newbutton" />
                    <input type="text" id="fr_date" class="date" name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off"> ~
                    <input type="text" id="to_date" class="date" name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off">
                </div>
            </td>
        </tr>
        <tr>
            <th>검색어</th>
            <td style="padding: 5px 10px">
              <input type="text" name="search" value="<?php echo $search; ?>" placeholder="사업소명 또는 사업소ID" id="search" class="frm_input" autocomplete="off" style="width:200px;">
              <input type="hidden" name="type" id="type" value="<?=$type;?>">
              <button type="submit" id="search-btn"><span>검색</span></button>
            </td>
        </tr>
    </table>
  </form>
</div>
<div style="width: 100%; margin: 15px 0; padding: 0 20px;">
  <form name="flist">
    검색개수 : <?=number_format($total_count)?>건
    <select name="page_rows" id="page_rows" style="position: relative; float: right; width: 10%; padding: 0 10px">
      <option value="15" <?php echo $page_rows == '15' ? 'selected' : ''; ?>>시스템 기본 보기</option>
      <option value="50" <?php echo $page_rows == '50' ? 'selected' : ''; ?>>50개씩 보기</option>
      <option value="100" <?php echo $page_rows == '100' ? 'selected' : ''; ?>>100개씩 보기</option>
      <option value="200" <?php echo $page_rows == '200' ? 'selected' : ''; ?>>200개씩 보기</option>
      <option value="500" <?php echo $page_rows == '500' ? 'selected' : ''; ?>>500개씩 보기</option>
      <option value="1000" <?php echo $page_rows == '1000' ? 'selected' : ''; ?>>1000개씩 보기</option>
    </select>
  </form>
</div>
<div class="tbl_head01 tbl_wrap">
    <input type="hidden" id="type" value="<?php echo $type ?>"/>
    <input type="hidden" id="fr_date" value="<?php echo $fr_date ?>"/>
    <input type="hidden" id="to_date" value="<?php echo $to_date ?>"/>
    <!-- <caption><?php echo $g5['title']; ?> 목록</caption> -->
    <table class="statistics_table">
      <colgroup>
          <?php foreach($type_data[0] as $key => $value) { ?>
				  <col width="<?=$value?>%"/>
          <?php } ?>
		  </colgroup>
    <thead>
      <tr>
        <?php foreach($type_data[0] as $key => $value) { ?>
        <th scope="col"><?=$key?></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody id = "table_static">
    <?php
      if($total_count==0){?>
        <tr><td colspan="<?=count($type_data[0])?>" style="text-align: center;padding: 50px 0;">관련 데이터가 없습니다.</td></tr>
      <?php }else{
      while($row=sql_fetch_array($results)) { ?>
        <tr class="bg0">
          <?php foreach($row as $key => $value) { ?>
          <td class="td_center" style="vertical-align: middle;"><?php if($value != "0000-00-00 00:00:00") echo $value; ?></td>
          <?php } ?>
        </tr>
    <?php } } ?>
    </tbody>
    </table>
</div>
  <?php echo get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].$qstr.'&amp;page='); ?>



<div style="width: 100%; margin: 20px 0; padding: 0 20px;">
  <a name="download_excel" id="download_excel" style="position: relative; float: right; width: 10%; padding: 10px 15px; background: #6e9254; color:#fff; text-align: center; border:1px solid #e3e3e3;">엑셀 다운로드</a>
</div>
</div>

<script>
function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

$(function() {
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

    $('#download_excel').click(function(e) {
        console.log(document.getElementsByClassName('excel_table')[0].innerHTML);
        console.log('<?=$params_td?>');
        var body = encodeURIComponent(document.getElementsByClassName('excel_table')[0].innerHTML);
        body = body.replace(/\s+/g,"");
        var type = $('#type').val();
        var page = $('#type').val();
        // var url = 'user_statistics_excel_download.php?body=' + body;
        // window.location.href = url;

        var form = document.createElement('form');
        form.method = 'post';
        form.action = 'service_log_management_excel_download.php';
        form.target='_blank';

        var hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = 'table_body';
        hiddenField.value = body;
        form.appendChild(hiddenField);

        var hiddenField2 = document.createElement('input');
        hiddenField2.type = 'hidden';
        hiddenField2.name = 'type';
        hiddenField2.value = type;
        form.appendChild(hiddenField2);

        document.body.appendChild(form);
        form.submit();
    });

    $('#page_rows').change(function() {
        const param_page_rows = $("<input type='hidden' value=" + $('#page_rows').val() + " name='page_rows' readonly>");
        $("#flist").append(param_page_rows);
        $("#flist").submit();
    });

    // 기간 - 오늘 버튼
    $('#select_date_today').click(function(e) {
      e.preventDefault();
      var today = new Date(); // 오늘
      $('#to_date').val(formatDate(today));
      $('#fr_date').val(formatDate(today));
    });
    // 기간 - 어제 버튼
    $('#select_date_yesterday').click(function(e) {
      e.preventDefault();
      var today = new Date(); // 오늘
      today.setDate(today.getDate()-1);
      $('#to_date').val(formatDate(today));
      $('#fr_date').val(formatDate(today));
    });
    // 기간 - 이번주 버튼
    $('#select_date_sevendays').click(function(e) {
      e.preventDefault();
      var today = new Date(); // 오늘
      $('#to_date').val(formatDate(today));
      today.setDate(today.getDate()-7);
      $('#fr_date').val(formatDate(today));
    });
    // 기간 - 저번달 버튼
    $('#select_date_lastmonth').click(function(e) {
      e.preventDefault();
      var today = new Date(); // 오늘
      today.setDate(today.getMonth()-1);
      today.setDate(0); // 지난달 마지막일
      $('#to_date').val(formatDate(today));
      today.setDate(1); // 지난달 1일
      $('#fr_date').val(formatDate(today));
    });
    // 기간 - 이번달 버튼
    $('#select_date_thismonth').click(function(e) {
      e.preventDefault();
      var today = new Date(); // 오늘
      $('#to_date').val(formatDate(today));
      today.setDate(1); // 이번달 1일
      $('#fr_date').val(formatDate(today));
    });
});
</script>
<?php
include_once('./admin.tail.php');
?>