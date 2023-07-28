# [START cloudrun_mc_hello_php_nginx_sidecar_dockerfile]

# Context: ../

FROM php:8-fpm-alpine

RUN docker-php-ext-install opcache

# COPY php.ini /usr/local/etc/php
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY . /var/www/html/

# [END cloudrun_mc_hello_php_nginx_sidecar_dockerfile]
