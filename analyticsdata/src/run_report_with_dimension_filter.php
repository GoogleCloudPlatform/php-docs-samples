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
* Google Analytics Data API sample application demonstrating the usage of
* dimension and metric filters in a report.
* See https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/properties/runReport#body.request_body.FIELDS.dimension_filter
* for more information.
* Usage:
*   composer update
*   php run_report_with_dimension_filter.php YOUR-GA4-PROPERTY-ID
*/

namespace Google\Cloud\Samples\Analytics\Data;

// [START analyticsdata_run_report_with_dimension_filter]
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\MetricType;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\RunReportResponse;

/**
* @param string $propertyID Your GA-4 Property ID
* Runs a report using a dimension filter. The call returns a time series
* report of `eventCount` when `eventName` is `first_open` for each date.
* This sample uses relative date range values. See
* https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/DateRange
* for more information.
*/
function run_report_with_dimension_filter(string $propertyId)
{
    // [START analyticsdata_initialize]
    //Imports the Google Analytics Data API client library.'

    $client = new BetaAnalyticsDataClient();

    // [END analyticsdata_initialize]

    // Make an API call.
    $response = $client->runReport([
        'property' => 'properties/' . $propertyId,

        'dimensions' => [new Dimension(
            [
                'name' => 'date',
            ]),
        ],
        'metrics' => [new Metric(
            [
                'name' => 'eventCount',
            ]
        )
        ],
        'dateRanges' => [new DateRange(
        [
            'start_date' => '7daysAgo',
            'end_date' => 'yesterday',
        ])
        ],
        'dimension_filter' => new FilterExpression
        ([
	    'filter' => new Filter(
	        'field_name' => 'eventName',
	        'string_filter' => new StringFilter(
	            'value' => 'first_open'
	        ),
	    ),
	 ]
        ),
    ]);

    printRunReportResponseWithDimensionFilter($response);
}

/**
 * Print results of a runReport call.
 * @param RunReportResponse $response
 */
function printRunReportResponseWithDimensionFilter(RunReportResponse $response)
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
// [END analyticsdata_run_report_with_dimension_filter]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
