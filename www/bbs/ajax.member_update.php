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
    /* // 파일명 : \www\bbs\ajax.member_update.php */
    /* // 파일 설명 : 신규파일 - 회원 정보변경 (ajax파일) */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

    include_once('./_common.php');


    $_referer = false;
    if( strpos($_SERVER["HTTP_REFERER"],"member_info_newform.php") ) { $_referer = true; }


    // POST값으로 mode에 값이 없을 경우 더 이상 처리 하지 않고, 에러 처리 한다.
    if( !$_POST['mode']  ) {
        $result["YN"] = "N";
        $result["YN_msg"] = "오류가 발생하였습니다.";
        echo json_encode($result); exit();
    }
    $_mode = $_POST['mode'];


    if( !$_referer ) {
        $result["YN"] = "N";
        $result["YN_msg"] = "잘못된 접근방식으로 실행하였습니다.";
        echo json_encode($result); exit();
    }
    

    $mbid = isset($_POST['mbid'])?trim($_POST['mbid']):"";
    $ck_member = get_member($mbid);
    if( !$ck_member && ( $ck_member['mb_no'] != $mbid ) ) {
        $result["YN"] = "N";
        $result["YN_msg"] = "변경하려는 회원정보에 문제가 발생하였습니다. \n잠시후 다시 시도해주시기 바랍니다.";
        echo json_encode($result); exit();
    }


    if( $_mode == "stop01" ) { 
        // --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --=
        // STOP01
        // --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --=

        $default_type = isset($_POST['default_type'])?trim($_POST['default_type']):"";
        $partner_type = isset($_POST['partner_type'])?trim($_POST['partner_type']):"";
        $bname = isset($_POST['bname'])?trim($_POST['bname']):"";
        $boss_name = isset($_POST['boss_name'])?trim($_POST['boss_name']):"";
        $buptae = isset($_POST['buptae'])?trim($_POST['buptae']):"";
        $bupjong = isset($_POST['bupjong'])?trim($_POST['bupjong']):"";
        $tel = isset($_POST['tel'])?trim($_POST['tel']):"";
        $fax = isset($_POST['fax'])?trim($_POST['fax']):"";
        $addr_zip = isset($_POST['addr_zip'])?trim($_POST['addr_zip']):"";
        $addr1 = isset($_POST['addr1'])?trim($_POST['addr1']):"";
        $addr2 = isset($_POST['addr2'])?trim($_POST['addr2']):"";


        $default_type = clean_xss_tags($default_type);
        $partner_type = clean_xss_tags($partner_type);
        $bname = clean_xss_tags($bname);
        $boss_name = clean_xss_tags($boss_name);
        $buptae = clean_xss_tags($buptae);
        $bupjong = clean_xss_tags($bupjong);
        $tel = clean_xss_tags($tel);
        $fax = clean_xss_tags($fax);
        $addr_zip = clean_xss_tags($addr_zip);
        $addr1 = clean_xss_tags($addr1);
        $addr2 = clean_xss_tags($addr2);
  
        if( !$bname || !$boss_name || !$buptae || !$bupjong || !$tel || !$addr_zip ) {
            if( !$bname ) $result["YN_msg"] = "상호명을 입력해주세요.";
            else if( !$boss_name ) $result["YN_msg"] = "대표자명을 입력해주세요.";
            else if( !$buptae ) $result["YN_msg"] = "업태를 입력해주세요.";
            else if( !$bupjong ) $result["YN_msg"] = "종목을 입력해주세요.";
            else if( !$tel ) $result["YN_msg"] = "사업소 연락처를 입력해주세요.";
            else if( !$addr_zip ) $result["YN_msg"] = "사업소 주소를 입력해주세요.";

            $result["YN"] = "N";
            echo json_encode($result); exit();
        }

        sql_query(" UPDATE g5_member
                    SET mb_update_date = NOW(),
                        mb_default_type = '{$default_type}',
                        mb_partner_type = '{$partner_type}',

                        mb_name = '{$bname}',
                        mb_giup_bname = '{$bname}',
                        mb_entNm = '{$bname}',

                        mb_tel = '{$tel}',
                        mb_giup_btel = '{$tel}',
                        mb_fax = '{$fax}',

                        mb_giup_zip1 = '" . mb_substr($addr_zip,0,3) . "',
                        mb_giup_zip2 = '" . mb_substr($addr_zip,3,2) . "',
                        mb_giup_addr1 = '{$addr1}',
                        mb_giup_addr2 = '{$addr2}',
                        mb_giup_boss_name = '{$boss_name}',
                        mb_giup_buptae = '{$buptae}',
                        mb_giup_bupjong = '{$bupjong}'
                        
                    WHERE mb_id = '{$ck_member['mb_id']}' AND mb_no ='{$ck_member['mb_no']}'
        ");

        $result["YN"] = "Y";
        $result["YN_msg"] = "";
        echo json_encode($result); exit();

     } else if( $_mode == "stop02" ) { 
        // --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --=
        // STOP02
        // --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --=

        $password = isset($_POST['password'])   ? trim($_POST['password']) : "";
        $name = isset($_POST['name'])   ? trim($_POST['name']) : "";
        $hp = isset($_POST['hp'])   ? trim($_POST['hp']) : "";
        $email = isset($_POST['email'])   ? trim($_POST['email']) : "";

        $mb_password = clean_xss_tags($password);
        $mb_giup_manager_name = clean_xss_tags($name);
        $mb_hp = clean_xss_tags($hp);
        $mb_email = clean_xss_tags($email);

        if( !$mb_password || !$mb_giup_manager_name || !$mb_hp || !$mb_email ) {
            if( !$mb_password ) $result["YN_msg"] = "비밀번호를 입력해주세요.";
            else if( !$mb_giup_manager_name ) $result["YN_msg"] = "담당자명을 입력해주세요.";
            else if( !$mb_hp ) $result["YN_msg"] = "담당자 휴대전화 번호를 입력해주세요.";
            else if( !$mb_email ) $result["YN_msg"] = "담당자 메일주소를 입력해주세요.";

            $result["YN"] = "N";
            echo json_encode($result); exit();
        }

        sql_query(" UPDATE g5_member
                    SET mb_update_date = NOW(),
                        mb_password = '".get_encrypt_string($mb_password)."',
                        mb_giup_manager_name = '{$mb_giup_manager_name}',
                        mb_hp = '{$mb_hp}',
                        mb_email = '{$mb_email}'
                    WHERE mb_id = '{$ck_member['mb_id']}' AND mb_no ='{$ck_member['mb_no']}'
        ");

        $result["YN"] = "Y";
        $result["YN_msg"] = "";
        echo json_encode($result); exit();

     } else if( $_mode == "stop03" ) { 
        // --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --=
        // STOP03
        // --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --=

        $addr_title = isset($_POST['addr_title']) ? trim($_POST['addr_title']):"";
        $addr_name  = isset($_POST['addr_name']) ? trim($_POST['addr_name']):"";
        $addr_tel   = isset($_POST['addr_tel']) ? trim($_POST['addr_tel']):"";
        $addr_zip   = isset($_POST['addr_zip']) ? trim($_POST['addr_zip']):"";
        $addr1  = isset($_POST['addr1']) ? trim($_POST['addr1']):"";
        $addr2  = isset($_POST['addr2']) ? trim($_POST['addr2']):"";
        $_set  = isset($_POST['mbset']) ? trim($_POST['mbset']):"";

        $addr_title = clean_xss_tags( $addr_title );
        $addr_name  = clean_xss_tags( $addr_name );
        $addr_tel   = clean_xss_tags( $addr_tel );
        $addr_zip   = clean_xss_tags( $addr_zip );
        $addr1  = clean_xss_tags( $addr1 );
        $addr2  = clean_xss_tags( $addr2 );
        $_set  = clean_xss_tags( $_set );


        if( $_set == "add" || $_set == "mod" ) {
            if( !$addr_title || !$addr_name || !$addr_tel || !$addr_zip ) {
                if( !$addr_title ) $result["YN_msg"] = "배송지명을 입력하세요.";
                else if( !$addr_name ) $result["YN_msg"] = "수령인을 입력하세요.";
                else if( !$addr_tel ) $result["YN_msg"] = "연락처를 입력하세요.";
                else if( !$addr_zip ) $result["YN_msg"] = "배송지 주소를 입력하세요.";

                $result["YN"] = "N";
                echo json_encode($result); exit();
            }
        }


        $_row = sql_fetch(" SELECT * FROM g5_member WHERE mb_id = '{$ck_member['mb_id']}' AND mb_no ='{$ck_member['mb_no']}'");
        $_AddrMore = "";
        $_AddrMore = json_decode( $_row['mb_addr_more'], TRUE );


        if( $_set == "add" ) {
            // 신규배송지 추가 부분 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            // 신규배송지 추가 부분 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            $_addr_default  = isset($_POST['addr_default']) ? trim($_POST['addr_default']):"";
            $_addr_default  = clean_xss_tags( $_addr_default );

            $_sqltxt = "";
            if( $_addr_default == "Y" ) {

                $_AddrMore[] = array(
                    "addr_title"=>$_row['mb_addr_title'],
                    "addr_name"=>$_row['mb_addr_name'],
                    "addr_tel"=>$_row['mb_addr_tel'],
                    "addr_zip"=>$_row['mb_zip1'].$_row['mb_zip2'],
                    "addr_1"=>$_row['mb_addr1'],
                    "addr_2"=>$_row['mb_addr2']
                );

                $_sqltxt = ("
                    mb_zip1 = '" . substr($addr_zip, 0, 3) . "',
                    mb_zip2 = '" . substr($addr_zip, 3) . "',
                    mb_addr1 = '" . $addr1 . "',
                    mb_addr2 = '" . $addr2 . "',
                    mb_addr_title = '" . $addr_title . "',
                    mb_addr_name = '" . $addr_name . "',
                    mb_addr_tel = '" . $addr_tel . "',
                ");

            } else {
                $_AddrMore[] = array(
                    "addr_title"=>$addr_title,
                    "addr_name"=>$addr_name,
                    "addr_tel"=>$addr_tel,
                    "addr_zip"=>$addr_zip,
                    "addr_1"=>$addr1,
                    "addr_2"=>$addr2
                );
            }

            $_AddrMore = array_values($_AddrMore);
            sql_query(" UPDATE g5_member
                        SET mb_update_date = NOW(),
                            {$_sqltxt}
                            mb_addr_more = '". json_encode($_AddrMore, JSON_UNESCAPED_UNICODE) ."'
                        WHERE mb_id = '{$ck_member['mb_id']}' AND mb_no ='{$ck_member['mb_no']}'
            ");
            
            $result["YN"] = "Y";
            $result["YN_msg"] = "";
            echo json_encode($result); exit();

            // ---------------------------------------------------------------------------------------------------------------

        } else if( $_set == "mod" ) {
            // 배송지 정보 변경 부분 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            // 배송지 정보 변경 부분 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            $_mbkey  = isset($_POST['mbkey']) ? trim($_POST['mbkey']):"";
            $_mbkey  = clean_xss_tags( $_mbkey );
            $_addr_default  = isset($_POST['addr_default']) ? trim($_POST['addr_default']):"";
            $_addr_default  = clean_xss_tags( $_addr_default );

            $_sqltxt = "";
            if( $_addr_default == "Y" ) {

                $_AddrMore[] = array(
                    "addr_title"=>$_row['mb_addr_title'],
                    "addr_name"=>$_row['mb_addr_name'],
                    "addr_tel"=>$_row['mb_addr_tel'],
                    "addr_zip"=>$_row['mb_zip1'].$_row['mb_zip2'],
                    "addr_1"=>$_row['mb_addr1'],
                    "addr_2"=>$_row['mb_addr2']
                );

                $tAddr = $_AddrMore[$_mbkey];
                unset($_AddrMore[$_mbkey]);

                $_sqltxt = ("
                    mb_zip1 = '" . substr($tAddr['addr_zip'], 0, 3) . "',
                    mb_zip2 = '" . substr($tAddr['addr_zip'], 3) . "',
                    mb_addr1 = '" . $tAddr['addr_1'] . "',
                    mb_addr2 = '" . $tAddr['addr_2'] . "',
                    mb_addr_title = '" . $tAddr['addr_title'] . "',
                    mb_addr_name = '" . $tAddr['addr_name'] . "',
                    mb_addr_tel = '" . $tAddr['addr_tel'] . "',
                ");

            } else {
                $_AddrMore[$_mbkey] = array(
                    "addr_title"=>$addr_title,
                    "addr_name"=>$addr_name,
                    "addr_tel"=>$addr_tel,
                    "addr_zip"=>$addr_zip,
                    "addr_1"=>$addr1,
                    "addr_2"=>$addr2
                );
            }

            $_AddrMore = array_values($_AddrMore);
            sql_query(" UPDATE g5_member
                        SET mb_update_date = NOW(),
                            {$_sqltxt}
                            mb_addr_more = '". json_encode($_AddrMore, JSON_UNESCAPED_UNICODE) ."'
                        WHERE mb_id = '{$ck_member['mb_id']}' AND mb_no ='{$ck_member['mb_no']}'
            ");
            
            $result["YN"] = "Y";
            $result["YN_msg"] = "";
            echo json_encode($result); exit();
            
            // ---------------------------------------------------------------------------------------------------------------

        } else if( $_set == "def" ) {
            // 기본배송지 설정 부분 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            // 기본배송지 설정 부분 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            $_mbkey  = isset($_POST['mbkey']) ? trim($_POST['mbkey']):"";
            $_mbkey  = clean_xss_tags( $_mbkey );

            $tAddr = $_AddrMore[$_mbkey];
            unset($_AddrMore[$_mbkey]);

            $_AddrMore[] = array(
                "addr_title"=>$_row['mb_addr_title'],
                "addr_name"=>$_row['mb_addr_name'],
                "addr_tel"=>$_row['mb_addr_tel'],
                "addr_zip"=>$_row['mb_zip1'].$_row['mb_zip2'],
                "addr_1"=>$_row['mb_addr1'],
                "addr_2"=>$_row['mb_addr2']
            );

            $_AddrMore = array_values($_AddrMore);
            sql_query(" UPDATE g5_member
                        SET mb_update_date = NOW(),

                            mb_zip1 = '" . substr($tAddr['addr_zip'], 0, 3) . "',
                            mb_zip2 = '" . substr($tAddr['addr_zip'], 3) . "',
                            mb_addr1 = '" . $tAddr['addr_1'] . "',
                            mb_addr2 = '" . $tAddr['addr_2'] . "',
                            mb_addr_title = '" . $tAddr['addr_title'] . "',
                            mb_addr_name = '" . $tAddr['addr_name'] . "',
                            mb_addr_tel = '" . $tAddr['addr_tel'] . "',

                            mb_addr_more = '". json_encode($_AddrMore, JSON_UNESCAPED_UNICODE) ."'
                        WHERE mb_id = '{$ck_member['mb_id']}' AND mb_no ='{$ck_member['mb_no']}'
            ");
            
            $result["YN"] = "Y";
            $result["YN_msg"] = "";
            echo json_encode($result); exit();

            // ---------------------------------------------------------------------------------------------------------------

        } else if( $_set == "del" ) {
            // 주소 삭제 부분 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            // 주소 삭제 부분 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            $_mbkey  = isset($_POST['mbkey']) ? trim($_POST['mbkey']):"";
            $_mbkey  = clean_xss_tags( $_mbkey );

            unset($_AddrMore[$_mbkey]);
            $_AddrMore = array_values($_AddrMore);
            sql_query(" UPDATE g5_member
                        SET mb_update_date = NOW(),
                            mb_addr_more = '". json_encode($_AddrMore, JSON_UNESCAPED_UNICODE) ."'
                        WHERE mb_id = '{$ck_member['mb_id']}' AND mb_no ='{$ck_member['mb_no']}'
            ");

            $result["YN"] = "Y";
            $result["YN_msg"] = "";
            echo json_encode($result); exit();

            // ---------------------------------------------------------------------------------------------------------------

        } else {

            $result["YN"] = "N";            
            $result["YN_msg"] = "알수없는 에러가 발생하였습니다.";
            echo json_encode($result); exit();
        }

     } else if( $_mode == "stop04" ) {
        // --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --=
        // STOP04
        // --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --= --=

        $mb_ent_num = isset($_POST['mb_ent_num'])   ? trim($_POST['mb_ent_num']) : "";
        $mb_account = isset($_POST['mb_account'])   ? trim($_POST['mb_account']) : "";
        $mb_entConAcc01 = isset($_POST['mb_entConAcc01'])   ? trim($_POST['mb_entConAcc01']) : "";

        $mb_ent_num = clean_xss_tags($mb_ent_num);
        $mb_account = clean_xss_tags($mb_account);
        $mb_entConAcc01 = clean_xss_tags($mb_entConAcc01);
/*
        if( !$mb_ent_num || !$mb_account || !$mb_entConAcc01 ) {
            if( !$mb_ent_num ) $result["YN_msg"] = "";
            else if( !$mb_account ) $result["YN_msg"] = "";
            else if( !$mb_entConAcc01 )$ result["YN_msg"] = "";

            $result["YN"] = "N";
            echo json_encode($result); exit();
        }
*/
        sql_query(" UPDATE g5_member
                    SET mb_update_date = NOW(),
                        mb_ent_num = '{$mb_ent_num}',
                        mb_account = '{$mb_account}',
                        mb_entConAcc01 = '{$mb_entConAcc01}'
                    WHERE mb_id = '{$ck_member['mb_id']}' AND mb_no ='{$ck_member['mb_no']}'
        ");

        $result["YN"] = "Y";
        $result["YN_msg"] = "";
        echo json_encode($result); exit();

     } else {

    }