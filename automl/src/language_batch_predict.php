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

if (count($argv) < 6 || count($argv) > 6) {
    return printf("Usage: php %s PROJECT_ID LOCATION MODEL_ID INPUT_URI OUTPUT_URI\n", __FILE__);
}
list($_, $projectId, $location, $modelId, $inputUri, $outputUri) = $argv;

// [START automl_language_batch_predict]
use Google\Cloud\AutoMl\V1\BatchPredictInputConfig;
use Google\Cloud\AutoMl\V1\BatchPredictOutputConfig;
use Google\Cloud\AutoMl\V1\GcsSource;
use Google\Cloud\AutoMl\V1\GcsDestination;
use Google\Cloud\AutoMl\V1\PredictionServiceClient;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $modelId = 'my_model_id_123';
// $inputUri = 'gs://cloud-samples-data/text.txt';
// $outputUri = 'gs://YOUR_BUCKET_ID/path_to_store_results/';

$client = new PredictionServiceClient();

try {
    // get full path of model
    $formattedName = $client->modelName(
        $projectId,
        $location,
        $modelId
    );

    // set the multiple GCS uri
    $gcsSource = (new GcsSource())
        ->setInputUri($inputUri);
    $inputConfig = (new BatchPredictInputConfig())
        ->setGcsSource($gcsSource);
    $gcsDestination = (new GcsDestination())
        ->setInputUri($outputUri);
    $outputConfig = (new BatchPredictOutputConfig())
        ->setGcsDestination($gcsDestination);

    $operationResponse = $client->batchPredict(
        $formattedName,
        $inputConfig,
        $outputConfig
    );

    printf('Waiting for operation to complete...' . PHP_EOL);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $result = $operationResponse->getResult();
        printf('Batch Prediction results saved to Cloud Storage bucket. %s' . PHP_EOL, $result);
    } else {
        $error = $operationResponse->getError();
        print($error->getMessage());
    }
} finally {
    $client->close();
}
// [END automl_language_batch_predict]
