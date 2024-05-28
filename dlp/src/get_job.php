<?php

/**
 * Copyright 2023 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/dlp/README.md
 */

namespace Google\Cloud\Samples\Dlp;

# [START dlp_get_job]
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\GetDlpJobRequest;

/**
 * Get DLP inspection job.
 * @param string $jobName           Dlp job name
 */
function get_job(
    string $jobName
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();
    try {
        // Send the get job request
        $getDlpJobRequest = (new GetDlpJobRequest())
            ->setName($jobName);
        $response = $dlp->getDlpJob($getDlpJobRequest);
        printf('Job %s status: %s' . PHP_EOL, $response->getName(), $response->getState());
    } finally {
        $dlp->close();
    }
}
# [END dlp_get_job]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
