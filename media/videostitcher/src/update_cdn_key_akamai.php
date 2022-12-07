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

// [START videostitcher_update_cdn_key_akamai]
use Google\Cloud\Video\Stitcher\V1\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\CdnKey;
use Google\Cloud\Video\Stitcher\V1\AkamaiCdnKey;
use Google\Protobuf\FieldMask;

/**
 * Updates an Akamai CDN key.
 *
 * @param string  $callingProjectId   The project ID to run the API call under
 * @param string  $location           The location of the CDN key
 * @param string  $cdnKeyId           The ID of the CDN key to be created
 * @param string  $hostname           The hostname of the CDN key
 * @param string  $tokenKey           The base64-encoded string token key for
 *                                    the Akamai CDN edge configuration
 */
function update_cdn_key_akamai(
    string $callingProjectId,
    string $location,
    string $cdnKeyId,
    string $hostname,
    string $tokenKey
): void {
    // Instantiate a client.
    $stitcherClient = new VideoStitcherServiceClient();

    $name = $stitcherClient->cdnKeyName($callingProjectId, $location, $cdnKeyId);
    $cdnKey = new CdnKey();
    $cdnKey->setName($name);
    $cdnKey->setHostname($hostname);
    $akamaiCdn = new AkamaiCdnKey();
    $akamaiCdn->setTokenKey($tokenKey);
    $cdnKey->setAkamaiCdnKey($akamaiCdn);

    $updateMask = new FieldMask([
        'paths' => ['hostname', 'akamai_cdn_key']
    ]);

    // Run CDN key creation request
    $response = $stitcherClient->updateCdnKey($cdnKey, $updateMask);

    // Print results
    printf('Updated CDN key: %s' . PHP_EOL, $response->getName());
}
// [END videostitcher_update_cdn_key_akamai]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
