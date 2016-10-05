<?php

# [START datastore_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Datastore\DatastoreClient;

# Instantiates a client
$datastore = new DatastoreClient();

# The kind of the entity to retrieve
$kind = 'Person';

# The name/ID of the entity to retrieve
$name = 'Bob';

# The Datastore key for the entity
$taskKey = $datastore->key($kind, $name);

# Retrieves the entity
$entity = $datastore->lookup($taskKey);
# [END datastore_quickstart]
return $entity;
