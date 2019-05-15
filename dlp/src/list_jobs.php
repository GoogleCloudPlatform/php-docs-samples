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

if (count($argv) < 2 || count($argv) > 3) {
    return print("Usage: php list_jobs.php CALLING_PROJECT [FILTER]\n");
}
list($_, $callingProjectId) = $argv;
$filter = isset($argv[2]) ? $argv[2] : '';

# [START dlp_list_jobs]
/**
 * List Data Loss Prevention API jobs corresponding to a given filter.
 */
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\DlpJobType;

/** Uncomment and populate these variables in your code */
// $callingProjectId = 'The project ID to run the API call under';
// $filter = 'The filter expression to use';

// Instantiate a client.
$dlp = new DlpServiceClient();

// The type of job to list (either 'INSPECT_JOB' or 'REDACT_JOB')
$jobType = DlpJobType::INSPECT_JOB;

// Run job-listing request
// For more information and filter syntax,
// @see https://cloud.google.com/dlp/docs/reference/rest/v2/projects.dlpJobs/list
$parent = $dlp->projectName($callingProjectId);
$response = $dlp->listDlpJobs($parent, [
  'filter' => $filter,
  'type' => $jobType
]);

// Print job list
$jobs = $response->iterateAllElements();
foreach ($jobs as $job) {
    printf('Job %s status: %s' . PHP_EOL, $job->getName(), $job->getState());
    $infoTypeStats = $job->getInspectDetails()->getResult()->getInfoTypeStats();

    if (count($infoTypeStats) > 0) {
        foreach ($infoTypeStats as $infoTypeStat) {
            printf(
                '  Found %s instance(s) of type %s' . PHP_EOL,
                $infoTypeStat->getCount(),
                $infoTypeStat->getInfoType()->getName()
            );
        }
    } else {
        print('  No findings.' . PHP_EOL);
    }
}
# [END dlp_list_jobs]
