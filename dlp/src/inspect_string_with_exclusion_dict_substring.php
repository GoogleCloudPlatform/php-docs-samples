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

# [START dlp_inspect_string_with_exclusion_dict_substring]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType\Dictionary;
use Google\Cloud\Dlp\V2\CustomInfoType\Dictionary\WordList;
use Google\Cloud\Dlp\V2\ExclusionRule;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectionRule;
use Google\Cloud\Dlp\V2\InspectionRuleSet;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\MatchingType;

/**
 * Inspect a string with an exclusion dictionary substring
 * Omit scan matches that include the substring "TEST".
 *
 * @param string $projectId                 The Google Cloud project id to use as a parent resource.
 * @param string $textToInspect             The string to inspect.
 * @param array  $excludedSubStringArray    The sub string to excluded.
 */
function inspect_string_with_exclusion_dict_substring(
    // TODO(developer): Replace sample parameters before running the code.
    string $projectId,
    string $textToInspect = 'Some email addresses: gary@example.com, TEST@example.com',
    array  $excludedSubStringArray = ['Test']
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$projectId/locations/global";

    // Specify what content you want the service to Inspect.
    $item = (new ContentItem())
        ->setValue($textToInspect);

    // Specify the type of info the inspection will look for.
    $infotypes = [
        (new InfoType())->setName('PHONE_NUMBER'),
        (new InfoType())->setName('EMAIL_ADDRESS'),
        (new InfoType())->setName('DOMAIN_NAME'),
        (new InfoType())->setName('PERSON_NAME'),
    ];

    // Exclude matches from the specified excludedSubstringList.
    $excludedSubstringList = (new Dictionary())
        ->setWordList((new WordList())
            ->setWords($excludedSubStringArray));

    $exclusionRule = (new ExclusionRule())
        ->setMatchingType(MatchingType::MATCHING_TYPE_PARTIAL_MATCH)
        ->setDictionary($excludedSubstringList);

    // Construct a ruleset that applies the exclusion rule to the EMAIL_ADDRESSES infotype.
    $inspectionRuleSet = (new InspectionRuleSet())
        ->setInfoTypes($infotypes)
        ->setRules([
            (new InspectionRule())
                ->setExclusionRule($exclusionRule),
        ]);

    // Construct the configuration for the Inspect request, including the ruleset.
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes($infotypes)
        ->setIncludeQuote(true)
        ->setRuleSet([$inspectionRuleSet]);

    // Run request
    $response = $dlp->inspectContent([
        'parent' => $parent,
        'inspectConfig' => $inspectConfig,
        'item' => $item
    ]);

    // Print the results
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
# [END dlp_inspect_string_with_exclusion_dict_substring]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
