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

if (count($argv) != 2) {
    return printf("Usage: php %s PROJECT_ID\n", basename(__FILE__));
}
list($_, $projectId) = $argv;

// [START modelarmor_get_project_floor_settings]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\GetFloorSettingRequest;

/** Uncomment and populate these variables in your code. */
// $projectId = "YOUR_PROJECT_ID"; // e.g. 'my-project-id';

// Instantiates a client.
$client = new ModelArmorClient();

$floorSettingsName = sprintf('projects/%s/locations/global/floorSetting', $projectId);

$response = $client->getFloorSetting((new GetFloorSettingRequest())->setName($floorSettingsName));

printf("Floor settings retrieved successfully: %s\n", $response->serializeToJsonString());
// [END modelarmor_get_project_floor_settings]
