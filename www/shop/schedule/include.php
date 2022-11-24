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
?>
<?php
    echo "<script>
    tailwind.config = {
      theme: {
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
  `od_memo` LONGTEXT NULL DEFAULT '' COMMENT '수령자 요청사항',
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