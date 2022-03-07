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

# [START compute_usage_report_get]
use Google\Cloud\Compute\V1\ProjectsClient;

/**
 * Retrieve Compute Engine usage export bucket for the Cloud project.
 * Replaces the empty value returned by the API with the default value used
 * to generate report file names.
 *
 * @param string $projectId Your Google Cloud project ID.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function get_usage_export_bucket(string $projectId)
{
    // Get the usage export location for the project from the server.
    $projectsClient = new ProjectsClient();
    $projectResponse = $projectsClient->get($projectId);

    // Replace the empty value returned by the API with the default value used to generate report file names.
    if ($projectResponse->hasUsageExportLocation()) {
        $responseUsageExportLocation = $projectResponse->getUsageExportLocation();

        // Verify that the server explicitly sent the optional field.
        if ($responseUsageExportLocation->hasReportNamePrefix()) {
            if ($responseUsageExportLocation->getReportNamePrefix() == '') {
                // Although the server explicitly sent the empty string value, the next usage
                // report generated with these settings still has the default prefix value "usage_gce".
                // See https://cloud.google.com/compute/docs/reference/rest/v1/projects/get
                print('Report name prefix not set, replacing with default value of `usage_gce`.' . PHP_EOL);
                $responseUsageExportLocation->setReportNamePrefix('usage_gce');
            }
        }

        printf(
            'Compute Engine usage export bucket for project `%s` is bucket_name = `%s` with ' .
            'report_name_prefix = `%s`.' . PHP_EOL,
            $projectId,
            $responseUsageExportLocation->getBucketName(),
            $responseUsageExportLocation->getReportNamePrefix()
        );
    } else {
        // The usage reports are disabled.
        printf('Compute Engine usage export bucket for project `%s` is disabled.', $projectId);
    }
}
# [END compute_usage_report_get]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
