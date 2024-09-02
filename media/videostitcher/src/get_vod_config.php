<?php

/**
 * Copyright 2024 Google LLC.
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

// [START videostitcher_get_vod_config]
use Google\Cloud\Video\Stitcher\V1\Client\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\GetVodConfigRequest;

/**
 * Gets a VOD config.
 *
 * @param string $callingProjectId     The project ID to run the API call under
 * @param string $location             The location of the VOD config
 * @param string $vodConfigId         The ID of the VOD config
 */
function get_vod_config(
    string $callingProjectId,
    string $location,
    string $vodConfigId
): void {
    // Instantiate a client.
    $stitcherClient = new VideoStitcherServiceClient();

    $formattedName = $stitcherClient->vodConfigName($callingProjectId, $location, $vodConfigId);
    $request = (new GetVodConfigRequest())
        ->setName($formattedName);
    $vodConfig = $stitcherClient->getVodConfig($request);

    // Print results
    printf('VOD config: %s' . PHP_EOL, $vodConfig->getName());
}
// [END videostitcher_get_vod_config]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
