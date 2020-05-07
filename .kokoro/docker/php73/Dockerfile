FROM gcr.io/gcp-runtimes/ubuntu_16_0_4
ENV PHP_DIR=/opt/php73
ENV PHP_SRC_DIR=/usr/local/src/php73-build
ENV PATH=${PATH}:/usr/local/bin:${PHP_DIR}/bin

RUN apt-get update && \
    apt-get -y install \
            autoconf \
            build-essential \
            git-core \
            jq \
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
            python-ipaddr \
            wget \
            zip \
            zlib1g-dev

RUN ln -s /usr/lib/libc-client.a /usr/lib/x86_64-linux-gnu/libc-client.a && \
    mkdir -p ${PHP_DIR} ${PHP_SRC_DIR} ${PHP_DIR}/lib/conf.d && \
    cd ${PHP_SRC_DIR} && \
    wget http://us1.php.net/get/php-7.3.0.tar.bz2/from/this/mirror \
         -O php-7.3.0.tar.bz2 && \
    tar jxf php-7.3.0.tar.bz2 && \
    cd php-7.3.0 && \
    ./configure \
        --prefix=${PHP_DIR} \
        --with-config-file-scan-dir=${PHP_DIR}/lib/conf.d \
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
        echo 'extension=grpc.so' >> ${PHP_DIR}/lib/conf.d/ext-grpc.ini && \
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
        php -r "if (hash_file('SHA384', 'composer-setup.php') === rtrim(file_get_contents('https://composer.github.io/installer.sig'))) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
        php composer-setup.php --filename=composer --install-dir=/usr/local/bin

# Install phpunit globally
RUN composer global require phpunit/phpunit:^7.0

# Install Google Cloud SDK
RUN curl https://dl.google.com/dl/cloudsdk/release/google-cloud-sdk.tar.gz \
        -o ${HOME}/google-cloud-sdk.tar.gz \
    && tar xzf ${HOME}/google-cloud-sdk.tar.gz -C $HOME \
    && ${HOME}/google-cloud-sdk/install.sh \
        --usage-reporting false \
        --path-update false \
        --command-completion false

# Make composer and gcloud bins available via the PATH variable
ENV PATH="$PATH:/root/.composer/vendor/bin:/root/google-cloud-sdk/bin"

# Configure Google Cloud SDK
RUN gcloud config set app/promote_by_default false && \
    gcloud config set disable_prompts true && \
    gcloud -q components install app-engine-python && \
    gcloud -q components install app-engine-php && \
    gcloud -q components update

# Build php-cs-fixer
RUN composer global require friendsofphp/php-cs-fixer
