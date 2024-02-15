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

// [START videostitcher_list_vod_stitch_details]
use Google\Cloud\Video\Stitcher\V1\Client\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\ListVodStitchDetailsRequest;

/**
 * Lists the stitch details for the specified VOD session.
 *
 * @param string $callingProjectId     The project ID to run the API call under
 * @param string $location             The location of the session
 * @param string $sessionId            The ID of the session
 */
function list_vod_stitch_details(
    string $callingProjectId,
    string $location,
    string $sessionId
): void {
    // Instantiate a client.
    $stitcherClient = new VideoStitcherServiceClient();

    $formattedName = $stitcherClient->vodSessionName($callingProjectId, $location, $sessionId);
    $request = (new ListVodStitchDetailsRequest())
        ->setParent($formattedName);
    $response = $stitcherClient->listVodStitchDetails($request);

    // Print the stitch details list.
    $stitchDetails = $response->iterateAllElements();
    print('VOD stitch details:' . PHP_EOL);
    foreach ($stitchDetails as $stitchDetail) {
        printf('%s' . PHP_EOL, $stitchDetail->getName());
    }
}
// [END videostitcher_list_vod_stitch_details]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
