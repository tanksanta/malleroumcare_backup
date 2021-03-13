<?php
include_once('./_common.php');

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
    require_once(G5_SHOP_PATH.'/yc/orderinquiry.php');
    return;
}

define("_ORDERINQUIRY_", true);

$od_pwd = get_encrypt_string($od_pwd);

// 회원인 경우
if ($is_member)
{
    $sql_common = " from {$g5['g5_shop_order_table']} where mb_id = '{$member['mb_id']}' AND od_del_yn = 'N' ";
}
else if ($od_id && $od_pwd) // 비회원인 경우 주문서번호와 비밀번호가 넘어왔다면
{
    $sql_common = " from {$g5['g5_shop_order_table']} where od_id = '$od_id' and od_pwd = '$od_pwd' AND od_del_yn = 'N' ";
}
else // 그렇지 않다면 로그인으로 가기
{
    goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/orderinquiry.php'));
}

// Page ID
$pid = ($pid) ? $pid : 'inquiry';
$at = apms_page_thema($pid);
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

$skin_row = array();
$skin_row = apms_rows('order_'.MOBILE_.'skin, order_'.MOBILE_.'set');
$skin_name = $skin_row['order_'.MOBILE_.'skin'];
$order_skin_path = G5_SKIN_PATH.'/apms/order/'.$skin_name;
$order_skin_url = G5_SKIN_URL.'/apms/order/'.$skin_name;

// 스킨 체크
list($order_skin_path, $order_skin_url) = apms_skin_thema('shop/order', $order_skin_path, $order_skin_url); 

// 스킨설정
$wset = array();
if($skin_row['order_'.MOBILE_.'set']) {
	$wset = apms_unpack($skin_row['order_'.MOBILE_.'set']);
}

// 데모
if($is_demo) {
	@include ($demo_setup_file);
}

// 설정값 불러오기
$is_inquiry_sub = false;
@include_once($order_skin_path.'/config.skin.php');

$g5['title'] = '주문내역조회';

if($is_inquiry_sub) {
	include_once(G5_PATH.'/head.sub.php');
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/head.sub.php');
} else {
	include_once('./_head.php');
}

$skin_path = $order_skin_path;
$skin_url = $order_skin_url;

// 셋업
$setup_href = '';
if(is_file($skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
	$setup_href = './skin.setup.php?skin=order&amp;name='.urlencode($skin_name).'&amp;ts='.urlencode(THEMA);
}

$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];
$sendData["prodId"] = $_GET['prodId'];

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9001);
curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectDetailList");
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
$res = json_decode($res, true);
curl_close($oCurl);

$list = [];
if($res["data"]){
    $list = $res["data"];
}


?>
<link rel="stylesheet" href="<?=G5_CSS_URL ?>/stock_page.css">

