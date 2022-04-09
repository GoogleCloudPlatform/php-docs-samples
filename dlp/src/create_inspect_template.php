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

namespace Google\Cloud\Samples\Dlp;

// [START dlp_create_inspect_template]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectTemplate;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\InspectConfig\FindingLimits;

/**
 * Create a new DLP inspection configuration template.
 *
 * @param string $callingProjectId project ID to run the API call under
 * @param string $templateId       name of the template to be created
 * @param string $displayName      (Optional) The human-readable name to give the template
 * @param string $description      (Optional) A description for the trigger to be created
 * @param int    $maxFindings      (Optional) The maximum number of findings to report per request (0 = server maximum)
 */
function create_inspect_template(
    string $callingProjectId,
    string $templateId,
    string $displayName = '',
    string $description = '',
    int $maxFindings = 0
): void {
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
    $parent = "projects/$callingProjectId/locations/global";
    $template = $dlp->createInspectTemplate($parent, $inspectTemplate, [
        'templateId' => $templateId
    ]);

    // Print results
    printf('Successfully created template %s' . PHP_EOL, $template->getName());
}
// [END dlp_create_inspect_template]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
