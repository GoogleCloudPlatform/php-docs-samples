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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/media/videostitcher/README.md
 */

namespace Google\Cloud\Samples\Media\Stitcher;

// [START videostitcher_delete_live_config]
use Google\Cloud\Video\Stitcher\V1\Client\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\DeleteLiveConfigRequest;

/**
 * Deletes a live config.
 *
 * @param string $callingProjectId     The project ID to run the API call under
 * @param string $location             The location of the live config
 * @param string $liveConfigId         The ID of the live config
 */
function delete_live_config(
    string $callingProjectId,
    string $location,
    string $liveConfigId
): void {
    // Instantiate a client.
    $stitcherClient = new VideoStitcherServiceClient();

    $formattedName = $stitcherClient->liveConfigName($callingProjectId, $location, $liveConfigId);
    $request = (new DeleteLiveConfigRequest())
        ->setName($formattedName);
    $operationResponse = $stitcherClient->deleteLiveConfig($request);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        // Print status
        printf('Deleted live config %s' . PHP_EOL, $liveConfigId);
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
}
// [END videostitcher_delete_live_config]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
