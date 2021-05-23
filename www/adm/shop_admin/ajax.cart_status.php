<?php
    include_once('./_common.php');
    if($_POST['ct_id']&&$_POST['step']){
        //변수지정
        $stoId="";
        $usrId="";
        $entId="";
        $add_sql="";
        $ct_ex_date = date("Y-m-d");
        $stateCd;

        //상태값 치환
        switch ($_POST['step']) {
            case '보유재고등록': $ct_status_text="보유재고등록"; break;
            case '재고소진': $ct_status_text="재고소진"; break;
            case '작성': $ct_status_text="작성"; break;
            case '주문무효': $ct_status_text="주문무효"; break;
            case '취소': $ct_status_text="주문취소"; break;
            case '주문': $ct_status_text="주문접수"; break;
            case '입금': $ct_status_text="입금완료"; break;
            case '준비': $ct_status_text="상품준비"; break;
            case '출고준비': $ct_status_text="출고준비"; break;
            case '배송': $ct_status_text="출고완료"; break;
            case '완료': $ct_status_text="배송완료"; break;
        }


        for($i=0;$i<count($_POST['ct_id']); $i ++){
            $sql_ct_s = "select a.od_id, a.it_id, a.it_name, a.ct_option, a.mb_id, a.stoId, b.mb_entId from `g5_shop_cart` a left join `g5_member` b on a.mb_id = b.mb_id where `ct_id` = '".$_POST['ct_id'][$i]."'";
            $result_ct_s=sql_fetch($sql_ct_s);
            $od_id=$result_ct_s['od_id'];
            $content=$result_ct_s['it_name'];
            if($result_ct_s['it_name'] !== $result_ct_s['ct_option']){
                $content=$content."(".$result_ct_s['ct_option'].")";
            }
            $content =$content."-".$ct_status_text." 변경";
            $sql = "INSERT INTO g5_shop_order_admin_log SET
                od_id = '{$od_id}',
                mb_id = '{$member['mb_id']}',
                ol_content = '{$content}',
                ol_datetime = now()
            ";
            //로그 insert
            sql_query($sql);

            //상태 update
            if($_POST['step']=="배송"){$add_sql = ", `ct_ex_date` = '".$ct_ex_date."'"; }
            $sql_ct = "update `g5_shop_cart` set `ct_status` = '".$_POST['step']."'".$add_sql." where `ct_id` = '".$_POST['ct_id'][$i]."'";
            sql_query($sql_ct);

            //시스템 상태값 변경
            $stoId=$stoId.$result_ct_s['stoId'];
            $usrId=$result_ct_s['mb_id'];
            $entId=$result_ct_s['mb_entId'];
        }

            //완료 판매완료로 바꿈
            $stateCd="06";
            switch ($_POST['step']) {
                case '배송':    $stateCd="01"; break;
                case '완료':    $stateCd="01"; break;
            }
            $stoIdDataList = explode('|',$stoId);
            $stoIdDataList=array_filter($stoIdDataList);
            $stoIdData = implode("|", $stoIdDataList);
            $sendData["stoId"] = $stoIdData;
            $res = get_eroumcare2(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
            $result_again =$res['data'];
            $new_sto_ids = array_map(function($data) {
                global $stateCd;
                return array(
                    'stoId' => $data['stoId'],
                    'prodBarNum' => $data['prodBarNum'],
                    'stateCd' => $stateCd
                );
            }, $result_again);
            $api_data = array(
                'usrId' => $usrId,
                'entId' => $entId,
                'prods' => $new_sto_ids,
            );
            $api_result = get_eroumcare(EROUMCARE_API_STOCK_UPDATE, $api_data);
            if ($api_result['errorYN'] === 'N') {
                echo "success";
            }else{
                echo "fail";
            }
    }else{
        echo "fail";
    }
?>