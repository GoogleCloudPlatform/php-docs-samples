<?php
/**
 * Copyright 2023 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/bigquery/api/README.md
 */

namespace Google\Cloud\Samples\BigQuery;

# [START bigquery_undelete_table]
use Google\Cloud\BigQuery\BigQueryClient;

/**
 * Restore a deleted table from its snapshot.
 *
 * @param string $projectId The project Id of your Google Cloud Project.
 * @param string $datasetId The BigQuery dataset ID.
 * @param string $tableId Table ID of the table to delete.
 * @param string $restoredTableId Table Id for the restored table.
 */
function undelete_table(
    string $projectId,
    string $datasetId,
    string $tableId,
    string $restoredTableId
): void {
    $bigQuery = new BigQueryClient(['projectId' => $projectId]);
    $dataset = $bigQuery->dataset($datasetId);

    // Choose an appropriate snapshot point as epoch milliseconds.
    // For this example, we choose the current time as we're about to delete the
    // table immediately afterwards
    $snapshotEpoch = date_create()->format('Uv');

    // Delete the table.
    $dataset->table($tableId)->delete();

    // Construct the restore-from table ID using a snapshot decorator.
    $snapshotId = "{$tableId}@{$snapshotEpoch}";

    // Restore the deleted table
    $restoredTable = $dataset->table($restoredTableId);
    $copyConfig = $dataset->table($snapshotId)->copy($restoredTable);
    $job = $bigQuery->runJob($copyConfig);

    // check if the job is complete
    $job->reload();
    if (!$job->isComplete()) {
        throw new \Exception('Job has not yet completed', 500);
    }
    // check if the job has errors
    if (isset($job->info()['status']['errorResult'])) {
        $error = $job->info()['status']['errorResult']['message'];
        printf('Error running job: %s' . PHP_EOL, $error);
    } else {
        print('Snapshot restored successfully' . PHP_EOL);
    }
}
# [END bigquery_undelete_table]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
