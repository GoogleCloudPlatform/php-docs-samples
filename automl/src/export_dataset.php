<?php
/*
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 5 || count($argv) > 5) {
    return printf("Usage: php %s PROJECT_ID LOCATION DATASET_ID GCS_URI\n", __FILE__);
}
list($_, $projectId, $location, $datasetId, $gcsUri) = $argv;

// [START automl_export_dataset]
use Google\Cloud\AutoMl\V1\AutoMlClient;
use Google\Cloud\AutoMl\V1\GcsDestination;
use Google\Cloud\AutoMl\V1\OutputConfig;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $datasetId = 'my_dataset_id_123';
// $gcsUri = 'gs://BUCKET_ID/path_to_export/';

$client = new AutoMlClient();

try {
    // get full path of dataset
    $formattedName = $client->datasetName(
        $projectId,
        $location,
        $datasetId
    );

    // set GCS uri
    $gcsDestination = (new GcsDestination())
        ->setInputUri($gcsUri);
    $outputConfig = (new OutputConfig())
        ->setGcsDestination($gcsDestination);

    $operationResponse = $client->exportData($formattedName, $outputConfig);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $result = $operationResponse->getResult();
        printf('Dataset exported.' . PHP_EOL);
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
} finally {
    $client->close();
}
// [END automl_export_dataset]
