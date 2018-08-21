<?php

# [START gae_erroreporting_register_handler]
# After running "composer require google/cloud-error-reporting", register the
# error handler by including `prepend.php` in your application. This is most
# easily done in `php.ini` using `auto_prepend_file`:
#
#    ; in your application's php.ini:
#    auto_prepend_file=/srv/vendor/google/cloud-error-reporting/src/prepend.php
#
# [END gae_erroreporting_register_handler]

# Uncomment this line if you'd like to include `prepend.php` manually instead of
# using `php.ini`:
#
#    require_once 'vendor/google/cloud-error-reporting/src/prepend.php';
#
// throw a test exception to trigger our exception handler
if (isset($_GET['type'])) {
    $linkText = '<p>This should now be visible in the '
      . '<a href="https://console.cloud.google.com/errors">Error Reporting UI<a>'
      . '</p>';
    switch ($_GET['type']) {
        case 'exception':
            print('Throwing a PHP Exception.');
            print($linkText);
            /**
             * Wrap the exception in a function so that we can see the function
             * in the Stackdriver Error Reporting UI.
             */
            function throwException()
            {
                throw new \Exception('This is from "throw new Exception()"');
            }
            throwException();
            break;
        case 'error':
            print('Triggering a PHP Error.');
            print($linkText);
            trigger_error('This is from "trigger_error()"', E_USER_ERROR);
            die;
        case 'fatal':
            print('Triggering a PHP Fatal Error by including a file with a syntax error.');
            print($linkText);
            $filename = tempnam(sys_get_temp_dir(), 'php_syntax_error');
            file_put_contents($filename, "<?php syntax-error");
            require($filename);
            break;
        default:
            exit('Invalid error type. Must be "exception", "error", or "fatal"');
    }
}

?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>Google Cloud Platform | App Engine for PHP 7.2 Error Reporting examples</title>
  </head>

  <body>
    <h1>Click an error type to send to Stackdriver Error Reporting</h1>

    <ul>
      <li><a href="/?type=exception">Throw a PHP Exception</a></li>
      <li><a href="/?type=error">Trigger a PHP user-level error (e.g. a PHP warning)</a></li>
      <li><a href="/?type=fatal">Trigger a PHP Fatal Error (e.g. a syntax error)</a></li>
    </ul>
  </body>
</html>
