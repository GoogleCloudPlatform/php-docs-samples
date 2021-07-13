<?php
/**
 * Copyright 2018 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/firestore/README.md
 */

namespace Google\Cloud\Samples\Firestore;

use Google\Cloud\Firestore\FirestoreClient;

/**
 * Create an example collection of documents.
 *
 * @param string $projectId The Google Cloud Project ID
 */
function data_get_dataset(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_retrieve_create_examples]
    # [START firestore_data_get_dataset]
    $citiesRef = $db->collection('samples/php/cities');
    $citiesRef->document('SF')->set([
        'name' => 'San Francisco',
        'state' => 'CA',
        'country' => 'USA',
        'capital' => false,
        'population' => 860000
    ]);
    $citiesRef->document('LA')->set([
        'name' => 'Los Angeles',
        'state' => 'CA',
        'country' => 'USA',
        'capital' => false,
        'population' => 3900000
    ]);
    $citiesRef->document('DC')->set([
        'name' => 'Washington D.C.',
        'state' => null,
        'country' => 'USA',
        'capital' => true,
        'population' => 680000
    ]);
    $citiesRef->document('TOK')->set([
        'name' => 'Tokyo',
        'state' => null,
        'country' => 'Japan',
        'capital' => true,
        'population' => 9000000
    ]);
    $citiesRef->document('BJ')->set([
        'name' => 'Beijing',
        'state' => null,
        'country' => 'China',
        'capital' => true,
        'population' => 21500000
    ]);
    printf('Added example cities data to the cities collection.' . PHP_EOL);
    # [END firestore_data_get_dataset]
    # [END fs_retrieve_create_examples]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
