# This file will go away once gcloud implements fingerprinting.
FROM gcr.io/google_appengine/php:latest

# The docker image will configure the document root according to this
# environment variable.
ENV DOCUMENT_ROOT /app/web

# For whitelisting a function
ENV WHITELIST_FUNCTIONS=libxml_disable_entity_loader

# add write permissions to var directory for cache, logs, and session
RUN chmod -R ug+w /app/var
