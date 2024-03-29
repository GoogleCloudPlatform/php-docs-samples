<?php
/**
 * Copyright 2018 Google LLC.
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

// [START dlp_inspect_text_file]
use Google\Cloud\Dlp\V2\ByteContentItem;
use Google\Cloud\Dlp\V2\ByteContentItem\BytesType;
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectContentRequest;
use Google\Cloud\Dlp\V2\Likelihood;

/**
 * @param string $projectId
 * @param string $filepath
 */
function inspect_text_file(string $projectId, string $filepath): void
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Get the bytes of the file
    $fileBytes = (new ByteContentItem())
        ->setType(BytesType::TEXT_UTF8)
        ->setData(file_get_contents($filepath));

    // Construct request
    $parent = "projects/$projectId/locations/global";
    $item = (new ContentItem())
        ->setByteItem($fileBytes);
    $inspectConfig = (new InspectConfig())
        // The infoTypes of information to match
        ->setInfoTypes([
            (new InfoType())->setName('PHONE_NUMBER'),
            (new InfoType())->setName('EMAIL_ADDRESS'),
            (new InfoType())->setName('CREDIT_CARD_NUMBER')
        ])
        // Whether to include the matching string
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
// [END dlp_inspect_file]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
