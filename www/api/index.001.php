<?php
include_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
include_once('api.config.php');



class API {
	function Select() {

		global $config, $g5, $item_fild_array, $_GET;

		@extract($_GET);
		@extract($_POST);
		@extract($_SERVER);

		$data = array();

		$error_num=0;
		foreach ($item_fild_array as $fild_key => $fild_value) {
			if(!$$fild_key){
				$data[$fild_key]['message'] = $fild_value.'값이 없습니다.';
				$data[$fild_key]['errorYN'] = 'Y';
				$error_num++;
			}
		}

		if(!$error_num){

			$data['prodId']			= "{$prodId}";			//제품아이디
			$data['gubun']			= "{$gubun}";			//구분 ("00")
			$data['prodNm']			= "{$prodNm}";			//제품 명
			$data['itemId']			= "{$itemId}";			//품목 아이디
			$data['subItem']		= "{$subItem}";			//하위품목
			$data['prodSupPrice']	= "{$prodSupPrice}";	//공급가격
			$data['prodOflPrice']	= "{$prodOflPrice}";	//판매금액
			$data['ProdPayCode']	= "{$ProdPayCode}";		//급여코드
			$data['supId']			= "{$supId}";			//공급업체 아이디
			$data['prodColor']		= "{$prodColor}";		//색상 (ex “빨강|파랑|노랑” )
			$data['prodSym']		= "{$prodSym}";			//재질
			$data['prodWeig']		= "{$prodWeig}";		//중량
			$data['prodSize']		= "{$prodSize}";		//사이즈
			$data['prodQty']		= "{$prodQty}";			//주문가능수량
			$data['prodDetail']		= "{$prodDetail}";		//상세정보
			$data['regDtm']			= "{$regDtm}";			//최초등록일시
			$data['regUsrId']		= "{$regUsrId}";		//최초등록자 ID
			$data['regUsrIp']		= "{$regUsrIp}";		//최초등록자 IP (IPV6 포함 총 39자리)
			$data['supNm']			= "{$supNm}";			//공급업체 이름
			$data['prodImgAttr']	= "{$prodImgAttr}";		//[이미지 첨부파일 이름들]

			$data['file1']			= "{$file1}";			//[첫번쨰 이미지 파일]
			$data['file2']			= "{$file2}";			//[두번째 이미지 파일]
			$data['file3']			= "{$file3}";			//[두번째 이미지 파일]
			$data['file4']			= "{$file4}";			//[두번째 이미지 파일]
			$data['file5']			= "{$file5}";			//[두번째 이미지 파일]
			$data['file6']			= "{$file6}";			//[두번째 이미지 파일]
			$data['file7']			= "{$file7}";			//[두번째 이미지 파일]
			$data['file8']			= "{$file8}";			//[두번째 이미지 파일]
			$data['file9']			= "{$file9}";			//[두번째 이미지 파일]
			$data['file10']			= "{$file10}";			//[두번째 이미지 파일]

			/*
			$data['prodId']			= "{$it_id}";					//제품아이디
			$data['gubun']			= "{$ca_id}";					//구분 ("00")
			$data['prodNm']			= "{$it_name}";					//제품 명
			$data['itemId']			= "{$it_thezone}";				//품목 아이디
			$data['subItem']		= "";							//하위품목
			$data['prodSupPrice']	= "{$it_cust_price}";			//공급가격
			$data['prodOflPrice']	= "{$it_price}";				//판매금액
			$data['ProdPayCode']	= "";							//급여코드
			$data['supId']			= "{$pt_id}";					//공급업체 아이디
			$data['prodColor']		= "{$it_option_subject}";		//색상 (ex “빨강|파랑|노랑” )
			$data['prodSym']		= "";							//재질
			$data['prodWeig']		= "";							//중량
			$data['prodSize']		= "";							//사이즈
			$data['prodQty']		= "{$it_stock_qty}";			//주문가능수량
			$data['prodDetail']		= "{$it_explan}";				//상세정보
			$data['regDtm']			= "";							//최초등록일시
			$data['regUsrId']		= "";							//최초등록자 ID
			$data['regUsrIp']		= "";							//최초등록자 IP (IPV6 포함 총 39자리)
			$data['supNm']			= "";							//공급업체 이름
			$data['prodImgAttr']	= "";							//[이미지 첨부파일 이름들]

			$data['file1']			= "{$it_img1}";					//[첫번쨰 이미지 파일]
			$data['file2']			= "{$it_img2}";					//[두번째 이미지 파일]
			$data['file3']			= "{$it_img3}";					//[두번째 이미지 파일]
			$data['file4']			= "{$it_img4}";					//[두번째 이미지 파일]
			$data['file5']			= "{$it_img5}";					//[두번째 이미지 파일]
			$data['file6']			= "{$it_img6}";					//[두번째 이미지 파일]
			$data['file7']			= "{$it_img7}";					//[두번째 이미지 파일]
			$data['file8']			= "{$it_img8}";					//[두번째 이미지 파일]
			$data['file9']			= "{$it_img9}";					//[두번째 이미지 파일]
			$data['file10']			= "{$it_img10}";				//[두번째 이미지 파일]
			*/

		}

		return json_encode($data);


	}
}

$API = new API;
header('Content-Type: application/json');
echo $API->Select();
?>