FROM gcr.io/google_appengine/php72

RUN apt-get update && apt-get install -y \
    python-ipaddr \
    autoconf \
    build-essential \
    zlib1g-dev

RUN git clone https://github.com/GoogleCloudPlatform/php-tools.git ${HOME}/php-tools \
    && ${HOME}/php-tools/scripts/install_test_deps.sh

# install / enable PHP extensions
RUN pecl install grpc \
    && echo "extension=grpc.so" >> /opt/php72/lib/conf.d/ext-grpc.ini \
    && echo "extension=bcmath.so" >> /opt/php72/lib/conf.d/ext-bcmath.ini

RUN composer global require phpunit/phpunit:^5.0
ENV PATH="$PATH:/opt/composer/vendor/bin"

ENTRYPOINT /bin/bash
