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
    return printf("Usage: php %s PROJECT_ID LOCATION_ID TEMPLATE_ID LABEL_KEY LABEL_VALUE\n", basename(__FILE__));
}
list($_, $projectId, $locationId, $templateId, $labelKey, $labelValue) = $argv;

// [START modelarmor_update_template_with_labels]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\UpdateTemplateRequest;
use Google\Cloud\ModelArmor\V1\Template;
use Google\Protobuf\FieldMask;

/** Uncomment and populate these variables in your code. */
// $projectId = "YOUR_GOOGLE_CLOUD_PROJECT"; // e.g. 'my-project';
// $locationId = 'YOUR_LOCATION_ID'; // e.g. 'us-central1';
// $templateId = 'YOUR_TEMPLATE_ID'; // e.g. 'my-template';
// $labelKey = 'YOUR_LABEL_KEY'; // e.g. 'my-label-key';
// $labelValue = 'YOUR_LABEL_VALUE'; // e.g. 'my-label-value';

// Specify regional endpoint.
$options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];

// Instantiates a client.
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
// [END modelarmor_update_template_with_labels]
