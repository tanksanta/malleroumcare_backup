<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" media="screen">', 0);

// 목록헤드
if(isset($wset['chead']) && $wset['chead']) {
    add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/head/'.$wset['chead'].'.css" media="screen">', 0);
    $head_class = 'list-head';
} else {
    $head_class = (isset($wset['ccolor']) && $wset['ccolor']) ? 'tr-head border-'.$wset['ccolor'] : 'tr-head border-black';
}

// 헤더 출력
if($header_skin)
    include_once('./header.php');

if ( is_array($item) && count($item) ) {
    $ct = sql_fetch("SELECT * FROM g5_shop_cart WHERE ct_id = '{$item[0]['ct_id']}'");
    $od_id = $ct['od_id'];
}


//쇼핑몰에서 설정한 일정한 금액 이상이 넘을경우 배송비 무료
$sql_d = "SELECT `de_send_conditional` FROM `g5_shop_default`";
$result_d = sql_fetch($sql_d);

if($tot_sell_price - $tot_sell_discount >=$result_d['de_send_conditional']){
    $tot_price=$tot_price-$send_cost;
    $send_cost=0;
}
?>

<script src="<?php echo $skin_url;?>/shop.js"></script>

<!-- Modal -->
<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <div id="mod_option_box"></div>
      </div>
    </div>
  </div>
</div>

