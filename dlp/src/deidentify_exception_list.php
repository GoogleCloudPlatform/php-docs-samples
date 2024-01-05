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

# [START dlp_deidentify_exception_list]
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType\Dictionary;
use Google\Cloud\Dlp\V2\CustomInfoType\Dictionary\WordList;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\DeidentifyContentRequest;
use Google\Cloud\Dlp\V2\ExclusionRule;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectionRule;
use Google\Cloud\Dlp\V2\InspectionRuleSet;
use Google\Cloud\Dlp\V2\MatchingType;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;
use Google\Cloud\Dlp\V2\ReplaceWithInfoTypeConfig;

/**
 * Create an exception list for de-identification
 * Create an exception list for a regular custom dictionary detector.
 *
 * @param string $callingProjectId  The project ID to run the API call under
 * @param string $textToDeIdentify  The String you want the service to DeIdentify
 */
function deidentify_exception_list(
    // TODO(developer): Replace sample parameters before running the code.
    string $callingProjectId,
    string $textToDeIdentify = 'jack@example.org accessed customer record of user5@example.com'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Specify what content you want the service to DeIdentify.
    $contentItem = (new ContentItem())
        ->setValue($textToDeIdentify);

    // Construct the custom word list to be detected.
    $wordList = (new Dictionary())
        ->setWordList((new WordList())
            ->setWords(['jack@example.org', 'jill@example.org']));

    // Specify the exclusion rule and build-in info type the inspection will look for.
    $exclusionRule = (new ExclusionRule())
        ->setMatchingType(MatchingType::MATCHING_TYPE_FULL_MATCH)
        ->setDictionary($wordList);

    $emailAddress = (new InfoType())
        ->setName('EMAIL_ADDRESS');
    $inspectionRuleSet = (new InspectionRuleSet())
        ->setInfoTypes([$emailAddress])
        ->setRules([
            (new InspectionRule())
                ->setExclusionRule($exclusionRule)
        ]);

    $inspectConfig = (new InspectConfig())
        ->setInfoTypes([$emailAddress])
        ->setRuleSet([$inspectionRuleSet]);

    // Define type of deidentification as replacement.
    $primitiveTransformation = (new PrimitiveTransformation())
        ->setReplaceWithInfoTypeConfig(new ReplaceWithInfoTypeConfig());

    // Associate de-identification type with info type.
    $transformation = (new InfoTypeTransformation())
        ->setInfoTypes([$emailAddress])
        ->setPrimitiveTransformation($primitiveTransformation);

    // Construct the configuration for the de-id request and list all desired transformations.
    $deidentifyConfig = (new DeidentifyConfig())
        ->setInfoTypeTransformations(
            (new InfoTypeTransformations())
                ->setTransformations([$transformation])
        );

    // Send the request and receive response from the service
    $parent = "projects/$callingProjectId/locations/global";
    $deidentifyContentRequest = (new DeidentifyContentRequest())
        ->setParent($parent)
        ->setDeidentifyConfig($deidentifyConfig)
        ->setInspectConfig($inspectConfig)
        ->setItem($contentItem);
    $response = $dlp->deidentifyContent($deidentifyContentRequest);

    // Print the results
    printf('Text after replace with infotype config: %s', $response->getItem()->getValue());
}
# [END dlp_deidentify_exception_list]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
