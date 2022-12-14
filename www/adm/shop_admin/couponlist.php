<?php
$sub_menu = '400800';
include_once('./_common.php');

$g5['title'] = '쿠폰관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 0);

auth_check($auth[$sub_menu], "r");

$type = $_GET['type']? $_GET['type'] : "group";
$couponlist_page_rows = $page_rows?$page_rows:$_COOKIE['couponlist_page_rows'];

if($type == "group"){
    $sql_common = "
      from {$g5['g5_shop_coupon_table']} c
      left join g5_shop_coupon_member cm on c.cp_no = cm.cp_no
      left join g5_member m on cm.mb_id = m.mb_id
      left join g5_member x on c.mb_id = x.mb_id
    ";

    $sql_group = "
        group by c.cp_no
    ";

    $sql_select = "
        select c.*, x.mb_name as mb_name
    ";

    $colspan = 13;
} else {
    $sql_common = "
      from g5_shop_coupon_member cm
      left join {$g5['g5_shop_coupon_table']} c on c.cp_no = cm.cp_no
      left join g5_member m on cm.mb_id = m.mb_id
      left join g5_member x on c.mb_id = x.mb_id
      left join g5_shop_coupon_log cpl on cpl.cp_id = c.cp_id and cpl.mb_id = cm.mb_id
    ";

    $sql_group = "";

    $sql_select = "
        select cpl.cl_datetime, cm.mb_id as coupon_user_id, m.mb_name as coupon_user_name, c.*, x.mb_name as mb_name
    ";

    $colspan = 12;
}

$sql_search = ' where (1) ';

// 기간만료된 쿠폰은 제외
$cp_expiration = $search_yn?($cp_expiration?$cp_expiration:'0'):'1';
if($cp_expiration){
    $sql_search .= $cp_expiration == '1'?' and (c.cp_end >= DATE_FORMAT(NOW(),"%Y-%m-%d")) ':'';
}

// 쿠폰 종류
if ($sel_cp_method) {
  switch ($sel_cp_method) {
    case 'cp_method_it' :
      $sql_search .= " and ( c.cp_method  = '0' ) ";
      break;
    case 'cp_method_cate' :
      $sql_search .= " and ( c.cp_method  = '1' ) ";
      break;
    case 'cp_method_od' :
      $sql_search .= " and ( c.cp_method  = '2' ) ";
      break;
    case 'cp_method_del' :
      $sql_search .= " and ( c.cp_method  = '3' ) ";
      break;
  }
}

// 검색어 검색
if ($sel_field) {
  if($type == "group") {
      switch ($sel_field) {
        case 'cp_all' :
          $sql_search .= " and ( c.cp_id like '%{$search}%' or c.cp_subject like '%{$search}%' ) ";
          break;
        case 'cp_id' :
          $sql_search .= " and ( c.cp_id like '%{$search}%' ) ";
          break;
        case 'cp_name' :
          $sql_search .= " and ( c.cp_subject like '%{$search}%' ) ";
          break;
      }
  } else {
      switch ($sel_field) {
        case 'cp_all' :
          $sql_search .= " and ( m.mb_id like '%{$search}%' or m.mb_name like '%{$search}%' or c.cp_id like '%{$search}%' or c.cp_subject like '%{$search}%' ) ";
          break;
        case 'mb_id' :
          $sql_search .= " and ( m.mb_id like '%{$search}%' ) ";
          break;
        case 'mb_name' :
          $sql_search .= " and ( m.mb_name like '%{$search}%' ) ";
          break;
        case 'cp_id' :
          $sql_search .= " and ( c.cp_id like '%{$search}%' ) ";
          break;
        case 'cp_name' :
          $sql_search .= " and ( c.cp_subject like '%{$search}%' ) ";
          break;
      }
  }
}

