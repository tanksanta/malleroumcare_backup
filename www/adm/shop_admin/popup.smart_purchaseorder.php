<?php
$sub_menu = '400480';

include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

$g5["title"] = "스마트 발주";

$sql = "
  SELECT *
    FROM 
      (SELECT
        it.it_purchase_order_partner,
        it.it_id,
        it.it_name,
        it.it_option_subject,
        io.io_type,
        io.io_no, 
        io.io_id, 
        io.io_price, 
        ws.ws_option,
        it.it_purchase_order_price,
        it.it_purchase_order_min_qty,
        it.it_purchase_order_unit,
        IFNULL(sum(ws.ws_qty), '0') AS sum_ws_qty,
        CASE
          WHEN io.io_stock_manage_min_qty IS NOT NULL AND io.io_stock_manage_min_qty > 0
            THEN io.io_stock_manage_min_qty 
          WHEN it.it_stock_manage_min_qty IS NOT NULL AND it.it_stock_manage_min_qty > 0
            THEN it.it_stock_manage_min_qty
          ELSE
            IFNULL(ROUND((SELECT sum(ct_qty) FROM g5_shop_cart
                  WHERE (ct_time >= DATE_FORMAT(CONCAT(SUBSTR(NOW() - INTERVAL 3 MONTH, 1 ,8), '01'), '%Y-%m-%d 00:00:00') AND
                    ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 MONTH), '%Y-%m-%d 23:59:59'))
                  AND ct_status IN ('주문', '입금', '준비', '출고준비', '배송', '완료')
                  AND it_id = it.it_id AND io_id = IFNULL(io.io_id, '')) / 3 * 0.5), 0)
        END AS safe_min_stock_qty,
        CASE
          WHEN io.io_stock_manage_max_qty IS NOT NULL AND io.io_stock_manage_max_qty > 0
            THEN io.io_stock_manage_max_qty 
          WHEN it.it_stock_manage_max_qty IS NOT NULL AND it.it_stock_manage_max_qty > 0
            THEN it.it_stock_manage_max_qty
          ELSE
            0
        END AS safe_max_stock_qty,
        IFNULL(ROUND((SELECT sum(ct_qty) FROM g5_shop_cart
          WHERE (ct_time >= DATE_FORMAT(CONCAT(SUBSTR(NOW() - INTERVAL 3 MONTH, 1 ,8), '01'), '%Y-%m-%d 00:00:00') AND
                ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 MONTH), '%Y-%m-%d 23:59:59'))
                AND ct_status IN ('주문', '입금', '준비', '출고준비', '배송', '완료')
                AND it_id = it.it_id AND io_id = IFNULL(io.io_id, '')) / 3), 0) AS sum_ct_qty_3month,
        IFNULL((SELECT sum(ct_qty) FROM g5_shop_cart 
          WHERE (ct_time >= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 31 DAY), '%Y-%m-%d 00:00:00') AND
                ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 DAY), '%Y-%m-%d 23:59:59'))
                AND ct_status IN ('주문', '입금', '준비', '출고준비', '배송', '완료')
                AND it_id = it.it_id AND io_id = IFNULL(io.io_id, '')), 0) AS sum_ct_qty_1month,
        IFNULL((SELECT sum(ct_qty) FROM g5_shop_cart 
          WHERE (ct_time >= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 2 DAY), '%Y-%m-%d 00:00:00') AND
                ct_time <= DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 DAY), '%Y-%m-%d 23:59:59'))
                AND ct_status IN ('주문', '입금', '준비', '출고준비', '배송', '완료')
                AND it_id = it.it_id AND io_id = IFNULL(io.io_id, '')), 0) AS sum_ct_qty_1day
      FROM 
        g5_shop_item it
        LEFT JOIN (SELECT * FROM g5_shop_item_option WHERE io_type = '0' AND io_use = '1') AS io ON it.it_id = io.it_id
        LEFT JOIN (SELECT * FROM warehouse_stock WHERE ws_del_yn = 'N') AS ws ON (it.it_id = ws.it_id AND IFNULL(io.io_id, '')= ws.io_id)
      GROUP BY it.it_id, io.io_id) AS t
  WHERE (sum_ws_qty <= safe_min_stock_qty) AND sum_ws_qty != 0 AND safe_min_stock_qty != 0
