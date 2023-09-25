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

# [START dlp_deidentify_replace_infotype]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\ReplaceWithInfoTypeConfig;

/**
 * De-identify sensitive data by replacing with infoType.
 * Uses the Data Loss Prevention API to deidentify sensitive data in a string by replacing it with
 * the info type.
 *
 * @param string $callingProjectId  The Google Cloud project id to use as a parent resource.
 * @param string $string            The string to deidentify (will be treated as text).
 */

function deidentify_replace_infotype(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $string
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$callingProjectId/locations/global";

    // Specify what content you want the service to de-identify.
    $content = (new ContentItem())
        ->setValue($string);

    // The infoTypes of information to mask.
    $phoneNumberinfoType = (new InfoType())
        ->setName('PHONE_NUMBER');
    $personNameinfoType = (new InfoType())
        ->setName('PERSON_NAME');
    $infoTypes = [$phoneNumberinfoType, $personNameinfoType];

    // Create the configuration object.
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes($infoTypes);

    // Create the information transform configuration objects.
    $primitiveTransformation = (new PrimitiveTransformation())
        ->setReplaceWithInfoTypeConfig(new ReplaceWithInfoTypeConfig());

    $infoTypeTransformation = (new InfoTypeTransformation())
        ->setPrimitiveTransformation($primitiveTransformation);

    $infoTypeTransformations = (new InfoTypeTransformations())
        ->setTransformations([$infoTypeTransformation]);

    // Create the deidentification configuration object.
    $deidentifyConfig = (new DeidentifyConfig())
        ->setInfoTypeTransformations($infoTypeTransformations);

    // Run request.
    $response = $dlp->deidentifyContent([
        'parent' => $parent,
        'deidentifyConfig' => $deidentifyConfig,
        'item' => $content,
        'inspectConfig' => $inspectConfig
    ]);

    // Print the results.
    printf('Text after replace with infotype config: %s', $response->getItem()->getValue());
}
# [END dlp_deidentify_replace_infotype]

// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
