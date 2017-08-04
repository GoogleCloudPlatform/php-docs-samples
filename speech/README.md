# Google Cloud Speech API Samples

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

To run the Speech Samples:

    $ php speech.php

    Cloud Speech

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
      help                    Displays help for a command
      list                    Lists commands
      transcribe              Transcribe an audio file using Google Cloud Speech API
      transcribe-async        Transcribe an audio file asynchronously using Google Cloud Speech API
      transcribe-async-gcs    Transcribe audio asynchronously from a Storage Object using Google Cloud Speech API
      transcribe-async-words  Transcribe an audio file asynchronously and print word time offsets using Google Cloud Speech API
      transcribe-gcs          Transcribe audio from a Storage Object using Google Cloud Speech API
      transcribe-stream       Transcribe a stream of audio using Google Cloud Speech API
      transcribe-words        Transcribe an audio file and print word time offsets using Google Cloud Speech API

Once you have a speech sample in the proper format, send it through the speech
API using the transcribe command:

```sh
php speech.php transcribe test/data/audio32KHz.raw --encoding LINEAR16 --sample-rate 32000
php speech.php transcribe-async test/data/audio32KHz.flac --encoding FLAC --sample-rate 32000
php speech.php transcribe-words test/data/audio32KHz.flac --encoding FLAC --sample-rate 32000

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

[speech-api]: http://cloud.google.com/speech
[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php/
[choose-encoding]: https://cloud.google.com/speech/docs/best-practices#choosing_an_audio_encoding
[sox]: http://sox.sourceforge.net/
[grpc]: http://grpc.io
[adc]: https://developers.google.com/identity/protocols/application-default-credentials
