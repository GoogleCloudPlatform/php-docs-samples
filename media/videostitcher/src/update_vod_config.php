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

// [START videostitcher_update_vod_config]
use Google\Cloud\Video\Stitcher\V1\Client\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\UpdateVodConfigRequest;
use Google\Cloud\Video\Stitcher\V1\VodConfig;
use Google\Protobuf\FieldMask;

/**
 * Updates the VOD config's sourceUri field.
 *
 * @param string $callingProjectId     The project ID to run the API call under
 * @param string $location             The location of the VOD config
 * @param string $vodConfigId          The name of the VOD config to update
 * @param string $sourceUri            Updated uri of the media to stitch; this URI must
 *                                     reference either an MPEG-DASH manifest
 *                                     (.mpd) file or an M3U playlist manifest
 *                                     (.m3u8) file.
 */
function update_vod_config(
    string $callingProjectId,
    string $location,
    string $vodConfigId,
    string $sourceUri
): void {
    // Instantiate a client.
    $stitcherClient = new VideoStitcherServiceClient();

    $formattedName = $stitcherClient->vodConfigName($callingProjectId, $location, $vodConfigId);
    $vodConfig = new VodConfig();
    $vodConfig->setName($formattedName);
    $vodConfig->setSourceUri($sourceUri);
    $updateMask = new FieldMask([
        'paths' => ['sourceUri']
    ]);

    // Run VOD config update request
    $request = (new UpdateVodConfigRequest())
        ->setVodConfig($vodConfig)
        ->setUpdateMask($updateMask);
    $operationResponse = $stitcherClient->updateVodConfig($request);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $result = $operationResponse->getResult();
        // Print results
        printf('Updated VOD config: %s' . PHP_EOL, $result->getName());
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
}
// [END videostitcher_update_vod_config]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
