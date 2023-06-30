<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

// 목록헤드
if(isset($wset['ahead']) && $wset['ahead']) {
	add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/head/'.$wset['ahead'].'.css" media="screen">', 0);
	$head_class = 'list-head';
} else {
	$head_class = (isset($wset['acolor']) && $wset['acolor']) ? 'tr-head border-'.$wset['acolor'] : 'tr-head border-black';
}
?>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css?v=<?php echo time(); ?>">
<div class="popup layer">
    <div class="pop_head">
        <h1>배송지 목록</h1>
        <button type="button" id="btn_close_popup"><img src="<?=THEMA_URL?>/assets/img/btn_top_menu_x.png"></button>
    </div>
    <div class="head">
        <form class="form-horizontal popadditemsearch" role="form" name="popadditemsearch" action="./orderaddress.php" onsubmit="return true;" method="get" autocomplete="off">
            <select name="sfl" id="sfl">
                <option value="all">전체</option>
                <option value="ad_subject">배송지명</option>
                <option value="ad_name">이름</option>
                <option value="ad_addr">주소</option>
            </select>

            <label for="stx" class="sound_only">검색어</label>
            <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" class="frm_input" style="width: calc(100% - 155px);">
            <input type="submit" value="검색" class="btn_submit shbtn">
        </form>
    </div>
        
    <form class="form" role="form" name="forderaddress" id="forderaddress" method="post" action="<?php echo $action_url; ?>" autocomplete="off">
    <input type="hidden" name="w" value="s">
    <div id="sod_addr">
        <div class="table-responsive">
            <table class="div-table table" style="width:100%;">
            <tbody>
            <tr class="<?php echo $head_class;?>">
                <th scope="col" style="width:50px;">
                    <label for="chk_all" class="sound_only">전체선택</label>
                    <span><input type="checkbox" name="chk_all" id="chk_all"></span>
                </th>
                <th scope="col"><span>배송정보</span></th>
                <th scope="col"><span class="last">선택</span></th>
            </tr>
            <?php for($i=0; $i < count($list); $i++) { ?>
                <tr<?php echo ($i == 0) ? ' class="tr-line"' : '';?>>
                    <td class="text-center">
                        <input type="hidden" name="ad_id[<?php echo $i; ?>]" value="<?php echo $list[$i]['ad_id'];?>">
                        <label for="chk_<?php echo $i;?>" class="sound_only">배송지선택</label>
                        <input type="checkbox" name="chk[]" value="<?php echo $i;?>" id="chk_<?php echo $i;?>">
                    </td>
                    <td class="td_ad_info">
                        <input type="hidden" value="<?php echo $list[$i]['addr']; ?>">
                        <p class="info">
                            <?php if($list[$i]['ad_default']) echo '[대표]'; ?>
                            <?php echo $list[$i]['ad_name']; ?>
                            (<?php echo $list[$i]['ad_hp'] ?: $list[$i]['ad_tel']; ?>)
                        </p>
                        <p class="address">
                            <?php echo $list[$i]['print_addr']; ?>
                        </p>
                    </td>
                    <td class="text-center" style="min-width:100px;">
                        <input type="hidden" value="<?php echo $list[$i]['addr']; ?>">
                        <button type="button" class="sel_address btn btn-color btn-xs" title="선택">선택</button>
                    </td>
                </tr>
            <?php } ?>
            <?php if (!count($list)) { ?>
                <tr>
                    <td colspan="3" class="text-center">
                        <div style="padding:50px 0;">검색 결과가 없습니다.</div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
            </table>
        </div>
    </div>
    </form>

    <div class="pop_foot">
        <div class="menu_wr">
            <button type="button" class="btn_address" id="btn_address_set">선택한 주소를 대표로 지정</button>
            <button type="button" class="btn_address" id="btn_address_del">선택삭제</button>
        </div>

        <?php if($total_count > 0) { ?>
        <div class="page_wr">
            <ul class="pagination pagination-sm" style="margin-top:0; padding-top:0;">
                <?php echo apms_paging($write_pages, $page, $total_page, $list_page); ?>
            </ul>
        </div>
        <?php } ?>
        
    </div>
</div>

<script>
$(function() {
    function close_popup() {
        if(parent.window && parent.window.close_popup_box)
            parent.window.close_popup_box();
        
        window.close();
    }

    $('#btn_close_popup').click(function() {
        close_popup();
    });

    $(".sel_address, .info, .address").on("click", function() {
        var addr = $(this).siblings("input").val().split(String.fromCharCode(30));

        var parent = window.parent ? window.parent : window.opener;

        var f = parent.forderform;
        f.od_b_name.value        = addr[0];
        f.od_b_tel.value         = addr[1];
        f.od_b_hp.value          = addr[2];
        f.od_b_zip.value         = addr[3] + addr[4];
        f.od_b_addr1.value       = addr[5];
        f.od_b_addr2.value       = addr[6];
        // f.od_b_addr3.value       = addr[7];
        f.od_b_addr_jibeon.value = addr[8];
        // f.ad_subject.value       = addr[9];

        var zip1 = addr[3].replace(/[^0-9]/g, "");
        var zip2 = addr[4].replace(/[^0-9]/g, "");

        if(zip1 != "" && zip2 != "") {
            var code = String(zip1) + String(zip2);

            if(parent.zipcode != code) {
                parent.zipcode = code;
                parent.calculate_sendcost(code);
            }
        }

        close_popup();
    });

    // 전체선택 부분
    $("#chk_all").on("click", function() {
        if($(this).is(":checked")) {
            $("input[name^='chk[']").attr("checked", true);
        } else {
            $("input[name^='chk[']").attr("checked", false);
        }
    });

    $('#btn_address_set').click(function() {
        if($("input[name^='chk[']:checked").length==0 ){
            alert("대표주소를 선택해주세요.");
            return false;
        }

        if($("input[name^='chk[']:checked").length > 1 ){
            alert("대표주소는 1개 주소만 선택해주세요.");
            return false;
        }

        $("input[name='w']").val('s');
        $('#forderaddress').submit();
    });

    $('#btn_address_del').click(function() {
        if($("input[name^='chk[']:checked").length==0 ){
            alert("삭제할 주소를 선택해주세요.");
            return false;
        }

        if(!confirm('정말 선택한 주소를 삭제하시겠습니까?'))
            return false;

        $("input[name='w']").val('d');
        $('#forderaddress').submit();
    });

});
</script>
