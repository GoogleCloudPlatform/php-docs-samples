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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 3 || count($argv) > 9) {
    return print("Usage: php create_trigger.php CALLING_PROJECT BUCKET [TRIGGER] [DISPLAY_NAME] [DESCRIPTION] [SCAN_PERIOD] [AUTO_POPULATE_TIMESPAN] [MAX_FINDINGS]\n");
}
list($_, $callingProjectId, $bucketName) = $argv;
$triggerId = isset($argv[3]) ? $argv[3] : '';
$displayName = isset($argv[4]) ? $argv[4] : '';
$description = isset($argv[5]) ? $argv[5] : '';
$scanPeriod = isset($argv[6]) ? (int) $argv[6] : 1;
$autoPopulateTimespan = isset($argv[7]) ? (bool) $argv[7] : false;
$maxFindings = isset($argv[8]) ? (int) $argv[8] : 0;

// [START dlp_create_trigger]
/**
 * Create a Data Loss Prevention API job trigger.
 */
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

/** Uncomment and populate these variables in your code */
// $callingProjectId = 'The project ID to run the API call under';
// $bucketName = 'The name of the bucket to scan';
// $triggerId = '';   // (Optional) The name of the trigger to be created';
// $displayName = ''; // (Optional) The human-readable name to give the trigger';
// $description = ''; // (Optional) A description for the trigger to be created';
// $scanPeriod = 1; // (Optional) How often to wait between scans, in days (minimum = 1 day)
// $autoPopulateTimespan = true; // (Optional) Automatically limit scan to new content only
// $maxFindings = 0; // (Optional) The maximum number of findings to report per request (0 = server maximum)

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
$parent = $dlp->projectName($callingProjectId);
$trigger = $dlp->createJobTrigger($parent, [
    'jobTrigger' => $jobTriggerObject,
    'triggerId' => $triggerId
]);

// Print results
printf('Successfully created trigger %s' . PHP_EOL, $trigger->getName());
// [END dlp_create_trigger]
