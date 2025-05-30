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

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 6) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID TEMPLATE_ID MODEL_RESPONSE USER_PROMPT\n", basename(__FILE__));
}
list($_, $projectId, $locationId, $templateId, $modelResponse, $userPrompt) = $argv;

// [START modelarmor_sanitize_model_response_with_user_prompt]
// Import the ModelArmor client library.
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\SanitizeModelResponseRequest;
use Google\Cloud\ModelArmor\V1\DataItem;

/** Uncomment and populate these variables in your code. */
// $projectId = "YOUR_GOOGLE_CLOUD_PROJECT"; // e.g. 'my-project';
// $locationId = 'YOUR_LOCATION_ID'; // e.g. 'us-central1';
// $templateId = 'YOUR_TEMPLATE_ID'; // e.g. 'my-template';
// $modelResponse = 'YOUR_MODEL_RESPONSE'; // e.g. 'my-model-response';
// $userPrompt = 'YOUR_USER_PROMPT'; // e.g. 'my-user-prompt';

// Specify regional endpoint.
$options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];

// Instantiates a client.
$client = new ModelArmorClient($options);

$modelResponseRequest = (new SanitizeModelResponseRequest())
    ->setName("projects/$projectId/locations/$locationId/templates/$templateId")
    ->setModelResponseData((new DataItem())->setText($modelResponse))
    ->setUserPrompt($userPrompt);

$response = $client->sanitizeModelResponse($modelResponseRequest);

printf('Result for Model Response Sanitization with User Prompt: %s' . PHP_EOL, $response->serializeToJsonString());
// [END modelarmor_sanitize_model_response_with_user_prompt]
