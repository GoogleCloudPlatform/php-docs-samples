<?php
/**
 * Copyright 2026 Google Inc.
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

// [START spanner_batch_write_at_least_once]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Inserts sample data into the given database via BatchWrite API.
 * The database and table must already exist and can be created using `create_database`.
 *
 * Example:
 * ```
 * batch_write($projectId, $instanceId, $databaseId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function batch_write(string $projectId, string $instanceId, string $databaseId): void
{
    $spanner = new SpannerClient(['projectId' => $projectId]);
    $database = $spanner->instance($instanceId)->database($databaseId);

    // Create Mutation Groups
    // All mutations within a single group are applied atomically.
    // Mutations across groups are applied non-atomically.

    // Group 1: Single mutation
    $mutationGroup1 = $database->mutationGroup();
    $mutationGroup1->insertOrUpdate('Singers', [
        'SingerId' => 16,
        'FirstName' => 'Scarlet',
        'LastName' => 'Terry'
    ]);

    // Group 2: Multiple mutations
    $mutationGroup2 = $database->mutationGroup();
    $mutationGroup2->insertOrUpdateBatch('Singers', [
        ['SingerId' => 17, 'FirstName' => 'Marc'],
        ['SingerId' => 18, 'FirstName' => 'Catalina', 'LastName' => 'Smith']
    ]);
    $mutationGroup2->insertOrUpdateBatch('Albums', [
        ['SingerId' => 17, 'AlbumId' => 1, 'AlbumTitle' => 'Total Junk'],
        ['SingerId' => 18, 'AlbumId' => 2, 'AlbumTitle' => 'Go, Go, Go']
    ]);

    // Call batchWrite on the high-level Database client.
    // Equivalent to batchWriteAtLeastOnce in other languages.
    $responses = $database->batchWrite([$mutationGroup1, $mutationGroup2], [
        'requestOptions' => ['transactionTag' => 'batch-write-tag']
    ]);

    // Check the response code of each response to determine whether the mutation group(s) were applied successfully.
    // $responses is a Generator yielding V1\BatchWriteResponse items.
    // Check the response code of each response to determine whether the mutation group(s) were applied successfully.
    // $responses is a Generator yielding response arrays.
    foreach ($responses as $response) {
        $status = $response['status'];
        $indexes = implode(', ', $response['indexes']);
        if ($status['code'] === 0) {
            $timestamp = $response['commitTimestamp'] ?? 'Unknown';
            printf('Mutation group indexes [%s] have been applied with commit timestamp %s' . PHP_EOL,
                $indexes,
                $timestamp
            );
        } else {
            printf('Mutation group indexes [%s] could not be applied with error code %s and error message %s' . PHP_EOL,
                $indexes,
                $status['code'],
                $status['message']
            );
        }
    }
}
// [END spanner_batch_write_at_least_once]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
