# Google Cloud Natural Language API Samples

These samples show how to use the [Google Cloud Natural Language API][language-api]
to analyze text.

This repository contains samples that use the [Google Cloud
Library for PHP][google-cloud-php] to make REST calls as well as
contains samples using the more-efficient (though sometimes more
complex) [GRPC][grpc] API. The GRPC API also allows streaming requests.

[language-api]: http://cloud.google.com/natural-language
[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php/
[grpc]: http://grpc.io


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

## Samples

To run the Natural Language Samples:

    $ php language.php
    Cloud Natural Language

    Usage:
      command [options] [arguments]

    Options:
      -h, --help            Display this help message
      -q, --quiet           Do not output any message
      -V, --version         Display this application version
          --ansi            Force ANSI output
          --no-ansi         Disable ANSI output
      -n, --no-interaction  Do not ask any interactive question
      -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

    Available commands:
      all        Analyze syntax, sentiment and entities in text.
      entities   Analyze entities in text.
      help       Displays help for a command
      list       Lists commands
      sentiment  Analyze sentiment in text.
      syntax     Analyze syntax in text.

### Run Analyse Entities

To run the Analyse Entities sample:

    $ php language.php entities 'I know the way to San Jose'
    entities:
        -
            name: way
            type: OTHER
            metadata: {  }
            salience: 0.6970506
            mentions:
                -
                    text:
                        content: way
                        beginOffset: 11
                    type: COMMON
        -
            name: 'San Jose'
            type: LOCATION
            metadata:
                mid: /m/0f04v
                wikipedia_url: 'http://en.wikipedia.org/wiki/San_Jose,_California'
            salience: 0.30294943
            mentions:
                -
                    text:
                        content: 'San Jose'
                        beginOffset: 18
                    type: PROPER
    language: en

### Run Analyse Sentiment

To run the Analyse Sentiment sample:

    $ php language.php sentiment 'I know the way to San Jose'
    documentSentiment:
        magnitude: 0.3
        score: 0.3
    language: en
    sentences:
        -
            text:
                content: 'I know the way to San Jose'
                beginOffset: 0
            sentiment:
                magnitude: 0.3
                score: 0.3

### Run Analyse Syntax

To run the Analyse Syntax sample:

    $ php language.php sentiment 'I know the way to San Jose'
    documentSentiment:
        magnitude: 0.3
        score: 0.3
    language: en
    sentences:
        -
            text:
                content: 'I know the way to San Jose'
                beginOffset: 0
            sentiment:
                magnitude: 0.3
                score: 0.3

## The client library

This sample uses the [Google Cloud Client Library for PHP][google-cloud-php].
You can read the documentation for more details on API usage and use GitHub
to [browse the source][google-cloud-php-source] and  [report issues][google-cloud-php-issues].

[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php
[google-cloud-php-source]: https://github.com/GoogleCloudPlatform/google-cloud-php
[google-cloud-php-issues]: https://github.com/GoogleCloudPlatform/google-cloud-php/issues
[google-cloud-sdk]: https://cloud.google.com/sdk/
