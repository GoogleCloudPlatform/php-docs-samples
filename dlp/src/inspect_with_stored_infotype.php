<?php
/**
 * Copyright 2023 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/dlp/README.md
 */

namespace Google\Cloud\Samples\Dlp;

# [START dlp_inspect_with_stored_infotype]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\StoredType;

/**
 * Inspect with stored infoType.
 * Scan content using a large custom dictionary detector.
 *
 * @param string $projectId             The Google Cloud Project ID to run the API call under.
 * @param string $storedInfoTypeName    The name of the stored infotype whose This value must be in the format
 * projects/projectName/(locations/locationId)/storedInfoTypes/storedInfoTypeName.
 * @param string $textToInspect         The string to inspect.
 */
function inspect_with_stored_infotype(
    string $projectId,
    string $storedInfoTypeName,
    string $textToInspect
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$projectId/locations/global";

    // Specify the content to be inspected.
    $item = (new ContentItem())
        ->setValue($textToInspect);

    // Reference to the existing StoredInfoType to inspect the data.
    $customInfoType = (new CustomInfoType())
        ->setInfoType((new InfoType())
            ->setName('STORED_TYPE'))
        ->setStoredType((new StoredType())
            ->setName($storedInfoTypeName));

    // Construct the configuration for the Inspect request.
    $inspectConfig = (new InspectConfig())
        ->setCustomInfoTypes([$customInfoType])
        ->setIncludeQuote(true);

    // Run request.
    $response = $dlp->inspectContent([
        'parent' => $parent,
        'inspectConfig' => $inspectConfig,
        'item' => $item
    ]);

    // Print the results.
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
# [END dlp_inspect_with_stored_infotype]
// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
