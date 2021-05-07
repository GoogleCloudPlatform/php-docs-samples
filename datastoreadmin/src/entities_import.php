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

// [START datastore_admin_entities_import]
use Google\Cloud\Datastore\Admin\V1\DatastoreAdminClient;

/**
 * Imports entities into Google Cloud Datastore
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $inputUri The full resource URL of the external storage
 *     location. Currently only Cloud Storage is supported. Values should be of
 *     form `gs://bucket-name/folder-name`.
 */
function entities_import($projectId, $inputUri)
{
    $admin = new DatastoreAdminClient();

    $operation = $admin->importEntities($projectId, $inputUri);

    $operation->pollUntilComplete([
        'initialPollDelayMillis' => 60000,
        'pollDelayMultiplier' => 1,
    ]);

    if (!$operation->operationFailed()) {
        print('The import operation succeeded' . PHP_EOL);
    } else {
        print('The import operation failed.');
    }
}
// [END datastore_admin_entities_import]
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
