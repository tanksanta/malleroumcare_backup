<?php
include_once('./_common.php');

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
  require_once(G5_SHOP_PATH.'/yc/orderinquiry.php');
  return;
}

define("_ORDERINQUIRY_", true);

// 회원이 아닌 경우
if (!$is_member) goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/claim_manage.php'));

// Page ID
$pid = ($pid) ? $pid : 'inquiry';
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
$is_inquiry_sub = false;
@include_once($order_skin_path.'/config.skin.php');

$g5['title'] = '청구관리';


include_once('./_head.php');

$skin_path = $order_skin_path;
$skin_url = $order_skin_url;

// 셋업
$setup_href = '';
if(is_file($skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
  $setup_href = './skin.setup.php?skin=order&amp;name='.urlencode($skin_name).'&amp;ts='.urlencode(THEMA);
}

if(!$selected_month) $selected_month = date('Y-m-01');

$entId = $member['mb_entId'];
if(!$entId) {
  alert('사업소 회원만 접근 가능합니다.');
}

$where = "";
$search = get_search_string($search);
if(in_array($searchtype, ['penNm', 'penLtmNum']) && $search) {
  $where = " AND $searchtype LIKE '%$search%' ";
}

// 수급자만 모아보기
if($penId) {
  $where .= " AND penId = '$penId' ";
}

$eform_query = sql_query("
  SELECT
    MIN(STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '3'), '%Y-%m-%d')) as start_date,
    SUM(
      CASE WHEN gubun = '01' AND STR_TO_DATE(CONCAT(SUBSTRING_INDEX(`it_date`, '-', '2'), '-01'), '%Y-%m-%d') <> '$selected_month'
      THEN
        CASE WHEN STR_TO_DATE(CONCAT(SUBSTRING(`it_date`, 12, 7), '-01'), '%Y-%m-%d') = '$selected_month' -- 마지막 달이면
        THEN TRUNCATE(
          (SELECT `it_rental_price` FROM `g5_shop_item` WHERE ProdPayCode = b.it_code limit 1) * ( SUBSTRING_INDEX(`it_date`, '-', '-1') / DATE_FORMAT(LAST_DAY(STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '-3'), '%Y-%m-%d')), '%d') )
          , -1)
        ELSE (SELECT `it_rental_price` FROM `g5_shop_item` WHERE ProdPayCode = b.it_code limit 1) END
      ELSE `it_price` END
    ) as total_price,
    SUM(
      CASE WHEN gubun = '01' AND STR_TO_DATE(CONCAT(SUBSTRING_INDEX(`it_date`, '-', '2'), '-01'), '%Y-%m-%d') <> '$selected_month'
      THEN
        TRUNCATE(
          CASE
            WHEN STR_TO_DATE(CONCAT(SUBSTRING(`it_date`, 12, 7), '-01'), '%Y-%m-%d') = '$selected_month'
          THEN
            TRUNCATE((SELECT `it_rental_price` FROM `g5_shop_item` WHERE ProdPayCode = b.it_code limit 1) * ( SUBSTRING_INDEX(`it_date`, '-', '-1') / DATE_FORMAT(LAST_DAY(STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '-3'), '%Y-%m-%d')), '%d') ), -1)
          ELSE
            (SELECT `it_rental_price` FROM `g5_shop_item` WHERE ProdPayCode = b.it_code limit 1)
          END
          *
          CASE
            WHEN penTypeCd = '01'
            THEN 0.09
            WHEN penTypeCd = '02' or penTypeCd = '03'
            THEN 0.06
            WHEN penTypeCd = '04'
            THEN 0
            ELSE 0.15
          END
        , -1)
      ELSE `it_price_pen` END
    ) as total_price_pen,
    (
      SUM(
        CASE WHEN gubun = '01' AND STR_TO_DATE(CONCAT(SUBSTRING_INDEX(`it_date`, '-', '2'), '-01'), '%Y-%m-%d') <> '$selected_month'
        THEN
          CASE WHEN STR_TO_DATE(CONCAT(SUBSTRING(`it_date`, 12, 7), '-01'), '%Y-%m-%d') = '$selected_month' -- 마지막 달이면
          THEN TRUNCATE(
            (SELECT `it_rental_price` FROM `g5_shop_item` WHERE ProdPayCode = b.it_code limit 1) * ( SUBSTRING_INDEX(`it_date`, '-', '-1') / DATE_FORMAT(LAST_DAY(STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '-3'), '%Y-%m-%d')), '%d') )
            , -1)
          ELSE (SELECT `it_rental_price` FROM `g5_shop_item` WHERE ProdPayCode = b.it_code limit 1) END
        ELSE `it_price` END
      )
      -
      SUM(
        CASE WHEN gubun = '01' AND STR_TO_DATE(CONCAT(SUBSTRING_INDEX(`it_date`, '-', '2'), '-01'), '%Y-%m-%d') <> '$selected_month'
        THEN
          TRUNCATE(
            CASE
              WHEN STR_TO_DATE(CONCAT(SUBSTRING(`it_date`, 12, 7), '-01'), '%Y-%m-%d') = '$selected_month'
            THEN
              TRUNCATE((SELECT `it_rental_price` FROM `g5_shop_item` WHERE ProdPayCode = b.it_code limit 1) * ( SUBSTRING_INDEX(`it_date`, '-', '-1') / DATE_FORMAT(LAST_DAY(STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '-3'), '%Y-%m-%d')), '%d') ), -1)
            ELSE
              (SELECT `it_rental_price` FROM `g5_shop_item` WHERE ProdPayCode = b.it_code limit 1)
            END
            *
            CASE
              WHEN penTypeCd = '01'
              THEN 0.09
              WHEN penTypeCd = '02' or penTypeCd = '03'
              THEN 0.06
              WHEN penTypeCd = '04'
              THEN 0
              ELSE 0.15
            END
          , -1)
        ELSE `it_price_pen` END
      )
    ) as total_price_ent,
    penId, penNm, penLtmNum, penRecGraCd, penRecGraNm, penTypeCd, penTypeNm, penBirth
  FROM
    `eform_document` a
    LEFT JOIN `eform_document_item` b ON a.dc_id = b.dc_id
  WHERE
    entId = '$entId'
    AND dc_status = '2'
    $where
    AND
    (
      (
        gubun = '00' AND
        STR_TO_DATE(CONCAT(SUBSTRING_INDEX(`it_date`, '-', '2'), '-01'), '%Y-%m-%d') = '$selected_month'
      )
      OR
      (
        gubun = '01' AND
        (
          (STR_TO_DATE(CONCAT(SUBSTRING_INDEX(`it_date`, '-', '2'), '-01'), '%Y-%m-%d') <= '$selected_month')
          AND
          (STR_TO_DATE(SUBSTRING_INDEX(`it_date`, '-', '-3'), '%Y-%m-%d') >= '$selected_month')
        )
      )
    )
  GROUP BY `penId`, `penTypeCd`
  ORDER BY `penNm` ASC, start_date asc
