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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_add_and_drop_database_role]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Adds and drops roles to the Singers table in the example database.
 * Example:
 * ```
 * add_and_drop_database_role($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function add_and_drop_database_role($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $role_parent = 'new_parent';
    $role_child = 'new_child';

    $operation = $database->updateDdlBatch([
        'CREATE ROLE ' . $role_parent,
        'GRANT SELECT ON TABLE Singers TO ROLE ' . $role_parent,
        'CREATE ROLE ' . $role_child,
        'GRANT ROLE ' . $role_parent . ' TO ROLE ' . $role_child
    ]);

    print('Waiting for create role and grant operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created roles ' . $role_parent . ' and ' . $role_child . ' and granted privileges' . PHP_EOL);

    $operation = $database->updateDdlBatch([
        'REVOKE ROLE ' . $role_parent . ' FROM ROLE ' . $role_child,
        'DROP ROLE ' . $role_child,
        'REVOKE SELECT ON TABLE Singers FROM ROLE ' . $role_parent,
        'DROP ROLE ' . $role_parent
    ]);

    print('Waiting for revoke role and drop role operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Revoked privileges and dropped roles ' . $role_child . ' and ' . $role_parent . PHP_EOL);
}
// [END spanner_add_and_drop_database_role]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
