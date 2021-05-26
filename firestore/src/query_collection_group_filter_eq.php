<?php
/**
 * Copyright 2020 Google LLC
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

use Google\Cloud\Firestore\FirestoreClient;

/**
 * Query collection group for documents.
 * ```
 * query_collection_group_filter_eq('your-project-id');
 * ```
 */
function query_collection_group_filter_eq(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);

    # [START fs_collection_group_query]
    # [START firestore_query_collection_group_filter_eq]
    $museums = $db->collectionGroup('landmarks')->where('type', '==', 'museum');
    foreach ($museums->documents() as $document) {
        printf('%s => %s' . PHP_EOL, $document->id(), $document->data()['name']);
    }
    # [END firestore_query_collection_group_filter_eq]
    # [END fs_collection_group_query]
}

require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);