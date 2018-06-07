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

require_once __DIR__ . '/../vendor/autoload.php';

# [START report_error_grpc]
use Google\Cloud\ErrorReporting\V1beta1\ReportErrorsServiceClient;
use Google\Cloud\ErrorReporting\V1beta1\ErrorContext;
use Google\Cloud\ErrorReporting\V1beta1\ReportedErrorEvent;
use Google\Cloud\ErrorReporting\V1beta1\SourceLocation;

/**
 * Uncomment these line and replace with your project ID, message, and user.
 */
// $projectId = 'YOUR_PROJECT_ID';
// $message = 'This is the error message to report!';
// $user = 'optional@user.com';

$errors = new ReportErrorsServiceClient();
$projectName = $errors->projectName($projectId);

$location = (new SourceLocation())
    ->setFunctionName('global');

$context = (new ErrorContext())
    ->setReportLocation($location)
    ->setUser($user);

$event = (new ReportedErrorEvent())
    ->setMessage($message)
    ->setContext($context);

$errors->reportErrorEvent($projectName, $event);
# [END report_error_grpc]
print('Reported an exception to Stackdriver using gRPC' . PHP_EOL);
