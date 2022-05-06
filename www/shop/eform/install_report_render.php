<?php
include_once("./_common.php");

$od_id = get_search_string($_GET['od_id']);
$mb_id = get_search_string($_GET['mb_id']);

$sql = "
    SELECT * FROM
        partner_install_report
    WHERE
        od_id = '$od_id' and
        mb_id = '$mb_id' and
        ir_sign_url <> '' and
        ir_file_url = ''
";
$report = sql_fetch($sql);

if(!$report)
    alert('유효하지 않은 요청입니다.');

$sql = "
    SELECT
        o.*,
        m.mb_name,
        mb_giup_btel
    FROM
        g5_shop_order o
    LEFT JOIN
        g5_member m ON o.mb_id = m.mb_id
    WHERE
        od_id = '$od_id'
";
$od = sql_fetch($sql);

if(!$od)
    alert('주문이 존재하지 않습니다.');

$sql = "
    SELECT * FROM
        g5_shop_cart
    WHERE
        od_id = '$od_id' and
        ct_direct_delivery_partner = '$mb_id' and
        ct_status IN('준비', '출고준비', '배송', '완료')
    ORDER BY
        ct_id ASC
";
$result = sql_query($sql);

$total_qty = 0;
$carts = [];
while($ct = sql_fetch_array($result)) {
    $ct['it_name'] .= $ct['ct_option'] && $ct['ct_option'] != $ct['it_name'] ? " ({$ct['ct_option']})" : '';

    // 바코드 정보 가져오기
    $sto_id = [];

    foreach(array_filter(explode('|', $ct['stoId'])) as $id) {
        $sto_id[] = $id;
    }

    $stock_result = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(
        'stoId' => implode('|', $sto_id)
    ));

    $barcodes = [];
    if($stock_result['data']) {
      foreach($stock_result['data'] as $data) {
        $barcodes[] = $data['prodBarNum'];
      }
    }

    $ct['barcode'] = $barcodes;

    $total_qty += $ct['ct_qty'];

    $carts[] = $ct;
}

// 문서명 (사업소명_주문번호_설치결과보고서)
$title = "{$od['mb_name']}_{$od_id}_설치결과보고서";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="initial-scale=1.0,user-scalable=1,width=device-width">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="css/default.css">
    <link rel="stylesheet" href="css/signeform.css?v=06042032">
    <link rel="stylesheet" href="css/install_report.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <style>
    body.render-eform {
         background-color: #fff;
    }

    .render-eform-body .a4 {
        width: 1240px;
        margin: 0 auto;
        background-color: #fff;
        page-break-inside: avoid;
    }

    .render-eform-body .a4 + .a4 {
         page-break-before: always;
    }
    </style>
</head>
<body class="render-eform">
<div class="render-eform-body">
    <?php
    include_once('./document/install_report.php');
    ?>
</div>
<script>
$(function() {
    var pos = {
        sign_ir_1: {
            top: 6,
            left: -35,
            width: 120,
            height: 50
        },
    };

    var state = {
        sign_ir_1: '<?=$report['ir_sign_url']?>',
    };

    function repaint() {
      // 서명
      $('.td_sign').each(function() {
        var id = $(this).data('id');
        if($('#'+id).length === 0) $(this).append('<div id="'+id+'" class="sign-wrap"></div>');

        var $wrap = $('#'+id);
        $wrap.css({
          top: pos[id].top,
          left: pos[id].left,
          width: pos[id].width,
          height: pos[id].height
        });

        // 서명이 비어있다면
        if(!state[id]) {
          $wrap.html('<button class="btn-sign" data-id="'+id+'">서명하기</button>');
        } else {
          var imageURL = state[id];
          $wrap.html('<img class="img-sign" data-id="'+id+'" src="'+imageURL+'" height="'+pos[id].height+'" alt="수급자 서명">');
        }
      });
    }

    repaint();
});
</script>
</body>
</html>
