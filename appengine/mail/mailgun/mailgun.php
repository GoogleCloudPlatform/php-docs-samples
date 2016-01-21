<?php

/**
 * Include the Autoloader
 * Run the following for the latest version:
 *
 *   $ composer require mailgun/mailgun-php
 *
 */
require 'vendor/autoload.php';
use Mailgun\Mailgun;

// Instantiate the client.
$mgClient = new Mailgun('YOUR_API_KEY');
$domain = "YOUR_DOMAIN_NAME";

// Make the call to the client.
$result = $mgClient->sendMessage($domain, array(
    'from'    => 'Excited User <YOU@YOUR_DOMAIN_NAME>',
    'to'      => 'foo@example.com',
    'cc'      => 'baz@example.com',
    'bcc'     => 'bar@example.com',
    'subject' => 'Hello',
    'text'    => 'Testing some Mailgun awesomness!',
    'html'    => '<html>HTML version of the body</html>'
), array(
    'attachment' => array('/path/to/file.txt', '/path/to/file.txt')
));
