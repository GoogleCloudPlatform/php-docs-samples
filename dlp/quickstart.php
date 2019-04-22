<?php
/**
 * Copyright 2017 Google Inc.
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

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# [START dlp_quickstart]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\InspectConfig\FindingLimits;

// Instantiate a client.
$dlp = new DlpServiceClient();

// The infoTypes of information to match
$usNameInfoType = (new InfoType())
    ->setName('PERSON_NAME');
$phoneNumberInfoType = (new InfoType())
    ->setName('PHONE_NUMBER');
$infoTypes = [$usNameInfoType, $phoneNumberInfoType];

// Set the string to inspect
$stringToInspect = 'Robert Frost';

// Only return results above a likelihood threshold, 0 for all
$minLikelihood = likelihood::LIKELIHOOD_UNSPECIFIED;

// Limit the number of findings, 0 for no limit
$maxFindings = 0;

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
    ->setValue($stringToInspect);

$parent = $dlp->projectName(getEnv('GOOGLE_PROJECT_ID'));

// Run request
$response = $dlp->inspectContent($parent, array(
    'inspectConfig' => $inspectConfig,
    'item' => $content
));

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
# [END dlp_quickstart]
