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

if (count($argv) != 4) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID TEMPLATE_ID\n", basename(__FILE__));
}
list($_, $projectId, $locationId, $templateId) = $argv;

// [START modelarmor_get_template]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\GetTemplateRequest;

/** Uncomment and populate these variables in your code. */
// $projectId = "YOUR_GOOGLE_CLOUD_PROJECT"; // eg. 'my-project'
// $locationId = 'YOUR_LOCATION_ID'; // eg. 'us-central1'
// $templateId = 'YOUR_TEMPLATE_ID'; // eg. 'my-template'

// Specify regional endpoint.
$options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];

// Instantiates a client.
$client = new ModelArmorClient($options);

// Construct the full resource name of the template.
$name = sprintf('projects/%s/locations/%s/templates/%s', $projectId, $locationId, $templateId);

// Prepare the request.
$getTemplateRequest = new GetTemplateRequest()->setName($name);

$response = $client->getTemplate($getTemplateRequest);

printf('Template retrieved: %s' . PHP_EOL, $response->getName());
// [END modelarmor_get_template]
