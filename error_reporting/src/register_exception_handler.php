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

# [START register_exception_handler]
use Google\Cloud\ErrorReporting\Bootstrap;
use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Core\Report\SimpleMetadataProvider;

/**
 * Uncomment these line and replace with your project ID, service, and version. The service and
 * version are arbitrary, but are required for the logs to be flagged in Error Reporting.
 */
// $projectId = 'YOUR_PROJECT_ID';
// $service = 'ARBITRARY_SERVICE_NAME';
// $version = 'ARBITRARY_VERSION';

$metadata = new SimpleMetadataProvider([], $projectId, $service, $version);
$logging = new LoggingClient([
    'projectId' => $projectId,
]);
$psrLogger = $logging->psrLogger('error-log', [
    'metadataProvider' => $metadata,
]);
Bootstrap::init($psrLogger);
# [END register_exception_handler]
