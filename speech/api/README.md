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

    $ cd /path/to/php-docs-samples/speech/api
    $ composer install

Configure your project using [Application Default Credentials][adc]

    $ export GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json

## Usage

Once you have a speech sample in the proper format, send it through the speech
API using the transcribe command:

```sh
php speech.php transcribe test/data/audio32KHz.raw --encoding LINEAR16 --sample-rate 32000
php speech.php transcribe test/data/audio32KHz.flac --encoding FLAC --sample-rate 32000 --async

```
## Troubleshooting

If you have not set a timezone you may get an error from php. This can be resolved by:
a) Finding where the php.ini is stored by running php -i | grep 'Configuration File'
b) Finding out your timezone from the list on this page: http://php.net/manual/en/timezones.php
c) Editing the php.ini file (or creating one if it doesn't exist)
d) Adding the timezone to the php.ini file e.g., adding the following line: date.timezone = "America/Los_Angeles"

[speech-api]: http://cloud.google.com/speech
[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php/
[choose-encoding]: https://cloud.google.com/speech/docs/best-practices#choosing_an_audio_encoding
[sox]: http://sox.sourceforge.net/
[grpc]: http://grpc.io
[adc]: https://developers.google.com/identity/protocols/application-default-credentials
