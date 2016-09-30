<?php

require __DIR__ . '/vendor/autoload.php';

# [START logging_quickstart]
# Imports the Google Cloud client library
use Google\Cloud\Logging\LoggingClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$logging = new LoggingClient([
    'projectId' => $projectId
]);

# List logging entries
$entries = $logging->entries();
# [END logging_quickstart]
return $entries;
