<?php

/**
 * Copyright 2023 Google LLC.
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
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/bigquery/api/README.md
 */

namespace Google\Cloud\Samples\Dlp;

// [START dlp_inspect_image_listed_infotypes]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\ByteContentItem;
use Google\Cloud\Dlp\V2\ByteContentItem\BytesType;
use Google\Cloud\Dlp\V2\Likelihood;

/**
 * Inspect an image for sensitive data with listed infoTypes.
 * If you want to inspect an image for only certain sensitive data types, specify their corresponding
 * built-in infoTypes.
 *
 * @param string $projectId         The Google Cloud project id to use as a parent resource.
 * @param string $inputPath         The image path to inspect.
 */
function inspect_image_listed_infotypes(
    // TODO(developer): Replace sample parameters before running the code.
    string $projectId,
    string $inputPath = './test/data/test.png'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Get the bytes of the file.
    $fileBytes = (new ByteContentItem())
        ->setType(BytesType::IMAGE_PNG)
        ->setData(file_get_contents($inputPath));

    $parent = "projects/$projectId/locations/global";

    // Specify what content you want the service to Inspect.
    $item = (new ContentItem())
        ->setByteItem($fileBytes);

    // Create inspect config configuration.
    $inspectConfig = (new InspectConfig())
        // The infoTypes of information to match.
        ->setInfoTypes([
            (new InfoType())->setName('PHONE_NUMBER'),
            (new InfoType())->setName('EMAIL_ADDRESS'),
            (new InfoType())->setName('US_SOCIAL_SECURITY_NUMBER')
        ]);

    // Run request.
    $response = $dlp->inspectContent([
        'parent' => $parent,
        'inspectConfig' => $inspectConfig,
        'item' => $item
    ]);

    // Print the results.
    $findings = $response->getResult()->getFindings();
    if (count($findings) == 0) {
        printf('No findings.' . PHP_EOL);
    } else {
        printf('Findings:' . PHP_EOL);
        foreach ($findings as $finding) {
            printf('  Info type: %s' . PHP_EOL, $finding->getInfoType()->getName());
            printf('  Likelihood: %s' . PHP_EOL, Likelihood::name($finding->getLikelihood()));
        }
    }
}

// [END dlp_inspect_image_listed_infotypes]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
