<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default')
  alert('접근할 수 없습니다.');

$ms_id = get_search_string($ms_id);
if(!$ms_id)
  alert('정상적으로 접근해주세요.');

$sql = "
  SELECT *
  FROM recipient_item_msg
  WHERE ms_id = '{$ms_id}' and mb_id = '{$member['mb_id']}'
";
$ms = sql_fetch($sql);
if(!$ms['ms_id'])
  alert('품목/정보 메시지를 조회할 수 없습니다.');

$msg_url = "https://eroumcare.com/shop/item_msg.php?url={$ms['ms_url']}";

$sql = " SELECT * FROM recipient_item_msg_item WHERE ms_id = '{$ms['ms_id']}' ORDER BY mi_id ASC ";
$result = sql_query($sql);

$items = [];
while($row = sql_fetch_array($result)) {
  $items[] = $row;
}

$g5['title'] = '품목/정보 메시지 상세';
include_once("./_head.php");
add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/item_msg.css">');
?>

<section class="wrap">
  <div class="title_wrap">
    <div class="sub_section_tit">
      품목/정보 메시지 상세
    </div>
    <div class="r_btn_wrap">
      <a class="c_btn" href="item_msg_list.php">목록</a>
    </div>
  </div>
  <div class="inner">
    <div class="form-horizontal iv_pen_info">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <label for="ms_pen_nm" class="col-sm-2 control-label">
              <strong>수급자명</strong>
            </label>
            <div class="col-sm-8">
              <?=$ms['ms_pen_nm']?>
            </div>
          </div>
          <div class="form-group">
            <label for="ms_pen_hp" class="col-sm-2 control-label">
              <strong>휴대폰번호</strong>
            </label>
            <div class="col-sm-8">
              <?php
              if($ms['ms_pro_yn'] === 'Y')
                echo '(보호자) ';
              else
                echo '(수급자) ';
              echo $ms['ms_pen_hp'];
              ?>
            </div>
          </div>
          <div class="form-group">
            <label for="ms_pen_nm" class="col-sm-2 control-label">
              <strong>전송 URL</strong>
            </label>
            <div class="col-sm-8 url">
              <a href="<?=$msg_url?>"><?=$msg_url?></a>
            </div>
          </div>
        </div>
        <?php
        if(!$ms['ms_pen_id']) {
          $qstr = 'penNm=' . urlencode($ms['ms_pen_nm']);
          if($ms['ms_pro_yn'] === 'Y')
            $qstr .= '&penProConNum=' . urlencode($ms['ms_pen_hp']);
          else
            $qstr .= '&penConNum=' . urlencode($ms['ms_pen_hp']);
        ?>
        <a href="/shop/my_recipient_write.php?<?=$qstr?>" class="btn_add_pen">신규 수급자 등록하기</a>
        <?php } ?>
      </div>
    </div>
    <div class="im_sch_wr">
      <div class="im_sch_hd">품목 목록</div>
    </div>
    <div class="im_list_wr" style="margin-top: 20px;">
      <?php require_once('./item_msg_render.php'); ?>
    </div>
  </div>
</section>

<?php include_once("./_tail.php"); ?>
