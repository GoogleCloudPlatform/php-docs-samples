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

// [START logging_delete_log]
use Google\Cloud\Logging\LoggingClient;

/** Delete a logger and all its entries.
 *
 * @param string $projectId The Google project ID.
 * @param string $loggerName The name of the logger.
 */
function delete_logger($projectId, $loggerName)
{
    $logging = new LoggingClient(['projectId' => $projectId]);
    $logger = $logging->logger($loggerName);
    $logger->delete();
    printf("Deleted a logger '%s'." . PHP_EOL, $loggerName);
}
// [END logging_delete_log]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
