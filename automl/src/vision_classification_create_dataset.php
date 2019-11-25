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

if (count($argv) < 4 || count($argv) > 4) {
    return printf("Usage: php %s PROJECT_ID LOCATION DISPLAY_NAME\n", __FILE__);
}
list($_, $projectId, $location, $displayName) = $argv;

// [START automl_vision_classification_create_dataset]
use Google\Cloud\AutoMl\V1\AutoMlClient;
use Google\Cloud\AutoMl\V1\ClassificationType;
use Google\Cloud\AutoMl\V1\Dataset;
use Google\Cloud\AutoMl\V1\ImageClassificationDatasetMetadata;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $displayName = 'your_dataset_name';

$client = new AutoMlClient();

try {
    // resource that represents Google Cloud Platform location
    $formattedParent = $client->locationName(
        $projectId,
        $location
    );

    $metadata = (new ImageClassificationDatasetMetadata())
        ->setClassificationType(ClassificationType::MULTILABEL);
    $dataset = (new Dataset())
        ->setDisplayName($displayName)
        ->setImageClassificationDatasetMetadata($metadata);

    // create dataset with the above location and metadata
    $operationResponse = $client->createDataset($formattedParent, $dataset);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $result = $operationResponse->getResult();

        // display dataset information
        $splitName = explode('/', $result->getName());
        printf('Dataset name: %s' . PHP_EOL, $result->getName());
        printf('Dataset id: %s' . PHP_EOL, end($splitName));
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
} finally {
    $client->close();
}
// [END automl_vision_classification_create_dataset]