";

$result = sql_query($sql);

$total_count = sql_num_rows($result);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>스마트 발주</title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script src="/js/barcode_utils.js"></script>
  <link type="text/css" rel="stylesheet" href="/thema/eroumcare/assets/css/font.css">
  <link type="text/css" rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/flex.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
      outline: none;
    }

    html, body {
      width: 100%;
      font-family: "Noto Sans KR", sans-serif;
    }

    body {
      padding-top: 60px;
      padding-bottom: 70px;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    ul, li {
      list-style: none;
    }

    button {
      border: 0;
      font-family: "Noto Sans KR", sans-serif;
      cursor: pointer;
    }

    input {
      font-family: "Noto Sans KR", sans-serif;
    }

    /* 고정 상단 */
    #popupHeaderTopWrap {
      position: fixed;
      width: 100%;
      height: 60px;
      left: 0;
      top: 0;
      z-index: 10;
      background-color: #333;
      padding: 0 20px;
    }

    #popupHeaderTopWrap:after {
      display: block;
      content: '';
      clear: both;
    }

    #popupHeaderTopWrap > div {
      height: 100%;
      line-height: 60px;
    }

    #popupHeaderTopWrap > .title {
      float: left;
      font-weight: bold;
      color: #FFF;
      font-size: 22px;
    }

    #popupHeaderTopWrap > .close {
      float: right;
    }

    #popupHeaderTopWrap > .close > a {
      color: #FFF;
      font-size: 40px;
      top: -2px;
    }

    /* 고정 하단 */
    #popupFooterBtnWrap {
      position: fixed;
      width: 100%;
      height: 70px;
      background-color: #000;
      bottom: 0;
      z-index: 10;
    }

    #popupFooterBtnWrap > button {
      font-size: 18px;
      font-weight: bold;
    }

    #popupFooterBtnWrap > .savebtn {
      float: left;
      width: 75%;
      height: 100%;
      background-color: #000;
      color: #FFF;
    }

    #popupFooterBtnWrap > .cancelbtn {
      float: right;
      width: 25%;
      height: 100%;
      color: #666;
      background-color: #DDD;
    }

    #popupHeaderTopWrap {
      background-color: #fff;
    }

    #popupHeaderTopWrap .title {
      color: #000;
      width: 100%;
      border-bottom: 1px solid #bfbfbf;
    }

    /* 설명 */
    #description {
      margin-top: 20px;
    }

    #description ul {
      margin-left: 20px;
      font-size: 14px;
    }

    #description ul, #description li {
      list-style: circle;
    }

    /* 테이블 */
    #itemTable {
      margin-top: 25px;
    }

    #itemTable p {
      font-size: 16px;
      font-weight: bold;
      margin-bottom: 10px;
    }

    #itemTable table {
      width: 100%;
      font-size: 14px;
      border-spacing: 0;
    }

    #itemTable table th {
      border-top: 1px solid #bfbfbf;
      border-bottom: 1px solid #bfbfbf;
      padding: 10px 0;
    }

    #itemTable table td {
      text-align: center;
      padding: 10px 0;
    }

    #itemTable .left {
      text-align: left;
    }

    #itemTable .purchaseQty {
      font-size: 16px;
      font-weight: bold;
      color: #000;
    }

    #itemTable .purchaseQty .qty button {
      width: 28px;
      font-size: 16px;
      font-weight: bold;
      border: 1px solid #bfbfbf;
      background: #f2f2f2;
      color: #000;
    }

    #itemTable .purchaseQty .qty input {
      width: 40px;
      height: 25px;
      font-size: 14px;
      border-radius: 0;
      border: 1px solid #bfbfbf;
      border-right: 0;
      border-left: 0;
      text-align: center;
    }

    #itemTable .purchaseQty .qty input[type="number"]::-webkit-outer-spin-button,
    #itemTable .purchaseQty .qty input[type="number"]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    #itemTable .purchaseQty > button {
      border: 1px solid #000;
      padding: 4px 10px;
      font-size: 15px;
      background: #fff;
    }
  </style>
