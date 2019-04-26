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
 * DO NOT EDIT! This is a generated sample ("Request",  "job_search_delete_tenant")
 */

// [START job_search_delete_tenant]
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Talent\V4beta1\TenantServiceClient;

/** Delete Tenant */
function sampleDeleteTenant($projectId, $tenantId)
{
    // [START job_search_delete_tenant_core]

    $tenantServiceClient = new TenantServiceClient();

    // $projectId = 'Your Google Cloud Project ID';
    // $tenantId = 'Your Tenant ID)';
    $formattedName = $tenantServiceClient->tenantName($projectId, $tenantId);

    try {
        $tenantServiceClient->deleteTenant($formattedName);
        printf('Deleted Tenant.'.PHP_EOL);
    } finally {
        $tenantServiceClient->close();
    }

    // [END job_search_delete_tenant_core]
}
// [END job_search_delete_tenant]

$opts = [
    'project_id::',
    'tenant_id::',
];

$defaultOptions = [
    'project_id' => 'Your Google Cloud Project ID',
    'tenant_id' => 'Your Tenant ID)',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];
$tenantId = $options['tenant_id'];

sampleDeleteTenant($projectId, $tenantId);
