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

# [START dlp_deidentify_simple_word_list]
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType;
use Google\Cloud\Dlp\V2\CustomInfoType\Dictionary;
use Google\Cloud\Dlp\V2\CustomInfoType\Dictionary\WordList;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\DeidentifyContentRequest;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\ReplaceWithInfoTypeConfig;

/**
 * De-identify sensitive data with a simple word list
 * Matches against a custom simple word list to de-identify sensitive data.
 *
 * @param string $callingProjectId  The Google Cloud project id to use as a parent resource.
 * @param string $string            The string to deidentify (will be treated as text).
 */

function deidentify_simple_word_list(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $string = 'Patient was seen in RM-YELLOW then transferred to rm green.'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$callingProjectId/locations/global";

    $content = (new ContentItem())
        ->setValue($string);

    // Construct the word list to be detected
    $wordList = (new Dictionary())
        ->setWordList((new WordList())
            ->setWords(['RM-GREEN', 'RM-YELLOW', 'RM-ORANGE']));

    // The infoTypes of information to mask
    $custoMRoomIdinfoType = (new InfoType())
        ->setName('CUSTOM_ROOM_ID');
    $customInfoType = (new CustomInfoType())
        ->setInfoType($custoMRoomIdinfoType)
        ->setDictionary($wordList);

    // Create the configuration object
    $inspectConfig = (new InspectConfig())
        ->setCustomInfoTypes([$customInfoType]);

    // Create the information transform configuration objects
    $primitiveTransformation = (new PrimitiveTransformation())
        ->setReplaceWithInfoTypeConfig(new ReplaceWithInfoTypeConfig());

    $infoTypeTransformation = (new InfoTypeTransformation())
        ->setPrimitiveTransformation($primitiveTransformation)
        ->setInfoTypes([$custoMRoomIdinfoType]);

    $infoTypeTransformations = (new InfoTypeTransformations())
        ->setTransformations([$infoTypeTransformation]);

    // Create the deidentification configuration object
    $deidentifyConfig = (new DeidentifyConfig())
        ->setInfoTypeTransformations($infoTypeTransformations);

    // Run request
    $deidentifyContentRequest = (new DeidentifyContentRequest())
        ->setParent($parent)
        ->setDeidentifyConfig($deidentifyConfig)
        ->setItem($content)
        ->setInspectConfig($inspectConfig);
    $response = $dlp->deidentifyContent($deidentifyContentRequest);

    // Print the results
    printf('Deidentified content: %s', $response->getItem()->getValue());
}
# [END dlp_deidentify_simple_word_list]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
