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
    return printf("Usage: php %s PROJECT_ID LOCATION OPERATION_ID\n", __FILE__);
}
list($_, $projectId, $location, $operationId) = $argv;

// [START automl_get_operation_status]
use Google\ApiCore\LongRunning\OperationsClient;

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $operationId = 'my_operation_id_123';

$client = new OperationsClient();

try {
    // full name of operation
    $formattedName = 'projects/' . $projectId . '/locations/us-central1/operations/' . $operationId;

    // get latest state of long running operation
    $operation = $client->getOperation($name);
    printf('Operation name: %s' . PHP_EOL, $operation->getName());
    print('Operation details: ');
    print($operation);
} finally {
    if (isset($client)) {
        $client->close();
    }
}
// [END automl_get_operation_status]
