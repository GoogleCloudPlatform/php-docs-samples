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

// [START spanner_field_access_on_nested_struct_parameters]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Database;
use Google\Cloud\Spanner\StructType;
use Google\Cloud\Spanner\StructValue;
use Google\Cloud\Spanner\ArrayType;

/**
 * Queries sample data from the database using a nested struct field value.
 * Example:
 * ```
 * query_data_with_nested_struct_field($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function query_data_with_nested_struct_field($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $nameType = new ArrayType(
       (new StructType)
            ->add('FirstName', Database::TYPE_STRING)
            ->add('LastName', Database::TYPE_STRING)
    );
    $songInfoType = (new StructType)
        ->add('SongName', Database::TYPE_STRING)
        ->add('ArtistNames', $nameType);
    $nameStructValue1 = (new StructValue)
        ->add('FirstName', 'Elena')
        ->add('LastName', 'Campbell');
    $nameStructValue2 = (new StructValue)
        ->add('FirstName', 'Hannah')
        ->add('LastName', 'Harris');
    $songInfoValues = (new StructValue)
        ->add('SongName', 'Imagination')
        ->add('ArtistNames', [$nameStructValue1, $nameStructValue2]);
    $results = $database->execute(
        'SELECT SingerId, @song_info.SongName FROM Singers ' .
        'WHERE STRUCT<FirstName STRING, LastName STRING>(FirstName, LastName) ' .
        'IN UNNEST(@song_info.ArtistNames)',
        [
            'parameters' => [
                'song_info' => $songInfoValues
            ],
            'types' => [
                'song_info' => $songInfoType
            ]
        ]
    );
    foreach ($results as $row) {
        printf('SingerId: %s SongName: %s' . PHP_EOL,
            $row['SingerId'], $row['SongName']);
    }
}
// [END spanner_field_access_on_nested_struct_parameters]
