<?php

/**
 * Copyright 2023 Google LLC.
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
 * For instructions on how to run the samples:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/media/livestream/README.md
 */

namespace Google\Cloud\Samples\Media\LiveStream;

// [START livestream_update_pool]
use Google\Cloud\Video\LiveStream\V1\Pool;
use Google\Cloud\Video\LiveStream\V1\Client\LivestreamServiceClient;
use Google\Cloud\Video\LiveStream\V1\UpdatePoolRequest;
use Google\Protobuf\FieldMask;

/**
 * Updates a pool.
 *
 * @param string  $callingProjectId   The project ID to run the API call under
 * @param string  $location           The location of the pool
 * @param string  $poolId             The ID of the pool to be updated
 * @param string  $peeredNetwork      The updated peer network
 */
function update_pool(
    string $callingProjectId,
    string $location,
    string $poolId,
    string $peeredNetwork
): void {
    // Instantiate a client.
    $livestreamClient = new LivestreamServiceClient();

    $formattedName = $livestreamClient->poolName($callingProjectId, $location, $poolId);
    $pool = (new Pool())
        ->setName($formattedName)
        ->setNetworkConfig(
            (new Pool\NetworkConfig())
                ->setPeeredNetwork($peeredNetwork));

    $updateMask = new FieldMask([
        'paths' => ['network_config']
    ]);

    // Run the pool update request. The response is a long-running operation ID.
    $request = (new UpdatePoolRequest())
        ->setPool($pool)
        ->setUpdateMask($updateMask);
    $operationResponse = $livestreamClient->updatePool($request);

    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $result = $operationResponse->getResult();
        // Print results
        printf('Updated pool: %s' . PHP_EOL, $result->getName());
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
}
// [END livestream_update_pool]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
