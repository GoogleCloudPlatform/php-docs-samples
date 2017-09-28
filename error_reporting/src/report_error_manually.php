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

# [START error_reporting_manual]
use Google\Cloud\Logging\LoggingClient;

function report_error_manually($projectId, $message = 'My Error Message', $user = 'some@user.com')
{
    $logging = new LoggingClient([
        'projectId' => $projectId
    ]);

    // The name of the log to write to
    $logName = 'my-log';

    // Selects the log to write to
    $logger = $logging->logger($logName);

    // Log a custom error entry by populating "reportLocation.functionName", "serviceContext.module"
    // and "serviceContext.version"
    $entry = $logger->entry([
        'message' => $message,
        'serviceContext' => [
            'service' => 'service',
            'version' => 'version'
        ],
        'context' => [
            'reportLocation' => [
                'functionName' => __FUNCTION__,
            ],
            'user' => $user
        ]
    ]);

    // Writes the log entry
    $logger->write($entry);
    print('Reported an error to Stackdriver' . PHP_EOL);
}
# [END error_reporting_manual]
