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
 * DO NOT EDIT! This is a generated sample ("Request",  "job_search_create_tenant")
 */

// [START job_search_create_tenant]
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Talent\V4beta1\TenantServiceClient;
use Google\Cloud\Talent\V4beta1\Tenant;

/** Create Tenant for scoping resources, e.g. companies and jobs */
function sampleCreateTenant($projectId, $externalId)
{
    // [START job_search_create_tenant_core]

    $tenantServiceClient = new TenantServiceClient();

    // $projectId = 'Your Google Cloud Project ID';
    // $externalId = 'Your Unique Identifier for Tenant';
    $formattedParent = $tenantServiceClient->projectName($projectId);
    $tenant = new Tenant();
    $tenant->setExternalId($externalId);

    try {
        $response = $tenantServiceClient->createTenant($formattedParent, $tenant);
        printf('Created Tenant'.PHP_EOL);
        printf('Name: %s'.PHP_EOL, $response->getName());
        printf('External ID: %s'.PHP_EOL, $response->getExternalId());
    } finally {
        $tenantServiceClient->close();
    }

    // [END job_search_create_tenant_core]
}
// [END job_search_create_tenant]

$opts = [
    'project_id::',
    'external_id::',
];

$defaultOptions = [
    'project_id' => 'Your Google Cloud Project ID',
    'external_id' => 'Your Unique Identifier for Tenant',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];
$externalId = $options['external_id'];

sampleCreateTenant($projectId, $externalId);
