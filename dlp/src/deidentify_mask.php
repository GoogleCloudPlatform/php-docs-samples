<?php

/**
 * Copyright 2018 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/dlp/README.md
 */

namespace Google\Cloud\Samples\Dlp;

# [START dlp_deidentify_masking]
use Google\Cloud\Dlp\V2\CharacterMaskConfig;
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\ContentItem;

/**
 * Deidentify sensitive data in a string by masking it with a character.
 *
 * @param string $callingProjectId The GCP Project ID to run the API call under
 * @param string $string           The string to deidentify
 * @param int    $numberToMask     (Optional) The maximum number of sensitive characters to mask in a match
 * @param string $maskingCharacter (Optional) The character to mask matching sensitive data with (defaults to "x")
 */
function deidentify_mask(
    string $callingProjectId,
    string $string,
    int $numberToMask = 0,
    string $maskingCharacter = 'x'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // The infoTypes of information to mask
    $ssnInfoType = (new InfoType())
        ->setName('US_SOCIAL_SECURITY_NUMBER');
    $infoTypes = [$ssnInfoType];

    // Create the masking configuration object
    $maskConfig = (new CharacterMaskConfig())
        ->setMaskingCharacter($maskingCharacter)
        ->setNumberToMask($numberToMask);

    // Create the information transform configuration objects
    $primitiveTransformation = (new PrimitiveTransformation())
        ->setCharacterMaskConfig($maskConfig);

    $infoTypeTransformation = (new InfoTypeTransformation())
        ->setPrimitiveTransformation($primitiveTransformation)
        ->setInfoTypes($infoTypes);

    $infoTypeTransformations = (new InfoTypeTransformations())
        ->setTransformations([$infoTypeTransformation]);

    // Create the deidentification configuration object
    $deidentifyConfig = (new DeidentifyConfig())
        ->setInfoTypeTransformations($infoTypeTransformations);

    $item = (new ContentItem())
        ->setValue($string);

    $parent = "projects/$callingProjectId/locations/global";

    // Run request
    $response = $dlp->deidentifyContent([
        'parent' => $parent,
        'deidentifyConfig' => $deidentifyConfig,
        'item' => $item
    ]);

    // Print the results
    $deidentifiedValue = $response->getItem()->getValue();
    print($deidentifiedValue);
}
# [END dlp_deidentify_masking]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
