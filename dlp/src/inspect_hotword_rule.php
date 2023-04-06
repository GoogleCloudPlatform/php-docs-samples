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

// [START dlp_inspect_hotword_rule]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType;
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
 * Inspect data with a hotword rule
 * This sample uses a custom regex with a hotword rule to increase the likelihood of match.
 *
 * @param string $projectId         The Google Cloud project id to use as a parent resource.
 * @param string $textToInspect     The string to inspect.
 */
function inspect_hotword_rule(
    // TODO(developer): Replace sample parameters before running the code.
    string $projectId,
    string $textToInspect = "Patient's MRN 444-5-22222 and just a number 333-2-33333"
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$projectId/locations/global";

    // Specify what content you want the service to Inspect.
    $item = (new ContentItem())
        ->setValue($textToInspect);

    // Specify the regex pattern the inspection will look for.
    $customRegexPattern = '[1-9]{3}-[1-9]{1}-[1-9]{5}';
    $hotwordRegexPattern = '(?i)(mrn|medical)(?-i)';

    // Construct the custom regex detector.
    $cMrnDetector = (new InfoType())->setName('C_MRN');
    $customInfoType = (new CustomInfoType())
        ->setInfoType($cMrnDetector)
        ->setLikelihood(Likelihood::POSSIBLE)
        ->setRegex((new Regex())->setPattern($customRegexPattern));

    // Specify hotword likelihood adjustment.
    $likelihoodAdjustment = (new LikelihoodAdjustment())->setFixedLikelihood(Likelihood::VERY_LIKELY);

    // Specify a window around a finding to apply a detection rule.
    $proximity = (new Proximity())->setWindowBefore(10);

    $hotwordRule = (new HotwordRule())
        ->setHotwordRegex((new Regex())->setPattern($hotwordRegexPattern))
        ->setLikelihoodAdjustment($likelihoodAdjustment)
        ->setProximity($proximity);

    // Construct rule set for the inspect config.
    $inspectionRuleSet = (new InspectionRuleSet())
        ->setInfoTypes([$cMrnDetector])
        ->setRules([(new InspectionRule())->setHotwordRule($hotwordRule)]);

    // Construct the configuration for the Inspect request.
    $inspectConfig = (new InspectConfig())
        ->setCustomInfoTypes([$customInfoType])
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
        print('No findings.' . PHP_EOL);
    } else {
        print('Findings:' . PHP_EOL);
        foreach ($findings as $finding) {
            print('  Quote: ' . $finding->getQuote() . PHP_EOL);
            print('  Info type: ' . $finding->getInfoType()->getName() . PHP_EOL);
            $likelihoodString = Likelihood::name($finding->getLikelihood());
            print('  Likelihood: ' . $likelihoodString . PHP_EOL);
        }
    }
}
// [END dlp_inspect_hotword_rule]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
