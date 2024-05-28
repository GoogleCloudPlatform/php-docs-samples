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

# [START dlp_create_job]
use Google\Cloud\Dlp\V2\Action;
use Google\Cloud\Dlp\V2\Action\PublishSummaryToCscc;
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\CloudStorageOptions;
use Google\Cloud\Dlp\V2\CloudStorageOptions\FileSet;
use Google\Cloud\Dlp\V2\CreateDlpJobRequest;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectConfig\FindingLimits;
use Google\Cloud\Dlp\V2\InspectJobConfig;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\StorageConfig;
use Google\Cloud\Dlp\V2\StorageConfig\TimespanConfig;

/**
 * Creates an inspection job with the Cloud Data Loss Prevention API.
 *
 * @param string $callingProjectId  The project ID to run the API call under.
 * @param string $gcsPath           GCS file to be inspected. Example : gs://GOOGLE_STORAGE_BUCKET_NAME/dlp_sample.csv
 */
function create_job(
    string $callingProjectId,
    string $gcsPath
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // Set autoPopulateTimespan to true to scan only new content.
    $timespanConfig = (new TimespanConfig())
        ->setEnableAutoPopulationOfTimespanConfig(true);

    // Specify the GCS file to be inspected.
    $cloudStorageOptions = (new CloudStorageOptions())
        ->setFileSet((new FileSet())
            ->setUrl($gcsPath));
    $storageConfig = (new StorageConfig())
        ->setCloudStorageOptions(($cloudStorageOptions))
        ->setTimespanConfig($timespanConfig);

    // ----- Construct inspection config -----
    $emailAddressInfoType = (new InfoType())
        ->setName('EMAIL_ADDRESS');
    $personNameInfoType = (new InfoType())
        ->setName('PERSON_NAME');
    $locationInfoType = (new InfoType())
        ->setName('LOCATION');
    $phoneNumberInfoType = (new InfoType())
        ->setName('PHONE_NUMBER');
    $infoTypes = [$emailAddressInfoType, $personNameInfoType, $locationInfoType, $phoneNumberInfoType];

    // Whether to include the matching string in the response.
    $includeQuote = true;
    // The minimum likelihood required before returning a match.
    $minLikelihood = likelihood::LIKELIHOOD_UNSPECIFIED;

    // The maximum number of findings to report (0 = server maximum).
    $limits = (new FindingLimits())
        ->setMaxFindingsPerRequest(100);

    // Create the Inspect configuration object.
    $inspectConfig = (new InspectConfig())
        ->setMinLikelihood($minLikelihood)
        ->setLimits($limits)
        ->setInfoTypes($infoTypes)
        ->setIncludeQuote($includeQuote);

    // Specify the action that is triggered when the job completes.
    $action = (new Action())
        ->setPublishSummaryToCscc(new PublishSummaryToCscc());

    // Configure the inspection job we want the service to perform.
    $inspectJobConfig = (new InspectJobConfig())
        ->setInspectConfig($inspectConfig)
        ->setStorageConfig($storageConfig)
        ->setActions([$action]);

    // Send the job creation request and process the response.
    $parent = "projects/$callingProjectId/locations/global";
    $createDlpJobRequest = (new CreateDlpJobRequest())
        ->setParent($parent)
        ->setInspectJob($inspectJobConfig);
    $job = $dlp->createDlpJob($createDlpJobRequest);

    // Print results.
    printf($job->getName());
}
# [END dlp_create_job]
// The following 2 lines are only needed to run the samples.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
