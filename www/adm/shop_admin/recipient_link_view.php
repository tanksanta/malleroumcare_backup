<?php
$sub_menu = '500050';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '수급자연결관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$rl = sql_fetch("SELECT * FROM g5_recipient_link WHERE rl_id = '{$rl_id}'");
if(!$rl['rl_id'])
  alert('존재하지 않는 수급자입니다.');
  
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

$sql_common = " from {$g5['member_table']} where 1=1 ";
$sql_common .= " and " . implode(' and ', $where);

// 테이블의 전체 레코드수만 얻음
$total_count = sql_fetch(" select count(*) as cnt " . $sql_common)['cnt'];

$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit $from_record, $page_rows ";
$result = sql_query(" select * " . $sql_common . $sql_limit);

$qstr = "rl_id={$rl_id}";
if($sel_field && $search)
  $qstr .= "&sel_field={$sel_field}&search={$search}";
?>
<div class="local_ov01 local_ov">
  <div class="tbl_frm01 tbl_wrap" style="padding: 0">
    <table>
      <tr>
        <th scope="row">수급자명</th>
        <td><?=get_text($rl['rl_name'])?></td>
      </tr>
      <tr>
        <th scope="row">연락처</th>
        <td><?=get_text($rl['rl_hp'])?></td>
      </tr>
      <tr>
        <th scope="row">주소</th>
        <td>
          <?php echo get_text($rl['rl_addr1']); ?>
          <?php echo get_text($rl['rl_addr2']); ?>
          <?php echo get_text($rl['rl_addr3']); ?>
        </td>
      </tr>
      <tr>
        <th scope="row">인정정보</th>
        <td>
          <?php
          if($rl['rl_ltm']) {
            echo "L{$rl['rl_ltm']}";
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
          if($rl['rl_pen_type'] == '11') // 직접입력
            echo get_text($rl['rl_pen_type_etc']);
          else
            echo $pen_pro_rel_cd[$rl['rl_pen_type']];
          ?> / 
          <?=get_text($rl['rl_pen_name'])?> / 
          <?=get_text($rl['rl_pen_hp'])?>
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
        <input type="hidden" name="page" value="<?=$page?>">
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
      <?php while($row = sql_fetch_array($result)) { ?>
      <tr>
        <td><?=$row['mb_entNm']?></td>
        <td>
          <?=$row['mb_giup_addr1']?> 
          <?=$row['mb_giup_addr2']?> 
          <?=$row['mb_giup_addr3']?>
        </td>
        <td>관리수급자</td>
        <td>최근3개월활동</td>
        <td>최근연결</td>
        <td>상태</td>
        <td>거리</td>
        <td>연결여부</td>
        <td>비고</td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
