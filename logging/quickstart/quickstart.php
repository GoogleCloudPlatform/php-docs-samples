<?php

# [START logging_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Logging\LoggingClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$logging = new LoggingClient([
    'projectId' => $projectId
]);

# The name of the log to write to
$logName = 'my-log';

# Selects the log to write to
$logger = $logging->logger($logName);

# The data to log
$text = 'Hello, world!';

# Creates the log entry
$entry = $logger->entry($text, [
    'type' => 'global'
]);

# Writes the log entry
$logger->write($entry);

echo 'Logged ' . $text;
# [END logging_quickstart]
return $entry;
