# Slim Framework on App Engine for PHP 7.2

This sample demonstrates how to deploy a *very* basic [Slim][slim] application to
[Google App Engine for PHP 7.2][appengine-php]. For a more complete guide, follow
the [Building an App][building-an-app] tutorial.

## Setup

Before running this sample:

### Create a project (if you haven't already)

- Go to [Google Developers Console][console] and create a new project.

## Deploy to App Engine

**Deploy with gcloud**

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.

## Application Components

The application consists of three components:

 1. An [`app.yaml`](app.yaml) which sets your application runtime to be `php72`.
 2. A [`composer.json`](composer.json) which declares your application's dependencies.
 3. An [`index.php`](index.php) which handles all the requests which get routed to your app.

The `index.php` file is the most important. All applications running on App Engine
for PHP 7.2 require use of a [front controller][front-controller] file.

[console]: https://console.developers.google.com/project
[slim]: https://www.slimframework.com/
[appengine-php]: https://cloud.google.com/appengine/docs/standard/php/
[front-controller]: https://stackoverflow.com/questions/6890200/what-is-a-front-controller-and-how-is-it-implemented-in-php
[building-an-app]: https://cloud.google.com/appengine/docs/standard/php7/building-app/
