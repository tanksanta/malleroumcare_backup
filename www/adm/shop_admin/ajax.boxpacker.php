<?php
$sub_menu = '400400';
include_once('./_common.php');

if($auth_check = auth_check($auth[$sub_menu], 'r', true))
  json_response(400, $auth_check);

$od_id = get_search_string($od_id);
if(!$od_id)
  json_response(400, '유효하지않은 요청입니다.');

use DVDoug\BoxPacker\InfalliblePacker;
use DVDoug\BoxPacker\Test\TestBox;  // use your own `Box` implementation
use DVDoug\BoxPacker\Test\TestItem; // use your own `Item` implementation

$packer = new InfalliblePacker();

// 쇼핑몰 박스규격 설정
for($i = 1; $i <= 15; $i++) {
  $box_size = explode(chr(30), $default['de_box_size'.($i)]);
  list($name, $width, $length, $depth) = $box_size;

  if(!($name && $width && $length && $depth))
    continue;
  
  // cm -> mm 변환, 소수점 내림
  $width = (int) floor($width * 10);
  $length = (int) floor($length * 10);
  $depth = (int) floor($depth * 10);

  $packer->addBox(new TestBox($name, $width, $length, $depth, 0, $width, $length, $depth, 1000));
}

// 위탁 상품은 제외
$sql = "
  select
    c.*,
    i.it_box_size,
    i.it_delivery_cnt
  from
    g5_shop_cart c
  left join
    g5_shop_item i ON c.it_id = i.it_id
  where
    c.od_id = '{$od_id}' and
    c.prodSupYn = 'Y' and
    c.ct_is_direct_delivery = 0
";
$result = sql_query($sql);

$compPacked = []; // 완전포장
while($row = sql_fetch_array($result)) {
  $box_size = explode(chr(30), $row['it_box_size']);
  list($width, $length, $depth) = $box_size;

  if(!($width && $length && $depth))
    continue;
  
  // cm -> mm 변환, 소수점 올림
  $width = (int) ceil($width * 10);
  $length = (int) ceil($length * 10);
  $depth = (int) ceil($depth * 10);

  $name = $row['it_name'];
  if($name != $row['ct_option']) {
    $name .= " ({$row['ct_option']})";
  }
  $box_qty = $row['ct_qty'] - $row['ct_stock_qty'];

  if($row['it_delivery_cnt'] > 1) {
    $comp_qty = (int) floor( $box_qty / $row['it_delivery_cnt'] );
    $box_qty = $box_qty % $row['it_delivery_cnt'];

    if($comp_qty > 0) {
      $compPacked[$name . " ({$row['it_delivery_cnt']}개)"] = $comp_qty;
    }
  }

  if($box_qty > 0)
    $packer->addItem(new TestItem($name, $width, $length, $depth, 0, false), $box_qty);
}

$packedBoxes = $packer->pack();

$ret = '';

if($compPacked) {
  $ret .= '[완전포장]' . PHP_EOL;
  foreach($compPacked as $key => $qty) {
    $ret .= "{$key} * {$qty}" . PHP_EOL;
  }
  $ret .= PHP_EOL;
}

if($packedBoxes->count()) {
  $ret .= '[합포추천]' . PHP_EOL;
  foreach($packedBoxes as $packedBox) {
    $boxType = $packedBox->getBox();
    $ret .= "{$boxType->getReference()} : " . PHP_EOL;

    $items = [];
    $packedItems = $packedBox->getItems();
    foreach ($packedItems as $packedItem) {
      $key = $packedItem->getItem()->getDescription();
      if(!$items[$key]) $items[$key] = 0;
      $items[$key]++;
    }

    foreach($items as $key => $val) {
      $ret .= "{$key} * {$val}" . PHP_EOL;
    }

    $ret .= PHP_EOL;
  }
}

if(!$ret) {
  json_response(500, '합포가 가능한 상품이 없습니다.');
}

json_response(200, 'OK', $ret);
