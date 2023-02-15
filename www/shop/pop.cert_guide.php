<?php
include_once('./_common.php');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Resources/Css/certStyle.css">
    <title>공인인증서등록 절차 알림</title>
    <!-- fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- google font -->    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100;300;400;500;700&display=swap" rel="stylesheet">
    <!-- swiper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/9.0.0/swiper-bundle.min.js" integrity="sha512-U0YYmuLwX0Z1X7dX4z45TWvkn0f8cDXPzLL0NvlgGmGs0ugchpFAO7K+7uXBcCrjVDq5A0wAnISCcf/XhSNYiA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
</head>
<body>
    <div class="visual">
        <div class="visualWrap">
            <!-- notiBarWrap -->
            <div class="notiBarWrap">
                <div class="notiBarTitle">공인인증서 등록 절차 알림</div>
                <div class="notiBarCancel">
                    <img src="/Resources/Images/btn_cencel.svg" alt="창닫기" title="창닫기" style="cursor:pointer" onClick="javascript:parent.$('#cert_guide_popup_box').trigger('click');">               
                </div>
            </div>
                <!-- topTitleWrap -->
            <div class="topTitleWrap">                
                <div class="topTitle">                    
                    요양정보조회 서비스를 이용하시려면<br>
                    <span class="title01">사업소의 공인인증서로 로그인</span> 하셔야 합니다.<br><br>
                    인증서 로그인을 위한 파일 설치를 해주십시오.<br>
                    설치파일을 다운로드 받으시려면 <span class="title02">[인증서 설치파일 다운로드]</span> 버튼을 클릭하십시오.
                </div>                
                <div class="topDownloadWrap">
                    <a href="#">
                        <div class="topDownload" onClick="parent.tilko_call('1');" title="인증서 설치파일 다운로드">
                            <div class="downloadName">인증서 설치파일 다운로드<?=$member["cert_reg_sts"]?></div>
                            <div><img src="/Resources/Images/icon_download.svg" alt="인증서 설치파일 다운로드"></div>                        
                        </div>
                    </a>
                </div>                
            </div>
            <div class="contentsWrap" style="padding:18px 60px 17px 60px;">
                <!-- Swiper -->
                <div class="swiper mySwiper">
                    <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="contentsImg">
                        <img src="/Resources/Images/cert_guide01.png" alt="">
                        </div>
                        <div class="contentsText">
                            <h3 class="conTitle">1. 공인 인증서 설치 파일을 다운로드 받은 후 설치하세요!</h3>
                        
                            [인증서 설치파일 다운로드] 버튼을 클릭하세요.<br>
                            팝업창이 나타나면 [확인] 버튼을 클릭하시면 설치 파일이 다운로드 됩니다.<br>                            
                            다운로드 후 'setup.exe' 파일을 더블 클릭하세요.       
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="contentsImg">
                            <img src="/Resources/Images/cert_guide02.png" alt="">
                            </div>
                            <div class="contentsText">
                                <h3 class="conTitle">2. 설치 마법사에 따라 파일을 설치하세요!</h3>
                                설치 마법사가 실행되면 그림과 같은 순서대로 진행하세요.<br>
                                (설치 마법사 화면 : 다음 → '사용권 동의' 선택 → 다음 → 설치 → 마침)            
                            </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="contentsImg">
                            <img src="/Resources/Images/cert_guide03.png" alt="">
                            </div>
                            <div class="contentsText">
                                <h3 class="conTitle">3. 사업소 공인 인증서를 등록하세요!</h3>
                                설치 완료 후 안내창의 ‘설치파일 다운로드’버튼을 한번 더 클릭 하세요.<br>
                                팝업창이 뜨면 [TilkoSign 열기] 버튼을 클릭하세요.<br>
                                [하드디스크] 나 [USB] 버튼을 클릭하여 공인인증서를 찾은 후 비밀번호를 입력하세요.<br>
                                인증서 성공 창이 나타나면 [확인] 버튼을 누르세요.                
                            </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="contentsImg">
                            <img src="/Resources/Images/cert_guide04.png" alt="">
                            </div>
                            <div class="contentsText">
                                <h3 class="conTitle"><h3 class="conTitle">4. 사업소 공인 인증서로 로그인하고 요양정보를 조회하세요!</h3>
                                요양정보 조회페이지에서 수급자명과 요양인정번호를 입력 후 [조회 요청] 버튼을 클릭하세요.<br>
                                회원가입시 장기요양기관번호를 기입하시지 않은 사업소는 장기요양기관번호 입력창에 번호를 입력하고 확인을 클릭하세요.<br>
                                사업소 공인인증서 비밀번호를 입력하여 로그인하세요.<br>
                                로그인 후 간단하게 요양정보를 조회하 실 수 있습니다.                      
                            </div>
                    </div>
                    </div>                    
                </div>
                <div class="fullScreenWrap">
                    <a href="/Resources/Images/Cert_installGuide.pdf" target="_blank">
                    <div class="fullScreen">전체화면
                        <img src="/Resources/Images/icon_enlarge.svg" alt="전체화면">
                     </div>
                     </a>
                </div>
                     <!-- Swiper 페이징 네비게이션-->
                    <div class="swiper-button-next bb"></div>
                    <div class="swiper-button-prev bb"></div>
                    <div class="swiper-pagination"></div>  
            </div>
            <!-- bottomWrap -->
            <a href="#">
                <div class="bottomWrap" onClick="parent.tilko_call('1');" title="인증서 설치파일 다운로드">
                    인증서 설치파일 다운로드
                    <img src="/Resources/Images/icon_download.svg" alt="인증서 설치파일 다운로드">
                </div>
             </a>
        </div>
    </div>

    <!-- Swiper JS -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

  <!-- Initialize Swiper -->
  <script>
    var swiper = new Swiper(".mySwiper", {
      slidesPerView: 1,
      spaceBetween: 30,
      loop: true,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
    });
  </script>
</body>
</html>