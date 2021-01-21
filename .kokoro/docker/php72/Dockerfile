FROM gcr.io/google_appengine/php72

RUN apt-get update && apt-get install -y \
    python-ipaddr \
    autoconf \
    build-essential \
    zlib1g-dev \
    jq

# install / enable PHP extensions
RUN pecl install grpc \
    && echo "extension=grpc.so" >> /opt/php72/lib/conf.d/ext-grpc.ini \
    && echo "extension=bcmath.so" >> /opt/php72/lib/conf.d/ext-bcmath.ini

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

# Install Cloud SQL proxy
RUN wget https://dl.google.com/cloudsql/cloud_sql_proxy.linux.amd64 -O cloud_sql_proxy \
    && chmod +x cloud_sql_proxy \
    && mv cloud_sql_proxy /usr/local/bin

# Make composer and gcloud bins available via the PATH variable
ENV PATH="$PATH:/opt/composer/vendor/bin:/root/google-cloud-sdk/bin"

# Configure Google Cloud SDK
RUN gcloud config set app/promote_by_default false && \
    gcloud config set disable_prompts true && \
    gcloud -q components install app-engine-python && \
    gcloud -q components install app-engine-php && \
    gcloud -q components update

ENTRYPOINT /bin/bash
