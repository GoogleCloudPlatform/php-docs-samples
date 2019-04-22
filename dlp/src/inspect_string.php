<?php

/**
 * Copyright 2016 Google Inc.
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
namespace Google\Cloud\Samples\Dlp;

# [START dlp_inspect_string]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\InspectConfig\FindingLimits;

/**
 * Inspect a string.
 *
 * @param string $callingProjectId The GCP Project ID to run the API call under
 * @param string $string The text to inspect
 * @param int $maxFindings The maximum number of findings to report per request (0 = server maximum)
 */
function inspect_string(
  $callingProjectId,
  $string,
  $maxFindings = 0
) {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // The infoTypes of information to match
    $personNameInfoType = (new InfoType())
        ->setName('PERSON_NAME');
    $phoneNumberInfoType = (new InfoType())
        ->setName('PHONE_NUMBER');
    $infoTypes = [$personNameInfoType, $phoneNumberInfoType];

    // The minimum likelihood required before returning a match
    $minLikelihood = likelihood::LIKELIHOOD_UNSPECIFIED;

    // Whether to include the matching string in the response
    $includeQuote = true;

    // Specify finding limits
    $limits = (new FindingLimits())
        ->setMaxFindingsPerRequest($maxFindings);

    // Create the configuration object
    $inspectConfig = (new InspectConfig())
        ->setMinLikelihood($minLikelihood)
        ->setLimits($limits)
        ->setInfoTypes($infoTypes)
        ->setIncludeQuote($includeQuote);

    $content = (new ContentItem())
        ->setValue($string);

    $parent = $dlp->projectName($callingProjectId);

    // Run request
    $response = $dlp->inspectContent($parent, [
        'inspectConfig' => $inspectConfig,
        'item' => $content
    ]);

    // Print the results
    $findings = $response->getResult()->getFindings();
    if (count($findings) == 0) {
        print('No findings.' . PHP_EOL);
    } else {
        print('Findings:' . PHP_EOL);
        foreach ($findings as $finding) {
            if ($includeQuote) {
                print('  Quote: ' . $finding->getQuote() . PHP_EOL);
            }
            print('  Info type: ' . $finding->getInfoType()->getName() . PHP_EOL);
            $likelihoodString = Likelihood::name($finding->getLikelihood());
            print('  Likelihood: ' . $likelihoodString . PHP_EOL);
        }
    }
}
# [END dlp_inspect_string]
