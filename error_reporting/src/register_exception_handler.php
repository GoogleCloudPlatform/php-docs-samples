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

use Exception;
# [START register_exception_handler]
use Google\Cloud\ErrorReporting\Bootstrap;
use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Core\Report\SimpleMetadataProvider;
// use Google\Cloud\ErrorReporting\V1beta1\ReportErrorsServiceClient;
// use Google\Devtools\Clouderrorreporting\V1beta1\ReportedErrorEvent;

// Uncomment this line and replace with your project ID:
// $projectId = 'YOUR_PROJECT_ID';

// Sets PHP's default exception handler
// set_exception_handler(function (Exception $e) use ($projectId) {
//     // Format the Exception for Stackdriver Error Reporting
//     $event = new ReportedErrorEvent();
//     $event->setMessage(sprintf('PHP Warning: %s', $e));

//     // Report the event to stackdriver
//     $errors = new ReportErrorsServiceClient();
//     $errors->reportErrorEvent(
//         $errors->formatProjectName($projectId),
//         $event
//     );
//     print('An error has occurred.' . PHP_EOL);
// });
$service = 'error_reporting_quickstart';
$version = '1.0-dev';
$metadata = new SimpleMetadataProvider(null, $projectId, $service, $version);
$logging = new LoggingClient([
    'projectId' => $projectId,
]);
$psrLogger = $logging->psrLogger('error-log', [
    'metadataProvider' => $metadata,
]);
Bootstrap::init($psrLogger);
# [END register_exception_handler]
