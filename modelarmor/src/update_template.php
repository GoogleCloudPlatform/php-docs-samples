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

// [START modelarmor_update_template]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\DetectionConfidenceLevel;
use Google\Cloud\ModelArmor\V1\PiAndJailbreakFilterSettings\PiAndJailbreakFilterEnforcement;
use Google\Cloud\ModelArmor\V1\PiAndJailbreakFilterSettings;
use Google\Cloud\ModelArmor\V1\MaliciousUriFilterSettings;
use Google\Cloud\ModelArmor\V1\UpdateTemplateRequest;
use Google\Cloud\ModelArmor\V1\FilterConfig;
use Google\Cloud\ModelArmor\V1\Template;

/** Uncomment and populate these variables in your code. */
// $projectId = "YOUR_GOOGLE_CLOUD_PROJECT"; // e.g. 'my-project';
// $locationId = 'YOUR_LOCATION_ID'; // e.g. 'us-central1';
// $templateId = 'YOUR_TEMPLATE_ID'; // e.g. 'my-template';

// Specify regional endpoint.
$options = ['apiEndpoint' => "modelarmor.$locationId.rep.googleapis.com"];

// Instantiates a client.
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

$template = (new Template())
    ->setFilterConfig($templateFilterConfig)
    ->setName("projects/$projectId/locations/$locationId/templates/$templateId");

$updateTemplateRequest = (new UpdateTemplateRequest())->setTemplate($template);

// Send the request to update the template.
$response = $client->updateTemplate($updateTemplateRequest);

printf('Template updated: %s' . PHP_EOL, $response->getName());
// [END modelarmor_update_template]
