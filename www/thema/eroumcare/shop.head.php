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
  switch(e.data) {
    case "nowPage" :
      history.go(-1);
      break;
  }
});
</script>

<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
include_once(THEMA_PATH.'/assets/thema.php');

$is_approved = false;

if($member['mb_id']) {
  // 수급자 활동 알림
  category_limit_noti();

  // 승인여부
  $res = api_post_call(EROUMCARE_API_ENT_ACCOUNT, array(
    'usrId' => $member['mb_id']
  ));
  if($res['data']['entConfirmCd'] == '01' || $member['mb_level'] >= 5 || $is_samhwa_partner || $member['mb_type'] === 'normal' ) {
    $is_approved = true;
  }

  // 쿠폰
  $cp_count = 0;
  $sql = "
    select cp_id
    from {$g5['g5_shop_coupon_table']} c
    left join g5_shop_coupon_member m on c.cp_no = m.cp_no
    where
      (
        c.mb_id IN ( '{$member['mb_id']}', '전체회원' ) or
        m.mb_id = '{$member['mb_id']}'
      )
      and cp_start <= '".G5_TIME_YMD."'
      and cp_end >= '".G5_TIME_YMD."'
    group by c.cp_no
  ";
  $res = sql_query($sql, true);
  for($k=0; $cp=sql_fetch_array($res); $k++) {
    if(!is_used_coupon($member['mb_id'], $cp['cp_id']))
    $cp_count++;
  }

  // 미수금
  if($member['mb_type'] == 'partner') $balance = get_partner_outstanding_balance($member['mb_id'], null, false, true);
  else $balance = get_outstanding_balance($member['mb_id'], null, false, true);
  
  // 주문건수
  if($member['mb_type'] == 'partner') {
    $result = sql_fetch("
      SELECT count(*) as cnt
      FROM {$g5['g5_shop_cart_table']}
      WHERE
        ct_status = '완료' and
        ct_is_direct_delivery IN(1, 2) and
        ct_direct_delivery_partner = '{$member['mb_id']}' and
        ct_select_time >= '".date('Y-m-01')." 00:00:00'
    ");
  } else {
    $result = sql_fetch("
      SELECT count(*) as cnt
      FROM {$g5['g5_shop_cart_table']}
      WHERE
        mb_id = '{$member['mb_id']}' and
        ct_status = '완료' and
        ct_qty - ct_stock_qty > 0 and
        ct_select_time >= '".date('Y-m-01')." 00:00:00'
    ");
  }
  $order_count = $result['cnt'] ?: 0;

  // 진행중인 이벤트 개수
  $result = sql_fetch(" select count(*) as cnt from g5_write_event where wr_is_comment = 0 ");
  $event_count = $result['cnt'] ?: 0;

  // 파트너회원: 거래가 1개라도 배정된 적이 있는 경우에만 메뉴 출력
  $show_partner_menu = true;
  if($member['mb_type'] == 'partner') {
    $result = sql_fetch("
      SELECT count(*) as cnt
      FROM {$g5['g5_shop_cart_table']}
      WHERE
        ct_is_direct_delivery IN(1, 2) and
        ct_direct_delivery_partner = '{$member['mb_id']}'
    ");
    if(!$result['cnt']) $show_partner_menu = false;
  }

  // 일반회원(수급자회원)
  if($member['mb_type'] == 'normal') {
    $pen_ents = get_pen_ent_by_pen_mb_id($member['mb_id']);
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

<!-- 모드바 스타일링 -->
<style>
  .top_mode_area{ position:fixed; top :0;z-index:9999999; display:block; width:100%; height:50px; text-align:center; background-color: #666;  color : #fff; font-size: 20px; line-height:50px; }
  @media screen and (max-width: 1200px){
    .top_mode_area{font-size:10px;display:none;}
  }
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

<?php
// 메인페이지 헤더 불러오기
if($is_main && !$is_member) {
  include_once('main/e-mall-index.head.php');
}
?>

<?php if(($member["mb_level"] =="3" || $member["mb_level"] =="4") && $_COOKIE["viewType"] == "basic") { ?>
<div class="top_mode_area">
  급여안내 모드 실행중 입니다.
</div>
<?php } ?>

<div class="mo_top">
  <div class="logo_wrap">
    <a href="<?=G5_URL?>" class="logo_title"><img src="<?=THEMA_URL?>/assets/img/hd_logo.png"></a>
  </div>
  <?php
  if(($member["mb_level"] =="3" || $member["mb_level"] =="4")) {
    if($_COOKIE["viewType"] == "adm") {
      echo '<a href="#" class="modeBtn" data-type="basic">구매모드</a>';
    } else {
      echo '<a href="#" class="modeBtn" data-type="adm">급여안내모드</a>';
    }
  }
  ?>
  <div id="btn_mo_menu">
    <img src="<?=THEMA_URL?>/assets/img/btn_hamburger.png" alt="메뉴">
  </div>
</div>

<div id="thema_wrapper" class="wrapper <?php echo $is_thema_layout;?> <?php echo $is_thema_font;?> <?php echo $is_index ?>">
  <div id="wrap" <?php if(($member["mb_level"] == "3" || $member["mb_level"] == "4") && $_COOKIE["viewType"] == "basic") { echo'style="margin-top:50px;"'; } ?>>
    <div class="top_fixed_wrap" <?php if(($member["mb_level"] == "3" || $member["mb_level"] == "4") && $_COOKIE["viewType"] == "basic") { echo'style="margin-top:50px;"'; } ?>>
      <div class="top_common_area">
        <div class="logo_wrap">
          <a href="<?=G5_URL?>" class="logo_title"><img src="<?=THEMA_URL?>/assets/img/hd_logo.png"></a>
        </div>
        <?php if($is_approved) { ?>
        <div class="search_wrap">
          <form name="tsearch" method="get" onsubmit="return tsearch_submit(this);" role="form" class="form">
            <img src="<?php echo THEMA_URL; ?>/assets/img/icon_search.png" >
            <input type="hidden" name="url" value="<?php echo (IS_YC) ? $at_href['isearch'] : $at_href['search'];?>">
            <input type="text" name="stx" class="ipt_search" value="<?php echo get_text($stx); ?>" id="search" placeholder="품목명/급여코드 검색" />
          </form>
        </div>
        <ul class="nav">
          <li><a href="/shop/list.php?ca_id=10">판매품목</a></li>
          <li><a href="/shop/list.php?ca_id=20">대여품목</a></li>
          <li><a href="/shop/list.php?ca_id=70">비급여품목</a></li>
        </ul>
        <?php } ?>
        <div class="top_right_area">
          <div class="link_area">
            <?php
            if( ($member["mb_level"] == "3" || $member["mb_level"] == "4" ) && $is_approved) {
              if($_COOKIE["viewType"] == "adm") {
                echo '<a href="#" class="modeBtn" data-type="basic">구매모드</a>';
              } else {
                echo '<a href="#" class="modeBtn" data-type="adm">급여안내모드</a>';
              }
            }
            ?>

            <?php if($is_member) { // 로그인 상태 ?>
              <?php if($member['admin'] || $is_samhwa_admin) { ?>
              <a href="<?php echo G5_ADMIN_URL;?>/shop_admin/samhwa_orderlist.php">관리</a>
              <?php } ?>
              <a href="<?php echo G5_BBS_URL; ?>/logout.php" class="btn_default">로그아웃</a>
            <?php } ?>
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

    <div class="at-body <?php if($is_main && !$is_member) echo 'is-index'; ?>">
      <?php if($is_member && $is_approved) { // 로그인 전에는 숨김 ?>
      <div class="mobile_menu_backdrop" style="display: none;"></div>
      <div class="side_menu_area">
        <div class="fixed_wrap">
          <div class="btn_close_side_menu">
            <i class="fa fa-times" aria-hidden="true"></i>
          </div>
        </div>
        <div class="scrollable_wrap">
          <?php if($member['mb_level'] >= 9) { ?>
          <a href="/shop/release_orderlist.php" class="btn_orderlist">관리자 주문내역 관리</a>
          <?php } ?>
          <div class="user_info_area">
            <a href="<?=$at_href['edit'];?>" class="btn_small btn_edit">정보수정</a>
            <div class="user_name"><?=$member['mb_entNm'] ?: $member['mb_name']?></div>
            <div class="grade_info">
              <?php if($member['mb_type'] === 'normal') { ?>
                <select id="sel_pen_ent">
                  <?php
                  if(!$pen_ents) {
                    echo '<option value="">연결된 사업소가 없습니다.</option>';
                  }

                  $ss_ent_mb_id = get_session('ss_ent_mb_id');
                  if(count($pen_ents) > 0 && !$ss_ent_mb_id) {
                    // 연결된 사업소가 1개 이상인데 선택된 사업소가 없다면 강제로 첫번째 사업소 연결
                    goto_url(G5_SHOP_URL."/connect_ent.php?ent_mb_id={$pen_ents[0]['ent_mb_id']}");
                  }

                  foreach($pen_ents as $pen_ent) {
                    $ent_mb = get_member($pen_ent['ent_mb_id']);
                    echo "<option value=\"{$ent_mb['mb_id']}\" ".get_selected($ss_ent_mb_id, $ent_mb['mb_id']).">{$ent_mb['mb_name']}</option>";
                  }
                  ?>
                </select>
              <?php } else if($show_partner_menu) { ?>
              <div class="btn_small">
                <?php
                if($member['mb_level'] == 3) echo '사업소';
                else if($member['mb_level'] == 4) echo '우수사업소';
                else if($member['mb_level'] >= 9) echo '관리자';
                if($member['mb_type'] == 'partner') echo '파트너';
                ?>
              </div>
              <?php } ?>
              <?php if($member['mb_grade'] > 0) { ?>
              <div class="btn_small primary">
                <?php echo "{$default['de_it_grade' . $member['mb_grade'] . '_name']} ({$default['de_it_grade' . $member['mb_grade'] . '_discount']}%적립)"; ?>
              </div>
              <?php } ?>
            </div>
            <?php if ($member['mb_type'] !== 'normal') { ?>
              <div class="point_info flex-justify">
                <?php if($member['mb_point'] > 0) { ?>
                <div class="point">
                  포인트 : <?=number_format($member['mb_point']);?>원
                  <a href="<?=$at_href['point']?>" target="_blank" class="btn_small win_point"><i class="fa fa-list" aria-hidden="true"></i></a>
                </div>
                <?php } ?>
                <?php if($cp_count > 0) { ?>
                <div class="coupon">
                  쿠폰
                  <a href="<?=$at_href['coupon']?>" target="_blank" class="btn_small win_point"><?=$cp_count?></a>
                </div>
                <?php } ?>
              </div>
              <?php if($manager = get_member($member['mb_manager'])) { ?>
              <div class="manager_info">
                <!-- 이로움 관리 담당자 : <?="{$manager['mb_name']} ({$manager['mb_hp']})"?> -->
                시스템문의 : 02-830-1301 (월~금 09:00~18:00)
              </div>
              <?php } ?>
              <?php if($show_partner_menu) { ?>
              <div class="balance_info flex-justify">
                <div class="balance_title">신용거래 (<?php echo date('n');?>월)</div>
                <div class="balance"><?=number_format($balance)?>원</div>
              </div>
              <div class="order_info flex-justify">
                <div class="order">이번달 <?=number_format($order_count)?>건</div>
                <a href="<?php if($member['mb_type'] == 'partner') echo '/shop/partner_ledger_list.php'; else echo '/shop/my_ledger_list.php'; ?>" class="btn_small">거래처 원장</a>
              </div>
              <?php } ?>
              <?php if($event_count) { ?>
              <a class="event_noti" href="/bbs/board.php?bo_table=event">
                진행중인 이벤트
                <span class="value"><?=$event_count?>건</span>
                <i class="fa fa-angle-right" aria-hidden="true"></i>
              </a>
              <?php } ?>
            <?php } ?>
          </div>

          <?php if ($member['mb_type'] === 'normal') { ?>
            <div class="notice_area">
              <div class="title">
                <a href="/bbs/board.php?bo_table=notice_user">공지사항</a>
              </div>
              <?php  echo latest('list_main', 'notice_user', 5, 25); ?>
            </div>
          <?php } else { ?>
            <div class="notice_area">
              <div class="title">
                <a href="/bbs/board.php?bo_table=notice">공지사항</a>
              </div>
              <?php  echo latest('list_main', 'notice', 5, 25); ?>
            </div>
          <?php } ?>

          <div class="catalog_area">
            <a href="/thema/eroumcare/assets/eroum_catalog_2021_2_2.pdf" class="catalog">
              <img src="<?php echo THEMA_URL; ?>/assets/img/icon_catalog.png">
              이달의 카달로그
              <div class="btn_small">다운로드</div>
            </a>
          </div>

          <?php
          if($member['mb_type'] == 'partner') {
            if($show_partner_menu) {
          ?>
          <div class="side_nav_area">
            <div class="div_title">파트너</div>
            <ul>
              <li>
                <a href="/shop/partner_orderinquiry_list.php">
                  주문내역
                  <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
              </li>
              <li>
                <a href="/shop/partner_ledger_list.php">
                  거래처원장
                  <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
              </li>
            </ul>
          </div>
          <?php
            }
          } else if($member['mb_type'] === 'normal') {
            if(get_session('ss_pen_id')) { // 연결된 사업소가 있는 경우
          ?>
          <div class="side_nav_area">
            <div class="div_title">수급자</div>
            <ul>
              <li>
                <a href="/shop/recipient_cart.php">
                  공급제품 보관함
                  <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
              </li>
            </ul>
          </div>
          <?php
            }
          } else {
          ?>
          <div class="side_nav_area">
            <div class="div_title">주문관리</div>
            <ul>
              <li>
                <a href="/shop/list.php?ca_id=10">
                  전체상품 보기
                  <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
              </li>
              <li>
                <a href="/shop/orderinquiry.php">
                  주문/배송 내역
                  <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
              </li>
              <li>
                <a href="/shop/cart.php">
                  장바구니
                  <?php if (get_boxcart_datas_count() > 0) { ?>
                  <span class="value">상품 (<?php echo get_boxcart_datas_count(); ?>)</span>
                  <?php } ?>
                  <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
              </li>
            </ul>
            <div class="div_title">운영관리</div>
            <ul>
              <li>
                <a href="/shop/claim_manage.php">
                  청구내역
                  <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
              </li>
              <li>
                <a href="/shop/electronic_manage.php">
                  전자문서관리
                  <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
              </li>
              <li>
                <a href="/shop/my_recipient_list.php">
                  수급자관리
                  <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <?php if($noti_count = get_recipient_noti_count() > 0) { ?>
                <a class="noti_pen" href="/shop/my_recipient_noti.php">
                  수급자 알림이 있습니다.
                  <span class="value"><?=$noti_count?>건</span>
                </a>
                <?php } ?>
                <?php if($pen_links = get_recipient_links($member['mb_id'])) { ?>
                <a class="noti_pen link" href="/shop/my_recipient_list.php">
                  ‘<?=$pen_links[0]['rl_pen_name']?>’ <?php $pen_links_count = count($pen_links); if($pen_links_count > 1) { echo '외 '.($pen_links_count - 1).'명 '; } ?>수급자 추천이 있습니다.
                </a>
                <?php } ?>
              </li>
              <li>
                <a href="/shop/sales_Inventory.php">
                  보유재고관리
                  <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
              </li>
            </ul>
            <div class="div_title">기타/편의</div>
            <ul class="etc">
              <?php if ($member['mb_type'] !== 'normal') { ?>
              <li>
                <a href="/shop/my_data_upload.php">
                  과거공단자료 업로드
                </a>
              </li>
              <?php } ?>
              <li>
                <a href="/bbs/qalist.php">
                  고객센터(1:1문의)
                </a>
              </li>
              <li>
                <a href="/bbs/board.php?bo_table=faq">
                  자주하는 질문
                </a>
              </li>
              <li>
                <a href="/bbs/board.php?bo_table=proposal">
                  제안하기
                </a>
              </li>
              <li style="display: none;">
                <a href="/bbs/board.php?bo_table=lab">
                  이로움 연구소
                </a>
              </li>
            </ul>
          </div>
          <?php } ?>

          <?php if ($member['mb_type'] !== 'normal') { ?>
          <div class="btn_info_area">
            <a href="/bbs/board.php?bo_table=notice&wr_id=30">
              <img src="<?=THEMA_URL?>/assets/img/btn_businesshour.png" alt="이로움 주문마감 안내 확인" />
            </a>
            <a href="/bbs/board.php?bo_table=faq&wr_id=6" >
              <img src="<?=THEMA_URL?>/assets/img/btn_installinfo.png" alt="안전손잡이 설치 안내" />
            </a>
          </div>

          <div class="account_info_area">
            <a href="<?=THEMA_URL?>/assets/img/eroum_account.jpg" target="_blank">
              <img src="<?=THEMA_URL?>/assets/img/icon_account.png">
              통장사본
              <div class="btn_small">다운로드</div>
            </a>
            <a href="<?=THEMA_URL?>/assets/티에이치케이컴퍼니_사업자등록증.pdf" target="_blank">
              <img src="<?=THEMA_URL?>/assets/img/icon_cert.png">
              사업자등록증
              <div class="btn_small">다운로드</div>
            </a>
          </div>
          <?php } ?>

          <div class="call_info_area">
            <div class="title">이로움 고객만족센터</div>
            <div class="info">
              <img src="<?=THEMA_URL?>/assets/img/mainCallIcon.png">
              <div class="call">
                <p>주문안내 : <span>032-562-6608</span></p>
                <p>시스템안내 : <span>02-830-1301~2</span></p>
              </div>
            </div>
            <ul>
              <li>
                <div>· 운영시간</div>
                <div>월~금 09:00~18:00 (점심시간 12시~13시)</div>
              </li>
              <li>
                <div>· Email</div>
                <div>ceyoon2066@thkc.co.kr</div>
              </li>
              <li>
                <div>· Fax</div>
                <div>02-830-1308</div>
              </li>
            </ul>
          </div>
          <div class="btn_logout_mo">
          	<a href="<?php echo G5_BBS_URL; ?>/logout.php" class="btn_default">로그아웃</a>
          </div>
        </div>
      </div>
      <?php } ?>
      <?php if($col_name) { ?>
      <div class="at-container">
        <?php if($col_name == "two") { ?>
          <div class="row at-row">
            <div class="col-md-<?php echo $col_content;?><?php echo ($at_set['side']) ? ' pull-right' : '';?> at-col at-main">    
        <?php } else { ?>
          <div class="at-content">
            <?php
            $tutorials = get_tutorials();
            if (($member['mb_id'] && $member['mb_type'] === 'default' && $tutorials && $tutorials['completed_count'] < 4) || $open_tutorial_popup) {
              $tutorial_percent = round($tutorials['completed_count'] / 4 * 100);
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
                <?php 
                $t_recipient_order_idx = array_search('recipient_order', array_column($tutorials['step'], 't_type'));
                $t_recipient_order_class = $t_recipient_order_idx !== false ? ($tutorials['step'][$t_recipient_order_idx]['t_state'] ? 'complete' : 'active') : '';
                ?>
                <li class="area <?php echo $t_recipient_order_class; ?>">
                  <a href='<?php echo G5_SHOP_URL; ?>/tutorial_order.php'>
                    수급자 주문체험
                  </a>
                </li>
                <li class="next">></li>
                <?php 
                $t_document_idx = array_search('document', array_column($tutorials['step'], 't_type'));
                $t_document_class = $t_document_idx !== false ? ($tutorials['step'][$t_document_idx]['t_state'] ? 'complete' : 'active') : '';
                ?>
                <li class="area <?php echo $t_document_class; ?>">
                  <a href='<?php echo G5_SHOP_URL; ?>/electronic_manage.php'>
                    전자문서 확인
                  </a>
                </li>
                <li class="next">></li>
                <?php 
                $t_claim_idx = array_search('claim', array_column($tutorials['step'], 't_type'));
                $t_claim_class = $t_claim_idx !== false ? ($tutorials['step'][$t_claim_idx]['t_state'] ? 'complete' : 'active') : '';
                ?>
                <li class="area <?php echo $t_claim_class; ?>">
                  <a href='<?php echo G5_SHOP_URL; ?>/claim_manage.php'>
                    청구내역 확인
                  </a>
                </li>
              </ul>
            </div>
            <?php
            }
            ?>
        <?php } ?>
      <?php } ?>
