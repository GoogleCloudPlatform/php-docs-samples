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

# [START compute_usage_report_set]
use Google\Cloud\Compute\V1\ProjectsClient;
use Google\Cloud\Compute\V1\UsageExportLocation;
use Google\Cloud\Compute\V1\Operation;

/**
 * Set Compute Engine usage export bucket for the Cloud project.
 * This sample presents how to interpret the default value for the report name prefix parameter.
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $bucketName Google Cloud Storage bucket used to store Compute Engine usage reports.
 * An existing Google Cloud Storage bucket is required.
 * @param string $reportNamePrefix Prefix of the usage report name which defaults to an empty string
 * to showcase default values behavior.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 * @throws \Google\ApiCore\ValidationException if local error occurs before remote call.
 */
function set_usage_export_bucket(
    string $projectId,
    string $bucketName,
    string $reportNamePrefix = ''
) {
    // Initialize UsageExportLocation object with provided bucket name and no report name prefix.
    $usageExportLocation = new UsageExportLocation(array(
        'bucket_name' => $bucketName,
        'report_name_prefix' => $reportNamePrefix
    ));

    if (strlen($reportNamePrefix) == 0) {
        // Sending empty value for report_name_prefix results in the next usage report
        // being generated with the default prefix value "usage_gce".
        // See https://cloud.google.com/compute/docs/reference/rest/v1/projects/setUsageExportBucket
        print('Setting report_name_prefix to empty value causes the ' .
            'report to have the default value of `usage_gce`.' . PHP_EOL);
    }

    // Set the usage export location.
    $projectsClient = new ProjectsClient();
    $operation = $projectsClient->setUsageExportBucket($projectId, $usageExportLocation);

    // Wait for the operation to complete.
    $operation->pollUntilComplete();
    if ($operation->operationSucceeded()) {
        printf(
            'Compute Engine usage export bucket for project `%s` set to bucket_name = `%s` with ' .
            'report_name_prefix = `%s`.' . PHP_EOL,
            $projectId,
            $usageExportLocation->getBucketName(),
            (strlen($reportNamePrefix) == 0) ? 'usage_gce' : $usageExportLocation->getReportNamePrefix()
        );
    } else {
        $error = $operation->getError();
        printf('Setting usage export bucket failed: %s' . PHP_EOL, $error->getMessage());
    }
}
# [END compute_usage_report_set]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
