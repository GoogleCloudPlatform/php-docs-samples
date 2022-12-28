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
 * Google Analytics Data API sample application retrieving dimension and metrics
 * metadata.
 * See https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/properties/getMetadata
 * for more information.
 * Usage:
 *   composer update
 *   php get_common_metadata.php
 */

namespace Google\Cloud\Samples\Analytics\Data;

// [START analyticsdata_get_common_metadata]
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\Metadata;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\MetricType;
use Google\Analytics\Data\V1beta\MetricAggregation;
use Google\Analytics\Data\V1beta\RunReportResponse;

/**
* Retrieves dimensions and metrics available for all Google Analytics 4 properties.
*/
function get_common_metadata()
{
    // Create an instance of the Google Analytics Data API client library.
    $client = new BetaAnalyticsDataClient();
    
    /*
    * Set the Property ID to 0 for dimensions and metrics common
    * to all properties. In this special mode, this method will
    * not return custom dimensions and metrics.
    */
    $propertyId = 0;
    
    echo "properties/{$propertyId}/metadata";

    // Make an API call.
   // $response = $client->getMetadata(
   //     'name' => 'properties/{$propertyId}/metadata',
    //);

   // printGetCommonMetadata($response);
}

/**
 * Print results of a getMetadata call.
 * @param Metadata $response
 */
function printGetCommonMetadata($response)
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
// [END analyticsdata_get_common_metadata]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
