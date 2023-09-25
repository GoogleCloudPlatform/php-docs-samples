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
 * dimension and metric filters in a report.
 * See https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/properties/runReport#body.request_body.FIELDS.dimension_filter
 * for more information.
 * Usage:
 *   composer update
 *   php run_report_with_dimension_and_metric_filters.php YOUR-GA4-PROPERTY-ID
 */

namespace Google\Cloud\Samples\Analytics\Data;

// [START analyticsdata_run_report_with_dimension_and_metric_filters]
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\NumericFilter;
use Google\Analytics\Data\V1beta\Filter\NumericFilter\Operation;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\Filter\StringFilter\MatchType;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\FilterExpressionList;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\MetricType;
use Google\Analytics\Data\V1beta\NumericValue;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\RunReportResponse;

/**
 * @param string $propertyId Your GA-4 Property ID
 * Runs a report using both metric and dimension filters. A dimension filter
 * limits the report to include only users who made an in-app purchase using
 * Android platform. A metric filter specifies that only users with session
 * counts larger than 1,000 should be included.
 */
function run_report_with_dimension_and_metric_filters(string $propertyId)
{
    // Create an instance of the Google Analytics Data API client library.
    $client = new BetaAnalyticsDataClient();

    // Make an API call.
    $request = (new RunReportRequest())
        ->setProperty('properties/' . $propertyId)
        ->setDimensions([new Dimension(['name' => 'city'])])
        ->setMetrics([new Metric(['name' => 'activeUsers'])])
        ->setDateRanges([new DateRange([
            'start_date' => '2020-03-31',
            'end_date' => 'today',
            ]),
        ])
        ->setMetricFilter(new FilterExpression([
            'filter' => new Filter([
                'field_name' => 'sessions',
                'numeric_filter' => new NumericFilter([
                    'operation' => Operation::GREATER_THAN,
                    'value' => new NumericValue([
                        'int64_value' => 1000,
                    ]),
                ]),
            ]),
        ]))
        ->setDimensionFilter(new FilterExpression([
            'and_group' => new FilterExpressionList([
                'expressions' => [
                    new FilterExpression([
                        'filter' => new Filter([
                            'field_name' => 'platform',
                            'string_filter' => new StringFilter([
                                'match_type' => MatchType::EXACT,
                                'value' => 'Android',
                            ])
                        ]),
                    ]),
                    new FilterExpression([
                        'filter' => new Filter([
                            'field_name' => 'eventName',
                            'string_filter' => new StringFilter([
                                'match_type' => MatchType::EXACT,
                                'value' => 'in_app_purchase',
                            ])
                        ])
                   ]),
                ],
            ]),
        ]));
    $response = $client->runReport($request);

    printRunReportResponseWithDimensionAndMetricFilters($response);
}

/**
 * Print results of a runReport call.
 * @param RunReportResponse $response
 */
function printRunReportResponseWithDimensionAndMetricFilters(RunReportResponse $response)
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
// [END analyticsdata_run_report_with_dimension_and_metric_filters]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
