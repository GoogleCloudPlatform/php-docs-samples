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

# [START storage_create_hmac_key]
use Google\Cloud\Storage\StorageClient;

/**
 * Create a new HMAC key.
 *
 * @param string $serviceAccountEmail Service account email to associate with the new HMAC key.
 * @param string $projectId Google Cloud Project ID.
 *
 */
function create_hmac_key($serviceAccountEmail, $projectId)
{
    $storage = new StorageClient();
    // By default createHmacKey will use the projectId used by StorageClient().
    $hmacKeyCreated = $storage->createHmacKey($serviceAccountEmail, ['projectId' => $projectId]);

    printf('The base64 encoded secret is: %s' . PHP_EOL, $hmacKeyCreated->secret());
    print('Do not miss that secret, there is no API to recover it.' . PHP_EOL);
    printf('HMAC key Metadata: %s' . PHP_EOL, print_r($hmacKeyCreated->hmacKey()->info(), true));
}
# [END storage_create_hmac_key]
