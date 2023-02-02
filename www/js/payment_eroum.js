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
    /* // 파일명 : payment_eroum.js */
    /* // 파일 설명 : 결제관련 전용 스크립트 모음 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */


    // BOOTPAY CDN 호출
    document.writeln("<script src='https://js.bootpay.co.kr/bootpay-4.2.7.min.js' crossorigin='anonymous' referrerpolicy='no-referrer'></script>");

    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */


    $(function() {

        $('.btn_OnlineBilling').click(function(e) { 
          $('#OnlineBilling_popup iframe').attr('src', '/shop/popup.payment_OnlineBilling.php');
          $('#OnlineBilling_popup iframe').attr('scrolling', 'no');
          $('#OnlineBilling_popup iframe').attr('frameborder', '0');
          $('#OnlineBilling_popup').show(); 
        });

        $('.OnlineBilling_popup_close').click(function() { $('#OnlineBilling_popup').hide(); location.reload(); });
      });



// 결제 함수
/* 
* @param string order : 결제 기초 정보(금액,부가세,주문서이름,주문서아이디)
* @param string user : 주문(결제)자 정보
*/
function Payment_Set_Billing( order, _type, user ) {
    
    /* 모바일 체크 */
    var deviceUserAgent = navigator.userAgent.toLowerCase();
    if(deviceUserAgent.indexOf("android") > -1 || deviceUserAgent.indexOf("iphone") > -1 || deviceUserAgent.indexOf("ipad") > -1 || deviceUserAgent.indexOf("ipod") > -1){
        alert("이용에 불편을 드려 죄송합니다.\n대금 결제는 모바일에서 불가능 합니다.\n컴퓨터(PC)를 이용해주세요.device"); return; 
    }

    if( (screen.width < 500) || (screen.height < 400) ){ 
        alert("이용에 불편을 드려 죄송합니다.\n대금 결제는 모바일에서 불가능 합니다.\n컴퓨터(PC)를 이용해주세요.screen"); return; 
    }

    if( !order._price ) { alert("결제 데이터 일부가 누락되었습니다.price"); return; }
    else if( !order._name ) { alert("결제 데이터 일부가 누락되었습니다.name"); return; }
    else if( !order._id ) { alert("결제 데이터 일부가 누락되었습니다.id"); return; }

    // 결제금액 재확인
    $.ajax({
        url: '/shop/ajax.patment_OnlineBilling_Check.php',
        type: 'POST',
        data: {"order_id":order._id},
        dataType: 'json',
        async: false,
        cache: false,
        error: function(){
            alert("이용에 불편을 드려 죄송합니다.\n결제 데이터 처리 과정에 문제가 발생하였습니다.\n\n고객센터로 문의 하여주시기 바랍니다."); return;
        }
    }).done(function(t) {
        
        // 결제 금액 체크 후 정상일 경우.
        if( user.id && user.username ) {
            var method = { _type: _type, function: Payment_Set_Billing_Result }    
            Payment_Request( order, method, user );     
            return; 
        } else { alert("회원 정보에 누락된 정보가 있습니다."); return; }

    }).fail(function(t) { location.reload(); return; });

    return;
}


// 대금&미수금 결제 후 회신 데이터 ajax 처리 저장
async function Payment_Set_Billing_Result(order_id, data){
    $.ajax({
        url: '/shop/ajax.patment_OnlineBilling_Result.php',
        type: 'POST',
        data: {"order_id":order_id, "data":data},
        dataType: 'json',
        async: false,
        cache: false,
        error: function(){
            alert("이용에 불편을 드려 죄송합니다.\n결제 데이터 처리 과정에 문제가 발생하였습니다.\n\n고객센터로 문의 하여주시기 바랍니다.");
        }
    }).done(function(t) {

        if( t.message=="error" ) { 
            alert( data.message );
        } else {
            var Billing_OK = $("#OnlineBilling_popup iframe").contents().find("#OnlineBilling_OK");
            Billing_OK.show();
        }
        
    });

}


