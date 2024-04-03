<?php
/**
 * Copyright 2024 Google Inc.
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

namespace Google\Cloud\Samples\StorageInsights;

# [START storageinsights_create_inventory_report_config]
use Google\Type\Date;
use Google\Cloud\StorageInsights\V1\CSVOptions;
use Google\Cloud\StorageInsights\V1\ReportConfig;
use Google\Cloud\StorageInsights\V1\FrequencyOptions;
use Google\Cloud\StorageInsights\V1\CloudStorageFilters;
use Google\Cloud\StorageInsights\V1\StorageInsightsClient;
use Google\Cloud\StorageInsights\V1\ObjectMetadataReportOptions;
use Google\Cloud\StorageInsights\V1\CloudStorageDestinationOptions;

/**
 * Creates an inventory report config.
 * Example:
 * ```
 * create_inventory_report_config($projectId, $bucketLocation, $sourceBucket, $destinationBucket);
 * ```
 * @param string $projectId Your Google Cloud Project ID
 * @param string $bucketLocation The location of your source and destination buckets
 * @param string $sourceBucket The name of your Google Cloud Storage source bucket
 * @param string $destinationBucket The name of your Google Cloud Storage destination bucket
 */
function create_inventory_report_config(
    string $projectId,
    string $bucketLocation,
    string $sourceBucket,
    string $destinationBucket
): void {
    $storageInsightsClient = new StorageInsightsClient();

    $reportConfig = (new ReportConfig())
        ->setDisplayName('Example inventory report configuration')
        ->setFrequencyOptions((new FrequencyOptions())
            ->setFrequency(FrequencyOptions\Frequency::WEEKLY)
            ->setStartDate((new Date())
                ->setDay(15)
                ->setMonth(8)
                ->setYear(3023))
            ->setEndDate((new Date())
                ->setDay(15)
                ->setMonth(9)
                ->setYear(3023)))
        ->setCsvOptions((new CSVOptions())
            ->setDelimiter(',')
            ->setRecordSeparator("\n")
            ->setHeaderRequired(true))
        ->setObjectMetadataReportOptions((new ObjectMetadataReportOptions())
            ->setMetadataFields(['project', 'name', 'bucket'])
            ->setStorageFilters((new CloudStorageFilters())
                ->setBucket($sourceBucket))
            ->setStorageDestinationOptions((new CloudStorageDestinationOptions())
                ->setBucket($destinationBucket)));

    $formattedParent = $storageInsightsClient->locationName($projectId, $bucketLocation);
    $response = $storageInsightsClient->createReportConfig($formattedParent, $reportConfig);

    print('Created inventory report config with name:' . PHP_EOL);
    print($response->getName());
}
# [END storageinsights_create_inventory_report_config]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
