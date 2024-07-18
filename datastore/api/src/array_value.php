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
 * Create a Datastore entity with some array properties.
 *
 * @param string $namespaceId
 * @param Key $key
 */
function array_value(string $namespaceId = null, Key $key)
{
    $datastore = new DatastoreClient(['namespaceId' => $namespaceId]);
    // [START datastore_array_value]
    $task = $datastore->entity(
        $key,
        [
            'tags' => ['fun', 'programming'],
            'collaborators' => ['alice', 'bob']
        ]
    );
    // [END datastore_array_value]
    print_r($task);
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
