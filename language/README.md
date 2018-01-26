# Google Cloud Natural Language API Samples

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.png
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=language

These samples show how to use the [Google Cloud Natural Language API][language-api]
to analyze text.

[language-api]: http://cloud.google.com/natural-language
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
      all               Analyze syntax, sentiment and entities in text.
      entities          Analyze entities in text.
      help              Displays help for a command
      list              Lists commands
      sentiment         Analyze sentiment in text.
      syntax            Analyze syntax in text.
      entity-sentiment  Analyze sentiment of entities in text.
      classify          Classify text into categories.

### Run Analyze Entities

To run the Analyze Entities sample:

    $ php language.php entities 'I know the way to San Jose. Do You?'
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

### Run Analyze Sentiment

To run the Analyze Sentiment sample:

    $ php language.php sentiment 'I know the way to San Jose. Do you?'
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
        -
            text:
                content: 'Do you?'
                beginOffset: 28
            sentiment:
                magnitude: 0.1
                score: -0.1

### Run Analyze Syntax

To run the Analyze Syntax sample:

    $ php language.php syntax 'I know the way to San Jose. Do you?'
    sentences:
        -
            text:
                content: 'I know the way to San Jose.'
                beginOffset: 0
        -
            text:
                content: 'Do you?'
                beginOffset: 28
    tokens:
        -
            text:
                content: I
                beginOffset: 0
            partOfSpeech:
                tag: PRON
                aspect: ASPECT_UNKNOWN
                case: NOMINATIVE
                form: FORM_UNKNOWN
                gender: GENDER_UNKNOWN
                mood: MOOD_UNKNOWN
                number: SINGULAR
                person: FIRST
                proper: PROPER_UNKNOWN
                reciprocity: RECIPROCITY_UNKNOWN
                tense: TENSE_UNKNOWN
                voice: VOICE_UNKNOWN
            dependencyEdge:
                headTokenIndex: 1
                label: NSUBJ
            lemma: I
                    score: 0.3
        ...
    language: en
    entities: {  }


### Run Analyze Entity Sentiment

To run the Analyze Entity Sentiment sample:

    $ php language.php entity-sentiment 'New York is great. New York is good.'
    Entity Name: New York
    Entity Type: LOCATION
    Entity Salience: 1
    Entity Magnitude: 1.7999999523163
    Entity Score: 0

    Mentions:
      Begin Offset: 0
      Content: New York
      Mention Type: PROPER
      Mention Magnitude: 0.89999997615814
      Mention Score: 0.89999997615814

    Begin Offset: 17
      Content: New York
      Mention Type: PROPER
      Mention Magnitude: 0.80000001192093
      Mention Score: -0.80000001192093

### Run Classify Text

To run the Classify Text sample:

    $ php language.php classify 'The first two gubernatorial elections since
    President Donald Trump took office went in favor of Democratic candidates
    in Virginia and New Jersey.'
    Category Name: /News/Politics
    Confidence: 0.99000000953674

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
