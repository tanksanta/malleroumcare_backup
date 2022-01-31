<?php
$sub_menu = '400620';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '상품 입출고관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$it_id = get_search_string($_GET['it_id']);

$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

$option_sql = "SELECT *
  FROM
    {$g5['g5_shop_item_option_table']}
  WHERE
    it_id = '$it_id'
    and io_type = 0 -- 선택옵션
  ORDER BY
    io_no ASC
";
$option_result = sql_query($option_sql);

$options = [];
while ($option_row = sql_fetch_array($option_result)) {
  $io_value = '';
  $it_option_subjects = explode(',', $it['it_option_subject']);
  $io_ids = explode(chr(30), $option_row['io_id']);
  for($g = 0; $g < count($io_ids); $g++) {
    if ($g > 0) {
      $io_value .= ' / ';
    }
    $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
  }

  $option_row['io_value'] = $io_value;
  $options[] = $option_row;
}

if(!$it['it_id']) alert('존재하지 않는 상품입니다.');

$warehouse_list = get_warehouses();
$count_item_option = count_item_option($it_id);
?>

<style>
  #item_stock_edit_form {
    padding: 0 20px;
  }

  #item_stock_edit_form .title {
    width: 200px;
    font-weight: bold;
  }

  #item_stock_edit_form li {
    border-bottom: 1px solid #ececec;
    padding: 8px 0;
    height: 41px;
  }

  #item_stock_edit_form li,
  #item_stock_edit_form .content {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
  }

  #item_stock_edit_form input {
    border: 1px solid #d9d9d9;
  }

  #item_stock_edit_form .button_wrap {
    margin: 0 auto;
    width: 200px;
    margin-top: 25px;
  }

  #item_stock_edit_form button {
    border: 1px solid #ECECEC;
    background: #F3F3F3;
    padding: 7px 0;
    width: 90px;
    font-size: 13px;
  }

  #item_stock_edit_form button.submit {
    background: #333;
    color: #fff;
  }

  #item_stock_edit_form input {
    margin-right: 3px;
  }

  #item_stock_edit_form li.move_type {
    display: none;
  }

</style>

<form id="item_stock_edit_form">
  <ul>
    <li>
      <div class="title">상품명</div>
      <div class="content">
        <span><?php echo $it['it_name'] ?></span>
        <?php
        if ($count_item_option > 0) {
        ?>
        <select id="item_option_sel" name="item_option" style="margin-left: 10px">
          <option value="" selected>옵션명</option>
          <?php
          foreach ($options as $option) {
            echo "<option value='{$option['io_id']}|{$option['io_value']}'>{$option['io_value']}</option>";
          }
          ?>
        </select>
        <?php
        }
        ?>
      </div>
    </li>

    <li>
      <div class="title">보유재고</div>
      <div id="stock_explain" class="content">
        <?php
        if ($count_item_option > 0) {
          echo "상품 옵션을 선택해주세요.";
        } else {
          $sql = "
            SELECT
              it_name, wh_name, (SUM(ws_qty) - SUM(ws_scheduled_qty)) AS ws_qty
            FROM
              warehouse_stock ws
            WHERE
              it_id = '{$it_id}' AND ws_del_yn = 'N'
            GROUP BY wh_name
          ";
          $row = sql_fetch($sql);
          echo "{$row['wh_name']} ({$row['ws_qty']}개)";
        }
        ?>
      </div>
    </li>

    <li>
      <div class="title">분류</div>
      <div class="content">
        <label style="margin-right: 10px"><input type="radio" name="edit_type" value="stock" checked/>입출관리</label>
        <label><input type="radio" name="edit_type" value="move"/>창고 이동 상품</label>
      </div>
    </li>

    <li class="stock_type">
      <div class="title">입/출고</div>
      <div class="content">
        <label style="margin-right: 10px"><input type="radio" name="stock_abs" value="plus" checked/>입고(+)</label>
        <label><input type="radio" name="stock_abs" value="minus"/>출고(-)</label>
        <span style="margin: 0 15px">/</span>
        <input type="number" name="stock_qty" style="width: 70px;" min="1">
        <span>개</span>
      </div>
    </li>

    <li class="stock_type">
      <div class="title">창고</div>
      <div class="content">
        <select name="wh_name">
          <option value="">창고명</option>
          <?php
          foreach ($warehouse_list as $warehouse) {
            echo "<option value='{$warehouse}'>{$warehouse}</option>";
          }
          ?>
        </select>
      </div>
    </li>

    <li class="move_type">
      <div class="title">수량</div>
      <input type="number" name="move_qty" style="width: 70px;"> 개
    </li>

    <li class="move_type">
      <div class="title">출고창고</div>
      <div class="content">
        <select name="wh_name_from">
          <?php
//          foreach ($warehouse_list as $warehouse) {
//            echo "<option value='{$warehouse}'>{$warehouse}</option>";
//          }

          if ($count_item_option > 0) {
            echo '<option value="" selected>상품옵션을 선택해주세요</option>';
          } else {
            echo "<option value='{$row['wh_name']}' selected>{$row['wh_name']}</option>";
          }
          ?>

        </select>
      </div>
    </li>

    <li class="move_type">
      <div class="title">입고창고</div>
      <div class="content">
        <select name="wh_name_to">
          <?php
          if ($count_item_option > 0) {
            echo '<option value="" selected>출고창고를 선택해주세요</option>';
          } else {
            foreach ($warehouse_list as $warehouse) {
              if ($warehouse != $row['wh_name']) {
                echo "<option value='{$warehouse}'>{$warehouse}</option>";
              }
            }
          }
          ?>
        </select>
      </div>
    </li>

    <li>
      <div class="title">내용</div>
      <input type="text" name="ws_memo" style="width: 225px;">
    </li>
  </ul>

  <div class="button_wrap flex-row justify-space-between">
    <button type="button" onclick="submitForm()" class="submit">등록</button>
    <button type="button" onclick="window.history.go(-1); return false;">취소</button>
  </div>
