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
use Google\Cloud\ErrorReporting\V1beta1\ReportErrorsServiceClient;
use Google\Devtools\Clouderrorreporting\V1beta1\ErrorContext;
use Google\Devtools\Clouderrorreporting\V1beta1\ReportedErrorEvent;
use Google\Devtools\Clouderrorreporting\V1beta1\SourceLocation;

function report_error_manually($projectId, $message = 'My Error Message', $user = 'some@user.com')
{
    $errors = new ReportErrorsServiceClient();
    $formattedProjectName = ReportErrorsServiceClient::formatProjectName($projectId);

    $location = new SourceLocation();
    $location->setFunctionName('report_error_manually');

    $context = new ErrorContext();
    $context->setUser($user);
    $context->setReportLocation($location);

    $event = new ReportedErrorEvent();
    $event->setMessage($message);
    $event->setContext($context);

    $errors->reportErrorEvent($formattedProjectName, $event);
    print('Logged an error to Stackdriver' . PHP_EOL);
}
# [END error_reporting_manual]
