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

// [START videostitcher_list_vod_configs]
use Google\Cloud\Video\Stitcher\V1\Client\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\ListVodConfigsRequest;

/**
 * Lists all VOD configs for a location.
 *
 * @param string $callingProjectId     The project ID to run the API call under
 * @param string $location             The location of the VOD configs
 */
function list_vod_configs(
    string $callingProjectId,
    string $location
): void {
    // Instantiate a client.
    $stitcherClient = new VideoStitcherServiceClient();

    $parent = $stitcherClient->locationName($callingProjectId, $location);
    $request = (new ListVodConfigsRequest())
        ->setParent($parent);
    $response = $stitcherClient->listVodConfigs($request);

    // Print the VOD config list.
    $vodConfigs = $response->iterateAllElements();
    print('VOD configs:' . PHP_EOL);
    foreach ($vodConfigs as $vodConfig) {
        printf('%s' . PHP_EOL, $vodConfig->getName());
    }
}
// [END videostitcher_list_vod_configs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
