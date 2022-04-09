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

// [START dlp_create_trigger]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\JobTrigger;
use Google\Cloud\Dlp\V2\JobTrigger\Trigger;
use Google\Cloud\Dlp\V2\JobTrigger\Status;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectJobConfig;
use Google\Cloud\Dlp\V2\Schedule;
use Google\Cloud\Dlp\V2\CloudStorageOptions;
use Google\Cloud\Dlp\V2\CloudStorageOptions_FileSet;
use Google\Cloud\Dlp\V2\StorageConfig;
use Google\Cloud\Dlp\V2\StorageConfig_TimespanConfig;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\InspectConfig\FindingLimits;
use Google\Protobuf\Duration;

/**
 * Create a Data Loss Prevention API job trigger.
 *
 * @param string $callingProjectId     The project ID to run the API call under
 * @param string $bucketName           The name of the bucket to scan
 * @param string $triggerId            (Optional) The name of the trigger to be created
 * @param string $displayName          (Optional) The human-readable name to give the trigger
 * @param string $description          (Optional) A description for the trigger to be created
 * @param int    $scanPeriod           (Optional) How often to wait between scans, in days (minimum = 1 day)
 * @param bool   $autoPopulateTimespan (Optional) Automatically limit scan to new content only
 * @param int    $maxFindings          (Optional) The maximum number of findings to report per request (0 = server maximum)
 */
function create_trigger(
    string $callingProjectId,
    string $bucketName,
    string $triggerId = '',
    string $displayName = '',
    string $description = '',
    int $scanPeriod = 0,
    bool $autoPopulateTimespan = false,
    int $maxFindings = 0
): void {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // ----- Construct job config -----
    // The infoTypes of information to match
    $personNameInfoType = (new InfoType())
        ->setName('PERSON_NAME');
    $phoneNumberInfoType = (new InfoType())
        ->setName('PHONE_NUMBER');
    $infoTypes = [$personNameInfoType, $phoneNumberInfoType];

    // The minimum likelihood required before returning a match
    $minLikelihood = likelihood::LIKELIHOOD_UNSPECIFIED;

    // Specify finding limits
    $limits = (new FindingLimits())
        ->setMaxFindingsPerRequest($maxFindings);

    // Create the inspectConfig object
    $inspectConfig = (new InspectConfig())
        ->setMinLikelihood($minLikelihood)
        ->setLimits($limits)
        ->setInfoTypes($infoTypes);

    // Create triggers
    $duration = (new Duration())
        ->setSeconds($scanPeriod * 60 * 60 * 24);

    $schedule = (new Schedule())
        ->setRecurrencePeriodDuration($duration);

    $triggerObject = (new Trigger())
        ->setSchedule($schedule);

    // Create the storageConfig object
    $fileSet = (new CloudStorageOptions_FileSet())
        ->setUrl('gs://' . $bucketName . '/*');

    $storageOptions = (new CloudStorageOptions())
        ->setFileSet($fileSet);

    // Auto-populate start and end times in order to scan new objects only.
    $timespanConfig = (new StorageConfig_TimespanConfig())
        ->setEnableAutoPopulationOfTimespanConfig($autoPopulateTimespan);

    $storageConfig = (new StorageConfig())
        ->setCloudStorageOptions($storageOptions)
        ->setTimespanConfig($timespanConfig);

    // Construct the jobConfig object
    $jobConfig = (new InspectJobConfig())
        ->setInspectConfig($inspectConfig)
        ->setStorageConfig($storageConfig);

    // ----- Construct trigger object -----
    $jobTriggerObject = (new JobTrigger())
        ->setTriggers([$triggerObject])
        ->setInspectJob($jobConfig)
        ->setStatus(Status::HEALTHY)
        ->setDisplayName($displayName)
        ->setDescription($description);

    // Run trigger creation request
    $parent = "projects/$callingProjectId/locations/global";
    $trigger = $dlp->createJobTrigger($parent, $jobTriggerObject, [
        'triggerId' => $triggerId
    ]);

    // Print results
    printf('Successfully created trigger %s' . PHP_EOL, $trigger->getName());
}
// [END dlp_create_trigger]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
