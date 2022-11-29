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

// [START write_with_monolog_logger]
use Google\Cloud\Logging\LoggingClient;
use Monolog\Handler\PsrHandler;
use Monolog\Logger as MonologLogger;
use Psr\Log\LogLevel;

/**
 * Write a log message via the Stackdriver Logging API.
 *
 * @param string $projectId The Google project ID.
 * @param string $loggerName The name of the logger.
 * @param string $message The log message.
 * @param int    $level
 */
function write_with_monolog_logger(
    string $projectId,
    string $loggerName,
    string $message,
    string $level = LogLevel::WARNING
) {
    $logging = new LoggingClient([
        'projectId' => $projectId
    ]);
    $logger = $logging->psrLogger($loggerName);

    // Create a Monolog logger
    // NOTE: You can use an existing monolog client, i.e. when using Laravel or Symfony.
    $monolog = new MonologLogger($loggerName);

    // Push the Psr logger onto the logger using a Monolog PsrHandler.
    $handler = new PsrHandler($logger);
    $monolog->pushHandler($handler);

    // Log the message
    $monolog->log($level, $message);
    printf("Wrote to monolog logger '%s' at level '%s'." . PHP_EOL, $loggerName, $level);
}
// [END write_with_monolog_logger]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
