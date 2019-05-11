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
 * DO NOT EDIT! This is a generated sample ("Request",  "job_search_create_job_custom_attributes")
 */

// [START job_search_create_job_custom_attributes]
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Talent\V4beta1\JobServiceClient;
use Google\Cloud\Talent\V4beta1\Job;

/**
 * Create Job with Custom Attributes.
 *
 * @param string $projectId Your Google Cloud Project ID
 * @param string $tenantId  Identifier of the Tenantd
 */
function sampleCreateJob($projectId, $tenantId, $companyName, $requisitionId, $languageCode)
{
    // [START job_search_create_job_custom_attributes_core]

    $jobServiceClient = new JobServiceClient();

    // $projectId = 'Your Google Cloud Project ID';
    // $tenantId = 'Your Tenant ID (using tenancy is optional)';
    // $companyName = 'Company name, e.g. projects/your-project/companies/company-id';
    // $requisitionId = 'Job requisition ID, aka Posting ID. Unique per job.';
    // $languageCode = 'en-US';
    $formattedParent = $jobServiceClient->tenantName($projectId, $tenantId);
    $job = new Job();
    $job->setCompany($companyName);
    $job->setRequisitionId($requisitionId);
    $job->setLanguageCode($languageCode);

    try {
        $response = $jobServiceClient->createJob($formattedParent, $job);
        printf('Created job: %s'.PHP_EOL, $response->getName());
    } finally {
        $jobServiceClient->close();
    }

    // [END job_search_create_job_custom_attributes_core]
}
// [END job_search_create_job_custom_attributes]

$opts = [
    'project_id::',
    'tenant_id::',
    'company_name::',
    'requisition_id::',
    'language_code::',
];

$defaultOptions = [
    'project_id' => 'Your Google Cloud Project ID',
    'tenant_id' => 'Your Tenant ID (using tenancy is optional)',
    'company_name' => 'Company name, e.g. projects/your-project/companies/company-id',
    'requisition_id' => 'Job requisition ID, aka Posting ID. Unique per job.',
    'language_code' => 'en-US',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];
$tenantId = $options['tenant_id'];
$companyName = $options['company_name'];
$requisitionId = $options['requisition_id'];
$languageCode = $options['language_code'];

sampleCreateJob($projectId, $tenantId, $companyName, $requisitionId, $languageCode);
