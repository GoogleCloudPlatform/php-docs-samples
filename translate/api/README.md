# Google Cloud Translate API Samples

## Description

These samples show how to use the [Google Cloud Translate API](
https://cloud.google.com/translate/).

## Build and Run
1.  **Enable APIs** - [Enable the Translate API](https://console.cloud.google.com/flows/enableapi?apiid=translate.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "Create Credentials"
    and select "API key". Copy the API key.
3.  **Clone the repo** and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/vision/api
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  **Run**:
```
$ php translate.php 
Console Tool

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
  detect      Detect which language text was written in using Google Cloud Translate API
  help        Displays help for a command
  list        Lists commands
  list-codes  List all the language codes in the Google Cloud Translate API
  list-langs  List language codes and names in the Google Cloud Translate API
  translate   Translate text using Google Cloud Translate API
```

6. Run `php translate.php COMMAND --help` to print information about the usage of each command.

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
