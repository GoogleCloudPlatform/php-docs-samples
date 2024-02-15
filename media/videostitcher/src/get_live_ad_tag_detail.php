<?php

/**
 * Copyright 2022 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/media/videostitcher/README.md
 */

namespace Google\Cloud\Samples\Media\Stitcher;

// [START videostitcher_get_live_ad_tag_detail]
use Google\Cloud\Video\Stitcher\V1\Client\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\GetLiveAdTagDetailRequest;

/**
 * Gets the specified ad tag detail for the live session.
 *
 * @param string $callingProjectId     The project ID to run the API call under
 * @param string $location             The location of the session
 * @param string $sessionId            The ID of the session
 * @param string $adTagDetailId        The ID of the ad tag detail
 */
function get_live_ad_tag_detail(
    string $callingProjectId,
    string $location,
    string $sessionId,
    string $adTagDetailId
): void {
    // Instantiate a client.
    $stitcherClient = new VideoStitcherServiceClient();

    $formattedName = $stitcherClient->liveAdTagDetailName($callingProjectId, $location, $sessionId, $adTagDetailId);
    $request = (new GetLiveAdTagDetailRequest())
        ->setName($formattedName);
    $adTagDetail = $stitcherClient->getLiveAdTagDetail($request);

    // Print results
    printf('Live ad tag detail: %s' . PHP_EOL, $adTagDetail->getName());
}
// [END videostitcher_get_live_ad_tag_detail]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
