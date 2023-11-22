<?php
    /* // */
    /* // */
    /* // */
    /* // */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* //  *  */
    /* //  *  */
    /* //  * (주)티에이치케이컴퍼 & 이로움 - [ THKcompany & E-Roum ] */
    /* //  *  */
    /* //  * Program Name : EROUMCARE Platform! = matchingservice Ver:1 */
    /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
    /* //  * Copyright (c) 2023 THKC Co,Ltd.  All rights reserved. */
    /* //  *  */
    /* //  *  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // */
    /* // */
    /* // */
    /* // */

    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */
    /* // 파일명 : www\adm\shop_admin\eroumon_matchingservice_list.php */
    /* // 파일 설명 : [관리자] 이로움ON 매칭 신청에 대한 사업소 리스트를 출력. */
    /*                 해당 페이지에서는 사업소의 매칭 여부와 매칭 상담 서비스에 대한 담당자 정보를 확인하고, */
    /*                 설문지에 대한 데이터가 있을 경우 해당 데이터를 팝업으로 보기 위한 기능을 제공 한다. */
    /*                                                                                                        */
    /*
        
      해당 페이지가 정상동작 하려면
        "www\sql\이로움Care_매칭서비스신청_관리자메뉴추가.sql"
      위 파일 경로에 있는 SQL을 1회 실행 후 정상 동작이 된다.
      
    */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

$sub_menu = '500060';
include_once("./_common.php");
auth_check($auth[$sub_menu], 'r');

$g5['title'] = '매칭상담 서비스관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

if(!$fr_date){ $fr_date = date("Y-m-d",strtotime("-90 day", time())); }
if(!$to_date){ $to_date = date("Y-m-d", time() ); }

if( !$list_num ) $list_num = 20;


$sql_common = (" FROM g5_member ");
//$where[] = "(mb_matching_dt IS NOT NULL OR mb_matching_dt <> '') AND ( mb_level IN (3,4) ) ";

// 날짜검색
if ($fr_date && $to_date) {
  $where[] = "mb_matching_dt BETWEEN '$fr_date 00:00:00' AND '$to_date 23:59:59' ";
} else {        
  $where[] = "mb_matching_dt BETWEEN '".date("Y-m-d",strtotime("-90 day", time()))."' AND '".date("Y-m-d",strtotime("+1 day", time()))."' ";
}

// 신청여부
if( $matchingY && !$matchingN ) { $where[] = "mb_giup_matching = 'Y'"; }
if( !$matchingY && $matchingN ) { $where[] = "mb_giup_matching = 'N'"; }

// 검색어
if( $search ) {

  if( $sel_field == "all" ) {
      $where[] = " 
        (
          ( `mb_id` LIKE '%" . $search . "%' ) 
          OR ( `mb_giup_bnum` LIKE '%" . $search . "%' ) 
          OR ( `mb_giup_bname` LIKE '%" . $search . "%' ) 
          OR ( `mb_matching_manager_tel` LIKE '%" . $search . "%' )
        )
      ";
  }
  else if( $sel_field == "mb_id" ) {
      $where[] = " ( `mb_id` LIKE '%" . $search . "%' ) ";
  }
  else if( $sel_field == "mb_giup_bnum" ) {
      $where[] = " ( `mb_giup_bnum` LIKE '%" . $search . "%' ) ";
  }
  else if( $sel_field == "mb_giup_bname" ) {
      $where[] = " ( `mb_giup_bname` LIKE '%" . $search . "%' ) ";
  }
  else if( $sel_field == "mb_matching_manager_tel" ) {
      $where[] = " ( `mb_matching_manager_tel` LIKE '%" . $search . "%' ) ";
  }
}

if ($where) {
  if ($sql_search) {
      $sql_search .= " AND ";
  }else{
      $sql_search .= " WHERE ";
  }

  $sql_search .= implode(' AND ', $where);
}

