<?php
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');

$where = " and ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

if ($sfl == "")  $sfl = "it_name";

$sql_common = " from g5_shop_matching where 1=1 ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$ptt = "ma_id desc";

$sql_order = "order by $pth $ptt";

$sql  = " select *
           $sql_common
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;
$qstr  = $qstr.'&amp;&amp;page='.$page.'&amp;save_stx='.$stx;
?>
<html>
<head>
<title>오픈마켓 상품 매칭</title>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css?v=<?php echo time(); ?>">
</head>
<style>
</style>
<div id="pop_add_item" class="admin_popup">
    <div class="header">
        <div class="pop_tit">오픈마켓 주문상품 연결정보</div>
    </div>
    <div class="content">
            
        <form class="form-horizontal popadditemsearch" role="form" name="popadditemsearch" action="./pop.order.item.matching.php" onsubmit="return true;" method="get" autocomplete="off">
            <label for="sfl" class="sound_only">검색대상</label>
            <select name="sfl" id="sfl">
                <option value="it_id">상품코드</option>
                <option value="it_name">상품명</option>
            </select>

			<label for="stx" class="sound_only">검색어</label>
            <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" class="frm_input">
            <input type="submit" value="검색" class="btn_submit shbtn">
         </form>
        
        
            <table class="popup_table">
                <thead>
                    <tr>
                        <th>매칭상품</th>
                        <th>연결 상품</th>
                        <th>해지</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                    ?>                    
                    <tr>
                        <td><?=$row['oit_id']." ".$row['oit_name']?>(<?=$row['oio_id']?>)</td>
                        <td><?=$row['it_id']." ".$row['it_name']?>(<?=$row['io_id']?>)</td>
                        <td><a href="./pop.openmarket.item.del.php?oit_id=<?=$row['oit_id']?>" class="shbtn lineblue">해지</a></td>
                        
                    </tr>
                    <?php
                    }
                    ?>
                    <?php if ( $i == 0 ) { ?>
                    <tr>
                        <td colspan="3" class="no_item">
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