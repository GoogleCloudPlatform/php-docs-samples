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

// [START write_with_psr_logger]
use Google\Cloud\Logging\LoggingClient;
use Psr\Log\LogLevel;

/** Write a log message via the Stackdriver Logging API.
 *
 * @param string $projectId The Google project ID.
 * @param string $loggerName The name of the logger.
 * @param string $message The log message.
 */
function write_with_psr_logger($projectId, $loggerName, $message, $level = LogLevel::WARNING)
{
    $logging = new LoggingClient(['projectId' => $projectId]);
    $logger = $logging->psrLogger($loggerName);
    $logger->log($level, $message);
    printf("Wrote to PSR logger '%s' at level '%s'." . PHP_EOL, $loggerName, $level);
}
// [END write_with_psr_logger]
