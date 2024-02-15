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

// [START videostitcher_get_vod_stitch_detail]
use Google\Cloud\Video\Stitcher\V1\Client\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\GetVodStitchDetailRequest;

/**
 * Gets the specified stitch detail for the VOD session.
 *
 * @param string $callingProjectId     The project ID to run the API call under
 * @param string $location             The location of the session
 * @param string $sessionId            The ID of the session
 * @param string $stitchDetailId       The ID of the stitch detail
 */
function get_vod_stitch_detail(
    string $callingProjectId,
    string $location,
    string $sessionId,
    string $stitchDetailId
): void {
    // Instantiate a client.
    $stitcherClient = new VideoStitcherServiceClient();

    $formattedName = $stitcherClient->vodStitchDetailName($callingProjectId, $location, $sessionId, $stitchDetailId);
    $request = (new GetVodStitchDetailRequest())
        ->setName($formattedName);
    $stitchDetail = $stitcherClient->getVodStitchDetail($request);

    // Print results
    printf('VOD stitch detail: %s' . PHP_EOL, $stitchDetail->getName());
}
// [END videostitcher_get_vod_stitch_detail]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
