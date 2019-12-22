FROM php:7.4-fpm-alpine

WORKDIR /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER 1

COPY ./ /var/www/html/

RUN apk update && \
    apk add --no-cache libzip-dev bash nginx supervisor && \
    docker-php-ext-install zip mysqli pdo pdo_mysql bcmath opcache && \
    docker-php-ext-configure zip && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-scripts

COPY ./.alpine /

ENTRYPOINT ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
