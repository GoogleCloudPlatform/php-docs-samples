<?php
/**
 * Copyright 2021 Google LLC.
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

/* Google Analytics Data API sample quickstart application.

This application demonstrates the usage of the Analytics Data API using
service account credentials.

Before you start the application, please review the comments starting with
"TODO(developer)" and update the code to use the correct values.

Usage:
  composer update
  php quickstart.php
 */

// [START analytics_data_quickstart]
require 'vendor/autoload.php';

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;

/**
 * TODO(developer): Replace this variable with your Google Analytics 4
 *   property ID before running the sample.
 */
$property_id = 'YOUR-GA4-PROPERTY-ID';

// [START analyticsdata_initialize]
// Using a default constructor instructs the client to use the credentials
// specified in GOOGLE_APPLICATION_CREDENTIALS environment variable.
$client = new BetaAnalyticsDataClient();
// [END analyticsdata_initialize]

// [START analyticsdata_run_report]
// Make an API call.
$response = $client->runReport([
    'property' => 'properties/' . $property_id,
    'dateRanges' => [
        new DateRange([
            'start_date' => '2020-03-31',
            'end_date' => 'today',
        ]),
    ],
    'dimensions' => [new Dimension(
        [
            'name' => 'city',
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
// [END analyticsdata_run_report]

// [START analyticsdata_run_report_response]
// Print results of an API call.
print 'Report result: ' . PHP_EOL;

foreach ($response->getRows() as $row) {
    print $row->getDimensionValues()[0]->getValue()
        . ' ' . $row->getMetricValues()[0]->getValue() . PHP_EOL;
    // [END analyticsdata_run_report_response]
}
// [END analytics_data_quickstart]
