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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_batch_client]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Queries sample data from the database using SQL.
 * Example:
 * ```
 * batch_query_data($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function batch_query_data($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $batch = $spanner->batch($instanceId, $databaseId);
    $snapshot = $batch->snapshot();
    $queryString = 'SELECT SingerId, FirstName, LastName FROM Singers';
    $partitions = $snapshot->partitionQuery($queryString);
    $totalPartitions = count($partitions);
    $totalRecords = 0;
    foreach ($partitions as $partition) {
        $result = $snapshot->executePartition($partition);
        $rows = $result->rows();
        foreach ($rows as $row) {
            $singerId = $row['SingerId'];
            $firstName = $row['FirstName'];
            $lastName = $row['LastName'];
            printf('SingerId: %s, FirstName: %s, LastName: %s' . PHP_EOL, $singerId, $firstName, $lastName);
            $totalRecords++;
        }
    }
    printf('Total Partitions: %d' . PHP_EOL, $totalPartitions);
    printf('Total Records: %d' . PHP_EOL, $totalRecords);
    $averageRecordsPerPartition = $totalRecords / $totalPartitions;
    printf('Average Records Per Partition: %f' . PHP_EOL, $averageRecordsPerPartition);
}
// [END spanner_batch_client]
