<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가



if($_GET['co_id']=="possession_manage"){ ?>
<link rel="stylesheet" href="<?php G5_URL?>/skin/apms/possession_manage/css/stock_page.css">
<?php include_once(G5_PATH.'/skin/apms/possession_manage/sales_Inventory.html'); } ?>

		<?php if($col_name) { ?>
			<?php if($col_name == "two") { ?>
					</div>
					<div class="col-md-<?php echo $col_side;?><?php echo ($at_set['side']) ? ' pull-left' : '';?> at-col at-side">
						<?php include_once($is_side_file); // Side ?>
					</div>
				</div>
			<?php } else { ?>
				</div><!-- .at-content -->
			<?php } ?>
			</div><!-- .at-container -->
		<?php } ?>

	</div><!-- .at-body -->
	</div><!-- wrap -->

	<div class="footer_area">
		<div>
			<div>
				<div class="logo"><img src="<?php echo THEMA_URL; ?>/assets/img/footer_logo.png" alt="" /></div>
				<div class="info">
					<p class="link">
						<a href="<?php echo G5_BBS_URL;?>/content.php?co_id=company">회사소개 </a>   |   <a href="<?php echo G5_BBS_URL;?>/content.php?co_id=provision">이용약관</a>    |   <a href="<?php echo G5_BBS_URL;?>/content.php?co_id=privacy"><strong>개인정보처리방침</strong></a>
					</p>
					<p>
						<?php echo $default['de_admin_company_name']; ?> <span class="pc_only"> ㅣ </span> <span class="mo_br"></span> 대표 : <?php echo $default['de_admin_company_owner']; ?> ㅣ 사업자등록번호 : <?php echo $default['de_admin_company_saupja_no']; ?> <a href="javascript:;" onclick="window.open('https://www.ftc.go.kr/bizCommPop.do?wrkr_no=6178614330','communicationViewPopup','width=750,height=700,scrollbars=yes')">[사업자정보확인]</a> <span class="pc_only"> ㅣ </span> <span class="mo_br"></span> 통신판매신고번호 : <?php echo $default['de_admin_tongsin_no']; ?> | 개인정보보호관리자 : <?php echo $default['de_admin_info_name']; ?><br>
						주소 : <?php echo $default['de_admin_company_addr']; ?><!-- <a href="http://naver.me/F4in5mn2" class="btn_map" target="_blank">지도보기 <img src="<?php echo THEMA_URL; ?>/assets/img/icon_pin.png" alt="" /></a> -->
            <br>
            사무소 :  서울시 금천구 서부샛길 606 대성디폴리스 B동 1401호 ㅣ 이메일 : eroum@thkc.co.kr
            <br>
            물류센터 : 인천광역시 서구 이든1로 21
					</p>
					<div class="desc">
						본, 쇼핑몰의 모든 정보, 콘텐츠 및 UI, 저작물 등의 저작권은 <span class="mo_br"></span><?php echo $default['de_admin_company_name']; ?>에 있으며, 어떠한 이유에서도<br>
						전시, 전송, 스크래핑, 무단복제, 도용 등은 저작권법(제97조5항)에 의거 금지되어 있으므로 이를 위반 시 법적처벌을 받을 수 있습니다.<br><br>
						Copyright ⓒEroumcare All righs reserved.
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div id="footerMoWrap" class="mo_layout">
		<ul>
			<li class="<?php echo defined('_INDEX_') ? 'active' : ''; ?>">
				<a href="/" title="홈">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/footerMoIconHome<?php echo defined('_INDEX_') ? 'Active' : ''; ?>.png" alt="홈">
					</p>
					<p class="name">홈</p>
				</a>
			</li>
			<li class="<?php echo defined('_RECIPIENT_') ? 'active' : ''; ?>">
				<a href="/shop/my_recipient_list.php" title="수급자">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/footerMoIconRecipient<?php echo defined('_RECIPIENT_') ? 'Active' : ''; ?>.png" alt="수급자">
					</p>
					<p class="name">수급자</p>
				</a>
			</li>
			<li class="<?php echo defined('_INVENTORY_') ? 'active' : ''; ?>">
				<!-- <a href="/bbs/content.php?co_id=inventory_guide" title="보유재고"> -->
				<a href="<?php echo G5_SHOP_URL?>/sales_Inventory.php" title="보유재고">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/footerMoIconStock<?php echo defined('_INVENTORY_') ? 'Active' : ''; ?>.png" alt="보유재고">
					</p>
					<p class="name">보유재고</p>
				</a>
			</li>
			<li class="<?php echo defined('_CART_') ? 'active' : ''; ?>">
				<a href="/shop/cart.php" title="장바구니">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/footerMoIconCart<?php echo defined('_CART_') ? 'Active' : ''; ?>.png" alt="장바구니">
					</p>
					<p class="name">장바구니</p>
				</a>
			</li>
			<li class="<?php echo defined('_MYPAGE_') ? 'active' : ''; ?>">
				<a href="/bbs/mypage.php" title="설정">
					<p class="img">
						<img src="<?=THEMA_URL?>/assets/img/footerMoIconMore<?php echo defined('_MYPAGE_') ? 'Active' : ''; ?>.png" alt="설정">
					</p>
					<p class="name">설정</p>
				</a>
			</li>
		</ul>
	</div>

