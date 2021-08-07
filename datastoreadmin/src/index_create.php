<?php
/**
 * Copyright 2021 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/datastoreadmin/README.md
 */

namespace Google\Cloud\Samples\DatastoreAdmin;

// [START datastore_admin_index_create]
use Google\Cloud\Datastore\Admin\V1\DatastoreAdminClient;
use Google\Cloud\Datastore\Admin\V1\Index;
use Google\Cloud\Datastore\Admin\V1\Index\AncestorMode;

/**
 * Create a Cloud Datastore index.
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $kind The entity kind to which this index applies.
 * @param int $initialPollDelayMillis number of milliseconds to wait before
 *   polling the operation. Increase $initialPollDelayMillis to 12000 for
 *   projects in Firestore-in-Datastore-mode.
 */
function index_create($projectId, $kind, int $initialPollDelayMillis = 1000)
{
    $admin = new DatastoreAdminClient([
        'projectId' => $projectId,
    ]);

    $operation = $admin->createIndex([
        'projectId' => $projectId,
        'index' => new Index([
            'kind' => $kind,
            'ancestor' => AncestorMode::ALL_ANCESTORS,
        ]),
    ]);

    $operation->pollUntilComplete([
        // delay the start of the polling operation to ensure the operation is ready.
        'initialPollDelayMillis' => $initialPollDelayMillis,
    ]);

    if (!$operation->operationFailed() && $operation->getResult() !== null) {
        printf(
            'The create index operation succeeded. Index ID: %s' . PHP_EOL,
            $operation->getResult()->getIndexId()
        );
    } else {
        $error = 'unknown';
        if ($operation->getError() !== null) {
            $error = $operation->getError()->getMessage();
        }
        printf('The create index operation failed with error %s', $error);
    }
}
// [END datastore_admin_index_create]
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
