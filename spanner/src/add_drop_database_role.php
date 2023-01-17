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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_add_and_drop_database_role]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Adds and drops roles to the Singers table in the example database.
 * Example:
 * ```
 * add_drop_database_role($instanceId, $databaseId, $databaseRole);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $databaseRole The database role.
 */
function add_drop_database_role(string $instanceId, string $databaseId, string $databaseRole): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $roleParent = $databaseRole;
    $roleChild = 'new_child';

    $operation = $database->updateDdlBatch([
        sprintf('CREATE ROLE %s', $roleParent),
        sprintf('GRANT SELECT ON TABLE Singers TO ROLE %s', $roleParent),
        sprintf('CREATE ROLE %s', $roleChild),
        sprintf('GRANT ROLE %s TO ROLE %s', $roleParent, $roleChild)
    ]);

    printf('Waiting for create role and grant operation to complete... %s', PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created roles %s and %s and granted privileges %s', $roleParent, $roleChild, PHP_EOL);

    $operation = $database->updateDdlBatch([
        sprintf('REVOKE ROLE %s FROM ROLE %s', $roleParent, $roleChild),
        sprintf('DROP ROLE %s', $roleChild)
    ]);

    printf('Waiting for revoke role and drop role operation to complete... %s', PHP_EOL);
    $operation->pollUntilComplete();

    printf('Revoked privileges and dropped role %s %s', $roleChild, PHP_EOL);
}
// [END spanner_add_and_drop_database_role]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
