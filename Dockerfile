FROM php:8.1.0

RUN set -x \
    && apt-get update \
    && apt-get install -y unixodbc-dev zlib1g-dev libzip-dev zip \
    # 创建`/usr/src/php/ext`目录
    && docker-php-source extract \
    # 安装composer
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer \
    && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
    # 安装扩展
    && docker-php-ext-install pdo_mysql \
        zip \
    # 删除`/usr/src/php/ext`目录
    && docker-php-source delete \
    && rm -rf /tmp/* \