// 날짜 검색
if ($fr_date || $to_date) {
  switch ($date_searching_option) {
    case '0' : // 생성일자
      $sql_fr_date = $fr_date?" date_format(c.cp_datetime, '%Y-%m-%d') >= date_format('{$fr_date}', '%Y-%m-%d') " :"";
      $sql_to_date = $to_date?" date_format(c.cp_datetime, '%Y-%m-%d') <= date_format('{$to_date}', '%Y-%m-%d') " :"";
      break;
    case '1' : // 사용일자
      $sql_fr_date = $fr_date?" date_format(cpl.cl_datetime, '%Y-%m-%d') >= date_format('{$fr_date}', '%Y-%m-%d') " :"";
      $sql_to_date = $to_date?" date_format(cpl.cl_datetime, '%Y-%m-%d') <= date_format('{$to_date}', '%Y-%m-%d') " :"";
      break;
    case '2' : // 사용가능기간
      $sql_fr_date = $fr_date?" date_format(c.cp_end, '%Y-%m-%d') >= date_format('{$fr_date}', '%Y-%m-%d') " :"";
      $sql_to_date = $to_date?" date_format(c.cp_start, '%Y-%m-%d') <= date_format('{$to_date}', '%Y-%m-%d') " :"";
      break;
  }
  if($fr_date && $to_date) {
      $sql_search .= " and (".$sql_fr_date." and ".$sql_to_date.") ";
  } else {
      $sql_search .= " and (".$sql_fr_date.$sql_to_date.") ";
  }
}

// 쿠폰 사용여부 체크
if ($sel_field_used) {
  switch ($sel_field_used) {
    case 'cp_used_use' :
      $sql_search .= " and (cpl.cl_datetime is not null) ";
      break;
    case 'cp_used_non' :
      $sql_search .= " and (cpl.cl_datetime is null) ";
      break;
  }
}

if (!$sst) {
    $sst  = "cp_no";
    $sod = "desc";
}

// sst : 정렬 어떤걸로(생성일자), sod : 오름차순/내림차순(내림차순)
$sql_order = " order by {$sst} {$sod} ";

$sql = "
  select count(*) as cnt
  from (
    select c.*
    {$sql_common}
    {$sql_search}
    {$sql_group}
    {$sql_order}
  ) u
";
$row = sql_fetch($sql, true);
$total_count = $row['cnt'];

$rows = $couponlist_page_rows?$couponlist_page_rows:10;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "
  {$sql_select}
  {$sql_common}
  {$sql_search}
  {$sql_group}
  {$sql_order}
  limit {$from_record}, {$rows}
";
$result = sql_query($sql, true);

// 초기 3개월 범위 적용
if (!$fr_date && !$to_date&&!$search_yn) {
    $fr_date = date("Y-m-d", strtotime("-3 month"));
    $to_date = date("Y-m-d");
}

// 기간 구분 초기화
if(!$date_searching_option) $date_searching_option = '0';

// 페이징 되는 주소 파라미터
$qstr = "type={$type}&amp;cp_expiration={$cp_expiration}&amp;sel_cp_method={$sel_cp_method}&amp;sel_field={$sel_field}&amp;search={$search}&amp;fr_date={$fr_date}&amp;to_date={$to_date}&amp;date_searching_option={$date_searching_option}&amp;sel_field_used={$sel_field_used}&amp;sst={$sst}&amp;sod={$sod}&amp;search_yn=searching";

?>
<style>
    /* 목록 바로가기 */
.coupon_menu {margin:30px 21px;padding:0;}
.coupon_menu li {float:left;margin-left:-1px;list-style:none;}
.coupon_menu:after {display:block;visibility:hidden;clear:both;content:""}
.coupon_menu a {
	border:1px solid #ddd;
    background: #f5f5f5;
    padding: 10px 20px;
}
.coupon_menu a:hover,
.coupon_menu a.on {
	color:#fff;
    border:1px solid #333;
    background:#333;
}
</style>

<!-- 쿠폰 관리 메뉴 -->
<ul class="coupon_menu">
    <li><a href="./couponlist.php?type=group" <?php if($type == "group"){echo 'style="color:#fff; border:1px solid #333; background:#333;"';} ?>>그룹으로 보기</a></li>
    <li><a href="./couponlist.php?type=user" <?php if($type == "user"){echo 'style="color:#fff; border:1px solid #333; background:#333;"';} ?>>회원별로 보기</a></li>
</ul>

