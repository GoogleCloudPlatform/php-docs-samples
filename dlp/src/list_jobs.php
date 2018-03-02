<?php

/**
 * Copyright 2016 Google Inc.
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
namespace Google\Cloud\Samples\Dlp;

# [START dlp_list_jobs]
use Google\Cloud\Dlp\V2\DlpServiceClient;
use Google\Cloud\Dlp\V2\DlpJobType;

/**
 * List Data Loss Prevention API jobs corresponding to a given filter.
 *
 * @param string $callingProject The project ID to run the API call under
 * @param string $filter The filter expression to use
 *        For more information and filter syntax, see https://cloud.google.com/dlp/docs/reference/rest/v2beta2/projects.dlpJobs/list
 */
function list_jobs ($callingProject, $filter) {
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    // The type of job to list (either 'INSPECT_JOB' or 'REDACT_JOB')
    $jobType = DlpJobType::INSPECT_JOB;

    // Run job-listing request
    $parent = $dlp->projectName($callingProject);
    $response = $dlp->listDlpJobs($parent, [
      'filter' => $filter,
      'type' => $jobType
    ]);

    // Print job list
    $jobs = $response->iterateAllElements();
    foreach ($jobs as $job) {
      print_r('Job ' . $job->getName() . ' status: ' . $job->getState() . PHP_EOL);
      $infoTypeStats = $job->getInspectDetails()->getResult()->getInfoTypeStats();

      if (count($infoTypeStats) > 0) {
        foreach ($infoTypeStats as $infoTypeStat) {
          print_r('  Found '. $infoTypeStat->getCount() . ' instance(s) of type ' . $infoTypeStat->getInfoType()->getName() . PHP_EOL);
        }
      } else {
        print_r('  No findings.' . PHP_EOL);
      }
    }
}
# [END dlp_list_jobs]