</div><!-- .wrapper -->

<!--<div class="at-go">
	<div id="go-btn" class="go-btn">
		<span class="go-top cursor"><i class="fa fa-chevron-up"></i></span>
		<span class="go-bottom cursor"><i class="fa fa-chevron-down"></i></span>
	</div>
</div>-->

<?php if ($_SESSION['recipient']) { ?>
<div id="fixed_recipient">
  <div class="info_wrap">
    <div class="info">
      <a href="<?php echo G5_SHOP_URL; ?>/my_recipient_view.php?id=<?=$_SESSION['recipient']['penId']?>">
        <h5>
          <?php echo $_SESSION['recipient']['penNm']; ?>
          (<?php echo substr($_SESSION['recipient']['penBirth'], 2, 2); ?>년생/<?php echo $_SESSION['recipient']['penGender']; ?>)
        </h5>
        <p>
          <?php echo $_SESSION['recipient']["penLtmNum"]; ?>
          (<?php if($_SESSION['recipient']["penRecGraNm"]==''){echo str_replace('0','',$_SESSION['recipient']["penRecGraCd"])."등급";} else {echo $_SESSION['recipient']["penRecGraNm"];} ?><?php echo $pen_type_cd[$_SESSION['recipient']['penTypeCd']] ? '/' . $pen_type_cd[$_SESSION['recipient']['penTypeCd']] : ''; ?>)
        </p>
      </a>
    </div>
    
    <a href='<?php echo G5_SHOP_URL; ?>/connect_recipient.php' class="close" onClick="return confirm('<?php echo $_SESSION['recipient']['penNm']; ?> 수급자 연결을 해지하겠습니까?');">
      <i class="fa fa-times"></i>
    </a>
    <a href='<?php echo G5_SHOP_URL; ?>/cart.php' class="cart">
      장바구니<br/><b><?php echo get_carts_by_recipient($_SESSION['recipient']['penId']); ?>개</b>
    </a>
  </div>
  <?php
  $limit_status = $limit_txt = null;

  if($_GET['it_id']) {
    $ca_id = sql_fetch("
      SELECT
        ca_id
      FROM
        {$g5['g5_shop_item_table']}
      WHERE
        it_id = '{$_GET['it_id']}'
    ")['ca_id'];
  }
  if($ca_id && strlen($ca_id) == 2 && $ca_sub)
    $ca_id .= $ca_sub[0];

  if($ca_id && strlen($ca_id) == 4) {

    // 수급자 취급품목인지 체크
    $pen_items = get_items_by_recipient($_SESSION['recipient']['penId']);
    $product_cate_table = $sale_product_cate_table + $rental_product_cate_table;

    $pen_item_flag = false;
    foreach($pen_items as $pen_item) {
      if($product_cate_table[$pen_item['itemId']] == $ca_id) {
        $pen_item_flag = true;
        break;
      }
    }
    
    if(!$pen_item_flag) {
      // 취급 품목이 아니면
      $cate_product_table = array_flip($product_cate_table);
      $product_table = $sale_product_table + $rental_product_table;
      $limit_status = 'warn';
      $limit_txt = "{$product_table[$cate_product_table[$ca_id]]} 구매 불가능";
    } else {
      // 내구연한(품목 별 구매가능 개수) 체크
      $limit = get_pen_category_limit($_SESSION['recipient']['penLtmNum'], $ca_id);
      if($limit) {
        $cur = intval($limit['num']) - intval($limit['current']);
        if($cur > 0) {
          $limit_status = 'good';
          $limit_txt = "{$limit['ca_name']} {$cur}개 구매가능";
        } else {
          $limit_status = 'warn';
          $limit_txt = "{$limit['ca_name']} 구매 수 초과";
        }
      }
    }
  }

  if($limit_status && $limit_txt) {
  ?>
  <div class="limit">
    <span class="<?=$limit_status?>">
      *<?=$limit_txt?>
    </span>
  </div>
  <?php
  }
  ?>
</div>
<?php } ?>

<!--[if lt IE 9]>
<script type="text/javascript" src="<?php echo THEMA_URL;?>/assets/js/respond.js"></script>
<![endif]-->

<!-- JavaScript -->
<script type="text/javascript">
	function setCookie(name, data){
		var date = new Date();

		if(data){
			date.setDate(date.getDate() + 1);
		} else {
			date.setDate(date.getDate() - 1);
		}

		var willCookie = "";
		willCookie += name + "=" + data + ";";
		willCookie += "path=/;";
		willCookie += "expires=" + date.toUTCString();

		document.cookie = willCookie;
	}
	
	<?php if($_GET["viewType"]){ ?>
//		setCookie("viewType", "<?=$_GET["viewType"]?>");
//		window.location.href = "/";
	<?php } ?>
	
	$(function(){
    $('#btn_mo_menu').click(function() {
      $('html, body').addClass('modal-open');
      $('.side_menu_area').addClass('active')
        .css({right: -322})
        .stop(true, true)
        .animate({right: 0}, 150);
      $('.mobile_menu_backdrop').show();
      $('.side_menu_area .scrollable_wrap').scrollTop(0);
    });

    $('.btn_close_side_menu, .mobile_menu_backdrop').click(function() {
      $('html, body').removeClass('modal-open');
      $('.side_menu_area')
        .stop(true, true)
        .animate({right: -322}, 150, function() {
          $(this).removeClass('active');
        });
      $('.mobile_menu_backdrop').hide();
    });

    <?php if($member['mb_type'] === 'normal') { ?>
    $('#sel_pen_ent').change(function() {
      var ent_mb_id = $(this).val();
      if(ent_mb_id) {
        window.location.href = '/shop/connect_ent.php?ent_mb_id=' + $(this).val();
      }
    });
    <?php } ?>
		
		<?php if($member["mb_level"] == "3"||$member["mb_level"] =="4") { ?>
		$(".modeBtn").click(function(e){
			e.preventDefault();
			
			$.ajax({
				url : "/shop/ajax.mode.change.php",
				type : "POST",
				data : {
					type : $(this).attr("data-type")
				},
				success : function(){
					window.location.reload();
				}
			});
		});
		<?php } ?>
	});
</script>

<script>
var sub_show = "<?php echo $at_set['subv'];?>";
var sub_hide = "<?php echo $at_set['subh'];?>";
var menu_startAt = "<?php echo ($m_sat) ? $m_sat : 0;?>";
var menu_sub = "<?php echo $m_sub;?>";
var menu_subAt = "<?php echo ($m_subsat) ? $m_subsat : 0;?>";
</script>
<script src="<?php echo THEMA_URL;?>/assets/bs3/js/bootstrap.min.js"></script>
<script src="<?php echo THEMA_URL;?>/assets/js/sly.min.js"></script>
<script src="<?php echo THEMA_URL;?>/assets/js/custom.js"></script>
<?php if($is_sticky_nav) { ?>
<script src="<?php echo THEMA_URL;?>/assets/js/sticky.js"></script>
<?php } ?>

<?php echo apms_widget('basic-sidebar'); //사이드바 및 모바일 메뉴(UI) ?>

<?php if($is_designer || $is_demo) include_once(THEMA_PATH.'/assets/switcher.php'); //Style Switcher ?>

<script>
<?php if ($member['mb_id']) { ?>
try {
  if (navigator.userAgent.indexOf("Android") > - 1) {
    window.EroummallApp.requestToken("");
  } else if (navigator.userAgent.indexOf("iPhone") > - 1) {
    window.webkit.messageHandlers.requestToken.postMessage("");
  }
} catch(ex) {
  // do nothing
}
<?php } ?>
function pushKey(token) {
	$.post('/api/register_token.php', {
		token: token,
	}, 'json')
	.done(function(data) {
	});
}
</script>
