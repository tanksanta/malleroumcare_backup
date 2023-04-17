<?php
$sub_menu = '500500';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$where = ' where ';
$sql_search = '';


if ( $bn_status ){
    $sql_search .= $where . " `bn_status` = '".$bn_status."' ";
    $where = ' AND ';
    $qstr .= "&amp;bn_status=".$bn_status;
} else {
    //$sql_search .= $where . " `bn_status` = 'Y' ";
    //$where = ' AND ';
}

if ( $bn_position ){
    $sql_search .= $where . " `bn_position` = '".$bn_position."' ";
    $where = ' AND ';
    $qstr .= "&amp;bn_position=".$bn_position;
} else {
    $sql_search .= $where . " `bn_position` = '사업소' ";
    $where = ' AND ';
    $bn_position = "사업소";
}


if( $fr_date && $to_date ){
    $sql_search .= $where . " (`bn_time` between '" . $fr_date . "' AND '" . $to_date . "')";
}





if ( $bn_time ){
    $sql_search .= ($bn_time === 'ing') ? " $where '".G5_TIME_YMDHIS."' between bn_begin_time and bn_end_time " : " $where bn_end_time < '".G5_TIME_YMDHIS."' ";
    $where = ' and ';
    $qstr .= "&amp;bn_time=$bn_time";
}

$g5['title'] = '배너관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$sql_common = " from {$g5['g5_shop_banner_table']} ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];


$rows = ($list_num)?$list_num:$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


?>

<style>
    /* 로딩 팝업 */
    #loading { display: none; width: 100%; height: 100%; position: fixed; left: 0; top: 0; z-index: 9999; background: rgba(0, 0, 0, 0.3); }
    #loading .loading_modal { position: absolute; width: 400px; padding: 30px 20px; background: #fff; text-align: center; top: 50%; left: 50%; transform: translate(-50%, -50%); }
    #loading .loading_modal p { padding: 0; font-size: 16px; }
    #loading .loading_modal img { display: block; margin: 20px auto; }
    #loading .loading_modal button { padding: 10px 30px; font-size: 16px; border: 1px solid #ddd; border-radius: 5px; }
</style>

<form name="bannerlist" id="bannerlist" method="get" onsubmit="return bannerlist_submit_function(this);">
<div class="new_form">

    <table style="background-color: #f8f8fa;" class="new_form_table">
        <tr>
            <td style="width:160px; height: 45px; text-align:center;"><strong>검색조건</strong></td>
            <td style="padding: 10px 25px;">

                <div style="float:left; margin:0px; padding-right:20px; padding-bottom:0px;">
                    <strong>사용여부</strong><br />
                    <select name="bn_status" id="" style="width:120px;">
                        <option value="" <?=($bn_status=="")?"selected":""?>> 전체 </option>
                        <option value="Y" <?=($bn_status=="Y")?"selected":""?>> 사용 </option>
                        <option value="N" <?=($bn_status=="N")?"selected":""?>> 미사용 </option>
                    </select>
                </div>

                <div style="float:left; margin:0px; padding-right:20px; padding-bottom:0px;">
                    <strong>표시분류</strong><br />

                    <select name="bn_position" id="" style="width:120px;">
                        <option value="사업소" <?=($bn_position=="사업소")?"selected":""?>> 사업소 </option>
                        <option value="파트너" <?=($bn_position=="파트너")?"selected":""?>> 파트너 </option>
                    </select>
                </div>

            </td>
        </tr>
        <tr>
            <td style="width:160px; height: 45px; text-align:center;"><strong>검색기간</strong></td>
            <td style="padding: 10px 25px;" class="sch_last">
                <strong>등록일자 </strong>
                <button type="button" class="select_date newbutton" onclick="javascript:set_date('전체');">전체</button>
                <button type="button" class="select_date newbutton" onclick="javascript:set_date('오늘');">오늘</button>
                <button type="button" class="select_date newbutton" onclick="javascript:set_date('어제');">어제</button>
                <button type="button" class="select_date newbutton" onclick="javascript:set_date('이번주');">일주일</button>
                <button type="button" class="select_date newbutton" onclick="javascript:set_date('이번달');">이번달</button>
                <button type="button" class="select_date newbutton" onclick="javascript:set_date('지난달');">지난달</button>
                <input type="text" id="fr_date"  name="fr_date" value="<?=$fr_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off"> ~
                <input type="text" id="to_date"  name="to_date" value="<?=$to_date; ?>" class="frm_input" size="10" maxlength="10" autocomplete="off">
            </td>
        </tr>
        <tr>
            <td style="width:160px; height: 45px; text-align:center;"><strong>검색어</strong></td>
            <td style="padding: 10px 25px;">
                타이틀 <input type="text" id="mb_name"  name="mb_name" value="<?=$mb_name; ?>" class="frm_input" size="30" maxlength="25" autocomplete="off" style="width:250px;">
                &nbsp; <input type="submit" value="검색" class="btn_submit" id="onlinebilling_submit" style="width:50px; height:25px;">
            </td>
        </tr>            
    </table>

