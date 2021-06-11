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
use Google\Cloud\Compute\V1\InstancesClient;
use Google\Cloud\Compute\V1\AttachedDisk;
use Google\Cloud\Compute\V1\AttachedDiskInitializeParams;
use Google\Cloud\Compute\V1\Instance;
use Google\Cloud\Compute\V1\NetworkInterface;
use Google\Cloud\Compute\V1\Operation;
use Google\Cloud\Compute\V1\ProjectsClient;
use Google\Cloud\Compute\V1\UsageExportLocation;
use Google\Cloud\Compute\V1\ZoneOperationsClient;

/**
 * Set Compute Engine usage export bucket for the Cloud Project.
 * This sample presents how to interpret default value for the name prefix parameter.
 * Example:
 * ```
 * ($projectId, $zone, $instanceName);
 * ```
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $bucketName Google Cloud Storage Bucket used to store Compute Engine usage reports.
 * An existing Google Cloud Storage bucket is required.
 * @param string $reportPrefixName Report Prefix Name which defaults to an empty string to showcase default
 * values behaviour.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function set_usage_export_bucket(
    string $projectId,
    string $bucketName,
    string $reportPrefixName = ''
) {
    // Initialize UsageExportLocation object with provided bucket name and no report name prefix.
    $usageExportLocation = new UsageExportLocation(array(
        "bucket_name" => $bucketName,
        "report_name_prefix" => $reportPrefixName
    ));

    // Set the usage export location.
    $projectsClient = new ProjectsClient();
    $projectsClient->setUsageExportBucket($projectId, $usageExportLocation);

    // We need to wait before the updated data is available.
    sleep(5);

    // Get the setting from server.
    $projectResponse = $projectsClient->get($projectId);

    // Construct proper values to be displayed taking into account default values behaviour.
    if ($projectResponse->hasUsageExportLocation()) {
        $responseUsageExportLocation = $projectResponse->getUsageExportLocation();
        $responseBucketName = $responseUsageExportLocation->getBucketName();
        $responseReportNamePrefix = '';

        // We verify that the server explicitly sent the optional field.
        if ($responseUsageExportLocation->hasReportNamePrefix()) {
            $responseReportNamePrefix = $responseUsageExportLocation->getReportNamePrefix();

            if ($responseReportNamePrefix == '') {
                // Although the server explicitly sent the empty string value,
                // the next usage report generated with these settings still has the default
                // prefix value "usage". (ref: https://cloud.google.com/compute/docs/reference/rest/v1/projects/get)
                $responseReportNamePrefix = 'usage';
            }
        }

        printf('Usage export bucket for project %s set.' . PHP_EOL, $projectId);
        printf('Bucket: %s, Report name prefix: %s', $responseBucketName, $responseReportNamePrefix);
    }
}
# [END compute_instances_verify_default_value]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
