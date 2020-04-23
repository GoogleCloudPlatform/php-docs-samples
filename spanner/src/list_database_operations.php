<?php
/**
 * Copyright 2020 Google Inc.
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

// [START spanner_list_database_operations]
use Google\Cloud\Spanner\SpannerClient;

/**
 * List all optimize restored database operations in an instance.
 * Example:
 * ```
 * list_database_operations($instanceId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 */
function list_database_operations($instanceId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);

    // List the databases that are being optimized after a restore operation.
    $filter = "(metadata.@type:type.googleapis.com/" .
              "google.spanner.admin.database.v1.OptimizeRestoredDatabaseMetadata)";

    $operations = $instance->databaseOperations(['filter' => $filter]);

    foreach ($operations as $operation) {
        if (!$operation->done()) {
            $meta = $operation->info()['metadata'];
            $dbName = basename($meta['name']);
            $progress = $meta['progress']['progressPercent'];
            printf('Database %s restored from backup is %d%% optimized.' . PHP_EOL, $dbName, $progress);
        }
    }
}
// [END spanner_list_database_operations]
