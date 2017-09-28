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

# [START report_error_grpc]
use Exception;
use Google\Cloud\ErrorReporting\V1beta1\ReportErrorsServiceClient;
use Google\Devtools\Clouderrorreporting\V1beta1\ErrorContext;
use Google\Devtools\Clouderrorreporting\V1beta1\ReportedErrorEvent;
use Google\Devtools\Clouderrorreporting\V1beta1\SourceLocation;

/**
 * @param string $projectId
 * @param string $message The optional error message
 * @param string $user optional user identifier to attach to the error.
 */
function report_error_grpc($projectId, $message = 'My Error Message', $user = 'some@user.com')
{
    $errors = new ReportErrorsServiceClient();
    $projectName = $errors->formatProjectName($projectId);

    $location = (new SourceLocation())
        ->setFunctionName(__FUNCTION__);

    $context = (new ErrorContext())
        ->setReportLocation($location)
        ->setUser($user);

    $event = (new ReportedErrorEvent())
        ->setMessage(sprintf($message))
        ->setContext($context);

    $errors->reportErrorEvent($projectName, $event);
    print('Reported an exception to Stackdriver using gRPC' . PHP_EOL);
}
# [END report_error_grpc]
