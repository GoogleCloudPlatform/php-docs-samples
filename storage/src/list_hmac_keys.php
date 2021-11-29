<?php
/**
 * Copyright 2019 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/storage/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START storage_list_hmac_keys]
use Google\Cloud\Storage\StorageClient;

/**
 * List HMAC keys.
 *
 * @param string $projectId The ID of your Google Cloud Platform project.
 */
function list_hmac_keys($projectId)
{
    // $projectId = 'my-project-id';

    $storage = new StorageClient();
    // By default hmacKeys will use the projectId used by StorageClient() to list HMAC Keys.
    $hmacKeys = $storage->hmacKeys(['projectId' => $projectId]);

    printf('HMAC Key\'s:' . PHP_EOL);
    foreach ($hmacKeys as $hmacKey) {
        printf('Service Account Email: %s' . PHP_EOL, $hmacKey->info()['serviceAccountEmail']);
        printf('Access Id: %s' . PHP_EOL, $hmacKey->info()['accessId']);
    }
}
# [END storage_list_hmac_keys]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
