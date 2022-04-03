<?php
$sub_menu = "200100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');


if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_datetime) ) $fr_datetime = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_datetime) ) $to_datetime = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_updatedatetime) ) $fr_updatedatetime = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_updatedatetime) ) $to_updatedatetime = '';

$sql_common = " from {$g5['member_table']} ";

//영업담당자 추가
$flag_m=false;
if($sfl=="mb_manager"){
  $sql_cm = "select `mb_id` from `g5_member` where `mb_name`= '".$stx."'";
  $result_cm= sql_fetch($sql_cm);
  $stx2=$stx;
  $stx=$result_cm['mb_id'];
  $flag_m=true;
}

$sql_search = " where (mb_type <> 'manager') ";
if ($stx) {
  $sql_search .= " and ( ";
  switch ($sfl) {
    case 'mb_point' :
      $sql_search .= " ({$sfl} >= '{$stx}') ";
      break;
    case 'mb_level' :
      $sql_search .= " ({$sfl} = '{$stx}') ";
      break;
    case 'mb_tel' :
    case 'mb_hp' :
      $sql_search .= " ({$sfl} like '%{$stx}') ";
      break;
    case 'all' :
      $sql_search .= "
        mb_tel like '%{$stx}%' OR
        mb_hp like '%{$stx}%' OR
        mb_id like '%{$stx}%' OR
        mb_nick like '%{$stx}%' OR
        mb_name like '%{$stx}%' OR
        mb_email like '%{$stx}%' OR
        mb_datetime like '%{$stx}%' OR
        mb_ip like '%{$stx}%' OR
        mb_giup_bnum like '%{$stx}%' OR
        mb_recommend like '%{$stx}%' OR
        mb_1 like '%{$stx}%' OR
        mb_giup_bname like '%{$stx}%' OR
        mb_manager like '%{$stx}%'
      ";
      break;
    default :
      $sql_search .= " ({$sfl} like '{$stx}%') ";
      break;
  }
  $sql_search .= " ) ";
}

if ($button_type) {
  $sql_search .= " and ( ";
  switch ($button_type) {
    case 'temp' :
      $sql_search .= "
        (
          mb_giup_bnum IN
          (
            SELECT mb_giup_bnum FROM g5_member WHERE mb_temp = TRUE
          )
          AND mb_temp = false
        )
      ";
      break;
    case 'partner' :
      $sql_search .= " (mb_type = 'partner') ";
      break;
    case 'default' :
      $sql_search .= " (mb_type = 'default' AND mb_level = 3) ";
      break;
    case 'vip' :
      $sql_search .= " (mb_type = 'default' AND mb_level = 4) ";
      break;
    case 'normal' :
      $sql_search .= " (mb_type = 'normal') ";
      break;
  }
  $sql_search .= " ) ";
  $qstr .= "&amp;button_type=$button_type";
}

if ($fr_datetime && $to_datetime) {
  $sql_search .= " and ( mb_datetime between '$fr_datetime 00:00:00' and '$to_datetime 23:59:59' )";
  $qstr .= "&amp;fr_datetime=$fr_datetime&amp;to_datetime=$to_datetime";
}

if ($fr_updatedatetime && $to_updatedatetime) {
  $sql_search .= " and ( mb_update_date between '$fr_updatedatetime 00:00:00' and '$to_updatedatetime 23:59:59' )";
  $qstr .= "&amp;fr_updatedatetime=$fr_updatedatetime&amp;to_updatedatetime=$to_updatedatetime";
}

if ($_GET['mb_level']) {
  $sql_search .= " and ( ";

  $mb_level = (int)$_GET['mb_level'];

  $sql_search .= " (mb_level like '%{$mb_level}') ";

  $sql_search .= " ) ";
  $qstr .= "&amp;mb_level=$mb_level";
}

if ($is_admin != 'super')
  $sql_search .= " and mb_level <= '{$member['mb_level']}' ";

if (!$sst) {
  $sst = "mb_datetime";
  $sod = "desc";
}

if($sst == "mb_email_certify") {
  $sql_order = " order by {$sst} {$sod} , mb_datetime asc";
} else {
  $sql_order = " order by {$sst} {$sod} ";
}

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 멤버쉽 확인 ------------------------
$is_membership = (function_exists('apms_membership_item')) ? true : false;

// 멥버쉽 회원수
if($is_membership) {
  $sql = " select count(*) as cnt {$sql_common} {$sql_search} and as_date > 0 {$sql_order} ";
  $row = sql_fetch($sql);
  $membership_count = $row['cnt'];
}

// 탈퇴회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_leave_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

// 차단회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_intercept_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$intercept_count = $row['cnt'];

// 임시계정 승인요청 수
$sql = " select count(*) as cnt {$sql_common} WHERE (
  mb_giup_bnum IN
  (
    SELECT mb_giup_bnum FROM g5_member WHERE mb_temp = TRUE
  )
  AND mb_temp = false
)";
$row = sql_fetch($sql);
$temp_count = $row['cnt'];

