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
    /* // 파일명 : \www\adm\shop_admin\popup.itemList_Select.php */
    /* // 파일 설명 :    */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

$sub_menu = '200950';
include_once('./_common.php');
auth_check($auth[$sub_menu], "r");


$qstr = ("target=".$_GET['target']."&amp;search_txt=".$_GET['search_txt']."&amp;");


$title = '상품검색';

$where = "";
if( $_GET['search_txt'] ){
    $where = "AND ( (`it_id` LIKE '%" . $_GET['search_txt'] . "%') OR  (`it_name` LIKE '%" . $_GET['search_txt'] . "%')  )";
}

$sql = " SELECT count(it_id) as cnt
            FROM g5_shop_item
            WHERE pt_it = '1' 
                AND prodSupYn = 'Y'
                {$where}
                AND (it_name <> '')
                AND (it_id <> '') ";

$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = ($list_num)?$list_num:$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$bl_id = $_GET["bl_id"];
// 내용(본문 리스트)
$_sql = ("  SELECT it_id, it_name
            FROM g5_shop_item
            WHERE pt_it = '1' 
                AND prodSupYn = 'Y'
                {$where}
                AND (it_name <> '')
                AND (it_id <> '')
                LIMIT {$from_record}, {$rows};
");

$result = sql_query($_sql);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">  
  <meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width" />
  <title>출고정보</title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script src="/js/barcode_utils.js"></script>
  <link type="text/css" rel="stylesheet" href="/thema/eroumcare/assets/css/font.css">
  <link type="text/css" rel="stylesheet" href="/js/font-awesome/css/font-awesome.min.css">
  <link type="text/css" rel="stylesheet" href="/skin/admin/new/css/basic/admin.css">
  <link type="text/css" rel="stylesheet" href="/skin/admin/new/css/admin.css">

  <link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/flex.css">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); outline: none; }
    html, body { width: 100%; font-family: "Noto Sans KR", sans-serif; }
    body { padding-top: 60px; padding-bottom: 70px; min-width: unset; }
    a { text-decoration: none; color: inherit; }
    ul, li { list-style: none; }
    button { border: 0; font-family: "Noto Sans KR", sans-serif; }
    input { font-family: "Noto Sans KR", sans-serif;  }


    /* 고정 상단 */
    #popupHeaderTopWrap { position: fixed; width: 100%; height: 50px; left: 0; top: 0; z-index: 10; background-color: #606060; padding: 0 20px; }
    #popupHeaderTopWrap:after { display: block; content: ''; clear: both; }
    #popupHeaderTopWrap > div { height: 100%; line-height: 50px; }
    #popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 22px; }
    #popupHeaderTopWrap > .close { float: right; }
    #popupHeaderTopWrap > .close > a { color: #FFF; font-size: 40px; top: -2px; }



    /* 팝업 */
    #popup { display: flex; justify-content: center; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, .7);z-index: 50; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);}
    #popup.hide {display: none;}
    #popup.multiple-filter { backdrop-filter: blur(4px) grayscale(90%); -webkit-backdrop-filter: blur(4px) grayscale(90%);}
    #popup .content { padding: 20px; background: #fff; border-radius: 5px; box-shadow: 1px 1px 3px rgba(0, 0, 0, .3); max-width:90%;}
    #popup .content { max-width:90%; font-size: 14px; }
    #popup .closepop { width: 100%; height: 40px; cursor: pointer; color:#fff; background-color:#000; border-radius:6px; margin-top: 10px; }


    /* 고정 하단 */
    #popupFooterBtnWrap { position: fixed; width: 100%; height: 40px; background-color: #FFF; bottom: 0px; z-index: 10; }
    #popupFooterBtnWrap > button { font-size: 18px; font-weight: bold; }
    #popupFooterBtnWrap > .savebtn{ float: left; width: 75%; height: 100%; background-color:#000; color: #FFF; }
    #popupFooterBtnWrap > .cancelbtn{ float: right; width: 25%; height: 100%; color: #666; background-color: #DDD; margin: 0px 35%;}

    .tbl_wrap {
        font-size: 14px;
        font-weight: revert;

    }
    .tbl_head01 tbody td {
        line-height: 1.2em;
        padding: 5px 5px;        
        font-size: 12px;
    }
    .pg_page, .pg_current {
        padding: 0px 8px;
    }
  </style>
</head>

<body>

    <!-- 고정 상단 -->
    <div id="popupHeaderTopWrap">
        <div class="title"><?php echo $title; ?></div>
        <div class="close">
            <a href="#" onclick="parent.$('#item_popup_box').hide();">
            &times;
            </a>
        </div>
    </div>

    <div style="height:60px;"></div>

    <div class="new_form">
    <form name="itemListSelect" id="itemListSelect" method="get" onsubmit="">
        <table style="background-color: #f8f8fa; width:100%;" class="new_form_table">
            <tr>
                <td style="width:100px; height: 45px; text-align:center;"><strong>검색</strong></td>
                <td style="padding: 10px 25px;">
                    <input type="text" id="search_txt"  name="search_txt" value="<?=$_GET['search_txt']?>" class="frm_input" autocomplete="off" style="width:250px; height:30px;" placeholder="상품명 또는 상품코드로 검색">
                    &nbsp; <input type="submit" value="검색" class="btn_submit" id="" style="width:80px; height:30px;">
                </td>
            </tr>          
        </table>

        <input type="hidden" name="target" value="<?php echo $_GET['target']; ?>">
    </form>
    </div>

    <div style="height:10px;"></div>
    <hr class="list">
    <div style="height:5px;"></div>

    <div class="tbl_wrap tbl_head01">
    <table style="background-color: #f8f8fa; width:100%;" class="new_form_table">
        <thead>
            <tr>
                <th style="text-align:center;">상품명</th>
                <th style="text-align:center; width:130px;">상품코드</th>
                <th style="text-align:center; width:100px;">선택</th>
            </tr>
        </thead>
        <tbody>
            <?php for($i=0; $row=sql_fetch_array($result); $i++) { ?>
            <tr>
                <td style="text-align:left;"><?=$row['it_name']?></td>
                <td style="text-align:left;"><?=$row['it_id']?></td>
                <td style="text-align:center;">
                    <input type="buttom" value="선택" class="btn btn_02" id="" style="width:80px; height:20px; text-align:center; cursor: pointer;" onclick="parentInsert_iTemInfo('<?=$_GET['target']; ?>', '<?=$row['it_id'];?>', '<?=$row['it_name'];?>')">
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>


    <?php
        $pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;domain='.$domain.'&amp;page=');
        echo $pagelist;
    ?>

    <!-- 고정 하단 -->
    <div id="popupFooterBtnWrap">
        <button type="button" class="cancelbtn" onclick="parent.$('#item_popup_box').hide();">취소</button>
    </div>

    <script type="text/javascript">
        function parentInsert_iTemInfo(target, id, nm) {
            parent.$('#id_'+target+'_nm').val(nm);
            parent.$('#id_'+target).val(id);
            parent.$('#item_popup_box').hide();
        }

    </script>








<?php
    include_once('./pop.tail.php');
?>

