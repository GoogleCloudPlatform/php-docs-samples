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

## Audio Format

For the best results, use [SoX][sox] to format audio files into raw format:

```sh
sox YourAudio.mp3 --rate 16k -encoding signed -bits 8 YourAudio.raw
```

See [Choosing an Audio Encoding][choose-encoding] for more information.

## Usage

Once you have a speech sample in the proper format, send it through the speech
API using the transcribe command:

```sh
php speech.php transcribe YourAudio.raw --encoding LINEAR16 --sample-rate 16000
```

[speech-api]: http://cloud.google.com/speech
[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php/
[choose-encoding]: https://cloud.google.com/speech/docs/best-practices#choosing_an_audio_encoding
[sox]: http://sox.sourceforge.net/
[grpc]: http://grpc.io
[adc]: https://developers.google.com/identity/protocols/application-default-credentials
