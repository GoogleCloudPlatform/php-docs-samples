<?php
/**
 * Copyright 2024 Google Inc.
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

// [START spanner_create_sequence]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\UpdateDatabaseDdlRequest;
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Result;

/**
 * Creates a sequence.
 *
 * Example:
 * ```
 * create_sequence($projectId, $instanceId, $databaseId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function create_sequence(string $projectId, string $instanceId, string $databaseId): void
{
    $databaseAdminClient = new DatabaseAdminClient();
    $spanner = new SpannerClient();

    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);
    $databaseName = DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId);

    $request = new UpdateDatabaseDdlRequest([
        'database' => $databaseName,
        'statements' => [
            "CREATE SEQUENCE Seq OPTIONS (sequence_kind = 'bit_reversed_positive')",
            'CREATE TABLE Customers (CustomerId INT64 DEFAULT (GET_NEXT_SEQUENCE_VALUE(' .
            'Sequence Seq)), CustomerName STRING(1024)) PRIMARY KEY (CustomerId)'
        ]
    ]);

    $operation = $databaseAdminClient->updateDatabaseDdl($request);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf(
        'Created Seq sequence and Customers table, where ' .
        'the key column CustomerId uses the sequence as a default value' .
        PHP_EOL
    );

    $transaction = $database->transaction();
    $res = $transaction->execute(
        'INSERT INTO Customers (CustomerName) VALUES ' .
        "('Alice'), ('David'), ('Marc') THEN RETURN CustomerId"
    );
    $rows = $res->rows(Result::RETURN_ASSOCIATIVE);

    foreach ($rows as $row) {
        printf('Inserted customer record with CustomerId: %d %s',
            $row['CustomerId'],
            PHP_EOL
        );
    }
    $transaction->commit();

    printf(sprintf(
        'Number of customer records inserted is: %d %s',
        $res->stats()['rowCountExact'],
        PHP_EOL
    ));
}
// [END spanner_create_sequence]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
