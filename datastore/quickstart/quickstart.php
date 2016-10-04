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

# Creates a Datastore query
$query = $datastore->gqlQuery("Select * from $kind");

# Runs the query
$results = $datastore->runQuery($query);
# [END datastore_quickstart]
return $results;
