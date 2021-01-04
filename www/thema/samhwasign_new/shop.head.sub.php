<script type="text/javascript">
// wetoz : 2020-09-04
if(!wcs_add) var wcs_add = {};
wcs_add["wa"] = "s_1a7f4699f871";
wcs.inflow("samhwasign.com");
</script>


<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
include_once(THEMA_PATH.'/assets/thema.php');

// 카테고리
$category = array();
$head_category = array();
$sql = "SELECT * FROM g5_shop_category where length(ca_id) = '2' and ca_use = '1' and ca_main_use = '1' ORDER BY ca_order, ca_id ASC";
$res = sql_query($sql);
while( $row = sql_fetch_array($res) ) {
    $sql = "SELECT * FROM g5_shop_category where  length(ca_id) = '4' and ca_id like '{$row['ca_id']}%' and ca_use = '1' and ca_main_use = '1'  ORDER BY ca_order, ca_id ASC";
    $res2 = sql_query($sql);
    while( $row2 = sql_fetch_array($res2) ) {
        $sql = "SELECT * FROM g5_shop_category where  length(ca_id) = '6' and ca_id like '{$row2['ca_id']}%' and ca_use = '1' and ca_main_use = '1' ORDER BY ca_order, ca_id ASC";
        $res3 = sql_query($sql);
        while( $row3 = sql_fetch_array($res3) ) {
            $row2['sub'][] = $row3;
        }
        $row['sub'][] = $row2;
    }
	$category[] = $row;
	if ( $row['ca_head_use'] ) {
		$head_category[] = $row;
	}
}

$banks = explode(PHP_EOL, $default['de_bank_account']); 

$sql = " select * from g5_write_notice where wr_datetime >= '". date('Y-m-d 00:00:00') ."' and wr_id = wr_parent"; 
$new_notice = sql_fetch($sql);

?>
<link type="text/css" rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/reset.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/font.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/slick.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/app.css"/>
<link rel="stylesheet" href="/css/default_shop.css?ver=180820">
<link rel="stylesheet" href="/css/apms.css?ver=180820">

<link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/bs3/css/bootstrap.min.css" type="text/css" class="thema-mode">
<link rel="stylesheet" href="<?php echo THEMA_URL; ?>/colorset/Basic/colorset.css" type="text/css" class="thema-colorset">
<link rel="stylesheet" href="/skin/apms/item/shop/style.css" >
<link rel="stylesheet" href="<?php echo THEMA_URL; ?>/widget/basic-sidebar/widget.css?ver=180820">
<link rel="stylesheet" href="/css/level/basic.css?ver=180820">
<link rel="stylesheet" href="<?php echo THEMA_URL; ?>/assets/css/samhwa.css?ver=1597565613">
<!--header -->
<div class="btn_top_scroll">
	<a onclick="scrollToTop()"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_scroll.png" alt=""></a>
</div>


