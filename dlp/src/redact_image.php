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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/dlp/README.md
 */

namespace Google\Cloud\Samples\Dlp;

# [START dlp_redact_image]
use Google\Cloud\Dlp\V2\ByteContentItem;
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\RedactImageRequest;
use Google\Cloud\Dlp\V2\RedactImageRequest\ImageRedactionConfig;

/**
 * Redact sensitive data from an image.
 *
 * @param string $callingProjectId    The project ID to run the API call under
 * @param string $imagePath           The local filepath of the image to inspect
 * @param string $outputPath          The local filepath to save the resulting image to
 */
function redact_image(
    string $callingProjectId,
    string $imagePath,
    string $outputPath
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // The infoTypes of information to match
    $phoneNumberInfoType = (new InfoType())
        ->setName('PHONE_NUMBER');
    $infoTypes = [$phoneNumberInfoType];

    // The minimum likelihood required before returning a match
    $minLikelihood = likelihood::LIKELIHOOD_UNSPECIFIED;

    // Whether to include the matching string in the response
    $includeQuote = true;

    // Create the configuration object
    $inspectConfig = (new InspectConfig())
        ->setMinLikelihood($minLikelihood)
        ->setInfoTypes($infoTypes);

    // Read image file into a buffer
    $imageRef = fopen($imagePath, 'rb');
    $imageBytes = fread($imageRef, filesize($imagePath));
    fclose($imageRef);

    // Get the image's content type
    $typeConstant = (int) array_search(
        mime_content_type($imagePath),
        [false, 'image/jpeg', 'image/bmp', 'image/png', 'image/svg']
    );

    // Create the byte-storing object
    $byteContent = (new ByteContentItem())
        ->setType($typeConstant)
        ->setData($imageBytes);

    // Create the image redaction config objects
    $imageRedactionConfigs = [];
    foreach ($infoTypes as $infoType) {
        $config = (new ImageRedactionConfig())
            ->setInfoType($infoType);
        $imageRedactionConfigs[] = $config;
    }

    $parent = "projects/$callingProjectId/locations/global";

    // Run request
    $redactImageRequest = (new RedactImageRequest())
        ->setParent($parent)
        ->setInspectConfig($inspectConfig)
        ->setByteItem($byteContent)
        ->setImageRedactionConfigs($imageRedactionConfigs);
    $response = $dlp->redactImage($redactImageRequest);

    // Save result to file
    file_put_contents($outputPath, $response->getRedactedImage());

    // Print completion message
    print('Redacted image saved to ' . $outputPath . PHP_EOL);
}
# [END dlp_redact_image]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
