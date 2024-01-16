<?php
if (!defined('_GNUBOARD_')) exit;

function print_menu1($key, $no) {
    global $menu;

    $str = print_menu2($key, $no);

    return $str;
}

function print_menu2($key, $no) {
    global $menu, $auth_menu, $is_admin, $auth, $g5, $sub_menu;

    $str .= "<ul class=\"gnb_2dul\">";
    for($i=1; $i<count($menu[$key]); $i++) {
		if($menu[$key][$i][1] == "회원탈퇴 관리"){
			$sql = "SELECT COUNT(*) AS cnt FROM `g5_member_leave` WHERE mb_leave_date2 !='' and mb_leave_date3 = ''";
			$cnt = sql_fetch($sql);
			if($cnt['cnt'] > 0){
				$red_dot="<span style='float:right;background-color:#b81e01;color:#fff;border-radius: 20px;height:20px;width:20px;text-align:center;padding-top:4px;margin-top:-2px;font-weight:bold;'>".$cnt['cnt']."</span>";
			}else{
				$red_dot="";
			}
		}else{
			$red_dot="";
		}
		
		if(!$menu[$key][$i][1])
			continue;

        if ($is_admin != 'super' && (!array_key_exists($menu[$key][$i][0],$auth) || !strstr($auth[$menu[$key][$i][0]], 'r')))
            continue;

        if (($menu[$key][$i][4] == 1 && $gnb_grp_style == false) || ($menu[$key][$i][4] != 1 && $gnb_grp_style == true)) $gnb_grp_div = 'gnb_grp_div';
        else $gnb_grp_div = '';

        if ($menu[$key][$i][4] == 1) $gnb_grp_style = 'gnb_grp_style';
        else $gnb_grp_style = '';

		if (isset($sub_menu) && $sub_menu == $menu[$key][$i][0]) $gnb_on = ' on';
        else $gnb_on = '';

		$gnb_item_qa = $menu[$key][$i][1] == '상품문의' ? 'gnb_2da_itemqa' : '';
		$gnb_item_use = $menu[$key][$i][1] == '사용후기' ? 'gnb_2da_itemuse' : '';
		$gnb_item_use = $menu[$key][$i][1] == '1:1문의설정' ? 'gnb_2da_qa' : '';
        $str .= '<li class="gnb_2dli"><a href="'.$menu[$key][$i][2].'" class="gnb_2da '.$gnb_grp_style.' '.$gnb_grp_div.$gnb_on.' '.$gnb_item_qa.$gnb_item_use.'">'.$menu[$key][$i][1].$red_dot.'</a></li>';

        $auth_menu[$menu[$key][$i][0]] = $menu[$key][$i][1];
    }
    $str .= "</ul>";

    return $str;
}

// Load Setting
$aset = array();
$aset = apms_admin_skin($_POST['asave'], $_POST['aset']); // 설정값 불러오기
$aset['css'] = (isset($aset['css']) && $aset['css']) ? $aset['css'] : 'basic'; // CSS스킨
$aset['logo'] = (isset($aset['logo']) && $aset['logo']) ? $aset['logo'] : '{아이콘:cube} ADMINISTRATOR'; // 텍스트 로고
$aset['font'] = (isset($aset['font']) && $aset['font']) ? $aset['font'] : '#fff'; // 메뉴 호버시 글자색
$aset['hover'] = (isset($aset['hover']) && $aset['hover']) ? $aset['hover'] : '#08a2cd'; // 메뉴 호버시 배경색
$aset['fixed'] = (isset($aset['fixed']) && $aset['fixed']) ? true : false; // 메뉴 상단고정

?>

<script>
var tempX = 0;
var tempY = 0;

function imageview(id, w, h)
{

    menu(id);

    var el_id = document.getElementById(id);

    //submenu = eval(name+".style");
    submenu = el_id.style;
    submenu.left = tempX - ( w + 11 );
    submenu.top  = tempY - ( h / 2 );

    selectBoxVisible();

    if (el_id.style.display != 'none')
        selectBoxHidden(id);
}
</script>

<style>
#inb a:hover, #side_menu a:hover, #side_menu a:focus, .gnb_1dli_on .gnb_1da, .gnb_1dli_air .gnb_1da, .gnb_2da:focus, .gnb_2da:hover {
	color: <?php echo $aset['font'];?> !important;
	background: <?php echo $aset['hover'];?> !important;
}
</style>

<link rel="stylesheet" href="<?php echo ADMIN_SKIN_URL;?>/css/<?php echo $aset['css'];?>/admin.css">

<div id="to_content"><a href="#container">본문 바로가기</a></div>

