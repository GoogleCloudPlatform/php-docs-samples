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
1. Fork the desired repo, develop and test your code changes.

   Install dependencies via [Composer](http://getcomposer.org/doc/00-intro.md).
   Run `composer install` (if composer is installed globally).

   To run the tests, first set up [application default
   credentials](https://cloud.google.com/docs/authentication/getting-started)
   by setting the environment variable `GOOGLE_APPLICATION_CREDENTIALS` to the
   path to a service account key JSON file.

   Then set any environment variables needed by the test. Take a look in
   `.kokoro/secrets-example.sh` for a list of all environment variables used in
   the tests, and in `$SAMPLES_DIRECTORY/test` to see the specific variables used
   in each test.
   ```
   export GOOGLE_PROJECT_ID=YOUR_PROJECT_ID
   export GOOGLE_STORAGE_BUCKET=YOUR_BUCKET
   ```

   To run the tests in a samples directory, first run
   `composer install -d testing/` to install shared test dependencies. Then run
   `composer install` in any directory containing a `phpunit.xml.dist` file.
   Invoke the `phpunit` contained in `testing/vendor/bin/phpunit` to run the
   tests.
   ```
   SAMPLES_DIRECTORY=translate
   composer install -d testing/
   cd $SAMPLES_DIRECTORY
   composer install
   ../testing/vendor/bin/phpunit
   ```

1. Ensure that your code adheres to the existing style in the sample to which
   you are contributing.
1. Ensure that your code has an appropriate set of unit tests which all pass.
1. Submit a pull request.

## Testing your code changes.

### Install dependencies

  Install your samples' dependencies with [Composer](http://getcomposer.org/doc/00-intro.md).
  ```
  cd $SAMPLES_DIRECTORY
  composer install
  ```

  Install the install the global Composer test dependencies by running
  ```
  bash testing/composer.sh 
  ```

  Add ~/.composer/vendor/bin to your $PATH, so you can access the test dependencies
  ```
  export PATH=$HOME/.composer/vendor/bin:$PATH
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
phpunit
```

Use `phpunit -v` to get a more detailed output if there are errors.

## Style

Samples in this repository follow the [PSR2][psr2] and [PSR4][psr4]
recommendations. This is enforced using [PHP CS Fixer][php-cs-fixer].

[psr2]: http://www.php-fig.org/psr/psr-2/
[psr4]: http://www.php-fig.org/psr/psr-4/
[php-cs-fixer]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
