<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "w");

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

// 분류
$ca_list  = '<option value="">선택</option>'.PHP_EOL;
$sql = " select * from {$g5['g5_shop_category_table']} ";
/*
if ($is_admin != 'super')
    $sql .= " where ca_mb_id = '{$member['mb_id']}' ";
*/
$sql .= " order by ca_order, ca_id ";
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
    if ($sfl != "" && $sfl != 'all') {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($sfl == 'all') {
        $sql_search .= " $where ( it_model like '%$stx%' OR it_name like '%$stx%' OR it_id like '%$stx%' OR pt_id like '%$stx%' ) ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

if ($sca != "") {
    $sql_search .= " $where (a.ca_id like '$sca%' or a.ca_id2 like '$sca%' or a.ca_id3 like '$sca%') ";
}

// $sql_search .= " AND it_id NOT IN ( select it_id from {$g5['g5_shop_cart_table']} WHERE od_id = '{$od_id}') AND it_use = '1'";

if ($sfl == "")  $sfl = "all";

$sql_common = " from {$g5['g5_shop_item_table']} a ,
                     {$g5['g5_shop_category_table']} b
               where (a.ca_id = b.ca_id";
/*
if ($is_admin != 'super')
    $sql_common .= " and b.ca_mb_id = '{$member['mb_id']}'";
*/
$sql_common .= ") ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
	$sst = "it_id";
    $sod = "desc";
}

if($sst == 'it_id') {
	$pth = "a.pt_num desc,";
	$ptt = "";
} else {
	$pth = "";
	$ptt = ", a.pt_num desc";
}

$sql_order = "order by $pth $sst $sod $ptt";

$sql  = " select *
           $sql_common
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;
$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page.'&amp;save_stx='.$stx;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// APMS - 2014.07.25
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');
$flist = array();
$flist = apms_form(1,0);


?>
<html>
<head>
<title>상품 추가 > 상품선택</title>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css?v=<?php echo time(); ?>">
</head>
<style>
</style>
<div id="pop_add_item" class="admin_popup">
    <div class="header">
        <ul class="add_item_header">
            <li class="on">상품선택</li>
            <li class="arrow">
                <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/icon_arrow_next.png" />
            </li>
            <li class="">옵션선택</li>
        </ul>
    </div>
    <div class="content">
        <form class="form-horizontal popadditemsearch" role="form" name="popadditemsearch" action="./pop.order.item.add.php" onsubmit="return true;" method="get" autocomplete="off">
            
            <input type="hidden" name="od_id" value="<?php echo $od_id ?>" id="od_id" required class="required frm_input">
            <label for="sca" class="sound_only">분류선택</label>
            <select name="sca" id="sca">
                <option value="">전체분류</option>
                <?php
                $sql1 = " select ca_id, ca_name, as_line from {$g5['g5_shop_category_table']} order by ca_order, ca_id ";
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

            <label for="sfl" class="sound_only">검색대상</label>
            <select name="sfl" id="sfl">
                <option value="all" <?php echo get_selected($sfl, 'all'); ?>>전체</option>
                <option value="it_model" <?php echo get_selected($sfl, 'it_model'); ?>>모델명</option>
                <option value="it_name" <?php echo get_selected($sfl, 'it_name'); ?>>상품명</option>
                <option value="it_id" <?php echo get_selected($sfl, 'it_id'); ?>>상품코드</option>
                <!-- <option value="it_maker" <?php echo get_selected($sfl, 'it_maker'); ?>>제조사</option>
                <option value="it_origin" <?php echo get_selected($sfl, 'it_origin'); ?>>원산지</option>
                <option value="it_sell_email" <?php echo get_selected($sfl, 'it_sell_email'); ?>>판매자 e-mail</option> -->
                <!-- APMS - 2014.07.20 -->
                    <option value="pt_id" <?php echo get_selected($sfl, 'pt_id'); ?>>파트너 아이디</option>
                <!-- // -->
            </select>

            <label for="stx" class="sound_only">검색어</label>
            <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" class="frm_input">
            <input type="submit" value="검색" class="btn_submit shbtn">

        </form>
        <form class="form-horizontal popadditem" role="form" name="popadditem" action="./pop.order.item.add.option.php" onsubmit="return true;" method="post" autocomplete="off">
            <table class="popup_table">
                <thead>
                    <tr>
                        <th>상품명</th>
                        <th>모델명</th>
                        <th>판매가</th>
                        <!--<th>파트너가</th>-->
                        <th>상태</th>
                        <th>선택</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                    ?>
                    <tr>
                        <td class="td-left image">
                            <a href="./itemform.php?w=u&it_id=<?php echo $row['it_id']; ?>" target="_blank">
                                <?php echo get_it_image($row['it_id'], 50, 50); ?>
                                <?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?>
                            </a>
                        </td>
                        <td><?php echo $row['it_model']; ?></td>
                        <td style="text-align:left;padding-left:30px;">
                            <span class="popup_price">기</span><?php echo number_format($row['it_price']); ?>원<br/>
                            <span class="popup_price gray">파</span><?php echo number_format($row['it_price_partner'] ?$row['it_price_partner']:$row['it_price']); ?>원<br/>
                            <span class="popup_price darkgray">딜</span><?php echo number_format($row['it_price_dealer'] ?$row['it_price_dealer']:$row['it_price']); ?>원<br/>
                            <span class="popup_price darkgray">우</span><?php echo number_format($row['it_price_dealer2'] ?$row['it_price_dealer2']:$row['it_price']); ?>원
                        </td>
                        <!--<td><?php echo number_format($row['it_price_partner'] ? $row['it_price_partner'] : $row['it_price']); ?>원</td>-->
                        <td>
                            <input type="checkbox" name="it_use[<?php echo $i; ?>]" <?php echo ($row['it_use'] ? 'checked' : ''); ?> value="1" id="use_<?php echo $i; ?>" onclick="return false;">일반몰
                            <br/>
                            <input type="checkbox" name="it_use_partner[<?php echo $i; ?>]" <?php echo ($row['it_use_partner'] ? 'checked' : ''); ?> value="1" id="use_partner_<?php echo $i; ?>" onclick="return false;">파트너몰
                        </td>
                        <td>
                            <a href="./pop.order.item.add.option.php?od_id=<?php echo $od_id; ?>&it_id=<?php echo $row['it_id']; ?>" class="shbtn lineblue">선택</a>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                    <?php if ( $i == 0 ) { ?>
                    <tr>
                        <td colspan="6" class="no_item">
                            검색된 상품이 없습니다.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;od_id='.$od_id.'&amp;page='); ?>
        </form>
    </div>
</div>

</body>
</html>