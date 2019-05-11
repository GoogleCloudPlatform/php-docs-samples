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
 * DO NOT EDIT! This is a generated sample ("RequestPagedAll",  "job_search_list_tenants")
 */

// [START job_search_list_tenants]
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Talent\V4beta1\TenantServiceClient;

/** List Tenants */
function sampleListTenants($projectId)
{
    // [START job_search_list_tenants_core]

    $tenantServiceClient = new TenantServiceClient();

    // $projectId = 'Your Google Cloud Project ID';
    $formattedParent = $tenantServiceClient->projectName($projectId);

    try {
        // Iterate through all elements
        $pagedResponse = $tenantServiceClient->listTenants($formattedParent);
        foreach ($pagedResponse->iterateAllElements() as $responseItem) {
            printf('Tenant Name: %s'.PHP_EOL, $responseItem->getName());
            printf('External ID: %s'.PHP_EOL, $responseItem->getExternalId());
        }
    } finally {
        $tenantServiceClient->close();
    }

    // [END job_search_list_tenants_core]
}
// [END job_search_list_tenants]

$opts = [
    'project_id::',
];

$defaultOptions = [
    'project_id' => 'Your Google Cloud Project ID',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];

sampleListTenants($projectId);
