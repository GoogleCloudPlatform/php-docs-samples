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

if (count($argv) < 3 || count($argv) > 3) {
    return printf("Usage: php %s PROJECT_ID LOCATION\n", __FILE__);
}
list($_, $projectId, $location) = $argv;

// [START automl_list_models]
use Google\Cloud\AutoMl\V1\AutoMlClient;
use Google\Cloud\AutoMl\V1\Model\DeploymentState;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';

$client = new AutoMlClient();

try {
    // resource that represents Google Cloud Platform location
    $formattedParent = $client->locationName(
        $projectId,
        $location
    );

    $pagedResponse = $client->listModels($formattedParent);

    print('List of models' . PHP_EOL);
    foreach ($pagedResponse->iteratePages() as $page) {
        foreach ($page as $model) {
            // retrieve deployment state
            if ($model->getDeploymentState() == DeploymentState::DEPLOYED) {
                $deployment_state = 'deployed';
            } else {
                $deployment_state = 'undeployed';
            }

            // display model information
            $splitName = explode('/', $model->getName());
            printf('Model name: %s' . PHP_EOL, $model->getName());
            printf('Model id: %s' . PHP_EOL, end($splitName));
            printf('Model display name: %s' . PHP_EOL, $model->getDisplayName());
            printf('Model create time' . PHP_EOL);
            printf('seconds: %d' . PHP_EOL, $model->getCreateTime()->getSeconds());
            printf('nanos : %d' . PHP_EOL, $model->getCreateTime()->getNanos());
            printf('Model deployment state: %s' . PHP_EOL, $deployment_state);
        }
    }
} finally {
    $client->close();
}
// [END automl_list_models]