<section id="stock" class="wrap" >
    <div class="list-more"><a href="<?=G5_SHOP_URL?>/sales_Inventory2.php?&page=<?=$_GET['page']?>&searchtype=<?=$_GET['searchtype']?>&searchtypeText=<?=$_GET['searchtypeText']?>">목록</a></div>
        <h2>대여 재고 상세</h2>
        <div class="stock-view view2">
            <div class="product-view">
                <div class="pro-image">
                    <img src="<?=G5_IMG_URL?>/big_ex01.png" alt="">
                </div>
                <div class="info-list">
                    <ul>
                        <li>
                            <span>유통</span>
                            <span>이로움</span>
                        </li>
                        <li>
                            <span>세금</span>
                            <span>과세</span>
                        </li>
                        <li>
                            <span>제품코드</span>
                            <span>M201481402</span>
                        </li>
                        <li>
                            <span>가격</span>
                            <span>15,000원</span>
                        </li>
                    </ul>
                    <div class="info-btn">
                        <div>
                            <a href="javascript:;" class="btn-01">신규재고등록</a>
                            <a href="javascript:;" class="btn-02">상세정보</a>
                        </div>
                        <p>*보유 재고 등록 가능</p>
                    </div>
                </div>
            </div>
            <div class="inner">
                <div class="table-wrap">
                    <p class="text01">대여기간 종료일이 1달 미만 제품입니다.</p>
                    <h3>보유 재고</h3>
                    <ul>
                        <li class="head cb">
                            <span class="num">No.</span>
                            <span class="product">상품(옵션)</span>
                            <span class="pro-num">바코드</span>
                            <span class="date">입고일</span>
                            <span class="state">상태</span>
                            <span class="none"></span>
                        </li>

                        <!--반복-->
                        <li class="list cb">
                            <!--pc용-->
                            <span class="num">3</span>
                            <span class="product m_off">미끄럼방지양말(흰색)</span>
                            <span class="pro-num m_off"><b>123456789</b></span>
                            <span class="date m_off">2021-03-03</span>
                            <span class="state m_off">
                                <b>대여가능<img style="padding-left:5px;" src="<?=G5_IMG_URL?>/iconnew.png" alt=""> </b>
                                <a class="state-btn1" href="javascript:;">대여</a>
                            </span>
                            <span class="none m_off">
                                <div class="state-btn2">
                                    <b><img src="<?=G5_IMG_URL?>/icon_11.png" alt=""></b>
                                    <ul>
                                        <li><a href="javascript:;">대여기간 수정</a></li>
                                        <li><a href="javascript:;">계약서 확인</a></li>
                                        <li class="p-btn01"><a href="javascript:;">소독신청</a></li>
                                        <li><a href="javascript:;">대여 가능상태</a></li>
                                        <li class="p-btn02"><a href="javascript:;">소독확인 신청</a></li>
                                        <li><a href="javascript:;">소독취소</a></li>
                                        <li><a href="javascript:;">소독확인중</a></li>
                                        <li><a href="javascript:;">소불용재고등록</a></li>
                                    </ul>
                                </div>
                                <a class="state-btn3" href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                            </span>
                            <!--mobile용-->
                            <div class="list-m">
                                <div class="info-m">
                                    <span class="product">미끄럼방지양말(흰색)</span>
                                    <span class="pro-num"><b>123456789</b></span>
                                </div>
                                <div class="info-m">
                                    <span class="state">
                                        <b>대여가능<img style="padding-left:5px;" src="<?=G5_IMG_URL?>/iconnew.png" alt=""> </b>
                                    </span>
                                    <span class="none">
                                        <a class="state-btn1" href="javascript:;">대여하기</a>
                                        <div class="state-btn2">
                                            <b><img src="<?=G5_IMG_URL?>/icon_11.png" alt=""></b>
                                            <ul>
                                                <li><a href="javascript:;">대여기간 수정</a></li>
                                                <li><a href="javascript:;">계약서 확인</a></li>
                                                <li class="p-btn01"><a href="javascript:;">소독신청</a></li>
                                                <li><a href="javascript:;">대여 가능상태</a></li>
                                                <li class="p-btn02"><a href="javascript:;">소독확인 신청</a></li>
                                                <li><a href="javascript:;">소독취소</a></li>
                                                <li><a href="javascript:;">소독확인중</a></li>
                                                <li><a href="javascript:;">소불용재고등록</a></li>
                                            </ul>
                                        </div>
                                        <a class="state-btn3" href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                                    </span>
                                </div>
                            </div>
                            <!--팝업 위치는 li 바로 하위[li태그자식]로 넣어주세요. -->
                            <div class="popup01 popup1">
                                <div class="p-inner">
                                    <h2>소독업체 지정</h2>
                                    <button class="cls-btn p-cls-btn" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                                    <ul>
                                        <li>
                                            <b>상세정보</b>
                                            <div class="input-box">
                                                <input type="text">
                                                <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                            </div>
                                        </li>
                                        <li>
                                            <b>담당자명</b>
                                            <div class="input-box">
                                                <input type="text">
                                                <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                            </div>
                                        </li>
                                        <li>
                                            <b>연락처</b>
                                            <div class="input-box">
                                                <input type="tel">
                                                <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="popup-btn">
                                        <button type="submit">확인</button>
                                        <button type="button" class="p-cls-btn">취소</button>
                                    </div>
                                </div>
                            </div>

                            <div class="popup01 popup2">
                                <div class="p-inner">
                                    <h2>소독 결과 확인</h2>
                                    <button class="cls-btn p-cls-btn" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                                    <ul>
                                        <li>
                                            <b>소독일자</b>
                                            <div class="input-box">
                                                <input type="text">
                                                <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                            </div>
                                        </li>
                                        <li>
                                            <b>약품종류</b>
                                            <div class="input-box">
                                                <input type="text">
                                                <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                            </div>
                                        </li>
                                        <li>
                                            <b>약품사용내역</b>
                                            <div class="input-box">
                                                <input type="text">
                                                <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                            </div>
                                        </li>
                                        <li class="file-list">
                                            <b>첨부파일(소독필증)</b>
                                            <div class="input-box">
                                                <input type="text">
                                                <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                            </div>
                                            <div class="inputFile cb">
                                                <input type="file" class="fileHidden" name="" id="bf_file_1" title="파일첨부 1 : 용량  이하만 업로드 가능">
                                                <label for="bf_file_1"></label>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="popup-btn">
                                        <button type="submit">확인</button>
                                        <button type="button" class="p-cls-btn">취소</button>
                                    </div>
                                </div>
                            </div>

                            <div class="popup01 popup3">
                                <div class="p-inner">
                                    <h2>대여 기록</h2>
                                    <button class="cls-btn p-cls-btn" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                                    <div class="table-box">
                                        <div class="tti">
                                            <h4>상품명(옵션명)</h4>
                                            <span>123456497</span>
                                        </div>
                                        <table>
                                            <colgroup>
                                                <col width="10%">
                                                <col width="30%">
                                                <col width="30%">
                                                <col width="30%">
                                            </colgroup>
                                            <thead>
                                                <th>No.</th>
                                                <th>내용</th>
                                                <th>기간</th>
                                                <th>문서</th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>3</td>
                                                    <td>홍길동 대여</td>
                                                    <td>01/20~02/11</td>
                                                    <td>
                                                        <a href="javascript:;">계약서</a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>2</td>
                                                    <td>ABC 업체소독</td>
                                                    <td>01/20~02/11</td>
                                                    <td>
                                                        <a href="javascript:;">소독 확인서</a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>1</td>
                                                    <td>홍길동 대여</td>
                                                    <td>01/20~02/11</td>
                                                    <td>
                                                        <a href="javascript:;">계약서</a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="pg-wrap">
                                        <div>
                                            <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_04.png" alt=""></a>
                                            <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_05.png" alt=""></a>
                                            <a href="javascript:;" class="on">1</a>
                                            <a href="javascript:;">2</a>
                                            <a href="javascript:;">3</a>
                                            <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_06.png" alt=""></a>
                                            <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_07.png" alt=""></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <!--반복-->


                        
                        <li class="list cb">
                            <!--pc용-->
                            <span class="num">2</span>
                            <span class="product m_off">미끄럼방지양말(흰색)</span>
                            <span class="pro-num m_off"><b>123456789</b></span>
                            <span class="date m_off">2021-03-03</span>
                            <span class="state m_off">
                                <b>대여가능<img style="padding-left:5px;" src="<?=G5_IMG_URL?>/iconnew.png" alt=""> </b>
                                <a class="state-btn1" href="javascript:;">대여</a>
                            </span>
                            <span class="none m_off">
                                <div class="state-btn2">
                                    <b><img src="<?=G5_IMG_URL?>/icon_11.png" alt=""></b>
                                    <ul>
                                        <li><a href="javascript:;">대여기간 수정</a></li>
                                        <li><a href="javascript:;">계약서 확인</a></li>
                                        <li><a href="javascript:;">소독신청</a></li>
                                        <li><a href="javascript:;">대여 가능상태</a></li>
                                        <li><a href="javascript:;">소독확인 신청</a></li>
                                        <li><a href="javascript:;">소독취소</a></li>
                                        <li><a href="javascript:;">소독확인중</a></li>
                                        <li><a href="javascript:;">소불용재고등록</a></li>
                                    </ul>
                                </div>
                                <a class="state-btn3" href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                            </span>
                            <!--mobile용-->
                            <div class="list-m">
                                <div class="info-m">
                                    <span class="product">미끄럼방지양말(흰색)</span>
                                    <span class="pro-num"><b>123456789</b></span>
                                </div>
                                <div class="info-m">
                                    <span class="state">
                                        <b>대여가능<img style="padding-left:5px;" src="<?=G5_IMG_URL?>/icon_care.png" alt=""> </b>
                                    </span>
                                    <span class="none">
                                        <a class="state-btn1" href="javascript:;">대여하기</a>
                                        <div class="state-btn2">
                                            <b><img src="<?=G5_IMG_URL?>/icon_11.png" alt=""></b>
                                            <ul>
                                                <li><a href="javascript:;">대여기간 수정</a></li>
                                                <li><a href="javascript:;">계약서 확인</a></li>
                                                <li><a href="javascript:;">소독신청</a></li>
                                                <li><a href="javascript:;">대여 가능상태</a></li>
                                                <li><a href="javascript:;">소독확인 신청</a></li>
                                                <li><a href="javascript:;">소독취소</a></li>
                                                <li><a href="javascript:;">소독확인중</a></li>
                                                <li><a href="javascript:;">소불용재고등록</a></li>
                                            </ul>
                                        </div>
                                        <a class="state-btn3" href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                                    </span>
                                </div>
                            </div>
                        </li>
                        <!--대여중일 때 클래스명 bg넣으면 배경색 변경됩니다.-->
                        <li class="list cb bg">
                            <!--pc용-->
                            <span class="num">1</span>
                            <span class="product m_off">미끄럼방지양말(흰색)</span>
                            <span class="pro-num m_off"><b>123456789</b></span>
                            <span class="date m_off">2021-03-03</span>
                            <span class="state m_off">
                                <b>대여중
                                    <i>(02/12~02/13)</i>
                                </b>
                            </span>
                            <span class="none m_off">
                                <div class="state-btn2">
                                    <b><img src="<?=G5_IMG_URL?>/icon_11.png" alt=""></b>
                                    <ul>
                                        <li><a href="javascript:;">대여기간 수정</a></li>
                                        <li><a href="javascript:;">계약서 확인</a></li>
                                        <li><a href="javascript:;">소독신청</a></li>
                                        <li><a href="javascript:;">대여 가능상태</a></li>
                                        <li><a href="javascript:;">소독확인 신청</a></li>
                                        <li><a href="javascript:;">소독취소</a></li>
                                        <li><a href="javascript:;">소독확인중</a></li>
                                        <li><a href="javascript:;">소불용재고등록</a></li>
                                    </ul>
                                </div>
                                <a class="state-btn3" href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                            </span>
                            <!--mobile용-->
                            <div class="list-m">
                                <div class="info-m">
                                    <span class="product">미끄럼방지양말(흰색)</span>
                                    <span class="pro-num"><b>123456789</b></span>
                                </div>
                                <div class="info-m">
                                    <span class="state">
                                        <b>대여중 </b>
                                    </span>
                                    <span class="none">
                                        <div class="state-btn2">
                                            <b><img src="<?=G5_IMG_URL?>/icon_11.png" alt=""></b>
                                            <ul>
                                                <li><a href="javascript:;">대여기간 수정</a></li>
                                                <li><a href="javascript:;">계약서 확인</a></li>
                                                <li><a href="javascript:;">소독신청</a></li>
                                                <li><a href="javascript:;">대여 가능상태</a></li>
                                                <li><a href="javascript:;">소독확인 신청</a></li>
                                                <li><a href="javascript:;">소독취소</a></li>
                                                <li><a href="javascript:;">소독확인중</a></li>
                                                <li><a href="javascript:;">소불용재고등록</a></li>
                                            </ul>
                                        </div>
                                        <a class="state-btn3" href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <i class="text02">* 이로움몰에서 구매한 바코드는 관리자 문의 후 수정이 가능합니다.</i>
                </div>

                <div class="table-wrap table-wrap2">
                    <h3>불용 재고 <i>(분실 및 파손으로 운영이 불가능한 제품)</i></h3>
                    <ul>
                        <li class="head cb">
                            <span class="num">No.</span>
                            <span class="product">상품(옵션)</span>
                            <span class="pro-num">바코드</span>
                            <span class="date">종료일</span>
                            <span class="none"></span>
                        </li>

                        <!--반복-->
                        <li class="list cb">
                            <!--pc용-->
                            <span class="num">1</span>
                            <span class="product m_off">미끄럼방지양말(흰색)</span>
                            <span class="pro-num m_off"><b>123456789</b></span>
                            <span class="date m_off">2021-03-03</span>
                            <span class="none m_off">
                                <a class="state-btn3" href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                            </span>
                            <!--mobile용-->
                            <div class="list-m">
                                <div class="info-m">
                                    <span class="product">미끄럼방지양말(흰색)</span>
                                    <span class="pro-num"><b>123456789</b></span>
                                </div>
                                <div class="info-m">
                                    <span class="none">
                                        <a class="state-btn3" href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                                    </span>
                                </div>
                            </div>
                        </li>
                        <!--반복-->
                      
                    </ul>
                </div>
                
            </div>
        </div>
        <script>

            $('.state-btn2').on('click',function(){
                $(this).find('ul').toggleClass('on');
                $(this).parents('.list').siblings('.list').find('.state-btn2').find('ul').removeClass('on');
            });

            //대여기록 팝업
            $('.state-btn3').on('click',function(){
                $(this).parents('.list').find('.popup3').stop().show();
            });

            //소독업체지정 팝업
            $('.p-btn01').on('click',function(){
                $(this).parents('.list').find('.popup1').stop().show();
            });
            //소독확인신청 팝업
            $('.p-btn02').on('click',function(){
                $(this).parents('.list').find('.popup2').stop().show();
            });
            //팝업 닫기 공통
            $('.popup01').find('.p-cls-btn').on('click',function(e){
                e.stopPropagation();
                $(this).parents('.popup01').stop().hide();
            });

            
        </script>
    </section>


<?php
if($is_inquiry_sub) {
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
	include_once(G5_PATH.'/tail.sub.php');
} else {
	include_once('./_tail.php');
}
?>
  
  
  