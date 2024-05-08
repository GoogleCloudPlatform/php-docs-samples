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

// [START spanner_postgresql_create_sequence]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\UpdateDatabaseDdlRequest;
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Result;

/**
 * Creates a sequence.
 * Example:
 * ```
 * pg_create_sequence($projectId, $instanceId, $databaseId);
 * ```
 *
 * @param string $projectId The Google Cloud Project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function pg_create_sequence(
    string $projectId,
    string $instanceId,
    string $databaseId
): void {
    $databaseAdminClient = new DatabaseAdminClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);
    $transaction = $database->transaction();
    $operation = $databaseAdminClient->updateDatabaseDdl(new UpdateDatabaseDdlRequest([
        'database' => DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId),
        'statements' => [
            'CREATE SEQUENCE Seq BIT_REVERSED_POSITIVE',
            "CREATE TABLE Customers (
            CustomerId           BIGINT DEFAULT nextval('Seq'), 
            CustomerName         CHARACTER VARYING(1024), 
            PRIMARY KEY (CustomerId))"
        ]
    ]));

    print('Waiting for operation to complete ...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf(
        'Created Seq sequence and Customers table, where ' .
        'the key column CustomerId uses the sequence as a default value' .
        PHP_EOL
    );

    $res = $transaction->execute(
        'INSERT INTO Customers (CustomerName) VALUES ' .
        "('Alice'), ('David'), ('Marc') RETURNING CustomerId"
    );
    $rows = $res->rows(Result::RETURN_ASSOCIATIVE);

    foreach ($rows as $row) {
        printf('Inserted customer record with CustomerId: %d %s',
            $row['customerid'],
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
// [END spanner_postgresql_create_sequence]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