<form name="frmcartlist" id="sod_bsk_list" method="post" action="<?php echo $action_url; ?>" class="form" role="form">
    <input type="hidden" name="only_recipient" value="0" />
    <div class="table-responsive">
		<div class="sub_section_tit"><?=($_SESSION['recipient']['penId']=="")?($member['mb_entNm'] ?: $member['mb_name']):$_SESSION['recipient']['penNm']."님";?> 장바구니</div>
        <table class="div-table table bsk-tbl bg-white">
        <tbody>
        <tr class="<?php echo $head_class;?>">
            <th scope="col">
                <label for="ct_all" class="sound_only">상품 전체</label>
                <span><input  type="checkbox" name="ct_all" value="1" id="ct_all" checked="checked"></span>
            </th>
            <th scope="col"><span>이미지</span></th>
            <th scope="col"><span>상품명</span></th>
            <th scope="col"><span>총수량</span></th>
            <th scope="col"><span>상품금액</span></th>
            <th scope="col"><span>할인가</span></th>
            <th scope="col"><span>소계</span></th>
            <!-- <th scope="col"><span>포인트</span></th> -->
            <th scope="col"><span class="last">배송비</span></th>
        </tr>
        <?php for($i=0;$i < count($item); $i++) { ?>
            <tr<?php echo ($i == 0) ? ' class="tr-line"' : '';?>>
                <td class="text-center">
                    <label for="ct_chk_<?php echo $i; ?>" class="sound_only">상품</label>
                    <input class="check_cart" data-target="<?=$item[$i]['sell_price']?>" data-target2="
                    <?php
                    if($item[$i]["prodSupYn"] == "N"){
                        echo "0";
                    }else{
                        echo get_item_sendcost($item[$i]['it_id'], $item[$i]['ct_price'], $item[$i]['qty'],$s_cart_id);
                    }
                    ?>
                    " type="checkbox" name="ct_chk[<?php echo $i; ?>]" value="1" id="ct_chk_<?php echo $i; ?>" checked="checked">
                </td>
                <td class="text-center">
                    <div class="item-img">
                        <img src="/data/item/<?=$item[$i]['thumbnail']?>" onerror="this.src = '/shop/img/no_image.gif';" style="width: 100px; height: 100px;">
                        <!-- <div class="item-type">
                            <?php echo $item[$i]['pt_it']; ?>
                        </div> -->
                    </div>
                </td>
                <td>
                    <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $item[$i]['it_id']; ?>">
                    <input type="hidden" name="it_name[<?php echo $i; ?>]" value="<?php echo get_text($item[$i]['it_name']); ?>">
                    <a href="./item.php?it_id=<?php echo $item[$i]['it_id'];?>">
                        <b><?php echo stripslashes($item[$i]['it_name']); ?></b>
                    <?php if($item[$i]["prodSupYn"] == "N"){ ?>
                        <b class="sup_n" style="position: relative; display: inline-block; width: 50px; height: 20px; line-height: 20px; top: -1px; border-radius: 5px; text-align: center; color: #FFF; font-size: 11px; background-color: #DC3333;">비유통</b>
                    <?php } ?>
                    </a>
                    <?php if($item[$i]['it_options']) { ?>
                        <div class="well well-sm"><?php echo $item[$i]['it_options'];?></div>
                        <button type="button" class="btn btn-primary btn-sm btn-block mod_options">선택사항수정</button>
                    <?php } ?>
                </td>
                <td class="text-center"><?php echo number_format($item[$i]['qty']); ?></td>
                <td class="text-right"><?php echo number_format($item[$i]['ct_price']); ?></td>
                <td class="text-right"><?php echo number_format($item[$i]['sell_discount']); ?></td>
                <td class="text-right"><span id="sell_price_<?php echo $i; ?>"><?php echo number_format($item[$i]['sell_price']); ?></span></td>
                <!-- <td class="text-right"><?php echo number_format($item[$i]['point']); ?></td> -->
                <td class="text-center"><?php echo $item[$i]['ct_send_cost']; ?></td>
            </tr>
        <?php } ?>
        <?php if ($i == 0) { ?>
            <tr><td colspan="8" class="text-center text-muted"><p style="padding:50px 0;">장바구니가 비어 있습니다.</p></td></tr>
        <?php } ?>
        </tbody>
        </table>
    </div>

    <?php //if ($tot_price > 0 || $send_cost > 0) { ?>
        <div class="well bg-white">
            <div class="row">
                <?php //if ($send_cost > 0) { // 배송비가 0 보다 크다면 (있다면) ?>
                    <!-- <div class="col-xs-6">배송비 정보</div>
                    <div class="col-xs-6 text-right">
                        <strong id="delivery_pirce"><?php echo number_format($send_cost); ?> 원 (*10만원이상 무료배송)</strong>
                        <strong id="delivery_pirce">10만원이상 무료배송</strong>
                    </div> -->
                <?php //} ?>
                <?php //if ($tot_price > 0) { ?>
                    <div class="col-xs-6"> 총 상품금액 </div>
                    <div class="col-xs-6 text-right">
                        <strong id="total_price"><?php echo number_format($tot_price); ?> 원 <!-- / <?php echo number_format($tot_point); ?> 점 --></strong>
                    </div>
                <?php //} ?>
            </div>
            <span>*10만원 이상 무료배송되며, 비유통상품은 주문시 결제금액에 포함되지 않습니다.</span>
        </div>
    <?php //} ?>

    <div style="margin-bottom:15px; text-align:center;">
        <?php if ($i == 0) { ?>
            <!-- <a href="<?php echo G5_SHOP_URL; ?>/" class="btn btn-color btn-sm">계속하기</a> -->
        <?php } else { ?>
            <input type="hidden" name="url" value="./orderform.php">
            <input type="hidden" name="records" value="<?php echo $i; ?>">
            <input type="hidden" name="act" value="">
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    <div class="form-group">
                        <button type="button" onclick="return form_check('buy');" class="btn btn-black btn-block btn-lg"> 주문하기</button> 
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    <div class="btn-group btn-group-justified">
                        <!-- <div class="btn-group">
                            <a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=<?php echo $continue_ca_id; ?>" class="btn btn-white btn-block btn-sm">계속하기</a>
                        </div> -->
                        <div class="btn-group">
                            <button type="button" onclick="return form_check('seldelete');" class="btn  btn-white btn-block btn-sm"> 선택삭제</button>
                        </div>
                        <div class="btn-group">
                            <button type="button" onclick="return form_check('alldelete');" class="btn btn-white btn-block btn-sm">전체삭제</button>
                        </div>
                    </div>
                    <?php if ($naverpay_button_js) { ?>
                        <div style="margin-top:20px;"><?php echo $naverpay_request_js.$naverpay_button_js; ?></div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <style>
    .send_estimate_div {
        margin:20px auto;
        max-width:585px;
    }
    #send_estimate {
        border:1px solid #0e5ea8;
        font-size:14px;
        display:block;
        text-align:center;
        line-height:40px;
        font-weight:bold;
    }
    </style>

    <div class="send_estimate_div">
        <a id="send_estimate">견적서 출력</a>
    </div>

<style>
.pop_sup {
    position: fixed;
    left: 50%;
    top: 50%;
    -webkit-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
    z-index:99;
    width: 320px;
    min-height: 220px;
    background-color: white;
    border: 1px solid #f5ab81;
    -webkit-box-shadow: 2px 2px 3px 2px rgba(0,0,0,0.2);
    box-shadow: 2px 2px 3px 2px rgba(0,0,0,0.2);
    display: none;
    padding: 15px;
}
.pop_sup .close {
	position:absolute;
	top: 15px;
	right: 15px;
	color: #b0b0b0;
	font-size: 1.5em;
}
.pop_sup h3 {
    text-align: left;
    color: #f27935;
    font-size: 13px;
    margin: 0;
    padding-bottom: 20px;
    font-weight: bold;
}
.pop_sup p {
    text-align: center;
    font-size: 13px;
    padding-top: 20px;
}
.pop_sup .sub_n_btn {
    background-color: #f08606;
    color: white;
    font-size: 14px;
}
.pop_sup .recipient_btn {
    margin-top: 10px;
    display: block;
    text-align: center;
    text-decoration: underline;
}
</style>

    <div class="pop_sup">
        <i class="fa fa-close fa-lg close"></i>
        <h3>주문알림</h3>
        <p>
        비유통 품목은 재고등록 후<br>
        수급자 계약서 작성만 가능합니다.
        </p>
        <button type="button" class="btn btn-block btn-lg sub_n_btn">비유통 품목 제외 후 상품주문</button>
        <?php /* <a href="#" class="btn recipient_btn" onclick="return form_check('sup_recipient');">수급자 계약서 작성하기</a> */?>
    </div>

</form>

<?php if($setup_href) { ?>
    <p class="text-center">
        <a class="btn btn-color btn-sm win_memo" href="<?php echo $setup_href;?>">
            <i class="fa fa-cogs"></i> 스킨설정
        </a>
    </p>
<?php } ?>

<script>

    $('.pop_sup .close').click(function() {
        $('.pop_sup').fadeOut();
    });

    $('.sub_n_btn').click(function() {
        // 비유통 상품 체크 해제
        var parents = $('input.check_cart:checked').closest('tr');
        for(var i=0; i<parents.length; i++) {
            if ($($(parents)[i]).find('.sup_n').length) {
                $($(parents)[i]).find('input.check_cart').prop("checked", false);
            }
        }

        form_check('buy');
    })

    //클릭시 총 상품금액 변경
    $(".check_cart").click(function() {
        var check_cart = $( '.check_cart' ).get();
        var price=0;
        var delivery=0;
        for ( var i = 0; i < check_cart.length; i++) {
            console.log(check_cart[i].checked);
            if(check_cart[i].checked==true){
                price = price+parseInt($(check_cart[i]).data('target'));
                delivery = delivery+parseInt($(check_cart[i]).data('target2'));
            }
        }
        if(price >= parseInt(<?=$result_d['de_send_conditional']?>)){delivery=0;}
        $("#delivery_pirce").html(number_format(delivery)+" 원 (*10만원이상 무료배송)");
        $("#total_price").html(number_format(price+delivery)+" 원");
    });

    //콤마찍기
    function number_format(num){
        var regexp = /\B(?=(\d{3})+(?!\d))/g;
        return num.toString().replace(regexp, ',');
    }

    $(function() {
        var close_btn_idx;

        // 선택사항수정
        $(".mod_options").click(function() {
            var it_id = $(this).closest("tr").find("input[name^=it_id]").val();
            var $this = $(this);
            close_btn_idx = $(".mod_options").index($(this));
            $('#cartModal').modal('show');
            $.post(
                "./cartoption.php",
                { it_id: it_id },
                function(data) {
                    $("#mod_option_form").remove();
                    //$this.after("<div id=\"mod_option_frm\"></div>");
                    $("#mod_option_box").html(data);
                    price_calculate();
                }
            );
        });

        // 모두선택
        $("input[name=ct_all]").click(function() {
            if($(this).is(":checked"))
                $("input[name^=ct_chk]").attr("checked", true);
            else
                $("input[name^=ct_chk]").attr("checked", false);

            //체크시 배송비 및 총 상품금액 반영
            var check_cart = $( '.check_cart' ).get();
            var price=0;
            var delivery=0;
            for ( var i = 0; i < check_cart.length; i++) {
                console.log(check_cart[i].checked);
                if(check_cart[i].checked==true){
                    price = price+parseInt($(check_cart[i]).data('target'));
                    delivery = delivery+parseInt($(check_cart[i]).data('target2'));
                }
            }
            if(price >= parseInt(<?=$result_d['de_send_conditional']?>)){delivery=0;}
            $("#delivery_pirce").html(number_format(delivery)+" 원 (*10만원이상 무료배송)");
            $("#total_price").html(number_format(price+delivery)+" 원");
        });

        // 옵션수정 닫기
        $(document).on("click", "#mod_option_close", function() {
            $('#cartModal').modal('hide');
            //$("#mod_option_frm").remove();
            $("#mod_option_form").remove();
            $(".mod_options").eq(close_btn_idx).focus();
        });
        $("#win_mask").click(function () {
            $('#cartModal').modal('hide');
            //$("#mod_option_frm").remove();
            $("#mod_option_form").remove();
            $(".mod_options").eq(close_btn_idx).focus();
        });

        // 견적서 출력
        $("#send_estimate").click(function() {
            var send_estimate_pop;
            var od_id = '<?php echo $od_id; ?>';
            if(od_id == ''){
                alert("장바구니에 담긴 상품이 없습니다.");
                return false;
            }
            var send_cost = '<?php echo $send_cost ? $send_cost : 0; ?>';
            send_estimate_pop = window.open('<?php echo G5_SHOP_URL; ?>/pop.estimate.php?od_id=' + od_id + '&send_cost=' + send_cost, "send_estimate", "width=730, height=800, resizable = no, scrollbars = no");
        });

    });

    function fsubmit_check(f) {
        if($("input[name^=ct_chk]:checked").size() < 1) {
            alert("구매하실 상품을 하나이상 선택해 주십시오.");
            return false;
        }

        return true;
    }

    function form_check(act) {
        var f = document.frmcartlist;
        var cnt = f.records.value;

        if (act == "buy")
        {
            if($("input[name^=ct_chk]:checked").size() < 1) {
                alert("주문하실 상품을 하나이상 선택해 주십시오.");
                return false;
            }

            // 비유통 상품 있는지 체크
            var parents = $('input.check_cart:checked').closest('tr');
            for(var i=0; i<parents.length; i++) {
                if ($($(parents)[i]).find('.sup_n').length) {
                    $('.pop_sup').fadeIn();
                    return false;
                }
            }

            f.act.value = act;
            f.submit();
        }
        // 무조건 수급자 주문
        else if (act == "sup_recipient")
        {
            f.only_recipient.value = '1';
            f.act.value = 'buy';
            f.submit();
        }
        else if (act == "alldelete")
        {
            f.act.value = act;
            f.submit();
        }
        else if (act == "seldelete")
        {
            if($("input[name^=ct_chk]:checked").size() < 1) {
                alert("삭제하실 상품을 하나이상 선택해 주십시오.");
                return false;
            }

            f.act.value = act;
            f.submit();
        }

        return true;
    }
</script>

