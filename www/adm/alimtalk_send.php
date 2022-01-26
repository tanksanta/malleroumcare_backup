<?php
$sub_menu = '200400';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$test = $_GET['test'];
$al_id = get_search_string($_GET['al_id']);

$al = sql_fetch(" select * from g5_alimtalk where al_id = '$al_id' ");
if(!$al)
  alert('존재하지 않는 알림톡입니다.');

if($test) {
  // 테스트
  $sql = "
    select * from
      g5_member
    where
      mb_id = 'admin'
  ";
} else {
  if($al['al_type'] == 0) {
    // 전체 사업소
    $sql = "
      select * from
        g5_member
      where
        mb_type = 'default' and
        mb_level >= 3 and
        mb_level < 9 and
        mb_temp = 0
    ";
  } else {
    // 사업소 선택
    $sql = "
      select
          m.*
      from
          g5_alimtalk_member a
      left join
          g5_member m on a.mb_id = m.mb_id
      where
          al_id = '$al_id'
      order by
          a.mb_id asc
    ";
  }
}
$mb_result = sql_query($sql, true);

while($mb = sql_fetch_array($mb_result)) {
  $num = $mb['mb_hp'];
  if ($al['al_cate'] == 0) {
    $url = "https://eroumcare.com/shop/list_oos.php?ca_id=10&sort=custom";
    $msg = "[이로움 긴급공지 안내]\n{$mb['mb_name']} 님,\n이로움 유통상품 중 현재 공급이 원활하지 않은 상품을 안내 드립니다.\n주문시 참고하여 주시기 바랍니다.\n\n■ 상품명 : {$al['al_itname']}\n■ 입고예정일 : {$al['al_itdate']}";
    send_alim_talk('ENT_STO_'.$mb['mb_id'], $num, 'ent_stock_date_btn', $msg,
      [
          'button' => [
              [
                  'name' => '품절상품 전체 확인하기',
                  'type' => 'WL',
                  'url_mobile' => $url,
                  'url_pc' => $url
              ]
          ]
      ]
    );
  }
  else if ($al['al_cate'] == 1) {
    $url = '';
    $msg = "[이로움 공지 안내]\n{$mb['mb_name']} 님,\주문하신 상품이 입고가 완료되어 아래와 같은 내용으로 출고예정입니다.\n■ 상품명 : {$al['al_itname']}\n■ 수량 : \n■ 출고예정일 : {$al['al_itdate']}";
    send_alim_talk('ENT_ORDER_RELEASE_'.$mb['mb_id'], $num, 'ent_order_release', $msg);
  }

  // 푸시 발송
  add_notification(
    [],
    $mb['mb_id'],
    '[이로움 긴급공지 안내]',
    $msg,
    $url,
  );
}

alert('전송이 완료되었습니다.');

