<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '상품관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

// 분류
$ca_list  = '<option value="">선택</option>'.PHP_EOL;
$sql = " select * from {$g5['g5_shop_category_table']} ";
// if ($is_admin != 'super')
//     $sql .= " where ca_mb_id = '{$member['mb_id']}' ";
$sql .= " order by ca_id, ca_order ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $len = strlen($row['ca_id']) / 2 - 1;
    $nbsp = '';
    for ($i=0; $i<$len; $i++) {
        $nbsp .= '&nbsp;&nbsp;&nbsp;';
    }

	if($row['as_line']) {
		$ca_list .= "<option value=\"\">".$nbsp."------------</option>\n";
	}

    $ca_list .= '<option value="'.$row['ca_id'].'">'.$nbsp.$row['ca_name'].'</option>'.PHP_EOL;
}

$where = " and ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "") {
        if($sfl == 'all') {
            $attrs = ['it_thezone', 'it_name', 'it_model', 'it_id', 'it_maker', 'it_origin', 'it_expected_warehousing_date', 'it_default_warehouse'];
            $sql_search .= " $where ( 1 != 1 ";
            $where = ' or ';
            foreach($attrs as $attr) {
                $sql_search .= " $where $attr like '%$stx%' ";
            }
            $sql_search .= ' ) ';
            $where = ' and ';
        } else {
            if ($sfl == 'it_default_warehouse_empty') {
                $sql_search .= " $where it_default_warehouse = '' ";    
            }
            else {
                $sql_search .= " $where $sfl like '%$stx%' ";
            }
            $where = " and ";
        }
    }
    if ($save_stx != $stx)
        $page = 1;
}
else {
    if ($sfl == 'it_default_warehouse_empty') {
        // 출하창고 미지정 검색인 경우
        $sql_search .=" AND it_default_warehouse = '' ";  
    }
}

// 상품태그
$it_type_where = [];
for($i = 1; $i <= 11; $i++) {
  $it_type = 'it_type'.$i;
  if($_GET[$it_type]) {
    $it_type_where[] = " {$it_type} = 1 ";
    $qstr .= "&amp;{$it_type}=1";
  }
}
if($it_type_where) {
  $sql_search .= ' AND (' . implode(' OR ', $it_type_where) . ') ';
}

if($_GET["searchProdSupYN"] != ""){
	$sql_search .= " AND prodSupYn = '{$_GET["searchProdSupYN"]}' ";
}

if ($sca != "") {
    // $sql_search .= " $where (a.ca_id like '$sca%' or a.ca_id2 like '$sca%' or a.ca_id3 like '$sca%') ";
    $sql_search .= " $where (a.ca_id like '$sca%' or
        a.ca_id2 like '$sca%' or
        a.ca_id3 like '$sca%' or
        a.ca_id4 like '$sca%' or
        a.ca_id5 like '$sca%' or
        a.ca_id6 like '$sca%' or
        a.ca_id7 like '$sca%' or
        a.ca_id8 like '$sca%' or
        a.ca_id9 like '$sca%' or
        a.ca_id10 like '$sca%' )
        ";
}

if ($sfl == "")  $sfl = "all";

$sql_common = " from {$g5['g5_shop_item_table']} a ,
                     {$g5['g5_shop_category_table']} b
               where (a.ca_id = b.ca_id";
// if ($is_admin != 'super')
//     $sql_common .= " and b.ca_mb_id = '{$member['mb_id']}'";
$sql_common .= ") ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

//$rows = $config['cf_page_rows'];
$page_rows = (int)$page_rows ? (int)$page_rows : $config['cf_page_rows'];;
$rows = $page_rows;

$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
	$sst = "it_id";
    $sod = "desc";
}

// if($sst == 'it_id') {
// 	$pth = "a.pt_num desc,";
// 	$ptt = "";
// } else {
// 	$pth = "";
// 	$ptt = ", a.pt_num desc";
// }

if ($orderby === 'it_name') {
    $sst = "it_name";
    $sod = "asc";
}

$sql_order = "order by $pth $sst $sod $ptt";

$sql  = " select *
           $sql_common
           $sql_order
           limit $from_record, $rows "; 
$result = sql_query($sql, true);

//$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;
$qstr .= '&amp;sca='.$sca.'&amp;page='.$page.'&amp;page_rows='.$page_rows.'&amp;save_stx='.$stx."&amp;searchProdSupYN=".$_GET["searchProdSupYN"];
if($api_it_id) $qstr  .= '&amp;api_it_id='.$api_it_id;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// APMS - 2014.07.25
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');
$flist = array();
$flist = apms_form(1,0);

// 입고예정일 수정한 상품 목록
//$affected_it_ids = $_GET['it_id'] ?: [];
?>

<script src="<?php echo G5_ADMIN_URL;?>/apms_admin/apms.admin.js"></script>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">등록된 상품</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
    <div class="right">
        <button id="itemprice">상품가격관리</button>
        <button id="itemexcel_all"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_img_ex.gif">엑셀전체다운로드</button>
        <button id="itemexcel"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/btn_img_ex.gif">엑셀다운로드</button>
    </div>
</div>

