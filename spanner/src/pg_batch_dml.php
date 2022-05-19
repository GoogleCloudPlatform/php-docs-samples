<?php
/**
 * Copyright 2022 Google Inc.
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

// [START spanner_postgresql_batch_dml]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Transaction;
use Google\Cloud\Spanner\Database;

/**
 * Execute a batch of DML statements on a Spanner PostgreSQL database
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function pg_batch_dml(string $instanceId, string $databaseId): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $sql = 'INSERT INTO Singers (SingerId, FirstName, LastName) VALUES ($1, $2, $3)';

    $database->runTransaction(function (Transaction $t) use ($sql) {
        $result = $t->executeUpdateBatch([
            [
                'sql' => $sql,
                'parameters' => [
                    'p1' => 1,
                    'p2' => 'Alice',
                    'p3' => 'Henderson',
                ],
                'types' => [
                    'p1' => Database::TYPE_INT64,
                    'p2' => Database::TYPE_STRING,
                    'p3' => Database::TYPE_STRING,
                ]
            ],
            [
                'sql' => $sql,
                'parameters' => [
                    'p1' => 2,
                    'p2' => 'Bruce',
                    'p3' => 'Allison',
                ],
                // you can omit types(provided the value isn't null)
            ]
        ]);
        $t->commit();

        if ($result->error()) {
            printf('An error occurred: %s' . PHP_EOL, $result->error()['status']['message']);
        } else {
            printf('Inserted %s singers using Batch DML.' . PHP_EOL, count($result->rowCounts()));
        }
    });
}
// [END spanner_postgresql_batch_dml]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
