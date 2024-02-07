<?php
include_once('./_common.php');

//$auth_check = auth_check($auth[$sub_menu], 'w', true);
//if($auth_check)
    //json_response(400, $auth_check);

if($_POST['ct_id'] && $_POST['ct_ex_date']) {
    //if(is_array($_POST['ct_id'])) {
	$ct_id = $_POST['ct_id'];
        for($i=0 ; $i<count($ct_id);$i++) {
            $result2 = sql_fetch("
			  SELECT a.*, b.mb_entId
			  FROM `g5_shop_cart` a
			  LEFT JOIN `g5_member` b ON a.mb_id = b.mb_id
			  WHERE `ct_id` = '".$ct_id[$i]."'
			");

			// 추가 옵션 선택이 있을 경우 선택옵션값에 대하 로그 데이터 추가
			$content = $result2['it_name'];
			if( $result2['it_name'] !== $result2['ct_option'] ){ $content = $content."(".$result2['ct_option'].")"; }
			$content .= "-출고완료일 변경";
			$content .= " [".str_replace("-",".",$result2['ct_ex_date'])."→".str_replace("-",".",$_POST['ct_ex_date'])."]";
			//로그 INSERT
			$sql2= "
			  INSERT INTO
				`g5_shop_order_admin_log`
			  SET
				`od_id` = '". $result2['od_id'] ."',
				`mb_id` = '" . $member['mb_id'] . "',
				`ol_content` = '" . $content . "',
				`ol_datetime` = now()
			";
			sql_query($sql2);
			
			$sql = "
                update
                    g5_shop_cart
                set
                    ct_ex_date = '{$_POST['ct_ex_date']}',
                    ct_move_date = now()
                where
                    ct_id = '{$ct_id[$i]}'
            ";
            $result = sql_query($sql);

			

            if(!$result)
                json_response(500, '서버 오류로 출고완료일 변경 적용에 실패했습니다.');
        }
    //}
}

json_response(200, 'OK');
