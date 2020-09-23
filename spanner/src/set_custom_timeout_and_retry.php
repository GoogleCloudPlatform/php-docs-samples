<?php
/**
 * Copyright 2020 Google LLC.
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

// [START spanner_set_custom_timeout_and_retry]
use Google\ApiCore\ApiStatus;
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Database;

/**
 * Set custom timeout and retry settings.
 * Example:
 * ```
 * set_custom_timeout_and_retry($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function set_custom_timeout_and_retry($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $database->runTransaction(function (Transaction $t) use ($spanner) {
        $rowCount = $t->executeUpdate(
            "INSERT Singers (SingerId, FirstName, LastName) "
            . " VALUES (10, 'Virginia', 'Watson')",
            [
                'retrySettings' => [
                    'initialRetryDelayMillis' => 500,
                    'retryDelayMultiplier' => 1.5,
                    'maxRetryDelayMillis' => 64000,
                    'retryableCodes' => [ApiStatus::DEADLINE_EXCEEDED, ApiStatus::UNAVAILABLE],
                ],
                'timeoutMillis' => 60000,
            ]);
        $t->commit();
        printf('Inserted %d row(s).' . PHP_EOL, $rowCount);
    });
}
// [END spanner_set_custom_timeout_and_retry]