");

$cl_query = sql_query("SELECT * FROM `claim_management` WHERE selected_month = '$selected_month' AND mb_id = '{$member['mb_id']}'");
$cl = [];
while($row = sql_fetch_array($cl_query)) {
  $cl[] = $row;
}

// 건보자료 가져오기
$nhis_query = sql_query("
  SELECT *
  FROM
    (
      SELECT *
      FROM
        `claim_nhis_upload`
      WHERE
        mb_id = '{$member['mb_id']}' AND
        selected_month = '{$selected_month}'
      ORDER BY created_at DESC
      LIMIT 1
    ) u
    LEFT JOIN `claim_nhis_content` c ON u.cu_id = c.cu_id
  ORDER BY c.cc_id ASC
");

$nhis = [];
for($i = 0; $row = sql_fetch_array($nhis_query); $i++) {
  # 청구내역에 매치되는 수급자 있는지 체크할 용도로 사용할 변수
  # matched = false 인 값들은 미 매칭 자료에 뿌려줌
  $row['matched'] = false;

  $nhis[] = $row;
}

$upload_date = '';
if($nhis) {
  $upload_date = $nhis[0]['created_at'];
}

// 청구내역 배열
$claims = [];
for($i = 0; $row = sql_fetch_array($eform_query); $i++) {
  $row['selected_month'] = $selected_month;
  if(strtotime($row['start_date']) < strtotime($selected_month))
    $row['start_date'] = $selected_month;
  
  $row['orig'] = array(
    'start_date' => $row['start_date'],
    'total_price' => $row['total_price'],
    'total_price_pen' => $row['total_price_pen'],
    'total_price_ent' => $row['total_price_ent']
  );

  $row['cl_status'] = '0'; // 0: 내용 수정 전, 1: 내용 수정 후

  foreach($cl as $val) {
    if
    (
      $val['penId'] == $row['penId'] &&
      $val['penNm'] == $row['penNm'] &&
      $val['penLtmNum'] == $row['penLtmNum'] &&
      $val['penRecGraCd'] == $row['penRecGraCd'] &&
      $val['penTypeCd'] == $row['penTypeCd'] &&
      $val['cl_status'] != 0
    ) {
      $row['cl_status'] = $val['cl_status'];
      $row['start_date'] = $val['cl_start_date'];
      $row['total_price'] = $val['cl_total_price'];
      $row['total_price_pen'] = $val['cl_total_price_pen'];
      $row['total_price_ent'] = $val['cl_total_price_ent'];
    }
  }

  $nhis_matched = [];
  for($n = 0; $n < count($nhis); $n++) {
    if (
      ($nhis[$n]['penNm'] === $row['penNm']) &&
      (substr($nhis[$n]['penLtmNum'], 0, 7) === substr($row['penLtmNum'], 0, 7))
    ) {
      $nhis_matched[] = &$nhis[$n];
    }
  }

  $row['match'] = null;
  $row['status'] = '대기';
  $row['error'] = [];
  foreach($nhis_matched as &$match) {
    // 이미 매칭된 자료면 넘어감
    if($match['matched'] == true) continue;

    if($match['penTypeCd'] == $row['penTypeCd']) {
      // 본인부담금율 같음
      $match['matched'] = true;
      $row['match'] = $match;
      $row['error'] = check_match_error($row, $match);
    } else {
      // 본인부담금율 다를 때
      if(
        count(array_filter($nhis_matched,
          function($nm) {
            return $nm['matched'] == false;
          }
        )) > 1
      ) {
        # 일단 정렬순서 믿고 그냥 매칭시켜봄
        $match['matched'] = true;
        $row['match'] = $match;
        $row['error'] = check_match_error($row, $match);
      } else {
        $match['matched'] = true;
        $row['match'] = $match;
        $row['error'] = check_match_error($row, $match);
      }
    }
  }
  unset($match);

  if($row['match']) {
    if($row['error'])
      $row['status'] = '오류';
    else if($row['cl_status'])
      $row['status'] = '변경완료';
    else
      $row['status'] = '정상';
  }

  $row['change'] = [];
  if($row['status'] == '변경완료') {
    foreach($row['orig'] as $key => $val) {
      if($row[$key] != $val) {
        $row['change'][] = $key;
      }
    }
  }
  
  $claims[] = $row;
}

function check_match_error($row, $match) {
  $error = [];

  if(
    $row['penTypeCd'] != $match['penTypeCd'] ||
    $row['penRecGraNm'] != $match['penRecGraNm']
  )
    $error[] = 'pen';
  
  if(
    $row['start_date'] != $match['start_date']
  )
    $error[] = 'start_date';
  
  if(
    $row['total_price'] != $match['total_price']
  )
    $error[] = 'total_price';
  
  if(
    $row['total_price_pen'] != $match['total_price_pen']
  )
    $error[] = 'total_price_pen';
  
  if(
    $row['total_price_ent'] != $match['total_price_ent']
  )
    $error[] = 'total_price_ent';
  
  return $error;
}

$cur_year = intval(date('Y'));
$cur_month = intval(date('n'));

$sel_year = intval(substr($selected_month, 0, 4));
$sel_month = intval(substr($selected_month, 5, 2));

$has_prev = false;
$has_next = false;
if($sel_year <= $cur_year && $sel_year >= 2021) {
  if($sel_year == 2021) {
    if($sel_month > 6) {
      $has_prev = true;
      $prev_month = "2021-".str_pad($sel_month-1, 2, '0', STR_PAD_LEFT).'-01';
    }
    if($sel_month < $cur_month) {
      $has_next = true;
      $next_month = "2021-".str_pad($sel_month+1, 2, '0', STR_PAD_LEFT).'-01';
    }
  } else if($sel_year > 2021) {
    $has_prev = true;
    if($sel_year == $cur_year) {
      if($sel_month < $cur_month) {
        $has_next = true;
        $next_month = "$sel_year-".str_pad($sel_month+1, 2, '0', STR_PAD_LEFT).'-01';
      }
    } else if($sel_year < $cur_year) {
      $has_next = true;
      if($sel_month == 12) {
        $next_month = ($sel_year + 1).'-01-01';
      } else {
        $next_month = "$sel_year-".str_pad($sel_month+1, 2, '0', STR_PAD_LEFT).'-01';
      }
    }
    if($sel_month == 1) {
      $prev_month = ($sel_year - 1).'-12-01';
    } else {
      $prev_month = $sel_year.'-'.str_pad($sel_month-1, 2, '0', STR_PAD_LEFT).'-01';
    }
  }
}

add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 8);
add_javascript('<script src="'.G5_PLUGIN_URL.'/DataTables/datatables.min.js"></script>', 9);
add_javascript('<script src="'.G5_JS_URL.'/remodal/remodal.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal.css">', 11);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal-default-theme.css">', 12);
add_stylesheet('<link rel="stylesheet" href="'.G5_PLUGIN_URL.'/DataTables/datatables.min.css">', 13);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 14);

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>
<section class="wrap">
  <div class="sub_section_tit">청구/전자문서관리</div>
  <ul class="list_tab">
    <li class="active"><a href="<?=G5_SHOP_URL?>/claim_manage.php">청구관리</a></li>
    <li ><a href="<?=G5_SHOP_URL?>/electronic_manage.php">전자문서관리<!--<span class="red_info">미작성: 1건</span>--></a></li>
  </ul>
  <div class="inner">
    <form action="/shop/claim_manage.php" method="get">
      <div class="date_wrap">
        <div class="date_this">
          <a href="?selected_month=<?=date('Y-m-01')?><?=$penId ? "&penId=$penId" : ''?>">이번달</a>
        </div>
        <div class="date_selected">
          <a href="<?=$has_prev ? '?selected_month='.$prev_month.($penId ? "&penId=$penId" : '') : '#'?>" class="<?=$has_prev ? '' : 'disabled'?>">◀ 지난달</a>
          <select name="selected_month" id="selected_month">
            <?php
            for($year = 2021; $year <= $cur_year; $year++) {
              for($month = 1; $month <= 12; $month++) {
                if($year == 2021 && $month < 6) { // 2021년 6월 이전은 무시 (신규계약서 적용 전)
                  continue;
                }

                if($year == $cur_year && $month > $cur_month) { // 현재 년/월 보다 미래는 무시
                  break;
                }

                $leading_zero_month = str_pad($month, 2, '0', STR_PAD_LEFT);

                $option_value = "{$year}-{$leading_zero_month}-01";
              ?>
              <option value="<?=$option_value?>"<?=$option_value == $selected_month ? ' selected' : ''?>><?="{$year}년 {$month}월 청구내역"?></option>
              <?php
              }
            }
            ?>
          </select>
          <a href="<?=$has_next ? '?selected_month='.$next_month.($penId ? "&penId=$penId" : '') : '#'?>" class="<?=$has_next ? '' : 'disabled'?>">다음달 ▶</a>
        </div>
      </div>
      
      <?php if(!$penId) { ?>
      <div class="search_box">
        <select name="searchtype">
          <option value="penNm" <?=get_selected($searchtype, 'penNm')?>>수급자명</option>
          <option value="penLtmNum" <?=get_selected($searchtype, 'penLtmNum')?>>요양인정번호</option>
        </select>
        <div class="input_search">
          <input name="search" value="<?=$_GET["search"]?>" type="text">
          <button type="submit"></button>
        </div>
      </div>
      <?php } ?>
    </form>
  </div>
  <div class="r_btn_area">
    <?php if($upload_date) { ?>
    <span>마지막 업데이트 : <?=date('Y-m-d', strtotime($upload_date))?></span>
    <?php } ?>
    <a href="#" id="btn_nhis" class="btn_nhis" data-remodal-target="modal">건보자료 업로드</a>
  </div>
  <div class="list_box">
    <div class="table_box">
      <table id="table_claim">
        <thead>
          <tr>
            <th>No.</th>
            <th>수급자 정보</th>
            <th>급여시작일</th>
            <th>급여비용총액</th>
            <th>본인부담금</th>
            <th>청구액</th>
            <th>검증상태</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          foreach($claims as $row) {
            $index = $i++;
          ?>
          <tr class="<?=$row['error'] ? 'tr_err' : ''?>">
            <td><?=$index?></td>
            <td class="pen">
              <?="{$row['penNm']}({$row['penLtmNum']} / {$row['penRecGraNm']} / {$row['penTypeNm']})"?>
              <?php
              if(in_array('pen', $row['error'])) {
              ?>
              <br>
              <span class="text_red">
                <?="{$row['penNm']}({$row['penLtmNum']} / {$row['match']['penRecGraNm']} / {$row['match']['penTypeNm']})"?>
              </span>
              <?php
              }
              ?>
            </td>
            <td class="start_date">
              <?php
              if(in_array('start_date', $row['change'])) {
                echo "<span class=\"text_point\">{$row['start_date']}</span>";
              } else {
                echo "<span class=\"val\">{$row['start_date']}</span>";
              }
              
              if(in_array('start_date', $row['error'])) {
              ?>
              <br>
              <span class="text_red">
                <?=$row['match']['start_date']?>
              </span>
              <br>
              <button class="btn_edit e_btn" data-key="start_date" data-json="<?=htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8')?>">수정</button>
              <?php
              }
              ?>
            </td>
            <td class="total_price">
              <?php
              if(in_array('total_price', $row['change'])) {
                echo "<span class=\"text_point\">" . number_format($row['total_price']).'원' . "</span>";
              } else {
                echo "<span class=\"val\">" . number_format($row['total_price']).'원' . "</span>";
              }

              if(in_array('total_price', $row['error'])) {
              ?>
              <br>
              <span class="text_red">
                <?=number_format($row['match']['total_price'])?>원
              </span>
              <br>
              <button class="btn_edit e_btn" data-key="total_price" data-json="<?=htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8')?>">수정</button>
              <?php
              }
              ?>
            </td>
            <td class="total_price_pen">
              <?php
              if(in_array('total_price_pen', $row['change'])) {
                echo "<span class=\"text_point\">" . number_format($row['total_price_pen']).'원' . "</span>";
              } else {
                echo "<span class=\"val\">" . number_format($row['total_price_pen']).'원' . "</span>";
              }

              if(in_array('total_price_pen', $row['error'])) {
              ?>
              <br>
              <span class="text_red">
                <?=number_format($row['match']['total_price_pen'])?>원
              </span>
              <br>
              <button class="btn_edit e_btn" data-key="total_price_pen" data-json="<?=htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8')?>">수정</button>
              <?php
              }
              ?>
            </td>
            <td class="total_price_ent">
              <?php
              if(in_array('total_price_ent', $row['change'])) {
                echo "<span class=\"text_point\">" . number_format($row['total_price_ent']).'원' . "</span>";
              } else {
                echo "<span class=\"val\">" . number_format($row['total_price_ent']).'원' . "</span>";
              }
              
              if(in_array('total_price_ent', $row['error'])) {
              ?>
              <br>
              <span class="text_red">
                <?=number_format($row['match']['total_price_ent'])?>원
              </span>
              <br>
              <button class="btn_edit e_btn" data-key="total_price_ent" data-json="<?=htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8')?>">수정</button>
              <?php
              }
              ?>
            </td>
            <td class="status" data-status="<?=$row['status']?>">
              <?=$row['status']?>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <?php if(!$where) { # 검색결과 보여줄땐 숨김 ?>
    <div class="subtit">
      건강관리공단 미 매칭 자료 
    </div>
    <div class="table_box">
      <table id="table_unmatched">
        <thead>
          <tr>
            <th>No.</th>
            <th>수급자 정보</th>
            <th>급여시작일</th>
            <th>급여비용총액</th>
            <th>본인부담금</th>
            <th>청구액</th>
            <th>검증상태</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          for($n = 0; $n < count($nhis); $n++) {
            $nh = $nhis[$n];
            if($nh['matched'] == false) {
              $index = $i++;
          ?>
          <tr>
            <td><?=$index?></td>
            <td><?="{$nh['penNm']}({$nh['penLtmNum']} / {$nh['penRecGraNm']} / {$nh['penTypeNm']})"?></td>
            <td><?=$nh['start_date']?></td>
            <td><?=number_format($nh['total_price'])?>원</td>
            <td><?=number_format($nh['total_price_pen'])?></td>
            <td><?=number_format($nh['total_price_ent'])?></td>
            <td class="text_gray">미매칭</td>
          </tr>
          <?php
            }
          }
          ?>
        </tbody>
      </table>
    </div>
    <?php } ?>
  </div>
</section>

<div class="remodal" data-remodal-id="modal" data-remodal-options="hashTracking: false">
  <button type="button" class="remodal-close" data-remodal-action="close"></button>
  <h2>건보자료 업로드</h2>
  <p class="help-block">건강보험공단 자료를 업로드해주세요.</p>
  <form id="form_nhis" class="form-horizontal" style="font-size: 14px;">
    <div class="form-group">
      <label for="nhisfile" class="col-sm-2 control-label">파일업로드</label>
      <div class="col-sm-10">
        <input type="file" name="nhisfile" id="nhisfile">
      </div>
    </div>
    <input type="submit" value="업로드" class="remodal-confirm">
    <button data-remodal-action="cancel" class="remodal-cancel">닫기</button>
  </form>
</div>

<script>
$(function() {
  var dt_option = {
    dom: 'rt<"bottom"p><"clear">',
    drawCallback: function() {
      var $api = this.api();
      var pages = $api.page.info().pages;
      if(pages <= 1) {
        this.parent().find('.bottom').css('display','none');
      }
    },
    info: false,
    lengthChange: false,
    searching: false,
    language: {
      info: "_PAGE_ 페이지 ( 총 _PAGES_ 페이지 )",
      infoEmpty: '',
      emptyTable: '내역이 없습니다.',
      paginate: {
        first: '«',
        previous: '‹',
        next: '›',
        last: '»'
      },
      aria: {
        paginate: {
          first: '처음',
          previous: '이전',
          next: '다음',
          last: '마지막'
        }
      }
    }
  };

  $('#table_unmatched').DataTable(dt_option);
  dt_option.dom = 'rt<"#excel_row"><"bottom"p><"clear">';
  $('#table_claim').DataTable(dt_option);
  $('#excel_row').html('\
    <div class="row" style="margin:20px 0 0 0">\
      <div class="r_btn_area">\
        <a href="./claim_manage_excel.php?selected_month=<?=$selected_month?>&searchtype=<?=$searchtype?>&search=<?=$search?><?=$penId ? "&penId=$penId" : ''?>">엑셀다운로드</a>\
      </div>\
    </div>\
  ');

  $('#form_nhis').on('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(document.getElementById("form_nhis"));
    fd.append('selected_month', '<?=$selected_month?>')
    $.ajax({
      url: 'ajax.claim_manage.nhis.upload.php',
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'json'
    })
    .done(function() {
      location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });
});
</script>

<style>
.table_box .row { margin: 0; }
.table_box .clear { clear: both; }
.table_box td { vertical-align: top; text-align: center; }

tr.tr_err { background-color: #f6f8e3; }
td.status {color: #bbb;}
td.status[data-status="정상"] {color: #333;}
td.status[data-status="오류"] {color: #ff0000;}
td.status[data-status="변경완료"] {color: #a9b329;}

.e_btn { color: #666; padding: 2px 6px; border: 1px solid #ddd; background-color:#eee; line-height: 20px; }
.e_ipt { border: 1px solid #ddd; line-height: 20px; padding: 2px; color: #333; width: 80px; }
.popModal_content > div { color: #999; }
.popModal .popModal_content { margin: 0 !important; }
</style>

<script>
function formatValue(key, value) {
  switch(key) {
    case 'pen':
      return value.penNm + '('
      + value.penLtmNum + ' / '
      + value.penRecGraNm + ' / '
      + value.penTypeNm + ')';
    case 'start_date':
      return value;
    case 'total_price':
    case 'total_price_pen':
    case 'total_price_ent':
      return parseInt(value).toLocaleString('en-US')+'원';
  }
}

function updateClaim(cl_id, key, value) {
  var data = {
    cl_id: cl_id
  };
  data[key] = value;

  $.post('./ajax.claim_manage.update.php', JSON.stringify(data), 'json')
  .done(function(result) {
    var $tr = $('#table_claim tr[data-id="'+cl_id+'"]');
    var $td = $tr.find('td.'+key);
    var json = $td.find('.btn_edit').data('json');

    if(!json || !json.match) return;
    var match = json.match[key];

    json[key] = value;
    $tr.find('.btn_edit').attr('data-json', JSON.stringify(json));

    if(value == match) {
      $td.html(
        '<span class="text_point">'
        + formatValue(key, value) +
        '</span>'
      );

      // 정상 체크
      if($tr.find('.text_red').length == 0) {
        // 오류가 없으면
        $tr.removeClass('tr_err')
        .find('td.status')
        .attr('data-status', '변경완료')
        .text('변경완료');
      }
    } else {
      $td.find('.val')
      .text(formatValue(key, value));
    }
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  });
}

function buildEditHtml(key, data) {
  // 수급자 수정일 경우
  if(key == 'pen') {
    return '\
    작성정보 : \
    ';
  }

  var val = data[key];
  var match = val;
  if(data.match)
    match = data.match[key];
  var html = '';
  switch(key) {
    case 'start_date':
      html = '\
        작성정보 : '+val+'\
        <br>\
        공단정보 : '+match+'\
        <br>\
        최종정보 : <input type="text" class="e_ipt" id="ipt_'+key+'">\
        <button class="e_btn" id="btn_'+key+'">확인</button>\
      ';
      break;
    case 'total_price':
    case 'total_price_pen':
    case 'total_price_ent':
      val = parseInt(val).toLocaleString('en-US');
      match = parseInt(match).toLocaleString('en-US');
      html = '\
        작성금액 : '+val+'\
        <br>\
        공단금액 : '+match+'\
        <br>\
        최종금액 : <input type="text" class="e_ipt" id="ipt_'+key+'">\
        <button class="e_btn" id="btn_'+key+'">확인</button>\
      ';
      break;
  }

  return html;
}

$(function() {
  // 청구 달 선택
  $('#selected_month').change(function() {
    var selected_month = $(this).val();
    location.href='./claim_manage.php?selected_month=' + selected_month;
  });

  // 내용변경 버튼
  $(document).on('click', '.btn_edit', function(e) {
    e.preventDefault();

    var $this = $(this);
    var key = $this.data('key');
    var data = $this.data('json');
    $.post('./ajax.claim_manage.write.php', JSON.stringify(data), 'json')
    .done(function(result) {
      var cl_id = result.message;
      $this.closest('tr').attr('data-id', cl_id);

      $this.popModal({
        html: buildEditHtml(key, data),
        showCloseBut: false,
        placement: 'bottomCenter'
      });

      //if(key == 'pen')

      if(key == 'start_date')
        $('#ipt_start_date').datepicker({ changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd' });


      $('#btn_'+key).click(function() {
        var val = $('#ipt_'+key).val();
        if(!val)
            return alert('값을 입력해주세요.');

        updateClaim(cl_id, key, val);
        $this.popModal('hide');
      });

    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });
});
</script>


<?php
include_once('./_tail.php');
?>