$sql = " SELECT count(mb_id) as cnt
{$sql_common}
{$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = ($list_num)?$list_num:$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$ListNum = ($total_count-$from_record);

$sql = " SELECT mb_giup_matching
                ,mb_id
                ,mb_giup_bnum
                ,mb_giup_bname
                ,mb_matching_manager_tel
                ,mb_matching_dt
                ,mb_referee_cd
                {$sql_common}
                {$sql_search}
          ORDER BY mb_matching_dt DESC
          LIMIT {$from_record}, {$rows} ";

$result = sql_query($sql);



// 페이징 되는 주소 파라미터
$qstr = ("select_date={$select_date}&amp;matchingY={$matchingY}&amp;matchingN={$matchingN}&amp;fr_date={$fr_date}&amp;to_date={$to_date}&amp;search={$search}&amp;sel_field={$sel_field}&amp;list_num={$list_num}&amp;");

?>

<script src="<?=G5_JS_URL;?>/jquery.fileDownload.js"></script>

<form name="frmsamhwaorderlist" id="frmsamhwaorderlist" style="margin-top:-15px;">
<input type="hidden" name="page_rows" id="page_rows" value="<?=$page_rows?>">
  <div class="new_form">
    <table class="new_form_table" id="search_detail_table">
	  <tr>
        <th>신청일</th>
        <td>
          <div class="sel_field">
            <!-- <input type="button" value="전체" onclick="javascript:set_date('전체');" id="select_date_all" name="select_date" class="select_date newbutton"/> -->
			      <input type="button" value="오늘" onclick="javascript:set_date('오늘');" id="select_date_today" name="select_date" class="select_date newbutton"/>
            <input type="button" value="어제" onclick="javascript:set_date('어제');" id="select_date_yesterday" name="select_date" class="select_date newbutton"/>
            <input type="button" value="일주일" onclick="javascript:set_date('일주일');" id="select_date_sevendays" name="select_date" class="select_date newbutton"/>
            <!-- <input type="button" value="이번달" onclick="javascript:set_date('이번달');" id="select_date_thismonth" name="select_date" class="select_date newbutton"/> -->
            <input type="button" value="지난달" onclick="javascript:set_date('지난달');" id="select_date_lastmonth" name="select_date" class="select_date newbutton"/>
            <input type="button" value="3개월" onclick="javascript:set_date('3개월');" id="select_date_3month" name="select_date" class="select_date newbutton"/>
            <input type="text" id="fr_date" class="date" name="fr_date" value="<?=$fr_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete='off'> ~
            <input type="text" id="to_date" class="date" name="to_date" value="<?=$to_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete='off'>
          </div>
        </td>
      </tr>

	  <tr>
        <th>신청여부</th>
        <td>

          <div class="btn-group" role="group" aria-label="Basic checkbox toggle button group">
            <input type="checkbox" class="btn-check" id="matchingY" name="matchingY" value="Y" autocomplete="off"<?=($matchingY == 'Y')?(' checked="checked"'):('');?>>
            <label class="btn btn-outline-primary" for="matchingY">승인</label>

            <input type="checkbox" class="btn-check" id="matchingN" name="matchingN" value="N" autocomplete="off"<?=($matchingN == 'N')?(' checked="checked"'):('');?>>
            <label class="btn btn-outline-primary" for="matchingN">미승인</label>
          </div>

        </td>
      </tr>

      <tr>
        <td colspan='2'>
          <select name="sel_field" id="sel_field" style="width:147px;margin-left: 20px;">
            <option value="all" <?=get_selected($sel_field, 'all'); ?>>전체</option>
            <option value="mb_id" <?=get_selected($sel_field, 'mb_id'); ?>>사업소아이디</option>
            <option value="mb_giup_bnum" <?=get_selected($sel_field, 'mb_giup_bnum'); ?>>사업자번호</option>
            <option value="mb_giup_bname" <?=get_selected($sel_field, 'mb_giup_bname'); ?>>사업소명</option>
            <option value="mb_matching_manager_tel" <?=get_selected($sel_field, 'mb_matching_manager_tel'); ?>>매칭 담당자 휴대폰번호</option>
          </select>
          <input type="text" name="search" value="<?=$search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">          
          <input type="submit" value="검색" class="newbutton">
        </td>
      </tr>
    </table>
  </div>


  <div style="padding: 0px 20px; height:28px; margin-bottom: 0px;">
        <select name="list_num" id="bn_position" style="width:120px; float:right;" onchange="submit();">
            <option value="20" <?=($list_num=="20")?"selected":""?>> 20씩 보기 </option>
            <option value="40" <?=($list_num=="40")?"selected":""?>> 40씩 보기 </option>
            <option value="60" <?=($list_num=="60")?"selected":""?>> 60씩 보기 </option>
            <option value="80" <?=($list_num=="80")?"selected":""?>> 80씩 보기 </option>
            <option value="999999" <?=($list_num=="999999")?"selected":""?>> 전체 보기 </option>
        </select>
        <div style="width:250px; font-size:12px; float:left;" >
            <span style="font-size:16px;">신청사업소 목록</span>
        </div>
    </div>

</form>

<div class="tbl_wrap tbl_head01">
    <table>
    <thead>
    <tr>
        <th scope="col" style="width:40px;">No.</th>
        <th scope="col" style="width:100px;">매칭상담<br />신청여부</th>
        <th scope="col">사업소아이디</th>
        <th scope="col">사업자번호</th>
        <th scope="col">사업소명</th>
        <th scope="col">매칭 담당자 휴대폰번호</th>
        <th scope="col">상담신청일시<br />(매칭 동의 일시)</th>
        <th scope="col" style="width:100px">사업소 추천코드</th>
    </tr>
    </thead>
    <tbody>
        <?php
            for ($i=0; $row=sql_fetch_array($result); $i++) {
                $bg = 'bg'.($i%2);            

        ?>
        <tr class="<?php echo $bg; ?>">
            <td><?=($ListNum-$i)?></td>
            <td><?=($row['mb_giup_matching']==="Y"?"신청":"미신청")?></td>
            <td><a href="/adm/member_form.php?w=u&mb_id=<?=($row['mb_id'])?>" target="_blank" class="h2"><?=($row['mb_id'])?></a></td>
            <td><a href="javascript:void(0);" class="btn_eroumon_form_result" data-id="<?=($row['mb_id'])?>" data-yn="<?=($row['mb_giup_matching'])?>" data-dt="<?=($row['mb_matching_dt'])?>"><?=($row['mb_giup_bnum'])?></a></td>
            <td><?=($row['mb_giup_bname'])?></td>
            <td><?=($row['mb_matching_manager_tel'])?></td>
            <td><?=($row['mb_matching_dt'])?></td>
            <td><?=($row['mb_referee_cd'])?></td>
        </tr>                
        <?php } ?>
        <?php if ($i == 0) echo '<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>'; ?>
    </tbody>
    </table>

</div>

<?php
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;domain='.$domain.'&amp;page=');
echo $pagelist;
?>

<div style="padding: 0px 20px; height:40px; margin-bottom: 10px;">
    <input type="button" value="엑셀다운로드" class="btn btn_02" id="ExcelDownload" style="width:100px;height:30px;font-size:12px;cursor:pointer; float:right;">
</div>

<!-- 설문 결과보기 팝업 -->
<div id="eroumon_form_result_popup" style="display: none;"><iframe></iframe></div>

<!-- 엑셀 다운로드 로딩 -->
<div id="loading_excel" style="display: none;">
  <div class="loading_modal">
      <p>엑셀파일 다운로드 중입니다.</p>
      <p>잠시만 기다려주세요.</p>
      <img src="/shop/img/loading.gif" alt="loading">
  </div>
</div>

<script type="application/javascript">

    $(function() {

        $('.btn_eroumon_form_result').click(function(e) { 
          const id = $(this).attr("data-id");
          const yn = $(this).attr("data-yn");
          const dt = $(this).attr("data-dt");

            if( yn === "Y" || dt ) {
              $('#eroumon_form_result_popup iframe').attr('src', '/adm/shop_admin/popup.eroumon_form_result.php?id=' + id);
              $('#eroumon_form_result_popup iframe').attr('scrolling', 'auto');
              $('#eroumon_form_result_popup iframe').attr('frameborder', '0');

              $("#eroumon_form_result_popup iframe").load(function(){
                  $('body').addClass('modal-open');
                  $("#eroumon_form_result_popup").show();
              });
            } else {
              alert('매칭상담 미신청 사업소는 설문 결과를 확인할 수 없습니다.');
              return;
            }

        });

        $("#fr_date, #to_date").datepicker({
          changeMonth: true,
          changeYear: true,
          dateFormat: "yy-mm-dd",
          showButtonPanel: true,
          yearRange: "c-99:c+99",
          maxDate: "+2y"
        });

        $.datepicker.regional["ko"] = {
          closeText: "닫기",
          prevText: "이전달",
          nextText: "다음달",
          currentText: "오늘",
          monthNames: ["1월(JAN)","2월(FEB)","3월(MAR)","4월(APR)","5월(MAY)","6월(JUN)", "7월(JUL)","8월(AUG)","9월(SEP)","10월(OCT)","11월(NOV)","12월(DEC)"],
          monthNamesShort: ["1월","2월","3월","4월","5월","6월", "7월","8월","9월","10월","11월","12월"],
          dayNames: ["일","월","화","수","목","금","토"],
          dayNamesShort: ["일","월","화","수","목","금","토"],
          dayNamesMin: ["일","월","화","수","목","금","토"],
          weekHeader: "Wk",
          dateFormat: "yymmdd",
          firstDay: 0,
          isRTL: false,
          showMonthAfterYear: true,
          yearSuffix: ""
        };

	      $.datepicker.setDefaults($.datepicker.regional["ko"]);

        $("#ExcelDownload").click(function(){
          Download_Excel();
        });
    });

    function Download_Excel() {
      if (!confirm("엑셀 파일을 다운로드 하시겠습니까?")) { return; }

      $('#loading_excel').show();

      var queryString = "<?=urldecode($_SERVER['QUERY_STRING']);?>";
      excel_downloader = $.fileDownload('/adm/shop_admin/ajax.eroumon_matchingservice_Excel.php', {
        httpMethod: "POST", data: { mode_set:"ExcelDown", data:queryString }
      })
      .always(function() { $('#loading_excel').hide(); });

    }
</script>

<style>

    /* 팝업 */
    #eroumon_form_result_popup { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index:9999; background:rgba(28, 26, 26, 0.5); }
    #eroumon_form_result_popup iframe { position:absolute; width:600px; height:700px; max-height: 90%; top: 50%; left: 50%; transform:translate(-50%, -50%); background:white; }

    .tbl_head01 tbody td { text-align:center; }

    /* 로딩 팝업 */
    #loading_excel { display: none; width: 100%; height: 100%; position: fixed; left: 0; top: 0; z-index: 9999; background: rgba(0, 0, 0, 0.3); }
    #loading_excel .loading_modal { position: absolute; width: 400px; padding: 30px 20px; background: #fff; text-align: center; top: 50%; left: 50%; transform: translate(-50%, -50%); }
    #loading_excel .loading_modal p { padding: 0; font-size: 16px; }
    #loading_excel .loading_modal img { display: block; margin: 20px auto; }
    #loading_excel .loading_modal button { padding: 10px 30px; font-size: 16px; border: 1px solid #ddd; border-radius: 5px; }

</style>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
