# Hello World on App Engine for PHP 7.2

This sample demonstrates how to deploy a *very* basic application to Google
App Engine for PHP 7.2.

## Setup

Before running this sample:

### Create a project (if you haven't already)

- Go to
  [Google Developers Console](https://console.developers.google.com/project)
  and create a new project.

## Deploy to App Engine

**Deploy with gcloud**

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.
