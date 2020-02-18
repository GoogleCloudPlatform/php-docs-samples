<?php
/*
 * Copyright 2020 Google LLC
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
 * DO NOT EDIT! This is a generated sample ("LongRunningRequest",  "job_search_batch_update_jobs")
 */

// sample-metadata
//   title:
//   description: Batch Update Jobs
//   usage: php samples/V4beta1/JobSearchBatchUpdateJobs.php [--project_id "Your Google Cloud Project ID"] [--tenant_id "Your Tenant ID (using tenancy is optional)"] [--job_name_one "job name, e.g. projects/your-project/tenants/tenant-id/jobs/job-id"] [--company_name_one "Company name, e.g. projects/your-project/companies/company-id"] [--requisition_id_one "Job requisition ID, aka Posting ID. Unique per job."] [--title_one "Software Engineer"] [--description_one "This is a description of this <i>wonderful</i> job!"] [--job_application_url_one "https://www.example.org/job-posting/123"] [--address_one "1600 Amphitheatre Parkway, Mountain View, CA 94043"] [--language_code_one "en-US"] [--job_name_two "job name, e.g. projects/your-project/tenants/tenant-id/jobs/job-id"] [--company_name_two "Company name, e.g. projects/your-project/companies/company-id"] [--requisition_id_two "Job requisition ID, aka Posting ID. Unique per job."] [--title_two "Quality Assurance"] [--description_two "This is a description of this <i>wonderful</i> job!"] [--job_application_url_two "https://www.example.org/job-posting/123"] [--address_two "111 8th Avenue, New York, NY 10011"] [--language_code_two "en-US"]
// [START job_search_batch_update_jobs]
require __DIR__.'/vendor/autoload.php';

use Google\Cloud\Talent\V4beta1\JobServiceClient;
use Google\Cloud\Talent\V4beta1\Job;
use Google\Cloud\Talent\V4beta1\Job\ApplicationInfo;

/**
 * Batch Update Jobs.
 *
 * @param string $projectId Your Google Cloud Project ID
 * @param string $tenantId  Identifier of the Tenant
 */
function sampleBatchUpdateJobs($projectId, $tenantId, $jobNameOne, $companyNameOne, $requisitionIdOne, $titleOne, $descriptionOne, $jobApplicationUrlOne, $addressOne, $languageCodeOne, $jobNameTwo, $companyNameTwo, $requisitionIdTwo, $titleTwo, $descriptionTwo, $jobApplicationUrlTwo, $addressTwo, $languageCodeTwo)
{
    $jobServiceClient = new JobServiceClient();

    // $projectId = 'Your Google Cloud Project ID';
    // $tenantId = 'Your Tenant ID (using tenancy is optional)';
    // $jobNameOne = 'job name, e.g. projects/your-project/tenants/tenant-id/jobs/job-id';
    // $companyNameOne = 'Company name, e.g. projects/your-project/companies/company-id';
    // $requisitionIdOne = 'Job requisition ID, aka Posting ID. Unique per job.';
    // $titleOne = 'Software Engineer';
    // $descriptionOne = 'This is a description of this <i>wonderful</i> job!';
    // $jobApplicationUrlOne = 'https://www.example.org/job-posting/123';
    // $addressOne = '1600 Amphitheatre Parkway, Mountain View, CA 94043';
    // $languageCodeOne = 'en-US';
    // $jobNameTwo = 'job name, e.g. projects/your-project/tenants/tenant-id/jobs/job-id';
    // $companyNameTwo = 'Company name, e.g. projects/your-project/companies/company-id';
    // $requisitionIdTwo = 'Job requisition ID, aka Posting ID. Unique per job.';
    // $titleTwo = 'Quality Assurance';
    // $descriptionTwo = 'This is a description of this <i>wonderful</i> job!';
    // $jobApplicationUrlTwo = 'https://www.example.org/job-posting/123';
    // $addressTwo = '111 8th Avenue, New York, NY 10011';
    // $languageCodeTwo = 'en-US';
    $formattedParent = $jobServiceClient->tenantName($projectId, $tenantId);
    $uris = [$jobApplicationUrlOne];
    $applicationInfo = new ApplicationInfo();
    $applicationInfo->setUris($uris);
    $addresses = [$addressOne];
    $jobsElement = new Job();
    $jobsElement->setName($jobNameOne);
    $jobsElement->setCompany($companyNameOne);
    $jobsElement->setRequisitionId($requisitionIdOne);
    $jobsElement->setTitle($titleOne);
    $jobsElement->setDescription($descriptionOne);
    $jobsElement->setApplicationInfo($applicationInfo);
    $jobsElement->setAddresses($addresses);
    $jobsElement->setLanguageCode($languageCodeOne);
    $uris2 = [$jobApplicationUrlTwo];
    $applicationInfo2 = new ApplicationInfo();
    $applicationInfo2->setUris($uris2);
    $addresses2 = [$addressTwo];
    $jobsElement2 = new Job();
    $jobsElement2->setName($jobNameTwo);
    $jobsElement2->setCompany($companyNameTwo);
    $jobsElement2->setRequisitionId($requisitionIdTwo);
    $jobsElement2->setTitle($titleTwo);
    $jobsElement2->setDescription($descriptionTwo);
    $jobsElement2->setApplicationInfo($applicationInfo2);
    $jobsElement2->setAddresses($addresses2);
    $jobsElement2->setLanguageCode($languageCodeTwo);
    $jobs = [$jobsElement, $jobsElement2];

    try {
        $operationResponse = $jobServiceClient->batchUpdateJobs($formattedParent, $jobs);
        $operationResponse->pollUntilComplete();
        if ($operationResponse->operationSucceeded()) {
            $response = $operationResponse->getResult();
            printf('Batch response: %s'.PHP_EOL, print_r($response, true));
        } else {
            $error = $operationResponse->getError();
            // handleError($error)
        }
    } finally {
        $jobServiceClient->close();
    }
}
// [END job_search_batch_update_jobs]

