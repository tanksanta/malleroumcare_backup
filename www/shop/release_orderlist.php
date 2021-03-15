<?php
	include_once("./_common.php");
	$g5["title"] = "주문 내역 바코드 수정";
	$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
	$od = sql_fetch($sql);
	$prodList = [];
	$prodListCnt = 0;
	$deliveryTotalCnt = 0;
    if($member['mb_level']< 9){alert("이용권한이 없습니다.");}
    $sub_menu = '400402';
 ?>
 <!DOCTYPE html>
 <html lang="en">
 <head>
     <meta charset="UTF-8">
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Document</title>
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
 </head>
 <body>
 <style>
    @font-face { font-family: 'NanumBarunGothic';
    src: url('<?php echo G5_CSS_URL?>/font/NanumBarunGothic.eot');
    src: url('<?php echo G5_CSS_URL?>/font/NanumBarunGothic.eot') format('embedded-opentype'),
    url('<?php echo G5_CSS_URL?>/font/NanumBarunGothic.woff') format('woff');}
    body { margin:0px; padding:0px; font-family: 'NanumBarunGothic', 'serif';}
    ul{list-style:none;}
    a { text-decoration:none } 
    .section1{ position:relative; width:100%; padding:0px;}
    .head{ position:relative; width:100%; background-color:#333333;height:60px; line-height:60px;}
    .head .p1{  float:left; margin-left:20px; color: #f1f1f1;font-size:30px; }
    .head .xbtn{ float:right;  margin-right:20px;color: #f1f1f1; font-size:40px; line-height:60px;}
    .view_complete_box{position:relative; width:100%; color: #666666; font-size:20px;height:60px;;border-bottom: 1px solid #dddddd;}
    .view_complete_box .sp1{ position:relative; left:20px; line-height:60px;}
    .view_complete_box input{ position:relative; top:1px;}
    .imfomation_box{ margin:0px;width:100%;position:relative;  padding:0px;display:block; width:100%; height:auto;}
    .imfomation_box a{width:100%;  height:160px;}
    .imfomation_box a .li_box{padding:16px 0 16px 0; width:100%;  height:160px; text-align:center; padding-bottom:16px; border-bottom: 1px solid #dddddd;}
    .imfomation_box a .li_box .li_box_line1{width: 100%; margin:auto;float:left; height:100px; color:#000;}
    .imfomation_box a .li_box .li_box_line1 .p1{height:100%; width: 75%; margin:auto;float:left; color:#000;}
    .imfomation_box a .li_box .li_box_line1 .p1 .span1{ flex: 1; font-size:20px; margin-left:20px; float:left;}
    .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{color:#000; float:left;}
    .imfomation_box a .li_box .li_box_line1 .p1 .span1_2{color:#ff690f;  float:left;}
    .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:13.5px; padding-bottom:20px; margin-left:20px;color:#a2a2a2; float:left;}
    .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:13.5px;  margin-left:20px;color:#a2a2a2; float:left;margin-left:20px;}
    .imfomation_box .state_box{ 
        width:20%; float:right; margin-right:10px; height:100%;   font-size:20px;
        border-radius: 6px; font-weight:bold; color:#fff;border:1px solid #dfdfdf;  text-align:center;
     }

     .imfomation_box .state_box.type1{ background: #79ad14;}    /* 입금완료 */
     .imfomation_box .state_box.type2{ background: #36830e;}    /* 상품준비 */
     .imfomation_box .state_box.type3{ background: #36a6de;}    /* 출고준비 */
     .imfomation_box .state_box.type4{ background: #28759c;}    /* 출고완료 */
     .imfomation_box .state_box.type5{ background: #372573;}    /* 배송완료 */
     .imfomation_box .state_box.type6{ background: #646464;}    /* 주문취소 */
     .imfomation_box .state_box.type7{ background: #2e427e;}    /* 주문무효 */

    .imfomation_box .barcode_box{
        position:relative;  background: #ffff; border-radius: 6px; font-weight:bold; margin-top:5px;  margin-left:2.5%;
        font-size: 18px; color:#666666;width:95%; height:50; line-height:50px; border:1px solid #dfdfdf; float:left;text-align:center; 
    }

    .imfomation_box .barcode_box.type1{background: #ffff; color:#666666;}
    .imfomation_box .barcode_box.type2{background: #b8b8b8; color:#fff;}
    .imfomation_box .barcode_box.type3{background: #ffff; color:#ff885f;border:1px solid #ff885f;}

    .imfomation_box .barcode_box_sp1{ position:relative;font-size: 17px;vertical-align:middle;color:#ff885f;left:20px; font-weight:none;}



    @media screen and (max-width: 4000px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:35px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:30px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:30px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{ width:330px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{width:500px;float:left; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;}
    }
    @media screen and (max-width: 1200px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:30px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:25px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:25px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{ width:270px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{width:auto;}
    }

    @media screen and (max-width: 1000px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:30px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:25px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:25px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{ width:270px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    }

    @media screen and (max-width: 900px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:28px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:23px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:23px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{ width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

    }

    @media screen and (max-width: 630px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:23px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:20px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:20px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{ width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

    }


    @media screen and (max-width: 500px){
        .imfomation_box a .li_box .li_box_line1 .p1 .span1{ font-size:20px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span2{ font-size:15px;}
        .imfomation_box a .li_box .li_box_line1 .p1 .span3{font-size:15px; }
        .imfomation_box a .li_box .li_box_line1 .p1 .span1_1{
            width:170px;float:left;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
        }
    }
    .li_box.type1{display:block;}
    .li_box.type1{display:none;}
}
 </style>



<section class="section1">

    <div class="head">
        <b class="p1">출고리스트</b>
        <a href="javascript:history.back();"><span class="xbtn">&times;</span></a>
    </div>

    <div class="view_complete_box">
        <span class="sp1">
            <input type="checkbox" name="" id="cf" onclick="cf_flag()"> 미완료 바코드 작성만 보기
        </span>
    </div>

    <ul class="imfomation_box" id="imfomation_box">
      
     
    </ul>
    <input type="hidden" value="1" id="page">
</section>

<script>
    var od_status = '';
    var od_step = "";
    var page= parseInt(document.getElementById('page').value);
    var loading = false;
    var end = false;
    var sel_field = 'od_id';
    var sub_menu = '400402';
    var last_step = '완료';
    var sel_date_field = 'od_time';

    var formdata= {};
    formdata['fr_date'] = "";
    formdata['last_step'] = "";
    formdata['od_important'] = "";
    formdata['od_release'] = "";
    formdata['od_status'] = "";
    formdata['od_step'] = "";
    formdata['search'] = "";
    formdata['sel_date_field'] = "od_time";
    formdata['sel_field'] = "od_id";
    formdata['sub_menu'] = "400402";
    formdata['to_date'] = "";
    doSearch();


    //리스트 불러오기 ajax
    function doSearch(){
        formdata['cf']=document.getElementById('cf').checked;
        formdata['page']=parseInt(document.getElementById('page').value);
        $.ajax({
            method: "POST",
            url: "<?=G5_URL?>/adm/shop_admin/ajax.release_orderlist.php",
            data: formdata,
        })
        .done(function(html) {
            if(html.data2){
                if(document.getElementById('page').value=="1"){
                    $('#imfomation_box').html(html.data2);
                }else{
                    $('#imfomation_box').append(html.data2);
                }
                document.getElementById('page').value=parseInt(document.getElementById('page').value)+1;
            }else{
                // alert('마지막 페이지입니다.');
            }
        })
        .fail(function() {
            console.log("ajax error");
        })
        .always(function() {
            loading = false;
        });
    }


    $( document ).ready(function() {
        
        $(window).scroll(function() {
            // alert($(window).scrollTop()%100)
            if($(window).scrollTop()%100){
                doSearch();
            }
            // alert($(document).height() - $(window).height());
            // alert($(document).height() - $(window).height()-100);
            // if ($(window).scrollTop() == $(document).height() - $(window).height()) {
            //     doSearch();
            // }
        });
    });


    // $( document ).ready(function() {
    //     $(document).scroll(function(){
    //         var max_height = $(document).height();
    //         var now_height = $(window).scrollTop() + $(window).height();
    //         //끝에 닿기전에 미리 함수실행
    //         if((max_height <= now_height + 100) && _temp == 1)
    //         {
    //             doSearch();
    //         }
    //     });
    // });
    //미완료 바코드 작성만보기버튼
    function cf_flag(){ 
        if(document.getElementById('cf').checked){
            $(".li_box.cf").addClass("type1");
            $(".li_box.cf").removeClass("type2");
            
        }else{
            $(".li_box.cf").addClass("type2");
            $(".li_box.cf").removeClass("type1");
        }
    }

    //바코드 버튼 클릭
    $(document).on("click", ".barcode_box", function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		
		// var popupWidth = 700;
		// var popupHeight = 700;

		// var popupX = (window.screen.width / 2) - (popupWidth / 2);
		// var popupY= (window.screen.height / 2) - (popupHeight / 2);
		location.href="<?php echo G5_URL?>/adm/shop_admin/popup.prodBarNum.form.php?od_id="+ id+"&new=1";

		// window.open("<?php echo G5_URL?>/adm/shop_admin/popup.prodBarNum.form.php?od_id=" + id, "바코드 저장", "width=" + popupWidth + ", height=" + popupHeight + ", scrollbars=yes, resizable=no, top=" + popupY + ", left=" + popupX );
	});

    //x 버튼 이동
    // function x_btn(){
    //     history.pushState(null, null, "#noback");

    //         $(window).bind("hashchange", function(){

    //             history.pushState(null, null, "#noback");

    //             alert(1);

    //         });
    // }
</script>
 </body>
 </html>