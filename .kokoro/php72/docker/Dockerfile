FROM gcr.io/google_appengine/php72

RUN apt-get update && apt-get install -y \
    python-ipaddr \
    autoconf \
    build-essential \
    zlib1g-dev

RUN git clone https://github.com/GoogleCloudPlatform/php-tools.git ${HOME}/php-tools \
    && ${HOME}/php-tools/scripts/install_test_deps.sh

RUN pecl install grpc \
    && echo "extension=grpc.so" >> /opt/php72/lib/conf.d/ext-grpc.ini

RUN export PATH="$PATH:/opt/composer/vendor/bin"
RUN composer global require phpunit/phpunit:4.8

ENTRYPOINT /bin/bash
