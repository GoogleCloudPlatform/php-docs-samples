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

// [START dlp_inspect_custom_regex]
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType;
use Google\Cloud\Dlp\V2\CustomInfoType\Regex;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectContentRequest;
use Google\Cloud\Dlp\V2\Likelihood;

/**
 * Inspect data with a custom regex
 * Regex example: Matching medical record numbers. The following sample uses a regular expression custom infoType detector that instructs Cloud DLP to match a medical record number (MRN) in the input text "Patient's MRN 444-5-22222," and then assigns each match a likelihood of POSSIBLE.
 *
 * @param string $projectId         The Google Cloud project id to use as a parent resource.
 * @param string $textToInspect     The string to inspect.
 */
function inspect_custom_regex(
    // TODO(developer): Replace sample parameters before running the code.
    string $projectId,
    string $textToInspect = 'Patients MRN 444-5-22222'
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$projectId/locations/global";

    // Specify what content you want the service to Inspect.
    $item = (new ContentItem())
        ->setValue($textToInspect);

    // Specify the regex pattern the inspection will look for.
    $customRegexPattern = '[1-9]{3}-[1-9]{1}-[1-9]{5}';

    // Construct the custom regex detector.
    $cMrnDetector = (new InfoType())
        ->setName('C_MRN');
    $customInfoType = (new CustomInfoType())
        ->setInfoType($cMrnDetector)
        ->setRegex((new Regex())
            ->setPattern($customRegexPattern))
        ->setLikelihood(Likelihood::POSSIBLE);

    // Construct the configuration for the Inspect request.
    $inspectConfig = (new InspectConfig())
        ->setCustomInfoTypes([$customInfoType])
        ->setIncludeQuote(true);

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
// [END dlp_inspect_custom_regex]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
