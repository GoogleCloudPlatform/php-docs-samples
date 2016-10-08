<?php

# [START bigquery_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\BigQuery\BigQueryClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$bigquery = new BigQueryClient([
    'projectId' => $projectId
]);

# The name for the new dataset
$datasetName = 'my_new_dataset';

# Creates the new dataset
$dataset = $bigquery->createDataset($datasetName);

echo 'Dataset ' . $dataset->id() . ' created.';
# [END bigquery_quickstart]
return $dataset;
