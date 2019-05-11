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
 * DO NOT EDIT! This is a generated sample ("RequestPagedAll",  "job_search_list_jobs")
 */

// [START job_search_list_jobs]
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Talent\V4beta1\JobServiceClient;

/**
 * List Jobs.
 *
 * @param string $projectId Your Google Cloud Project ID
 * @param string $tenantId  Identifier of the Tenant
 */
function sampleListJobs($projectId, $tenantId, $filter)
{
    // [START job_search_list_jobs_core]

    $jobServiceClient = new JobServiceClient();

    // $projectId = 'Your Google Cloud Project ID';
    // $tenantId = 'Your Tenant ID (using tenancy is optional)';
    // $filter = 'companyName=projects/my-project/companies/company-id';
    $formattedParent = $jobServiceClient->tenantName($projectId, $tenantId);

    try {
        // Iterate through all elements
        $pagedResponse = $jobServiceClient->listJobs($formattedParent, $filter);
        foreach ($pagedResponse->iterateAllElements() as $responseItem) {
            printf('Job name: %s'.PHP_EOL, $responseItem->getName());
            printf('Job requisition ID: %s'.PHP_EOL, $responseItem->getRequisitionId());
            printf('Job title: %s'.PHP_EOL, $responseItem->getTitle());
            printf('Job description: %s'.PHP_EOL, $responseItem->getDescription());
        }
    } finally {
        $jobServiceClient->close();
    }

    // [END job_search_list_jobs_core]
}
// [END job_search_list_jobs]

$opts = [
    'project_id::',
    'tenant_id::',
    'filter::',
];

$defaultOptions = [
    'project_id' => 'Your Google Cloud Project ID',
    'tenant_id' => 'Your Tenant ID (using tenancy is optional)',
    'filter' => 'companyName=projects/my-project/companies/company-id',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];
$tenantId = $options['tenant_id'];
$filter = $options['filter'];

sampleListJobs($projectId, $tenantId, $filter);
