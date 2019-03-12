# Cloud Text-to-Speech: PHP Samples

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=texttospeech

## Description

This command-line application demonstrates how to invoke Cloud Text-to-Speech 
API from PHP.

## Setup

### Authentication

This sample requires you to have authentication setup. Refer to the [Authentication Getting Started Guide](https://cloud.google.com/docs/authentication/getting-started) for instructions on setting up credentials for applications.

## Install Dependencies

1. [Enable the Cloud Text-to-Speech API](https://console.cloud.google.com/flows/enableapi?apiid=texttospeech.googleapis.com).

1. **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `composer install` (if composer is installed globally).

## Samples

### List voices
```
Usage:
  php texttospeech.php list-voices

```

### Synthesize text/ssml
```
Usage:
  php texttospeech.php synthesize-text <TEXT>
  php texttospeech.php synthesize-ssml <SSML>

Examples:
  php texttospeech.php synthesize-text -h
  php texttospeech.php synthesize-ssml -h
  php texttospeech.php synthesize-text-audio-profile -h
  php texttospeech.php synthesize-text "Hello there."
  php texttospeech.php synthesize-ssml "<speak>Hello there.</speak>"
  php texttospeech.php synthesize-text-effects-profile "Hello there." "handset-class-device"
```

### Synthesize file
```
Usage:
  php texttospeech.php synthesize-text-file <FILE_PATH> 
  php texttospeech.php synthesize-ssml-file <FILE_PATH> 
  php texttospeech.php synthesize-text-effects-profile-file <FILE_PATH> <AUDIO_PROFILE>
  
Examples:
  php texttospeech.php synthesize-text-file -h
  php texttospeech.php synthesize-ssml-file -h
  php texttospeech.php synthesize-text-audio-profile-file -h
  php texttospeech.php synthesize-text-file resources/hello.txt
  php texttospeech.php synthesize-ssml-file resources/hello.ssml
  php texttospeech.php synthesize-text-effects-profile-file resources/hello.txt "handset-class-device"
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