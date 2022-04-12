<?php
/**
 * Copyright 2022 Google Inc.
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

# [START storage_set_client_endpoint]
use Google\Cloud\Storage\StorageClient;

/**
 * Sets a custom endpoint for storage client.
 *
 * @param string $projectId The ID of your Google Cloud Platform project.
 * @param string $endpoint The endpoint for storage client to target.
 */
function set_client_endpoint(
    string $projectId,
    string $endpoint
): void {
    // $projectId = 'my-project-id';
    // $endpoint = 'https://storage.googleapis.com';

    $storage = new StorageClient([
        'projectId' => $projectId,
        'apiEndpoint' => $endpoint,
    ]);

    // fetching apiEndpoint and baseUri from StorageClient is excluded for brevity
    # [START_EXCLUDE]
    $connectionProperty = new \ReflectionProperty($storage, 'connection');
    $connectionProperty->setAccessible(true);
    $connection = $connectionProperty->getValue($storage);

    $apiEndpointProperty = new \ReflectionProperty($connection, 'apiEndpoint');
    $apiEndpointProperty->setAccessible(true);
    $apiEndpoint = $apiEndpointProperty->getValue($connection);

    $requestBuilderProperty = new \ReflectionProperty($connection, 'requestBuilder');
    $requestBuilderProperty->setAccessible(true);
    $requestBuilder = $requestBuilderProperty->getValue($connection);

    $baseUriProperty = new \ReflectionProperty($requestBuilder, 'baseUri');
    $baseUriProperty->setAccessible(true);
    $baseUri = $baseUriProperty->getValue($requestBuilder);

    printf('API endpoint: %s' . PHP_EOL, $apiEndpoint);
    printf('Base URI: %s' . PHP_EOL, $baseUri);
    # [END_EXCLUDE]
    print('Storage Client initialized.' . PHP_EOL);
}
# [END storage_set_client_endpoint]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
