<?php 

// viewType=basic
// viewType=adm

    $http_host = $_SERVER['HTTP_HOST'];
    $request_uri = $_SERVER['REQUEST_URI'];
    $for_viewType = 'https://'.$http_host.$request_uri;
    $mood_type_string="";
    if(strpos($for_viewType, '?') !== false) {  
        if($_COOKIE["viewType"] == "adm"){
            $for_viewType=$for_viewType.'&viewType=basic';
        }else{
            $for_viewType=$for_viewType.'&viewType=adm';
            $mood_type_string="급여안내";
        }
    }else{
        if($_COOKIE["viewType"] == "adm"){
            $for_viewType=$for_viewType.'?viewType=basic';
        }else{
            $for_viewType=$for_viewType.'?viewType=adm';
            $mood_type_string="급여안내";
        }
    }
?>

<script type="text/javascript">
    function gotosearch(){
        window.location.href = '<?=G5_SHOP_URL?>/search.php?qname=1';
    }

	// wetoz : 2020-09-04
	if(!wcs_add) var wcs_add = {};
	wcs_add["wa"] = "s_4372b22f12c2";
	wcs.inflow("samhwasnd.com");
	
	/* 210115 */
	document.addEventListener("message", function(e){
		switch(e.data){
			case "nowPage" :
				history.go(-1);
				break;
		}
	});
	
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

$is_index = '';
if(defined('_INDEX_') && !defined('_MAIN_')) { // index에서만 실행
	$is_index = 'is-index';
}
?>
<script src="<?php echo THEMA_URL; ?>/assets/js/ofi.js" type="text/javascript" charset="utf-8"></script>
<script>
$(document).ready(function() {
	objectFitImages();
});
scrollToTop();
</script>

<?php if ( $_COOKIE['right_menu_area'] == 'on' ) { ?>
<!-- 오른쪽 메뉴 닫기 열기 -->
<style>
.right_menu_area {
	right:-180px;
}
</style>
<?php } ?>
<style>
	.mo_top > .modeBtn { position: absolute; font-weight: bold; font-size: 16px; top: 32px; right: 70px; display: none; }
