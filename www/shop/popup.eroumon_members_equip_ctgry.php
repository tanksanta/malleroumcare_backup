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
    /* //  * (주)티에이치케이컴퍼 & 이로움Care & 이로움ON - [ THKcompany & EroumCare & EroumON ] */
    /* //  *  */
    /* //  * Program Name : EROUMCARE Platform! & EroumON 1:1 Matching Service Ver:1.0 */
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
    /* // 파일명 : www\shop\popup.eroumon_members_simplesearch.php */
    /* // 파일 설명 : 해당 파일은 이로움ON에서 상담 배정을 받은 수급자 정보에 L넘버(요양인정번호)가 있으며, */
    /*                 상담유형이 '요양정보상담' 인경우 화면의 '상세보기'버튼에서 본 파일의 URL을 호출 한다. */
    /*                 이 경우, 기존 이로움Care에서 수급자 조회하던 프로세스를 진행하지 않고, 새루운 로직으로 틸코API 데이터를 가져와 가공하여 화면에 표현한다. */
    /*                 해당 페이지에서 조회된 수급자는 브라우저 로컬 데이터베이스에 저장하며, 조회 시점부터 1시간 동안 유효한다. */
    /*                 수급자 정보가 1시간 이내 재 조회 인경우 틸코API를 호출하지 않으며, 브라우저에 저장된 내용을 보여주며, 1시간이 초과된 시점엔 틸코API를 재조회 한다. */
    /*                  */
    /*   !!! 주의 !!!   */
    /* 본 파일은 틸코API의 정보를 사용하며, "www\shop\ajax.recipient.inquiry.php" 경로의 데이터를 회신받아 재가공하여 표현 한다. */
    /*  이에, 해당 파일 경로의 내용이 수정 될 경우 아래 코드의 변경은 불가피하며, 변경내역에 대한 코드 수정이 필요할 수도 있다. */
    /*                  */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    include_once('./_common.php');

    if( !$is_member ) { alert("먼저 로그인하세요."); }
    if( $member['mb_type'] !== 'default' ) { alert("사업소 회원만 접근 가능합니다."); }

	$_errCK = false;
	$_msg = "";
	$_url = "";
    if( ($member["cert_reg_sts"]!="Y") || (!$member["cert_data_ref"]) ) { 
		$_errCK = true;
		$_msg = "공인인증서 등록 후 확인 가능합니다. \\n회원정보 수정 메뉴에서 공인인증서 등록 후 사용해주세요.";
		$_url = "/bbs/member_confirm.php?url=member_info_newform.php&STEP=stop04";
	}
	if( $member["cert_data_ref"] ) {
        $cert_data_ref =  explode("|",$member["cert_data_ref"]);
        if( strtotime(base64_decode($cert_data_ref[2])." 23:59:59") < time() ) { 
			$_errCK = true;
			$_msg = "등록된 인증서의 기간이 만료 되었습니다. \\n회원정보 수정 메뉴에서 새로운 공인인증서를 등록 후 사용해주세요.";
			$_url = "/bbs/member_confirm.php?url=member_info_newform.php&STEP=stop04";
		}
    }
	$_consltID = $_POST['consltID'];//
	// == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 시작
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    if( $eroumon_connect_db ) {
	$sql = ("SELECT CTGRY_NM,CARE_CTGRY_CD FROM `MBR_CONSLT_GDS` WHERE CONSLT_NO='{$_consltID}';");
        $sql_result2 = "";
        $sql_result2 = sql_query( $sql , "" , $g5['eroumon_db'] ); mysqli_next_result($g5['eroumon_db']);
		
    }
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // SQL 처리 부분 종료
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==

    $_penNum = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['penNum'] ) ) );
    $_penNm = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['penNm'] ) ) );    
    if( !$_penNm ) { alert("수급자 정보를 확인할 수 없습니다."); }


	/* SQL 처리 부분 시작 ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ==== */
	
	$sql = "	SELECT ProdPayCode 
				FROM g5_shop_item 
				WHERE (ca_id LIKE '1070%') OR (ca_id2 LIKE '1070%') 
				GROUP BY ProdPayCode
	";
	$sql_result = sql_query($sql);
	$data_array = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);
	$prodPayCodeArray = [];
	$prodPayCodeArray = array_column($data_array, "ProdPayCode");

	/* SQL 처리 부분 종료 ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ====  ==== */


$img = array(//품목별 이미지 번호
				'성인용보행기10'=> 1
				,'수동휠체어20'=> 2
				,'지팡이10'=> 3
				,'안전손잡이10'=> 4
				,'미끄럼방지매트10'=> 5
				,'미끄럼방지양말10'=> 6
				,'욕창예방매트리스10'=> 7
				,'욕창예방방석10'=> 8
				,'자세변환용구10'=> 9
				,'요실금팬티10'=> 10
				,'목욕의자10'=> 11
				,'간이변기10'=> 12
				,'이동변기10'=> 13
				,'경사로10'=> 14
				,'경사로20'=> 15
				,'전동침대20'=> 16
				,'수동침대20'=> 17
				,'이동욕조20'=> 18
				,'목욕리프트20'=> 19
				,'배회감지기20'=> 20
				,'욕창예방매트리스20'=> 7
			);

$img2 = array(//품목별 이미지 번호
				'성인용보행기10'=> 1
				,'수동휠체어20'=> 2
				,'지팡이10'=> 3
				,'안전손잡이10'=> 4
				,'미끄럼방지용품_매트/방지액10'=> 5
				,'미끄럼방지용품_양말10'=> 6
				,'욕창예방매트리스10'=> 7
				,'욕창예방방석10'=> 8
				,'자세변환용구10'=> 9
				,'요실금팬티10'=> 10
				,'목욕의자10'=> 11
				,'간이변기10'=> 12
				,'이동변기10'=> 13
				,'경사로(실내용)10'=> 14
				,'경사로(실외용)20'=> 15
				,'전동침대20'=> 16
				,'수동침대20'=> 17
				,'이동욕조20'=> 18
				,'목욕리프트20'=> 19
				,'배회감지기20'=> 20
				,'욕창예방매트리스20'=> 21
			);

