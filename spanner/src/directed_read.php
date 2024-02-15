<?php
/**
 * Copyright 2024 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_directed_read]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\V1\DirectedReadOptions\ReplicaSelection\Type as ReplicaType;

/**
 * Queries sample data from the database with directed read options.
 * Example:
 * ```
 * directed_read($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function directed_read(string $instanceId, string $databaseId): void
{
    $directedReadOptionsForClient = [
        'directedReadOptions' => [
            'excludeReplicas' => [
                'replicaSelections' => [
                    [
                        'location' => 'us-east4'
                    ]
                ]
            ]
        ]
    ];

    $directedReadOptionsForRequest = [
        'directedReadOptions' => [
            'includeReplicas' => [
                'replicaSelections' => [
                    [
                        'type' => ReplicaType::READ_WRITE
                    ]
                ],
                'autoFailoverDisabled' => true
            ]
        ]
    ];

    $spanner = new SpannerClient($directedReadOptionsForClient);
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);
    $snapshot = $database->snapshot();

    // directedReadOptions at Request level will override the options set at
    // Client level
    $results = $snapshot->execute(
        'SELECT SingerId, AlbumId, AlbumTitle FROM Albums',
        $directedReadOptionsForRequest
    );

    foreach ($results as $row) {
        printf('SingerId: %s, AlbumId: %s, AlbumTitle: %s' . PHP_EOL,
            $row['SingerId'], $row['AlbumId'], $row['AlbumTitle']);
    }
}
// [END spanner_directed_read]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
