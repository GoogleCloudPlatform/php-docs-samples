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
 * Google Analytics Data API sample application demonstrating the usage of
 * cohort specification in a report.
 * See https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/properties/runReport#body.request_body.FIELDS.cohort_spec
 * for more information.
 * Usage:
 *   composer update
 *   php run_report_with_cohorts.php YOUR-GA4-PROPERTY-ID
 */

namespace Google\Cloud\Samples\Analytics\Data;

// [START analyticsdata_run_report_with_cohorts]
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\Cohort;
use Google\Analytics\Data\V1beta\CohortSpec;
use Google\Analytics\Data\V1beta\CohortsRange;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\MetricType;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\RunReportResponse;

/**
 * @param string $propertyId Your GA-4 Property ID
 * Runs a report on a cohort of users whose first session happened on the
 * same week. The number of active users and user retention rate is calculated
 * for the cohort using WEEKLY granularity.
 */
function run_report_with_cohorts(string $propertyId)
{
    // Creates an instance of the Google Analytics Data API client library.
    $client = new BetaAnalyticsDataClient();

    // Make an API call.
    $request = (new RunReportRequest())
        ->setProperty('properties/' . $propertyId)
        ->setDimensions([
            new Dimension(['name' => 'cohort']),
            new Dimension(['name' => 'cohortNthWeek']),
        ])
        ->setMetrics([
            new Metric(['name' => 'cohortActiveUsers']),
            new Metric([
                'name' => 'cohortRetentionRate',
                'expression' => 'cohortActiveUsers/cohortTotalUsers'
            ])
        ])
        ->setCohortSpec(new CohortSpec([
            'cohorts' => [
                new Cohort([
                    'dimension' => 'firstSessionDate',
                    'name' => 'cohort',
                    'date_range' => new DateRange([
                        'start_date' => '2021-01-03',
                        'end_date' => '2021-01-09',
                    ]),
                ])
            ],
            'cohorts_range' => new CohortsRange([
                'start_offset' => '0',
                'end_offset' => '4',
                'granularity' => '2',
            ]),
        ]));
    $response = $client->runReport($request);

    printRunReportResponseWithCohorts($response);
}

/**
 * Print results of a runReport call.
 * @param RunReportResponse $response
 */
function printRunReportResponseWithCohorts($response)
{
    // [START analyticsdata_print_run_report_response_header]
    printf('%s rows received%s', $response->getRowCount(), PHP_EOL);
    foreach ($response->getDimensionHeaders() as $dimensionHeader) {
        printf('Dimension header name: %s%s', $dimensionHeader->getName(), PHP_EOL);
    }
    foreach ($response->getMetricHeaders() as $metricHeader) {
        printf(
            'Metric header name: %s (%s)' . PHP_EOL,
            $metricHeader->getName(),
            MetricType::name($metricHeader->getType())
        );
    }
    // [END analyticsdata_print_run_report_response_header]

    // [START analyticsdata_print_run_report_response_rows]
    print 'Report result: ' . PHP_EOL;

    foreach ($response->getRows() as $row) {
        printf(
            '%s %s' . PHP_EOL,
            $row->getDimensionValues()[0]->getValue(),
            $row->getMetricValues()[0]->getValue()
        );
    }
    // [END analyticsdata_print_run_report_response_rows]
}
// [END analyticsdata_run_report_with_cohorts]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
