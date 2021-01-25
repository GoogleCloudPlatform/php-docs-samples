# How to become a contributor and submit your own code

## Contributor License Agreements

We'd love to accept your patches! Before we can take them, we
have to jump a couple of legal hurdles.

Please fill out either the individual or corporate Contributor License Agreement
(CLA).

  * If you are an individual writing original source code and you're sure you
    own the intellectual property, then you'll need to sign an
    [individual CLA](https://developers.google.com/open-source/cla/individual).
  * If you work for a company that wants to allow you to contribute your work,
    then you'll need to sign a
    [corporate CLA](https://developers.google.com/open-source/cla/corporate).

Follow either of the two links above to access the appropriate CLA and
instructions for how to sign and return it. Once we receive it, we'll be able to
accept your pull requests.

## Contributing A Patch

1. Submit an issue describing your proposed change.
1. The repo owner will respond to your issue promptly.
1. If your proposed change is accepted, and you haven't already done so, sign a
   Contributor License Agreement (see details above).
1. Fork this repo, develop and test your code changes.
1. Ensure that your code adheres to the existing style in the sample to which
   you are contributing.
1. Ensure that your code has an appropriate set of unit tests which all pass.
1. Submit a pull request.

## Testing your code changes.

### Install dependencies

To run the tests in a samples directory, you will need to install
[Composer](http://getcomposer.org/doc/00-intro.md).

First install the testing dependencies which are shared across all samples:

```
composer install -d testing/
```

Next, install the dependencies for the individual sample you're testing:

```
SAMPLES_DIRECTORY=translate
cd $SAMPLES_DIRECTORY
composer install
```

### Environment variables
Set up [application default credentials](https://cloud.google.com/docs/authentication/getting-started)
by setting the environment variable `GOOGLE_APPLICATION_CREDENTIALS` to the path to a service
account key JSON file.

Then set any environment variables needed by the test. Check the
`$SAMPLES_DIRECTORY/test` directory to see what specific variables are needed.
```
export GOOGLE_PROJECT_ID=YOUR_PROJECT_ID
export GOOGLE_STORAGE_BUCKET=YOUR_BUCKET
```

If your tests require new environment variables, you can set them up in
[.kokoro/secrets.sh.enc](.kokoro/secrets.sh.enc). For instructions on managing those variables,
view [.kokoro/secrets-example.sh](.kokoro/secrets-example.sh) for more information.

### Run the tests

Once the dependencies are installed and the environment variables set, you can run the
tests in a samples directory.
```
cd $SAMPLES_DIRECTORY
# Execute the "phpunit" installed for the shared dependencies
PATH_TO_REPO=/path/to/php-docs-samples
$PATH_TO_REPO/testing/vendor/bin/phpunit
```

Use `phpunit -v` to get a more detailed output if there are errors.

## Style

Samples in this repository follow the [PSR2][psr2] and [PSR4][psr4]
recommendations. This is enforced using [PHP CS Fixer][php-cs-fixer].

Install that by running

```
composer global require friendsofphp/php-cs-fixer
```

Then to fix your directory or file run 

```
php-cs-fixer fix .
php-cs-fixer fix path/to/file
```

The [DLP snippets](https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/dlp) are an example of snippets following the latest style guidelines.

[psr2]: http://www.php-fig.org/psr/psr-2/
[psr4]: http://www.php-fig.org/psr/psr-4/
[php-cs-fixer]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