$img3 = array(//품목별 이미지 번호
				'성인용보행기10'=> 1
				,'수동휠체어20'=> 2
				,'지팡이10'=> 3
				,'안전손잡이10'=> 4
				,'미끄럼방지매트10'=> 5
				,'미끄럼방지양말10'=> 6
				,'욕창예방매트리스10'=> 7
				,'욕창예방방석10'=> 8
				,'자세변환용구10'=> 9
				,'요실금팬티10'=> 10
				,'목욕의자10'=> 11
				,'간이변기10'=> 12
				,'이동변기10'=> 13
				,'경사로10'=> 14
				,'경사로20'=> 15
				,'전동침대20'=> 16
				,'수동침대20'=> 17
				,'이동욕조20'=> 18
				,'목욕리프트20'=> 19
				,'배회감지기20'=> 20
				,'욕창예방매트리스20'=> 21
				);

$pro_title = array(//품목명 변경
				'성인용보행기10'=>'성인용보행기'
				,'수동휠체어20'=>'수동휠체어'
				,'지팡이10'=>'지팡이'
				,'안전손잡이10'=>'안전손잡이'
				,'미끄럼방지매트10'=>'미끄럼방지용품_매트/방지액'
				,'미끄럼방지양말10'=>'미끄럼방지용품_양말'
				,'욕창예방매트리스10'=>'욕창예방매트리스(판매)'
				,'욕창예방방석10'=>'욕창예방방석'
				,'자세변환용구10'=>'자세변환용구'
				,'요실금팬티10'=>'요실금팬티'
				,'목욕의자10'=>'목욕의자'
				,'간이변기10'=>'간이변기'
				,'이동변기10'=>'이동변기'
				,'경사로10'=>'경사로(실내용)'
				,'경사로20'=>'경사로(실외용)'
				,'전동침대20'=>'전동침대'
				,'수동침대20'=>'수동침대'
				,'이동욕조20'=>'이동욕조'
				,'목욕리프트20'=>'목욕리프트'
				,'배회감지기20'=>'배회감지기'
				,'욕창예방매트리스20'=>'욕창예방매트리스(대여)'
			);

?>

<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="initial-scale=1.0,user-scalable=yes,maximum-scale=1,width=device-width" />
    	<meta http-equiv="imagetoolbar" content="no">

        <title>simpleSearch</title>
      
        <script src="<?=G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
        <script src="<?=G5_JS_URL ?>/jquery-ui.min.js"></script>
	    <link rel="stylesheet" href="<?=G5_ADMIN_URL; ?>/css/popup.css?v=<?=APMS_SVER;?>">
		<!-- bootstrap -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
	 <!-- font swesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
	<link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
    </head>

    <body>

        <!-- 고정 상단 -->
        <div id="popupHeaderTopWrap">
            <div class="title">수급자 상담 정보 상세</div>
            <div class="close" style="padding-right:0px;"> <a href="javascript:void(0);" onclick="setClose();" > &times; </a> </div>
        </div>
        
        <div style="height:60px;"></div>

		<div class="d-flex flex-column justify-content-center" style="width: 100%">
			<div class="d-flex flex-column">     
				<p class="fs-2 fw-bolder">복지용구상담</p>
				<p class="fs-5 p-3" style="background-color: #eee;">상담받을 연락처  : <span class="fw-bolder"><?=$_POST['MBR_TELNO']?></span></p>           
			</div>
			<div class="d-flex flex-column ps-3">  
				<p class="fs-6 fw-bolder mb-1">어르신 관심 품목 (<?=sql_num_rows($sql_result2)?>개)</p>
				<p class="text-secondary" style="font-size:14px;">※ 요양정보(계약완료/구매예상)는 데이터 조회 시점에 따라 실제와 다를 수 있으니 참고용으로만 사용바랍니다</p>
			</div>
			<div>
			<div class="d-flex flex-row gap-3 ml mb-3">
<?php 
	$arr_item = [];
	for($i=1;$row = sql_fetch_array($sql_result2);$i++){
		$pro_titles = str_replace("(판매)","",$pro_title[$row["CTGRY_NM"].substr($row["CARE_CTGRY_CD"],0,2)]);
		$pro_titles = str_replace("(대여)","",$pro_titles);
		$arr_item[] = $pro_titles;
		if($i != 1 && $i%4 == 1){?>
			</div>
			</div>
			<div id="" class="d2">
			<div class="d-flex flex-row gap-3 ml mb-3 ">
<?php	}	?>
				<div class="card p-3" style="width: 23%;">
				  <div style="height: 40px">
						<font class="card-title fw-bold" style="font-size:14px;"><?=$pro_title[$row["CTGRY_NM"].substr($row["CARE_CTGRY_CD"],0,2)]?></font>             
				  </div>
				  <div class="p-3 d-flex justify-content-center" style="height: 80px;"><img src="https://eroum.co.kr/html/page/index/assets/images/img-checkpoint<?=$img[$row["CTGRY_NM"].substr($row["CARE_CTGRY_CD"],0,2)]?>.png" alt="card-group-image" style="height:100%;"></div>
				  <div class="d-flex flex-column align-items-center">
					<span class="d-flex gap-4" style="font-size:14px;width:100%;">계약완료 <span class="fw-bolder" style="width:25%;text-align:center;" id="e_count<?=$img3[$row["CTGRY_NM"].substr($row["CARE_CTGRY_CD"],0,2)]?>">-</span></span>
					<span class="d-flex gap-4" style="font-size:14px;width:100%;">구매예상 <span class="fw-bolder" style="width:25%;text-align:center;" id="s_count<?=$img3[$row["CTGRY_NM"].substr($row["CARE_CTGRY_CD"],0,2)]?>">-</span></span>
				  </div>
				</div>
<?php }?>
				  </div>
			  </div>