<form name="flist" class="local_sch01 local_sch">
  <div class="local_sch03 local_sch">
    <div class="sch_last">
      <strong>상품태그</strong>
      <label>
        <input type="checkbox" name="it_type1" value="1" <?=get_checked($it_type1, 1)?>>
        <span style="display:inline-block; border:1px solid <?=$default['de_it_type1_color']?>;color:<?=$default['de_it_type1_color']?>"><?=$default['de_it_type1_name']?></span>
      </label>
      <label>
        <input type="checkbox" name="it_type2" value="1" <?=get_checked($it_type2, 1)?>>
        <span style="display:inline-block; border:1px solid <?=$default['de_it_type2_color']?>;color:<?=$default['de_it_type2_color']?>"><?=$default['de_it_type2_name']?></span>
      </label>
      <label>
        <input type="checkbox" name="it_type3" value="1" <?=get_checked($it_type3, 1)?>>
        <span style="display:inline-block; border:1px solid <?=$default['de_it_type3_color']?>;color:<?=$default['de_it_type3_color']?>"><?=$default['de_it_type3_name']?></span>
      </label>
      <label>
        <input type="checkbox" name="it_type4" value="1" <?=get_checked($it_type4, 1)?>>
        <span style="display:inline-block; border:1px solid <?=$default['de_it_type4_color']?>;color:<?=$default['de_it_type4_color']?>"><?=$default['de_it_type4_name']?></span>
      </label>
      <label>
        <input type="checkbox" name="it_type5" value="1" <?=get_checked($it_type5, 1)?>>
        <span style="display:inline-block; border:1px solid <?=$default['de_it_type5_color']?>;color:<?=$default['de_it_type5_color']?>"><?=$default['de_it_type5_name']?></span>
      </label>
      <?php
      for($i = 6; $i <= 11; $i++) {
        $cur_it_type = 'it_type' . $i;
        if($default['de_'. $cur_it_type .'_name']) {
      ?>
      <label>
        <input type="checkbox" name="<?=$cur_it_type?>" value="1" <?=get_checked($$cur_it_type, 1)?>>
        <span style="display:inline-block; border:1px solid <?=$default['de_'. $cur_it_type .'_color']?>;color:<?=$default['de_'. $cur_it_type .'_color']?>"><?=$default['de_'. $cur_it_type .'_name']?></span>
      </label>
      <?php
        }
      }
      ?>
    </div>
  </div>
  <input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

  <label for="sca" class="sound_only">분류선택</label>
  <select name="sca" id="sca">
    <option value="">전체분류</option>
    <?php
    $sql1 = " select ca_id, ca_name, as_line from {$g5['g5_shop_category_table']} order by ca_id, ca_order ";
    $result1 = sql_query($sql1);
    for ($i=0; $row1=sql_fetch_array($result1); $i++) {
      $len = strlen($row1['ca_id']) / 2 - 1;
      $nbsp = '';
      for ($i=0; $i<$len; $i++) $nbsp .= '&nbsp;&nbsp;&nbsp;';

      if($row1['as_line']) {
        echo "<option value=\"\">".$nbsp."------------</option>\n";
      }

      echo '<option value="'.$row1['ca_id'].'" '.get_selected($sca, $row1['ca_id']).'>'.$nbsp.$row1['ca_name'].'</option>'.PHP_EOL;
    }
    ?>
  </select>

  <label for="searchProdSupYN" class="sound_only">유통구분</label>
  <select name="searchProdSupYN" id="searchProdSupYN">
    <option value="">유통구분 전체</option>
    <option value="Y" <?=($_GET["searchProdSupYN"] == "Y") ? "selected" : ""?>>유통</option>
    <option value="N" <?=($_GET["searchProdSupYN"] == "N") ? "selected" : ""?>>비유통</option>
  </select>

  <script>
  $( '#searchProdSupYN' ).change( function(){
    $('#searchProdSupYN2').val($(this).val());
  });
  </script>

  <label for="sfl" class="sound_only">검색대상</label>
  <select name="sfl" id="sfl">
    <option value="all" <?php echo get_selected($sfl, 'all'); ?>>전체</option>
    <option value="it_thezone" <?php echo get_selected($sfl, 'it_thezone'); ?>>분류코드</option>
    <option value="it_name" <?php echo get_selected($sfl, 'it_name'); ?>>상품명</option>
    <option value="it_model" <?php echo get_selected($sfl, 'it_model'); ?>>모델명</option>
    <option value="it_id" <?php echo get_selected($sfl, 'it_id'); ?>>상품코드</option>
    <option value="it_maker" <?php echo get_selected($sfl, 'it_maker'); ?>>제조사</option>
    <option value="it_origin" <?php echo get_selected($sfl, 'it_origin'); ?>>원산지</option>
    <option value="it_sell_email" <?php echo get_selected($sfl, 'it_sell_email'); ?>>판매자 e-mail</option>
    <!-- APMS - 2014.07.20 -->
    <option value="pt_id" <?php echo get_selected($sfl, 'pt_id'); ?>>파트너 아이디</option>
    <!-- // -->
    <option value="it_expected_warehousing_date" <?php echo get_selected($sfl, 'it_expected_warehousing_date'); ?>>입고예정알림</option>
    <option value="it_default_warehouse" <?php echo get_selected($sfl, 'it_default_warehouse'); ?>>출하창고</option>
    <option value="it_default_warehouse_empty" <?php echo get_selected($sfl, 'it_default_warehouse_empty'); ?>>출하창고 미지정</option>
  </select>

  <label for="stx" class="sound_only">검색어</label>
  <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" class="frm_input">
  <input type="submit" value="검색" class="btn_submit">

  <div class="right">
    <select name="orderby" id="orderby">
      <option value="it_id" <?php echo $orderby == 'it_id' || !$orderby ? 'selected' : ''; ?>>최근등록순 정렬</option>
      <option value="it_name" <?php echo $orderby == 'it_name' ? 'selected' : ''; ?>>가나다순 정렬</option>
    </select>
    <select name="page_rows" id="page_rows">
      <option value="10" <?php echo $page_rows == '10' ? 'selected' : ''; ?>>10개씩보기</option>
      <option value="15" <?php echo $page_rows == '15' ? 'selected' : ''; ?>>15개씩보기</option>
      <option value="20" <?php echo $page_rows == '20' ? 'selected' : ''; ?>>20개씩보기</option>
      <option value="50" <?php echo $page_rows == '50' ? 'selected' : ''; ?>>50개씩보기</option>
      <option value="100" <?php echo $page_rows == '100' ? 'selected' : ''; ?>>100개씩보기</option>
      <option value="200" <?php echo $page_rows == '200' ? 'selected' : ''; ?>>200개씩보기</option>
    </select>
  </div>
