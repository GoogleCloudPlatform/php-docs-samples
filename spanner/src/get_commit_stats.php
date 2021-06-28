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

// [START spanner_get_commit_stats]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Transaction;

/**
 * Creates a database and tables for sample data.
 * Example:
 * ```
 * create_database($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function get_commit_stats($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $commitStats = $database->runTransaction(function (Transaction $t) use ($spanner) {
        $t->updateBatch('Albums', [
            [
                'SingerId' => 1,
                'AlbumId' => 1,
                'MarketingBudget' => 200000,
            ],
            [
                'SingerId' => 2,
                'AlbumId' => 2,
                'MarketingBudget' => 400000,
            ]
        ]);
        $t->commit(['returnCommitStats' => true]);
        return $t->getCommitStats();
    });

    print('Updated data with ' . $commitStats['mutationCount'] . ' mutations.' . PHP_EOL);
}
// [END spanner_get_commit_stats]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
