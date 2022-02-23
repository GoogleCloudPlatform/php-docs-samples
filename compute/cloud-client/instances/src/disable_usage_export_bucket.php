<?php
/**
 * Copyright 2021 Google Inc.
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
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/compute/cloud-client/README.md
 */

namespace Google\Cloud\Samples\Compute;

# [START compute_usage_report_disable]
use Google\Cloud\Compute\V1\ProjectsClient;
use Google\Cloud\Compute\V1\Operation;

/**
 * Disable Compute Engine usage export bucket for the Cloud Project.
 *
 * @param string $projectId Your Google Cloud project ID.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function disable_usage_export_bucket(string $projectId)
{
    // Disable the usage export location by sending null as usageExportLocationResource.
    $projectsClient = new ProjectsClient();
    $operation = $projectsClient->setUsageExportBucket($projectId, null);

    // Wait for the operation to complete.
    $operation->pollUntilComplete();
    if ($operation->operationSucceeded()) {
        printf('Compute Engine usage export bucket for project `%s` was disabled.', $projectId);
    } else {
        $error = $operation->getError();
        printf('Failed to disable usage report bucket for project `%s`: %s' . PHP_EOL, $projectId, $error->getMessage());
    }
}
# [END compute_usage_report_disable]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
