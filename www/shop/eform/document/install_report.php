<div class="a4">
    <div class="ir_wr">
        <h1 class="ir_h1">이로움 설치확인서</h1>
        <div class="ir_date">주문접수일: <span><?=date('Y년 m월 d일', strtotime($od['od_time']))?></span></div>
        <table>
            <colgroup>
                <col style="width: 20%" />
                <col style="width: 20%" />
                <col style="width: 20%" />
                <col style="width: 20%" />
                <col style="width: 20%" />
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row">사업소명<br>(원발주처)</th>
                    <td colspan="2"><?=$od['mb_name']?></td>
                    <th scope="row">연락처</th>
                    <td><?=$od['mb_giup_btel']?></td>
                </tr>
                <tr>
                    <th scope="row" rowspan="4">수급자정보</th>
                    <th scope="row">수급자</th>
                    <td><?=$od['od_b_name']?></td>
                    <th scope="row">연락처</th>
                    <td><?=$od['od_b_hp'] ?: $od['od_b_tel']?></td>
                </tr>
                <tr>
                    <th scope="row">보호자</th>
                    <td>&nbsp;</td>
                    <th scope="row">연락처</th>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <th scope="row">주소</th>
                    <td colspan="3"><?=sprintf("(%s%s)", $od['od_b_zip1'], $od['od_b_zip2']).' '.print_address($od['od_b_addr1'], $od['od_b_addr2'], $od['od_b_addr3'], $od['od_b_addr_jibeon'])?></td>
                </tr>
                <tr>
                    <th scope="row">배송요청사항</th>
                    <td colspan="3"><?=$od['od_memo']?></td>
                </tr>
            </tbody>
        </table>
        <table>
            <colgroup>
                <col style="width: 20%" />
                <col style="width: 20%" />
                <col style="width: 45%" />
                <col style="width: 15%" />
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">품명</th>
                    <th scope="col">수량</th>
                    <th scope="col">바코드</th>
                    <th scope="col">비고</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($carts as $ct) { ?>
                <tr>
                    <td><?=$ct['it_name']?></td>
                    <td style="text-align: center;"><?=$ct['ct_qty']?></td>
                    <td><?=implode(', ', $ct['barcode'])?></td>
                    <td><?=$ct['prodMemo']?></td>
                </tr>
                <?php } ?>
                <tr>
                    <th scope="row">총 설치 수량</th>
                    <td colspan="3" style="text-align: center;"><?=$total_qty.'개'?></td>
                </tr>
                <tr>
                    <th scope="row">특기사항</th>
                    <td colspan="3"></td>
                </tr>
                <tr class="tr_sign">
                    <th scope="row">확인자 서명</th>
                    <td colspan="2" style="border-right: 0; padding-left: 100px;"><?=date('Y년 m월 d일')?></td>
                    <td class="td_sign" data-id="sign_ir_1" style="border-left: 0;">(서명)</td>
                </tr>
            </tbody>
        </table>
        <table>
            <colgroup>
                <col style="width: 20%" />
                <col style="width: 80%" />
            </colgroup>
            <tbody>
                <tr class="tr_check_bed">
                    <th scope="row">침대 확인 사항</th>
                    <td style="text-align:center;" aligh="center" valign="middle" >
						<img src="<?=G5_SHOP_URL?>/eform/document/install_report_bed_check.png">
					</td>
                </tr>
                <tr class="tr_check_wheelchair">
                    <th scope="row">휠체어 확인 사항</th>
                    <td style="width:33%; text-align:center;" aligh="center" valign="middle" >
						<img src="<?=G5_SHOP_URL?>/eform/document/install_report_wheelchair_check.png">
					</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
