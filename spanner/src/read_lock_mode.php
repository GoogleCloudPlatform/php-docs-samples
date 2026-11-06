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

// [START spanner_read_lock_mode]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Transaction;
use Google\Cloud\Spanner\V1\TransactionOptions\ReadWrite\ReadLockMode;

/**
 * Shows how to run a Read Write transaction with read lock mode options.
 *
 * Example:
 * ```
 * read_lock_mode($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function read_lock_mode(string $instanceId, string $databaseId): void
{
    // The read lock mode specified at the client-level will be applied to all
    // RW transactions.
    $spanner = new SpannerClient([
        'readLockMode' => ReadLockMode::OPTIMISTIC
    ]);
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    // The read lock mode specified at the request level takes precedence over
    // the read lock mode configured at the client level.
    $database->runTransaction(function (Transaction $t) {
        // Read an AlbumTitle.
        $results = $t->execute('SELECT AlbumTitle from Albums WHERE SingerId = 2 and AlbumId = 1');
        foreach ($results as $row) {
            printf('Current Album Title: %s' . PHP_EOL, $row['AlbumTitle']);
        }

        // Update the AlbumTitle.
        $rowCount = $t->executeUpdate('UPDATE Albums SET AlbumTitle = \'A New Title\' WHERE SingerId = 2 and AlbumId = 1');

        // Commit the transaction!
        $t->commit();

        printf('%d record(s) updated.' . PHP_EOL, $rowCount);
    }, [
        'transactionOptions' => [
            'readLockMode' => ReadLockMode::PESSIMISTIC
        ]
    ]);
}
// [END spanner_read_lock_mode]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
