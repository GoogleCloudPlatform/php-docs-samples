<?php
/*
 * Copyright 2025 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
*/

declare(strict_types=1);

namespace Google\Cloud\Samples\ModelArmor;

// [START modelarmor_create_template_with_advanced_sdp]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\SdpAdvancedConfig;
use Google\Cloud\ModelArmor\V1\Template;
use Google\Cloud\ModelArmor\V1\FilterConfig;
use Google\Cloud\ModelArmor\V1\CreateTemplateRequest;
use Google\Cloud\ModelArmor\V1\SdpFilterSettings;

/**
 * Create a Model Armor template with an Advanced SDP Filter.
 *
 * @param string $projectId The ID of the project (e.g. 'my-project').
 * @param string $locationId The ID of the location (e.g. 'us-central1').
 * @param string $templateId The ID of the template (e.g. 'my-template').
 * @param string $inspectTemplate The resource name of the inspect template.
          (e.g. 'organizations/{organization}/inspectTemplates/{inspect_template}')
 * @param string $deidentifyTemplate The resource name of the de-identify template.
          (e.g. 'organizations/{organization}/deidentifyTemplates/{deidentify_template}')
 */
function create_template_with_advanced_sdp(
    string $projectId,
    string $locationId,
    string $templateId,
    string $inspectTemplate,
    string $deidentifyTemplate
): void {
    $options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];
    $client = new ModelArmorClient($options);
    $parent = $client->locationName($projectId, $locationId);

    // Build the Model Armor template with Advanced SDP Filter.

    // Note: If you specify only Inspect template, Model Armor reports the filter matches if
    // sensitive data is detected. If you specify Inspect template and De-identify template, Model
    // Armor returns the de-identified sensitive data and sanitized version of prompts or
    // responses in the deidentifyResult.data.text field of the finding.
    $sdpAdvancedConfig = (new SdpAdvancedConfig())
        ->setInspectTemplate($inspectTemplate)
        ->setDeidentifyTemplate($deidentifyTemplate);

    $sdpSettings = (new SdpFilterSettings())->setAdvancedConfig($sdpAdvancedConfig);

    $templateFilterConfig = (new FilterConfig())
        ->setSdpSettings($sdpSettings);

    $template = (new Template())->setFilterConfig($templateFilterConfig);

    $request = (new CreateTemplateRequest())
        ->setParent($parent)
        ->setTemplateId($templateId)
        ->setTemplate($template);

    $response = $client->createTemplate($request);

    printf('Template created: %s' . PHP_EOL, $response->getName());
}
// [END modelarmor_create_template_with_advanced_sdp]

// The following 2 lines are only needed to execute the samples on the CLI.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
