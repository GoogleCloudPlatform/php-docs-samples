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

// [START spanner_list_backups]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\ListBackupsRequest;

/**
 * List backups in an instance.
 * Example:
 * ```
 * list_backups($projectId, $instanceId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 */
function list_backups(string $projectId, string $instanceId): void
{
    $databaseAdminClient = new DatabaseAdminClient();
    $parent = DatabaseAdminClient::instanceName($projectId, $instanceId);

    // List all backups.
    print('All backups:' . PHP_EOL);
    $request = new ListBackupsRequest([
        'parent' => $parent
    ]);
    $backups = $databaseAdminClient->listBackups($request)->iterateAllElements();
    foreach ($backups as $backup) {
        print('  ' . basename($backup->getName()) . PHP_EOL);
    }

    // List all backups that contain a name.
    $backupName = 'backup-test-';
    print("All backups with name containing \"$backupName\":" . PHP_EOL);
    $filter = "name:$backupName";
    $request = new ListBackupsRequest([
        'parent' => $parent,
        'filter' => $filter
    ]);
    $backups = $databaseAdminClient->listBackups($request)->iterateAllElements();
    foreach ($backups as $backup) {
        print('  ' . basename($backup->getName()) . PHP_EOL);
    }

    // List all backups for a database that contains a name.
    $databaseId = 'test-';
    print("All backups for a database which name contains \"$databaseId\":" . PHP_EOL);
    $filter = "database:$databaseId";
    $request = new ListBackupsRequest([
        'parent' => $parent,
        'filter' => $filter
    ]);
    $backups = $databaseAdminClient->listBackups($request)->iterateAllElements();
    foreach ($backups as $backup) {
        print('  ' . basename($backup->getName()) . PHP_EOL);
    }

    // List all backups that expire before a timestamp.
    $expireTime = (new \DateTime('+30 days'))->format('c');
    print("All backups that expire before $expireTime:" . PHP_EOL);
    $filter = "expire_time < \"$expireTime\"";
    $request = new ListBackupsRequest([
        'parent' => $parent,
        'filter' => $filter
    ]);
    $backups = $databaseAdminClient->listBackups($request)->iterateAllElements();
    foreach ($backups as $backup) {
        print('  ' . basename($backup->getName()) . PHP_EOL);
    }

    // List all backups with a size greater than some bytes.
    $size = 500;
    print("All backups with size greater than $size bytes:" . PHP_EOL);
    $filter = "size_bytes > $size";
    $request = new ListBackupsRequest([
        'parent' => $parent,
        'filter' => $filter
    ]);
    $backups = $databaseAdminClient->listBackups($request)->iterateAllElements();
    foreach ($backups as $backup) {
        print('  ' . basename($backup->getName()) . PHP_EOL);
    }

    // List backups that were created after a timestamp that are also ready.
    $createTime = (new \DateTime('-1 day'))->format('c');
    print("All backups created after $createTime:" . PHP_EOL);
    $filter = "create_time >= \"$createTime\" AND state:READY";
    $request = new ListBackupsRequest([
        'parent' => $parent,
        'filter' => $filter
    ]);
    $backups = $databaseAdminClient->listBackups($request)->iterateAllElements();
    foreach ($backups as $backup) {
        print('  ' . basename($backup->getName()) . PHP_EOL);
    }

    // List backups with pagination.
    print('All backups with pagination:' . PHP_EOL);
    $request = new ListBackupsRequest([
        'parent' => $parent,
        'page_size' => 2
    ]);
    $pages = $databaseAdminClient->listBackups($request)->iteratePages();
    foreach ($pages as $pageNumber => $page) {
        print("All backups, page $pageNumber:" . PHP_EOL);
        foreach ($page as $backup) {
            print('  ' . basename($backup->getName()) . PHP_EOL);
        }
    }
}
// [END spanner_list_backups]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
