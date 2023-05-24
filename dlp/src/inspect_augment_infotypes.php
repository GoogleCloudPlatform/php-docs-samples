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

// [START dlp_inspect_augment_infotypes]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType;
use Google\Cloud\Dlp\V2\CustomInfoType\Dictionary;
use Google\Cloud\Dlp\V2\CustomInfoType\Dictionary\WordList;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\Likelihood;

/**
 * Augment a built-in infotype detector.
 * Consider a scenario in which a built-in infoType detector isn’t returning the correct values.
 * For example, you want to return matches on person names, but Cloud DLP's built-in
 * PERSON_NAME detector is failing to return matches on some person names that are common in your dataset.
 * Cloud DLP allows you to augment built-in infoType detectors by including a built-in detector in the
 * declaration for a custom infoType detector, as shown in the following example. This snippet
 * illustrates how to configure Cloud DLP so that the PERSON_NAME built-in infoType detector will
 * additionally match the name “Quasimodo:”.
 *
 * @param string $projectId         The Google Cloud project id to use as a parent resource.
 * @param string $textToInspect     The string to inspect.
 * @param array  $matchWordList     Specify the set of words to match.
 */
function inspect_augment_infotypes(
    // TODO(developer): Replace sample parameters before running the code.
    string $projectId,
    string $textToInspect = 'Smith and Quasimodo are good cricketer',
    array  $matchWordList = ['quasimodo']
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$projectId/locations/global";

    // Specify what content you want the service to Inspect.
    $item = (new ContentItem())
        ->setValue($textToInspect);

    // The infoTypes of information to match.
    $personNameInfoType = (new InfoType())
        ->setName('PERSON_NAME');

    // Construct the word list to be detected.
    $wordList = (new Dictionary())
        ->setWordList((new WordList())
            ->setWords($matchWordList));

    // Construct the custom infotype detector.
    $customInfoType = (new CustomInfoType())
        ->setInfoType($personNameInfoType)
        ->setLikelihood(Likelihood::POSSIBLE)
        ->setDictionary($wordList);

    // Construct the configuration for the Inspect request.
    $inspectConfig = (new InspectConfig())
        ->setCustomInfoTypes([$customInfoType])
        ->setIncludeQuote(true);

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
            printf('  Quote: %s' . PHP_EOL, $finding->getQuote());
            printf('  Info type: %s' . PHP_EOL, $finding->getInfoType()->getName());
            printf('  Likelihood: %s' . PHP_EOL, Likelihood::name($finding->getLikelihood()));
        }
    }
}
// [END dlp_inspect_augment_infotypes]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
