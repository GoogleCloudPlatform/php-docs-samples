<?php

require __DIR__ . '/vendor/autoload.php';

# [START bigquery_quickstart]
# Imports the Google Cloud client library
use Google\Cloud\BigQuery\BigQueryClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$bigqueryClient = new BigQueryClient([
    'projectId' => $projectId
]);

# The name for the new dataset
$datasetName = 'my_new_dataset';

# Creates the new dataset
$dataset = $bigqueryClient->createDataset($datasetName);
# [END bigquery_quickstart]
