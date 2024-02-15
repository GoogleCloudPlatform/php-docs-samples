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

// [START videostitcher_update_cdn_key]
use Google\Cloud\Video\Stitcher\V1\CdnKey;
use Google\Cloud\Video\Stitcher\V1\Client\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\GoogleCdnKey;
use Google\Cloud\Video\Stitcher\V1\MediaCdnKey;
use Google\Cloud\Video\Stitcher\V1\UpdateCdnKeyRequest;
use Google\Protobuf\FieldMask;

/**
 * Updates a CDN key. Cloud CDN keys and Media CDN keys are supported.
 *
 * @param string  $callingProjectId   The project ID to run the API call under
 * @param string  $location           The location of the CDN key
 * @param string  $cdnKeyId           The ID of the CDN key to be created
 * @param string  $hostname           The hostname of the CDN key
 * @param string  $keyName            For a Media CDN key, this is the keyset name.
 *                                    For a Cloud CDN key, this is the public name of the
 *                                    CDN key.
 * @param string  $privateKey         For a Media CDN key, this is a 64-byte Ed25519 private
 *                                    key encoded as a base64-encoded string. See
 *                                    https://cloud.google.com/video-stitcher/docs/how-to/managing-cdn-keys#create-private-key-media-cdn
 *                                    for more information. For a Cloud CDN key,
 *                                    this is a base64-encoded string secret.
 * @param bool    $isMediaCdn         If true, update a Media CDN key. If false,
 *                                    update a Cloud CDN key.
 */
function update_cdn_key(
    string $callingProjectId,
    string $location,
    string $cdnKeyId,
    string $hostname,
    string $keyName,
    string $privateKey,
    bool $isMediaCdn
): void {
    // Instantiate a client.
    $stitcherClient = new VideoStitcherServiceClient();

    $name = $stitcherClient->cdnKeyName($callingProjectId, $location, $cdnKeyId);
    $cdnKey = new CdnKey();
    $cdnKey->setName($name);
    $cdnKey->setHostname($hostname);
    $updateMask = new FieldMask();

    if ($isMediaCdn == true) {
        $cloudCdn = new MediaCdnKey();
        $cdnKey->setMediaCdnKey($cloudCdn);
        $updateMask = new FieldMask([
            'paths' => ['hostname', 'media_cdn_key']
        ]);
    } else {
        $cloudCdn = new GoogleCdnKey();
        $cdnKey->setGoogleCdnKey($cloudCdn);
        $updateMask = new FieldMask([
            'paths' => ['hostname', 'google_cdn_key']
        ]);
    }
    $cloudCdn->setKeyName($keyName);
    $cloudCdn->setPrivateKey($privateKey);

    // Run CDN key creation request
    $request = (new UpdateCdnKeyRequest())
        ->setCdnKey($cdnKey)
        ->setUpdateMask($updateMask);
    $operationResponse = $stitcherClient->updateCdnKey($request);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $result = $operationResponse->getResult();
        // Print results
        printf('Updated CDN key: %s' . PHP_EOL, $result->getName());
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
}
// [END videostitcher_update_cdn_key]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
