FROM gcr.io/google_appengine/PHP_VERSION

RUN apt-get update && apt-get install -y \
    python-ipaddr \
    autoconf \
    build-essential \
    zlib1g-dev \
    jq

# install / enable PHP extensions
RUN pecl install grpc \
    && echo "extension=grpc.so" >> /opt/PHP_VERSION/lib/conf.d/ext-grpc.ini \
    && echo "extension=bcmath.so" >> /opt/PHP_VERSION/lib/conf.d/ext-bcmath.ini

# Install phpunit globally
RUN composer global require phpunit/phpunit:^5.0

# Install Google Cloud SDK
RUN curl https://dl.google.com/dl/cloudsdk/release/google-cloud-sdk.tar.gz \
        -o ${HOME}/google-cloud-sdk.tar.gz \
    && tar xzf ${HOME}/google-cloud-sdk.tar.gz -C $HOME \
    && ${HOME}/google-cloud-sdk/install.sh \
        --usage-reporting false \
        --path-update false \
        --command-completion false

# Make composer and gcloud bins available via the PATH variable
ENV PATH="$PATH:/opt/composer/vendor/bin:/root/google-cloud-sdk/bin"

# Configure Google Cloud SDK
RUN gcloud config set app/promote_by_default false && \
    gcloud config set disable_prompts true && \
    gcloud -q components install app-engine-python && \
    gcloud -q components install app-engine-php && \
    gcloud -q components update

ENTRYPOINT /bin/bash
