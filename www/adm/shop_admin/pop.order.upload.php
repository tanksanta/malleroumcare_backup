<?php
// $sub_menu = '400400';
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');

// auth_check($auth[$sub_menu], "w");

$title = '주문서 일괄등록';
include_once('./pop.head.php');
?>
 <!-- font swesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    #loading_excel {
    display: none;
    width: 100%;
    height: 100%;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.3);
  }
  #loading_excel .loading_modal {
    position: absolute;
    width: 400px;
    padding: 30px 20px;
    background: #fff;
    text-align: center;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
  #loading_excel .loading_modal p {
    padding: 0;
    font-size: 16px;
  }
  #loading_excel .loading_modal img {
    display: block;
    margin: 20px auto;
  }
  #loading_excel .loading_modal button {
    padding: 10px 30px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
  }
</style>
<form name="order_upload" id="order_upload"  method="post" action="" enctype="MULTIPART/FORM-DATA" autocomplete="off">
    <div id="pop_order_add" class="admin_popup admin_popup_padding">
        <h4 class="h4_header"><?php echo $title; ?></h4>

        <div class="pop_order_add_item">
            <div class="header">
			<h5 class="h5_header">&nbsp;</h5>
                <div class="btns">
                    <button type="button" class="shbtn lineblue add_cart" id="select_file"/><i class="fa-solid fa-arrow-up"></i> 일괄 등록</button>
                    <button type="button" class="shbtn clear_cart"  onClick="javascript:location.href='./주문서일괄등록_sample.xlsx'"/><i class="fa-solid fa-arrow-down"></i> 양식 받기</button>
                </div>
            </div>
			<div style="margin-top:10px;">
				<input type="file" name="excelfile" id="excelfile" style="display:none;" onchange="chk_file();value_chk(this.value);" accept=".xlsx">
			파일&nbsp;&nbsp;&nbsp;<input type="text" name="" id="file_info" readonly style="width:85%;"> <input type="button" class="shbtn small delete_cart" value="삭제" id="file_del" style="float:right;"/>
			</div>
            
        </div>
       

        <div id="popup_buttom">
            <div class="addoptionbuttons">
                <a href='#' class="order_add_close">
                    취소
                </a>
                <input type="submit" id="fileSubmitBtn" value="등록" />
            </div>
        </div>
    </div>
</form>
<!-- 엑셀 다운로드 -->
<div id="loading_excel">
  <div class="loading_modal">
    <p>엑셀파일 업로드 처리 중입니다.</p>
    <p>주문 수량에 따라 처리 시간이 오래 걸리 수 있습니다.</p>
	<p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
  </div>
</div>

<script>

    function chk_file(a){
		var fileVal = $("#excelfile").val();
		if( fileVal == "" ){
			$('#file_info').val("");
		}
		if( fileVal != "" ){
			var ext = fileVal.split('.').pop().toLowerCase(); //확장자분리
			//아래 확장자가 있는지 체크
			if($.inArray(ext, ['xlsx']) == -1){
			  alert('xlsx 파일만 업로드 할수 있습니다.');
			  document.order_upload.reset();
			  return false;
			}
		}else {
			if(a == 1){
				alert('업로드할 파일을 선택해 주세요.');
				return false;
			}
		}
		//document.order_upload.submit();
	}

	function value_chk(){
				//input file 태그.
				var file = document.getElementById('excelfile');
				//파일 경로.
				var filePath = file.value;
				//전체경로를 \ 나눔.
				var filePathSplit = filePath.split('\\'); 
				//전체경로를 \로 나눈 길이.
				var filePathLength = filePathSplit.length;
				//마지막 경로를 .으로 나눔.
				var fileNameSplit = filePathSplit[filePathLength-1].split('.');
				//파일명 : .으로 나눈 앞부분
				var fileName = fileNameSplit[0];
				//파일 확장자 : .으로 나눈 뒷부분
				var fileExt = fileNameSplit[1];
				//파일 크기
				var fileSize = file.files[0].size;

		$('#file_info').val(fileName+"."+fileExt);
	}
	
	var loading = false;

    

    $(function() {

		$('#select_file').click(function (e) {
			e.preventDefault();
			$("#excelfile").trigger("click");
		});
		
		$('#file_del').click(function () {
			document.order_upload.reset();
		});

        

        $(document).on("click", ".order_add_close", function (e) {
            e.preventDefault();

            $('#popup_order_upload', parent.document).hide();
            $('#hd', parent.document).css('z-index', 10);
        });

		$('#fileSubmitBtn').on("click",function(e){
			e.preventDefault();
			if($("#excelfile").val() == ""){
				alert('업로드할 파일을 선택해 주세요.');
				$("#excelfile").trigger("click");
				return false;
			}else{
				$('#loading_excel').show();
				
				var form = $('#excelfile')[0].files[0];
				var formData = new FormData();
				
				formData.append('files', form);
				 $.ajax({
					type: "POST",
					enctype: 'multipart/form-data',
					url: "./pop.order.upload_result.php",
					data: formData,
					processData: false,
					contentType: false,
					cache: false,
					timeout: 90000000,
					success: function (data) {
						$('#loading_excel').hide();
						if(data.msg != "ok"){
							alert(data.msg);
						}else{
							$("#search-btn", parent.document).trigger("click");
						}						
					},
					error: function (request, status, error) {
						//alert(JSON.stringify(request));
						alert(request.responseJSON.message);
						$('#loading_excel').hide();
					}
				});
			}
		});

        
        

    });

</script>

</body>
</html>
