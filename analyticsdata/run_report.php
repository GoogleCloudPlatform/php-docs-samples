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

/*

"""Google Analytics Data API sample application demonstrating the creation
of a basic report.
See https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/properties/runReport
for more information.
"""

Before you start the application, please review the comments starting with
"TODO(developer)" and update the code to use the correct values.

Usage:
  composer update
  php run_report.php
 */

// [START analyticsdata_run_report]
require 'vendor/autoload.php';

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\MetricType;

function runReport()
{
    /**
     * TODO(developer): Replace this variable with your Google Analytics 4
     *   property ID before running the sample.
     */
    $property_id = 'YOUR-GA4-PROPERTY-ID';

    // [START analyticsdata_initialize]
    //Imports the Google Analytics Data API client library.'

    $client = new BetaAnalyticsDataClient();

    // [END analyticsdata_initialize]

    // Make an API call.
    $response = $client->runReport([
        'property' => 'properties/' . $property_id,
        'dateRanges' => [
            new DateRange([
                'start_date' => '2020-09-01',
                'end_date' => '2020-09-15',
            ]),
        ],
        'dimensions' => [new Dimension(
            [
                'name' => 'country',
            ]
        ),
        ],
        'metrics' => [new Metric(
            [
                'name' => 'activeUsers',
            ]
        )
        ]
    ]);

    printRunReportResponse($response);
}

// Print results of a runReport call.
function printRunReportResponse($response)
{
    // [START analyticsdata_print_run_report_response_header]
    printf('%s rows received%s', $response->getRowCount(), PHP_EOL);
    foreach ($response->getDimensionHeaders() as $dimensionHeader) {
        printf('Dimension header name: %s%s', $dimensionHeader->getName(), PHP_EOL);
    }
    foreach ($response->getMetricHeaders() as $metricHeader) {
        printf(
            'Metric header name: %s (%s)%s',
            $metricHeader->getName(),
            MetricType::name($metricHeader->getType()),
            PHP_EOL
        );
    }
    // [END analyticsdata_print_run_report_response_header]

    // [START analyticsdata_print_run_report_response_rows]
    print 'Report result: ' . PHP_EOL;

    foreach ($response->getRows() as $row) {
        print $row->getDimensionValues()[0]->getValue()
        . ' ' . $row->getMetricValues()[0]->getValue() . PHP_EOL;
    }
    // [END analyticsdata_print_run_report_response_rows]
}
// [END analyticsdata_run_report]
runReport();
