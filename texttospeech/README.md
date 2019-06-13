# Cloud Text-to-Speech: PHP Samples

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=texttospeech

## Description

These command-line samples demonstrates how to invoke
[Cloud Text-to-Speech API][tts-api] from PHP.

[tts-api]: https://cloud.google.com/text-to-speech/docs/quickstart-client-libraries

## Setup

### Authentication

This sample requires you to have authentication setup. Refer to the [Authentication Getting Started Guide](https://cloud.google.com/docs/authentication/getting-started) for instructions on setting up credentials for applications.

## Install Dependencies

1. [Enable the Cloud Text-to-Speech API](https://console.cloud.google.com/flows/enableapi?apiid=texttospeech.googleapis.com).

1. **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `composer install` (if composer is installed globally).

## Samples

### List voices

Run `php src/SNIPPET_NAME.php`. The usage will print for each if arguments are required:

```sh
$ php src/synthesize_text.php
Usage: php src/synthesize_text.php TEXT

$ php src/list_voices.php
Name: ar-XA-Wavenet-A
Supported language: ar-XA
SSML voice gender: FEMALE
Natural Sample Rate Hertz: 24000
...
```

### Synthesize text/ssml

```
Usage:
  php src/synthesize_text.php <TEXT>
  php src/synthesize_ssml.php <SSML>

Examples:
  php src/synthesize_text_audio_profile.php
  php src/synthesize_text.php "Hello there."
  php src/synthesize_ssml.php "<speak>Hello there.</speak>"
  php src/synthesize_text_effects_profile.php "Hello there." "handset-class-device"
```

### Synthesize file
```
Usage:
  php src/synthesize_text_file.php <FILE_PATH>
  php src/synthesize_ssml_file.php <FILE_PATH>
  php src/synthesize_text_effects_profile_file.php <FILE_PATH> <AUDIO_PROFILE>

Examples:
  php src/synthesize_text_file.php
  php texttospeech.php synthesize_ssml_file.php
  php src/synthesize_text_audio_profile_file.php
  php src/synthesize_text_file.php resources/hello.txt
  php src/synthesize_ssml_file.php resources/hello.ssml
  php src/synthesize_text_effects_profile_file.php resources/hello.txt "handset-class-device"
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

  1. Finding where the php.ini is stored by running `php -i | grep 'Configuration File'`
  1. Finding out your timezone from the list on this page: http://php.net/manual/en/timezones.php
  1. Editing the php.ini file (or creating one if it doesn't exist)
  1. Adding the timezone to the php.ini file e.g., adding the following line: `date.timezone = "America/Los_Angeles"`

[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php
[google-cloud-php-source]: https://github.com/GoogleCloudPlatform/google-cloud-php
[google-cloud-php-issues]: https://github.com/GoogleCloudPlatform/google-cloud-php/issues