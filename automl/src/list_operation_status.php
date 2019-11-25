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

// [START automl_list_operation_status]
use Google\ApiCore\LongRunning\OperationsClient;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $operationId = 'my_operation_id_123';

$client = new OperationsClient();

try {
    // resource that represents Google Cloud Platform location
    $formattedName = $client->locationName(
        $projectId,
        $location
    );

    // list all operations
    $filter = '';
    $pagedResponse = $client->listOperations($formattedName, '');

    print('List of models' . PHP_EOL);
    foreach ($pagedResponse->iteratePages() as $page) {
        foreach ($page as $operation) {
            // display operation information
            printf('Operation name: %s' . PHP_EOL, $operation->getName());
            print('Operation details: ');
            print($operation);
        }
    }
} finally {
    if (isset($client)) {
        $client->close();
    }
}
// [END automl_list_operation_status]
