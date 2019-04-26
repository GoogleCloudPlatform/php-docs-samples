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
 * DO NOT EDIT! This is a generated sample ("RequestPagedAll",  "job_search_list_companies")
 */

// [START job_search_list_companies]
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Talent\V4beta1\CompanyServiceClient;

/** List Companies */
function sampleListCompanies($projectId, $tenantId)
{
    // [START job_search_list_companies_core]

    $companyServiceClient = new CompanyServiceClient();

    // $projectId = 'Your Google Cloud Project ID';
    // $tenantId = 'Your Tenant ID (using tenancy is optional)';
    $formattedParent = $companyServiceClient->tenantName($projectId, $tenantId);

    try {
        // Iterate through all elements
        $pagedResponse = $companyServiceClient->listCompanies($formattedParent);
        foreach ($pagedResponse->iterateAllElements() as $responseItem) {
            printf('Company Name: %s'.PHP_EOL, $responseItem->getName());
            printf('Display Name: %s'.PHP_EOL, $responseItem->getDisplayName());
            printf('External ID: %s'.PHP_EOL, $responseItem->getExternalId());
        }
    } finally {
        $companyServiceClient->close();
    }

    // [END job_search_list_companies_core]
}
// [END job_search_list_companies]

$opts = [
    'project_id::',
    'tenant_id::',
];

$defaultOptions = [
    'project_id' => 'Your Google Cloud Project ID',
    'tenant_id' => 'Your Tenant ID (using tenancy is optional)',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];
$tenantId = $options['tenant_id'];

sampleListCompanies($projectId, $tenantId);
