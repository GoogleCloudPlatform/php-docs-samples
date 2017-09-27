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

# [START error_reporting_exception]
use Exception;
use Google\Cloud\ErrorReporting\V1beta1\ReportErrorsServiceClient;
use Google\Devtools\Clouderrorreporting\V1beta1\ErrorContext;
use Google\Devtools\Clouderrorreporting\V1beta1\ReportedErrorEvent;
use Google\Devtools\Clouderrorreporting\V1beta1\SourceLocation;

/**
 * @param string $projectId
 * @param Exception $e The exception to log to stackdriver
 */
function report_exception($projectId, Exception $e)
{
    $errors = new ReportErrorsServiceClient();
    $projectName = $errors->formatProjectName($projectId);

    $event = new ReportedErrorEvent();
    $event->setMessage(sprintf('PHP Warning: %s', $e));

    $errors->reportErrorEvent($projectName, $event);
    print('Reported an exception to Stackdriver' . PHP_EOL);
}
# [END error_reporting_exception]

# [START register_exception_handler]
/**
 * All PHP exceptions will be logged to stackdriver
 * @param string $projectId
 */
function register_exception_handler($projectId)
{
    $handlerFunction = function (\Exception $e) use ($projectId) {
        // Format the exception for Stackdriver Error Reporting
        // @see __DIR__/report_exception.php
        $prev = report_exception($projectId, $e);
        printf('Caught Exception "%s"' . PHP_EOL, $e->getMessage());
    };

    // Sets PHP's default exception handler
    set_exception_handler($handlerFunction);
}
# [END register_exception_handler]