<!-- 검색폼 -->
<form name="frmcouponlist" id="frmcouponlist">
    <div class="new_form">
        <table class="new_form_table" id="search_detail_table">
            <tr>
                <th>검색조건</th>
                <td>
                    <span style="margin: 0.5em 0;">쿠폰종류</span>
                    <select name="sel_cp_method" id="sel_cp_method" style="margin: 0.5em  2em;">
                        <option value="cp_method_all" <?php echo $sel_cp_method == 'od_all' ? 'selected="selected"' : ''; ?>>전체</option>
                        <option value="cp_method_it" <?php echo get_selected($sel_cp_method, 'cp_method_it'); ?>>개별상품할인</option>
                        <option value="cp_method_cate" <?php echo get_selected($sel_cp_method, 'cp_method_cate'); ?>>카테고리할인</option>
                        <option value="cp_method_od" <?php echo get_selected($sel_cp_method, 'cp_method_od'); ?>>주문금액할인</option>
                        <option value="cp_method_del" <?php echo get_selected($sel_cp_method, 'cp_method_del'); ?>>배송비할인</option>
                    </select>
                    <?php if($type=="user"){ ?>
                        <span style="margin: 0.5em 0;">쿠폰 사용여부</span>
                        <select name="sel_field_used" id="sel_field_used" style="margin: 0.5em 1.5em;">
                            <option value="cp_used_all" <?php echo $sel_field_used == 'cp_used_all' ? 'selected="selected"' : ''; ?>>전체</option>
                            <option value="cp_used_use" <?php echo get_selected($sel_field_used, 'cp_used_use'); ?>>사용</option>
                            <option value="cp_used_non" <?php echo get_selected($sel_field_used, 'cp_used_non'); ?>>미사용</option>
                        </select>
                    <?php } ?>
                    <input type="checkbox" name="cp_expiration" id="cp_expiration" value="1" title="" <?php echo option_array_checked('1', $cp_expiration); ?>><label for="cp_expiration">기간 만료 된 쿠폰 제외</label>
                </td>
            </tr>
            <tr>
                <th>기간 구분</th>
                <td>
                    <div>
                        <input type="radio" name="date_searching_option" id="date_searching_option_0" value="0" <?php echo option_array_checked('0', $date_searching_option); ?>><label for="date_searching_option_0">생성일자</label>
                        <?php if($type == "user"){?><input type="radio" name="date_searching_option" id="date_searching_option_1" value="1" <?php echo option_array_checked('1', $date_searching_option); ?>><label for="date_searching_option_1">사용일자</label><?php } ?>
                        <input type="radio" name="date_searching_option" id="date_searching_option_2" value="2" <?php echo option_array_checked('2', $date_searching_option); ?>><label for="date_searching_option_2">사용가능 기간</label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>날짜</th>
                <td class="date">
                    <div class="sch_last" style="margin-left:0;">
                        <input type="button" value="전체" id="select_date_today" name="select_date" class="select_date newbutton" />
                        <input type="button" value="내일" id="select_date_tomorrow" name="select_date" class="select_date newbutton" />
                        <input type="button" value="오늘" id="select_date_today" name="select_date" class="select_date newbutton" />
                        <input type="button" value="어제" id="select_date_yesterday" name="select_date" class="select_date newbutton" />
                        <input type="button" value="일주일" id="select_date_sevendays" name="select_date" class="select_date newbutton" />
                        <input type="button" value="이번달" id="select_date_lastmonth" name="select_date" class="select_date newbutton" />
                        <input type="text" id="fr_date" class="date" name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off"> ~
                        <input type="text" id="to_date" class="date" name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off">
                    </div>
                </td>
            </tr>
            <tr>
                <th>검색어</th>
                <td>
                    <?php if($type == "group"){ ?>
                        <select name="sel_field" id="sel_field">
                            <option value="cp_all" <?php echo $sel_field == 'cp_all' ? 'selected="selected"' : ''; ?>>전체</option>
                            <option value="cp_id" <?php echo get_selected($sel_field, 'cp_id'); ?>>쿠폰 번호</option>
                            <option value="cp_name" <?php echo get_selected($sel_field, 'cp_name'); ?>>쿠폰 이름</option>
                        </select>
                    <?php } else { ?>
                        <select name="sel_field" id="sel_field">
                            <option value="cp_all" <?php echo $sel_field == 'cp_all' ? 'selected="selected"' : ''; ?>>전체</option>
                            <option value="cp_id" <?php echo get_selected($sel_field, 'cp_id'); ?>>쿠폰 번호</option>
                            <option value="cp_name" <?php echo get_selected($sel_field, 'cp_name'); ?>>쿠폰 이름</option>
                            <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
                            <option value="mb_name" <?php echo get_selected($sel_field, 'mb_name'); ?>>회원 이름</option>
                        </select>
                    <?php } ?>
                    <input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
                    <input type="hidden" id="search_yn" name="search_yn" value="searching">
                    <button class="newbutton" type="submit" id="search-btn"><span>검색</span></button>
                </td>
            </tr>
        </table>
    </div>
    <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
