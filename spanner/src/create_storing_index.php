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
 *
 * For more information on this function:
 *
 * @see https://cloud.google.com/spanner/docs/secondary-indexes#storing_clause
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_create_storing_index]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\UpdateDatabaseDdlRequest;

/**
 * Adds an storing index to the example database.
 *
 * This sample uses the `MarketingBudget` column. You can add the column
 * by running the `add_column` sample or by running this DDL statement against
 * your database:
 *
 *     ALTER TABLE Albums ADD COLUMN MarketingBudget INT64
 *
 * Example:
 * ```
 * create_storing_index($projectId, $instanceId, $databaseId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function create_storing_index(string $projectId, string $instanceId, string $databaseId): void
{
    $databaseAdminClient = new DatabaseAdminClient();
    $databaseName = DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId);
    $statement = 'CREATE INDEX AlbumsByAlbumTitle2 ON Albums(AlbumTitle) ' .
        'STORING (MarketingBudget)';
    $request = new UpdateDatabaseDdlRequest([
        'database' => $databaseName,
        'statements' => [$statement]
    ]);

    $operation = $databaseAdminClient->updateDatabaseDdl($request);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Added the AlbumsByAlbumTitle2 index.' . PHP_EOL);
}
// [END spanner_create_storing_index]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
