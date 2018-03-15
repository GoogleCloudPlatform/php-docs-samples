FROM gcr.io/google_appengine/php72

RUN composer global require phpunit/phpunit:4.8

RUN export PATH="$PATH:/opt/composer/vendor/bin"

ENTRYPOINT /bin/bash
