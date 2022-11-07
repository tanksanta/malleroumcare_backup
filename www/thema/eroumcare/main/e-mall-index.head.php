<div class="service_top">
    <div class="hd_logo"></div>
    <div class="half_patition">
        <p>장기요양기관</p>
        <p>통합관리시스템</p>
        <p><span class="line"> </span> </p>
        <p>이로움만의 장기요양기관 통합관리시스템으로<br>모든 것을 쉽고 편하게 관리해보세요</p>
        <p><a href="/bbs/login.php">로그인 <span>⇀</span></a></p>
        <p><a href="/bbs/register.php">회원가입 <span>⇀</span></a></p>
    </div>
</div>

<style>
.service_top {
  display:block;
  width: 100%;
  height: 937px;
  min-height: 200px;
  background-image: url("<?=G5_URL?>/thema/eroumcare/assets/img/eroumranding_top.png");
  background-repeat: no-repeat;
  background-size: cover;
  position: relative;
}

.half_patition {
  width: 60%;
  height: 100%;
  background-color: rgba(255,255,255,0.5);
  float: right;
  text-align: right;
  padding-right: 10%;
}

.hd_logo{
    width: 12%;
    height: 12%;
    background-image: url("<?=G5_URL?>/thema/eroumcare/assets/img/hd_logo.png");
    background-repeat: no-repeat;
    background-size: contain;
    position: absolute;
    margin: 3%;
}

.half_patition p:first-child {font-weight: bold; font-size: 60px;line-height: 1.5;margin-top: 15%;}
.half_patition p:nth-child(2) {color:#e86b19; font-weight: bold; font-size: 60px;line-height: 1.5;}
.half_patition p:nth-child(3) .line {content: ' ';width:15%;background: #27140c;height:8px;margin-top:5%;display:inline-block;}
.half_patition p:nth-child(4) {font-weight:500; margin-top:5%; font-size: 25px;line-height: 2;}
.half_patition p:nth-child(5) {margin-top: 10%; font-size: 30px; background-color: #0c0c0c; font-weight:550; display: inline-block; padding: 20px 25px 20px 25px; border: 3px solid #0c0c0c; margin-left: 8%; color: #F5F5F5;}
.half_patition p:nth-child(5) span{color: #e86b19; font-weight:1000;}
.half_patition p:nth-child(6) {margin-top: 10%; font-size: 30px; background-color: #F5F5F5; font-weight:550; display: inline-block; padding: 20px 25px 20px 25px; border: 3px solid #0c0c0c; margin-left: 8%; color: #0c0c0c;}
.half_patition p:nth-child(6) span{font-weight:1000;}

@media (max-width: 500px) {
    .service_top{
        width: 100%;
        height: 200px;
        background-size: contain;
        position: relative;
    }
    .hd_logo{
        width: 20%;
        height: 20%;
    }

    .half_patition p:first-child {font-weight: bold; font-size: 120%;line-height: 1;}
    .half_patition p:nth-child(2) {color:#e86b19; font-weight: bold;font-size: 120%;line-height: 1;}
    .half_patition p:nth-child(3) .line {content: ' ';width:15%;background: #27140c;height:2px;display:inline-block;}
    .half_patition p:nth-child(4) {display: none;}
    .half_patition p:nth-child(5) {margin-top: 1%; font-size: 10px; background-color: #0c0c0c; font-weight:550; display: inline-block; padding: 2px 4px 2px 4px; border: 1px solid #0c0c0c; margin-left: 3%; color: #F5F5F5;}
    .half_patition p:nth-child(5) span{color: #e86b19; font-weight:1000;}
    .half_patition p:nth-child(6) {margin-top: 1%; font-size: 10px; background-color: #F5F5F5; font-weight:550; display: inline-block; padding: 2px 4px 2px 4px; border: 1px solid #0c0c0c; margin-left: 3%; color: #0c0c0c;}
    .half_patition p:nth-child(6) span{font-weight:1000;}
}

@media (max-width: 420px) {
    .service_top{
        display:none;
    }
}
</style>