</form>

<script>
  var IT_ID = '<?php echo $it_id ?>';
  var WAREHOUSE_LIST = <?php echo json_encode($warehouse_list) ?>;
  var LOADING = false;

  $(function () {
    $('#item_option_sel').change(function () {
      var value = $(this).val();

      if (value) {
        var returnValue = getItemStockByOption(IT_ID, value.split('|')[1]);
        var selectHtml = '';

        // 보유재고
        var stockText = [];
        // A창고 (1개), B창고(2개)
        for (var i = 0; i < returnValue.length; i++) {
          stockText.push(returnValue[i]['wh_name'] + " (" + returnValue[i]['ws_qty'] + "개)")
        }
        $('#stock_explain').text(stockText.join(', '));

        // 출고창고
        for (i = 0; i < returnValue.length; i++) {
          selectHtml += '<option value="'+ returnValue[i]['wh_name'] +'">' + returnValue[i]['wh_name'] + '</option>'
        }
        $('select[name="wh_name_from"]').html(selectHtml);
        $('select[name="wh_name_from"]').trigger('change');
      } else {
        // 보유재고
        $('#stock_explain').text('상품 옵션을 선택해주세요.');
        $('select[name="wh_name_from"]').html('<option value="" selected>상품옵션을 선택해주세요</option>');
        $('select[name="wh_name_to"]').html('<option value="" selected>출고창고를 선택해주세요</option>');
      }
    });

    $('input[name="edit_type"]').change(function () {
      // value : stock, move
      toggleEditType($(this).val());
    });

    $('select[name="wh_name_from"]').change(function () {
      var value = $(this).val();

      if (value) {
        var optionHtml = ''
        for (var i = 0; i < WAREHOUSE_LIST.length; i++) {
          if (value !== WAREHOUSE_LIST[i]) {
            optionHtml += '<option value="'+ WAREHOUSE_LIST[i] +'">' + WAREHOUSE_LIST[i] + '</option>'
          }
        }

        console.log(optionHtml)
      } else {
        var optionHtml = '<option value="" selected>출고창고를 선택해주세요</option>';
      }

      $('select[name="wh_name_to"]').html(optionHtml);
    });

    toggleEditType('stock');
  });

  function submitForm() {
    // 공통
    var edit_type = $('input[name="edit_type"]:checked').val();
    var item_option = $('select[name="item_option"]').val();
    var ws_memo = $('input[name="ws_memo"]').val();

    // 입출관리
    var stock_abs = $('input[name="stock_abs"]:checked').val();
    var stock_qty = $('input[name="stock_qty"]').val();
    var wh_name = $('select[name="wh_name"]').val();

    // 창고 이동 상품
    var move_qty = $('input[name="move_qty"]').val();
    var wh_name_from = $('select[name="wh_name_from"]').val();
    var wh_name_to = $('select[name="wh_name_to"]').val();

    var data = {};
    data['it_id'] = IT_ID;
    data['edit_type'] = edit_type;
    data['item_option'] = item_option;
    data['ws_memo'] = ws_memo;
    data['stock_abs'] = stock_abs;
    data['stock_qty'] = stock_qty;
    data['wh_name'] = wh_name;
    data['move_qty'] = move_qty;
    data['wh_name_from'] = wh_name_from;
    data['wh_name_to'] = wh_name_to;

    <?php
    if ($count_item_option > 0) {
    ?>
      if (!item_option) {
        alert('상품 옵션을 선택해주세요');
        return;
      }
    <?php
    }
    ?>

    if (edit_type === 'stock') {
      if (stock_qty <= 0) {
        alert('입/출고 수량은 1이상 값으로 입력해주세요.');
        return;
      }

      if (!wh_name) {
        alert('창고를 선택해주세요.');
        return;
      }

    } else if (edit_type === 'move') {
      if (move_qty <= 0) {
        alert('창고이동 수량은 1이상 값으로 입력해주세요.');
        return;
      }

      if (!wh_name_from) {
        alert('출고창고를 선택해주세요.');
        return;
      }

      if (!wh_name_to) {
        alert('입고창고를 선택해주세요.');
        return;
      }
    }

    if (!ws_memo || ws_memo.length == 0) {
      alert('내용을 입력해주세요');
      return;
    }

    if (LOADING) {
      alert('저장중입니다. 잠시만 기다려주세요.');
      return;
    }

    LOADING = true;

    $.ajax({
      url: 'ajax.itemstockedit.php',
      type: 'POST',
      data: data,
      dataType: 'json'
    })
    .done(function(result) {
      var data = result.data;
      alert(result.message);
      window.history.go(-1);
      return false;
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      LOADING = false;
    });
  }

  function toggleEditType(type) {
    var flexCss = 'display: -webkit-box;display: -ms-flexbox;display: flex;'

    if (type === 'stock') {
      $('li.stock_type').attr('style', flexCss);
      $('li.move_type').hide();
    } else {
      $('li.stock_type').hide();
      $('li.move_type').attr('style', flexCss);
    }
  }

  function getItemStockByOption(it_id, ws_option) {
    var returnValue = [];
    $.ajax({
      url: 'ajax.itemstock.by.option.php',
      type: 'GET',
      data: {
        it_id: it_id,
        ws_option: ws_option,
      },
      dataType: 'json',
      async: false,
    })
    .done(function(result) {
      var data = result.data;
      returnValue = data;
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });

    return returnValue;
  }
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
