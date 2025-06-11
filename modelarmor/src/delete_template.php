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

// [START modelarmor_delete_template]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\DeleteTemplateRequest;

/**
 * Deletes a Model Armor template.
 *
 * @param string $projectId The ID of the project (e.g. 'my-project').
 * @param string $locationId The ID of the location (e.g. 'us-central1').
 * @param string $templateId The ID of the template (e.g. 'my-template').
 */
function delete_template(string $projectId, string $locationId, string $templateId): void
{
    $options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];
    $client = new ModelArmorClient($options);
    $templateName = sprintf('projects/%s/locations/%s/templates/%s', $projectId, $locationId, $templateId);

    $dltTemplateRequest = (new DeleteTemplateRequest())->setName($templateName);

    $client->deleteTemplate($dltTemplateRequest);

    printf('Deleted template: %s' . PHP_EOL, $templateName);
}
// [END modelarmor_delete_template]

// The following 2 lines are only needed to execute the samples on the CLI.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
