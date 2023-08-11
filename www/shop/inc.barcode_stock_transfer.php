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
    /* //  * Program Name : EROUMCARE Platform! = Renewal Ver:1.0 */
    /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
    /* //  * Copyright (c) 2023 THKC Co,Ltd.  All rights reserved. */
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
    /* // 파일 작성 일자 : 23.07.18 */
    /* // 파일 작성자 : 박서원 */
    /* // 파일명 : /www/shop/inc.barcode_stock_transfer.php */
    /* // 파일 설명 : 바코드 재공이동 신규 기능 추가에 따른 파일 */
    /* //              해당 파일은 바코드의 동일 급여 코드에 해당하는 상품에 대하여 기 등록된 바코드 정보를 이관처리하기 위한 프로세스 중 일부 파일. */
    /* //              javascript:BarCodeStock_Transfer(true) 함수 호출로 해당 본 기능의 팝업 및 기능 활성화. */
    
    /* // 최종 수정 일자 : 23.07.18 */
    /* // 최종 수정 내용 : 바코드 재고이관을 위해 신규 추가됨. */
?>


<style>

    .stop-scroll {height: 100%;overflow: hidden; } 

    /* 왼쪽 정렬 스타일 */
    .left-align { text-align: left; }

    /* 오른쪽 정렬 스타일 */
    .right-align { text-align: right; }

    .popup_BarCodeStock_Transfer { position: fixed; left: 0; top: 0; z-index: 100; width: 100%; height: 100%; display: none; }


    #popup_BarCodeStock_Transfer_confirm, #popup_BarCodeStock_Transfer_ok { position: fixed; left: 25%; top: 25%; z-index: 100; width: 100%; max-width:500px; height: 50%; display: none; }
    @media (max-width: 650px) {
        #popup_BarCodeStock_Transfer_confirm, #popup_BarCodeStock_Transfer_ok { position: fixed; left: 0%; top: 10%; z-index: 100; width: 100%; height: 80%; display: none; }
    }

    .popup_BarCodeStock_Transfer .dim { position: fixed; width: 100%; height: 100%; left: 0; top: 0; background: rgba(0, 0, 0, 0.5); }
    .popup_BarCodeStock_Transfer .pop { position: absolute; width: 90%; height: 80%; left: 5%; top: 5%; background: #fff; padding: 20px 20px 70px; }
    .popup_BarCodeStock_Transfer .pop .head p { font-size: 18px; font-weight: 700; }
    .popup_BarCodeStock_Transfer .pop .head span { font-size: 30px; position: relative; top: -11px; cursor: pointer; }


    #popup_BarCodeStock_Transfer .content { padding: 5px 25px; }
    @media (max-width: 600px) {
        #popup_BarCodeStock_Transfer .content { padding: 0px; }
    }

    #popup_BarCodeStock_Transfer .content .search_input .frm_input { width: 100%; height: 45px; font-size: 20px; padding: 20px 10px; }
    #popup_BarCodeStock_Transfer .content #transfer_BarcodeItem { width: 100%; height: 45px; font-size: 18px; padding: 5px 10px; }

    #popup_BarCodeStock_Transfer .content .selectElement_BarCodeList .Select_listContent option { padding : 2px 10px; }

    #popup_BarCodeStock_Transfer .content .selectElement_BarCodeList .title_barcode{ float:left; width: -webkit-fill-available; }
    #popup_BarCodeStock_Transfer .content .selectElement_BarCodeList .title_barcode_cnt_total{ float:right; display: inline-grid; width: 160px; }
    #popup_BarCodeStock_Transfer .content .selectElement_BarCodeList .title_barcode_cnt_select{ float:right; display: inline-grid; width: 130px; }

    .popup_BarCodeStock_Transfer .footer { position: absolute; left: 0; bottom: 0; width: 100%; height: 50px; }
    .popup_BarCodeStock_Transfer .footer .btn_wrap { width: 100%; height: 100%; }
    .popup_BarCodeStock_Transfer .footer .btn_wrap .save { background: #000; color: #fff; }


    #popup_BarCodeStock_Transfer_confirm .Barcode_Transfer_Info { margin: 25px 0px; }

    #popup_BarCodeStock_Transfer_confirm .Barcode_Transfer_Info,
    #popup_BarCodeStock_Transfer_confirm .item_prev_nm,
    #popup_BarCodeStock_Transfer_confirm .item_direction,
    #popup_BarCodeStock_Transfer_confirm .item_next_nm { text-align:center; padding:2px 0px;}

    #popup_BarCodeStock_Transfer_confirm .item_next_nm { font-weight: bold; }


    #popup_BarCodeStock_Transfer_ok .TransferOK_Title,
    #popup_BarCodeStock_Transfer_ok .TransferOK_Cnt,
    #popup_BarCodeStock_Transfer_ok .TransferOK_ItemNM { text-align:center; padding:5px 0px; }

    #popup_BarCodeStock_Transfer_ok .TransferOK_Title span,
    #popup_BarCodeStock_Transfer_ok .TransferOK_Cnt span,
    #popup_BarCodeStock_Transfer_ok .TransferOK_ItemNM span { font-weight: bold; }

    
    #popup_BarCodeStock_Transfer_ok .TransferOK_ErrorList { text-align:center; margin-top: 10px; height: 120px; overflow-x: auto; }
    #popup_BarCodeStock_Transfer_ok .TransferOK_ErrorList .err_barcodeList { text-align:left; padding-left:10%; }


</style>


<script>

    // 바코드이동 팝업창 실행 및 백패널 스크롤STOP
    function BarCodeStock_Transfer(flag) {

        if (flag) {
            $('#popup_BarCodeStock_Transfer').show();
            document.body.classList.add("stop-scroll");
            $('#popup_BarCodeStock_Transfer .ipt_so_sch').focus();
        } else {
            $('#popup_BarCodeStock_Transfer, #popup_BarCodeStock_Transfer_confirm, #popup_BarCodeStock_Transfer_ok').hide();
            
            document.body.classList.remove("stop-scroll");
            $('#popup_BarCodeStock_Transfer .ipt_so_sch').val("");
            $('.select_option, .selectElement_item, .selectElement_BarCodeList').css({'display':'none'});
        }

    }


    function BarCodeStock_Transfer_Continue() {
        $('#popup_BarCodeStock_Transfer, #popup_BarCodeStock_Transfer_confirm, #popup_BarCodeStock_Transfer_ok').hide();
        $('#popup_BarCodeStock_Transfer .ipt_so_sch').val("");
        $('.select_option, .selectElement_item, .selectElement_BarCodeList').css({'display':'none'});
        $("div.selectElement_BarCodeList span.cnt_select").text("0개");
        
        BarCodeStock_Transfer(true);
    }


    // 바코드이동 실행 함수.
    function BarCodeStock_Transfer_MoveitStep1(flag) {   
        if (flag) {
            if( !$("#transfer_BarcodeItem option:selected").val() ) {
                alert("상품이 또는 옵션 정보가 선택되지 않았습니다.\n상품또는 옵션을 먼저 선택하세요.");
                return;
            }
            else if( !$(".Select_listContent option").length ) {
                alert("이동하려는 바코드정보가 선택되어 있지 않습니다.\n상품을 검색하여 이동하려는 바코드정보를 선택하세요.");
                return;
            }
            else if( !$(".Select_listContent option:selected").length ) {
                alert("이동하려는 바코드정보가 선택되어 있지 않습니다.\n");
                return;
            }
            
            var select_data = $(".Select_listContent option:selected").toArray();
            var barcodeText = $('<div>').html(select_data[0].innerHTML).find('.list_barcode').text();

            var msg;
            if( select_data.length > 1 ) {
                msg = "<strong>" + barcodeText + "외 " + (select_data.length-1) + "건</strong><br />바코드를 재고이동 하시겠습니까? "
            } else {
                msg = "<strong>" + barcodeText + "</strong><br />바코드를 재고이동 하시겠습니까? ";
            }
  
            $('#popup_BarCodeStock_Transfer_confirm').show();
            $('#popup_BarCodeStock_Transfer').hide();
            
            $('#popup_BarCodeStock_Transfer_confirm .Barcode_Transfer_Info').html( msg );
            $('#popup_BarCodeStock_Transfer_confirm .item_prev_nm').html( $('.ipt_so_sch').val() );
            if( $('#popup_BarCodeStock_Transfer .select_option_id').text() ){
                $('#popup_BarCodeStock_Transfer_confirm .item_prev_nm').append( "<br />┗ "  + $('#popup_BarCodeStock_Transfer .select_option_id').text() );
            }

            // 해당 option 요소가 속한 optgroup 라벨명을 가져옴
            var item_next_nm;
            if( $('#transfer_BarcodeItem option:selected').parent("optgroup").attr("label") ) {
                item_next_nm = $('#transfer_BarcodeItem option:selected').parent("optgroup").attr("label") 
                                + "<br />" 
                                + $('#transfer_BarcodeItem option:selected').text();
            } else {
                item_next_nm = $('#transfer_BarcodeItem option:selected').text();
            }

            $('#popup_BarCodeStock_Transfer_confirm .item_next_nm').html( item_next_nm );

            //console.log(select_data);
        } else {
            $('#popup_BarCodeStock_Transfer').show();
            $('#popup_BarCodeStock_Transfer_confirm').hide();
        }
    }


    // 바코드 재고 이관 진행 관련 함수 (실제 DB 처리 부분)
    function BarCodeStock_Transfer_MoveitStep2(flag) {
        
        if (flag) {
            var TmpBarcode = $(".Select_listContent option:selected").toArray();
            //console.log(TmpBarcode);
            var barcodeText = $('<div>').html(TmpBarcode[0].innerHTML).find('.list_barcode').text();

            var borcodeList = []; // 배열을 선언할 때는 []를 사용합니다.
            TmpBarcode.forEach(tmp => {
                var _val = $(tmp).val();
                borcodeList.push(_val);
                //console.log(_val);
            });

            var prevItid = TmpBarcode[0].getAttribute('data-itid'); if (!prevItid) { prevItid = ""; }
            var prevIoid = TmpBarcode[0].getAttribute('data-ioid'); if (!prevIoid) { prevIoid = ""; }
            var nextItid = $("#transfer_BarcodeItem option:selected").data('val'); if (!nextItid) { nextItid = ""; }
            var nextIoid = $("#transfer_BarcodeItem option:selected").data('ioid'); if (!nextIoid) { nextIoid = ""; }

            $.ajax({
                url: '/shop/ajax.barcode_transfer_moveit.php', type: 'POST', dataType: 'json', async: false,
                data : {
                    borcodeData: borcodeList,
                    prev_itid: prevItid, prev_ioid: prevIoid,
                    next_itid: nextItid, next_ioid: nextIoid
                },
                success: function(data) {
                    if( data.YN === "Y" ) {
                        
                        $('#popup_BarCodeStock_Transfer_ok').show();

                        $('#popup_BarCodeStock_Transfer_ok .TransferOK_Title span').html( data.YN_msg );

                        var _cnt_Total = $(".Select_listContent option:selected").length;
                        var _cnt_Error = 0;

                        const errorList = $('#popup_BarCodeStock_Transfer_ok .TransferOK_ErrorList span');
                        errorList.empty(); // 기존 내용 초기화

                        if( data.ERROR ) { 
                            _cnt_Error = Object.keys(data.ERROR).length;

                            Object.keys(data.ERROR).forEach(key => {
                                errorList.append(`<div class='err_barcodeList'>${data.ERROR[key]}</div>`);
                            });                            
  
                            $('#popup_BarCodeStock_Transfer_ok .TransferOK_ErrorList').show();
                            
                        }

                        $('#popup_BarCodeStock_Transfer_ok .TransferOK_Cnt span').html(  parseInt(_cnt_Total, 10) - parseInt(_cnt_Error, 10) );

                        // 해당 option 요소가 속한 optgroup 라벨명을 가져옴
                        var item_next_nm;
                        if( $('#transfer_BarcodeItem option:selected').parent("optgroup").attr("label") ) {
                            item_next_nm = $('#transfer_BarcodeItem option:selected').parent("optgroup").attr("label") 
                                            + "<br />" 
                                            + $('#transfer_BarcodeItem option:selected').text();
                        } else {
                            item_next_nm = $('#transfer_BarcodeItem option:selected').text();
                        }
                        $('#popup_BarCodeStock_Transfer_ok .TransferOK_ItemNM span').html( item_next_nm );

                        //$('#popup_BarCodeStock_Transfer_confirm').hide();

                    } else {
                        alert(data.YN_msg);
                        location.reload();
                    }
                },
                error: function(e) {}
            });


        } else {
            document.body.classList.remove("stop-scroll");
            $('#popup_BarCodeStock_Transfer .ipt_so_sch').val("");
            $('#popup_BarCodeStock_Transfer_ok, #popup_BarCodeStock_Transfer_confirm').hide();
            location.reload();
        }

    }


    // 해당 상품의 바코드 정보를 가져오는 함수.
    function get_ListBarCode(val_page, val_itid, val_ioid) {
        var data = [];

        $.ajax({
            url: '/adm/shop_admin/ajax.release_stock_barcode_list.php',
            type: 'GET',
            data: {
                it_id: val_itid,
                io_id: val_ioid,
                only_not_deleted_barcode: 'true',
                page:val_page
            },
            dataType: 'json',
            async: false
        })
        .done(function(result) {
            data = result.data;
        })
        .fail(function($xhr) {
            var data = $xhr.responseJSON;
            alert(data && data.message);
        });

        return data;
    }


    // 멀티 select박스에서 선택된 바코드 수량 확인 함수.
    function updateSelectedCount() {
        $("div.selectElement_BarCodeList span.cnt_select").text( $(".Select_listContent option:selected").length +"개");
    }


    $(function() {
    
        var deviceUserAgent = navigator.userAgent.toLowerCase();
        var device;

        if(deviceUserAgent.indexOf("android") > -1) {
            /* android */
            device = "android";
        } else if(deviceUserAgent.indexOf("iphone") > -1 || deviceUserAgent.indexOf("ipad") > -1 || deviceUserAgent.indexOf("ipod") > -1) {
            /* ios */
            device = "ios";
        }


        $(".select_option, .selectElement_item, .selectElement_BarCodeList").css({'display':'none'});


        // 품목 검색
        $('#ipt_so_sch').flexdatalist({
            minLength: 1,
            url: '/shop/ajax.release_stock_ItemInfo.php',
            cache: false, // cache
            searchContain: true, // %검색어%
            noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
            selectionRequired: true,
            focusFirstResult: false,
            searchIn: ["it_name","it_model","id", "it_name_no_space"],
            visibleCallback: function($li, item, options) {
                //console.log(item);
                var $item = {};
                
                if( item.io_no ) {
                    $item = $('<span>').html("<strong>[" + item.gubun + "] " + item.it_name + "</strong>");
                    $item.append("<br />");
                    $item.append("<span>┗ 옵션 : " + item.io_id + "</span>");
                } else {
                    $item = $('<span>').html("<strong>[" + item.gubun + "] " + item.it_name + "</strong>");
                }

                $item.appendTo($li);
                
                $("#popup_BarCodeStock_Transfer .select_option, #popup_BarCodeStock_Transfer .selectElement_item, #popup_BarCodeStock_Transfer .Select_listContent").css({'display':'none'});
                $(".select_item_itid, .input_option_no, .input_option_id").val();
                $(".select_option_id").text("");
                $("div.selectElement_BarCodeList span.cnt_select").text("0개");

                return $li;
            },
        })
        .on("select:flexdatalist", function(event, obj) {


            $(".selectElement_option, .selectElement_itemInsert").html('');
            $(".Select_listContent option:selected").prop("selected", false);
            $(".Select_listContent").empty();
            $(".selectElement_BarCodeList").css({'display':'none'});


            var BarCode_List = get_ListBarCode( '1', obj.it_id, obj.io_id );            
            //console.log(BarCode_List);

            if( BarCode_List.length <= 0 ) {
                alert("재고 수량이 부족하여 옮길 수 없는 상품입니다.");

                if(confirm("다른 상품을 다시 검색하시겠습니까...? ")) {                    
                    $('#popup_BarCodeStock_Transfer .ipt_so_sch').val("");
                    $('#popup_BarCodeStock_Transfer .ipt_so_sch').focus();
                }

                return;
            }


            if( (obj.ppc.length <= 1) && (obj.options.length == 0) ) {
                //console.log(obj.ppc.length);
                //console.log(obj.options.length);

                alert("추가로 등록된 상품이 존재하지 않습니다.\n다른 상품을 검색해주세요.");
                $('#popup_BarCodeStock_Transfer .ipt_so_sch').val("");
                $('#popup_BarCodeStock_Transfer .ipt_so_sch').focus();
                return;
            }


            $(this).val(obj.ProdType +" "+ obj.it_name);
            $(".select_item_itid").val(obj.it_id);

            if( obj.io_id ) {
                $(".input_option_no").val(obj.io_no);
                $(".input_option_id").val(obj.io_id);
                $(".select_option_id").html(obj.io_id);
                $('.select_option').css({'display':'block', 'font-size':'20px', 'padding':'10px 15px'}); 
            }
            
            $('#popup_BarCodeStock_Transfer .selectElement_item, #popup_BarCodeStock_Transfer .Select_listContent').css({'display':'block'});

            options = obj.options;
            prodpaycodes = obj.ppc;

            if( (prodpaycodes.length > 1) || (options.length > 0) ){
                //console.log(prodpaycodes.length);
                //console.log(prodpaycodes);

                // <select> 요소 생성
                var selectElement = $("<select>").attr("id", "transfer_BarcodeItem");
                selectElement.on('change', function() { $("#loading").show(); });

                selectElement.append( $("<option>").attr("value", '').text('상품을 선택하세요.') );

                prodpaycodes.forEach(element => {
                if (element.it_id == obj.it_id && options.length == 0) { return; }

                if( options.length > 0 ) {
                    var optionGroup = $("<optgroup>").attr("label", element.ProdType +" "+ element.it_name);
                    options.forEach(options_element => {
                        if (options_element.io_no == obj.io_no)  { return; }
                        optionGroup.append( $("<option>")
                            .attr("value", options_element.io_no)
                            .attr("data-val", element.it_id)
                            .attr("data-ioid", options_element.io_id)
                            .text("┗ "+options_element.io_id)
                        );
                    });
                    selectElement.append(optionGroup);
                } else {
                    selectElement.append( $("<option>")
                        .attr("value", element.it_id)
                        .attr("data-val", element.it_id)
                        .text(element.ProdType +" "+ element.it_name)
                    );
                }


                });
                // <select> 요소를 문서에 추가
                $(".selectElement_itemInsert").append(selectElement);
            }

            $("#transfer_BarcodeItem").focus();
            //$("#transfer_BarcodeItem").attr("size", $("#transfer_BarcodeItem").children().length);

        });


        // 23.07.19 : 바코드 재고 이동 관련
        $(document).on("change", "#transfer_BarcodeItem", function(e) { 
            
            $(".Select_listContent option:selected").prop("selected", false);
            $(".Select_listContent").empty();
            $(".Select_listContent").scrollTop(0);
            $(".barcode_pages").val(1);

            updateSelectedCount();

            if( !$(this).val() ) {
                $("div.selectElement_BarCodeList span.cnt_total").text("0개");
                $("#loading").hide();
                return;
            }

            //alert( $(".select_item_itid").val() );
            //alert( $(".input_option_no").val() );
            //alert( $(".input_option_id").val() );

            $("div.selectElement_BarCodeList span.cnt_total").text( "검색중...");

            // 0.25초(250ms) 뒤에 BarCode_List 함수를 실행합니다.
            setTimeout(function() {
                var page = device ? '' : 1;                
                var BarCode_List;

                BarCode_List = get_ListBarCode(page, $(".select_item_itid").val(), $(".input_option_id").val());

                if ( BarCode_List && BarCode_List.length > 0) {
                    
                    var check_status;                
                    $(".barcode_pages").val(1);

                    for (var i = 0; i < BarCode_List.length; i++) {

                        if (BarCode_List[i].checked_at) { check_status = '[' + BarCode_List[i].checked_at +']'; } else { check_status = '[미확인]'; }

                        $('.Select_listContent').append( $("<option>")
                            .attr("value", BarCode_List[i].bc_id)
                            .attr("data-itid", $(".select_item_itid").val() )
                            .attr("data-ioid", $(".input_option_id").val() )
                            .html( "<span class='list_barcode'>" + BarCode_List[i].bc_barcode + "</span>" + "　　　　"+ "<span class='list_insertdt'>" + check_status + "</span>" )
                        );
                    }
                    
                    $("div.selectElement_BarCodeList span.cnt_total").text( $(".Select_listContent option").length +"개");

                }

                $("#loading").hide();
            }, 250);
            
            $('.selectElement_BarCodeList').css({'display':'block'});

            $('.selectElement_BarCodeList .content li .title_barcode').css({ 'width': '55%', 'font-weight': 'bold', 'font-size': '20px' });
            $('.selectElement_BarCodeList .content li .title_check_status').css({ 'width': '35%', 'text-align': 'center' });
            $('.selectElement_BarCodeList .Select_listContent').css({ 'width': '100%', 'font-weight': 'bold', 'font-size': '18px' });

            if(device) {
                $('.selectElement_BarCodeList .Select_listContent').css({ 'height': '48px', });
            } else {
                $('.selectElement_BarCodeList .Select_listContent').css({ 'height': '290px', });
            }
    
        });

        $(".ipt_so_sch").on("focus", function() { 
            this.select();
            $("#popup_BarCodeStock_Transfer .select_item_itid").val("");
            $("#popup_BarCodeStock_Transfer .input_option_no").val("");
            $("#popup_BarCodeStock_Transfer .input_option_id").val("");
        });

            
        $(".Select_listContent").on("change", function() {
            updateSelectedCount();
        });

        $(".Select_listContent").on("scroll", function() {
            if( $(".Select_listContent option").length < 1000 ) return;

            var element = $(this)[0];

            if ( ( element.scrollHeight - element.scrollTop ) <= (parseInt(element.clientHeight, 10)+12) ) {
                // 여기서 끝까지 스크롤 했을 때 원하는 동작을 수행합니다.
                //console.log("끝까지 스크롤 했습니다!");

                var page = parseInt($(".barcode_pages").val(), 10) + 1;
                var BarCode_List = get_ListBarCode( page, $(".select_item_itid").val(), $(".input_option_id").val() );
                if ( BarCode_List && BarCode_List.length > 0) {
                    var check_status;
                    $(".barcode_pages").val(page);
                    for (var i = 0; i < BarCode_List.length; i++) {

                        if (BarCode_List[i].checked_at) { check_status = '[' + BarCode_List[i].checked_at +']'; } else { check_status = '[미확인]'; }

                        $('.Select_listContent').append( $("<option>")
                            .attr("value", BarCode_List[i].bc_id)
                            .attr("data-itid", $(".select_item_itid").val() )
                            .attr("data-ioid", $(".input_option_id").val() )
                            .html( "<span class='list_barcode'>" + BarCode_List[i].bc_barcode + "</span>" + "　　　　"+ "<span class='list_insertdt'>" + check_status + "</span>" )
                        );
                            
                    }

                    $("div.selectElement_BarCodeList span.cnt_total").text( $(".Select_listContent option").length +"개");

                }

            }

        });
    
    });
</script>


<div id="popup_BarCodeStock_Transfer" class="popup_BarCodeStock_Transfer">
    <div class="dim"></div>
    <div class="pop">

        <div class="head">
        <div class="flex-row justify-space-between">
            <p>바코드 재고 이동</p>
            <span onclick="BarCodeStock_Transfer(false);">&times;</span>
        </div>
        </div>

        <div class="content">
        
        <div class="content_title">
            상품명으로 검색하세요.<br />
            같은 급여코드의 상품만 가능 합니다.
        </div>

        <ul class="search_input">
            <li>
            <input type="hidden" class="select_item_itid" />
            <input type="text" maxlength="12" id="ipt_so_sch" class="ipt_so_sch frm_input required" onfocus="$(this).select()" value="" placeholder="상품명을 입력해주세요.">
            <!-- <button type="button" class="barNumCustomSubmitBtn" onclick="alert(this)">검색</button> -->
            </li>
            <li class="select_option">
            <input type="hidden" class="input_option_no" />
            <input type="hidden" class="input_option_id" />
            <span> ┗ 선택 옵션 : </span><span class="select_option_id"></span>
            </li>
        </ul>

        <div style="height:20px;"></div>

        <div class="selectElement_item">
            <ul>
            <li>바코드 재고를 이동하려는<br />'상품' 또는 '옵션'을 선택하세요.</li>
            <li class="selectElement_itemInsert"></li>
            </ul>

        </div>

        <div style="height:20px;"></div>

        <div class="selectElement_BarCodeList">
            <div id="content">
            <li class="item flex-row align-center">
                <div class="title_barcode">바코드를 선택하세요.</div>
                <div class="title_barcode_cnt_total">전체: <span class="cnt_total">0개</span></div>
                <div class="title_barcode_cnt_select">선택: <span class="cnt_select">0개</span></div>
            </li>
            <select  class="Select_listContent" multiple></select>
            <input type="hidden" class="barcode_pages" />
            </div>
        </div>

        </div>

        <div class="footer">
        <div class="flex-row btn_wrap">
            <button style="width: 60%" class="save" onclick="BarCodeStock_Transfer_MoveitStep1(true);">선택 바코드 재고 이동</button>
            <button style="width: 40%" class="cancel" onclick="BarCodeStock_Transfer(false);">닫기</button>
        </div>
        </div>

    </div>

</div>






<div id="popup_BarCodeStock_Transfer_confirm" class="popup_BarCodeStock_Transfer">
    <div class="dim"></div>
    <div class="pop">

        <div class="head">
            <div class="flex-row justify-space-between">
                <p>바코드 재고이동 확인</p>
                <span onclick="BarCodeStock_Transfer(false)">&times;</span>
            </div>
        </div>

        <div class="content">
            <div class="Barcode_Transfer_Info"></div>
            <div style="height:10px;"></div>

            <div class="item_prev_nm"></div>
            <div class="item_direction">↓</div>
            <div class="item_next_nm"></div>        
        </div>

        <div class="footer">
        <div class="flex-row btn_wrap">
            <button style="width: 60%" class="save" onclick="BarCodeStock_Transfer_MoveitStep2(true)">진행</button>
            <button style="width: 40%" class="cancel" onclick="BarCodeStock_Transfer_MoveitStep1(false);"">돌아가기</button>
        </div>
        </div>
        
    </div>

</div>







<div id="popup_BarCodeStock_Transfer_ok" class="popup_BarCodeStock_Transfer">
    <div class="dim"></div>
    <div class="pop">

        <div class="head">
            <div class="flex-row justify-space-between">
                <p>바코드 재고이동 완료</p>
                <span onclick="BarCodeStock_Transfer(false)">&times;</span>
            </div>
        </div>

        <div class="content">
            <div style="height:10px;"></div>
            <div class="TransferOK_Title">바코드 재고이동이 <span>완료</span>되었습니다.</div>            
            <div style="height:30px;"></div>

            <div class="TransferOK_Cnt">바코드 수량: <span>10</span>개</div>
            <div class="TransferOK_ItemNM">상품명: <span></span></div>
            <div class="TransferOK_ErrorList" style="display: none;">
                <li> ※ 미 처리된 바코드 리스트 </li>
                <span></span>
            </div>
        </div>

        <div class="footer">
        <div class="flex-row btn_wrap">
            <button style="width: 60%" class="save" onclick="BarCodeStock_Transfer_MoveitStep2(false)">완료</button>
            <button style="width: 40%" class="cancel" onclick="BarCodeStock_Transfer_Continue();">계속하기</button>
        </div>
        </div>
        
    </div>

</div>
