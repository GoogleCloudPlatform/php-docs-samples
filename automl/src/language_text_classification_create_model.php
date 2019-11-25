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
    return printf("Usage: php %s PROJECT_ID LOCATION DATASET_ID DISPLAY_NAME\n", __FILE__);
}
list($_, $projectId, $location, $datasetId, $displayName) = $argv;

// [START automl_language_text_classification_create_model]
use Google\Cloud\AutoMl\V1\AutoMlClient;
use Google\Cloud\AutoMl\V1\Model;
use Google\Cloud\AutoMl\V1\TextClassificationModelMetadata;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $datasetId = 'my_dataset_id_123';
// $displayName = 'your_dataset_name';

$client = new AutoMlClient();

try {
    // resource that represents Google Cloud Platform location
    $formattedParent = $client->locationName(
        $projectId,
        $location
    );

    // leave model unset to use the default base model provided by Google
    $metadata = new TextClassificationModelMetadata();
    $model = (new Model())
        ->setDisplayName($displayName)
        ->setDatasetId($datasetId)
        ->setTextClassificationModelMetadata($metadata);

    // create model with above location and metadata
    $operationResponse = $client->createModel($formattedParent, $model);
    $operation = $operationResponse->getOperation();
    printf('Training operation name: %s' . PHP_EOL, $operation->getName());
    print('Training started...' . PHP_EOL);
} finally {
    $client->close();
}
// [END automl_language_text_classification_create_model]
