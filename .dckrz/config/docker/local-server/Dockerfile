FROM php:7.1.0-apache

RUN pecl install redis-3.1.0 && \
    docker-php-ext-enable redis

RUN a2enmod rewrite

COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf

WORKDIR /var/www
