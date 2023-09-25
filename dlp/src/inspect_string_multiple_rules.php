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

# [START dlp_inspect_string_multiple_rules]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType\DetectionRule\HotwordRule;
use Google\Cloud\Dlp\V2\CustomInfoType\DetectionRule\LikelihoodAdjustment;
use Google\Cloud\Dlp\V2\CustomInfoType\DetectionRule\Proximity;
use Google\Cloud\Dlp\V2\CustomInfoType\Dictionary;
use Google\Cloud\Dlp\V2\CustomInfoType\Dictionary\WordList;
use Google\Cloud\Dlp\V2\CustomInfoType\Regex;
use Google\Cloud\Dlp\V2\ExclusionRule;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectionRule;
use Google\Cloud\Dlp\V2\InspectionRuleSet;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\MatchingType;

/**
 * Inspect a string for sensitive data by using multiple rules
 * Illustrates applying both exclusion and hotword rules. This snippet's rule set includes both hotword rules and dictionary and regex exclusion rules. Notice that the four rules are specified in an array within the rules element.
 *
 * @param string $projectId         The Google Cloud project id to use as a parent resource.
 * @param string $textToInspect     The string to inspect.
 */
function inspect_string_multiple_rules(
    // TODO(developer): Replace sample parameters before running the code.
    string $projectId,
    string $textToInspect = 'patient: Jane Doe'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$projectId/locations/global";

    // Specify what content you want the service to Inspect.
    $item = (new ContentItem())
        ->setValue($textToInspect);

    // Construct hotword rules
    $patientRule = (new HotwordRule())
        ->setHotwordRegex((new Regex())
            ->setPattern('patient'))
        ->setProximity((new Proximity())
            ->setWindowBefore(10))
        ->setLikelihoodAdjustment((new LikelihoodAdjustment())
            ->setFixedLikelihood(Likelihood::VERY_LIKELY));

    $doctorRule = (new HotwordRule())
        ->setHotwordRegex((new Regex())
            ->setPattern('doctor'))
        ->setProximity((new Proximity())
            ->setWindowBefore(10))
        ->setLikelihoodAdjustment((new LikelihoodAdjustment())
            ->setFixedLikelihood(Likelihood::VERY_UNLIKELY));

    // Construct exclusion rules
    $wordList = (new Dictionary())
        ->setWordList((new WordList())
            ->setWords(['Quasimodo']));

    $quasimodoRule = (new ExclusionRule())
        ->setMatchingType(MatchingType::MATCHING_TYPE_PARTIAL_MATCH)
        ->setDictionary($wordList);

    $redactedRule = (new ExclusionRule())
        ->setMatchingType(MatchingType::MATCHING_TYPE_PARTIAL_MATCH)
        ->setRegex((new Regex())
            ->setPattern('REDACTED'));

    // Specify the exclusion rule and build-in info type the inspection will look for.
    $personName = (new InfoType())
        ->setName('PERSON_NAME');
    $inspectionRuleSet = (new InspectionRuleSet())
        ->setInfoTypes([$personName])
        ->setRules([
            (new InspectionRule())
                ->setHotwordRule($patientRule),
            (new InspectionRule())
                ->setHotwordRule($doctorRule),
            (new InspectionRule())
                ->setExclusionRule($quasimodoRule),
            (new InspectionRule())
                ->setExclusionRule($redactedRule),
        ]);

    // Construct the configuration for the Inspect request, including the ruleset.
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes([$personName])
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
# [END dlp_inspect_string_multiple_rules]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
