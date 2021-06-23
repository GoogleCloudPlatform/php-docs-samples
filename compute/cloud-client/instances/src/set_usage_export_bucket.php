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

# [START compute_instances_verify_default_value]
# [START compute_usage_report_set]
# [START compute_usage_report_get]
# [START compute_usage_report_disable]
use Google\Cloud\Compute\V1\ProjectsClient;
use Google\Cloud\Compute\V1\UsageExportLocation;

# [END compute_usage_report_disable]
# [END compute_usage_report_get]
# [END compute_usage_report_set]

# [START compute_usage_report_set]
/**
 * Set Compute Engine usage export bucket for the Cloud project.
 * This sample presents how to interpret the default value for the report name prefix parameter.
 * Example:
 * ```
 * set_usage_export_bucket($projectId, $bucketName, $reportNamePrefix);
 * ```
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $bucketName Google Cloud Storage bucket used to store Compute Engine usage reports.
 * An existing Google Cloud Storage bucket is required.
 * @param string $reportNamePrefix Prefix of the usage report name which defaults to an empty string
 * to showcase default values behavior.
 *
 * @return \Google\Cloud\Compute\V1\Operation
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function set_usage_export_bucket(
    string $projectId,
    string $bucketName,
    string $reportNamePrefix = ''
) {
    // Initialize UsageExportLocation object with provided bucket name and no report name prefix.
    $usageExportLocation = new UsageExportLocation(array(
        "bucket_name" => $bucketName,
        "report_name_prefix" => $reportNamePrefix
    ));

    if (strlen($reportNamePrefix) == 0) {
        // Sending empty value for report_name_prefix results in the next usage report
        // being generated with the default prefix value "usage_gce".
        // See https://cloud.google.com/compute/docs/reference/rest/v1/projects/setUsageExportBucket
        print("Setting report_name_prefix to empty value causes the " .
            "report to have the default value of `usage_gce`.");
    }

    // Set the usage export location.
    $projectsClient = new ProjectsClient();
    return $projectsClient->setUsageExportBucket($projectId, $usageExportLocation);
}
# [END compute_usage_report_set]

# [START compute_usage_report_get]
/**
 * Retrieve Compute Engine usage export bucket for the Cloud project.
 * Replaces the empty value returned by the API with the default value used
 * to generate report file names.
 * Example:
 * ```
 * get_usage_export_bucket($projectId);
 * ```
 *
 * @param string $projectId Your Google Cloud project ID.
 * @return UsageExportLocation|null UsageExportLocation object describing the current usage
 * export settings for project $projectId.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function get_usage_export_bucket(string $projectId)
{
    // Get the usage export location for the project from the server.
    $projectsClient = new ProjectsClient();
    $projectResponse = $projectsClient->get($projectId);

    // Construct proper values to be displayed, taking into account default values behavior.
    if ($projectResponse->hasUsageExportLocation()) {
        $responseUsageExportLocation = $projectResponse->getUsageExportLocation();

        // Verify that the server explicitly sent the optional field.
        if ($responseUsageExportLocation->hasReportNamePrefix()) {
            if ($responseUsageExportLocation->getReportNamePrefix() == '') {
                // Although the server explicitly sent the empty string value, the next usage
                // report generated with these settings still has the default prefix value "usage_gce".
                // See https://cloud.google.com/compute/docs/reference/rest/v1/projects/get
                print("Report name prefix not set, replacing with default value of `usage_gce`.");
                $responseUsageExportLocation->setReportNamePrefix('usage_gce');
            }
        }

        return $responseUsageExportLocation;
    } else {
        // The usage reports are disabled.
        return null;
    }
}
# [END compute_usage_report_get]
# [END compute_instances_verify_default_value]

# [START compute_usage_report_disable]
/**
 * Disable Compute Engine usage export bucket for the Cloud Project.
 * Example:
 * ```
 * disable_usage_export_bucket($projectId);
 * ```
 *
 * @param string $projectId Your Google Cloud project ID.
 *
 * @return \Google\Cloud\Compute\V1\Operation
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function disable_usage_export_bucket(string $projectId)
{
    // Disable the usage export location by sending null as usageExportLocationResource.
    $projectsClient = new ProjectsClient();
    return $projectsClient->setUsageExportBucket($projectId, null);
}
# [END compute_usage_report_disable]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
