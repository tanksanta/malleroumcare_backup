<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  alert('파트너 회원만 접근가능합니다.');

$g5['title'] = "파트너 주문상세";
include_once("./_head.php");
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$manager_mb_id = get_session('ss_manager_mb_id');

$od_id = get_search_string($_GET['od_id']);
$od = sql_fetch("
  SELECT
    o.*,
    m.mb_temp,
    m.mb_name,
    mb_entNm
  FROM
    {$g5['g5_shop_order_table']} o
  LEFT JOIN
    {$g5['member_table']} m ON o.mb_id = m.mb_id
  WHERE
    od_id = '{$od_id}'
");
if(!$od['od_id'])
  alert('존재하지 않는 주문입니다.');

// 설치결과보고서
$report = sql_fetch("
  SELECT *
  FROM
    partner_install_report
  WHERE
    od_id = '$od_id' AND
    mb_id = '{$member['mb_id']}'
");
$report['issue'] = [];
if($report['ir_is_issue_1'])
  $report['issue'][] = '상품변경';
if($report['ir_is_issue_2'])
  $report['issue'][] = '상품추가';
if($report['ir_is_issue_3'])
  $report['issue'][] = '미설치';
$photo_result = sql_query("
  SELECT * FROM partner_install_photo
  WHERE od_id = '$od_id' AND mb_id = '{$member['mb_id']}' AND img_type = '설치사진'
  ORDER BY ip_id ASC
", true);
$report['photo'] = [];
while($photo = sql_fetch_array($photo_result)) {
  $report['photo'][] = $photo;
}

$photo_result2 = sql_query("
  SELECT * FROM partner_install_photo
  WHERE od_id = '$od_id' AND mb_id = '{$member['mb_id']}' AND img_type = '실물바코드사진'
  ORDER BY ip_id ASC
", true);
$report['photo2'] = [];
while($photo = sql_fetch_array($photo_result2)) {
  $report['photo2'][] = $photo;
}

$photo_result3 = sql_query("
  SELECT * FROM partner_install_photo
  WHERE od_id = '$od_id' AND mb_id = '{$member['mb_id']}' AND img_type = '설치ㆍ회수ㆍ소독확인서'
  ORDER BY ip_id ASC
", true);
$report['photo3'] = [];
while($photo = sql_fetch_array($photo_result3)) {
  $report['photo3'][] = $photo;
}

$photo_result4 = sql_query("
  SELECT * FROM partner_install_photo
  WHERE od_id = '$od_id' AND mb_id = '{$member['mb_id']}' AND img_type = '추가사진'
  ORDER BY ip_id ASC
", true);
$report['photo4'] = [];
while($photo = sql_fetch_array($photo_result4)) {
  $report['photo4'][] = $photo;
}

//주문 기록 
$sql = "SELECT * FROM g5_shop_order_admin_log WHERE od_id = '{$od_id}' ORDER BY ol_no DESC";
$result = sql_query($sql);
$logs = array();
while($row = sql_fetch_array($result)) { $logs[] = $row; } 

// 임시회원의경우 mb_entNm 대신 mb_name 출력
if($od['mb_temp']) {
  $od['mb_entNm'] = $od['mb_name'];
}

$cart_result = sql_query("
  SELECT
    c.*,
    i.it_img1
  FROM
    {$g5['g5_shop_cart_table']} c
  LEFT JOIN
    {$g5['g5_shop_item_table']} i ON c.it_id = i.it_id
  WHERE
    od_id = '{$od_id}' AND
    ct_direct_delivery_partner = '{$member['mb_id']}' AND
    ct_status IN('출고준비', '배송', '완료', '취소', '주문무효')
  ORDER BY
    ct_id ASC
");

$total_price_p = 0; // 총 공급가 합계
$total_price_s = 0; // 총 부가세 합계
$count_delivery_inserted = 0; // 배송비 정보 입력된 숫자

$carts = [];
$has_install = false; // 설치 상품 있는지 여부
while($row = sql_fetch_array($cart_result)) {
  if($row['ct_delivery_num'])
    $count_delivery_inserted++;

  $row['it_name'] .= ($row['ct_option'] != $row['it_name'] ? " ({$row['ct_option']})" : '');

  $ct_direct_delivery_text = '배송';
  if($row['ct_is_direct_delivery'] == '2') {
    $ct_direct_delivery_text = '설치';
    $has_install = true;
  }
  $row['ct_direct_delivery'] = $ct_direct_delivery_text;

  $price = intval($row['ct_direct_delivery_price']) * intval($row['ct_qty']);
  // 공급가액
  $price_p = @round(($price ?: 0) / 1.1);
  // 부가세
  $price_s = @round(($price ?: 0) / 1.1 / 10);

  $total_price_p += $price_p;
  $total_price_s += $price_s;

  $row['price_p'] = $price_p;
  $row['price_s'] = $price_s;

  $carts[] = $row;
}

if(!$carts)
  alert('존재하지 않는 주문입니다.');

function trans_ct_status_text($ct_status_text) {
  switch ($ct_status_text) {
    case '보유재고등록': $ct_status_text = "보유재고등록"; break;
    case '재고소진': $ct_status_text = "재고소진"; break;
    case '주문무효': $ct_status_text = "주문무효"; break;
    case '취소': $ct_status_text = "주문취소"; break;
    case '주문': $ct_status_text = "상품주문"; break;
    case '입금': $ct_status_text = "입금완료"; break;
    case '준비': $ct_status_text = "상품준비"; break;
    case '출고준비': $ct_status_text = "출고준비"; break;
    case '배송': $ct_status_text = "출고완료"; break;
    case '완료': $ct_status_text = "배송완료"; break;
  }

  return $ct_status_text;
}

// 담당자
$manager_result = sql_query("
  select * from g5_member
  where mb_type = 'manager' AND mb_manager = '{$member['mb_id']}'
");
$managers = [];
while($manager = sql_fetch_array($manager_result)) {
  $managers[] = $manager;
}

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/partner_order.css?v=1128">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/magnific-popup.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery.wheelzoom.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery.magnific-popup.js"></script>', 0);
?>

<section id="partner-order" class="wrap">
  <h2 class="title row no-gutter">
    주문상세
  </h2>

  <section class="row no-gutter justify-space-between container">
    <div class="left-wrap">
      <?php if($report['photo'] || $report['photo2'] || $report['photo3'] || $report['photo4']) { ?>
      <div class="install-report">
        <div class="top-wrap row no-gutter justify-space-between">
          <span>설치결과보고서</span>
          <button type="button" class="report-btn btn_install_report">결과보고서 작성</button>
        </div>
        <?php if($report) { ?>
        <div class="mid-wrap">
          <?php if($report['ir_file_url']) { ?>
          <a href="<?=G5_SHOP_URL."/eform/install_report_download.php?od_id={$od_id}"?>" class="btn_ir_download">결과보고서
            다운로드</a>
          <?php } ?>
        </div>
        <?php } ?>

        <?php if($report['photo']) {?>
        <div class="row report-img-wrap">
          <?php if($report['ir_cert_url']) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>" target="_blank" class="view_image">
                <img src="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>"
                  onerror="this.src='/shop/img/no_image.gif';">
              </a>
            </div>
          </div>
          <?php } ?>
          <?php foreach($report['photo'] as $photo) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; php?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
        </div>
        <div class="col title-wrap">
          설치 사진(필수)
        </div>
        <?php } ?>

        <?php if($report['photo2']) {?>
        <div class="row report-img-wrap">
          <?php if($report['ir_cert_url']) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>" target="_blank" class="view_image">
                <img src="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
          <?php foreach($report['photo2'] as $photo) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; php?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
        </div>
        <div class="col title-wrap">
          실물 바코드 사진(필수)
        </div>
        <?php } ?>

        <?php if($report['photo3']) {?>
        <div class="row report-img-wrap">
          <?php if($report['ir_cert_url']) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>" target="_blank" class="view_image">
                <img src="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
          <?php foreach($report['photo3'] as $photo) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; php?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
        </div>
        <div class="col title-wrap">
          설치ㆍ회수ㆍ소독확인서 사진(필수)
        </div>
        <?php } ?>

        <?php if($report['photo4']) {?>
        <div class="row report-img-wrap">
          <?php if($report['ir_cert_url']) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>" target="_blank" class="view_image">
                <img src="<?=G5_DATA_URL.'/partner/img/'.$report['ir_cert_url']?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
          <?php foreach($report['photo4'] as $photo) { ?>
          <div class="col">
            <div class="report-img">
              <a href="<?=G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']?>" target="_blank" class="view_image">
                <img
                  src="<?php if (str_ends_with($photo['ip_photo_url'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo G5_DATA_URL.'/partner/img/'.$photo['ip_photo_url']; php?>"
                  onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
              </a>
            </div>
          </div>
          <?php } ?>
        </div>
        <div class="col title-wrap">
          추가사진(선택) - 상품변경 혹은 특이사항 발생 시
        </div>
        <?php } ?>
      </div>
      <?php } ?>

      <?php if($report['issue']) { ?>
      <div class="col issue-wrap">
        <div class="col title-wrap">
          이슈사항
        </div>
        <div class="issue-select">
          이슈사항 (
          <?php echo implode(' /', $report['issue']); ?>
          )
        </div>
        <div class="issue">
          <p>
            <?=nl2br($report['ir_issue'])?>
          </p>
        </div>
      </div>
      <?php } ?>

      <form id="form_ct_status">
        <div class="top row no-gutter justify-space-between align-center">
          <div class="col title">
            <?=$od['mb_entNm']?> (주문일시: <?=date('Y-m-d H:i:s', strtotime($od['od_time']))?>)
          </div>
          <div class="col">
            <select name="ct_status" class="order-status-select">
              <option value="출고준비">출고준비</option>
              <option value="배송" selected>출고완료</option>
              <option value="취소">주문취소</option>
            </select>
            <button type="button" id="btn_ct_status" class="order-status-btn">저장</button>
          </div>
        </div>

        <div class="item-list">
          <ul>
            <?php foreach($carts as $cart) { ?>
            <li class="item row align-center">
              <div class="col checkbox-wrap text-center">
                <input type="checkbox" name="ct_id[]" value="<?=$cart['ct_id']?>" />
              </div>
              <div class="col item-img-wrap">
                <div class="item-img">
                  <img src="/data/item/<?=$cart["it_img1"]?>"
                    onerror="this.src='<? if (strpos($photo['ip_photo_name'], '.pdf')) echo '/shop/img/icon_pdf.png'; else echo '/shop/img/no_image.gif'; ?>';">
                </div>
              </div>
              <div class="col item-info-wrap">
                <div class="title full-width">
                  <?=$cart['it_name']?>
                </div>
                <div class="price full-width text-grey">
                  금액 : 공급가(<?=number_format($cart['price_p'])?>원),
                  부가세(<?=number_format($cart['price_s'])?>원)
                </div>
                <div class="qty full-width text-grey">
                  수량 : <?=$cart['ct_qty']?>개 / 위탁 : <?=$cart['ct_direct_delivery']?>
                </div>
              </div>
              <div class="col delivery-wrap text-center">
                <?=trans_ct_status_text($cart['ct_status'])?>
              </div>
              <div class="col barcode-wrap text-center">
                <a href="javascript:void(0);" class="barcode-btn btn_barcode_info" data-id="<?=$cart['ct_id']?>">
                  <img src="/skin/apms/order/new_basic/image/icon_02.png" alt="">
                  바코드
                </a>
              </div>
            </li>
            <?php
            }
            ?>
          </ul>
        </div>
      </form>

      <a href="partner_orderinquiry_edit.php?od_id=<?=$od_id?>" class="btn_od_edit">주문상품 변경</a>

      <div class="row no-gutter">
        <div class="col title">배송정보</div>
      </div>
      <div class="row no-gutter delivery-info-wrap">
        <ul>
          <li>
            <div class="row">
              <div class="col left">수령인</div>
              <div class="col right"><?=get_text($od['od_b_name'])?></div>
            </div>
          </li>
          <li>
            <div class="row">
              <div class="col left">연락처</div>
              <div class="col right">연락처 <?=get_text($od['od_b_tel']) ?: '-'?>, 휴대폰
                <?=get_text($od['od_b_hp']) ?: '-'?></div>
            </div>
          </li>
          <li>
            <div class="row">
              <div class="col left">주소</div>
              <div class="col right">
                <?=get_text(sprintf("(%s%s)", $od['od_b_zip1'], $od['od_b_zip2']).' '.print_address($od['od_b_addr1'], $od['od_b_addr2'], $od['od_b_addr3'], $od['od_b_addr_jibeon']))?>
              </div>
            </div>
          </li>
          <li>
            <div class="row">
              <div class="col left">전달메시지</div>
              <div class="col right">
                <?php
                if($od['od_memo'])
                  echo $od['od_memo'];
                else 
                  echo '없음';
                ?>
              </div>
            </div>
          </li>
        </ul>
      </div>

      <div class="block_list">
        <div class="block">
          <div class="row no-gutter">
            <div class="col title" style="margin-top:20px;">기록</div>
          </div>
          <div class="row no-gutter delivery-info-wrap">
            <ul>
              <?php
              foreach($logs as $log) {
                $log_mb = get_member($log['mb_id']);
                if ($log_mb['mb_id'] == $member['mb_id']) { $manager = $member['mb_name']; }
                else if ($log_mb['mb_type'] != 'manager') { $manager = '이로움 관리자'; }
                else { $manager = $member['mb_name'] . '>[직원]' . $log_mb['mb_name']; }

                echo ('
                  <li class="log"><div class="row">
                    <div class="log_datetime">'.$log['ol_datetime'] . '</div>
                    <div>(' . $manager . ') ' . $log['ol_content'] . '</div>
                  </div></li>
                ');
              }

              if (!count($logs)) { echo '기록이 없습니다.'; }
            ?>
            </ul>
          </div>
        </div>
      </div>

      <div class="block">
        <div class="row no-gutter">
          <div class="col title" style="margin-top:20px;">바코드 기록</div>
        </div>
        <div class="row no-gutter delivery-info-wrap">
          <ul>
            <?php
              $logs = get_barcode_log($od['od_id']);
              foreach($logs as $log) {
                  $log_mb = get_member($log['mb_id']);
                  echo ('
                    <li class="log"><div class="row">
                      <div class="log_datetime">'.$log['b_date'] . '</div>
                      <div>(' . $log_mb['mb_name'] . ' 매니저) ' . $log['b_content'] . '</div>
                    </div></li>
                  ');
              }

              if (!count($logs)) { echo '기록이 없습니다.'; }
            ?>
          </ul>
        </div>
      </div>

      <div class="block">
        <div class="row no-gutter">
          <div class="col title" style="margin-top:20px;">배송 기록</div>
        </div>
        <div class="row no-gutter delivery-info-wrap">
          <ul class="block-box">
            <?php
              $logs = get_delivery_log($od['od_id']);
              $last_log = [];

              foreach($logs as $log) {
                $log_mb = get_member($log['mb_id']);

                //아이템 검색
                $sql_ct = "select * from g5_shop_cart where ct_id = '".$log['ct_id']."'";
                $result_ct = sql_fetch($sql_ct);

                //아이템 이름
                $it_name = $result_ct['it_name'];
                if(str_replace(' ', '', $result_ct['ct_option']) != str_replace(' ', '', $result_ct['it_name'])) { $it_name .="(".$result_ct['ct_option'].")"; }

                //택배사
                $delivery_company="";
                foreach($delivery_companys as $data){ 
                  if($log["ct_delivery_company"] == $data["val"] ) { $delivery_company = "(".$data["name"].")"; }
                }

                //직배송
                $direct_delivery = "";
                if($log["ct_is_direct_delivery"] == "1") { $direct_delivery = "[위탁:배송]"; }
                else if($log["ct_is_direct_delivery"] == "2") { $direct_delivery = "[위탁:설치]"; }

                //합포
                $combine="";
                if($log["ct_combine_ct_id"]) {

                  //합포 검색
                  $sql_ct_p = "select * from g5_shop_cart where ct_id = '".$log['ct_combine_ct_id']."'";
                  $result_ct_p = sql_fetch($sql_ct_p);

                  //합포 아이템 이름
                  $it_name_p = $result_ct_p['it_name'];
                  if($result_ct_p['ct_option']){ $it_name_p .= "(".$result_ct_p['ct_option'].")"; }

                  $combine="합포 - ".$it_name_p."";
                }

                // 비교할 이전 로그가 없으면 자체 정보로 비교
                if(!$log['was_combined'] && $log['ct_combine_ct_id']) {

                  // 합포적용
                  echo ('
                    <li class="log"><div class="row">
                      <div class="log_datetime">' . $log['d_date'] . '</div>
                      <div>(' . $log_mb['mb_name'] . ' 매니저) 합포정보 입력 : ' . $it_name . ' 상품을 ' . $it_name_p . ' 상품에 합포적용했습니다.</div>
                    </div></li>
                  ');

                } else if($log['was_combined'] && !$log['ct_combine_ct_id']) {

                  // 합포해지
                  echo ('
                    <li class="log"><div class="row">
                      <div class="log_datetime">' . $log['d_date'] . '</div>
                      <div>(' . $log_mb['mb_name'] . ' 매니저) 합포정보 입력 : ' . $it_name . ' 상품을 합포해지했습니다.</div>
                    </div></li>
                  ');

                }
        
                if(!$log['was_direct_delivery'] && $log['ct_is_direct_delivery']) {
                  // 위탁적용
                  $direct_delivery_type = '';

                  if($log["ct_is_direct_delivery"] == "1") { $direct_delivery_type = '배송'; }
                  else if($log["ct_is_direct_delivery"] == "2") { $direct_delivery_type = '설치'; }

                  echo ('
                    <li class="log"><div class="row">
                      <div class="log_datetime">' . $log['d_date'] . '</div>
                      <div>(' . $log_mb['mb_name'] . ' 매니저) 위탁정보 입력 : ' . $it_name . ' 상품을 위탁 적용했습니다. (' . $direct_delivery_type . '/' . $log['ct_direct_delivery_partner'] . '/1개당 ' . ($log['ct_direct_delivery_price']) . '원)</div>
                    </div></li>
                  ');
            
                } else if($log['was_direct_delivery'] && !$log['ct_is_direct_delivery']) {
                  // 위탁해지
                  echo ('
                    <li class="log"><div class="row">
                      <div class="log_datetime">' . $log['d_date'] . '</div>
                      <div>(' . $log_mb['mb_name'] . ' 매니저) 위탁정보 입력 : ' . $it_name . ' 상품을 위탁 해지했습니다.</div>
                    </div></li>
                  ');
                }

                if($log['ct_combine_ct_id']) {
                  echo ('
                    <li class="log"><div class="row">
                      <div class="log_datetime">' . $log['d_date'] . '</div>
                      <div>(' . $log_mb['mb_name'] . ' 매니저) 배송정보 입력 : ' . $delivery_company . ' ' . $it_name . ' [' . $combine . '] ' . $direct_delivery . '</div>
                    </div></li>
                  ');
                } else {
                  echo ('
                    <li class="log"><div class="row">
                      <div class="log_datetime">' . $log['d_date'] . '</div>
                      <div>(' . $log_mb['mb_name'] . ' 매니저) 배송정보 입력 : ' . $delivery_company . ' ' . $it_name . ' 송장번호[' . $log['ct_delivery_num'] . '] ' . $direct_delivery . '</div>
                    </div></li>
                  ');
                }

                if ($log['set_warehouse']) { 
                  echo ('
                    <li class="log"><div class="row">
                      <div class="log_datetime">' . $log['d_date'] . '</div>
                      <div>(' . $log_mb['mb_name'] . ' 매니저) ' . $log['d_content'] . ' 저장</div>
                    </div></li>
                  ');
                }

                $last_log[$log['ct_id']] = $log;

              }

              if (!count($logs)) { echo '기록이 없습니다.'; }
            ?>
          </ul>
        </div>
      </div>

    </div>

    <div class="right-wrap">
      <div class="row no-gutter">
        <a href="partner_orderinquiry_excel.php?od_id=<?=$od_id?>" class="instructor-btn">작업지시서 다운로드</a>
      </div>
      <div class="delivery-status-title row no-gutter title justify-space-between">
        <div>담당자</div>
        <?php
        if($manager_mb_id) {
          $manager_txt = '미지정';
          if($od['od_partner_manager']) {
            $manager = get_member($od['od_partner_manager']);
            $manager_txt = '[직원] ' . $manager['mb_name'];
          }
          echo "<div style=\"font-size: 16px;\">{$manager_txt}</div>";
        } else {
        ?>
        <select class="sel_manager order-status-select" data-id="<?=$od_id?>" style="width: 150px;">
          <option value="">미지정</option>
          <?php foreach($managers as $manager) { ?>
          <option value="<?=$manager['mb_id']?>" <?=get_selected($od['od_partner_manager'], $manager['mb_id'])?>>[직원]
            <?=$manager['mb_name']?>
          </option>
          <?php } ?>
        </select>
        <?php } ?>
      </div>
      <div class="delivery-status-title row no-gutter title">
        배송정보
      </div>
      <div class="row no-gutter">
        <a href="javascript:void(0);" id="btn_delivery_info" class="delivery-status-info col full-width text-center">
          배송정보 (<?=$count_delivery_inserted?>/<?=count($carts)?>)
        </a>
      </div>
      <div class="delivery-info-list">
        <form id="form_delivery_date">
          <input type="hidden" name="od_id" value="<?=$od_id?>">
          <ul>
            <?php
            foreach($carts as $cart) {
            ?>
            <li class="delivery-info-item">
              <div class="info-title text-weight-bold">
                <?=$cart['it_name']?>
              </div>
              <div class="row">
                <div class="col left">출고 예정일</div>
                <div class="col right">
                  <input type="hidden" name="ct_id[]" value="<?=$cart['ct_id']?>">
                  <input type="text" class="datepicker" name="ct_direct_delivery_date_<?=$cart['ct_id']?>"
                    value="<?=$cart['ct_direct_delivery_date'] ? date('Y-m-d', strtotime($cart['ct_direct_delivery_date'])) : ''?>">
                  <select name="ct_direct_delivery_time_<?=$cart['ct_id']?>">
                    <?php
                    $ct_direct_delivery_time = $cart['ct_direct_delivery_date'] ? date('H', strtotime($cart['ct_direct_delivery_date'])) : '';
                    for($i = 0; $i < 24; $i++) {
                      $time = str_pad($i, 2, '0', STR_PAD_LEFT); 
                    ?>
                    <option value="<?=$time?>" <?=get_selected($ct_direct_delivery_time, $time)?>>
                      <?=$time?>시</option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col left">출고 완료일</div>
                <div class="col right"><?=$cart['ct_ex_date'] ?: '대기'?></div>
              </div>
              <?php
              if($cart['ct_delivery_num']) {
                $delivery_company_name = '';
                foreach($delivery_companys as $data) {
                  if($data['val'] == $cart['ct_delivery_company']) {
                    $delivery_company_name = $data['name'];
                    break;
                  }
                } 
              ?>
              <div class="row">
                <div class="col left">[<?=$delivery_company_name?>]</div>
                <div class="col right"><?=$cart['ct_delivery_num']?></div>
              </div>
              <?php } ?>
            </li>
            <?php } ?>
          </ul>
          <button type="button" id="btn_delivery_date" class="delivery-save-btn">출고예정일 저장</button>
        </form>
      </div>

      <div class="order-settle-title title row no-gutter">
        정산정보
      </div>
      <div class="order-settle-info">
        <ul>
          <li class="row no-gutter justify-space-between">
            <div class="col">공급가액</div>
            <div class="col"><?=number_format($total_price_p)?>원</div>
          </li>
          <li class="row no-gutter justify-space-between">
            <div class="col">부가세</div>
            <div class="col"><?=number_format($total_price_s)?>원</div>
          </li>
          <li class="row no-gutter justify-space-between">
            <div class="col">합계</div>
            <div class="col total"><?=number_format($total_price_p + $total_price_s)?>원</div>
          </li>
        </ul>
      </div>
    </div>
  </section>
</section>

<style>
#popup_box {
  position: fixed;
  width: 100vw;
  height: 100vh;
  left: 0;
  top: 0;
  z-index: 99999999;
  background-color: rgba(0, 0, 0, 0.6);
  display: table;
  table-layout: fixed;
  opacity: 0;
}

#popup_box>div {
  width: 100%;
  height: 100%;
  display: table-cell;
  vertical-align: middle;
}

#popup_box iframe {
  position: relative;
  width: 500px;
  height: 700px;
  border: 0;
  background-color: #FFF;
  left: 50%;
  margin-left: -250px;
}

@media (max-width : 750px) {
  #popup_box iframe {
    width: 100%;
    height: 100%;
    left: 0;
    margin-left: 0;
  }
}

.block-box {
  max-height: 300px;
  overflow-y: scroll;
}
</style>

<div id="popup_box">
  <div></div>
</div>

<script>
$(function() {
  $("#popup_box").hide();
  $("#popup_box").css("opacity", 1);

  // 출고예정일 datepicker
  $('.datepicker').datepicker({
    changeMonth: true,
    changeYear: true,
    dateFormat: "yy-mm-dd",
    showButtonPanel: true,
    yearRange: "c-99:c+99"
  });

  // 배송정보 버튼
  $('#btn_delivery_info').click(function(e) {
    e.preventDefault();
    $("body").addClass('modal-open');
    $("#popup_box > div").html('<iframe src="popup.partner_deliveryinfo.php?od_id=<?=$od_id?>">');
    $("#popup_box iframe").load(function() {
      $("#popup_box").show();
    });
  });

  // 설치결과보고서 작성 버튼
  $('.btn_install_report').click(function() {
    $("body").addClass('modal-open');
    $("#popup_box > div").html('<iframe src="popup.partner_installreport.php?od_id=<?=$od_id?>">');
    $("#popup_box iframe").load(function() {
      $("#popup_box").show();
    });
  });

  // 바코드 버튼
  $('.btn_barcode_info').click(function(e) {
    e.preventDefault();

    var ct_id = $(this).data('id');
    $("body").addClass('modal-open');
    $("#popup_box > div").html('<iframe src="popup.partner_barcodeinfo.php?ct_id=' + ct_id + '">');
    $("#popup_box iframe").load(function() {
      $("#popup_box").show();
    });
  });

  // 주문상태 변경
  $('#btn_ct_status').click(function() {
    $('#form_ct_status').submit();
  });
  $('#form_ct_status').on('submit', function(e) {
    e.preventDefault();

    // 주문상태 변경
    if ($('select[name="ct_status"]').val() == '취소' && !confirm(
        '주문취소 후 상태 변경은 불가능합니다. 취소하시겠습니까?')) {
      return false;
    }

    $.post('ajax.partner_ctstatus.php', $(this).serialize(), 'json')
      .done(function() {
        alert('변경이 완료되었습니다.');
        window.location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
  });

  // 출고예정일 변경
  $('#btn_delivery_date').click(function() {
    $('#form_delivery_date').submit();
  });
  $('#form_delivery_date').on('submit', function(e) {
    e.preventDefault();

    const send_data = {};
    const obj = $(this).serializeArray();
    send_data['od_id'] = obj[0].value;
    send_data['ct_id'] = obj[1].value;
    send_data['delivery_date'] = obj[2].value;
    send_data['delivery_datetime'] = obj[3].value + ":00";
    send_data['partner_manager_mb_id'] = $('.sel_manager').val();
    const send_data2 = $(this).serialize();
    $.post('schedule/ajax.schedule.php', send_data, 'json').done(function() {
      $.post('ajax.partner_deliverydate.php', send_data2, 'json')
        .done(function() {
          alert('변경이 완료되었습니다.');
          window.location.reload();
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
        });
    }).fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  $(document).on("DOMNodeInserted", '.mfp-content', function() {
    window.wheelzoom($('.mfp-img'));
  });
  $('.report-img-wrap').click(function() {
    window.wheelzoom($('.mfp-img'));
  });

  // 담당자 선택
  var loading_manager = false;
  $('.sel_manager').change(function() {
    if (loading_manager)
      return alert('로딩중입니다. 잠시후 다시 시도해주세요.');

    var od_id = $(this).data('id');
    var manager = $(this).val();
    var manager_name = $(this).find('option:selected').text();

    const send_data = {};
    const obj = $("#form_delivery_date").serializeArray();
    send_data['od_id'] = obj[0].value;
    send_data['ct_id'] = obj[1].value;
    send_data['delivery_date'] = obj[2].value;
    send_data['partner_manager_mb_id'] = manager;
    loading_manager = true;

    if (send_data['delivery_date']) {
      $.post('schedule/ajax.schedule.php', send_data, 'json').done(function() {
        $.post('ajax.partner_manager.php', {
            od_id: od_id,
            manager: manager
          }, 'json')
          .done(function() {
            alert(manager_name + ' 담당자로 변경되었습니다.');
          })
          .fail(function($xhr) {
            var data = $xhr.responseJSON;
            alert(data && data.message);
          })
          .always(function() {
            loading_manager = false;
          });
      }).fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      }).always(function() {
        loading_manager = false;
      });
    } else {
      $.post('ajax.partner_manager.php', {
          od_id: od_id,
          manager: manager
        }, 'json')
        .done(function() {
          alert(manager_name + ' 담당자로 변경되었습니다.');
        })
        .fail(function($xhr) {
          var data = $xhr.responseJSON;
          alert(data && data.message);
        })
        .always(function() {
          loading_manager = false;
        });
    }
  });
});
</script>

<script>
$(function() {
  $('.report-img-wrap').magnificPopup({
    delegate: 'a',
    type: 'image',
    image: {
      titleSrc: function(item) {

        var $div = $('<div>');

        // 원본크기
        var $btn_zoom_orig = $(
            '<button type="button" class="btn-bottom btn-zoom-orig">원본크기</button>')
          .click(function() {
            $btn_zoom_orig.hide();
            $btn_zoom_fit.show();

            $(item.img).css('max-width', 'unset');
            $(item.img).css('max-height', 'unset');
          });

        // 창맞추기
        var $btn_zoom_fit = $(
            '<button type="button" class="btn-bottom btn-zoom-fit">창맞추기</button>"')
          .hide()
          .click(function() {
            $btn_zoom_orig.show();
            $btn_zoom_fit.hide();

            $(item.img).css('max-width', '100%');
            $(item.img).css('max-height', '100%');
          });

        // 다운로드
        var $btn_download = $('<a class="btn-bottom btn-download">다운로드</a>')
          .attr('href', item.src)
          .attr('download', '설치이미지_' + item.index + '.jpg');

        // 회전
        var rotate_deg = 0;
        var $btn_rotate = $(
            '<button type="button" class="btn-bottom btn-rotate">회전</button>')
          .click(function() {
            rotate_deg = (rotate_deg + 90) % 360;
            $(item.img).css('transform', 'rotate(' + rotate_deg + 'deg)')
          });

        return $div.append(
          $btn_zoom_orig,
          $btn_zoom_fit,
          $btn_download,
          $btn_rotate);
      },
    },
    gallery: {
      enabled: true,
      tPrev: '이전', // title for left button
      tNext: '다음', // title for right button
      tCounter: '%curr% / %total%'
    },
  });
});
</script>

<?php
include_once('./_tail.php');
?>