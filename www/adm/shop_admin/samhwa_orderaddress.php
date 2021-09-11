<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$mb_id = $_GET['mb_id'];

if(!$mb_id)
  alert_close('유효하지않은 요청입니다.');

$sql_common = " from {$g5['g5_shop_order_address_table']} where mb_id = '{$mb_id}' ";

$sql = " select count(ad_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "
  select *
  $sql_common
  order by ad_default desc, ad_id desc
  limit $from_record, $rows
";

$result = sql_query($sql);

if(!sql_num_rows($result))
  alert_close('배송지 목록 자료가 없습니다.');

  $list = array();

$sep = chr(30);
for($i=0; $row=sql_fetch_array($result); $i++) {
  $list[$i] = $row;
  $list[$i]['addr'] = $row['ad_name'].$sep.$row['ad_tel'].$sep.$row['ad_hp'].$sep.$row['ad_zip1'].$sep.$row['ad_zip2'].$sep.$row['ad_addr1'].$sep.$row['ad_addr2'].$sep.$row['ad_addr3'].$sep.$row['ad_jibeon'].$sep.$row['ad_subject'];

  $list[$i]['addr'] = get_text($list[$i]['addr']);
  $list[$i]['ad_name'] = get_text($list[$i]['ad_name']);
  $list[$i]['ad_subject'] = get_text($list[$i]['ad_subject']);
  $list[$i]['print_addr'] = print_address($row['ad_addr1'], $row['ad_addr2'], $row['ad_addr3'], $row['ad_jibeon']);
}

$write_pages = G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'];
$list_page = $_SERVER['SCRIPT_NAME']."?mb_id={$mb_id}&amp;page=";

$pid = ($pid) ? $pid : ''; // Page ID
$at = apms_page_thema($pid);
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

$skin_row = array();
$skin_row = apms_rows('order_'.MOBILE_.'skin, order_'.MOBILE_.'set');
$skin_name = $skin_row['order_'.MOBILE_.'skin'];
$order_skin_path = G5_SKIN_PATH.'/apms/order/'.$skin_name;
$order_skin_url = G5_SKIN_URL.'/apms/order/'.$skin_name;

// 스킨 체크
list($order_skin_path, $order_skin_url) = apms_skin_thema('shop/order', $order_skin_path, $order_skin_url); 

// 스킨설정
$wset = array();
if($skin_row['order_'.MOBILE_.'set']) {
  $wset = apms_unpack($skin_row['order_'.MOBILE_.'set']);
}

// 데모
if($is_demo) {
  @include ($demo_setup_file);
}

// 설정값 불러오기
$is_address_sub = true;
@include_once($order_skin_path.'/config.skin.php');

$g5['title'] = '배송지 목록';

if($is_address_sub) {
  include_once(G5_PATH.'/head.sub.php');
  if(!USE_G5_THEME) @include_once(THEMA_PATH.'/head.sub.php');
} else {
  include_once('./_head.php');
}

$skin_path = $order_skin_path;
$skin_url = $order_skin_url;

add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

// 목록헤드
if(isset($wset['ahead']) && $wset['ahead']) {
  add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/head/'.$wset['ahead'].'.css" media="screen">', 0);
  $head_class = 'list-head';
} else {
  $head_class = (isset($wset['acolor']) && $wset['acolor']) ? 'tr-head border-'.$wset['acolor'] : 'tr-head border-black';
}
?>

<div id="sod_addr">
  <div class="table-responsive">
    <table class="div-table table">
      <tbody>
        <tr class="<?php echo $head_class;?>">
          <th scope="col"><span>배송지명</span></th>
          <th scope="col"><span>이름</span></th>
          <th scope="col"><span>전화번호</span></th>
          <th scope="col"><span>주소</span></th>
          <th scope="col"><span class="last">선택</span></th>
        </tr>
        <?php for($i=0; $i < count($list); $i++) { ?>
        <tr<?php echo ($i == 0) ? ' class="tr-line"' : '';?>>
          <td class="text-center"><?php echo $list[$i]['ad_subject']; ?></td>
          <td class="text-center"><?php echo $list[$i]['ad_name']; ?></td>
          <td class="text-center"><?php echo $list[$i]['ad_tel']; ?><br><?php echo $list[$i]['ad_hp']; ?></td>
          <td><?php echo $list[$i]['print_addr']; ?></td>
          <td class="text-center">
            <input type="hidden" value="<?php echo $list[$i]['addr']; ?>">
            <button type="button" class="sel_address btn btn-color btn-xs" title="선택"><i class="fa fa-check fa-lg"></i><span class="sound_only">선택</span></button>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <div style="margin:0px 20px 20px;">
    <div class="pull-left">
      <button type="button" onclick="self.close();" class="btn btn-black btn-sm">닫기</button>
    </div>

    <?php if($total_count > 0) { ?>
      <div class="pull-right">
        <ul class="pagination pagination-sm" style="margin-top:0; padding-top:0;">
          <?php echo apms_paging($write_pages, $page, $total_page, $list_page); ?>
        </ul>
      </div>
    <?php } ?>

    <div class="clearfix"></div>
  </div>
</div>

<script>
$(function() {
  $(".sel_address").on("click", function() {
    var addr = $(this).siblings("input").val().split(String.fromCharCode(30));

    var f = window.opener.frmsamhwaorderdeliveryform;
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

    window.close();
  });
});
</script>


<?php
if($is_address_sub) {
  if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
  include_once(G5_PATH.'/tail.sub.php');
} else {
  include_once('./_tail.php');
}
?>
