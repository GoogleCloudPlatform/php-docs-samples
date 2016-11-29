# Memcache and Google App Engine Flexible Environment

This sample application demonstrates how to use memcache with Google App Engine.

## Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

```sh
$ composer install
```

## Deploy to App Engine

**Prerequisites**

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).
- Set up memcache using [Redis Labs Memcache Cloud][redis labs memcache].
- edit `app.yaml` and update the environment variables for your Memcache
  instance.

**Deploy with gcloud**

```
$ gcloud config set project YOUR_PROJECT_ID
$ gcloud app deploy
```

**Store and retrieve values from the cache.**

```
$ echo hello > hello.txt
$ curl http://{YOUR_PROJECT_ID}.appspot.com/memcached/a
# Store the value hello in /a.
$ curl http://{YOUR_PROJECT_ID}.appspot.com/memcached/a -T hello.txt
$ curl http://{YOUR_PROJECT_ID}.appspot.com/memcached/a
hello
```

[redis labs memcache]: https://cloud.google.com/appengine/docs/flexible/python/using-redislabs-memcache