</form>

<form id="fitemlistupdate" name="fitemlistupdate" method="post" action="./itemlistupdate.php" onsubmit="return fitemlist_submit(this);" autocomplete="off">
<input type="hidden" name="sca" value="<?php echo $sca; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="page_rows" value="<?php echo $page_rows; ?>">
<input type="hidden" name="searchProdSupYN" value="" id="searchProdSupYN2">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" rowspan="3">
            <label for="chkall" class="sound_only">상품 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col" rowspan="3"><?php echo subject_sort_link('it_id', 'sca='.$sca); ?>상품ID</a></th>
        <th scope="col" colspan="7">분류</th>
		<th scope="col" rowspan="3">관리자메모</th>
		<th scope="col" rowspan="3"><?php echo subject_sort_link('it_order', 'sca='.$sca); ?>순서</a></th>
        <th scope="col" rowspan="3"><?php echo subject_sort_link('it_use', 'sca='.$sca, 1); ?>판매</a></th>
        <th scope="col" rowspan="3"><?php echo subject_sort_link('it_soldout', 'sca='.$sca, 1); ?>품절</a></th>
        <th scope="col" rowspan="3"><?php echo subject_sort_link('it_hit', 'sca='.$sca, 1); ?>조회</a></th>
        <th scope="col" rowspan="3">상품태그</th>
        <th scope="col" rowspan="3">관리</th>
    </tr>
    <tr>
        <th scope="col" rowspan="2" id="th_img">이미지</th>


        <!-- <th scope="col" rowspan="2" id="th_pc_title"><?php echo subject_sort_link('it_name', 'sca='.$sca); ?>상품정보 (모델명 / 상품코드 / 상품명)</a></th>
		<th scope="col" rowspan="2" id="th_amt"><?php echo subject_sort_link('it_price', 'sca='.$sca); ?>판매가격</a></th>
        <th scope="col" rowspan="2" id="th_amt"><?php echo subject_sort_link('it_price_dealer', 'sca='.$sca); ?>사업소가격</a></th>
        <th scope="col" rowspan="2" id="th_amt"><?php echo subject_sort_link('it_price_dealer2', 'sca='.$sca); ?>우수사업소가격</a></th>
        <th scope="col" rowspan="2" id="th_amt"><?php echo subject_sort_link('it_price_partner', 'sca='.$sca); ?>파트너가격</a></th> -->

        <th scope="col" rowspan="2" id="th_pc_title"><?php echo subject_sort_link('it_name', 'sca='.$sca); ?>상품정보 (모델명 / 입고예정일알림 / 상품명)</a></th>
		<th scope="col" rowspan="2" id="th_amt"><?php echo subject_sort_link('it_price', 'sca='.$sca); ?>판매가격</a></th>
        <th scope="col" rowspan="2" id="th_amt"><?php echo subject_sort_link('it_cust_price', 'sca='.$sca); ?>급여가</a></th>
        <th scope="col" rowspan="2" id="th_amt"><?php echo subject_sort_link('it_price_dealer', 'sca='.$sca); ?>사업소가격</a></th>
        <th scope="col" rowspan="2" id="th_amt"><?php echo subject_sort_link('it_price_dealer2', 'sca='.$sca); ?>우수사업소가격</a></th>

        
        <!--<th scope="col" id="th_camt"><?php echo subject_sort_link('it_cust_price', 'sca='.$sca); ?>시중가격</a></th>-->
		<!-- APMS - 2014.07.20 -->
        <!--
			<th scope="col" id="th_fee"><?php echo subject_sort_link('pt_commission', 'sca='.$sca); ?>수수료(%)</a></th>
			<th scope="col" id="th_start"><?php echo subject_sort_link('pt_reserve', 'sca='.$sca); ?>예약일</a></th>
	        <th scope="col" id="th_type">상품종류</a></th>
            -->
		<!-- // -->
    </tr>
    <tr>
        <!--
		<th scope="col" id="th_pt"><?php echo subject_sort_link('it_point', 'sca='.$sca); ?>포인트</a></th>
        <th scope="col" id="th_qty"><?php echo subject_sort_link('it_stock_qty', 'sca='.$sca); ?>재고</a></th>
        -->
		<!-- APMS - 2014.07.20 -->
            <!--
			<th scope="col" id="th_icnt"><?php echo subject_sort_link('pt_incentive', 'sca='.$sca); ?>인센티브(%)</a></th>
			<th scope="col" id="th_end"><?php echo subject_sort_link('pt_end', 'sca='.$sca); ?>종료일</a></th>
            -->
	        <!--<th scope="col" id="th_cmt">댓글사용</th>
            <th scope="col" id="th_cmt">상품종류</th>-->
		<!-- // -->
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $href = G5_SHOP_URL.'/item.php?it_id='.$row['it_id'];
        $bg = 'bg'.($i%2);

        $it_point = $row['it_point'];
        if($row['it_point_type'])
            $it_point .= '%';
    ?>
    <tr class="<?php echo $bg; ?>">
        <td rowspan="3" class="td_chk">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['it_name']); ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i; ?>">
        </td>
		<!-- APMS - 2014.07.20 -->
		<td rowspan="3" class="td_num" style="white-space:nowrap; position: relative;">
       		<b style="position: absolute; width: 40px; height: 20px; line-height: 20px; top: 5px; right: 5px; border-radius: 5px; color: #FFF; background-color: #<?=($row["prodSupYn"] == "Y") ? "3366CC" : "DC3333"?>; font-size: 11px; text-align: center;"><?=($row["prodSupYn"] == "Y") ? "유통" : "비유통"?></b>
            <input type="hidden" id="it_id" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
			<?php if($row['pt_it']) { ?>
				<div style="font-size:11px; letter-spacing:-1px;"><?php echo apms_pt_it($row['pt_it'],1);?></div>
			<?php } ?>
			<b><?php echo $row['it_id']; ?></b>
			<?php if($row['pt_id']) { ?>
				<div style="font-size:11px; letter-spacing:-1px;"><?php echo $row['pt_id'];?></div>
			<?php } ?>
        </td>
		<!-- // -->
		<td colspan="7" class="td_sort">
            <label for="ca_id_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['it_name']); ?> 기본분류</label>
            <select name="ca_id[<?php echo $i; ?>]" id="ca_id_<?php echo $i; ?>" required>
                <?php echo conv_selected_option($ca_list, $row['ca_id']); ?>
            </select>
            <label for="ca_id2_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['it_name']); ?> 2차분류</label>
            <select name="ca_id2[<?php echo $i; ?>]" id="ca_id2_<?php echo $i; ?>">
                <?php echo conv_selected_option($ca_list, $row['ca_id2']); ?>
            </select>
            <label for="ca_id3_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['it_name']); ?> 3차분류</label>
            <select name="ca_id3[<?php echo $i; ?>]" id="ca_id3_<?php echo $i; ?>">
                <?php echo conv_selected_option($ca_list, $row['ca_id3']); ?>
            </select>
            <label for="ca_id4_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['it_name']); ?> 4차분류</label>
            <select name="ca_id4[<?php echo $i; ?>]" id="ca_id4_<?php echo $i; ?>">
                <?php echo conv_selected_option($ca_list, $row['ca_id4']); ?>
            </select>
            <label for="ca_id5_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['it_name']); ?> 5차분류</label>
            <select name="ca_id5[<?php echo $i; ?>]" id="ca_id5_<?php echo $i; ?>">
                <?php echo conv_selected_option($ca_list, $row['ca_id5']); ?>
            </select>
        </td>
       
		<td rowspan="3" class="td_mngsmall">
            <label for="order_<?php echo $i; ?>" class="sound_only">관리자메모</label>
            <?php echo get_text($row['it_admin_memo']); ?>
        </td>
		<td rowspan="3" class="td_mngsmall">
            <label for="order_<?php echo $i; ?>" class="sound_only">순서</label>
            <input type="text" name="it_order[<?php echo $i; ?>]" value="<?php echo $row['it_order']; ?>" id="order_<?php echo $i; ?>" class="frm_input" size="3">
        </td>
        <td rowspan="3" class="td_mngsmall">
            <label for="use_<?php echo $i; ?>" class="sound_only">판매여부</label>
            <input type="checkbox" name="it_use[<?php echo $i; ?>]" <?php echo ($row['it_use'] ? 'checked' : ''); ?> value="1" id="use_<?php echo $i; ?>">일반몰
            <br/>
            <label for="use_partner_<?php echo $i; ?>" class="sound_only">파트너몰 판매여부</label>
            <input type="checkbox" name="it_use_partner[<?php echo $i; ?>]" <?php echo ($row['it_use_partner'] ? 'checked' : ''); ?> value="1" id="use_partner_<?php echo $i; ?>">파트너몰
        </td>
        <td rowspan="3" class="td_mngsmall">
            <label for="soldout_<?php echo $i; ?>" class="sound_only">품절</label>
            <input type="checkbox" name="it_soldout[<?php echo $i; ?>]" <?php echo ($row['it_soldout'] ? 'checked' : ''); ?> value="1" id="soldout_<?php echo $i; ?>">
        </td>
        <td rowspan="3" class="td_num"><?php echo $row['it_hit']; ?></td>
        <td rowspan="3" class="td_mngsmall" style="min-width:150px">
            <input type="checkbox" name="it_type1[<?php echo $i; ?>]" value="1" <?php echo ($row['it_type1'] ? "checked" : ""); ?> id="it_type1_<?php echo $i; ?>">
            <label for="it_type1_<?php echo $i; ?>"><span style="border:1px solid <?php echo $default['de_it_type1_color']; ?>;color:<?php echo $default['de_it_type1_color']; ?>"><?php echo $default['de_it_type1_name']; ?></span></label>
            <br/>
            <input type="checkbox" name="it_type2[<?php echo $i; ?>]" value="1" <?php echo ($row['it_type2'] ? "checked" : ""); ?> id="it_type2_<?php echo $i; ?>">
            <label for="it_type2_<?php echo $i; ?>"><span style="border:1px solid <?php echo $default['de_it_type2_color']; ?>;color:<?php echo $default['de_it_type2_color']; ?>"><?php echo $default['de_it_type2_name']; ?></span></label>
            <br/>
            <input type="checkbox" name="it_type3[<?php echo $i; ?>]" value="1" <?php echo ($row['it_type3'] ? "checked" : ""); ?> id="it_type3_<?php echo $i; ?>">
            <label for="it_type3_<?php echo $i; ?>"><span style="border:1px solid <?php echo $default['de_it_type3_color']; ?>;color:<?php echo $default['de_it_type3_color']; ?>"><?php echo $default['de_it_type3_name']; ?></span></label>
            <br/>
            <input type="checkbox" name="it_type4[<?php echo $i; ?>]" value="1" <?php echo ($row['it_type4'] ? "checked" : ""); ?> id="it_type4_<?php echo $i; ?>">
            <label for="it_type4_<?php echo $i; ?>"><span style="border:1px solid <?php echo $default['de_it_type4_color']; ?>;color:<?php echo $default['de_it_type4_color']; ?>"><?php echo $default['de_it_type4_name']; ?></span></label>
            <br/>
            <input type="checkbox" name="it_type5[<?php echo $i; ?>]" value="1" <?php echo ($row['it_type5'] ? "checked" : ""); ?> id="it_type5_<?php echo $i; ?>">
            <label for="it_type5_<?php echo $i; ?>"><span style="border:1px solid <?php echo $default['de_it_type5_color']; ?>;color:<?php echo $default['de_it_type5_color']; ?>"><?php echo $default['de_it_type5_name']; ?></span></label>
            <?php
            for($x = 6; $x <= 11; $x++) {
              $cur_it_type = 'it_type' . $x;
              if($default['de_'. $cur_it_type .'_name']) {
            ?>
            <br/>
            <input type="checkbox" name="<?=$cur_it_type?>[<?php echo $i; ?>]" value="1" <?php echo ($row[$cur_it_type] ? "checked" : ""); ?> id="<?=$cur_it_type?>_<?php echo $i; ?>">
            <label for="<?=$cur_it_type?>_<?php echo $i; ?>"><span style="border:1px solid <?php echo $default['de_' . $cur_it_type . '_color']; ?>;color:<?php echo $default['de_' . $cur_it_type . '_color']; ?>"><?php echo $default['de_' . $cur_it_type . '_name']; ?></span></label>
            <?php if($x == 11){ ?>
                <input class="frm_input" size="5" type="time" name="it_deadline[<?php echo $i; ?>]" value="<?php echo ($row['it_deadline'] ? : "00:00"); ?>" id="it_deadline_<?php echo $i; ?>" style="text-align: center; <?php echo ($row['it_type11'] ? 'background-color : white;':''); ?>" <?php echo ($row['it_type11'] ? '':'disabled'); ?>>
            <?php }
              }
            }
            ?>
        </td>
        <td rowspan="3" class="td_mng td_mng_s">
            <a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>&amp;fn=<?php echo $row['pt_form'];?>&amp;ca_id=<?php echo $row['ca_id']; ?>&amp;<?php echo $qstr; ?>" class="btn btn_03"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>수정</a>
            <a href="./itemcopy.php?it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>" class="itemcopy btn btn_02" target="_blank"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>복사</a>
            <a href="<?php echo $href; ?>" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>보기</a>
			<a href="<?php echo $_SERVER['SCRIPT_NAME'].'?api_it_id='.$row['it_id'].'&$qstr&amp;page='; ?>" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>정보반영</a>
        </td>
    </tr>
    <tr class="<?php echo $bg; ?>">
        <td rowspan="2" class="td_img"><a href="<?php echo $href; ?>"><img src="/data/item/<?=$row["it_img1"]?>" style="width: 50px;" onerror="this.src='/shop/img/no_image.gif'"></a></td>
        <td headers="th_pc_title" rowspan="2" class="td_input td_left" style="font-size: 0;">
			<!-- <?php echo help("상품등록폼 : ".apms_form_option('name', $flist, $row['pt_form']));?>  -->
			<!--모델명 : <?php echo htmlspecialchars2(cut_str($row['it_model'],250, "")); ?>, 상품코드 : <?php echo htmlspecialchars2(cut_str($row['it_thezone'],250, "")); ?>-->
            <label for="model_<?php echo $i; ?>" class="sound_only">모델명</label>
            <input type="text" name="it_model[<?php echo $i; ?>]" value="<?php echo htmlspecialchars2(cut_str($row['it_model'],250, "")); ?>" id="model_<?php echo $i; ?>" class="frm_input" size="30" placeholder="모델명" style="width:50%">

            <label for="expectedwarehousingdate_<?php echo $i; ?>" class="sound_only">입고 예정일 알림</label>
            <input type="text" name="it_expected_warehousing_date[<?php echo $i; ?>]" value="<?php echo htmlspecialchars2(cut_str($row['it_expected_warehousing_date'],250, "")); ?>" id="expectedwarehousingdate_<?php echo $i; ?>" class="frm_input" size="30" placeholder="입고 예정일 알림(최대 44자)" maxlength="44" style="width:50%">

            <label for="name_<?php echo $i; ?>" class="sound_only">상품명</label>
            <input type="text" name="it_name[<?php echo $i; ?>]" value="<?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?>" id="name_<?php echo $i; ?>" required class="frm_input required" size="30">
        </td>

        <td rowspan="2" headers="th_amt" class="td_numbig td_input">
            <label for="price_<?php echo $i; ?>" class="sound_only">판매가격</label>
            <input type="text" name="it_price[<?php echo $i; ?>]" value="<?php echo $row['it_price']; ?>" id="price_<?php echo $i; ?>" class="frm_input sit_amt" size="7">
        </td>
        <td rowspan="2" headers="th_amt" class="td_numbig td_input">
        <label for="it_cust_price_<?php echo $i; ?>" class="sound_only">급여가</label>
            <input type="text" name="it_cust_price[<?php echo $i; ?>]" value="<?php echo $row['it_cust_price']; ?>" id="it_cust_price_<?php echo $i; ?>" class="frm_input sit_camt" size="7">
        </td>

        <td rowspan="2" headers="th_camt" class="td_numbig td_input">
            <label for="it_price_dealer_<?php echo $i; ?>" class="sound_only">사업소가격</label>
            <input type="text" name="it_price_dealer[<?php echo $i; ?>]" value="<?php echo $row['it_price_dealer']; ?>" id="it_price_dealer_<?php echo $i; ?>" class="frm_input sit_amt" size="7">
        </td>

		<td rowspan="2" headers="th_amt" class="td_numbig td_input">
            <label for="it_price_dealer2_<?php echo $i; ?>" class="sound_only">우수사업소</label>
            <input type="text" name="it_price_dealer2[<?php echo $i; ?>]" value="<?php echo $row['it_price_dealer2']; ?>" id="it_price_dealer2_<?php echo $i; ?>" class="frm_input sit_amt" size="7">
		</td>

    </tr>
    <tr class="<?php echo $bg; ?>">
        <!--
        <td headers="th_pt" class="td_numbig td_input"><?php echo $it_point; ?></td>
        <td headers="th_qty" class="td_numbig td_input">
            <label for="stock_qty_<?php echo $i; ?>" class="sound_only">재고</label>
            <input type="text" name="it_stock_qty[<?php echo $i; ?>]" value="<?php echo $row['it_stock_qty']; ?>" id="stock_qty_<?php echo $i; ?>" class="frm_input sit_qty" size="7">
        </td>
        <td headers="th_amt" class="td_numbig td_input">
            <label for="incentive_<?php echo $i; ?>" class="sound_only">인센티브</label>
            <input type="text" name="pt_incentive[<?php echo $i; ?>]" value="<?php echo $row['pt_incentive']; ?>" id="incentive_<?php echo $i; ?>" class="frm_input sit_camt" size="3">
        </td>
        <td headers="th_amt" class="td_numbig td_input">
            <?php echo ($default['pt_reserve_cache'] > 0 && $row['pt_end']) ? date("Y.m.d", $row['pt_end']) : '-'; ?>
        </td>
        <td headers="th_amt" class="td_numbig td_input">
            <select name="pt_comment_use[<?php echo $i; ?>]" id="pt_comment_use_<?php echo $i; ?>">
                <option value="0"<?php echo get_selected('0', $row['pt_comment_use']); ?>>사용안함</option>
                <option value="1"<?php echo get_selected('1', $row['pt_comment_use']); ?>>모두등록</option>
                <option value="2"<?php echo get_selected('2', $row['pt_comment_use']); ?>>나만등록</option>
            </select>
        </td>
        -->
    </tr>
    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="17" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./itemform.php" class="btn btn_01">상품등록</a>
    <a href="./itemexcel.php" onclick="return excelform(this.href);" target="_blank" class="btn btn_02">상품일괄등록</a>
    <a href="./itemexcel2.php" onclick="return excelform(this.href);" target="_blank" class="btn btn_02">상품일괄수정</a>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <button type="button" class="btn btn_02" id="btn_orderent">주문 중인 사업소목록</button>
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
</div>
<!-- <div class="btn_confirm01 btn_confirm">
    <input type="submit" value="일괄수정" class="btn_submit" accesskey="s">
