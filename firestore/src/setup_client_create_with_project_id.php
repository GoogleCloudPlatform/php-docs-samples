<?php
/**
 * Copyright 2018 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/firestore/README.md
 */

namespace Google\Cloud\Samples\Firestore;

# TODO(craiglabenz): Remove the `firestore_setup_client_create_with_project_id`
#                    region tag after consolidating to `firestore_setup_client_create`
# [START fs_initialize_project_id]
# [START firestore_setup_client_create_with_project_id]
use Google\Cloud\Firestore\FirestoreClient;

/**
 * Initialize Cloud Firestore with a provided project ID.
 *
 * @param string $projectId Your Google Cloud Project ID
 */
function setup_client_create_with_project_id(string $projectId): void
{
    // Create the Cloud Firestore client with a provided project ID.
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    printf('Created Cloud Firestore client with project ID: %s' . PHP_EOL, $projectId);
}
# [END firestore_setup_client_create_with_project_id]
# [END fs_initialize_project_id]
# TODO(craiglabenz): Remove the `firestore_setup_client_create_with_project_id`
#                    region tag after consolidating to `firestore_setup_client_create`

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
