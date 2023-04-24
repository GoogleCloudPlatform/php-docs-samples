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

# [START dlp_deidentify_redact]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\RedactConfig;

/**
 * De-identify data: Redacting with matched input values
 * Uses the Data Loss Prevention API to de-identify sensitive data in a string by redacting matched input values.
 *
 * @param string $callingProjectId      The Google Cloud project id to use as a parent resource.
 * @param string $textToInspect         The string to deidentify (will be treated as text).
 */
function deidentify_redact(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $textToInspect = 'My name is Alicia Abernathy, and my email address is aabernathy@example.com.'

): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Specify the content to be de-identify.
    $contentItem = (new ContentItem())
        ->setValue($textToInspect);

    // Specify the type of info the inspection will look for.
    $infoType = (new InfoType())
        ->setName('EMAIL_ADDRESS');
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes([$infoType]);

    // Define type of de-identification.
    $primitiveTransformation = (new PrimitiveTransformation())
        ->setRedactConfig(new RedactConfig());

    // Associate de-identification type with info type.
    $transformation = (new InfoTypeTransformation())
        ->setInfoTypes([$infoType])
        ->setPrimitiveTransformation($primitiveTransformation);

    // Construct the configuration for the Redact request and list all desired transformations.
    $deidentifyConfig = (new DeidentifyConfig())
        ->setInfoTypeTransformations((new InfoTypeTransformations())
            ->setTransformations([$transformation]));

    $parent = "projects/$callingProjectId/locations/global";

    // Run request
    $response = $dlp->deidentifyContent([
        'parent' => $parent,
        'deidentifyConfig' => $deidentifyConfig,
        'inspectConfig' => $inspectConfig,
        'item' => $contentItem
    ]);

    // Print results
    printf('Text after redaction: %s', $response->getItem()->getValue());
}
# [END dlp_deidentify_redact]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
