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
 * DO NOT EDIT! This is a generated sample ("Request",  "job_search_get_job")
 */

// [START job_search_get_job]
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Talent\V4beta1\JobServiceClient;

/** Get Job */
function sampleGetJob($projectId, $tenantId, $jobId)
{
    // [START job_search_get_job_core]

    $jobServiceClient = new JobServiceClient();

    // $projectId = 'Your Google Cloud Project ID';
    // $tenantId = 'Your Tenant ID (using tenancy is optional)';
    // $jobId = 'Job ID';
    $formattedName = $jobServiceClient->jobName($projectId, $tenantId, $jobId);

    try {
        $response = $jobServiceClient->getJob($formattedName);
        printf('Job name: %s'.PHP_EOL, $response->getName());
        printf('Requisition ID: %s'.PHP_EOL, $response->getRequisitionId());
        printf('Title: %s'.PHP_EOL, $response->getTitle());
        printf('Description: %s'.PHP_EOL, $response->getDescription());
        printf('Posting language: %s'.PHP_EOL, $response->getLanguageCode());
        foreach ($response->getAddresses() as $address) {
            printf('Address: %s'.PHP_EOL, $address);
        }
        foreach ($response->getApplicationInfo()->getEmails() as $email) {
            printf('Email: %s'.PHP_EOL, $email);
        }
        foreach ($response->getApplicationInfo()->getUris() as $websiteUri) {
            printf('Website: %s'.PHP_EOL, $websiteUri);
        }
    } finally {
        $jobServiceClient->close();
    }

    // [END job_search_get_job_core]
}
// [END job_search_get_job]

$opts = [
    'project_id::',
    'tenant_id::',
    'job_id::',
];

$defaultOptions = [
    'project_id' => 'Your Google Cloud Project ID',
    'tenant_id' => 'Your Tenant ID (using tenancy is optional)',
    'job_id' => 'Job ID',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];
$tenantId = $options['tenant_id'];
$jobId = $options['job_id'];

sampleGetJob($projectId, $tenantId, $jobId);
