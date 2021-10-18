<?php
/**
 * Copyright 2021 Google Inc.
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

// [START spanner_update_database_with_default_leader]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Updates the default leader of the database.
 * Example:
 * ```
 * update_database_with_default_leader($instanceId, $databaseId, $defaultLeader);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $defaultLeader The leader instance configuration used by default.
 */
function update_database_with_default_leader($instanceId, $databaseId, $defaultLeader)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $database->updateDdl(
        "ALTER DATABASE `$databaseId` SET OPTIONS (default_leader = '$defaultLeader')");

    printf('Updated the default leader to %d' . PHP_EOL, $database->info()['defaultLeader']);
}
// [END spanner_update_database_with_default_leader]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
