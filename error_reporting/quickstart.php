<?php

// Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# [START error_reporting_quickstart]
// Imports the Cloud Client Library
use Google\Cloud\ErrorReporting\Bootstrap;
use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Core\Report\SimpleMetadataProvider;

// These variables are set by the App Engine environment. To test locally,
// ensure these are set or manually change their values.
$projectId = getenv('GCLOUD_PROJECT') ?: 'YOUR_PROJECT_ID';
$service = getenv('GAE_SERVICE') ?: 'error_reporting_quickstart';
$version = getenv('GAE_VERSION') ?: 'test';

// Instantiates a client
$logging = new LoggingClient([
    'projectId' => $projectId,
]);
// Set the projectId, service, and version via the SimpleMetadataProvider
$metadata = new SimpleMetadataProvider([], $projectId, $service, $version);
// Create a PSR-3 compliant logger
$psrLogger = $logging->psrLogger('error-log', [
    'metadataProvider' => $metadata,
]);
// Using the Error Reporting Bootstrap class, register your PSR logger as a PHP
// exception hander. This will ensure all exceptions are logged to Stackdriver.
Bootstrap::init($psrLogger);

print("Throwing a test exception. You can view the message at https://console.cloud.google.com/errors." . PHP_EOL);
throw new Exception('quickstart.php test exception');
# [END error_reporting_quickstart]
