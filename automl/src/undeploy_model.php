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
    return printf("Usage: php %s PROJECT_ID LOCATION MODEL_ID\n", __FILE__);
}
list($_, $projectId, $location, $modelId) = $argv;

// [START automl_undeploy_model]
use Google\Cloud\AutoMl\V1\AutoMlClient;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $modelId = 'my_model_id_123';

$client = new AutoMlClient();

try {
    // get full path of model
    $formattedName = $client->modelName(
        $projectId,
        $location,
        $modelId
    );

    $operationResponse = $client->undeployModel($formattedName);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $result = $operationResponse->getResult();
        printf('Model undeployed.' . PHP_EOL);
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
} finally {
    $client->close();
}
// [END automl_undeploy_model]
