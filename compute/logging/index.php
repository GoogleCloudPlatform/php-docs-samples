<?php

# [START logging]
require_once __DIR__ . '/vendor/autoload.php';

use Fluent\Logger\FluentLogger;

$GLOBALS['logger'] = new FluentLogger('localhost', '24224');

function fluentd_exception_handler(Exception $e)
{
    global $logger;

    $msg = array(
        'message' => $e->getMessage(),
        'serviceContext' => array('service' => 'myapp'),
        // ... add more metadata
    );
    $logger->post('myapp.errors', $msg);
}

set_exception_handler('fluentd_exception_handler');
# [END logging]
