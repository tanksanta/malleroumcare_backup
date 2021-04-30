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
        
        for($i=0;$i<count($_POST['ct_id']); $i ++){
            
            if($_POST['step']=="완료"||$_POST['step']=="주문무효"||$_POST['step']=="취소"){
                $sql_ct_s = "select a.it_id, a.mb_id, a.stoId, b.mb_entId from `g5_shop_cart` a left join `g5_member` b on a.mb_id = b.mb_id where `ct_id` = '".$_POST['ct_id'][$i]."'";
                $result_ct_s=sql_fetch($sql_ct_s);

                $stoId=$stoId.$result_ct_s['stoId'];
                $usrId=$result_ct_s['mb_id'];
                $entId=$result_ct_s['mb_entId'];
            }
            if($_POST['step']=="배송"){$add_sql = ", `ct_ex_date` = '".$ct_ex_date."'"; }
            $sql_ct = "update `g5_shop_cart` set `ct_status` = '".$_POST['step']."'".$add_sql." where `ct_id` = '".$_POST['ct_id'][$i]."'";
            sql_query($sql_ct);
      
        }

        if($_POST['step']=="완료"||$_POST['step']=="주문무효"||$_POST['step']=="취소"){
            //완료 판매완료로 바꿈
            switch ($_POST['step']) {
                case '완료':    $stateCd="01"; break;
                case '주문무효': $stateCd="06"; break;
                case '취소':    $stateCd="06"; break;
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
            echo "success";
        }
    }else{
        echo "fail";
    }
?>