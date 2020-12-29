<?php
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');

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
        <div class="pop_tit">오픈마켓 주문상품 연결</div>
    </div>
    <div class="content">
        	<div class="item_desc">
        		[G마켓] ABC상품 (옵션1)
        		
        		<!-- 옵션이 없는 경우 
        		[G마켓] ABC상품  -->
        	</div>
            
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
                <option value="it_name" <?php echo get_selected($sfl, 'it_name'); ?>>상품명</option>
                <option value="it_id" <?php echo get_selected($sfl, 'it_id'); ?>>상품코드</option>
                <option value="it_maker" <?php echo get_selected($sfl, 'it_maker'); ?>>제조사</option>
                <option value="it_origin" <?php echo get_selected($sfl, 'it_origin'); ?>>원산지</option>
                <option value="it_sell_email" <?php echo get_selected($sfl, 'it_sell_email'); ?>>판매자 e-mail</option>
                <!-- APMS - 2014.07.20 -->
                    <option value="pt_id" <?php echo get_selected($sfl, 'pt_id'); ?>>파트너 아이디</option>
                <!-- // -->
            </select>

            <label for="stx" class="sound_only">검색어</label>
            <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" class="frm_input">
            <input type="submit" value="검색" class="btn_submit shbtn">

        
        
            <table class="popup_table">
                <thead>
                    <tr>
                        <th>상품명</th>
                        <th>모델명</th>
                        <th>옵션선택</th>
                        <th>판매가</th>
                        <th>선택</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <tr>
                        <td class="td-left image">
                            <a href="./itemform.php?w=u&it_id=<?php echo $row['it_id']; ?>" target="_blank">
                               <img src="#" alt="" /> 상품명
                               
                                <!-- <?php echo get_it_image($row['it_id'], 50, 50); ?>
                                <?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> -->
                            </a>
                        </td>
                        <td>모델명이 보입니다.<!-- <?php echo $row['it_model']; ?> --></td>
                        <td>
                            <select name="" id="">
                            	<option>옵션 선택안함</option>
                            	<option>옵션1</option>
                            	<option>옵션2</option>
                            </select>
                        </td>
                        <td>
                            <span class="popup_price">기</span><?php echo number_format($row['it_price']); ?>원<br/>
                            <span class="popup_price gray">파</span><?php echo number_format($row['it_price_partner'] ?$row['it_price_partner']:$row['it_price']); ?>원<br/>
                            <span class="popup_price darkgray">딜</span><?php echo number_format($row['it_price_dealer'] ?$row['it_price_dealer']:$row['it_price']); ?>원
                        </td>
                        <td>
                            <a href="#" class="shbtn lineblue">연결</a>
                        </td>
                    </tr>
                    
                    <!-- <?php if ( $i == 0 ) { ?>
                    <tr>
                        <td colspan="6" class="no_item">
                            검색된 상품이 없습니다.
                        </td>
                    </tr>
                    <?php } ?> -->
                </tbody>
            </table>
            <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;od_id='.$od_id.'&amp;page='); ?>
        </form>
    </div>
</div>

</body>
</html>