FROM php:8.0

RUN set -x \
    && apt-get update \
    && apt-get install -y unixodbc-dev zlib1g-dev libzip-dev zip \
    # 创建`/usr/src/php/ext`目录
    && docker-php-source extract \
    # 安装xdebug
    && pecl install xdebug-3.1.5 \
    # 安装composer
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer \
    && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
    # 安装扩展
    && docker-php-ext-install pdo_mysql \
        zip \
        pcntl \
    && docker-php-ext-enable xdebug \
    # xdebug配置
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9111" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    # 删除`/usr/src/php/ext`目录
    && docker-php-source delete \
    && rm -rf /tmp/* \