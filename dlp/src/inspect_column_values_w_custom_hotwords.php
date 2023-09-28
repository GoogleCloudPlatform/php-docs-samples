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

// [START dlp_inspect_column_values_w_custom_hotwords]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\CustomInfoType\DetectionRule\HotwordRule;
use Google\Cloud\Dlp\V2\CustomInfoType\DetectionRule\LikelihoodAdjustment;
use Google\Cloud\Dlp\V2\CustomInfoType\DetectionRule\Proximity;
use Google\Cloud\Dlp\V2\CustomInfoType\Regex;
use Google\Cloud\Dlp\V2\FieldId;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectionRule;
use Google\Cloud\Dlp\V2\InspectionRuleSet;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\Table;
use Google\Cloud\Dlp\V2\Table\Row;
use Google\Cloud\Dlp\V2\Value;

/**
 * Hotword example: Set the match likelihood of a table column.
 * This example demonstrates how you can set the match likelihood of an entire column of data.
 * This approach is helpful, for example, if you want to exclude a column of data from inspection
 * results.
 *
 * @param string $projectId         The Google Cloud project id to use as a parent resource.
 */
function inspect_column_values_w_custom_hotwords(string $projectId): void
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = "projects/$projectId/locations/global";

    // Specify the table to be inspected.
    $tableToDeIdentify = (new Table())
        ->setHeaders([
            (new FieldId())
                ->setName('Fake Social Security Number'),
            (new FieldId())
                ->setName('Real Social Security Number'),
        ])
        ->setRows([
            (new Row())->setValues([
                (new Value())
                    ->setStringValue('111-11-1111'),
                (new Value())
                    ->setStringValue('222-22-2222')
            ])
        ]);

    $item = (new ContentItem())
        ->setTable($tableToDeIdentify);

    // Specify the regex pattern the inspection will look for.
    $hotwordRegexPattern = 'Fake Social Security Number';

    // Specify hotword likelihood adjustment.
    $likelihoodAdjustment = (new LikelihoodAdjustment())
        ->setFixedLikelihood(Likelihood::VERY_UNLIKELY);

    // Specify a window around a finding to apply a detection rule.
    $proximity = (new Proximity())
        ->setWindowBefore(1);

    // Construct the hotword rule.
    $hotwordRule = (new HotwordRule())
        ->setHotwordRegex((new Regex())
            ->setPattern($hotwordRegexPattern))
        ->setLikelihoodAdjustment($likelihoodAdjustment)
        ->setProximity($proximity);

    // Construct rule set for the inspect config.
    $infotype = (new InfoType())
        ->setName('US_SOCIAL_SECURITY_NUMBER');
    $inspectionRuleSet = (new InspectionRuleSet())
        ->setInfoTypes([$infotype])
        ->setRules([
            (new InspectionRule())
                ->setHotwordRule($hotwordRule)
        ]);

    // Construct the configuration for the Inspect request.
    $inspectConfig = (new InspectConfig())
        ->setInfoTypes([$infotype])
        ->setIncludeQuote(true)
        ->setRuleSet([$inspectionRuleSet])
        ->setMinLikelihood(Likelihood::POSSIBLE);

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
// [END dlp_inspect_column_values_w_custom_hotwords]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
