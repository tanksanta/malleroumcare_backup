<?php
include_once('./_common.php');

$mail_contents = '
<div style="background-color:#f9f9f9;width:100%;max-width:800px;padding:30px;">
<div style="padding-bottom:30px;border-bottom:1px solid #cfcfcf;">
    <div style="color:#333333;position:relative;width:70%;float:left;">
        <p style="font-size:42px;padding:0;margin:0;"><b style="font-size:52px;">' . $it['it_outsourcing_company'] . '  ' . $it['it_outsourcing_manager'] . '님</b><br/>주문이 접수되었습니다.</p>
        <p>이로움을 이용해주셔서 감사합니다.</p>
    </div>
    <div style="width:30%;float:right;" >
        <img src="'. G5_IMG_URL. '/logo_big.png" style="width:100%;" />
    </div>
    <div style="clear:both;"></div>
</div>
<div style="margin-top:50px;border-bottom:1px solid #cfcfcf;padding-bottom:20px;text-align: center;">
    <p style="margin:0;text-align:center;padding-bottom:30px;">신규주문정보</p>
    <table style="border:1px solid #c6c6c6;width:80%;margin:0 auto;border-collapse: collapse;border-spacing: 0;">
        <thead>
            <tr>
                <th style="background-color:#ffffff;border-bottom:1px solid #cfcfcf;font-weight:normal;line-height:30px;font-size:13px;">상품</th>
                <th style="background-color:#ffffff;border-bottom:1px solid #cfcfcf;font-weight:normal;line-height:30px;font-size:13px;">수량</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;">
                    ' . $it_name . '
                </td>
                <td style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;">
                    ' . number_format($amount['order']) . '원
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div style="margin-top:50px;border-bottom:1px solid #cfcfcf;padding-bottom:20px;text-align: center;">
    <p style="margin:0;text-align:center;padding-bottom:30px;">배송정보</p>
    <table style="border:1px solid #c6c6c6;width:80%;margin:0 auto;border-collapse: collapse;border-spacing: 0;">
        <tbody>
            <tr>
                <th style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:20%;font-weight:normal;">
                    수령인
                </th>
                <td style="text-align:left;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:80%;">
                    홍길동
                </td>
            </tr>
            <tr>
                <th style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:20%;font-weight:normal;">
                    전화번호
                </th>
                <td style="text-align:left;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:80%;">
                    010-1111-1111
                </td>
            </tr>
            <tr>
                <th style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:20%;font-weight:normal;">
                    핸드폰
                </th>
                <td style="text-align:left;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:80%;">
                    010-1111-1111
                </td>
            </tr>
            <tr>
                <th style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:20%;font-weight:normal;">
                    주소
                </th>
                <td style="text-align:left;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:80%;">
                    010-1111-1111
                </td>
            </tr>
            <tr>
                <th style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:20%;font-weight:normal;">
                    전달메세지
                </th>
                <td style="text-align:left;color:#656565;font-size:13px;height:50px;background-color:#ffffff;width:80%;">
                    010-1111-1111
                </td>
            </tr>
        </tbody>
    </table>
    <div style="text-align: left;width: 80%;margin: 20px auto;">
        <img src="'. G5_IMG_URL. '/icon_file.png" style="float:left;display:block;" />
        <div style="float:left;margin-left:10px;">
            <span style="line-height:30px;font-size:13px;">첨부파일</span>
            <ul style="list-style:none;margin:0;padding:0;">
                <li style="list-style:none;margin:0;padding:0;margin-bottom:10px;"><a href="#" style="text-decoration: underline;color: #0592ff;font-size:12px;">외부출고</a></li>
                <li style="list-style:none;margin:0;padding:0;margin-bottom:10px;"><a href="#" style="text-decoration: underline;color: #0592ff;font-size:12px;">외부출고</a></li>
            </ul>
        </div>
        <div style="clear:both;"></div>
    </div>
</div>
<p style="font-size:12px;color:#656565;margin:30px auto;text-align:center;">
    대표자: ' . $default['de_admin_company_owner'] . ' | 사업자등록번호: ' . $default['de_admin_company_saupja_no'] . ' | 통신판매신고번호: ' . $default['de_admin_tongsin_no'] . ' <br/>
    개인정보보호관리자: ' . $default['de_admin_info_name'] . ' | 주소: ' . $default['de_admin_company_addr'] . '
    <br/><br/>
    Copyright © ' . $default['de_admin_company_name'] . ' All rights reserved.
</p>
</div>
';

echo $mail_contents;

?>
<!--
<div style="background-color:#f9f9f9;width:100%;max-width:800px;padding:30px;">
        <div style="color:#333333;padding-bottom:30px;border-bottom:1px solid #cfcfcf;position:relative;">
            <p style="font-size:42px;padding:0;margin:0;"><b style="font-size:52px;">최고관리자님</b><br/>견적서가 도착하였습니다.</p>
            <p>이로움을 이용해주셔서 감사합니다.</p>
            <img src="<?php echo G5_IMG_URL; ?>/logo_big.png" style="width:120px;position:absolute;top:0;right:0;" />
        </div>
        <div style="margin-top:50px;border-bottom:1px solid #cfcfcf;padding-bottom:20px;">
            <p style="margin:0;text-align:center;padding-bottom:30px;">견적서확인을 클릭하시면 전송된 내용을 확인할 수 있습니다.</p>
            <table style="border:1px solid #c6c6c6;width:80%;margin:0 auto;border-collapse: collapse;border-spacing: 0;">
                <thead>
                    <tr>
                        <th style="background-color:#ffffff;border-bottom:1px solid #cfcfcf;font-weight:normal;line-height:30px;font-size:13px;">상품</th>
                        <th style="background-color:#ffffff;border-bottom:1px solid #cfcfcf;font-weight:normal;line-height:30px;font-size:13px;">총 금액</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;">
                            LED 도광판방식,상품테스트 옵션테스트,LSD-A1W,LSD-A1W_서상열테스트2,IB-40120W-W,LSD-A1W_서상열테스트2,LB-60120-W
                        </td>
                        <td style="text-align:center;color:#656565;font-size:13px;height:50px;background-color:#ffffff;">
                            1,239,419원
                        </td>
                    </tr>
                </tbody>
            </table>
            <a href="http://signstand.doto.li/shop/pop.estimate.php?od_id=2020051811524474" target="_blank" style="background-color:#0aa2cd;width:200px;display:block;text-align:center;line-height:60px;color:white;text-decoration:none;margin:20px auto;font-size:18px;">견적서확인</a>
        </div>
        <p style="font-size:12px;color:#656565;margin:30px auto;text-align:center;">
            대표자: 김영재 | 사업자등록번호: 134-81-13428 | 통신판매신고번호: 2016-서울성동-00432 <br/>
            개인정보보호관리자: 유성균 | 주소: 서울특별시 성동구 성수이로10길 14 (에이스 하이엔드 성수타워) B101~104호
            <br/><br/>
            Copyright © 삼화에스앤디(주) All rights reserved.
        </p>
        </div>
-->
    