// 주문 결제 함수
function Payment_Set_Order( order, _type, user, item ) {

    if( !order._price ) { alert("결제 데이터 일부가 누락되었습니다.price"); return; }
    else if( !order._name ) { alert("결제 데이터 일부가 누락되었습니다.name"); return; }
    else if( !order._id ) { alert("결제 데이터 일부가 누락되었습니다.id"); return; }

    $.ajax({
        url: '/shop/ajax.patment_OnlineBilling_Check.php',
        type: 'POST',
        data: {"order_id":order_id},
        dataType: 'json',
        async: false,
        cache: false,
        error: function(){
            alert("이용에 불편을 드려 죄송합니다.\n결제 데이터 처리 과정에 문제가 발생하였습니다.\n\n고객센터로 문의 하여주시기 바랍니다.");
        }
    }).done(function(t) {        
        //Payment_Request( order, method, user, item );
    }).fail (function(t) {        
        alert("실패");
    });

}


// BOOTPAY 결제 진행
async function Payment_Request( order, method="", user="", item="" ){


    // BOOTPAY Javascript 키 (도메인에 따른 키값 변경 처리)
    if( window.location.host == "www.eroumcare.com" || window.location.host == "eroumcare.com" ) {
        var application_id = '63bd16143049c8001c50c2f4'; //이로움Ver1.0_대금결제(상용)
    } else {
        var application_id = '63bd16643049c8001a50c306'; //이로움Ver1.0_대금결제(개발&테스트)
    }


    // BOOTPAY pg LIST
    var pg = "이니시스";

    
    // 결제시작
    try {

        var response = await Bootpay.requestPayment({
            "application_id": application_id,
            "pg": pg,
            "method": method._type,
            "order_name": order._name,
            "order_id": order._id,
            "price": order._price,
            "tax_free": order._tax_free,
            "user": {
                "id": user['id'],
                "username": user['username'],
                "phone": user['phone'],
                "email": user['email'],
                "addr": user['addr']
            },
            "extra": { "open_type": "iframe" }
        });

        switch(response.event) {
            case 'issued': // 가상계좌 입금 완료 처리
                break;
            case 'done': // 결제 완료 처리
                //console.log("response: " + response);                
                method.function(order._id, response.data);
                break;
            case 'confirm': //payload.extra.separately_confirmed = true; 일 경우 승인 전 해당 이벤트가 호출됨
                //console.log("response.receipt_id: " + response.receipt_id);
                /*
                 * 1. 클라이언트 승인을 하고자 할때
                 * // validationQuantityFromServer(); //예시) 재고확인과 같은 내부 로직을 처리하기 한다.
                 */

                /*
                var confirmedData = Bootpay.confirm(); //결제를 승인한다
                if(confirmedData.event === 'done') {
                    alert("성공");
                } else if(confirmedData.event === 'error') {
                    alert("실패");
                }
                */

                /**
                 * 2. 서버 승인을 하고자 할때
                 * // requestServerConfirm(); //예시) 서버 승인을 할 수 있도록  API를 호출한다. 서버에서는 재고확인과 로직 검증 후 서버승인을 요청한다.
                 * Bootpay.destroy(); //결제창을 닫는다.
                 */
                break;
        };

    } catch(e) {
        method.function(order._id, e);
        
        // 결제 진행중 오류 발생
        // e.error_code - 부트페이 오류 코드
        // e.pg_error_code - PG 오류 코드
        // e.message - 오류 내용
        console.log(e.message);

        switch(e.event) {
            case 'cancel':
                // 사용자가 결제창을 닫을때 호출
                console.log(e.message);
                break;
            case 'error':
                // 결제 승인 중 오류 발생시 호출
                console.log(e.error_code);
                break;
        }
    }

    return;

  }