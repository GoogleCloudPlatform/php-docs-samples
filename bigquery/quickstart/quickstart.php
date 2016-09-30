<?php

require __DIR__ . '/vendor/autoload.php';

# [START bigquery_quickstart]
# Imports the Google Cloud client library
use Google\Cloud\BigQuery\BigQueryClient;

# Instantiates a client
$bigquery = new BigQueryClient();

# The name for the new dataset
$datasetName = 'my_new_dataset';

# Creates the new dataset
$dataset = $bigquery->createDataset($datasetName);
# [END bigquery_quickstart]
return $dataset;
