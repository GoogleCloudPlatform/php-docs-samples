# Google Stackdriver Debugger PHP Sample Application

## Description

This simple [Silex][silex] application demonstrates how to
install and run the [Stackdriver Debugger Agent][debugger] for PHP.

[debugger]: https://cloud.google.com/debugger/docs/setup/php

## Build and Run

1. Add the Stackdriver Debugger composer package to your `composer.json`:
```
    $ composer require google/cloud-debugger:^0.1
```
2. Install the composer package:
```
    $ composer install
```
3. Install the PHP extension from [PECL][pecl]:
```
    $ pecl install stackdriver_debugger-alpha
```
4. Run the Stackdriver Debugger daemon:
```
    $ vendor/bin/google-cloud-debugger .
```
5. Run the AsyncBatchDaemon daemon:
```
    $ vendor/bin/google-cloud-batch daemon
```
6. Run the application:
```
    $ IS_BATCH_DAEMON_RUNNING=true php -S localhost:8000 -t web/
```
7. Navigate to the [Google Cloud Debugger console][debug-console] and [Select Source Code][select-source-code]
8. [Set a snapshot][snapshots] or [set a logpoint][logpoints].

See [Setting Up Stackdriver Debugger for PHP](https://cloud.google.com/debugger/docs/setup/php)
for more information.

## Contributing changes

* See [CONTRIBUTING.md][contributing]

## Licensing

* See [LICENSE][license]

[silex]: https://silex.symfony.com/
[pecl]: https://pecl.php.net/
[debug-console]: https://console.cloud.google.com/debug
[select-source-code]: https://cloud.google.com/debugger/docs/source-options]
[snapshots]: https://cloud.google.com/debugger/docs/using/snapshots
[logpoints]: https://cloud.google.com/debugger/docs/using/logpoints
[contributing]: ../CONTRIBUTING.md
[license]: ../LICENSE
