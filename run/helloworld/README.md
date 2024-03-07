# Hello World for Cloud Run

This sample demonstrates how to deploy a **Hello World** application to Cloud Run.

**View the [full tutorial](https://cloud.google.com/run/docs/quickstarts/build-and-deploy/deploy-php-service)**

# Adding Composer

To add composer to this example, add the following to the minimum `Dockerfile`
included in this sample:

```
# composer prefers to use libzip and requires git for dev dependencies
RUN apt-get update && apt-get install git libzip-dev -y

# RUN docker-php-ext-configure zip --with-libzip
RUN docker-php-ext-install zip

# Install compoesr dependencies
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install
```
