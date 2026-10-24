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

// [START parametermanager_render_param_template_version]
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\RenderTemplateVersionRequest;

/**
 * Renders a template version using the values of a parameter version.
 *
 * @param string $projectId The Google Cloud Project ID (e.g. 'my-project')
 * @param string $templateId The Template ID (e.g. 'my-template')
 * @param string $versionId The Version ID (e.g. 'my-version')
 * @param string $parameterId The Parameter ID (e.g. 'my-param')
 * @param string $parameterVersionId The Parameter Version ID (e.g. 'my-param-version')
 */
function render_param_template_version(string $projectId, string $templateId, string $versionId, string $parameterId, string $parameterVersionId): void
{
    // Create a client for the Parameter Manager service.
    $client = new ParameterManagerClient();

    // Build the resource name of the template version.
    $templateVersionName = $client->templateVersionName($projectId, 'global', $templateId, $versionId);

    // Build the resource name of the parameter version.
    $parameterVersionName = $client->parameterVersionName($projectId, 'global', $parameterId, $parameterVersionId);

    // Build the request.
    $request = (new RenderTemplateVersionRequest())
        ->setName($templateVersionName)
        ->setParameterVersion($parameterVersionName);

    // Render the template version.
    $response = $client->renderTemplateVersion($request);

    // Print the template payload and the rendered payload.
    printf('Template payload: %s' . PHP_EOL, $response->getPayload()->getData());
    printf('Rendered payload: %s' . PHP_EOL, $response->getRenderedPayload());
}
// [END parametermanager_render_param_template_version]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
