# Google Stackdriver Debugger PHP Sample Application

## Description

This simple [Silex](https://silex.symfony.com/) application demonstrates how to
install and run the Stackdriver Debugger Agent for PHP.

## Build and Run

1. Add the Stackdriver Debugger composer package to your `composer.json`:
```
    $ composer require google/cloud-debugger:^0.1
```
2. Install the composer package:
```
    $ composer install
```
3. Install the PHP extension from [PECL](https://pecl.php.net/):
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

See [Setting Up Stackdriver Debugger for PHP](https://cloud.google.com/debugger/docs/setup/php)
for more information.

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
