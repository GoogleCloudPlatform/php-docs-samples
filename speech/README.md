# Google Cloud Speech API Samples

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=speech

These samples show how to use the [Google Cloud Speech API][speech-api]
to transcribe audio files, as well as live audio from your computer's
microphone.

This repository contains samples that use the [Google Cloud
Library for PHP][google-cloud-php] to make REST calls as well as
contains samples using the more-efficient (though sometimes more
complex) [GRPC][grpc] API. The GRPC API also allows streaming requests.

## Installation

Install the dependencies for this library via [composer](https://getcomposer.org)

    $ cd /path/to/php-docs-samples/speech
    $ composer install

Configure your project using [Application Default Credentials][adc]

    $ export GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json

## Usage

Run `php src/SNIPPET_NAME.php`. The usage will print for each if no arguments
are provided:

```sh
$ php src/transcribe_sync.php
Usage: php src/transcribe_sync.php AUDIO_FILE

$ php src/transcribe_sync.php test/data/audio32KHz.raw
Transcript: how old is the Brooklyn Bridge
Confidence: 0.98662775754929
```

Once you have a speech sample in the proper format, send it through the speech
API using the transcribe command:

```sh
php src/transcribe_sync.php test/data/audio32KHz.raw
php src/transcribe_async.php test/data/audio32KHz.raw
php src/transcribe_async_words.php test/data/audio32KHz.raw
```
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

[speech-api]: https://cloud.google.com/speech-to-text/docs/quickstart-client-libraries
[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php/
[choose-encoding]: https://cloud.google.com/speech-to-text/docs/best-practices#choosing_an_audio_encoding
[sox]: http://sox.sourceforge.net/
[grpc]: http://grpc.io
[adc]: https://developers.google.com/identity/protocols/application-default-credentials
