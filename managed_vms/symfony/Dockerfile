# This file will go away once gcloud implements fingerprinting.
FROM gcr.io/php-mvm-a/php-nginx:latest

# The docker image will configure the document root according to this
# environment variable.
ENV DOCUMENT_ROOT /app/web

# add write permissions to var directory for cache, logs, and session
RUN chmod -R u+w /app/var