</head>

<body>

<!-- 고정 상단 -->
<div style="padding: 0 20px;">
  <div id="popupHeaderTopWrap">
    <div class="title"><?php echo $g5["title"] ?></div>
  </div>

  <div id="description">
    <ul>
      <li>스마트 발주는 안전하게 출고를 하기 위해 보유해야 하는 재고를 자동으로 발주하는 기능입니다.</li>
      <li>계산방법 : 주문필요수량 = 최대보유수량 - 현재 보유 수량</li>
    </ul>
  </div>

  <div id="itemTable">
    <p>총 발주 수량 <?php echo $total_count ?>개</p>
    <table>
      <thead>
        <tr>
          <th width="110px" class="left">물품공급파트너</th>
          <th width="180px">상품</th>
          <th>옵션</th>
          <th width="85px">창고재고</th>
          <th width="85px">평균출고</th>
          <th width="85px">안전재고</th>
          <th width="155px">실시간평균판매</th>
          <th width="100px">주문수량</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = sql_fetch_array($result)) { ?>
        <tr>
          <input type="hidden" name="partner_id" value="<?php echo $row['it_purchase_order_partner'] ?>">
          <input type="hidden" name="it_id" value="<?php echo $row['it_id'] ?>">
          <input type="hidden" name="it_name" value="<?php echo $row['it_name'] ?>">
          <input type="hidden" name="io_no" value="<?php echo $row['io_no'] ?>">
          <input type="hidden" name="io_id" value="<?php echo $row['io_id'] ?>">
          <input type="hidden" name="io_price" value="<?php echo $row['io_price'] ?>">
          <input type="hidden" name="it_price" value="<?php echo $row['it_purchase_order_price'] ?>">
          <input type="hidden" name="it_purchase_order_min_qty" value="<?php echo $row['it_purchase_order_min_qty'] ?>">
          <input type="hidden" name="it_purchase_order_unit" value="<?php echo $row['it_purchase_order_unit'] ?>">

          <td class="partner">
            <?php echo $row['it_purchase_order_partner'] ?>
          </td>
          <td>
            <?php echo $row['it_name'] ?>
          </td>
          <td>
            <?php
            $option = '';
            $option_br = '';
            if ($row['io_type']) {
              $opt = explode(chr(30), $row['io_id']);
              if ($opt[0] && $opt[1])
                $option .= $opt[0] . ' : ' . $opt[1];
            } else {
              $subj = explode(',', $row['it_option_subject']);
              $opt = explode(chr(30), $row['io_id']);
              for ($k = 0; $k < count($subj); $k++) {
                if ($subj[$k] && $opt[$k]) {
                  $option .= $option_br . $subj[$k] . ' : ' . $opt[$k];
                  $option_br = '<br>';
                }
              }
            }
            echo $option
            ?>
          </td>
          <td>
            <?php echo $row['sum_ws_qty'] ?>
          </td>
          <td>
            <?php echo $row['sum_ct_qty_3month'] ?>
          </td>
          <td>
            <?php echo $row['safe_min_stock_qty'] ?>
          </td>
          <td>
            <?php echo "월 {$row['sum_ct_qty_1month']}개 / 일 {$row['sum_ct_qty_1day']}개" ?>
          </td>
          <td>
            <?php
            $purchase_qty = 0;
            $max_qty = intval($row['safe_max_stock_qty']);
            $min_qty = intval($row['safe_min_stock_qty']);
            $current_qty = intval($row['sum_ws_qty']);
            $order_min_qty = intval($row['it_purchase_order_min_qty']); // 최소주문량
            $order_unit = intval($row['it_purchase_order_unit']); // 최소주문단위
            $debug_type = 0;

            // 최대 재고 있을 경우
            if ($max_qty > 0 && $current_qty < $max_qty) {
              $not_enough_qty = $max_qty - $current_qty;
              $debug_type = 1;
            } else if ($current_qty < $min_qty) {
              $not_enough_qty = $min_qty - $current_qty;
              $debug_type = 2;
            }

            if ($not_enough_qty > 0) {
              // 최소구매수량 맞추기
              if ($order_min_qty > 0 && $not_enough_qty < $order_min_qty) {
                $not_enough_qty += $order_min_qty - $not_enough_qty;
                $debug_type += 10;
              }

              // 구매단위 맞추기
              if ($order_unit > 0 && $not_enough_qty % $order_unit != 0) {
                $not_enough_qty += $order_unit - ($not_enough_qty % $order_unit);
                $debug_type += 100;
              }

              $purchase_qty = $not_enough_qty;
            }
            ?>
            <div class="purchaseQty flex-row align-center justify-center">
              <div class="flex-row align-center qty" style="margin-left: 5px">
                <button onclick="setNumberPurchaseQty(this, 'minus')">-</button><input data-type="<?php echo $debug_type ?>" type="number" class="qty_input" name="qty_input" value="<?php echo $purchase_qty ?>"><button onclick="setNumberPurchaseQty(this, 'plus')">+</button>
              </div>
            </div>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>



<!-- 고정 하단 -->
<div id="popupFooterBtnWrap">
  <button type="button" class="savebtn" onclick="saveData();">저장</button>
  <button type="button" class="cancelbtn" onclick="window.close();">취소</button>
</div>

<script>
  var CT_QTY = Number('<?= $ct["ct_qty"] ?>');

  function getUrlParams() {
    var params = {};

    window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,
      function(str, key, value) {
        params[key] = decodeURI(value);
      }
    );

    return params;
  }

  function setNumberPurchaseQty(x, param) {
    var targetNode = $(x).parent().find('.qty_input');
    var currentVal = Number(targetNode.val());
    var maxVal = 99999;
    var minVal = 0;

    if (param === 'plus') {
      if (currentVal < maxVal)
        targetNode.val(++currentVal);
    } else if (param === 'minus') {
      if (currentVal > -minVal)
        targetNode.val(--currentVal);
    } else { // 숫자 입력
      targetNode.val(param);
    }
  }

  function saveData() {
    var obj = {};
    var obj2;

    $('#itemTable tbody tr').each(function(k, v) {
      var partner_id = $(this).find('input[name="partner_id"]').val();
      var it_id = $(this).find('input[name="it_id"]').val();
      var it_name = $(this).find('input[name="it_name"]').val();
      var io_no = $(this).find('input[name="io_no"]').val();
      var io_id = $(this).find('input[name="io_id"]').val();
      var io_price = $(this).find('input[name="io_price"]').val();
      var it_price = $(this).find('input[name="it_price"]').val();
      var qty = $(this).find('input[name="qty_input"]').val();
      var it_purchase_order_min_qty = $(this).find('input[name="it_purchase_order_min_qty"]').val();
      var it_purchase_order_unit = $(this).find('input[name="it_purchase_order_unit"]').val();

      if (!partner_id) {
        partner_id = '*NULL';
      }

      obj2 = {};
      obj2['partner_id'] = partner_id;
      obj2['it_id'] = it_id;
      obj2['it_name'] = it_name;
      obj2['io_no'] = io_no;
      obj2['io_id'] = io_id;
      obj2['io_price'] = io_price;
      obj2['it_price'] = it_price;
      obj2['qty'] = qty;
      obj2['it_purchase_order_min_qty'] = it_purchase_order_min_qty;
      obj2['it_purchase_order_unit'] = it_purchase_order_unit;

      if (!(partner_id in obj)) {
        obj[partner_id] = [];
      }
      obj[partner_id].push(obj2);
    });
    console.log(obj);

    $(opener.document).find('#smartForm input[name="smart_purchase_data"]').val(JSON.stringify(obj));
    $(opener.document).find('#smartForm').submit();
    window.close();
  }
</script>
</body>