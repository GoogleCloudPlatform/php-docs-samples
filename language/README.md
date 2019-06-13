# Google Cloud Natural Language API Samples

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=language

These samples show how to use the [Google Cloud Natural Language API][language-api]
from PHP to analyze text.

[language-api]: https://cloud.google.com/natural-language/docs/quickstart-client-libraries
[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php/

## Setup

### Authentication

Authentication is typically done through [Application Default Credentials][adc]
which means you do not have to change the code to authenticate as long as
your environment has credentials. You have a few options for setting up
authentication:

1. When running locally, use the [Google Cloud SDK][google-cloud-sdk]

        gcloud auth application-default login

1. When running on App Engine or Compute Engine, credentials are already
   set-up. However, you may need to configure your Compute Engine instance
   with [additional scopes][additional_scopes].

1. You can create a [Service Account key file][service_account_key_file]. This file can be used to
   authenticate to Google Cloud Platform services from any environment. To use
   the file, set the ``GOOGLE_APPLICATION_CREDENTIALS`` environment variable to
   the path to the key file, for example:

        export GOOGLE_APPLICATION_CREDENTIALS=/path/to/service_account.json

[adc]: https://cloud.google.com/docs/authentication#getting_credentials_for_server-centric_flow
[additional_scopes]: https://cloud.google.com/compute/docs/authentication#using
[service_account_key_file]: https://developers.google.com/identity/protocols/OAuth2ServiceAccount#creatinganaccount

## Install Dependencies

1. [Enable the Cloud Natural Language API](https://console.cloud.google.com/flows/enableapi?apiid=language.googleapis.com).

1. **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).

1. Create a service account at the
[Service account section in the Cloud Console](https://console.cloud.google.com/iam-admin/serviceaccounts/)

1. Download the json key file of the service account.

1. Set `GOOGLE_APPLICATION_CREDENTIALS` environment variable pointing to that file.

1. If you are using the Analyze Entity Sentiment or Classify Text features, you will need to install and enable the [gRPC extension for PHP][grpc].

[grpc]: https://cloud.google.com/php/grpc

## Samples

To run the Natural Language Samples, run `php src/SNIPPET_NAME.php`. For example:

```sh
$ php src/analyze_all.php "This is some text to analyze"
$ php src/analyze_all_from_file.php "gs://your-gcs-bucket/file-to-analyze.txt"
```

### Run Analyze Entities

To run the Analyze Entities sample:

```
$ php src/analyze_entities.php 'I know the way to San Jose. Do You?'
Name: way
Type: OTHER
Salience: 0.63484555

Name: San Jose
Type: LOCATION
Salience: 0.36515442
```

### Run Analyze Sentiment

To run the Analyze Sentiment sample:

```
Document Sentiment:
  Magnitude: 0.1
  Score: 0

Sentence: I know the way to San Jose.
Sentence Sentiment:
Entity Magnitude: 0
Entity Score: 0

Sentence: Do you?
Sentence Sentiment:
Entity Magnitude: 0
Entity Score: 0
```

### Run Analyze Syntax

To run the Analyze Syntax sample:

```
$ php src/analyze_syntax.php 'I know the way to San Jose. Do you?'
Token text: I
Token part of speech: PRON

Token text: know
Token part of speech: VERB

Token text: the
Token part of speech: DET

Token text: way
Token part of speech: NOUN

Token text: to
Token part of speech: ADP

Token text: San
Token part of speech: NOUN

Token text: Jose
Token part of speech: NOUN

Token text: .
Token part of speech: PUNCT

Token text: Do
Token part of speech: VERB

Token text: you
Token part of speech: PRON

Token text: ?
Token part of speech: PUNCT
```

### Run Analyze Entity Sentiment

To run the Analyze Entity Sentiment sample:

```
$ php src/analyze_entity_sentiment.php 'New York is great. New York is good.'
Entity Name: New York
Entity Type: LOCATION
Entity Salience: 1
Entity Magnitude: 1.8
Entity Score: 0.9
```

### Run Classify Text

To run the Classify Text sample:

```
$ php src/classify_text.php 'The first two gubernatorial elections since
President Donald Trump took office went in favor of Democratic candidates
in Virginia and New Jersey.'
Category Name: /News/Politics
Confidence: 0.99
```

## The client library

This sample uses the [Google Cloud Client Library for PHP][google-cloud-php].
You can read the documentation for more details on API usage and use GitHub
to [browse the source][google-cloud-php-source] and [report issues][google-cloud-php-issues].

## Troubleshooting

If you get the following error, set the environment variable `GCLOUD_PROJECT` to your project ID:

```
[Google\Cloud\Core\Exception\GoogleException]
No project ID was provided, and we were unable to detect a default project ID.
```

If you have not set a timezone you may get an error from php. This can be resolved by:

  1. Finding where the php.ini is stored by running php -i | grep 'Configuration File'
  1. Finding out your timezone from the list on this page: http://php.net/manual/en/timezones.php
  1. Editing the php.ini file (or creating one if it doesn't exist)
  1. Adding the timezone to the php.ini file e.g., adding the following line: date.timezone = "America/Los_Angeles"

[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php
[google-cloud-php-source]: https://github.com/GoogleCloudPlatform/google-cloud-php
[google-cloud-php-issues]: https://github.com/GoogleCloudPlatform/google-cloud-php/issues
[google-cloud-sdk]: https://cloud.google.com/sdk/
