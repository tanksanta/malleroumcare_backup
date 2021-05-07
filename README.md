# mall-eroumcare

Server: PHP Version 7.3.x

## Install

```sh
git clone https://github.com/pelogvc/mall-eroumcare.git malleroumcare
chmod -R 755 malleroumcare
cd malleroumcare/www/data
mkdir session
mkdir cache
cd ../
chmod -R 707 data
```

## wkhtmltopdf

<https://wkhtmltopdf.org/downloads.html> 설치

## PM2

```sh
cd malleroumcare/webhook
pm2 start index.js --name "malleroumcare-webhook" --watch
pm2 startup
pm2 save
```

## Development

hosts 파일에 해당 내용 추가

```sh
127.0.0.1 mall.eroumcare.doto.li
```

vhost.conf에 해당 내용 추가

```
<VirtualHost *:80>
    DocumentRoot /위치/mall-eroumcare/www
    ServerName mall.eroumcare.doto.li
    ServerAlias mall.eroumcare.doto.li
    <Directory "/위치/mall-eroumcare/www">
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>
</VirtualHost>
```

## URLs

- PMA: <http://mall.eroumcare.com/eroumcarepma>
- 라이브: <https://mall.eroumcare.com>
- 테스트: <http://mall.eroumcare.doto.li>
