<?php
/*
 * Copyright 2025 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/parametermanager/README.md
 */

declare(strict_types=1);

namespace Google\Cloud\Samples\ParameterManager;

// [START parametermanager_delete_param_template_version]
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\DeleteTemplateVersionRequest;

/**
 * Deletes a template version.
 *
 * @param string $projectId The Google Cloud Project ID (e.g. 'my-project')
 * @param string $templateId The Template ID (e.g. 'my-template')
 * @param string $versionId The Version ID (e.g. 'my-version')
 */
function delete_param_template_version(string $projectId, string $templateId, string $versionId): void
{
    // Create a client for the Parameter Manager service.
    $client = new ParameterManagerClient();

    // Build the resource name of the template version.
    $templateVersionName = $client->templateVersionName($projectId, 'global', $templateId, $versionId);

    // Build the request.
    $request = (new DeleteTemplateVersionRequest())
        ->setName($templateVersionName);

    // Delete the template version.
    $client->deleteTemplateVersion($request);

    // Print the confirmation.
    printf('Deleted template version: %s' . PHP_EOL, $versionId);
}
// [END parametermanager_delete_param_template_version]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
