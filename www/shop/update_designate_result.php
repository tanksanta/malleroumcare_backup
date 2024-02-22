<?php
    include_once('./_common.php');


    if($_FILES['dis_file']['tmp_name']){
        $max_file_size = 2097152;
        // 변수 정리
        $uploads_dir = G5_DATA_PATH.'/file/disinfection';
        $error = $_FILES['dis_file']['error'];
        $name = $_FILES['dis_file']['name'];
        $allowed_ext = array('exe');
        $ext = array_pop(explode('.', $name));
        $temp = explode(".", $_FILES["dis_file"]["name"]);
        $newfilename = $_POST['member'].'_'.round(microtime(true)) . '.' . end($temp);
    
        // 오류 확인
        if( $error != UPLOAD_ERR_OK ) {
            switch( $error ) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    alert('파일이 너무 큽니다.');
                    break;
                // case UPLOAD_ERR_NO_FILE:
                //     echo "파일이 첨부되지 않았습니다. ($error)";
                //     break;
                default:
                    alert('파일이 제대로 업로드되지 않았습니다.');
            }
            exit;
        }
        if($file['size'] >= $max_file_size) {
            alert("2MB 까지만 업로드 가능합니다.");
            return false;
        }
        // 확장자 확인
        if( in_array($ext, $allowed_ext) ) {
            alert('허용되지 않는 확장자입니다.');
            exit;
        }
        //파일저장
        move_uploaded_file( $_FILES['dis_file']['tmp_name'], "$uploads_dir/$newfilename");
    }
    //최신 소독로그 조회
    $sql = "SELECT `rental_log_Id` FROM `g5_rental_log` WHERE `stoId`= '{$stoId}' AND `rental_log_division`='1' ORDER BY `dis_total_date` DESC LIMIT 1";
    // echo $sql;
    $row = sql_fetch($sql);
    $rental_log_Id = $row['rental_log_Id'];
    //최신 소독로그에 update
    $sql_update=" update `g5_rental_log`
        set `strdate` = '{$strdate}',
            `enddate` = '{$enddate}',
            `dis_state` = '소독완료',
            `dis_chemical` = '{$dis_chemical}',
            `dis_chemical_history` = '{$dis_chemical_history}',
            `dis_file` = '{$newfilename}'
            where `rental_log_Id` = '{$rental_log_Id}'";
    sql_query($sql_update);



    //대여가능 변경
    $sendData=[];
    $prodsSendData = [];
    $prodsData =[];
    $prodsData["stoId"] = $stoId;
    $prodsData["stateCd"] = "01";

    array_push($prodsSendData,$prodsData);

    $sendData['usrId']= $_POST['member'];
    $sendData["prods"]=$prodsSendData;

    // echo json_encode($sendData);
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9901);
	curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_UPDATE);
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    $res = curl_exec($oCurl);
    $res = json_decode($res, true);
    curl_close($oCurl);
    if($res["errorYN"] == "Y"){
       alert($res["message"]);
    } else {
        alert('완료되었습니다.',$_SERVER['HTTP_REFERER']);
    }


?>
