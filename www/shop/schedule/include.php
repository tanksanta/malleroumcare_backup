<?php
    echo '<meta name="viewport" content="width=device-width, initial-scale=1" />';
    echo '<link rel="dns-prefetch" href="//cdn.tailwindcss.com/3.1.8" />';
    header("Content-Type:text/html;charset=utf-8");
    echo '<script src="https://cdn.tailwindcss.com/3.1.8?plugins=line-clamp,forms"></script>';
    echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>';
    echo '<script src="/js/moment.min.js" defer></script>';
    echo '<script src="https://unpkg.com/alpinejs@3.10.4/dist/cdn.min.js" defer></script>';
    echo '<script src="https://hammerjs.github.io/dist/hammer.js"></script>';
    echo '<script src="https://momentjs.com/downloads/moment-with-locales.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js" integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
    echo '<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>';
    echo '<script src="/js/detectmobilebrowser.js"></script>';
    echo '<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">';

?>
<?php
    echo "<script>
    tailwind.config = {
      theme: {
        minWidth: {
          '20': '5rem',
          '24': '6rem',
        },
        extend: {
          spacing: {
            '68': '17rem',
            '76': '19rem',
            '84': '21rem',
            '88': '22rem',
            '100': '25rem',
            '104': '26rem',
            '108': '27rem',
            '112': '28rem',
            '116': '29rem',
            '120': '30rem',
            '124': '31rem',
            '128': '32rem',
            '132': '33rem',
            '136': '34rem',
            '140': '35rem',
            '144': '36rem',
            '148': '37rem',
            '152': '38rem',
            '156': '39rem',
            '160': '40rem',
            '164': '41rem',
            '168': '42rem',
            '172': '43rem',
            '20%': '20%',
            '25%': '25%',
            '30%': '30%',
            '35%': '35%',
            '40%': '40%',
            '90%': '90%',
            '10vh': '10vh',
            '15vh': '15vh',
            '20vh': '20vh',
            '25vh': '25vh',
            '30vh': '30vh',
            '35vh': '35vh',
            '50vh': '50vh',
            '75vh': '75vh',
            '80vh': '80vh',
            '90vh': '90vh',
          },
          lineClamp: {
            7: '7',
            8: '8',
            9: '9',
            10: '10',
          }
        },
        variants: {
          lineClamp: ['responsive', 'hover']
        }
      }
    }
  </script>"
?>
<?php
  echo "<style>
  [x-cloak] {
    display: none;
  }
  
  .popModal {
    font-size: 12px;
    line-height: 22px;
    padding: 10px;
    cursor: default;
  }
  
  .popModal .popModal_content {
    margin: 0;
  }
  
  .popModal .title {
    color: #666;
    margin-bottom: 5px;
  }
  
  .popModal input[type=\"text\"] {
    background: #fff;
    color: #666;
    border: 1px solid #ddd;
    text-align: center;
    width: 110px;
  }
  
  .popModal select {
    background: #fff;
    color: #666;
    border: 1px solid #ddd;
    height: 24px;
    width: 55px;
  }
  
  .popModal .btn_submit {
    display: block;
    padding: 4px;
    border-radius: 3px;
    background: #f1a73a;
    color: #fff;
    margin: 5px auto 0 auto;
    width: 100px;
  }
  
  .modal-open div.popup_box {
    position: fixed;
    width: 100vw;
    height: 100vh;
    left: 0;
    top: 0;
    z-index: 99999999;
    background-color: rgba(0, 0, 0, 0.6);
    display: table;
    table-layout: fixed;
    opacity: 0;
  }
  
  .modal-open div.popup_box>div {
    width: 100%;
    height: 100%;
    display: table-cell;
    vertical-align: middle;
  }
  
  .modal-open div.popup_box iframe {
    position: relative;
    width: 600px;
    height: 700px;
    border: 0;
    background-color: #FFF;
    left: 50%;
    margin-left: -250px;
  }
  
  .ct_status_mode_wr {
    display: inline-block;
    margin: 0 !important;
  }
  
  .ct_status_mode_wr input[type=\"radio\"] {
    margin: 8px 0;
    width: 14px;
    height: 14px;
  }
  
  .ct_status_mode_wr label {
    margin: 5px 10px 5px 0;
    line-height: 20px;
  }
  
  @media (max-width : 750px) {
    .modal-open div.popup_box iframe {
      width: 100%;
      height: 100%;
      left: 0;
      margin-left: 0;
    }
  }
  </style>";

?>
<?php
if ($_SESSION['ss_manager_mb_id']) {
  $member = get_member($_SESSION['ss_manager_mb_id']);
} else {
  $member = get_member($_SESSION['ss_mb_id']);
}
?>

<?php
// 설치 파트너 매니저 설치 일정 테이블 유무 확인 후 생성
sql_query("CREATE TABLE IF NOT EXISTS `partner_inst_sts` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `status` CHAR(12) NOT NULL COMMENT '설치 일정 상태(출고준비|출고완료|취소|주문무효)',
  `delivery_date` DATE NULL COMMENT '설치(출고) 날짜',
  `delivery_datetime` CHAR(5) NULL COMMENT '설치(출고) 시간(포맷: HH:MM)',
  `ct_id` INT(11) NOT NULL COMMENT '카트 id',
  `it_name` VARCHAR(255) NOT NULL COMMENT '상품명',
  `partner_mb_id` VARCHAR(255) NULL COMMENT '설치 파트너 mb_id',
  `partner_manager_mb_id` VARCHAR(255) NULL COMMENT '설치 파트너 매니저 mb_id',
  `partner_manager_mb_name` VARCHAR(255) NULL COMMENT '설치 파트너 매니저 이름',
  `od_id` BIGINT(20) NOT NULL COMMENT '주문 id',
  `od_mb_id` VARCHAR(255) NOT NULL COMMENT '사업소 mb_id',
  `od_mb_ent_name` VARCHAR(30) NOT NULL COMMENT '사업소 이름',
  `od_b_name` VARCHAR(30) NOT NULL COMMENT '수령자 이름',
  `od_b_hp` VARCHAR(20) NULL DEFAULT '' COMMENT '수령자 연락처',
  `od_b_addr1` VARCHAR(100) NULL DEFAULT '' COMMENT '수령자 주소',
  `od_b_addr2` VARCHAR(100) NULL DEFAULT '' COMMENT '수령자 상세주소',
  `prodMemo` LONGTEXT NULL DEFAULT '' COMMENT '수령자 요청사항'
);");
// 설치 파트너 설치 불가 날짜 테이블 유무 확인 후 생성
sql_query("CREATE TABLE IF NOT EXISTS `partner_manager_deny_schedule` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `partner_mb_id` VARCHAR(255) NOT NULL COMMENT '설치 파트너 mb_id',
  `partner_manager_mb_id` VARCHAR(255) NOT NULL COMMENT '설치 파트너 매니저 mb_id',
  `deny_date` DATE NOT NULL COMMENT '설치 불가능 날짜'
);");
?>