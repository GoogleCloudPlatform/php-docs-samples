<?php

/**
 * Copyright 2025 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/storage/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START storage_configure_retries]
use Google\Cloud\Storage\StorageClient;

/**
 * Configures retries with customizations.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 */
function configure_retries(string $bucketName): void
{
    $storage = new StorageClient([
        // The maximum number of automatic retries attempted before returning
        // the error.
        // Default: 3
        'retries' => 10,

        // Exponential backoff settings
        // Retry strategy to signify that we never want to retry an operation
        // even if the error is retryable.
        // Default: StorageClient::RETRY_IDEMPOTENT
        'retryStrategy' => StorageClient::RETRY_ALWAYS,

        // Executes a delay
        // Defaults to utilizing `usleep`.
        // Function signature should match: `function (int $delay) : void`.
        // This function is mostly used internally, so the tests don't wait 
        // the time of the delay to run.
        'restDelayFunction' => function ($delay) {
            usleep($delay);
        },

        // Sets the conditions for determining how long to wait between attempts to retry.
        // Function signature should match: `function (int $attempt) : int`.
        // Allows to change the initial retry delay, retry delay multiplier and maximum retry delay.
        'restCalcDelayFunction' => fn ($attempt) => ($attempt + 1) * 100,

        // Sets the conditions for whether or not a request should attempt to retry. 
        // Function signature should match: `function (\Exception $ex) : bool`.
        'restRetryFunction' => function (\Exception $e) {
            // Custom logic: ex. only retry if the error code is 404.
            return $e->getCode() === 404;
        },

        // Runs after the restRetryFunction. This might be used to simply consume the 
        // exception and $arguments b/w retries. This returns the new $arguments thus allowing 
        // modification on demand for $arguments. For ex: changing the headers in b/w retries.
        'restRetryListener' => function (\Exception $e, $retryAttempt, &$arguments) {
            // logic
        },
    ]);
    $bucket = $storage->bucket($bucketName);
    $operationRetriesOverrides = [
        // The maximum number of automatic retries attempted before returning
        // the error.
        // Default: 3
        'retries' => 10,

        // Exponential backoff settings
        // Retry strategy to signify that we never want to retry an operation
        // even if the error is retryable.
        // Default: StorageClient::RETRY_IDEMPOTENT
        'retryStrategy' => StorageClient::RETRY_ALWAYS,

        // Executes a delay
        // Defaults to utilizing `usleep`.
        // Function signature should match: `function (int $delay) : void`.
        // This function is mostly used internally, so the tests don't wait 
        // the time of the delay to run.
        'restDelayFunction' => function ($delay) {
            usleep($delay);
        },

        // Sets the conditions for determining how long to wait between attempts to retry.
        // Function signature should match: `function (int $attempt) : int`.
        // Allows to change the initial retry delay, retry delay multiplier and maximum retry delay.
        'restCalcDelayFunction' => fn ($attempt) => ($attempt + 1) * 100,

        // Sets the conditions for whether or not a request should attempt to retry. 
        // Function signature should match: `function (\Exception $ex) : bool`.
        'restRetryFunction' => function (\Exception $e) {
            // Custom logic: ex. only retry if the error code is 404.
            return $e->getCode() === 404;
        },

        // Runs after the restRetryFunction. This might be used to simply consume the 
        // exception and $arguments b/w retries. This returns the new $arguments thus allowing 
        // modification on demand for $arguments. For ex: changing the headers in b/w retries.
        'restRetryListener' => function (\Exception $e, $retryAttempt, &$arguments) {
            // logic
        },
    ];
    foreach ($bucket->objects($operationRetriesOverrides) as $object) {
        printf('Object: %s' . PHP_EOL, $object->name());
    }
}
# [END storage_configure_retries]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
