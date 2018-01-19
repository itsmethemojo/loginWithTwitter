FROM php:7.1.0-cli

RUN pecl install redis-3.1.0 && \
    docker-php-ext-enable redis

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/public
