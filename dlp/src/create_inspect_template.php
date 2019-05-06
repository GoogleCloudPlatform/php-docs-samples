<?php

/**
 * Copyright 2018 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/dlp/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 3 || count($argv) > 6) {
    return print("Usage: php create_inspect_template.php CALLING_PROJECT TEMPLATE [DISPLAY_NAME] [DESCRIPTION] [MAX_FINDINGS]\n");
}
list($_, $callingProjectId, $templateId, $displayName, $description) = $argv;
$displayName = isset($argv[3]) ? $argv[3] : '';
$description = isset($argv[4]) ? $argv[4] : '';
$maxFindings = isset($argv[5]) ? (int) $argv[5] : 0;

// [START dlp_create_inspect_template]
/**
 * Create a new DLP inspection configuration template.
 */
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectTemplate;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\InspectConfig\FindingLimits;

/** Uncomment and populate these variables in your code */
// $callingProjectId = 'The project ID to run the API call under';
// $templateId = 'The name of the template to be created';
// $displayName = ''; // (Optional) The human-readable name to give the template
// $description = ''; // (Optional) A description for the trigger to be created
// $maxFindings = 0;  // (Optional) The maximum number of findings to report per request (0 = server maximum)

// Instantiate a client.
$dlp = new DlpServiceClient();

// ----- Construct inspection config -----
// The infoTypes of information to match
$personNameInfoType = (new InfoType())
    ->setName('PERSON_NAME');
$phoneNumberInfoType = (new InfoType())
    ->setName('PHONE_NUMBER');
$infoTypes = [$personNameInfoType, $phoneNumberInfoType];

// Whether to include the matching string in the response
$includeQuote = true;

// The minimum likelihood required before returning a match
$minLikelihood = likelihood::LIKELIHOOD_UNSPECIFIED;

// Specify finding limits
$limits = (new FindingLimits())
    ->setMaxFindingsPerRequest($maxFindings);

// Create the configuration object
$inspectConfig = (new InspectConfig())
    ->setMinLikelihood($minLikelihood)
    ->setLimits($limits)
    ->setInfoTypes($infoTypes)
    ->setIncludeQuote($includeQuote);

// Construct inspection template
$inspectTemplate = (new InspectTemplate())
    ->setInspectConfig($inspectConfig)
    ->setDisplayName($displayName)
    ->setDescription($description);

// Run request
$parent = $dlp->projectName($callingProjectId);
$template = $dlp->createInspectTemplate($parent, [
    'inspectTemplate' => $inspectTemplate,
    'templateId' => $templateId
]);

// Print results
printf('Successfully created template %s' . PHP_EOL, $template->getName());
// [END dlp_create_inspect_template]
