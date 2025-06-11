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

// [START modelarmor_sanitize_model_response_with_user_prompt]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\SanitizeModelResponseRequest;
use Google\Cloud\ModelArmor\V1\DataItem;

/**
 * Sanitizes a model response with user prompt using the specified template.
 *
 * @param string $projectId The ID of your Google Cloud Platform project (e.g. 'my-project').
 * @param string $locationId The ID of the location where the template is stored (e.g. 'us-central1').
 * @param string $templateId The ID of the template (e.g. 'my-template').
 * @param string $modelResponse The model response to sanitize (e.g. 'my-model-response').
 * @param string $userPrompt The user prompt for the model response (e.g. 'my-user-prompt').
 */
function sanitize_model_response_with_user_prompt($projectId, $locationId, $templateId, $modelResponse, $userPrompt):void
{
    $options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];
    $client = new ModelArmorClient($options);

    $modelResponseRequest = (new SanitizeModelResponseRequest())
        ->setName("projects/$projectId/locations/$locationId/templates/$templateId")
        ->setModelResponseData((new DataItem())->setText($modelResponse))
        ->setUserPrompt($userPrompt);

    $response = $client->sanitizeModelResponse($modelResponseRequest);

    printf('Result for Model Response Sanitization with User Prompt: %s' . PHP_EOL, $response->serializeToJsonString());
}
// [END modelarmor_sanitize_model_response_with_user_prompt]

// The following 2 lines are only needed to execute the samples on the CLI.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
