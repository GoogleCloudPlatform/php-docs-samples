<?php

/**
 * Copyright 2023 Google Inc.
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

# [START dlp_redact_image_colored_infotypes]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\RedactImageRequest\ImageRedactionConfig;
use Google\Cloud\Dlp\V2\ByteContentItem;
use Google\Cloud\Dlp\V2\Color;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;

/**
 * Redact data from an image with color-coded infoTypes.
 *
 * @param string $callingProjectId    The project ID to run the API call under.
 * @param string $imagePath           The local filepath of the image to inspect.
 * @param string $outputPath          The local filepath to save the resulting image to.
 */
function redact_image_colored_infotypes(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $imagePath = './test/data/test.png',
    string $outputPath = './test/data/sensitive-data-image-redacted-color-coding.png'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Read image file into a buffer.
    $imageRef = fopen($imagePath, 'rb');
    $imageBytes = fread($imageRef, filesize($imagePath));
    fclose($imageRef);

    // Get the image's content type.
    $typeConstant = (int) array_search(
        mime_content_type($imagePath),
        [false, 'image/jpeg', 'image/bmp', 'image/png', 'image/svg']
    );

    // Create the byte-storing object.
    $byteContent = (new ByteContentItem())
        ->setType($typeConstant)
        ->setData($imageBytes);

    // Define the types of information to redact and associate each one with a different color.
    $ssnInfotype = (new InfoType())
        ->setName('US_SOCIAL_SECURITY_NUMBER');
    $emailInfotype = (new InfoType())
        ->setName('EMAIL_ADDRESS');
    $phoneInfotype = (new InfoType())
        ->setName('PHONE_NUMBER');
    $infotypes = [$ssnInfotype, $emailInfotype, $phoneInfotype];

    $ssnRedactionConfig = (new ImageRedactionConfig())
        ->setInfoType($ssnInfotype)
        ->setRedactionColor((new Color())
            ->setRed(.3)
            ->setGreen(.1)
            ->setBlue(.6));

    $emailRedactionConfig = (new ImageRedactionConfig())
        ->setInfoType($emailInfotype)
        ->setRedactionColor((new Color())
            ->setRed(.5)
            ->setGreen(.5)
            ->setBlue(1));

    $phoneRedactionConfig = (new ImageRedactionConfig())
        ->setInfoType($phoneInfotype)
        ->setRedactionColor((new Color())
            ->setRed(1)
            ->setGreen(0)
            ->setBlue(.6));

    $imageRedactionConfigs = [$ssnRedactionConfig, $emailRedactionConfig, $phoneRedactionConfig];

    // Create the configuration object.
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes($infotypes);
    $parent = "projects/$callingProjectId/locations/global";

    // Run request.
    $response = $dlp->redactImage([
        'parent' => $parent,
        'byteItem' => $byteContent,
        'inspectConfig' => $inspectConfig,
        'imageRedactionConfigs' => $imageRedactionConfigs
    ]);

    // Save result to file.
    file_put_contents($outputPath, $response->getRedactedImage());

    // Print completion message.
    printf('Redacted image saved to %s ' . PHP_EOL, $outputPath);
}
# [END dlp_redact_image_colored_infotypes]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
