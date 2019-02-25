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
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Logging\LoggingClient;

// Create a PSR-3-Compatible logger
$logger = LoggingClient::psrBatchLogger('app');

// Log messages with varying log levels.
$logger->info('This will show up as log level INFO');
$logger->warning('This will show up as log level WARNING');
$logger->error('This will show up as log level ERROR');

?>

Logged INFO, WARNING, and ERROR log levels. Visit console.cloud.google.com/logs to see them
