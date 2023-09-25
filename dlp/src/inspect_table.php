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

// [START dlp_inspect_table]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\FieldId;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\Table;
use Google\Cloud\Dlp\V2\Table\Row;
use Google\Cloud\Dlp\V2\Value;

/**
 * Inspect a table for sensitive content.
 *
 * @param string $projectId         The Google Cloud project id to use as a parent resource.
 */
function inspect_table(string $projectId): void
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$projectId/locations/global";

    // Specify the table to be inspected.
    $tableToDeIdentify = (new Table())
        ->setHeaders([
            (new FieldId())
                ->setName('NAME'),
            (new FieldId())
                ->setName('PHONE'),
        ])
        ->setRows([
            (new Row())->setValues([
                (new Value())
                    ->setStringValue('John Doe'),
                (new Value())
                    ->setStringValue('(206) 555-0123')
            ])
        ]);

    $item = (new ContentItem())
        ->setTable($tableToDeIdentify);

    // Construct the configuration for the Inspect request.
    $phoneNumber = (new InfoType())
        ->setName('PHONE_NUMBER');
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes([$phoneNumber])
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
// [END dlp_inspect_table]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
