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

// [START functions_tips_infinite_retries]

/**
 * This function shows an example method for avoiding infinite retries in
 * Google Cloud Functions. By default, functions configured to automatically
 * retry execution on failure will be retried indefinitely - causing an
 * infinite loop. To avoid this, we stop retrying executions (by not throwing
 * exceptions) for any events that are older than a predefined threshold.
 */

use Google\CloudFunctions\CloudEvent;

function avoidInfiniteRetries(CloudEvent $event): void
{
    $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');

    $eventId = $event->getId();

    // The maximum age of events to process.
    $maxAge = 10; // 10 seconds

    // The age of the event being processed.
    $eventAge = time() - strtotime($event->getTime());

    // Ignore events that are too old
    if ($eventAge > $maxAge) {
        fwrite($log, 'Dropping event ' . $eventId . ' with age ' . $eventAge . ' seconds' . PHP_EOL);
        return;
    }

    // Do what the function is supposed to do
    fwrite($log, 'Processing event: ' . $eventId . ' with age ' . $eventAge . ' seconds' . PHP_EOL);

    // infinite_retries failed function executions
    $failed = true;
    if ($failed) {
        throw new Exception('Event ' . $eventId . ' failed; retrying...');
    }
}
// [END functions_tips_infinite_retries]
