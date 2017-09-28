<?php
/**
 * Copyright 2017 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\ErrorReporting;

# [START report_error_manually]
use Google\Cloud\Logging\LoggingClient;

/**
 * Uncomment these line and replace with your project ID, message, and user.
 * User is optional. Service and version are not optional, but can be any string.
 */
// $projectId = 'YOUR_PROJECT_ID';
// $message = 'This is the error message to report!';
// $user = 'optional@user.com';
// $service = 'ARBITRARY_SERVICE_NAME';
// $version = 'ARBITRARY_VERSION';

$logging = new LoggingClient([
    'projectId' => $projectId
]);

// Selects the log to write to. The log name "error-log" can be any string.
$logger = $logging->logger('error-log');

// Log a custom error entry by populating "reportLocation.functionName", "serviceContext.module"
// and "serviceContext.version"
$entry = $logger->entry([
    'message' => $message,
    'serviceContext' => [
        'service' => $service,
        'version' => $version,
    ],
    'context' => [
        'reportLocation' => [
            'functionName' => 'global',
        ],
        'user' => $user
    ]
]);

// Writes the log entry
$logger->write($entry);
# [END report_error_manually]
print('Reported an error to Stackdriver' . PHP_EOL);
