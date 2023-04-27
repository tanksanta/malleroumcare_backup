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
    /* //  * Program Name : EROUMCARE Platform! = Renewal Ver:1.0 */
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
    /* // 파일명 : \www\adm\shop_admin\product_recommended.php */
    /* // 파일 설명 :   */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    $sub_menu = '200950';
    include_once('./_common.php');
    auth_check($auth[$sub_menu], "r");


    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    if( ($_POST['mode']) && ($_POST['mode']=="Insert") ) {

        sql_query(" INSERT g5_shop_recommended
                    SET product1 = '{$_POST['prod1']}',
                        product2 = '{$_POST['prod2']}',
                        product3 = '{$_POST['prod3']}',
                        recommended_url = '{$_POST['url']}',
                        mb_id = '{$member['mb_id']}'
        "); 
        $_sq = sql_insert_id();
        
        if($_POST['prod1']) sql_query(" INSERT g5_shop_event_item SET ev_id = '{$_sq}', it_id = '{$_POST['prod1']}';"); 
        if($_POST['prod2']) sql_query(" INSERT g5_shop_event_item SET ev_id = '{$_sq}', it_id = '{$_POST['prod2']}';"); 
        if($_POST['prod3']) sql_query(" INSERT g5_shop_event_item SET ev_id = '{$_sq}', it_id = '{$_POST['prod3']}';"); 
        
        exit();
    }



    $row = sql_fetch(" SELECT count(sq) as cnt FROM g5_shop_recommended");
    $total_count = $row['cnt'];

    $rows = ($list_num)?$list_num:$config['cf_page_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    // 내용(본문 리스트)
    $result_log = sql_query("  SELECT * FROM g5_shop_recommended ORDER BY sq DESC LIMIT {$from_record}, {$rows}; ");
    $result_data=sql_fetch_array($result_log);
    if( $result_data ) {
        $result_data = sql_fetch("  SELECT * , 
                                        ( SELECT it_name FROM g5_shop_item WHERE it_id = '{$result_data['product1']}' ) AS product1_nm,
                                        ( SELECT it_name FROM g5_shop_item WHERE it_id = '{$result_data['product2']}' ) AS product2_nm,
                                        ( SELECT it_name FROM g5_shop_item WHERE it_id = '{$result_data['product3']}' ) AS product3_nm
                                    FROM g5_shop_recommended ORDER BY sq DESC LIMIT 1
        ");
    }
    mysqli_data_seek($result_log, 0);

    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==


    $g5['title'] = "추천상품관리";
    include_once (G5_ADMIN_PATH.'/admin.head.php');

?>

<style>
    .new_form {
        margin: 10px 20px;
    }
    .sub_section_tit {
        padding: 0px 5px;
    }
    
    hr.list { border: none; border-top: 2px dotted #333; color: #fff; background-color: #fff; height: 1px; width: 100%; }

    /* 팝업 */
    #item_popup_box, #reject_popup_box { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index:999; background: rgba(0, 0, 0, 0.8); }
    #item_popup_box iframe { width:550px; height:750px; max-height: 90%; position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%); background: white; }

    /* 로딩 팝업 */
    #loading_excel { display: none; width: 100%; height: 100%; position: fixed; left: 0; top: 0; z-index: 9999; background: rgba(0, 0, 0, 0.3); }
    #loading_excel .loading_modal { position: absolute; width: 400px; padding: 30px 20px; background: #fff; text-align: center; top: 50%; left: 50%; transform: translate(-50%, -50%); }
    #loading_excel .loading_modal p { padding: 0; font-size: 16px; }
    #loading_excel .loading_modal img { display: block; margin: 20px auto; }
    #loading_excel .loading_modal button { padding: 10px 30px; font-size: 16px; border: 1px solid #ddd; border-radius: 5px; }
</style>

<section class="wrap">
    <div class="sub_section_tit" style="font-size: 20px;">추천상품 선택</div>
</section>
<div class="new_form">

    <table style="background-color: #f8f8fa;" class="new_form_table">
        <tr>
            <td style="width:160px; height: 45px; text-align:center;"><strong>추천상품1</strong></td>
            <td style="padding: 10px 25px;">
                상품명 <input type="text" id="id_product1_nm" name="product1_nm" value="<?=$result_data['product1_nm'];?>" class="frm_input" size="30" autocomplete="off" style="width:280px;">
                &nbsp; &nbsp; 
                상품코드 <input type="text" id="id_product1" name="product1" value="<?=$result_data['product1'];?>" class="frm_input" size="30" maxlength="25" autocomplete="off" style="width:150px;">
                &nbsp; <input type="submit" value="상품 검색" class="btn_submit" id="" style="width:100px; height:30px;" OnClick="pop_itemList_Select('product1');">
            </td>
        </tr>
        <tr>
            <td style="width:160px; height: 45px; text-align:center;"><strong>추천상품2</strong></td>
            <td style="padding: 10px 25px;">
                상품명 <input type="text" id="id_product2_nm" name="product2_nm" value="<?=$result_data['product2_nm'];?>" class="frm_input" size="30" autocomplete="off" style="width:280px;">
                &nbsp; &nbsp; 
                상품코드 <input type="text" id="id_product2" name="product2" value="<?=$result_data['product2'];?>" class="frm_input" size="30" maxlength="25" autocomplete="off" style="width:150px;">
                &nbsp; <input type="submit" value="상품 검색" class="btn_submit" id="" style="width:100px; height:30px;" OnClick="pop_itemList_Select('product2');">
            </td>
        </tr>
        <tr>
            <td style="width:160px; height: 45px; text-align:center;"><strong>추천상품3</strong></td>
            <td style="padding: 10px 25px;">
                상품명 <input type="text" id="id_product3_nm" name="product3_nm" value="<?=$result_data['product3_nm'];?>" class="frm_input" size="30" autocomplete="off" style="width:280px;">
                &nbsp; &nbsp; 
                상품코드 <input type="text" id="id_product3"  name="product3" value="<?=$result_data['product3'];?>" class="frm_input" size="30" maxlength="25" autocomplete="off" style="width:150px;">
                &nbsp; <input type="submit" value="상품 검색" class="btn_submit" id="" style="width:100px; height:30px;" OnClick="pop_itemList_Select('product3');">
                &nbsp; &nbsp; 
                <span>※ 추천상품3은 해상도(모바일)에 따라 보이지 않을수 있습니다.</span>
            </td>
        </tr>            
    </table>

</div>

<div style="height:10px;"></div>
<hr class="list">
<div style="height:30px;"></div>

<section class="wrap">
    <div class="sub_section_tit" style="font-size: 20px;">추천 게시물 연결</div>
</section>
<div class="new_form">

    <table style="background-color: #f8f8fa;" class="new_form_table">
        <tr>
            <td style="width:160px; height: 45px; text-align:center;"><strong>링크 URL</strong></td>
            <td style="padding: 10px 25px;">
                <input type="text" id="id_recommended_url"  name="recommended_url" value="<?=$result_data['recommended_url'];?>" class="frm_input" autocomplete="off" style="width:550px;">
                &nbsp; <input type="submit" value="링크이동" class="btn_submit" id="" style="width:100px; height:30px;" onclick="linkURL_go($('#id_recommended_url').val());">
            </td>
        </tr>          
    </table>

</div>

<div style="height:10px;"></div>
<hr class="list">
<div style="height:30px;"></div>

<section class="wrap">
    <div class="sub_section_tit" style="font-size: 20px;">추천 상품 기록</div>
</section>
<div class="tbl_wrap tbl_head01">
    <table style="background-color: #f8f8fa;" class="new_form_table">
        <thead>
        <tr>
            <th style="text-align:center;">번호</th>
            <th style="text-align:center;">추천상품1</th>
            <th style="text-align:center;">클릭수</th>
            <th style="text-align:center;">추천상품2</th>
            <th style="text-align:center;">클릭수</th>
            <th style="text-align:center;">추천상품3</th>
            <th style="text-align:center;">클릭수</th>
            <th style="text-align:center;">추천 게시물</th>
            <th style="text-align:center;">등록 ID</th>
            <th style="text-align:center;">등록일자</th>
        </tr>
        </thead>
        <tbody>
        <?php for($i=0; $row=sql_fetch_array($result_log); $i++) { ?>
        <tr class="bg0">
            <td style="text-align:center;"><?=$row['sq']?></td>
            <td style="text-align:center;"><?=$row['product1']?></td>
            <td style="text-align:center;"><?=number_format($row['product1_hit'])?></td>
            <td style="text-align:center;"><?=$row['product2']?></td>
            <td style="text-align:center;"><?=number_format($row['product2_hit'])?></td>
            <td style="text-align:center;"><?=$row['product3']?></td>
            <td style="text-align:center;"><?=number_format($row['product3_hit'])?></td>
            <td style="text-align:center;"><?=$row['recommended_url']?></td>
            <td style="text-align:center;"><?=$row['mb_id']?></td>
            <td style="text-align:center;"><?=$row['reg_dt']?></td>
        </tr>
        <?php } ?>
        </tbody>     
    </table>
</div>

<?php
    $pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;domain='.$domain.'&amp;page=');
    echo $pagelist;
?>

<div class="btn_fixed_top">
    <a href="#" class="btn_02 btn" Onclick="save_recommended();">저장</a>
</div>

<div id="item_popup_box" style="display: none;">
    <div class="popup_box_close"><i class="fa fa-times"></i></div>
    <iframe name="iframe" src="" scrolling="yes" frameborder="0" allowTransparency="false"></iframe>
</div>

<!-- 로딩 -->
<div id="loading" style="display: none;">
<div class="loading_modal">
    <p>처리중 입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
</div>
</div>


<script>

    // 상품 리스트팝업 실행
    function pop_itemList_Select( target ) {

        $('#item_popup_box iframe').attr('src', '<?=G5_ADMIN_URL?>/shop_admin/popup.itemList_Select.php?target='+target);
        $("#item_popup_box iframe").load(function () { 
            $('#item_popup_box').show();
        });
        
    }

    // 상품 리스트팝업 실행
    function linkURL_go( URL ) {
        if( !URL ) {
            alert("링크URL 필드에 입력된 값이 없습니다."); return;
        }
        window.open( URL );
    }


    // 상품 리스트팝업 실행
    function save_recommended() {
        if( !confirm("위 추천상품관리 정보를 새롭게 저장 하시겠습니까?") ) { return; }
        $('#loading').show();

            // ajax 처리 시작
            $.ajax({
                url: '<?=$_SERVER['PHP_SELF']?>', type: 'POST', dataType: 'json', 
                data: { 
                    mode:"Insert",
                    prod1:$("#id_product1").val(),
                    prod2:$("#id_product2").val(),
                    prod3:$("#id_product3").val(),
                    url:$("#id_recommended_url").val()

                },
                success: function(data) {
                    location.reload();
                },
                error: function(e) {}
            });

    }



    /*
    $('#').click(function() { 
        $('#item_popup_box iframe').attr('src', url);
        $("#item_popup_box iframe").load(function () { 
            $('#item_popup_box').show();
        });
    });
    */
</script>



<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
