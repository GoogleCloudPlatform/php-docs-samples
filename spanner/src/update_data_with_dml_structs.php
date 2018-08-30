<?php
/**
 * Copyright 2018 Google LLC
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

// [START spanner_dml_structs]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Database;
use Google\Cloud\Spanner\Transaction;
use Google\Cloud\Spanner\StructType;
use Google\Cloud\Spanner\StructValue;

/**
 * Update data with a DML statement using Structs.
 *
 * The database and table must already exist and can be created using
 * `create_database`.
 * Example:
 * ```
 * insert_data($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function update_data_with_dml_structs($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $database->runTransaction(function (Transaction $t) use ($spanner) {
        $nameValue = (new StructValue)
            ->add('FirstName', 'Timothy')
            ->add('LastName', 'Campbell');
        $nameType = (new StructType)
            ->add('FirstName', Database::TYPE_STRING)
            ->add('LastName', Database::TYPE_STRING);

        $rowCount = $t->executeUpdate(
            "UPDATE Singers SET LastName = 'Grant' "
             . "WHERE STRUCT<FirstName STRING, LastName STRING>(FirstName, LastName) "
             . "= @name",
            [
                'parameters' => [
                    'name' => $nameValue
                ],
                'types' => [
                    'name' => $nameType
                ]
            ]);
        $t->commit();
        printf('Updated %d row(s).' . PHP_EOL, $rowCount);
    });
}
// [END spanner_dml_structs]