$opts = [
    'project_id::',
    'tenant_id::',
    'job_name_one::',
    'company_name_one::',
    'requisition_id_one::',
    'title_one::',
    'description_one::',
    'job_application_url_one::',
    'address_one::',
    'language_code_one::',
    'job_name_two::',
    'company_name_two::',
    'requisition_id_two::',
    'title_two::',
    'description_two::',
    'job_application_url_two::',
    'address_two::',
    'language_code_two::',
];

$defaultOptions = [
    'project_id' => 'Your Google Cloud Project ID',
    'tenant_id' => 'Your Tenant ID (using tenancy is optional)',
    'job_name_one' => 'job name, e.g. projects/your-project/tenants/tenant-id/jobs/job-id',
    'company_name_one' => 'Company name, e.g. projects/your-project/companies/company-id',
    'requisition_id_one' => 'Job requisition ID, aka Posting ID. Unique per job.',
    'title_one' => 'Software Engineer',
    'description_one' => 'This is a description of this <i>wonderful</i> job!',
    'job_application_url_one' => 'https://www.example.org/job-posting/123',
    'address_one' => '1600 Amphitheatre Parkway, Mountain View, CA 94043',
    'language_code_one' => 'en-US',
    'job_name_two' => 'job name, e.g. projects/your-project/tenants/tenant-id/jobs/job-id',
    'company_name_two' => 'Company name, e.g. projects/your-project/companies/company-id',
    'requisition_id_two' => 'Job requisition ID, aka Posting ID. Unique per job.',
    'title_two' => 'Quality Assurance',
    'description_two' => 'This is a description of this <i>wonderful</i> job!',
    'job_application_url_two' => 'https://www.example.org/job-posting/123',
    'address_two' => '111 8th Avenue, New York, NY 10011',
    'language_code_two' => 'en-US',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];
$tenantId = $options['tenant_id'];
$jobNameOne = $options['job_name_one'];
$companyNameOne = $options['company_name_one'];
$requisitionIdOne = $options['requisition_id_one'];
$titleOne = $options['title_one'];
$descriptionOne = $options['description_one'];
$jobApplicationUrlOne = $options['job_application_url_one'];
$addressOne = $options['address_one'];
$languageCodeOne = $options['language_code_one'];
$jobNameTwo = $options['job_name_two'];
$companyNameTwo = $options['company_name_two'];
$requisitionIdTwo = $options['requisition_id_two'];
$titleTwo = $options['title_two'];
$descriptionTwo = $options['description_two'];
$jobApplicationUrlTwo = $options['job_application_url_two'];
$addressTwo = $options['address_two'];
$languageCodeTwo = $options['language_code_two'];

sampleBatchUpdateJobs($projectId, $tenantId, $jobNameOne, $companyNameOne, $requisitionIdOne, $titleOne, $descriptionOne, $jobApplicationUrlOne, $addressOne, $languageCodeOne, $jobNameTwo, $companyNameTwo, $requisitionIdTwo, $titleTwo, $descriptionTwo, $jobApplicationUrlTwo, $addressTwo, $languageCodeTwo);