<header id="hd">
	<div id="hd_wrap">
		<h1><?php echo $config['cf_title'] ?></h1>

		<div id="logo"><a href="<?php echo G5_ADMIN_URL ?>"><?php echo apms_fa($aset['logo']);?></a> <span><?php echo $member['mb_name']; ?></span></div>
		
		<div id="adm_search_box">
			
			<!-- <div class="top_btn_area">
					<button type="button" onclick="location.href='<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_orderlist.php'">통합주문리스트</button>
					<button type="button" onclick="location.href='<?php echo G5_ADMIN_URL; ?>/member_list.php'">회원리스트</button>
					<button type="button" onclick="location.href='<?php echo G5_ADMIN_URL; ?>/shop_admin/albank.php'">무통장</button>
			</div> -->
			<form id="fadmsearch" name="fadmsearch" class="local_sch" method="get" action="<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_orderlist.php">
				<select name="sel_field" id="head_sel_field">
                    <option value="od_all" <?php echo $sel_field == 'od_all' ? 'selected="selected"' : ''; ?>>전체</option>
					<option value="od_name" <?php echo $sel_field == 'od_name' ? 'selected="selected"' : ''; ?>>주문자</option>
					<option value="od_b_name" <?php echo $sel_field == 'od_b_name' ? 'selected="selected"' : ''; ?>>받는분</option>
                    <option value="it_name" <?php echo $sel_field == 'it_name' ? 'selected="selected"' : ''; ?>>상품명</option>
					<option value="od_id" <?php echo $sel_field == 'od_id' ? 'selected="selected"' : ''; ?>>주문번호</option>
					<option value="od_naver_orderid" <?php echo $sel_field == 'od_id' ? 'selected="selected"' : ''; ?>>네이버페이주문번호</option>
					<option value="mb_id" <?php echo $sel_field == 'mb_id' ? 'selected="selected"' : ''; ?>>회원 ID</option>
					<option value="od_tel" <?php echo $sel_field == 'od_tel' ? 'selected="selected"' : ''; ?>>주문자전화</option>
					<option value="od_hp" <?php echo $sel_field == 'od_hp' ? 'selected="selected"' : ''; ?>>주문자핸드폰</option>
					<option value="od_b_tel" <?php echo $sel_field == 'od_b_tel' ? 'selected="selected"' : ''; ?>>받는분전화</option>
					<option value="od_b_hp" <?php echo $sel_field == 'od_b_hp' ? 'selected="selected"' : ''; ?>>받는분핸드폰</option>
					<option value="od_deposit_name" <?php echo $sel_field == 'od_deposit_name' ? 'selected="selected"' : ''; ?>>입금자</option>
					<option value="od_invoice" <?php echo $sel_field == 'od_invoice' ? 'selected="selected"' : ''; ?>>운송장번호</option>
				</select>
				<input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
				<button type="submit"><span>검색</span></button>
			</form>
		</div>

		<ul id="tnb">
			<!-- <li><a href="<?php echo G5_ADMIN_URL ?>/member_form.php?w=u&amp;mb_id=<?php echo $member['mb_id'] ?>">관리자정보</a></li>
			<li><a href="<?php echo G5_ADMIN_URL ?>/config_form.php">기본환경</a></li>
			<li><a href="<?php echo G5_ADMIN_URL ?>/service.php">부가서비스</a></li>
			<li><a href="<?php echo G5_URL ?>/">커뮤니티</a></li>
			<?php if(defined('G5_USE_SHOP')) { ?>
			<li><a href="<?php echo G5_ADMIN_URL ?>/shop_admin/configform.php">쇼핑몰환경</a></li> -->
			<li><a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_orderlist.php" >통합주문리스트</a></li>
			<li><a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/samhwa_deliverylist.php">출고리스트</a></li>
			<li><a href="<?php echo G5_ADMIN_URL; ?>/member_list.php">회원리스트</a></li>
			<li><a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/itemlist.php">상품정보</a></li>
			<li><a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/outsourcinglist.php">외부발주</a></li>
			<!-- <li><a href="<?php echo G5_SHOP_URL ?>/" target="_blank">쇼핑몰</a></li> -->
			<?php } ?>
			<li id="tnb_logout"><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></li>
		</ul>
		
		<nav id="gnb">
			<div id="inb">
				<a href="<?php echo G5_URL ?>/" title="커뮤니티"><i class="fa fa-home"></i></a>
				<?php if(IS_YC) { ?>
					<a href="<?php echo G5_SHOP_URL ?>/" title="쇼핑몰"><i class="fa fa-shopping-cart"></i></a>
				<?php } ?>
				<a class="cursor btn-switcher" title="스킨설정"><i class="fa fa-cog"></i></a>
				<a href="<?php echo G5_BBS_URL ?>/logout.php" title="로그아웃"><i class="fa fa-sign-out"></i></a>
			</div>
			<?php
			$gnb_str = "<ul id=\"gnb_1dul\">";
			
			$gnb_index_class = ($is_index) ? ' gnb_1dli_air' : '';

			foreach($amenu as $key=>$value) {
        if($key == '999') {
          foreach($menu['menu'.$key] as $m_row) {
            $auth_menu[$m_row[0]] = $m_row[1];
          }
          continue;
        }
				$href1 = $href2 = '';
				if ($menu['menu'.$key][0][2]) {
					//$board_class = $menu['menu'.$key][0][1] == '게시판관리' ? 'gnb_1da_board' : '';
					$count_class = $menu['menu'.$key][0][1] == '게시판관리' ? 'gnb_1da_board' : '';
					$count_class = $menu['menu'.$key][0][1] == '쇼핑몰관리' ? 'gnb_1da_shop' : $count_class;
					$href1 = '<a href="'.$menu['menu'.$key][0][2].'" class="gnb_1da '.$count_class.'">';
					$href2 = '</a>';
				} else {
					continue;
				}
				$current_class = "";
				if (isset($sub_menu) && (substr($sub_menu, 0, 3) == substr($menu['menu'.$key][0][0], 0, 3)))
					$current_class = " gnb_1dli_air";
				$gnb_str .= '<li class="gnb_1dli'.$current_class.'">'.PHP_EOL;
				$sql = "SELECT COUNT(*) AS cnt FROM `g5_member_leave` WHERE mb_leave_date2 !='' and mb_leave_date3 = ''";
				$cnt = sql_fetch($sql);
				if($cnt['cnt'] > 0 && $menu['menu'.$key][0][1] == "회원관리"){
					$red_dot="<span class='board_cnt'>".$cnt['cnt']."</span>";
				}else{
					$red_dot="";
				}
				$gnb_str .=  $href1 . $menu['menu'.$key][0][1] .$red_dot. $href2;
				$gnb_str .=  print_menu1('menu'.$key, 1);
				$gnb_str .=  "</li>";
			}
			$gnb_str .= "</ul>";
			echo $gnb_str;
			?>
		</nav>

	</div>
