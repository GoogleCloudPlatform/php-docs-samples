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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_alter_table_with_foreign_key_delete_cascade]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Alter table to add a foreign key delete cascade action.
 * Example:
 * ```
 * alter_table_with_foreign_key_delete_cascade($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function alter_table_with_foreign_key_delete_cascade(
    string $instanceId,
    string $databaseId
): void {
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $operation = $database->updateDdl(
        'ALTER TABLE ShoppingCarts
        ADD CONSTRAINT FKShoppingCartsCustomerName
        FOREIGN KEY (CustomerName)
        REFERENCES Customers(CustomerName)
        ON DELETE CASCADE'
    );

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf(sprintf(
        'Altered ShoppingCarts table with FKShoppingCartsCustomerName ' .
        'foreign key constraint on database %s on instance %s %s',
        $databaseId,
        $instanceId,
        PHP_EOL
    ));
}
// [END spanner_alter_table_with_foreign_key_delete_cascade]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
