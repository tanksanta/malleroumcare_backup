<?php
$sub_menu = '500050';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '수급자연결관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$rl = sql_fetch("SELECT * FROM recipient_link WHERE rl_id = '{$rl_id}'");
if(!$rl['rl_id'])
  alert('존재하지 않는 수급자입니다.');

// 수급자 주소 경위도 위치 비어있으면
if(!$rl['rl_pen_addr_lat'] || !$rl['rl_pen_addr_lng'] || $rl['rl_pen_addr_lat'] == '0.000' || $rl['rl_pen_addr_lng'] == '0.000') {
  $lat_lng = get_lat_lng_by_address($rl['rl_pen_addr1']);
  if($lat_lng) {
    sql_query("
      UPDATE recipient_link SET
      rl_pen_addr_lat = '{$lat_lng['lat']}',
      rl_pen_addr_lng = '{$lat_lng['lng']}'
      WHERE rl_id = '{$rl['rl_id']}'
    ");

    $rl['rl_pen_addr_lat'] = $lat_lng['lat'];
    $rl['rl_pen_addr_lng'] = $lat_lng['lng'];
  }
}

// 검색
$where = [];
$where[] = " mb_level IN('3', '4') "; // 사업소 or 우수사업소
$where[] = " (mb_entId is not null and mb_entId != '') ";

$search = get_search_string($search);
if( !in_array($sel_field, array('mb_entNm')) ){   //검색할 필드 대상이 아니면 값을 제거
  $sel_field = '';
  $search = '';
}
if ($sel_field != "" && $search) {
  $where[] = " $sel_field like '%$search%' ";
}

$sql_common = " from {$g5['member_table']} mb where 1=1 ";
$sql_common .= " and " . implode(' and ', $where);

// 경위도 위치 비어있는 사업소들 경위도값 업데이트
$result = sql_query("
  select * " . $sql_common . " and
  (
    mb_giup_addr_lat IS NULL or
    mb_giup_addr_lat = 0 or
    mb_giup_addr_lng IS NULL or
    mb_giup_addr_lng = 0
  )
");

while($row = sql_fetch_array($result)) {
  $lat_lng = get_lat_lng_by_address($row['mb_giup_addr1']);
  if($lat_lng) {
    sql_query("
      UPDATE {$g5['member_table']} SET
      mb_giup_addr_lat = '{$lat_lng['lat']}',
      mb_giup_addr_lng = '{$lat_lng['lng']}'
      WHERE mb_no = '{$row['mb_no']}'
    ");
  }
}

// 테이블의 전체 레코드수만 얻음
$total_count = sql_fetch(" select count(*) as cnt " . $sql_common)['cnt'];

$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit $from_record, $page_rows ";

// 경위도 사이 거리 구하는 함수
sql_query("
  DROP FUNCTION IF EXISTS distance_between
");
sql_query("
  CREATE FUNCTION distance_between (from_lat DECIMAL(6, 3), from_lng DECIMAL(6, 3), to_lat DECIMAL(6, 3), to_lng DECIMAL(6, 3)) RETURNS DECIMAL(11, 3)
    RETURN 6371 * 2 * ATAN2(SQRT(POW(SIN(RADIANS(to_lat - from_lat)/2), 2) + POW(SIN(RADIANS(to_lng - from_lng)/2), 2) * COS(RADIANS(from_lat)) * COS(RADIANS(to_lat))), SQRT(1 - POW(SIN(RADIANS(to_lat - from_lat)/2), 2) + POW(SIN(RADIANS(to_lng - from_lng)/2), 2) * COS(RADIANS(from_lat)) * COS(RADIANS(to_lat))))
");

$result = sql_query(" select distance_between('{$rl['rl_pen_addr_lat']}', '{$rl['rl_pen_addr_lng']}', mb.mb_giup_addr_lat, mb.mb_giup_addr_lng) as distance, mb.* " . $sql_common . ' order by distance asc ' . $sql_limit);

$qstr = "rl_id={$rl_id}";
if($sel_field && $search)
  $qstr .= "&sel_field={$sel_field}&search={$search}";
?>
<div class="local_ov01 local_ov">
  <div class="tbl_frm01 tbl_wrap" style="padding: 0">
    <table>
      <tr>
        <th scope="row">수급자명</th>
        <td><?=get_text($rl['rl_pen_name'])?></td>
      </tr>
      <tr>
        <th scope="row">연락처</th>
        <td><?=get_text($rl['rl_pen_hp'])?></td>
      </tr>
      <tr>
        <th scope="row">주소</th>
        <td>
          <?php echo get_text($rl['rl_pen_addr1']); ?>
          <?php echo get_text($rl['rl_pen_addr2']); ?>
          <?php echo get_text($rl['rl_pen_addr3']); ?>
        </td>
      </tr>
      <tr>
        <th scope="row">인정정보</th>
        <td>
          <?php
          if($rl['rl_pen_ltm_num']) {
            echo "L{$rl['rl_pen_ltm_num']}";
          } else {
            echo '예비수급자';
          }
          ?>
        </td>
      </tr>
      <tr>
        <th scope="row">보호자정보</th>
        <td>
          <?php
          if($rl['rl_pen_pro_type'] == '11') // 직접입력
            echo get_text($rl['rl_pen_pro_type_etc']);
          else
            echo $pen_pro_rel_cd[$rl['rl_pen_pro_type']];
          ?> / 
          <?=get_text($rl['rl_pen_pro_name'])?> / 
          <?=get_text($rl['rl_pen_pro_hp'])?>
        </td>
      </tr>
      <tr>
        <th scope="row">연결사업소</th>
        <td>
          <?=$recipient_link_state[$rl['rl_state']]?>중
        </td>
      </tr>
      <tr>
        <th scope="row">요청사항</th>
        <td><?=nl2br(get_text($rl['rl_request']))?></td>
      </tr>
    </table>
  </div>
  <div style="text-align:right;">
    <a class="btn btn_01" href="http://mall.eroumcare.doto.li/adm/shop_admin/recipient_link_form.php?w=u&rl_id=<?=$rl_id?>">정보수정</a>
    <a class="btn btn_02" href="http://mall.eroumcare.doto.li/adm/shop_admin/recipient_link_list.php">목록</a>
  </div>
</div>

<h1 class="page_title" style="margin-top: 20px;">사업소 연결</h1>

<div class="local_ov01 local_ov">
    <form name="flist" class=" local_sch">
        <input type="hidden" name="rl_id" value="<?=$rl['rl_id']?>">
        <select name="sel_field" id="sel_field">
          <option value="mb_entNm">사업소명</option>
        </select>
        <label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="search" value="" id="search" class="frm_input">
        <input type="submit" value="검색" class="btn_submit">
    </form>
</div>

<div class="tbl_head01 tbl_wrap">
  <table>
    <thead>
      <tr>
        <th scope="col">사업소명</th>
        <th scope="col">주소</th>
        <th scope="col">관리수급자</th>
        <th scope="col">최근 3개월 활동</th>
        <th scope="col">최근연결</th>
        <th scope="col">상태</th>
        <th scope="col">거리</th>
        <th scope="col">연결여부</th>
        <th scope="col">비고</th>
      </tr>
    </thead>
    <tbody>
      <?php
      while($row = sql_fetch_array($result)) {
        // 수급자-사업소 연결 가져오기
        $link = get_recipient_link($rl['rl_id'], $row['mb_id']);
      ?>
      <tr>
        <td><?=$row['mb_entNm']?></td>
        <td>
          <?=$row['mb_giup_addr1']?> 
          <?=$row['mb_giup_addr2']?> 
          <?=$row['mb_giup_addr3']?>
        </td>
        <td>
          <?php
          // 총 수급자 수
          $ent_pens = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
            'usrId' => $row['mb_id'],
            'entId' => $row['mb_entId']
          ));
          if($ent_pens['errorYN'] == 'N') echo count($ent_pens['data']).'명';
          else '0명';

          // 한 번 이상 계약서 작성한 수급자 수
          $ent_ef_pens = sql_fetch("
            SELECT COUNT(*) as cnt FROM
            (
              SELECT * FROM `eform_document`
              WHERE entId = '{$row['mb_entId']}'
              AND dc_status IN ('2', '3')
              GROUP BY penId
            ) r
          ");
          echo "({$ent_ef_pens['cnt']}명 진행)";
          ?>
        </td>
        <td>
          <?php
          // 최근 3개월 동안 계약서 작성한 수급자 수 & 수급자별 평균 계약 금액
          $ent_recent_ef_pens = sql_fetch("
            SELECT COUNT(*) as cnt, FLOOR(AVG(r.sum)) as avg FROM
            (
              SELECT sum(CONVERT(it_price, UNSIGNED INTEGER)) as sum FROM `eform_document` d
              LEFT JOIN `eform_document_item` i on d.dc_id = i.dc_id
              WHERE entId = '{$row['mb_entId']}'
              AND dc_status IN ('2', '3')
              AND (d.dc_datetime BETWEEN DATE_SUB(NOW(), INTERVAL 3 MONTH) AND NOW())
              GROUP BY penId
            ) r
          ");
          $ent_recent_ef_pens['avg'] = number_format($ent_recent_ef_pens['avg']);
          echo "{$ent_recent_ef_pens['cnt']}명 / 평균 {$ent_recent_ef_pens['avg']}원"
          ?>
        </td>
        <td style="text-align:center;">
          <?php
          // 최근 3개월 동안 이로움에서 연결해준 수급자 수
          $ent_recent_links = sql_fetch("
            SELECT COUNT(*) as cnt
            FROM `recipient_link_rel`
            WHERE mb_id = '{$row['mb_id']}'
            AND (created_at BETWEEN DATE_SUB(NOW(), INTERVAL 3 MONTH) AND NOW())
          ");
          echo "{$ent_recent_links['cnt']}명";
          ?>
        </td>
        <td style="text-align:center;">
          <?php
          // 상태
          $status = '대기';
          switch($link['status']) {
            case 'request':
              $status = '요청중';
              break;
            case 'link':
              $status = '연결완료';
              break;
            case 'done':
              $status = '등록완료';
              break;
          }
          echo $status;
          ?>
        </td>
        <td style="text-align:center;"><?=number_format($row['distance'])?>km</td>
        <td style="text-align:center;">
          <?php
          if(!$link || $link['status'] == 'wait') {
          ?>
          <button class="btn btn_03 btn_request" data-id="<?=$row['mb_id']?>">요청하기</button>
          <?php
          } else if($link['status'] == 'request') {
          ?>
          <button class="btn btn_01 btn_cancel" data-id="<?=$row['mb_id']?>">요청취소</button>
          <?php
          } else if($link['status'] == 'link') {
            echo '연결됨';
          } else if($link['status'] == 'done') {
            echo '등록완료';
          }
          ?>
        </td>
        <td>
          <?php
          // 비고
          if($link && $link['status'] == 'wait') {
            echo date('Y-m-d 연결기록',strtotime($link['updated_at']));
          }
          ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script>
$(function() {
  // 요청하기 버튼
  $(document).on('click', '.btn_request', function(e) {
    var $this = $(this);
    $.post('./ajax.recipient.link.php', {
      mb_id: $this.data('id'),
      rl_id: '<?=$rl['rl_id']?>'
    }, 'json')
    .done(function(data) {
      $this.removeClass('btn_request btn_03')
      .addClass('btn_cancel btn_01')
      .text('요청취소');
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 요청취소 버튼
  $(document).on('click', '.btn_cancel', function(e) {
    var $this = $(this);
    $.post('./ajax.recipient.link.php', {
      mb_id: $this.data('id'),
      rl_id: '<?=$rl['rl_id']?>',
      w: 'd'
    }, 'json')
    .done(function(data) {
      $this.removeClass('btn_cancel btn_01')
      .addClass('btn_request btn_03')
      .text('요청하기')
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });
});
</script>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
