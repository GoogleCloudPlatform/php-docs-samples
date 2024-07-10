<?php
/**
 * Copyright 2024 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/firestore/README.md
 */

namespace Google\Cloud\Samples\Firestore;

use Google\Cloud\Firestore\FirestoreClient;

/**
 * Example of a query with range and inequality filters on multiple fields.
 * @see https://cloud.google.com/firestore/docs/query-data/multiple-range-fields
 *
 * @param string $projectId The Google Cloud Project ID
 */
function query_filter_compound_multi_ineq(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    $collection = $db->collection('samples/php/users');
    // Setup the data before querying for it
    $collection->document('person1')->set(['age' => 23, 'height' => 65]);
    $collection->document('person2')->set(['age' => 37, 'height' => 55]);
    $collection->document('person3')->set(['age' => 40, 'height' => 75]);
    $collection->document('person4')->set(['age' => 40, 'height' => 65]);

    # [START firestore_query_filter_compound_multi_ineq]
    $chainedQuery = $collection
        ->where('age', '>', 35)
        ->where('height', '>', 60)
        ->where('height', '<', 70);
    # [END firestore_query_filter_compound_multi_ineq]
    foreach ($chainedQuery->documents() as $document) {
        printf(
            'Document %s returned by age > 35 and heigiht between 60 and 70' . PHP_EOL,
            $document->id()
        );
    }
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
