<?php

require __DIR__ . '/vendor/autoload.php';

# [START storage_quickstart]
# Imports the Google Cloud client library
use Google\Cloud\Storage\StorageClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$storageClient = new StorageClient([
    'projectId' => $projectId
]);

# The name for the new bucket
$bucketName = 'my-new-bucket';

# Creates the new bucket
$bucket = $storageClient->createBucket($bucketName);
# [END storage_quickstart]
