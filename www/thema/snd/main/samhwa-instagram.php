<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
if (!defined('G5_INSTAGRAM_TOKEN')) {
    echo "인스타그램 토큰이 설정되지 않았습니다.";
    exit;
}

$instagram_url = urlencode('https://graph.instagram.com/me/media?access_token=' . G5_INSTAGRAM_TOKEN . '&fields=id,caption,media_type,media_url,thumbnail_url,permalink');

$json = samhwa_cache('instagram', 3000, "curl_exec_instagram_api('". $instagram_url ."')");
$json = json_decode($json, True);

?>

<style>
.fj-instagram {
    margin-top: 40px;
    margin-bottom: 40px;
}

@media (max-width: 990px) {
  .fj-instagram {
    margin-top: 20px;
    margin-bottom: 20px;
  }
}

.fj-instagram-header {
    margin: 20px auto;
    text-align: left;
    font-size: 22px;
}

.fj-instagram-contents {
    font-size: 0;
}

.fj-instagram-feed {
  display: inline-block;

  position: relative;
  width: 189px;
  height: 189px;
  margin-right:5px;
  margin-bottom:5px;
  border-radius:3px;
}

.fj-instagram-feed:before {
  content: '';

  display: block;

  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;

  background: rgba(0, 0, 0, 0.5);

  opacity: 0;

  transition: opacity 0.3s ease-in-out;
}

.fj-instagram-feed:hover:before {
  opacity: 1;
}

@media (max-width: 990px) {
  .fj-instagram-feed {
    width: 33.3333%;
    /*height: auto;*/
    height: 110px;
    margin-right:0px;
    margin-bottom:0px;
  }
  .fj-instagram-feed img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }


  .fj-instagram-feed:nth-child(10),
  .fj-instagram-feed:nth-child(11),
  .fj-instagram-feed:nth-child(12),
  .fj-instagram-feed:nth-child(13),
  .fj-instagram-feed:nth-child(14),
  .fj-instagram-feed:nth-child(15),
  .fj-instagram-feed:nth-child(16) {
    display: none;
  }
}
</style>


<div class="fj-instagram">
    <div class="fj-instagram-header">
    <span class="pc">
        <a href="https://www.instagram.com/samhwasnd/" target="_blank">
            <img src="<?php echo THEMA_URL; ?>/assets/img/instagram.png"/>
        </a>
    </span>
    <span class="mobile">
        <a href="https://www.instagram.com/samhwasnd/" target="_blank">
            <img src="<?php echo THEMA_URL; ?>/assets/img/instagram.png"/>
        </a>
    </span>
    </div>
    <div class="fj-instagram-contents">
      <?php foreach($json['data'] as $data) { ?>
        <a href="<?php echo $data['permalink'] ?>" target="_blank" class="fj-instagram-feed">
          <img src="<?php echo $data['media_url']; ?>" width="100%" height="100%"/>
        </a>
      <?php } ?>
    </div>
</div>