<div class="right_menu_area <?php echo $_COOKIE['right_menu_area'] == 'on' ? 'on' : ''; ?>"
    style="<?php echo $_COOKIE['right_menu_area'] == 'on' ? '' : 'right: -200px;' ?>">
	<button type="button"  class="right_menu_toggle">
	<?php echo $_COOKIE['right_menu_area'] == 'on' ? '▶' : '◀'; ?>
	</button>
	<div class="quick_menu">
		<ul>
			<li><a href="<?php echo G5_SHOP_URL; ?>/cart.php"><img src="<?php echo THEMA_URL; ?>/assets/img/icon_s_cart.png" alt=""> 장바구니</a></li>
			<li><a href="<?php echo THEMA_URL; ?>/assets/files/삼화에스앤디고양지점_입금통장사본.pdf"  target="_blank"><img src="<?php echo THEMA_URL; ?>/assets/img/icon_s_bank.png" alt=""> 입금통장</a></li>
			<li><a href="<?php echo THEMA_URL; ?>/assets/files/삼화에스앤디고양지점_사업자등록증사본.pdf" target="_blank"><img src="<?php echo THEMA_URL; ?>/assets/img/icon_s_biz.png" alt=""> 사업자등록증</a></li>
		</ul>
	</div>
	<div class="talk_area">
		<a  href="#" onclick="javascript:window.open('http://talk.naver.com/w4v8my?ref=http%3A%2F%2Fsignstand.doto.li%2F', 'talktalk', 'scrollbars=1, resizable=1, width=486, height=745');return false;"> <img src="<?php echo THEMA_URL; ?>/assets/img/icon_talk_naver.png" alt=""> 네이버 톡톡 상담</a>
		<a href="http://pf.kakao.com/_sxgBEK/chat" target="_blank"> <img src="<?php echo THEMA_URL; ?>/assets/img/icon_talk_kakao.png" alt=""> 카카오톡 상담</a>
	</div>
	<div class="info">
		<p>고객센터</p>
		<p><span>
			<?php if ( $member['mb_type'] == 'partner' ) { ?>
				02-2273-8011
			<?php }else{ ?>
				<?php echo $default['de_admin_company_tel']; ?>
			<?php } ?>
			</span><br>
			<?php echo $default['de_admin_info_email']; ?><br><br>
			웹하드 : <br>
			<span class="txt_s">
			<?php echo $config['cf_1'] ?>
			</span>
		</p>
	</div>
	
	<div class="info">
		<p>최근본상품</p>
		<?php include(THEMA_PATH.'/side/boxtodayview.skin.php'); // 오늘 본 상품 ?>
	</div>
</div>