</div> -->
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<!-- 팝업 박스 시작 -->
<style>
#popup_box { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
#popup_box > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
#popup_box iframe { position: relative; width: 700px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -250px; }

@media (max-width : 750px) {
  #popup_box iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
}
</style>

<div id="popup_box">
  <div></div>
</div>

<script>
//var it_ids = <?=json_encode($affected_it_ids)?>;
//if(it_ids.length > 0)
//    open_order_ent(it_ids);

$(function() {
  $("#popup_box").hide();
  $("#popup_box").css("opacity", 1);

  $('#popup_box').click(function() {
      close_popup_box();
  });
});

function open_popup_box(url) {
  $('html, body').addClass('modal-open');
  $("#popup_box > div").html('<iframe src="' + url + '">');
  $("#popup_box iframe").load(function() {
    $("#popup_box").show();
  });
}

function close_popup_box() {
  $('html, body').removeClass('modal-open');
  $('#popup_box').hide();
  $('#popup_box').find('iframe').remove();
}

function open_order_ent(it_ids) {
    var query = '';
    for(var i = 0; i < it_ids.length; i++) {
        query += 'it_id%5B%5D=' + it_ids[i] + '&';
    }

    open_popup_box('itemorderent.php?' + query);
}

$('#btn_orderent').click(function() {
    var $chk = $('input[name="chk[]"]:checked');

    var it_ids = [];
    $chk.each(function() {
        var chk = $(this).val();

        var it_id = $('input[name="it_id[' + chk + ']"]').val();
        if(it_id && !~it_ids.indexOf(it_id))
            it_ids.push(it_id);
    });

    open_order_ent(it_ids);
});
</script>
<!-- 팝업 박스 끝 -->

