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

// [START spanner_postgresql_case_sensitivity]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\UpdateDatabaseDdlRequest;

/**
 * Create a table with case-sensitive and case-folded columns for
 * a Spanner PostgreSQL database
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $table The name of the table to create, default to Singers.
 */
function pg_case_sensitivity(string $projectId, string $instanceId, string $databaseId, string $table = 'Singers'): void
{
    $databaseAdminClient = new DatabaseAdminClient();
    $databaseName = DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId);
    $ddl = sprintf(
        'CREATE TABLE %s (
            -- SingerId will be translated to "singerid"
            SingerId  bigint NOT NULL PRIMARY KEY,
            -- FirstName and LastName are double-quoted and will therefore
            -- retain their mixed case and are case-sensitive. This means that any statement that
            -- compares any of these columns must use double quotes.
            "FirstName" varchar(1024) NOT NULL,
            "LastName"  varchar(1024) NOT NULL
        )',
        $table
    );
    $request = new UpdateDatabaseDdlRequest([
        'database' => $databaseName,
        'statements' => [$ddl]
    ]);

    $operation = $databaseAdminClient->updateDatabaseDdl($request);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf(
        'Created %s table in database %s on instance %s' . PHP_EOL,
        $tableName,
        $databaseId,
        $instanceId
    );
}
// [END spanner_postgresql_case_sensitivity]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
