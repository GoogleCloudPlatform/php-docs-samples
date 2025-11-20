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

// [START dlp_inspect_string_custom_omit_overlap]
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType;
use Google\Cloud\Dlp\V2\CustomInfoType\ExclusionType;
use Google\Cloud\Dlp\V2\CustomInfoType\Regex;
use Google\Cloud\Dlp\V2\ExcludeInfoTypes;
use Google\Cloud\Dlp\V2\ExclusionRule;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectContentRequest;
use Google\Cloud\Dlp\V2\InspectionRule;
use Google\Cloud\Dlp\V2\InspectionRuleSet;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\MatchingType;

/**
 * Inspect a string for sensitive data, omitting custom matches
 * Omit scan matches from a PERSON_NAME detector scan that overlap with a custom detector.
 *
 * @param string $projectId         The Google Cloud project id to use as a parent resource.
 * @param string $textToInspect     The string to inspect.
 */
function inspect_string_custom_omit_overlap(
    // TODO(developer): Replace sample parameters before running the code.
    string $projectId,
    string $textToInspect = 'Name: Jane Doe. Name: Larry Page.'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$projectId/locations/global";

    // Specify what content you want the service to Inspect.
    $item = (new ContentItem())
        ->setValue($textToInspect);

    // Specify the type of info the inspection will look for.
    $vipDetector = (new InfoType())
        ->setName('VIP_DETECTOR');
    $pattern = 'Larry Page|Sergey Brin';
    $customInfoType = (new CustomInfoType())
        ->setInfoType($vipDetector)
        ->setRegex((new Regex())
            ->setPattern($pattern))
        ->setExclusionType(ExclusionType::EXCLUSION_TYPE_EXCLUDE);

    // Exclude matches that also match the custom infotype.
    $exclusionRule = (new ExclusionRule())
        ->setMatchingType(MatchingType::MATCHING_TYPE_FULL_MATCH)
        ->setExcludeInfoTypes((new ExcludeInfoTypes())
                ->setInfoTypes([$customInfoType->getInfoType()])
        );

    // Construct a ruleset that applies the exclusion rule to the PERSON_NAME infotype.
    $personName = (new InfoType())
        ->setName('PERSON_NAME');
    $inspectionRuleSet = (new InspectionRuleSet())
        ->setInfoTypes([$personName])
        ->setRules([
            (new InspectionRule())
                ->setExclusionRule($exclusionRule),
        ]);

    // Construct the configuration for the Inspect request, including the ruleset.
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes([$personName])
        ->setCustomInfoTypes([$customInfoType])
        ->setIncludeQuote(true)
        ->setRuleSet([$inspectionRuleSet]);

    // Run request
    $inspectContentRequest = (new InspectContentRequest())
        ->setParent($parent)
        ->setInspectConfig($inspectConfig)
        ->setItem($item);
    $response = $dlp->inspectContent($inspectContentRequest);

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
// [END dlp_inspect_string_custom_omit_overlap]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
