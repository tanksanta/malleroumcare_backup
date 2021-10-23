FROM php:7.3-apache-stretch

ARG DEBIAN_FRONTEND=noninteractive
RUN DEBIAN_FRONTEND=noninteractive \
    echo "deb http://http.debian.net/debian/ stretch main contrib non-free" > /etc/apt/sources.list && \
    echo "deb http://http.debian.net/debian/ stretch-updates main contrib non-free" >> /etc/apt/sources.list && \
    echo "deb http://security.debian.org/ stretch/updates main contrib non-free" >> /etc/apt/sources.list && \
    apt-get update && \
    apt-get install --no-install-recommends -y \
    openssl \
    build-essential \
    curl \
    xz-utils \
    ca-certificates \
    xfonts-100dpi \
    xfonts-75dpi \
    xfonts-base \
    fonts-roboto \
    fonts-inconsolata \
    ttf-mscorefonts-installer \
    fontconfig \
    xorg \
    libpng-dev \
    libxpm-dev \
    libwebp-dev \
    libfreetype6-dev \
    zlib1g-dev \
    libzip-dev \
    libjpeg62-turbo-dev && \
    curl -L https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.5/wkhtmltox_0.12.5-1.stretch_amd64.deb -o wkhtmltox_0.12.5-1.stretch_amd64.deb && \
    dpkg -i wkhtmltox_0.12.5-1.stretch_amd64.deb && \
    fc-cache -f -v && \
    apt-get purge -y curl && \
    apt-get autoremove -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /var/tmp/* /tmp/*
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install mbstring
RUN docker-php-ext-configure gd --with-gd --with-webp-dir --with-jpeg-dir \
    --with-png-dir --with-zlib-dir --with-xpm-dir --with-freetype-dir

RUN docker-php-ext-install gd
RUN docker-php-ext-install zip

# Install Xdebug
RUN pecl install -f xdebug
RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini;

EXPOSE 80
