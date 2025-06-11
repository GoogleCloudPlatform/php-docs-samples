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

// [START modelarmor_update_template_labels]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\UpdateTemplateRequest;
use Google\Cloud\ModelArmor\V1\Template;
use Google\Protobuf\FieldMask;

/**
 * Creates a Model Armor template with labels.
 *
 * @param string $projectId The ID of the project (e.g. 'my-project').
 * @param string $locationId The ID of the location (e.g. 'us-central1').
 * @param string $templateId The ID of the template (e.g. 'my-template').
 * @param string $labelKey The key of the label to add (e.g. 'my-label-key').
 * @param string $labelValue The value of the label to add (e.g. 'my-label-value').
 */
function update_template_labels($projectId, $locationId, $templateId, $labelKey, $labelValue) {
    $options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];
    $client = new ModelArmorClient($options);

    $template = (new Template())
        ->setLabels([$labelKey => $labelValue])
        ->setName("projects/$projectId/locations/$locationId/templates/$templateId");

    // Define the update mask to specify which fields to update.
    $updateMask = [
        'paths' => ['labels'],
    ];

    $updateRequest = (new UpdateTemplateRequest())
        ->setTemplate($template)
        ->setUpdateMask((new FieldMask($updateMask)));

    $response = $client->updateTemplate($updateRequest);

    printf('Template updated: %s' . PHP_EOL, $response->getName());
}
// [END modelarmor_update_template_labels]

// The following 2 lines are only needed to execute the samples on the CLI.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
