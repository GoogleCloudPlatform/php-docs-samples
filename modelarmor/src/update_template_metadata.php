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

// [START modelarmor_update_template_metadata]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\DetectionConfidenceLevel;
use Google\Cloud\ModelArmor\V1\PiAndJailbreakFilterSettings\PiAndJailbreakFilterEnforcement;
use Google\Cloud\ModelArmor\V1\UpdateTemplateRequest;
use Google\Cloud\ModelArmor\V1\PiAndJailbreakFilterSettings;
use Google\Cloud\ModelArmor\V1\MaliciousUriFilterSettings;
use Google\Cloud\ModelArmor\V1\Template\TemplateMetadata;
use Google\Cloud\ModelArmor\V1\FilterConfig;
use Google\Cloud\ModelArmor\V1\Template;

/**
 * Updates a Model Armor template with the specified configuration.
 *
 * @param string $projectId The ID of the project (e.g. 'my-project').
 * @param string $locationId The ID of the location (e.g. 'us-central1').
 * @param string $templateId The ID of the template (e.g. 'my-template').
 */
function update_template_metadata($projectId, $locationId, $templateId): void
{
    $options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];
    $client = new ModelArmorClient($options);

    $templateFilterConfig = new FilterConfig()
        ->setPiAndJailbreakFilterSettings(
            (new PiAndJailbreakFilterSettings())
                ->setFilterEnforcement(PiAndJailbreakFilterEnforcement::ENABLED)
                ->setConfidenceLevel(DetectionConfidenceLevel::LOW_AND_ABOVE)
        )
        ->setMaliciousUriFilterSettings(
            (new MaliciousUriFilterSettings())
                ->setFilterEnforcement(PiAndJailbreakFilterEnforcement::ENABLED)
        );

    $templateMetadata = (new TemplateMetadata())
        ->setLogTemplateOperations(true)
        ->setLogSanitizeOperations(true);

    $template = (new Template())
        ->setFilterConfig($templateFilterConfig)
        ->setName("projects/$projectId/locations/$locationId/templates/$templateId")
        ->setTemplateMetadata($templateMetadata);

    $updateTemplateRequest = (new UpdateTemplateRequest())->setTemplate($template);

    $response = $client->updateTemplate($updateTemplateRequest);

    printf('Template updated: %s' . PHP_EOL, $response->getName());
}
// [END modelarmor_update_template_metadata]

// The following 2 lines are only needed to execute the samples on the CLI.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
