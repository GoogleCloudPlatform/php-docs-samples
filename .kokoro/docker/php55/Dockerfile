# This image is for googleapis/google-cloud-php repo.
FROM gcr.io/gcp-runtimes/ubuntu_16_0_4
ENV PHP_DIR=/opt/php55
ENV PHP_SRC_DIR=/usr/local/src/php55-build
ENV PATH=${PATH}:/usr/local/bin:${PHP_DIR}/bin

RUN apt-get update && \
    apt-get -y install \
            autoconf \
            build-essential \
            git-core \
            libbz2-dev \
            libcurl4-openssl-dev \
            libc-client2007e \
            libc-client2007e-dev \
            libfcgi-dev \
            libfcgi0ldbl \
            libfreetype6-dev \
            libicu-dev \
            libjpeg62-dbg \
            libjpeg-dev \
            libkrb5-dev \
            libmcrypt-dev \
            libpng12-dev \
            libpq-dev \
            libssl-dev \
            libxml2-dev \
            libxslt1-dev \
            libzip-dev \
            wget \
            zip

RUN wget -nv -O phpunit.phar https://phar.phpunit.de/phpunit-4.phar && \
    chmod +x phpunit.phar && \
    mv phpunit.phar /usr/local/bin/phpunit && \
    ln -s /usr/lib/libc-client.a /usr/lib/x86_64-linux-gnu/libc-client.a && \
    mkdir ${PHP_DIR} ${PHP_SRC_DIR} && \
    cd ${PHP_SRC_DIR} && \
    wget http://us1.php.net/get/php-5.5.38.tar.bz2/from/this/mirror \
         -O php-5.5.38.tar.bz2 && \
    tar jxf php-5.5.38.tar.bz2 && \
    cd php-5.5.38 && \
    ./configure \
        --prefix=${PHP_DIR} \
        --with-pdo-pgsql \
        --with-zlib-dir \
        --with-freetype-dir \
        --enable-mbstring \
        --with-libxml-dir=/usr \
        --enable-soap \
        --enable-intl \
        --enable-calendar \
        --with-curl \
        --with-mcrypt \
        --with-zlib \
        --with-gd \
        --with-pgsql \
        --disable-rpath \
        --enable-inline-optimization \
        --with-bz2 \
        --with-zlib \
        --enable-sockets \
        --enable-sysvsem \
        --enable-sysvshm \
        --enable-sysvmsg \
        --enable-pcntl \
        --enable-mbregex \
        --enable-exif \
        --enable-bcmath \
        --with-mhash \
        --enable-zip \
        --with-pcre-regex \
        --with-mysql \
        --with-pdo-mysql \
        --with-mysqli \
        --with-jpeg-dir=/usr \
        --with-png-dir=/usr \
        --enable-gd-native-ttf \
        --with-openssl \
        --with-fpm-user=www-data \
        --with-fpm-group=www-data \
        --with-libdir=/lib/x86_64-linux-gnu \
        --enable-ftp \
        --with-imap \
        --with-imap-ssl \
        --with-gettext \
        --with-xmlrpc \
        --with-xsl \
        --with-kerberos \
        --enable-fpm && \
        make && \
        make install && \
        pecl install grpc && \
        cp php.ini-production ${PHP_DIR}/lib/php.ini && \
        echo 'zend_extension=opcache.so' >> ${PHP_DIR}/lib/php.ini && \
        echo 'extension=grpc.so' >> ${PHP_DIR}/lib/php.ini && \
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
        php -r "if (hash_file('SHA384', 'composer-setup.php') === rtrim(file_get_contents('https://composer.github.io/installer.sig'))) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
        php composer-setup.php --filename=composer --install-dir=/usr/local/bin
