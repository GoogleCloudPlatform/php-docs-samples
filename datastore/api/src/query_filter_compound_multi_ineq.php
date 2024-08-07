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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/datastore/api/README.md
 */

namespace Google\Cloud\Samples\Datastore;

use Google\Cloud\Datastore\DatastoreClient;
use DateTime;

/**
 * Example of a query with range and inequality filters on multiple fields.
 * @see https://cloud.google.com/datastore/docs/multiple-range-fields
 *
 * @param string $namespaceId
 */
function query_filter_compound_multi_ineq(string $namespaceId = null): void
{
    $datastore = new DatastoreClient(['namespaceId' => $namespaceId]);
    // [START datastore_query_filter_compound_multi_ineq]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('priority', '>', 3)
        ->filter('created', '>', new DateTime('1990-01-01T00:00:00z'));
    // [END datastore_query_filter_compound_multi_ineq]
    $result = $datastore->runQuery($query);
    $found = false;
    foreach ($result as $entity) {
        $found = true;
        printf(
            'Document %s returned by priority > 3 and created > 1990' . PHP_EOL,
            $entity->key()
        );
    }

    if (!$found) {
        print("No records found.\n");
    }
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