</form>

<div class="local_ov">
    <span class="ov_txt">검색 개수 : </span><span class="ov_num"> <?php echo number_format($total_count) ?> 건</span>

    <select name="page_rows" id="page_rows" style="float: right; width: fit-content; padding: 0px 10px;">
        <option value="10" <?php echo $couponlist_page_rows == '10' ? 'selected="selected"' : ''; ?>>10개씩보기</option>
        <option value="30" <?php echo $couponlist_page_rows == '30' ? 'selected="selected"' : ''; ?>>30개씩보기</option>
        <option value="50" <?php echo $couponlist_page_rows == '50' ? 'selected="selected"' : ''; ?>>50개씩보기</option>
        <option value="100" <?php echo $couponlist_page_rows == '100' ? 'selected="selected"' : ''; ?>>100개씩보기</option>
    </select>
</div>

<form name="fcouponlist" id="fcouponlist" method="post" action="./couponlist_delete.php" onsubmit="return fcouponlist_submit(this);">
    <?php if($type == "group"){ ?>
        <div class="tbl_head01 tbl_wrap">
        <table>
        <caption><?php echo $g5['title']; ?></caption>
        <thead>
        <tr>
            <th scope="col">
                <label for="chkall" class="sound_only">쿠폰 전체</label>
                <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
            </th>
            <th scope="col">쿠폰번호</th>
            <th scope="col">쿠폰이름</th>
            <th scope="col">쿠폰종류</th>
            <th scope="col">쿠폰금액</th>
            <th scope="col">발행인원</th>
            <th scope="col">총발행금액</th>
            <th scope="col">사용인원</th>
            <th scope="col">총사용금액</th>
            <th scope="col">남은기간</th>
            <th scope="col">사용가능일자</th>
            <th scope="col">생성일자</th>
            <th scope="col">비고</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            switch($row['cp_method']) { // 쿠폰 종류 분류
                case '0':
                    $sql3 = " select it_name from {$g5['g5_shop_item_table']} where it_id = '{$row['cp_target']}' ";
                    $row3 = sql_fetch($sql3);
                    $cp_method = '개별상품할인';
                    $cp_target = get_text($row3['it_name']);
                    break;
                case '1':
                    $sql3 = " select ca_name from {$g5['g5_shop_category_table']} where ca_id = '{$row['cp_target']}' ";
                    $row3 = sql_fetch($sql3);
                    $cp_method = '카테고리할인';
                    $cp_target = get_text($row3['ca_name']);
                    break;
                case '2':
                    $cp_method = '주문금액할인';
                    $cp_target = '주문금액';
                    break;
                case '3':
                    $cp_method = '배송비할인';
                    $cp_target = '배송비';
                    break;
            }

            $link1 = '<a href="./orderform.php?od_id='.$row['od_id'].'">';
            $link2 = '</a>';

            // 쿠폰발행인원
            $sql = " select count(*) as cnt from g5_shop_coupon_member where cp_no = '{$row['cp_no']}' ";
            $tmp = sql_fetch($sql);
            $total_count = $tmp['cnt'];

            // 쿠폰사용회수
            $sql = " select count(*) as cnt from {$g5['g5_shop_coupon_log_table']} where cp_id = '{$row['cp_id']}' ";
            $tmp = sql_fetch($sql);
            $used_count = $tmp['cnt'];

            switch($row['cp_type']) { //할인금액(정액할인/정률할인)
                case '0':
                    $cp_type = "정액할인";
                    $cp_price = number_format($row['cp_price']);
                    $total_cp_price = number_format($row['cp_price']*$total_count);
                    $total_used_price = number_format($row['cp_price']*$used_count);
                    break;
                case '1':
                    $cp_type = "정률할인";
                    $cp_price = $row['cp_price'].'%';
                    $total_cp_price = '-';
                    $total_used_price = '-';
                    break;
            }

            $datetime_end = new DateTime(date("Y-m-d", strtotime($row['cp_end'])));
            $datetime_now = new DateTime(date("Y-m-d"));

            if ($datetime_end >= $datetime_now) { // 쿠폰 종료일이 오늘보다 뒤이면
                $interval = date_diff( $datetime_end, $datetime_now );
            } else {
                $interval = '기간만료';
            }

            $bg = 'bg'.($i%2);
        ?>

        <tr class="<?php echo $bg; ?>">
            <td class="td_chk">
                <input type="hidden" id="cp_id_<?php echo $i; ?>" name="cp_id[<?php echo $i; ?>]" value="<?php echo $row['cp_id']; ?>">
                <input type="checkbox" id="chk_<?php echo $i; ?>" name="chk[]" value="<?php echo $i; ?>" title="내역선택" <?php if($used_count != 0){ echo "disabled";} ?>>
            </td>
            <td class="td_category1"><a href="couponlist.php?type=user&sel_field=cp_id&search=<?php echo $row['cp_id']; ?>"><?php echo $row['cp_id']; ?></a></td> <!-- 쿠폰번호 -->
            <td class="td_mng_l td_center"><?php echo $row['cp_subject']; ?></td> <!-- 쿠폰이름 -->
            <td class="td_category3"><?php echo $cp_method; ?></td> <!-- 쿠폰종류 -->
            <td class="td_send td_price"><?=$cp_price?></td> <!-- 쿠폰금액 -->
            <td class="td_send"><?=$total_count?></td> <!-- 발행인원 -->
            <td class="td_send td_price"><?=$total_cp_price?></td> <!-- 총발행금액 -->
            <td class="td_send"><?=$used_count?></td> <!-- 시용인원 -->
            <td class="td_send td_price"><?=$total_used_price?></td> <!-- 총 사용금액 -->
            <td class="td_send td_price"><?php if($interval=='기간만료'){ echo $interval; } else{ echo ($interval->days+1).'일';} ?></td> <!-- 남은기간 -->
            <td class="td_datetime"><?php echo $row['cp_start']; ?> ~ <?php echo $row['cp_end']; ?></td> <!-- 시용가능일자 -->
            <td class="td_date"><?php echo date("Y-m-d H:m:i", strtotime($row['cp_datetime'])); ?></td> <!-- 생성일자 -->
            <td class="td_mng td_mng_s">
                <a href="./couponform.php?w=u&amp;cp_id=<?php echo $row['cp_id']; ?>&amp;<?php echo $qstr; ?>" class="btn btn_03"><span class="sound_only"><?php echo $row['cp_id']; ?> </span>수정</a>
            </td>
        </tr>

        <?php
        }

        if ($i == 0)
            echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>
    <?php } else if($type == "user") { ?>
        <div class="tbl_head01 tbl_wrap">
        <table>
        <caption><?php echo $g5['title']; ?></caption>
        <thead>
        <tr>
            <th scope="col">
                <label class="sound_only">쿠폰 전체</label>
            </th>
            <th scope="col">쿠폰번호</th>
            <th scope="col">회원ID</th>
            <th scope="col">회원이름</th>
            <th scope="col">쿠폰이름</th>
            <th scope="col">쿠폰종류</th>
            <th scope="col">쿠폰금액</th>
            <th scope="col">남은기간</th>
            <th scope="col">사용가능일자</th>
            <th scope="col">생성일자</th>
            <th scope="col">사용일자</th>
            <th scope="col">비고</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            switch($row['cp_method']) { // 쿠폰 종류 분류
                case '0':
                    $sql3 = " select it_name from {$g5['g5_shop_item_table']} where it_id = '{$row['cp_target']}' ";
                    $row3 = sql_fetch($sql3);
                    $cp_method = '개별상품할인';
                    $cp_target = get_text($row3['it_name']);
                    break;
                case '1':
                    $sql3 = " select ca_name from {$g5['g5_shop_category_table']} where ca_id = '{$row['cp_target']}' ";
                    $row3 = sql_fetch($sql3);
                    $cp_method = '카테고리할인';
                    $cp_target = get_text($row3['ca_name']);
                    break;
                case '2':
                    $cp_method = '주문금액할인';
                    $cp_target = '주문금액';
                    break;
                case '3':
                    $cp_method = '배송비할인';
                    $cp_target = '배송비';
                    break;
            }

            $link1 = '<a href="./orderform.php?od_id='.$row['od_id'].'">';
            $link2 = '</a>';

            // 쿠폰사용일자
            $used_date = $row['cl_datetime'];

            switch($row['cp_type']) { //할인금액(정액할인/정률할인)
                case '0':
                    $cp_type = "정액할인";
                    $cp_price = number_format($row['cp_price']);
                    break;
                case '1':
                    $cp_type = "정률할인";
                    $cp_price = $row['cp_price'].'%';
                    break;
            }

            //남은기간 계산
            $datetime_end = new DateTime(date("Y-m-d", strtotime($row['cp_end'])));
            $datetime_now = new DateTime(date("Y-m-d"));

            if ($datetime_end >= $datetime_now) { // 쿠폰 종료일이 오늘보다 뒤이면
                $interval = date_diff( $datetime_end, $datetime_now );
            } else {
                $interval = '기간만료';
            }

            $bg = 'bg'.($i%2);
        ?>


        <tr class="<?php echo $bg; ?>">
            <td class="cp_index td_numsmall"><?=($total_count-($page-1)*15)-$i;?></td> <!-- 인덱스 -->
            <td class="cp_id td_category1"><?php echo $row['cp_id']; ?></td> <!-- 쿠폰ID -->
            <td class="cp_user_id td_category3"><?php echo $row['coupon_user_id']; ?></td> <!-- 쿠폰받은 회원 ID -->
            <td class="cp_user_name td_type td_center"><?php echo $row['coupon_user_name']; ?></td> <!-- 쿠폰받은 회원 이름 -->
            <td class="cp_subject td_mng_l td_center"><?php echo $row['cp_subject']; ?></td> <!-- 쿠폰이름 -->
            <td class="cp_method td_category3"><?php echo $cp_method; ?></td> <!-- 쿠폰종류 -->
            <td class="cp_price td_send td_price"><?php echo $cp_price; ?></td> <!-- 쿠폰금액 -->
            <td class="cp_interval td_send td_price"><?php if($interval=='기간만료'){ echo $interval; } else{ echo ($interval->days+1).'일';} ?></td> <!-- 남은기간 -->
            <td class="cp_using_date td_datetime"><?php echo $row['cp_start']; ?> ~ <?php echo $row['cp_end']; ?></td> <!-- 사용가능일자 -->
            <td class="cp_datetime td_delicom td_center"><?php echo date("Y-m-d H:m:i", strtotime($row['cp_datetime'])); ?></td> <!-- 생성일자 -->
            <td class="cp_used_datetime td_delicom td_center"><?php if($used_date){ echo date("Y-m-d H:m:i", strtotime($used_date)); } ?></td> <!-- 사용일자 -->
            <td class="cp_etc td_mng td_mng_s"> <!-- 비고 : 이미 사용한 쿠폰은 삭제할 수 없다. -->
                <?php if(!$used_date){ ?><a href="./couponlist_delete.php?cp_no=<?php echo $row['cp_no']; ?>&amp;mb_id=<?php echo $row['coupon_user_id']; ?>&amp;<?php echo $qstr; ?>" class="btn btn_03"><span class="sound_only"><?php echo $row['cp_id']; ?> </span>삭제</a> <?php } ?>
            </td>
        </tr>

        <?php
        }

        if ($i == 0)
            echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>
    <?php } ?>

    <div class="btn_fixed_top">
        <?php if($type=="group") { // 그룹별 삭제만 선택삭제?>
            <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
        <?php } ?>
       <a href="./couponform.php" id="coupon_add" class="btn btn_01">쿠폰 추가</a>
    </div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function () {
    $('#fr_date, #to_date').datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        showButtonPanel: true,
        yearRange: 'c-99:c+99',
        maxDate: '+0d',
    });

    $('.select_date').click(function () {
      var val = $(this).val();
      set_date(val);
    });
});

$(document).on('change','#page_rows',function(){ // 10/15/20/.../개씩 보기 변경 시
  var couponlist_page_rows = $("#page_rows option:selected").val();
  console.log($("#page_rows option:selected").val());
  // console.log(recipient_page_rows);
  $.cookie('couponlist_page_rows', couponlist_page_rows);
  window.location.reload();
});

function fcouponlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>