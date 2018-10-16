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
 * ```
 * query_create_examples('your-project-id');
 * ```
 */
function query_create_examples($projectId)
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_query_create_examples]
    $citiesRef = $db->collection('cities');
    $citiesRef->document('SF')->set([
        'name' => 'San Francisco',
        'state' => 'CA',
        'country' => 'USA',
        'capital' => false,
        'population' => 860000,
        'regions' => ['west_coast', 'norcal']
    ]);
    $citiesRef->document('LA')->set([
        'name' => 'Los Angeles',
        'state' => 'CA',
        'country' => 'USA',
        'capital' => false,
        'population' => 3900000,
        'regions' => ['west_coast', 'socal']
    ]);
    $citiesRef->document('DC')->set([
        'name' => 'Washington D.C.',
        'state' => null,
        'country' => 'USA',
        'capital' => true,
        'population' => 680000,
        'regions' => ['east_coast']
    ]);
    $citiesRef->document('TOK')->set([
        'name' => 'Tokyo',
        'state' => null,
        'country' => 'Japan',
        'capital' => true,
        'population' => 9000000,
        'regions' => ['kanto', 'honshu']
    ]);
    $citiesRef->document('BJ')->set([
        'name' => 'Beijing',
        'state' => null,
        'country' => 'China',
        'capital' => true,
        'population' => 21500000,
        'regions' => ['jingjinji', 'hebei']
    ]);
    printf('Added example cities data to the cities collection.' . PHP_EOL);
    # [END fs_query_create_examples]
}
