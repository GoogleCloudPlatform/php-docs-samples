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
    return printf("Usage: php %s FOLDER_ID\n", basename(__FILE__));
}
list($_, $folderId) = $argv;


// [START modelarmor_update_folder_floor_settings]
use Google\Cloud\ModelArmor\V1\Client\ModelArmorClient;
use Google\Cloud\ModelArmor\V1\RaiFilterType;
use Google\Cloud\ModelArmor\V1\DetectionConfidenceLevel;
use Google\Cloud\ModelArmor\V1\UpdateFloorSettingRequest;
use Google\Cloud\ModelArmor\V1\FilterConfig;
use Google\Cloud\ModelArmor\V1\FloorSetting;
use Google\Cloud\ModelArmor\V1\RaiFilterSettings;
use Google\Cloud\ModelArmor\V1\RaiFilterSettings\RaiFilter;

/**
 * Updates the floor settings for a given folder.
 *
 * @param string $folderId The folder Id for which the floor settings is to be updated.
 *
 */
function update_folder_floor_settings($folderId) {
    $client = new ModelArmorClient();

    $floorSettingsName = sprintf('folders/%s/locations/global/floorSetting', $folderId);

    // Build the floor settings with your preferred filters
    // For more details on filters, please refer to the following doc:
    // https://cloud.google.com/security-command-center/docs/key-concepts-model-armor#ma-filters

    $raiFilterSetting = (new RaiFilterSettings())
        ->setRaiFilters([
            (new RaiFilter())
                ->setFilterType(RaiFilterType::HATE_SPEECH)
                ->setConfidenceLevel(DetectionConfidenceLevel::HIGH)
        ]);

    $filterConfig = (new FilterConfig())->setRaiSettings($raiFilterSetting);
    $floorSetting = (new FloorSetting())
        ->setName($floorSettingsName)
        ->setFilterConfig($filterConfig)
        ->setEnableFloorSettingEnforcement(true);

    $updateRequest = (new UpdateFloorSettingRequest())->setFloorSetting($floorSetting);

    $response = $client->updateFloorSetting($updateRequest);

    printf("Floor setting updated: %s\n", $response->getName());
}
// [END modelarmor_update_folder_floor_settings]

// The following 2 lines are only needed to execute the samples on the CLI.
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
