<?php
$sub_menu = "300100";
include_once('./_common.php');
$query = "SHOW tables LIKE 'g5_board_log'";//게시판 hit 로그 테이블 생성
		$wzres = sql_num_rows( sql_query($query) );
		if($wzres < 1) {
			sql_query("CREATE TABLE `g5_board_log` (
			  `log_id` int(11) NOT NULL AUTO_INCREMENT,
			  `bo_table` varchar(255) DEFAULT NULL COMMENT '테이블명',
			  `wr_id` int(11) DEFAULT NULL COMMENT '게시글id',
			  `mb_id` varchar(30) DEFAULT NULL COMMENT '작성자id',
			  `create_time` datetime DEFAULT NULL COMMENT '조회일시',
			  PRIMARY KEY (`log_id`),
			  KEY `index` (`bo_table`,`wr_id`,`mb_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
			", true);
		}
auth_check($auth[$sub_menu], 'r');

$sql_common = " from {$g5['board_table']} a ";
$sql_search = " where (1) ";

if ($is_admin != "super") {
    $sql_common .= " , {$g5['group_table']} b ";
    $sql_search .= " and a.gr_id = b.gr_id and find_in_set('{$member['mb_id']}', b.gr_admin) ";
}

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "bo_table" :
            $sql_search .= " ($sfl like '$stx%') ";
            break;
        case "a.gr_id" :
            $sql_search .= " ($sfl = '$stx') ";
            break;
        default :
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "a.gr_id, a.bo_table";
    $sod = "asc";
}
$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
$page_rows = (int)$page_rows ? (int)$page_rows : $config['cf_page_rows'];
$rows = $page_rows;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '게시판 관리';
include_once('./admin.head.php');

$colspan = 8;
?>
<style type="text/css">
	.input__on-off {
  display: none;
}

.label__on-off {
    overflow: hidden;
    position: relative;
    display: inline-block;
    width: 43px;
    height: 19px;
    -webkit-border-radius: 13px;
    -moz-border-radius: 13px;
    border-radius: 13px;
    background-color: #aaaaaa;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    -webkit-transition: all .3s;
    -moz-transition: all .3s;
    -ms-transition: all .3s;
    -o-transition: all .3s;
    transition: all .3s;
}

.label__on-off > * {
    vertical-align: middle;
    -webkit-transition: all .3s;
    -moz-transition: all .3s;
    -ms-transition: all .3s;
    -o-transition: all .3s;
    transition: all .3s;
    font-size: 14px;
}

.label__on-off .marble {
    position: absolute;
    top: 1px;
    left: 1px;
    display: block;
    width: 17px;
    height: 17px;
    background-color: #fff;
    -webkit-border-radius: 50%;
    -moz-border-radius: 50%;
    border-radius: 50%;
    -webkit-box-shadow: 0 0 10px rgba(0, 0, 0, .3);
    -moz-box-shadow: 0 0 10px rgba(0, 0, 0, .3);
    box-shadow: 0 0 10px rgba(0, 0, 0, .3);
}

.label__on-off .on {
    display: none;
    padding-right: 16px;
	line-height: 17px;
}

.label__on-off .off {
    padding-left: 14px;
    line-height: 17px;
}

.input__on-off:checked + .label__on-off {
    background-color: #3f51b5;
}

.input__on-off:checked + .label__on-off .on {
    display: inline-block;
}

.input__on-off:checked + .label__on-off .off {
    display: none;
}

.input__on-off:checked + .label__on-off .marble {
    left: 25px;
}
</style>

<!--div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">생성된 게시판수</span><span class="ov_num"> <?php echo number_format($total_count) ?>개</span></span>
</div-->
<form name="fsearch" id="fsearch" class="local_sch01 local_sch" method="get">
<input type="hidden" name="page_rows" id="page_rows" value="<?=$page_rows?>">
키워드 검색&nbsp;&nbsp;&nbsp;
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="bo_table"<?php echo get_selected($_GET['sfl'], "bo_table", true); ?>>TABLE</option>
    <option value="bo_subject"<?php echo get_selected($_GET['sfl'], "bo_subject"); ?>>제목</option>
    <option value="a.gr_id"<?php echo get_selected($_GET['sfl'], "a.gr_id"); ?>>그룹ID</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" value="검색" class="btn_submit">

</form>
<div class="local_ov01 local_ov">
    <?php $listall ?>
    검색개수 : <?php echo number_format($total_count) ?> 건
	<div style="float:right;right:0px;margin-top:-8px;">
	<select name="page_rows" id="page_rows2" onChange="javascript:$('#page_rows').val(this.value);$('#fsearch').submit();" style="width:130px;height:33px;">
		<option value="" <?=($list_num=="")?"selected":""?>> 쇼핑몰 설정으로 보기 </option>
		<option value="50"  <?=($page_rows =='50')?"selected":"";?>>50개씩보기</option>
        <option value="100" <?=($page_rows=='100')?"selected":"";?>>100개씩보기</option>
        <option value="500" <?=($page_rows=='500')?"selected":"";?>>500개씩보기</option>
    </select>
</div>
</div>

<form name="fboardlist" id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="<?php echo $token ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">게시판 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <!--th scope="col"><?php echo subject_sort_link('a.gr_id') ?>그룹</a></th-->
        <th scope="col"><?php echo subject_sort_link('bo_table') ?>TABLE</a></th>
        <th scope="col"><?php echo subject_sort_link('bo_subject') ?>제목</a></th>
		<th scope="col"><?php echo subject_sort_link('bo_skin', '', 'desc') ?>PC스킨</a></th>
        <th scope="col"><?php echo subject_sort_link('bo_mobile_skin', '', 'desc') ?>모바일스킨</span></a></th>
        <!--th scope="col">읽기P<span class="sound_only">포인트</span></th>
        <th scope="col">쓰기P<span class="sound_only">포인트</span></th>
        <th scope="col">댓글P<span class="sound_only">포인트</span></th>
        <th scope="col">다운P<span class="sound_only">포인트</span></th>
        <th scope="col"><?php echo subject_sort_link('bo_use_sns') ?>SNS<br>사용</a></th-->
		<th scope="col"><?php echo subject_sort_link('bo_count_write') ?>게시글수</a></th>
        <th scope="col"><?php echo subject_sort_link('bo_use_search') ?>검색사용</a></th>
        <!--th scope="col"><?php echo subject_sort_link('bo_order') ?>출력<br>순서</a></th>
        <th scope="col">접속기기</th-->
        <th scope="col" width="300">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $one_update = '<a href="./board_form.php?w=u&amp;bo_table='.$row['bo_table'].'&amp;'.$qstr.'" class="btn btn_03">수정</a>';
        $one_copy = '<a href="./board_copy.php?bo_table='.$row['bo_table'].'" class="board_copy btn btn_02" target="win_board_copy">복사</a>';
		$one_search = '<a href="./board_search.php?bo_table='.$row['bo_table'].'" class="btn btn_02" style="background:#555555;" target="win_board_copy">게시판 조회</a>';
		$one_go = '<a href="/bbs/board.php?bo_table='.$row['bo_table'].'" class="btn btn_02" style="background:#555555;" target="win_board_copy">게시판 바로가기</a>';

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td class="td_chk">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['bo_subject']) ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td style="display:none;">
            <?php if ($is_admin == 'super'){ ?>
                <?php echo get_group_select("gr_id[$i]", $row['gr_id']) ?>
            <?php }else{ ?>
                <input type="hidden" name="gr_id[<?php echo $i ?>]" value="<?php echo $row['gr_id'] ?>"><?php echo $row['gr_subject'] ?>
            <?php } ?>
        </td>
        <td>
            <input type="hidden" name="board_table[<?php echo $i ?>]" value="<?php echo $row['bo_table'] ?>">
            <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $row['bo_table'] ?>" class="get_board_cnt" data-bo-table="<?php echo $row['bo_table']; ?>"><?php echo $row['bo_table'] ?></a>
        </td>
        <td>
            <label for="bo_subject_<?php echo $i; ?>" class="sound_only">게시판 제목<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="bo_subject[<?php echo $i ?>]" value="<?php echo get_text($row['bo_subject']) ?>" id="bo_subject_<?php echo $i ?>" required class="required frm_input bo_subject full_input" size="15">
        </td>
		<td>
            <label for="bo_skin_<?php echo $i; ?>" class="sound_only">PC 스킨</label>
            <?php echo get_skin_select('board', 'bo_skin_'.$i, "bo_skin[$i]", $row['bo_skin']); ?>
        </td>
        <td>
            <label for="bo_mobile_skin_<?php echo $i; ?>" class="sound_only">모바일 스킨</label>
            <?php //echo get_mobile_skin_select('board', 'bo_mobile_skin_'.$i, "bo_mobile_skin[$i]", $row['bo_mobile_skin']); ?>
            <?php echo get_skin_select('board', 'bo_mobile_skin_'.$i, "bo_mobile_skin[$i]", $row['bo_mobile_skin']); // PC로 변경 ?>
        </td>
        <td class="td_mngsmall" style="display:none;">
            <label for="bo_read_point_<?php echo $i; ?>" class="sound_only">읽기 포인트</label>
            <input type="text" name="bo_read_point[<?php echo $i ?>]" value="<?php echo $row['bo_read_point'] ?>" id="bo_read_point_<?php echo $i; ?>" class="frm_input" size="2">
        </td>
        <td class="td_mngsmall" style="display:none;">
            <label for="bo_write_point_<?php echo $i; ?>" class="sound_only">쓰기 포인트</label>
            <input type="text" name="bo_write_point[<?php echo $i ?>]" value="<?php echo $row['bo_write_point'] ?>" id="bo_write_point_<?php echo $i; ?>" class="frm_input" size="2">
        </td>
        <td class="td_mngsmall" style="display:none;">
            <label for="bo_comment_point_<?php echo $i; ?>" class="sound_only">댓글 포인트</label>
            <input type="text" name="bo_comment_point[<?php echo $i ?>]" value="<?php echo $row['bo_comment_point'] ?>" id="bo_comment_point_<?php echo $i; ?>" class="frm_input" size="2">
        </td>
        <td class="td_mngsmall" style="display:none;">
            <label for="bo_download_point_<?php echo $i; ?>" class="sound_only">다운 포인트</label>
            <input type="text" name="bo_download_point[<?php echo $i ?>]" value="<?php echo $row['bo_download_point'] ?>" id="bo_download_point_<?php echo $i; ?>" class="frm_input" size="2">
        </td>
        <td class="td_numsmall" style="display:none;">
            <label for="bo_use_sns_<?php echo $i; ?>" class="sound_only">SNS 사용</label>
            <input type="checkbox" name="bo_use_sns[<?php echo $i ?>]" value="1" id="bo_use_sns_<?php echo $i ?>" <?php echo $row['bo_use_sns']?"checked":"" ?>>
        </td>
        <td align="center">
            <?=number_format($row["bo_count_write"]);?>
        </td>
		<td class="td_numsmall">
            <!--label for="bo_use_search_<?php echo $i; ?>" class="sound_only">검색 사용</label>
            <input type="checkbox" name="bo_use_search[<?php echo $i ?>]" value="1" id="bo_use_search_<?php echo $i ?>" <?php echo $row['bo_use_search']?"checked":"" ?>-->
			<input type="checkbox" id="bo_use_search_<?php echo $i ?>" value="1" name="bo_use_search[<?php echo $i ?>]" class="input__on-off" <?php echo $row['bo_use_search']?"checked":"" ?>>
		<label for="bo_use_search_<?php echo $i; ?>" class="label__on-off">
		  <span class="marble"></span>
		  <span class="on">on</span>
		  <span class="off">off</span>
		</label>
        </td>
        <td class="td_numsmall" style="display:none;">
            <label for="bo_order_<?php echo $i; ?>" class="sound_only">출력 순서</label>
            <input type="text" name="bo_order[<?php echo $i ?>]" value="<?php echo $row['bo_order'] ?>" id="bo_order_<?php echo $i ?>" class="frm_input" size="2">
        </td>
        <td class="td_mngsmall" style="display:none;">
            <label for="bo_device_<?php echo $i; ?>" class="sound_only">접속기기</label>
            <select name="bo_device[<?php echo $i ?>]" id="bo_device_<?php echo $i ?>">
                <option value="both"<?php echo get_selected($row['bo_device'], 'both', true); ?>>모두</option>
                <option value="pc"<?php echo get_selected($row['bo_device'], 'pc'); ?>>PC</option>
                <option value="mobile"<?php echo get_selected($row['bo_device'], 'mobile'); ?>>모바일</option>
            </select>
        </td>
        <td align="center">
            <?php echo $one_update ?>
            <?php echo $one_copy ?>
			<?php echo $one_search ?>
			<?php echo $one_go ?>
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

<div class="btn_fixed_top">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php if ($is_admin == 'super') { ?>
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
    <?php if ($is_admin == 'super') { ?>
    <a href="./board_form.php" id="bo_add" class="btn_01 btn">게시판 추가</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;page='); ?>

<script>
function fboardlist_submit(f)
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

$(function(){
    $(".board_copy").click(function(){
        window.open(this.href, "win_board_copy", "left=100,top=100,width=550,height=450");
        return false;
    });
});
</script>

<?php
include_once('./admin.tail.php');
?>
