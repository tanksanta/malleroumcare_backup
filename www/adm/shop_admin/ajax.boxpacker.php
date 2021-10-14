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
use DVDoug\BoxPacker\EroumItem;

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

// 출고준비 단계만 계산
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
    c.ct_status = '출고준비'
";
$result = sql_query($sql);

$compPacked = []; // 완전포장
$unPacked = []; // 단일포장
$joinPacked = []; // 합포추천
$dirPacked = []; // 위탁상품
while($row = sql_fetch_array($result)) {

  $ct_id = $row['ct_id'];

  $box_size = explode(chr(30), $row['it_box_size']);
  list($width, $length, $depth) = $box_size;

  $name = $row['it_name'];
  if($name != $row['ct_option']) {
    $name .= " ({$row['ct_option']})";
  }
  $ct_qty = $row['ct_qty'] - $row['ct_stock_qty'];

  // 위탁배송인 경우
  if($row['ct_is_direct_delivery']) {
    $dirPacked[$ct_id] = array(
      'name' => $name,
      'qty' => $ct_qty
    );
    continue;
  }

  // 상품 규격 입력 안되어있으면 단일포장
  if(!($width && $length && $depth)) {
    $unPacked[$ct_id] = array(
      'name' => $name,
      'qty' => $ct_qty
    );
    continue;
  }

  // cm -> mm 변환, 소수점 올림
  $width = (int) ceil($width * 10);
  $length = (int) ceil($length * 10);
  $depth = (int) ceil($depth * 10);

  if($row['it_delivery_cnt'] > 1) {
    $comp_qty = (int) floor( $ct_qty / $row['it_delivery_cnt'] );
    $ct_qty = $ct_qty % $row['it_delivery_cnt'];

    if($comp_qty > 0) {
      $compPacked[$ct_id] = array(
        'name' => $name . " ({$row['it_delivery_cnt']}개)",
        'qty' => $comp_qty
      );
    }
  }

  if($ct_qty > 0)
    $packer->addItem(new EroumItem($name, $ct_id, $width, $length, $depth, 0, false), $ct_qty);
}

$packedBoxes = $packer->pack();

foreach($packedBoxes as $packedBox) {
  $boxType = $packedBox->getBox();

  $items = [];
  $packedItems = $packedBox->getItems();
  foreach ($packedItems as $packedItem) {
    $ct_id = $packedItem->getItem()->getCtId();
    $name = $packedItem->getItem()->getDescription();
    if(!$items[$ct_id])
      $items[$ct_id] = array(
        'name' => $name,
        'qty' => 0
      );
    $items[$ct_id]['qty']++;
  }

  // 박스에 단일상품밖에 없으면 단일포장
  if(count($items) === 1) {
    foreach($items as $ct_id => $item) {
      $unPacked[$ct_id] = array(
        'name' => $item['name'],
        'qty' => $item['qty']
      );
    }
  } else {
    $joinPacked[$boxType->getReference()] = $items;
  }
}

// 포장 불가능한 상품은 단일상품에 추가
$unpackedItems = $packer->getUnpackedItems();
foreach($unpackedItems as $unpackedItem) {
  $ct_id = $packedItem->getItem()->getCtId();
  $name = $packedItem->getItem()->getDescription();
  if(!$unPacked[$ct_id])
    $unPacked[$ct_id] = array(
      'name' => $name,
      'qty' => 0
    );

  $unPacked[$ct_id]['qty']++;
}

$ret = '';

if($compPacked) {
  $ret .= '[완전포장]' . PHP_EOL;
  foreach($compPacked as $ct_id => $item) {
    $ret .= "{$item['name']} * {$item['qty']}" . PHP_EOL;
  }
  $ret .= PHP_EOL;
}

if($unPacked) {
  $ret .= '[단일상품]' . PHP_EOL;

  foreach($unPacked as $ct_id => $item) {
    $ret .= "{$item['name']} * {$item['qty']}" . PHP_EOL;
  }

  $ret .= PHP_EOL;
}

if($joinPacked) {
  $ret .= '[합포추천]' . PHP_EOL;
  foreach($joinPacked as $box => $items) {
    $ret .= "● {$box}" . PHP_EOL;

    foreach($items as $ct_id => $item) {
      $ret .= "{$item['name']} * {$item['qty']}" . PHP_EOL;
    }

    $ret .= PHP_EOL;
  }
}

if($dirPacked) {
  $ret .= '[위탁상품]' . PHP_EOL;
  foreach($dirPacked as $ct_id => $item) {
    $ret .= "{$item['name']} * {$item['qty']}" . PHP_EOL;
  }
  $ret .= PHP_EOL;
}

if(!$ret) {
  json_response(500, '합포가 가능한 상품이 없습니다.');
}

json_response(200, 'OK', array(
  'html' => $ret,

  'compPacked' => $compPacked, // 완전포장 { ct_id: { name, qty } }
  'unPacked' => $unPacked, // 단일포장 { ct_id: { name, qty } }
  'joinPacked' => $joinPacked, //합포추천 { box: { ct_id: { name, qty } } }
  'dirPacked' => $dirPacked, //위탁상품 { ct_id: { name, qty } }
));
