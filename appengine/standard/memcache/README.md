# Memcache and Google App Engine

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

**Deploy with gcloud**

```
$ gcloud config set project YOUR_PROJECT_ID
$ gcloud app deploy
```

**Store and retrieve values from the cache.**

```
$ echo hello > hello.txt
$ echo bye > bye.txt
$ curl http://{YOUR_PROJECT_ID}.appspot.com/memcache/a
# Store the value hello in /a.
$ curl http://{YOUR_PROJECT_ID}.appspot.com/memcache/a -T hello.txt
$ curl http://{YOUR_PROJECT_ID}.appspot.com/memcache/a
hello
```