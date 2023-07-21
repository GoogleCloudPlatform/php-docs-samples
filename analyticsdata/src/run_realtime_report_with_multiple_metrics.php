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
 * a realtime report.
 * See https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/properties/runRealtimeReport
 * for more information.
 * Usage:
 *   composer update
 *   php run__realtime_report_with_multiple_metrics.php YOUR-GA4-PROPERTY-ID
 */

namespace Google\Cloud\Samples\Analytics\Data;

// [START analyticsdata_run_realtime_report_with_multiple_metrics]
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\MetricType;
use Google\Analytics\Data\V1beta\RunRealtimeReportRequest;
use Google\Analytics\Data\V1beta\RunRealtimeReportResponse;

/**
 * Runs a realtime report on a Google Analytics 4 property.
 * @param string $propertyId Your GA-4 Property ID
 */
function run_realtime_report_with_multiple_metrics(string $propertyId)
{
    // Create an instance of the Google Analytics Data API client library.
    $client = new BetaAnalyticsDataClient();

    // Make an API call.
    $request = (new RunRealtimeReportRequest())
        ->setProperty('properties/' . $propertyId)
        ->setDimensions([new Dimension(['name' => 'unifiedScreenName'])])
        ->setMetrics([
            new Metric(['name' => 'screenPageViews']),
            new Metric(['name' => 'conversions']),
        ]);
    $response = $client->runRealtimeReport($request);

    printRunRealtimeReportWithMultipleMetricsResponse($response);
}

/**
 * Print results of a runRealtimeReport call.
 * @param RunRealtimeReportResponse $response
 */
function printRunRealtimeReportWithMultipleMetricsResponse(RunRealtimeReportResponse $response)
{
    // [START analyticsdata_print_run_realtime_report_response_header]
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
    // [END analyticsdata_print_run_realtime_report_response_header]

    // [START analyticsdata_print_run_realtime_report_response_rows]
    print 'Report result: ' . PHP_EOL;

    foreach ($response->getRows() as $row) {
        printf(
            '%s %s' . PHP_EOL,
            $row->getDimensionValues()[0]->getValue(),
            $row->getMetricValues()[0]->getValue()
        );
    }
    // [END analyticsdata_print_run_realtime_report_response_rows]
}
// [END analyticsdata_run_realtime_report_with_multiple_metrics]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
