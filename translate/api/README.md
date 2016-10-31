# Google Cloud Translate API Samples

These samples show how to use the [Google Cloud Translate API](
https://cloud.google.com/translate/).

## Setup

1. Visit the [Google API Console](
https://pantheon.corp.google.com/apis/dashboard) and enable the Cloud 
Translate API.

2. Click `Credentials` -> `Create Credentials` -> `API key`.  Copy the
API key.

3. Replace `YOUR-API-KEY` in `translate.php` with your api key copied
in step 2.

4. Run:
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

