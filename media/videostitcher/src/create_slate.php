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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/media/videostitcher/README.md
 */

namespace Google\Cloud\Samples\Media\Stitcher;

// [START videostitcher_create_slate]
use Google\Cloud\Video\Stitcher\V1\Client\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\CreateSlateRequest;
use Google\Cloud\Video\Stitcher\V1\Slate;

/**
 * Creates a slate. A slate is displayed when ads are not available.
 *
 * @param string $callingProjectId     The project ID to run the API call under
 * @param string $location             The location of the slate
 * @param string $slateId              The name of the slate to be created
 * @param string $slateUri             The public URI for an MP4 video with at least one audio track
 */
function create_slate(
    string $callingProjectId,
    string $location,
    string $slateId,
    string $slateUri
): void {
    // Instantiate a client.
    $stitcherClient = new VideoStitcherServiceClient();

    $parent = $stitcherClient->locationName($callingProjectId, $location);
    $slate = new Slate();
    $slate->setUri($slateUri);

    // Run slate creation request
    $request = (new CreateSlateRequest())
        ->setParent($parent)
        ->setSlateId($slateId)
        ->setSlate($slate);
    $operationResponse = $stitcherClient->createSlate($request);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $result = $operationResponse->getResult();
        // Print results
        printf('Slate: %s' . PHP_EOL, $result->getName());
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
}
// [END videostitcher_create_slate]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
