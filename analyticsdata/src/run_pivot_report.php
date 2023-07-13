<?php
/**
 * Copyright 2022 Google LLC.
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
 * Google Analytics Data API sample application demonstrating the creation of
 * a pivot report.
 * See https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/properties/runPivotReport
 * for more information.
 * Usage:
 *   composer update
 *   php run_pivot_report.php YOUR-GA4-PROPERTY-ID
 */

namespace Google\Cloud\Samples\Analytics\Data;

// [START analyticsdata_run_pivot_report]
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\Pivot;
use Google\Analytics\Data\V1beta\RunPivotReportRequest;
use Google\Analytics\Data\V1beta\RunPivotReportResponse;

/**
 * Runs a pivot query to build a report of session counts by country,
 * pivoted by the browser dimension.
 * @param string $propertyId Your GA-4 Property ID
 */
function run_pivot_report(string $propertyId)
{
    // Create an instance of the Google Analytics Data API client library.
    $client = new BetaAnalyticsDataClient();

    // Make an API call.
    $request = (new RunPivotReportRequest())
        ->setProperty('properties/' . $propertyId)
        ->setDateRanges([new DateRange([
            'start_date' => '2021-01-01',
            'end_date' => '2021-01-30',
            ]),
        ])
        ->setPivots([
            new Pivot([
                'field_names' => ['country'],
                'limit' => 250,
                'order_bys' => [new OrderBy([
                    'dimension' => new DimensionOrderBy([
                        'dimension_name' => 'country',
                    ]),
                ])],
            ]),
            new Pivot([
                'field_names' => ['browser'],
                'offset' => 3,
                'limit' => 3,
                'order_bys' => [new OrderBy([
                    'metric' => new MetricOrderBy([
                        'metric_name' => 'sessions',
                    ]),
                    'desc' => true,
                ])],
            ]),
        ])
        ->setMetrics([new Metric(['name' => 'sessions'])])
        ->setDimensions([
            new Dimension(['name' => 'country']),
            new Dimension(['name' => 'browser']),
        ]);
    $response = $client->runPivotReport($request);

    printPivotReportResponse($response);
}

/**
 * Print results of a runPivotReport call.
 * @param RunPivotReportResponse $response
 */
function printPivotReportResponse(RunPivotReportResponse $response)
{
    // [START analyticsdata_print_run_pivot_report_response]
    print 'Report result: ' . PHP_EOL;

    foreach ($response->getRows() as $row) {
        printf(
            '%s %s' . PHP_EOL,
            $row->getDimensionValues()[0]->getValue(),
            $row->getMetricValues()[0]->getValue()
        );
    }
    // [END analyticsdata_print_run_pivot_report_response]
}
// [END analyticsdata_run_pivot_report]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