<?php
$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$api_it_id' ";
$it = sql_fetch($sql);
?>

<script>

$("#itemexcel").click(function() {
    var it_ids = [];
    $('input[id="it_id"]').each(function() {
        if ($(this).prop('checked', true)) {
            it_ids.push("'"+$(this).val()+"'");
        }
    });
    it_ids = it_ids.join(",");
    $(location).attr('href',"./excel_item.php?it_ids="+it_ids);
    
});

$("#itemexcel_all").click(function() {
    $(location).attr('href',"./excel_item.php");
});

$('#itemprice').click(function() {
    window.location.href = './itemprice.php';
});

<?php if ($api_it_id) { ?>
$(".banner_or_img").addClass("sit_wimg");
$(function() {
    $(".sit_wimg_view").bind("click", function() {
        var sit_wimg_id = $(this).attr("id").split("_");
        var $img_display = $("#"+sit_wimg_id[1]);

        $img_display.toggle();

        if($img_display.is(":visible")) {
            $(this).text($(this).text().replace("확인", "닫기"));
        } else {
            $(this).text($(this).text().replace("닫기", "확인"));
        }

        var $img = $("#"+sit_wimg_id[1]).children("img");
        var width = $img.width();
        var height = $img.height();
        if(width > 700) {
            var img_width = 700;
            var img_height = Math.round((img_width * height) / width);

            $img.width(img_width).height(img_height);
        }
    });
    $(".sit_wimg_close").bind("click", function() {
        var $img_display = $(this).parents(".banner_or_img");
        var id = $img_display.attr("id");
        $img_display.toggle();
        var $button = $("#it_"+id+"_view");
        $button.text($button.text().replace("닫기", "확인"));
    });
});
<?php } ?>