</header>

<div id="tbl_wrap">
	<div id="side_wrap">
		<div id="side_login">
			<div class="user">
				<div class="photo">
					<a href="<?php echo $at_href['myphoto'];?>" target="_blank" class="win_memo">
						<?php echo ($member['photo']) ? '<img src="'.$member['photo'].'" alt="">' : '<i class="fa fa-user-plus"></i>'; //사진 ?>
					</a>
				</div>
				<div class="name">
					<p>
						<?php echo apms_sideview($member['mb_id'], $member['mb_nick'], $member['mb_email'], $member['mb_homepage'], 'no');?> 
					</p>
					<?php echo $member['grade'];?>
				</div>
			</div>
			<ul class="msg">
				<li>
					<a href="<?php echo $at_href['response'];?>" target="_blank" class="win_memo">
						미확인 알림
						<span class="pull-right">
							<?php echo ($member['response'] > 0) ? '<b class="red">'.number_format($member['response']).'</b> 개' : '없음';?>
						</span>
					</a>		
				</li>
				<li>
					<a href="<?php echo $at_href['memo'];?>" target="_blank" class="win_memo">
						미확인 쪽지
						<span class="pull-right">
							<?php echo ($member['memo'] > 0) ? '<b class="orangered">'.number_format($member['memo']).'</b> 개' : '없음';?>
						</span>
					</a>		
				</li>
			</ul>
		</div>
		
		<ul id="side_menu">
		<?php
			foreach($amenu as $key=>$value) {
				if ($menu['menu'.$key][0][1] && $menu['menu'.$key][0][2]) {
					if (isset($sub_menu) && $sub_menu && (substr($sub_menu, 0, 3) == substr($menu['menu'.$key][0][0], 0, 3))) {

						echo '<li><a href="'.$menu['menu'.$key][0][2].'" class="on">'.$menu['menu'.$key][0][1].'</a>';

						$menu_key = substr($sub_menu, 0, 3);
						$nl = '';
						foreach($menu['menu'.$menu_key] as $key=>$value) {

							if(!($value[1] && $value[2])) continue;

							if($key > 0) {
								if ($is_admin != 'super' && (!array_key_exists($value[0],$auth) || !strstr($auth[$value[0]], 'r')))
									continue;

								$on_class = ($sub_menu == $value[0]) ? ' class="on"' : '';

								$nl .= '<li><a href="'.$value[2].'"'.$on_class.'>'.$value[1].'</a></li>'.PHP_EOL;
							}
						}

						if($nl) echo '<ul>'.$nl.'</ul>'.PHP_EOL;

						echo '</li>'.PHP_EOL;
					} else {
						echo '<li><a href="'.$menu['menu'.$key][0][2].'">'.$menu['menu'.$key][0][1].'</a></li>';
					}
				}
			}
		?>
		</ul>
	</div>
	<div id="wrapper">
		<div id="container">
			<div id="text_size">
				<!-- font_resize('엘리먼트id', '제거할 class', '추가할 class'); -->
				<button onclick="font_resize('container', 'ts_up ts_up2', '');"><img src="<?php echo G5_ADMIN_URL ?>/img/ts01.gif" alt="기본"></button>
				<button onclick="font_resize('container', 'ts_up ts_up2', 'ts_up');"><img src="<?php echo G5_ADMIN_URL ?>/img/ts02.gif" alt="크게"></button>
				<button onclick="font_resize('container', 'ts_up ts_up2', 'ts_up2');"><img src="<?php echo G5_ADMIN_URL ?>/img/ts03.gif" alt="더크게"></button>
			</div>
			<h1 class="page_title"><?php echo $g5['title'] ?></h1>
