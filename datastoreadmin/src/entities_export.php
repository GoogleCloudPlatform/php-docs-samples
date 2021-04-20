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

// [START datastore_admin_entities_export]
use Google\Cloud\Datastore\Admin\V1\DatastoreAdminClient;

/**
 * Exports entities from Google Cloud Datastore
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $outputUrlPrefix Location for the export metadata and data
 *     files. Currently only Cloud Storage is supported. Values should be of
 *     form `gs://bucket-name/folder-name`.
 */
function entities_export($projectId, $outputUrlPrefix)
{
    $admin = new DatastoreAdminClient();

    $operation = $admin->exportEntities($projectId, $outputUrlPrefix);

    $operation->pollUntilComplete();
    if ($operation->operationSucceeded()) {
        printf(
            'The export operation succeeded. File location is %s' . PHP_EOL,
            $operation->getResult()->getOutputUrl()
        );
    } else {
        $error = $operation->getError();
        printf(
            'The export operation failed with message %s' . PHP_EOL,
            $error->getMessage()
        );
    }
}
// [END datastore_admin_entities_export]
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