// 파트너 수
$sql = " select count(*) as cnt {$sql_common} WHERE (
  mb_type = 'partner'
)";
$row = sql_fetch($sql);
$partner_count = $row['cnt'];

// 사업소 수
$sql = " select count(*) as cnt {$sql_common} WHERE (
  mb_type = 'default'
)";
$row = sql_fetch($sql);
$default_count = $row['cnt'];

// VIP 사업소 수
$sql = " select count(*) as cnt {$sql_common} WHERE (
  mb_type = 'default' AND mb_level = 4
)";
$row = sql_fetch($sql);
$vip_count = $row['cnt'];

// 일반회원 수
$sql = " select count(*) as cnt {$sql_common} WHERE (
  mb_type = 'normal'
)";
$row = sql_fetch($sql);
$normal_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '회원관리';
include_once('./admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

if($_GET['manager']){
$sql_common = " from {$g5['member_table']} a left join {$g5['auth_table']} b on (a.mb_id=b.mb_id) ";
$sql_search = " where b.au_menu ='200100' ";
}
$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = ($is_membership) ? 21 : 20;


?>

<?php
//영업담당자 이름표시
$original_stx = $stx;
$stx=$stx2;
?>
<div class="local_ov01 local_ov">
  <?php echo $listall ?>
  <span class="btn_ov01"><span class="ov_txt">총회원수 </span><span class="ov_num"> <?php echo number_format($total_count) ?>명 </span></span>
  <?php if($is_membership) { ?>
  <a href="?sst=as_date&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01"> <span class="ov_txt">멤버쉽 </span><span class="ov_num"><?php echo number_format($membership_count) ?></span></a>
  <?php } ?>
  <a href="?sst=mb_intercept_date&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01"> <span class="ov_txt">차단 </span><span class="ov_num"><?php echo number_format($intercept_count) ?>명</span></a>
  <a href="?sst=mb_leave_date&amp;sod=desc&amp;sfl=<?php echo $sfl ?>&amp;stx=<?php echo $stx ?>" class="btn_ov01"> <span class="ov_txt">탈퇴  </span><span class="ov_num"><?php echo number_format($leave_count) ?>명</span></a>
  <div class="right">
    <button id="mbexcel"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_img_ex.gif">엑셀다운로드</button>
  </div>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<style>
  .local_sch03 button.active {
    background:#ff1464;
  }
</style>
<div class="local_sch03 local_sch">
  <input type="hidden" name="button_type" value="<?php echo $mb_button; ?>">
  <button type="button" class="mb_button <?php echo !$button_type ? 'active' : ''; ?>" data-value="">전체</button>
  <button type="button" class="mb_button <?php echo $button_type === 'temp' ? 'active' : ''; ?>" data-value="temp">임시계정 승인요청 (<?php echo $temp_count; ?>)</button>
  <button type="button" class="mb_button <?php echo $button_type === 'partner' ? 'active' : ''; ?>" data-value="partner">파트너 (<?php echo $partner_count; ?>)</button>
  <button type="button" class="mb_button <?php echo $button_type === 'default' ? 'active' : ''; ?>" data-value="default">사업소 (<?php echo $default_count; ?>)</button>
  <button type="button" class="mb_button <?php echo $button_type === 'vip' ? 'active' : ''; ?>" data-value="vip">VIP사업소 (<?php echo $vip_count; ?>)</button>
  <button type="button" class="mb_button <?php echo $button_type === 'normal' ? 'active' : ''; ?>" data-value="normal">일반회원 (<?php echo $normal_count; ?>)</button>
</div>

<div class="local_sch03 local_sch">
  <div class="sch_last">
    <strong>가입일</strong>
    <input type="text" id="fr_datetime"  name="fr_datetime" value="<?php echo $fr_datetime; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off"> ~
    <input type="text" id="to_datetime"  name="to_datetime" value="<?php echo $to_datetime; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off">
    <button type="button" onclick="javascript:set_date2('datetime', '오늘');">오늘</button>
    <button type="button" onclick="javascript:set_date2('datetime', '어제');">어제</button>
    <button type="button" onclick="javascript:set_date2('datetime', '3일');">3일</button>
    <button type="button" onclick="javascript:set_date2('datetime', '일주일');">일주일</button>
    <button type="button" onclick="javascript:set_date2('datetime', '이번주');">이번주</button>
    <button type="button" onclick="javascript:set_date2('datetime', '이번달');">이번달</button>
    <button type="button" onclick="javascript:set_date2('datetime', '지난주');">지난주</button>
    <button type="button" onclick="javascript:set_date2('datetime', '지난달');">지난달</button>
    <button type="button" onclick="javascript:set_date2('datetime', '전체');">전체</button>
  </div>
</div>


<div class="local_sch03 local_sch">
  <div class="sch_last">
    <strong>최종수정일</strong>
    <input type="text" id="fr_updatedatetime"  name="fr_updatedatetime" value="<?php echo $fr_updatedatetime; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off"> ~
    <input type="text" id="to_updatedatetime"  name="to_updatedatetime" value="<?php echo $to_updatedatetime; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off">
    <button type="button" onclick="javascript:set_date2('updatedatetime', '오늘');">오늘</button>
    <button type="button" onclick="javascript:set_date2('updatedatetime', '어제');">어제</button>
    <button type="button" onclick="javascript:set_date2('updatedatetime', '3일');">3일</button>
    <button type="button" onclick="javascript:set_date2('updatedatetime', '일주일');">일주일</button>
    <button type="button" onclick="javascript:set_date2('updatedatetime', '이번주');">이번주</button>
    <button type="button" onclick="javascript:set_date2('updatedatetime', '이번달');">이번달</button>
    <button type="button" onclick="javascript:set_date2('updatedatetime', '지난주');">지난주</button>
    <button type="button" onclick="javascript:set_date2('updatedatetime', '지난달');">지난달</button>
    <button type="button" onclick="javascript:set_date2('updatedatetime', '전체');">전체</button>
  </div>
</div>


<?php echo samhwa_get_member_level_select('mb_level', 1, $member['mb_level'], $_GET['mb_level'], '', true) ?>

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
  <option value="all"<?php echo get_selected($_GET['sfl'], "all"); ?>>전체</option>
  <option value="mb_id"<?php echo get_selected($_GET['sfl'], "mb_id"); ?>>회원아이디</option>
  <option value="mb_nick"<?php echo get_selected($_GET['sfl'], "mb_nick"); ?>>닉네임</option>
  <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>이름</option>
  <option value="mb_giup_bnum"<?php echo get_selected($_GET['sfl'], "mb_giup_bnum"); ?>>사업자번호</option>
  <option value="mb_level"<?php echo get_selected($_GET['sfl'], "mb_level"); ?>>권한</option>
  <option value="mb_grade"<?php echo get_selected($_GET['sfl'], "mb_grade"); ?>>등급</option>
  <option value="mb_email"<?php echo get_selected($_GET['sfl'], "mb_email"); ?>>E-MAIL</option>
  <option value="mb_tel"<?php echo get_selected($_GET['sfl'], "mb_tel"); ?>>전화번호</option>
  <option value="mb_hp"<?php echo get_selected($_GET['sfl'], "mb_hp"); ?>>휴대폰번호</option>
  <option value="mb_point"<?php echo get_selected($_GET['sfl'], "mb_point"); ?>>포인트</option>
  <option value="mb_datetime"<?php echo get_selected($_GET['sfl'], "mb_datetime"); ?>>가입일시</option>
  <option value="mb_ip"<?php echo get_selected($_GET['sfl'], "mb_ip"); ?>>IP</option>
  <option value="mb_recommend"<?php echo get_selected($_GET['sfl'], "mb_recommend"); ?>>추천인</option>
  <option value="mb_1"<?php echo get_selected($_GET['sfl'], "mb_1"); ?>>여분필드1</option>
  <option value="mb_giup_bname"<?php echo get_selected($_GET['sfl'], "mb_giup_bname"); ?>>기업명</option>
  <option value="mb_manager"<?php echo get_selected($_GET['sfl'], "mb_manager"); ?>>영업담당자</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $original_stx ?? $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
<!-- <button type="submit" class="btn_submit" name="manager" value="1">영업담당자 검색</button> -->
</form>

<div class="local_desc01 local_desc">
  <p>
    회원자료 삭제 시 다른 회원이 기존 회원아이디를 사용하지 못하도록 회원아이디, 이름, 닉네임은 삭제하지 않고 영구 보관합니다.
  </p>
</div>

<form name="fmemberlist" id="fmemberlist" action="./member_list_update.php" onsubmit="return fmemberlist_submit(this);" method="post">
  <input type="hidden" name="sst" value="<?php echo $sst ?>">
  <input type="hidden" name="sod" value="<?php echo $sod ?>">
  <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
  <input type="hidden" name="stx" value="<?php echo $stx ?>">
  <input type="hidden" name="page" value="<?php echo $page ?>">
  <input type="hidden" name="token" value="">

  <div class="tbl_head01 tbl_wrap tbl_mblist">
    <table>
      <caption><?php echo $g5['title']; ?> 목록</caption>
      <thead>
        <tr>
          <th scope="col" id="mb_list_chk" rowspan="2" >
            <label for="chkall" class="sound_only">회원 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
          </th>
          <th scope="col" id="mb_list_id" colspan="2"><?php echo subject_sort_link('mb_id') ?>아이디</a></th>
          <!--<th scope="col" rowspan="2" id="mb_list_cert"><?php echo subject_sort_link('mb_certify', '', 'desc') ?>본인확인</a></th>-->
          <th scope="col" rowspan="2" id="mb_list_level">권한</th>
          <th scope="col" rowspan="2" id="mb_list_level">등급</th>
          <th scope="col" rowspan="2" id="mb_list_email">이메일</th>
          <th scope="col" rowspan="2" id="mb_list_thezone">고객(거래처)코드</th>
          <th scope="col" rowspan="2" id="mb_giup_bnum">사업자번호</th>
          <th scope="col" rowspan="2" id="mb_list_datetime">회원가입일</th>
          <th scope="col" rowspan="2" id="mb_list_updatedate">최종수정일</th>
          <th scope="col" rowspan="2" id="mb_list_lastmanager">영업담당자</th>
          <!--
          <th scope="col" id="mb_list_mailc"><?php echo subject_sort_link('mb_email_certify', '', 'desc') ?>메일인증</a></th>
          <th scope="col" id="mb_list_open"><?php echo subject_sort_link('mb_open', '', 'desc') ?>정보공개</a></th>
          <th scope="col" id="mb_list_mailr"><?php echo subject_sort_link('mb_mailling', '', 'desc') ?>메일수신</a></th>
          -->
          <th scope="col" id="mb_list_auth">상태</th>
          <th scope="col" id="mb_list_misu">-</th>
          <th scope="col" id="mb_list_mobile">휴대폰</th>
          <th scope="col" id="mb_list_partnermall">파트너몰 회원 여부</th>
          <th scope="col" id="mb_list_lastcall"><?php echo subject_sort_link('mb_today_login', '', 'desc') ?>최종접속</a></th>
          <th scope="col" id="mb_list_grp">접근그룹</th>
          <?php if($is_membership) { ?>
          <th scope="col" id="as_membership"><?php echo subject_sort_link('as_date', '', 'desc') ?>멤버쉽기간(잔여시간)</a></th>
          <?php } ?>
          <th scope="col" rowspan="2" id="mb_list_order">주문</th>
          <th scope="col" rowspan="2" id="mb_list_mng">관리</th>
          <th scope="col" rowspan="2" id="mb_list_accept" class="accept">승인</th>
        </tr>
        <tr>
          <th scope="col" id="mb_list_name"><?php echo subject_sort_link('mb_name') ?>이름</a></th>
          <th scope="col" id="mb_list_nick"><?php echo subject_sort_link('mb_nick') ?>닉네임</a></th>
          <!--
          <th scope="col" id="mb_list_sms"><?php echo subject_sort_link('mb_sms', '', 'desc') ?>SMS수신</a></th>
          <th scope="col" id="mb_list_adultc"><?php echo subject_sort_link('mb_adult', '', 'desc') ?>성인인증</a></th>
          <th scope="col" id="mb_list_auth"><?php echo subject_sort_link('mb_intercept_date', '', 'desc') ?>접근차단</a></th>
          -->
          <th scope="col" id="mb_list_deny" colspan="2"><?php echo subject_sort_link('mb_level', '', 'desc') ?>권한</a></th>
          <th scope="col" id="mb_list_tel">전화번호</th>
          <th scope="col" id="mb_list_partnermall2">파트너몰 회원 상태</th>
          <th scope="col" id="mb_list_join"><?php echo subject_sort_link('mb_datetime', '', 'desc') ?>가입일</a></th>
          <th scope="col" id="mb_list_point"><?php echo subject_sort_link('mb_point', '', 'desc') ?> 포인트</a></th>
          <?php if($is_membership) { ?>
          <th scope="col" id="as_membership_add">기간증감/해제</th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php
        for ($i=0; $row=sql_fetch_array($result); $i++) {
          // print_r($row);
          // 접근가능한 그룹수
          $sql2 = " select count(*) as cnt from {$g5['group_member_table']} where mb_id = '{$row['mb_id']}' ";
          $row2 = sql_fetch($sql2);
          $group = '';
          if ($row2['cnt'])
            $group = '<a href="./boardgroupmember_form.php?mb_id='.$row['mb_id'].'">'.$row2['cnt'].'</a>';

          if ($is_admin == 'group') {
            $s_mod = '';
          } else {
            $s_mod = '<a target="_blank" href="./member_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$row['mb_id'].'" class="btn btn_03">수정</a>';
          }
          $s_grp = '<a href="./boardgroupmember_form.php?mb_id='.$row['mb_id'].'" class="btn btn_02">그룹</a>';

          $leave_date = $row['mb_leave_date'] ? $row['mb_leave_date'] : date('Ymd', G5_SERVER_TIME);
          $intercept_date = $row['mb_intercept_date'] ? $row['mb_intercept_date'] : date('Ymd', G5_SERVER_TIME);

          $mb_nick = get_sideview($row['mb_id'], get_text($row['mb_nick']), $row['mb_email'], $row['mb_homepage']);

          $mb_id = $row['mb_id'];
          $leave_msg = '';
          $intercept_msg = '';
          $intercept_title = '';
          if ($row['mb_leave_date']) {
            $mb_id = $mb_id;
            $leave_msg = '<span class="mb_leave_msg">탈퇴함</span>';
          }
          else if ($row['mb_intercept_date']) {
            $mb_id = $mb_id;
            $intercept_msg = '<span class="mb_intercept_msg">차단됨</span>';
            $intercept_title = '차단해제';
          }
          if ($intercept_title == '')
            $intercept_title = '차단하기';

          $address = $row['mb_zip1'] ? print_address($row['mb_addr1'], $row['mb_addr2'], $row['mb_addr3'], $row['mb_addr_jibeon']) : '';

          $bg = 'bg'.($i%2);

          switch($row['mb_certify']) {
            case 'hp':
              $mb_certify_case = '휴대폰';
              $mb_certify_val = 'hp';
              break;
            case 'ipin':
              $mb_certify_case = '아이핀';
              $mb_certify_val = '';
              break;
            case 'admin':
              $mb_certify_case = '관리자';
              $mb_certify_val = 'admin';
              break;
            default:
              $mb_certify_case = '&nbsp;';
              $mb_certify_val = 'admin';
              break;
          }
        ?>

        <tr class="<?php echo $bg; ?>">
          <td headers="mb_list_chk" class="td_chk" rowspan="2">
            <input type="hidden" name="mb_id[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>" id="mb_id_<?php echo $i ?>" class="mb_id_input">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
          </td>
          <td headers="mb_list_id" colspan="2" class="td_name sv_use">
            <?php echo $mb_id ?>
            <?php if ($row['mb_temp']) { ?>
            <a class="btn_back">임시계정</a>
            <?php } ?>
            <?php
            //소셜계정이 있다면
            if(function_exists('social_login_link_account')){
              if( $my_social_accounts = social_login_link_account($row['mb_id'], false, 'get_data') ){

                echo '<div class="member_social_provider sns-wrap-over sns-wrap-32">';
                foreach( (array) $my_social_accounts as $account) {     //반복문
                  if( empty($account) || empty($account['provider']) ) continue;

                  $provider = strtolower($account['provider']);
                  $provider_name = social_get_provider_service_name($provider);

                  echo '<span class="sns-icon sns-'.$provider.'" title="'.$provider_name.'">';
                  echo '<span class="ico"></span>';
                  echo '<span class="txt">'.$provider_name.'</span>';
                  echo '</span>';
                }
                echo '</div>';
              }
            }
            ?>
          </td>
          <td headers="mb_list_level"  rowspan="2" class="td_mblevel" style="text-align:center;">
            <?php echo $row['mb_level']; ?>
            <?php echo $row['mb_level'] == '3' ? '(딜러)' : ''; ?>
            <?php echo $row['mb_level'] == '4' ? '(우수딜러)' : ''; ?>
          </td>
          <td headers="mb_list_level"  rowspan="2" class="td_mblevel" style="text-align:center;">
            <?php echo $default['de_it_grade' . $row['mb_grade'] . '_name']; ?>
            (<?php echo $default['de_it_grade' . $row['mb_grade'] . '_discount']; ?>%적립)
          </td>
          <td headers="mb_list_email"  rowspan="2" class="td_mbemail" style="">
            <?php echo $row['mb_email']; ?>
          </td>
          <td headers="mb_list_thezone"  rowspan="2" class="td_mbthezone" style="">
            <?php echo $row['mb_thezone']; ?>
          </td>
          <td headers="mb_list_bnum"  rowspan="2" class="td_giup_bnum" style="">
            <?php echo $row['mb_giup_bnum']; ?>
          </td>
          <td headers="mb_list_datetime"  rowspan="2" class="td_mbdatetime" style="">
            <?php echo $row['mb_datetime']; ?>
          </td>
          <td headers="mb_list_updatedate"  rowspan="2" class="td_mbupdatedate" style="">
            <?php echo $row['mb_update_date']; ?>
          </td>
          <td headers="mb_list_lastmanager"  rowspan="2" class="td_mblastmanager" style="">
            <?php
            // $sql =  "select b.*
            //             from {$g5['g5_shop_order_table']} as a
            //             LEFT JOIN g5_member_giup_manager as b ON a.od_giup_manager = b.mm_no
            //             where a.mb_id = '{$mb_id}' and a.od_giup_manager != 0
            //             ORDER BY a.od_id DESC
            //         ";
            // $lastmanager = sql_fetch($sql);
            // print_r2($lastmanager);
            // echo htmlspecialchars($lastmanager['mm_name']);
            $sql_v = "SELECT `mb_name` FROM `g5_member` where `mb_id` = '".$row['mb_manager']."'";
            $manaher_name = sql_fetch($sql_v);
            echo $manaher_name['mb_name'];
            ?>
          </td>
          <!--
          <td headers="mb_list_cert"  rowspan="2" class="td_mbcert">
            <input type="radio" name="mb_certify[<?php echo $i; ?>]" value="ipin" id="mb_certify_ipin_<?php echo $i; ?>" <?php echo $row['mb_certify']=='ipin'?'checked':''; ?>>
            <label for="mb_certify_ipin_<?php echo $i; ?>">아이핀</label><br>
            <input type="radio" name="mb_certify[<?php echo $i; ?>]" value="hp" id="mb_certify_hp_<?php echo $i; ?>" <?php echo $row['mb_certify']=='hp'?'checked':''; ?>>
            <label for="mb_certify_hp_<?php echo $i; ?>">휴대폰</label>
          </td>
          -->
          <!--
          <td headers="mb_list_mailc"><?php echo preg_match('/[1-9]/', $row['mb_email_certify'])?'<span class="txt_true">Yes</span>':'<span class="txt_false">No</span>'; ?></td>
          <td headers="mb_list_open">
            <label for="mb_open_<?php echo $i; ?>" class="sound_only">정보공개</label>
            <input type="checkbox" name="mb_open[<?php echo $i; ?>]" <?php echo $row['mb_open']?'checked':''; ?> value="1" id="mb_open_<?php echo $i; ?>">
          </td>
          <td headers="mb_list_mailr">
            <label for="mb_mailling_<?php echo $i; ?>" class="sound_only">메일수신</label>
            <input type="checkbox" name="mb_mailling[<?php echo $i; ?>]" <?php echo $row['mb_mailling']?'checked':''; ?> value="1" id="mb_mailling_<?php echo $i; ?>">
          </td>
          -->
          <td headers="mb_list_auth" class="td_mbstat">
            <?php
            if ($leave_msg || $intercept_msg) echo $leave_msg.' '.$intercept_msg;
            else echo "정상";
            ?>
            (<?php echo $row['mb_giup_type'] ? '기업' : '개인'; ?>)
          </td>
          <td headers="mb_list_misu" class="td_mbstat"> -
            <!--  <?php
            $misu = samhwa_get_misu($row['mb_id']);
            echo number_format($misu['misu']) . '원';
            
            if ( $row['mb_type'] == 'partner' && $misu['misu'] ) {
                echo '&nbsp;&nbsp;&nbsp;<a href="'. G5_ADMIN_URL .'/shop_admin/partnerpayform.php?mb_id='.$row['mb_id'].'" class="btn btn_02">결제하기</a>';
            }
            ?> -->
          </td>
          <td headers="mb_list_mobile" class="td_tel"><?php echo get_text($row['mb_hp']); ?></td>
          <td headers="mb_list_mobile" class="td_partnermall" style="text-align:center;">
            <?php echo $row['mb_type'] == 'partner' ? '파트너몰' : '일반몰'; ?>
          </td>
          <td headers="mb_list_lastcall" class="td_date"><?php echo substr($row['mb_today_login'],2,8); ?></td>
          <td headers="mb_list_grp" class="td_numsmall"><?php echo $group ?></td>
          <?php if($is_membership) { ?>
          <td headers="as_membership" class="td_tel">
            <?php
            if($row['as_date']) {
              echo date("Y/m/d", $row['as_date']).'('.number_format(($row['as_date'] - G5_SERVER_TIME) / 3600).'시간)';
            }
            ?>
            <input type="hidden" name="as_date[<?php echo $i; ?>]" value="<?php echo $row['as_date'];?>" id="as_date_<?php echo $i;?>">
          </td>
          <?php } ?>

          <?php
          $sql =  "select count(*) as cnt
                    from {$g5['g5_shop_order_table']}
                    where mb_id = '{$mb_id}'";
          $row2 = sql_fetch($sql);
          $total_count = $row2['cnt'];
          ?>
          <style>
            a.btn_back {
              border: 1px solid #383838;
              background: #383838;
              padding: 0px 13px;
              color: #fff;
              display: inline-block;
            }
          </style>

          <td headers="mb_list_order" rowspan="2" class="td_mng td_order_s"> <a class="btn_back" href="/adm/shop_admin/samhwa_orderlist.php?mb_info=true&sel_field=mb_id&search=<?php echo $mb_id ?>"><?php echo $total_count ?> 건</a></td>
          <td headers="mb_list_mng" rowspan="2" class="td_mng td_mng_s"><?php echo $s_mod ?><?php echo $s_grp ?></td>
          <td headers="mb_list_mng" rowspan="2" class="td_mng td_mng_s">
            <?php
            if(!$row['mb_entId']) {
              $temp = sql_fetch("SELECT * FROM `{$g5['member_table']}` WHERE mb_giup_bnum = '{$row['mb_giup_bnum']}' AND mb_temp = TRUE");
              if ($temp['mb_id']) {
                echo '<button type="button" class="btn btn_02 temp_accept" data-id="'.$row['mb_id'].'"">임시계정연결</button>';
                echo '<button type="button" class="btn btn_01 temp_reject" data-id="'.$row['mb_id'].'"">계정연결거절</button>';
              } else {
                echo "쇼핑몰 전용 아이디";
              }
            } else {
              $resInfo = api_post_call(EROUMCARE_API_ENT_ACCOUNT, array(
                'usrId' => $row['mb_id']
              ));
              if($resInfo['data']['entConfirmCd'] == "01") {
                echo "<span>승인완료</span>";
                echo '<button type="button" class="btn accept-rollback" style="border: 1px solid #b5b5b5;
                padding: 0px 3px;
                font-size: 0.95em;" data-id="'.$row['mb_id'].'" data-entid="'.$row['mb_entId'].'">승인취소</button>';
              } else {
                echo '<button type="button" class="btn btn_02 accept" data-id="'.$row['mb_id'].'" data-entid="'.$row['mb_entId'].'">승인</button>';
              }
            }
            ?>
          </td>
        </tr>
        <tr class="<?php echo $bg; ?>">
          <td headers="mb_list_name" class="td_mbname"><?php echo get_text($row['mb_name']); ?></td>
          <td headers="mb_list_nick" class="td_name sv_use"><div><?php echo $mb_nick ?></div></td>
          <!--
          <td headers="mb_list_sms">
              <label for="mb_sms_<?php echo $i; ?>" class="sound_only">SMS수신</label>
              <input type="checkbox" name="mb_sms[<?php echo $i; ?>]" <?php echo $row['mb_sms']?'checked':''; ?> value="1" id="mb_sms_<?php echo $i; ?>">
          </td>
          <td headers="mb_list_adultc">
              <label for="mb_adult_<?php echo $i; ?>" class="sound_only">성인인증</label>
              <input type="checkbox" name="mb_adult[<?php echo $i; ?>]" <?php echo $row['mb_adult']?'checked':''; ?> value="1" id="mb_adult_<?php echo $i; ?>">
          </td>
          <td headers="mb_list_deny">
              <?php if(empty($row['mb_leave_date'])){ ?>
              <input type="checkbox" name="mb_intercept_date[<?php echo $i; ?>]" <?php echo $row['mb_intercept_date']?'checked':''; ?> value="<?php echo $intercept_date ?>" id="mb_intercept_date_<?php echo $i ?>" title="<?php echo $intercept_title ?>">
              <label for="mb_intercept_date_<?php echo $i; ?>" class="sound_only">접근차단</label>
              <?php } ?>
          </td>
          -->
          <td headers="mb_list_auth" class="td_mbstat" colspan="2">
            <?php echo get_member_level_select("mb_level[$i]", 1, $member['mb_level'], $row['mb_level']) ?>
          </td>
          <td headers="mb_list_tel" class="td_tel"><?php echo get_text($row['mb_tel']); ?></td>
          <td headers="mb_list_tel" class="td_tel">
            <?php if ( $row['mb_type'] == 'partner' ) { ?>
              <?php if ( $row['mb_partner_auth'] == 0 ) { ?>
              미승인
              <?php }else if ( $row['mb_partner_auth'] == 1 ) { ?>
              승인(~<?php echo date('Y-m-d', strtotime($row['mb_partner_date'])); ?>)
              <?php } ?>
            <?php } ?>
          </td>
          <td headers="mb_list_join" class="td_date"><?php echo substr($row['mb_datetime'],2,8); ?></td>
          <td headers="mb_list_point" class="td_num"><a href="point_list.php?sfl=mb_id&amp;stx=<?php echo $row['mb_id'] ?>"><?php echo number_format($row['mb_point']) ?></a></td>
          <?php if($is_membership) { ?>
          <td headers="as_membership_add" class="td_date">
            <?php if($row['as_date']) { ?>
            ± <input type="text" name="as_date_plus[<?php echo $i; ?>]" value="" id="as_date_plus_<?php echo $i;?>" maxlength="20" class="frm_input" size="4"> 일
            -
            <label><input type="checkbox" name="as_date_del[<?php echo $i; ?>]" value="1" id="as_date_del_<?php echo $i;?>"> 해제</label>
            <?php } ?>
          </td>
          <?php } ?>
        </tr>

        <?php
        }
        if ($i == 0)
            echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
        ?>
      </tbody>
    </table>
  </div>

  <div class="btn_list01 btn_list">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="완전삭제" onclick="document.pressed=this.value" class="btn btn_02">
  </div>

  <div class="btn_fixed_top">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <?php if ($is_admin == 'super') { ?>
      <a href="./member_form.php" id="member_add" class="btn btn_01">회원추가</a>
      <a href="./temp_member_excel.php" onclick="return excelform(this.href);" target="_blank" class="btn btn_02">임시회원일괄등록</a>
    <?php } ?>
  </div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<style>
#admin-member-search {
    display: none;
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    z-index:9999;
    background: rgba(0, 0, 0, 0.8);
}
.admin-member-search-content {
    width:400px;
    max-height: 80%;
    position: absolute; 
    top: 50%; 
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
  padding: 15px 20px;
}
.admin-member-search-close {
    position:absolute;
    top:15px;
    right: 15px;
    color: white;
    font-size: 2.5em;
    cursor:pointer;
}
.admin-member-search-inputs {
  display:flex;
  justify-content:space-between;
}
.admin-member-search-input {
  background-color: white;
    color: #5f5f5f;
    border: 1px solid #ccc;
    height: 34px;
    width: 65%;
    box-sizing: border-box;
  padding: 0 10px;
}
.admin-member-search-submit {
  background-color: rgba(24,24,24,0.6);
    color: #ccc;
    border: 1px solid #ccc;
    height: 34px;
    width: 30%;
    box-sizing: border-box;
}
</style>

<div id="admin-member-search">
  <div class="admin-member-search-close">
    <i class="fa fa-times"></i>
  </div>
  <div class="admin-member-search-content">
    <form id="fpopupmembersearch" name="fpopupmembersearch" method="get">
      <input type="hidden" name="sfl" value="all" />
      <p>
        회원정보(이름/아이디)를 검색하세요.
      </p>
      <div class="admin-member-search-inputs">
        <input type="text" class="admin-member-search-input" name="stx" />
        <input type="submit" class="admin-member-search-submit" value="검색" />
      </div>
    </form>
  </div>
</div>

<script> 
$(document).ready(function() {
  // 회원 검색
  $('.admin-member-search-close').click(function() {
    $('#admin-member-search').hide();
  });
  $('#admin-member-search').click(function(e) {
    $('#admin-member-search').hide();
  }).children().click(function(e) {
    return false;
  });
  // $(document).on("keyup", function(event){
  //   // input 제외
  //   if ($(event.target).is('input')) {
  //     return;
  //   }

    //     //alt, ctrl, shift
    //     if (event.keyCode === 16 || event.keyCode === 17 || event.keyCode === 18 || event.ctrlKey || event.shiftKey || event.altKey) {
  //     return;
  //   }

  //   $('#admin-member-search').show();
    //     $('.admin-member-search-input').val('');
  //   $('.admin-member-search-input').focus();
  // });
  $(".admin-member-search-input").on("keyup", function(event){
    // esc 닫기
    if (event.keyCode === 27) {
      $('.admin-member-search-close').click();
      return;
    }

    if(event.keyCode === 13) {
      $('#fpopupmembersearch').submit();
    }
  });
  $('.admin-member-search-submit').click(function() {
    $('#fpopupmembersearch').submit();
  });
});
</script>


<script>
function fmemberlist_submit(f)
{
  if (!is_checked("chk[]")) {
    alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
    return false;
  }

  if(document.pressed == "선택삭제") {
    if(!confirm("선택회원의 기본정보만 삭제되며 아이디, 닉네임 기록은 남습니다.\n\n선택한 자료를 정말 삭제하시겠습니까?")) {
      return false;
    }
  }

  if(document.pressed == "완전삭제") {
    if(!confirm("선택회원의 회원정보 자체를 DB에서 완전히 삭제합니다.\n\n선택한 자료를 정말 삭제하시겠습니까?")) {
      return false;
    }
  }

  return true;
}
$( document ).ready(function() {

  var use_excel = false;

  $("#fr_datetime, #to_datetime, #fr_updatedatetime, #to_updatedatetime").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

  // $("#fsearch").on("submit",function(event){
  //     console.log(event);
  // });

  $("#fsearch .btn_submit").on("click",function(event){
    $('#fsearch').attr("action", "./member_list.php");
    $('#fsearch').submit();
  });

  $("#mbexcel").click(function(){
    window.location.href="./excel_mb.php" + window.location.search;
  });

  $(".temp_accept").click(function() {
    var mb_id = $(this).data('id');

    $.post('member_temp_accept.php', {
      mb_id: mb_id,
    }, 'json')
    .done(function() {
      alert('연결되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  
  $(".temp_reject").click(function() {
    var mb_id = $(this).data('id');

    $.post('member_temp_reject.php', {
      mb_id: mb_id,
    }, 'json')
    .done(function() {
      alert('거절되었습니다. 해당 계정은 삭제되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });
});

$(".accept").click(function() {
  var usrId = $(this).data('id');
  var entId = $(this).data('entid');

  $.post('member_accept.php', {
    usrId: usrId,
    entId: entId
  }, 'json')
  .done(function() {
    alert('승인되었습니다.');
    window.location.reload();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  });
});

$(".accept-rollback").click(function() {
  var usrId = $(this).data('id');
  var entId = $(this).data('entid');

  $.post('member_accept_rollback.php', {
    usrId: usrId,
    entId: entId
  }, 'json')
  .done(function() {
    alert('승인취소되었습니다.');
    window.location.reload();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  });
});


$(".mb_button").click(function() {
  var value = $(this).data('value');
  $('input[name="button_type"]').val(value);
  $('.btn_submit')[0].click();
});

function excelform(url)
{
    var opt = "width=600,height=450,left=10,top=10";
    window.open(url, "win_excel", opt);
    return false;
}
</script>

<?php
include_once ('./admin.tail.php');
?>