<?php if($i > 5){?>			  
			  <div class="d-flex justify-content-center py-3 ">
				<button type="button" class="btn btn-outline-secondary" id="b1">더보기 <i class="fa-solid fa-angle-down"></i></button>
				<button type="button" class="btn btn-outline-secondary" id="b2">숨기기 <i class="fa-solid fa-angle-up"></i></button>
			  </div>
<?php }?>         
		</div>	

		<div id="" style="width: 100%; padding: 15px 0px ; border: #ddd 2px solid; background-color: #f5f5f5; text-align: center; vertical-align: middle;border-radius: 0.3rem;font-size: 14px; font-weight:bold;color:#777;"> 
			<input type="hidden" id="penNum" value="<?=$_penNum?>">
			수급자 성명&nbsp;&nbsp;<input type="text" id="penNm" value="<?=$_penNm?>" style="background-color:#ddd;width:80px;" class="input-sm" readonly>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			요양인정번호&nbsp;&nbsp;<input type="text" id="penNum2" value="<?=($_penNum !="")?"있음":"";?>" placeholder="L을 제외한 숫자만 입력" <?=($_penNum !="")?"readonly":"oninput=\"this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');\"";?>  style="background-color:#ddd;width:170px;" maxlength="10" class="input-sm">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="button" value="조회하기" id="recipient_info" class="btn" style="padding:5px 15px;background-color:#333333;color:#fff;font-size: 14px;"> 
		</div>
		<div style="height:20px;"></div>
	    <div class="head"><?php //==================요양정보 구간 ?>
	    	
			<input type="hidden" id="APDT_FR_DT">
			<input type="hidden" id="APDT_TO_DT">

	        <p class="head-title"><span class="panNm">이로움</span>님의 요양정보</span></p>
	    
	        <div class="rep_amount" style="padding:19px 0px 4px 0px;">
	            <p style="color: #ee0000;">급여 잔액 : <span class = "rem_amount">1,600,000</span>원</p>
	            <p>사용 금액 : <span class ="used_amount">0</span>원</p>
	        </div>
	    
	        <div class="rep_info">
	            <table>
	              <colgroup>
	                <col width="28%"/>
	                <col width="22%"/>
	                <col width="28%"/>
	                <col width="22%"/>
	              </colgroup>
	              <tr>
	                <td colspan="1"><span>인정등급</span></td>
	                <td colspan="1" class = "penRecGraNm"> - 등급</td>
	                <td colspan="1"><span>본인부담율</span></td>
	                <td colspan="1" class = "penTypeNm"> - %</td>
	              </tr>
	              <tr>
	                <td colspan="1"><span>인정유효기간</span></td>
	                <td colspan="3" class = "penExpiDtm"> ~ </td>
	              </tr>
	              <tr>
	                <td colspan="1"><span>적용기간</span></td>
	                <td colspan="3" class = "penAppDtm"> ~ </td>
	              </tr>
	            </table>

	        </div>

			<div class="separator">
				<p><span class="line"> </span> </p>
			</div>

			<div class="contents">
				<p class="sub_title" > 판매 급여 품목</p>

				<table>
					<colgroup>
						<col width="10%"/>
						<col width="10%"/>
						<col width="10%"/>
						<col width="10%"/>
						<col width="10%"/>
						<col width="10%"/>
						<col width="5%"/>
						<col width="5%"/>
						<col width="10%"/>
						<col width="20%"/>
					</colgroup>
					
					<thead>
						<tr>
							<th colspan="1">No</th>
							<th colspan="4">품목명</th>
							<th colspan="2">급여유무</th>
							<th colspan="2">계약완료</th>
							<th colspan="1">판매가능</th>
						</tr>
					</thead>

					<tbody id = "table_sale">
						<tr>
							<td colspan="10" style="padding: 8% 0%; border-left-style:none; border-right-style:none;">
								조회된 구매 품목이 없습니다.
							</td> 
						</tr>
					</tbody>
				</table>

				<div class="separator">
					<p><span class="line"> </span> </p>
				</div>

				<p class="sub_title" > 대여 급여 품목</p>

				<table>
					<colgroup>
						<col width="10%"/>
						<col width="10%"/>
						<col width="10%"/>
						<col width="10%"/>
						<col width="10%"/>
						<col width="10%"/>
						<col width="5%"/>
						<col width="5%"/>
						<col width="10%"/>
						<col width="20%"/>
					</colgroup>
					<thead>
						<tr>
							<th colspan="1">No</th>
							<th colspan="4">품목명</th>
							<th colspan="2">급여유무</th>
							<th colspan="2">계약완료</th>
							<th colspan="1">대여가능</th>
						</tr>
					</thead>
					<tbody id = "table_rental">
						<tr>
							<td colspan="10" style="padding: 8% 0%; border-left-style:none; border-right-style:none;">
								조회된 대여 품목이 없습니다.
							</td>
						</tr>
					</tbody>
				</table>

			</div>

	    </div>
        
        <div style="height:30px;"></div>

        <!-- 고정 하단 -->
        <div id="popupFooterBtnWrap">
            <a href="javascript:void(0);" class="btn_close" onclick="setClose();" > 확인 </a>
        </div>

		<!-- 로딩 중 -->
		<div id="loading" style="display:none;">
			<div>
				<img src="/img/loading_apple.gif" class="img-responsive">
				<p>정보를 불러오고 있습니다.<br>잠시만 기다려주세요.</p>
			</div>
		</div>

        <!-- 인증서 업로드 추가 영역 -->
        <div id="cert_ent_num_popup_box"><iframe name="cert_ent_num_iframe" src="" scrolling="no" frameborder="0" allowTransparency="false"></iframe></div>
        <div id="cert_popup_box"><iframe name="cert_iframe" src="" scrolling="no" frameborder="0" allowTransparency="false"></iframe></div>
        <div id="cert_guide_popup_box"><iframe name="cert_guide_iframe" src="" scrolling="no" frameborder="0" allowTransparency="false"></iframe></div>
        <iframe name="tilko" id="tilko" src="" scrolling="no" frameborder="0" allowTransparency="false" height="0" width="0"></iframe>

		<style>
			body {
				padding: 0 5%;
			}

			.head-title {
				font-size: x-large;
			}

			.head span {
				font-weight: bold;
			}

			.head-title {
				font-size: x-large;
			}

			.head { 
				width: 100%;
				padding-top:1%;
				display:none;
			}
			
			.head .rep_amount {
			    width: 40%;
			    margin: auto;
			    float: left;
			    border: #ddd 2px solid;
			    background-color: #f5f5f5;
			    text-align: center;
			    vertical-align: middle;
			}

			.head .rep_amount p {
				font-size: large;
			}
			
			.head .rep_info { 
				width: 58%;
				float: right;
				border: #ddd 1px solid;
			}
			
			.head .rep_info table { 
				width: 100%;
			 	border: 1px solid #ddd;
			 	border-collapse: collapse;
			}
			
			.head .rep_info td { 
				font-weight: normal;
			 	border: 1px solid #ddd;
			 	text-align: center;
                padding: 6px 0;
			}

			.separator p .line {
				content: ' ';
				width: 100%;
				background: #A6A6A6;
				height: 3px;
				margin: auto;
				display: inline-block;
				margin-top: 0.5%;
				margin-bottom: 0.5%;
			}

			.sub_title {
				font-weight: bold;
				font-size: large;
				padding-top: 1%;
			}

			.contents {
				width: 100%;
				padding: unset;
			}

			.contents table {
			    width: 100%;
			    height: fit-content;
			    border-collapse: collapse;
			    margin-bottom: 3%;
			}

			.contents thead {
				background-color: #F2F2F2;
			}

			.contents th {
			    border: 1px solid #ddd;
			    border-left-style:none;
			    border-right-style:none;
			    text-align: center;
			    font-size: medium;
			    font-weight: bold;
			    padding: 0.8% 0%;
			}

			.contents thead {
				font-weight: normal;
			}

			.contents td {
			    font-weight: normal;
			    border: 1px solid #ddd;
			    text-align: center;
			    font-size: 14px;
			    padding: 0.5% 0%;
			}

			.contents .NotApplicable {
				background-color: #f5f5f5;
			}

            .normal-row td {
                padding: 0.8% 0%;
            }
			
			.d2,#b2{
				display:none;
			}

			#loading {
				background-color: rgba(0,0,0,0.7);
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				z-index : 9999999999999999 !important;
			}

			#loading > div {
				position: relative;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				text-align: center;

			}

			#loading img {
				top: 50%;
				width: 60px;
				position: relative;
			}

			#loading p {
				color: #fff;
				position: relative;
				top: -25px;
				margin-top: 40px;
				font-size: 20px;
				line-height: 40px;
			}

            @media (max-width: 480px) {
                .head-title {font-size: large;}
                .head .rep_amount p {font-size: medium;}
                .sub_title {font-weight: bold; font-size: medium;}
                .head .rep_amount {border: 1px; height: 60%;}
                .head .rep_info {border: 0.5px; height: 60%;}
                .head .rep_info table {border: 0.5px;}
                .head .rep_info td {font-size: small;}
                .contents th {font-size: small;}
                .contents td {font-size: small;}
            }

            @media (max-width: 400px) {
                .head-title {font-size: medium;}
                .head .rep_info td {font-size: x-small;}
                .contents th {font-size: x-small;}
                .contents td {font-size: x-small;}
            }

            /* 고정 상단 */
            #popupHeaderTopWrap { position: fixed; width: 100%;  height:50px; left: 0; top: 0; z-index: 10; background-color: #333; padding: 12px 20px; }
            #popupHeaderTopWrap:after { display: block; content: ''; clear: both; }
            #popupHeaderTopWrap > div { height: 100%; line-height: 22px; }
            #popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 16px; line-height: 28px; }
            #popupHeaderTopWrap > .close { float: right; padding-right: 32px; }
            #popupHeaderTopWrap > .close > a { color: #FFF; font-size: 30px; top: -2px; text-decoration: none; }

            /* 고정 하단 */
            #popupFooterBtnWrap { position: fixed; left: 0px; width: 100%; height: 50px; background-color: #fff; bottom: 0px; z-index: 10; text-align:center; }
            #popupFooterBtnWrap > a { color: #fff; -webkit-text-stroke: medium; line-height: 22px; text-decoration: none; padding: 10px 5px; }

            .btn_close{ margin-top: 4px; align-items: center; border-radius: 0.5rem; border: 0px; background-color: #333;  display: inline-flex; font-weight: 500; justify-content: center; line-height: 1; padding: 1.25rem 0.125rem; width: 100px; --tw-shadow: 0px 0.154em 0.154em #00000027; --tw-shadow-colored: 0px 0.154em 0.154em var(--tw-shadow-color); box-shadow: var(--tw-ring-offset-shadow,0 0 #0000),var(--tw-ring-shadow,0 0 #0000),var(--tw-shadow); }

			#cert_popup_box {
				display: none;
				position: fixed;
				width: 100%;
				height: 100%;
				left: 0;
				top: 0;
				z-index:9999;
				background: rgba(0, 0, 0, 0.5);
			}

			#cert_popup_box iframe {
				width:322px;
				height:307px;
				max-height: 80%;
				position: absolute;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				background: white;
			}

			#cert_guide_popup_box {
				display: none;
				position: fixed;
				width: 100%;
				height: 100%;
				left: 0;
				top: 0;
				z-index:9999;
				background: rgba(0, 0, 0, 0.5);
			}
			#cert_guide_popup_box iframe {
				width:850px;
				height:750px;
				position: absolute;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				background: white;
			}
			#cert_ent_num_popup_box {
				display: none;
				position: fixed;
				width: 100%;
				height: 100%;
				left: 0;
				top: 0;
				z-index:9999;
				background: rgba(0, 0, 0, 0.5);
			}
			#cert_ent_num_popup_box iframe {
				width:300px;
				height:305.33px;
				position: absolute;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				background: white;
			}
			.input-sm {
				padding: 5px 10px;
				font-size: 14px;
				line-height: 1.5;
				border:0px;
				font-weight:bold;
				color:#777;
			}
		</style>

	    <script>

	        $(function () {	
                $('#b1').click(function() {//더보기 클릭                   
					$('.d2').fadeIn('fast');
					$('#b2').show();
					$('#b1').hide();
                });

				$('#b2').click(function() {//숨기기 클릭
                    $('.d2').hide();
					$('#b2').hide();
					$('#b1').show();
                });

				$('#cert_popup_box').click(function() {
                    $('body').removeClass('modal-open');
                    $('#cert_popup_box').hide();
                });

                $('#cert_guide_popup_box').click(function() {
                    $('body').removeClass('modal-open');
                    $('#cert_guide_popup_box').hide();
                });

                $('#cert_ent_num_popup_box').click(function() {
                    $('body').removeClass('modal-open');
                    $('#cert_ent_num_popup_box').hide();
                });

                
				$('#recipient_info').click(function() {
//수급자 조회 관련 추가, 개발완료 시 삭제 필요====================================================================
		swal("사용 제한","수급자 조회조건 개선으로 간편조회 및\n일부 서비스가 일시 중단되었습니다.\n서비스 재개는 추후 공지를 통해 안내드리겠습니다.","error");
		return false;
//=======================================================================================================
<?php if( $_errCK ) {?>
		alert('<?=$_ms?>');
		window.parent.document.location.href = '<?=$_url?>';
		return false;
<?php }?>

					var ID = "<?=$_penNum;?>";//$("#penNum").val();
					const RN = "<?=$_penNm;?>";//$("#penNm").val();
					if(ID == ""){//요양인정번호 없을 경우
						if($("#penNum2").val() != ""){
							ID = $("#penNum2").val();
						}else{
							alert("요양인정번호를 입력해 주세요.");
							$("#penNum2").focus();
							return false;
						}
					}
									
					$(".panNm").text( RN );
					
					const RecipientInquiryDT = localStorage.getItem(ID+'_RecipientInquiryDT');
					const RecipientInquiryID = localStorage.getItem(ID+'_RecipientInquiryID');
					const currentDate = new Date();                
						  //currentDate.setHours(currentDate.getHours() + 2);

					// 동일 페이지 내에서 새로고침시 틸코 조회 제한.
					$("#loading").show();
					if( (Number(RecipientInquiryDT) > currentDate.getTime()) && (RecipientInquiryID === ID) ) {

						const ds_welToolTgtList = JSON.parse(localStorage.getItem(ID+'_ds_welToolTgtList'));
						const ds_toolPayLmtList = JSON.parse(localStorage.getItem(ID+'_ds_toolPayLmtList'));
						const recipientToolList = JSON.parse(localStorage.getItem(ID+'_recipientToolList'));
						const ds_ctrHistTotalList = JSON.parse(localStorage.getItem(ID+'_ds_ctrHistTotalList'));
	/*
						console.log( "== == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == " );
						console.log( ds_welToolTgtList );
						console.log( "== == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == " );
						console.log( ds_toolPayLmtList );
						console.log( "== == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == " );
						console.log( recipientToolList );
						console.log( "== == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == " );
						console.log( ds_ctrHistTotalList );
						console.log( "== == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == " );
	*/
						
						Set_Display( ds_welToolTgtList, ds_toolPayLmtList, recipientToolList, ds_ctrHistTotalList );

					} else {

						// ajax 처리 시작
						$.ajax({
							url: '/shop/ajax.recipient.inquiry.php', type: 'POST', dataType: 'json', 
							data: { id : ID, rn : RN },
							success: function( result ) {

								const ds_welToolTgtList = result.data.recipientContractDetail.Result.ds_welToolTgtList[0];
								const ds_toolPayLmtList = result.data.recipientContractDetail.Result.ds_toolPayLmtList;
								const recipientToolList = result.data.recipientToolList;
								const ds_ctrHistTotalList = result.data.recipientContractDetail.Result.ds_ctrHistTotalList
	/*
								console.log( "== == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == " );
								console.log( ds_welToolTgtList );
								console.log( "== == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == " );
								console.log( ds_toolPayLmtList );
								console.log( "== == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == " );
								console.log( recipientToolList );
								console.log( "== == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == " );
								console.log( ds_ctrHistTotalList );
								console.log( "== == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == " );
	*/
								// 로컬 스토리지 저장.
								localStorage.setItem(ID+'_ds_welToolTgtList', JSON.stringify(ds_welToolTgtList));
								localStorage.setItem(ID+'_ds_toolPayLmtList', JSON.stringify(ds_toolPayLmtList));
								localStorage.setItem(ID+'_recipientToolList', JSON.stringify(recipientToolList));
								localStorage.setItem(ID+'_ds_ctrHistTotalList', JSON.stringify(ds_ctrHistTotalList));

								const rDT = new Date();
									  rDT.setHours(rDT.getHours() + 1);

								localStorage.setItem(ID+'_RecipientInquiryDT', rDT.getTime());
								localStorage.setItem(ID+'_RecipientInquiryID', ID);

								Set_Display( ds_welToolTgtList, ds_toolPayLmtList, recipientToolList, ds_ctrHistTotalList );

							},
							error: function( error ) {

								if( error.responseJSON == undefined ) {
									alert( "수급자의 요양정보를 확인할 수 없습니다." );
									setClose();
									return;
								}

								let errorMessage = error.responseJSON.message;
								let errorCode = Number(error.responseJSON.data.err_code);
								
								//console.log( errorMessage );
								//console.log( errorCode );

								if( errorCode === 1 ) { alert("공인인증서가 등록되어어 있지 않습니다.\n<?=(!$_isMobile)?' 컴퓨터에서':'';?> 공인인증서를 재등록 해 주세요."); }
								else if( errorCode === 2 ) { pwd_insert(); }
								else if( errorCode === 3 ) { alert("등록된 인증서가 사용 기간이 만료 되었습니다.\n<?=(!$_isMobile)?' 컴퓨터에서':'';?> 공인인증서를 재등록 해 주세요."); }
								else if( errorCode === 4 ) { alert("수급자의 요양정보를 확인할 수 없습니다."); }
								else if( errorCode === 5 ) { ent_num_insert(); }

								$("#loading").hide();

								return false;

							}

						});
						// ajax 처리 종료

					}
				});

	        });

			const ItemCnt = {
				'간이변기': 2
				,'경사로(실내용)': 6
				,'경사로(실외용)': 1
				,'미끄럼방지용품': 11
				,'미끄럼방지용품_매트/방지액': 5
				,'미끄럼방지용품_시스템미등록': 5
				,'미끄럼방지용품_양말': 6
				,'목욕의자': 1
				,'목욕리프트': 1
				,'배회감지기': 1
				,'성인용보행기': 2
				,'수동침대': 1
				,'수동휠체어': 1
				,'안전손잡이': 10
				,'이동변기': 1
				,'이동욕조': 1
				,'자세변환용구': 5
				,'전동침대': 1
				,'지팡이': 1
				,'욕창예방매트리스': 1
				,'욕창예방방석': 1
				,'요실금팬티': 4
			};

			const UseDurable = {
				'경사로(실내용)': 2
				,'목욕의자': 5
				,'성인용보행기': 5
				,'욕창예방매트리스': 3
				,'욕창예방방석': 3
				,'이동변기': 5
				,'지팡이': 2
			};

			const tmpItemList = <?=json_encode($prodPayCodeArray)?> ;

			function Set_Display( ds_welToolTgtList, ds_toolPayLmtList, recipientToolList, ds_ctrHistTotalList ) {
				$(".head").show();
				// 수급자 정보 셋팅
				pen_userSet_Info( ds_welToolTgtList );

				// 수급자 유효,적용 기간 셋팅
				pen_userSet_ApplyDt( ds_toolPayLmtList );
				
				// 수급자 급여 가능 품목 셋팅
				let tableList = pen_userSet_ItemList( recipientToolList );
				
				// 수급자 계약 및 판매 카운트 셋팅
				let ToolList = pen_userSet_ItemCnt( tableList, ds_ctrHistTotalList );

				// 최종 화면 그리기
				pen_listSet_Item( ToolList, ItemCnt );

				// 팝업 로딩 화면 닫기
				$("#loading").hide();

			}

			function pen_userSet_Info( ds_welToolTgtList ) {

				if(ds_welToolTgtList['REDUCE_NM'] == '감경') { //REDUCE_NM가 대상자 구분, 감경은 SBA_CD를 이용하여 본인부담율을 가져오기
					var penPayRate_api = ds_welToolTgtList['SBA_CD'].replace('(', ' ').replace(')', '');
				} else {
					var penPayRate_api = ds_welToolTgtList['REDUCE_NM'] == '일반' ? '일반 15%'
											: ds_welToolTgtList['REDUCE_NM'] == '기초' ? '기초 0%'
											: ds_welToolTgtList['SBA_CD'] == '일반' ? '일반 15%'
											: ds_welToolTgtList['SBA_CD'] == '기초' ? '기초 0%'
											: ds_welToolTgtList['SBA_CD'];
				}

				// 수급자 이름
				$(".panNm").text( ds_welToolTgtList['FNM'] );
				
				// 조회날짜
				$("#search_date").text( "(조회 : "+ds_welToolTgtList['UPDATE']+")" );
				
				// 인정등급
				$(".penRecGraNm").text( ds_welToolTgtList['LTC_RCGT_GRADE_CD']+"등급" );
	            
	            // 본인부담율
	            $(".penTypeNm").text( penPayRate_api );
	            
	            // 인정유효기간
	            $(".penExpiDtm").text( ds_welToolTgtList['RCGT_EDA_DT'] );
        	}


        	function pen_userSet_ApplyDt( ds_toolPayLmtList ) {
        		let today = new Date();

		        for(var ind = 0; ind < ds_toolPayLmtList.length; ind++) {

		            var appst = new Date( ds_toolPayLmtList[ind]['APDT_FR_DT'].substr(0,4) + '-' + ds_toolPayLmtList[ind]['APDT_FR_DT'].substr(4,2) + '-' + ds_toolPayLmtList[ind]['APDT_FR_DT'].substr(6,2)+" 00:00:00" );
		            var apped = new Date( ds_toolPayLmtList[ind]['APDT_TO_DT'].substr(0,4) + '-' + ds_toolPayLmtList[ind]['APDT_TO_DT'].substr(4,2) + '-' + ds_toolPayLmtList[ind]['APDT_TO_DT'].substr(6,2)+" 23:59:59" );

		            if(today < apped && today > appst) {
		                applydtm = ds_toolPayLmtList[ind]['APDT_FR_DT'].substr(0,4) + '-' + 
		                			ds_toolPayLmtList[ind]['APDT_FR_DT'].substr(4,2) + '-' + 
		                			ds_toolPayLmtList[ind]['APDT_FR_DT'].substr(6,2) + ' ~ ' + 
		                			ds_toolPayLmtList[ind]['APDT_TO_DT'].substr(0,4) + '-' + 
		                			ds_toolPayLmtList[ind]['APDT_TO_DT'].substr(4,2) + '-' + 
		                			ds_toolPayLmtList[ind]['APDT_TO_DT'].substr(6,2);
						break;
		            }

		            if(ind == ds_toolPayLmtList.length-1) {
						applydtm = ds_toolPayLmtList[0]['APDT_FR_DT']+' ~ '+ds_toolPayLmtList[0]['APDT_TO_DT'];
		            }
		        }
				
				//적용기간
				$(".penAppDtm").text( applydtm );
				$("#APDT_FR_DT").val( applydtm.substr(0,10) );
				$("#APDT_TO_DT").val( applydtm.substr(13,10) );
			}

			
			function pen_userSet_ItemList( recipientToolList ) {
				recipientToolList = JSON.parse(recipientToolList)['Result'];

				var _arrayToolList = [];
					_arrayToolList['sale'] = [];
					_arrayToolList['rent'] = [];

				// 구매 - 가능 품목
				for(var i = 0; i < recipientToolList['ds_payPsbl1'].length; i++) {
					const ItemNM = recipientToolList['ds_payPsbl1'][i]['WIM_ITM_CD'].replace(' ','');
					if( ItemNM === "미끄럼방지용품" ) {
						_arrayToolList['sale'][ "미끄럼방지용품_양말" ] = { 'use': 1 };
						_arrayToolList['sale'][ "미끄럼방지용품_매트/방지액" ] = { 'use': 1 };
					}
					else { _arrayToolList['sale'][ ItemNM ] = { 'use': 1 }; }
				}

				// 구매 - 불가 품목
				for(var i = 0; i < recipientToolList['ds_payPsbl2'].length; i++) {
					const ItemNM = recipientToolList['ds_payPsbl2'][i]['WIM_ITM_CD'].replace(' ','');
					if( ItemNM === "미끄럼방지용품" ) { 
						_arrayToolList['sale'][ "미끄럼방지용품_양말" ] = { 'use': 0 };
						_arrayToolList['sale'][ "미끄럼방지용품_매트/방지액" ] = { 'use': 0 };
					}
					else { _arrayToolList['sale'][ ItemNM ] = { 'use': 0 }; }
				}

				// 대여 - 가능 품목
				for(var i = 0; i < recipientToolList['ds_payPsblLnd1'].length; i++) { 
					_arrayToolList['rent'][ recipientToolList['ds_payPsblLnd1'][i]['WIM_ITM_CD'].replace(' ','') ] = { 'use': 1 };
				}

				// 대여 - 불가 품목
				for(var i = 0; i < recipientToolList['ds_payPsblLnd2'].length; i++) { 
					_arrayToolList['rent'][ recipientToolList['ds_payPsblLnd2'][i]['WIM_ITM_CD'].replace(' ','') ] = { 'use': 0 };
				}

				return _arrayToolList;
			}


			function pen_userSet_ItemCnt( tableList, ds_ctrHistTotalList ) {
		        
		        const useFrDt = new Date( $("#APDT_FR_DT").val() + " 00:00:00" );
		        const useToDt = new Date( $("#APDT_TO_DT").val() + " 23:59:59" );
		        const totalmoney = 1600000;
		        let amount = 0;
 
				Object.entries(ds_ctrHistTotalList).forEach(([key, value]) => {

					const itemDt = new Date( value['POF_FR_DT'].substr(0,4) + '-' + value['POF_FR_DT'].substr(4,2) + '-' + value['POF_FR_DT'].substr(6,2) );

					//console.log( itemDt );
					//console.log( value['WIM_ITM_CD_NM'], value['POF_FR_DT'], value['WIM_CD'], value['PERIOD'], value['WEL_PAY_STL_NM'] );

					value['WIM_ITM_CD_NM'] = value['WIM_ITM_CD_NM'].replace(' ','');

					const ItemNM = value['WIM_ITM_CD_NM'];
					const DateY = UseDurable[ItemNM];

					let UseDurable_DT = itemDt;
					if( DateY ) {
						UseDurable_DT = UseDurable_DT.setFullYear( UseDurable_DT.getFullYear() + Number(DateY) );
						//console.log( UseDurable_DT );
					}

					if( value['WEL_PAY_STL_NM'] === "판매" ) {						

						if( useFrDt.getTime() > UseDurable_DT ) { return; }

						if( ItemNM === "미끄럼방지용품" ) {
							//console.log( value['WIM_CD'] );

							let val_ck = false;
								val_ck = tmpItemList.includes( value['WIM_CD'] );

							if( val_ck ) {
								if( tableList['sale'][ItemNM+"_양말"]['cnt'] ) { tableList['sale'][ItemNM+"_양말"]['cnt'] += 1; } else { tableList['sale'][ItemNM+"_양말"]['cnt'] = 1; }
							} else {
								if( tableList['sale'][ItemNM+"_매트/방지액"]['cnt'] ) { tableList['sale'][ItemNM+"_매트/방지액"]['cnt'] += 1; } else { tableList['sale'][ItemNM+"_매트/방지액"]['cnt'] = 1; }
							}

						} 
						else { if( tableList['sale'][ItemNM]['cnt'] ) { tableList['sale'][ItemNM]['cnt'] += 1; } else { tableList['sale'][ItemNM]['cnt'] = 1; } }
						
						//console.log( $("#APDT_FR_DT").val(), value['POF_FR_DT'].substr(0,4) + '-' + value['POF_FR_DT'].substr(4,2) + '-' + value['POF_FR_DT'].substr(6,2) );
						//console.log( useFrDt, itemDt, UseDurable_DT );
						//console.log( "=====>", useFrDt, value['POF_FR_DT'], itemDt, UseDurable_DT, value['TOT_AMT'], amount );

						const tmpitemDt = new Date( value['POF_FR_DT'].substr(0,4) + '-' + value['POF_FR_DT'].substr(4,2) + '-' + value['POF_FR_DT'].substr(6,2) );
						if( useFrDt.getTime() > tmpitemDt.getTime() ) { return; }
						amount += Number(value['TOT_AMT']);						

					}
					else if( value['WEL_PAY_STL_NM'] === "대여" ) {

						let period_Fr = "";
							period_Fr = new Date( value['PERIOD'].substr(0,4) + '-' + value['PERIOD'].substr(5,2) + '-' + value['PERIOD'].substr(8,2) );

						let period_To = "";							
		        			period_To = new Date( value['PERIOD'].substr(11,4) + '-' + value['PERIOD'].substr(16,2) + '-' + value['PERIOD'].substr(19,2) + " 23:59:59" );
						
						console.log(  value['PERIOD'].substr(11,4) + '-' + value['PERIOD'].substr(16,2) + '-' + value['PERIOD'].substr(19,2) );
						//console.log( "useFrDt", useFrDt.getTime(), "Fr", period_Fr.getTime(), "useToDt", useToDt.getTime(), "To", period_To.getTime() );
						console.log( "=====>", value['WIM_ITM_CD_NM'], value['POF_FR_DT'], value['WIM_CD'], value['PERIOD'], value['WEL_PAY_STL_NM'], useFrDt, value['POF_FR_DT'], itemDt, UseDurable_DT, value['TOT_AMT'], amount );

						if( (useFrDt.getTime() < period_Fr.getTime()) && ( useToDt.getTime() > period_Fr.getTime() ) ) {

							if( tableList['rent'][ItemNM]['cnt'] ) { tableList['rent'][ItemNM]['cnt'] += 1; } 
							else { tableList['rent'][ItemNM]['cnt'] = 1; }

							amount += Number(value['TOT_AMT']);
						}
					}

				});
				

				//급여잔액
				$(".rep_amount .rem_amount").text( makeComma(totalmoney - amount) );
				
				//사용금액
				$(".rep_amount .used_amount").text( makeComma(amount) );

				return tableList;
			}


			function pen_listSet_Item( TableList, ItemCnt ) {
				var arr_item = <?=json_encode($arr_item);?>;
				var arr_img = <?=json_encode($img2);?>;
				var sale_yok = 0;
				var rant_yok = 0;

				var tr_bg = "";
				var No = 0;
				let flag = true;

				Object.entries(TableList['rent']).forEach(([key, value]) => {
					if( key.includes( "욕창예방매트리스" ) && (value['cnt']?value['cnt']:0)>0) {
						rant_yok = 1;
					}
				});
				$('#table_sale').empty();  
				Object.entries(TableList['sale']).forEach(([key, value]) => {
					tr_bg = (arr_item.includes(key))? " style='background-color:#fff2f2'" : "";
					let tNo = No;
					let sale = "";
					let cnt_Sale = "";
					let cnt_Stock = "";

					if( value['use'] ) { 
						sale = `<font color='blue'>급여가능</font>`;
						if(key.includes( "욕창예방매트리스" ) && rant_yok == 1){//욕창예방매트리스 대여 있을 경우
							cnt_Sale = `0개`;
							cnt_Stock = `0개`;
						}else{
							if(key.includes( "욕창예방매트리스" ) && (value['cnt']?value['cnt']:0)>0){
								sale_yok = 1;
							}
							cnt_Sale = (value['cnt']?value['cnt']:0) + `개`;
							cnt_Stock = (value['cnt']?ItemCnt[key]-value['cnt']:ItemCnt[key]) + `개`;

							$("#e_count"+arr_img[key+'10']).text((value['cnt']?value['cnt']:0));
							$("#s_count"+arr_img[key+'10']).text((value['cnt']?ItemCnt[key]-value['cnt']:ItemCnt[key]));
						}
					} else { 
						sale = `<font color='red'>급여불가</font>`;
						cnt_Sale = `<div class='NotApplicable'>해당없음</div>`;
						cnt_Stock = `<div class='NotApplicable'>해당없음</div>`;

						$("#e_count"+arr_img[key+'10']).html("<font color='red'>X</font>");
						$("#s_count"+arr_img[key+'10']).html("<font color='red'>X</font>");
					}

					if( key.includes( "미끄럼방지용품" ) ) {
						if( flag ) { 
							tNo = ++No; 
							flag = false;
						}
						if( key.includes( "미끄럼방지용품_양말" ) ) { tNo = No + "-1"; }
						else if( key.includes( "미끄럼방지용품_매트/방지액" ) ) { tNo = (No) + "-2"; } 

					} else { tNo = ++No; }
					
					let row = `
						<tr id="" class="normal-row"`+tr_bg+`>
							<td colspan="1">` + tNo + `</td> <td colspan="4">` + key + `</td> <td colspan="2">` + sale + `</td> <td colspan="2">` + cnt_Sale + `</td> <td colspan="1">` + cnt_Stock + `</td>
						</tr>
					`;
					
					$('#table_sale').append( row );

				});


				var No = 1;
				$('#table_rental').empty();
				Object.entries(TableList['rent']).forEach(([key, value]) => {
					tr_bg = (arr_item.includes(key))? " style='background-color:#fff2f2'" : "";
					let sale = "";
					let cnt_Sale = "";
					let cnt_Stock = "";

					if( value['use'] ) { 
						sale = `<font color='blue'>급여가능</font>`;
						if(key.includes( "욕창예방매트리스" ) && sale_yok == 1){//욕창예방매트리스 대여 있을 경우
							cnt_Sale = `0개`;
							cnt_Stock = `0개`;
							$("#e_count"+arr_img[key+'20']).text("0");
							$("#s_count"+arr_img[key+'20']).text("0");
						}else{							
							let _Sale = (value['cnt']?value['cnt']:0);
							cnt_Sale = (_Sale>=1?1:0) + `개`;
							let _Stock = (value['cnt']?ItemCnt[key]-value['cnt']:ItemCnt[key]);
							cnt_Stock = (_Stock<0?"0":_Stock) + `개`;

							$("#e_count"+arr_img[key+'20']).text((_Sale>=1?1:0));
							$("#s_count"+arr_img[key+'20']).text((_Stock<0?"0":_Stock));
						}
					} else { 
						sale = `<font color='red'>급여불가</font>`;
						cnt_Sale = `<div class='NotApplicable'>해당없음</div>`;
						cnt_Stock = `<div class='NotApplicable'>해당없음</div>`;
						$("#e_count"+arr_img[key+'20']).html("<font color='red'>X</font>");
						$("#s_count"+arr_img[key+'20']).html("<font color='red'>X</font>");
					}

		            let row = `
		            	<tr id="" class="normal-row"`+tr_bg+`>
							<td colspan="1">` + No + `</td> <td colspan="4">` + key + `</td> <td colspan="2">` + sale + `</td> <td colspan="2">` + cnt_Sale + `</td> <td colspan="1">` + cnt_Stock + `</td>
						</tr>
					`;

					$('#table_rental').append( row );
					No++;

				});

			}

            // 공인인증서 비밀번호 입력 창 오픈
            function pwd_insert() {
                $('#cert_popup_box iframe').attr('src', '/shop/pop.certmobilelogin.php');
                $('body').addClass('modal-open');
                $('#cert_popup_box').show();
            }
            
            // 장기요양기관번호 입력 창 오픈
            function ent_num_insert() {
                $('#cert_ent_num_popup_box iframe').attr('src', '/shop/pop.ent_num.php');
                $('body').addClass('modal-open');
                $('#cert_ent_num_popup_box').show();
            }

            // 인증서 비밀번호 체크
            function cert_pwd( pwd ) {

                $.ajax({
                    type : "POST",            // HTTP method type(GET, POST) 형식이다.
                    url : "/ajax.tilko.php",      // 컨트롤러에서 대기중인 URL 주소이다.
                    data : { mode : 'pwd' ,Pwd : pwd }, 
                    dataType: 'json',// Json 형식의 데이터이다.
                    success : function( res ) { // 비동기통신의 성공일경우 success콜백으로 들어옵니다. 'res'는 응답받은 데이터이다.
                        location.reload();
                    },
                    error : function( error ) { // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
                        alert( error['responseJSON']['message'] );
                        pwd_insert();
                    }
                });
            }

            // 팝업창 닫기.
            function setClose() { 
                parent.$('.Popup_simpleSearch').hide();
                parent.$('body').removeClass('modal-open');                
            }

			function setDate( str_date ) { return str_date.substr(0,4)+'-'+str_date.substr(4,2)+'-'+str_date.substr(6,2); }
			function makeComma( str ) { str = String(str); return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,'); }

	    </script>

    </body>

</html>