</style>
<!-- 모드바 스타일링 -->
<style>
    .top_mode_area{ position:fixed; top :0;z-index:9999999; display:block; width:100%; height:50px; text-align:center; background-color: rgba(0,0,0,0.7);  color : #fff; font-size: 20px; line-height:50px; opacity:70%;}
    @media screen and (max-width: 960px){
        .top_mode_area{font-size:10px;display:none;}
    }
    .mode_div{ position: absolute; width: 140px; right: 85px; top: 30px; font-size:16px;}
</style>
<div id="mask" style="position:absolute; left:0;top:0; background-color:#000; z-index:300"></div> 


<style>
.btn_top_scroll {
	display:flex;
}
.btn_top_scroll .scroll_btn {
	text-align: center;
    background-color: #6b6b6b;
    opacity: 0.7;
    margin-left: 5px;
    width: 50px;
    padding: 5px 0;
    line-height: 15px;
    color: white;
    border-radius: 2px;
}
.btn_top_scroll .scroll_btn span {
	display:block;
}
</style>

<div class="btn_top_scroll">
	<a onclick="scrollToBack()" class="scroll_btn">
		<span>◀</span>
		Back
	</a> 
	<a onclick="scrollToTop()" class="scroll_btn">
		<span>▲</span>
		Top
	</a>
</div>


<div class="main_top_service_info">
	<div class="top_area">
		<div class="logo"><img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_logo_hd.png"  ></div>
		<div class="btn_login"><a href="/bbs/login.php">통합관리시스템로그인 ▶</a></div>
	</div>
	<div class="service_desc">
		<div class="txt_area">
			<p><span class="line"> </span>
				오직 <span>이로움</span>만의 <span>특별한 관리시스템</span></p>
			<p>장기요양기관</p>
			<p>통합관리시스템</p>
			<p>하단 아이콘에 마우스를 올려보세요.</p>
		</div>
		<div class="service_wrap">
			<ul>
				<li>
					<img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_top_service_icon_01.png"  >
					복지용구급여
					<div class="desc_area">
						<p class="desc_tit">복지용구급여</p>
						<p>
							복지용구란 일상생활 · 신체활동의 지원 및 인지 기능의 유지,<br>
							기능 향상에 필요한 용구를 말합니다.<br><br>
							
							본 시스템의 복지용구급여 서비스는<br>
							수급자 통합 관리 시스템으로 수급자와 복지용구 사업소의<br>
							연결 기능을 수행합니다.<br><br>
							
							외에도 재고관리, 주문과 동시에 수급자 계약 체결 기능,<br>
							공급계약서, 제공기록지등의 문서 양식 전자서식 도입,<br>
							건강보험공단 청구 시 검증 기능 등을<br><br>
							
							제공하여 사업소의 업무 효율성을 높여줍니다.   
						</p>
					</div>
				</li>
				<li>
					<img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_top_service_icon_02.png"  >
					방문급여
					<div class="desc_area">
						<p class="desc_tit">방문급여</p>
						<p>
							방문급여란 방문요양, 방문목욕, 방문간호 등의 서비스가 제공되며,<br>
							장기요양요원이나 간호사가 수급자의 가정 등을 방문하여<br>
							신체 활동 및 가사 활동, 진료의 보조등을 지원하는 장기요양 급여입니다.  <br><br>
							
							본 시스템에서 방문급여 서비스는<br>
							이용자관리/건강관리/종결 및 사후관리 등의 방문 급여시설의<br>
							업무 서비스를 지원합니다.
						</p>
					</div>
				</li>
				<li>
					<img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_top_service_icon_03.png"  >
					주야간보호급여
					<div class="desc_area">
						<p class="desc_tit">주야간보호급여</p>
						<p>
							주야간보호급여란 수급자를 하루 중 일정한 시간동안 장기요양기관에<br>
							보호하며 신체활동 지원 및 심신기능의 유지 · 향상을 위한<br>
							교육 · 훈련 등을 제공하는 장기 요양 급여를 말합니다.<br><br>
							
							본 시스템의 주야간보호급여 서비스는<br>
							이동/목욕/급식/간호/기능회복훈련/치매관리 등의 주야간 보호센터의<br>
							업무를 쉽고 간편하게 할 수 있도록 지원하는 기능을 제공합니다. 
						</p>
					</div>
				</li>
				<li>
					<img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_top_service_icon_04.png"  >
					시설급여
					<div class="desc_area">
						<p class="desc_tit">시설급여</p>
						<p>
							시설급여란 노인요양시설, 노인요양공동생활 가정 등에<br>
							장기간 입소하여, 신체활동 지원 및 심신기능유지 향상을 위한<br>
							교육 훈련 등을 제공합니다.<br><br>
							 
							* 노인요양시설의 입소정원  10명 이상<br>
							* 노인요양공동생활가정의 입소정원: 5~9명 
						</p>
					</div>
				</li>
				<li>
					<img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_top_service_icon_05.png"  >
					재무회계
					<div class="desc_area">
						<p class="desc_tit">재무회계</p>
						<p>
							복지용구란 일상생활 · 신체활동의 지원 및 인지 기능의 유지,<br>
							기능 향상에 필요한 용구를 말합니다.<br><br>
							
							본 시스템의 복지용구급여 서비스는<br>
							수급자 통합 관리 시스템으로 수급자와 복지용구 사업소의<br>
							연결 기능을 수행합니다.<br><br>
							
							외에도 재고관리, 주문과 동시에 수급자 계약 체결 기능,<br>
							공급계약서, 제공기록지등의 문서 양식 전자서식 도입,<br>
							건강보험공단 청구 시 검증 기능 등을<br><br>
							
							제공하여 사업소의 업무 효율성을 높여줍니다.   
						</p>
					</div>
				</li>
				<li>
					<img src="<?=G5_URL?>/thema/eroumcare/assets/img/main_top_service_icon_06.png"  >
					시니어타운
					<div class="desc_area">
						<p class="desc_tit">시니어타운</p>
						<p>
							본 시스템의 시니어타운 서비스는 시니어를 위한<br>
							토탈 케어 서비스, 의식주 및 돌봄, <br>
							각종 정보/어르신 일자리안내/노인용품/중고장터 등의<br>
							기능을 제공하여 줍니다. 

						</p>
					</div>
				</li>
			</ul>
		</div>
	</div>
	<div class="service_footer">
		이로움만의 장기요양기관 통합관리시스템으로 모든 것을 쉽고 편하게 관리해보세요.
	</div>
</div>

<?php if(($member["mb_level"] =="3"||$member["mb_level"] =="4")&&$_COOKIE["viewType"]=="basic"){ ?>
        <div class="top_mode_area">
            <?=$mood_type_string;?> 모드 실행중 입니다.
        </div>
<?php } ?>
<?php #//급여모드 보고시면  top_mode_area 주석 해제, mo_top에는 style="margin-top:50px;" 넣어야함  ?>
<div class="mo_top <?php echo $is_index; ?>" <?php if(($member["mb_level"] =="3"||$member["mb_level"] =="4")&&$_COOKIE["viewType"]=="basic"){ ?><?php  } ?>>
    <div class="logoWrap">
        <a href="<?=G5_URL?>"><img src="<?=THEMA_URL?>/assets/img/hd_logo.png" alt="이로움 로고"></a>
    </div>
    <div class="mode_div">
        <?php if(($member["mb_level"] =="3"||$member["mb_level"] =="4")){ ?>
            <?php if($_COOKIE["viewType"] == "adm"){ ?>
                <a href="#" class="modeBtn" data-type="basic" ><b>구매모드</b>
            </a>
            <?php } else { ?>
                <a href="#" class="modeBtn" data-type="adm"><b>급여안내모드</b></a>
            <?php } ?>
        <?php } ?>
    </div>
    <!-- <img src="<?=THEMA_URL?>/assets/img/btn_mo_menu_search.png" alt="" class="header-search-btn" onclick="gotosearch();"> -->
    <img src="<?=THEMA_URL?>/assets/img/btn_mo_menu_new.png" alt="" class="header-hamburger-btn">
</div>

<!-- <div id="thema_wrapper" class="wrapper <?php echo $is_thema_layout;?> <?php echo $is_thema_font;?>"<?php if($member["mb_level"] =="3"&&$_COOKIE["viewType"]=="basic"){ ?>style="margin-top:50px;"<?php } ?>> -->
<div id="thema_wrapper" class="wrapper <?php echo $is_thema_layout;?> <?php echo $is_thema_font;?> <?php echo $is_index ?>">
	
	<div id="samhwa-m-menu" >
		<div class="wrap"<?php if(($member["mb_level"] =="3"||$member["mb_level"] =="4")&&$_COOKIE["viewType"]=="basic"){ ?>style="margin-top:50px;"<?php } ?>>
			<div class="closer">
				<img src="<?php echo THEMA_URL; ?>/assets/img/btn_mo_menu_close.png" />
			</div>
			<div class="logo_area">
				<div class="ent_name"><?=($member["mb_entNm"] ? $member["mb_entNm"] : '이로움')?></div>
				<?php if($member['mb_level'] >= 9){ ?>
				<style>
						.or_manage{ width: 100%; height: 50px; line-height: 50px; text-align:center; margin-bottom:10px; color: #fff; background-color: #ef7c00; border-radius: 10px; }
				</style>
				<div class="or_manage">
						<a href="<?php echo G5_SHOP_URL?>/release_orderlist.php">관리자 주문 출고 관리</a> 
				</div>
				<?php } ?>
			</div>
			<div class="scrollable-wrap">

				<div class="mobileCate">
					<div class="cate_head">복지용구통합관리</div>
					<ul class="cate_menu">
						<li><a href="/shop/my_recipient_list.php">수급자 관리</a></li>
						<li><a href="/shop/claim_manage.php">청구/전자문서 관리</a></li>
						<li><a href="/shop/sales_Inventory.php">보유재고 관리</a></li>
						<li><a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php">주문/배송 관리</a></li>
						<li><a href="<?php echo G5_SHOP_URL; ?>/cart.php">장바구니</a></li>
						<li><a href="<?php echo G5_BBS_URL; ?>/mypage.php">마이페이지</a></li>
					</ul>
					<div class="cate_head">복지용구 품목</div>
					<ul class="cate_menu">
						<li><a href="/shop/list.php?ca_id=10"  >판매품목</a></li>
						<li><a href="/shop/list.php?ca_id=20"  >대여품목</a></li>
						<li><a href="/shop/list.php?ca_id=70"  >비급여품목</a></li>
					</ul>
				<a href="/thema/eroumcare/assets/eroum_catalog_2021_2_2.pdf" class="cata_link" target="_blank" alt="이달의 카달로그">
					<div class="catalogWrap">
						<span>이달의 카달로그</span>
						<img src="<?php echo THEMA_URL; ?>/assets/img/btn_catalogue_icon.png">
					</div>
				</a>
				<div class="cate_msg">
					<p>보다 나은 세상을 위해</p>
					<p class="bold">이로움이 함께합니다.</p>
				</div>
				<?php if($is_member) { // 로그인 상태 ?>
					<a href="<?php echo $at_href['logout'];?>">로그아웃</a>
				<?php }else{ ?>
					<a href="<?php echo $at_href['login'];?>" class="green">로그인</a>
				<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<script>
	$(document).ready(function() {

		$('.header-system-move-btn').click(function() {
			location.href = "https://system.eroumcare.com/cmm/cmm2000/cmm2000/selectCmm2003View.do";
		});

		$('.header-hamburger-btn').click(function() {
			// $('#samhwa-m-menu').toggle();
			$('#samhwa-m-menu').show(10);
			$('#samhwa-m-menu .wrap').addClass('active');
		});

    $('#samhwa-m-menu .wrap').click(function(e) {
      e.stopPropagation();
    });

		$('#samhwa-m-menu .wrap .closer, #samhwa-m-menu').click(function(e) {
			$('#samhwa-m-menu').hide(100);
			$('#samhwa-m-menu .wrap').removeClass('active');

		});

		$('#samhwa-m-menu .wrap .scrollable-wrap ul.mobile-cate>li').click(function() {
			$('#samhwa-m-menu .wrap .scrollable-wrap ul.mobile-cate>li').removeClass('on');
			$(this).addClass('on');
			//return false;
		});
		$('#samhwa-m-menu .wrap .scrollable-wrap ul.mobile-cate>li>a').dblclick(function() {
			console.log('aaa');
			window.location = this.href;
			//return false;
		});
	});
	</script>

	<div id="wrap">
		<?php
		// 배너
		$bn_md = array();
		$tb_result = sql_query("SELECT * FROM g5_shop_banner WHERE bn_device = 'both' AND ('" .G5_TIME_YMDHIS . "' between bn_begin_time and bn_end_time" . ") AND bn_position = '상단배너' ORDER BY bn_order ASC ");
		while($tb_row = sql_fetch_array($tb_result)) {
			$bn_md[] = $tb_row;
		}
		if ( $bn_md && $_COOKIE['top_banner_nomore'] != 'on' ) {
		?>
		<div class="container_wrap_wide">
			<div class="top_banner_wide">
				<div class="slick">
					<?php foreach($bn_md as $tb_row) { ?>
					<div class="item" style="background-color:<?php echo get_text($tb_row['bn_bgcolor']); ?>;">
						<img src="<?php echo G5_DATA_URL; ?>/banner/<?php echo $tb_row['bn_id']; ?>" title="<?php echo get_text($tb_row['bn_title']); ?>">
					</div>
					<?php }?>
				</div>
				<div class="top_banner_nomore">
					<input type="checkbox" name="top_banner_nomore" value="3" id="top_banner_nomore"><label for="top_banner_nomore">3일동안 보지 않기</label><span id="top_banner_nomore_close">닫기</span>
				</div>
			</div>
		</div>
		<?php } ?>


		<div class="container_wrap txt_center top_common_area"<?php if(($member["mb_level"] =="3"||$member["mb_level"] =="4")&&$_COOKIE["viewType"]=="basic"){ ?>style="margin-top:50px;"<?php } ?>>
			<style>
				.move_system{position:absolute; left:0; top:0; text-align:right;}
				.move_system a{padding:15px 20px 0 0;line-height:26px;color:#333;font-size:13px;font-weight:bold;float:left;color:#666;}
			</style>
			<div class="move_system">
				<!-- 바로가시 버튼 삭제 -->
			</div>
			<div class="logoWrap">
				<a href="<?=G5_URL?>" class="logoTitle"><img src="<?=THEMA_URL?>/assets/img/hd_logo.png"  ></a>
				<ul class="nav nav-left">
					<li><a href="/shop/my_recipient_list.php" >수급자</a></li>
					<li><a href="/shop/claim_manage.php"  >청구/전자문서</a></li>
					<li><a href="/shop/sales_Inventory.php" >보유재고</a></li>
				</ul>
				<ul class="nav nav-right">
					<li><a href="/shop/list.php?ca_id=10"  >판매품목</a></li>
					<li><a href="/shop/list.php?ca_id=20" >대여품목</a></li>
					<li><a href="/shop/list.php?ca_id=70" >비급여품목</a></li>
					<li class="catalog">
						<a href="/thema/eroumcare/assets/eroum_catalog_2021_2_2.pdf" target="_blank"  >
							<div class="catalogWrap">
								<span>이달의 카달로그</span>
								<img src="<?php echo THEMA_URL; ?>/assets/img/btn_catalogue_icon.png">
							</div>
						</a>
					</li>
				</ul>
			</div>
			<div class="bottomWrap">
				<?php if($member["mb_level"] == "3"||$member["mb_level"] == "4"){ ?>
				<div class="link_area"  style="float:right; padding:0px;">
						<a href="javascript:void(0)" style="cursor:default;"><?=$member["mb_entNm"]?></a>
				</div>
				<?php } ?>	
			</div>

      <div class="top_left_area">
        <div class="search">
					<form name="tsearch" method="get" onsubmit="return tsearch_submit(this);" role="form" class="form">
            <img src="<?php echo THEMA_URL; ?>/assets/img//btn_search.png" >
						<input type="hidden" name="url"	value="<?php echo (IS_YC) ? $at_href['isearch'] : $at_href['search'];?>">
						<input type="text" name="stx" value="<?php echo get_text($stx); ?>" id="search"/>
					</form>
				</div>
      </div>
			<div class="top_right_area">
				<div class="link_area">
                    <?php if(($member["mb_level"] =="3"||$member["mb_level"] =="4")){ ?>
                        <?php if($_COOKIE["viewType"] == "adm"){ ?>
                            <a href="#" class="modeBtn" data-type="basic">구매모드</a>
                        <?php } else { ?>
                            <a href="#" class="modeBtn" data-type="adm">급여안내모드</a>
                        <?php } ?>
                    <?php } ?>
                        
                        <?php if($is_member) { // 로그인 상태 ?>
														<!-- <a href="<?php echo G5_SHOP_URL; ?>/search.php" >상품검색</a> -->
                            <a href="<?php echo G5_SHOP_URL; ?>/cart.php" >장바구니</a>
                            <a href="<?php echo G5_BBS_URL; ?>/mypage.php" >마이페이지</a>
                            <a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php" >주문/배송</a>
                            <?php if($member['admin']) {?>
                                <a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
                            <?php } ?>
                            <?php if($is_samhwa_admin && !$member['admin']) {?>
                                <a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
                            <?php } ?>
                            <a href="<?php echo G5_BBS_URL; ?>/logout.php" >로그아웃</a>
                        <?php }else{ ?>
                            <a href="<?php echo $at_href['login'];?>" class="green">로그인</a>
                            <a href="<?=G5_BBS_URL?>/register.php"  >회원가입</a>
                    <!--						<a href="<?php echo $at_href['lost'];?>" class="win_password_lost">정보찾기</a>-->
                        <?php } ?>
                        <!-- <?php if ( $member['mb_type'] == 'partner' ) { ?>
                            <a href="https://signstand.co.kr/shop/list.php?ca_id=10">파트너전용</a>
                        <?php }else{ ?>
                            <a href="https://signstand.co.kr/shop/list.php?ca_id=10">기업전용</a>
                        <?php } ?> -->
				</div>
			</div>
			
			<script type="text/javascript" charset="utf-8">
					function pagePrintPreview(){
 
          var browser = navigator.userAgent.toLowerCase();
          if ( -1 != browser.indexOf('chrome') ){
                     window.print();
          }else if ( -1 != browser.indexOf('trident') ){
                     try{
                              //참고로 IE 5.5 이상에서만 동작함
 
                              //웹 브라우저 컨트롤 생성
                              var webBrowser = '<OBJECT ID="previewWeb" WIDTH=0 HEIGHT=0 CLASSID="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2"></OBJECT>';
 
                              //웹 페이지에 객체 삽입
                              document.body.insertAdjacentHTML('beforeEnd', webBrowser);
 
                              //ExexWB 메쏘드 실행 (7 : 미리보기 , 8 : 페이지 설정 , 6 : 인쇄하기(대화상자))
                              previewWeb.ExecWB(7, 1);
 
                              //객체 해제
                              previewWeb.outerHTML = "";
                     }catch (e) {
                              alert("- 도구 > 인터넷 옵션 > 보안 탭 > 신뢰할 수 있는 사이트 선택\n   1. 사이트 버튼 클릭 > 사이트 추가\n   2. 사용자 지정 수준 클릭 > 스크립팅하기 안전하지 않은 것으로 표시된 ActiveX 컨트롤 (사용)으로 체크\n\n※ 위 설정은 프린트 기능을 사용하기 위함임");
                     }
                    
                }
                
                }           
					
			</script>
			
		</div>
		
			<div id="headerTopQuickMenuWrap" class="<?=$is_index?>">
				<div>
					<div>
						<ul class="listWrap">
							<li>
								<a href="/shop/my_recipient_list.php" title="수급자">
									<span>수급자</span>
								</a>
							</li>
							<li>
								<a href="/shop/claim_manage.php" title="청구/전자문서">
									<span>청구/전자문서</span>
								</a>
							</li>
							<li>
								<a href="<?php echo G5_SHOP_URL?>/sales_Inventory.php" title="보유재고">
									<span>보유재고</span>
								</a>
							</li>
							<li class="marginDisable">
								<a href="/shop/list.php?ca_id=10" title="판매/대여품목">
									<span>판매/대여품목</span>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
	
		<div class="top_menu_wrap" style="display: none;">
			<div class="menu_wrap">
				<div class="menu"><div class="top_menu_all"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_top_menu2.png" ><span>전체 상품 카테고리</span></div>
				</div>
				<div class="main_menu">
					<table >
						<tr>
						<?php foreach($head_category as $cate) { ?>
							<td>
								<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' class='title'><?php echo $cate['ca_name']; ?></a>
								<div class="select_menu">
								<?php foreach($cate['sub'] as $i=>$sub) { ?>
									<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='cate_02 <?php echo $sub['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub['ca_name']; ?></a>
									<?php if (!empty($sub['sub'])) { ?>
										<?php foreach($sub['sub'] as $sub2) { ?>
											<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub2['ca_id']; ?>' class='cate_03 <?php echo $sub2['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub2['ca_name']; ?></a>
										<?php } ?>
									<?php } ?>
								<?php } ?>
								</div>
							</td>
						<?php } ?>
							<td><a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=60">추천상품</a></td>
							<td><a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=30">신상품</a></td>
						</tr>
					</table>
				</div>
				<div class="catalogueWrap">
					<a href="/thema/eroumcare/assets/catalog_2.pdf" target="_blank">
						<span>이로움 카달로그(2호)</span>
						<img src="<?php echo THEMA_URL; ?>/assets/img/btn_catalogue_icon.png" >
					</a>
				</div>
			</div>
			
			<div class="all_menu_wrap">
				<div class="all_menu">
					<table>
						<?php for($i=0;$i<count($category);$i++) { ?>
							<?php if ( $i == 0 ) echo '<tr>'; ?>
							<td>
								<div class="tit"><a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$category[$i]['ca_id']; ?>' class='sub-title'><?php echo $category[$i]['ca_name']; ?></a></div>
								<?php if ( $category[$i]['sub'] ) { ?>
									<?php foreach($category[$i]['sub'] as $sub) { ?>
										<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='sub-title'><?php echo $sub['ca_name']; ?></a>
									<?php } ?>
								<?php } ?>
							</td>
							<?php if ( $i != 0 && $i % 5 == 4 ) echo '</tr><tr>'; ?>
							<?php if ( $i == count($category)-1 ) echo '</tr>'; ?>
						<?php } ?>
					</table>
				</div>
			</div>
		</div>
		<?php
		$tutorials = get_tutorials();
		if ($tutorials) {
			$tutorial_percent = round($tutorials['completed_count'] / 8 * 100);
			$tutorial_percent = $tutorial_percent < 5 ? 5 : $tutorial_percent;
		?>
		<div id="head_tutorial">
			<div class="head_tutorial_info">
				<h4>신규사업소 등록을 환영합니다.</h4>
				<p>이로움 통합관리 서비스를 경험해보세요.</p>
				<div class="head_tutorial_progress">
					<div class="progress-bar">
						<span class="progress-bar-fill" style="width: <?php echo $tutorial_percent; ?>%;">
							<?php if ($tutorials['completed_count']) { ?>
								<?php echo $tutorial_percent; ?>% <span class="pc_layout">완료</span>
							<?php } ?>
						</span>
					</div>
				</div>
			</div>
			<ul class="head_tutorial_step">
				<?php 
				$t_recipient_add_idx = array_search('recipient_add', array_column($tutorials['step'], 't_type'));
				$t_recipient_add_class = $t_recipient_add_idx !== false ? ($tutorials['step'][$t_recipient_add_idx]['t_state'] ? 'complete' : 'active') : '';
				?>
				<li class="area <?php echo $t_recipient_add_class; ?>">
					<a href='<?php echo G5_SHOP_URL; ?>/my_recipient_write.php?tutorial=true'>
						수급자 신규등록
					</a>
				</li>
				<li class="next">></li>
				<li class="area">
					<a href='#'>
						수급자 주문체험
					</a>
				</li>
				<li class="next">></li>
				<li class="area">
					<a href='#'>
						전자문서 확인
					</a>
				</li>
				<li class="next">></li>
				<li class="area">
					<a href='#'>
						청구내역 확인
					</a>
				</li>
			</ul>
		</div>
		<?php
		}
		?>
		
		<div class="scroll_top">
			<div class="scroll_top_menu">
				<div class="scroll_top_menu_wrap">
					<div class="scroll_top_menu">
						<a href="<?php echo G5_URL; ?>"><img src="<?php echo THEMA_URL; ?>/assets/img//top_logo_s.png"></a>
						<div class="menu_area">
							<?php if($is_member) { // 로그인 상태 ?>
								<a href="<?php echo G5_SHOP_URL; ?>/cart.php" >장바구니</a>
								<a href="<?php echo G5_BBS_URL; ?>/mypage.php" >마이페이지</a>
								<a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php" >주문/배송</a>
								<?php if($member['admin']) {?>
									<a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
								<?php } ?>
								<?php if($is_samhwa_admin && !$member['admin']) {?>
									<a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
								<?php } ?>
								<a href="<?php echo G5_BBS_URL; ?>/logout.php" >로그아웃</a>
							<?php }else{ ?>
								<a href="<?php echo $at_href['login'];?>" class="green">로그인</a>
								<a href="<?php echo $at_href['reg'];?>">회원가입</a>
								<a href="<?php echo $at_href['lost'];?>" class="win_password_lost">정보찾기</a>
							<?php } ?>
							<?php if ( $member['mb_type'] == 'partner' ) { ?>
							<a href="#">파트너전용</a>
							<?php }else{ ?>
							<a href="#">기업전용</a>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<div class="top_menu_wrap ">
				<div class="menu_wrap">
					<div class="menu"><button class="top_menu_all"><span>전체카테고리</span> <img src="<?php echo THEMA_URL; ?>/assets/img//btn_top_menu.png" ></button></div>
					<div class="main_menu">
						<table >
							<tr>
								<?php foreach($head_category as $cate) { ?>
									<td>
										<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' class='title'><?php echo $cate['ca_name']; ?></a>
										<div class="select_menu">
											<table class="menu_area">
												<?php foreach($cate['sub'] as $i=>$sub) { ?>
													<?php if ( $i == 0 ) echo '<tr>'; ?>
														<td <?php echo $i == count($cate['sub'])-1 && count($cate['sub']) % 3 ? 'colspan="'.(4-count($cate['sub'])%3).'"' : ''; ?>>
															<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='cate_02 <?php echo $sub['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub['ca_name']; ?></a>
															<?php if (!empty($sub['sub'])) { ?>
																<?php foreach($sub['sub'] as $sub2) { ?>
																	<a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub2['ca_id']; ?>' class='cate_03 <?php echo $sub2['ca_id'] == $ca_id ? 'on' : ''; ?>'><?php echo $sub2['ca_name']; ?></a>
																<?php } ?>
															<?php } ?>
														</td>
													<?php if ( $i != 0 && $i % 3 == 2 ) echo '</tr><tr>'; ?>
													<?php if ( $i == count($cate['sub'])-1 ) echo '</tr>'; ?>
												<?php } ?>
											</table>
											
											<img src="<?php echo G5_DATA_URL; ?>/category/<?php echo $cate['ca_id']; ?>" alt="" />
										</div>
									</td>
								<?php } ?>
							</tr>
						</table>
					</div>
				</div>
			</div>
			
			
		</div>
		
		<?php if($page_title) { // 페이지 타이틀 ?>
			<div class="at-title">
				<div class="at-container">
					<div class="page-title en">
						<strong<?php echo ($bo_table) ? " class=\"cursor\" onclick=\"go_page('".G5_BBS_URL."/board.php?bo_table=".$bo_table."');\"" : "";?>>
							<?php echo $page_title;?>
						</strong>
					</div>
					<?php if($page_desc) { // 페이지 설명글 ?>
						<div class="page-desc hidden-xs">
							<?php echo $page_desc;?>
						</div>
					<?php } ?>
					<div class="clearfix"></div>
				</div>
			</div>
		<?php } ?>
		
		<?php if ( $is_main ) { ?>
		<?php @include(THEMA_PATH . '/main/samhwa-main.php'); ?>
		<?php } ?>

		<div class="at-body">
			<?php if($col_name) { ?>
				<div class="at-container">
					<?php if($is_member) { // 로그인 전에는 숨김 ?>
					<div class="scrollBannerListWrap left">
						<ul>
							<li>
								<a href="/bbs/content.php?co_id=guide">
									<img src="<?=THEMA_URL?>/assets/img/scroll_left_visual_01.jpg" alt="" />
								</a>
							</li>
							<li>
								<a href="/bbs/board.php?bo_table=faq&wr_id=6" >
									<img src="<?=THEMA_URL?>/assets/img/scroll_left_visual_02.jpg" alt="" />
								</a>
							</li>
							<li>
								<a href="<?=THEMA_URL?>/assets/티에이치케이컴퍼니_사업자등록증.pdf" target="_blank">
									<img src="<?=THEMA_URL?>/assets/img/scroll_left_visual_04.jpg" alt="" />
								</a>
							</li>
							<li>
								<a href="<?=THEMA_URL?>/assets/img/eroum_account.jpg" target="_blank">
									<img src="<?=THEMA_URL?>/assets/img/scroll_left_visual_03.jpg" alt="" />
								</a>
							</li>
						</ul>
					</div>
					
					<div class="scrollBannerListWrap right">
						<div class="todayViewWrap">
							<?php include(THEMA_PATH."/side/boxtodayview.skin.php"); ?>
						</div>
						
						<div class="goToTopBtnWrap">
							<img src="<?php echo THEMA_URL; ?>/assets/img/btn_go_to_top.png" alt="" onclick="$('html, body').animate({ scrollTop : 0 }, 1000);" />
						</div>
						<div class="btn_quick_area">
							<a href="/shop/cart.php">
								<?php if (get_boxcart_datas_count() > 0) { ?>
								<span class="num_cart"><?php echo get_boxcart_datas_count(); ?></span>
								<?php } ?>
								<img src="<?php echo THEMA_URL; ?>/assets/img/btn_quick_icon_cart.png" alt="" /><br>
								장바구니
							</a>
							<a href="/shop/wishlist.php">
								<img src="<?php echo THEMA_URL; ?>/assets/img/btn_quick_icon_wish.png" alt="" /><br>
								취급상품
							</a>
						</div>
					</div>
					<?php } ?>
				<?php if($col_name == "two") { ?>
					<div class="row at-row">
						<div class="col-md-<?php echo $col_content;?><?php echo ($at_set['side']) ? ' pull-right' : '';?> at-col at-main">		
				<?php } else { ?>
					<div class="at-content">
				<?php } ?>
			<?php } ?>
