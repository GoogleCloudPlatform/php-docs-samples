<?php

/**
 * Copyright 2018 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Google\Cloud\Samples\Dlp;

# [START dlp_list_triggers]
use Google\Cloud\Dlp\V2\DlpServiceClient;

/**
 * List Data Loss Prevention API job triggers.
 * @param string $callingProjectId The GCP Project ID to run the API call under
 */
function list_triggers($callingProjectId)
{
    // Instantiate a client.
    $dlp = new DlpServiceClient();

    $parent = $dlp->projectName($callingProjectId);

    // Run request
    $response = $dlp->listJobTriggers($parent);

    // Print results
    $triggers = $response->iterateAllElements();
    foreach ($triggers as $trigger) {
        printf('Trigger %s' . PHP_EOL, $trigger->getName());
        printf('  Created: %s' . PHP_EOL, $trigger->getCreateTime()->getSeconds());
        printf('  Updated: %s' . PHP_EOL, $trigger->getUpdateTime()->getSeconds());
        printf('  Display Name: %s' . PHP_EOL, $trigger->getDisplayName());
        printf('  Description: %s' . PHP_EOL, $trigger->getDescription());
        printf('  Status: %s' . PHP_EOL, $trigger->getStatus());
        printf('  Error count: %s' . PHP_EOL, count($trigger->getErrors()));
    }
}
# [END dlp_list_triggers]