<div class="mo_top">
	<div class="logo_area">
		<a href="<?php echo G5_SHOP_URL ?>/"><img src="<?php echo THEMA_URL; ?>/assets/img/top_logo_s.png"></a>
	</div>
	<div class="left_area">
        <button class="header-hamburger-btn"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_menu.png" class="top_btn_img"></button>
            <div id="samhwa-m-menu">
            <div class="wrap">
                <div class="closer">
                    <img src="<?php echo THEMA_URL; ?>/assets/img/btn_close.png" />
                </div>
                <div class="scrollable-wrap">
                    <ul class="mobile-cate">   
                        <?php foreach($category as $cate) { ?>
                            <li class="<?php echo (substr($ca_id, 0, strlen($cate['ca_id'])) === $cate['ca_id']) ? 'on default_on ': ''; ?>" data-id="<?php echo $cate['ca_id']; ?>">
                                <a class='title'><?php echo $cate['ca_name']; ?></a> <?php /*href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' data-id="<?php echo $cate['ca_id']; ?>"*/?>
                                <?php if ( $cate['sub'] ) { ?>
                                    <ul class='sub'>
                                        <?php foreach($cate['sub'] as $sub) { ?>
                                            <li class="<?php echo $sub['ca_id'] == $ca_id ? 'on' : ''; ?> ">
                                                <a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='sub-title'><?php echo $sub['ca_name']; ?></a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                    <?php if($is_member) { // 로그인 상태 ?>
                        <a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php">주문/배송</a>
                        <a href="<?php echo G5_BBS_URL; ?>/mypage.php">마이페이지</a>
                        <a href="<?php echo G5_SHOP_URL; ?>/cart.php">장바구니</a>
                        <a href="<?php echo $at_href['logout'];?>">로그아웃</a>
                    <?php }else{ ?>
                        <a href="/shop/orderinquiry.php" class="green">로그인</a>
                        <!-- <a href="<?php echo $at_href['login'];?>" class="green">로그인</a> -->
                    <?php } ?>
                </div>
            </div>
        </div>
        
	</div>
	<div class="right_area">
		<a href="/shop/search.php"> <img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_search.png" class="top_btn_img" /></a>
	</div>
	<div class="sub_menu">
		<?php echo get_samhwa_content('top_common_menu'); ?>
	</div>
</div>


<div id="wrap">

	<div class="top_common_wrap">
		<div class="top_common_area">
			<div class="top_left_area">
				<div class="link_area">
					<ul>
						<li><a href="https://search.naver.com/search.naver?sm=top_hty&fbm=1&ie=utf8&query=%EC%82%BC%ED%99%94" target="_blank"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_link_naver.png" alt=""></a></li>
						<li class="favorite"><a href="#">즐겨찾기</a></li>
						<!-- <li><a href="#">바로가기</a></li> -->
						<!-- <li>
							<select>
								<option>바로가기</option>
								<option><a href="#">바로가기</a></option>
							</select>
						</li> -->
					</ul>
					
				</div>
			</div>
			<div class="top_right_area">
				<div class="link_area">
                <?php if($is_member) { // 로그인 상태 ?>
                    <a href="<?php echo G5_BBS_URL; ?>/logout.php" >로그아웃</a>
	                    <?php if($member['admin']) {?>
								<a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
							<?php } ?>
							<?php if($is_samhwa_admin && !$member['admin']) {?>
								<a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
							<?php } ?>
                    <?php }else{ ?>
                        <a href="/shop/orderinquiry.php">로그인</a>
                        <a href="<?php echo $at_href['reg'];?>">회원가입</a>
                    <?php } ?>
                    <?php if($is_member) { // 로그인 상태 ?>
                        <a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php">주문/배송</a>
                        <a href="<?php echo G5_BBS_URL; ?>/mypage.php">마이페이지</a>
                        <a href="<?php echo G5_SHOP_URL; ?>/cart.php">장바구니</a>
                    <?php } ?>
                        <a href="/bbs/board.php?bo_table=qa">고객센터</a>
				</div>
			</div>
			<div class="top_center_area">
				<div class="top_logo">
					<a href="<?php echo G5_SHOP_URL ?>/"><img src="<?php echo THEMA_URL; ?>/assets/img/top_logo.png" alt=""></a>
				</div>
				<div class="search">
                    <form name="tsearch" method="get" onsubmit="return tsearch_submit(this);" role="form" class="form">
						<input type="hidden" name="url"	value="<?php echo (IS_YC) ? $at_href['isearch'] : $at_href['search'];?>">
						<input type="text" name="stx" value="<?php echo get_text($stx); ?>" id="search" placeholder="Search"/>
                        <button type="submit" id="sch_submit" value=""><img src="<?php echo THEMA_URL; ?>/assets/img//btn_search.png" ></button>
					</form>
				</div>
				<div class="cs_tel">
					<a href="tel:02-2268-2868">
						<img src="<?php echo THEMA_URL; ?>/assets/img/icon_tel.png" >
						<div class="cs_info">
							<p>광고자재 납품 / 도매 B2B 문의</p>
							<p>고객센터(
								<?php if ( $member['mb_type'] == 'partner' ) { ?>
									02-2273-8011
								<?php }else{ ?>
									<?php echo $default['de_admin_company_tel']; ?>
								<?php } ?>
								)로 전화주세요.</p>
						</div>
					</a>
				</div>
			</div>
		</div>
		
		
	</div>

	
	<div class="scroll_top">
		<div class="scroll_top_menu">
			<div class="scroll_top_menu_wrap">
				<div class="scroll_top_menu">
                    <a href="<?php echo G5_SHOP_URL ?>/">
                        <img src="<?php echo THEMA_URL; ?>/assets/img/top_logo_s.png">
                    </a>
					<div class="menu_area">
                    <?php if($is_member) { // 로그인 상태 ?>
                    <a href="<?php echo G5_BBS_URL; ?>/logout.php" >로그아웃</a>
	                    <?php if($member['admin']) {?>
								<a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
							<?php } ?>
							<?php if($is_samhwa_admin && !$member['admin']) {?>
								<a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
							<?php } ?>
                    <?php }else{ ?>
                        <a href="/shop/orderinquiry.php">로그인</a>
                        <a href="<?php echo $at_href['reg'];?>">회원가입</a>
                    <?php } ?>
                    <?php if($is_member) { // 로그인 상태 ?>
                        <a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php">주문/배송</a>
                        <a href="<?php echo G5_BBS_URL; ?>/mypage.php">마이페이지</a>
                        <a href="<?php echo G5_SHOP_URL; ?>/cart.php">장바구니</a>
                    <?php } ?>
                        <a href="/bbs/board.php?bo_table=qa">고객센터</a>
					</div>
				</div>
			</div>
		</div>
    </div>
    <div class="top_menu_wrap ">
        <div class="menu_wrap">
        <div class="menu"><button id="menu_open" class="top_menu_all"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_menu.png" class="icon_menu">전체카테고리 <i><img src="<?php echo THEMA_URL; ?>/assets/img/icon_arrow_down.png" class="icon"></i> </button> 
        </div>
        <?php include(THEMA_PATH.'/side/category.php'); // 전체메뉴 ?>
        		<div class="main_menu">
	                <?php echo get_samhwa_content('top_common_menu'); ?>
	            </div>
	            <div class="notice">
					<a href="/bbs/board.php?bo_table=notice">
						<img src="<?php echo THEMA_URL; ?>/assets/img/icon_notice.png">
						<p>
							공지사항
						</p>
					</a>
					<div class="new"><?php echo $new_notice['wr_id'] ? 'N' : ''; ?></div>
				</div>
        </div>
    </div>
    <div class="container_wrap sub-page">
        <div class="sub-left-menu">
            <div class="menu-content">
                <ul>
                <?php 
                $cr = 1;
                foreach($head_category as $cate) { ?>
                <?php 
                    $selectedCat = NULL;
                    if( isset($ca_id) && $ca_id ) $selectedCat = substr($ca_id, 0, 2);
                    elseif( isset($it) ) $selectedCat = substr($it['ca_id'], 0, 2); 
                ?>
                <li class="smenu <?php echo $selectedCat == $cate['ca_id'] ? 'open': ''; ?>">
                    <a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' class='title'><?php echo str_pad($cr++, 2, "0", STR_PAD_LEFT).".".$cate['ca_name']; ?></a>
                        <?php if(isset($cate['sub']) && !empty($cate['sub'])): ?>
                        <ul>
                            <?php foreach($cate['sub'] as $i=>$sub) { ?>
                                <?php if ( $i == 0 ) echo '<tr>'; ?>
                                    <li <?php echo $i == count($cate['sub'])-1 && count($cate['sub']) % 3 ? 'colspan="'.(4-count($cate['sub'])%3).'"' : ''; ?>>
                                        <a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='cate_02 <?php echo $sub['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub['ca_name']; ?></a>
                                    </li>
                                <?php if ( $i != 0 && $i % 3 == 2 ) echo '</tr><tr>'; ?>
                                <?php if ( $i == count($cate['sub'])-1 ) echo '</tr>'; ?>
                            <?php } ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                <?php } ?>
                </ul>
                <!--메뉴 연동 -->
            </div>
        </div>
        <div class="sub-content">
            <?php
                $selectedCat1 = $selectedCat2 = $selectedCat3 = null;
                $catId = NULL;
                if( (isset($ca_id) && $ca_id) ) $catId = $ca_id;
                elseif( isset($it) && isset($it['ca_id']) && $it['ca_id']) $catId = $it["ca_id"];

                if( $catId )
                {
                    if( strlen($catId) == 6 )
                    {
                        $selectedCat3 = $catId;
                        $selectedCat2 = substr($catId, 0, 4);
                        $selectedCat1 = substr($catId, 0, 2);
                    }
                    elseif( strlen($catId) == 4 )
                    {
                        $selectedCat2 = $catId;
                        $selectedCat1 = substr($catId, 0, 2);
                    }
                    else
                    {
                        $selectedCat1 = $catId;
                    }
                }
            ?>
            <div class="multi-selectbtn">
                HOME <span> > </span> 
                <select id="c_code_1" name="cate_select_1" class='cate_select' data-cate-level='1' data-cate-id='c_code'>
                    <option value="">전체</option>
                    <?php foreach($head_category as $cat): ?>
                    <option value="<?php echo $cat['ca_id']; ?>" <?php echo $selectedCat1 == $cat["ca_id"]?"selected":""; ?>>
                        <?php echo $cat['ca_name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <span> > </span> 
                <?php
                    // Level 2 Categories
                    $sql = "SELECT * FROM `g5_shop_category` 
                        WHERE  length(ca_id) = '4' and ca_use = '1' and ca_main_use = '1' ORDER BY ca_order, ca_id ASC";
                    $level2Categories = sql_query($sql);
                ?>
                <select id="c_code_2" name="cate_select_2" class='cate_select' data-cate-level='2' data-cate-id='c_code'>
                    <option value="">전체</option>
                    <?php while( $row = sql_fetch_array($level2Categories) ): ?>
                    <option value="<?php echo $row['ca_id']; ?>" data-parent="<?php echo substr($row['ca_id'], 0, 2); ?>"
                        <?php echo $selectedCat2 == $row["ca_id"]?"selected":""; ?>>
                        <?php echo $row['ca_name']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                <span> > </span> 
                <?php
                    // Level 3 Categories
                    $sql = "SELECT * FROM `g5_shop_category` 
                        WHERE  length(ca_id) = '6' and ca_use = '1' and ca_main_use = '1' ORDER BY ca_order, ca_id ASC";
                    $level3Categories = sql_query($sql);
                ?>
                <select id="c_code_3" name="cate_select_3" class='cate_select' data-cate-level='3' data-cate-id='c_code'>
                    <option value="">전체</option>
                    <?php while( $row = sql_fetch_array($level3Categories) ): ?>
                    <option value="<?php echo $row['ca_id']; ?>" data-parent="<?php echo substr($row['ca_id'], 0, 4); ?>"
                        <?php echo $selectedCat3 == $row["ca_id"]?"selected":""; ?>>
                        <?php echo $row['ca_name']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                <button id="category-go-btn" type="button">GO</button>
            </div>
								
<script>

var cates = {};
$(document).ready(function(){
     // 카테고리 목록 저장하기
     $('.cate_select').each(function() {
        var level = $(this).data('cate-level');
        var cate_id = $(this).data('cate-id');
        if ( !cates[cate_id] ){
            cates[cate_id] = {};
        }
        if ( !cates[cate_id][level] ) {
            cates[cate_id][level] = new Array();
        }
        $(this).find('option').each(function() {
            cates[cate_id][level].push({
                'value': $(this).val(),
                'selected': $(this).is(':selected'),
                'text': $(this).html(),
            })
        });
    }).promise().done(function() {
        // 2,3차 카테고리 상위 카테고리인것만 골라서 보여주기
        $('.cate_select').each(function() {
            var level = $(this).data('cate-level');
            var cate_id = $(this).data('cate-id');
            var selected_val = $(this).find('option:selected').val();
            var next = $('#' + cate_id + '_' + (level + 1));

            if ( $(next).length ) {
                $(next).find('option').each(function () {
                    if ( $(this).val() == '' || $(this).val().substring(0, level*2) == selected_val ) {
                    }else{
                        $(this).remove();
                    }
                });
            }
        });
    });

    // 카테고리 선택시 하위 카테고리 내용 바꿔주기
    $('.cate_select').on('change', function() {
        var level = $(this).data('cate-level');
        var cate_id = $(this).data('cate-id');
        var new_arr = new Array();
        var selected_val = $(this).find('option:selected').val();
        var str;

        for(var i=level+1;i<=3;i++) {

            new_arr = [];
            if ( level + 1 == i ) {

                for(var a=0;a<cates[cate_id][i].length;a++) {

                    if ( !cates[cate_id][i][a].value || cates[cate_id][i][a].value.substring(0, level*2) == selected_val) {
                        new_arr.push(cates[cate_id][i][a]);
                    }

                }

                str = '';
                for(var a=0;a<new_arr.length;a++) {
                    str += '<option value="' + new_arr[a]['value'] + '">' + new_arr[a]['text'] + '</option>';
                }
                
                $('#' + cate_id + '_' + i).html(str);
            }else{
                str = '<option value="' + cates[cate_id][i][0].value + '">' + cates[cate_id][i][0].text + '</option>';
                $('#' + cate_id + '_' + i).html(str);
            }
        }
    });

    $("#category-go-btn").click(function() {
        var catId = $("#c_code_3").val() || $("#c_code_2").val() || $("#c_code_1").val() || undefined;
        if( catId ) {
            window.location.href = g5_url + "/shop/list.php?ca_id=" + catId
        }
    })

});
</script>