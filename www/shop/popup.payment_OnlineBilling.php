<?php
    /* // */
    /* // */
    /* // */
    /* // */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* //  *  */
    /* //  *  */
    /* //  * (주)티에이치케이컴퍼 & 이로움 - [ THKcompany & E-Roum ] */
    /* //  *  */
    /* //  * Program Name : EROUMCARE Platform! = OnlineBilling Ver:0.1 */
    /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
    /* //  * Copyright (c) 2022 THKC Co,Ltd.  All rights reserved. */
    /* //  *  */
    /* //  *  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // */
    /* // */
    /* // */
    /* // */

    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */
    /* // 파일명 : /www/shop/popup.payment_OnlineBilling.php */
    /* // 파일 설명 :   온라인 결제(사업소화면) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  대금 & 미수금 결제를 위한 금액 확인용 팝업창 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

	include_once("./_common.php");


    function txt_pay_ENUM( $val ) {

        $_result = "";
      
        switch($val) {    
      
          case('phone'): $_result = "휴대폰"; break;
          case('card'): $_result = "카드"; break;
          case('bank'): $_result = "계좌이체"; break;
          case('vbank'): $_result = "가상계좌"; break;
          case('easy'): $_result = "간편"; break;
          case('easy_rebill'): $_result = "간편자동"; break;
          case('card_rebill'): $_result = "카드자동"; break;
          case('kakaopay'): $_result = "카카오페이"; break;
          case('naverpay'): $_result = "네이버페이"; break;
          case('payco'): $_result = "페이코"; break;
          case('toss'): $_result = "토스"; break;
          case('easy_card'): $_result = "간편카드"; break;
          case('easy_card_rebill'): $_result = "간편카드자동"; break;
          case('auth'): $_result = "본인인증"; break;
          case('digital_card'): $_result = "디지털카드"; break;
          case('digital_bank'): $_result = "디지털계좌이체"; break;
          case('digital_card_rebill'): $_result = "디지털카드자동"; break;
      
          default : $_result = "-"; break;
        }
      
        return $_result;
    }

    $_sql = ("  SELECT bl.*, mb.mb_thezone, mb.mb_giup_bname, mb.mb_giup_btel, mb.mb_giup_tax_email, mb.mb_giup_addr1, mb.mb_giup_addr2, mb.mb_giup_addr3
                FROM 
                    payment_billing_list bl
                LEFT JOIN
                    g5_member mb ON bl.mb_id = mb.mb_id
                WHERE bl.mb_id = '" . $member['mb_id'] . "'
                    AND bl.mb_thezone = '" . $member['mb_thezone'] . "'
                    AND bl.billing_yn = 'Y'
                    AND YEAR(bl.create_dt) = YEAR(CURRENT_DATE()) 
                    AND MONTH(bl.create_dt) = MONTH(CURRENT_DATE())
                    AND ( pay_confirm_id IS NULL OR pay_confirm_id = '' )
                    AND ( pay_confirm_receipt_id IS NULL OR pay_confirm_receipt_id = '' )
    ");
    $_sql_bl = sql_fetch($_sql);

    $_sql = ("  SELECT bl.*, par.method_symbol, par.status_locale, par.card_company, par.card_quota
                FROM 
                    payment_billing_list bl
                    LEFT OUTER JOIN
                    payment_api_request par ON par.id = (
                        SELECT MAX(id)
                        FROM payment_api_request par2
                        WHERE par2.bl_id = bl.bl_id
                        ORDER BY par2.create_dt
                        LIMIT 1
                    )
                WHERE bl.mb_id = '" . $member['mb_id'] . "'
                    AND bl.mb_thezone = '" . $member['mb_thezone'] . "'
                    AND bl.billing_yn = 'Y'
                    AND ( pay_confirm_id IS NOT NULL OR pay_confirm_id <> '' )
                    AND ( pay_confirm_receipt_id IS NOT NULL OR pay_confirm_receipt_id <> '' )
                ORDER BY pay_confirm_dt DESC
                LIMIT 4

    ");
    $result = sql_query($_sql);
    $_num_rows = $result->num_rows;

    // 청구건이 없는 경우
    if( !$_sql_bl ) {

        $_billing = sql_fetch_array($result);
        if( $_billing['billing_month'] != date("m", mktime(0, 0, 0, date("m")-1, 1)) ) {
            mysqli_data_seek($result,0); // SQL 포인터 초기화
            $_billing="";
        } else {
            mysqli_data_seek($result,1); // SQL 포인터 초기화
            $_sql_bl = $_billing;
            $_sql_bl['bl_id'] = "";
            $_num_rows --;
        }

    }

    $_fee = (int)($_sql_bl['billing_fee'])?($_sql_bl['billing_fee']):(json_decode( $default['de_paymenet_billing_OnOff'], TRUE )['fee_card']);
    
    if( $_sql_bl['billing_fee_yn']=="Y" ) {
        $_online_price_total = $_sql_bl['price_total'] + ( ceil(($_fee/100)*$_sql_bl['price_total']) );
    } else {
        $_online_price_total = $_sql_bl['price_total'];
    }
    

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CardPayment</title>
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/payment_reset.css">
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/payment_style.css">
    <!-- fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="<?=G5_JS_URL;?>/jquery.fileDownload.js"></script>

    <script type="application/javascript">
        <?php if( $_sql_bl['bl_id'] ) { ?>
        var order = {  _price: "<?=($_online_price_total);?>",
                    _tax_free: "0",
                    _name: "청구_<?=$_sql_bl['mb_giup_bname'];?>_<?=date("m", mktime(0, 0, 0, date("m")-1, 1))?>월_<?=$_sql_bl['billing_type']?>결제",
                    _id: "<?=$_sql_bl['bl_id'];?>"
        };

        var user = {  id: "<?=$_sql_bl['mb_thezone'];?>", 
                    username: "<?=$_sql_bl['mb_giup_bname'];?>", 
                    phone: "<?=$_sql_bl['mb_giup_btel'];?>", 
                    email: "<?=$_sql_bl['mb_giup_tax_email'];?>",
                    addr: "<?=$_sql_bl['mb_giup_addr1']." ".$_sql_bl['mb_giup_addr2']." ".$_sql_bl['mb_giup_addr3'];?>"
        };
        <?php } ?>

        $(function() {

            <?php if( $_sql_bl['bl_id'] ) { ?>
            $('.contentsWrap .okBtn').click(function() { $('#OnlineBilling_OK').show(); });
            $('.contentsWrap .okBtn').click(function() { $('#OnlineBilling_OK').hide(); });
            $('#OnlineBilling_OK').hide();
            $('#OnlineBilling_OK .okBtn').click(function() { location.reload(); });
            <?php } ?>

            $('.btn01').click(function() { 

            <?php if( $_sql_bl['price_total']>0 ) { ?>
            ExcelDownload('<?=($_sql_bl['bl_id'])?$_sql_bl['bl_id']:$_billing['bl_id'];?>');
            <?php } else { ?>
            alert("청구 금액이 없습니다."); 
            <?php } ?>

            });

        });


        function ExcelDownload(id) {
        
            $('#loading_excel').show();

            var excel_downloader = $.fileDownload("/shop/popup.payment_OnlineBilling_PDF.php", { 
                httpMethod: "GET", 
                data: { "bl_id":id }
            })
            .always(function() { $('#loading_excel').hide(); });

        }

        function cancelExcelDownload() {
            $('#loading_excel').hide();
        }
    </script>


    <style>

        /* 온라인결제 팝업 */
        #OnlineBilling_OK { display: none; position: fixed; width: 100%; height: 100%; left: 0; top: 0; z-index:9999; background:rgba(229, 229, 229, 0.5); }
        .OnlineBilling_OK_close { position:absolute; top:15px; right: 15px; color: #000; font-size: 2.5em; cursor:pointer; }
        
        /* 로딩 팝업 */
        #loading_excel { display: none; width: 100%; height: 100%; position: fixed; left: 0; top: 0; z-index: 9999; background: rgba(0, 0, 0, 0.3); }
        #loading_excel .loading_modal { position: absolute; width: 400px; padding: 30px 20px; background: #fff; text-align: center; top: 50%; left: 50%; transform: translate(-50%, -50%); }
        #loading_excel .loading_modal p { padding: 0; font-size: 16px; }
        #loading_excel .loading_modal img { display: block; margin: 20px auto; }
        #loading_excel .loading_modal button { padding: 10px 30px; font-size: 16px; border: 1px solid #ddd; border-radius: 5px; }

    </style>

</head>
<body>
    <div class="visual">
		<div class="hanacardWrap" id="hanacardPopup">           
                <p><span class="hanacardImg" id="hanacard_close"><a href="#" class="hana_closeBg"><img src="../img/hanacard_btn_close_x.svg" alt="닫기"></a></span>
                <a href="https://eroumcare.com/bbs/board.php?bo_table=notice&wr_id=176" class="hanacarBtn" target="_top">추가 혜택 확인하기 GO <img src="../img/hanacard_icon_arrow.svg" alt="하나카드"></a>
                </p>          
        </div>
        <script>
            document.getElementById("hanacard_close").addEventListener("click", function() {
              document.getElementById("hanacardPopup").style.display = "none";
            });
          </script>   
        <div class="visalWrap">
            <!-- title -->
            <div class="headerTitle">
                <h5>사업소 대금 결제</h5>
                <a href="#" class="cancel" onclick="parent.$('.OnlineBilling_popup_close').click();"><i class="fa-sharp fa-solid fa-xmark"></i></a>
            </div>
             <!-- contents -->
             <div class="contentsWrap">
                <!-- 결제 청구서 내역 -->
                <div class="billWrap">                    
                    <div class="billTitle">[<?=$member['mb_giup_bname'];?>][<?=date("m", mktime(0, 0, 0, date("m")-1, 1));?>월]대금 결제 청구서</div>
                    <div class="billListWrap">
                        <div class="billList">
                            <div class="price">
                                <div class="pTitle">과세 물품 구매 금액</div>
                                <div class="price">₩ <?=number_format(($_sql_bl['price_tax'])?($_sql_bl['price_tax']):(0));?></div>
                            </div>
                            <hr class="line01">
                            <div class="price">
                                <div class="pTitle">면세 물품 구매 금액</div>
                                <div class="price">₩ <?=number_format(($_sql_bl['price_tax_free'])?($_sql_bl['price_tax_free']):(0));?></div>
                            </div>
                            <hr class="line02">
                            <div class="totalrice">
                                <div class="pTitle">총 청구 금액</div>
                                <div class="price">₩ <?=number_format(($_sql_bl['price_total'])?($_sql_bl['price_total']):(0));?></div>
                            </div>
                        </div>
                        <div class="billList2">
                            <div class="totalrice" >
                                <div class="price" >신용 카드 결제 금액</div>
                                <div class="price">₩ <?=number_format( $_online_price_total );?></div>
                            </div>                            
                        </div>
                    </div>
                    <?php if( $_sql_bl['billing_fee_yn'] == "Y" ) { ?>
                    <div class="note">※ 온라인 결제 시에는 <span class="txt_b">결제 수수료(<?=($_fee);?>%)</span> 포함한 금액으로 결제합니다.</div>
                    <?php } ?>
                    <div class="billBtnWrap">
                        <div class="btn01">정산 내역서 받기</div>
                        <div class="btn02W">
                        <?php if( $_sql_bl['bl_id'] ) { ?>
                            <div class="btn02 btn02-01" onClick='parent.Payment_Set_Billing( order, "카드", user );'>카드 결제</div>
                            <!-- <div class="btn02" onClick='parent.Payment_Set_Billing( order, "계좌이체", user );'>실시간계좌이체</div> -->
                        <?php } else { ?>
                        <?php if( $_sql_bl['price_total']>0 ) { ?>
                            <div class="btn03">대금 결제 완료</div>
                        <?php } else {?>
                            <div class="btn03">청구 금액 없음</div>
                        <?php } ?>
                        <?php } ?>
                        </div>
                        
                    </div>
                    <div class="billBtnWrapM">
                        <a href="javascript:void(0);" onclick="window.open('<?=G5_DATA_URL;?>/file/이로움_스마트_서비스_05_간편결제_하기.pdf');" class="thkc_btnManual">간편결제 메뉴얼</a>
                    </div>                    
                </div>
                <!-- 이전 결제 내역 -->
                <div class="priceListWrap">
                    <div class="listTitle">이전 결제 내역</div>
                    <table>
                        <tr>
                            <th>월</th>
                            <th>결제일</th>
                            <th>결제금액</th>
                            <th>결제방식</th>
                            <th>할부구분</th>                            
                            <th>비고</th>
                        </tr>

                        <?php if( $_num_rows > 0 ) { ?>
                        <?php while( $row=sql_fetch_array($result) ) { ?>
                        <tr>
                            <td><a href="#" Onclick="ExcelDownload('<?=$row['bl_id'];?>')"><?=$row['billing_month']?>월</a></td>
                            <td><?=substr($row['pay_confirm_dt'],0,10);?></td>
                            <td>
                                <?php if( $row['billing_fee_yn'] == "Y" ) { ?>
                                <?=number_format( $row['price_total'] + ( ceil(($row['billing_fee']/100)*$row['price_total']) ) );?>원
                                <?php } else { ?> <?=number_format( $row['price_total'] );?>원 <?php } ?>
                            </td>
                            <td><?=( ($row['method_symbol']=="card")?(txt_pay_ENUM($row['method_symbol'])."(".$row['card_company'].")"):(txt_pay_ENUM($row['method_symbol'])) )  ?></td>
                            <td><?=( ($row['card_quota']=="00")?("일시불"):("할부(".(int)$row['card_quota']."개월)") )?></td>
                            <td>
                                <?php if( $row['billing_fee_yn'] == "Y" ) { ?> 수수료적용(<?=$row['billing_fee']?>%) <?php } else { ?> 수수료미적용 <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php } else { ?>
                        <tr>
                            <td colspan="6"> 이전 결제 내역이 없습니다. </td>
                        </tr>
                        <?php } ?>
                      </table>
                </div>
             </div>
            <?php if( !$_sql_bl['bl_id'] ) { ?>
            <!-- 하단 확인 버튼 -->
            <div class="okBtn" onclick="parent.$('.OnlineBilling_popup_close').click();">확인</div>
            <?php } ?>
        </div>
    </div>


    <!-- 결제완료 팝업 -->
    <div id="OnlineBilling_OK">
        <!-- 결제완료 팝업 -->
        <div class="visual">
            <div class="paymentPop">
                
                <!-- 결제완료 -->
                <div class="content">
                    <div class="title">
                        <svg version="1.1"xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve" width="50" height="50">                   
                            <g>
                            <polyline class="st0" points="14.5,21 23.5,30 46.5,9"/>
                                <g>
                                    <path class="st1" d="M39.17,11.44C35.52,7.49,30.31,5,24.5,5c-11.05,0-20,8.95-20,20c0,11.05,8.95,20,20,20s20-8.95,20-20 c0-2.97-0.66-5.78-1.83-8.31"/>
                                </g>
                            </g>
                        </svg>
                        <h3>결제완료</h3>                    
                    </div>
                    <div class="txt">
                        <span><?=$_sql_bl['billing_month']?>월</span> 대금 <span class="won"><?=number_format($_online_price_total);?>원</span>의<br>
                        결제가 완료 되었습니다.
                    </div>
                </div>
                
                <!-- 하단 확인 버튼 -->
                <div class="okBtn">확인</div>
                </div>
            </div>    
        </div>
    </div>
    
    <div id="loading_excel" style="display: none;">
    <div class="loading_modal">
        <p>파일 다운로드 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
        <img src="/shop/img/loading.gif" alt="loading">
    </div>
    </div>

</body>
</html>
