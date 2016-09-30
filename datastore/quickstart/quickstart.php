<?php

require __DIR__ . '/vendor/autoload.php';

# [START datastore_quickstart]
# Imports the Google Cloud client library
use Google\Cloud\Datastore\DatastoreClient;

# Instantiates a client
$datastore = new DatastoreClient();

# The kind of the entity to retrieve
$kind = 'Task';
# The id of the entity to retrieve
$id = 1234567890;
# The Datastore key for the entity
$taskKey = $datastore->key($kind, $id);

# Retrieves the entity
$entity = $datastore->entity($taskKey);
# [END datastore_quickstart]
return $entity;
