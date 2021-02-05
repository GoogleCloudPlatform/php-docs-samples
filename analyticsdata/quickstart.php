<?php
// Copyright 2021 Google LLC
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

$property_id = 'YOUR-GA4-PROPERTY-ID';
// [START analytics_data_quickstart]

require 'vendor/autoload.php';

use Google\Analytics\Data\V1alpha\AlphaAnalyticsDataClient;
use Google\Analytics\Data\V1alpha\DateRange;
use Google\Analytics\Data\V1alpha\Dimension;
use Google\Analytics\Data\V1alpha\Entity;
use Google\Analytics\Data\V1alpha\Metric;

/**
 * TODO(developer): Uncomment this variable and replace with your GA4
 *   property ID before running the sample.
 */
// $property_id = 'YOUR-GA4-PROPERTY-ID';


// Make an API call.
$client = new AlphaAnalyticsDataClient();
$response = $client->runReport([
    'entity' => new Entity([
        'property_id' => $property_id
    ]),
    'dateRanges' => [
        new DateRange([
            'start_date' => '2020-03-31',
            'end_date' => 'today',
        ]),
    ],
    'dimensions' => [new Dimension(
        [
            'name' => 'city',
        ]),
    ],
    'metrics' => [new Metric(
        [
            'name' => 'activeUsers',
        ])
    ]
]);

// Print results of an API call.
print 'Report result: ' . PHP_EOL;

foreach ($response->getRows() as $row) {
    print $row->getDimensionValues()[0]->getValue()
        . ' ' . $row->getMetricValues()[0]->getValue() . PHP_EOL;
}


// [END analytics_data_quickstart]
