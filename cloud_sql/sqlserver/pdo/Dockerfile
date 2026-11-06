FROM php:8.3-apache

COPY --from=composer:latest-bin /composer /usr/local/bin/composer

WORKDIR /var/www/html
COPY . .

RUN apt-get update && \
    apt-get -y install gnupg2 curl ca-certificates && \
    curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg && \
    curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list

RUN apt-get update && \
    ACCEPT_EULA=Y apt-get -y install \
        autoconf \
        build-essential \
        msodbcsql18 \
        unixodbc-dev \
        unzip

RUN pecl install pdo_sqlsrv && \
    docker-php-ext-enable pdo_sqlsrv
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
RUN composer update
