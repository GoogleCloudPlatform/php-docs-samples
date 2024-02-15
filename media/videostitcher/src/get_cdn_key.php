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

// [START videostitcher_get_cdn_key]
use Google\Cloud\Video\Stitcher\V1\Client\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\GetCdnKeyRequest;

/**
 * Gets a CDN key.
 *
 * @param string $callingProjectId     The project ID to run the API call under
 * @param string $location             The location of the CDN key
 * @param string $cdnKeyId             The ID of the CDN key
 */
function get_cdn_key(
    string $callingProjectId,
    string $location,
    string $cdnKeyId
): void {
    // Instantiate a client.
    $stitcherClient = new VideoStitcherServiceClient();

    $formattedName = $stitcherClient->cdnKeyName($callingProjectId, $location, $cdnKeyId);
    $request = (new GetCdnKeyRequest())
        ->setName($formattedName);
    $key = $stitcherClient->getCdnKey($request);

    // Print results
    printf('CDN key: %s' . PHP_EOL, $key->getName());
}
// [END videostitcher_get_cdn_key]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
