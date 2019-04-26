<?php
/*
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * DO NOT EDIT! This is a generated sample ("Request",  "job_search_delete_job")
 */

// [START job_search_delete_job]
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Talent\V4beta1\JobServiceClient;

/** Delete Job */
function sampleDeleteJob($projectId, $tenantId, $jobId)
{
    // [START job_search_delete_job_core]

    $jobServiceClient = new JobServiceClient();

    // $projectId = 'Your Google Cloud Project ID';
    // $tenantId = 'Your Tenant ID (using tenancy is optional)';
    // $jobId = 'Company ID';
    $formattedName = $jobServiceClient->jobName($projectId, $tenantId, $jobId);

    try {
        $jobServiceClient->deleteJob($formattedName);
        printf('Deleted job.'.PHP_EOL);
    } finally {
        $jobServiceClient->close();
    }

    // [END job_search_delete_job_core]
}
// [END job_search_delete_job]

$opts = [
    'project_id::',
    'tenant_id::',
    'job_id::',
];

$defaultOptions = [
    'project_id' => 'Your Google Cloud Project ID',
    'tenant_id' => 'Your Tenant ID (using tenancy is optional)',
    'job_id' => 'Company ID',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];
$tenantId = $options['tenant_id'];
$jobId = $options['job_id'];

sampleDeleteJob($projectId, $tenantId, $jobId);
