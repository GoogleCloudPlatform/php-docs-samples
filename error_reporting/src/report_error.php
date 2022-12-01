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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/error_reporting/README.md
 */

namespace Google\Cloud\Samples\ErrorReporting;

# [START report_error]
use Google\Cloud\ErrorReporting\V1beta1\ReportErrorsServiceClient;
use Google\Cloud\ErrorReporting\V1beta1\ErrorContext;
use Google\Cloud\ErrorReporting\V1beta1\ReportedErrorEvent;
use Google\Cloud\ErrorReporting\V1beta1\SourceLocation;

/**
 * This sample shows how to report an error by creating a ReportedErrorEvent.
 * The ReportedErrorEvent object gives you more control over how the error
 * appears and the details associated with it.
 *
 * @param string $projectId Your Google Cloud Project ID.
 * @param string $message   The error message to report.
 * @param string $user      Optional user email address
 */
function report_error(string $projectId, string $message, string $user = '')
{
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
    print('Reported an exception to Stackdriver' . PHP_EOL);
}
# [END report_error]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
