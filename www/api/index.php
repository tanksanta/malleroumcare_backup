<?php
include_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
include_once('api.config.php');

class API {
	function Item_Write() {

		global $config, $g5, $item_required_fild;

		@extract($_GET);
		@extract($_POST);
		@extract($_SERVER);

		$data = array();
		$error_code = array();

		$error_num=0;
		foreach ($item_required_fild as $fild_key => $fild_value) {
			if(!$$fild_key){
				$error_code[$fild_key] = $fild_value.'값이 없습니다.';
				$error_num++;
			}
		}

		// 상품요약정보
		$it_info_gubun = 'wear';	//상품요약
		$it_info_value = 'a:8:{s:8:"material";s:28:"상세설명페이지 참고";s:5:"color";s:28:"상세설명페이지 참고";s:4:"size";s:28:"상세설명페이지 참고";s:5:"maker";s:28:"상세설명페이지 참고";s:7:"caution";s:28:"상세설명페이지 참고";s:16:"manufacturing_ym";s:28:"상세설명페이지 참고";s:8:"warranty";s:28:"상세설명페이지 참고";s:2:"as";s:28:"상세설명페이지 참고";}';	//상품요약

		$it_name = strip_tags(clean_xss_attributes(trim($prodNm)));

		$it_id = $prodId;							//제품아이디
		$ca_id = $gubun;							//구분 ("00")
		$it_name = $prodNm;							//제품명
		$it_thezone = $itemId;						//품목 아이디
		$it_cust_price = $prodSupPrice;				//공급가격
		$it_price = $prodOflPrice;					//판매금액
		$pt_id = $supId;							//공급업체 아이디
		$it_option_subject = $prodColor;			//색상 (ex “빨강|파랑|노랑” )
		$it_stock_qty = ($prodQty)?$prodQty:9999;	//주문가능수량
		$it_explan = $prodDetail;					//상세정보
		$it_ip = ($regUsrIp)?$regUsrIp:$_SERVER['REMOTE_ADDR']; //최초등록자 IP (IPV6 포함 총 39자리)



		$it_img_dir = G5_DATA_PATH.'/item';

		// 이미지업로드
		if ($file1) {
			$it_img1 = FileSave($file1, $it_img_dir.'/'.$it_id);
		}
		if ($file2) {
			$it_img2 = FileSave($file2, $it_img_dir.'/'.$it_id);
		}
		if ($file3) {
			$it_img3 = FileSave($file3, $it_img_dir.'/'.$it_id);
		}
		if ($file4) {
			$it_img4 = FileSave($file4, $it_img_dir.'/'.$it_id);
		}
		if ($file5) {
			$it_img5 = FileSave($file5, $it_img_dir.'/'.$it_id);
		}
		if ($file6) {
			$it_img6 = FileSave($file6, $it_img_dir.'/'.$it_id);
		}
		if ($file7) {
			$it_img7 = FileSave($file7, $it_img_dir.'/'.$it_id);
		}
		if ($file8) {
			$it_img8 = FileSave($file8, $it_img_dir.'/'.$it_id);
		}
		if ($file9) {
			$it_img9 = FileSave($file9, $it_img_dir.'/'.$it_id);
		}
		if ($file10) {
			$it_img10 = FileSave($file10, $it_img_dir.'/'.$it_id);
		}

		$sql_common = " pt_it = '1',
						ca_id = '$ca_id',
						it_thezone = '$it_thezone',
						it_name = '$it_name',
						it_basic = '$it_basic',
						it_explan = '$it_explan',

						it_type1 = '1',
						it_type2 = '0',
						it_type3 = '0',
						it_type4 = '0',
						it_type5 = '0',
						pt_main = '0',

						it_maker = '',
						it_origin = '',
						it_brand = '',
						it_model = '$it_model',

						it_price = '$it_price',
						it_price_partner = '$it_price_partner',
						it_price_dealer = '$it_price_dealer',
						it_price_dealer2 = '$it_price_dealer2',

						it_use = '1',
						it_use_custom_order = '1',
						pt_point = '1',
						pt_comment_use = '1',
						it_point_type = '0',
						it_point = '0',
						it_supply_point = '0',
						it_soldout = '0',
						it_stock_qty = '$it_stock_qty',

						it_info_gubun       = '$it_info_gubun',
						it_info_value       = '$it_info_value',

						it_ip               = '$it_ip',

						it_img1 = '$it_img1',
						it_img2 = '$it_img2',
						it_img3 = '$it_img3',
						it_img4 = '$it_img4',
						it_img5 = '$it_img5',
						it_img6 = '$it_img6',
						it_img7 = '$it_img7',
						it_img8 = '$it_img8',
						it_img9 = '$it_img9',
						it_img10 = '$it_img10',

						it_sc_add_sendcost = '-1',
						it_sc_add_sendcost_partner = '-1',

						prodId				= '$prodId',
						gubun               = '$gubun',
						prodNm				= '$prodNm',
						itemId				= '$itemId',
						subItem				= '$subItem',
						prodSupPrice		= '$prodSupPrice',
						prodOflPrice		= '$prodOflPrice',
						ProdPayCode			= '$ProdPayCode',
						supId               = '$supId',
						prodColor			= '$prodColor',
						prodSym				= '$prodSym',
						prodWeig			= '$prodWeig',
						prodSize			= '$prodSize',
						prodQty				= '$prodQty',
						prodDetail			= '$prodDetail',
						regDtm				= '$regDtm',
						regUsrId			= '$regUsrId',
						regUsrIp			= '$regUsrIp',
						supNm               = '$supNm',
						prodImgAttr			= '$prodImgAttr',

						pt_id				= '$pt_id'

						";

		if(!$error_num){

			$t_it_id = preg_replace("/[A-Za-z0-9\-_]/", "", $it_id);
			if($t_it_id){
				$data['message'] = '제품아이디(코드)는 영문자, 숫자, -, _ 만 사용할 수 있습니다.';
				$data['errorYN'] = 'Y';
			}


			$row = sql_fetch(" select count(*) as cnt from {$g5['g5_shop_item_table']} where it_id = '$it_id' ");
			if ($row['cnt']){

				$data['message'] = $it_id.' 은(는) 이미 존재하는 제품아이디 입니다.';
				$data['errorYN'] = 'Y';

			}else{



				$pt_num = time();

				if($regDtm){
					$sql_common .= " , it_time = '$regDtm' ";
					$sql_common .= " , it_update_time = '$regDtm' ";
				}else{
					$sql_common .= " , it_time = '".G5_TIME_YMDHIS."' ";
					$sql_common .= " , it_update_time = '".G5_TIME_YMDHIS."' ";
				}

				$sql = " insert {$g5['g5_shop_item_table']}
							set it_id = '$it_id',
								pt_num = '$pt_num',
								$sql_common	";

				if(sql_query($sql)){

					$data['message'] = '상품등록완료';
					$data['errorYN'] = 'N';

				}else{

					$data['message'] = '상품등록에 문제가 있습니다. 관리자에게 문의하세요.';
					$data['errorYN'] = 'Y';

				}
			}

		}else{

			$data['message'] = '상품등록값을 확인해주세요.';
			$data['errorCode'] = $error_code;
			$data['errorYN'] = 'Y';

		}

		return json_encode($data);

	}
}

$API = new API;
header('Content-Type: application/json');
echo $API->Item_Write();
?>