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

use Google\Cloud\Firestore\FirestoreClient;

/**
 * Create a query with range clauses.
 * ```
 * fs_range_query();
 * ```
 *
 */
function fs_range_query()
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient();
    $cities_ref = $db->collection('cities');
    # [START fs_range_query]
    $range_query = $cities_ref->where('state', '>=', 'CA')->where('state', '<=', 'IN');
    # [END fs_range_query]
    foreach ($range_query->documents() as $document) {
        printf('Document %s returned by query CA<=state<=IN' . PHP_EOL, $document->id());
    }
}
