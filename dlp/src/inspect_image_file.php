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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigquery/api/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 3) {
    return printf("Usage: php %s PROJECT_ID FILEPATH\n", __FILE__);
}
list($_, $projectId, $filepath) = $argv;

// [START dlp_inspect_image_file]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\ByteContentItem;
use Google\Cloud\Dlp\V2\ByteContentItem\BytesType;
use Google\Cloud\Dlp\V2\Likelihood;

/** Uncomment and populate these variables in your code */
// $projectId = 'YOUR_PROJECT_ID';
// $filepath = 'path/to/image.png';

// Instantiate a client.
$dlp = new DlpServiceClient();

// Get the bytes of the file
$fileBytes = (new ByteContentItem())
    ->setType(BytesType::IMAGE_PNG)
    ->setData(file_get_contents($filepath));

// Construct request
$parent = $dlp->projectName($projectId);
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
$response = $dlp->inspectContent($parent, [
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
// [END dlp_inspect_image_file]
