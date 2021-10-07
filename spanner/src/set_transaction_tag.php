<?php
/**
 * Copyright 2021 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_set_transaction_tag]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Transaction;

/**
 * Executes a transaction with a transaction tag.
 * Example:
 * ```
 * spanner_set_transaction_tag($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function set_transaction_tag(string $instanceId, string $databaseId): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $database->runTransaction(function (Transaction $t) use ($spanner) {
        $t->executeUpdate(
            'UPDATE Venues SET Capacity = CAST(Capacity/4 AS INT64) WHERE OutdoorVenue = false',
            [
                'requestOptions' => ['requestTag' => 'app=concert,env=dev,action=update']
            ]
        );
        print('Venue capacities updated.' . PHP_EOL);
        $t->executeUpdate(
            'INSERT INTO Venues (VenueId, VenueName, Capacity, OutdoorVenue, LastUpdateTime) '
            . 'VALUES (@venueId, @venueName, @capacity, @outdoorVenue, PENDING_COMMIT_TIMESTAMP())',
            [
                'parameters' => [
                    'venueId' => 81,
                    'venueName' => 'Venue 81',
                    'capacity' => 1440,
                    'outdoorVenue' => true,
                ],
                'requestOptions' => ['requestTag' => 'app=concert,env=dev,action=insert']
            ]
        );
        print('New venue inserted.' . PHP_EOL);
        $t->commit();
    }, [
        'requestOptions' => ['transactionTag' => 'app=concert,env=dev']
    ]);
}
// [END spanner_set_transaction_tag]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
