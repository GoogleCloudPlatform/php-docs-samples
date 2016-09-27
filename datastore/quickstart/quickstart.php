<?php

require __DIR__ . '/vendor/autoload.php';

# [START datastore_quickstart]
# Imports the Google Cloud client library
use Google\Cloud\Datastore\DatastoreClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$datastoreClient = new DatastoreClient([
    'projectId' => $projectId
]);

# The kind of the entity to retrieve
$kind = 'Task';
# The id of the entity to retrieve
$id = 1234567890;
# The Datastore key for the entity
$taskKey = $datastoreClient->key($kind, $id);

# Retrieves the entity
$dataset = $datastoreClient->lookup($taskKey);
# [END datastore_quickstart]
