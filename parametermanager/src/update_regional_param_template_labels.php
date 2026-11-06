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

// [START parametermanager_update_regional_param_template_labels]
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\GetTemplateRequest;
use Google\Cloud\ParameterManager\V1\Template;
use Google\Cloud\ParameterManager\V1\UpdateTemplateRequest;
use Google\Protobuf\FieldMask;

/**
 * Adds or updates a label on a template.
 *
 * @param string $projectId The Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId The Parameter Location (e.g. 'us-central1')
 * @param string $templateId The Template ID (e.g. 'my-template')
 * @param string $key The label key (e.g. 'environment')
 * @param string $value The label value (e.g. 'production')
 */
function update_regional_param_template_labels(string $projectId, string $locationId, string $templateId, string $key, string $value): void
{
    // Specify regional endpoint.
    $options = ['apiEndpoint' => "parametermanager.$locationId.rep.googleapis.com"];

    // Create a client for the Parameter Manager service.
    $client = new ParameterManagerClient($options);

    // Build the resource name of the template.
    $templateName = $client->templateName($projectId, $locationId, $templateId);

    // Build the template with the label.
    $template = (new Template())
        ->setName($templateName)
        ->setLabels([$key => $value]);

    // Build the update mask for the labels field.
    $updateMask = (new FieldMask())
        ->setPaths(['labels']);

    // Build the request.
    $request = (new UpdateTemplateRequest())
        ->setTemplate($template)
        ->setUpdateMask($updateMask);

    // Update the template.
    $client->updateTemplate($request);

    // Get the updated template.
    $updatedTemplate = $client->getTemplate((new GetTemplateRequest())->setName($templateName));

    // Print the updated labels.
    printf('Updated regional template %s with labels %s' . PHP_EOL, $updatedTemplate->getName(), json_encode(iterator_to_array($updatedTemplate->getLabels())));
}
// [END parametermanager_update_regional_param_template_labels]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
