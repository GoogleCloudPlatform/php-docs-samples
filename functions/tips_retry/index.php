<?php
/**
 * Copyright 2021 Google LLC.
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

// [START functions_tips_retry]

use Google\CloudFunctions\CloudEvent;

function tipsRetry(CloudEvent $event): void
{
    $cloudEventData = $event->getData();
    $pubSubData = $cloudEventData['message']['data'];

    $json = json_decode(base64_decode($pubSubData), true);

    // Determine whether to retry the invocation based on a parameter
    $tryAgain = $json['some_parameter'];

    if ($tryAgain) {
        /**
         * Functions with automatic retries enabled should throw exceptions to
         * indicate intermittent failures that a retry might fix. In this
         * case, a thrown exception will cause the original function
         * invocation to be re-sent.
         */
        throw new Exception('Intermittent failure occurred; retrying...');
    }

    /**
     * If a function with retries enabled encounters a non-retriable
     * failure, it should return *without* throwing an exception.
     */
    $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');
    fwrite($log, 'Not retrying' . PHP_EOL);
}
// [END functions_tips_retry]
