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

if ($od['mb_id']) {
    alert('이미 선택된 사업소가 있습니다.');
}

$sql_common = " from {$g5['member_table']} ";

$sql_search = " where (1) ";
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
                                mb_recommend like '%{$stx}%' OR 
                                mb_1 like '%{$stx}%' 
                            ";
            break;
        default :
            $sql_search .= " ({$sfl} like '{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
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

// $rows = $config['cf_page_rows'];
$rows = 10;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);
?>
<html>
<head>
<title>주문자 회원 수정</title>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css">
</head>
<style>
</style>
<div id="pop_order_change_member" class="admin_popup">
    <div class="header">
        <h1>주문서 회원 수정</h1>
    </div>
    <div class="content">
        <form class="form-horizontal poporderchangemembersearch" role="form" name="poporderchangemembersearch" action="./pop.order.change_member.php" onsubmit="return true;" method="get" autocomplete="off">
            
            <input type="hidden" name="od_id" value="<?php echo $od_id ?>" id="od_id" required class="required frm_input">
            <label for="sfl" class="sound_only">검색대상</label>
            <select name="sfl" id="sfl">
                <option value="all"<?php echo get_selected($_GET['sfl'], "all"); ?>>전체</option>
                <option value="mb_id"<?php echo get_selected($_GET['sfl'], "mb_id"); ?>>회원아이디</option>
                <option value="mb_nick"<?php echo get_selected($_GET['sfl'], "mb_nick"); ?>>닉네임</option>
                <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>이름</option>
                <option value="mb_level"<?php echo get_selected($_GET['sfl'], "mb_level"); ?>>권한</option>
                <option value="mb_email"<?php echo get_selected($_GET['sfl'], "mb_email"); ?>>E-MAIL</option>
                <option value="mb_tel"<?php echo get_selected($_GET['sfl'], "mb_tel"); ?>>전화번호</option>
                <option value="mb_hp"<?php echo get_selected($_GET['sfl'], "mb_hp"); ?>>휴대폰번호</option>
                <option value="mb_point"<?php echo get_selected($_GET['sfl'], "mb_point"); ?>>포인트</option>
                <option value="mb_datetime"<?php echo get_selected($_GET['sfl'], "mb_datetime"); ?>>가입일시</option>
                <option value="mb_ip"<?php echo get_selected($_GET['sfl'], "mb_ip"); ?>>IP</option>
                <option value="mb_recommend"<?php echo get_selected($_GET['sfl'], "mb_recommend"); ?>>추천인</option>
                <option value="mb_1"<?php echo get_selected($_GET['sfl'], "mb_1"); ?>>여분필드1</option>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
            <input type="submit" class="btn_submit shbtn" value="검색">

        </form>
        <form class="form-horizontal poporderchangemember" role="form" name="poporderchangemember" action="./pop.order.change_member_result.php" onsubmit="return true;" method="post" autocomplete="off">
            <table>
                <thead>
                    <tr>
                        <th>번호</th>
                        <th>아이디</th>
                        <th>회원정보</th>
                        <th>선택</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                    ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><?php echo $row['mb_id']; ?></td>
                        <td><?php echo $row['mb_name']; ?></td>
                        <td>
                            <!--<input type="button" class="btn_submit shbtn" value="선택">-->
                            <a href="./pop.order.change_member_result.php?od_id=<?php echo $od_id; ?>&mb_id=<?php echo $row['mb_id']; ?>" class="shbtn">선택</a>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;od_id='.$od_id.'&amp;page='); ?>
        </form>
    </div>
</div>

</body>
</html>