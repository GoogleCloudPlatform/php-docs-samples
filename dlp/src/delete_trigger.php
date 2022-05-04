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

/**
 * For instructions on how to run the samples:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/dlp/README.md
 */

namespace Google\Cloud\Samples\Dlp;

# [START dlp_delete_trigger]
use Google\Cloud\Dlp\V2\DlpServiceClient;

/**
 * Delete a Data Loss Prevention API job trigger.
 *
 * @param string $callingProjectId  The project ID to run the API call under
 * @param string $triggerId         The name of the trigger to be deleted.
 */
function delete_trigger(string $callingProjectId, string $triggerId): void
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Run request
    // The Parent project ID is automatically extracted from this parameter
    $triggerName = "projects/$callingProjectId/locations/global/jobTriggers/$triggerId";
    $response = $dlp->deleteJobTrigger($triggerName);

    // Print the results
    printf('Successfully deleted trigger %s' . PHP_EOL, $triggerName);
}
# [END dlp_delete_trigger]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
