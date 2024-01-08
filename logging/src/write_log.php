<?php
/**
 * Copyright 2016 Google Inc.
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

namespace Google\Cloud\Samples\Logging;

// [START logging_write_log_entry]
use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Logging\Logger;

/** Write a log message via the Stackdriver Logging API.
 *
 * @param string $projectId The Google project ID.
 * @param string $loggerName The name of the logger.
 * @param string $message The log message.
 */
function write_log($projectId, $loggerName, $message)
{
    $logging = new LoggingClient(['projectId' => $projectId]);
    $logger = $logging->logger($loggerName, [
        'resource' => [
            'type' => 'gcs_bucket',
            'labels' => [
                'bucket_name' => 'my_bucket'
            ]
        ]
    ]);
    $entry = $logger->entry($message, [
        'severity' => Logger::INFO
    ]);
    $logger->write($entry);
    printf("Wrote a log to a logger '%s'." . PHP_EOL, $loggerName);
}
// [END logging_write_log_entry]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
