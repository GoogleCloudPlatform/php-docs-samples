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

# [START dlp_inspect_string_custom_hotword]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType\DetectionRule\HotwordRule;
use Google\Cloud\Dlp\V2\CustomInfoType\DetectionRule\LikelihoodAdjustment;
use Google\Cloud\Dlp\V2\CustomInfoType\DetectionRule\Proximity;
use Google\Cloud\Dlp\V2\CustomInfoType\Regex;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectionRule;
use Google\Cloud\Dlp\V2\InspectionRuleSet;
use Google\Cloud\Dlp\V2\Likelihood;

/**
 * Inspect a string from sensitive data by using a custom hotword
 * Increase the likelihood of a PERSON_NAME match if there is the hotword "patient" nearby. Illustrates using the InspectConfig property for the purpose of scanning a medical database for patient names. You can use Cloud DLP's built-in PERSON_NAME infoType detector, but that causes Cloud DLP to match on all names of people, not just names of patients. To fix this, you can include a hotword rule that looks for the word "patient" within a certain character proximity from the first character of potential matches. You can then assign findings that match this pattern a likelihood of "very likely," since they correspond to your special criteria. Setting the minimum likelihood to VERY_LIKELY within InspectConfig ensures that only matches to this configuration are returned in findings.
 *
 * @param string $projectId         The Google Cloud project id to use as a parent resource.
 * @param string $textToInspect     The string to inspect.
 */
function inspect_string_custom_hotword(
    // TODO(developer): Replace sample parameters before running the code.
    string $projectId,
    string $textToInspect = 'patient name: John Doe'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$projectId/locations/global";

    // Specify what content you want the service to Inspect.
    $item = (new ContentItem())
        ->setValue($textToInspect);

    // Construct hotword rules
    $hotwordRule = (new HotwordRule())
        ->setHotwordRegex(
            (new Regex())
                ->setPattern('patient')
        )
        ->setProximity(
            (new Proximity())
                ->setWindowBefore(50)
        )
        ->setLikelihoodAdjustment(
            (new LikelihoodAdjustment())
                ->setFixedLikelihood(Likelihood::VERY_LIKELY)
        );

    // Construct a ruleset that applies the hotword rule to the PERSON_NAME infotype.
    $personName = (new InfoType())
        ->setName('PERSON_NAME');
    $inspectionRuleSet = (new InspectionRuleSet())
        ->setInfoTypes([$personName])
        ->setRules([
            (new InspectionRule())
                ->setHotwordRule($hotwordRule),
        ]);

    // Construct the configuration for the Inspect request, including the ruleset.
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes([$personName])
        ->setIncludeQuote(true)
        ->setRuleSet([$inspectionRuleSet])
        ->setMinLikelihood(Likelihood::VERY_LIKELY);

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
# [END dlp_inspect_string_custom_hotword]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