</div>

<div style="padding: 0px 20px; height:30px; margin-bottom: 0px;">
    <select name="list_num" id="bn_position" style="width:120px; float:right;" onchange="submit();">
        <option value="" <?=($list_num=="")?"selected":""?>> 시스템 기본 보기 </option>
        <option value="50" <?=($list_num=="50")?"selected":""?>> 50씩 보기 </option>
        <option value="100" <?=($list_num=="100")?"selected":""?>> 100씩 보기 </option>
        <option value="200" <?=($list_num=="200")?"selected":""?>> 200씩 보기 </option>
        <option value="500" <?=($list_num=="500")?"selected":""?>> 500씩 보기 </option>
        <option value="1000" <?=($list_num=="1000")?"selected":""?>> 1000씩 보기 </option>
    </select>

    <div style="width:250px; height:30px; font-size:12px; float:left;" >
        검색 개수 : <span id="list_cnt"></span>
    </div>
</div>

</form>

<div class="btn_fixed_top">
    <a href="./bannerform.php" class="btn_01 btn">배너추가</a>
    <a href="#" class="btn_02 btn" onclick="bannerlist_orderAllSet('<?=$bn_position?>');">순서일괄정리</a>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th style="text-align:center;">ID</th>
        <th style="text-align:center;">배너 타이틀</th>
        <th style="text-align:center;">시작일시</th>
        <th style="text-align:center;">종료일시</th>
        <th style="text-align:center;">연결 URL</th>
        <th style="text-align:center;">이미지</th>
        <th style="text-align:center;">조회</th>
        <th style="text-align:center;">사용여부</th>
        <th style="text-align:center;">등록일자</th>
        <th style="text-align:center;">순서변경</th>
        <th style="text-align:center;">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = " select * from {$g5['g5_shop_banner_table']} $sql_search
          order by  bn_status ASC , bn_order ASC
          limit $from_record, $rows  ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bn_img = "";
		// 테두리 있는지
        $bn_border  = $row['bn_border'];
        // 새창 띄우기인지
        $bn_new_win = ($row['bn_new_win']) ? 'target="_blank"' : '';

        $bimg = G5_DATA_PATH.'/banner/'.$row['bn_id'];
        if(file_exists($bimg)) {
            $size = @getimagesize($bimg);
            if($size[0] && $size[0] > 800)
                $width = 800;
            else
                $width = $size[0];            
           
            $bn_img = '<img src="'.G5_DATA_URL.'/banner/'.$row['bn_id'].'" width="150px" alt="'.get_text($row['bn_alt']).'">';
        }

        $bn_begin_time = substr($row['bn_begin_time'], 2, 14);
        $bn_end_time   = substr($row['bn_end_time'], 2, 14);

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td style="text-align:center;"><?php echo $row['bn_id']; ?></td>
        <td style="text-align:center;"><?php echo $row['bn_alt']; ?></td>

        <td style="text-align:center;"><?php echo $bn_begin_time; ?></td>
        <td style="text-align:center;"><?php echo $bn_end_time; ?></td>
        <td style="text-align:center;"><?php echo $row['bn_url']; ?></td>
        <td style="text-align:center;"><?php echo $bn_img; ?></td>
        
        <td style="text-align:center;"><?php echo $row['bn_hit']; ?></td>        
        <td style="text-align:center;"><?php echo ($row['bn_status']=="Y")?"사용":"미사용"; ?></td>
        <td style="text-align:center;"><?php echo $row['bn_time']; ?></td>
        
        <td style="text-align:center;">
            <?php if($row['bn_status']=="Y") { ?>
            <div><?php echo $row['bn_order']; ?></div>
            <?php 
                if( $i>0 ) { 
                    mysqli_data_seek($result, $i-1);
                    $_tmpRow = sql_fetch_array($result);
                    mysqli_data_seek($result, $i);
                    $row = sql_fetch_array($result);                    
            ?>
            <a href="#" onclick="return bannerlist_orderSet('up','<?php echo $row['bn_id']; ?>','<?php echo $_tmpRow['bn_id']; ?>');" class="btn btn_02">▲</a>
            <?php } ?>
            &nbsp;
            <?php 
                if( ($i+1)<$result->num_rows ) {
                    $_tmpRow = sql_fetch_array($result); 
                    mysqli_data_seek($result, $i);
                    $row = sql_fetch_array($result);
                    if( $_tmpRow['bn_status'] == "Y" ) {
            ?>
            <a href="#" onclick="return bannerlist_orderSet('down','<?php echo $row['bn_id']; ?>','<?php echo $_tmpRow['bn_id']; ?>');" class="btn btn_02">▼</a>
            <?php } } ?>
            <?php } ?>
        </td>
       
        <td style="text-align:center;">
            <a href="./bannerform.php?w=u&amp;bn_id=<?php echo $row['bn_id']; ?>" class="btn btn_03">수정</a>
            <a href="./bannerformupdate.php?w=d&amp;bn_id=<?php echo $row['bn_id']; ?>" onclick="return delete_confirm(this);" class="btn btn_02">삭제</a>
        </td>
    </tr>
    <?php
    }
    if ($i == 0) {
    echo '<tr><td colspan="11" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>

</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>


<!-- 로딩 -->
<div id="loading" style="display: none;">
<div class="loading_modal">
    <p>처리중 입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
</div>
</div>


<script>
    $(function(){
        $("#list_cnt").html('<?=number_format($total_count);?>건');
    });
    
    $(document).ready(function() {
        $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
    });

    function bannerlist_orderSet(mod, order1, order2) {
        var _str = "";

        if( mod==="up" ) { _str = "[ID값:"+order2+"] 아래(▼), [ID값:"+order1+"] 위(▲)로 변경 됩니다.\n"; }
        else if( mod==="down" ) { _str = "[ID값:"+order2+"] 위(▲), [ID값:"+order1+"] 아래(▼)로 변경 됩니다.\n";  }
        else { alert("배너 순서 변경값에 오류가 있습니다."); return; }

        if( !confirm(_str+"배너 순서를 변경 합니다.") ) { return; }
        $('#loading').show();

        $.ajax({
            url: '/adm/shop_admin/ajax.bannerlist_orderSet.php',
            type: 'POST',
            data: {
                "mode_set":mod, 
                "order1":order1, 
                "order2":order2
            },
            dataType: 'json',
            success: function(data) {
                location.reload();
            },
            error: function(e) {}
        });

    }

    function bannerlist_orderAllSet(position) {

        if( !confirm("[ " + position + " ] - 사용중인 배너의 순서를 일괄 정리 합니다.") ) { return; }
        $('#loading').show();

        $.ajax({
            url: '/adm/shop_admin/ajax.bannerlist_orderSet.php',
            type: 'POST',
            data: {
                "mode_set":"AllSet",
                "position":position
            },
            dataType: 'json',
            success: function(data) {
                location.reload();
            },
            error: function(e) {}
        });
    }

</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
