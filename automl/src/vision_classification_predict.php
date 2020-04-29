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
    return printf("Usage: php %s PROJECT_ID LOCATION MODEL_ID FILE_PATH\n", __FILE__);
}
list($_, $projectId, $location, $modelId, $filePath) = $argv;

// [START automl_vision_classification_predict]
use Google\Cloud\AutoMl\V1\ExamplePayload;
use Google\Cloud\AutoMl\V1\Image;
use Google\Cloud\AutoMl\V1\PredictionServiceClient;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $modelId = 'my_model_id_123';
// $filePath = 'path_to_local_file.jpg';

$client = new PredictionServiceClient();

try {
    // get full path of model
    $formattedName = $client->modelName(
        $projectId,
        $location,
        $modelId);

    // read the file
    $content = file_get_contents($filePath);
    $image = (new Image())
        ->setImageBytes($content);
    // create payload
    $payload = (new ExamplePayload())
        ->setImage($image);

    // params is additional domain-specific parameters
    // score_threshold is used to filter the result
    $params = ['score_threshold' => '0.8']; // value between 0.0 and 1.0

    // predict with above model and payload
    $response = $client->predict($formattedName, $payload, $params);
    $annotations = $response->getPayload();

    // display results
    foreach ($annotations as $annotation) {
        $classification = $annotation->getClassification();
        printf('Predicted class name: %s' . PHP_EOL, $annotation->getDisplayName());
        printf('Predicted class score: %s' . PHP_EOL, $classification->getScore());
    }
} finally {
    $client->close();
}
// [END automl_vision_classification_predict]
