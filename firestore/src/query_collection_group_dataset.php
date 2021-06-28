<?php
/**
 * Copyright 2020 Google LLC
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
 * Create example collection group for documents.
 *
 * @param string $projectId The Google Cloud Project ID
 */
function query_collection_group_dataset(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);

    # [START fs_collection_group_query_data_setup]
    # [START firestore_query_collection_group_dataset]
    $citiesRef = $db->collection('samples/php/cities');
    $citiesRef->document('SF')->collection('landmarks')->newDocument()->set([
        'name' => 'Golden Gate Bridge',
        'type' => 'bridge'
    ]);
    $citiesRef->document('SF')->collection('landmarks')->newDocument()->set([
        'name' => 'Legion of Honor',
        'type' => 'museum'
    ]);
    $citiesRef->document('LA')->collection('landmarks')->newDocument()->set([
        'name' => 'Griffith Park',
        'type' => 'park'
    ]);
    $citiesRef->document('LA')->collection('landmarks')->newDocument()->set([
        'name' => 'The Getty',
        'type' => 'museum'
    ]);
    $citiesRef->document('DC')->collection('landmarks')->newDocument()->set([
        'name' => 'Lincoln Memorial',
        'type' => 'memorial'
    ]);
    $citiesRef->document('DC')->collection('landmarks')->newDocument()->set([
        'name' => 'National Air and Space Museum',
        'type' => 'museum'
    ]);
    $citiesRef->document('TOK')->collection('landmarks')->newDocument()->set([
        'name' => 'Ueno Park',
        'type' => 'park'
    ]);
    $citiesRef->document('TOK')->collection('landmarks')->newDocument()->set([
        'name' => 'National Museum of Nature and Science',
        'type' => 'museum'
    ]);
    $citiesRef->document('BJ')->collection('landmarks')->newDocument()->set([
        'name' => 'Jingshan Park',
        'type' => 'park'
    ]);
    $citiesRef->document('BJ')->collection('landmarks')->newDocument()->set([
        'name' => 'Beijing Ancient Observatory',
        'type' => 'museum'
    ]);
    print('Added example landmarks collections to the cities collection.' . PHP_EOL);
    # [END firestore_query_collection_group_dataset]
    # [END fs_collection_group_query_data_setup]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
