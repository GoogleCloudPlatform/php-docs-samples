<?php

# [START storage_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Storage\StorageClient;

# Instantiates a client
$storage = new StorageClient();

# The name for the new bucket
$bucketName = 'my-new-bucket';

# Creates the new bucket
$bucket = $storage->createBucket($bucketName);

echo 'Bucket ' . $bucket->name() . ' created.';
# [END storage_quickstart]
return $bucket;
