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
    return printf("Usage: php %s PROJECT_ID LOCATION MODEL_ID CONTENT\n", __FILE__);
}
list($_, $projectId, $location, $modelId, $content) = $argv;

// [START automl_language_text_classification_predict]
use Google\Cloud\AutoMl\V1\ExamplePayload;
use Google\Cloud\AutoMl\V1\PredictionServiceClient;
use Google\Cloud\AutoMl\V1\TextSnippet;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $modelId = 'my_model_id_123';
// $content = 'text to predict';
$client = new PredictionServiceClient();
try {
    // get full path of model
    $formattedName = $client->modelName(
        $projectId,
        $location,
        $modelId);

    // create payload
    $textSnippet = (new TextSnippet())
        ->setContent($content)
        ->setMimeType('text/plain'); // Types: 'text/plain', 'text/html'
    $payload = (new ExamplePayload())
        ->setTextSnippet($textSnippet);

    // predict with above model and payload
    $response = $client->predict($formattedName, $payload);
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
// [END automl_language_text_classification_predict]
