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
 * DO NOT EDIT! This is a generated sample ("Request",  "job_search_create_company")
 */

// [START job_search_create_company]
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Talent\V4beta1\CompanyServiceClient;
use Google\Cloud\Talent\V4beta1\Company;

/** Create Company */
function sampleCreateCompany($projectId, $tenantId, $displayName, $externalId)
{
    // [START job_search_create_company_core]

    $companyServiceClient = new CompanyServiceClient();

    // $projectId = 'Your Google Cloud Project ID';
    // $tenantId = 'Your Tenant ID (using tenancy is optional)';
    // $displayName = 'My Company Name';
    // $externalId = 'Identifier of this company in my system';
    $formattedParent = $companyServiceClient->tenantName($projectId, $tenantId);
    $company = new Company();
    $company->setDisplayName($displayName);
    $company->setExternalId($externalId);

    try {
        $response = $companyServiceClient->createCompany($formattedParent, $company);
        printf('Created Company'.PHP_EOL);
        printf('Name: %s'.PHP_EOL, $response->getName());
        printf('Display Name: %s'.PHP_EOL, $response->getDisplayName());
        printf('External ID: %s'.PHP_EOL, $response->getExternalId());
    } finally {
        $companyServiceClient->close();
    }

    // [END job_search_create_company_core]
}
// [END job_search_create_company]

$opts = [
    'project_id::',
    'tenant_id::',
    'display_name::',
    'external_id::',
];

$defaultOptions = [
    'project_id' => 'Your Google Cloud Project ID',
    'tenant_id' => 'Your Tenant ID (using tenancy is optional)',
    'display_name' => 'My Company Name',
    'external_id' => 'Identifier of this company in my system',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];
$tenantId = $options['tenant_id'];
$displayName = $options['display_name'];
$externalId = $options['external_id'];

sampleCreateCompany($projectId, $tenantId, $displayName, $externalId);
