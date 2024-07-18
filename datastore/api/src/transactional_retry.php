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

namespace Google\Cloud\Samples\Datastore;

use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Key;

/**
 * Call a function and retry upon conflicts for several times.
 *
 * @param string $namespaceId
 * @param Key $fromKey
 * @param Key $toKey
 */
function transactional_retry(
    Key $fromKey,
    Key $toKey,
    string $namespaceId = null
) {
    $datastore = new DatastoreClient(['namespaceId' => $namespaceId]);
    // [START datastore_transactional_retry]
    $retries = 5;
    for ($i = 0; $i < $retries; $i++) {
        try {
            require_once __DIR__ . '/transfer_funds.php';
            transfer_funds($fromKey, $toKey, 10, $namespaceId);
        } catch (\Google\Cloud\Core\Exception\ConflictException $e) {
            // if $i >= $retries, the failure is final
            continue;
        }
        // Succeeded!
        break;
    }
    // [END datastore_transactional_retry]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