function fitemformcheck(f)
{

    if (!f.it_id.value) {
        alert("상품관리코드가 없습니다.");
        return false;
    }

	if (f.prodId.value && $('#api_edit_use').is(":checked")) {
		var error = message = "";
		var url = 'https://system.eroumcare.com/api/adm/adm3000/adm3200/updateAdm3200ProdInfoAjax.do';
		/*
		var dataList = {
			'prodId' : '제품아이디',
			'prodNm' : '제품명',
			'prodSym' : '재질',
			'prodWeig' : '중량',
			'prodSize' : '사이즈',
			'prodDetail' : '상세정보',
			'prodImgAttr' : '이미지 첨부파일 이름들',
			'file1' : '첫번쨰 이미지 파일',
			'file2' : '두번째 이미지 파일'
		}
		*/
		var pf = document.fitemlist2;
		var dataList = $(pf).serialize();

		$.ajax({
			url: url,
			type: "POST",
			data: dataList,
			dataType: "json",
			async: false,
			cache: false,
			success: function(data, textStatus) {
				error = data.errorYN;
				message = data.message;
			}
		});

		if (error == "Y") {
			alert(message);
			return false;
		}
	}

}

function fitemlist_submit(f)
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

$(function() {

	$('#itemform').click(function() {

		var url = 'https://eroumcare.com/eroumcare_itemformupdate.php';
		var dataList = {
			'pt_it' : '1',							//상품종류 - 1: 일반상품(배송가능), 2: 컨텐츠상품(배송불가)
			'ca_id' : '1080',						//상품분류
			'it_id' : 'A_<?php echo time();?>',		//상품코드
			'it_thezone' : 'F<?php echo time();?>',	//상품코드
			'it_name' : '4DM-<?php echo time();?>',	//상품명
			'it_basic' : '미끄럼방지용품 - 양말',	//기본설명
			'it_explan' : '상품설명',				//상품설명

			'it_type1' : '1',						//상품테그 - 메인상품(기본값:0, 선택:1)
			'it_type2' : '0',						//상품테그 - 대여상품(기본값:0, 선택:1)
			'it_type3' : '0',						//상품테그 - 주문상품(기본값:0, 선택:1)
			'it_type4' : '0',						//상품테그 - 상담상품(기본값:0, 선택:1)
			'it_type5' : '0',						//상품테그 - 택배상품(기본값:0, 선택:1)
			'pt_main' : '0',						//상품테그 - 메인(기본값:0, 선택:1)

			'it_maker' : '',						//제조사
			'it_origin' : '',						//원산지
			'it_brand' : '',						//브랜드
			'it_model' : '미끄럼방지양말',			//모델

			'opt1_subject' : '색상',				//옵션제목
			'opt1' : '실버,검정',					//옵션명
			'opt1_price' : '50000,80000',			//추가금액
			'opt1_price_partner' : '45000,75000',	//파트너가격
			'opt1_price_dealer' : '42500,72500',	//사업소가격
			'opt1_price_dealer2' : '42500,72500',	//사업소가격
			'opt1_stock_qty' : '0,0',				//재고수량
			'opt1_noti_qty' : '0,0',				//통보수량
			'opt1_use' : '1,1',						//사용여부
			'opt1_thezone' : '0,0',					//더존코드

			'it_price' : '1500000',					//판매가격
			'it_price_partner' : '1200000',			//파트너몰 판매가격
			'it_price_dealer' : '1274900',			//사업소가격
			'it_price_dealer2' : '1274900',			//사업소가격

			'it_use' : '1',							//상품판매

			'it_use_custom_order' : '1',			//주문제작 가능

			'pt_point' : '1',						//포인트결제안함
			'pt_comment_use' : '1',					//댓글등록

			'it_point_type' : '0',					//포인트유형 1:설정금액, 1:판매가기준 설정비율, 2:구매가기준 설정비율
			'it_point' : '0',						//포인트
			'it_supply_point' : '0',				//추가옵션상품 포인트

			'it_soldout' : '0',						//상품품절
			'it_stock_qty' : '99999',				//재고수량

			'it_img1' : 'https://cdn.imweb.me/thumbnail/20200408/71330c9671fba.jpg',		//이미지1
			'it_img1_del' : 0,																			//이미지1삭제

			'it_sc_add_sendcost' : '-1',			//산간지역 추가 배송비
			'it_sc_add_sendcost_partner' : '-1',		//파트너회원 산간지역 추가 배송비

			'prodId' : '제품아이디',
			'gubun' : '구분 ("00")',
			'prodNm' : '제품 명',
			'itemId' : '품목 아이디',
			'subItem' : '하위품목',
			'prodSupPrice' : '공급가격',
			'prodOflPrice' : '판매금액',
			'ProdPayCode' : '급여코드',
			'supId' : '공급업체 아이디',
			'prodColor' : '색상',
			'prodSym' : '재질',
			'prodWeig' : '중량',
			'prodSize' : '사이즈',
			'prodQty' : '주문가능수량',
			'prodDetail' : '상세정보',
			'regDtm' : '최초등록일시',
			'regUsrId' : '최초등록자 ID',
			'regUsrIp' : '최초등록자 IP (IPV6 포함 총 39자리)',
			'supNm' : '공급업체 이름',
			'prodImgAttr' : '이미지 첨부파일 이름들'

		};

		$.ajax({
			type : "post",
			url : url,
			data: dataList,
			dataType : "json",
			success : function(data){
				if(data.error){

					alert(data.error);
					return false;

				}else{

					alert('등록완료[제품아이디:'+ data.prodId + ']');
					location.reload();

				}
			}
		});

    });

	$('#itemformupdate').click(function() {

		var url = 'https://system.eroumcare.com/api/adm/adm3000/adm3200/updateAdm3200ProdInfoAjax.do';
		var dataList = {
			'prodId' : '제품 아이디',	// prodId = it_id
			'prodNm' : '제품명',			//	it_name
			'prodSym' : '재질',				// prodSym
			'prodWeig' : '중량',			// prodWeig
			'prodSize' : '사이즈',			// prodSize
			'prodDetail' : '상세정보',	  	//  prodDetail
			'beforeDelFileSn': []		  // 빈 어레이 반환
		}

		$.ajax({
			type : "post",
			url : url,
			data: dataList,
			dataType : "json",
			success : function(data){
				if(data.errorYN == 'Y'){

					alert(data.message);
					return false;

				}else{

					alert('상품수정완료');
					location.reload();

				}
			}
		});
	});


    $(".itemcopy").click(function() {
        var href = $(this).attr("href");
        window.open(href, "copywin", "left=100, top=100, width=300, height=200, scrollbars=0");
        return false;
    });

    $('#page_rows, #orderby').change(function() {
        document.flist.submit();
    })

  // 상품태그 > 마감 선택
  $(document).on("click", "input[name^='it_type11']", function() {
    var $it_deadline = $(this).parent().find("input[id^='it_deadline']");
    if($(this).is(":checked")) {
      $it_deadline.attr("disabled", false);
      $it_deadline.css("background-color", "white");
    } else {
      $it_deadline.attr("disabled", true);
      $it_deadline.css("background-color", "#f3f3f3");
    }
  });
});

function excelform(url)
{
    var opt = "width=600,height=450,left=10,top=10";
    window.open(url, "win_excel", opt);
    return false;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
