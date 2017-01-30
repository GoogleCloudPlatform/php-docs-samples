<?php

// Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# [START error_reporting]
// Imports the Google Cloud client library
use Google\Cloud\Logging\LoggingClient;

// These variables are set by the App Engine environment. To test locally,
// ensure these are set or manually change their values.
$projectId = getenv('GCLOUD_PROJECT') ?: 'YOUR_PROJECT_ID';
$service = getenv('GAE_SERVICE') ?: 'error_reporting_quickstart';
$version = getenv('GAE_VERSION') ?: '1.0-dev';

// Instantiates a client
$logging = new LoggingClient([
    'projectId' => $projectId
]);

// The name of the log to write to
$logName = 'my-log';

// Selects the log to write to
$logger = $logging->logger($logName);

$handlerFunction = function (Exception $e) use ($logger, $service, $version) {
    // Creates the log entry with the exception trace
    $entry = $logger->entry([
        'message' => sprintf('PHP Warning: %s', $e),
        'serviceContext' => [
            'service' => $service,
            'version' => $version,
        ]
    ]);
    // Writes the log entry
    $logger->write($entry);

    print("Exception logged to Stack Driver Error Reporting" . PHP_EOL);
};

// Sets PHP's default exception handler
set_exception_handler($handlerFunction);

throw new Exception('This will be logged to Stack Driver Error Reporting');
# [END error_reporting]
